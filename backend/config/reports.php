<?php

return [

    'version' => env('REPORTS_VERSION', '2.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Default PDF driver
    |--------------------------------------------------------------------------
    | Which engine PdfReportService falls back to when the request does not
    | specify one. One of: "dompdf", "snappy", "puppeteer".
    */
    'default_pdf_driver' => env('REPORT_PDF_DRIVER', 'dompdf'),

    'storage_disk' => env('REPORT_STORAGE_DISK', 'local'),

    'output_path' => 'reports',

    'pdftk_binary' => env('PDFTK_PATH', '/usr/bin/pdftk'),

    'wkhtmltopdf_binary' => env('WKHTMLTOPDF_BINARY', '/usr/bin/wkhtmltopdf'),
    'wkhtmltoimage_binary' => env('WKHTMLTOIMAGE_BINARY', '/usr/bin/wkhtmltoimage'),

    'node_binary' => env('NODE_BINARY_PATH', '/usr/bin/node'),
    'npm_binary' => env('NPM_BINARY_PATH', '/usr/bin/npm'),
    'chrome_binary' => env('CHROME_PATH', '/usr/bin/chromium'),
];
