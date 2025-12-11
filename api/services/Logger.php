<?php

class Logger
{
    private $logFile;

    public function __construct($logFile)
    {
        $this->logFile = $logFile;
        $this->ensureLogDirectory();
    }

    private function ensureLogDirectory()
    {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function log($message, $level = 'INFO', $data = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message";
        
        if ($data !== null) {
            $logEntry .= ' | Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        $logEntry .= PHP_EOL;
        
        error_log($logEntry, 3, $this->logFile);
    }

    public function info($message, $data = null)
    {
        $this->log($message, 'INFO', $data);
    }

    public function error($message, $data = null)
    {
        $this->log($message, 'ERROR', $data);
    }

    public function warning($message, $data = null)
    {
        $this->log($message, 'WARNING', $data);
    }

    public function debug($message, $data = null)
    {
        $this->log($message, 'DEBUG', $data);
    }
}
