<?php

namespace PcntlJob;

/**
 * Class PcntlJob
 * pcntl adapter for process creating
 *
 * example of usage:
 *      $job = new Job;
 *      $job->create(array($this, 'parseRSS'), array($href));
 *      function parseRSS($ref) { ... }
 */
class PcntlJob
{
    protected $childProcesses = array();

    protected $countChildProcesses;


    public function __construct($countChildProcesses = 20)
    {
    	$this->countChildProcesses = $countChildProcesses;
    }

    public function create($closure, $args = array())
    {
        $closureKey = md5(json_encode($closure));
        $created = FALSE;

        if (!isset($this->childProcesses[$closureKey])) {
            $this->childProcesses[$closureKey] = array();
        }

        while (!$created) {

            // whether child process still alive?
            while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
                if ($signaled_pid == -1) {
                    // there are no child processes
                    $this->childProcesses[$closureKey] = array();
                    break;
                } else {
                    unset($this->childProcesses[$closureKey][$signaled_pid]);
                }
            }

            if (count($this->childProcesses[$closureKey]) < 30) {
                $pid = pcntl_fork();

                if ($pid == -1) {
                    throw new \RuntimeException("Error when fork");
                } elseif ($pid) {
                    // parent process
                    // control of quantity of child processes
                    $this->childProcesses[$closureKey][$pid] = TRUE;
                    $created = TRUE;
                } else {
                    // child process

                    if ($args) {
                        call_user_func_array($closure, $args);
                    } else {
                        call_user_func($closure);
                    }

                    // do the job and close
                    exit;
                }
                // here will be both processes - child and parent
            } else {
                sleep(1);
            }
        }
    }
}
