<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request){ $this->guard($request); return Inertia::render('Reports/Index'); }
    public function salesOrders(Request $request, ReportService $reports){ $this->guard($request); return Inertia::render('Reports/SalesOrders', ['records' => $reports->salesOrders($request->all())]); }
    public function dispatches(Request $request, ReportService $reports){ $this->guard($request); return Inertia::render('Reports/Dispatches', ['records' => $reports->dispatches($request->all())]); }
    public function inventory(Request $request, ReportService $reports){ $this->guard($request); return Inertia::render('Reports/Inventory', ['records' => $reports->inventory($request->all())]); }
    public function export(Request $request, ReportService $reports, AuditLogService $audit): StreamedResponse
    {
        $this->guard($request); $type = $request->get('type', 'sales-orders');
        $records = $type === 'dispatches' ? $reports->dispatches($request->all()) : ($type === 'inventory' ? $reports->inventory($request->all()) : $reports->salesOrders($request->all()));
        $audit->log('report.exported', null, [], ['type' => $type, 'filters' => $request->all()]);
        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Reference','Status','Date','Amount']);
            foreach ($records as $record) fputcsv($out, [$record->id, $record->so_number ?? $record->dr_number ?? $record->movement_type, $record->status ?? $record->quantity_type, optional($record->po_date ?? $record->dispatch_date ?? $record->created_at)->format('Y-m-d'), $record->grand_total ?? $record->quantity ?? '']);
            fclose($out);
        }, 'vic-logistics-report.csv', ['Content-Type' => 'text/csv']);
    }
    private function guard(Request $request): void { abort_unless($request->user()->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::VIEWER]), 403); }
}