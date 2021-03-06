<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Test\Integration;

use ptlis\ShellCommand\CommandBuilder;
use ptlis\ShellCommand\Test\ptlisShellCommandTestcase;
use ptlis\ShellCommand\UnixEnvironment;

/**
 * @covers \ptlis\ShellCommand\CommandBuilder
 */
class CommandBuilderTest extends ptlisShellCommandTestcase
{
    public function testInvalidCommand()
    {
        $this->setExpectedException(
            '\RuntimeException',
            'Invalid command "foobar" provided to ptlis\ShellCommand\CommandBuilder.'
        );

        $builder = new CommandBuilder(new UnixEnvironment());

        $builder
            ->setCommand('foobar')
            ->buildCommand();
    }
}
