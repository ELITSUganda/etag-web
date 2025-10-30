<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;

/**
 * LabelPrintingTask Model
 * 
 * Manages label generation tasks for butcher records.
 * Tracks the progress of PDF generation, template used, and configuration options.
 * 
 * @property int $id
 * @property string $task_number Unique task identifier (e.g., LPT-20251030-001)
 * @property array $butcher_record_ids Array of ButcherRecord IDs to generate labels for
 * @property string $template_type Template design: Compact, Standard, Detailed, Premium
 * @property string $include_qr Include QR code: Yes/No
 * @property string $include_barcode Include barcode: Yes/No
 * @property string $include_company_info Include company info: Yes/No
 * @property string $include_animal_info Include animal details: Yes/No
 * @property string $label_size Label dimensions (default: A6)
 * @property int $labels_per_page Labels per page count
 * @property int $total_labels Total labels to generate
 * @property int $generated_labels Labels generated so far
 * @property string $status Pending, Processing, Completed, Failed
 * @property string $pdf_path Storage path for generated PDF
 * @property string $pdf_size File size in human readable format
 * @property string $error_message Error message if failed
 * @property \Carbon\Carbon $started_at Generation start timestamp
 * @property \Carbon\Carbon $completed_at Generation completion timestamp
 * @property int $created_by_id Administrator who created the task
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class LabelPrintingTask extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'label_printing_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_number',
        'butcher_record_ids',
        'template_type',
        'include_qr',
        'include_barcode',
        'include_company_info',
        'include_animal_info',
        'label_size',
        'labels_per_page',
        'total_labels',
        'generated_labels',
        'status',
        'pdf_path',
        'pdf_size',
        'error_message',
        'started_at',
        'completed_at',
        'created_by_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'butcher_record_ids' => 'array',
        'total_labels' => 'integer',
        'generated_labels' => 'integer',
        'labels_per_page' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the administrator who created this task.
     */
    public function creator()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    /**
     * Get the butcher records associated with this task.
     */
    public function butcherRecords()
    {
        if (empty($this->butcher_record_ids)) {
            return collect([]);
        }
        return ButcherRecord::whereIn('id', $this->butcher_record_ids)->get();
    }

    /**
     * Generate unique task number.
     * Format: LPT-YYYYMMDD-XXX
     * 
     * @return string
     */
    public static function generateTaskNumber()
    {
        $date = date('Ymd');
        $prefix = "LPT-{$date}-";
        
        // Get last task number for today
        $lastTask = self::where('task_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTask) {
            // Extract number and increment
            $lastNumber = intval(substr($lastTask->task_number, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get progress percentage.
     * 
     * @return float
     */
    public function getProgressPercentage()
    {
        if ($this->total_labels == 0) {
            return 0;
        }
        return round(($this->generated_labels / $this->total_labels) * 100, 2);
    }

    /**
     * Check if task is completed.
     * 
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if task is processing.
     * 
     * @return bool
     */
    public function isProcessing()
    {
        return $this->status === 'Processing';
    }

    /**
     * Check if task has failed.
     * 
     * @return bool
     */
    public function hasFailed()
    {
        return $this->status === 'Failed';
    }

    /**
     * Mark task as started.
     * 
     * @return void
     */
    public function markAsStarted()
    {
        $this->status = 'Processing';
        $this->started_at = now();
        $this->save();
    }

    /**
     * Mark task as completed.
     * 
     * @param string $pdfPath
     * @param string $pdfSize
     * @return void
     */
    public function markAsCompleted($pdfPath, $pdfSize)
    {
        $this->status = 'Completed';
        $this->completed_at = now();
        $this->pdf_path = $pdfPath;
        $this->pdf_size = $pdfSize;
        $this->generated_labels = $this->total_labels;
        $this->save();
    }

    /**
     * Mark task as failed.
     * 
     * @param string $errorMessage
     * @return void
     */
    public function markAsFailed($errorMessage)
    {
        $this->status = 'Failed';
        $this->completed_at = now();
        $this->error_message = $errorMessage;
        $this->save();
    }

    /**
     * Update generation progress.
     * 
     * @param int $generatedCount
     * @return void
     */
    public function updateProgress($generatedCount)
    {
        $this->generated_labels = $generatedCount;
        $this->save();
    }

    /**
     * Get available template types.
     * 
     * @return array
     */
    public static function getTemplateTypes()
    {
        return [
            'Compact' => 'Compact - Minimal design with essential info only',
            'Standard' => 'Standard - Balanced design with all key details',
            'Detailed' => 'Detailed - Full information including animal history',
            'Premium' => 'Premium - Branded design with company logo and styling',
        ];
    }

    /**
     * Get full PDF URL.
     * 
     * @return string|null
     */
    public function getPdfUrl()
    {
        if (empty($this->pdf_path)) {
            return null;
        }
        return url('storage/' . $this->pdf_path);
    }
}
