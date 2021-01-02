<?php

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
Route::namespace('Api')->group( function(){
    Route::post('/usuario/store', 'UsuariosController@store');
    
    Route::get('/usuario/index', 'UsuariosController@index')->middleware('JwtAuthenticate');
    
    Route::put('/usuario/alterarsenha', 'AuthenticateController@alterarSenha')->middleware('JwtAuthenticate');
    
    Route::put('/usuario/atualizardados', 'UsuariosController@update')->middleware('JwtAuthenticate');
    
    Route::delete('/usuario/delete', 'UsuariosController@destroy')->middleware('JwtAuthenticate');
    
    Route::post('/login', 'AuthenticateController@login');
});