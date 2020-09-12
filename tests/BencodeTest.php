<?php

namespace OwenVoke\Torrent;

use OwenVoke\Torrent\Exceptions\BencodeException;
use PHPUnit\Framework\TestCase;

class BencodeTest extends TestCase
{
    /** @test */
    public function canEncodeString(): void
    {
        $data = 'test';

        $result = Bencode::encode($data);

        $this->assertEquals('4:test', $result);
    }

    /** @test */
    public function canEncodeInt(): void
    {
        $data = 10;

        $result = Bencode::encode($data);

        $this->assertEquals('i10e', $result);
    }

    /** @test */
    public function canEncodeArray(): void
    {
        $data = [
            'test',
        ];

        $result = Bencode::encode($data);

        $this->assertEquals('l4:teste', $result);
    }

    /** @test */
    public function canEncodeObject(): void
    {
        $data = new \stdClass();
        $data->test = 1;

        $result = Bencode::encode($data);

        $this->assertEquals('d4:testi1ee', $result);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function canDecodeString(): void
    {
        $data = '4:test';

        $result = Bencode::decode($data);

        $this->assertEquals('test', $result);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function canDecodeInt(): void
    {
        $data = 'i10e';

        $result = Bencode::decode($data);

        $this->assertEquals(10, $result);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function canDecodeArray(): void
    {
        $data = 'l4:teste';

        $result = Bencode::decode($data);

        $this->assertEquals(['test'], $result);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function canDecodeObject(): void
    {
        $data = 'd4:testi1ee';

        $result = Bencode::decode($data);

        $this->assertEquals(['test' => 1], $result);
    }
}
