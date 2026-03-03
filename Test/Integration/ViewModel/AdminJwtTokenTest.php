<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\ViewModel\AdminJwtToken;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for AdminJwtToken ViewModel.
 */
#[DbIsolation(true)]
class AdminJwtTokenTest extends TestCase
{
    /**
     * Verify getJwt returns a valid JWT structure.
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtReturnsValidJwtStructure(): void
    {
        $viewModel = Bootstrap::getObjectManager()->create(AdminJwtToken::class);
        $jwt = $viewModel->getJwt();

        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'JWT must have 3 dot-separated segments');
    }

    /**
     * Verify getJwt returns a subscriber token (subscribe claim, not publish).
     */
    #[Config('mercure/general/jwt_subscriber_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testGetJwtReturnsSubscriberToken(): void
    {
        $viewModel = Bootstrap::getObjectManager()->create(AdminJwtToken::class);
        $jwt = $viewModel->getJwt();

        $parts = explode('.', $jwt);
        $payload = json_decode(
            base64_decode(strtr($parts[1], '-_', '+/')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertArrayHasKey('mercure', $payload);
        $this->assertArrayHasKey('subscribe', $payload['mercure']);
        $this->assertEmpty($payload['mercure']['publish'] ?? []);
    }
}
