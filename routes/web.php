<?php
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

Route::get('/', 'HomeController@index')->name('home');
Route::get('/netsuite/search', 'NetSuiteController@searchVendorBill');
Route::get('/ap_voucher_print/{id}', 'NetSuiteController@print_voucher');
Route::post('/new_ap_voucher/{id}', 'NetSuiteController@new_ap');
Route::put('/update_ap_voucher/{id}', 'NetSuiteController@update_ap');


