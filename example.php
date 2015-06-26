<?php
require('crawler_class.php');

use leonhardly\crawler;
$crawler = new crawler;

$crawler->start($argv[1], 200);






