<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class RequestListener implements EventSubscriberInterface
{
    public function __construct(protected Stopwatch $stopwatch)
    {
    }

    /**
     * Starts request execution time
     */
    public function startExecutionTimeStopwatch(): void
    {
        $this->stopwatch->start('execTime');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => [
                ['startExecutionTimeStopwatch', 255],
            ],
        ];
    }
}
