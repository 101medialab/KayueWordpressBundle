<?php

namespace Kayue\WordpressBundle\Tests\Twig;

use Kayue\WordpressBundle\Twig\Extension\WordpressExtension;
use Kayue\WordpressBundle\Wordpress\Helper\AttachmentHelper;
use Kayue\WordpressBundle\Wordpress\ManagerRegistry;
use Kayue\WordpressBundle\Wordpress\Shortcode\ShortcodeChain;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class WordpressExtensionTest extends TestCase
{
    private WordpressExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new WordpressExtension(
            $this->createMock(ManagerRegistry::class),
            new ShortcodeChain(),
            $this->createMock(AttachmentHelper::class)
        );
    }

    public function testExtendsAbstractExtension()
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->extension);
    }

    public function testGetFiltersReturnsExpectedFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertCount(3, $filters);
        $this->assertContainsOnlyInstancesOf(TwigFilter::class, $filters);

        $names = array_map(fn(TwigFilter $f) => $f->getName(), $filters);
        $this->assertContains('wp_autop', $names);
        $this->assertContains('wp_texturize', $names);
        $this->assertContains('wp_shortcode', $names);
    }

    public function testGetFunctionsReturnsExpectedFunctions()
    {
        $functions = $this->extension->getFunctions();

        $this->assertNotEmpty($functions);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $functions);

        $names = array_map(fn(TwigFunction $f) => $f->getName(), $functions);
        $this->assertContains('wp_switch_blog', $names);
        $this->assertContains('wp_find_post_by', $names);
        $this->assertContains('wp_find_user_meta_by', $names);
        $this->assertContains('wp_find_terms_by_post', $names);
        $this->assertContains('wp_find_thumbnail', $names);
        $this->assertContains('wp_get_attachment_url', $names);
    }

    public function testWpautopWrapsTextInParagraphs()
    {
        $result = $this->extension->wpautop("Hello world\n\nSecond paragraph");

        $this->assertStringContainsString('<p>Hello world</p>', $result);
        $this->assertStringContainsString('<p>Second paragraph</p>', $result);
    }

    public function testWpautopReturnsEmptyForBlankInput()
    {
        $this->assertEquals('', $this->extension->wpautop(''));
        $this->assertEquals('', $this->extension->wpautop('   '));
    }

    public function testWpautopConvertsLineBreaks()
    {
        $result = $this->extension->wpautop("Line one\nLine two");

        $this->assertStringContainsString('<br />', $result);
    }

    public function testWpautopPreservesPreBlocks()
    {
        $input = "Before\n\n<pre>code\nhere</pre>\n\nAfter";
        $result = $this->extension->wpautop($input);

        $this->assertStringContainsString('<pre>code', $result);
    }

    public function testWptexturizeConvertsQuotes()
    {
        $result = $this->extension->wptexturize('He said "hello"');

        $this->assertStringContainsString('&#8220;', $result);
        $this->assertStringContainsString('&#8221;', $result);
    }

    public function testWptexturizeConvertsEmDash()
    {
        $result = $this->extension->wptexturize('word---word');

        $this->assertStringContainsString('&#8212;', $result);
    }

    public function testWptexturizeConvertsEllipsis()
    {
        $result = $this->extension->wptexturize('wait...');

        $this->assertStringContainsString('&#8230;', $result);
    }

    public function testWptexturizeSkipsPreTags()
    {
        $result = $this->extension->wptexturize('<pre>"quotes"</pre>');

        $this->assertStringNotContainsString('&#8220;', $result);
    }

    public function testDoShortcodeWithNoShortcodesRegistered()
    {
        $result = $this->extension->doShortcode('Hello [unknown]world[/unknown]');

        $this->assertEquals('Hello [unknown]world[/unknown]', $result);
    }
}
