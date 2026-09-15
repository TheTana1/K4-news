<?php

namespace App\Repositories;

use App\Models\News;
use App\Models\File;
use App\Http\Requests\NewsRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NewsRepository
{
    private const PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const CACHE_TTL = 600;

    final public function paginate(int $perPage = self::PER_PAGE)
    {
        $key = 'news-index:' . md5(
                $perPage
            );
        return Cache::tags(['news'])->remember($key, self::CACHE_TTL, fn()=>News::query()
            ->forCurrentUser()
            ->with(['role'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
        );
    }
    final public function show(News $news, int $countPaginate = self::COMMENTS_PER_PAGE)
    {
        $news = Cache::tags(['news', 'news:' . $news->id])->remember(
            'news:'. $news->id,
            self::CACHE_TTL,
            fn() =>  $news->load(['files', 'role'])
        );
        $comments = Cache::tags(['news', 'news:' . $news->id])->remember(
            'news:' . $news->id . ':comments:page:'.request()->query('page'),
            self::CACHE_TTL,
            fn () => $news->comments()
                ->with(['user.role', 'commentable'])
                ->latest()
                ->paginate($countPaginate)
                ->withQueryString()
        );
        return ['news'=>$news, 'comments'=>$comments];

    }
    protected function uploadFiles(array $files, News $news): void
    {
        foreach ($files as $file) {
            if ($file->isValid()) {
                $path = $file->store('news/' . $news->id, 'public');

                $news->files()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'disk' => 'public'
                ]);
            }
        }
    }
    protected function deleteFiles( News $news, array $fileIds): void
    {
        $files = $news->files()->whereIn('id', $fileIds)->get();
        foreach ($files as $file) {
            Storage::disk($file->disk)->delete($file->file_path);
            $file->delete();
        }
    }
    protected function deleteAllFiles(News $news): void
    {
        foreach ($news->files as $file) {
            Storage::disk($file->disk)->delete($file->file_path);
            $file->delete();
        }
    }
    final public function store(NewsRequest $request): News
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            if (auth()->check() && !isset($validatedData['author_id'])) {
                $validatedData['author_id'] = auth()->id();
            }
            $news = News::query()->create($validatedData);
            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $news);
            }

            DB::commit();
            Cache::tags(['news'])->flush();
            Cache::tags(['dashboard'])->flush();
            return $news->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при создании новости: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при создании новости: ' . $exception->getMessage());
        }
    }

    final public function update(NewsRequest $request, News $news): News
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $news->update($validatedData);
            if ($request->hasFile('files')) {
                $this->uploadFiles($request->file('files'), $news);
            }
            if ($request->has('delete_files') && is_array($request->delete_files)) {
                $this->deleteFiles($news, $request->delete_files);
            }

            DB::commit();
            Cache::tags(['news'])->flush();
            Cache::tags(['dashboard'])->flush();

            return $news->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при обновлении объявления: ' . $exception->getMessage(), [
                'news_id' => $news->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при обновлении объявления: ' . $exception->getMessage());
        }
    }
    final public function destroy(News $news): bool
    {
        DB::beginTransaction();

        try {
            $this->deleteAllFiles($news);
            $result = $news->delete();

            DB::commit();
            Cache::tags(['news'])->flush();
            Cache::tags(['dashboard'])->flush();
            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при удалении объявления: ' . $exception->getMessage(), [
                'news_id' => $news->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении объявления: ' . $exception->getMessage());
        }
    }
}
