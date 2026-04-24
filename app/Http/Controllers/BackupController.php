<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function fullZip()
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['owner', 'admin', 'super_admin'], true), 403);

        $teamId = $user->team_id;
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crm-backup-' . now()->format('Ymd-His-') . uniqid();

        if (!mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
            abort(500, 'Failed to create temp directory');
        }

        $exports = [
            'leads'      => Lead::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
            'deals'      => Deal::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
            'tasks'      => Task::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
            'properties' => Property::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
        ];

        foreach ($exports as $name => $records) {
            $file = fopen("{$tmpDir}/{$name}.csv", 'w');
            if ($records->isNotEmpty()) {
                fputcsv($file, array_keys($records->first()->toArray()));
                foreach ($records as $row) {
                    fputcsv($file, array_values($row->toArray()));
                }
            } else {
                fputcsv($file, ['(no records)']);
            }
            fclose($file);
        }

        $zipPath = $tmpDir . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Failed to create ZIP archive');
        }
        foreach (glob("{$tmpDir}/*.csv") as $csv) {
            $zip->addFile($csv, basename($csv));
        }
        $zip->close();

        // Cleanup temp CSVs (keep the zip for download)
        array_map('unlink', glob("{$tmpDir}/*.csv"));
        @rmdir($tmpDir);

        return response()->download($zipPath, 'crm-backup-' . now()->format('Ymd-His') . '.zip')
            ->deleteFileAfterSend(true);
    }
}
