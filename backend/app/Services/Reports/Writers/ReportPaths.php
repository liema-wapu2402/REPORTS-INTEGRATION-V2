<?php

namespace App\Services\Reports\Writers;

final class ReportPaths
{
    /** Absolute path inside storage/app/reports (directory is created if missing). */
    public static function for(string $filename): string
    {
        $dir = storage_path('app/reports');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . DIRECTORY_SEPARATOR . $filename;
    }
}
