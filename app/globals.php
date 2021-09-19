<?php

/**
 * Global functions.
 */

use Rialto\Exception\AssertionFailedException;
use Rialto\Debugger;

/**
 * Returns the application environment: "prod", "stage", or "dev".
 * @return string
 */
function getSymfonyEnvironment($default = 'prod')
{
    $env = getenv('SYMFONY_ENV') ?: $default;
    return in_array($env, ['prod', 'stage', 'dev']) ? $env : $default;
}

/**
 * @param string $symfonyEnv The Symfony environment, eg "prod", "stage", "dev"
 * @return bool True if the current environment should have debug enabled.
 */
function isDevEnvironment($symfonyEnv)
{
    $debug = in_array($symfonyEnv, ['dev', 'test']);
    $debug = $debug || getenv('SYMFONY_DEBUG');
    return $debug;
}


/**
 * Log a debug message.
 */
function logDebug()
{
    $args = func_get_args();
    $filename = Debugger::getFilename();

    $dbg = Debugger::getInstance($filename);
    call_user_func_array([$dbg, 'write'], $args);
    return $args[0];
}


/**
 * Converts foreign characters in a UTF-8 string into their closest ASCII
 * counterparts.
 */
function utf8ToAscii($string)
{
    setlocale(LC_CTYPE, 'en_US.utf8');
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

/**
 * Removes non-ASCII characters from the given UTF-8 string.
 *
 * @param string $string
 * @return string
 */
function stripUtf8($string)
{
    setlocale(LC_CTYPE, 'en_US.utf8');
    return iconv('UTF-8', 'ASCII//IGNORE', $string);
}

/**
 * Wrapper around preg_match() with better error handling and return values.
 *
 * @see preg_match()
 * @return boolean True if $subject matches $pattern; false otherwise.
 * @throws UnexpectedValueException if an error occurs.
 */
function regex_match($pattern, $subject, $matches = [])
{
    $result = preg_match($pattern, $subject, $matches);
    if (false === $result) {
        $err = error_get_last();
        throw new \UnexpectedValueException($err['message']);
    }
    return (bool) $result;
}

/**
 * Check to see if two arbitrary precision numbers are equal.
 *
 * This is an extension to PHP's bcmath library:
 * @see http://php.net/manual/en/book.bc.php
 *
 * @param float $a
 * @param float $b
 * @param integer $scale
 * @return bool
 */
function bceq($a, $b, $scale = null)
{
    return 0 === bccomp($a, $b, $scale);
}

/**
 * @param string $needle
 * @param string $haystack
 * @return bool True if $needle is a substring of $haystack
 */
function is_substring($needle, $haystack)
{
    return false !== strpos($haystack, $needle);
}

/**
 * A replacement for PHP assert() that 1) never does string evals, and 2)
 * always throws an exception if $expression is false.
 *
 * @param bool $expression
 * @param string $message
 *
 * @throws AssertionFailedException if $expression does not evaluate to true
 */
function assertion($expression, $message = null)
{
    if (!$expression) {
        throw $message
            ? new AssertionFailedException($message)
            : AssertionFailedException::fromBacktrace(debug_backtrace());
    }
}
