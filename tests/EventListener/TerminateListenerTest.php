<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class TerminateListenerTest extends TestCase
{
    /** @var MockObject|Stopwatch */
    protected $stopwatch;
    /** @var MockObject|Logger */
    protected $logger;

    protected function setUp(): void
    {
        $this->stopwatch = $this->createMock(Stopwatch::class);
        $this->stopwatch->expects($this->any())
            ->method('isStarted')
            ->with('execTime')
            ->willReturn(true);

        $this->logger = $this->createMock(Logger::class);
    }

    protected function tearDown(): void
    {
        unset($this->stopwatch, $this->logger);
    }

    public function testShouldStopStopwatchOnTerminate(): void
    {
        $this->stopwatch->expects($this->once())
            ->method('stop')
            ->with('execTime')
            ->willReturn($this->getStopwatchEvent());

        $listener = new TerminateListener($this->stopwatch, $this->logger, 1);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    public function testShouldNotStopStopwatchOnTerminateWhenStopwatchWasNotStarted(): void
    {
        $notStartedStopwatch = $this->createMock(Stopwatch::class);

        $notStartedStopwatch->expects($this->any())
            ->method('isStarted')
            ->with('execTime')
            ->willReturn(false);
        $notStartedStopwatch->expects($this->never())
            ->method('stop')
            ->with('execTime');

        $listener = new TerminateListener($notStartedStopwatch, $this->logger, 1);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    public function testShouldLogPerformanceOnTerminate(): void
    {
        $this->stopwatch->expects($this->any())
            ->method('stop')
            ->willReturn($this->getStopwatchEvent());

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Execution time', ['time' => 0.121]);

        $listener = new TerminateListener($this->stopwatch, $this->logger, 1);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    public function testShouldLogWarningWhenThresholdIsReached(): void
    {
        $this->stopwatch->expects($this->once())
            ->method('stop')
            ->willReturn($this->getStopwatchEvent(1121));

        $this->logger->expects($this->never())
            ->method('info');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Performance alert', ['time' => 1.121]);

        $listener = new TerminateListener($this->stopwatch, $this->logger, 1);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    public function testShouldNotLogPerformanceForProfileOnTerminate(): void
    {
        $this->stopwatch->expects($this->any())
            ->method('stop');

        $this->logger->expects($this->never())
            ->method('info');

        $listener = new TerminateListener($this->stopwatch, $this->logger, 1);
        $listener->onKernelTerminate($this->getResponseEvent(true));
    }

    protected function getStopwatchEvent(int $time = 121): StopwatchEvent
    {
        $stopwatchEvent = $this->createMock(StopwatchEvent::class);

        $stopwatchEvent->expects($this->once())
            ->method('getEndTime')
            ->willReturn($time);

        return $stopwatchEvent;
    }

    protected function getResponseEvent(bool $profilerRequest = false): TerminateEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request = $this->createMock(Request::class);

        if ($profilerRequest) {
            $request->expects($this->once())
                ->method('get')
                ->with('_route')
                ->willReturn('_wdt');
        }

        $response = $this->createMock(Response::class);

        return new TerminateEvent($kernel, $request, $response);
    }
}
