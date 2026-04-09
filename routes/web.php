<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\Api\RecipeApiController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\WeatherController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather');
    Route::get('/api/weather', [WeatherController::class, 'search']);
    Route::get('/map', [MarkerController::class, 'index'])->name('map');
    Route::post('/markers', [MarkerController::class, 'store'])->name('markers.store');
    Route::put('/markers/{marker}', [MarkerController::class, 'update'])->name('markers.update');
    Route::delete('/markers/{marker}', [MarkerController::class, 'destroy'])->name('markers.destroy');
    Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
    Route::get('/blog/{post}', [PostController::class, 'show'])->name('blog.show');
    Route::post('/blog', [PostController::class, 'store'])->name('blog.store');
    Route::put('/blog/{post}', [PostController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{post}', [PostController::class, 'destroy'])->name('blog.destroy');
    Route::post('/blog/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
    Route::get('/shop/checkout', [OrderController::class, 'checkout'])->name('shop.checkout');
    Route::post('/shop/checkout', [OrderController::class, 'store'])->name('shop.order');
    Route::get('/shop/success/{order}', [OrderController::class, 'success'])->name('shop.success');
    Route::get('/shop/cancel/{order}', [OrderController::class, 'cancel'])->name('shop.cancel');
    Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
    Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
    Route::inertia('/music', 'Music')->name('music');
});

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/recipes', [RecipeApiController::class, 'index']);
    Route::get('/api/recipes/{recipe}', [RecipeApiController::class, 'show']);
});

require __DIR__ . '/settings.php';
