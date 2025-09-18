<?php


class Logger {
    public string $logFile;

    public function __construct(string $logFile) {
        $this->logFile = $logFile;
    }
    

    private function log(string $level, string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf("[%s] [%s] %s\n", $timestamp, strtoupper($level), $message);
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function info(string $message): void {
        $this->log('info', $message);
    }

    public function warning(string $message): void {
        $this->log('warning', $message);
    }

    public function error(string $message): void {
        $this->log('error', $message);
    }
}
