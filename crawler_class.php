<?php
namespace leonhardly;
require('multi.php');

use jonathonhill\Multiprocess;

class crawler {

    public $site_store = array();
    private $work_array = array();
    private $dom = array();
    private $work_url = array();
    private $work_url_host = array();
    private $count;
    private $iteration_count;
    private $condition;
    private $sites_crawled = array();

    public function __construct() {
        $this->iteration_count = 0;
        $this->count = 0;
    }

    public function condition($condition = null) {
        $this->condition = $condition;

        return $this;
    }

    public function start($url, $length) {
        $this->processThread($url);

        do {
            if (empty($this->site_store[$this->iteration_count])) {
                echo '<br>No more URLs found<br>';
                break;
            }

            if ($this->iteration_count > 20 && $this->iteration_count <= $this->count - 20) {

                $cut = array_slice($this->site_store, $this->iteration_count - 1);

                (new Multiprocess($cut, function($value) {
                    $this->processThread($value);
                }))->run(20);
            } else {

                $this->processThread($this->site_store[$this->iteration_count]);
            }
        } while($this->count < $length);

        $this->site_store = array_unique($this->site_store);

        return $this;
    }

    public function processThread($value) {
        if (!in_array($value, $this->sites_crawled)) {
            $this->sites_crawled[] = $value;
            $this->load($value);
        }
    }

    public function load($url) {
        $this->iteration_count++;

        try {
            $this->dom[$url]  = new \DOMDocument();
            $this->html[$url] = @file_get_contents($url);

            if (empty($this->html[$url])) {
                echo 'HTTP failure: ' . $url . "\r\n";
                return false;
            }

            @$this->dom[$url]->loadHTML($this->html[$url]);
        } catch(Exception $e) {}

        $this->loadURLs($url);

        return $this;
    }

    private function loadURLs($url) {
        $origin_host = parse_url($url)['host'];
        foreach($this->dom[$url]->getElementsByTagname('a') as $link) {
            $this->validateURL($link->getAttribute('href'), $origin_host);
        }

        return $this->work_array;
    }

    public function afterLoop($callback) {
        $new_store = array();

        foreach($this->site_store as $key => $url) {
            $result = $callback($key, $url);

            if ($result !== false) $new_store[] = $result;
        }

        $this->site_store = array_filter($new_store);
    }

    private function validateURL($url, $origin_host) {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            $href = parse_url($url);

            if (!empty($href['host']) && $href['host'] !== $origin_host) {
                $condition = $this->condition;
                if ($condition == null || $condition($url)) {
                    $url = $href['scheme'] . '://' . $href['host'];
                    if (!in_array($url, $this->site_store)) {
                        $this->site_store[] = $url;
                        $this->count++;
                        echo $url . "\r\n";
                    }
                }

            }
        }
    }

}
