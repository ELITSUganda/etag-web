<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class PackagingExporter extends ExcelExporter
{
    protected $type;
    protected $fileName;
    
    public function __construct($type = 'fore-quarters')
    {
        $this->type = $type;
        
        $fileNames = [
            'fore-quarters' => 'Fore_Quarter_Packaging',
            'hind-quarters' => 'Hind_Quarter_Packaging',
            'offals' => 'Offals_Packaging',
        ];
        
        $this->fileName = $fileNames[$type] ?? 'Packaging';
    }

    public function export()
    {
        $this->chunk(function ($records) {
            $rows = [];
            
            foreach ($records as $record) {
                $row = [
                    'ID' => $record->id,
                    'Package Code' => $record->package_code,
                    'Barcode' => $record->barcode,
                    'E-ID' => $record->e_id,
                    'V-ID' => $record->v_id,
                    'LHC' => $record->lhc,
                    'Breed' => $record->breed,
                    'Sex' => $record->sex,
                    'Package Type' => $record->package_type,
                    'Total Weight (kg)' => $record->total_weight,
                    'Packaging Date' => $record->packaging_date,
                    'Expiry Date' => $record->expiry_date,
                    'Status' => $record->status,
                    'Packaged By' => $record->packager ? $record->packager->name : 'N/A',
                ];
                
                // Add type-specific columns
                if ($this->type === 'fore-quarters') {
                    $row['Chuck Ribs (kg)'] = $record->chuck_ribs ?? 0;
                    $row['Brisket (kg)'] = $record->brisket ?? 0;
                    $row['Fore Rib (kg)'] = $record->fore_rib ?? 0;
                    $row['Shin (kg)'] = $record->shin ?? 0;
                    $row['Neck (kg)'] = $record->neck ?? 0;
                    $row['Beef Boneless (kg)'] = $record->beef_boneless ?? 0;
                    $row['Ribs (kg)'] = $record->ribs ?? 0;
                    $row['Bones (kg)'] = $record->bones ?? 0;
                    $row['Minced Meat (kg)'] = $record->minced_meat ?? 0;
                } elseif ($this->type === 'hind-quarters') {
                    $row['Fillet (kg)'] = $record->fillet ?? 0;
                    $row['Sirloin/Striploin (kg)'] = $record->sirloin_striploin ?? 0;
                    $row['Rump (kg)'] = $record->rump ?? 0;
                    $row['Topside (kg)'] = $record->topside_beef_roast ?? 0;
                    $row['Silverside (kg)'] = $record->silver_side ?? 0;
                    $row['T-Bone (kg)'] = $record->t_bone ?? 0;
                    $row['Rib Eye (kg)'] = $record->rib_eye ?? 0;
                    $row['Thick Flank (kg)'] = $record->thick_flank ?? 0;
                    $row['Leg Cut (kg)'] = $record->leg_cut ?? 0;
                    $row['Ossubucco (kg)'] = $record->ossubucco ?? 0;
                    $row['Beef Stew (kg)'] = $record->beef_stew ?? 0;
                } elseif ($this->type === 'offals') {
                    $row['Heart (kg)'] = $record->heart ?? 0;
                    $row['Liver (kg)'] = $record->liver ?? 0;
                    $row['Kidneys (kg)'] = $record->kidneys ?? 0;
                    $row['Tongue (kg)'] = $record->tongue ?? 0;
                    $row['Tripe (kg)'] = $record->tripe ?? 0;
                    $row['Lungs (kg)'] = $record->lungs ?? 0;
                    $row['Tail (kg)'] = $record->tail ?? 0;
                    $row['Head (kg)'] = $record->head ?? 0;
                    $row['Feet (kg)'] = $record->feet ?? 0;
                    $row['Testicles (kg)'] = $record->testicles ?? 0;
                }
                
                $row['Notes'] = $record->notes;
                $row['Created At'] = $record->created_at;
                
                $rows[] = $row;
            }
            
            return $rows;
        });
        
        parent::export();
    }
}
