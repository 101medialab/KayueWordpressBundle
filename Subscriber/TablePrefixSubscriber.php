<?php

namespace Kayue\WordpressBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Kayue\WordpressBundle\Annotation\WordpressTable;

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

    private function getPrefix(string $entityName, $em): string
    {
        $prefix = $this->prefix;

        if ($entityName === 'Kayue\WordpressBundle\Entity\User' ||
            $entityName === 'Kayue\WordpressBundle\Entity\UserMeta') {
            return $this->prefix;
        }

        if (method_exists($em, 'getBlogId')) {
            $blogId = $em->getBlogId();

            if ($blogId > 1) {
                $prefix = $prefix . $blogId . '_';
            }
        }

        return $prefix;
    }
}
