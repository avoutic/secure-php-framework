<?php

namespace Tests\Unit;

use WebFramework\Core\Security\ProtectService;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProtectServiceTest extends \Codeception\Test\Unit
{
    public function testEncodeAndAuthStringEmpty()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->encode_and_auth_string(''))
            ->equals('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-WjlFVkU--4R1BtS3ZwQlBUZ1B--0cHhlUT09-4e0a82--9ee9b1aafad0f84b--95bed9979c081d12--e5bf7ebdd7bda9ee--fa0037dc5b');
    }

    public function testDecodeAndVerifyStringEmpty()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->decode_and_verify_string('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-WjlFVkU--4R1BtS3ZwQlBUZ1B--0cHhlUT09-4e0a82--9ee9b1aafad0f84b--95bed9979c081d12--e5bf7ebdd7bda9ee--fa0037dc5b'))
            ->equals('');
    }

    public function testEncodeAndAuthStringMessage()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->encode_and_auth_string('TestMessage'))
            ->equals('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730');
    }

    public function testDecodeAndVerifyStringMessage()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->decode_and_verify_string('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730'))
            ->equals('TestMessage');
    }

    public function testDecodeAndVerifyStringMissingDash()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->decode_and_verify_string('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09aa0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730'))
            ->equals(false);
    }

    public function testDecodeAndVerifyStringMessageWrongHmac()
    {
        $instance = $this->construct(
            ProtectService::class,
            [
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
            [
                'get_random_bytes' => '1234567890123456',
            ]
        );

        verify($instance->decode_and_verify_string('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8731'))
            ->equals(false);
    }
}