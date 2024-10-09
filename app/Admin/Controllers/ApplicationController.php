<?php

namespace App\Admin\Controllers;

use App\Models\Application;
use App\Models\ApplicationType;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ApplicationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Application Forms';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Application());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('applicant_id', __('Applicant id'));
        $grid->column('inspector_1_id', __('Inspector 1 id'));
        $grid->column('inspector_2_id', __('Inspector 2 id'));
        $grid->column('inspector_3_id', __('Inspector 3 id'));
        $grid->column('application_type_id', __('Application type id'));
        $grid->column('stage', __('Stage'));
        $grid->column('payment_status', __('Payment status'));
        $grid->column('payment_prn', __('Payment prn'));
        $grid->column('payment_prn_status', __('Payment prn status'));
        $grid->column('stage_message', __('Stage message'));
        $grid->column('applicant_remarks', __('Applicant remarks'));
        $grid->column('applicant_name', __('Applicant name'));
        $grid->column('applicant_occupation', __('Applicant occupation'));
        $grid->column('applicant_phone', __('Applicant phone'));
        $grid->column('applicant_id_type', __('Applicant id type'));
        $grid->column('applicant_id_number', __('Applicant id number'));
        $grid->column('applicant_address', __('Applicant address'));
        $grid->column('applicant_tin', __('Applicant tin'));
        $grid->column('applicant_region', __('Applicant region'));
        $grid->column('applicant_district_id', __('Applicant district id'));
        $grid->column('applicant_subcounty_id', __('Applicant subcounty id'));
        $grid->column('applicant_county_id', __('Applicant county id'));
        $grid->column('applicant_parish_id', __('Applicant parish id'));
        $grid->column('applicant_village', __('Applicant village'));
        $grid->column('applicant_business_name', __('Applicant business name'));
        $grid->column('applicant_business_address', __('Applicant business address'));
        $grid->column('applicant_business_region', __('Applicant business region'));
        $grid->column('applicant_business_district_id', __('Applicant business district id'));
        $grid->column('applicant_business_subcounty_id', __('Applicant business subcounty id'));
        $grid->column('applicant_business_parish_id', __('Applicant business parish id'));
        $grid->column('applicant_photo', __('Applicant photo'));
        $grid->column('applicant_proof_of_payment_photo', __('Applicant proof of payment photo'));
        $grid->column('applicant_recommendation', __('Applicant recommendation'));
        $grid->column('applicant_nationality', __('Applicant nationality'));
        $grid->column('applicant_national_insurance_number', __('Applicant national insurance number'));
        $grid->column('applicant_has_been_convicted', __('Applicant has been convicted'));
        $grid->column('applicant_conviction_details', __('Applicant conviction details'));
        $grid->column('applicant_conviction_date', __('Applicant conviction date'));
        $grid->column('type_of_skin', __('Type of skin'));
        $grid->column('animal_name', __('Animal name'));
        $grid->column('animal_species', __('Animal species'));
        $grid->column('animal_breed', __('Animal breed'));
        $grid->column('animal_age', __('Animal age'));
        $grid->column('animal_sex', __('Animal sex'));
        $grid->column('animal_e_id', __('Animal e id'));
        $grid->column('animal_v_id', __('Animal v id'));
        $grid->column('animal_color', __('Animal color'));
        $grid->column('animal_dob', __('Animal dob'));
        $grid->column('animal_weight', __('Animal weight'));
        $grid->column('animal_quantity', __('Animal quantity'));
        $grid->column('animal_identification_remarks', __('Animal identification remarks'));
        $grid->column('package_hs_code', __('Package hs code'));
        $grid->column('package_type', __('Package type'));
        $grid->column('package_wight', __('Package wight'));
        $grid->column('package_number', __('Package number'));
        $grid->column('package_purpose', __('Package purpose'));
        $grid->column('package_goods_description', __('Package goods description'));
        $grid->column('package_monetry_value', __('Package monetry value'));
        $grid->column('package_currency', __('Package currency'));
        $grid->column('origin_owner_name', __('Origin owner name'));
        $grid->column('origin_address', __('Origin address'));
        $grid->column('origin_subscount_id', __('Origin subscount id'));
        $grid->column('origin_district_id', __('Origin district id'));
        $grid->column('destination_country_id', __('Destination country id'));
        $grid->column('destination_district_id', __('Destination district id'));
        $grid->column('destination_subcounty_id', __('Destination subcounty id'));
        $grid->column('destination_address', __('Destination address'));
        $grid->column('destination_importer_name', __('Destination importer name'));
        $grid->column('port_of_exit', __('Port of exit'));
        $grid->column('movement_route', __('Movement route'));
        $grid->column('movement_transport_means', __('Movement transport means'));
        $grid->column('movement_quarantine', __('Movement quarantine'));
        $grid->column('has_buyer_licence', __('Has buyer licence'));
        $grid->column('buyer_license_number', __('Buyer license number'));
        $grid->column('buyer_license_expiry', __('Buyer license expiry'));
        $grid->column('buyer_tin', __('Buyer tin'));
        $grid->column('buyer_nin', __('Buyer nin'));
        $grid->column('operation_location_of_premise', __('Operation location of premise'));
        $grid->column('operation_floor_space_of_the_store', __('Operation floor space of the store'));
        $grid->column('operation_district_id', __('Operation district id'));
        $grid->column('operation_capacity_of_press', __('Operation capacity of press'));
        $grid->column('operation_sub_country_id', __('Operation sub country id'));
        $grid->column('operation_director_of_company_of_staffing', __('Operation director of company of staffing'));
        $grid->column('feed_type', __('Feed type'));
        $grid->column('feed_quantity', __('Feed quantity'));
        $grid->column('feed_description', __('Feed description'));
        $grid->column('feed_batch_no', __('Feed batch no'));
        $grid->column('invoice_number', __('Invoice number'));
        $grid->column('invoice_value', __('Invoice value'));
        $grid->column('invoice_currency', __('Invoice currency'));

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
        $show = new Show(Application::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('applicant_id', __('Applicant id'));
        $show->field('inspector_1_id', __('Inspector 1 id'));
        $show->field('inspector_2_id', __('Inspector 2 id'));
        $show->field('inspector_3_id', __('Inspector 3 id'));
        $show->field('application_type_id', __('Application type id'));
        $show->field('stage', __('Stage'));
        $show->field('payment_status', __('Payment status'));
        $show->field('payment_prn', __('Payment prn'));
        $show->field('payment_prn_status', __('Payment prn status'));
        $show->field('stage_message', __('Stage message'));
        $show->field('applicant_remarks', __('Applicant remarks'));
        $show->field('applicant_name', __('Applicant name'));
        $show->field('applicant_occupation', __('Applicant occupation'));
        $show->field('applicant_phone', __('Applicant phone'));
        $show->field('applicant_id_type', __('Applicant id type'));
        $show->field('applicant_id_number', __('Applicant id number'));
        $show->field('applicant_address', __('Applicant address'));
        $show->field('applicant_tin', __('Applicant tin'));
        $show->field('applicant_region', __('Applicant region'));
        $show->field('applicant_district_id', __('Applicant district id'));
        $show->field('applicant_subcounty_id', __('Applicant subcounty id'));
        $show->field('applicant_county_id', __('Applicant county id'));
        $show->field('applicant_parish_id', __('Applicant parish id'));
        $show->field('applicant_village', __('Applicant village'));
        $show->field('applicant_business_name', __('Applicant business name'));
        $show->field('applicant_business_address', __('Applicant business address'));
        $show->field('applicant_business_region', __('Applicant business region'));
        $show->field('applicant_business_district_id', __('Applicant business district id'));
        $show->field('applicant_business_subcounty_id', __('Applicant business subcounty id'));
        $show->field('applicant_business_parish_id', __('Applicant business parish id'));
        $show->field('applicant_photo', __('Applicant photo'));
        $show->field('applicant_proof_of_payment_photo', __('Applicant proof of payment photo'));
        $show->field('applicant_recommendation', __('Applicant recommendation'));
        $show->field('applicant_nationality', __('Applicant nationality'));
        $show->field('applicant_national_insurance_number', __('Applicant national insurance number'));
        $show->field('applicant_has_been_convicted', __('Applicant has been convicted'));
        $show->field('applicant_conviction_details', __('Applicant conviction details'));
        $show->field('applicant_conviction_date', __('Applicant conviction date'));
        $show->field('type_of_skin', __('Type of skin'));
        $show->field('animal_name', __('Animal name'));
        $show->field('animal_species', __('Animal species'));
        $show->field('animal_breed', __('Animal breed'));
        $show->field('animal_age', __('Animal age'));
        $show->field('animal_sex', __('Animal sex'));
        $show->field('animal_e_id', __('Animal e id'));
        $show->field('animal_v_id', __('Animal v id'));
        $show->field('animal_color', __('Animal color'));
        $show->field('animal_dob', __('Animal dob'));
        $show->field('animal_weight', __('Animal weight'));
        $show->field('animal_quantity', __('Animal quantity'));
        $show->field('animal_identification_remarks', __('Animal identification remarks'));
        $show->field('package_hs_code', __('Package hs code'));
        $show->field('package_type', __('Package type'));
        $show->field('package_wight', __('Package wight'));
        $show->field('package_number', __('Package number'));
        $show->field('package_purpose', __('Package purpose'));
        $show->field('package_goods_description', __('Package goods description'));
        $show->field('package_monetry_value', __('Package monetry value'));
        $show->field('package_currency', __('Package currency'));
        $show->field('origin_owner_name', __('Origin owner name'));
        $show->field('origin_address', __('Origin address'));
        $show->field('origin_subscount_id', __('Origin subscount id'));
        $show->field('origin_district_id', __('Origin district id'));
        $show->field('destination_country_id', __('Destination country id'));
        $show->field('destination_district_id', __('Destination district id'));
        $show->field('destination_subcounty_id', __('Destination subcounty id'));
        $show->field('destination_address', __('Destination address'));
        $show->field('destination_importer_name', __('Destination importer name'));
        $show->field('port_of_exit', __('Port of exit'));
        $show->field('movement_route', __('Movement route'));
        $show->field('movement_transport_means', __('Movement transport means'));
        $show->field('movement_quarantine', __('Movement quarantine'));
        $show->field('has_buyer_licence', __('Has buyer licence'));
        $show->field('buyer_license_number', __('Buyer license number'));
        $show->field('buyer_license_expiry', __('Buyer license expiry'));
        $show->field('buyer_tin', __('Buyer tin'));
        $show->field('buyer_nin', __('Buyer nin'));
        $show->field('operation_location_of_premise', __('Operation location of premise'));
        $show->field('operation_floor_space_of_the_store', __('Operation floor space of the store'));
        $show->field('operation_district_id', __('Operation district id'));
        $show->field('operation_capacity_of_press', __('Operation capacity of press'));
        $show->field('operation_sub_country_id', __('Operation sub country id'));
        $show->field('operation_director_of_company_of_staffing', __('Operation director of company of staffing'));
        $show->field('feed_type', __('Feed type'));
        $show->field('feed_quantity', __('Feed quantity'));
        $show->field('feed_description', __('Feed description'));
        $show->field('feed_batch_no', __('Feed batch no'));
        $show->field('invoice_number', __('Invoice number'));
        $show->field('invoice_value', __('Invoice value'));
        $show->field('invoice_currency', __('Invoice currency'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Application());
        $u = Admin::user();
        $form_type = null;
        if (isset($_GET['create_form_for'])) {
            //create_form_for set session
            $form_type = ApplicationType::find($_GET['create_form_for']);
            if ($form_type != null) {
                session(['create_form_for' => $form_type->id]);
            }
        }

        if ($form_type == null) {
            $form_type = ApplicationType::find(session('create_form_for'));
        }

        if ($form_type == null) {
            return admin_error('Error', 'Invalid form type');
        }

        if ($form->isCreating()) {
            $form->hidden('applicant_id', __('Applicant id'))->default($u->id);
        }

        $form->divider('Applicant Details');
        $form->text('applicant_name', __('Applicant name'))->rules('required')->required();
        $form->text('applicant_phone', __('Applicant phone number'))->rules('required')->required();
        $form->text('applicant_tin', __('Applicant TIN'));
        $form->text('applicant_address', __('Applicant Address'))->rules('required')->required();

        if (in_array($form_type->id, [1])) {
            $form->divider('Animal Information');
            $form->radio('animal_species', __('Animal species'))->options([
                'Bovine/Cattle' => 'Bovine/Cattle',
                'Caprine/Goat' => 'Caprine/Goat',
                'Ovine/Sheep' => 'Ovine/Sheep',
                'Swine/Pig' => 'Swine/Pig',
            ])->rules('required')->required();
            //animal_age
            $form->decimal('animal_age', __('Animal age (in Months)'))->rules('required')->required();
            //animal_breed
            $form->text('animal_breed', __('Animal breed'))->rules('required')->required();
            //animal_weight
            $form->decimal('animal_weight', __('Animal weight (in Kgs)'))->rules('required')->required();
            //animal_sex
            $form->radio('animal_sex', __('Animal\'s sex'))->options(['Male', 'Female'])->rules('required')->required();
            //animal_color
            $form->text('animal_color', __('Animal color'))->rules('required')->required();
            //animal_quantity
            $form->decimal('animal_quantity', __('Animal quantity'))->rules('required')->required();
            //animal_identification_remarks
            $form->textarea('animal_identification_remarks', __('Animal identification remarks'))->rules('required')->required();
            //animal_e_id
            $form->text('animal_e_id', __('Animal TAG Electronic ID'))->rules('required')->required();
            //animal_v_id
            $form->text('animal_v_id', __('Animal TAG Visual ID'))->rules('required')->required();
        }

        if (in_array($form_type->id, [1])) {
            $form->divider('Package Information');
            $form->text('package_hs_code', __('HS code'));
            $form->radio('package_type', __('Package type'))->options([
                'Crate' => 'Crate',
                'Vehicle' => 'Vehicle',
            ])->rules('required')->required();
            //package_wight
            $form->decimal('package_wight', __('Package weight (in Kgs)'))->rules('required')->required();
            //package_number
            $form->text('package_number', __('Number of packages'));
            //package_purpose
            $form->text('package_purpose', __(':Purpose'));
            //package_goods_description
            $form->text('package_goods_description', __('Goods description'));
            //package_monetry_value
            $form->decimal('package_monetry_value', __('Monetary value'))->rules('required')->required();
            //package_currency
            $form->text('package_currency', __('Currency'))->rules('required')->required();
        }

        //
        if (in_array($form_type->id, [1])) {
            $form->divider('Origin Information');
            //Name of owner/consigner 
            $form->text('origin_owner_name', __('Name of owner/consigner'))->required();
            $form->text('origin_address', __('Origin address'));

            $form->select('origin_subscount_id', 'Sub-county')->options(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id =>  $a->name_text];
                }
            })
                ->ajax(url(
                    '/api/sub-counties'
                ));

            /* 

         
*/
        }
        if (in_array($form_type->id, [1])) {
            $form->divider('Destination Information');
            $form->select('destination_country_id')
                ->help('Nationality of the suspect')
                ->required()
                ->options(Utils::COUNTRIES())->rules('required');

            //destination_address
            $form->text('destination_address', __('Destination address'))->rules('required')->required();
            $form->text('destination_importer_name', __('Name of consignee/importer'))->rules('required')->required();
            //Means of transport 
            $form->text('movement_transport_means', __('Means of transport'))->rules('required')->required();
            //Port of exit
            $form->text('port_of_exit', __('Port of exit'))->rules('required')->required();
            $form->text('movement_route', __('Movement route'));
        }

        if (in_array($form_type->id, [1])) {
            $form->divider('Attachments');
            $form->file('file_inspection_report', __('Inspection report'))->uniqueName();
            $form->file('file_objection_letter', __('Attach no objection letter/import permit from importing country'))->uniqueName();
            $form->file('file_laboratory_results', __('Laboratory results'))->uniqueName();
            $form->file('file_invoice', __('Invoice and other supporting documents'))->uniqueName();
        }
        /* 
             $table->string('file_inspection_report')->nullable()->comment('Inspection report');
            $table->string('file_objection_letter')->nullable()->comment('Attach no objection letter/import permit from importing country');
            $table->string('file_laboratory_results')->nullable()->comment('Laboratory results');
            $table->string('file_invoice')->nullable()->comment('Invoice and other supporting documents');
        */


        return $form;


        /* 
            $form->textarea('animal_name', __('Animal name'));
            $form->textarea('animal_dob', __('Animal dob')); 

*/
        $form->textarea('applicant_occupation', __('Applicant occupation'));
        $form->number('inspector_1_id', __('Inspector 1 id'));
        $form->number('inspector_2_id', __('Inspector 2 id'));
        $form->number('inspector_3_id', __('Inspector 3 id'));
        $form->number('application_type_id', __('Application type id'));
        $form->text('stage', __('Stage'))->default('Pending');
        $form->text('payment_status', __('Payment status'))->default('Pending');
        $form->text('payment_prn', __('Payment prn'))->default('Pending');
        $form->text('payment_prn_status', __('Payment prn status'))->default('Pending');
        $form->textarea('stage_message', __('Stage message'));
        $form->textarea('applicant_remarks', __('Applicant remarks'));


        $form->textarea('applicant_id_type', __('Applicant id type'));
        $form->textarea('applicant_id_number', __('Applicant id number'));
        $form->textarea('applicant_region', __('Applicant region'));
        $form->textarea('applicant_district_id', __('Applicant district id'));
        $form->textarea('applicant_subcounty_id', __('Applicant subcounty id'));
        $form->textarea('applicant_county_id', __('Applicant county id'));
        $form->textarea('applicant_parish_id', __('Applicant parish id'));
        $form->textarea('applicant_village', __('Applicant village'));
        $form->textarea('applicant_business_name', __('Applicant business name'));
        $form->textarea('applicant_business_address', __('Applicant business address'));
        $form->textarea('applicant_business_region', __('Applicant business region'));
        $form->textarea('applicant_business_district_id', __('Applicant business district id'));
        $form->textarea('applicant_business_subcounty_id', __('Applicant business subcounty id'));
        $form->textarea('applicant_business_parish_id', __('Applicant business parish id'));
        $form->textarea('applicant_photo', __('Applicant photo'));
        $form->textarea('applicant_proof_of_payment_photo', __('Applicant proof of payment photo'));
        $form->textarea('applicant_recommendation', __('Applicant recommendation'));
        $form->textarea('applicant_nationality', __('Applicant nationality'));
        $form->textarea('applicant_national_insurance_number', __('Applicant national insurance number'));
        $form->textarea('applicant_has_been_convicted', __('Applicant has been convicted'));
        $form->textarea('applicant_conviction_details', __('Applicant conviction details'));
        $form->textarea('applicant_conviction_date', __('Applicant conviction date'));
        $form->textarea('type_of_skin', __('Type of skin'));





        $form->textarea('movement_transport_means', __('Movement transport means'));
        $form->textarea('movement_quarantine', __('Movement quarantine'));
        $form->textarea('has_buyer_licence', __('Has buyer licence'));
        $form->textarea('buyer_license_number', __('Buyer license number'));
        $form->textarea('buyer_license_expiry', __('Buyer license expiry'));
        $form->textarea('buyer_tin', __('Buyer tin'));
        $form->textarea('buyer_nin', __('Buyer nin'));
        $form->textarea('operation_location_of_premise', __('Operation location of premise'));
        $form->textarea('operation_floor_space_of_the_store', __('Operation floor space of the store'));
        $form->textarea('operation_district_id', __('Operation district id'));
        $form->textarea('operation_capacity_of_press', __('Operation capacity of press'));
        $form->textarea('operation_sub_country_id', __('Operation sub country id'));
        $form->textarea('operation_director_of_company_of_staffing', __('Operation director of company of staffing'));
        $form->textarea('feed_type', __('Feed type'));
        $form->textarea('feed_quantity', __('Feed quantity'));
        $form->textarea('feed_description', __('Feed description'));
        $form->textarea('feed_batch_no', __('Feed batch no'));
        $form->textarea('invoice_number', __('Invoice number'));
        $form->textarea('invoice_value', __('Invoice value'));
        $form->textarea('invoice_currency', __('Invoice currency'));

        return $form;
    }
}
