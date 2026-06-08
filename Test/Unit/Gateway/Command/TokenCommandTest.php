<?php
/**
 * Copyright 2020 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace Vipps\Login\Test\Unit\Gateway\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Api\ModuleMetadataInterface;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\ConfigInterface;

/**
 * Covers the VIPPS-51 token-storage hardening: the cached token payload must be encrypted at rest
 * (and decrypted when a duplicate callback reuses it), and the stale-row cleanup must use a valid
 * datetime format so rows actually expire.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenCommandTest extends TestCase
{
    /** @var ConfigInterface|MockObject */
    private $config;

    /** @var SerializerInterface|MockObject */
    private $serializer;

    /** @var ApiEndpointsInterface|MockObject */
    private $apiEndpoints;

    /** @var ClientFactory|MockObject */
    private $httpClientFactory;

    /** @var UrlInterface|MockObject */
    private $url;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ResourceConnection|MockObject */
    private $resourceConnection;

    /** @var ModuleMetadataInterface|MockObject */
    private $moduleMetadata;

    /** @var EncryptorInterface|MockObject */
    private $encryptor;

    /** @var AdapterInterface|MockObject */
    private $connection;

    /** @var TokenCommand */
    private $tokenCommand;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->apiEndpoints = $this->createMock(ApiEndpointsInterface::class);
        $this->httpClientFactory = $this->createMock(ClientFactory::class);
        $this->url = $this->createMock(UrlInterface::class);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass();
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->moduleMetadata = $this->createMock(ModuleMetadataInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $this->connection = $this->createMock(AdapterInterface::class);
        $this->connection->method('getTableName')->willReturnArgument(0);
        $this->connection->method('quote')->willReturnCallback(static fn($v) => "'" . $v . "'");
        $this->connection->method('select')->willReturn($select);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);

        $objectManager = new ObjectManager($this);
        $this->tokenCommand = $objectManager->getObject(TokenCommand::class, [
            'config' => $this->config,
            'serializer' => $this->serializer,
            'apiEndpoints' => $this->apiEndpoints,
            'httpClientFactory' => $this->httpClientFactory,
            'url' => $this->url,
            'logger' => $this->logger,
            'resourceConnection' => $this->resourceConnection,
            'moduleMetadata' => $this->moduleMetadata,
            'encryptor' => $this->encryptor,
        ]);
    }

    /**
     * A duplicate/concurrent callback finds the payload already cached: it must decrypt the stored
     * value and must NOT call Vipps again (the authorization code is single-use).
     */
    public function testReusePathDecryptsCachedPayloadAndSkipsVipps(): void
    {
        $code = 'auth-code';
        $cipher = 'ENCRYPTED_PAYLOAD';
        $body = '{"access_token":"abc"}';

        $this->connection->method('fetchRow')->willReturn([
            'code' => $code,
            'payload' => $cipher,
            'created_at' => '2026-06-08 10:00:00',
        ]);

        $this->encryptor->expects($this->once())
            ->method('decrypt')
            ->with($cipher)
            ->willReturn($body);

        // No 'id_token' key -> getPayload() short-circuits, so no JWKS/JWT round trip in the test.
        $this->serializer->method('unserialize')->with($body)->willReturn(['access_token' => 'abc']);

        $this->httpClientFactory->expects($this->never())->method('create');

        $result = $this->tokenCommand->execute($code);

        $this->assertSame('abc', $result['access_token']);
        $this->assertArrayHasKey('id_token_payload', $result);
        $this->assertNull($result['id_token_payload']);
    }

    /**
     * First callback for a code: no cached payload yet, so it exchanges the code with Vipps and must
     * encrypt the response body before persisting it.
     */
    public function testStorePathEncryptsTokenPayloadBeforePersisting(): void
    {
        $code = 'auth-code';
        $body = '{"access_token":"abc"}';
        $cipher = 'CIPHER';

        // No row first, then a row without payload after the insert.
        $this->connection->method('fetchRow')->willReturnOnConsecutiveCalls(false, ['code' => $code]);
        $this->connection->expects($this->once())->method('insert');

        $captured = null;
        $this->connection->expects($this->once())
            ->method('update')
            ->willReturnCallback(function ($table, $data, $where) use (&$captured) {
                $captured = $data;
                return 1;
            });

        $this->config->method('getLoginClientId')->willReturn('client-id');
        $this->config->method('getLoginClientSecret')->willReturn('client-secret');
        $this->apiEndpoints->method('getTokenEndpoint')->willReturn('https://example.test/token');
        $this->url->method('getUrl')->willReturn('https://shop.test/vipps/login/redirect/');
        $this->moduleMetadata->method('getSystemName')->willReturn('Magento');
        $this->moduleMetadata->method('getSystemVersion')->willReturn('2.4.8');
        $this->moduleMetadata->method('getModuleName')->willReturn('Vipps_Login');
        $this->moduleMetadata->method('getModuleVersion')->willReturn('2.6.2');

        $client = $this->createMock(ClientInterface::class);
        $client->method('getBody')->willReturn($body);
        $this->httpClientFactory->method('create')->willReturn($client);

        $this->encryptor->expects($this->once())->method('encrypt')->with($body)->willReturn($cipher);
        $this->serializer->method('unserialize')->with($body)->willReturn(['access_token' => 'abc']);

        $result = $this->tokenCommand->execute($code);

        $this->assertSame($cipher, $captured['payload'], 'The persisted payload must be the ciphertext.');
        $this->assertSame('abc', $result['access_token']);
    }

    /**
     * Regression guard for the cleanup bug: the 5-minute cutoff was formatted 'Y-m-d H:i-s' (a dash
     * before seconds), which produced a malformed datetime so stale token rows were never pruned.
     */
    public function testStaleCleanupUsesValidDatetimeFormat(): void
    {
        $captured = null;
        $this->connection->method('delete')
            ->willReturnCallback(function ($table, $where) use (&$captured) {
                $captured = $where;
                return 1;
            });

        // Short-circuit on the reuse path so the test stays focused on the cleanup call.
        $this->connection->method('fetchRow')->willReturn(['code' => 'c', 'payload' => 'X']);
        $this->encryptor->method('decrypt')->willReturn('{}');
        $this->serializer->method('unserialize')->willReturn(['access_token' => 'a']);

        $this->tokenCommand->execute('c');

        $this->assertNotNull($captured, 'The cleanup delete should have run.');
        $this->assertMatchesRegularExpression(
            "/^created_at < '\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}'$/",
            $captured,
            'Cutoff must use Y-m-d H:i:s (colon before seconds), not the malformed H:i-s.'
        );
    }
}
