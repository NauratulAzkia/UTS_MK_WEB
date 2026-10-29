<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $query = Sale::query()->where('status', 'completed')
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('sold_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('sold_at', '<=', $date));
        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return view('reports.index', [
            'from' => $filters['from'] ?? '',
            'to' => $filters['to'] ?? '',
            'total' => (clone $query)->sum('total'),
            'count' => (clone $query)->count(),
            'rows' => (clone $query)->selectRaw('DATE(sold_at) as day, COUNT(*) as transactions, SUM(total) as total')
                ->groupBy('day')->orderByDesc('day')->paginate(20)->withQueryString(),
        ]);
    }
}
