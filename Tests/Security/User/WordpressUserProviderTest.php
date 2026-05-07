<?php

namespace Kayue\WordpressBundle\Tests\Security\User;

use Kayue\WordpressBundle\Entity\User;
use Kayue\WordpressBundle\Security\User\WordpressUserProvider;
use Kayue\WordpressBundle\Wordpress\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class WordpressUserProviderTest extends TestCase
{
    public function testImplementsUserProviderInterface()
    {
        $provider = new WordpressUserProvider($this->createMock(ManagerRegistry::class));

        $this->assertInstanceOf(UserProviderInterface::class, $provider);
    }

    public function testLoadUserByIdentifierReturnsUser()
    {
        $user = $this->createMock(User::class);

        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'admin'])
            ->willReturn($user);

        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($em);

        $provider = new WordpressUserProvider($registry);

        $this->assertSame($user, $provider->loadUserByIdentifier('admin'));
    }

    public function testLoadUserByIdentifierThrowsOnNotFound()
    {
        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($em);

        $provider = new WordpressUserProvider($registry);

        $this->expectException(UserNotFoundException::class);
        $provider->loadUserByIdentifier('nonexistent');
    }

    public function testRefreshUserReloadsFromRepository()
    {
        $user = $this->createMock(User::class);
        $user->method('getUserIdentifier')->willReturn('admin');

        $refreshedUser = $this->createMock(User::class);

        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => 'admin'])
            ->willReturn($refreshedUser);

        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($em);

        $provider = new WordpressUserProvider($registry);

        $this->assertSame($refreshedUser, $provider->refreshUser($user));
    }

    public function testSupportsWordpressUserClass()
    {
        $provider = new WordpressUserProvider($this->createMock(ManagerRegistry::class));

        $this->assertTrue($provider->supportsClass('Kayue\WordpressBundle\Entity\User'));
        $this->assertFalse($provider->supportsClass('App\Entity\User'));
    }
}
