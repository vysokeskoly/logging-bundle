<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\Fixtures\DummyUser;
use VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\Fixtures\TestFormatter;

/**
 * @covers \VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\AbstractFormatter
 */
class AbstractFormatterTest extends TestCase
{
    protected LogRecord $record;
    protected AbstractFormatter $formatter;

    protected function setUp(): void
    {
        $this->record = new LogRecord(
            new \DateTimeImmutable('1.1.2011'),
            'app.cz',
            Level::Error,
            'Event message',
            ['foo' => 'bar'],
        );
        $this->formatter = new TestFormatter();
    }

    protected function tearDown(): void
    {
        unset($this->record);
    }

    public function testShouldExtendMessageWithUserInformation(): void
    {
        $user = new DummyUser('user@name.cz');
        $this->record = $this->record->with(context: ['user' => $user]);

        $message = $this->formatter->format($this->record);

        $this->assertEquals($message->getAdditional('email'), 'user@name.cz');
    }
}
