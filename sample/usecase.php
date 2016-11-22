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
