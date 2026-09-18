<?php

if (!function_exists('local_date')) {
    function local_date(?\Carbon\CarbonInterface $date, string $format = 'd.m.Y H:i', string $timezone = 'Europe/Moscow'): string
    {
        return $date ? $date->timezone($timezone)->format($format) : '';
    }
}
