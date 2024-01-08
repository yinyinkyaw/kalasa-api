<?php


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\MediumController;
use App\Http\Controllers\Api\ArtworkController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Api\ArtworkCategoryController;
use App\Http\Controllers\Api\CollectionController;

// End user routes
Route::prefix('enduser')->group(function() {
    Route::resource('contact', ContactController::class)->only('store');

    Route::prefix('artist')->controller(ArtistController::class)->group(function() {
        Route::get('/list', 'enduserArtistList');
        Route::get('/search-by-name', 'enduserSearchByName');
        Route::get('/{id}', 'show');
    });

    Route::prefix('artwork')->controller(ArtworkController::class)->group(function() {
        Route::get('/list', 'index');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/{id}', 'show');
        Route::get('/get-by-category/{category}', 'getByCategory');
        Route::get('/get-by-artist/{artist}', 'getByArtist');
    });

    Route::prefix('blog')->controller(BlogController::class)->group(function() {
        Route::get('/list', 'index');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/{id}', 'show');
    });

    Route::prefix('event')->controller(EventController::class)->group(function() {
        Route::get('/list-for-searchbox', 'searchList');
        Route::get('/list', 'index');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/{id}', 'show');
    });

    Route::prefix('collection')->controller(CollectionController::class)->group(function() {
        Route::get('/list', 'index');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/{id}', 'show');
    });
});

// Admin dashboard routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-password-reset-link', [ResetPasswordController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::prefix('admin')->middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route::controller(ArtistController::class)->prefix('artist')->group(function() {
    //     Route::delete('/multiple-delete', 'multipleDelete');
    // });

    Route::controller(ArtistController::class)->prefix('artist')->group(function() {
        Route::post('/{id}', 'updateArtist');
        Route::put('/status-update/{id}', 'statusUpdate');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/total', 'total');
        Route::get('/sort-by-name', 'sortByName');
        Route::delete('/multiple-delete', 'multipleDelete');
    });

    Route::resource('artist', ArtistController::class);

    Route::controller(ArtworkController::class)->prefix('artwork')->group(function() {
        Route::get('/search-by-name', 'searchByName');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/sort-by-name', 'sortByName');
        Route::get('/total', 'total');
        Route::get('/get-by-category/{category}', 'getByCategory');
        Route::post('/{id}', 'updateArtwork');
        Route::put('/status-update/{id}', 'statusUpdate');
        Route::delete('/multiple-delete', 'multipleDelete');
    });

    Route::resource('artwork', ArtworkController::class);

    Route::resource('artwork-category', ArtworkCategoryController::class);

    Route::controller(BlogController::class)->prefix('blog')->group(function() {
        Route::get('/total', 'total');
        Route::post('/{id}', 'update');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/sort-by-name', 'sortByName');
        Route::delete('/multiple-delete', 'multipleDelete');
    });

    Route::resource('blog', BlogController::class);

    Route::controller(ContactController::class)->prefix('contact')->group(function() {
        Route::get('/search-by-name', 'searchByName');
        Route::get('/total', 'total');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/sort-by-name', 'sortByName');
        Route::delete('/multiple-delete', 'multipleDeletxe');
    });

    Route::resource('contact', ContactController::class)->except('store');

    Route::controller(CollectionController::class)->prefix('collection')->group(function() {
        Route::get('/total', 'total');
        Route::post('/{id}', 'update');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/sort-by-name', 'sortByName');
        Route::delete('/multiple-delete', 'multipleDelete');
    });
    Route::resource('collection', CollectionController::class);

    Route::controller(EventController::class)->prefix('event')->group(function() {
        Route::get('/total', 'total');
        Route::post('/{id}', 'update');
        Route::get('/search-by-name', 'searchByName');
        Route::get('/filter-by-date', 'filterByDate');
        Route::get('/sort-by-name', 'sortByName');
        Route::delete('/multiple-delete', 'multipleDelete');
    });
    Route::resource('event', EventController::class);
});
