<?php
require('crawler_class.php');

use leonhardly\crawler;

$crawler = new crawler('https://www.google.ie/search?q=random', 5);

print_r($crawler->site_store);