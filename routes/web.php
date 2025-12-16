<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // CRUD routes for Gizmo
    Route::resource('gizmos', App\Http\Controllers\GizmoController::class);

    // CRUD routes for Post
    Route::resource('posts', App\Http\Controllers\PostController::class);

    // CRUD routes for Widget
    Route::resource('widgets', App\Http\Controllers\WidgetController::class);
});

require __DIR__.'/settings.php';
