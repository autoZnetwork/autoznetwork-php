<?php

namespace AutozNetwork\Tests\Authentication;

use AutozNetwork\Authentication\AccessTokenMetadata;
use PHPUnit\Framework\TestCase;

class AccessTokenMetadataTest extends TestCase
{

    protected $graphResponseData = [
        'data' => [
            'app_id' => '123',
            'application' => 'Foo App',
            'error' => [
                'code' => 190,
                'message' => 'Foo error message.',
                'subcode' => 463,
            ],
            'issued_at' => 1422110200,
            'expires_at' => 1422115200,
            'is_valid' => false,
            'metadata' => [
                'sso' => 'iphone-sso',
                'auth_type' => 'rerequest',
                'auth_nonce' => 'no-replicatey',
            ],
            'scopes' => ['public_profile', 'basic_info', 'user_friends'],
            'profile_id' => '1000',
            'user_id' => '1337',
        ],
    ];

    public function testDatesGetCastToDateTime()
    {
        $metadata = new AccessTokenMetadata($this->graphResponseData);

        $expires = $metadata->getExpiresAt();
        $issuedAt = $metadata->getIssuedAt();

        $this->assertInstanceOf('DateTime', $expires);
        $this->assertInstanceOf('DateTime', $issuedAt);
    }

    public function testAllTheGettersReturnTheProperValue()
    {
        $metadata = new AccessTokenMetadata($this->graphResponseData);

        $this->assertEquals('123', $metadata->getAppId());
        $this->assertEquals('Foo App', $metadata->getApplication());
        $this->assertTrue($metadata->isError(), 'Expected an error');
        $this->assertEquals('190', $metadata->getErrorCode());
        $this->assertEquals('Foo error message.', $metadata->getErrorMessage());
        $this->assertEquals('463', $metadata->getErrorSubcode());
        $this->assertFalse($metadata->getIsValid(), 'Expected the access token to not be valid');
        $this->assertEquals('iphone-sso', $metadata->getSso());
        $this->assertEquals('rerequest', $metadata->getAuthType());
        $this->assertEquals('no-replicatey', $metadata->getAuthNonce());
        $this->assertEquals('1000', $metadata->getProfileId());
        $this->assertEquals(['public_profile', 'basic_info', 'user_friends'], $metadata->getScopes());
        $this->assertEquals('1337', $metadata->getUserId());
    }

    public function testInvalidMetadataWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        new AccessTokenMetadata(['foo' => 'bar']);
    }

    public function testAnExpectedAppIdWillNotThrow()
    {
        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateAppId('123');

        $this->expectNotToPerformAssertions();
    }

    public function testAnUnexpectedAppIdWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateAppId('foo');
    }

    public function testAnExpectedUserIdWillNotThrow()
    {
        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateUserId('1337');

        $this->expectNotToPerformAssertions();
    }

    public function testAnUnexpectedUserIdWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateUserId('foo');
    }

    public function testAnActiveAccessTokenWillNotThrow()
    {
        $this->graphResponseData['data']['expires_at'] = time() + 1000;
        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateExpiration();

        $this->expectNotToPerformAssertions();
    }

    public function testAnExpiredAccessTokenWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $this->graphResponseData['data']['expires_at'] = time() - 1000;
        $metadata = new AccessTokenMetadata($this->graphResponseData);
        $metadata->validateExpiration();
    }
}
