<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlaughterRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Slaughter Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());
        
        // Search and Filters
        $grid->quickSearch('e_id', 'v_id', 'lhc')->placeholder('Search by E-ID, V-ID or LHC');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'E-ID');
            $filter->like('v_id', 'V-ID');
            $filter->like('lhc', 'LHC');
            $filter->between('created_at', 'Slaughter Date');
            $filter->equal('post_grade', 'Meat Grade')->select([
                'Grade A' => 'Grade A',
                'Grade B' => 'Grade B',
                'Grade C' => 'Grade C',
                'Grade D' => 'Grade D',
                'Grade E' => 'Grade E',
            ]);
            $filter->equal('carcus_owen_assigned', 'Carcass Assignment')->select([
                'Yes' => 'Assigned',
                'No' => 'Not Assigned',
            ]);
            $filter->equal('sex', 'Sex')->select([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);
        });

        $grid->model()->orderBy('id', 'DESC');

        // 1. Slaughter Date
        $grid->column('created_at', __('Slaughter Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->format('d M Y');
            })
            ->sortable();

        // 2. E-ID
        $grid->column('e_id', __('E-ID'))
            ->sortable();

        // 3. Slaughtered By
        $grid->column('administrator_id', __('Slaughtered By'))
            ->display(function ($f) {
                $u = Administrator::find($f);
                if ($u == null) {
                    return 'N/A';
                }
                return $u->name;
            })
            ->sortable();

        // 4. Ante-mortem Findings
        $grid->column('has_post_info', __('Ante-mortem Findings'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null' || strtolower($f) == 'no') {
                    return 'No findings';
                }
                
                // Try to parse as JSON array
                $data = json_decode($f);
                if ($data !== null && is_array($data) && count($data) > 0) {
                    $findings = array_filter($data);
                    if (count($findings) > 0) {
                        $text = implode(', ', $findings);
                        return '<span title="' . htmlspecialchars($text) . '">' . 
                               htmlspecialchars(substr($text, 0, 60)) . 
                               (strlen($text) > 60 ? '...' : '') . '</span>';
                    }
                }
                
                // If just "Yes", show generic message
                if (strtolower($f) == 'yes') {
                    return 'Findings recorded';
                }
                
                // Otherwise display as is
                return htmlspecialchars($f);
            });

        // 5. Post-mortem Findings
        $grid->column('post_other', __('Post-mortem Findings'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'No findings';
                }
                
                // Try to parse as JSON
                $data = json_decode($f);
                if ($data !== null && is_array($data) && count($data) > 0) {
                    $findings = array_filter($data);
                    if (count($findings) > 0) {
                        $text = implode(', ', $findings);
                        return '<span title="' . htmlspecialchars($text) . '">' . 
                               htmlspecialchars(substr($text, 0, 50)) . 
                               (strlen($text) > 50 ? '...' : '') . '</span>';
                    }
                }
                
                // If not JSON, display as is
                $text = trim($f);
                return '<span title="' . htmlspecialchars($text) . '">' . 
                       htmlspecialchars(substr($text, 0, 50)) . 
                       (strlen($text) > 50 ? '...' : '') . '</span>';
            });

        // 6. Dentition
        $grid->column('post_dentition', __('Dentition'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return '-';
                }
                return htmlspecialchars($f);
            })
            ->sortable();

        // 7. Carcass Weight
        $grid->column('post_weight', __('Carcass Weight (kg)'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'N/A';
                }
                return $f;
            })
            ->sortable();

        // 8. Carcass Grade
        $grid->column('post_grade', __('Carcass Grade'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'Not Graded';
                }
                return htmlspecialchars($f);
            })
            ->sortable();

        // 9. Assigned To
        $grid->column('carcus_owen_name', __('Assigned To'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return '-';
                }
                return $f;
            })
            ->sortable();

        // 10. Status
        $grid->column('breed', __('Status'))
            ->display(function ($f) {
                $isDone = strtolower($f) == 'done';
                return $isDone ? 'Completed' : 'Ongoing';
            })
            ->sortable();

        // Additional Details (Hidden - can be shown via column selector)
        $grid->column('v_id', __('V-ID'))->hide();
        $grid->column('lhc', __('LHC'))->hide();
        $grid->column('sex', __('Sex'))->hide();
        $grid->column('post_age', __('Age'))->hide();
        $grid->column('post_fat', __('Fat (mm)'))->hide();
        $grid->column('dob', __('Date of Birth'))->hide();
        $grid->column('fmd', __('Last FMD'))->hide();
        $grid->column('available_weight', __('Available Weight'))->hide();
        $grid->column('destination_slaughter_house', __('Slaughter House'))->hide();

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableExport();
        
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SlaughterRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('lhc', __('Lhc'));
        $show->field('v_id', __('V id'));
        $show->field('e_id', __('E id'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('fmd', __('Fmd'));
        $show->field('destination_slaughter_house', __('Destination slaughter house'));
        $show->field('details', __('Details'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('type', __('Type'));
        $show->field('bar_code', __('Bar code'));
        $show->field('post_grade', __('Post grade'));
        $show->field('post_animal', __('Post animal'));
        $show->field('post_age', __('Post age'));
        $show->field('post_dentition', __('Post dentition'));
        $show->field('post_weight', __('Post weight'));
        $show->field('post_fat', __('Post fat'));
        $show->field('post_other', __('Post other'));
        $show->field('has_post_info', __('Has post info'));
        $show->field('available_weight', __('Available weight'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SlaughterRecord());

        $form->textarea('lhc', __('Lhc'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('fmd', __('Fmd'));
        $form->textarea('destination_slaughter_house', __('Destination slaughter house'));
        $form->textarea('details', __('Details'));
        $form->number('administrator_id', __('Administrator id'))->default(14);
        $form->textarea('type', __('Type'));
        $form->textarea('bar_code', __('Bar code'));
        $form->textarea('post_grade', __('Post grade'));
        $form->textarea('post_animal', __('Post animal'));
        $form->textarea('post_age', __('Post age'));
        $form->textarea('post_dentition', __('Post dentition'));
        $form->textarea('post_weight', __('Post weight'));
        $form->textarea('post_fat', __('Post fat'));
        $form->textarea('post_other', __('Post other'));
        $form->textarea('has_post_info', __('Has post info'));
        $form->textarea('available_weight', __('Available weight'));

        return $form;
    }
}
