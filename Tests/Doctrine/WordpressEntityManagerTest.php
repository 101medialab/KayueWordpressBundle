<?php

namespace Kayue\WordpressBundle\Tests\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Kayue\WordpressBundle\Doctrine\WordpressEntityManager;
use PHPUnit\Framework\TestCase;

class WordpressEntityManagerTest extends TestCase
{
    public function testExtendsEntityManagerDecorator()
    {
        $wrapped = $this->createMock(EntityManagerInterface::class);
        $em = new WordpressEntityManager($wrapped);

        $this->assertInstanceOf(EntityManagerDecorator::class, $em);
    }

    public function testDefaultBlogIdIsOne()
    {
        $wrapped = $this->createMock(EntityManagerInterface::class);
        $em = new WordpressEntityManager($wrapped);

        $this->assertEquals(1, $em->getBlogId());
    }

    public function testSetAndGetBlogId()
    {
        $wrapped = $this->createMock(EntityManagerInterface::class);
        $em = new WordpressEntityManager($wrapped);

        $em->setBlogId(5);
        $this->assertEquals(5, $em->getBlogId());
    }

    public function testDelegatesMethodsToWrappedEntityManager()
    {
        $wrapped = $this->createMock(EntityManagerInterface::class);
        $wrapped->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $em = new WordpressEntityManager($wrapped);

        $this->assertTrue($em->isOpen());
    }

    public function testDelegatesFlushToWrapped()
    {
        $wrapped = $this->createMock(EntityManagerInterface::class);
        $wrapped->expects($this->once())->method('flush');

        $em = new WordpressEntityManager($wrapped);
        $em->flush();
    }

    public function testFindBlogIdReturnsOneWhenNotRegistered(): void
    {
        $unknown = $this->createMock(EntityManagerInterface::class);

        $this->assertSame(1, WordpressEntityManager::findBlogId($unknown));
    }

    public function testFindBlogIdReturnsSetValueViaDecorator(): void
    {
        $inner = $this->createMock(EntityManagerInterface::class);
        $wpEm = new WordpressEntityManager($inner);
        $wpEm->setBlogId(5);

        $this->assertSame(5, WordpressEntityManager::findBlogId($wpEm));
    }

    public function testFindBlogIdReturnsSetValueViaInnerEm(): void
    {
        $inner = $this->createMock(EntityManagerInterface::class);
        $wpEm = new WordpressEntityManager($inner);
        $wpEm->setBlogId(7);

        $this->assertSame(7, WordpressEntityManager::findBlogId($inner));
    }

    public function testSetBlogIdLastWriteWinsForSharedInner(): void
    {
        $inner = $this->createMock(EntityManagerInterface::class);
        $first = new WordpressEntityManager($inner);
        $first->setBlogId(2);
        $second = new WordpressEntityManager($inner);
        $second->setBlogId(9);

        $this->assertSame(9, WordpressEntityManager::findBlogId($inner));
    }

    public function testConstructorDoesNotRegisterWithoutSetBlogId(): void
    {
        $inner = $this->createMock(EntityManagerInterface::class);
        new WordpressEntityManager($inner);

        $this->assertSame(1, WordpressEntityManager::findBlogId($inner));
    }

    protected function tearDown(): void
    {
        $ref = new \ReflectionProperty(WordpressEntityManager::class, 'blogIdMap');
        $ref->setValue(null, null);
    }
}
