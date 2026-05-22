<?php

namespace Kayue\WordpressBundle\Tests\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Kayue\WordpressBundle\Doctrine\WordpressEntityManager;
use Kayue\WordpressBundle\Subscriber\TablePrefixSubscriber;
use PHPUnit\Framework\TestCase;

class TablePrefixSubscriberTest extends TestCase
{
    private function createInitializedMetadata(string $className): ClassMetadata
    {
        $metadata = new ClassMetadata($className);
        $metadata->initializeReflection(new RuntimeReflectionService());

        return $metadata;
    }

    public function testLoadClassMetadataWithWordpressBundleEntity()
    {
        $subscriber = new TablePrefixSubscriber('wp_');
        $em = $this->getEntityManagerMock();
        $metadataInfo = $this->createInitializedMetadata('Kayue\WordpressBundle\Tests\Fixture\Sample');
        $args = new LoadClassMetadataEventArgs($metadataInfo, $em);

        $subscriber->loadClassMetadata($args);

        $this->assertStringStartsWith('wp_', $metadataInfo->getTableName());
    }

    public function testLoadClassMetadataWithOtherEntity()
    {
        $subscriber = new TablePrefixSubscriber('wp_');
        $em = $this->getEntityManagerMock();
        $metadataInfo = $this->createInitializedMetadata('Kayue\WordpressBundle\Tests\Fixture\SampleWithoutAnnotation');
        $originalTable = $metadataInfo->getTableName();
        $args = new LoadClassMetadataEventArgs($metadataInfo, $em);

        $subscriber->loadClassMetadata($args);

        $this->assertEquals($originalTable, $metadataInfo->getTableName());
    }

    public function testPrefixWithNormalEntityManager()
    {
        $subscriber = new TablePrefixSubscriber('other_');
        $em = $this->getEntityManagerMock();

        $metadataInfo = $this->createInitializedMetadata('Kayue\WordpressBundle\Tests\Fixture\Sample');
        $metadataInfo->name = 'Kayue\WordpressBundle\Entity\Post';
        $metadataInfo->setPrimaryTable(['name' => 'posts']);

        $args = new LoadClassMetadataEventArgs($metadataInfo, $em);
        $subscriber->loadClassMetadata($args);

        $this->assertEquals('other_posts', $metadataInfo->getTableName());
    }

    /**
     * @dataProvider wordpressEntitiesProvider
     */
    public function testTablePrefix($blogId, $entityName, $tableName, $result)
    {
        $subscriber = new TablePrefixSubscriber('wp_');

        $innerEm = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $wpEm = new WordpressEntityManager($innerEm);
        $wpEm->setBlogId($blogId);

        $metadataInfo = $this->createInitializedMetadata('Kayue\WordpressBundle\Tests\Fixture\Sample');
        $metadataInfo->name = "Kayue\\WordpressBundle\\Entity\\{$entityName}";
        $metadataInfo->setPrimaryTable(['name' => $tableName]);

        $args = new LoadClassMetadataEventArgs($metadataInfo, $innerEm);
        $subscriber->loadClassMetadata($args);

        $this->assertEquals("wp_{$result}", $metadataInfo->getTableName());
    }

    public static function wordpressEntitiesProvider()
    {
        return [
            [1, 'User', 'users', 'users'],
            [1, 'UserMeta', 'usermeta', 'usermeta'],
            [1, 'Post', 'posts', 'posts'],
            [1, 'Term', 'terms', 'terms'],
            [2, 'User', 'users', 'users'],
            [2, 'UserMeta', 'usermeta', 'usermeta'],
            [2, 'Post', 'posts', '2_posts'],
            [2, 'Term', 'terms', '2_terms'],
            [3, 'Post', 'posts', '3_posts'],
            [3, 'Term', 'terms', '3_terms'],
            [3, 'User', 'users', 'users'],
            [3, 'UserMeta', 'usermeta', 'usermeta'],
        ];
    }

    private function getEntityManagerMock()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

}
