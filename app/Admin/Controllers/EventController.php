<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\District;
use App\Models\DrugDosage;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Medicine;
use App\Models\SubCounty;
use App\Models\User;
use App\Models\Utils;
use App\Models\Vaccine;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Faker\Factory as Faker;

class EventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Events';

    protected function title()
    {
        $segments = request()->segments();
        if (in_array('events-production', $segments)) {
            return "Production events";
        } else if (in_array('events-sanitary', $segments)) {
            return "Sanitary events";
        } else {
            return "Events";
        }
    }

    /**
     * Make a grid builder.
     

     * @return Grid
     */
    protected function grid()
    {

        /*         $data = Event::where([
            'type' => 'Milking'
        ])->whereBetween('created_at',['2023-05-11','2023-05-12'])->get();


        $ans = [];
        foreach ($data as $key => $d) {
            if(in_array($d->animal_id,$ans)){
                $d->delete();
                continue;
            }
            $ans[] = $d->animal_id;
        }
        dd($ans); */


        //Utils::display_alert_message();

        $grid = new Grid(new Event());
        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->export(function ($export) {
            $date_time = date('Y-m-d_H-i-s');
            $export->filename('Events-' . $date_time);
            $export->only([
                'created_at',
                'animal_id',
                'type',
                'disease_id',
                'inseminator',
                'inseminator_1',
                'inseminator_2',
                'inseminator_3',
                'medicine_id',
                'description',
            ]);
            $export->originalValue([
                'created_at',
                'animal_id',
                'type',
                'disease_id',
                'inseminator',
                'inseminator_1',
                'inseminator_2',
                'inseminator_3',
                'medicine_id',
                'description',
            ]);

            $export->originalValue(['id', 'created_at', 'type', 'description', 'detail']);
            $export->column('description', function ($value) {
                return strip_tags($value);
            });
            $export->column('detail', function ($value) {
                return strip_tags($value);
            });
        });

        $segments = request()->segments();
        $isSanitary = false;
        if (in_array('events-production', $segments)) {
            $isSanitary = false;
        } else if (in_array('events-sanitary', $segments)) {
            $isSanitary = true;
        } else {
            $isSanitary = false;
        }



        $grid->disableBatchActions();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });


        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();
        $dis = null;
        if ($r != null) {
            $dis = Location::find($r->type_id);
        }

        if ($dis != null) {
            $grid->model()->where('district_id', '=', $dis->id);
        }
        if ($u->isRole('administrator')) {
        } else if (Admin::user()->isRole('farmer')) {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                $actions->disableEdit();
            });
            //$grid->disableCreateButton();
        } else {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                $actions->disableEdit();
            });
        }
        $district = $dis ? $dis->id : null;
        $date_title = 'Date';


        $segments = request()->segments();
        if (in_array('events-production', $segments)) {
            $grid->setTitle("Production events");
            $types = [
                'Ownership Transfer',
                'Milking',
                'Temperature check',
                'Weight check',
                'Service',
                'Roll call',
                'Pregnancy check',
                'Pregnancy',
                'Calving',
                'Weaning',
                'Slaughter',
                'Note',
                'Picture',
                'Check point',
            ];
            $grid->model()->whereIn('type', $types);
            $date_title = 'Date';
        } else if (in_array('events-sanitary', $segments)) {

            $lastEvent = Event::where([])->orderBy('created_at', 'desc')->first();

            // dd($lastEvent);
            $types = [
                'Abortion',
                'Death',
                'Disease test',
                'Sample taken',
                'Sample result',
                'Test conducted',
                'Test result',
                'Treatment',
                'Vaccination',
                'Mortality',
                'Batch Treatment',
            ];
            $grid->model()->whereIn('type', $types);
            $grid->setTitle("Sanitary events");
        } else {
            $grid->setTitle("Events");
        }

        $cols = [];
        if (in_array('events-abortion', $segments)) {
            $grid->model()->where([
                'type' => 'Abortion'
            ]);
            $grid->setTitle("Abortion events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'detail',
            ];
            $date_title = "Abortion Date";
        } else if (in_array('events-disease-test', $segments)) {
            $grid->model()->where([
                'type' => 'Disease test'
            ]);
            $grid->setTitle("Disease suspected events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'disease_id',
                'detail',
            ];
            $date_title = "Date";
        } else if (in_array('events-sample-taken', $segments)) {
            $grid->model()->where([
                'type' => 'Sample taken'
            ]);
            $grid->setTitle("Sample taken events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'sample_taken',
                'detail',
            ];
            $date_title = "Sample taken Date";
        } else if (in_array('events-sample-result', $segments)) {
            $grid->model()->where([
                'type' => 'Sample result'
            ]);
            $grid->setTitle("Sample result events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'sample_results',
                'detail',
            ];
            $date_title = "Sample result Date";
        } else if (in_array('events-test-conducted', $segments)) {
            $grid->model()->where([
                'type' => 'Test conducted'
            ]);
            $grid->setTitle("Test conducted events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'test_conducted',
                'detail',
            ];
            $date_title = "Test conducted Date";
        } else if (in_array('events-treatment', $segments)) {
            $grid->model()->where([
                'type' => 'Treatment'
            ])->orWhere([
                'type' => 'Batch Treatment'
            ]);
            $grid->setTitle("Treatment events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'disease_id',
                'medicine_id',
                'detail',
            ];
            $date_title = "Treatment Date";
        } else if (in_array('events-mortality', $segments)) {
            $grid->model()->where([
                'type' => 'Mortality'
            ]);
            $grid->setTitle("Mortality events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'detail',
            ];
            $date_title = "Mortality Date";
        } else if (in_array('events-milking', $segments)) {
            $grid->model()->where([
                'type' => 'Milking'
            ]);
            $grid->setTitle("Milking events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'milk',
            ];
            $date_title = "Milking Date";
        } else if (in_array('events-weighing', $segments)) {
            $grid->model()->where([
                'type' => 'Weight check'
            ]);
            $grid->setTitle("Weighing events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'weight',
            ];
            $date_title = "Weighing Date";
        } else if (in_array('events-calving', $segments)) {
            $grid->model()->where([
                'type' => 'Calving'
            ]);
            $grid->setTitle("Calving events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'calf_sex',
                'calf_weight',
                'calf_id',
                'detail',
            ];
            $date_title = "Birth Date";
        } else if (in_array('events-weaning', $segments)) {
            $grid->model()->where([
                'type' => 'Weaning'
            ]);
            $grid->setTitle("Weaning events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'wean_weight',
                'detail',
            ];
            $date_title = "Weaning Date";
        } else if (in_array('events-service-artificial', $segments)) {
            $grid->model()->where([
                'type' => 'Service',
                'service_type' => 'Artificial insemination'
            ]);
            $grid->setTitle("Artificial insemination - service events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'service_type',
                'inseminator',
                'simen_code',
                'sire_breed',
                'detail',
            ];
            $date_title = "Service Date";
        } else if (in_array('events-service-natural', $segments)) {
            $grid->model()->where([
                'type' => 'Service',
                'service_type' => 'Natural service'
            ]);
            $grid->setTitle("Natural service - service events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'service_type',
                'sire_breed',
                'detail',
            ];
            $date_title = "Date mated";
        } else if (in_array('events-production', $segments)) {
            $grid->model()->where([
                'type' => 'Service',
                'service_type' => 'Natural breeding'
            ]);
            $grid->setTitle("Natural breeding - service events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'service_type',
                'inseminator',
                'sire_breed',
                'detail',
            ];
            $date_title = "Service Date";
        } else if (in_array('events-vaccination', $segments)) {
            $grid->model()->where([
                'type' => 'Vaccination'
            ]);
            $grid->setTitle("Vaccination events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'vaccination_against',
                'detail',

            ];
            $date_title = "Vaccination Date";
        } else if (in_array('events-pregnancy', $segments)) {
            $grid->model()->where([
                'type' => 'Pregnancy check'
            ]);
            $grid->setTitle("Pregnancy check events");
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'service_type',
                'is_present',
                'detail',
            ];
            $date_title = "Pregnancy check Date";
        } else {
            $cols = [
                'id',
                'animal_id',
                'created_at',
                'type',
                'disease_id',
                'inseminator',
                'inseminator_1',
                'inseminator_2',
                'inseminator_3',
                'medicine_id',
                'description',
                'detail',
            ];
            $date_title = "Date";
        }

        //Sample Result

        $grid->model()->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {


            $admins = [];

            $animals = [];
            $u = Auth::user();
            $filter->disableIdFilter();

            $filter->equal('administrator_id', 'Filter by farm owner')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })
                ->ajax(
                    url('api/ajax-users')
                );
            $filter->equal('animal_id', 'Filter by Animal')->select(function ($id) {
                $a = Animal::find($id);
                if ($a) {
                    return [$a->id => $a->e_id];
                }
            })
                ->ajax(
                    url('api/ajax-animals')
                );

            $filter->between('created_at', 'Created between')->date();
        });


        $grid->column('id', __('ID'))->sortable()->hide();
        $grid->column('created_at', __($date_title))
            ->display(function ($f) {
                return Utils::my_date_time($f);
            })->sortable();
        $grid->column('animal_id', __('E-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return 'N/A';
                }
                return $u->e_id;
            })->sortable();
        $grid->column('v_id', __('V-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return 'N/A';
                }
                return $u->v_id;
            })
            ->hide();






        $sanitaryEvents = [
            'Abortion',
            'Death',
            'Disease test',
            'Sample taken',
            'Sample result',
            'Test conducted',
            'Test result',
            'Treatment',
            'Vaccination',
            'Mortality',
            'Batch Treatment',
        ];
        $productionEvents = [
            'Ownership Transfer',
            'Milking',
            'Temperature check',
            'Weight check',
            'Service',
            'Roll call',
            'Pregnancy check',
            'Pregnancy',
            'Calving',
            'Weaning',
            'Slaughter',
            'Note',
            'Picture',
            'Check point',
        ];
        $type_filters = [];
        if ($isSanitary) {
            $type_filters = $sanitaryEvents;
        } else {
            $type_filters = $productionEvents;
        }

        if (in_array('events', $segments)) {
            $type_filters = array_merge($sanitaryEvents, $productionEvents);
            $grid->column('type', __('Event Type'))->sortable()
                ->filter(array_combine($type_filters, $type_filters));
        } else {
            $grid->column('type', __('Event Type'))->sortable()->hide();
        }


        if (in_array('disease_id', $cols))
            $grid->column('disease_id', __('Disease suspected'))
                ->display(function ($id) {
                    if ($this->type != 'Disease test' && $this->type != 'Treatment') {
                        return 'N/A';
                    }

                    $u = Disease::find($id);
                    if (!$u) {
                        return 'N/A';
                    }
                    return $u->name;
                })
                ->sortable();

        //if sanitary event, display -	Sample taken
        if (in_array('inseminator', $cols))
            $grid->column('inseminator', __('Inseminator'))
                ->display(function ($id) {

                    return $id;
                })
                ->sortable();
        //if sanitary event, display -	Sample taken
        if (in_array('sample_taken', $cols))
            $grid->column('sample_taken', __('Sample taken'))
                ->display(function ($id) {
                    return $id;
                })
                ->sortable();

        //if sanitary event, display -	Sample result
        if (in_array('sample_results', $cols))
            $grid->column('sample_results', __('Sample result'))
                ->display(function ($id) {
                    return $this->inseminator;
                });

        ///-	Test conducted
        if (in_array('inseminator_2', $cols))
            $grid->column('inseminator_2', __('Test conducted'))
                ->display(function ($id) {
                    if (strtolower($this->type) != 'test conducted') {
                        return 'N/A';
                    }
                    return $this->inseminator;
                });
        //-	Test result
        if (in_array('inseminator_3', $cols))
            $grid->column('inseminator_3', __('Test result'))
                ->display(function ($id) {
                    return $this->inseminator;
                });
        //-	Test result
        if (in_array('status', $cols))
            $grid->column('status', __('Disease Test result'))
                ->display(function ($id) {
                    return $this->status;
                })
                ->sortable()
                ->filter([
                    'Positive' => 'Positive',
                    'Negative' => 'Negative',
                ]);

        //Treatment

        //Treatment
        if (in_array('medicine_id', $cols))
            $grid->column('medicine_id', __('Drug'))
                ->display(function ($id) {
                    if (strtolower($this->type) != 'treatment' && strtolower($this->type) != 'batch treatment') {
                        return 'N/A';
                    }
                    if (strtolower($this->type) == 'batch treatment') {
                        return $this->description;
                    }
                    $u = DrugStockBatch::find($id);
                    if (!$u) {
                        return "#" . $id;
                    }
                    return $u->name;
                })
                ->sortable();

        //-	Mortality 
        /*  if ($isSanitary)
            $grid->column('inseminator_4', __('Mortality'))
                ->display(function ($id) {
                    if (strtolower($this->type) != 'mortality') {
                        return 'N/A';
                    }
                    return $this->inseminator;
                }); */

        #-	Milking
        if (in_array('milk', $cols))
            $grid->column('milk', __('Milk (L)'))
                ->display(function ($id) {
                    return $id;
                })
                ->sortable()
            ;
        #-    Weight check
        if (in_array('weight', $cols))
            $grid->column('weight', __('Weight (KG)'))
                ->display(function ($id) {
                    if ($this->type != 'Weight check') {
                        return 'N/A';
                    }
                    return $id;
                })
                ->sortable();


        //calf_sex
        if (in_array('calf_sex', $cols))
            $grid->column('calf_sex', __('Calf Sex'))
                ->display(function ($id) {
                    if ($this->type != 'Calving') {
                        return 'N/A';
                    }
                    return $id;
                })
                ->sortable();
        //calf_weight
        if (in_array('calf_weight', $cols))
            $grid->column('calf_weight', __('Calf Weight (KG)'))
                ->display(function ($id) {
                    if ($this->type != 'Calving') {
                        return 'N/A';
                    }
                    return $id;
                })
                ->sortable();
        if (in_array('calf_id', $cols))
            $grid->column('calf_id', __('Calf Born (E-ID)'))
                ->display(function ($id) {
                    $an = Animal::find($id);
                    if (!$an) {
                        return 'N/A';
                    }
                    return $an->e_id;
                })
                ->sortable();

        //-	Weaning
        if (in_array('wean_weight', $cols))
            $grid->column('wean_weight', __('Wean Weight (KG)'))
                ->display(function ($id) {
                    if ($this->type != 'Weaning') {
                        return 'N/A';
                    }
                    return $id;
                })
                ->sortable();

        //-	Service (service_type)
        if (in_array('service_type', $cols))
            $grid->column('service_type', __('Service Type'))
                ->display(function ($id) {
                    if ($this->type != 'Service') {
                        return 'N/A';
                    }
                    return $id;
                })
                ->sortable();

        //-	Service (service_type)
        if (in_array('is_present', $cols))
            $grid->column('is_present', __('PD Results'))
                ->sortable();

        if (in_array('sire_breed', $cols))
            $grid->column('inseminator_1', __('Sire E-ID'))
                ->display(function ($id) {
                    if ($this->type != 'Service') {
                        return 'N/A';
                    }
                    return $this->inseminator;
                });

        if (in_array('vaccination_against', $cols))
            $grid->column('vaccination_against', __('Vaccinated against'))
                ->display(function ($id) {
                    return $this->inseminator;
                });
        //Sire Breed (inseminator) inseminator_2
        if (in_array('simen_code', $cols))
            $grid->column('simen_code', __('Simen Code'))
                ->sortable();

        //inseminator
        if (in_array('inseminator', $cols))
            $grid->column('inseminator', __('Inseminator'))
                ->display(function ($id) {
                    if ($this->type != 'Service') {
                        return 'N/A';
                    }
                    return $this->inseminator;
                });


        if (in_array('test_conducted', $cols))
            $grid->column('test_conducted', __('Test Conducted'))
                ->display(function ($id) {
                    return $this->inseminator;
                });


        if (in_array('description', $cols))
            $grid->column('description', __('Description'))->sortable()
                ->limit(70);
        if (in_array('detail', $cols))
            $grid->column('detail', __('Details'))->sortable();



        /* $grid->column('district_id', __('District'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable(); */
        /*        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();

 */

        if (in_array('administrator_id', $cols))
            $grid->column('administrator_id', __('Animal owner'))
                ->display(function ($id) {
                    $u = Administrator::find($id);
                    if (!$u) {
                        return $id;
                    }
                    return $u->name;
                })->sortable()
            ;




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
        $show = new Show(Event::findOrFail($id));

        $show = new Show(Farm::findOrFail($id));
        if (Admin::user()->isRole('farmer')) {
            $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableDelete();
                });
        }


        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('type', __('Type'));
        $show->field('approved_by', __('Approved by'));
        $show->field('detail', __('Detail'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        /*
        die("sone"); */


        /*    $ids = [];
        foreach (Animal::where([  
            'administrator_id' => Auth::user()->id
        ])->get() as $k => $a) {
            $ids[] = $a->id;
        }

        $faker = Faker::create();
        for ($x = 1; $x < 25; $x++) {
            $e = new Event();
            $e->animal_id = $ids[rand(1,3)];
            $e->created_at = $faker->dateTimeBetween('-1 year');
            $e->type = "Milking";
            $e->milk = rand(1, 15);
           try {
            $e->save();
           } catch (\Throwable $th) {
            //throw $th;
           }
            echo $x."<br>";
        }  
        dd("done");
 */
        Utils::display_alert_message();
        $form = new Form(new Event());

        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('is_batch_import', __('Sub county id'))->default(0);
        $form->hidden('farm_id', __('Farm id'))->default(1);




        $form->select('animal_id', 'Select Animal')
            ->options(function ($id) {
                $parent = Animal::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->v_id . " - " . $parent->e_id];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-animals?'
                    . "&administrator_id={$u->id}")
            )->rules('required');

        $form->divider();
        $form->radio('type', __('Event type'))
            ->options(array(
                'Milking' => 'Milking',
                'Weight check' => 'Weight check',
                'Disease test' => 'Disease test',
                'Treatment' => 'Treatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy check' => 'Pregnancy check',
                'Temperature check' => 'Temperature check',
                'Stolen' => 'Stolen',
                'Home slaughter' => 'Home slaughter',
                'Photo' => 'Photo',
                'Death' => 'Death',


            ))
            ->rules('required')
            ->when('Weight check', function (Form $form) {
                $form->decimal('weight', 'Weight value')
                    ->help('in Killograms (KGs)')
                    ->rules('required');
            })
            ->when('Photo', function (Form $form) {
                $form->image('photo', 'Attach photo');
            })
            ->when('Milking', function (Form $form) {
                $form->decimal('milk', 'Milk quantity')
                    ->help('in litters')
                    ->rules('required');
            })
            ->when('Temperature check', function (Form $form) {
                $form->decimal('temperature', 'Temperature value')
                    ->help('in degrees Celsius (°C)')
                    ->rules('required');
            })
            ->when('Pregnancy check', function (Form $form) {
                $form->radio('pregnancy_check_method', __(
                    'Pregnancy check method used'
                ))
                    ->options(array(
                        'Palpation' => 'Palpation',
                        'Ultrasound' => 'Ultrasound',
                        'Observation' => 'Observation',
                        'Blood' => 'Blood',
                        'Urine' => 'Urine',
                    ))
                    ->rules('required');

                $form->radio('pregnancy_check_results', __(
                    'Pregnancy check results'
                ))
                    ->options(array(
                        'Pregnant' => 'Is pregnant',
                        'Not Pregnant' => 'Is not pregnant',
                    ))
                    ->when('Pregnant', function (Form $form) {
                        $form->radio('pregnancy_fertilization_method', __(
                            'Pregnancy fertilization method used'
                        ))
                            ->options(array(
                                'Artificial insemination' => 'Artificial insemination',
                                'Natural breeding' => 'Natural breeding',
                            ))
                            ->rules('required');
                        $form->radio('pregnancy_expected_sex', __(
                            'Expected calf sex'
                        ))
                            ->options(array(
                                'Male' => 'Bull',
                                'Heifer' => 'Heifer',
                                'Unknown' => 'Unknown',
                            ))
                            ->rules('required');
                    })
                    ->rules('required');
            })
            ->when('Disease test', function (Form $form) {
                $form->select('disease_id', __('Select disease'))
                    ->options(Disease::all()->pluck('name', 'id'))
                    ->rules('required');
                $form->radio('disease_test_results', __(
                    'Disease test results'
                ))
                    ->options(array(
                        'Positive' => 'Positive (Has the disease)',
                        'Negative' => 'Negative (Does not have the disease)',
                    ))
                    ->rules('required');
            })
            ->when('Treatment', function (Form $form) {
                $drugs = [];
                foreach (
                    DrugStockBatch::where([
                        'administrator_id' => Auth::user()->id
                    ])
                        ->where('current_quantity', '>', 0)
                        ->get() as $key => $v
                ) {

                    $unit = "";
                    if ($v->category != null) {
                        $unit = " - {$v->category->unit}";
                    }
                    $drugs[$v->id] = $v->name . " - Available QTY: {$v->current_quantity} {$unit}";
                }


                $form->select('medicine_id', __('Please select Drug'))
                    ->options($drugs)
                    ->rules('required');
                $form->decimal('medicine_quantity', 'Applied quantity')->rules('required');
            })
            ->when('Vaccination', function (Form $form) {
                $form->select('vaccine_id', __('Please select Vaccine'))
                    ->options(Vaccine::all()->pluck('name', 'name'))
                    ->rules('required');
            });
        $form->divider();
        $form->text('detail', __('Event Details'))
            ->help("Specify the event and be as brief as possible. For example, if Death, only enter the cause of death in
        this detail field.");

        $user = Auth::user();
        $form->hidden('approved_by', __('Approved by'))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();


        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
