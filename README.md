This PHP library can easily post articles to wordpress with its thumbnail
=============================

[![Coverage Status](https://coveralls.io/repos/github/YuzuruS/post-to-wp/badge.svg?branch=master)](https://coveralls.io/github/YuzuruS/post-to-wp?branch=master)
[![Build Status](https://travis-ci.org/YuzuruS/post-to-wp.png?branch=master)](https://travis-ci.org/YuzuruS/post-to-wp)
[![Stable Version](https://poser.pugx.org/yuzuru-s/post-to-wp/v/stable)](https://packagist.org/packages/yuzuru-s/post-to-wp)
[![Download Count](https://poser.pugx.org/yuzuru-s/post-to-wp/downloads.png)](https://packagist.org/packages/yuzuru-s/post-to-wp)
[![License](https://poser.pugx.org/yuzuru-s/post-to-wp/license)](https://packagist.org/packages/yuzuru-s/post-to-wp)

Requirements
-----------------------------
- PHP
  - >=5.5 >=5.6, >=7.0
- ext-xmlrpc
- Composer



Installation
----------------------------

* Using composer

```
{
    "require": {
       "yuzuru-s/post-to-wp": "1.0.*"
    }
}
```

```
$ php composer.phar update yuzuru-s/post-to-wp --dev
```

How to use
----------------------------
Please check [sample code](https://github.com/YuzuruS/post-to-wp/blob/master/sample/usecase.php)

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
use YuzuruS\Wordpress\Post;

// endpoint → example.com
$wp = new Post(getenv(WP_USERNAME), getenv(WP_PASSWD), getenv(WP_ENDPOINT));

$res = $wp->makeCategories([
	['name' => 'かて1', 'slug' => 'cate1'],
	['name' => 'かて2', 'slug' => 'cate2'],
]);

$wp
	->setTitle('たいとる')
	->setDescription('本文')
	->setKeywords(['key1','key2'])
	->setCategories(['かて1','かて2'])
	->setDate('2016-11-11')
	->setWpSlug('entry')
	->setThumbnail('https://www.pakutaso.com/shared/img/thumb/SAYA160312500I9A3721_TP_V.jpg')
	->post();

```


How to run unit test
----------------------------

Run with default setting.
```
% vendor/bin/phpunit -c phpunit.xml.dist
```

Currently tested with PHP 7.0.0


History
----------------------------




License
----------------------------
Copyright (c) 2016 YUZURU SUZUKI. See MIT-LICENSE for further details.

Copyright
-----------------------------
- Yuzuru Suzuki
  - http://yuzurus.hatenablog.jp/
