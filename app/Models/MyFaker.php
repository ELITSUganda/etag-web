<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;

class MyFaker  extends Model
{


    /* 
		
approved_by	
detail	
animal_type	
disease_id	
vaccine_id	
medicine_id	
is_batch_import	
time_stamp	
import_file	
description	
temperature	
e_id	
v_id	
status	
disease_text	
short_description	
medicine_text	
medicine_quantity	
medicine_name	
medicine_batch_number	
medicine_supplier	
medicine_manufacturer	
medicine_expiry_date	
medicine_image	
vaccination	 */


    public static function makeEvents($max = 20)
    {
        $faker = Faker::create();
        $u = Admin::user();
        $type = [
            'Disease test',
            'Treatment',
            'Vaccination',
            'Pregnancy check',
            'Milking',
            'Weight check',
            'Temperature check',
            'Stolen',
            'Home slaughter',
            'Death', 'Other'
        ];

        $animals = [];
        $disease_id = [];
        $medicine_id = [];
        $pregnancy_expected_sex = [
            'Male',
            'Heifer',
            'Unknown',
        ];
        $medicine_quantity = [1, 2, 3, 4, 3, 5, 6];
        $pregnancy_fertilization_method = [
            'Artificial insemination',
            'Natural breeding',
        ];
        $disease_test_results = ['Positive', 'Negative'];
        $pregnancy_check_results = ['Pregnant', 'Not Pregnant'];
        $pregnancy_check_method = [
            'Palpation',
            'Ultrasound',
            'Observation',
            'Blood',
            'Urine',
        ];

        foreach (Animal::where([
            'administrator_id' => $u->id
        ])->limit(100)->get() as $key => $animal) {
            $animals[] = $animal->id;
        }

        $females = [];
        foreach (Animal::where([
            'sex' => 'Female',
            'administrator_id' => $u->id
        ])->get() as $k => $a) {
            $females[] = $a->id;
        }


        foreach (Disease::all() as $key => $d) {
            $disease_id[] = $d->id;
        }
        foreach (DrugStockBatch::all() as $key => $d) {
            $medicine_id[] = $d->id;
        }
        for ($i = 0; $i < $max; $i++) {

            echo $i . "<hr>";
            shuffle($disease_id);
            shuffle($disease_test_results);
            shuffle($medicine_quantity);
            shuffle($animals);
            shuffle($animals);
            shuffle($pregnancy_check_method);
            shuffle($pregnancy_check_results);
            shuffle($pregnancy_expected_sex);
            shuffle($pregnancy_fertilization_method);
            shuffle($females); 
            shuffle($medicine_id); 
            shuffle($type);
            $eve = new Event();
            $eve->created_at = $faker->dateTimeBetween('-1 year');

            $eve->type = $type[0];
            if ($eve->type == 'Milking') {
                $eve->animal_id = $females[0];
            } else {
                $eve->animal_id = $animals[0];
            }
            $eve->disease_id = $disease_id[0];
            $eve->medicine_quantity = $medicine_quantity[0];
            $eve->disease_test_results = $disease_test_results[0];
            $eve->pregnancy_check_method = $pregnancy_check_method[0];
            $eve->pregnancy_check_results = $pregnancy_check_results[0];
            $eve->pregnancy_fertilization_method = $pregnancy_fertilization_method[0];
            $eve->pregnancy_expected_sex = $pregnancy_expected_sex[0];
            $eve->medicine_id = $medicine_id[0];
            $eve->detail = $faker->sentence(200);
            $eve->milk = rand(1, 15); 
            $eve->temperature = rand(35, 65);
            $eve->weight = rand(100, 800);

            $eve->save();
        }
        //

    }
    public static function makeAnimals($max = 20)
    {

        die("done Generating animals data");
        ini_set('memory_limit', '-1');
        set_time_limit('-1');

        $admins = [];
        $faker = Faker::create();
        $sub_counties = [];

        $u = Admin::user();

        $breed = [
            'Ankole',
            'Ankole',
            'Ankole',
            'Short horn zebu',
            'Holstein',
            'Other',
        ];

        $type = [
            'Cattle',
            'Cattle',
            'Cattle',
            'Cattle',
            'Cattle',
            'Goat',
            'Goat',
            'Goat',
            'Sheep',
        ];
        $sex = [
            'Female',
            'Female',
            'Female',
            'Male',
        ];
        $farm = Farm::where([
            'administrator_id' => $u->id
        ])->first();
        $i = 0;
        foreach (Location::get_sub_counties() as $v) {
            $i++;
            shuffle($type);
            shuffle($sex);
            $animal = new Animal();
            $animal->administrator_id = $u->id;
            $animal->farm_id = $farm->id;
            $animal->type = $type[0];
            $animal->breed = $breed[0];
            $animal->v_id = rand(1000, 10000) . $i;
            $animal->e_id = "100000" . $animal->v_id;
            $animal->sex =  $sex[0];
            $animal->dob =  $faker->dateTimeBetween('-1 year');
            $animal->fmd =  $faker->dateTimeBetween('-1 year');
            $animal->details =  $faker->sentence(200);
            $animal->photo =  'images/' . rand(1, 10) . '.jpeg';
            $animal->save();
        }
        die("anjane");
        $bools = [0, 1];
        $sex = ['Male', 'Female'];
        for ($i = 0; $i < $max; $i++) {
            $c = new Animal();


            die("1000");
            $c->save();
        }
    }


    public static function make_cases($max = 20)
    {

        ini_set('memory_limit', '-1');
        set_time_limit('-1');

        $admins = [];
        $f = Faker::create();
        $statuses = Utils::case_statuses();

        foreach (Administrator::all() as $key => $u) {
            $admins[] = $u->id;
        }
        $sub_counties = [];

        foreach (Location::get_sub_counties() as $v) {
            $sub_counties[] = $v->id;
        }


        $cats = Utils::case_categpries();
        $statuses = Utils::case_statuses();
        $bools = [0, 1];
        $sex = ['Male', 'Female'];
        for ($i = 0; $i < $max; $i++) {
            $c = new CaseModel();
            shuffle($admins);
            shuffle($cats);
            shuffle($sub_counties);
            shuffle($statuses);
            shuffle($bools);
            shuffle($sex);
            shuffle($bools);

            $c->created_at = $f->dateTimeBetween('-1 year');
            $c->administrator_id = $admins[3];
            $c->case_category = $cats[2];
            $c->sub_county = $sub_counties[4];
            $c->status = $statuses[2];
            $c->title = $f->sentence();
            $c->description = $f->sentence(500);
            $c->response = $f->sentence(300);
            $c->phone_number_1 = $f->phoneNumber();
            $c->phone_number_2 = $f->phoneNumber();
            $c->village = $f->word();
            $c->request = $f->sentence(200);
            $c->address = $f->sentence(45);
            $c->applicant_name = $f->name();
            $c->latitude = '0.174917';
            $c->longitude = '30.077517';
            $c->is_court = $bools[1];
            $c->sex = $sex[1];
            $c->is_authority = $bools[1];
            $c->save();
        }

        dd(":=-=:");
    }


    public static function make_users($max = 20)
    {

        $f = Faker::create();

        /*         foreach (Administrator::all() as $key => $u) {
            $u->phone_number_1 =   $f->phoneNumber(2);
            $u->phone_number_2 =   $f->phoneNumber(3);
            $u->avatar = rand(1, 30) . ".jpg";
            $u->save();
        }
        dd("done"); */

        $sex = ['Male', 'Female'];
        $sub_counties = [];
        foreach (Location::get_sub_counties() as $v) {
            $sub_counties[] = $v->id;
        }


        for ($i = 0; $i < $max; $i++) {
            $u = new Administrator();
            $u->email = $f->email();
            $u->username = $u->email;
            $u->password = password_hash('4321', PASSWORD_DEFAULT);
            $u->avatar = rand(1, 20) . ".jpg";
            $u->created_at = $f->dateTimeBetween('-8 year');
            $u->date_of_birth = $f->dateTimeBetween('-35 year', '-18 year',);
            $u->first_name =   $f->firstName(5);
            $u->middle_name =   $f->firstName(6);
            $u->last_name =   $f->firstName(2);
            shuffle($sex);
            $u->sex =   $sex[0];
            $u->phone_number_1 =   $f->phoneNumber(2);
            $u->phone_number_2 =   $f->phoneNumber(3);
            shuffle($sub_counties);
            $u->sub_county_id =   $sub_counties[0];
            $u->address =   $f->address(3);
            $u->save();
        }
    }
}
