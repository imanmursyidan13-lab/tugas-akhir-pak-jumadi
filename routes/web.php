<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BalasanController;
use App\Http\Controllers\CastController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\KritikController;
use App\Http\Controllers\PeranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [FilmController::class, 'movieHome'])->name('home');
Route::get('/movies', [FilmController::class, 'movies'])->name('movies');
Route::get('/movies/{film}', [FilmController::class, 'show'])->name('movies.show');
Route::get('/movies/genre/{genre}', [FilmController::class, 'moviesByGenre'])->name('genre');

/*
|--------------------------------------------------------------------------
| Autentikasi (login, register, logout)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // komentar + rating (hanya user yang login)
    Route::post('/movies/{film}/comments', [KritikController::class, 'comment'])->name('comments.store');

    // balas komentar (siapa pun yang login boleh membalas komentar user lain)
    Route::post('/comments/{kritik}/balasan', [BalasanController::class, 'store'])->name('balasan.store');
    Route::delete('/balasan/{balasan}', [BalasanController::class, 'destroy'])->name('balasan.destroy');

    // watchlist / "ingin ditonton" milik sendiri
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/movies/{film}/watchlist', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

    // profil milik sendiri
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Admin  (harus login DAN role admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('films', FilmController::class)->except('show');
    Route::resource('genres', GenreController::class)->except('show');
    Route::resource('casts', CastController::class)->except('show');
    Route::resource('perans', PeranController::class)->except('show');
    Route::resource('kritiks', KritikController::class)->except(['show']);
    Route::resource('users', UserController::class)->except('show');
});
