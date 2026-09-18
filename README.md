# COACHTECH BookShelf 書籍レビューアプリ

BookShelf は、読書管理を効率化し、ユーザー同士の書籍レビュー共有を促進するための Web アプリケーションです。
書籍の登録・閲覧・レビュー投稿・お気に入り登録に加え、ジャンル分類、レビューへのいいね、ランキング、読書計画、通知機能、マイ読書レポートなど、読書体験を総合的にサポートする機能を備えています。

## 作成者

江草　英樹

## 使用技術

- PHP 8.5
- Laravel 10.x
- Laravel Sanctum（API 認証）
- Laravel Fortify（ユーザー認証）
- Laravel Notification（DatabaseChannel）
- Laravel Scheduler
- Laravel Eloquent ORM
- Laravel Artisan Console
- PHPUnit / Pest

- Vite
- Tailwind CSS 3.4

- Docker / Docker Compose
- Laravel Sail

- MySQL 8.4
- Nginx
- phpMyAdmin

- Mermaid（ER 図作成）
- Git / GitHub
- WSL2（Windows 開発環境）##　ER図

```mermaid
erDiagram

  users {
    bigint id PK
    string name
    string email
    string password
    string remember_token
  }

  books {
    bigint id PK
    string title
    string author
    string isbn
    date published_date
    string description
    string image_url
    bigint user_id FK
  }

  genres {
    bigint id PK
    string name
  }

  book_genres {
    bigint id PK
    bigint book_id FK
    bigint genre_id FK
    timestamps
    unique book_id, genre_id
  }

  reviews {
    bigint id PK
    bigint user_id FK
    bigint book_id FK
    tinyint rating
    string comment
  }

  likes {
    bigint id PK
    bigint user_id FK
    bigint review_id FK
  }

  favorites {
    bigint id PK
    bigint user_id FK
    bigint book_id FK
  }

  reading_plans {
    bigint id PK
    bigint user_id FK
    bigint book_id FK
    string status
    date target_date
    timestamp completed_at
    timestamp reminder_sent_at
  }

  notifications {
    uuid id PK
    string type
    string notifiable_type
    bigint notifiable_id
    json data
    timestamp read_at
  }

  users ||--o{ reviews : "writes"
  users ||--o{ likes : "likes"
  users ||--o{ favorites : "favorites"
  users ||--o{ reading_plans : "plans"

  books ||--o{ reviews : "has"
  books ||--o{ favorites : "favorited"
  books ||--o{ book_genres : "categorized"
  books ||--o{ reading_plans : "planned"

  reviews ||--o{ likes : "liked by"

  genres ||--o{ book_genres : "has"

```

## 環境構築手順（Setup Guide）

1. 前提条件（Prerequisites）

- Docker Desktop
- Git
- （Windows）WSL2
- Node.js（任意、Sail 内で動作）

2. リポジトリのクローン

```bash
git clone git@github.com:Denchan55/Bookshelf-v2.git
```

```bash
cd bookshelf-v2
```

3. 依存関係をインストール

```bash
composer install
```

4. .env の作成と設定

```bash
cp .env.example .env
```

必要に応じて以下を確認・修正

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
DB_DATABASE_TESTING=testing
```

5. Docker（Laravel Sail）の起動

```bash
./vendor/bin/sail up -d
```

6. 依存関係のインストール

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

7. アプリケーションキーの生成

```bash
./vendor/bin/sail artisan key:generate
```

8. マイグレーション & シーディング

```bash
./vendor/bin/sail artisan migrate --seed
```

9. アクセス方法

- アプリケーション: http://localhost
- 書籍一覧: http://localhost/books
- 読書計画: http://localhost/reading-plans
- マイ読書レポート: http://localhost/reports
- phpMyAdmin: http://localhost:8080

テスト実行

```bash
./vendor/bin/sail artisan test
```

補足（Notes）
Vite のポート競合が起きた場合は npm run dev を再実行してください。
Docker の初回起動には時間がかかる場合があります。
Seeder により初期データ（書籍データ・ユーザーなど）が自動投入されます。

## APIエンドポイント一覧

認証不要の公開APIです。全エンドポイントは `/api/v1` プレフィックス配下に定義されています。

| HTTPメソッド | URI                   | 概要                       |
| ------------ | --------------------- | -------------------------- |
| GET          | /api/v1/books         | 書籍一覧                   |
| GET          | /api/v1/books/{book}  | 書籍詳細                   |
| POST         | /api/v1/books         | 書籍登録（201 Created）    |
| PUT          | /api/v1/books/{books} | 書籍更新（200 OK）         |
| DELETE       | /api/v1/books/{books} | 書籍削除（204 No Content） |

## 通知機能の動作確認方法

手動実行

```bash
./vendor/bin/sail artisan reading-plan:notify
```

スケジューラ実行

```bash
./vendor/bin/sail artisan schedule:run
```

スケジューラ設定（参考）

```bash
php
protected function schedule(Schedule $schedule)
{
$schedule->command('reading-plan:notify')->dailyAt('20:00');
}
```

完了
以上で環境構築は完了です。
アプリケーションを起動し、動作を確認してください。
