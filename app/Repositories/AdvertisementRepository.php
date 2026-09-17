<?php

namespace App\Repositories;

use App\Filters\AdvertisementFilter;
use App\Http\Requests\AdvertisementRequest;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdvertisementRepository
{
    private const PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const CACHE_TTL = 900;
public function __construct(readonly AdvertisementFilter $advertisementFilter)
{
}

    final public function index(Request $request, int $perPage = self::PER_PAGE)
    {
        $key = 'users-index:' . md5(serialize([
                $request->query(),
                $perPage
            ]));

        return Cache::tags(['advertisements-index'])->remember($key, self::CACHE_TTL, fn ()=>
             $this->advertisementFilter
                 ->apply(request(),Advertisement::query())
                ->forCurrentUser()
                ->with(['role'])
                ->latest()
                ->paginate($perPage)
                ->withQueryString()
        );

    }

    final  public function show(Advertisement $advertisement, int $countPaginate = self::COMMENTS_PER_PAGE): array
    {
        $advertisement = Cache::tags(['advertisement:'. $advertisement->id])->remember(
            'advertisement:'. $advertisement->id,
            self::CACHE_TTL,
            fn() =>  $advertisement->load([ 'files', 'role'])
        );
        $comments = Cache::tags(['advertisement:'. $advertisement->id])->remember(
            'advertisement:' . $advertisement->id . ':comments:page:'.request()->query('page'),
            self::CACHE_TTL,
            fn () => $advertisement->comments()
                ->with(['user.role', 'commentable'])
                ->latest()
                ->paginate($countPaginate)
                ->withQueryString()
        );
        return ['advertisement'=>$advertisement, 'comments'=>$comments];

    }

    final public function store(AdvertisementRequest $request): Advertisement
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $validatedData['telegram_author_name'] = auth()->user()->telegram_username;

            $advertisement = Advertisement::query()->create($validatedData);

            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $advertisement);
            }

            DB::commit();

            Cache::tags(['advertisements-index'])->flush();
            Cache::tags(['advertisement:'. $advertisement->id])->flush();
            Cache::tags(['dashboard'])->flush();

            return $advertisement->load('files', 'role');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Ошибка при создании объявления: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при создании объявления: ' . $exception->getMessage());
        }
    }

    final public function update(AdvertisementRequest $request, Advertisement $advertisement): Advertisement
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $advertisement);
            }
            if ($request->has('delete_files') && is_array($request->delete_files)) {
                $this->deleteFiles($advertisement, $request->delete_files);
            }
            $advertisement->update($validatedData);
            DB::commit();

            Cache::tags(['advertisements-index'])->flush();
            Cache::tags(['advertisement:' . $advertisement->id])->flush();
            Cache::tags(['dashboard'])->flush();

            return $advertisement->load('files', 'role');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при обновлении объявления: ' . $exception->getMessage(), [
                'advertisement_id' => $advertisement->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при обновлении объявления: ' . $exception->getMessage());
        }
    }

    final public function destroy(Advertisement $advertisement): bool
    {
        DB::beginTransaction();

        try {
            $this->deleteAllFiles($advertisement);
            $result = $advertisement->delete();

            DB::commit();

            Cache::tags(['advertisements-index'])->flush();
            Cache::tags(['advertisement:'. $advertisement->id])->flush();
            Cache::tags(['dashboard'])->flush();

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при удалении объявления: ' . $exception->getMessage(), [
                'advertisement_id' => $advertisement->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении объявления: ' . $exception->getMessage());
        }
    }

    protected function uploadFiles(array $files, Advertisement $advertisement): void
    {
        foreach ($files as $file) {
            $path =  $file->store('advertisements/' . $advertisement->id, 'public');

            $advertisement->files()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'disk' => 'public'
            ]);

        }
    }

    protected function deleteFiles(Advertisement $advertisement, array $fileIds): void
    {
        $files = $advertisement->files()->whereIn('id', $fileIds)->get();
        foreach ($files as $file) {
            Storage::disk($file->disk)->delete($file->file_path);
            $file->delete();
        }
    }

    protected function deleteAllFiles(Advertisement $advertisement): void
    {
        foreach ($advertisement->files as $file) {
            Storage::disk($file->disk)->delete($file->file_path);
            $file->delete();
        }
    }
}

