# iflytek-php-sdk

基于laravel的讯飞文本纠错

[![image](https://img.shields.io/github/stars/jiaoyu-cn/iflytek-php-sdk)](https://github.com/jiaoyu-cn/iflytek-php-sdk/stargazers)
[![image](https://img.shields.io/github/forks/jiaoyu-cn/iflytek-php-sdk)](https://github.com/jiaoyu-cn/iflytek-php-sdk/network/members)
[![image](https://img.shields.io/github/issues/jiaoyu-cn/iflytek-php-sdk)](https://github.com/jiaoyu-cn/iflytek-php-sdk/issues)

## 安装

```shell
composer require githen/iflytek-php-sdk:~v1.0.0

# 迁移配置文件
php artisan vendor:publish --provider="Githen\IflytekPhpSdk\Providers\IflytekServiceProvider"
```

## 配置文件说明

生成`iflytek.php`上传配置文件

```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | 讯飞开放平台配置
    |--------------------------------------------------------------------------
    |
    */
    // 开放平台 AppId
    'app_id' => '',
    // 开放平台秘钥 APISecret
    'api_secret' => '',
    // 开放平台秘钥 APIKey
    'api_key' => '',
    
];
```
