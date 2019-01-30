<?php

namespace TreeHouse\WorkerBundle\Tests\DependencyInjection;

use Pheanstalk\Pheanstalk;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Tests\Logger;
use TreeHouse\WorkerBundle\DependencyInjection\TreeHouseWorkerExtension;
use TreeHouse\WorkerBundle\TreeHouseWorkerBundle;

class TreeHouseWorkerExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;
    /**
     * @var TreeHouseWorkerExtension
     */
    private $extension;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $definition = new Definition(EventDispatcher::class, []);
        $this->container->setDefinition('event_dispatcher', $definition);

        $definition = new Definition(Logger::class, []);
        $this->container->setDefinition('logger', $definition);

        $this->extension = new TreeHouseWorkerExtension();

        $bundle = new TreeHouseWorkerBundle();
        $bundle->build($this->container);
    }

    public function testExistingPheanstalkService()
    {
        $id         = 'pheanstalk.default';
        $definition = new Definition(Pheanstalk::class, []);
        $this->container->setDefinition($id, $definition);

        $config = [
            'tree_house.worker' => [
                'pheanstalk' => $id,
            ],
        ];

        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('tree_house.worker.queue_manager'));

        $definition = $this->container->getDefinition('tree_house.worker.queue_manager');
        $injected = $definition->getArgument(0);

        if ($injected instanceof Definition) {
            $this->assertEquals(Pheanstalk::class, $injected->getClass());
        } else {
            $this->assertEquals($id, (string) $injected);
        }
    }

    public function testNewPheanstalkService()
    {
        $server  = 'localhost';
        $port    = 1234;
        $timeout = 5678;

        $config = [
            'tree_house.worker' => [
                'queue' => [
                    'server'  => $server,
                    'port'    => $port,
                    'timeout' => $timeout,
                ],
            ],
        ];

        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('tree_house.worker.queue_manager'));

        $definition = $this->container->getDefinition('tree_house.worker.queue_manager');

        /** @var Definition $pheanstalk */
        $pheanstalk = $definition->getArgument(0);

        $this->assertEquals(Pheanstalk::class, $pheanstalk->getClass());
        $this->assertEquals([$server, $port, $timeout], $pheanstalk->getArguments());
    }

    /**
     * @dataProvider getInvalidConfigurations
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMissingPheanstalk(array $config)
    {
        $this->extension->load($config, $this->container);
        $this->container->compile();
    }

    public function getInvalidConfigurations()
    {
        return [
            [
                ['tree_house.worker'],
            ],
            [
                ['tree_house.worker' => ['queue' => []]],
            ],
            [
                ['tree_house.worker' => ['queue' => ['server' => 'localhost', 'port' => '']]],
            ],
            [
                ['tree_house.worker' => ['queue' => ['server' => 'localhost', 'timeout' => '']]],
            ],
        ];
    }

    public function testDefaultTtr()
    {
        $config = [
            'tree_house.worker' => [
                'queue' => [
                    'server'  => 'localhost',
                    'port'    => 1234,
                    'timeout' => 60,
                ],
                'queue_manager' => [
                    'default_ttr'  => 600,
                ],
            ],
        ];

        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('tree_house.worker.queue_manager'));

        $definition = $this->container->getDefinition('tree_house.worker.queue_manager');
        $methodCalls = $definition->getMethodCalls();

        $this->assertContains(['setDefaultTtr', [600]], $methodCalls);
    }

    public function testDefaultDefaultTtrIs1200()
    {
        $config = [
            'tree_house.worker' => [
                'queue' => [
                    'server'  => 'localhost',
                    'port'    => 1234,
                    'timeout' => 60,
                ]
            ],
        ];

        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('tree_house.worker.queue_manager'));

        $definition = $this->container->getDefinition('tree_house.worker.queue_manager');
        $methodCalls = $definition->getMethodCalls();

        $this->assertContains(['setDefaultTtr', [1200]], $methodCalls);
    }
}
