<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Stopwatch\Stopwatch;

class TerminateListener
{
    /** Name or routes associated with Symfony profiler */
    protected const PROFILER_ROUTES = [
        '_profiler',
        '_wdt',
    ];

    public function __construct(
        protected Stopwatch $stopwatch,
        protected Logger $logger,
        protected float $perflogThreshold,
    ) {
    }

    /**
     * Stop timer of execution time and log results
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$this->stopwatch->isStarted('execTime')) {
            return;
        }

        $result = $this->stopwatch->stop('execTime');

        // Filter out profiler requests
        if (!in_array($event->getRequest()->get('_route'), self::PROFILER_ROUTES, true)) {
            $time = $result->getEndTime() / 1000;
            if ($time > $this->perflogThreshold) {
                $this->logger->warning('Performance alert', ['time' => $time]);
            } else {
                $this->logger->info('Execution time', ['time' => $time]);
            }
        }
    }
}
