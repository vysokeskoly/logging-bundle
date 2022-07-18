<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\EventListener;

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DoctrineListenerTest extends TestCase
{
    /** @var MockObject|Logger */
    protected Logger $logger;
    /** @var MockObject|Request */
    protected Request $request;
    /** @var MockObject|Response */
    protected Response $response;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->response = $this->createMock(Response::class);
    }

    protected function tearDown(): void
    {
        unset($this->request, $this->response);
    }

    public function testShouldNotLogQueriesOnTerminateWhenThresholdIsNotSet(): void
    {
        $doctrineDataCollector = $this->createMock(DoctrineDataCollector::class);
        $logger = $this->createMock(Logger::class);
        $doctrineDataCollector->expects($this->never())->method('collect');

        $listener = new DoctrineListener($doctrineDataCollector, $logger, null);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    public function testShouldLogQueriesOnTerminate(): void
    {
        $doctrineDataCollector = $this->createMock(DoctrineDataCollector::class);
        $logger = $this->createMock(Logger::class);
        $queries = [
            [
                [
                    'executionMS' => 0.2,
                    'sql' => 'SELECT * FROM finances',
                ],
                [
                    'executionMS' => 0.02,
                    'sql' => 'SELECT * FROM goods',
                ],
            ],
        ];

        $doctrineDataCollector->expects($this->once())
            ->method('collect')
            ->with($this->request, $this->response)
            ->willReturn(null);

        $doctrineDataCollector->expects($this->once())
            ->method('getQueries')
            ->willReturn($queries);

        $logger->expects($this->once())
            ->method('warning')
            ->with(
                'Doctrine query (time: 200.00 ms)',
                [
                    'time' => 200,
                    'query' => 'SELECT * FROM finances',
                ],
            );

        $listener = new DoctrineListener($doctrineDataCollector, $logger, 50);
        $listener->onKernelTerminate($this->getResponseEvent());
    }

    protected function getResponseEvent(): TerminateEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new TerminateEvent($kernel, $this->request, $this->response);
    }
}
