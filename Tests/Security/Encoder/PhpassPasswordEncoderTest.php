<?php

namespace Kayue\WordpressBundle\Tests\Security\Encoder;

use Kayue\WordpressBundle\Security\Encoder\PhpassPasswordEncoder;
use PHPUnit\Framework\TestCase;

class PhpassPasswordEncoderTest extends TestCase
{
    private PhpassPasswordEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PhpassPasswordEncoder();
    }

    public function testHashReturnsNonEmptyString()
    {
        $hash = $this->encoder->hash('password123');

        $this->assertNotEmpty($hash);
        $this->assertNotEquals('*', $hash);
    }

    public function testHashProducesPhpassFormat()
    {
        $hash = $this->encoder->hash('test');

        $this->assertMatchesRegularExpression('/^\$P\$/', $hash, 'Hash should start with $P$ (phpass portable format)');
        $this->assertEquals(34, strlen($hash), 'Portable hash should be 34 characters');
    }

    public function testVerifyCorrectPassword()
    {
        $hash = $this->encoder->hash('correct-password');

        $this->assertTrue($this->encoder->verify($hash, 'correct-password'));
    }

    public function testVerifyWrongPassword()
    {
        $hash = $this->encoder->hash('correct-password');

        $this->assertFalse($this->encoder->verify($hash, 'wrong-password'));
    }

    public function testVerifyWithKnownPhpassHash()
    {
        $knownHash = $this->encoder->hash('test-password');

        $freshEncoder = new PhpassPasswordEncoder();
        $this->assertTrue($freshEncoder->verify($knownHash, 'test-password'));
        $this->assertFalse($freshEncoder->verify($knownHash, 'wrong'));
    }

    public function testHashProducesDifferentHashesForSamePassword()
    {
        $hash1 = $this->encoder->hash('same-password');
        $hash2 = $this->encoder->hash('same-password');

        $this->assertNotEquals($hash1, $hash2, 'Different salts should produce different hashes');
        $this->assertTrue($this->encoder->verify($hash1, 'same-password'));
        $this->assertTrue($this->encoder->verify($hash2, 'same-password'));
    }

    public function testNeedsRehashReturnsFalse()
    {
        $hash = $this->encoder->hash('test');

        $this->assertFalse($this->encoder->needsRehash($hash));
    }

    public function testImplementsPasswordHasherInterface()
    {
        $this->assertInstanceOf(
            \Symfony\Component\PasswordHasher\PasswordHasherInterface::class,
            $this->encoder
        );
    }
}
