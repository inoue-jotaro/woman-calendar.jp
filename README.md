# ホスティングサービス用Wordpress

[Bedrock](https://roots.io/bedrock/) を使用。

## 概要
* 本体の更新やプラグイン導入はComposerを使用する。
  * `composer.json`,`composer.lock` に定義される。
* 導入環境の設定は.envに行なう
* テーマは別管理なので、このリポジトリでは導入プラグインの管理だけ

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

### 2. `.env.example` をコピーし `.env` ファイルに環境を記述
  手元でプラグインの導入削除するだけなら以下だけ追記すればいい
  * `ACF_KEY` : Advanced Custom Fields のライセンスコード

### 3. Wordpressのアップデートとプラグイン導入削除
以下コマンド、または`composer.json`直接弄ってから`composer update`でもよい
```ShellSession
  # 導入とアップデート
  $ composer update

  # 追加
  $ composer require wpackagist-plugin/classic-editor:*

  # 削除
  $ composer remove wpackagist-plugin/classic-editor:*
```

Wordpress公式で配布されているものならwpackagistにホストされてるはず

https://ja.wordpress.org/plugins/wp-multibyte-patch/ Wordpress公式配布URL内に書かれているプラグイン名を元に https://wpackagist.org/ で調べる。

バージョン指定は `*` で最新を入れるよう指定

### 3.5. プラグイン日本語化
[ここ](https://github.com/wp-languages/wp-languages.github.io#manually-adding-any-language-zip-to-your-composerjson)を参考に、Wordpress公式翻訳リポジトリから直接取る記述を追加し、requireする。

{%version} の記述が使えるのでurlにはバージョン書かなくていい。

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

#### 翻訳のアップデート

API https://api.wordpress.org/translations/plugins/1.0/?slug=<プラグイン名> から翻訳バージョンを調べる。
repositories.version項に記述し、`composer update`する。


### 4. githubリポジトリを作成
  * リポジトリ名 : hosting-**<ドメイン名>**
  * originを変更
  ```ShellSession
  $ git remote set-url origin <新リポジトリ>
  ```

  * プラグイン導入削除しただけなら `composer.json`と`composer.lock`だけが変更されている
  ```ShellSession
  $ git commit -a -m "Add/Remove plugins."
  $ git push
  ```

### 導入先環境作成
[こちらに書く](https://github.com/cookpad-baby/BabyPad-ansible)
