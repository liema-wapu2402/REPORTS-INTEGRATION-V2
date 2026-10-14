<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Reports\Samples\SampleCatalog;
use App\Services\Reports\Samples\SampleReportService;
use Illuminate\Http\Request;
use Throwable;

class SampleController extends Controller
{
    public function __construct(protected SampleReportService $samples) {}

    /** GET /api/samples - catalog grouped by generator. */
    public function index()
    {
        return response()->json([
            'generators' => SampleCatalog::grouped(),
            'total' => count(SampleCatalog::all()),
        ]);
    }

    /** GET /api/samples/{id}/preview - first rows of the sample dataset. */
    public function preview(string $id)
    {
        $sample = SampleCatalog::find($id);
        abort_unless($sample, 404, 'Unknown sample.');

        return response()->json([
            'sample' => SampleCatalog::publicShape($sample),
        ] + $this->samples->preview($sample));
    }

    /** POST /api/samples/{id}/generate - builds the file with its generator. */
    public function generate(Request $request, string $id)
    {
        $options = $request->validate([
            'paper' => 'nullable|in:A4,Letter,Legal',
            'orientation' => 'nullable|in:portrait,landscape',
        ]);

        abort_unless(SampleCatalog::find($id), 404, 'Unknown sample.');

        try {
            return response()->json($this->samples->generate($id, array_filter($options)));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Generation failed: ' . class_basename($e) . ' - ' . $e->getMessage(),
                'hint' => 'Check that the engine for this sample is installed (see System Status).',
            ], 500);
        }
    }
}
