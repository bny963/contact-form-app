# COACHTECH お問い合わせフォーム 拡張開発プロジェクト

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
| **VCS** | Git / GitHub | フィーチャーブランチ運用 / プルリクエスト管理 |

---

## 📊 データベース設計（ER図）

### カーディナリティ（関連性）
- **`categories` と `contacts`（1対多）**: 1つのカテゴリに複数のお問い合わせが紐づきます。
- **`contacts` と `tags`（多対多）**: 中間テーブル `contact_tag` を介して、複数のお問い合わせに複数のタグを柔軟に設定できます（複合ユニーク制約により重複登録を防止）。
- **`users`（管理者）**: 他のテーブルと直接の外部キー（FK）結合は持ちませんが、認証（Auth）を通過することで全データの閲覧・操作権限を持ちます。

```mermaid
erDiagram
    users {
        bigint_unsigned id PK
        varchar_255 name
        varchar_255 email UK
        timestamp email_verified_at "NULL"
        varchar_255 password
        varchar_100 remember_token "NULL"
        timestamp created_at
        timestamp updated_at
    }

    categories {
        bigint_unsigned id PK
        varchar_255 content
        timestamp created_at
        timestamp updated_at
    }

    tags {
        bigint_unsigned id PK
        varchar_50 name UK
        timestamp created_at
        timestamp updated_at
    }

    contacts {
        bigint_unsigned id PK
        bigint_unsigned category_id FK "ON DELETE CASCADE"
        varchar_255 first_name
        varchar_255 last_name
        tinyint gender "1:男性, 2:女性, 3:その他"
        varchar_255 email
        varchar_11 tel "ハイフンなし"
        varchar_255 address
        varchar_255 building "NULL"
        varchar_120 detail
        timestamp created_at
        timestamp updated_at
    }

    contact_tag {
        bigint_unsigned id PK
        bigint_unsigned contact_id FK "ON DELETE CASCADE"
        bigint_unsigned tag_id FK "ON DELETE CASCADE"
        timestamp created_at
        timestamp updated_at
    }

    categories ||--o{ contacts : "1対多"
    contacts ||--o{ contact_tag : "多対多"
    tags ||--o{ contact_tag : "多対多"