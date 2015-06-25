<?php
require('crawler_class.php');

use leonhardly\crawler;

$crawler = new crawler;

$crawler->condition(function($url) {
    return true;
})->start('https://www.google.ie/search?q=random', 5);

print_r($crawler->site_store);