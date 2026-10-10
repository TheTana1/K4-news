<?php

namespace App\Repositories;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ShiftRepository
{
    private const CACHE_TTL = 300; // 5 минут

    // ЧТЕНИЕ
    public function index(?string $month = null): array
    {
        $month = $month ?: Carbon::now()->format('Y-m');

        $key = 'shifts:index:' . $month;

        return Cache::tags(['shifts'])->remember($key, self::CACHE_TTL, function () use ($month) {
            $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $days  = $this->buildDays($startOfMonth, $endOfMonth);
            $users = $this->getEmployees();

            $shiftsMatrix = $this->getShiftsMatrix(
                $users->pluck('id')->all(),
                $startOfMonth,
                $endOfMonth
            );

            return [
                'days'         => $days,
                'users'        => $users,
                'shiftsMatrix' => $shiftsMatrix,
                'month'        => $startOfMonth->format('Y-m'),
            ];
        });
    }

    private function buildDays(Carbon $start, Carbon $end): array
    {
        $key = 'shifts:days:' . $start->format('Y-m');

        return Cache::tags(['shifts'])->remember($key, self::CACHE_TTL, function () use ($start, $end) {
            $days    = [];
            $current = $start->copy();

            while ($current <= $end) {
                $days[] = [
                    'date'       => $current->format('Y-m-d'),
                    'day'        => $current->day,
                    'weekday'    => $current->isoFormat('dd'),
                    'is_weekend' => $current->isWeekend(),
                ];
                $current->addDay();
            }
            return $days;
        });
    }

    private function getEmployees()
    {
        return Cache::tags(['shifts'])->remember('shifts:employees', self::CACHE_TTL, function () {
            return User::query()
                ->where('role_id', '!=', 1)
                ->whereNull('deleted_at')
                ->with('role')
                ->orderBy('role_id')
                ->orderBy('name')
                ->get(['id', 'name', 'role_id', 'avatar_path']);
        });
    }

    private function getShiftsMatrix(array $userIds, Carbon $start, Carbon $end): array
    {
        if (empty($userIds)) return [];

        $key = 'shifts:matrix:' . md5(serialize([$userIds, $start->format('Y-m')]));

        return Cache::tags(['shifts'])->remember($key, self::CACHE_TTL, function () use ($userIds, $start, $end) {
            $rows = Shift::whereIn('user_id', $userIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            $matrix = [];
            foreach ($rows as $shift) {
                $matrix[$shift->user_id][$shift->date->format('Y-m-d')] = [
                    'type'   => $shift->type,
                    'status' => $shift->status,
                ];
            }
            return $matrix;
        });
    }

    // ЗАПИСЬ
    /**
     * Сотрудник отправляет СВОЮ строку на рассмотрение.
     */
    public function submitOwn(int $userId, array $days): void
    {
        DB::beginTransaction();

        try {
            $dates = array_column($days, 'date');

            if (!empty($dates)) {
                $start = min($dates);
                $end   = max($dates);

                Shift::where('user_id', $userId)
                    ->whereBetween('date', [$start, $end])
                    ->delete();
            }

            foreach ($days as $day) {
                Shift::create([
                    'user_id' => $userId,
                    'date'    => $day['date'],
                    'type'    => $day['type'],
                    'status'  => 'pending',
                ]);
            }

            DB::commit();
            Cache::tags(['shifts'])->flush();
            Cache::tags(['dashboard'])->flush();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Ошибка отправки смен: ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);
            throw new BadRequestHttpException('Не удалось отправить смены');
        }
    }

    /**
     * Админ/модератор сохраняет изменения (approved).
     */
    public function saveAll(int $userId, array $days): void
    {
        DB::beginTransaction();

        try {
            $dates = array_column($days, 'date');

            if (!empty($dates)) {
                $start = min($dates);
                $end   = max($dates);

                Shift::where('user_id', $userId)
                    ->whereBetween('date', [$start, $end])
                    ->delete();
            }

            foreach ($days as $day) {
                Shift::create([
                    'user_id' => $userId,
                    'date'    => $day['date'],
                    'type'    => $day['type'],
                    'status'  => 'approved',
                ]);
            }

            DB::commit();
            Cache::tags(['shifts'])->flush();
            Cache::tags(['dashboard'])->flush();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Ошибка сохранения смен: ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);
            throw new BadRequestHttpException('Не удалось сохранить смены');
        }
    }

    /**
     * Согласовать смену (pending → approved).
     */
    public function approve(int $userId, string $date): bool
    {
        DB::beginTransaction();

        try {
            $shift = Shift::where('user_id', $userId)
                ->where('date', $date)
                ->firstOrFail();

            $shift->update(['status' => 'approved']);

            DB::commit();
            Cache::tags(['shifts'])->flush();
            Cache::tags(['dashboard'])->flush();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Ошибка согласования смены: ' . $e->getMessage(), [
                'user_id' => $userId,
                'date'    => $date,
            ]);
            throw new BadRequestHttpException('Не удалось согласовать смену');
        }
    }
}
