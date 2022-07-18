<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use PHPUnit\Framework\TestCase;
use VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\Fixtures\DummyUser;
use VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\Fixtures\TestFormatter;

/**
 * @covers \VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\AbstractFormatter
 */
class AbstractFormatterTest extends TestCase
{
    protected array $record;

    protected AbstractFormatter $formatter;

    protected function setUp(): void
    {
        $this->record = [
            'message' => 'Event message',
            'context' => ['foo' => 'bar'] ,
            'channel' => 'app.cz',
            'level' => 400,
            'level_name' => 'ERROR',
            'datetime' => new \DateTime('1.1.2011'),
            'extra' => [],
        ];
        $this->formatter = new TestFormatter();
    }

    protected function tearDown(): void
    {
        unset($this->record);
    }

    public function testShouldExtendMessageWithUserInformation(): void
    {
        $user = new DummyUser('user@name.cz');
        $this->record['context']['user'] = $user;

        $message = $this->formatter->format($this->record);

        $this->assertEquals($message->getAdditional('email'), 'user@name.cz');
    }
}
