<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMediaController;
use App\Http\Controllers\AdminServicesController;
use App\Http\Controllers\AdminPricingController;
use App\Http\Controllers\PublicPricingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Marketing\MarketingAuthController;
use App\Http\Controllers\Marketing\MarketingDashboardController;
use App\Http\Controllers\Marketing\MarketingCampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/rooms', [PageController::class, 'rooms'])->name('rooms');
Route::get('/offer', [PageController::class, 'offer'])->name('offer');
Route::get('/our-hotel', [PageController::class, 'ourHotel'])->name('our-hotel');
Route::get('/explore', [PageController::class, 'explore'])->name('explore');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');

Route::get('/booking-prices', [PublicPricingController::class, 'preview'])
    ->middleware('throttle:30,1')->name('booking-prices.preview');

// Private management UI. Session, CSRF and login throttling are enforced.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::patch('/bookings/{booking}/status', [AdminController::class, 'bookingStatus'])->name('bookings.status');
    Route::put('/rooms/{room}/rates', [AdminController::class, 'roomRate'])->name('rooms.rate');
    Route::post('/promotions', [AdminController::class, 'promotion'])->name('promotions.store');
    Route::get('/rates', [AdminPricingController::class, 'index'])->name('pricing.index');
    Route::post('/rates/plans', [AdminPricingController::class, 'createPlan'])->name('pricing.plans.store');
    Route::patch('/rates/plans/{code}', [AdminPricingController::class, 'publishPlan'])->name('pricing.plans.publish');
    Route::post('/rates/default', [AdminPricingController::class, 'saveBase'])->name('pricing.base');
    Route::post('/rates/calendar', [AdminPricingController::class, 'saveSchedule'])->name('pricing.schedule');
    Route::post('/rates/bulk', [AdminPricingController::class, 'bulk'])->name('pricing.bulk');

    // Photos
    Route::get('/photos', [AdminMediaController::class, 'index'])->name('media.index');
    Route::post('/photos/slots/{slot}', [AdminMediaController::class, 'saveSlot'])->where('slot', '[a-z0-9.\-]+')->name('media.slot');
    Route::delete('/photos/slots/{slot}', [AdminMediaController::class, 'resetSlot'])->where('slot', '[a-z0-9.\-]+')->name('media.slot.reset');
    Route::post('/photos/gallery', [AdminMediaController::class, 'addGallery'])->name('media.gallery.add');
    Route::post('/photos/gallery/{id}', [AdminMediaController::class, 'updateGallery'])->whereNumber('id')->name('media.gallery.update');
    Route::post('/photos/gallery/{id}/move', [AdminMediaController::class, 'moveGallery'])->whereNumber('id')->name('media.gallery.move');
    Route::delete('/photos/gallery/{id}', [AdminMediaController::class, 'deleteGallery'])->whereNumber('id')->name('media.gallery.delete');

    // Services: amenities + paid extras
    Route::get('/services', [AdminServicesController::class, 'index'])->name('services.index');
    Route::post('/services', [AdminServicesController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [AdminServicesController::class, 'update'])->whereNumber('id')->name('services.update');
    Route::patch('/services/{id}/toggle', [AdminServicesController::class, 'toggle'])->whereNumber('id')->name('services.toggle');
    Route::patch('/services/{id}/move', [AdminServicesController::class, 'move'])->whereNumber('id')->name('services.move');
    Route::delete('/services/{id}', [AdminServicesController::class, 'destroy'])->whereNumber('id')->name('services.destroy');
    Route::delete('/rates/calendar', [AdminPricingController::class, 'removeOverride'])->name('pricing.remove');
});

// SEO / AEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');


// Staff-only AI Hotel Marketing Assistant; never published in the public site map.
Route::get('/admin/marketing/login', [MarketingAuthController::class, 'form'])->name('login');
Route::post('/admin/marketing/login', [MarketingAuthController::class, 'login'])
    ->middleware('throttle:5,1')->name('marketing.login.attempt');

Route::prefix('admin/marketing')->middleware(['auth', 'can:manage-marketing'])->group(function () {
    Route::get('/', [MarketingDashboardController::class, 'index'])->name('marketing.dashboard');
    Route::post('/ask', [MarketingDashboardController::class, 'ask'])
        ->middleware('throttle:10,1')->name('marketing.ask');
    Route::post('/campaigns', [MarketingCampaignController::class, 'store'])
        ->name('marketing.campaigns.store');
    Route::post('/campaigns/{campaign}/generate', [MarketingCampaignController::class, 'generate'])
        ->middleware('throttle:10,1')->name('marketing.campaigns.generate');
    Route::post('/logout', [MarketingAuthController::class, 'logout'])->name('marketing.logout');
});
