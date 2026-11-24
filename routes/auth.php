<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Models\Url;

Route::middleware('guest')->group(function () {
    $register_slug = 'register';
    $login_slug = 'login';
    $forgot_slug = 'forgot-password';
    $reset_slug = 'reset-password';

    $RegisterPage = Url::getSystemPage('auth', 'register');
    $LoginPage = Url::getSystemPage('auth', 'login');
    $ForgotPage = Url::getSystemPage('auth', 'forgot_password');
    $ResetPage = Url::getSystemPage('auth', 'reset_password');

    if ($RegisterPage) { $register_slug = $RegisterPage->url_slug; }
    if ($LoginPage) { $login_slug = $LoginPage->url_slug; }
    if ($ForgotPage) { $forgot_slug = $ForgotPage->url_slug; }
    if ($ResetPage) { $reset_slug = $ResetPage->url_slug; }

    Route::get($register_slug, [RegisteredUserController::class, 'create'])->name('register');
    Route::post($register_slug, [RegisteredUserController::class, 'store']);

    Route::get($login_slug, [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post($login_slug, [AuthenticatedSessionController::class, 'store']);

    Route::get($forgot_slug, [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post($forgot_slug, [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get($reset_slug . '/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post($reset_slug, [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
           ->middleware(['signed', 'throttle:6,1'])
           ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
           ->middleware('throttle:6,1')
           ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
           ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
           ->name('logout');
});
