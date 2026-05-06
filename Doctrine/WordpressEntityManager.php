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
    protected int $blogId = 1;

    public function __construct(EntityManagerInterface $wrapped)
    {
        parent::__construct($wrapped);
    }

    public function setBlogId(int $blogId): void
    {
        $this->blogId = $blogId;
    }

    public function getBlogId(): int
    {
        return $this->blogId;
    }

    public static function create(Connection $conn, Configuration $config): self
    {
        $em = EntityManager::create($conn, $config, $conn->getEventManager());
        return new self($em);
    }

    public function getRepository($entityName): EntityRepository
    {
        if (strpos($entityName, 'KayueWordpressBundle:') !== 0) {
            $entityName = 'KayueWordpressBundle:' . $entityName;
        }

        return parent::getRepository($entityName);
    }
}
