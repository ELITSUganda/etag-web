<?php

namespace App\Admin\Controllers;

use App\Models\PregnantAnimal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PregnantAnimalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Pregnant Animals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PregnantAnimal());
        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('administrator_id', __('Administrator id'));
        $grid->column('animal_id', __('Parent Animal'))
            ->display(function ($animal_id) {
                if ($this->animal == null) {
                    return "Animal not found";
                }
                return $this->animal->v_id;
            });
        /*         $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('original_status', __('Original status'));
 */
        $grid->column('current_status', __('Stage'))->sortable();
        $grid->column('fertilization_method', __('Fertilization Method'))->sortable();
        $grid->column('expected_sex', __('Expected Sex'))->filter([
            'Male' => 'Male',
            'Female' => 'Female',
        ])->sortable();
        $grid->column('details', __('Details'))->hide();
        $grid->column('pregnancy_check_method', __('Pregnancy Check Method'))->sortable();
        $grid->column('born_sex', __('Born sex'));
        $grid->column('conception_date', __('Conception date'));
        $grid->column('expected_calving_date', __('Expected calving date'));
        $grid->column('gestation_length', __('Gestation length'));
        $grid->column('did_animal_abort', __('Did animal abort'));
        $grid->column('reason_for_animal_abort', __('Reason for animal abort'));
        $grid->column('did_animal_conceive', __('Did animal conceive'));
        $grid->column('calf_birth_weight', __('Calf birth weight'));
        $grid->column('pregnancy_outcome', __('Pregnancy outcome'));
        $grid->column('calving_difficulty', __('Calving difficulty'));
        $grid->column('postpartum_recovery_time', __('Postpartum recovery time'))->hide();
        $grid->column('post_calving_complications', __('Post calving complications'))->hide();
        $grid->column('total_pregnancies', __('Total pregnancies'))->hide();
        $grid->column('hormone_use', __('Hormone use'))->hide();
        $grid->column('nutritional_status', __('Nutritional status'))->hide();
        $grid->column('number_of_calves', __('Number of calves'));
        $grid->column('born_date', __('Born date'));
        $grid->column('calf_id', __('Calf id'));
        $grid->column('total_calving_milk', __('Total calving milk'));
        $grid->column('is_weaned_off', __('Is weaned off'));
        $grid->column('weaning_date', __('Weaning date'));
        $grid->column('weaning_weight', __('Weaning weight'));
        $grid->column('weaning_age', __('Weaning age'));
        $grid->column('got_pregnant', __('Got pregnant'));
        $grid->column('ferilization_date', __('Ferilization date'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('parent_v_id', __('Parent v id'));
        $grid->column('calf_v_id', __('Calf v id'));
        $grid->column('parent_photo', __('Parent photo'));
        $grid->column('calf_photo', __('Calf photo'));

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
        $show = new Show(PregnantAnimal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('original_status', __('Original status'));
        $show->field('current_status', __('Current status'));
        $show->field('fertilization_method', __('Fertilization method'));
        $show->field('expected_sex', __('Expected sex'));
        $show->field('details', __('Details'));
        $show->field('pregnancy_check_method', __('Pregnancy check method'));
        $show->field('born_sex', __('Born sex'));
        $show->field('conception_date', __('Conception date'));
        $show->field('expected_calving_date', __('Expected calving date'));
        $show->field('gestation_length', __('Gestation length'));
        $show->field('did_animal_abort', __('Did animal abort'));
        $show->field('reason_for_animal_abort', __('Reason for animal abort'));
        $show->field('did_animal_conceive', __('Did animal conceive'));
        $show->field('calf_birth_weight', __('Calf birth weight'));
        $show->field('pregnancy_outcome', __('Pregnancy outcome'));
        $show->field('calving_difficulty', __('Calving difficulty'));
        $show->field('postpartum_recovery_time', __('Postpartum recovery time'));
        $show->field('post_calving_complications', __('Post calving complications'));
        $show->field('total_pregnancies', __('Total pregnancies'));
        $show->field('hormone_use', __('Hormone use'));
        $show->field('nutritional_status', __('Nutritional status'));
        $show->field('number_of_calves', __('Number of calves'));
        $show->field('born_date', __('Born date'));
        $show->field('calf_id', __('Calf id'));
        $show->field('total_calving_milk', __('Total calving milk'));
        $show->field('is_weaned_off', __('Is weaned off'));
        $show->field('weaning_date', __('Weaning date'));
        $show->field('weaning_weight', __('Weaning weight'));
        $show->field('weaning_age', __('Weaning age'));
        $show->field('got_pregnant', __('Got pregnant'));
        $show->field('ferilization_date', __('Ferilization date'));
        $show->field('farm_id', __('Farm id'));
        $show->field('parent_v_id', __('Parent v id'));
        $show->field('calf_v_id', __('Calf v id'));
        $show->field('parent_photo', __('Parent photo'));
        $show->field('calf_photo', __('Calf photo'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PregnantAnimal());

        $form->number('administrator_id', __('Administrator id'));
        $form->number('animal_id', __('Animal id'));
        $form->number('district_id', __('District id'))->default(1);
        $form->number('sub_county_id', __('Sub county id'))->default(1);
        $form->text('original_status', __('Original status'))->default('Pregnant');
        $form->text('current_status', __('Current status'))->default('Pregnant');
        $form->text('fertilization_method', __('Fertilization method'));
        $form->text('expected_sex', __('Expected sex'));
        $form->textarea('details', __('Details'));
        $form->text('pregnancy_check_method', __('Pregnancy check method'));
        $form->textarea('born_sex', __('Born sex'));
        $form->text('conception_date', __('Conception date'));
        $form->text('expected_calving_date', __('Expected calving date'));
        $form->number('gestation_length', __('Gestation length'));
        $form->text('did_animal_abort', __('Did animal abort'));
        $form->text('reason_for_animal_abort', __('Reason for animal abort'));
        $form->text('did_animal_conceive', __('Did animal conceive'));
        $form->decimal('calf_birth_weight', __('Calf birth weight'));
        $form->text('pregnancy_outcome', __('Pregnancy outcome'));
        $form->text('calving_difficulty', __('Calving difficulty'));
        $form->number('postpartum_recovery_time', __('Postpartum recovery time'));
        $form->textarea('post_calving_complications', __('Post calving complications'));
        $form->number('total_pregnancies', __('Total pregnancies'));
        $form->text('hormone_use', __('Hormone use'));
        $form->textarea('nutritional_status', __('Nutritional status'));
        $form->number('number_of_calves', __('Number of calves'));
        $form->text('born_date', __('Born date'));
        $form->text('calf_id', __('Calf id'));
        $form->number('total_calving_milk', __('Total calving milk'));
        $form->text('is_weaned_off', __('Is weaned off'));
        $form->text('weaning_date', __('Weaning date'));
        $form->text('weaning_weight', __('Weaning weight'));
        $form->text('weaning_age', __('Weaning age'));
        $form->text('got_pregnant', __('Got pregnant'))->default('Pending');
        $form->date('ferilization_date', __('Ferilization date'))->default(date('Y-m-d'));
        $form->number('farm_id', __('Farm id'));
        $form->textarea('parent_v_id', __('Parent v id'));
        $form->textarea('calf_v_id', __('Calf v id'));
        $form->textarea('parent_photo', __('Parent photo'));
        $form->textarea('calf_photo', __('Calf photo'));

        return $form;
    }
}
