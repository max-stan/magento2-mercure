<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\ViewModel;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\ViewModel\HubUrl;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for HubUrl ViewModel.
 */
#[DbIsolation(true)]
class HubUrlTest extends TestCase
{
    private HubUrl $hubUrl;

    protected function setUp(): void
    {
        $this->hubUrl = Bootstrap::getObjectManager()->get(HubUrl::class);
    }

    /**
     * Verify getEncodedUrl returns base64-encoded hub URL.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    public function testGetEncodedUrlReturnsBase64EncodedValue(): void
    {
        $encoded = $this->hubUrl->getEncodedUrl();

        $this->assertNotEmpty($encoded);
        $this->assertSame(
            'https://hub.test/.well-known/mercure',
            base64_decode($encoded)
        );
    }

    /**
     * Verify getEncodedUrl returns empty string when hub URL is not configured.
     */
    #[Config('mercure/general/hub_url', '', 'store', 'default')]
    public function testGetEncodedUrlReturnsEmptyStringWhenNotConfigured(): void
    {
        $this->assertSame('', $this->hubUrl->getEncodedUrl());
    }
}
