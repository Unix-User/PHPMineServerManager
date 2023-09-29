<?php
use App\Http\Controllers\MinecraftController;
use App\Http\Controllers\MinecraftRconController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UpdatePostsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
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
Route::get('rules', [MinecraftController::class, 'serverRules'])->name('rules');
Route::get('donations', [MinecraftController::class, 'donations'])->name('donations');
Route::get('factionCommands', [MinecraftController::class, 'factionCommands'])->name('factionCommands');

// updates
Route::resource('update/posts', UpdatePostsController::class)->names([
    'index' => 'update.posts',
    'store' => 'update.posts.store',
    'show' => 'update.posts.show',
    'update' => 'update.posts.update',
    'destroy' => 'update.posts.delete',
]);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // map
    Route::get('/map', function () {
        return Inertia::render('MapIframe');
    })->name('map');

    Route::get('/factions', function () {
        return Inertia::render('Factions');
    })->name('factions');


    Route::post('/run-poi-update', [MinecraftController::class, 'runPoiUpdate']);
    Route::post('/run-map-update', [MinecraftController::class, 'runMapUpdate']);

    // shop
    Route::resource('shop/items', ShopItemController::class)->names([
        'index' => 'shop.items',
        'store' => 'shop.items.store',
        'show' => 'shop.items.show',
        'update' => 'shop.items.update',
        'destroy' => 'shop.items.delete',
    ]);

    

    // rcon
    Route::get('/rcon', function () {
        if (Auth::user()->roles->pluck('name')->contains('admin')) {
            return Inertia::render('RconConsole');
        } else {
            abort(403, 'Unauthorized');
        }
    })->name('rcon');

    Route::post('/execute-command', [MinecraftRconController::class, 'executeCommand'])->name('execute-command');
    Route::post('/close-connection', [MinecraftRconController::class, 'closeRconConnection'])->name('close-connection');

    // status
    Route::get('status', [StatusController::class, 'show'])->name('status');
    Route::get('busy', [StatusController::class, 'overviewerIsRunning'])->name('busy');
});