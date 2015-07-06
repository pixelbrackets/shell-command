<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\RunningProcess;

use Psr\Log\LogLevel;
use ptlis\ShellCommand\Interfaces\RunningProcessInterface;
use ptlis\ShellCommand\Logger\SignalSentLogger;
use ptlis\ShellCommand\Test\Logger\MockPsrLogger;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;
use ptlis\ShellCommand\UnixRunningProcess;

class UnixRunningProcessTest extends ptlisShellCommandTestcase
{
    public function testRunProcess()
    {
        $command = './tests/commands/unix/test_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());
        $process->wait();

        $this->assertEquals(
            false,
            $process->isRunning()
        );

        $this->assertEquals(
            './tests/commands/unix/test_binary',
            $process->getCommand()
        );
    }

    public function testWaitWithClosure()
    {
        $command = './tests/commands/unix/test_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());
        $process->wait(function($stdOut, $stdErr) {

        });

        $this->assertEquals(
            false,
            $process->isRunning()
        );
    }

    public function testHandleCommandError()
    {
        $command = './tests/commands/unix/error_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());

        $fullStdOut = '';
        $fullStdErr = '';
        $process->wait(function($stdOut, $stdErr) use (&$fullStdOut, &$fullStdErr) {
            $fullStdOut .= $stdOut;
            $fullStdErr .= $stdErr;
        });

        $this->assertEquals(
            5,
            $process->getExitCode()
        );

        $this->assertEquals(
            'Fatal Error' . PHP_EOL,
            $fullStdErr
        );
    }

    public function testErrorGetExitCodeWhileRunning()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Cannot get exit code of still-running process.'
        );

        $command = './tests/commands/unix/sleep_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());
        $process->getExitCode();
    }

    public function testGetPid()
    {
        $command = './tests/commands/unix/sleep_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());

        $this->assertNotNull(
            $process->getPid()
        );
    }

    public function testErrorGetPidNotRunning()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Cannot get the process id of a process that has already exited.'
        );

        $command = './tests/commands/unix/test_binary';

        $process = new UnixRunningProcess(new UnixEnvironment(), $command, getcwd());
        $process->wait();

        $process->getPid();
    }

    public function testStopRunning()
    {
        $command = './tests/commands/unix/sleep_binary';

        $logger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            new UnixEnvironment(),
            $command,
            getcwd(),
            -1,
            1000,
            new SignalSentLogger($logger)
        );

        $process->stop();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => RunningProcessInterface::SIGTERM
                    )
                )
            ),
            $logger->getLogs()
        );
    }

    public function testTimeoutLongRunning()
    {
        $command = './tests/commands/unix/long_sleep_binary';

        $logger = new MockPsrLogger();

        $process = new UnixRunningProcess(
            new UnixEnvironment(),
            $command,
            getcwd(),
            500000,
            1000,
            new SignalSentLogger($logger)
        );

        $process->wait();

        $this->assertLogsMatch(
            array(
                array(
                    'level' => LogLevel::DEBUG,
                    'message' => 'Signal sent',
                    'context' => array(
                        'signal' => RunningProcessInterface::SIGTERM
                    )
                )
            ),
            $logger->getLogs()
        );
    }
}
