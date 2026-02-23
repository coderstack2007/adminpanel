<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Branch $branch)
    {
        $departments = Department::where('branch_id', $branch->id)
            ->withCount('subdivisions')
            ->orderByDesc('created_at')
            ->get();

        return view('supervisor.departments', compact('branch', 'departments'));
    }

    public function store(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        $branch->departments()->create($validated);

        return redirect()
            ->route('supervisor.branches.departments.index', $branch)
            ->with('success', 'Отдел успешно создан.');
    }

    public function destroy(Branch $branch, Department $department)
    {
        if ($department->subdivisions()->exists()) {
            return redirect()
                ->route('supervisor.branches.departments.index', $branch)
                ->with('error', 'Нельзя удалить отдел с подразделениями.');
        }

        $department->delete();

        return redirect()
            ->route('supervisor.branches.departments.index', $branch)
            ->with('success', 'Отдел удалён.');
    }
}