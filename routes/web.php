<?php
use App\Http\Controllers\CatalogueController;
use Illuminate\Support\Facades\Route;

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
Route::get('/', function () {
    return view('welcome');
});

Route::post('catalogue/upload', [CatalogueController::class,'upload']);

Route::post('catalogue/checkJob', [CatalogueController::class,'checkUploadStatus']);

Route::any('{slug}', function () {
    return view('welcome');
});