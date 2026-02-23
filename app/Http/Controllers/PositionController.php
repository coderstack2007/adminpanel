<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Subdivision;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Subdivision $subdivision)
    {
        $subdivision->load('department.branch');

        $positions = Position::where('subdivision_id', $subdivision->id)
            ->orderByDesc('created_at')
            ->get();

        return view('supervisor.positions', compact('subdivision', 'positions'));
    }

    public function store(Request $request, Subdivision $subdivision)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|in:A,B,C,D',
            'grade'    => 'required|integer|min:1|max:5',
            'is_vacant' => 'sometimes|boolean',
        ]);

        $validated['is_vacant'] = $request->has('is_vacant');

        $subdivision->positions()->create($validated);

        return redirect()
            ->route('supervisor.subdivisions.positions.index', $subdivision)
            ->with('success', 'Должность успешно создана.');
    }

    public function destroy(Subdivision $subdivision, Position $position)
    {
        if ($position->users()->exists()) {
            return redirect()
                ->route('supervisor.subdivisions.positions.index', $subdivision)
                ->with('error', 'Нельзя удалить должность — есть привязанные сотрудники.');
        }

        $position->delete();

        return redirect()
            ->route('supervisor.subdivisions.positions.index', $subdivision)
            ->with('success', 'Должность удалена.');
    }
}