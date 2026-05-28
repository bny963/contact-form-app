# COACHTECH お問い合わせフォーム

## 概要
本プロジェクトは、COACHTECHのカリキュラムとして開発された「お問い合わせフォーム」のバックエンド機能を実務レベルへと拡張したWebアプリケーションです。
一般ユーザーからの投稿機能に加え、管理者向けの堅牢な認証、検索条件と完全連動する高速なCSVエクスポート機能、および外部フロントエンドとの連携を想定したフルCRUDのRESTful APIを実装しています。

### 実装した主要機能
- **お問い合わせ投稿・管理機能**: カテゴリ選択やバリデーションを含む入力・完了フロー。
- **検索連動型CSVエクスポート機能**: 大量データにも対応可能なストリームレスポンス（BOM付きUTF-8対応）によるリアルタイムダウンロード。
- **RESTful API（5系統）**: フロントエンドのSPA化を見据えた、検索・一覧・詳細・作成・更新・削除の全CRUDエンドポイント。
- **自動テスト環境の構築**: Feature/Unitテストによる、認証、バリデーション、API、CSV出力の完全自動検証（全23アサーション通過）。

---

## 使用技術スタック

| 分類 | 技術・ツール | バージョン / 詳細 |
| :--- | :--- | :--- |
| **Backend** | PHP / Laravel | PHP 8.x / Laravel 10.x |
| **Database** | MySQL | MySQL 8.0 |
| **Web Server** | Nginx | 1.x |
| **Infra / Dev** | Docker / Laravel Sail | Docker Desktop / WSL2 |
| **Testing** | PHPUnit | Feature Test & Unit Test (23 Assertions) |

---

## 📊 データベース設計（ER図）

![ER図](https://github.com/user-attachments/assets/36e3717b-af60-4d61-b8f3-5e90aa0e40d0)

---

## 🌐 APIエンドポイント一覧

すべてのAPIレスポンスはJSON形式で返却されます。

| メソッド | パス | 機能概要 | 認証 |
| :--- | :--- | :--- | :---: |
| **GET** | `/api/contacts` | お問い合わせ一覧の取得（検索条件・ページネーション付） | なし |
| **GET** | `/api/contacts/{id}` | 特定のお問い合わせ詳細データの取得 | なし |
| **POST** | `/api/contacts` | 新規お問い合わせの登録（バリデーション検証あり） | なし |
| **PUT/PATCH** | `/api/contacts/{id}` | 登録済みお問い合わせデータの更新 | なし |
| **DELETE** | `/api/contacts/{id}` | お問い合わせデータの物理削除 | なし |



---

## 🔗 開発環境URL
アプリケーションのトップページ（お問い合わせ入力）: http://localhost

管理者ログインページ: http://localhost/login

管理画面ダッシュボード: http://localhost/admin

---

## 👤 作成者
小林 瑠真 (Ruma Kobayashi)

---

## 🛠️ 環境構築・初期設定手順

WSL2 / Docker環境がインストールされている前提での手順です。

### 1. リポジトリのクローンと環境構築
```bash
# 1. リポジトリのクローンと移動
git clone https://github.com/bny963/contact-form-app contact-form-app
cd contact-form-app

# 2. 環境設定ファイルの準備
cp .env.example .env

# 3. Composerパッケージのインストール（Docker経由）
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs

# 4. 開発環境（Dockerコンテナ）の起動
./vendor/bin/sail up -d

# 5. アプリケーションキーの生成
sail artisan key:generate

# 6. データベースのマイグレーションとシーディング
sail artisan migrate:fresh --seed

# 7. 自動テストの実行確認
sail artisan test
