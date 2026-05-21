<?php

namespace App\Http\Controllers;

use App\Models\DataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportDownloadController extends Controller
{
    public function __invoke(Request $request, DataExport $dataExport): StreamedResponse
    {
        abort_unless($dataExport->isOwnedBy($request->user()), 404);
        abort_unless($dataExport->isCompleted(), 404);
        abort_unless(Storage::disk($dataExport->disk)->exists($dataExport->path), 404);

        return Storage::disk($dataExport->disk)->download($dataExport->path, $dataExport->filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
