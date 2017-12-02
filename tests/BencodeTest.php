<?php

namespace pxgamer\Torrent;

use PHPUnit\Framework\TestCase;

/**
 * Class BencodeTest
 */
class BencodeTest extends TestCase
{
    /**
     *
     */
    public function testCanEncodeString()
    {
        $data = 'test';

        $result = Bencode::encode($data);

        $this->assertEquals('4:test', $result);
    }

    /**
     *
     */
    public function testCanEncodeInt()
    {
        $data = 10;

        $result = Bencode::encode($data);

        $this->assertEquals('i10e', $result);
    }

    /**
     *
     */
    public function testCanEncodeArray()
    {
        $data = [
            'test'
        ];

        $result = Bencode::encode($data);

        $this->assertEquals('l4:teste', $result);
    }

    /**
     *
     */
    public function testCanEncodeObject()
    {
        $data = new \stdClass();
        $data->test = 1;

        $result = Bencode::encode($data);

        $this->assertEquals('d4:testi1ee', $result);
    }

    /**
     * @throws \Exception
     */
    public function testCanDecodeString()
    {
        $data = '4:test';

        $result = Bencode::decode($data);

        $this->assertEquals('test', $result);
    }

    /**
     * @throws \Exception
     */
    public function testCanDecodeInt()
    {
        $data = 'i10e';

        $result = Bencode::decode($data);

        $this->assertEquals(10, $result);
    }

    /**
     * @throws \Exception
     */
    public function testCanDecodeArray()
    {
        $data = 'l4:teste';

        $result = Bencode::decode($data);

        $this->assertEquals(['test'], $result);
    }

    /**
     * @throws \Exception
     */
    public function testCanDecodeObject()
    {
        $data = 'd4:testi1ee';

        $result = Bencode::decode($data);

        $this->assertEquals(['test'=>1], $result);
    }
}
