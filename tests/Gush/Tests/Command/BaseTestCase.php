<?php

/**
 * This file is part of Gush.
 *
 * (c) Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Tests\Command;

use Gush\Tester\Adapter\TestAdapter;
use Gush\Config;
use Gush\Tests\TestableApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Gush\Event\CommandEvent;
use Gush\Event\GushEvents;
use Symfony\Component\Console\Input\InputAwareInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestAdapter
     */
    protected $adapter;

    /**
     * @var Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = $this->getMock('Gush\Config');
        $this->adapter = $this->buildAdapter();
    }

    /**
     * @param  Command       $command
     * @return CommandTester
     */
    protected function getCommandTester(Command $command)
    {
        $application = new TestableApplication();
        $application->setAutoExit(false);
        $application->setConfig($this->config);
        $application->setAdapter($this->adapter);
        $application->setVersionEyeClient($this->buildVersionEyeClient());

        $command->setApplication($application);

        $application->getDispatcher()->dispatch(
            GushEvents::DECORATE_DEFINITION,
            new CommandEvent($command)
        );

        $application->getDispatcher()->addListener(GushEvents::INITIALIZE, function ($event) {
            $command = $event->getCommand();
            $input = $event->getInput();

            foreach ($command->getHelperSet() as $helper) {
                if ($helper instanceof InputAwareInterface) {
                    $helper->setInput($input);
                }
            }
        });

        return new CommandTester($command);
    }

    /**
     * @return TestAdapter
     */
    protected function buildAdapter()
    {
        return new TestAdapter($this->config, 'gushphp', 'gush');
    }

    protected function buildVersionEyeClient()
    {
        $client = new \Guzzle\Http\Client();
        $client->setBaseUrl('https://www-versioneye-com-'.getenv('RUNSCOPE_BUCKET').'.runscope.net/');
        $client->setDefaultOption('query', ['api_key' => getenv('VERSIONEYE_TOKEN')]);

        return $client;
    }
}
