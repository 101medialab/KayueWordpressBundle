<?php

namespace Kayue\WordpressBundle\Tests\Security\Http;

use Kayue\WordpressBundle\Security\Authentication\Token\WordpressToken;
use Kayue\WordpressBundle\Security\Http\WordpressCookieService;
use Kayue\WordpressBundle\Wordpress\AuthenticationCookieManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class WordpressCookieServiceTest extends TestCase
{
    private const COOKIE_NAME = 'wordpress_logged_in_abc123';

    public function testAutoLoginCookie()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);
        $cookieManager->method('validateCookie')->willReturn($user);

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));

        $request = new Request();
        $request->cookies->set(self::COOKIE_NAME, 'admin|9999999999999|hmac');

        $token = $service->autoLogin($request);

        $this->assertInstanceOf(WordpressToken::class, $token);
        $this->assertNotNull($token->getUser());
    }

    public function testAutoLoginCookieWithEmptyRequest()
    {
        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));
        $request = new Request();

        $this->assertNull($service->autoLogin($request));
    }

    public function testAutoLoginCookieWithInvalidCookie()
    {
        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);
        $cookieManager->method('validateCookie')
            ->willThrowException(new AuthenticationException('Invalid WordPress cookie.'));

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));
        $request = new Request();
        $request->cookies->set(self::COOKIE_NAME, 'something');

        $this->assertNull($service->autoLogin($request));
    }

    public function testAutoLoginCookieWithUserNotFound()
    {
        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);
        $cookieManager->method('validateCookie')
            ->willThrowException(new UserNotFoundException());

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));
        $request = new Request();
        $request->cookies->set(self::COOKIE_NAME, 'nobody|9999999999999|hmac');

        $this->assertNull($service->autoLogin($request));
    }

    public function testAutoLoginCookieWithInvalidHmac()
    {
        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);
        $cookieManager->method('validateCookie')
            ->willThrowException(new AuthenticationException('Invalid HMAC'));

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));
        $request = new Request();
        $request->cookies->set(self::COOKIE_NAME, 'admin|9999999999999|invalid');

        $this->assertNull($service->autoLogin($request));
    }

    public function testAutoLoginCookieWithExpiredCookie()
    {
        $cookieManager = $this->createMock(AuthenticationCookieManager::class);
        $cookieManager->method('getLoggedInCookieName')->willReturn(self::COOKIE_NAME);
        $cookieManager->method('validateCookie')
            ->willThrowException(new AuthenticationException('The WordPress cookie has expired.'));

        $service = new WordpressCookieService($cookieManager, $this->createMock(UserProviderInterface::class));
        $request = new Request();
        $request->cookies->set(self::COOKIE_NAME, 'admin|1|hmac');

        $this->assertNull($service->autoLogin($request));
    }
}
