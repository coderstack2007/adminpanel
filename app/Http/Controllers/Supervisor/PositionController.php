<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'user_name' => 'nullable|string|max:255',
            'email'     => 'nullable|email|unique:users,email',
            'password'  => 'nullable|string|min:4',
            'role'      => 'nullable|exists:roles,name',
        ];

        if ($request->filled('email')) {
            $rules['user_name'] = 'required|string|max:255';
            $rules['password']  = 'required|string|min:4';
            $rules['role']      = 'required|exists:roles,name';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request, $subdivision) {
            $withUser = $request->filled('email');

            // Должность вакантна если сотрудник не создаётся сразу
            $position = $subdivision->positions()->create([
                'name'      => $validated['name'],
                'category'  => $validated['category'],
                'grade'     => $validated['grade'],
                'is_vacant' => !$withUser,  // ← сразу правильное значение
            ]);

            if ($withUser) {
                $subdivision->load('department.branch');

                $user = User::create([
                    'name'           => $validated['user_name'],
                    'email'          => $validated['email'],
                    'password'       => Hash::make($validated['password']),
                    'employee_code'  => $this->generateUniqueCode(),
                    'position_id'    => $position->id,
                    'subdivision_id' => $subdivision->id,
                    'department_id'  => $subdivision->department_id,
                    'branch_id'      => $subdivision->department->branch_id,
                ]);

                $user->assignRole($validated['role']);

                if ($validated['role'] === 'department_head') {
                    $subdivision->update(['head_user_id' => $user->id]);
                }
            }
        });

        $message = $request->filled('email')
            ? 'Должность создана и сотрудник добавлен.'
            : 'Должность успешно создана.';

        return redirect()
            ->route('supervisor.subdivisions.positions.index', $subdivision)
            ->with('success', $message);
    }

    public function destroy(Subdivision $subdivision, Position $position)
    {
        $currentUser = auth()->user();

        if ($currentUser->position_id === $position->id) {
            return redirect()
                ->route('supervisor.subdivisions.positions.index', $subdivision)
                ->with('error', 'Нельзя удалить свою собственную должность.');
        }

        DB::transaction(function () use ($position) {
            $position->users()->each(function ($user) {
                $user->roles()->detach();
                $user->delete();
            });

            $position->delete();
        });

        return redirect()
            ->route('supervisor.subdivisions.positions.index', $subdivision)
            ->with('success', 'Должность и привязанный сотрудник удалены.');
    }

    private function generateUniqueCode(): string
    {
        do {
            $letters = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 3));
            $digits  = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code    = "EMP-{$letters}{$digits}";
        } while (User::where('employee_code', $code)->exists());

        return $code;
    }
}