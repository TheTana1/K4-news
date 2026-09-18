<?php

namespace App\Repositories;

use App\Filters\ReviewFilter;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class  ReviewRepository
{
    private const PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const CACHE_TTL = 900;
public function __construct(readonly ReviewFilter $reviewFilter)
{
}

    final function index(Request $request, int $perPage = self::PER_PAGE)
    {
        $key = 'review-index:' . md5(serialize([
                $request->query(),
                $perPage
            ]));
        return Cache::tags(['reviews-index'])->remember($key, self::CACHE_TTL, fn()=>
            $this->reviewFilter
                ->apply($request, Review::query())
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
        );
    }
    final function show(Review $review, int $countPaginate =self::COMMENTS_PER_PAGE)
    {
        $review = Cache::tags(['review:'. $review->id])->remember(
            'review:'. $review->id,
            self::CACHE_TTL,
            fn() =>  $review
        );
        $comments = Cache::tags(['review:'. $review->id])->remember(
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
    public static function store($text, $count, $from, $publishedAt)
    {
        DB::beginTransaction();
        try {

            Review::create([
                'content' => $text,
                'rating' => $count,
                'telegram_author_name' => $from->username,
                'published_at' => \Carbon\Carbon::createFromTimestamp($publishedAt),
            ]);
            DB::commit();

            Cache::tags(['reviews-index'])->flush();
            Cache::tags(['dashboard'])->flush();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error("Ошибка создания отзыва: " . $e->getMessage());
            return false;
        }
    }
    public function delete(Review $review)
    {
        DB::beginTransaction();

        try {
            $result = $review->delete();

            DB::commit();
            Cache::tags(['review:'. $review->id])->flush();
            Cache::tags(['reviews-index'])->flush();

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
