<?php

use App\Http\Controllers\MinecraftController;
use App\Http\Controllers\MinecraftRconController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\StatusController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
--------------------------------------------------------------------------
 Web Routes
--------------------------------------------------------------------------

 Here is where you can register web routes for your application. These
 routes are loaded by the RouteServiceProvider within a group which
 contains the "web" middleware group. Now create something great!

*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('/rcon', function () {
        return Inertia::render('RconConsole');
    })->name('rcon');
    Route::get('/map', function () {
        return Inertia::render('MapIframe');
    })->name('map');
    Route::post('/run-poi-update', [MinecraftController::class, 'runPoiUpdate']);
    Route::post('/run-map-update', [MinecraftController::class, 'runMapUpdate']);
    Route::prefix('/shop/items')->group(function () {
        Route::get('/', function () {
            return Inertia::render('ShopItems');
        })->name('shop.items');
        Route::post('/', [ShopItemController::class, 'store'])->name('shop.items.store');
        Route::get('/{id}', function ($id) {
            return Inertia::render('ShopItem', ['id' => $id]);
        })->name('shop.items.show');
        Route::put('/{id}', [ShopItemController::class, 'update'])->name('shop.items.update');
        Route::delete('/{id}', [ShopItemController::class, 'destroy'])->name('shop.items.delete');
    });

    Route::prefix('/')->group(function () {
        Route::post('execute-command', [MinecraftRconController::class, 'executeCommand'])->name('executeCommand');
        Route::post('access-rcon-terminal', [MinecraftRconController::class, 'accessRconTerminal'])->middleware('auth:sanctum')->name('accessRconTerminal');
        Route::post('close-rcon-connection', [MinecraftRconController::class, 'closeRconConnection'])->name('closeRconConnection');
        Route::get('/status', [StatusController::class, 'show'])->name('status');
    });
});