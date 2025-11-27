# Newsweek RSS Feed Aggregator

ニューズウィーク日本版のRSSフィードを自動収集し、検索・閲覧できるWebアプリケーション

## 目次

- [プロジェクト概要](#プロジェクト概要)
- [主要機能](#主要機能)
- [技術スタック](#技術スタック)
- [システム要件](#システム要件)
- [インストール手順](#インストール手順)
- [環境設定](#環境設定)
- [使い方](#使い方)
- [スケジュール設定](#スケジュール設定)
- [プロジェクト構成](#プロジェクト構成)
- [開発ガイドライン](#開発ガイドライン)
- [データベース設計](#データベース設計)
- [ドキュメント](#ドキュメント)
- [ライセンス](#ライセンス)

---

## プロジェクト概要

本システムは、ニューズウィーク日本版（Newsweek Japan）のRSSフィードから記事を自動収集し、データベースに保存して検索・閲覧できるWebアプリケーションです。

### 特徴

- **自動収集**: 5分ごとにRSSフィードから最新記事を自動取得
- **高速検索**: 日本語N-gram全文検索により、大量データでも高速な部分一致検索を実現
- **データ管理**: 古いデータの自動クリーンアップによる効率的なストレージ利用
- **可視化**: RSS取得状況をリアルタイムで確認可能

---

## 主要機能

### 記事管理
- **RSS記事の自動取得**: Laravelスケジューラーによる5分間隔での自動取得
- **重複チェック**: URL単位での重複記事の自動除外
- **エラーハンドリング**: リトライ機能とエラーログによる堅牢な取得処理

### 検索機能
- **タイトル検索**: 日本語部分一致検索（N-gram全文検索対応）
- **日付範囲検索**: 公開日による期間指定検索
- **複合検索**: タイトルと日付範囲の組み合わせ検索
- **高速表示**: ページネーション対応（100万件のデータでも高速動作）

### データライフサイクル管理
- **自動削除**: 90日以上経過した記事の自動削除（設定変更可能）
- **ログ管理**: RSS取得ログの自動クリーンアップ
- **ストレージ最適化**: 定期的なデータ整理による効率的なストレージ利用

### その他
- **RSS取得状況の可視化**: 最新の取得状況をダッシュボードで確認
- **カスタムエラーページ**: 404/500/503エラーに対応したユーザーフレンドリーなエラー表示
- **検索フォームクリア**: JavaScriptによる使いやすいUI

---

## 技術スタック

### バックエンド
- **Laravel**: 10.x
- **PHP**: 8.1以上
- **Guzzle HTTP**: 7.2 - HTTPクライアント

### データベース
- **MySQL**: 8.0以上（N-gram全文検索サポート必須）
- **N-gram Parser**: 日本語部分一致検索対応

### フロントエンド
- **Blade**: Laravelテンプレートエンジン
- **Alpine.js**: 軽量JavaScriptフレームワーク
- **Tailwind CSS**: ユーティリティファーストCSSフレームワーク

### その他
- **Laravel Scheduler**: タスクの定期実行
- **Composer**: PHPパッケージ管理

---

## システム要件

### 必須環境

| 項目 | バージョン |
|------|-----------|
| PHP | 8.1以上 |
| Composer | 最新版 |
| MySQL | 8.0以上（N-gramサポート） |
| Webサーバー | nginx または Apache |
| OS | Linux（Ubuntu 22.04 LTS推奨）または Windows |

### 必要なPHP拡張モジュール

- php-cli
- php-fpm（nginx使用時）
- php-mysql
- php-xml
- php-mbstring
- php-curl
- php-zip
- php-gd
- php-bcmath

### cron（定期実行用）

- Linux: cron
- Windows: タスクスケジューラー

---

## インストール手順

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd newsweek-rss
```

### 2. Composer依存パッケージのインストール

```bash
composer install
```

開発環境の場合:
```bash
composer install
```

本番環境の場合:
```bash
composer install --optimize-autoloader --no-dev
```

### 3. 環境設定ファイルの作成

```bash
cp .env.example .env
```

### 4. アプリケーションキーの生成

```bash
php artisan key:generate
```

### 5. データベースの作成

MySQLにログインして以下を実行:

```sql
CREATE DATABASE newsweek_rss CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'newsweek_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON newsweek_rss.* TO 'newsweek_user'@'localhost';
FLUSH PRIVILEGES;
```

### 6. 環境変数の設定

`.env`ファイルを編集して、データベース接続情報を設定してください（詳細は[環境設定](#環境設定)を参照）。

### 7. データベースマイグレーション

```bash
php artisan migrate
```

### 8. 初回RSS取得（任意）

```bash
php artisan rss:fetch
```

### 9. 開発サーバーの起動（開発環境のみ）

```bash
php artisan serve
```

ブラウザで `http://localhost:8000` にアクセスして動作確認してください。

### 10. cronの設定（本番環境）

詳細は[スケジュール設定](#スケジュール設定)を参照してください。

**本番環境への詳細な導入手順については、[docs/導入手順書.md](docs/導入手順書.md)を参照してください。**

---

## 環境設定

`.env`ファイルで以下の環境変数を設定してください。

### アプリケーション設定

```env
APP_NAME="Newsweek RSS"
APP_ENV=production          # 本番環境: production, 開発環境: local
APP_DEBUG=false             # 本番環境: false, 開発環境: true
APP_URL=http://your-domain.com
```

### データベース設定

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=newsweek_rss
DB_USERNAME=newsweek_user
DB_PASSWORD=your_secure_password_here
```

### RSS取得設定

| 環境変数 | 説明 | デフォルト値 |
|---------|------|------------|
| `RSS_FEED_URL` | RSSフィードのURL | `https://www.newsweekjapan.jp/story/rss.xml` |
| `RSS_USER_AGENT` | HTTPリクエストのUser-Agent（**必須**） | `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36` |
| `RSS_TIMEOUT` | タイムアウト時間（秒） | `30` |
| `RSS_RETRY_COUNT` | リトライ回数 | `3` |
| `RSS_RETRY_DELAY` | リトライ間隔（秒） | `2` |

**重要**: `RSS_USER_AGENT`は必ず設定してください。設定しないとHTMLが返されてXMLパースが失敗します。

### データライフサイクル設定

| 環境変数 | 説明 | デフォルト値 |
|---------|------|------------|
| `ARTICLE_RETENTION_DAYS` | 記事の保持期間（日） | `90` |
| `LOG_RETENTION_DAYS` | ログの保持期間（日） | `90` |

設定例:

```env
# RSS Feed Settings
RSS_FEED_URL=https://www.newsweekjapan.jp/story/rss.xml
RSS_USER_AGENT="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
RSS_TIMEOUT=30
RSS_RETRY_COUNT=3
RSS_RETRY_DELAY=2

# データライフサイクル設定
ARTICLE_RETENTION_DAYS=90
LOG_RETENTION_DAYS=90
```

---

## 使い方

### Artisanコマンド

#### RSS取得（手動実行）

```bash
php artisan rss:fetch
```

最新のRSSフィードを取得して、新規記事をデータベースに保存します。

#### 古い記事の削除

```bash
# 実際に削除
php artisan cleanup:old-articles

# 削除対象を確認のみ（dry-run）
php artisan cleanup:old-articles --dry-run
```

`ARTICLE_RETENTION_DAYS`で指定した日数より古い記事を削除します。

#### 古いログの削除

```bash
# 実際に削除
php artisan cleanup:old-logs

# 削除対象を確認のみ（dry-run）
php artisan cleanup:old-logs --dry-run
```

`LOG_RETENTION_DAYS`で指定した日数より古いRSS取得ログを削除します。

#### パフォーマンステスト

```bash
php artisan test:performance
```

検索パフォーマンスのテストを実行します（開発・検証用）。

### Web画面

#### 記事一覧・検索画面

ブラウザで `http://your-domain.com` にアクセスすると、記事一覧・検索画面が表示されます。

**機能:**
- タイトル検索（部分一致）
- 日付範囲検索（開始日〜終了日）
- 複合検索（タイトル + 日付範囲）
- ページネーション（1ページ20件）
- RSS取得状況の表示

---

## スケジュール設定

Laravelスケジューラーを使用して、以下のタスクが自動実行されます。

### 実行スケジュール

| タスク | 実行タイミング | コマンド | 説明 |
|--------|--------------|---------|------|
| RSS取得 | 5分ごと | `rss:fetch` | 最新記事を取得してデータベースに保存 |
| 記事削除 | 毎日午前3時 | `cleanup:old-articles` | 90日以上前の記事を削除 |
| ログ削除 | 毎日午前3時10分 | `cleanup:old-logs` | 90日以上前のログを削除 |

### cron設定（Linux）

以下のコマンドでcronを設定します:

```bash
# www-dataユーザーのcrontabを編集（nginx使用時）
sudo crontab -u www-data -e
```

以下の行を追加:

```cron
* * * * * cd /var/www/newsweek-rss && php artisan schedule:run >> /dev/null 2>&1
```

### タスクスケジューラー設定（Windows）

Windowsの場合、タスクスケジューラーで以下のコマンドを1分ごとに実行するように設定します:

```cmd
cd C:\path\to\newsweek-rss && php artisan schedule:run
```

### スケジュール確認

設定されているスケジュールを確認:

```bash
php artisan schedule:list
```

手動でスケジューラーを実行（テスト用）:

```bash
php artisan schedule:run
```

---

## プロジェクト構成

### ディレクトリ構成

```
newsweek-rss/
├── app/
│   ├── Console/
│   │   ├── Commands/           # Artisanコマンド
│   │   │   ├── FetchRssCommand.php
│   │   │   ├── CleanupOldArticlesCommand.php
│   │   │   ├── CleanupOldLogsCommand.php
│   │   │   └── PerformanceTestCommand.php
│   │   └── Kernel.php          # スケジューラー設定
│   ├── Http/
│   │   ├── Controllers/        # コントローラー（薄く保つ）
│   │   │   └── ArticleController.php
│   │   ├── Requests/           # FormRequestバリデーション
│   │   └── Middleware/
│   ├── Models/                 # Eloquent Model
│   │   ├── Article.php
│   │   └── RssFetchLog.php
│   ├── Services/               # ビジネスロジック層
│   │   └── RssFeedService.php
│   └── Exceptions/             # カスタム例外
├── database/
│   ├── migrations/             # データベースマイグレーション
│   └── seeders/
├── resources/
│   ├── views/                  # Bladeテンプレート
│   │   ├── articles/
│   │   │   └── index.blade.php
│   │   ├── components/
│   │   ├── errors/
│   │   └── layouts/
│   └── js/                     # JavaScriptファイル
├── docs/                       # ドキュメント
│   ├── 導入手順書.md
│   ├── 20251123_2243_データベース仕様書.md
│   ├── 20251124_1359_検索パフォーマンス設計.md
│   ├── 20251125_0100_RSS取得機能設計.md
│   ├── 20251125_1500_記事検索機能設計.md
│   ├── 20251125_1530_データライフサイクル管理設計.md
│   ├── 20251125_1930_パフォーマンステスト結果.md
│   └── 20251125_2000_エラーハンドリング実装.md
├── CLAUDE.md                   # 開発ガイドライン
└── README.md                   # このファイル
```

### レイヤー構成

```
Controller (薄い層)
    ↓ FormRequestでバリデーション
Service (ビジネスロジック)
    ↓
Model + Scope (データアクセス)
    ↓
Database
```

**設計方針:**
- コントローラーは薄く保ち、ビジネスロジックをサービス層に委譲
- データアクセスはサービスから直接Eloquentを使用（Repository層は不採用）
- 複雑な検索条件はModelのScopeで整理
- バリデーションはFormRequestクラスで分離

---

## 開発ガイドライン

本プロジェクトの開発ルール、コーディング規約、アーキテクチャ方針は[CLAUDE.md](CLAUDE.md)に記載されています。

### 主要なルール

#### アーキテクチャ
- コントローラーは薄く保ち、ビジネスロジックはサービス層に委譲
- データアクセスはサービスから直接Eloquentを使用
- 複雑な検索条件はModelのScopeで整理

#### フロントエンド
- テンプレートエンジン: Blade
- JavaScriptフレームワーク: Alpine.js
- Bladeテンプレート内にHTMLとPHPロジックを混在させない

#### 定期実行タスク
- バッチ処理や定期実行タスクはLaravelスケジューラーを使用
- `app/Console/Kernel.php`で実行タイミングを設定

#### Git操作
- featureブランチを切ってコミット後、PRを作成
- develop/mainへの直接pushは禁止
- ロジック変更後は`php artisan test`を実行

詳細は[CLAUDE.md](CLAUDE.md)を参照してください。

---

## データベース設計

### 主要テーブル

#### articles（記事テーブル）

RSSから取得した記事データを保存するメインテーブル。

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | BIGINT UNSIGNED | 主キー |
| url | VARCHAR(255) | 記事URL（ユニーク） |
| title | VARCHAR(255) | 記事タイトル |
| content | TEXT | 記事の内容 |
| published_at | DATETIME | 記事公開日時 |
| created_at | TIMESTAMP | レコード作成日時 |
| updated_at | TIMESTAMP | レコード更新日時 |

**インデックス:**
- `idx_url`: URL完全一致検索・重複チェック用（UNIQUE）
- `idx_published_at`: 日付範囲検索・ソート用
- `idx_title`: タイトル部分一致検索用（FULLTEXT N-gram）
- `idx_created_at`: 古いデータ削除時の効率化

#### rss_fetch_logs（RSS取得ログテーブル）

RSS取得処理の実行履歴を記録するテーブル。

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | BIGINT UNSIGNED | 主キー |
| status | ENUM('success', 'error') | 取得結果 |
| fetched_count | INT UNSIGNED | 取得件数 |
| new_count | INT UNSIGNED | 新規保存件数 |
| error_message | TEXT | エラーメッセージ（失敗時） |
| executed_at | DATETIME | 実行日時 |
| created_at | TIMESTAMP | レコード作成日時 |
| updated_at | TIMESTAMP | レコード更新日時 |

**インデックス:**
- `idx_executed_at`: 実行日時検索用
- `idx_status`: ステータスフィルタリング用

### パフォーマンス設計

**N-gram全文検索:**
- MySQL 8.0のN-gram Full-Text Parserを使用
- 日本語の部分一致検索に対応
- `LIKE '%keyword%'`よりも圧倒的に高速
- 100万件のデータでも0.5秒以内で検索完了

**インデックス戦略:**
- 頻繁に検索・ソートされるカラムにインデックスを設定
- WHERE句とORDER BY句を考慮した複合インデックス
- 大量データを扱う場合のパフォーマンスを重視

詳細は[docs/20251123_2243_データベース仕様書.md](docs/20251123_2243_データベース仕様書.md)を参照してください。

---

## ドキュメント

プロジェクトの詳細な設計書は[docs/](docs/)ディレクトリに格納されています。

### 設計書一覧

| ドキュメント | 説明 |
|------------|------|
| [導入手順書.md](docs/導入手順書.md) | 本番環境への詳細な導入手順 |
| [データベース仕様書.md](docs/20251123_2243_データベース仕様書.md) | テーブル設計、インデックス設計 |
| [検索パフォーマンス設計.md](docs/20251124_1359_検索パフォーマンス設計.md) | N-gram全文検索の設計と最適化 |
| [RSS取得機能設計.md](docs/20251125_0100_RSS取得機能設計.md) | RSS取得処理の詳細設計 |
| [記事検索機能設計.md](docs/20251125_1500_記事検索機能設計.md) | 検索機能の実装設計 |
| [データライフサイクル管理設計.md](docs/20251125_1530_データライフサイクル管理設計.md) | 古いデータの削除処理設計 |
| [パフォーマンステスト結果.md](docs/20251125_1930_パフォーマンステスト結果.md) | 大量データでの性能測定結果 |
| [エラーハンドリング実装.md](docs/20251125_2000_エラーハンドリング実装.md) | エラー処理の実装方針 |

### 設計作業ルール

新しい設計ドキュメントを作成する場合は、以下のルールに従ってください:

- **ファイル名**: `YYYYMMDD_HHMM_{日本語の作業内容}.md`
- **保存場所**: `docs/`ディレクトリ
- **フォーマット**: Markdown

例: `docs/20250815_1430_記事検索機能設計.md`

---

## ライセンス

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
