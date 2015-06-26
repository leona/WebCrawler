<?php
// Source http://jonathonhill.net/projects/multiprocessing/

namespace jonathonhill;

class Multiprocess {
	
	protected $processes = array();
	protected $work_queue = array();
	protected $callback;
	
	public function __construct($data, $callback) {
		$this->work_queue = $data;
		$this->callback = $callback;
	}
	
	public function run($concurrent = 5) {
	
		$this->completed = 0;
		foreach($this->work_queue as $data) {

			$pid = pcntl_fork(); // clone

			switch($pid) {
				case -1:
					throw new Exception("Out of memory!");
					
				case 0:
					// child process
					call_user_func($this->callback, $data);
					exit(0);
					
				default:
					// parent process
					$this->processes[$pid] = TRUE; // log the child process ID
			}
		
			// wait on a process to finish if we're at our concurrency limit
			while(count($this->processes) >= $concurrent) {
				$this->reap_child();
				usleep(500);
			}
		}
		
		// wait on remaining processes to finish
		while(count($this->processes) > 0) {
			$this->reap_child();
			usleep(500);
		}
	}
	
	protected function reap_child() {

		// check if any child process has terminated,
		// and if so remove it from memory
		$pid = pcntl_wait($status, WNOHANG);

		if($pid < 0) {
 			throw new Exception("Error: out of memory!");
 		} elseif($pid > 0) {
			unset($this->processes[$pid]);
		}
	}	
}