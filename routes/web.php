<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect('/admin');
});

/* // Home route con Livewire
Route::livewire('/', 'pages::welcome')->name('welcome');

// About route con Livewire
Route::livewire('/about', 'pages::about')->name('about'); */