<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->input('action');
        $search = $request->input('search');

        $logs = AuditLog::with('user')
            ->when($action, fn($q) => $q->where('action', 'LIKE', "{$action}%"))
            ->when($search, function ($query, $search) {
                $query->where('description', 'ILIKE', "%{$search}%")
                    ->orWhere('action', 'ILIKE', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'actions', 'action', 'search'));
    }
}
