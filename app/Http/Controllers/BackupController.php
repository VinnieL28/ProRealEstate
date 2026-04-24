<?php

namespace App\Http\Controllers;

use App\Exports\DealsExport;
use App\Exports\LeadsExport;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BackupController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['owner', 'admin', 'super_admin'], true), 403);
    }

    public function leadsExcel()
    {
        $this->ensureAdmin();
        return Excel::download(
            new LeadsExport(Auth::user()?->team_id),
            'leads-backup-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function dealsExcel()
    {
        $this->ensureAdmin();
        return Excel::download(
            new DealsExport(Auth::user()?->team_id),
            'deals-backup-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function fullZip()
    {
        $this->ensureAdmin();
        $teamId = Auth::user()?->team_id;
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

        $flatten = function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }
            return (string) ($value ?? '');
        };

        foreach ($exports as $name => $records) {
            $file = fopen("{$tmpDir}/{$name}.csv", 'w');
            if ($records->isNotEmpty()) {
                fputcsv($file, array_keys($records->first()->toArray()));
                foreach ($records as $row) {
                    fputcsv($file, array_map($flatten, array_values($row->toArray())));
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
