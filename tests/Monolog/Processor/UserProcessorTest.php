<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProcessorTest extends TestCase
{
    public function testShouldSetUserToRecord(): void
    {
        $user = $this->createMock(UserInterface::class);

        $security = $this->createMock(Security::class);
        $security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('security.helper')
            ->willReturn($security);

        $processor = new UserProcessor($container);
        $record = $processor([]);

        $this->assertEquals($user, $record['context']['user']);
    }

    public function testShouldNotSetContextWhenTokenIsNotAvailable(): void
    {
        $security = $this->createMock(Security::class);
        $security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('security.helper')
            ->willReturn($security);

        $processor = new UserProcessor($container);
        $record = $processor([]);

        $this->assertArrayNotHasKey('context', $record);
    }

    public function testShouldWorkWithoutSecurityContextService(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(false);

        $processor = new UserProcessor($container);
        $record = $processor([]);

        $this->assertEquals([], $record);
    }
}
