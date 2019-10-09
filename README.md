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
* Composer 以下で入れるのが楽
  * https://brew.sh/index_ja HomeBrew
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

### 3. アップロードファイルを追加 (必要時のみ)
`web/app/uploads` にファイル・フォルダを追加

```ShellSession
$ git add -f web/app/uploads/
$ git commit -m "Add upload files."
$ git push
```

### 4. プラグイン導入 (必要時)
※hosting-wordpressにはこれまで使った全プラグイン導入するように設定してあるので、これを弄ることはあまりないはず

#### composerを実行できるようにする
`.env.example` をコピーし `.env` ファイルに環境を記述

プラグインの導入するだけなら以下だけ追記すればいい
* `ACF_KEY` : Advanced Custom Fields のライセンスコード

#### composerで導入
以下コマンド、または`composer.json`直接弄ってもよい
```ShellSession
# 追加
$ composer require wpackagist-plugin/classic-editor:*

# 削除
$ composer remove wpackagist-plugin/classic-editor:*
```

Wordpress公式で配布されているものならwpackagistにホストされてるはず

https://ja.wordpress.org/plugins/wp-multibyte-patch/ Wordpress公式配布URL内に書かれているプラグイン名を元に https://wpackagist.org/ で調べる。

バージョン指定は `*` で最新を入れるよう指定

#### composer.json コミット
* プラグイン導入したら変更された `composer.json`をgitにコミットする
```ShellSession
$ git commit -a -m "Add/Remove plugins."
$ git push
```

#### プラグイン日本語翻訳の追加
[ここ](https://github.com/wp-languages/wp-languages.github.io#manually-adding-any-language-zip-to-your-composerjson)を参考に、Wordpress公式翻訳リポジトリから直接取る記述を追加し、requireする。

{%version} の記述が使えるのでurlにはバージョン書かなくていい。

個別gitリポジトリでなく hosting-wordpress リポジトリに記述すること。

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
repositories.version項に記述

### 5. (各産院用個別リポジトリ用) hosting-wordpress リポジトリに追従
```ShellSession
$ git fetch upstream
$ git merge upstream/master
$ git push
```

### 導入先環境作成
[こちらに書く](https://github.com/cookpad-baby/BabyPad-ansible)
