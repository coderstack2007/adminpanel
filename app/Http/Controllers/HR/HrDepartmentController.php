<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class HrDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Branch::with('departments')->where('is_active', true);

        // Если HR привязан к конкретному филиалу — показываем только его
        if ($user->branch_id) {
            $query->where('id', $user->branch_id);
        }

        $branches = $query->orderBy('name')->get();

        return view('hr.departments', compact('branches'));
    }
}