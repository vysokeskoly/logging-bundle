<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use VysokeSkoly\LoggingBundle\Command\PerfloggableCommandInterface;

class CommandPerflogListenerTest extends TestCase
{
    /** @var MockObject|Stopwatch */
    protected $stopwatchMock;
    /** @var MockObject|LoggerInterface */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->stopwatchMock = $this->createMock(Stopwatch::class);
        $this->loggerMock = $this->createMock(Logger::class);
    }

    /**
     * @dataProvider provideCommand
     */
    public function testShouldStartStopwatchOnlyForCommandsImplementingPerfloggableInterface(
        Command $command,
        bool $shouldStart
    ): void {
        $stopwatch = new Stopwatch();
        $listener = new CommandPerflogListener($stopwatch, $this->loggerMock);

        $event = new ConsoleCommandEvent(
            $command,
            new StringInput(''),
            new NullOutput()
        );

        $listener->onConsoleCommand($event);

        $this->assertSame($shouldStart, $stopwatch->isStarted(CommandPerflogListener::STOPWATCH_NAME));
    }

    /**
     * @return array[]
     */
    public function provideCommand(): array
    {
        return [
            'Implementing PerfloggableCommandInterface' => [
                new class() extends Command implements PerfloggableCommandInterface {
                },
                true,
            ],
            'Not implementing PerfloggableCommandInterface' => [
                new class() extends Command {
                },
                false,
            ],
        ];
    }

    public function testShouldNotLogAnythingIfStopwatchNotStarted(): void
    {
        $this->stopwatchMock
            ->expects($this->once())
            ->method('isStarted')
            ->with(CommandPerflogListener::STOPWATCH_NAME)
            ->willReturn(false);

        $this->loggerMock
            ->expects($this->never())
            ->method('info');

        $listener = new CommandPerflogListener($this->stopwatchMock, $this->loggerMock);

        $event = new ConsoleTerminateEvent(
            new Command(),
            new StringInput(''),
            new NullOutput(),
            0
        );
        $listener->onConsoleTerminate($event);
    }

    public function testShouldLogExecutionTimeIfStopwatchAreStarted(): void
    {
        $this->stopwatchMock
            ->expects($this->once())
            ->method('isStarted')
            ->with(CommandPerflogListener::STOPWATCH_NAME)
            ->willReturn(true);

        $this->stopwatchMock->expects($this->once())
            ->method('stop')
            ->willReturn($this->getStopwatchEventMock(1337));

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Command execution time', ['metric' => 'foo:bar:command', 'time' => 1.337]);

        $listener = new CommandPerflogListener($this->stopwatchMock, $this->loggerMock);

        $commandMock = $this->createMock(Command::class);
        $commandMock->expects($this->once())
            ->method('getName')
            ->willReturn('foo:bar:command');

        $event = new ConsoleTerminateEvent($commandMock, new StringInput(''), new NullOutput(), 0);

        $listener->onConsoleTerminate($event);
    }

    /**
     * @return MockObject|StopwatchEvent
     */
    protected function getStopwatchEventMock(int $endTime)
    {
        $stopwatchEvent = $this->createMock(StopwatchEvent::class);

        $stopwatchEvent->expects($this->once())
            ->method('getEndTime')
            ->willReturn($endTime);

        return $stopwatchEvent;
    }
}
