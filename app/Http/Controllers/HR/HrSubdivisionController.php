<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subdivision;

class HrSubdivisionController extends Controller
{
    public function index(Department $department)
    {
        $department->load('branch');

        $subdivisions = Subdivision::where('department_id', $department->id)
            ->with(['positions.users'])
            ->withCount('positions')
            ->orderBy('name')
            ->get();

        return view('hr.subdivisions', compact('department', 'subdivisions'));
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