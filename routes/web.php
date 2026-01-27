<?php

use Illuminate\Support\Facades\Route;

// Home route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Admin route - redirect basic users to home
/* Route::get('/admin', function () {
    if (auth()->check() && auth()->user()->hasRole('user')) {
        return redirect()->route('home');
    }
})->name('admin');
 */