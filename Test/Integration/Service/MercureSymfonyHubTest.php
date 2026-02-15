<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\MercureTopicsAuthorizationInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Service\MercureSymfonyHub;
use PHPUnit\Framework\TestCase;
use Magento\Authorization\Model\UserContextInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

/**
 * Integration test for MercureSymfonyHub service
 *
 * Tests the Mercure hub initialization, caching behavior, and token provider functionality
 */
class MercureSymfonyHubTest extends TestCase
{
    private const TEST_JWT_SECRET = 'test-secret-key-that-is-at-least-256-bits-long!!';
    private const TEST_HUB_URL = 'http://localhost:8080/.well-known/mercure';
    private const TEST_CUSTOMER_ID = 42;
    private const TEST_ANOTHER_CUSTOMER_ID = 99;

    private MercureSymfonyHub $mercureHub;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        // Create mock Config that returns valid JWT secrets and hub URL
        $configMock = $this->createMock(Config::class);
        $configMock->method('getPublisherJwtSecret')
            ->willReturn(self::TEST_JWT_SECRET);
        $configMock->method('getSubscriberJwtSecret')
            ->willReturn(self::TEST_JWT_SECRET);
        $configMock->method('getHubUrl')
            ->willReturn(self::TEST_HUB_URL);

        // Create stub for MercureTopicsAuthorizationInterface
        $topicsAuthorizationStub = $this->createStub(MercureTopicsAuthorizationInterface::class);
        $topicsAuthorizationStub->method('getAllowedTopics')
            ->willReturn(['test-topic-*', 'customer-*']);

        // Create mock UserContextInterface
        $userContextMock = $this->createMock(UserContextInterface::class);
        $userContextMock->method('getUserId')
            ->willReturn(self::TEST_CUSTOMER_ID);

        // Get real instances from ObjectManager for cookie handling
        $cookieManager = $objectManager->get(CookieManagerInterface::class);
        $cookieMetadataFactory = $objectManager->get(CookieMetadataFactory::class);

        // Create the service with mocked dependencies
        $this->mercureHub = $objectManager->create(
            MercureSymfonyHub::class,
            [
                'config' => $configMock,
                'mercureTopicsAuthorization' => $topicsAuthorizationStub,
                'userContext' => $userContextMock,
                'cookieManager' => $cookieManager,
                'cookieMetadataFactory' => $cookieMetadataFactory,
            ]
        );
    }

    /**
     * Test that getMercureHub returns an instance of HubInterface
     */
    public function testGetMercureHubReturnsHubInstance(): void
    {
        $hub = $this->mercureHub->getMercureHub(self::TEST_CUSTOMER_ID);

        $this->assertInstanceOf(
            HubInterface::class,
            $hub,
            'getMercureHub should return an instance of HubInterface'
        );
    }

    /**
     * Test that getMercureHub caches instances per customer ID
     */
    public function testGetMercureHubCachesPerCustomer(): void
    {
        $hub1 = $this->mercureHub->getMercureHub(self::TEST_CUSTOMER_ID);
        $hub2 = $this->mercureHub->getMercureHub(self::TEST_CUSTOMER_ID);

        $this->assertSame(
            $hub1,
            $hub2,
            'getMercureHub should return the same instance for the same customer ID'
        );
    }

    /**
     * Test that different customer IDs produce different hub instances
     */
    public function testGetMercureHubDifferentCustomersDifferentInstances(): void
    {
        $hub1 = $this->mercureHub->getMercureHub(self::TEST_CUSTOMER_ID);
        $hub2 = $this->mercureHub->getMercureHub(self::TEST_ANOTHER_CUSTOMER_ID);

        $this->assertNotSame(
            $hub1,
            $hub2,
            'getMercureHub should return different instances for different customer IDs'
        );
    }

    /**
     * Test that getMercureHub handles null customer ID
     */
    public function testGetMercureHubWithNullCustomerId(): void
    {
        $hub = $this->mercureHub->getMercureHub(null);

        $this->assertInstanceOf(
            HubInterface::class,
            $hub,
            'getMercureHub should handle null customer ID'
        );
    }

    /**
     * Test that getTokenProvider returns an instance of TokenProviderInterface
     */
    public function testGetTokenProviderReturnsTokenProviderInstance(): void
    {
        $tokenProvider = $this->mercureHub->getTokenProvider(self::TEST_CUSTOMER_ID);

        $this->assertInstanceOf(
            TokenProviderInterface::class,
            $tokenProvider,
            'getTokenProvider should return an instance of TokenProviderInterface'
        );
    }

    /**
     * Test that getTokenProvider caches instances per customer ID
     */
    public function testGetTokenProviderCachesPerCustomer(): void
    {
        $tokenProvider1 = $this->mercureHub->getTokenProvider(self::TEST_CUSTOMER_ID);
        $tokenProvider2 = $this->mercureHub->getTokenProvider(self::TEST_CUSTOMER_ID);

        $this->assertSame(
            $tokenProvider1,
            $tokenProvider2,
            'getTokenProvider should return the same instance for the same customer ID'
        );
    }

    /**
     * Test that different customer IDs produce different token provider instances
     */
    public function testGetTokenProviderDifferentCustomersDifferentInstances(): void
    {
        $tokenProvider1 = $this->mercureHub->getTokenProvider(self::TEST_CUSTOMER_ID);
        $tokenProvider2 = $this->mercureHub->getTokenProvider(self::TEST_ANOTHER_CUSTOMER_ID);

        $this->assertNotSame(
            $tokenProvider1,
            $tokenProvider2,
            'getTokenProvider should return different instances for different customer IDs'
        );
    }

    /**
     * Test that getTokenProvider produces a valid JWT token
     */
    public function testGetTokenProviderProducesValidJwt(): void
    {
        $tokenProvider = $this->mercureHub->getTokenProvider(self::TEST_CUSTOMER_ID);
        $jwt = $tokenProvider->getJwt();

        $this->assertIsString($jwt, 'JWT should be a string');
        $this->assertNotEmpty($jwt, 'JWT should not be empty');

        // JWT should have three parts separated by dots (header.payload.signature)
        $parts = explode('.', $jwt);
        $this->assertCount(
            3,
            $parts,
            'Valid JWT should have three parts (header.payload.signature)'
        );

        // Each part should be base64-encoded (non-empty)
        foreach ($parts as $part) {
            $this->assertNotEmpty($part, 'Each JWT part should be non-empty');
        }
    }

    /**
     * Test that getTokenProvider handles null customer ID
     */
    public function testGetTokenProviderWithNullCustomerId(): void
    {
        $tokenProvider = $this->mercureHub->getTokenProvider(null);

        $this->assertInstanceOf(
            TokenProviderInterface::class,
            $tokenProvider,
            'getTokenProvider should handle null customer ID'
        );

        $jwt = $tokenProvider->getJwt();
        $this->assertNotEmpty($jwt, 'Token provider with null customer ID should produce valid JWT');
    }

    /**
     * Note: setAuthorizationHeader() is difficult to test in integration tests
     * without a real HTTP request context. This method sets cookies which require
     * a proper HTTP response context to be testable.
     *
     * Consider testing this method in functional tests where the full HTTP
     * request/response cycle is available, or in unit tests with mocked
     * cookie manager and metadata factory.
     */
}
