<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private Config $config;
    private MutableScopeConfigInterface $mutableScopeConfig;
    private EncryptorInterface $encryptor;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->create(Config::class);
        $this->mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->encryptor = $objectManager->get(EncryptorInterface::class);
    }

    /**
     * @magentoConfigFixture default/mercure/hub_settings/hub_url http://localhost:8080/.well-known/mercure
     */
    public function testGetHubUrl(): void
    {
        $hubUrl = $this->config->getHubUrl();

        $this->assertSame('http://localhost:8080/.well-known/mercure', $hubUrl);
    }

    public function testGetHubUrlReturnsEmptyStringWhenNotConfigured(): void
    {
        $hubUrl = $this->config->getHubUrl();

        $this->assertSame('', $hubUrl);
    }

    public function testGetPublisherJwtSecret(): void
    {
        $plainSecret = 'test-publisher-secret-key';
        $encryptedSecret = $this->encryptor->encrypt($plainSecret);

        $this->mutableScopeConfig->setValue(
            'mercure/hub_settings/publisher_jwt_secret',
            $encryptedSecret
        );

        $decryptedSecret = $this->config->getPublisherJwtSecret();

        $this->assertSame($plainSecret, $decryptedSecret);
    }

    public function testGetSubscriberJwtSecret(): void
    {
        $plainSecret = 'test-subscriber-secret-key';
        $encryptedSecret = $this->encryptor->encrypt($plainSecret);

        $this->mutableScopeConfig->setValue(
            'mercure/hub_settings/subscriber_jwt_secret',
            $encryptedSecret
        );

        $decryptedSecret = $this->config->getSubscriberJwtSecret();

        $this->assertSame($plainSecret, $decryptedSecret);
    }

    public function testGetPublisherJwtSecretReturnsEmptyStringWhenNotConfigured(): void
    {
        $secret = $this->config->getPublisherJwtSecret();

        $this->assertSame('', $secret);
    }

    public function testGetSubscriberJwtSecretReturnsEmptyStringWhenNotConfigured(): void
    {
        $secret = $this->config->getSubscriberJwtSecret();

        $this->assertSame('', $secret);
    }
}
