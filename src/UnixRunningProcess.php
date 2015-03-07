<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand;

use ptlis\ShellCommand\Exceptions\CommandExecutionException;
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;

/**
 * UNIX implementation of running process.
 *
 * @todo Use timeout!
 */
class UnixRunningProcess implements RunningProcessInterface
{
    const STDIN = 0;
    const STDOUT = 1;
    const STDERR = 2;


    /**
     * @var int (microseconds) How long to wait for a command to finish executing, -1 to wait indefinitely.
     */
    private $timeout;

    /**
     * @var int (microseconds) The amount of time to sleep for when polling for completion, defaults to 1/100 of a
     *  second.
     */
    private $pollTimeout;

    /**
     * @var float Unix timestamp with microseconds.
     */
    private $startTime;

    /**
     * @var int The exit code of the process, set once the process has exited.
     */
    private $exitCode;

    /**
     * @var array Pipes populated by proc_open.
     */
    private $pipeList = array();

    /**
     * @var resource Process resource returned by proc_open.
     */
    private $process = null;


    /**
     * Constructor.
     *
     * @throws CommandExecutionException
     *
     * @param string $command
     * @param string $cwdOverride
     * @param int $timeout
     * @param int $pollTimeout
     */
    public function __construct($command, $cwdOverride, $timeout = -1, $pollTimeout = 1000)
    {
        // Store CWD, set to override
        $prevCwd = getcwd();
        chdir($cwdOverride);

        $this->process = proc_open(
            $command,
            array(
                self::STDOUT => array('pipe', 'w'),
                self::STDERR => array('pipe', 'w')
            ),
            $this->pipeList
        );
        $this->startTime = microtime(true);

        // Reset CWD to previous
        chdir($prevCwd);

        if (!is_resource($this->process)) {
            throw new CommandExecutionException('Call to proc_open failed for unknown reason.');
        }

        $this->timeout = $timeout;
        $this->pollTimeout = $pollTimeout;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        $status = $this->getStatus();

        return $status['running'];
    }

    /**
     * {@inheritDoc}
     */
    public function wait(\Closure $callback = null)
    {
        while ($this->isRunning()) {
            $stdOut = $this->readOutput(self::STDOUT);
            $stdErr = $this->readOutput(self::STDERR);

            if (!is_null($callback)) {
                $callback($stdOut, $stdErr);
            }

            usleep($this->pollTimeout);
        }

        $stdOut = $this->readOutput(self::STDOUT);
        $stdErr = $this->readOutput(self::STDERR);

        if (!is_null($callback)) {
            $callback($stdOut, $stdErr);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop($timeout = 1000000)
    {
        $originalTime = microtime(true);
        $this->sendSignal(SIGTERM);

        while ($this->isRunning()) {
            $time = microtime(true);

            // If term hasn't succeeded by the specified timeout then try and kill
            if (($time - $originalTime) * 1000000 > $timeout) {
                $this->sendSignal(SIGKILL);
                break;
            }

            usleep($this->pollTimeout);
        }
    }

    /**
     * Send a signal to the running process.
     *
     * @param int $signal
     */
    public function sendSignal($signal)
    {
        if (true !== proc_terminate($this->process, $signal)) {
            throw new CommandExecutionException(
                'Call to proc_terminate with signal "' . $signal . '" failed for unknown reason.'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readOutput($streamId)
    {
        $data = stream_get_contents($this->pipeList[$streamId]);
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getExitCode()
    {
        if ($this->isRunning()) {
            throw new \RuntimeException('Cannot get exit code of still-running process.');
        }

        return $this->exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getPid()
    {
        $status = $this->getStatus();
        if (!$status['running']) {
            throw new \RuntimeException('Cannot get the process id of a process that has already exited.');
        }

        return $status['pid'];
    }

    /**
     * Proxy method to proc_get_status.
     *
     * This is used so that we always update the exit code when retrieving process status. This works around the issue
     * where only the last call after process termination contains the real exit code.
     *
     * See http://stackoverflow.com/a/7841550 For more information.
     *
     * @return array
     */
    private function getStatus()
    {
        $status = proc_get_status($this->process);

        if (!$status['running'] && is_null($this->exitCode)) {
            $this->exitCode = $status['exitcode'];
        }

        return $status;
    }
}
