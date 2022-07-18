<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;

class ConfigurationTest extends TestCase
{
    public function testConfigurationDefinition(): void
    {
        $dumper = new YamlReferenceDumper();
        $reference = <<<CONFIG
            vysoke_skoly_logging:
                app_id:               ~ # Required
                perflog_threshold:    ~ # Required
                doctrine_execute_time_threshold: null
                graylog:
                    facility:             ~ # Required
                    hostname:             ~ # Required
                    port:                 12201

            CONFIG;

        $this->assertEquals($reference, $dumper->dump(new Configuration()));
    }
}
