<?php

namespace App\Repositories;

use App\Models\News;
use App\Models\File;
use App\Http\Requests\NewsRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NewsRepository
{
    private const NEWS_PER_PAGE = 10;

    final public function paginate(int $countPaginate = self::NEWS_PER_PAGE)
    {
        return News::query()
            ->with(['author', 'files'])
            ->latest()
            ->paginate($countPaginate)
            ->withQueryString();
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

            Log::info('News created successfully', [
                'news_id' => $news->id,
                'content' => $news->content
            ]);

            return $news->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to create news: ' . $exception->getMessage(), [
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

            Log::info('Advertisement updated successfully', [
                'news_id' => $news->id,
                'user_id' => auth()->id()
            ]);

            return $news->load('files');

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to update advertisement: ' . $exception->getMessage(), [
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

            Log::info('Advertisement deleted successfully', [
                'news_id' => $news->id
            ]);

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Failed to delete advertisement: ' . $exception->getMessage(), [
                'news_id' => $news->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении объявления: ' . $exception->getMessage());
        }
    }

    final public function search(string $query, int $countPaginate = self::NEWS_PER_PAGE)
    {
        return News::query()
            ->with(['author', 'files'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->orWhere('short_description', 'like', "%{$query}%")
            ->latest()
            ->paginate($countPaginate)
            ->withQueryString();
    }

    final public function findWithRelations(int $id): ?News
    {
        return News::with(['author', 'files', 'comments'])->find($id);
    }
}
