<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class RequestListener implements EventSubscriberInterface
{
    protected Stopwatch $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
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
