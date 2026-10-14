<?php

use Illuminate\Support\Facades\Route;

// The React app (frontend/) is served separately by Vite/your web server.
// This backend is API-only - see routes/api.php.
Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'status' => 'ok',
    'api' => url('/api'),
]));
