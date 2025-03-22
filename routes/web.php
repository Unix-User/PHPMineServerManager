<?php

use App\Http\Controllers\MinecraftController;
use App\Http\Controllers\MinecraftRconController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\StatusController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\JsonApiReloadedController;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Controllers\AccountLinkController;

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



// status
Route::get('status', [StatusController::class, 'show'])->name('status');

// discord invite
Route::get('invite', fn () => redirect(env('DISCORD_INVITE_URL')))->name('invite');

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

Route::match(['get', 'post'], 'auth/google', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'google');
})->name('google');
Route::match(['get', 'post'], 'auth/google/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'google');
})->name('google.callback');

Route::get('auth/facebook', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'facebook');
})->name('facebook');
Route::get('auth/facebook/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'facebook');
})->name('facebook.callback');

Route::get('auth/discord', function () use ($loginController) {
    return $loginController->redirectToProvider(request(), 'discord');
})->name('discord');
Route::get('auth/discord/callback', function () use ($loginController) {
    return $loginController->handleProviderCallback(request(), 'discord');
})->name('discord.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // pages
    Route::get('/home', fn () => Inertia::render('Home'))->name('home');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/map', fn () => Inertia::render('MapIframe'))->name('map');
    Route::get('/factions', fn () => Inertia::render('Factions'))->name('factions');
    Route::get('/help', fn () => Inertia::render('Help'));
    Route::get('/updates', fn () => Inertia::render('UpdatesPage'))->name('updates');
    
    // shop
    Route::get('/shop', [ShopItemController::class, 'index'])->name('shop');
    Route::resource('shop/items', ShopItemController::class)->names([
        'store' => 'shop.items.store',
        'show' => 'shop.items.show',
        'update' => 'shop.items.update',
        'destroy' => 'shop.items.delete',
    ]);
    

    // // updates
    // Route::resource('update/posts', UpdatePostsController::class)->names([
    //     'index' => 'update.posts',
    //     'store' => 'update.posts.store',
    //     'show' => 'update.posts.show',
    //     'update' => 'update.posts.update',
    //     'destroy' => 'update.posts.delete',
    // ]);

    // backups
    Route::get('/backups', function () {
        if (Auth::user()->roles->pluck('name')->contains('admin')) {
            return Inertia::render('Backups');
        } else {
            abort(403, 'Unauthorized');
        }
    })->name('backups');
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

    Route::get('busy', [StatusController::class, 'overviewerIsRunning'])->name('busy');

    Route::prefix('discord')->group(function () {
        Route::post('send-message', [DiscordController::class, 'sendMessage'])->name('send-message');
        Route::get('get-messages', [DiscordController::class, 'getChannelMessages'])->name('get-messages');
        Route::get('get-updates', [DiscordController::class, 'getServerUpdates'])->name('get-updates');
        Route::post('webhook', [DiscordController::class, 'handleWebhook'])->name('webhook');
    });
    
    Route::prefix('api')->group(function () {
        Route::get('/get-latest-chats', function () {
            $req = new HttpRequest;
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->getLatestChatsWithLimit($req->merge(['limit' => 30]));
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.get-latest-chats');

        Route::post('/execute-command', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->runCommand();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.execute-command');

        Route::get('/check-connection', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->checkServerConnection();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.check-connection');

        Route::post('/teleport-player', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->teleportPlayer();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.teleport-player');

        Route::post('/give-player-item', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->givePlayerItem();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.give-player-item');

        Route::post('/set-world-time', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->setWorldTime();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.set-world-time');

        Route::get('/server-version', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->getServerVersion();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.server-version');

        Route::post('/ban-player', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->banPlayer();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.ban-player');

        Route::post('/unban-player', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->unbanPlayer();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.unban-player');

        Route::post('/online-players', function (Request $request) {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                $worldName = $request->input('worldName');
                return app(JsonApiReloadedController::class)->getOnlinePlayerNamesInWorld($worldName);
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.online-players');

        Route::get('/player-count', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->getPlayerCount();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.player-count');

        Route::get('/get-java-memory-usage', function () {
            if (Auth::user()->roles->pluck('name')->contains('admin')) {
                return app(JsonApiReloadedController::class)->getJavaMemoryUsage();
            } else {
                abort(403, 'Unauthorized');
            }
        })->name('api.getJavaMemoryUsage');
    });
    
    Route::post('/account/link/register', [AccountLinkController::class, 'sendConfirmationEmail'])->name('account.link.register');
    Route::get('/account/link/confirm/{token}', [AccountLinkController::class, 'confirm'])->name('account.link.confirm');
    Route::post('/minecraft-password/request-reset', [AccountLinkController::class, 'resetPassword'])->name('minecraft-password.request-reset');
    Route::post('/account/unlink', [AccountLinkController::class, 'unlinkAccount'])->name('account.unlink');

    Route::get('/lsdirectory/{directoryPath?}', function (HttpRequest $request, $directoryPath = null) {
        if (empty($directoryPath)) {
            $directoryPath = './';
        } else {
            $directoryPath = './' . ltrim($directoryPath, '/');
        }
        $request->merge(['directoryPath' => $directoryPath]);
        return app(JsonApiReloadedController::class)->fsListDirectory($request);
    })->where('directoryPath', '.*')->name('list-directory');
});

