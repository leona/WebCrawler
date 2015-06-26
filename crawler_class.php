<?php
namespace leonhardly;

class crawler {

    public $site_store      = array();
    public $sites_crawled   = array();
    public $dom             = array();
    public $count           = 0;
    public $iteration_count = 0;
    public $condition;

    public function __construct() {

    }

    public function condition($condition = null) {
        $this->condition = $condition;

        return $this;
    }

    public function initiate($depth = 100, $callback) {
        while($this->count < $depth) {
            $callback($this);
        }
    }

    public function start($url, $length) {
        $this->initThread($url);

        while ($this->count < $length) {
            if (empty($this->site_store[$this->iteration_count])) {
                echo '<br>No more URLs found<br>';
                break;
            }

            $this->initThread($this->site_store[$this->iteration_count]);

            echo 'Site count: ' . $this->count . ' Iteration count: ' . $this->iteration_count . "\r\n";
        }
        file_put_contents('log.txt', print_r($this->site_store, true));

        return $this;
    }

    private function cleanUp() {
        $this->site_store = array_unique($this->site_store);
    }


    public function startThread($value) {file_put_contents('data_store', implode("\r\n", $this->site_store), FILE_APPEND);
        if (!in_array($value, $this->sites_crawled)) {
            $this->sites_crawled[] = $value;
            $this->fetchDOM($value);
        }
    }

    public function fetchDOM($url) {
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

        $this->parseLinks($url);

        return $this;
    }

    private function parseLinks($url) {
        $this->origin_host = parse_url($url)['host'];

        foreach($this->dom[$url]->getElementsByTagname('a') as $link) {
            $this->validateURL($link->getAttribute('href'), $origin_host);
        }
        $this->cleanUp();
        
        file_put_contents('data_store', implode("\r\n", $this->site_store), FILE_APPEND);
        
        return $this;
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
