<?php

namespace App\Admin\Controllers;

use App\Models\PackagingRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;

class ButcheryPackagingController extends AdminController
{
    protected $title = 'Packaging';

    // Fore quarter primal cut fields
    protected static $foreQuarterCuts = [
        'Beef Boneless'   => 'beef_boneless',
        'Beef Stew'       => 'beef_stew',
        'Bones'           => 'bones',
        'Brisket'         => 'brisket',
        'Chops'           => 'chops',
        'Chuck Ribs'      => 'chuck_ribs',
        'Family Steak'    => 'family_steak',
        'Fore Rib'        => 'fore_rib',
        'Leg Cut'         => 'leg_cut',
        'Middle Rib'      => 'middle_rib',
        'Minced Meat'     => 'minced_meat',
        'Neck'            => 'neck',
        'Ossubucco'       => 'ossubucco',
        'Ribs'            => 'ribs',
        'Shin'            => 'shin',
        'Staff Meat'      => 'staff_meat',
        'Thick Flank'     => 'thick_flank',
    ];

    // Hind quarter primal cut fields
    protected static $hindQuarterCuts = [
        'Fillet'             => 'fillet',
        'Oxtail'             => 'oxtail',
        'Rib Eye'            => 'rib_eye',
        'Rolled Loin'        => 'rolled_loin',
        'Rump'               => 'rump',
        'Silver Side'        => 'silver_side',
        'Sirloin/Striploin'  => 'sirloin_striploin',
        'T-Bone'             => 't_bone',
        'Topside/Beef Roast' => 'topside_beef_roast',
        'Veal Steak'         => 'veal_steak',
    ];

    // Offal fields
    protected static $offalCuts = [
        'Heart'     => 'heart',
        'Kidneys'   => 'kidneys',
        'Liver'     => 'liver',
        'Tongue'    => 'tongue',
        'Lungs'     => 'lungs',
        'Tripe'     => 'tripe',
        'Tail'      => 'tail',
        'Head'      => 'head',
        'Feet'      => 'feet',
        'Testicles' => 'testicles',
    ];

    public function index(Content $content)
    {
        $type = request()->route()->defaults['type'] ?? request()->get('type', 'fore-quarters');
        
        $titles = [
            'fore-quarters' => 'Primal Cut Packages – Fore Quarters',
            'hind-quarters' => 'Primal Cut Packages – Hind Quarters',
            'offals'        => 'Offals Packages',
        ];

        $descriptions = [
            'fore-quarters' => 'Record of packaged products from each fore quarter primal cut per EID',
            'hind-quarters' => 'Record of packaged products from each hind quarter primal cut per EID',
            'offals'        => 'Record of packaged offal products per EID',
        ];

        return $content
            ->title($titles[$type] ?? 'Packaging')
            ->description($descriptions[$type] ?? '')
            ->body($this->grid($type));
    }

    public function show($id, Content $content)
    {
        return $content
            ->title('Package Details')
            ->body($this->detail($id));
    }

    public function edit($id, Content $content)
    {
        $type = request()->get('type', 'fore-quarters');
        return $content
            ->title('Edit Package')
            ->body($this->form($type)->edit($id));
    }

    public function create(Content $content)
    {
        $type = request()->get('type', 'fore-quarters');
        return $content
            ->title('Create Package')
            ->body($this->form($type));
    }

    protected function grid($type = 'fore-quarters')
    {
        $cutsMap = $this->getCutsMap($type);

        $grid = new Grid(new PackagingRecord());

        if ($type === 'fore-quarters') {
            $fqFields = array_values(self::$foreQuarterCuts);
            $grid->model()->where('package_type', 'Primal Cut')
                ->where(function ($q) use ($fqFields) {
                    foreach ($fqFields as $field) {
                        $q->orWhere($field, '>', 0);
                    }
                });
        } elseif ($type === 'hind-quarters') {
            $hqFields = array_values(self::$hindQuarterCuts);
            $grid->model()->where('package_type', 'Primal Cut')
                ->where(function ($q) use ($hqFields) {
                    foreach ($hqFields as $field) {
                        $q->orWhere($field, '>', 0);
                    }
                });
        } elseif ($type === 'offals') {
            $grid->model()->where('package_type', 'Offal');
        }

        $grid->model()->orderBy('packaging_date', 'desc');

        // EID – expandable to show per-cut breakdown
        $grid->column('e_id', 'EID')->expand(function ($model) use ($cutsMap, $type) {
            $rows       = '';
            $totalWeight = 0;
            $totalPkgs   = 0;

            foreach ($cutsMap as $label => $field) {
                $weight = (float) ($model->$field ?? 0);
                if ($weight <= 0) {
                    continue;
                }
                $totalWeight += $weight;
                $totalPkgs++;
                $rows .= '<tr>'
                    . '<td><strong>' . htmlspecialchars($label) . '</strong></td>'
                    . '<td>1</td>'
                    . '<td>' . number_format($weight, 2) . ' kg</td>'
                    . '<td><strong>' . number_format($weight, 2) . ' kg</strong></td>'
                    . '</tr>';
            }

            if (!$rows) {
                $rows = '<tr><td colspan="4" style="color:#999;text-align:center;">No weights recorded</td></tr>';
            }

            $typeLabel = $type === 'offals' ? 'Offal' : 'Primal Cut';
            $hdr       = $type === 'offals' ? 'Offal' : 'Primal Cut';

            return '<div style="padding:15px;background:#f9f6f3;">'
                . '<h4 style="color:#6B3C00;margin-top:0;border-bottom:2px solid #6B3C00;padding-bottom:6px;">'
                . $typeLabel . ' Package Record – EID: ' . htmlspecialchars((string) ($model->e_id ?? ''))
                . '</h4>'
                . '<table class="table table-bordered table-condensed" style="background:#fff;margin-bottom:0;">'
                . '<thead style="background:#6B3C00;color:#fff;"><tr>'
                . '<th>' . $hdr . '</th>'
                . '<th>Nos Packages</th>'
                . '<th>Package Weights</th>'
                . '<th>Total</th>'
                . '</tr></thead>'
                . '<tbody>' . $rows . '</tbody>'
                . '<tfoot><tr style="background:#f5ede4;font-weight:bold;">'
                . '<td>TOTAL</td><td>' . $totalPkgs . '</td><td></td>'
                . '<td>' . number_format($totalWeight, 2) . ' kg</td>'
                . '</tr></tfoot>'
                . '</table></div>';
        })->sortable();

        // Number of packages (non-zero cut entries per record)
        $grid->column('nos_packages', 'Nos Packages')->display(function () use ($cutsMap) {
            $count = 0;
            foreach ($cutsMap as $field) {
                if ((float) ($this->$field ?? 0) > 0) {
                    $count++;
                }
            }
            return $count;
        });

        // Total weight
        $grid->column('total_weight', 'Total (kg)')->display(function ($weight) {
            return '<strong>' . number_format((float) ($weight ?? 0), 2) . ' kg</strong>';
        })->sortable();

        // Date
        $grid->column('packaging_date', 'Date')->display(function ($date) {
            return $date ? Carbon::parse($date)->format('d M Y') : '—';
        })->sortable();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'EID');
            $filter->between('packaging_date', 'Package Date')->date();
        });

        $grid->disableCreateButton();
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(PackagingRecord::findOrFail($id));

        $show->panel()->title('Package Details')->tools(function ($tools) {
            $tools->disableDelete();
        });

        $show->field('e_id', 'EID');
        $show->field('v_id', 'VID');
        $show->field('package_type', 'Package Type');
        $show->field('total_weight', 'Total Weight')->as(function ($v) {
            return number_format((float) $v, 2) . ' kg';
        });
        $show->field('packaging_date', 'Packaging Date')->as(function ($d) {
            return $d ? Carbon::parse($d)->format('d F Y') : '—';
        });
        $show->field('notes', 'Notes');

        return $show;
    }

    protected function form($type = 'fore-quarters')
    {
        $form = new \Encore\Admin\Form(new PackagingRecord());

        $form->text('e_id', 'EID')->required();
        $form->text('v_id', 'VID');
        $form->hidden('package_type')->default($type === 'offals' ? 'Offal' : 'Primal Cut');
        $form->date('packaging_date', 'Packaging Date')->required();
        $form->decimal('total_weight', 'Total Weight (kg)');
        $form->textarea('notes', 'Notes');

        return $form;
    }

    protected function getCutsMap(string $type): array
    {
        if ($type === 'fore-quarters') {
            return self::$foreQuarterCuts;
        }
        if ($type === 'hind-quarters') {
            return self::$hindQuarterCuts;
        }
        return self::$offalCuts;
    }
}
