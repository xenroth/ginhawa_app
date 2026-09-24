<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommunityInteractionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'global')->middleware('auth')->name('home');
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::view('/register', 'auth.register')->middleware('guest')->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth')->name('profile');
Route::patch('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::post('/profile/verification', [ProfileController::class, 'submitVerification'])->middleware('auth')->name('profile.verification');
Route::post('/community/posts/{post}/comments', [CommunityInteractionController::class, 'comment'])->middleware('auth')->name('community.comments.store');
Route::post('/members/{user}/connect', [CommunityInteractionController::class, 'connect'])->middleware('auth')->name('members.connect');
Route::post('/members/{user}/message', [CommunityInteractionController::class, 'message'])->middleware('auth')->name('members.message');
Route::get('/community', [ForumController::class, 'index'])->middleware('auth')->name('community');
Route::get('/community', [ForumController::class, 'index'])->middleware('auth')->name('forum');
Route::post('/community/posts', [ForumController::class, 'store'])->middleware('auth')->name('posts.store');
Route::patch('/community/posts/{post}', [ForumController::class, 'update'])->middleware('auth')->name('posts.update');
Route::delete('/community/posts/{post}', [ForumController::class, 'destroy'])->middleware('auth')->name('posts.destroy');
Route::get('/admin', [AdminController::class, 'index'])->middleware(['auth', 'role:administrator,moderator'])->name('admin');
Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.users.update');
Route::patch('/admin/posts/{post}', [AdminController::class, 'updatePost'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.posts.update');
Route::delete('/admin/posts/{post}', [AdminController::class, 'destroyPost'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.posts.destroy');
Route::post('/admin/update/check', [AdminController::class, 'checkUpdate'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.update.check');
Route::post('/admin/update/install', [AdminController::class, 'installUpdate'])->middleware(['auth', 'role:administrator'])->name('admin.update.install');
Route::get('/admin/settings', [AdminController::class, 'settings'])->middleware(['auth', 'role:administrator'])->name('admin.settings');
Route::get('/admin/verifications', [AdminController::class, 'verifications'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.verifications');
Route::patch('/admin/settings', [AdminController::class, 'updateSettings'])->middleware(['auth', 'role:administrator'])->name('admin.settings.update');
Route::post('/admin/roles', [AdminController::class, 'storeRole'])->middleware(['auth', 'role:administrator'])->name('admin.roles.store');
Route::post('/admin/sectors', [AdminController::class, 'storeSector'])->middleware(['auth', 'role:administrator'])->name('admin.sectors.store');
Route::post('/admin/directives', [AdminController::class, 'storeDirective'])->middleware(['auth', 'role:administrator'])->name('admin.directives.store');
Route::patch('/admin/verifications/{verification}', [AdminController::class, 'reviewVerification'])->middleware(['auth', 'role:administrator,moderator'])->name('admin.verifications.update');