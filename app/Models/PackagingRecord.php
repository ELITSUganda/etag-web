<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use Carbon\Carbon;

class PackagingRecord extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'slaughter_record_id', 
        'animal_id', 
        'slaughter_distribution_record_id',
        'v_id', 
        'e_id', 
        'lhc', 
        'breed', 
        'sex',
        'barcode', 
        'qr_code_link',
        'package_type',
        // Prime cuts
        'beef_boneless', 'beef_stew', 'bones', 'brisket', 'chops', 'chuck_ribs',
        'family_steak', 'fore_rib', 'leg_cut', 'middle_rib', 'minced_meat', 'neck',
        'ossubucco', 'oxtail', 'ribs', 'shin', 'staff_meat', 'thick_flank',
        'fillet', 'rib_eye', 'rolled_loin', 'rump', 'silver_side', 'sirloin_striploin',
        't_bone', 'topside_beef_roast', 'veal_steak',
        // Offals
        'heart', 'kidneys', 'liver', 'tongue', 'lungs', 'tripe',
        'tail', 'head', 'feet', 'testicles',
        // Other fields
        'total_weight', 
        'packaging_date', 
        'expiry_date',
        'packaged_by', 
        'pdf_generated', 'pdf_file_path',
        'notes', 
        'status'
    ];
    
    protected $casts = [
        'packaging_date' => 'date',
        'expiry_date' => 'date',
        'total_weight' => 'decimal:2',
    ];
    
    protected $appends = ['package_code', 'is_expired'];
    
    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-calculate expiry date and total weight before saving
        static::saving(function ($record) {
            // Calculate total weight
            $record->total_weight = $record->calculateTotalWeight();
            
            // Auto-update status based on expiry
            if ($record->expiry_date && Carbon::parse($record->expiry_date)->isPast() && $record->status === 'Active') {
                $record->status = 'Expired';
            }
        });
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the slaughter record associated with this packaging record
     */
    public function slaughterRecord()
    {
        return $this->belongsTo(SlaughterRecord::class);
    }
    
    /**
     * Get the animal associated with this packaging record
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
    
    /**
     * Get the distribution record associated with this packaging record
     */
    public function distributionRecord()
    {
        return $this->belongsTo(SlaughterDistributionRecord::class, 'slaughter_distribution_record_id');
    }
    
    /**
     * Get the user who packaged this record
     */
    public function packager()
    {
        return $this->belongsTo(Administrator::class, 'packaged_by');
    }
    
    // ==================== ACCESSORS ====================
    
    /**
     * Get the package code attribute
     */
    public function getPackageCodeAttribute()
    {
        return 'PKG-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get the PDF URL attribute
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_file_path && file_exists(public_path($this->pdf_file_path))) {
            return url($this->pdf_file_path);
        }
        return null;
    }
    
    /**
     * Check if package is expired
     */
    public function getIsExpiredAttribute()
    {
        return Carbon::parse($this->expiry_date)->isPast();
    }
    
    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        $now = Carbon::now();
        $expiry = Carbon::parse($this->expiry_date);
        
        if ($expiry->isPast()) {
            return 0;
        }
        
        return $now->diffInDays($expiry);
    }
    
    // ==================== BUSINESS LOGIC METHODS ====================
    
    /**
     * Calculate total weight based on package type
     */
    public function calculateTotalWeight()
    {
        $total = 0;
        
        if ($this->package_type === 'Prime Cut') {
            $primeCuts = $this->getPrimeCutFields();
            foreach ($primeCuts as $field) {
                $total += (float) ($this->$field ?? 0);
            }
        } else {
            $offalFields = $this->getOffalFields();
            foreach ($offalFields as $field) {
                $total += (float) ($this->$field ?? 0);
            }
        }
        
        return round($total, 2);
    }
    
    /**
     * Get breakdown of cuts with non-zero weights
     */
    public function getCutBreakdown()
    {
        $breakdown = [];
        
        if ($this->package_type === 'Prime Cut') {
            $cuts = $this->getPrimeCutLabels();
        } else {
            $cuts = $this->getOffalLabels();
        }
        
        foreach ($cuts as $label => $field) {
            $value = (float) ($this->$field ?? 0);
            if ($value > 0) {
                $breakdown[] = [
                    'label' => $label,
                    'field' => $field,
                    'weight' => $value
                ];
            }
        }
        
        return $breakdown;
    }
    
    /**
     * Get array of prime cut field names
     */
    public static function getPrimeCutFields()
    {
        return [
            'beef_boneless', 'beef_stew', 'bones', 'brisket', 'chops', 'chuck_ribs',
            'family_steak', 'fore_rib', 'leg_cut', 'middle_rib', 'minced_meat', 'neck',
            'ossubucco', 'oxtail', 'ribs', 'shin', 'staff_meat', 'thick_flank',
            'fillet', 'rib_eye', 'rolled_loin', 'rump', 'silver_side', 'sirloin_striploin',
            't_bone', 'topside_beef_roast', 'veal_steak'
        ];
    }
    
    /**
     * Get array of offal field names
     */
    public static function getOffalFields()
    {
        return [
            'heart', 'kidneys', 'liver', 'tongue', 'lungs', 'tripe',
            'tail', 'head', 'feet', 'testicles'
        ];
    }
    
    /**
     * Get prime cut labels mapped to field names
     */
    public static function getPrimeCutLabels()
    {
        return [
            'Beef Boneless' => 'beef_boneless',
            'Beef Stew' => 'beef_stew',
            'Bones' => 'bones',
            'Brisket' => 'brisket',
            'Chops' => 'chops',
            'Chuck Ribs' => 'chuck_ribs',
            'Family Steak' => 'family_steak',
            'Fore Rib' => 'fore_rib',
            'Leg Cut' => 'leg_cut',
            'Middle Rib' => 'middle_rib',
            'Minced Meat' => 'minced_meat',
            'Neck' => 'neck',
            'Ossubucco' => 'ossubucco',
            'Oxtail' => 'oxtail',
            'Ribs' => 'ribs',
            'Shin' => 'shin',
            'Staff Meat' => 'staff_meat',
            'Thick Flank' => 'thick_flank',
            'Fillet' => 'fillet',
            'Rib Eye' => 'rib_eye',
            'Rolled Loin' => 'rolled_loin',
            'Rump' => 'rump',
            'Silver Side' => 'silver_side',
            'Sirloin/Striploin' => 'sirloin_striploin',
            'T-Bone' => 't_bone',
            'Topside/Beef Roast' => 'topside_beef_roast',
            'Veal Steak' => 'veal_steak',
        ];
    }
    
    /**
     * Get offal labels mapped to field names
     */
    public static function getOffalLabels()
    {
        return [
            'Heart' => 'heart',
            'Kidneys' => 'kidneys',
            'Liver' => 'liver',
            'Tongue' => 'tongue',
            'Lungs' => 'lungs',
            'Tripe' => 'tripe',
            'Tail' => 'tail',
            'Head' => 'head',
            'Feet' => 'feet',
            'Testicles' => 'testicles',
        ];
    }
    
    /**
     * Check if package has any weights entered
     */
    public function hasWeights()
    {
        return $this->total_weight > 0;
    }
    
    /**
     * Validate that at least one weight is entered based on package type
     */
    public function validateWeights()
    {
        if ($this->package_type === 'Prime Cut') {
            $fields = $this->getPrimeCutFields();
        } else {
            $fields = $this->getOffalFields();
        }
        
        foreach ($fields as $field) {
            if ((float) ($this->$field ?? 0) > 0) {
                return true;
            }
        }
        
        return false;
    }
}
