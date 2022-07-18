<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @phpstan-import-type Record from \Monolog\Logger
 */
class RequestProcessorTest extends TestCase
{
    /** @phpstan-var Record */
    protected array $record;

    protected RequestProcessor $processor;

    protected function setUp(): void
    {
        $this->record = [
            'message' => 'Event message',
            'context' => [],
            'channel' => 'app.cz',
            'level' => 400,
            'level_name' => 'ERROR',
            'datetime' => new \DateTimeImmutable('1.1.2011'),
            'extra' => [],
        ];
    }

    protected function tearDown(): void
    {
        unset($this->record);
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

        $processor = new RequestProcessor();
        $processor->onKernelRequest($event);
        /** @var array $record */
        $record = $processor($this->record);

        $this->assertEquals($attributes, $record['request']['attributes']);
        $this->assertEquals($query, $record['request']['query']);
        $this->assertSame([], $record['request']['request']); // POST data should not be added for security reasons
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

        $processor = new RequestProcessor();
        $processor->onKernelRequest($event);
        $record = $processor($this->record);

        $this->assertEquals($server['HTTP_USER_AGENT'], $record['extra']['ua']);
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

        $processor = new RequestProcessor();
        $processor->onKernelRequest($event);
        $record = $processor($this->record);

        $this->assertEquals($server['REMOTE_ADDR'], $record['extra']['ip']);
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
        $request::setTrustedProxies(['10.0.0.0/8'], Request::HEADER_X_FORWARDED_ALL);

        $event = $this->getResponseEvent($request);

        $processor = new RequestProcessor();
        $processor->onKernelRequest($event);
        $record = $processor($this->record);

        $this->assertEquals($headers['X_FORWARDED_FOR'], $record['extra']['ip']);
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
