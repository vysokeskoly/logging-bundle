<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use PHPUnit\Framework\TestCase;

class FacilityProcessorTest extends TestCase
{
    public function testShouldSetFacilityAsChannelInRecord(): void
    {
        $facility = 'wwwappcz';
        $processor = new FacilityProcessor('wwwappcz');
        $record = $processor([]);

        $this->assertEquals($facility, $record['channel']);
    }
}
