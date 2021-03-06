<?php

/**
 * @copyright (c) 2015-2018 brian ridley
 * @author      brian ridley <ptlis@ptlis.net>
 * @license     http://opensource.org/licenses/MIT MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ptlis\ShellCommand\Test;


class ptlisShellCommandTestcase extends \PHPUnit_Framework_TestCase
{
    public function assertLogsMatch(array $expectedLogList, array $actualLogList)
    {
        $this->assertEquals(true, count($expectedLogList) == count($actualLogList), 'Log list lengths do not match.');

        foreach ($expectedLogList as $index => $expectedLog) {

            $hasMatch = false;

            foreach ($actualLogList as $actualLog) {
                if ($expectedLog == $actualLog) {
                    $hasMatch = true;
                }
            }

            $this->assertEquals(true, $hasMatch, 'Element with index ' . $index . ' not found');
        }
    }
}
