<?php

namespace Kayue\WordpressBundle\Tests\Wordpress\Shortcode;

use Kayue\WordpressBundle\Wordpress\Shortcode\GalleryShortcode;
use PHPUnit\Framework\TestCase;

class GalleryShortcodeTest extends TestCase
{
    public function testProcess()
    {
        $this->markTestIncomplete(
            'This test has not been updated yet.'
        );

        $attachmentManagerMock = $this->getMockBuilder('Kayue\WordpressBundle\Model\AttachmentManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $attachmentManagerMock
            ->expects($this->any())
            ->method('findImageWithIds')
            ->will($this->returnValue(array()))
        ;
        $templatingMock = $this->createMock(\Symfony\Component\Templating\EngineInterface::class);
        $templatingMock
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('something'))
        ;
        $container = $this->createMock(\Symfony\Component\DependencyInjection\Container::class);
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->onConsecutiveCalls($attachmentManagerMock, $templatingMock))
        ;

        $galleryShortcode = new GalleryShortcode($container);
        $final = $galleryShortcode->process(array('ids' => '1,2,3'));

        $this->assertEquals($final, 'something');
    }
}
