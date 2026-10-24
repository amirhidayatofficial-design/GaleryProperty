<?php

use App\Http\Controllers\Admin\AgencyPartnerController;
use App\Http\Controllers\Admin\BuyerLeadController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DigitalAkadController;
use App\Http\Controllers\Admin\ListingVerificationController;
use App\Http\Controllers\Admin\PropertyFileController;
use App\Http\Controllers\Admin\PropertyProjectController;
use App\Http\Controllers\Admin\SupportConversationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Agency\AgencyProfileController;
use App\Http\Controllers\Agency\CommissionController as AgencyCommissionController;
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

// Profil tetap bisa dibuka akun pending, misalnya untuk membetulkan email yang salah ketik
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
Route::middleware(['auth', 'verified', 'role:buyer,agency_partner'])->prefix('bantuan')->name('support.')->group(function () {
    Route::get('/', [SupportController::class, 'show'])->name('show');
    Route::get('/messages', [SupportController::class, 'messages'])->name('messages');
    Route::post('/messages', [SupportController::class, 'send'])->name('send');
});

// Chat calon pembeli <-> agency partner
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/properti/{propertyProject:slug}/chat', [ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'send'])->name('chat.send');
});

// Area Agency Partner: profil agency dan pengajuan listing
Route::middleware(['auth', 'verified', 'role:agency_partner'])->prefix('agency')->name('agency.')->group(function () {
    Route::get('profile', [AgencyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [AgencyProfileController::class, 'update'])->name('profile.update');
    Route::get('commissions', [AgencyCommissionController::class, 'index'])->name('commissions.index');
    Route::get('commissions/{commission}', [AgencyCommissionController::class, 'show'])->name('commissions.show');
    Route::resource('property-projects', AgencyPropertyProjectController::class)->except(['destroy']);
});

// Super Admin Management Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('agency-partners', AgencyPartnerController::class);
    Route::resource('property-projects', PropertyProjectController::class);
    Route::resource('property-files', PropertyFileController::class);
    Route::resource('buyer-leads', BuyerLeadController::class);
    Route::resource('digital-akads', DigitalAkadController::class);

    // Komisi per termin: diterbitkan otomatis dari akad, setiap transisi lewat tombol (tanpa form isian manual)
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('commissions/{commission}', [CommissionController::class, 'show'])->name('commissions.show');
    Route::get('commissions/{commission}/invoice', [CommissionController::class, 'invoice'])->name('commissions.invoice');
    Route::post('commissions/{commission}/mark-due', [CommissionController::class, 'markDue'])->name('commissions.mark-due');
    Route::post('commissions/{commission}/issue-invoice', [CommissionController::class, 'issueInvoice'])->name('commissions.issue-invoice');
    Route::post('commissions/{commission}/record-payment', [CommissionController::class, 'recordPayment'])->name('commissions.record-payment');
    Route::post('commissions/{commission}/record-payout', [CommissionController::class, 'recordPayout'])->name('commissions.record-payout');
    Route::post('commissions/{commission}/void', [CommissionController::class, 'void'])->name('commissions.void');

    // Verifikasi listing: satu-satunya jalan untuk menayangkan atau menarik listing dari katalog
    Route::get('listing-verifications', [ListingVerificationController::class, 'index'])->name('listing-verifications.index');
    Route::get('listing-verifications/{propertyProject}', [ListingVerificationController::class, 'show'])->name('listing-verifications.show');
    Route::post('listing-verifications/{propertyProject}/approve', [ListingVerificationController::class, 'approve'])->name('listing-verifications.approve');
    Route::post('listing-verifications/{propertyProject}/unpublish', [ListingVerificationController::class, 'unpublish'])->name('listing-verifications.unpublish');

    // Inbox customer support
    Route::get('support', [SupportConversationController::class, 'index'])->name('support.index');
    Route::get('support/{supportConversation}', [SupportConversationController::class, 'show'])->name('support.show');
    Route::get('support/{supportConversation}/messages', [SupportConversationController::class, 'messages'])->name('support.messages');
    Route::post('support/{supportConversation}/messages', [SupportConversationController::class, 'send'])->name('support.send');
});

require __DIR__.'/auth.php';

