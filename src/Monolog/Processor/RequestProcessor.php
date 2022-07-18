<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\Processor\WebProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Injects request-base data retrieved from Symfony's Request service to the log record
 * and user agent and remote IP of the current web request to extra the log record
 */
class RequestProcessor extends WebProcessor
{
    protected Request $request;
    protected ?string $clientIp = null;

    public function __construct()
    {
        // Pass an empty array as the default null value would access $_SERVER and omit IP
        parent::__construct([], [
            'url' => 'url',
            'http_method' => 'http_method',
            'server' => 'server',
            'referrer' => 'referrer',
        ]);
    }

    public function __invoke(array $record): array
    {
        $record = parent::__invoke($record);

        // Symfony added attributes, GET query string
        foreach (['attributes', 'query'] as $key) {
            if (!empty($this->request->{$key}) && $this->request->{$key}->count()) {
                $record['request'][$key] = $this->request->{$key}->all();
            }
        }

        $record['request']['request'] = []; // POST is no longer added to record, empty array is used instead to keep BC

        $record['extra'] = array_merge(
            $record['extra'],
            [
                'ua' => $this->serverData['HTTP_USER_AGENT'] ?? null,
                'ip' => $this->clientIp,
            ]
        );

        return $record;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->request = $event->getRequest();
            $this->serverData = $this->request->server->all();
            $this->clientIp = $event->getRequest()->getClientIp();
        }
    }
}
