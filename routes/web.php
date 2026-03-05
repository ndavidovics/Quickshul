<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyInfoController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\Admin\EmailReminderController;
use App\Http\Controllers\Admin\MemberEditController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\QbController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

// --- Public ---
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::get('/agreement', fn() => view('legal.agreement'))->name('agreement');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

// PayPal webhook — no CSRF (exempted in bootstrap/app.php)
Route::post('/paypal/webhook', [PayPalWebhookController::class, 'handle'])->name('paypal.webhook');

// Public payment page (token-based, no auth required)
Route::get('/pay/{token}', [PublicPaymentController::class, 'show'])->name('public.pay');
Route::post('/pay/{token}/create-order', [PublicPaymentController::class, 'createOrder'])->name('public.pay.create-order');
Route::post('/pay/{token}/capture', [PublicPaymentController::class, 'captureOrder'])->name('public.pay.capture');

// --- Authenticated Member ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/family', [FamilyInfoController::class, 'index'])->name('family');

    Route::get('/financial', [FinancialController::class, 'index'])->name('financial');
    Route::post('/financial/pay', [FinancialController::class, 'payNow'])->name('financial.pay');
    Route::get('/financial/pay/return', [FinancialController::class, 'payReturn'])->name('financial.pay.return');
    Route::get('/financial/pay/cancel', [FinancialController::class, 'payCancel'])->name('financial.pay.cancel');
    Route::get('/financial/payments/export', [FinancialController::class, 'exportPayments'])->name('financial.export.payments');
    Route::get('/financial/pledges/export', [FinancialController::class, 'exportPledges'])->name('financial.export.pledges');

    // Member profile editing
    Route::post('/family/contact', [MemberProfileController::class, 'updateContact'])->name('family.contact.update');
    Route::post('/family/members', [MemberProfileController::class, 'addMember'])->name('family.member.add');
    Route::put('/family/members/{mid}', [MemberProfileController::class, 'updateMember'])->name('family.member.update');

    // Donations
    Route::get('/donate', [FinancialController::class, 'donateForm'])->name('donate');
    Route::post('/donate/create-order', [FinancialController::class, 'donateCreateOrder'])->name('donate.create-order');
    Route::post('/donate/apple-pay-create-order', [FinancialController::class, 'applePayCreateOrder'])->name('donate.apple-pay-create-order');
    Route::post('/donate/capture', [FinancialController::class, 'donateCaptureOrder'])->name('donate.capture');
    Route::post('/donate/apple-pay-capture', [FinancialController::class, 'applePayCapture'])->name('donate.apple-pay-capture');
    // Legacy redirect-flow routes (kept for any in-flight sessions)
    Route::post('/donate', [FinancialController::class, 'donatePay'])->name('donate.pay');
    Route::get('/donate/return', [FinancialController::class, 'donateReturn'])->name('donate.return');
    Route::get('/donate/cancel', [FinancialController::class, 'donateCancel'])->name('donate.cancel');

    Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password');
});

// --- Admin ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Members
    Route::get('/members', [MembersController::class, 'index'])->name('members');
    Route::get('/members/export', [MembersController::class, 'export'])->name('members.export');
    Route::get('/members/{id}', [MembersController::class, 'show'])->name('members.show');
    Route::get('/members/{id}/payments/export', [MembersController::class, 'exportPayments'])->name('members.export.payments');
    Route::get('/members/{id}/pledges/export', [MembersController::class, 'exportPledges'])->name('members.export.pledges');
    Route::get('/members/{id}/edit', [MemberEditController::class, 'edit'])->name('members.edit');
    Route::put('/members/{id}', [MemberEditController::class, 'update'])->name('members.update');
    Route::post('/members/{id}/members', [MemberEditController::class, 'addMember'])->name('members.add-member');
    Route::get('/members/{id}/members/{mid}/edit', [MemberEditController::class, 'editMember'])->name('members.edit-member');
    Route::put('/members/{id}/members/{mid}', [MemberEditController::class, 'updateMember'])->name('members.update-member');
    Route::delete('/members/{id}/members/{mid}', [MemberEditController::class, 'deleteMember'])->name('members.delete-member');
    Route::post('/members/{id}/emails', [MemberEditController::class, 'addEmail'])->name('members.add-email');
    Route::delete('/members/{id}/emails/{eid}', [MemberEditController::class, 'removeEmail'])->name('members.remove-email');
    Route::post('/members/{id}/push-to-qb', [QbController::class, 'syncPushFamily'])->name('members.push-to-qb');

    // QuickBooks
    Route::get('/qb', [QbController::class, 'index'])->name('qb');
    Route::get('/qb/connect', [QbController::class, 'connect'])->name('qb.connect');
    Route::post('/qb/disconnect', [QbController::class, 'disconnect'])->name('qb.disconnect');
    Route::post('/qb/sync/pull', [QbController::class, 'syncPull'])->name('qb.sync.pull');
    Route::post('/qb/sync/push', [QbController::class, 'syncPush'])->name('qb.sync.push');
    Route::get('/qb/conflicts', [QbController::class, 'conflicts'])->name('qb.conflicts');
    Route::post('/qb/conflicts/{id}/resolve', [QbController::class, 'resolveConflict'])->name('qb.resolve');

    // QB OAuth (also admin-only)
    Route::get('/auth/qb/redirect', [QbController::class, 'redirect'])->name('qb.redirect');
    Route::get('/auth/qb/callback', [QbController::class, 'callback'])->name('qb.callback');

    // Email Reminders
    Route::get('/emails', [EmailReminderController::class, 'index'])->name('emails');
    Route::post('/emails/balance/send', [EmailReminderController::class, 'sendBalanceReminder'])->name('emails.balance.send');
    Route::post('/emails/balance/preview', [EmailReminderController::class, 'previewBalance'])->name('emails.balance.preview');
    Route::post('/emails/balance/test', [EmailReminderController::class, 'sendBalanceReminderTest'])->name('emails.balance.test');
    Route::post('/emails/statement/send', [EmailReminderController::class, 'sendGivingStatement'])->name('emails.statement.send');
    Route::post('/emails/statement/test', [EmailReminderController::class, 'sendGivingStatementTest'])->name('emails.statement.test');
    Route::post('/emails/statement/preview', [EmailReminderController::class, 'previewStatement'])->name('emails.statement.preview');
    Route::get('/members/{id}/email', [EmailReminderController::class, 'redirectToMemberEmail'])->name('members.email');

    // Users
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::post('/users/{id}/toggle-admin', [UserManagementController::class, 'toggleAdmin'])->name('users.toggle');
});
