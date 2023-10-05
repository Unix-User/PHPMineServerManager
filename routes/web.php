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
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;

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

// other
Route::get('donations', [MinecraftController::class, 'donations'])->name('donations');
Route::get('rules', [MinecraftController::class, 'serverRules'])->name('rules');

// help
Route::get('factionCommands', [MinecraftController::class, 'factionCommands'])->name('factionCommands');
Route::get('currencies', [MinecraftController::class, 'currencies'])->name('currencies');

/** Oauth e login social */
$loginController = app(LoginController::class);

Route::get('auth/github', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'github');
})->name('github');
Route::get('auth/github/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'github');
})->name('github.callback');

Route::get('auth/google', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'google');
})->name('google');
Route::get('auth/google/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'google');
})->name('google.callback');

Route::get('auth/facebook', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'facebook');
})->name('facebook');
Route::get('auth/facebook/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'facebook');
})->name('facebook.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // pages
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/map', fn() => Inertia::render('MapIframe'))->name('map');
    Route::get('/factions', fn() => Inertia::render('Factions'))->name('factions');
    Route::get('/help', fn() => Inertia::render('Help'));

    // shop
    Route::resource('shop/items', ShopItemController::class)->names([
        'index' => 'shop.items',
        'store' => 'shop.items.store',
        'show' => 'shop.items.show',
        'update' => 'shop.items.update',
        'destroy' => 'shop.items.delete',
    ]);

    // updates
    Route::resource('update/posts', UpdatePostsController::class)->names([
        'index' => 'update.posts',
        'store' => 'update.posts.store',
        'show' => 'update.posts.show',
        'update' => 'update.posts.update',
        'destroy' => 'update.posts.delete',
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

    Route::post('/run-poi-update', [MinecraftController::class, 'runPoiUpdate']);
    Route::post('/run-map-update', [MinecraftController::class, 'runMapUpdate']);

    Route::get('status', [StatusController::class, 'show'])->name('status');
    Route::get('busy', [StatusController::class, 'overviewerIsRunning'])->name('busy');
});