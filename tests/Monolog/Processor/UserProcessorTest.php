<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use VysokeSkoly\LoggingBundle\AbstractTestCase;

class UserProcessorTest extends AbstractTestCase
{
    private UserProcessor $processor;
    /** @var ContainerInterface|MockObject */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->processor = new UserProcessor($this->container);
    }

    public function testShouldImplementProcessorInterface(): void
    {
        $this->assertInstanceOf(ProcessorInterface::class, $this->processor);
    }

    public function testShouldSetUserToRecord(): void
    {
        $user = $this->createMock(UserInterface::class);

        $security = $this->createMock(Security::class);
        $security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(true);
        $this->container->expects($this->once())
            ->method('get')
            ->with('security.helper')
            ->willReturn($security);

        $record = $this->processor->__invoke($this->emptyRecord());

        $this->assertEquals($user, $record->context['user']);
    }

    public function testShouldNotSetContextWhenTokenIsNotAvailable(): void
    {
        $security = $this->createMock(Security::class);
        $security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(true);
        $this->container->expects($this->once())
            ->method('get')
            ->with('security.helper')
            ->willReturn($security);

        $record = $this->processor->__invoke($this->emptyRecord());

        $this->assertEmpty($record->context);
    }

    public function testShouldWorkWithoutSecurityContextService(): void
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with('security.helper')
            ->willReturn(false);

        $originalRecord = $this->emptyRecord();
        $record = $this->processor->__invoke($originalRecord);

        $this->assertSame($originalRecord, $record);
    }
}
