<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use PHPUnit\Framework\TestCase;

/**
 * @covers \VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\BusinesslogFormatter
 */
class BusinesslogFormatterTest extends TestCase
{
    protected array $record;

    protected BusinesslogFormatter $formatter;

    protected function setUp(): void
    {
        $this->record = [
            'message' => 'metricname',
            'context' => ['foo' => 'bar'] ,
            'channel' => 'app.cz',
            'level' => 400,
            'level_name' => 'ERROR',
            'datetime' => new \DateTime('1.1.2011'),
            'extra' => [],
        ];
        $this->formatter = new BusinesslogFormatter();
    }

    protected function tearDown(): void
    {
        unset($this->record);
    }

    public function testShouldUseCommonMessageAndPlaceMetricNameIntoAdditionalAndFullMessage(): void
    {
        $message = $this->formatter->format($this->record);

        $this->assertEquals($message->getShortMessage(), 'Business log');
        $this->assertEquals($message->getFullMessage(), 'metricname');
        $this->assertEquals($message->getAdditional('metric'), 'metricname');
    }
}
