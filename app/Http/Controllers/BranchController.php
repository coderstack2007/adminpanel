<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{

   
    public function index()
    {
        $branches = Branch::withCount('departments')
            ->orderByDesc('created_at')
            ->get();

        return view('supervisor.branches', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:branches,code',
            'address' => 'nullable|string|max:500',
        ]);

        Branch::create($validated);

        return redirect()
            ->route('supervisor.branches.index')
            ->with('success', 'Филиал успешно создан.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->departments()->exists()) {
            return redirect()
                ->route('supervisor.branches.index')
                ->with('error', 'Нельзя удалить филиал с отделами.');
        }

        $branch->delete();

        return redirect()
            ->route('supervisor.branches.index')
            ->with('success', 'Филиал удалён.');
    }
}