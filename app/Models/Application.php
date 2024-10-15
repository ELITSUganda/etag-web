<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    //belongs ApplicationType
    public function application_type()
    {
        // dd($this->application_type_id);
        return $this->belongsTo(ApplicationType::class, 'application_type_id');
    }



    public function get_qr_code()
    {
        $exists = false;
        if ($this->code != null) {
            if (strlen($this->code) > 3) {
                $full_path = public_path($this->qr_code);
                if (file_exists($full_path)) {
                    $exists = true;
                }
            }
        }
        if ($exists) {
            return $this->qr_code;
        }
        //generate alphanumeric code

        /* abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ */
        $code = (substr(str_shuffle("0123456789"), 0, 8));
        $url = url("") . "/verify/" . $code;
        $path = Utils::generate_qrcode($url);
        $public_path = public_path($path);
        if (file_exists($public_path)) {
            $this->code = $code;
            $this->qr_code = $path;
            $this->save();
        } else {
            $path = null;
        }
        return $path;
    }
    public function get_content()
    {
        $content = $this->application_type->message_1;

        $content = str_replace('color: rgb(199, 37, 78);', '', $content);
        $content = str_replace('background-color: rgb(249, 242, 244);', '', $content);

        //replace [NAME_OF_APPLICANT] with the applicant name in content
        $content = str_replace('[NAME_OF_APPLICANT]', strtoupper($this->applicant_name), $content);
        $content = str_replace('[APPLICANT_ADDRESS]', ($this->applicant_address), $content);
        $content = str_replace('[COUNTRY_OF_ORIGIN]', strtoupper($this->origin_country), $content);
        $content = str_replace('[SINGLE_ANIMAL_TABLE]', $this->single_animal_table(), $content);

        return $content;
    }

    public function single_animal_table()
    {/* 
        Name Breed Color Age (Years) Sex Date of Birth MicroChip No
        */
        return <<<EOT
        <table class="my-table" style="width: 100%;">
            <thead>
                <tr style="">
                    <th class="text-center" >Name</th>
                    <th class="text-center" >Breed</th>
                    <th class="text-center" >Color</th>
                    <th class="text-center" >Age (Years)</th>
                    <th class="text-center" >Sex</th>
                    <th class="text-center" >Date of Birth</th>
                    <th class="text-center" >MicroChip No</th>
                </tr>
            <tbody>
                <tr>
                    <td>{$this->animal_name}</td>
                    <td>{$this->animal_breed}</td>
                    <td>{$this->animal_color}</td>
                    <td style="text-align: center;">{$this->animal_age}</td>
                    <td style="text-align: center;">{$this->animal_sex}</td>
                    <td style="text-align: center;">{$this->animal_dob}</td>
                    <td>{$this->animal_e_id}</td>
                </tr>
            </tbody>
        </table>
        EOT;
    }

    /* 
    
    
id
created_at
updated_at
applicant_id
The user who applied
inspector_1_id
First inspector
inspector_2_id
Second inspector
inspector_3_id
Third inspector
application_type_id
stage
Current stage of the application
payment_status
Payment status
payment_prn
Payment PRN
payment_prn_status
Payment PRN status
stage_message
Message regarding the current stage
applicant_remarks
Remarks from the applicant
applicant_name
Name of the applicant
applicant_occupation
Occupation of the applicant
applicant_phone
Phone number of the applicant
applicant_id_type
Type of ID provided by the applicant
applicant_id_number
ID number of the applicant

Address of the applicant
applicant_tin
TIN of the applicant
applicant_region
Region of the applicant
applicant_district_id
District of the applicant
applicant_subcounty_id
Subcounty of the applicant
applicant_county_id
County of the applicant
applicant_parish_id
Parish of the applicant
applicant_village
Village of the applicant
applicant_business_name
Business name of the applicant
applicant_business_address
Business address of the applicant
applicant_business_region
Business region of the applicant
applicant_business_district_id
Business district of the applicant
applicant_business_subcounty_id
Business subcounty of the applicant
applicant_business_parish_id
Business parish of the applicant
applicant_photo
Photo of the applicant
applicant_proof_of_payment_photo
Proof of payment photo
applicant_recommendation
Recommendation for the applicant
applicant_nationality
Nationality of the applicant
applicant_national_insurance_number
National Insurance Number of the applicant
applicant_has_been_convicted
Has the applicant been convicted?
applicant_conviction_details
Details of the applicant's conviction
applicant_conviction_date
Date of the applicant's conviction
type_of_skin
Type of skin
animal_name
Name of the animal
animal_species
Species of the animal
animal_breed
Breed of the animal
animal_age
Age of the animal
animal_sex
Sex of the animal
animal_e_id
E-ID of the animal
animal_v_id
V-ID of the animal
animal_color
Color of the animal
animal_dob
Date of birth of the animal
animal_weight
Weight of the animal
animal_quantity
Quantity of the animal
animal_identification_remarks
Identification remarks of the animal
package_hs_code
HS code of the package
package_type
Type of the package
package_wight
Weight of the package
package_number
Number of the package
package_purpose
Purpose of the package
package_goods_description
Description of the goods in the package
package_monetry_value
Monetary value of the package
package_currency
Currency of the package value
origin_owner_name
Name of the origin owner
origin_address
Address of the origin
origin_subscount_id
Subcounty of the origin
origin_district_id
District of the origin
destination_country_id
Destination country
destination_district_id
Destination district
destination_subcounty_id
Destination subcounty
destination_address
Destination address
destination_importer_name
Name of the destination importer
port_of_exit
Port of exit
movement_route
Movement route
movement_transport_means
Transport means for movement
movement_quarantine
Quarantine details for movement
has_buyer_licence
Does the buyer have a license?
buyer_license_number
License number of the buyer
buyer_license_expiry
License expiry date of the buyer
buyer_tin
TIN of the buyer
buyer_nin
NIN of the buyer
operation_location_of_premise
Location of the operation premise
operation_floor_space_of_the_store
Floor space of the store
operation_district_id
District of the operation
operation_capacity_of_press
Capacity of the press
operation_sub_country_id
Subcounty of the operation
operation_director_of_company_of_staffing
Director of company staffing
feed_type
Type of feed
feed_quantity
Quantity of feed
feed_description
Description of feed
feed_batch_no
Batch number of feed
invoice_number
Invoice number
invoice_value
Invoice value
invoice_currency
Currency of the invoice
file_inspection_report
Inspection report
file_objection_letter
Attach no objection letter/import permit from impo…
file_laboratory_results
Laboratory results
file_invoice
Invoice and other supporting documents

Edit Edit
Copy Copy

*/

    /*     //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            $application = Application::process_application($application);
        });

        //updating
        static::updating(function ($application) {
            $application = Application::process_application($application);
        });
    }


    public static function process_application($app)
    {
        $subcounty = Location::find($app->origin_subscount_id);
        if ($subcounty != null) {
            $app->origin_district_id = $subcounty->name;
        }
        return $app;
    } */
}
