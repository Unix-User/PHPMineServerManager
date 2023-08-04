<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\MinecraftRconController;
use App\Http\Controllers\MinecraftOverviewerController;

/*
--------------------------------------------------------------------------
 API Routes
--------------------------------------------------------------------------

 Here is where you can register API routes for your application. These
 routes are loaded by the RouteServiceProvider and all of them will
 be assigned to the "api" middleware group. Make something great!

*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/status', [StatusController::class, 'show'])->name('status');
Route::post('/execute-command', [MinecraftRconController::class, 'executeCommand'])->name('executeCommand');
Route::post('/access-rcon-terminal', [MinecraftRconController::class, 'accessRconTerminal'])->name('accessRconTerminal');
Route::post('/close-rcon-connection', [MinecraftRconController::class, 'closeRconConnection'])->name('closeRconConnection');
Route::get('/generate-maps', [MinecraftOverviewerController::class, 'index'])->name('generateMaps');