<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;

class UserProcessor implements ProcessorInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (!$this->container->has('security.helper')) {
            return $record;
        }

        /** @var Security $security */
        $security = $this->container->get('security.helper');
        if (($user = $security->getUser())) {
            return $record->with(context: ['user' => $user]);
        }

        return $record;
    }
}
