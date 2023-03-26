<?php

namespace Encore\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Transaction;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class Dashboard
{


    public static function dvo_farms_widget()
    {
        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

        if ($r == null) {
            return 'District role not found.';
        }
        $dis = Location::find($r->type_id);
        if ($dis == null) {
            return 'District not found.';
        }

        $sub_title =  "Farms in $dis->name district.";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Farms',
            'sub_title' => $sub_title,
            'number' => number_format(Farm::where(['district_id' => $dis->id])->count()),
            'link' => admin_url('farms')
        ]);
    }

    public static function dvo_animals_widget()
    {
        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

        if ($r == null) {
            return 'District role not found.';
        }
        $dis = Location::find($r->type_id);
        if ($dis == null) {
            return 'District not found.';
        }

        $sub_title =  "Animals in $dis->name district.";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Livestock',
            'sub_title' => $sub_title,
            'number' => number_format(Animal::where(['district_id' => $dis->id])->count()),
            'link' => admin_url('animals')
        ]);
    }

    public static function dvo_events_widget()
    {
        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

        if ($r == null) {
            return 'District role not found.';
        }
        $dis = Location::find($r->type_id);
        if ($dis == null) {
            return 'District not found.';
        }

        $sub_title =  "Events in $dis->name district.";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Events',
            'sub_title' => $sub_title,
            'number' => number_format(Event::where(['district_id' => $dis->id])->count()),
            'link' => admin_url('events')
        ]);
    }

    public static function dvo_movements_widget()
    {
        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

        if ($r == null) {
            return 'District role not found.';
        }
        $dis = Location::find($r->type_id);
        if ($dis == null) {
            return 'District not found.';
        }

        $sub_title =  "Events in $dis->name district.";
        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'Movements',
            'sub_title' => $sub_title,
            'number' => number_format(Movement::where(['district_from' => $dis->id])->count()),
            'link' => admin_url('movements')
        ]);
    }

    public static function dvo_recent_events()
    {
        $events = Event::where([])->orderBy('id', 'Desc')->limit(15)->get();
        return view('dashboard.recent-events', [
            'title' => 'Recent events',
            'items' => $events
        ]);
    }

    public static function dvo_recent_animals()
    {
        $u = Auth::user();
        $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

        if ($r == null) {
            return 'District role not found.';
        }
        $dis = Location::find($r->type_id);
        if ($dis == null) {
            return 'District not found.';
        }
        $events = Movement::where(['district_from' => $dis->id])->orderBy('id', 'Desc')->limit(15)->get();
        //dd($events->first());
        return view('dashboard.recent-movements', [
            'title' => 'Recent movement permits',
            'items' => $events
        ]);
    }




    public static function milkCollection()
    {

        $administrator_id = Auth::user()->id;
        $data = [];
        $records = [];
        $prev = 0;
        for ($i = 29; $i >= -1; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subDays($i);
            $min->subDays(($i + 1));

            //2022-11-03 19:33:51.955979 UTC (+00:00)

            //2022-11-03 00:00:00.0 UTC (+00:00)
            //2022-11-02 00:00:00.0 UTC (+00:00)


            $max = Carbon::parse($max->format('Y-m-d'));
            $min = Carbon::parse($min->format('Y-m-d'));


            $milk = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking',
                    'administrator_id' => $administrator_id,
                ])
                ->sum('milk');

            $count = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking',
                    'administrator_id' => $administrator_id,
                ])
                ->count('animal_id');


            $expence = Transaction::whereBetween('created_at', [$min, $max])
                ->where([
                    'is_income' => 0,
                    'administrator_id' => $administrator_id,
                ])
                ->sum('amount');

            $income = Transaction::whereBetween('created_at', [$min, $max])
                ->where([
                    'is_income' => 1,
                    'administrator_id' => $administrator_id,
                ])
                ->sum('amount');

            $data['data'][] = $milk;
            $data['income'][] = $income;
            $data['count'][] = $count;
            $data['expence'][] = ((-1) * ($expence));
            $data['labels'][] = Utils::my_day($min);

            $rec['day'] = Utils::my_date_1($min);
            $rec['animals'] = $count;
            $rec['milk'] = $milk;

            $rec['progress'] = 0;
            if ($count > 0) {
                $avg = $milk / $count;
                $rec['progress'] =  $avg - $prev;
                $prev = $avg;
            }

            $data['records'][] = $rec;
        }

        $data['records'] = array_reverse($data['records']);
        return view('dashboard.farmerMilkCollection', $data);
    }
    public static function farmerEvents()
    {
        $u = Auth::user();
        $events = Event::where([
            'administrator_id' => $u->id
        ])->orderBy('created_at', 'Desc')->limit(17)->get();
        return view('dashboard.farmerEvents', [
            'events' => $events
        ]);
    }

    public static function farmerSummary()
    {

        $u = Auth::user();
        for ($i = 12; $i >= 0; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subMonths($i);
            $min->subMonths(($i + 1));
            $allAnimals = Animal::whereBetween('dob', [$min, $max])
                ->where([

                    'administrator_id' => $u->id
                ])
                ->count();

            $cattle = Animal::whereBetween('dob', [$min, $max])
                ->where([
                    'type' => 'Cattle',
                    'administrator_id' => $u->id
                ])
                ->count();
            $goat = Animal::whereBetween('dob', [$min, $max])
                ->where([
                    'type' => 'Goat',
                    'administrator_id' => $u->id
                ])
                ->count();

            $sheep = Animal::whereBetween('dob', [$min, $max])
                ->where([
                    'type' => 'Sheep',
                    'administrator_id' => $u->id
                ])
                ->count();





            $data['allAnimals'][] = $allAnimals;
            $data['cattle'][] = $cattle;
            $data['goat'][] = $goat;
            $data['sheep'][] = $sheep;

            $data['labels'][] = Utils::month($max);
        }

        $countCattle = Animal::where([
            'type' => 'Cattle',
            'administrator_id' => $u->id
        ])
            ->count();
        $countCattleMale = Animal::where([
            'type' => 'Cattle',
            'sex' => 'Male',
            'administrator_id' => $u->id
        ])
            ->count();
        $countCattleFemale = Animal::where([
            'type' => 'Cattle',
            'sex' => 'Female',
            'administrator_id' => $u->id
        ])
            ->count();


        $countGoatFemale = Animal::where([
            'type' => 'Goat',
            'sex' => 'Female',
            'administrator_id' => $u->id
        ])->count();

        $countGoatMale = Animal::where([
            'type' => 'Goat',
            'sex' => 'Male',
            'administrator_id' => $u->id
        ])->count();

        $countGoat = Animal::where([
            'type' => 'Goat',
            'administrator_id' => $u->id
        ])->count();


        //////////////////SHEEP===///////////////
        $countSheepFemale = Animal::where([
            'type' => 'Sheep',
            'sex' => 'Female',
            'administrator_id' => $u->id
        ])->count();

        $countSheepMale = Animal::where([
            'type' => 'Sheep',
            'sex' => 'Male',
            'administrator_id' => $u->id
        ])->count();

        $countSheep = Animal::where([
            'type' => 'Sheep',
            'administrator_id' => $u->id
        ])->count();

        $count = Animal::where([
            'administrator_id' => $u->id
        ])->count();



        $data['countSheepFemale'] = $countSheepFemale;
        $data['countSheepMale'] = $countSheepMale;
        $data['countSheep'] = $countSheep;


        $data['countGoat'] = $countGoat;
        $data['countGoatMale'] = $countGoatMale;
        $data['countGoatFemale'] = $countGoatFemale;
        $data['countCattleFemale'] = $countCattleFemale;

        $data['countCattle'] = $countCattle;
        $data['countCattleMale'] = $countCattleMale;
        $data['countCattleFemale'] = $countCattleFemale;

        $data['count'] = $count;



        return view('dashboard.farmerSummary', $data);
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/' . PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'laravel-admin-ext/helpers',
                'link' => 'https://github.com/laravel-admin-extensions/helpers',
                'icon' => 'gears',
            ],
            'log-viewer' => [
                'name' => 'laravel-admin-ext/log-viewer',
                'link' => 'https://github.com/laravel-admin-extensions/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'laravel-admin-ext/backup',
                'link' => 'https://github.com/laravel-admin-extensions/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'laravel-admin-ext/config',
                'link' => 'https://github.com/laravel-admin-extensions/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'laravel-admin-ext/api-tester',
                'link' => 'https://github.com/laravel-admin-extensions/api-tester',
                'icon' => 'sliders',
            ],
            'media-manager' => [
                'name' => 'laravel-admin-ext/media-manager',
                'link' => 'https://github.com/laravel-admin-extensions/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'laravel-admin-ext/scheduling',
                'link' => 'https://github.com/laravel-admin-extensions/scheduling',
                'icon' => 'clock-o',
            ],
            'reporter' => [
                'name' => 'laravel-admin-ext/reporter',
                'link' => 'https://github.com/laravel-admin-extensions/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'laravel-admin-ext/redis-manager',
                'link' => 'https://github.com/laravel-admin-extensions/redis-manager',
                'icon' => 'flask',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
