<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use WebFramework\Security\OpensslRandomProvider;
use WebFramework\Security\ProtectService;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProtectServiceTest extends Unit
{
    public function testPackStringEmpty()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->packString(''))
            ->equals('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-WjlFVkU--4R1BtS3ZwQlBUZ1B--0cHhlUT09-4e0a82--9ee9b1aafad0f84b--95bed9979c081d12--e5bf7ebdd7bda9ee--fa0037dc5b')
        ;
    }

    public function testUnpackStringEmpty()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->unpackString('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-WjlFVkU--4R1BtS3ZwQlBUZ1B--0cHhlUT09-4e0a82--9ee9b1aafad0f84b--95bed9979c081d12--e5bf7ebdd7bda9ee--fa0037dc5b'))
            ->equals('')
        ;
    }

    public function testPackStringMessage()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->packString('TestMessage'))
            ->equals('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730')
        ;
    }

    public function testUnpackStringMessage()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->unpackString('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730'))
            ->equals('TestMessage')
        ;
    }

    public function testUnpackStringMissingDash()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->unpackString('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09aa0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8730'))
            ->equals(false)
        ;
    }

    public function testUnpackStringMessageWrongHmac()
    {
        $randomProvider = $this->make(
            OpensslRandomProvider::class,
            [
                'getRandom' => '1234567890123456',
            ]
        );

        $instance = $this->construct(
            ProtectService::class,
            [
                $randomProvider,
                [
                    'hash' => 'sha256',
                    'crypt_key' => '12345678901234567890',
                    'hmac_key' => 'ABCDEFGHIJKLMNOPQRST',
                ],
            ],
        );

        verify($instance->unpackString('MTIzNDU2Nzg5MDEy--MzQ1Ng~~-Nng2ZmJ--jMkZQa0VwVVBKUmc--zZTlkQT09-a0748e--6b775c146d632302--ad333fb5c4d5a326--742066db62d07a08--f7053e8731'))
            ->equals(false)
        ;
    }
}
