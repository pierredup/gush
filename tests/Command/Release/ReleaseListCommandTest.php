<?php

/*
 * This file is part of Gush package.
 *
 * (c) Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Tests\Command\Release;

use Gush\Command\Release\ReleaseListCommand;
use Gush\Tests\Command\CommandTestCase;

class ReleaseListCommandTest extends CommandTestCase
{
    public function testListsReleases()
    {
        $tester = $this->getCommandTester(new ReleaseListCommand());
        $tester->execute();

        $display = $tester->getDisplay();

        $this->assertTableOutputMatches(
            ['ID', 'Name', 'Tag', 'Draft', 'Prerelease', 'Created', 'Published'],
            [
                ['1', 'v1.0.0', 'v1.0.0', 'no', 'no', '2014-01-05 10:00', '2014-01-05 10:00'],
            ],
            $display
        );
    }
}
