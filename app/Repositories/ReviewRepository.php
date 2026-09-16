<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class  ReviewRepository
{
    private const PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const CACHE_TTL = 900;

    final function index(int $perPage = self::PER_PAGE)
    {
        $key = 'review-index:' . md5(
                $perPage.request('page')
            );
        return Cache::tags(['reviews-index'])->remember($key, self::CACHE_TTL, fn()=>Review::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
        );
    }
    final function show(Review $review, int $countPaginate =self::COMMENTS_PER_PAGE)
    {
        $review = Cache::tags(['reviews'])->remember(
            'review:'. $review->id,
            self::CACHE_TTL,
            fn() =>  $review
        );
        $comments = Cache::tags(['reviews'])->remember(
            'review:' . $review->id . ':comments:page:'.request()->query('page'),
            self::CACHE_TTL,
            fn () => $review->comments()
                ->with(['user.role', 'commentable'])
                ->latest()
                ->paginate($countPaginate)
                ->withQueryString()
        );
        return ['review'=>$review, 'comments'=>$comments];
    }

    public function delete(Review $review)
    {
        DB::beginTransaction();

        try {
            $result = $review->delete();

            DB::commit();
            Cache::tags(['reviews'])->flush();

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::critical('Ошибка при удалении отзыва: ' . $exception->getMessage(), [
                'review_id' => $review->id,
                'trace' => $exception->getTraceAsString()
            ]);
            throw new BadRequestHttpException('Ошибка при удалении отзыва: ' . $exception->getMessage());
        }
    }
}
