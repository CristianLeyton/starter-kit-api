<?php

use Illuminate\Support\Facades\Route;


// Home route con Livewire
Route::livewire('/', 'pages::welcome')->name('welcome');

// About route con Livewire
Route::livewire('/about', 'pages::about')->name('about');