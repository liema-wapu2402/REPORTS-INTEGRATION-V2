<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Services\Reports\Samples\SampleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SampleReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_every_generator(): void
    {
        $res = $this->getJson('/api/samples')->assertOk();

        $keys = collect($res->json('generators'))->pluck('key')->all();
        $this->assertSame(['dompdf', 'snappy', 'puppeteer', 'excel', 'csv', 'docx'], $keys);
        $this->assertSame(count(SampleCatalog::all()), $res->json('total'));
    }

    public function test_every_sample_has_a_preview(): void
    {
        foreach (SampleCatalog::all() as $sample) {
            $this->getJson("/api/samples/{$sample['id']}/preview")
                ->assertOk()
                ->assertJsonStructure(['sample', 'headings', 'rows', 'total_rows']);
        }
    }

    public function test_unknown_sample_is_404(): void
    {
        $this->getJson('/api/samples/nope/preview')->assertNotFound();
        $this->postJson('/api/samples/nope/generate')->assertNotFound();
    }

    #[DataProvider('binaryFreeSamples')]
    public function test_generates_files_that_need_no_external_binary(string $id, string $ext): void
    {
        $res = $this->postJson("/api/samples/$id/generate")->assertOk();

        $path = storage_path('app/reports/' . $res->json('filename'));
        $this->assertFileExists($path);
        $this->assertGreaterThan(500, filesize($path));
        $this->assertStringEndsWith(".$ext", $path);
        $this->assertDatabaseHas('reports', ['filename' => $res->json('filename')]);

        @unlink($path);
    }

    public static function binaryFreeSamples(): array
    {
        return [
            ['dompdf-consolidated-sales', 'pdf'],
            ['dompdf-employee-directory', 'pdf'],
            ['dompdf-inventory-stock', 'pdf'],
            ['excel-sales-workbook', 'xlsx'],
            ['excel-inventory', 'xlsx'],
            ['excel-attendance', 'xlsx'],
            ['csv-transactions', 'csv'],
            ['csv-employees', 'csv'],
            ['docx-memo', 'docx'],
            ['docx-service-report', 'docx'],
        ];
    }

    public function test_generic_excel_csv_docx_endpoints(): void
    {
        $payload = ['title' => 'T', 'headings' => ['A', 'B'], 'rows' => [[1, 2], [3, 4]]];

        foreach (['excel', 'csv', 'docx'] as $kind) {
            $res = $this->postJson("/api/reports/$kind", $payload)->assertOk();
            $this->assertFileExists(storage_path('app/reports/' . $res->json('filename')));
            @unlink(storage_path('app/reports/' . $res->json('filename')));
        }
    }

    public function test_health_and_about(): void
    {
        $this->getJson('/api/system/health')->assertOk()->assertJsonPath('engines.0.key', 'dompdf');
        $this->getJson('/api/system/about')->assertOk()->assertJsonPath('name', 'Report Integrations');
    }

    public function test_delete_removes_row_and_file(): void
    {
        $res = $this->postJson('/api/samples/csv-employees/generate')->assertOk();
        $id = Report::first()->id;
        $this->deleteJson("/api/reports/$id")->assertOk();
        $this->assertFileDoesNotExist(storage_path('app/reports/' . $res->json('filename')));
        $this->assertDatabaseMissing('reports', ['id' => $id]);
    }
}
