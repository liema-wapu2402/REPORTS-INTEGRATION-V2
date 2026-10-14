<?php

namespace App\Services\Reports;

use App\Models\Report;

/**
 * Shared by every report controller: after a file is generated and saved
 * to disk, write the matching row to the `reports` table so the
 * dashboard / history page can list it.
 */
trait RecordsReport
{
    protected function recordReport(string $type, string $title, string $path, ?string $driver = null, array $metadata = []): Report
    {
        return Report::create([
            'type' => $type,
            'driver' => $driver,
            'title' => $title,
            'filename' => basename($path),
            'path' => $path,
            'size_bytes' => is_file($path) ? (int) filesize($path) : 0,
            'metadata' => $metadata,
        ]);
    }
}
