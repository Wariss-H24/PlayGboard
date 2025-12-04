<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TraducteurController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\CompteController;


Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/traducteur', [TraducteurController::class, 'index'])->middleware(['auth', 'verified'])->name('traducteur');

Route::get('/historique', [HistoriqueController::class, 'index'])->middleware(['auth', 'verified'])->name('historique');

Route::get('/parametres', [ParametreController::class, 'index'])->middleware(['auth', 'verified'])->name('parametres');

Route::get('/mon-compte', [CompteController::class, 'index'])->middleware(['auth', 'verified'])->name('compte');

require __DIR__.'/settings.php';