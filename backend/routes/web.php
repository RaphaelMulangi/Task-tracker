<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// This is an API-only backend (the React app is the actual frontend).
// Named so Laravel's auth middleware has somewhere to resolve `route('login')`
// to when a guest request doesn't send `Accept: application/json`.
Route::get('/login', fn () => response()->json(['message' => 'Unauthenticated.'], 401))->name('login');
