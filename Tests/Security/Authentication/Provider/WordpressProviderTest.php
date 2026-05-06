<?php

namespace Kayue\WordpressBundle\Tests\Security\Authentication\Provider;

use Kayue\WordpressBundle\Security\Authentication\Provider\WordpressProvider;
use Kayue\WordpressBundle\Security\Authentication\Token\WordpressToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class WordpressProviderTest extends TestCase
{
    public function testAuthenticateWithAnonymousToken()
    {
        $userCheckerMock = $this->createMock(UserCheckerInterface::class);
        $tokenMock = $this->createMock(TokenInterface::class);
        $wordpressProvider = new WordpressProvider($userCheckerMock);

        $this->assertNull($wordpressProvider->authenticate($tokenMock));
    }

    public function testAuthenticateWithWordpressToken()
    {
        $userCheckerMock = $this->createMock(UserCheckerInterface::class);
        $userMock = $this->createMock(\Kayue\WordpressBundle\Entity\User::class);
        $userMock->expects($this->any())->method('getRoles')->will($this->returnValue(array('WP_SUBSCRIBER')));
        $tokenMock = $this->getMockBuilder(WordpressToken::class)
            ->setConstructorArgs([$userMock, []])
            ->onlyMethods(['getUser'])
            ->getMock();
        $tokenMock->expects($this->any())->method('getUser')->will($this->returnValue($userMock));

        $wordpressProvider = new WordpressProvider($userCheckerMock);

        $this->assertTrue($wordpressProvider->authenticate($tokenMock)->isAuthenticated());
    }
}
