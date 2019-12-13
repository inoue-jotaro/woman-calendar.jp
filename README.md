# ホスティングサービス用Wordpress

[Bedrock](https://roots.io/bedrock/) を使用。

## 概要
* 本体の更新やプラグイン導入はComposerを使用する。
  * `composer.json`,`composer.lock` に定義される。
* 導入環境の設定は.envに行なう
* テーマは別管理
  * このリポジトリでは導入プラグインと以前のWordpressでアップロードされたファイルの管理だけ行なう

## 構築に必要なもの
* macOS
* Git
* Composer
  * [HomeBrew](https://brew.sh/index_ja)
  * `brew install composer`

## 構築
以下手順の`<ドメイン名>`は導入先ドメインに置き換えること

### 1. ローカルにプロジェクトを作成
```ShellSession
$ git clone git@github.com:cookpad-baby/hosting-wordpress.git <ドメイン名>
```

### 2. githubリポジトリを作成
[GitHubにリポジトリ作成する](https://github.com/organizations/cookpad-baby/repositories/new)
* リポジトリ名 : hosting-**<ドメイン名>**
* public
* origin変更とupstreamの追加

```ShellSession
$ git remote set-url origin <新リポジトリ>
$ git remote add upstream git@github.com:cookpad-baby/hosting-wordpress.git
$ git push
```

### 3. composer設定

`.env`を生成する。

```ShellSession
$ vi .env
====
ACF_KEY: 〜〜〜〜

```

```ShellSession
$ composer install
$ git add composer.lock
$ git commit -m "Composer update."
$ git push
```

### 4. 導入先環境作成・デプロイ
[こちらに書く](https://github.com/cookpad-baby/BabyPad-ansible/wiki/Create-Wordpress-Host)

----

# その他

### プラグイン追加・変更

#### composer操作

```ShellSession
# 追加
$ composer require wpackagist-plugin/classic-editor:*

# 削除
$ composer remove wpackagist-plugin/classic-editor
```

Wordpress公式で配布されているものならwpackagistにホストされてる

`https://ja.wordpress.org/plugins/` *wp-multibyte-patch* `/`
Wordpress公式配布URL内に書かれているプラグイン名を元に https://wpackagist.org/ で調べる。

バージョン指定は `*` で最新を入れるよう指定

#### コミット
* composer操作で更新された `composer.json`, `composer.lock`をgitにコミットする

```ShellSession
$ git add composer.json composer.lock
$ git commit -m "Add/Remove plugin hogehoge."
$ git push
```

#### プラグイン日本語翻訳の追加
[ここ](https://github.com/wp-languages/wp-languages.github.io#manually-adding-any-language-zip-to-your-composerjson)を参考に、Wordpress公式翻訳リポジトリから直接取る記述を追加し、requireに追記する。

url内のバージョンは `{%version}` に置換する。

```json
{
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "koodimonni-plugin-language/wp-super-cache-ja",
        "type": "wordpress-language",
        "version": "1.6.9",
        "dist": {
          "type": "zip",
          "url": "https://downloads.wordpress.org/translation/plugin/wp-super-cache/{%version}/ja.zip",
          "reference": "master"
        }
      }
    }
  ],
  "require": {
    "koodimonni-plugin-language/wp-super-cache-ja": "*"
  }
}
```

#### プラグイン日本語翻訳のアップデート

API https://api.wordpress.org/translations/plugins/1.0/?slug=<プラグイン名> から翻訳バージョンを調べる。
repositories.package.version項に記述

### アップロードファイルを追加する

※旧Wordpressからの移行時に実施

`web/app/uploads` にファイル・フォルダを追加

```ShellSession
$ git add -f web/app/uploads/
$ git commit -m "Add upload files."
$ git push
```

### (各産院用個別リポジトリ用) hosting-wordpress リポジトリのアップデートに追従する

```ShellSession
$ git fetch upstream
$ git merge upstream/master
$ git push
```
