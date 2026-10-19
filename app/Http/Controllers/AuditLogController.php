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
            ->when($action, fn ($q) => $q->where('action', 'LIKE', "{$action}%"))
            ->when($search, function ($query, $search) {
                $term = '%'.strtolower($search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(description) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(action) LIKE ?', [$term])
                        ->orWhereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$term]));
                });
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
