<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AnimalNewController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Animal';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Animal());

        $grid->column('id', __('Id'));
        $grid->quickSearch('e_id', 'v_id', 'lhc', 'breed')->placeholder('Search by E ID, V ID, LHC, Breed');
        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'))->sortable();
        $grid->column('administrator_id', __('Administrator id'))->sortable();
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('parish_id', __('Parish id'));
        $grid->column('status', __('Status'));
        $grid->column('type', __('Type'));
        $grid->column('e_id', __('E id'));
        $grid->column('v_id', __('V id'));
        $grid->column('lhc', __('Lhc'));
        $grid->column('breed', __('Breed'));
        $grid->column('sex', __('Sex'));
        $grid->column('dob', __('Dob'));
        $grid->column('color', __('Color'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('fmd', __('Fmd'));
        $grid->column('trader', __('Trader'));
        $grid->column('destination', __('Destination'));
        $grid->column('destination_slaughter_house', __('Destination slaughter house'));
        $grid->column('destination_farm', __('Destination farm'));
        $grid->column('details', __('Details'));
        $grid->column('deleted_at', __('Deleted at'));
        $grid->column('for_sale', __('For sale'));
        $grid->column('price', __('Price'));
        $grid->column('weight', __('Weight'));
        $grid->column('decline_reason', __('Decline reason'));
        $grid->column('origin_latitude', __('Origin latitude'));
        $grid->column('origin_longitude', __('Origin longitude'));
        $grid->column('address', __('Address'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('has_parent', __('Has parent'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('photo', __('Photo'))
        ->display(function ($photo) {
            if ($photo == null) {
                return '<img src="' . url('images/logo.png') . '" style="width: 60px; height: 60px;" />';
            }
            $splits = explode('/', $photo);
            if (count($splits) < 1) {
                return '<img src="' . url('images/logo.png') . '" style="width: 60px; height: 60px;" />';
            }
            $last = $splits[count($splits) - 1];
            if ($last == null) {
                return '<img src="' . url('images/logo.png') . '" style="width: 60px; height: 60px;" />';
            }
            $url = url('storage/images/' . $last);
            if ($url == null) {
                return '<img src="' . url('images/logo.png') . '" style="width: 60px; height: 60px;" />';
            }
            

            return '<img src="' . $url . '" style="width: 60px; height: 60px;" />';
        })
        //->image(, 60, 60)
        //->lightbox(['width' => 60, 'height' => 60])
        ->sortable(); 
        $grid->column('stage', __('Stage'));
        $grid->column('average_milk', __('Average milk'));
        $grid->column('weight_text', __('Weight text'));
        $grid->column('slaughter_house_id', __('Slaughter house id'));
        $grid->column('movement_id', __('Movement id'));
        $grid->column('has_more_info', __('Has more info'));
        $grid->column('was_purchases', __('Was purchases'));
        $grid->column('purchase_date', __('Purchase date'));
        $grid->column('purchase_from', __('Purchase from'));
        $grid->column('purchase_price', __('Purchase price'));
        $grid->column('current_price', __('Current price'));
        $grid->column('weight_at_birth', __('Weight at birth'));
        $grid->column('conception', __('Conception'));
        $grid->column('genetic_donor', __('Genetic donor'));
        $grid->column('group_id', __('Group id'));
        $grid->column('comments', __('Comments'));
        $grid->column('local_id', __('Local id'));
        $grid->column('registered_by_id', __('Registered by id'));
        $grid->column('has_fmd', __('Has fmd'));
        $grid->column('registered_id', __('Registered id'));
        $grid->column('weight_change', __('Weight change'));
        $grid->column('has_produced_before', __('Has produced before'));
        $grid->column('age_at_first_calving', __('Age at first calving'));
        $grid->column('weight_at_first_calving', __('Weight at first calving'));
        $grid->column('has_been_inseminated', __('Has been inseminated'));
        $grid->column('age_at_first_insemination', __('Age at first insemination'));
        $grid->column('weight_at_first_insemination', __('Weight at first insemination'));
        $grid->column('inter_calving_interval', __('Inter calving interval'));
        $grid->column('calf_mortality_rate', __('Calf mortality rate'));
        $grid->column('weight_gain_per_day', __('Weight gain per day'));
        $grid->column('number_of_isms_per_conception', __('Number of isms per conception'));
        $grid->column('is_a_calf', __('Is a calf'));
        $grid->column('is_weaned_off', __('Is weaned off'));
        $grid->column('wean_off_weight', __('Wean off weight'));
        $grid->column('wean_off_age', __('Wean off age'));
        $grid->column('last_profile_update_date', __('Last profile update date'));
        $grid->column('profile_updated', __('Profile updated'));
        $grid->column('birth_position', __('Birth position'));
        $grid->column('age', __('Age'));
        $grid->column('service_type', __('Service type'));
        $grid->column('semen_number', __('Semen number'));
        $grid->column('sire_e_id', __('Sire e id'));
        $grid->column('sire_id', __('Sire id'));

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
        $show = new Show(Animal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('lhc', __('Lhc'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('color', __('Color'));
        $show->field('farm_id', __('Farm id'));
        $show->field('fmd', __('Fmd'));
        $show->field('trader', __('Trader'));
        $show->field('destination', __('Destination'));
        $show->field('destination_slaughter_house', __('Destination slaughter house'));
        $show->field('destination_farm', __('Destination farm'));
        $show->field('details', __('Details'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('for_sale', __('For sale'));
        $show->field('price', __('Price'));
        $show->field('weight', __('Weight'));
        $show->field('decline_reason', __('Decline reason'));
        $show->field('origin_latitude', __('Origin latitude'));
        $show->field('origin_longitude', __('Origin longitude'));
        $show->field('address', __('Address'));
        $show->field('phone_number', __('Phone number'));
        $show->field('has_parent', __('Has parent'));
        $show->field('parent_id', __('Parent id'));
        $show->field('photo', __('Photo'));
        $show->field('stage', __('Stage'));
        $show->field('average_milk', __('Average milk'));
        $show->field('weight_text', __('Weight text'));
        $show->field('slaughter_house_id', __('Slaughter house id'));
        $show->field('movement_id', __('Movement id'));
        $show->field('has_more_info', __('Has more info'));
        $show->field('was_purchases', __('Was purchases'));
        $show->field('purchase_date', __('Purchase date'));
        $show->field('purchase_from', __('Purchase from'));
        $show->field('purchase_price', __('Purchase price'));
        $show->field('current_price', __('Current price'));
        $show->field('weight_at_birth', __('Weight at birth'));
        $show->field('conception', __('Conception'));
        $show->field('genetic_donor', __('Genetic donor'));
        $show->field('group_id', __('Group id'));
        $show->field('comments', __('Comments'));
        $show->field('local_id', __('Local id'));
        $show->field('registered_by_id', __('Registered by id'));
        $show->field('has_fmd', __('Has fmd'));
        $show->field('registered_id', __('Registered id'));
        $show->field('weight_change', __('Weight change'));
        $show->field('has_produced_before', __('Has produced before'));
        $show->field('age_at_first_calving', __('Age at first calving'));
        $show->field('weight_at_first_calving', __('Weight at first calving'));
        $show->field('has_been_inseminated', __('Has been inseminated'));
        $show->field('age_at_first_insemination', __('Age at first insemination'));
        $show->field('weight_at_first_insemination', __('Weight at first insemination'));
        $show->field('inter_calving_interval', __('Inter calving interval'));
        $show->field('calf_mortality_rate', __('Calf mortality rate'));
        $show->field('weight_gain_per_day', __('Weight gain per day'));
        $show->field('number_of_isms_per_conception', __('Number of isms per conception'));
        $show->field('is_a_calf', __('Is a calf'));
        $show->field('is_weaned_off', __('Is weaned off'));
        $show->field('wean_off_weight', __('Wean off weight'));
        $show->field('wean_off_age', __('Wean off age'));
        $show->field('last_profile_update_date', __('Last profile update date'));
        $show->field('profile_updated', __('Profile updated'));
        $show->field('birth_position', __('Birth position'));
        $show->field('age', __('Age'));
        $show->field('service_type', __('Service type'));
        $show->field('semen_number', __('Semen number'));
        $show->field('sire_e_id', __('Sire e id'));
        $show->field('sire_id', __('Sire id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Animal());

        $form->number('administrator_id', __('Administrator id'))->default(1);
        $form->number('district_id', __('District id'))->default(1);
        $form->number('sub_county_id', __('Sub county id'))->default(1);
        $form->number('parish_id', __('Parish id'))->default(1);
        $form->textarea('status', __('Status'));
        $form->text('type', __('Type'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('lhc', __('Lhc'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('color', __('Color'));
        $form->number('farm_id', __('Farm id'))->default(1);
        $form->textarea('fmd', __('Fmd'));
        $form->number('trader', __('Trader'));
        $form->textarea('destination', __('Destination'));
        $form->number('destination_slaughter_house', __('Destination slaughter house'));
        $form->number('destination_farm', __('Destination farm'));
        $form->textarea('details', __('Details'));
        $form->switch('for_sale', __('For sale'));
        $form->number('price', __('Price'));
        $form->decimal('weight', __('Weight'));
        $form->textarea('decline_reason', __('Decline reason'));
        $form->text('origin_latitude', __('Origin latitude'))->default('0.00');
        $form->text('origin_longitude', __('Origin longitude'))->default('0.00');
        $form->text('address', __('Address'));
        $form->text('phone_number', __('Phone number'));
        $form->text('has_parent', __('Has parent'))->default('No');
        $form->number('parent_id', __('Parent id'));
        $form->textarea('photo', __('Photo'));
        $form->text('stage', __('Stage'))->default('Other');
        $form->decimal('average_milk', __('Average milk'));
        $form->text('weight_text', __('Weight text'));
        $form->number('slaughter_house_id', __('Slaughter house id'));
        $form->number('movement_id', __('Movement id'));
        $form->text('has_more_info', __('Has more info'));
        $form->text('was_purchases', __('Was purchases'));
        $form->text('purchase_date', __('Purchase date'));
        $form->text('purchase_from', __('Purchase from'));
        $form->number('purchase_price', __('Purchase price'));
        $form->number('current_price', __('Current price'));
        $form->number('weight_at_birth', __('Weight at birth'));
        $form->text('conception', __('Conception'));
        $form->text('genetic_donor', __('Genetic donor'));
        $form->number('group_id', __('Group id'));
        $form->textarea('comments', __('Comments'));
        $form->text('local_id', __('Local id'));
        $form->number('registered_by_id', __('Registered by id'))->default(1);
        $form->text('has_fmd', __('Has fmd'))->default('No');
        $form->number('registered_id', __('Registered id'))->default(1);
        $form->number('weight_change', __('Weight change'));
        $form->text('has_produced_before', __('Has produced before'))->default('No');
        $form->number('age_at_first_calving', __('Age at first calving'));
        $form->decimal('weight_at_first_calving', __('Weight at first calving'));
        $form->text('has_been_inseminated', __('Has been inseminated'))->default('No');
        $form->number('age_at_first_insemination', __('Age at first insemination'));
        $form->decimal('weight_at_first_insemination', __('Weight at first insemination'));
        $form->number('inter_calving_interval', __('Inter calving interval'));
        $form->decimal('calf_mortality_rate', __('Calf mortality rate'));
        $form->decimal('weight_gain_per_day', __('Weight gain per day'));
        $form->decimal('number_of_isms_per_conception', __('Number of isms per conception'));
        $form->text('is_a_calf', __('Is a calf'))->default('No');
        $form->text('is_weaned_off', __('Is weaned off'))->default('No');
        $form->decimal('wean_off_weight', __('Wean off weight'));
        $form->decimal('wean_off_age', __('Wean off age'));
        $form->datetime('last_profile_update_date', __('Last profile update date'))->default(date('Y-m-d H:i:s'));
        $form->text('profile_updated', __('Profile updated'))->default('No');
        $form->number('birth_position', __('Birth position'));
        $form->number('age', __('Age'));
        $form->text('service_type', __('Service type'));
        $form->textarea('semen_number', __('Semen number'));
        $form->text('sire_e_id', __('Sire e id'));
        $form->text('sire_id', __('Sire id'));

        return $form;
    }
}
