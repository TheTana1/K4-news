<?php

namespace App\Repositories;

use App\Http\Requests\AdvertisementRequest;
use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdvertisementRepository
{
    private const PER_PAGE = 10;

    final public function paginate(int $perPage = self::PER_PAGE)
    {
        return Advertisement::query()
            ->with(['author'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
    final public function findWithRelations(int $id): ?Advertisement
    {
        return Advertisement::with(['author', 'files', 'comments.user'])
            ->find($id);
    }

    final public function store(AdvertisementRequest $request): Advertisement
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            if (auth()->check() && !isset($validatedData['author_id'])) {
                $validatedData['author_id'] = auth()->id();
            }
            $advertisement = Advertisement::query()->create($validatedData);
            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $advertisement);
            }

            DB::commit();

            Log::info('Advertisement created successfully', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id()
            ]);

            return $advertisement->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to create advertisement: ' . $exception->getMessage(), [
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
            $advertisement->update($validatedData);
            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $advertisement);
            }
            if ($request->has('delete_files') && is_array($request->delete_files)) {
                $this->deleteFiles($advertisement, $request->delete_files);
            }

            DB::commit();

            Log::info('Advertisement updated successfully', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id()
            ]);

            return $advertisement->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to update advertisement: ' . $exception->getMessage(), [
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

            Log::info('Advertisement deleted successfully', [
                'advertisement_id' => $advertisement->id
            ]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to delete advertisement: ' . $exception->getMessage(), [
                'advertisement_id' => $advertisement->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении объявления: ' . $exception->getMessage());
        }
    }
    protected function uploadFiles(array $files, Advertisement $advertisement): void
    {
        foreach ($files as $file) {
            if ($file->isValid()) {
                $path = $file->store('advertisements/' . $advertisement->id, 'public');

                $advertisement->files()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'disk' => 'public'
                ]);
            }
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

    final public function search(string $query, int $perPage = self::PER_PAGE)
    {
        return Advertisement::query()
            ->with(['author', 'files'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->orWhere('telegram_author_name', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    final public function getActive(int $perPage = self::PER_PAGE)
    {
        return Advertisement::query()
            ->with(['author', 'files'])
            ->where('status', 'active')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}

