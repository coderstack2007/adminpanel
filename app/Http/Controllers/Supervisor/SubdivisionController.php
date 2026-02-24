<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subdivision;
use Illuminate\Http\Request;

class SubdivisionController extends Controller
{
    // ─── Для supervisor ──────────────────────────────────────
    public function index(Department $department)
    {
        $department->load('branch');

        $subdivisions = Subdivision::where('department_id', $department->id)
            ->withCount('positions')
            ->orderByDesc('created_at')
            ->get();

        return view('supervisor.subdivisions', compact('department', 'subdivisions'));
    }

    public function store(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subdivisions,code',
        ]);

        $department->subdivisions()->create($validated);

        return redirect()
            ->route('supervisor.departments.subdivisions.index', $department)
            ->with('success', 'Подразделение успешно создано.');
    }

    public function destroy(Department $department, Subdivision $subdivision)
    {
        if ($subdivision->positions()->exists()) {
            return redirect()
                ->route('supervisor.departments.subdivisions.index', $department)
                ->with('error', 'Нельзя удалить подразделение с должностями.');
        }

        $subdivision->delete();

        return redirect()
            ->route('supervisor.departments.subdivisions.index', $department)
            ->with('success', 'Подразделение удалено.');
    }

    public function employeeView(Request $request)
    {
        $user = $request->user();

        $user->load([
            'branch',
            'department',
            'subdivision.positions.users',
            'subdivision.head', // ← ответственный за подразделение
            'position',
        ]);

        if (!$user->subdivision_id) {
            return view('employee.subdivision', [
                'user'        => $user,
                'subdivision' => null,
                'positions'   => collect(),
            ]);
        }

        return view('employee.subdivision', [
            'user'        => $user,
            'subdivision' => $user->subdivision,
            'positions'   => $user->subdivision->positions,
        ]);
    }
}