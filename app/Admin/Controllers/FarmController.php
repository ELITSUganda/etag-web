<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\District;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Movement;
use App\Models\SubCounty;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class FarmController extends AdminController
{



    protected $title = 'LHR - (Farms)';

    protected function form()
    {
        $form = new Form(new Farm());
        $admins = [];
        $u = Admin::user();
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('farmer')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }




        if ($form->isCreating()) {

            if (
                Admin::user()->isRole('administrator') ||
                Admin::user()->isRole('scvo') ||
                Admin::user()->isRole('clo') ||
                Admin::user()->isRole('admin')
            ) {

                $form->select('administrator_id', 'Farm owner')->options(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => "#" . $a->id . " - " . $a->name];
                    }
                })
                    ->rules('required')
                    ->ajax(url(
                        '/api/ajax?'
                            . "&search_by_1=name"
                            . "&search_by_2=id"
                            . "&model=User"
                    ));
            } else {
                $form->hidden('administrator_id', __('Farm owner'))
                    ->default($u->id)
                    ->value($u->id)
                    ->required();
            }
        }




        $form->select('sub_county_id', 'Sub-county')->options(function ($id) {
            $a = Location::find($id);
            if ($a) {
                return [$a->id =>  $a->name_text];
            }
        })
            ->rules('required')
            ->ajax(url(
                '/api/sub-counties'
            ));




        $form->text('village', __('Village'))->required();

        $form->select('farm_type', __('Farm type'))
            ->options(array(
                'Dairy' => 'Dairy',
                'Beef' => 'Beef',
                'Mixed' => 'Mixed'
            ))
            ->required();


        if ($form->isCreating()) {
            $form->hidden('sheep_count', __('Number of sheep'))->default(0)->required();
            $form->hidden('goats_count', __('Number of goats'))->default(0)->required();
            $form->hidden('cattle_count', __('Number of cattle'))->default(0)->required();
        }

        $form->text('size', __('Size (in Ha)'))->attribute('type', 'number')->required();
        $form->text('latitude', __('Latitude'))->rules('required');
        $form->text('longitude', __('Longitude'))->rules('required');

        $form->textarea('dfm', __('Farm Details'));
        $form->text('holding_code', __('Holding code'))->readonly();

        $form->disableCreatingCheck();
        //$form->disableB
        $form->decimal('cattle_count', __('Number of cattle'))->default(0)->required();
        return $form;
    }



    protected function grid()
    {

        $grid = new Grid(new Farm());



        //add button view on map
        $url_view_farm_on_map = admin_url('maps');
        $grid->header(function ($query) use ($url_view_farm_on_map) {
            return <<<HTML
            <a target="_blank" href="{$url_view_farm_on_map}" class="btn btn-sm btn-primary">View farms on map</a>
            HTML;
        });

        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();
        $dis = null;
        if ($r != null) {
            $dis = Location::find($r->type_id);
        }
        if ($dis != null) {
            $grid->model()->where('district_id', '=', $dis->id);
        } else if (Admin::user()->isRole('farmer')) {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
        } else if (
            Admin::user()->isRole('dvo')
        ) {
            $u = Auth::user();
            $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();
            $dis = null;
            if ($r != null) {
                $dis = Location::find($r->type_id);
            }
            $grid->model()->where([
                'district_id' => $dis->id
            ])->orderBy('id', 'DESC');
        }

        if ($u->isRole('data-viewer')) {
            $grid->disableActions();
            $grid->disableCreateButton();
        }


        $grid->filter(function ($filter) {







            $filter->equal('holding_code', "LHC")->select(Farm::all()->pluck('holding_code', 'holding_code'));


            $filter->equal('administrator_id', 'Filter by farm owner')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })
                ->ajax(
                    url('api/ajax-users')
                );

            $filter->equal('farm_type', "Farm type")->select([
                'Beef' => 'Beef',
                'Dairy' => 'Dairy',
                'Mixed' => 'Mixed',
            ]);


            $filter->equal('district_id', 'Filter by district')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/districts')
                );
            $filter->equal('sub_county_id', 'Filter by sub-county')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/sub-counties')
                );
            //between created_at date
            $filter->between('created_at', 'Created at')->date();
        });

        $grid->disableBatchActions();
        $grid->quickSearch('holding_code')->placeholder("Search by LHC...");
        $grid->model()->orderBy('id', 'DESC');
        $grid->column('id', __('Id'))->sortable();


        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();



        $grid->column('holding_code', __('Holding code'))->sortable();
        $grid->column('size', __('Size (Ha)'))->sortable();
        $grid->column('cattle_count', __('Cattle'))->sortable()
            ->display(function ($id) {
                return number_format($this->cattle_count);
            })
            ->totalRow(function ($amount) {
                return "<span class='text-success'>" . number_format($amount) . "</span>";
            });
        $grid->column('goats_count', __('Goats'))->sortable()
            ->display(function ($id) {
                return number_format($this->goats_count);
            })
            ->totalRow(function ($amount) {
                return "<span class='text-success'>" . number_format($amount) . "</span>";
            });
        $grid->column('sheep_count', __('Sheep'))->sortable()
            ->display(function ($id) {
                return number_format($this->sheep_count);
            })
            ->totalRow(function ($amount) {
                return "<span class='text-success'>" . number_format($amount) . "</span>";
            });
        $grid->column('longitude', __('GPS'))->display(function ($id) {
            return $this->latitude . "," . $this->longitude;
        })->sortable();
        $grid->column('village', __('Village'))->sortable();
        $grid->column('administrator_id', __('Owner'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name . " ({$u->phone_number}) ";
            })->sortable();
        $grid->column('farm_type', __('Farm type'))->sortable();


        $grid->column('district_id', __('District'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();

        //is_processed
        $grid->column('is_processed', __('Processed'))->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
                'FAILED' => 'Failed',
            ])->label([
                'Yes' => 'success',
                'No' => 'danger',
                'FAILED' => 'warning',
            ])->hide();
        //duplicate_results
        $grid->column('duplicate_results', __('Duplicate'))->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
                'FAILED' => 'Failed',
            ])->label([
                'Yes' => 'success',
                'No' => 'danger',
                'FAILED' => 'warning',
            ])->hide();

        //registered_id by 
        $grid->column('registered_id', __('Registered by'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                $phone_number = '';
                if ($u->phone_number != null && strlen($u->phone_number) > 3) {
                    $phone_number = " - " . $u->phone_number;
                }
                return $u->name . $phone_number;
            })->sortable();

        return $grid;
    }


    protected function detail($id)
    {
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
        $show->field('farm_type', __('Farm type'));
        $show->field('holding_code', __('Holding code'));
        $show->field('size', __('Size'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('dfm', __('Detail'));

        return $show;
    }
}
