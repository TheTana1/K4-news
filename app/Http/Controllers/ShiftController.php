<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftRequest;
use App\Models\Shift;
use App\Repositories\ShiftRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(
        readonly ShiftRepository $shiftRepository,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shift::class);

        $data = $this->shiftRepository->index($request->query('month'));

        return view('shifts.index', $data);
    }

    /**
     * Сотрудник отправляет свою строку на рассмотрение.
     */
    public function submit(ShiftRequest $request): RedirectResponse
    {

        $validated = $request->validated();

        // Группируем по user_id
        $grouped = collect($validated['days'])->groupBy('user_id');

        foreach ($grouped as $userId => $days) {
            $this->authorize('submit', [Shift::class, (int) $userId]);

            $this->shiftRepository->submitOwn(
                (int) $userId,
                $days->map(fn ($d) => [
                    'date' => $d['date'],
                    'type' => $d['type'],
                ])->all()
            );
        }

        return redirect()
            ->route('shifts.index', ['month' => $validated['month']])
            ->with('success', 'Ваша строка отправлена на рассмотрение.');
    }

    /**
     * Админ/модератор сохраняет изменения (approved).
     */
    public function save(ShiftRequest $request): RedirectResponse
    {

        $validated = $request->validated();

        $this->authorize('saveAll', Shift::class);

        $grouped = collect($validated['days'])->groupBy('user_id');

        foreach ($grouped as $userId => $days) {
            $this->shiftRepository->saveAll(
                (int) $userId,
                $days->map(fn ($d) => [
                    'date' => $d['date'],
                    'type' => $d['type'],
                ])->all()
            );
        }

        return redirect()
            ->route('shifts.index', ['month' => $validated['month']])
            ->with('success', 'Изменения сохранены.');
    }

    /**
     * Согласовать смену (pending → approved).
     */
    public function approve(int $user, string $date): RedirectResponse
    {
        $this->authorize('approve', Shift::class);

        $this->shiftRepository->approve($user, $date);

        return back()->with('success', 'Смена согласована.');
    }
}
