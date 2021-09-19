<?php

namespace Rialto;

use Doctrine\Common\Util\Debug;

/**
 * Writes debug output to a file.
 */
final class Debugger
{
    private static $instances = [];
    const ERROR_LOG_APPEND = 3;

    private $logfile;
    private $caller_depth;
    private $num_calls;

    public static function getInstance($filename, $caller_depth = 2)
    {
        if (!isset(self::$instances[$filename])) {
            self::$instances[$filename] = new Debugger($filename, $caller_depth);
        }
        return self::$instances[$filename];
    }

    public static function getFilename()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli';
        return "/tmp/debug.$host.txt";
    }


    /**
     * Private constructor to enforce singleton design pattern.
     */
    private function __construct($filename, $caller_depth)
    {
        $this->logfile = $filename;
        $this->caller_depth = $caller_depth;
        $this->num_calls = 0;

        /* Clear the log file. */
        if (!is_file($this->logfile)) {
            touch($this->logfile);
        }
        if (is_writable($this->logfile)) {
            $fp = fopen($this->logfile, "w");
            ftruncate($fp, 0);
            fclose($fp);
        } else {
            trigger_error("Cannot write to " . $this->logfile);
        }
    }

    function __destruct()
    {
        $this->writeLine($this->num_calls . " lines written to " . $this->logfile);
    }

    public function getNumCalls()
    {
        return $this->num_calls;
    }

    public function write($datum, $label = '', $maxDepth = 2)
    {
        if ($this->isOn() and is_writable($this->logfile)) {
            $backtrace = debug_backtrace();
            $calling_file = $backtrace[$this->caller_depth]['file'];
            $calling_line = $backtrace[$this->caller_depth]['line'];
            $output = "$calling_file ($calling_line): $label" . PHP_EOL .
                print_r(Debug::export($datum, $maxDepth), true)
                . PHP_EOL . PHP_EOL;
            $this->writeLine($output);
            $this->num_calls++;
        }
    }

    private function writeLine($output)
    {
        error_log($output, self::ERROR_LOG_APPEND, $this->logfile);
    }

    /**
     * @return bool True if logging is enabled.
     */
    private function isOn()
    {
        $env = getSymfonyEnvironment();
        return in_array($env, ['dev', 'test']);
    }
}

