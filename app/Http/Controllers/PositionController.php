<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PositionController extends Controller
{
    public function index(Subdivision $subdivision)
    {
        $subdivision->load('department.branch');

        $positions = Position::where('subdivision_id', $subdivision->id)
            ->with('users')
            ->orderByDesc('created_at')
            ->get();

        $roles = Role::whereNotIn('name', ['super_admin'])->get();

        return view('supervisor.positions', compact('subdivision', 'positions', 'roles'));
    }

    public function store(Request $request, Subdivision $subdivision)
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'category'  => 'required|in:A,B,C,D',
            'grade'     => 'required|integer|min:1|max:5',
            'is_vacant' => 'sometimes|boolean',
        ];

        // Если введён email — создаём пользователя, валидируем доп. поля
        if ($request->filled('email')) {
            $rules['email']    = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:4';
            $rules['role']     = 'required|exists:roles,name';
        }

        $validated = $request->validate($rules);

        // Создаём должность
        $position = $subdivision->positions()->create([
            'name' => $request->filled('user_name') ? $request->user_name : $validated['name'],
            'category'  => $validated['category'],
            'grade'     => $validated['grade'],
            'is_vacant' => $request->has('is_vacant'),
        ]);

        // Если указан email — создаём сотрудника и привязываем к должности
        if ($request->filled('email')) {
            $user = User::create([
                'name'           => $validated['name'], // имя = название должности как заглушка
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'employee_code'  => $this->generateEmployeeCode(),
                'position_id'    => $position->id,
                'subdivision_id' => $subdivision->id,
                'department_id'  => $subdivision->department_id,
                'branch_id'      => $subdivision->department->branch_id,
                'is_active'      => true,
            ]);

            $user->assignRole($validated['role']);

            // Должность теперь занята
            $position->update(['is_vacant' => false]);
        }

        return redirect()
            ->route('supervisor.subdivisions.positions.index', $subdivision)
            ->with('success', 'Должность создана' . ($request->filled('email') ? ' и сотрудник добавлен.' : '.'));
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

    private function generateEmployeeCode(): string
    {
        $last = User::whereNotNull('employee_code')->orderByDesc('id')->value('employee_code');
        $num  = $last ? (int) filter_var($last, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        return 'EMP-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}