<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class DummyUser implements UserInterface
{
    public function __construct(private string $username)
    {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): string
    {
        return 'password';
    }

    public function getSalt(): string
    {
        return 'salt';
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }
}
