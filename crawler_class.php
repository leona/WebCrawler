<?php

namespace leonhardly;

class crawler {
    
    public $site_store = array();
    private $work_array;
    private $dom;
    private $work_url;
    private $work_url_host;
    private $count;
    private $condition;
    
    public function condition($condition = null) {
        $this->condition = $condition;
        
        return $this;
    }
    
    public function start($url, $length) {
        $this->load($url);

        for($i = 0; $i < $length;$i++) {
            if (empty($this->site_store[$i])) {
                echo '<br>No more URLs found<br>';
                break;
            }
  
            $this->load($this->site_store[$i]);
        }
        $this->site_store = array_unique($this->site_store);
    }
    
    private function loadURLs() {
        $this->work_array = array();
        
        foreach($this->dom->getElementsByTagname('a') as $link) {
            $this->validateURL($link->getAttribute('href'));
        }
        
        return $this->work_array;
    }
    
    private function validateURL($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            $href = parse_url($url);

            if ($href['host'] !== $this->work_url_parsed['host']) {
                $condition = $this->condition;
                if ($condition == null || $condition($url))
                    $this->site_store[] = $href['scheme'] . '://' . $href['host'];
            }
        }
    }
    private function load($url) {
        $this->work_url = $url;
        $this->work_url_parsed = parse_url($url);
        $this->count++;
        
        try {
            $this->dom = new \DOMDocument();
            $this->html = @file_get_contents($this->work_url);
            
            if (!$this->html) return false;
            
            @$this->dom->loadHTML(file_get_contents($this->work_url));
        } catch(Exception $e) {}
        
        $this->loadURLs();
        return $this;
    }
}