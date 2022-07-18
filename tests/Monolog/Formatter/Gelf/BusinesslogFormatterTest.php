<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\BusinesslogFormatter
 */
class BusinesslogFormatterTest extends TestCase
{
    protected LogRecord $record;
    protected BusinesslogFormatter $formatter;

    protected function setUp(): void
    {
        $this->record = new LogRecord(
            new \DateTimeImmutable('1.1.2011'),
            'app.cz',
            Level::Error,
            'metricname',
            ['foo' => 'bar'],
        );
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
