<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestProcessorTest extends TestCase
{
    protected LogRecord $record;
    protected RequestProcessor $processor;

    protected function setUp(): void
    {
        $this->record = new LogRecord(
            new \DateTimeImmutable('1.1.2011'),
            'app.cz',
            Level::Error,
            'Event message',
        );

        $this->processor = new RequestProcessor();
    }

    protected function tearDown(): void
    {
        unset($this->record);
    }

    public function testShouldImplementProcessorInterface(): void
    {
        $this->assertInstanceOf(ProcessorInterface::class, $this->processor);
    }

    public function testShouldExtendRecordWithRequestAttributesAndQueryData(): void
    {
        $request = new Request();

        $attributes = ['_route' => 'homepage'];
        $request->attributes->replace($attributes);

        $data = ['foo' => 'bar'];
        $request->request->replace($data);

        $query = ['bar' => 'baz'];
        $request->query->replace($query);

        $event = $this->getResponseEvent($request);

        $this->processor->onKernelRequest($event);
        /** @var LogRecord $record */
        $record = $this->processor->__invoke($this->record);

        $this->assertEquals($attributes, $record->extra['request']['attributes']);
        $this->assertEquals($query, $record->extra['request']['query']);
        $this->assertSame([], $record->extra['request']['request']); // POST data should not be added for security reasons
    }

    public function testShouldExtendRecordWithUserAgent(): void
    {
        $server = [
            'REQUEST_URI' => 'A',
            'HTTP_USER_AGENT' => 'B',
        ];

        $request = new Request();
        $request->server->replace($server);

        $event = $this->getResponseEvent($request);

        $this->processor = new RequestProcessor();
        $this->processor->onKernelRequest($event);
        $record = $this->processor->__invoke($this->record);

        $this->assertEquals($server['HTTP_USER_AGENT'], $record->extra['ua']);
    }

    public function testShouldExtendRecordWithClientIpFromRemoteAddr(): void
    {
        $server = [
            'REQUEST_URI' => 'A',
            'REMOTE_ADDR' => '10.0.0.1',
        ];

        $request = new Request();
        $request->server->replace($server);

        $event = $this->getResponseEvent($request);

        $this->processor = new RequestProcessor();
        $this->processor->onKernelRequest($event);
        $record = $this->processor->__invoke($this->record);

        $this->assertEquals($server['REMOTE_ADDR'], $record->extra['ip']);
    }

    public function testShouldExtendRecordWithClientIpFromXForwardedFor(): void
    {
        $server = [
            'REQUEST_URI' => 'A',
            'REMOTE_ADDR' => '10.0.0.1',
        ];
        $headers = [
            'X_FORWARDED_FOR' => '1.2.3.4',
        ];

        $request = new Request();
        $request->server->replace($server);
        $request->headers->replace($headers);
        $request::setTrustedProxies(['10.0.0.0/8'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX);

        $event = $this->getResponseEvent($request);

        $this->processor = new RequestProcessor();
        $this->processor->onKernelRequest($event);
        $record = $this->processor->__invoke($this->record);

        $this->assertEquals($headers['X_FORWARDED_FOR'], $record->extra['ip']);
    }

    public function getResponseEvent(Request $request): RequestEvent
    {
        $event = $this->createMock(RequestEvent::class);

        $event->expects($this->any())
            ->method('isMainRequest')
            ->willReturn(true);

        $event->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        return $event;
    }
}
