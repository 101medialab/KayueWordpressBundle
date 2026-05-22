<?php

namespace Kayue\WordpressBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Kayue\WordpressBundle\Annotation\WordpressTable;
use Kayue\WordpressBundle\Doctrine\WordpressEntityManager;

class TablePrefixSubscriber
{
    protected string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();

        if (!$classMetadata->getReflectionClass()) {
            return;
        }

        $attributes = $classMetadata->getReflectionClass()->getAttributes(WordpressTable::class);

        if (empty($attributes)) {
            return;
        }

        $prefix = $this->getPrefix($classMetadata->name, $args->getEntityManager());

        $classMetadata->setPrimaryTable([
            'name' => $prefix . $classMetadata->getTableName(),
        ]);

        foreach ($classMetadata->associationMappings as &$mapping) {
            if (isset($mapping['joinTable']) && !empty($mapping['joinTable']) && strpos($mapping['joinTable']['name'], $prefix) !== 0) {
                $mapping['joinTable']['name'] = $prefix . $mapping['joinTable']['name'];
            }
        }
    }

    private function getPrefix(string $entityName, EntityManagerInterface $em): string
    {
        $prefix = $this->prefix;

        if ($entityName === 'Kayue\WordpressBundle\Entity\User' ||
            $entityName === 'Kayue\WordpressBundle\Entity\UserMeta') {
            return $this->prefix;
        }

        $wpEm = WordpressEntityManager::findWrapper($em);

        if ($wpEm !== null && $wpEm->getBlogId() > 1) {
            $prefix = $prefix . $wpEm->getBlogId() . '_';
        }

        return $prefix;
    }
}
