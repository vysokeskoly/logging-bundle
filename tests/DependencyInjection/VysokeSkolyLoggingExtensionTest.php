<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VysokeSkolyLoggingExtensionTest extends TestCase
{
    private ContainerBuilder $containerBuilder;
    private VysokeSkolyLoggingExtension $extension;

    /** @var array Bundle configuration */
    private array $config;

    protected function setUp(): void
    {
        $this->config = [
            'app_id' => 'appcz',
            'graylog' => [
                'hostname' => 'log01',
                'facility' => 'app.cz',
            ],
            'perflog_threshold' => 1,
        ];

        $this->loadExtension([$this->config]);
    }

    private function loadExtension(array $configs): void
    {
        $this->extension = new VysokeSkolyLoggingExtension();
        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->registerExtension($this->extension);

        $this->extension->load($configs, $this->containerBuilder);
    }

    public function testShouldRegisterParameters(): void
    {
        $registeredParameters = [
            'app_id',
            'graylog_hostname',
            'graylog_facility',
            'doctrine_execute_time_threshold',
        ];

        foreach ($registeredParameters as $params) {
            $this->assertTrue($this->containerBuilder->hasParameter('vysokeskoly_logging.' . $params));
        }
    }

    public function testShouldRegisterServices(): void
    {
        $registeredServices = [
            'formatter.extended',
            'formatter.gelf.message',
            'processors.web',
            'processors.facility',
            'processors.user',
            'handler.gelf',
        ];

        foreach ($registeredServices as $service) {
            $this->assertTrue($this->containerBuilder->hasDefinition('vysokeskoly.monolog.' . $service));
        }
    }
}
