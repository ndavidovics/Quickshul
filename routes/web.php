<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\MemberApplicationController;
use App\Http\Controllers\Admin\ApplicationController;
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
use App\Http\Controllers\Admin\YahrtzeitController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FinancialsController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MemorialSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use Illuminate\Support\Facades\Route;

// --- Registration + root domain routes (no tenant middleware) ---
Route::get('/register', [RegistrationController::class, 'showRegister'])->name('register');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.submit');
Route::get('/register/check-slug', [RegistrationController::class, 'checkSlug'])->name('register.check-slug');
Route::get('/register/google', [RegistrationController::class, 'redirectToGoogle'])->name('register.google');
Route::get('/register/google/callback', [RegistrationController::class, 'googleCallback'])->name('register.google.callback');
Route::middleware('auth')->group(function () {
    Route::get('/register/step2', [RegistrationController::class, 'showGmail'])->name('register.step2');
    Route::get('/register/gmail/connect', [RegistrationController::class, 'connectGmail'])->name('register.gmail.connect');
    Route::get('/auth/gmail/callback', [RegistrationController::class, 'gmailCallback'])->name('register.gmail.callback');
    Route::post('/register/gmail/skip', [RegistrationController::class, 'skipGmail'])->name('register.gmail.skip');
    Route::get('/register/step3', [RegistrationController::class, 'showPaypal'])->name('register.step3');
    Route::post('/register/paypal/connect', [RegistrationController::class, 'connectPaypal'])->name('register.paypal.connect');
    Route::post('/register/paypal/skip', [RegistrationController::class, 'skipPaypal'])->name('register.paypal.skip');
    Route::get('/register/step4', [RegistrationController::class, 'showDone'])->name('register.step4');
});

// Super admin login (no tenant, no super_admin middleware — it's the login page)
Route::get('/superadmin/login', function () {
    if (auth()->check() && auth()->user()->is_super_admin) {
        return redirect()->route('superadmin.index');
    }
    return view('superadmin.login');
})->name('superadmin.login');
Route::post('/superadmin/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])
    ->name('superadmin.login.submit')
    ->middleware('throttle:5,1');

// Platform Gmail OAuth callback (no super_admin middleware — Google redirects here unauthenticated)
Route::middleware('auth')->get('/auth/platform-gmail/callback',
    [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'gmailCallback']
)->name('superadmin.platform.gmail.callback');

Route::middleware(['auth', 'super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminTenantController::class, 'index'])->name('index');
    Route::get('/tenants/{id}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
    Route::post('/tenants/{id}/activate', [SuperAdminTenantController::class, 'activate'])->name('tenants.activate');
    Route::post('/tenants/{id}/suspend', [SuperAdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::delete('/tenants/{id}', [SuperAdminTenantController::class, 'destroy'])->name('tenants.destroy');
    Route::post('/tenants/{tenantId}/send-invite/{userId}', [SuperAdminTenantController::class, 'sendInvite'])->name('tenants.send-invite');

    // Impersonation
    Route::post('/tenants/{id}/impersonate', [App\Http\Controllers\SuperAdmin\ImpersonateController::class, 'start'])->name('tenants.impersonate');

    // Failed Jobs
    Route::get('/jobs', [App\Http\Controllers\SuperAdmin\JobsController::class, 'index'])->name('jobs.index');
    Route::post('/jobs/{id}/retry', [App\Http\Controllers\SuperAdmin\JobsController::class, 'retry'])->name('jobs.retry');
    Route::delete('/jobs/{id}', [App\Http\Controllers\SuperAdmin\JobsController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/flush', [App\Http\Controllers\SuperAdmin\JobsController::class, 'destroyAll'])->name('jobs.flush');

    // System Health
    Route::get('/health', [App\Http\Controllers\SuperAdmin\HealthController::class, 'index'])->name('health.index');

    // Platform settings
    Route::get('/platform', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'index'])->name('platform.settings');
    Route::get('/platform/gmail/connect', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'connectGmail'])->name('platform.gmail.connect');
    Route::post('/platform/gmail/disconnect', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'disconnectGmail'])->name('platform.gmail.disconnect');
    Route::post('/platform/gmail/test', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'testEmail'])->name('platform.gmail.test');
});

// Impersonation consumer — lives on tenant subdomains, requires auth but NOT super_admin
Route::middleware('auth')->get('/do-impersonate/{token}',
    [App\Http\Controllers\ImpersonateConsumeController::class, 'consume']
)->name('impersonate.consume');

// Stop impersonation (clears session flag, returns to super admin)
Route::middleware('auth')->post('/stop-impersonate', function () {
    $returnUrl = session()->pull('impersonation_return_url', route('superadmin.index'));
    session()->forget('impersonating');
    return redirect($returnUrl);
})->name('impersonate.stop');

// --- Public ---
Route::get('/', function () {
    // On a tenant subdomain → go to their login (or dashboard if already logged in)
    if (app()->bound('tenant')) {
        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }
    return view('home');
})->name('home');
Route::get('/memorial-board', [App\Http\Controllers\MemorialController::class, 'index'])->name('memorial');
Route::get('/memorial-board/slide/{n}', [App\Http\Controllers\MemorialController::class, 'slide'])->name('memorial.slide');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,60');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,10');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback')->middleware('throttle:20,1');

// QuickBooks OAuth callback — lives on root domain (Intuit can't redirect to wildcard subdomains)
Route::middleware('auth')->get('/auth/qb/callback', [App\Http\Controllers\Admin\QbController::class, 'rootCallback'])->name('qb.root.callback');
Route::get('/apply', [MemberApplicationController::class, 'show'])->name('apply');
Route::post('/apply', [MemberApplicationController::class, 'submit'])->name('apply.submit');
Route::get('/apply/thank-you', [MemberApplicationController::class, 'thankYou'])->name('apply.thank-you');

Route::get('/find', [App\Http\Controllers\FindPortalController::class, 'show'])->name('find-portal');
Route::post('/find', [App\Http\Controllers\FindPortalController::class, 'submit'])->name('find-portal.submit')->middleware('throttle:5,10');

Route::get('/agreement', fn() => view('legal.agreement'))->name('agreement');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

// PayPal webhook — no CSRF (exempted in bootstrap/app.php)
Route::post('/paypal/webhook', [PayPalWebhookController::class, 'handle'])->name('paypal.webhook');

// Public payment page (token-based, no auth required)
Route::get('/pay/{token}', [PublicPaymentController::class, 'show'])->name('public.pay');
Route::post('/pay/{token}/create-order', [PublicPaymentController::class, 'createOrder'])->name('public.pay.create-order');
Route::post('/pay/{token}/capture', [PublicPaymentController::class, 'captureOrder'])->name('public.pay.capture');

// Public event payment pages (no auth required, but respects logged-in state)
Route::get('/events/{tenantSlug}/{eventSlug}', [App\Http\Controllers\EventPaymentController::class, 'show'])->name('event.pay');
Route::post('/events/{tenantSlug}/{eventSlug}/create-order', [App\Http\Controllers\EventPaymentController::class, 'createOrder'])->name('event.pay.create-order');
Route::post('/events/{tenantSlug}/{eventSlug}/capture', [App\Http\Controllers\EventPaymentController::class, 'captureOrder'])->name('event.pay.capture');

// --- Authenticated Member ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/family', [FamilyInfoController::class, 'index'])->name('family');

    Route::get('/financial', [FinancialController::class, 'index'])->name('financial');
    Route::post('/financial/pay', [FinancialController::class, 'payNow'])->name('financial.pay');
    Route::get('/financial/pay/return', [FinancialController::class, 'payReturn'])->name('financial.pay.return');
    Route::get('/financial/pay/cancel', [FinancialController::class, 'payCancel'])->name('financial.pay.cancel');
    Route::get('/financial/pledges', [FinancialController::class, 'pledgesPage'])->name('financial.pledges');
    Route::get('/financial/payments', [FinancialController::class, 'paymentsPage'])->name('financial.payments');
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
    Route::get('/members/import', [ImportController::class, 'show'])->name('members.import');
    Route::post('/members/import/preview', [ImportController::class, 'preview'])->name('members.import.preview');
    Route::post('/members/import/process', [ImportController::class, 'process'])->name('members.import.process');
    Route::get('/members/import/template', [ImportController::class, 'template'])->name('members.import.template');
    Route::get('/members/{id}', [MembersController::class, 'show'])->name('members.show');
    Route::get('/members/{id}/payments/export', [MembersController::class, 'exportPayments'])->name('members.export.payments');
    Route::get('/members/{id}/pledges/export', [MembersController::class, 'exportPledges'])->name('members.export.pledges');
    Route::get('/members/{id}/payments/page', [MembersController::class, 'paymentsAjax'])->name('members.payments.ajax');
    Route::get('/members/{id}/pledges/page', [MembersController::class, 'pledgesAjax'])->name('members.pledges.ajax');
    Route::get('/members/{id}/edit', [MemberEditController::class, 'edit'])->name('members.edit');
    Route::put('/members/{id}', [MemberEditController::class, 'update'])->name('members.update');
    Route::post('/members/{id}/members', [MemberEditController::class, 'addMember'])->name('members.add-member');
    Route::get('/members/{id}/members/{mid}/edit', [MemberEditController::class, 'editMember'])->name('members.edit-member');
    Route::put('/members/{id}/members/{mid}', [MemberEditController::class, 'updateMember'])->name('members.update-member');
    Route::delete('/members/{id}/members/{mid}', [MemberEditController::class, 'deleteMember'])->name('members.delete-member');
    Route::post('/members/{id}/emails', [MemberEditController::class, 'addEmail'])->name('members.add-email');
    Route::delete('/members/{id}/emails/{eid}', [MemberEditController::class, 'removeEmail'])->name('members.remove-email');
    Route::post('/members/{id}/push-to-qb', [QbController::class, 'syncPushFamily'])->name('members.push-to-qb');

    // Yahrtzeits listing
    Route::get('/yahrtzeits', [YahrtzeitController::class, 'index'])->name('yahrtzeits.index');
    Route::get('/yahrtzeits/export', [YahrtzeitController::class, 'export'])->name('yahrtzeits.export');
    Route::post('/yahrtzeits', [YahrtzeitController::class, 'storeGlobal'])->name('yahrtzeits.store-global');

    // Financials
    Route::get('/financials/payments', [FinancialsController::class, 'payments'])->name('financials.payments');
    Route::get('/financials/payments/export', [FinancialsController::class, 'exportPayments'])->name('financials.payments.export');
    Route::get('/financials/pledges', [FinancialsController::class, 'pledges'])->name('financials.pledges');
    Route::get('/financials/pledges/export', [FinancialsController::class, 'exportPledges'])->name('financials.pledges.export');

    // Yahrtzeits (admin only)
    Route::post('/members/{id}/yahrtzeits', [YahrtzeitController::class, 'store'])->name('yahrtzeits.store');
    Route::get('/members/{id}/yahrtzeits/{yid}/edit', [YahrtzeitController::class, 'edit'])->name('yahrtzeits.edit');
    Route::put('/members/{id}/yahrtzeits/{yid}', [YahrtzeitController::class, 'update'])->name('yahrtzeits.update');
    Route::delete('/members/{id}/yahrtzeits/{yid}', [YahrtzeitController::class, 'destroy'])->name('yahrtzeits.destroy');
    Route::get('/yahrtzeits/hebrew-date', function (\Illuminate\Http\Request $request) {
        $date = $request->query('date');
        if (!$date) return response()->json([]);
        $svc = app(\App\Services\HebrewDateService::class);
        $h   = $svc->gregorianToHebrew($date);
        return response()->json([
            'month' => $h['month'],
            'day'   => $h['day'],
            'full'  => "{$h['day']} {$h['month_name']} {$h['year']}",
        ]);
    })->name('yahrtzeits.hebrew-date');

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
    Route::get('/emails/sends/page', [EmailReminderController::class, 'recentSendsAjax'])->name('emails.sends.ajax');
    Route::post('/emails/balance/send', [EmailReminderController::class, 'sendBalanceReminder'])->name('emails.balance.send');
    Route::post('/emails/balance/preview', [EmailReminderController::class, 'previewBalance'])->name('emails.balance.preview');
    Route::post('/emails/balance/test', [EmailReminderController::class, 'sendBalanceReminderTest'])->name('emails.balance.test');
    Route::post('/emails/statement/send', [EmailReminderController::class, 'sendGivingStatement'])->name('emails.statement.send');
    Route::post('/emails/statement/test', [EmailReminderController::class, 'sendGivingStatementTest'])->name('emails.statement.test');
    Route::post('/emails/statement/preview', [EmailReminderController::class, 'previewStatement'])->name('emails.statement.preview');
    Route::get('/members/{id}/email', [EmailReminderController::class, 'redirectToMemberEmail'])->name('members.email');

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/approve', [ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{id}/reject', [ApplicationController::class, 'reject'])->name('applications.reject');

    // Users
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::post('/users/{id}/toggle-admin', [UserManagementController::class, 'toggleAdmin'])->name('users.toggle');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::get('/settings/gmail/connect', [SettingsController::class, 'connectGmail'])->name('settings.gmail.connect');
    Route::post('/settings/gmail/disconnect', [SettingsController::class, 'disconnectGmail'])->name('settings.gmail.disconnect');
    Route::post('/settings/paypal', [SettingsController::class, 'updatePaypal'])->name('settings.paypal');
    Route::post('/settings/qb/toggle', [SettingsController::class, 'toggleQb'])->name('settings.qb.toggle');

    // Membership types
    Route::get('/settings/membership', [App\Http\Controllers\Admin\MembershipTypeController::class, 'index'])->name('membership-types.index');
    Route::post('/settings/membership', [App\Http\Controllers\Admin\MembershipTypeController::class, 'store'])->name('membership-types.store');
    Route::put('/settings/membership/{id}', [App\Http\Controllers\Admin\MembershipTypeController::class, 'update'])->name('membership-types.update');
    Route::delete('/settings/membership/{id}', [App\Http\Controllers\Admin\MembershipTypeController::class, 'destroy'])->name('membership-types.destroy');

    // Events
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::post('/events/{id}/close', [EventController::class, 'close'])->name('events.close');
    Route::post('/events/{id}/reopen', [EventController::class, 'reopen'])->name('events.reopen');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{id}/payments', [EventController::class, 'payments'])->name('events.payments');

    // Memorial Board
    Route::get('/memorial/settings', [MemorialSettingsController::class, 'edit'])->name('memorial.settings');
    Route::post('/memorial/settings', [MemorialSettingsController::class, 'update'])->name('memorial.settings.save');

    // Calendar
    Route::get('/calendar/settings', [CalendarController::class, 'settings'])->name('calendar.settings');
    Route::post('/calendar/settings', [CalendarController::class, 'saveSettings'])->name('calendar.settings.save');
    Route::get('/calendar/minyanim', [CalendarController::class, 'minyanim'])->name('calendar.minyanim');
    Route::post('/calendar/minyanim/reorder', [CalendarController::class, 'reorderMinyanim'])->name('calendar.minyanim.reorder');
    Route::post('/calendar/minyanim', [CalendarController::class, 'storeMinyan'])->name('calendar.minyanim.store');
    Route::put('/calendar/minyanim/{id}', [CalendarController::class, 'updateMinyan'])->name('calendar.minyanim.update');
    Route::delete('/calendar/minyanim/{id}', [CalendarController::class, 'deleteMinyan'])->name('calendar.minyanim.delete');
    Route::get('/calendar/minyanim/{id}/exceptions', [CalendarController::class, 'listExceptions'])->name('calendar.minyanim.exceptions');
    Route::post('/calendar/minyanim/{id}/exceptions', [CalendarController::class, 'storeException'])->name('calendar.minyanim.exceptions.store');
    Route::put('/calendar/minyanim/{id}/exceptions/{eid}', [CalendarController::class, 'updateException'])->name('calendar.minyanim.exceptions.update');
    Route::delete('/calendar/minyanim/{id}/exceptions/{eid}', [CalendarController::class, 'deleteException'])->name('calendar.minyanim.exceptions.delete');
    Route::post('/calendar/minyanim/{id}/time-rules', [CalendarController::class, 'saveTimeRules'])->name('calendar.minyanim.time-rules');
    Route::get('/calendar/generate', [CalendarController::class, 'generate'])->name('calendar.generate');
    Route::post('/calendar/preview', [CalendarController::class, 'preview'])->name('calendar.preview');
    Route::post('/calendar/hebcal/refresh', [CalendarController::class, 'refreshHebcal'])->name('calendar.hebcal.refresh');
});
