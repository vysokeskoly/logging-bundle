<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class RequestListenerTest extends TestCase
{
    public function testShouldStartStopwatchOnRequest(): void
    {
        $stopwatchMock = $this->createMock(Stopwatch::class);

        $stopwatchMock->expects($this->once())
            ->method('start')
            ->with('execTime');

        $listener = new RequestListener($stopwatchMock);
        $listener->startExecutionTimeStopwatch();
    }
}
