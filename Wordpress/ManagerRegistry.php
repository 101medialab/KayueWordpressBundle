<?php

namespace Kayue\WordpressBundle\Wordpress;

use BadMethodCallException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Kayue\WordpressBundle\Doctrine\WordpressEntityManager;
use Kayue\WordpressBundle\WordpressEvents;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ManagerRegistry implements ManagerRegistryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityManagerInterface
     */
    protected $defaultEntityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected $rootDir;
    protected $environment;
    protected $currentBlogId = 1;
    protected $previousBlogId = 1;
    protected $managers = [];

    private $metadataCache;
    private $queryCache;
    private $resultCache;

    public function __construct(
        Connection $connection,
        EntityManagerInterface $defaultEntityManager,
        EventDispatcherInterface $eventDispatcher,
        $rootDir,
        $environment,
        CacheItemPoolInterface $metadataCache,
        CacheItemPoolInterface $queryCache,
        CacheItemPoolInterface $resultCache
    )
    {
        $this->connection = $connection;
        $this->defaultEntityManager = $defaultEntityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
        $this->metadataCache = $metadataCache;
        $this->queryCache = $queryCache;
        $this->resultCache = $resultCache;
    }

    /**
     * @param int $blogId
     *
     * @return WordpressEntityManager
     */
    public function getManager($blogId = null)
    {
        if ($blogId !== null && $blogId !== $this->currentBlogId) {
            $this->setCurrentBlogId($blogId);
        }

        if (!isset($this->managers[$this->currentBlogId])) {
            $config = ORMSetup::createAttributeMetadataConfiguration([], 'prod' !== $this->environment);
            $config->addEntityNamespace('KayueWordpressBundle', 'Kayue\WordpressBundle\Entity');
            $config->setAutoGenerateProxyClasses(true);
            $config->setProxyDir($this->defaultEntityManager->getConfiguration()->getProxyDir());

            $config->setMetadataCache($this->getCachePool($this->metadataCache, $this->currentBlogId));
            $config->setQueryCache($this->getCachePool($this->queryCache, $this->currentBlogId));
            $config->setResultCache($this->getCachePool($this->resultCache, $this->currentBlogId));

            $em = WordpressEntityManager::create($this->connection, $config);

            $this->eventDispatcher->dispatch(new GenericEvent($em), WordpressEvents::CREATE_ENTITY_MANAGER);

            $em->setBlogId($this->currentBlogId);

            $this->managers[$this->currentBlogId] = $em;
        }

        return $this->managers[$this->currentBlogId];
    }

    /**
     * Switches the active blog until the user calls the restorePreviousBlog() method.
     *
     * @param $blogId
     */
    public function setCurrentBlogId($blogId)
    {
        if ($this->currentBlogId === $blogId) {
            return;
        }

        $this->previousBlogId = $this->currentBlogId;
        $this->currentBlogId = $blogId;
    }

    /**
     * Switches active blog back after user calls the setCurrentBlogId() method.
     */
    public function restorePreviousBlog()
    {
        $this->setCurrentBlogId($this->previousBlogId);
    }

    protected function getCachePool(CacheItemPoolInterface $pool, int $blogId): CacheItemPoolInterface
    {
        $namespace = sprintf('wordpress_blog_%s_', $blogId);
        return new ProxyAdapter($pool, $namespace);
    }

    public function getDefaultConnectionName()
    {
        throw new BadMethodCallException();
    }

    public function getConnection($name = null)
    {
        return $this->connection;
    }

    public function getConnections()
    {
        return [$this->connection];
    }

    public function getConnectionNames()
    {
        throw new BadMethodCallException();
    }

    public function getDefaultManagerName()
    {
        throw new BadMethodCallException();
    }

    public function getManagers()
    {
        throw new BadMethodCallException();
    }

    public function resetManager($name = null)
    {
        throw new BadMethodCallException();
    }

    public function getAliasNamespace($alias)
    {
        throw new BadMethodCallException();
    }

    public function getManagerNames()
    {
        throw new BadMethodCallException();
    }

    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        throw new BadMethodCallException();
    }

    public function getManagerForClass($class)
    {
        throw new BadMethodCallException();
    }
}
