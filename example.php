<?php
require('crawler_class.php');
require('multi.php');

use jonathonhill\Multiprocess;
use leonhardly\crawler;
$crawler = new crawler;

$url = 'http://google.com/search?q=random';

$crawler->startThread($url);

$crawler->initiate(20, function($crawler) {
    
    $queue = $crawler->count - $crawler->iteration_count;
    sleep(1);
    echo '<br>Queue: ' . $queue;
    echo '<br>Site count: ' . $crawler->count;
    
    if ($queue > 20) {
        $pass_in = array_slice($crawler->site_store, $crawler->iteration_count, $crawler->iteration_count + 20);
        $threads = 20;
    } else if ($queue == 0) {
        //die('No more URLs found');
    } else {
        $pass_in = array_slice($crawler->site_store, $crawler->iteration_count);
        $threads = $queue;
    }

    file_put_contents('data_store', null);
    
    (new Multiprocess($pass_in, function($value) use($crawler) {
        $crawler->startThread($value);
        
        $test[] = 123;
    }))->run($threads);
    
    $this->site_store[] = file('data_store', FILE_IGNORE_NEW_LINES);
    
    $crawler->iteration_count = $crawler->iteration_count + $threads;
});
echo '<br>finished';
