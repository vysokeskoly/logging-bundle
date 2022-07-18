<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Monolog\Logger;
use Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class DoctrineListener
{
    public function __construct(
        protected DoctrineDataCollector $doctrineDataCollector,
        protected Logger $logger,
        protected ?int $doctrineExecuteTimeThreshold,
    ) {
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if ($this->doctrineExecuteTimeThreshold === null) {
            return;
        }

        $this->doctrineDataCollector->collect($event->getRequest(), $event->getResponse());

        foreach ($this->doctrineDataCollector->getQueries() as $queries) {
            foreach ($queries as $query) {
                $timeInMilliseconds = $query['executionMS'] * 1000;
                if ($timeInMilliseconds > $this->doctrineExecuteTimeThreshold) {
                    $this->logger->warning(
                        sprintf('Doctrine query (time: %.2f ms)', $timeInMilliseconds),
                        [
                            'time' => $timeInMilliseconds,
                            'query' => $query['sql'],
                        ],
                    );
                }
            }
        }
    }
}
