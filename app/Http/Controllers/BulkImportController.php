<?php

namespace App\Http\Controllers;

use App\Models\BulkImport;
use App\Services\BulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    public function __construct(
        protected BulkImportService $importService
    ) {}

    public function index(): View
    {
        $imports = BulkImport::with('admin')
            ->latest('id')
            ->paginate(10);

        return view('admin.bulk-import.index', compact('imports'));
    }

    public function downloadTemplate(string $type): StreamedResponse|RedirectResponse
    {
        if (!in_array($type, ['patients', 'medicines', 'staff'], true)) {
            return back()->with('error', 'Invalid template type specified.');
        }

        return $this->importService->downloadTemplate($type);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_type' => 'required|in:patients,medicines,staff',
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        $type = $request->input('import_type');
        $file = $request->file('file');
        $admin = Auth::user();

        try {
            $bulkImport = match ($type) {
                'patients' => $this->importService->importPatients($file, $admin),
                'medicines' => $this->importService->importMedicines($file, $admin),
                'staff' => $this->importService->importStaff($file, $admin),
            };

            if ($bulkImport->failed_rows > 0) {
                return redirect()->route('admin.bulk-import.show', $bulkImport)
                    ->with('warning', "Import partially completed: {$bulkImport->successful_rows} records added, {$bulkImport->failed_rows} rows had errors. Review the error log below.");
            }

            return redirect()->route('admin.bulk-import.index')
                ->with('success', "Bulk import successful! {$bulkImport->successful_rows} records added cleanly.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function show(BulkImport $bulkImport): View
    {
        $bulkImport->load('admin');
        return view('admin.bulk-import.show', compact('bulkImport'));
    }
}
