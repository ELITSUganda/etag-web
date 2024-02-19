<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\WebController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Gen;
use App\Models\Image;
use App\Models\Image as ModelsImage;
use App\Models\ImageModel;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Grid\Tools\Header;
use Illuminate\Support\Facades\Route;
use Milon\Barcode\DNS1D;

use function PHPUnit\Framework\fileExists;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function () {
    //echo DNS1D::getBarcodeSVG('4445645656', 'PHARMA2T');
    //echo DNS1D::getBarcodeHTML('4445645656', 'PHARMA2T');
    //echo '<img src="data:image/png,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';
    //echo DNS1D::getBarcodePNGPath('4445645656', 'PHARMA2T');
    //echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';

    $data = 'VID: 4445645656\n';
    $data .= "SLAUGHTER DATE: \n";
    $data .= "4445645656\n";
    $data .= "4445645656\n";
    $data .= '4445645656\n';
    $data .= '4445645656\n';
    $multiplier = 3;
    $link = DNS2D::getBarcodePNGPath($data, 'QRCODE', 3 * $multiplier, 3 * $multiplier, array(0, 0, 0), true); 
    $url = url($link);
    echo $url;

    $img_size = getimagesize($url);

    //to mb
    $size = $img_size[0] * $img_size[1] * 8 / 1024 / 1024;

    echo '<img  width="400" src="' . $url . '" alt="barcode"   />';
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "Size: $size MB";
 
    die(); 


    echo DNS1D::getBarcodeSVG('4445645656', 'C39');
    echo DNS2D::getBarcodeHTML('4445645656', 'QRCODE');
    echo DNS2D::getBarcodePNGPath('4445645656', 'PDF417');
    echo DNS2D::getBarcodeSVG('4445645656', 'DATAMATRIX');
    echo '<img src="data:image/png;base64,' . DNS2D::getBarcodePNG('4', 'PDF417') . '" alt="barcode"   />';
    die();

    $multiplier = 1.5;
    $link = DNS1D::getBarcodePNGPath('4445561', 'C128', 3 * $multiplier, 44 * $multiplier, array(0, 0, 0), true);
    $url = url($link);

    $img_size = getimagesize($url);

    //to mb
    $size = $img_size[0] * $img_size[1] * 8 / 1024 / 1024;

    echo '<img  width="400" src="' . $url . '" alt="barcode"   />';
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "Size: $size MB";



    die();
});


//Route::get('/', [WebController::class, 'index']);
Route::get('/', function () {
    header('Location: ' . admin_url());
    die();
});

Route::get('/process-profile-photos', function () {
    $aniamls = Animal::all();
    $i = 0;
    foreach ($aniamls as $key => $an) {
        if ($an->photo != null) {
            if (strlen($an->photo) > 3) {
                continue;
            }
        }
        $i++;
        $img = Image::where([
            'parent_id' => $an->id,
            'type' => 'Animal',
            'parent_endpoint' => 'Animal',
        ])->first();
    }
    echo "Done: $i";
});
Route::get('/process', function () {

    die();

    //set_time_limit(0);
    set_time_limit(-1);
    //ini_set('memory_limit', '1024M');
    ini_set('memory_limit', '-1');


    $items = json_decode('[".","..","1700268541-11732.jpg","1700268541-12108.jpg","1700268541-12353.jpg","1700268541-14937.jpg","1700268541-17370.jpg","1700268541-20570.jpg","1700268541-20603.jpg","1700268541-20639.jpg","1700268541-21150.jpg","1700268541-21439.jpg","1700268541-21631.jpg","1700268541-21783.jpg","1700268541-22241.jpg","1700268541-23127.jpg","1700268541-29566.jpg","1700268541-31659.jpg","1700268541-33056.jpg","1700268541-36429.jpg","1700268541-42313.jpg","1700268541-43082.jpg","1700268541-44846.jpg","1700268541-49849.jpg","1700268541-52311.jpg","1700268541-57192.jpg","1700268541-60820.jpg","1700268541-61878.jpg","1700268541-66998.jpg","1700268541-67042.jpg","1700268541-68368.jpg","1700268541-68969.jpg","1700268541-69190.jpg","1700268541-70240.jpg","1700268541-70365.jpg","1700268541-72883.jpg","1700268541-76416.jpg","1700268541-79125.jpg","1700268541-79753.jpg","1700268541-80201.jpg","1700268541-80626.jpg","1700268541-82521.jpg","1700268541-85337.jpg","1700268541-85815.jpg","1700268541-85872.jpg","1700268541-87403.jpg","1700268541-88725.jpg","1700268541-90348.jpg","1700268541-91235.jpg","1700268541-95118.jpg","1700268541-96165.jpg","1700268541-96242.jpg","1700268541-96460.jpg","1700268541-97727.jpg","1700268541-98981.jpg","1700268541-99350.jpg","1700268541-99725.jpg","1700268542-10465.jpg","1700268542-14380.jpg","1700268542-15622.jpg","1700268542-16547.jpg","1700268542-18264.jpg","1700268542-18413.jpg","1700268542-18755.jpg","1700268542-20723.jpg","1700268542-21418.jpg","1700268542-25960.jpg","1700268542-29737.jpg","1700268542-30780.jpg","1700268542-31361.jpg","1700268542-33280.jpg","1700268542-33457.jpg","1700268542-35258.jpg","1700268542-35700.jpg","1700268542-38575.jpg","1700268542-39232.jpg","1700268542-42782.jpg","1700268542-43227.jpg","1700268542-45767.jpg","1700268542-47312.jpg","1700268542-48452.jpg","1700268542-49072.jpg","1700268542-54609.jpg","1700268542-56283.jpg","1700268542-56391.jpg","1700268542-57149.jpg","1700268542-57392.jpg","1700268542-57801.jpg","1700268542-64753.jpg","1700268542-65792.jpg","1700268542-66206.jpg","1700268542-69115.jpg","1700268542-69262.jpg","1700268542-71901.jpg","1700268542-77372.jpg","1700268542-79122.jpg","1700268542-81334.jpg","1700268542-86379.jpg","1700268542-86813.jpg","1700268542-89459.jpg","1700268542-89868.jpg","1700268542-90899.jpg","1700268542-92334.jpg","1700268542-92497.jpg","1700268542-92686.jpg","1700268542-92903.jpg","1700268542-92930.jpg","1700268542-93737.jpg","1700268542-94462.jpg","1700268542-97733.jpg","1700268542-98014.jpg","1700268542-99602.jpg","1700268543-10982.jpg","1700268543-16534.jpg","1700268543-16895.jpg","1700268543-17055.jpg","1700268543-24350.jpg","1700268543-25183.jpg","1700268543-26236.jpg","1700268543-26251.jpg","1700268543-26503.jpg","1700268543-27136.jpg","1700268543-32222.jpg","1700268543-32321.jpg","1700268543-32599.jpg","1700268543-35021.jpg","1700268543-35520.jpg","1700268543-35939.jpg","1700268543-36012.jpg","1700268543-36248.jpg","1700268543-36534.jpg","1700268543-36956.jpg","1700268543-38595.jpg","1700268543-40165.jpg","1700268543-44239.jpg","1700268543-45535.jpg","1700268543-47006.jpg","1700268543-49278.jpg","1700268543-50805.jpg","1700268543-52826.jpg","1700268543-54417.jpg","1700268543-56303.jpg","1700268543-58454.jpg","1700268543-59966.jpg","1700268543-60666.jpg","1700268543-61901.jpg","1700268543-62083.jpg","1700268543-64119.jpg","1700268543-65383.jpg","1700268543-67827.jpg","1700268543-68081.jpg","1700268543-69995.jpg","1700268543-70369.jpg","1700268543-71847.jpg","1700268543-72842.jpg","1700268543-73037.jpg","1700268543-74590.jpg","1700268543-74694.jpg","1700268543-79319.jpg","1700268543-79825.jpg","1700268543-81420.jpg","1700268543-83937.jpg","1700268543-85376.jpg","1700268543-87517.jpg","1700268543-88619.jpg","1700268543-93305.jpg","1700268543-94404.jpg","1700268543-94801.jpg","1700268543-98090.jpg","1700268544-12431.jpg","1700268544-12497.jpg","1700268544-14511.jpg","1700268544-21253.jpg","1700268544-21877.jpg","1700268544-22897.jpg","1700268544-23827.jpg","1700268544-27266.jpg","1700268544-27672.jpg","1700268544-27988.jpg","1700268544-28679.jpg","1700268544-29551.jpg","1700268544-31715.jpg","1700268544-33294.jpg","1700268544-33760.jpg","1700268544-35818.jpg","1700268544-36321.jpg","1700268544-38443.jpg","1700268544-38862.jpg","1700268544-39015.jpg","1700268544-40329.jpg","1700268544-42462.jpg","1700268544-42895.jpg","1700268544-49991.jpg","1700268544-50392.jpg","1700268544-53193.jpg","1700268544-53743.jpg","1700268544-55269.jpg","1700268544-57404.jpg","1700268544-60800.jpg","1700268544-61610.jpg","1700268544-65140.jpg","1700268544-65669.jpg","1700268544-67006.jpg","1700268544-67105.jpg","1700268544-67201.jpg","1700268544-71192.jpg","1700268544-71614.jpg","1700268544-73269.jpg","1700268544-76162.jpg","1700268544-77410.jpg","1700268544-78070.jpg","1700268544-79325.jpg","1700268544-79938.jpg","1700268544-80401.jpg","1700268544-82814.jpg","1700268544-84177.jpg","1700268544-84822.jpg","1700268544-85649.jpg","1700268544-89115.jpg","1700268544-90768.jpg","1700268544-94064.jpg","1700268544-96163.jpg","1700268544-98469.jpg","1700268545-12732.jpg","1700268545-13006.jpg","1700268545-13148.jpg","1700268545-13974.jpg","1700268545-15197.jpg","1700268545-18825.jpg","1700268545-21533.jpg","1700268545-26063.jpg","1700268545-27127.jpg","1700268545-34552.jpg","1700268545-38668.jpg","1700268545-38800.jpg","1700268545-40611.jpg","1700268545-40675.jpg","1700268545-41053.jpg","1700268545-41817.jpg","1700268545-42613.jpg","1700268545-43144.jpg","1700268545-46453.jpg","1700268545-48108.jpg","1700268545-53071.jpg","1700268545-53280.jpg","1700268545-53736.jpg","1700268545-58548.jpg","1700268545-59747.jpg","1700268545-62500.jpg","1700268545-62848.jpg","1700268545-67531.jpg","1700268545-68118.jpg","1700268545-69008.jpg","1700268545-71189.jpg","1700268545-72841.jpg","1700268545-72868.jpg","1700268545-73125.jpg","1700268545-73659.jpg","1700268545-73817.jpg","1700268545-74646.jpg","1700268545-75432.jpg","1700268545-76012.jpg","1700268545-76326.jpg","1700268545-77973.jpg","1700268545-78871.jpg","1700268545-83857.jpg","1700268545-85859.jpg","1700268545-90154.jpg","1700268545-92608.jpg","1700268545-93932.jpg","1700268545-95253.jpg","1700268545-96248.jpg","1700268545-96613.jpg","1700268545-97839.jpg","1700268545-98822.jpg","1700268545-98939.jpg","1700268546-11304.jpg","1700268546-18199.jpg","1700268546-18368.jpg","1700268546-18387.jpg","1700268546-18874.jpg","1700268546-20783.jpg","1700268546-22147.jpg","1700268546-24348.jpg","1700268546-25522.jpg","1700268546-25739.jpg","1700268546-27092.jpg","1700268546-28701.jpg","1700268546-29276.jpg","1700268546-30486.jpg","1700268546-31619.jpg","1700268546-32272.jpg","1700268546-32913.jpg","1700268546-33105.jpg","1700268546-33109.jpg","1700268546-35000.jpg","1700268546-36644.jpg","1700268546-42544.jpg","1700268546-46470.jpg","1700268546-47182.jpg","1700268546-48033.jpg","1700268546-50332.jpg","1700268546-50347.jpg","1700268546-53195.jpg","1700268546-53927.jpg","1700268546-54638.jpg","1700268546-60531.jpg","1700268546-62308.jpg","1700268546-62495.jpg","1700268546-63235.jpg","1700268546-64234.jpg","1700268546-66301.jpg","1700268546-66479.jpg","1700268546-67905.jpg","1700268546-70311.jpg","1700268546-70537.jpg","1700268546-70960.jpg","1700268546-76220.jpg","1700268546-76297.jpg","1700268546-77790.jpg","1700268546-78948.jpg","1700268546-80446.jpg","1700268546-81645.jpg","1700268546-87571.jpg","1700268546-87900.jpg","1700268546-88782.jpg","1700268546-90312.jpg","1700268546-91737.jpg","1700268546-92719.jpg","1700268546-96522.jpg","1700268547-11918.jpg","1700268547-12445.jpg","1700268547-14749.jpg","1700268547-15490.jpg","1700268547-17067.jpg","1700268547-17448.jpg","1700268547-19500.jpg","1700268547-20443.jpg","1700268547-21821.jpg","1700268547-24705.jpg","1700268547-29717.jpg","1700268547-33522.jpg","1700268547-33560.jpg","1700268547-34665.jpg","1700268547-35719.jpg","1700268547-38047.jpg","1700268547-38067.jpg","1700268547-38833.jpg","1700268547-40033.jpg","1700268547-40147.jpg","1700268547-41412.jpg","1700268547-46425.jpg","1700268547-54859.jpg","1700268547-57918.jpg","1700268547-58854.jpg","1700268547-59640.jpg","1700268547-60153.jpg","1700268547-61176.jpg","1700268547-61609.jpg","1700268547-62438.jpg","1700268547-62871.jpg","1700268547-63030.jpg","1700268547-64130.jpg","1700268547-64371.jpg","1700268547-64469.jpg","1700268547-64752.jpg","1700268547-65432.jpg","1700268547-68277.jpg","1700268547-68887.jpg","1700268547-69929.jpg","1700268547-71017.jpg","1700268547-71573.jpg","1700268547-72237.jpg","1700268547-74623.jpg","1700268547-79841.jpg","1700268547-81176.jpg","1700268547-82451.jpg","1700268547-82513.jpg","1700268547-82728.jpg","1700268547-85064.jpg","1700268547-85258.jpg","1700268547-89851.jpg","1700268547-92537.jpg","1700268548-11727.jpg","1700268548-11749.jpg","1700268548-12295.jpg","1700268548-14990.jpg","1700268548-15519.jpg","1700268548-15750.jpg","1700268548-16962.jpg","1700268548-20557.jpg","1700268548-21600.jpg","1700268548-23546.jpg","1700268548-24421.jpg","1700268548-25403.jpg","1700268548-25586.jpg","1700268548-27912.jpg","1700268548-34702.jpg","1700268548-35061.jpg","1700268548-36696.jpg","1700268548-38293.jpg","1700268548-38373.jpg","1700268548-38728.jpg","1700268548-47379.jpg","1700268548-48126.jpg","1700268548-49199.jpg","1700268548-52223.jpg","1700268548-53027.jpg","1700268548-55873.jpg","1700268548-57048.jpg","1700268548-57858.jpg","1700268548-58420.jpg","1700268548-58442.jpg","1700268548-63476.jpg","1700268548-64604.jpg","1700268548-66693.jpg","1700268548-69449.jpg","1700268548-70203.jpg","1700268548-72512.jpg","1700268548-74254.jpg","1700268548-75695.jpg","1700268548-77378.jpg","1700268548-79167.jpg","1700268548-79246.jpg","1700268548-80213.jpg","1700268548-85172.jpg","1700268548-86946.jpg","1700268548-87330.jpg","1700268548-88386.jpg","1700268548-89411.jpg","1700268548-89695.jpg","1700268548-90795.jpg","1700268548-91067.jpg","1700268548-92527.jpg","1700268548-94461.jpg","1700268548-97434.jpg","1700268549-10318.jpg","1700268549-10966.jpg","1700268549-13109.jpg","1700268549-13409.jpg","1700268549-15280.jpg","1700268549-17263.jpg","1700268549-21459.jpg","1700268549-24593.jpg","1700268549-24776.jpg","1700268549-27296.jpg","1700268549-27338.jpg","1700268549-27394.jpg","1700268549-29369.jpg","1700268549-30365.jpg","1700268549-30407.jpg","1700268549-33376.jpg","1700268549-35761.jpg","1700268549-43663.jpg","1700268549-45992.jpg","1700268549-51090.jpg","1700268549-53819.jpg","1700268549-54832.jpg","1700268549-56069.jpg","1700268549-56492.jpg","1700268549-56735.jpg","1700268549-57099.jpg","1700268549-57155.jpg","1700268549-57963.jpg","1700268549-61228.jpg","1700268549-61932.jpg","1700268549-62885.jpg","1700268549-66003.jpg","1700268549-66748.jpg","1700268549-67708.jpg","1700268549-68283.jpg","1700268549-68694.jpg","1700268549-70106.jpg","1700268549-71295.jpg","1700268549-73288.jpg","1700268549-78146.jpg","1700268549-83705.jpg","1700268549-83779.jpg","1700268549-84768.jpg","1700268549-85434.jpg","1700268549-86147.jpg","1700268549-86496.jpg","1700268549-86768.jpg","1700268549-91717.jpg","1700268549-93011.jpg","1700268549-93529.jpg","1700268549-95478.jpg","1700268549-95850.jpg","1700268549-99851.jpg","1700268550-10283.jpg","1700268550-10671.jpg","1700268550-12962.jpg","1700268550-14692.jpg","1700268550-15871.jpg","1700268550-18239.jpg","1700268550-18620.jpg","1700268550-21439.jpg","1700268550-24255.jpg","1700268550-24340.jpg","1700268550-24656.jpg","1700268550-25743.jpg","1700268550-27461.jpg","1700268550-30531.jpg","1700268550-31654.jpg","1700268550-36268.jpg","1700268550-36562.jpg","1700268550-36681.jpg","1700268550-39333.jpg","1700268550-40277.jpg","1700268550-40466.jpg","1700268550-42879.jpg","1700268550-43447.jpg","1700268550-49627.jpg","1700268550-50036.jpg","1700268550-51607.jpg","1700268550-55769.jpg","1700268550-56497.jpg","1700268550-58359.jpg","1700268550-58753.jpg","1700268550-58909.jpg","1700268550-60680.jpg","1700268550-63962.jpg","1700268550-66688.jpg","1700268550-67747.jpg","1700268550-67817.jpg","1700268550-68799.jpg","1700268550-72539.jpg","1700268550-73987.jpg","1700268550-75792.jpg","1700268550-76492.jpg","1700268550-78191.jpg","1700268550-80054.jpg","1700268550-80325.jpg","1700268550-80431.jpg","1700268550-81168.jpg","1700268550-82165.jpg","1700268550-83498.jpg","1700268550-85512.jpg","1700268550-94358.jpg","1700268550-99583.jpg","1700268551-13426.jpg","1700268551-15361.jpg","1700268551-18885.jpg","1700268551-20452.jpg","1700268551-20839.jpg","1700268551-24931.jpg","1700268551-28772.jpg","1700268551-31545.jpg","1700268551-33538.jpg","1700268551-34438.jpg","1700268551-34622.jpg","1700268551-36063.jpg","1700268551-37068.jpg","1700268551-37663.jpg","1700268551-42571.jpg","1700268551-43644.jpg","1700268551-43822.jpg","1700268551-45240.jpg","1700268551-45786.jpg","1700268551-47136.jpg","1700268551-47983.jpg","1700268551-49972.jpg","1700268551-51483.jpg","1700268551-53075.jpg","1700268551-53453.jpg","1700268551-54711.jpg","1700268551-55442.jpg","1700268551-56133.jpg","1700268551-65129.jpg","1700268551-65904.jpg","1700268551-67119.jpg","1700268551-67549.jpg","1700268551-70559.jpg","1700268551-71021.jpg","1700268551-73802.jpg","1700268551-74207.jpg","1700268551-74856.jpg","1700268551-76135.jpg","1700268551-77427.jpg","1700268551-77946.jpg","1700268551-78559.jpg","1700268551-78662.jpg","1700268551-79388.jpg","1700268551-83672.jpg","1700268551-88681.jpg","1700268551-89954.jpg","1700268551-92993.jpg","1700268551-93266.jpg","1700268551-96988.jpg","1700268551-97182.jpg","1700268551-99890.jpg","1700268552-11064.jpg","1700268552-15067.jpg","1700268552-18193.jpg","1700268552-21285.jpg","1700268552-27365.jpg","1700268552-29413.jpg","1700268552-29683.jpg","1700268552-31962.jpg","1700268552-32203.jpg","1700268552-33687.jpg","1700268552-38255.jpg","1700268552-39770.jpg","1700268552-42250.jpg","1700268552-42727.jpg","1700268552-46011.jpg","1700268552-48125.jpg","1700268552-49348.jpg","1700268552-51464.jpg","1700268552-51468.jpg","1700268552-53939.jpg","1700268552-54043.jpg","1700268552-56033.jpg","1700268552-58559.jpg","1700268552-60635.jpg","1700268552-61959.jpg","1700268552-62956.jpg","1700268552-63880.jpg","1700268552-64978.jpg","1700268552-65315.jpg","1700268552-65355.jpg","1700268552-65493.jpg","1700268552-67536.jpg","1700268552-68883.jpg","1700268552-69599.jpg","1700268552-71373.jpg","1700268552-73027.jpg","1700268552-74245.jpg","1700268552-74308.jpg","1700268552-77565.jpg","1700268552-79096.jpg","1700268552-80907.jpg","1700268552-82404.jpg","1700268552-83788.jpg","1700268552-84544.jpg","1700268552-89788.jpg","1700268552-92211.jpg","1700268552-93567.jpg","1700268552-94716.jpg","1700268552-94948.jpg","1700268552-97269.jpg","1700268552-99532.jpg","1700268553-10663.jpg","1700268553-11897.jpg","1700268553-12144.jpg","1700268553-13376.jpg","1700268553-13512.jpg","1700268553-20588.jpg","1700268553-20873.jpg","1700268553-22824.jpg","1700268553-23751.jpg","1700268553-24421.jpg","1700268553-25646.jpg","1700268553-27010.jpg","1700268553-27353.jpg","1700268553-27859.jpg","1700268553-29521.jpg","1700268553-30492.jpg","1700268553-30665.jpg","1700268553-30819.jpg","1700268553-33109.jpg","1700268553-33780.jpg","1700268553-35842.jpg","1700268553-38099.jpg","1700268553-39909.jpg","1700268553-39992.jpg","1700268553-40332.jpg","1700268553-40409.jpg","1700268553-41376.jpg","1700268553-47378.jpg","1700268553-49221.jpg","1700268553-50476.jpg","1700268553-54004.jpg","1700268553-55031.jpg","1700268553-56961.jpg","1700268553-57890.jpg","1700268553-58488.jpg","1700268553-63449.jpg","1700268553-63720.jpg","1700268553-64153.jpg","1700268553-68482.jpg","1700268553-69689.jpg","1700268553-74146.jpg","1700268553-74212.jpg","1700268553-76305.jpg","1700268553-79637.jpg","1700268553-81983.jpg","1700268553-83639.jpg","1700268553-93426.jpg","1700268553-94759.jpg","1700268553-95951.jpg","1700268553-99177.jpg","1700268553-99494.jpg","1700268553-99626.jpg","1700268553-99762.jpg","1700268554-11678.jpg","1700268554-12182.jpg","1700268554-14944.jpg","1700268554-15821.jpg","1700268554-17375.jpg","1700268554-19467.jpg","1700268554-21380.jpg","1700268554-22976.jpg","1700268554-23102.jpg","1700268554-23178.jpg","1700268554-25843.jpg","1700268554-27720.jpg","1700268554-30905.jpg","1700268554-35353.jpg","1700268554-37942.jpg","1700268554-42577.jpg","1700268554-43766.jpg","1700268554-45404.jpg","1700268554-48108.jpg","1700268554-49498.jpg","1700268554-50184.jpg","1700268554-52475.jpg","1700268554-52674.jpg","1700268554-53937.jpg","1700268554-54677.jpg","1700268554-57289.jpg","1700268554-57617.jpg","1700268554-57872.jpg","1700268554-58017.jpg","1700268554-59356.jpg","1700268554-60480.jpg","1700268554-61800.jpg","1700268554-63577.jpg","1700268554-65965.jpg","1700268554-66260.jpg","1700268554-66318.jpg","1700268554-67806.jpg","1700268554-68320.jpg","1700268554-69628.jpg","1700268554-69694.jpg","1700268554-75297.jpg","1700268554-79488.jpg","1700268554-81188.jpg","1700268554-82715.jpg","1700268554-84134.jpg","1700268554-87983.jpg","1700268554-89188.jpg","1700268554-94799.jpg","1700268554-96437.jpg","1700268554-97499.jpg","1700268554-98247.jpg","1700268555-10333.jpg","1700268555-10424.jpg","1700268555-14840.jpg","1700268555-14939.jpg","1700268555-15694.jpg","1700268555-21240.jpg","1700268555-21406.jpg","1700268555-25046.jpg","1700268555-28749.jpg","1700268555-29436.jpg","1700268555-30162.jpg","1700268555-30615.jpg","1700268555-30659.jpg","1700268555-36428.jpg","1700268555-38074.jpg","1700268555-39637.jpg","1700268555-39864.jpg","1700268555-40364.jpg","1700268555-42323.jpg","1700268555-43452.jpg","1700268555-44781.jpg","1700268555-47731.jpg","1700268555-48437.jpg","1700268555-55635.jpg","1700268555-56570.jpg","1700268555-56645.jpg","1700268555-57104.jpg","1700268555-57154.jpg","1700268555-59091.jpg","1700268555-59984.jpg","1700268555-61418.jpg","1700268555-65544.jpg","1700268555-68696.jpg","1700268555-70974.jpg","1700268555-73788.jpg","1700268555-77754.jpg","1700268555-80143.jpg","1700268555-81262.jpg","1700268555-83336.jpg","1700268555-84894.jpg","1700268555-85241.jpg","1700268555-85487.jpg","1700268555-87330.jpg","1700268555-87468.jpg","1700268555-89358.jpg","1700268555-92153.jpg","1700268555-92653.jpg","1700268555-93968.jpg","1700268555-94183.jpg","1700268555-95496.jpg","1700268555-96926.jpg","1700268556-11146.jpg","1700268556-13465.jpg","1700268556-13736.jpg","1700268556-19032.jpg","1700268556-20700.jpg","1700268556-21703.jpg","1700268556-22284.jpg","1700268556-24648.jpg","1700268556-29120.jpg","1700268556-32941.jpg","1700268556-34389.jpg","1700268556-35736.jpg","1700268556-37517.jpg","1700268556-44834.jpg","1700268556-46449.jpg","1700268556-46659.jpg","1700268556-48537.jpg","1700268556-52746.jpg","1700268556-53239.jpg","1700268556-53395.jpg","1700268556-60214.jpg","1700268556-60444.jpg","1700268556-60701.jpg","1700268556-63366.jpg","1700268556-64798.jpg","1700268556-64803.jpg","1700268556-66782.jpg","1700268556-69260.jpg","1700268556-70501.jpg","1700268556-72656.jpg","1700268556-73996.jpg","1700268556-76923.jpg","1700268556-78339.jpg","1700268556-80200.jpg","1700268556-81550.jpg","1700268556-81843.jpg","1700268556-82691.jpg","1700268556-88907.jpg","1700268556-90324.jpg","1700268556-93146.jpg","1700268556-94465.jpg","1700268556-97156.jpg","1700268556-98302.jpg","1700268626-22246.jpg","1700268626-22913.jpg","1700268626-32987.jpg","Archive.zip"]');


    $i = 0;
    foreach ($items as $key => $img) {
        $path = base_path('public/storage/images/' . $img);
        $path = 'public_html/public/storage/images/' . $img;
        $i++;
        if (!file_exists($path)) {
            echo $i . ". " . $img . " DNE.<br>";
            continue;
        }
        echo $i . ". " . $img . " Exists.<br>";
        try {
            unlink($path);
        } catch (\Throwable $th) {
            echo $th->getMessage() . "<br>";
        }
    }
    die();


    $folderPath = base_path('public/storage/images/');
    $folderPath = base_path('public/thumbs/done/');

    $biggest = 0;
    $tot = 0;
    // Check if the folder exists
    if (is_dir($folderPath)) {

        // Get the list of items in the folder
        $items = scandir($folderPath);

        die(json_encode($items));

        $i = 0;

        $imgs = [];
        // Loop through the items
        foreach ($items as $item) {


            // Exclude the current directory (.) and parent directory (..)
            if ($item != '.' && $item != '..') {


                $ext = pathinfo($item, PATHINFO_EXTENSION);
                if ($ext == null) {
                    return null;
                }
                $ext = strtolower($ext);


                if (!in_array($ext, [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                ])) {
                    continue;
                }

                $isMain = false;
                if (str_contains($item, 'm')) {
                    $isMain = true;
                }
                if ($isMain != true) {
                    if (!str_contains($item, '(')) {
                        $isMain = true;
                    }
                }

                $v_id = (int)($item);
                $an = Animal::where([
                    'v_id' => $v_id
                ])->first();
                if ($an == null) {
                    die("$v_id not found");
                    continue;
                }


                $img_id = $v_id . "-" . time() . '-' . rand(10000, 99999) . "." . $ext;


                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/done2/" . $img_id;
                $img = new ImageModel();
                $img->administrator_id = $an->administrator_id;
                $img->src = $img_id;
                $img->thumbnail = 'thumb_' . $img_id;
                $img->thumbnail = null;
                $img->parent_id = $an->id;
                $img->product_id = null;
                $img->size  = 0;
                $img->type  = 'Animal';
                $img->parent_endpoint  = 'Animal';
                $img->note  = 'First Photo';
                try {
                    $img->save();
                    if ($isMain) {
                        $an->photo = $img_id;
                        $an->save();
                    }
                    rename($source, $target);
                } catch (\Throwable $th) {
                }


                $i++;
                echo $i . ". " . $item . "<br>";
                continue;
                $source = $folderPath . "" . $item;
                $thumb = $folderPath . "thumb_" . $item;

                if (file_exists($thumb)) {
                    continue;
                }

                $source = $folderPath . "" . $item;
                $target = $folderPath . "thumb_" . $item;
                Utils::create_thumbail([
                    'source' => $source,
                    'target' => $target
                ]);

                $target_file_size = filesize($target);
                $target_file_size = $target_file_size / (1024 * 1024);
                $target_file_size = round($target_file_size, 2);
                $target_file_size = $target_file_size . " MB";

                $thumb_size = filesize($target);
                $thumb_size = $thumb_size / (1024 * 1024);
                $thumb_size = round($thumb_size, 2);
                $thumb_size = $thumb_size . " MB";

                echo "Original Size: " . $item . "<br>";
                echo "Original Size: " . $target_file_size . "<br>";
                echo "Thumb Size: " . $thumb_size . "<br>";
                echo "<hr>";

                die("compress");

                $id = (int)$item;
                if (20142 < 1000) {
                    die("ID is less than 1000 => $id");
                }

                $an = Animal::where([
                    'v_id' => $id
                ])->first();
                if ($an == null) {
                    continue;
                    die("Animal not exists => $id");
                }

                $img_id = time() . '-' . rand(10000, 99999) . "." . $ext;

                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/done/" . $img_id;
                rename($source, $target);
                $img = new \App\Models\Image();
                $img->administrator_id = $an->administrator_id;
                $img->src = $img_id;
                $img->thumbnail = 'thumb_' . $img_id;
                $img->parent_id = $an->id;
                $img->product_id = null;
                $img->size  = 0;
                $img->type  = 'Animal';
                $img->parent_endpoint  = 'Animal';
                $img->note  = 'First Photo';
                $img->save();

                echo $i . ". " . $item . "<br>";
                continue;

                $imgs[] = $id;

                $target = $folderPath . "/" . $item;
                $target_file_size = filesize($target);

                if ($target_file_size > $biggest) {
                    $biggest = $target_file_size;
                }
                $tot += $target_file_size;


                //echo $i.". ".$item . "<br>";
                $i++;
                continue;

                $i++;
                print_r($i . "<br>");



                $fileSize = filesize($folderPath . "/" . $item);
                $fileSize = $fileSize / (1024 * 1024);
                $fileSize = round($fileSize, 2);
                $fileSize = $fileSize . " MB";
                $url = "http://localhost:8888/ham/public/temp/pics-1/" . $item;

                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/thumb/" . $item;
                Utils::create_thumbail([
                    'source' => $source,
                    'target' => $target
                ]);

                echo "<img src='$url' alt='$item' width='550'/>";
                $target_file_size = filesize($target);
                $target_file_size = $target_file_size / (1024 * 1024);
                $target_file_size = round($target_file_size, 2);
                $target_file_size = $target_file_size . " MB";
                $url_2 = "http://localhost:8888/ham/public/temp/pics-1/thumb/" . $item;
                echo "<img src='$url_2' alt='$item' width='550' />";


                // Print the item's name
                echo "<b>" . $fileSize . "<==>" . $target_file_size . "<b><br>";
            }
        }
    } else {
        echo "The specified folder does not exist.";
    }

    die("<hr>");
    $i = 0;
    $farm = Farm::find(309);
    foreach ($imgs as $key => $v_id) {
        $an = Animal::where([
            'v_id' => $v_id
        ])->first();
        if ($an != null) {
            continue;
        }
        $an = new Animal();
        $an->administrator_id = $farm->administrator_id;
        $an->district_id = $farm->district_id;
        $an->sub_county_id = $farm->sub_county_id;
        $an->parish_id = $farm->parish_id;
        $an->status = 'Active';
        $an->type = 'Cattle';
        $an->e_id = '8000000000' . $v_id;
        $an->v_id = $v_id;
        $an->farm_id = $farm->id;
        $an->lhc = $farm->holding_code;
        $an->breed = 'Ankole';
        $an->sex = 'Female';
        $an->dob = Carbon::now()->subYears(2);
        $an->weight = 0;
        $an->group_id = 263;
        $an->save();
        $i++;
        echo $i . ". " . $v_id . "<br>";
    }
    die("done");

    dd($imgs);
    //8000000
    $biggest = $biggest / (1024 * 1024);
    $biggest = round($biggest, 2);
    $biggest = $biggest . " MB";
    $tot = $tot / (1024 * 1024);
    $tot_gb = $tot / 1024;
    $tot = round($tot, 2);
    echo "<hr>";
    $tot = $tot . " MB<br>";
    $tot = $tot_gb . " GB<br>";
    echo "Biggest: " . $biggest . "<br>";
    echo "Total: " . $tot . "<br>";
    echo "Count: " . $i . "<br>";
    die("done");
});


Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("gen");


Route::get('demo', function () {
    $not_found = [];
    $ans = Animal::where([
        'administrator_id' => 777,
        'type' => 'Cattle'
    ])
        ->orderBy('updated_at', 'desc')
        ->get();
    foreach ($ans as $key => $v) {
        $event = Event::where([
            'animal_id' => $v->id,
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($event == null) {
            $not_found[] = $v;
            continue;
        }

        $d1 = Carbon::now();
        $d2 = Carbon::parse($event->created_at);
        $dif = $d1->diffInDays($d2);
        if ($dif > 100) {
            $not_found[] = $v;
            continue;
        }

        $event = Event::where([
            'animal_id' => $v->id,
            'type' => 'Weight check',
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($event == null) {
            $not_found[] = $v;
            continue;
        }


        $d1 = Carbon::now();
        $d2 = Carbon::parse($event->created_at);
        $dif = $d1->diffInDays($d2);
        if ($dif > 10) {
            $not_found[] = $v;
            continue;
        }
    }
    $i = 0;
    foreach ($not_found as $key => $an) {
        $i++;
        echo $i . ".<br><b>VID</b>: {$an->v_id} <br> <b>WEI:</b> {$an->weight_text}<br><br>";
    }
    die("romina");
    return '<h2>DVO Lyantonde: <code>+256775679511</code></h2>' .
        '<h2>DVO Checkpoint officer: <code>+256706638491</code></h2>';
});
Route::get('generate-variables', [MarketController::class, 'generate_variables']);
Route::get('market', [MarketController::class, 'index'])->name('market');
Route::get('market/register', [MarketController::class, 'register'])->name('m-register');
Route::get('market/account-orders', [MarketController::class, 'account_orders'])->name('account-orders');
Route::get('market/account-logout', [MarketController::class, 'account_logout'])->name('account-logout');
Route::get('buy-now/{id}', [MarketController::class, 'buy_now'])->name('buy-now');
Route::post('buy-now/{id}', [MarketController::class, 'buy_now_post'])->name('buy-now-post');

Route::post('market/register', [MarketController::class, 'register_post'])
    ->middleware(RedirectIfAuthenticated::class)->name('m-register-post');


Route::match(['get', 'post'], '/process_thumbnails', [PrintController::class, 'prepareThumbnails']);
Route::match(['get', 'post'], '/print2', [PrintController::class, 'index']);
Route::match(['get', 'post'], '/print', [PrintController::class, 'index']);
Route::get('vaccination/{id}', [PrintController::class, 'print_vaccination']);


Route::match(['get'], '/register', [MainController::class, 'create_account_page']);
Route::get('process-photos', [MainController::class, 'process_photos']);
Route::match(['post'], '/register', [MainController::class, 'create_account_save']);



Route::get('/compress', function () {

    foreach (Image::where([
        'administrator_id' => 873,
    ])->get() as $key => $img) {
        $img->create_thumbail();
        echo ($img->thumbnail . "<br>");
        die("done");
    }

    dd('dine');

    $directory = 'public/temp_pics/DONE/';
    // Get the list of files in the directory
    $done = scandir($directory . '/done');
    $files = array_diff($done, array('.', '..'));
    $uniques = [];

    $i = 0;


    foreach ($files as $key => $pic) {


        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        $img = Image::where([
            'src' => trim($pic)
        ])->first();
        if ($img != null) {
            continue;
        }

        $pics = explode('-', $pic);
        if (!isset($pics[0])) {
            die("nott found");
        }
        $_pic = $pics[0];

        $an = Animal::where([
            'v_id' => $_pic
        ])->first();
        if ($an == null) {
            die('Animal not found');
        }

        $img = new Image();
        $img->administrator_id = 873;
        $img->src = trim($pic);
        $img->thumbnail = null;
        $img->parent_id = $an->id;
        $img->product_id = $an->id;
        $img->type = 'animal';
        $img->parent_endpoint = 'animal';
        $img->note = 'New Photo';
        $img->save();
        if (str_contains(strtolower($pic), 'm')) {
            $an->photo = 'storage/images/' . $pic;
            $an->save();
        }
        echo ("$i. DONE => " . $img->src . "<br>");
        $i++;
    }
    die("as");

    die('done');

    dd($f2);
    foreach ($files as $key => $pic) {
        $pics = explode(' ', $pic);
        if (!isset($pics[0])) {
            die("nott found");
        }
        $_pic = $pics[0];

        if (in_array($_pic, $uniques)) {
            continue;
        }
        $uniques[] = $_pic;

        $an = Animal::where([
            'v_id' => $_pic
        ])->first();
        if ($an != null) {
            continue;
        }

        $an = new Animal();
        $an->administrator_id = 873;
        $an->status = 'Active';
        $an->type = 'Cattle';
        $an->e_id = '8000000000' . $_pic;
        $an->v_id = $_pic;
        $an->farm_id = 309;
        $an->breed = 'Ankole';
        $an->sex = 'Female';
        $an->dob = Carbon::now()->subYears(4);
        $an->fmd = Carbon::now()->subYears(4);
        $an->save();
        echo $i . ". " . $pic . "<br>";

        $i++;
    }

    die();

    $files = scandir($directory);

    set_time_limit(-1);
    ini_set('memory_limit', '-1');
    // Remove . and .. from the list
    $files = array_diff($files, array('.', '..'));
    foreach ($files as $src) {
        if (in_array($src, $done)) {
            echo $src . "<===done <br>";
            continue;
        }
        $i++;
        try {
            $thumb = Utils::create_thumbail([
                'source' => $directory . $src,
                'target' => 'public/temp_pics/DONE/done/' . $src,
                'quality' => 40,

            ]);
        } catch (\Throwable $th) {
            echo "FAILED ==> $src<br>";
            continue;
        }
        echo "<h2>$i. $src ===> " . round(filesize('public/temp_pics/DONE/' . $src) / (1024 * 1024), 2) . "MBs => " . round(filesize('public/temp_pics/DONE/done/' . $src) / (1024 * 1024), 2) . " MBs</h2>";

        echo '<img width="500" src="temp_pics/DONE/' . $src . '" >';
        echo '<img width="500" src="temp_pics/DONE/done/' . $src . '" >';
        echo "<hr>";

        //        unlink('public/temp_pics/DONE/' . $src);

    }
})->name("gen");

Route::get('/{slug}', [MarketController::class, 'product'])->name('product');
