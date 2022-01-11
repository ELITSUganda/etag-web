<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = \Faker\Factory::create();
        $names = ["Abim",
        "Adjumani",
        "Agago",
        "Alebtong",
        "Amolatar",
        "Amudat",
        "Amuria",
        "Amuru",
        "Apac",
        "Arua",
        "Budaka",
        "Bududa",
        "Bugiri",
        "Buikwe",
        "Bukedea",
        "Bukomansimbi",
        "Bukwo",
        "Bulambuli",
        "Buliisa",
        "Bundibugyo",
        "Bushenyi",
        "Busia",
        "Butaleja",
        "Butambala",
        "Buvuma",
        "Buyende",
        "Dokolo",
        "Gomba",
        "Gulu",
        "Hoima",
        "Ibanda",
        "Iganga",
        "Isingiro",
        "Jinja",
        "Kaabong",
        "Kabale",
        "Kabarole",
        "Kaberamaido",
        "Kalangala",
        "Kaliro",
        "Kalungu",
        "Kampala",
        "Kamuli",
        "Kamwenge",
        "Kanungu",
        "Kapchorwa",
        "Kasese",
        "Katakwi",
        "Katerere",
        "Kayunga",
        "Kibaale",
        "Kibingo",
        "Kiboga",
        "Kibuku",
        "Kiruhura",
        "Kiryandongo",
        "Kisoro",
        "Kitgum",
        "Koboko",
        "Kole",
        "Kotido",
        "Kumi",
        "Kween",
        "Kyankwanzi",
        "Kyegegwa",
        "Kyenjojo",
        "Lamwo",
        "Lira",
        "Luuka",
        "Luwero",
        "Lwengo",
        "Lyantonde",
        "Manafwa",
        "Maracha",
        "Masaka",
        "Masindi",
        "Mayuge",
        "Mbale",
        "Mbarara",
        "Mitooma",
        "Mityana",
        "Moroto",
        "Moyo",
        "Mpigi",
        "Mubende",
        "Mukono",
        "Nakapiripirit",
        "Nakaseke",
        "Nakasongola",
        "Namiyango",
        "Namutumba",
        "Napak",
        "Nebbi",
        "Ngora",
        "Nsiika",
        "Ntoroko",
        "Ntungamo",
        "Nwoya",
        "Otuke",
        "Oyam",
        "Pader",
        "Pallisa",
        "Rakai",
        "Rukungiri",
        "Serere",
        "Sironko",
        "Soroti",
        "Ssembabule",
        "Tororo",
        "Wakiso",
        "Yumbe",
        "Zombo"];

        District::truncate();
        foreach ($names as $key => $value) {
            District::create([
                'name' => $value,
                'detail' => $faker->paragraph,
            ]);
        }
    }
}

