<?php

namespace App\Services;

use App\Models\ButcherRecord;
use App\Models\LabelPrintingTask;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Label PDF Generator Service
 * 
 * Generates professional, space-efficient PDF labels for butcher records.
 * Supports 4 templates: Compact, Standard, Detailed, Premium
 * 
 * Each template is designed for A6 paper size (105x148mm) - 4 labels per A4 sheet
 * Optimized for minimal paper waste and maximum information density.
 */
class LabelPdfGenerator
{
    /**
     * @var LabelPrintingTask
     */
    protected $task;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $butcherRecords;

    /**
     * Constructor
     *
     * @param LabelPrintingTask $task
     */
    public function __construct(LabelPrintingTask $task)
    {
        $this->task = $task;
        $this->butcherRecords = $task->butcherRecords();
    }

    /**
     * Generate PDF labels based on task configuration
     *
     * @return array ['success' => bool, 'path' => string|null, 'size' => string|null, 'error' => string|null]
     */
    public function generate()
    {
        try {
            // Mark task as started
            $this->task->markAsStarted();

            // Validate butcher records exist
            if ($this->butcherRecords->isEmpty()) {
                throw new \Exception('No butcher records found for label generation');
            }

            // Generate HTML based on template type
            $html = $this->generateHtml();

            // Generate PDF
            $pdf = Pdf::loadHTML($html);
            
            // Set paper size to A6 landscape for optimal label printing
            $pdf->setPaper([0, 0, 419.53, 297.64], 'portrait'); // A6 in points (105x148mm)
            
            // Set options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'enable_php' => false,
            ]);

            // Generate filename
            $filename = 'labels_' . $this->task->task_number . '_' . time() . '.pdf';

            // Ensure directory exists
            $directory = public_path('storage/images');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save PDF directly to public directory
            $fullPath = public_path('storage/images/' . $filename);
            file_put_contents($fullPath, $pdf->output());

            // Get file size
            $fileSize = filesize($fullPath);
            $fileSizeFormatted = $this->formatBytes($fileSize);

            // Mark task as completed with relative path
            $this->task->markAsCompleted('storage/images/' . $filename, $fileSizeFormatted);

            return [
                'success' => true,
                'path' => 'storage/images/' . $filename,
                'size' => $fileSizeFormatted,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('Label PDF Generation Failed: ' . $e->getMessage());
            $this->task->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate HTML based on selected template
     *
     * @return string
     */
    protected function generateHtml()
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Butcher Record Labels</title>
    ' . $this->getCommonStyles() . '
</head>
<body>';

        $count = 0;
        foreach ($this->butcherRecords as $record) {
            $count++;
            
            // Add page break after every 4 labels (A6 labels on A4 sheet)
            if ($count > 1 && ($count - 1) % 4 == 0) {
                $html .= '<div class="page-break"></div>';
            }

            // Generate label based on template type
            switch ($this->task->template_type) {
                case 'Compact':
                    $html .= $this->generateCompactLabel($record);
                    break;
                case 'Standard':
                    $html .= $this->generateStandardLabel($record);
                    break;
                case 'Detailed':
                    $html .= $this->generateDetailedLabel($record);
                    break;
                case 'Premium':
                    $html .= $this->generatePremiumLabel($record);
                    break;
                default:
                    $html .= $this->generateStandardLabel($record);
            }

            // Update progress
            $this->task->updateProgress($count);
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Get common CSS styles for all templates
     *
     * @return string
     */
    protected function getCommonStyles()
    {
        return '<style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "DejaVu Sans", sans-serif;
                font-size: 9pt;
                line-height: 1.2;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            .label {
                width: 105mm;
                height: 148mm;
                padding: 8mm;
                border: 1px solid #ddd;
                page-break-inside: avoid;
                position: relative;
            }
            
            .label-header {
                text-align: center;
                border-bottom: 2px solid #333;
                padding-bottom: 4mm;
                margin-bottom: 4mm;
            }
            
            .company-name {
                font-size: 14pt;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 2mm;
            }
            
            .company-tagline {
                font-size: 8pt;
                color: #7f8c8d;
                font-style: italic;
            }
            
            .label-body {
                margin-bottom: 4mm;
            }
            
            .info-row {
                display: table;
                width: 100%;
                margin-bottom: 2mm;
            }
            
            .info-label {
                display: table-cell;
                font-weight: bold;
                width: 35%;
                color: #555;
                font-size: 8pt;
            }
            
            .info-value {
                display: table-cell;
                color: #000;
                font-size: 9pt;
            }
            
            .section-title {
                font-weight: bold;
                color: #2c3e50;
                font-size: 10pt;
                border-bottom: 1px solid #bdc3c7;
                padding-bottom: 1mm;
                margin: 3mm 0 2mm 0;
            }
            
            .codes-section {
                text-align: center;
                margin: 4mm 0;
            }
            
            .barcode-img, .qr-img {
                max-width: 100%;
                height: auto;
                margin: 2mm 0;
            }
            
            .code-text {
                font-size: 8pt;
                font-family: monospace;
                color: #333;
                margin-top: 1mm;
            }
            
            .label-footer {
                position: absolute;
                bottom: 8mm;
                left: 8mm;
                right: 8mm;
                text-align: center;
                font-size: 7pt;
                color: #7f8c8d;
                border-top: 1px solid #ecf0f1;
                padding-top: 2mm;
            }
            
            .premium-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 4mm;
                margin: -8mm -8mm 4mm -8mm;
                text-align: center;
            }
            
            .premium-header .company-name {
                color: white;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            }
            
            .compact-grid {
                display: table;
                width: 100%;
            }
            
            .compact-row {
                display: table-row;
            }
            
            .compact-cell {
                display: table-cell;
                padding: 1mm;
                font-size: 8pt;
            }
            
            .badge {
                display: inline-block;
                padding: 1mm 2mm;
                background: #e8f5e9;
                color: #2e7d32;
                border-radius: 2mm;
                font-size: 7pt;
                font-weight: bold;
            }
            
            .highlight {
                background: #fff3cd;
                padding: 2mm;
                border-left: 3px solid #ffc107;
                margin: 2mm 0;
            }
        </style>';
    }

    /**
     * Generate Compact Label Template
     * Minimal design with only essential information
     *
     * @param ButcherRecord $record
     * @return string
     */
    protected function generateCompactLabel(ButcherRecord $record)
    {
        $html = '<div class="label">';
        
        // Header - Always show company name
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-header">
                <div class="company-name">ULITS E-TAG</div>
            </div>';
        }
        
        // Record ID and Cut Type
        $html .= '<div class="highlight">
            <strong style="font-size: 11pt;">' . htmlspecialchars($record->cut_type) . '</strong>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<br><span style="font-size: 9pt;">' . htmlspecialchars($record->prime_cut_type) . '</span>';
        }
        if (!empty($record->offal_cut_type)) {
            $html .= '<br><span style="font-size: 9pt;">' . htmlspecialchars($record->offal_cut_type) . '</span>';
        }
        
        $html .= '</div>';
        
        // Essential Info
        $html .= '<div class="compact-grid">';
        $html .= '<div class="compact-row">
            <div class="compact-cell"><strong>Weight:</strong></div>
            <div class="compact-cell">' . number_format($record->original_weight, 2) . ' kg</div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="compact-row">
                <div class="compact-cell"><strong>Price:</strong></div>
                <div class="compact-cell">UGX ' . number_format($record->price) . '</div>
            </div>';
        }
        
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<div class="compact-row">
                <div class="compact-cell"><strong>Breed:</strong></div>
                <div class="compact-cell">' . htmlspecialchars($record->animal->breed ?? 'N/A') . '</div>
            </div>';
        }
        
        $html .= '</div>';
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $html .= '<img src="' . $record->bar_code . '" class="barcode-img" style="height: 15mm;" alt="Barcode">';
            $html .= '<div class="code-text">' . htmlspecialchars($record->id) . '</div>';
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $html .= '<img src="' . $record->qr_code . '" class="qr-img" style="width: 20mm; height: 20mm;" alt="QR Code">';
        }
        
        $html .= '</div>';
        
        // Footer
        $html .= '<div class="label-footer">
            Printed: ' . date('d/m/Y H:i') . '
        </div>';
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate Standard Label Template
     * Balanced design with all key details
     *
     * @param ButcherRecord $record
     * @return string
     */
    protected function generateStandardLabel(ButcherRecord $record)
    {
        $html = '<div class="label">';
        
        // Header
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-header">
                <div class="company-name">ULITS E-TAG SYSTEM</div>
                <div class="company-tagline">Quality Meat Traceability</div>
            </div>';
        }
        
        // Meat Information Section
        $html .= '<div class="section-title">MEAT INFORMATION</div>';
        $html .= '<div class="info-row">
            <div class="info-label">Cut Type:</div>
            <div class="info-value"><strong>' . htmlspecialchars($record->cut_type) . '</strong></div>
        </div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">Prime Cut:</div>
                <div class="info-value">' . htmlspecialchars($record->prime_cut_type) . '</div>
            </div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">Offal Cut:</div>
                <div class="info-value">' . htmlspecialchars($record->offal_cut_type) . '</div>
            </div>';
        }
        
        $html .= '<div class="info-row">
            <div class="info-label">Weight:</div>
            <div class="info-value"><strong>' . number_format($record->original_weight, 2) . ' kg</strong></div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="info-row">
                <div class="info-label">Price:</div>
                <div class="info-value">UGX ' . number_format($record->price) . '</div>
            </div>';
        }
        
        // Animal Information (if enabled)
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<div class="section-title">ANIMAL DETAILS</div>';
            
            if (!empty($record->animal->breed)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Breed:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->breed) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Animal ID:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->v_id) . '</div>
                </div>';
            }
        }
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $html .= '<img src="' . $record->bar_code . '" class="barcode-img" style="height: 18mm;" alt="Barcode">';
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $html .= '<img src="' . $record->qr_code . '" class="qr-img" style="width: 25mm; height: 25mm;" alt="QR Code">';
        }
        
        $html .= '</div>';
        
        // Footer
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer">
                ULITS E-TAG | Printed: ' . date('d/m/Y H:i') . '
            </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate Detailed Label Template
     * Full information including complete animal history
     *
     * @param ButcherRecord $record
     * @return string
     */
    protected function generateDetailedLabel(ButcherRecord $record)
    {
        $html = '<div class="label">';
        
        // Header
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-header">
                <div class="company-name">ULITS E-TAG SYSTEM</div>
                <div class="company-tagline">Complete Meat Traceability & Quality Assurance</div>
            </div>';
        }
        
        // Record Status Badge
        $statusBadge = $record->is_sold === 'Yes' ? 
            '<span class="badge" style="background: #ffebee; color: #c62828;">SOLD</span>' : 
            '<span class="badge">AVAILABLE</span>';
        $html .= '<div style="text-align: right; margin-bottom: 3mm;">' . $statusBadge . '</div>';
        
        // Meat Information
        $html .= '<div class="section-title">MEAT CLASSIFICATION</div>';
        $html .= '<div class="info-row">
            <div class="info-label">Cut Type:</div>
            <div class="info-value"><strong>' . htmlspecialchars($record->cut_type) . '</strong></div>
        </div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">Prime Cut:</div>
                <div class="info-value">' . htmlspecialchars($record->prime_cut_type) . '</div>
            </div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">Offal Cut:</div>
                <div class="info-value">' . htmlspecialchars($record->offal_cut_type) . '</div>
            </div>';
        }
        
        // Weight and Price
        $html .= '<div class="section-title">WEIGHT & PRICING</div>';
        $html .= '<div class="info-row">
            <div class="info-label">Original Weight:</div>
            <div class="info-value"><strong>' . number_format($record->original_weight, 2) . ' kg</strong></div>
        </div>';
        
        $html .= '<div class="info-row">
            <div class="info-label">Current Weight:</div>
            <div class="info-value">' . number_format($record->current_weight, 2) . ' kg</div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="info-row">
                <div class="info-label">Price:</div>
                <div class="info-value">UGX ' . number_format($record->price) . '</div>
            </div>';
        }
        
        // Animal Information (if enabled)
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<div class="section-title">SOURCE ANIMAL</div>';
            
            if (!empty($record->animal->breed)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Breed:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->breed) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Animal ID:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->v_id) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->sex)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Sex:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->sex) . '</div>
                </div>';
            }
        }
        
        // Source Information
        if (!empty($record->source_name)) {
            $html .= '<div class="section-title">SUPPLIER</div>';
            $html .= '<div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">' . htmlspecialchars($record->source_name) . '</div>
            </div>';
            
            if (!empty($record->source_phone)) {
                $html .= '<div class="info-row">
                    <div class="info-label">Contact:</div>
                    <div class="info-value">' . htmlspecialchars($record->source_phone) . '</div>
                </div>';
            }
        }
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $html .= '<img src="' . $record->bar_code . '" class="barcode-img" style="height: 15mm;" alt="Barcode">';
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $html .= '<img src="' . $record->qr_code . '" class="qr-img" style="width: 22mm; height: 22mm;" alt="QR Code">';
        }
        
        $html .= '</div>';
        
        // Footer
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer">
                ULITS E-TAG System | Contact: +256-XXX-XXXXXX<br>
                Printed: ' . date('d M Y, H:i') . ' | ID: #' . $record->id . '
            </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate Premium Label Template
     * Branded design with professional styling
     *
     * @param ButcherRecord $record
     * @return string
     */
    protected function generatePremiumLabel(ButcherRecord $record)
    {
        $html = '<div class="label">';
        
        // Premium Header
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="premium-header">
                <div class="company-name">🥩 ULITS E-TAG</div>
                <div style="font-size: 9pt; margin-top: 1mm;">Premium Meat Traceability System</div>
            </div>';
        }
        
        // Featured Product Card
        $html .= '<div class="highlight" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-left: 4px solid #667eea;">
            <div style="font-size: 12pt; font-weight: bold; color: #2c3e50; margin-bottom: 1mm;">' . htmlspecialchars($record->cut_type) . '</div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div style="font-size: 9pt; color: #34495e;">' . htmlspecialchars($record->prime_cut_type) . '</div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div style="font-size: 9pt; color: #34495e;">' . htmlspecialchars($record->offal_cut_type) . '</div>';
        }
        
        $html .= '</div>';
        
        // Premium Info Grid
        $html .= '<div style="background: #f8f9fa; padding: 3mm; margin: 3mm 0; border-radius: 2mm;">';
        
        $html .= '<div class="info-row" style="margin-bottom: 2mm;">
            <div class="info-label" style="color: #667eea;">💎 Weight:</div>
            <div class="info-value"><strong>' . number_format($record->original_weight, 2) . ' kg</strong></div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="info-row" style="margin-bottom: 2mm;">
                <div class="info-label" style="color: #667eea;">💰 Price:</div>
                <div class="info-value"><strong>UGX ' . number_format($record->price) . '</strong></div>
            </div>';
        }
        
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            if (!empty($record->animal->breed)) {
                $html .= '<div class="info-row" style="margin-bottom: 2mm;">
                    <div class="info-label" style="color: #667eea;">🐄 Breed:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->breed) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<div class="info-row">
                    <div class="info-label" style="color: #667eea;">🏷️ Animal ID:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->v_id) . '</div>
                </div>';
            }
        }
        
        $html .= '</div>';
        
        // Quality Assurance Badge
        $html .= '<div style="text-align: center; margin: 3mm 0;">
            <div style="display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 2mm 4mm; border-radius: 3mm; font-size: 8pt; font-weight: bold;">
                ✓ QUALITY ASSURED
            </div>
        </div>';
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $html .= '<div style="background: white; display: inline-block; padding: 2mm; border-radius: 2mm; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <img src="' . $record->qr_code . '" class="qr-img" style="width: 28mm; height: 28mm;" alt="QR Code">
            </div>';
        }
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $html .= '<div style="margin-top: 2mm;">
                <img src="' . $record->bar_code . '" class="barcode-img" style="height: 16mm;" alt="Barcode">
            </div>';
        }
        
        $html .= '</div>';
        
        // Premium Footer
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin: 4mm -8mm -8mm -8mm; padding: 3mm; border-top: none;">
                <strong>ULITS E-TAG SYSTEM</strong><br>
                📞 +256-XXX-XXXXXX | 🌐 www.ulits-etag.com<br>
                <span style="font-size: 6pt;">Printed: ' . date('d/m/Y H:i') . '</span>
            </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Generate single label for reprint
     *
     * @param ButcherRecord $record
     * @param string $templateType
     * @param array $options
     * @return array
     */
    public static function generateSingleLabel(ButcherRecord $record, $templateType = 'Standard', $options = [])
    {
        try {
            // Create temporary task
            $task = new LabelPrintingTask([
                'task_number' => 'REPRINT-' . $record->id . '-' . time(),
                'butcher_record_ids' => [$record->id],
                'template_type' => $templateType,
                'include_qr' => $options['include_qr'] ?? 'Yes',
                'include_barcode' => $options['include_barcode'] ?? 'Yes',
                'include_company_info' => $options['include_company_info'] ?? 'Yes',
                'include_animal_info' => $options['include_animal_info'] ?? 'Yes',
                'total_labels' => 1,
                'status' => 'Processing'
            ]);

            $generator = new self($task);
            $result = $generator->generate();

            return $result;

        } catch (\Exception $e) {
            Log::error('Single Label Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
