<?php

namespace Kayue\WordpressBundle\Tests\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    private static $metadataFactory;

    public static function setUpBeforeClass(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../../Entity'],
            true
        );

        $driver = $config->getMetadataDriverImpl();
        self::$metadataFactory = $driver;
    }

    /**
     * @dataProvider entityProvider
     */
    public function testEntityMetadataLoads(string $className, string $expectedTable)
    {
        $metadata = new ClassMetadata($className);
        $metadata->initializeReflection(new RuntimeReflectionService());
        self::$metadataFactory->loadMetadataForClass($className, $metadata);

        $this->assertEquals($expectedTable, $metadata->getTableName());
    }

    /**
     * @dataProvider entityProvider
     */
    public function testEntityHasIdField(string $className)
    {
        $metadata = new ClassMetadata($className);
        $metadata->initializeReflection(new RuntimeReflectionService());
        self::$metadataFactory->loadMetadataForClass($className, $metadata);

        $this->assertNotEmpty($metadata->getIdentifier(), "$className should have an identifier");
    }

    public function testPostEntityHasExpectedColumns()
    {
        $metadata = $this->loadMetadata('Kayue\WordpressBundle\Entity\Post');

        $this->assertTrue($metadata->hasField('title'), 'Post should have title field');
        $this->assertTrue($metadata->hasField('content'), 'Post should have content field');
        $this->assertTrue($metadata->hasField('status'), 'Post should have status field');
        $this->assertTrue($metadata->hasField('type'), 'Post should have type field');
        $this->assertTrue($metadata->hasField('slug'), 'Post should have slug field');
    }

    public function testPostEntityHasAssociations()
    {
        $metadata = $this->loadMetadata('Kayue\WordpressBundle\Entity\Post');

        $this->assertTrue($metadata->hasAssociation('user'), 'Post should have user association');
        $this->assertTrue($metadata->hasAssociation('metas'), 'Post should have metas association');
        $this->assertTrue($metadata->hasAssociation('comments'), 'Post should have comments association');
        $this->assertTrue($metadata->hasAssociation('taxonomies'), 'Post should have taxonomies association');
    }

    public function testUserEntityHasExpectedColumns()
    {
        $metadata = $this->loadMetadata('Kayue\WordpressBundle\Entity\User');

        $this->assertTrue($metadata->hasField('username'), 'User should have username field');
        $this->assertTrue($metadata->hasField('email'), 'User should have email field');
        $this->assertTrue($metadata->hasField('password'), 'User should have password field');
        $this->assertTrue($metadata->hasField('displayName'), 'User should have displayName field');
    }

    public function testUserEntityHasAssociations()
    {
        $metadata = $this->loadMetadata('Kayue\WordpressBundle\Entity\User');

        $this->assertTrue($metadata->hasAssociation('metas'), 'User should have metas association');
        $this->assertTrue($metadata->hasAssociation('posts'), 'User should have posts association');
        $this->assertTrue($metadata->hasAssociation('comments'), 'User should have comments association');
    }

    public function testTaxonomyEntityHasAssociations()
    {
        $metadata = $this->loadMetadata('Kayue\WordpressBundle\Entity\Taxonomy');

        $this->assertTrue($metadata->hasAssociation('term'), 'Taxonomy should have term association');
        $this->assertTrue($metadata->hasAssociation('posts'), 'Taxonomy should have posts association');
    }

    public static function entityProvider(): array
    {
        return [
            ['Kayue\WordpressBundle\Entity\Post', 'posts'],
            ['Kayue\WordpressBundle\Entity\PostMeta', 'postmeta'],
            ['Kayue\WordpressBundle\Entity\User', 'users'],
            ['Kayue\WordpressBundle\Entity\UserMeta', 'usermeta'],
            ['Kayue\WordpressBundle\Entity\Comment', 'comments'],
            ['Kayue\WordpressBundle\Entity\CommentMeta', 'commentmeta'],
            ['Kayue\WordpressBundle\Entity\Term', 'terms'],
            ['Kayue\WordpressBundle\Entity\TermMeta', 'termmeta'],
            ['Kayue\WordpressBundle\Entity\Taxonomy', 'term_taxonomy'],
            ['Kayue\WordpressBundle\Entity\Option', 'options'],
            ['Kayue\WordpressBundle\Entity\Blog', 'blogs'],
        ];
    }

    private function loadMetadata(string $className): ClassMetadata
    {
        $metadata = new ClassMetadata($className);
        $metadata->initializeReflection(new RuntimeReflectionService());
        self::$metadataFactory->loadMetadataForClass($className, $metadata);

        return $metadata;
    }
}
