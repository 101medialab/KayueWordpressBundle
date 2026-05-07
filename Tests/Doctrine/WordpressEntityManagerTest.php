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
}
