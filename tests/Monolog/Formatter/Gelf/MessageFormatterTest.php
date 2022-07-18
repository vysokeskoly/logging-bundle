<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;
use VysokeSkoly\LoggingBundle\Fixtures\CircularReference\Foo;

/**
 * @covers \VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\MessageFormatter
 */
class MessageFormatterTest extends TestCase
{
    protected array $record;
    protected MessageFormatter $formatter;

    protected function setUp(): void
    {
        $this->record = [
            'message' => 'Event message',
            'context' => [],
            'channel' => 'app.cz',
            'level' => 400,
            'level_name' => 'ERROR',
            'datetime' => new \DateTime('1.1.2011'),
            'extra' => [],
        ];
        $this->formatter = new MessageFormatter();
    }

    protected function tearDown(): void
    {
        unset($this->record);
    }

    /**
     * @dataProvider attributesProvider
     *
     * @param mixed $value
     */
    public function testShouldExtendMessageWithRequestAttributes(string $key, $value): void
    {
        $this->record['request']['attributes'] = [$key => $value];
        $message = $this->formatter->format($this->record);
        $dumpedValue = $message->getAdditional(sprintf('attribute_%s', $key));

        $this->assertIsString($dumpedValue);

        $this->assertNull(VarDumper::dump('value'), 'Value should be null, since varDumper should be restored to `null`');
    }

    public function attributesProvider(): array
    {
        return [
            // key, value, expectedFormattedValue
            'route' => ['_route', 'homepage'],
            'int-array' => ['int-array', [1, 2]],
            'object' => ['object', new \stdClass()],
            'object in array' => ['object-in-array', [new \stdClass()]],
            'circular reference' => ['circular-reference', new Foo()],
            'circular reference in array' => ['circular-reference-in-array', [new Foo()]],
        ];
    }

    public function testShouldExtendFullMessageWithGetData(): void
    {
        $this->record['request']['query'] = ['foo' => 'bar', 'bar' => 'baz'];

        $message = $this->formatter->format($this->record);
        $expected = <<<DATA

            ------ GET ------
            array (
              'foo' => 'bar',
              'bar' => 'baz',
            )
            DATA;

        $this->assertEquals($expected, $message->getFullMessage());
    }

    public function testShouldExtendFullMessageWithException(): void
    {
        try {
            throw new NotFoundHttpException('Exception message barbaz');
        } catch (NotFoundHttpException $e) {
            $this->record['message'] = $e->getMessage();
            $this->record['context']['exception'] = $e;
        }

        $message = $this->formatter->format($this->record);
        $this->assertStringContainsString('------ Exception ------', $message->getFullMessage());
        $this->assertStringContainsString('Exception message barbaz', $message->getFullMessage());
        $this->assertStringMatchesFormat(
            '%A#0 %S VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\MessageFormatterTest->'
            . 'testShouldExtendFullMessageWithException()%A',
            $message->getFullMessage()
        );
    }

    public function testShouldExtendFullMessageWithPreviousException(): void
    {
        try {
            try {
                throw new NotFoundHttpException('Exception message barbaz');
            } catch (NotFoundHttpException $e) {
                throw new HttpException(401, 'Exception with previous exception', $e);
            }
        } catch (HttpException $e) {
            $this->record['message'] = $e->getMessage();
            $this->record['context']['exception'] = $e;
        }

        $message = $this->formatter->format($this->record);
        $this->assertStringContainsString('Exception message barbaz', $message->getFullMessage());
        $this->assertStringMatchesFormat(
            '%A------ Previous exception ------

Exception message barbaz' . "\n"
            . '#0 %S VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\MessageFormatterTest->'
            . 'testShouldExtendFullMessageWithPreviousException()%A',
            $message->getFullMessage()
        );
    }
}
