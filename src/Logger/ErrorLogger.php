<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logs error data from the process (stderr and exit code if not 0).
 */
final class ErrorLogger extends NullProcessObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logLevel;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(LoggerInterface $logger, $logLevel = LogLevel::ERROR)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * Create a log entry, utility method for derived classes.
     *
     * @param string $message
     * @param array $context
     */
    protected function log($message, array $context = [])
    {
        $this->logger->log($this->logLevel, $message, $context);
    }

    /**
     * The contents of the stderr buffer have been read.
     *
     * @param string $stdErr
     */
    public function stdErrRead($stdErr)
    {
        $this->log('Read from stderr', ['stderr' => $stdErr]);
    }

    /**
     * Process has completed and the exit code is available.
     *
     * @param int $exitCode
     */
    public function processExited($exitCode)
    {
        $this->log('Process exited', ['exit_code' => $exitCode]);
    }
}
