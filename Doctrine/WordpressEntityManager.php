<?php

namespace Kayue\WordpressBundle\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class WordpressEntityManager extends EntityManagerDecorator
{
    private static ?\WeakMap $blogIdMap = null;

    protected int $blogId = 1;

    public function setBlogId(int $blogId): void
    {
        $this->blogId = $blogId;

        self::$blogIdMap ??= new \WeakMap();
        self::$blogIdMap[$this->wrapped] = $blogId;
    }

    public function getBlogId(): int
    {
        return $this->blogId;
    }

    public static function findBlogId(EntityManagerInterface $em): int
    {
        if ($em instanceof self) {
            return $em->getBlogId();
        }

        if (self::$blogIdMap !== null && isset(self::$blogIdMap[$em])) {
            return self::$blogIdMap[$em];
        }

        return 1;
    }

    public static function create(Connection $conn, Configuration $config): self
    {
        $em = EntityManager::create($conn, $config, $conn->getEventManager());

        return new self($em);
    }

    public function getRepository($entityName): EntityRepository
    {
        if (str_starts_with($entityName, 'KayueWordpressBundle:')) {
            $entityName = 'Kayue\\WordpressBundle\\Entity\\' . substr($entityName, strlen('KayueWordpressBundle:'));
        }

        return parent::getRepository($entityName);
    }
}
