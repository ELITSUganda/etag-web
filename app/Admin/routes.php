<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('districts', DistrictController::class);
    $router->resource('sub-counties', SubCountyController::class);
    $router->resource('farms', FarmController::class);
    $router->resource('animals', AnimalController::class);
    $router->resource('events', EventController::class);
    $router->resource('movements', MovementController::class);
    $router->resource('diseases', DiseaseController::class);
    $router->resource('medicines', MedicineController::class);
    $router->resource('vaccines', VaccineController::class);
    $router->resource('movement-items', MovementsItemsController::class);
    $router->resource('slaughter-records', SlaughterRecordController::class);
    $router->resource('archived-animals', ArchivedAnimalController::class);
    $router->resource('sales', AnimalSalesController::class);
    $router->resource('check-points', CheckPointController::class);
    $router->resource('check-point-records', CheckPointRecordController::class);
    $router->resource('product-categories', ProductCategoryController::class);
    $router->resource('products', ProductController::class); 
    $router->resource('orders', OrderController::class);
    $router->resource('drug-categories', DrugCategoryController::class);
    $router->resource('form-drug-sellers', FormDrugSellerController::class);
    $router->resource('form-drug-stock-approvals', FormDrugStockApprovalController::class);

});
