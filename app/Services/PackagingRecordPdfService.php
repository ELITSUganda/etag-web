<?php

namespace App\Services;

use App\Models\PackagingRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class PackagingRecordPdfService
{
    /**
     * Generate PDF label for packaging record
     *
     * @param PackagingRecord $record
     * @return string Path to generated PDF
     */
    public function generateLabel(PackagingRecord $record)
    {
        try {
            // Generate QR code as base64 image
            $qrCodeData = $this->generateQrCode($record);
            
            // Generate barcode as base64 image
            $barcodeData = $this->generateBarcode($record);
            
            // Prepare data for PDF
            $data = [
                'record' => $record,
                'slaughter' => $record->slaughterRecord,
                'packager' => $record->packager,
                'cutBreakdown' => $record->getCutBreakdown(),
                'qrCode' => $qrCodeData,
                'barcode' => $barcodeData,
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.packaging-label', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);
            
            // Create directory if it doesn't exist
            $directory = public_path('storage/images');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save PDF
            $filename = $record->package_code . '.pdf';
            $path = 'storage/images/' . $filename;
            $fullPath = public_path($path);
            
            $pdf->save($fullPath);
            
            // Update record
            $record->update([
                'pdf_generated' => 'Yes',
                'pdf_file_path' => $path
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error for PackagingRecord #' . $record->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate QR code as base64 image
     *
     * @param PackagingRecord $record
     * @return string
     */
    protected function generateQrCode(PackagingRecord $record)
    {
        try {
            $qrData = $record->qr_code_link ?? url("/api/packaging-records/{$record->id}");
            
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($qrData);
            
            return 'data:image/png;base64,' . base64_encode($qrCode);
            
        } catch (\Exception $e) {
            \Log::error('QR Code Generation Error: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Generate barcode as base64 image
     *
     * @param PackagingRecord $record
     * @return string
     */
    protected function generateBarcode(PackagingRecord $record)
    {
        try {
            $barcodeText = $record->barcode ?? $record->package_code;
            
            $barcode = \DNS1D::getBarcodePNG($barcodeText, 'C128', 2, 60);
            
            return 'data:image/png;base64,' . $barcode;
            
        } catch (\Exception $e) {
            \Log::error('Barcode Generation Error: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Regenerate PDF label for existing packaging record
     *
     * @param PackagingRecord $record
     * @return string Path to generated PDF
     */
    public function regenerateLabel(PackagingRecord $record)
    {
        // Delete old PDF if exists
        if ($record->pdf_file_path && file_exists(public_path($record->pdf_file_path))) {
            unlink(public_path($record->pdf_file_path));
        }
        
        // Generate new PDF
        return $this->generateLabel($record);
    }
}
