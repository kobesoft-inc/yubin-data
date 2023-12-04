# yubin-data

## 1. 概要

郵便番号データを扱うためのライブラリです。

## 2. インストール

```bash
composer config repositories.kobesoft/yubin-data vcs https://github.com/kobesoft-inc/yubin-data.git
composer require kobesoft/yubin-data:dev-main
```

## 3. 使い方

### 3.1. データの読み込み

郵便番号データは、artisanコマンドで読み込みます。

```bash
php artisan import:yubin-data
```

### 3.2. モデル

郵便番号データは、以下のように取得します。

```php
use Kobesoft\YubinData\Models\JpZipCode;

JpZipCode::whereZipCode('0000000')->first();
```
