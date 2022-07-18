<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use VysokeSkoly\LoggingBundle\AbstractTestCase;

class FacilityProcessorTest extends AbstractTestCase
{
    private FacilityProcessor $facilityProcessor;

    protected function setUp(): void
    {
        $this->facilityProcessor = new FacilityProcessor('wwwappcz');
    }

    public function testShouldImplementProcessorInterface(): void
    {
        $this->assertInstanceOf(ProcessorInterface::class, $this->facilityProcessor);
    }

    public function testShouldSetFacilityAsChannelInRecord(): void
    {
        $facility = 'wwwappcz';
        $processor = $this->facilityProcessor;
        $record = $processor($this->emptyRecord());

        $this->assertEquals($facility, $record['channel']);
        $this->assertEquals($facility, $record->channel);
    }
}
