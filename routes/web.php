<?php

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
Route::get('admin/login','Admin\LoginController@index');
Route::post('admin/loginCheck','Admin\LoginController@loginCheck');
Route::get('admin/logout','Admin\LoginController@logout');
Route::group(['prefix'=>'admin','namespace'=>'Admin','middleware'=>['login_check']],function (){
    Route::get('/','IndexController@index');
    Route::get('/first','IndexController@first');
    Route::any('uploadImg','BaseController@uploadImg');

    Route::group(['prefix'=>'poll'],function(){     //投票管理
        Route::get('index','PollController@index');
        Route::get('add','PollController@add');
        Route::post('addChange','PollController@addChange');
        Route::get('edit/{poll_id}','PollController@edit');
        Route::post('editChange','PollController@editChange');
        Route::post('changePollStatus','PollController@changePollStatus');    //改变投票状态接口 传参 poll_id poll_status
        Route::get('seePollResult/{poll_id}','PollController@seePollResult');             //查看投票结果接口
        Route::post('getOptions','PollController@getOptions');        //获取选项接口
        Route::get('seePollInput/{option_id}','PollController@seePollInput');      //查看某个填空题的回答
        Route::get('seePollUpload/{option_id}','PollController@seePollUpload');      //查看某个上传题的回答
    });

    Route::post('changePasswd','LoginController@changePasswd'); //改密
});

Route::group(['prefix'=>'api/','namespace'=>'Index'],function(){
    Route::get('/poll/{poll}','PollController@index');   //投票页
    Route::get('/poll','PollController@pollList');      //列表页
    Route::post('/userPoll','PollController@userPoll');  // 投票动作
    Route::get('test/{poll}','PollController@test');   //测试
});

//Route::any('notify/valid','index\WeChatAuthController@valid');