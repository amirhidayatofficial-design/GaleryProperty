<?php

use App\Http\Controllers\Admin\AgencyPartnerController;
use App\Http\Controllers\Admin\BuyerLeadController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DigitalAkadController;
use App\Http\Controllers\Admin\PropertyFileController;
use App\Http\Controllers\Admin\PropertyProjectController;
use App\Http\Controllers\Admin\SupportConversationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Agency\PropertyProjectController as AgencyPropertyProjectController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

// Landing page hanya untuk pengunjung; pengguna yang sudah login diarahkan ke dashboard
Route::get('/', LandingController::class)->middleware('guest')->name('home');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Katalog publik (hanya listing yang sudah ditayangkan admin); admin mengelola listing lewat Kelola Data
Route::middleware('not-admin')->group(function () {
    Route::get('/properti', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/properti/{propertyProject:slug}', [CatalogController::class, 'show'])->name('catalog.show');
});

// Bantuan (customer support): pembeli dan agency partner chat dengan tim admin
Route::middleware(['auth', 'role:buyer,agency_partner'])->prefix('bantuan')->name('support.')->group(function () {
    Route::get('/', [SupportController::class, 'show'])->name('show');
    Route::get('/messages', [SupportController::class, 'messages'])->name('messages');
    Route::post('/messages', [SupportController::class, 'send'])->name('send');
});

// Chat calon pembeli <-> agency partner
Route::middleware('auth')->group(function () {
    Route::post('/properti/{propertyProject:slug}/chat', [ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'send'])->name('chat.send');
});

// Area Agency Partner: pengajuan listing
Route::middleware(['auth', 'role:agency_partner'])->prefix('agency')->name('agency.')->group(function () {
    Route::resource('property-projects', AgencyPropertyProjectController::class)->except(['destroy']);
});

// Super Admin Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('agency-partners', AgencyPartnerController::class);
    Route::resource('property-projects', PropertyProjectController::class);
    Route::resource('property-files', PropertyFileController::class);
    Route::resource('buyer-leads', BuyerLeadController::class);
    Route::resource('digital-akads', DigitalAkadController::class);
    Route::resource('commissions', CommissionController::class);

    // Inbox customer support
    Route::get('support', [SupportConversationController::class, 'index'])->name('support.index');
    Route::get('support/{supportConversation}', [SupportConversationController::class, 'show'])->name('support.show');
    Route::get('support/{supportConversation}/messages', [SupportConversationController::class, 'messages'])->name('support.messages');
    Route::post('support/{supportConversation}/messages', [SupportConversationController::class, 'send'])->name('support.send');
});

require __DIR__.'/auth.php';

