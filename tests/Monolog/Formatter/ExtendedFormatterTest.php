<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExtendedFormatterTest extends TestCase
{
    protected ExtendedFormatter $formatter;
    protected LogRecord $record;

    protected function setUp(): void
    {
        $this->formatter = new ExtendedFormatter();
        $this->record = new LogRecord(
            new \DateTimeImmutable('1.1.2011'),
            'app.cz',
            Level::Info,
            'Error: Newbie in da house',
            extra: [
                'url' => 'http://www.w3.org/',
                'ip' => '82.113.38.98',
                'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) '
                    . 'Chrome/37.0.2062.122 Safari/537.36',
            ],
        );
    }

    public function testShouldFormatBaseOutput(): void
    {
        $output = $this->formatter->format($this->record);

        $this->assertStringContainsString('2011-01-01 00:00:00', $output);
        $this->assertStringContainsString('Error: Newbie in da house', $output);
        $this->assertStringContainsString('URI: http://www.w3.org/', $output);
        $this->assertStringContainsString('IP: 82.113.38.98', $output);
        $this->assertStringContainsString(
            'UA: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 '
            . '(KHTML, like Gecko) Chrome/37.0.2062.122 Safari/537.36',
            $output,
        );
    }

    public function testShouldFormatOutputWithParameters(): void
    {
        $this->record->extra['request'] = [
            'query' => ['foo' => 'bar'],
            'request' => ['bar' => 'baz'],
        ];

        $output = $this->formatter->format($this->record);

        $this->assertMatchesRegularExpression('/GET: .*array\(1\)/', $output);
        $this->assertStringContainsString('string(3) "bar"', $output);
        $this->assertStringNotContainsString('baz', $output); // POST data should not be added for security reasons
    }

    public function testShouldFormatUsername(): void
    {
        $user = new class() {
            public function getUsername(): string
            {
                return 'Jon Snow';
            }
        };

        $this->record = $this->record->with(context: ['user' => $user]);

        $output = $this->formatter->format($this->record);

        $this->assertStringContainsString('Email: Jon Snow', $output);
    }

    public function testShouldFormatUserIdentifier(): void
    {
        $user = new class() {
            public function getUserIdentifier(): string
            {
                return 'Jon Snow';
            }
        };

        $this->record = $this->record->with(context: ['user' => $user]);

        $output = $this->formatter->format($this->record);

        $this->assertStringContainsString('Email: Jon Snow', $output);
    }

    public function testShouldFormatOutputWithException(): void
    {
        try {
            throw new NotFoundHttpException('Exception message barbaz', null, 1337);
        } catch (NotFoundHttpException $e) {
            $this->record = $this->record->with(message: $e->getMessage());
            $this->record = $this->record->with(context: ['exception' => $e]);
        }

        $output = $this->formatter->format($this->record);
        $this->assertStringContainsString('Exception: Exception message barbaz (1337)', $output);
        $this->assertStringContainsString(
            'VysokeSkoly\LoggingBundle\Monolog\Formatter\ExtendedFormatterTest->testShouldFormatOutputWithException()',
            $output,
        );
    }

    public function testShouldFormatOutputWithPreviousException(): void
    {
        try {
            try {
                throw new NotFoundHttpException('Exception message barbaz', null, 1337);
            } catch (NotFoundHttpException $e) {
                throw new HttpException(401, 'Exception with previous exception', $e);
            }
        } catch (HttpException $e) {
            $this->record = $this->record->with(message: $e->getMessage());
            $this->record = $this->record->with(context: ['exception' => $e]);
        }

        $output = $this->formatter->format($this->record);

        $this->assertStringContainsString(
            'Previous Exception' . "\n"
            . 'Exception message barbaz',
            $output,
        );
        $this->assertStringContainsString(
            'VysokeSkoly\LoggingBundle\Monolog\Formatter\ExtendedFormatterTest->testShouldFormatOutputWithPreviousException()',
            $output,
        );
    }
}
