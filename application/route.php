<?php

use think\Route;
Route::get('/','');

Route::group('back/brand',function(){
    //品牌管理
    Route::get('','back/brand/index');
    Route::get('index','back/brand/index');
    Route::any('titleUniqueCheck','back/brand/titleUniqueCheck');
    Route::any('set','back/brand/set');
    Route::any('del','back/brand/del');
});

//代码生成
Route::get('back/make/table','back/make/table');
Route::get('back/make/info','back/make/info');
Route::post('back/make/generate','back/make/generate');

   
        Route::group('back/role',function(){
    //角色管理
    Route::get('','back/role/index');
    Route::get('index','back/role/index');
    Route::any('set','back/role/set');
    Route::any('del','back/role/del');
});