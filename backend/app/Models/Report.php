<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single row per generated file. See the `reports` table / its
 * migration for column meanings.
 *
 * @property string $type pdf|excel|csv|docx|fillable_pdf
 * @property string|null $driver dompdf|snappy|puppeteer (pdf only)
 */
class Report extends Model
{
    use HasFactory;

    public const TYPE_PDF = 'pdf';
    public const TYPE_EXCEL = 'excel';
    public const TYPE_CSV = 'csv';
    public const TYPE_DOCX = 'docx';
    public const TYPE_FILLABLE_PDF = 'fillable_pdf';

    public const TYPES = [
        self::TYPE_PDF,
        self::TYPE_EXCEL,
        self::TYPE_CSV,
        self::TYPE_DOCX,
        self::TYPE_FILLABLE_PDF,
    ];

    protected $fillable = [
        'type',
        'driver',
        'title',
        'filename',
        'path',
        'size_bytes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
