<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\ViewModel;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\ViewModel\Mercure;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure ViewModel.
 */
#[DbIsolation(true)]
class HubUrlTest extends TestCase
{
    private Mercure $viewModel;

    protected function setUp(): void
    {
        $this->viewModel = Bootstrap::getObjectManager()->get(Mercure::class);
    }

    /**
     * Verify getEncodedParams returns query string with base64-encoded hub URL.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    public function testGetEncodedParamsReturnsQueryStringWithEncodedHub(): void
    {
        $params = $this->viewModel->getEncodedParams();

        $this->assertNotEmpty($params);
        parse_str($params, $parsed);
        $this->assertArrayHasKey('hub', $parsed);
        $this->assertSame(
            'https://hub.test/.well-known/mercure',
            base64_decode($parsed['hub'])
        );
    }

    /**
     * Verify getEncodedParams returns empty hub when URL is not configured.
     */
    #[Config('mercure/general/hub_url', '', 'store', 'default')]
    public function testGetEncodedParamsReturnsEmptyHubWhenNotConfigured(): void
    {
        $params = $this->viewModel->getEncodedParams();

        parse_str($params, $parsed);
        $this->assertEmpty(base64_decode($parsed['hub'] ?? ''));
    }

    /**
     * Verify getTokenRefreshEndpoint returns a non-empty URL.
     */
    public function testGetTokenRefreshEndpointReturnsUrl(): void
    {
        $endpoint = $this->viewModel->getTokenRefreshEndpoint();

        $this->assertNotEmpty($endpoint);
        $this->assertStringContainsString('mercure/token/refresh', $endpoint);
    }
}
