<?php

namespace App\Admin\Controllers;

use App\Models\PackagingRecord;
use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ButcheryPackagingController extends AdminController
{
    protected $title = 'Packaging';
    protected $packagingType;

    /**
     * Index interface with type-based customization
     */
    public function index(Content $content, $type = 'fore-quarters')
    {
        $this->packagingType = $type;
        
        $titles = [
            'fore-quarters' => 'Primal Cuts - Fore Quarters Packaging',
            'hind-quarters' => 'Primal Cuts - Hind Quarters Packaging',
            'offals' => 'Offals Packaging',
        ];
        
        $descriptions = [
            'fore-quarters' => 'Package primal cuts from fore quarters (Chuck, Brisket, Rib, Shank, Plate)',
            'hind-quarters' => 'Package primal cuts from hind quarters (Loin, Flank, Round, Sirloin)',
            'offals' => 'Package offal items (Heart, Kidneys, Liver, Tongue, etc.)',
        ];

        return $content
            ->title($titles[$type] ?? 'Packaging')
            ->description($descriptions[$type] ?? 'Manage packaging records')
            ->body($this->grid($type));
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return $content
            ->title('Package Details')
            ->description('Complete packaging information')
            ->body($this->detail($id));
    }

    /**
     * Edit interface
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title('Edit Package')
            ->description('Update packaging information')
            ->body($this->form($this->packagingType)->edit($id));
    }

    /**
     * Create interface
     */
    public function create(Content $content)
    {
        $type = request()->get('type', 'fore-quarters');
        
        return $content
            ->title('Create Package')
            ->description('Fill in packaging details')
            ->body($this->form($type));
    }

    /**
     * Make a grid builder with type-based filtering
     */
    protected function grid($type = 'fore-quarters')
    {
        $grid = new Grid(new PackagingRecord());

        // Filter based on packaging type
        if ($type === 'fore-quarters') {
            $grid->model()->where('package_type', 'Fore Quarter Cut')
                ->orWhere(function($q) {
                    $q->where('package_type', 'Prime Cut')
                      ->whereNotNull('beef_boneless'); // Has fore quarter specific cuts
                });
        } elseif ($type === 'hind-quarters') {
            $grid->model()->where('package_type', 'Hind Quarter Cut')
                ->orWhere(function($q) {
                    $q->where('package_type', 'Prime Cut')
                      ->whereNotNull('fillet'); // Has hind quarter specific cuts
                });
        } elseif ($type === 'offals') {
            $grid->model()->where('package_type', 'Offal')
                ->orWhereNotNull('heart')
                ->orWhereNotNull('liver')
                ->orWhereNotNull('kidneys');
        }

        $grid->model()->orderBy('packaging_date', 'desc');

        // Common columns
        $grid->column('id', 'ID')->sortable();
        
        $grid->column('package_code', 'Package Code')->display(function() {
            return '<strong style="color:#6B3C00;font-size:12px;">' . 
                   ($this->package_code ?? 'N/A') . 
                   '</strong>';
        });

        // Animal identification
        $grid->column('e_id', 'E-ID')->sortable();
        $grid->column('v_id', 'V-ID');

        // Type-specific columns
        if ($type === 'fore-quarters') {
            $this->addForeQuarterColumns($grid);
        } elseif ($type === 'hind-quarters') {
            $this->addHindQuarterColumns($grid);
        } elseif ($type === 'offals') {
            $this->addOffalColumns($grid);
        }

        // Common columns
        $grid->column('total_weight', 'Total Weight')->display(function($weight) {
            return '<strong>' . number_format($weight ?? 0, 2) . ' kg</strong>';
        })->sortable();

        $grid->column('packaging_date', 'Package Date')->display(function($date) {
            return Carbon::parse($date)->format('d M Y');
        })->sortable();

        $grid->column('expiry_date', 'Expiry Date')->display(function($date) {
            if(!$date) return '<em style="color:#999;">N/A</em>';
            
            $expiry = Carbon::parse($date);
            $now = Carbon::now();
            
            if($expiry->isPast()) {
                return '<span style="color:#d32f2f;font-weight:600;">' . 
                       $expiry->format('d M Y') . ' <i class="fa fa-warning"></i> EXPIRED</span>';
            } elseif($e
        $grid->column('packager.name', 'Packaged By');

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        // Filters
        $grid->filter(function($filter) use ($type) {
            $filter->disableIdFilter();
            
            $filter->like('e_id', 'E-ID');
            $filter->like('v_id', 'V-ID');
            $filter->like('barcode', 'Barcode');
            
            $filter->between('packaging_date', 'Package Date')->date();
            $filter->between('expiry_date', 'Expiry Date')->date();
            
            $filter->equal('status', 'Status')->select([
                'Active' => 'Active',
                'Sold' => 'Sold',
                'Expired' => 'Expired',
                'Discarded' => 'Discarded',
            ]);

            if($type === 'fore-quarters' || $type === 'hind-quarters') {
                $filter->where(function ($query) {
                    $query->where('total_weight', '>=', $this->input);
                }, 'Min Weight (kg)');
            
            $filter->between('packaging_date', 'Package
        $grid->exporter(new \App\Admin\Extensions\PackagingExporter($type));

        // Disable create button - packaging should be created from distribution records
        $grid->disableCreateButton();
        
        // Add custom button to create from distribution
        $grid->tools(function ($tools) use ($type) {
            $tools->append('
                <div class="btn-group pull-right" style="margin-right: 10px">
                    <a href="' . admin_url('slaughter-distributions?type=' . $type) . '" class="btn btn-sm btn-success">
                        <i class="fa fa-cut"></i> Create from Distribution Records
                    </a>
                </div>
            ');
        });

        return $grid;
    }

    /**
     * Add fore quarter specific columns
     */ 
            if(empty($cuts)) return '<em style="color:#999;">No cuts recorded</em>';
            
            return '<small style="line-height:1.6;">' . implode('<br>', array_slice($cuts, 0, 3)) . '</small>';
        });
    }

    /**
     * Add hind quarter specific columns
     */
    protected function addHindQuarterColumns($grid)
    {
        $grid->column('cuts_summary', 'Hind Quarter Cuts')->display(function() {
            $cuts = [];
            if($this->fillet) $cuts[] = "Fillet: {$this->fillet}kg";
            if($this->sirloin_striploin) $cuts[] = "Sirloin: {$this->sirloin_striploin}kg";
            if($this->rump) $cuts[] = "Rump: {$this->rump}kg";
            if($this->topside_beef_roast) $cuts[] = "Topside: {$this->topside_beef_roast}kg";
            if($this->silver_side) $cuts[] = "Silverside: {$this->silver_side}kg";
            if($this->t_bone) $cuts[] = "T-Bone: {$this->t_bone}kg";
            if($this->rib_eye) $cuts[] = "Rib Eye: {$this->rib_eye}kg";
            
            if(empty($cuts)) return '<em style="color:#999;">No cuts recorded</em>';
            
            return '<small style="line-height:1.6;">' . implode('<br>', array_slice($cuts, 0, 3)) . '</small>';
        });
    }

    /**
     * Add offal specific columns
     */
    protected function addOffalColumns($grid)
    {
        $grid->column('offal_summary', 'Offal Items')->display(function() {
            $items = [];
            if($this->heart) $items[] = "Heart: {$this->heart}kg";
            if($this->liver) $items[] = "Liver: {$this->liver}kg";
            if($this->kidneys) $items[] = "Kidneys: {$this->kidneys}kg";
            if($this->tongue) $items[] = "Tongue: {$this->tongue}kg";
            if($this->tripe) $items[] = "Tripe: {$this->tripe}kg";
            if($this->lungs) $items[] = "Lungs: {$this->lungs}kg";
            if($this->tail) $items[] = "Tail: {$this->tail}kg";
            if($this->head) $items[] = "Head: {$this->head}kg";
            
            if(empty($items)) return '<em style="color:#999;">No items recorded</em>';
            
            return '<small style="line-height:1.6;">' . implode('<br>', array_slice($items, 0, 4)) . '</small>';
        });
    }

    /**
     * Make a show builder.
     */
    protected function detail($id)
    {
        $show = new Show(PackagingRecord::findOrFail($id));

        $show->panel()->title('Package Details')->tools(function ($tools) {
            $tools->disableDelete();
        });

        $show->field('id', 'ID');
        $show->field('package_code', 'Package Code');
        $show->field('barcode', 'Barcode');
        $show->field('qr_code_link', 'QR Code')->link();
        
        $show->divider('Animal Information');
        $show->field('e_id', 'E-ID');
        $show->field('v_id', 'V-ID');
        $show->field('lhc', 'LHC');
        $show->field('breed', 'Breed');
        $show->field('sex', 'Sex');

        $show->divider('Package Details');
        $show->field('package_type', 'Package Type');
        $show->field('total_weight', 'Total Weight')->as(function($w) {
            return number_format($w, 2) . ' kg';
        });
        $show->field('packaging_date', 'Packaging Date');
        $show->field('expiry_date', 'Expiry Date');
        $show->field('status', 'Status');

        $show->divider('Cuts Breakdown');
        // Show all non-null cut fields
        $record = PackagingRecord::find($id);
        $cutFields = [
            'beef_boneless', 'beef_stew', 'bones', 'brisket', 'chops', 'chuck_ribs',
            'family_steak', 'fore_rib', 'leg_cut', 'middle_rib', 'minced_meat', 'neck',
            'ossubucco', 'oxtail', 'ribs', 'shin', 'staff_meat', 'thick_flank',
            'fillet', 'rib_eye', 'rolled_loin', 'rump', 'silver_side', 'sirloin_striploin',
            't_bone', 'topside_beef_roast', 'veal_steak',
            'heart', 'kidneys', 'liver', 'tongue', 'lungs', 'tripe',
            'tail', 'head', 'feet', 'testicles'
        ];

        foreach($cutFields as $field) {
            if($record->$field > 0) {
                $label = ucwords(str_replace('_', ' ', $field));
                $show->field($field, $label)->as(function($v) {
                    return number_format($v, 2) . ' kg';
                });
            }
        }

        $show->field('notes', 'Notes');
        $show->field('packager.name', 'Packaged By');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    /**
     * Make a form builder.
     */
    protected function form($type = 'fore-quarters')
    {
        $form = new Form(new PackagingRecord());

        $form->tab('Basic Information', function ($form) use ($type) {
            
            // Auto-fill from distribution record if provided
            $distributionId = request()->get('distribution_id');
            if($distributionId) {
                $distribution = SlaughterDistributionRecord::find($distributionId);
                if($distribution && $distribution->slaughterRecord) {
                    $slaughter = $distribution->slaughterRecord;
                    
                    $form->hidden('slaughter_distribution_record_id')->default($distributionId);
                    $form->hidden('slaughter_record_id')->default($slaughter->id);
                    $form->hidden('animal_id')->default($slaughter->animal_id);
                    $form->display('source_info', 'Source')->default(
                        "From: {$distribution->source_address} - {$distribution->original_weight}kg"
                    );
                }
            } else {
                $form->select('slaughter_record_id', 'Slaughter Record')
                    ->options(SlaughterRecord::orderBy('created_at', 'desc')
                        ->limit(100)
                        ->get()
                        ->pluck('e_id', 'id'))
                    ->required()
                    ->load('animal_id', admin_url('api/slaughter-records/animal'));
            }

            $typeLabels = [
                'fore-quarters' => 'Fore Quarter Cut',
                'hind-quarters' => 'Hind Quarter Cut',
                'offals' => 'Offal',
            ];
            
            $form->select('package_type', 'Package Type')
                ->options([
                    'Fore Quarter Cut' => 'Fore Quarter Cut',
                    'Hind Quarter Cut' => 'Hind Quarter Cut',
                    'Offal' => 'Offal',
                    'Prime Cut' => 'Prime Cut',
                    'Mixed' => 'Mixed',
                ])
                ->default($typeLabels[$type] ?? 'Prime Cut')
                ->required();

            $form->text('barcode', 'Barcode')->help('Auto-generated if left empty');
            $form->date('packaging_date', 'Packaging Date')->default(date('Y-m-d'))->required();
            $form->date('expiry_date', 'Expiry Date')->required();
            $form->select('status', 'Status')->options([
                'Active' => 'Active',
                'Sold' => 'Sold',
                'Expired' => 'Expired',
                'Discarded' => 'Discarded',
            ])->default('Active')->required();
        });

        // Type-specific tabs
        if($type === 'fore-quarters') {
            $form->tab('Fore Quarter Cuts', function($form) {
                $form->decimal('chuck_ribs', 'Chuck Ribs (kg)')->default(0);
                $form->decimal('brisket', 'Brisket (kg)')->default(0);
                $form->decimal('fore_rib', 'Fore Rib (kg)')->default(0);
                $form->decimal('shin', 'Shin (kg)')->default(0);
                $form->decimal('neck', 'Neck (kg)')->default(0);
                $form->decimal('beef_boneless', 'Beef Boneless (kg)')->default(0);
                $form->decimal('ribs', 'Ribs (kg)')->default(0);
                $form->decimal('bones', 'Bones (kg)')->default(0);
                $form->decimal('minced_meat', 'Minced Meat (kg)')->default(0);
            });
        } elseif($type === 'hind-quarters') {
            $form->tab('Hind Quarter Cuts', function($form) {
                $form->decimal('fillet', 'Fillet (kg)')->default(0);
                $form->decimal('sirloin_striploin', 'Sirloin/Striploin (kg)')->default(0);
                $form->decimal('rump', 'Rump (kg)')->default(0);
                $form->decimal('topside_beef_roast', 'Topside/Beef Roast (kg)')->default(0);
                $form->decimal('silver_side', 'Silverside (kg)')->default(0);
                $form->decimal('t_bone', 'T-Bone (kg)')->default(0);
                $form->decimal('rib_eye', 'Rib Eye (kg)')->default(0);
                $form->decimal('thick_flank', 'Thick Flank (kg)')->default(0);
                $form->decimal('leg_cut', 'Leg Cut (kg)')->default(0);
                $form->decimal('ossubucco', 'Ossubucco (kg)')->default(0);
                $form->decimal('beef_stew', 'Beef Stew (kg)')->default(0);
            });
        } elseif($type === 'offals') {
            $form->tab('Offal Items', function($form) {
                $form->decimal('heart', 'Heart (kg)')->default(0);
                $form->decimal('liver', 'Liver (kg)')->default(0);
                $form->decimal('kidneys', 'Kidneys (kg)')->default(0);
                $form->decimal('tongue', 'Tongue (kg)')->default(0);
                $form->decimal('tripe', 'Tripe (kg)')->default(0);
                $form->decimal('lungs', 'Lungs (kg)')->default(0);
                $form->decimal('tail', 'Tail (kg)')->default(0);
                $form->decimal('head', 'Head (kg)')->default(0);
                $form->decimal('feet', 'Feet (kg)')->default(0);
                $form->decimal('testicles', 'Testicles (kg)')->default(0);
            });
        }

        $form->tab('Additional Information', function($form) {
            $form->textarea('notes', 'Notes')->rows(3);
            $form->hidden('packaged_by')->default(auth()->user()->id);
        });

        // Save hooks
        $form->saving(function (Form $form) {
            // Generate barcode if not provided
            if(empty($form->barcode)) {
                $form->barcode = 'PKG-' . strtoupper(uniqid());
            }
            
            // Auto-calculate total weight (model will handle this in boot method)
            // Set packaged_by
            $form->packaged_by = auth()->user()->id;
        });

        return $form;
    }
}
