# ホスティングサービス用Wordpress

[Bedrock](https://roots.io/bedrock/) を使用。

## 機能

* 本体・プラグインの自動更新は行なわれない
* 本体の更新やプラグイン導入はComposerを使用する

## 導入

1. プロジェクトを作成
  ```sh
  $ git clone git@github.com:cookpad-baby/hosting-wordpress.git <domain>
  ```

2. データベースとユーザーを作成

3. `.env.example` をコピーし `.env` ファイルに環境を記述
  * `DB_NAME` - データベース名
  * `DB_USER` - データベースユーザー
  * `DB_PASSWORD` - データベースパスワード
  * `DB_HOST` - `db01.babypad.local`
  * `WP_ENV` - `production`
  * `WP_HOME` - `https://<産院domain>`
  * `WP_SITEURL` - `https://<産院domain>`
  * `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
    * [WordPress salts generator](https://roots.io/salts.html) を使用して生成
  * `ACF_KEY` - Advanced Custom Fields のライセンスコードを記述

4. githubリポジトリを作成
  * リポジトリ名 hosting-**産院domain**
  * originを変更
  ```sh
  $ git remote set-url origin <新リポジトリ>
  ```

5. `deploy.php` を編集
  * `set('application', '<産院domain>');`
  * `set('repository', '<新リポジトリ>');`

6. デプロイ
  ```sh
  $ vendor/bin/dep deploy
  ```
