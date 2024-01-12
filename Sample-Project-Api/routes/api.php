<?php

use App\Http\Controllers\SpecimenTrackingController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::prefix('/auth')
    ->controller(AuthController::class)
    ->group(function () {
            Route::post('/register', 'register')->name('auth.register');
            Route::post('/login', 'login')->name('auth.login');
});

Route::group(['middleware' => 'jwt', 'prefix' => 'v1'], function () {

    Route::prefix('/user')
    ->controller(AuthController::class)
    ->group(function () {
        Route::get('/profile', 'profile')->name('user.profile');
        Route::post('/logout', 'logout')->name('user.logout');
        Route::post('/refresh', 'refresh')->name('user.refresh');
    });

    Route::prefix('/specimens')
    ->controller(SpecimenTrackingController::class)
    ->group(function () {
        Route::post('/', 'createSample')->name('sample.create');
        Route::post('/feeding/{specimenForm}', 'createFeeding')->name('feeding.create');
        Route::put('/update-feeding/{specimenForm}', 'updateFeeding')->name('feeding.update');
        Route::post('/courier-information', 'courierInformation')->name('courier.create');
        Route::get('/show-couriers', 'showCouriers')->name('courier.show');
        Route::get('/all-samples', 'showAllSample')->name('allSample.show');
        Route::get('/courier-sample/{trackingNumber}', 'showCourierSamples')->name('courierSample.show');
        Route::get('/refresh-samples', 'courierSampleRefresh')->name('refreshSample.show');
        Route::get('/refresh-specimen', 'specimenRefresh')->name('specimenRefresh.show');
        Route::get('{specimenForm}/', 'showSpecificSample')->name('specificSample.show');
        Route::put('{specimenForm}/', 'updateSample')->name('sample.update');
        Route::post('/send-samples', 'sendSamples')->name('sample.send');
        Route::post('/update-checked', 'updateCheckStatus')->name('samples.updateCheckedStatus');
        Route::delete('{specimenForm}/', 'delete')->name('sample.delete');
        
    });
});
