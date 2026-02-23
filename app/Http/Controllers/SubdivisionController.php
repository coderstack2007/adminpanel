<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Subdivision;
use Illuminate\Http\Request;

class SubdivisionController extends Controller
{
    public function index(Department $department)
    {
        $department->load('branch'); // ← добавь эту строку

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
}