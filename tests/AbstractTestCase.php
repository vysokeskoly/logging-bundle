<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    public function emptyRecord(): LogRecord
    {
        return new LogRecord(new \DateTimeImmutable('2022-07-18 13:37:00'), 'channel', Level::Info, 'Event message');
    }
}
