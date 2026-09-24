<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForumController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'global')->name('home');
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::view('/register', 'auth.register')->middleware('guest')->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/forum', [ForumController::class, 'index'])->name('forum');
Route::post('/forum/posts', [ForumController::class, 'store'])->middleware('auth')->name('posts.store');
Route::patch('/forum/posts/{post}', [ForumController::class, 'update'])->middleware('auth')->name('posts.update');
Route::delete('/forum/posts/{post}', [ForumController::class, 'destroy'])->middleware('auth')->name('posts.destroy');
Route::get('/admin', [AdminController::class, 'index'])->middleware(['auth', 'role:administrator,moderator'])->name('admin');
Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.users.update');
Route::patch('/admin/posts/{post}', [AdminController::class, 'updatePost'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.posts.update');
Route::post('/admin/update/check', [AdminController::class, 'checkUpdate'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.update.check');
Route::post('/admin/update/install', [AdminController::class, 'installUpdate'])->middleware(['auth', 'role:administrator'])->name('admin.update.install');