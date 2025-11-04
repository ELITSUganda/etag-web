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
            
            // Set paper size to A6 for optimal label printing
            $pdf->setPaper([0, 0, 419.53, 297.64], 'portrait'); // A6 in points (105x148mm)
            
            // Set options for better rendering
            // Note: Images are embedded as base64 data URIs for reliable rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false, // Not needed - using base64 embedded images
                'defaultFont' => 'DejaVu Sans',
                'enable_php' => false,
                'chroot' => public_path(), // Restrict file access to public directory
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
                font-size: 10pt;
                line-height: 1.4;
                color: #2d2d2d;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            .label {
                width: 105mm;
                height: 148mm;
                padding: 10mm;
                border: 2px solid #693A01;
                page-break-inside: avoid;
                position: relative;
                background: white;
            }
            
            .label-header {
                text-align: center;
                border-bottom: 3px solid #693A01;
                padding-bottom: 5mm;
                margin-bottom: 5mm;
                background: #F5EDEC;
                margin: -10mm -10mm 6mm -10mm;
                padding: 8mm 10mm 5mm 10mm;
            }
            
            .company-name {
                font-size: 16pt;
                font-weight: bold;
                color: #693A01;
                letter-spacing: 1pt;
                text-transform: uppercase;
                margin-bottom: 1mm;
            }
            
            .company-tagline {
                font-size: 9pt;
                color: #693A01;
                font-weight: 600;
                letter-spacing: 0.5pt;
            }
            
            .label-body {
                margin-bottom: 5mm;
            }
            
            .info-row {
                display: table;
                width: 100%;
                margin-bottom: 2.5mm;
                border-bottom: 1px solid #E8E8E8;
                padding-bottom: 2mm;
            }
            
            .info-label {
                display: table-cell;
                font-weight: 700;
                width: 40%;
                color: #693A01;
                font-size: 9pt;
                vertical-align: top;
                padding-right: 3mm;
            }
            
            .info-value {
                display: table-cell;
                color: #2d2d2d;
                font-size: 10pt;
                vertical-align: top;
                font-weight: 500;
            }
            
            .section-title {
                font-weight: bold;
                color: white;
                background: #693A01;
                font-size: 11pt;
                padding: 2mm 3mm;
                margin: 5mm -10mm 4mm -10mm;
                text-transform: uppercase;
                letter-spacing: 0.5pt;
            }
            
            .section-divider {
                border-top: 2px solid #693A01;
                margin: 4mm 0;
            }
            
            .codes-section {
                text-align: center;
                margin: 5mm 0;
                padding: 4mm;
                background: #F5EDEC;
                border: 1px solid #693A01;
            }
            
            .barcode-img, .qr-img {
                max-width: 100%;
                height: auto;
                margin: 2mm auto;
                display: block;
            }
            
            .code-text {
                font-size: 9pt;
                font-family: "Courier New", monospace;
                color: #693A01;
                margin-top: 2mm;
                font-weight: bold;
                letter-spacing: 1pt;
            }
            
            .label-footer {
                position: absolute;
                bottom: 8mm;
                left: 10mm;
                right: 10mm;
                text-align: center;
                font-size: 8pt;
                color: #693A01;
                border-top: 2px solid #693A01;
                padding-top: 2mm;
                font-weight: 600;
            }
            
            .premium-border {
                border: 3px double #693A01;
                padding: 2mm;
                margin: 3mm 0;
            }
            
            .badge {
                display: inline-block;
                padding: 1.5mm 3mm;
                background: #693A01;
                color: white;
                font-size: 8pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5pt;
            }
            
            .highlight-box {
                background: #F5EDEC;
                padding: 3mm;
                border-left: 4px solid #693A01;
                margin: 3mm 0;
            }
            
            .data-grid {
                width: 100%;
                border-collapse: collapse;
                margin: 3mm 0;
            }
            
            .data-grid td {
                padding: 2mm;
                border: 1px solid #693A01;
                font-size: 9pt;
            }
            
            .data-grid td:first-child {
                font-weight: bold;
                background: #F5EDEC;
                color: #693A01;
                width: 40%;
            }
            
            .separator-line {
                border: 0;
                height: 2px;
                background: #693A01;
                margin: 4mm 0;
            }
        </style>';
    }

    /**
     * Convert image path to base64 data URI for DomPDF
     * DomPDF requires base64-encoded images for proper rendering
     *
     * @param string|null $imagePath
     * @return string|null
     */
    protected function getImageDataUri($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        // Clean the path - remove leading slashes and /storage prefix
        $cleanPath = ltrim($imagePath, '/');
        $cleanPath = preg_replace('#^storage/#', '', $cleanPath);
        
        // Try multiple possible locations for the image file
        $possiblePaths = [
            public_path($cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path('app/public/' . $cleanPath),
            base_path($imagePath), // Try as relative from base
        ];

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath)) {
                try {
                    $imageData = file_get_contents($fullPath);
                    $mimeType = mime_content_type($fullPath);
                    $base64 = base64_encode($imageData);
                    return 'data:' . $mimeType . ';base64,' . $base64;
                } catch (\Exception $e) {
                    Log::warning('Failed to encode image: ' . $fullPath . ' - ' . $e->getMessage());
                    continue;
                }
            }
        }

        // If file not found, log warning
        Log::warning('Image file not found for PDF: ' . $imagePath . ' - Checked paths: ' . implode(', ', $possiblePaths));
        return null;
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
                <div class="company-tagline">LIVESTOCK IDENTIFICATION</div>
            </div>';
        }
        
        // Record ID and Cut Type
        $html .= '<div class="highlight-box">';
        $html .= '<div class="info-row">
            <div class="info-label">CUT TYPE:</div>
            <div class="info-value">' . htmlspecialchars($record->cut_type) . '</div>
        </div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">PRIME CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->prime_cut_type) . '</div>
            </div>';
        }
        if (!empty($record->offal_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">OFFAL CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->offal_cut_type) . '</div>
            </div>';
        }
        
        $html .= '</div>';
        
        // Essential Info
        $html .= '<table class="data-grid">';
        $html .= '<tr>
            <td>WEIGHT</td>
            <td>' . number_format($record->original_weight, 2) . ' KG</td>
        </tr>';
        
        if (!empty($record->price)) {
            $html .= '<tr>
                <td>PRICE</td>
                <td>UGX ' . number_format($record->price) . '</td>
            </tr>';
        }
        
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<tr>
                <td>BREED</td>
                <td>' . htmlspecialchars($record->animal->breed ?? 'N/A') . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $barcodeDataUri = $this->getImageDataUri($record->bar_code);
            if ($barcodeDataUri) {
                $html .= '<img src="' . $barcodeDataUri . '" class="barcode-img" style="height: 15mm;" alt="Barcode">';
                $html .= '<div class="code-text">ID: ' . htmlspecialchars($record->id) . '</div>';
            }
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $qrDataUri = $this->getImageDataUri($record->qr_code);
            if ($qrDataUri) {
                $html .= '<img src="' . $qrDataUri . '" class="qr-img" style="width: 20mm; height: 20mm;" alt="QR Code">';
            }
        }
        
        $html .= '</div>';
        
        // Footer
        $html .= '<div class="label-footer">
            PRINTED: ' . strtoupper(date('d/M/Y H:i')) . '
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
                <div class="company-tagline">QUALITY MEAT TRACEABILITY</div>
            </div>';
        }
        
        // Meat Information Section
        $html .= '<div class="section-title">MEAT INFORMATION</div>';
        $html .= '<div class="info-row">
            <div class="info-label">CUT TYPE:</div>
            <div class="info-value">' . htmlspecialchars($record->cut_type) . '</div>
        </div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">PRIME CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->prime_cut_type) . '</div>
            </div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">OFFAL CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->offal_cut_type) . '</div>
            </div>';
        }
        
        $html .= '<div class="separator-line"></div>';
        
        $html .= '<div class="info-row">
            <div class="info-label">WEIGHT:</div>
            <div class="info-value">' . number_format($record->original_weight, 2) . ' KG</div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="info-row">
                <div class="info-label">PRICE:</div>
                <div class="info-value">UGX ' . number_format($record->price) . '</div>
            </div>';
        }
        
        // Animal Information (if enabled)
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<div class="section-title">ANIMAL DETAILS</div>';
            
            if (!empty($record->animal->breed)) {
                $html .= '<div class="info-row">
                    <div class="info-label">BREED:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->breed) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<div class="info-row">
                    <div class="info-label">ANIMAL ID:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->v_id) . '</div>
                </div>';
            }
        }
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $barcodeDataUri = $this->getImageDataUri($record->bar_code);
            if ($barcodeDataUri) {
                $html .= '<img src="' . $barcodeDataUri . '" class="barcode-img" style="height: 18mm;" alt="Barcode">';
                $html .= '<div class="code-text">RECORD ID: ' . htmlspecialchars($record->id) . '</div>';
            }
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $qrDataUri = $this->getImageDataUri($record->qr_code);
            if ($qrDataUri) {
                $html .= '<div style="margin-top: 3mm;"></div>';
                $html .= '<img src="' . $qrDataUri . '" class="qr-img" style="width: 25mm; height: 25mm;" alt="QR Code">';
                $html .= '<div class="code-text" style="margin-top: 1mm;">SCAN FOR FULL DETAILS</div>';
            }
        }
        
        $html .= '</div>';
        
        // Footer
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer">
                ULITS E-TAG SYSTEM | PRINTED: ' . strtoupper(date('d/M/Y H:i')) . '
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
            '<span class="badge" style="background: #321C00;">STATUS: SOLD</span>' : 
            '<span class="badge">STATUS: AVAILABLE</span>';
        $html .= '<div style="text-align: right; margin-bottom: 3mm;">' . $statusBadge . '</div>';
        
        // Meat Information
        $html .= '<div class="section-title">MEAT CLASSIFICATION</div>';
        $html .= '<div class="info-row">
            <div class="info-label">CUT TYPE:</div>
            <div class="info-value">' . htmlspecialchars($record->cut_type) . '</div>
        </div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">PRIME CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->prime_cut_type) . '</div>
            </div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div class="info-row">
                <div class="info-label">OFFAL CUT:</div>
                <div class="info-value">' . htmlspecialchars($record->offal_cut_type) . '</div>
            </div>';
        }
        
        // Weight and Price
        $html .= '<div class="section-title">WEIGHT & PRICING</div>';
        $html .= '<div class="info-row">
            <div class="info-label">ORIGINAL WEIGHT:</div>
            <div class="info-value">' . number_format($record->original_weight, 2) . ' KG</div>
        </div>';
        
        $html .= '<div class="info-row">
            <div class="info-label">CURRENT WEIGHT:</div>
            <div class="info-value">' . number_format($record->current_weight, 2) . ' KG</div>
        </div>';
        
        if (!empty($record->price)) {
            $html .= '<div class="info-row">
                <div class="info-label">PRICE:</div>
                <div class="info-value">UGX ' . number_format($record->price) . '</div>
            </div>';
        }
        
        // Animal Information (if enabled)
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            $html .= '<div class="section-title">SOURCE ANIMAL</div>';
            
            if (!empty($record->animal->breed)) {
                $html .= '<div class="info-row">
                    <div class="info-label">BREED:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->breed) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<div class="info-row">
                    <div class="info-label">ANIMAL ID:</div>
                    <div class="info-value">' . htmlspecialchars($record->animal->v_id) . '</div>
                </div>';
            }
            
            if (!empty($record->animal->sex)) {
                $html .= '<div class="info-row">
                    <div class="info-label">SEX:</div>
                    <div class="info-value">' . strtoupper(htmlspecialchars($record->animal->sex)) . '</div>
                </div>';
            }
        }
        
        // Source Information
        if (!empty($record->source_name)) {
            $html .= '<div class="section-title">SUPPLIER INFORMATION</div>';
            $html .= '<div class="info-row">
                <div class="info-label">SUPPLIER NAME:</div>
                <div class="info-value">' . htmlspecialchars($record->source_name) . '</div>
            </div>';
            
            if (!empty($record->source_phone)) {
                $html .= '<div class="info-row">
                    <div class="info-label">CONTACT:</div>
                    <div class="info-value">' . htmlspecialchars($record->source_phone) . '</div>
                </div>';
            }
        }
        
        // Codes Section
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $barcodeDataUri = $this->getImageDataUri($record->bar_code);
            if ($barcodeDataUri) {
                $html .= '<img src="' . $barcodeDataUri . '" class="barcode-img" style="height: 15mm;" alt="Barcode">';
                $html .= '<div class="code-text">RECORD: ' . htmlspecialchars($record->id) . '</div>';
            }
        }
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $qrDataUri = $this->getImageDataUri($record->qr_code);
            if ($qrDataUri) {
                $html .= '<div style="margin-top: 3mm;"></div>';
                $html .= '<img src="' . $qrDataUri . '" class="qr-img" style="width: 22mm; height: 22mm;" alt="QR Code">';
                $html .= '<div class="code-text" style="margin-top: 1mm;">SCAN FOR FULL TRACEABILITY</div>';
            }
        }
        
        $html .= '</div>';
        
        // Footer
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer">
                ULITS E-TAG SYSTEM<br>
                PRINTED: ' . strtoupper(date('d M Y, H:i')) . ' | RECORD ID: ' . $record->id . '
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
        
        // Premium Header with solid brown background
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-header">
                <div class="company-name">ULITS E-TAG</div>
                <div class="company-tagline">PREMIUM MEAT TRACEABILITY</div>
            </div>';
        }
        
        // Featured Product Card with premium border
        $html .= '<div class="premium-border">';
        $html .= '<div class="highlight-box">';
        $html .= '<div style="font-size: 13pt; font-weight: bold; color: #693A01; margin-bottom: 2mm; text-align: center; text-transform: uppercase; letter-spacing: 1pt;">' . htmlspecialchars($record->cut_type) . '</div>';
        
        if (!empty($record->prime_cut_type)) {
            $html .= '<div style="font-size: 10pt; color: #693A01; text-align: center; margin-top: 1mm;">' . htmlspecialchars($record->prime_cut_type) . '</div>';
        }
        
        if (!empty($record->offal_cut_type)) {
            $html .= '<div style="font-size: 10pt; color: #693A01; text-align: center; margin-top: 1mm;">' . htmlspecialchars($record->offal_cut_type) . '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Premium Info Grid
        $html .= '<table class="data-grid" style="margin: 4mm 0;">';
        
        $html .= '<tr>
            <td>WEIGHT</td>
            <td>' . number_format($record->original_weight, 2) . ' KG</td>
        </tr>';
        
        if (!empty($record->price)) {
            $html .= '<tr>
                <td>PRICE</td>
                <td>UGX ' . number_format($record->price) . '</td>
            </tr>';
        }
        
        if ($this->task->include_animal_info === 'Yes' && $record->animal) {
            if (!empty($record->animal->breed)) {
                $html .= '<tr>
                    <td>BREED</td>
                    <td>' . htmlspecialchars($record->animal->breed) . '</td>
                </tr>';
            }
            
            if (!empty($record->animal->v_id)) {
                $html .= '<tr>
                    <td>ANIMAL ID</td>
                    <td>' . htmlspecialchars($record->animal->v_id) . '</td>
                </tr>';
            }
        }
        
        $html .= '</table>';
        
        // Quality Assurance Badge
        $html .= '<div style="text-align: center; margin: 4mm 0;">
            <span class="badge" style="font-size: 9pt; padding: 2mm 5mm; letter-spacing: 1pt;">QUALITY ASSURED</span>
        </div>';
        
        // Codes Section with premium styling
        $html .= '<div class="codes-section">';
        
        if ($this->task->include_qr === 'Yes' && !empty($record->qr_code)) {
            $qrDataUri = $this->getImageDataUri($record->qr_code);
            if ($qrDataUri) {
                $html .= '<div style="padding: 3mm; margin-bottom: 2mm;">
                    <img src="' . $qrDataUri . '" class="qr-img" style="width: 28mm; height: 28mm;" alt="QR Code">
                    <div class="code-text" style="margin-top: 2mm;">SCAN FOR COMPLETE TRACEABILITY</div>
                </div>';
            }
        }
        
        if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
            $barcodeDataUri = $this->getImageDataUri($record->bar_code);
            if ($barcodeDataUri) {
                $html .= '<div style="margin-top: 2mm;">
                    <img src="' . $barcodeDataUri . '" class="barcode-img" style="height: 16mm;" alt="Barcode">
                    <div class="code-text" style="margin-top: 1mm;">RECORD: ' . htmlspecialchars($record->id) . '</div>
                </div>';
            }
        }
        
        $html .= '</div>';
        
        // Premium Footer with solid brown background
        if ($this->task->include_company_info === 'Yes') {
            $html .= '<div class="label-footer" style="background: #693A01; color: white; margin: 4mm -10mm -10mm -10mm; padding: 4mm 10mm; border-top: none; text-align: center;">
                <strong style="font-size: 9pt; letter-spacing: 0.5pt;">ULITS E-TAG SYSTEM</strong><br>
                <span style="font-size: 8pt;">CONTACT: +256-XXX-XXXXXX | WWW.ULITS-ETAG.COM</span><br>
                <span style="font-size: 7pt; margin-top: 1mm; display: block;">PRINTED: ' . strtoupper(date('d/M/Y H:i')) . '</span>
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
