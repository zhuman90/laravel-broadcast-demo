<?php

use App\Events\MessageEvent;
use App\Events\UserUpdateEvent;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BroadcastAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {

    // 获取联系人
    Route::get('/contacts', function () {
        return view('contacts', [
            'users' => App\Models\User::all(),
        ]);
    });

    // 发送消息
    Route::post('/contacts/send', function (Request $request) {
        $toUser = App\Models\User::findOrFail($request->input('user_id'));
        MessageEvent::dispatch(auth()->user(), $toUser, $request->input('message'));
        return response()->json([
            'from_user' => auth()->user(),
            'to_user' => auth()->user(),
            'message' => $request->input('message'),
        ]);
    });

    // 更新用户信息
    Route::put('/user', function () {
        $user = auth()->user();
        $user->update(request()->all());

        UserUpdateEvent::dispatch($user);
        return response()->json($user);
    });
});

