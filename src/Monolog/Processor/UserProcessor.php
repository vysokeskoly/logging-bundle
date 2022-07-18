<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;

class UserProcessor
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(array $record): array
    {
        if (!$this->container->has('security.helper')) {
            return $record;
        }

        /** @var Security $security */
        $security = $this->container->get('security.helper');
        if (($user = $security->getUser())) {
            $record['context']['user'] = $user;
        }

        return $record;
    }
}
