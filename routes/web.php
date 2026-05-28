<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

// PG01: お問い合わせフォーム入力ページ
Route::get('/', [ContactController::class, 'index'])->name('contact.index');
// PG02: お問い合わせフォーム確認ページ
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
// お問い合わせ送信処理
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');
// PG03: サンクスページ
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

// ==========================================
// 2. 管理者ページ（認証が必要なエリア）
// ==========================================

Route::middleware(['auth'])->group(function () {

    // PG04: 管理画面（一覧表示・検索・ページネーション）
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // お問い合わせ削除処理（モーダル内または一覧からのPOST先）
    Route::delete('/admin/contacts/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // 詳細データをAjaxなどで取得するためのGETルート
    Route::get('/admin/contacts/{id}', [AdminController::class, 'show'])->name('admin.show');
});