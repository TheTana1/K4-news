<?php

namespace App\Services;

class ReviewParseService
{
    public function parseRating(string $text): ?int
    {
        $count = mb_substr_count($text, '★', 'UTF-8');

        if ($count === 0) {
            return null;
        }
        return min($count, 5);
    }

}
