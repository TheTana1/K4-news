<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use App\Repositories\ShiftRepository;
use Illuminate\Http\JsonResponse;
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
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'days'        => 'required|array',
            'days.*.date' => 'required|date',
            'days.*.type' => 'required|in:off,full,half',
        ]);

        $this->authorize('submit', [Shift::class, $validated['user_id']]);

        $this->shiftRepository->submitOwn($validated['user_id'], $validated['days']);

        return response()->json(['success' => true]);
    }

    /**
     * Админ/модератор сохраняет изменения (approved).
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'days'        => 'required|array',
            'days.*.date' => 'required|date',
            'days.*.type' => 'required|in:off,full,half',
        ]);

        $this->authorize('saveAll', Shift::class);

        $this->shiftRepository->saveAll($validated['user_id'], $validated['days']);

        return response()->json(['success' => true]);
    }

    /**
     * Согласовать смену (pending → approved).
     */
    public function approve(int $user, string $date): JsonResponse
    {
        $this->authorize('approve', Shift::class);

        $this->shiftRepository->approve($user, $date);

        return response()->json(['success' => true]);
    }
}
