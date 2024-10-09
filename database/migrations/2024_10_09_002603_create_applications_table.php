<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('application_type', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable()->comment('Name of the application form');
            $table->text('description')->nullable()->comment('Description of the application form');
            $table->text('fields')->nullable()->comment('Fields of the application form');
            $table->text('message_1')->nullable()->comment('Message for the first stage');
            $table->text('message_2')->nullable()->comment('Message for the second stage');
            $table->text('message_3')->nullable()->comment('Message for the third stage');
            $table->string('is_paid')->nullable()->default('Yes')->comment('Is the application paid for?');
            $table->text('documents')->nullable()->comment('Documents required for the application');
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'applicant_id')->nullable()->comment('The user who applied');
            $table->foreignIdFor(Administrator::class, 'inspector_1_id')->nullable()->comment('First inspector');
            $table->foreignIdFor(Administrator::class, 'inspector_2_id')->nullable()->comment('Second inspector');
            $table->foreignIdFor(Administrator::class, 'inspector_3_id')->nullable()->comment('Third inspector');
            $table->foreignIdFor(Administrator::class, 'application_type_id')->nullable();
            $table->string('stage')->nullable()->default('Pending')->comment('Current stage of the application');
            $table->string('payment_status')->nullable()->default('Pending')->comment('Payment status');
            $table->string('payment_prn')->nullable()->default('Pending')->comment('Payment PRN');
            $table->string('payment_prn_status')->nullable()->default('Pending')->comment('Payment PRN status');
            $table->text('stage_message')->nullable()->comment('Message regarding the current stage');
            $table->text('applicant_remarks')->nullable()->comment('Remarks from the applicant');
            $table->text('applicant_name')->nullable()->comment('Name of the applicant');
            $table->text('applicant_occupation')->nullable()->comment('Occupation of the applicant');
            $table->text('applicant_phone')->nullable()->comment('Phone number of the applicant');
            $table->text('applicant_id_type')->nullable()->comment('Type of ID provided by the applicant');
            $table->text('applicant_id_number')->nullable()->comment('ID number of the applicant');
            $table->text('applicant_address')->nullable()->comment('Address of the applicant');
            $table->text('applicant_tin')->nullable()->comment('TIN of the applicant');
            $table->text('applicant_region')->nullable()->comment('Region of the applicant');
            $table->text('applicant_district_id')->nullable()->comment('District of the applicant');
            $table->text('applicant_subcounty_id')->nullable()->comment('Subcounty of the applicant');
            $table->text('applicant_county_id')->nullable()->comment('County of the applicant');
            $table->text('applicant_parish_id')->nullable()->comment('Parish of the applicant');
            $table->text('applicant_village')->nullable()->comment('Village of the applicant');
            $table->text('applicant_business_name')->nullable()->comment('Business name of the applicant');
            $table->text('applicant_business_address')->nullable()->comment('Business address of the applicant');
            $table->text('applicant_business_region')->nullable()->comment('Business region of the applicant');
            $table->text('applicant_business_district_id')->nullable()->comment('Business district of the applicant');
            $table->text('applicant_business_subcounty_id')->nullable()->comment('Business subcounty of the applicant');
            $table->text('applicant_business_parish_id')->nullable()->comment('Business parish of the applicant');
            $table->text('applicant_photo')->nullable()->comment('Photo of the applicant');
            $table->text('applicant_proof_of_payment_photo')->nullable()->comment('Proof of payment photo');
            $table->text('applicant_recommendation')->nullable()->comment('Recommendation for the applicant');
            $table->text('applicant_nationality')->nullable()->comment('Nationality of the applicant');
            $table->text('applicant_national_insurance_number')->nullable()->comment('National Insurance Number of the applicant');
            $table->text('applicant_has_been_convicted')->nullable()->comment('Has the applicant been convicted?');
            $table->text('applicant_conviction_details')->nullable()->comment('Details of the applicant\'s conviction');
            $table->text('applicant_conviction_date')->nullable()->comment('Date of the applicant\'s conviction');
            $table->text('type_of_skin')->nullable()->comment('Type of skin');
            $table->text('animal_name')->nullable()->comment('Name of the animal');
            $table->text('animal_species')->nullable()->comment('Species of the animal');
            $table->text('animal_breed')->nullable()->comment('Breed of the animal');
            $table->text('animal_age')->nullable()->comment('Age of the animal');
            $table->text('animal_sex')->nullable()->comment('Sex of the animal');
            $table->text('animal_e_id')->nullable()->comment('E-ID of the animal');
            $table->text('animal_v_id')->nullable()->comment('V-ID of the animal');
            $table->text('animal_color')->nullable()->comment('Color of the animal');
            $table->text('animal_dob')->nullable()->comment('Date of birth of the animal');
            $table->text('animal_weight')->nullable()->comment('Weight of the animal');
            $table->text('animal_quantity')->nullable()->comment('Quantity of the animal');
            $table->text('animal_identification_remarks')->nullable()->comment('Identification remarks of the animal');
            $table->text('package_hs_code')->nullable()->comment('HS code of the package');
            $table->text('package_type')->nullable()->comment('Type of the package');
            $table->text('package_wight')->nullable()->comment('Weight of the package');
            $table->text('package_number')->nullable()->comment('Number of the package');
            $table->text('package_purpose')->nullable()->comment('Purpose of the package');
            $table->text('package_goods_description')->nullable()->comment('Description of the goods in the package');
            $table->text('package_monetry_value')->nullable()->comment('Monetary value of the package');
            $table->text('package_currency')->nullable()->comment('Currency of the package value');
            $table->text('origin_owner_name')->nullable()->comment('Name of the origin owner');
            $table->text('origin_address')->nullable()->comment('Address of the origin');
            $table->text('origin_subscount_id')->nullable()->comment('Subcounty of the origin');
            $table->text('origin_district_id')->nullable()->comment('District of the origin');
            $table->text('destination_country_id')->nullable()->comment('Destination country');
            $table->text('destination_district_id')->nullable()->comment('Destination district');
            $table->text('destination_subcounty_id')->nullable()->comment('Destination subcounty');
            $table->text('destination_address')->nullable()->comment('Destination address');
            $table->text('destination_importer_name')->nullable()->comment('Name of the destination importer');
            $table->text('port_of_exit')->nullable()->comment('Port of exit');
            $table->text('movement_route')->nullable()->comment('Movement route');
            $table->text('movement_transport_means')->nullable()->comment('Transport means for movement');
            $table->text('movement_quarantine')->nullable()->comment('Quarantine details for movement');
            $table->text('has_buyer_licence')->nullable()->comment('Does the buyer have a license?');
            $table->text('buyer_license_number')->nullable()->comment('License number of the buyer');
            $table->text('buyer_license_expiry')->nullable()->comment('License expiry date of the buyer');
            $table->text('buyer_tin')->nullable()->comment('TIN of the buyer');
            $table->text('buyer_nin')->nullable()->comment('NIN of the buyer');
            $table->text('operation_location_of_premise')->nullable()->comment('Location of the operation premise');
            $table->text('operation_floor_space_of_the_store')->nullable()->comment('Floor space of the store');
            $table->text('operation_district_id')->nullable()->comment('District of the operation');
            $table->text('operation_capacity_of_press')->nullable()->comment('Capacity of the press');
            $table->text('operation_sub_country_id')->nullable()->comment('Subcounty of the operation');
            $table->text('operation_director_of_company_of_staffing')->nullable()->comment('Director of company staffing');
            $table->text('feed_type')->nullable()->comment('Type of feed');
            $table->text('feed_quantity')->nullable()->comment('Quantity of feed');
            $table->text('feed_description')->nullable()->comment('Description of feed');
            $table->text('feed_batch_no')->nullable()->comment('Batch number of feed');
            $table->text('invoice_number')->nullable()->comment('Invoice number');
            $table->text('invoice_value')->nullable()->comment('Invoice value');
            $table->text('invoice_currency')->nullable()->comment('Currency of the invoice');
        });
        /*
https://livestock.agriculture.go.ug/client/#!/services/13/apply
        */

        Schema::create('application_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('application_id')->comment('ID of the application');
            $table->text('name')->nullable()->comment('Name of the item');
            $table->text('breed')->nullable()->comment('Breed of the item');
            $table->text('purpose')->nullable()->comment('Purpose of the item');
            $table->text('description')->nullable()->comment('Description of the item');
            $table->text('quantity')->nullable()->comment('Quantity of the item');
            $table->text('package_type')->nullable()->comment('Type of the package');
            $table->text('packages_number')->nullable()->comment('Number of packages');
            $table->text('package_marks')->nullable()->comment('Marks on the package');
            $table->text('category')->nullable()->comment('Category of the item');
            $table->text('package_weight')->nullable()->comment('Weight of the package');
            $table->text('animal_species')->nullable()->comment('Species of the animal');
            $table->text('unit_doze')->nullable()->comment('Unit dose of the item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
