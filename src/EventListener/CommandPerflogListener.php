<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use VysokeSkoly\LoggingBundle\Command\PerfloggableCommandInterface;

/**
 * Log execution time of commands implementing PerfloggableCommandInterface
 */
class CommandPerflogListener
{
    public const STOPWATCH_NAME = 'commandExecTime';

    public function __construct(protected Stopwatch $stopwatch, protected LoggerInterface $logger)
    {
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand() instanceof PerfloggableCommandInterface) {
            $this->stopwatch->start(self::STOPWATCH_NAME);
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        if (!$this->stopwatch->isStarted(self::STOPWATCH_NAME)) {
            return;
        }

        $result = $this->stopwatch->stop(self::STOPWATCH_NAME);

        $time = $result->getEndTime() / 1000;

        $this->logger->info(
            'Command execution time',
            [
                'time' => $time,
                'metric' => ($command = $event->getCommand())
                    ? $command->getName() ?? 'unknown'
                    : 'unknown',
            ],
        );
    }
}
