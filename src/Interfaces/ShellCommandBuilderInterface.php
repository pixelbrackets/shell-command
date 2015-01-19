<?php

/**
 * PHP Version 5.3
 *
 * @copyright (c) 2015 brian ridley
 * @author brian ridley <ptlis@ptlis.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ptlis\ShellCommand\Interfaces;

/**
 * Builder to create a command ready to execute.
 */
interface ShellCommandBuilderInterface
{
    /**
     * Set the binary to execute.
     *
     * @param $binary
     */
    public function setBinary($binary);

    /**
     * Add an argument to the command.
     *
     * @param string $argument
     * @param string $value
     * @param string $separator
     */
    public function addArgument($argument, $value = '', $separator = ArgumentInterface::SEPARATOR_SPACE);

    /**
     * Add a flag to the command.
     *
     * @param string $flag
     * @param string $value
     */
    public function addFlag($flag, $value = '');

    /**
     * Add a parameter to the command.
     *
     * @param string $parameter
     */
    public function addParameter($parameter);

    /**
     * Add an ad-hoc argument, useful for non-standard and old commands.
     *
     * @param string $argument
     */
    public function addAdHoc($argument);

    /**
     * Gets the built command & resets the builder.
     *
     * @return ShellCommandInterface
     */
    public function getCommand();
}
