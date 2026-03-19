<?php


if (!function_exists('formatDateForInput')) {
    function formatDateForInput($date)
    {
        if (empty($date)) {
            return '';
        }

        try {
            // Если дата уже в формате Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Если дата в формате d.m.Y
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                return \Carbon\Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
            }

            // Если это объект Carbon
            if ($date instanceof \Carbon\Carbon) {
                return $date->format('Y-m-d');
            }

            // Попытка распарсить любой формат
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    }

}
