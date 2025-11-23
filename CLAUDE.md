# Claude Code 設定

## 設計作業ルール

設計作業を依頼された場合は、以下のルールに従ってファイルを作成すること：

- ファイル名: `YYYYMMDD_HHMM_{日本語の作業内容}.md`
- 保存場所: `docs/` 以下
- フォーマット: Markdown

例: `docs/20250815_1430_記事検索機能設計.md`

---

## Laravelアプリケーションの実装ルール

### アーキテクチャ

- コントローラーは薄く保ち、ビジネスロジックはサービス層に委譲する
- データアクセスはサービスから直接Eloquentを使用（Repository層は不採用）
- 複雑な検索条件はModelのScopeで整理し、サービスで組み立てる
- バリデーションはFormRequestクラスを使用し、コントローラーから分離する

### レイヤー構成

```
Controller (薄い) 
    ↓ FormRequest でバリデーション
Service (ビジネスロジック)
    ↓
Model + Scope (データアクセス)
    ↓
Database
```

### フロントエンド

- テンプレートエンジン: Blade
- JavaScriptフレームワーク: Alpine.js
- 理由: 軽量でBladeと相性が良く、学習コストが低い。Vue/Reactのような大規模SPAフレームワークは、必要性が明確な場合のみ採用
- Bladeテンプレート内にHTMLとPHPロジックを混在させない（ビューとロジックの分離）

### 定期実行タスク

- バッチ処理や定期実行タスクはLaravelスケジューラーを使用
- `app/Console/Kernel.php` で実行タイミングを設定
- システムのcronで `* * * * * cd /path-to-project && php artisan schedule:run` を登録
- 定期実行するコマンドは `app/Console/Commands/` に配置

### データライフサイクル管理

- 古いデータの削除が必要な場合は、専用のArtisanコマンドを作成
- スケジューラーで定期的に実行
- 削除ロジックはサービス層に委譲し、コマンドは薄く保つ

---

## コード構成

以下のディレクトリ構成に従ってコードを配置する：

```
app/
├── Console/
│   ├── Commands/
│   └── Kernel.php
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
├── Models/
├── Services/
├── Exceptions/
└── DTOs/

resources/
├── views/
│   └── layouts/
└── js/

database/
├── migrations/
└── seeders/
```

### 各ディレクトリの責務

- **Console/**
  - Commands/ - Artisanコマンド（バッチ処理、定期実行タスクなど）
  - Kernel.php - スケジューラー設定、コマンド登録

- **Http/**
  - Controllers/ - HTTPリクエストの受付とレスポンス返却（薄く保つ）
  - Requests/ - FormRequestクラスによるバリデーションルール
  - Middleware/ - リクエスト前後の共通処理

- **Models/**
  - Eloquent Model + Scopeメソッド
  - データベーステーブルとの対応
  - リレーション定義

- **Services/**
  - ビジネスロジック層
  - 複数のModelやロジックを組み合わせた処理
  - トランザクション制御

- **Exceptions/**
  - カスタム例外クラス
  - エラーハンドリングの拡張

- **DTOs/** (Data Transfer Objects)
  - データの受け渡しに使用する値オブジェクト
  - 必要に応じて使用

- **resources/**
  - views/ - Bladeテンプレート
  - js/ - JavaScriptファイル（Alpine.jsコンポーネントなど）

- **database/**
  - migrations/ - データベーススキーマのバージョン管理
  - seeders/ - テストデータ投入

---

## データベース設計ルール

### マイグレーション

- すべてのテーブル変更はマイグレーションで管理
- インデックスは必ずマイグレーションファイルに明記
- 検索条件として使われるカラムにはインデックスを付ける
- 複合検索がある場合は複合インデックスを検討

### インデックス設計の方針

- 頻繁に検索・ソートされるカラムにインデックスを設定
- 部分一致検索（LIKE）にはプレフィックスインデックスを活用
- WHERE句とORDER BY句の組み合わせを考慮した複合インデックス
- 大量データを扱う場合は特にパフォーマンスを意識

---

## 命名規則

### クラス

- **Service**: `{Domain}Service` (例: `UserService`, `OrderService`)
- **FormRequest**: `{Domain}{Action}Request` (例: `UserStoreRequest`, `OrderSearchRequest`)
- **Command**: `{Action}Command` (例: `SendNotificationCommand`, `CleanupOldDataCommand`)
- **Exception**: `{Domain}Exception` (例: `PaymentException`, `ExternalApiException`)

### Model Scope

- **Scope**: `scope{Condition}` (例: `scopeActive`, `scopeRecent`, `scopeByStatus`)

```php
// Model内での定義例
public function scopeActive($query) {
    return $query->where('is_active', true);
}

// 使用例
Model::active()->recent()->paginate(20);
```

---

## コメント・ドキュメント

### PHPDoc

- すべてのpublicメソッドにPHPDocを記述
- パラメータの型、戻り値の型、例外を明記

```php
/**
 * データを処理して結果を返す
 *
 * @param array $data 処理対象のデータ
 * @return bool 処理の成否
 * @throws CustomException 処理に失敗した場合
 */
public function process(array $data): bool
{
    // ...
}
```

### 日本語コメント

- 複雑なビジネスロジックには日本語コメントを記述
- 特に検索条件の組み立て、パフォーマンス対策の理由などを明記

---

## エラーハンドリング

### 例外の使い分け

- **Laravel標準の例外**: 基本的にこれを使用
  - `ValidationException`: バリデーションエラー（FormRequestが自動throw）
  - `ModelNotFoundException`: モデルが見つからない
  - `QueryException`: データベースエラー

- **カスタム例外**: 特定のドメインロジックで専用例外が必要な場合のみ追加
  - 例: `ExternalApiException`（外部API連携失敗）、`DataProcessException`（データ処理失敗）

### エラーハンドリングの実装例

```php
// Service内での基本的なエラーハンドリング
try {
    // ビジネスロジック実行
    $result = $this->someOperation();
} catch (CustomException $e) {
    // カスタム例外の処理
    Log::error('Operation failed', [
        'message' => $e->getMessage(),
        'context' => $additionalContext,
    ]);
    throw $e;
}
```

### エラーログの記録

- 重要なエラーは必ずログに記録（`Log::error()`）
- コンテキスト情報（URL、ユーザーIDなど）を含める
- 必要に応じてエラー状態をデータベースに保存し、画面で可視化

---

## パフォーマンス対策

### Eager Loadingの徹底

- N+1問題を防ぐため、リレーションは必ず `with()` で事前ロード
- 使用しないリレーションはロードしない

```php
// Good
$models = Model::with('relation')->get();

// Bad
$models = Model::all();
foreach ($models as $model) {
    echo $model->relation->name; // N+1発生
}
```

### インデックス設計

- マイグレーションファイルにインデックスを明記
- 検索条件とソート条件を考慮した複合インデックス
- 大量データを扱う場合は特にパフォーマンスを意識

### ページネーション

- 検索結果は必ずページネーション（`paginate()`）を使用
- 1ページあたりの件数は適切に設定（一般的には10〜50件程度）

---

## Git操作ルール

- ユーザーから機能追加・修正を依頼されたときは、featureブランチを切りコミットを行ってからPRを出す
- develop/mainへの直接pushは禁止
- マイグレーションを含む変更は、自動デプロイで環境を壊す可能性があるため、ユーザーに許可を取ってから実行
- ロジックに関わる変更をした後のpush前には、以下を実行する:
  - `php artisan test` - テスト実行
  - `./vendor/bin/phpstan analyse` - 静的解析（導入している場合）
  - `./vendor/bin/php-cs-fixer fix` - コードスタイル修正（導入している場合）
- PR作成時は適切なベースブランチ（通常はdevelop）を指定する

---

## テストルール

### 実装推奨

- サービス層のユニットテスト
- FormRequestのバリデーションテスト
- 重要なコマンドの統合テスト
- コントローラーの機能テスト

### テストディレクトリ構成

```
tests/
├── Feature/
│   ├── Http/
│   │   └── Controllers/
│   └── Console/
│       └── Commands/
└── Unit/
    ├── Services/
    └── Requests/
```

---

## 環境・依存関係管理

### 依存パッケージの追加

- 必要なパッケージは `composer.json` で管理
- プロジェクト固有の依存関係は明示的に記載
- フロントエンドライブラリ（Alpine.jsなど）はCDN経由も検討

### 環境変数

- 環境固有の設定は `.env` ファイルで管理
- `.env.example` に必要な項目を記載し、コミット
- 本番環境では適切な値を設定

---

## 補足事項

- 上記ルールは開発の指針であり、プロジェクトの要件に応じて柔軟に変更可能
- ルールの適用により開発効率が低下する場合は、理由を明確にした上で例外を認める
- 新しいベストプラクティスが確立された場合は、ルールを更新する

