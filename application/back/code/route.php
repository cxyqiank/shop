Route::group('back/%table%',function(){
    //%name%管理
    Route::get('','back/%table%/index');
    Route::get('index','back/%table%/index');
    Route::any('set','back/%table%/set');
    Route::any('del','back/%table%/del');
});