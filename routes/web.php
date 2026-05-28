<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. 公開ページ（一般ユーザー向け：認証不要）
// ==========================================

// PG01: お問い合わせフォーム入力ページ
Route::get('/', [ContactController::class, 'index'])->name('contact.index');

// PG02: お問い合わせフォーム確認ページ
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');

// お問い合わせ送信処理（確認画面からのPOST先）
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');

// PG03: サンクスページ
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');