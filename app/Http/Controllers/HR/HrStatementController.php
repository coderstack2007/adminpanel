<?php
// app/Http/Controllers/Hr/HrStatementsController.php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use Illuminate\Http\Request;

class HrStatementController extends Controller
{
    public function index()
    {
        $statements = VacancyRequest::with(['position', 'subdivision', 'requester'])
            ->whereIn('status', ['submitted', 'hr_reviewed', 'approved', 'rejected', 'on_hold', 'searching', 'closed', 'confirmed_closed'])
            ->orderByDesc('created_at')
            ->get();

        return view('hr.statements', compact('statements'));
    }

    public function show(VacancyRequest $statement)
    {
        $statement->load([
            'position', 'subdivision.head.position',
            'department', 'branch', 'requester',
            'hrEditor', 'logs.user',
        ]);

        return view('department_head.statement_show', compact('statement'));
    }
}