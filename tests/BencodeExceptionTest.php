<?php

namespace pxgamer\Torrent;

use PHPUnit\Framework\TestCase;
use pxgamer\Torrent\Exceptions\BencodeException;

/**
 * Class BencodeExceptionTest
 */
class BencodeExceptionTest extends TestCase
{
    /**
     * @throws BencodeException
     */
    public function testStringDecodeThrowsExceptionOnInvalidLength()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_LEADING_ZERO);

        $data = '00';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testStringDecodeThrowsExceptionOnColonNotFound()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_COLON_NOT_FOUND);

        $data = '0';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testStringDecodeThrowsExceptionOnInputTooShort()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_INPUT_TOO_SHORT);

        $data = '1:';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testIntDecodeThrowsExceptionOnEmpty()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_IS_EMPTY);

        $data = 'ie';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testIntDecodeThrowsExceptionOnLeadingZero()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_LEADING_ZERO);

        $data = 'i00e';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testIntDecodeThrowsExceptionOnNonDigitCharacters()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_NON_DIGIT_CHARS);

        $data = 'i1a2e';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testArrayDecodeThrowsExceptionOnUnterminatedList()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::LIST_UNTERMINATED);

        $data = 'l2:-e';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testObjectDecodeThrowsExceptionOnMisSortedKeys()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_MIS_SORTED_KEYS);

        $data = 'd3:fooi42e3:bar4:spame';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testObjectDecodeThrowsExceptionOnDuplicateKeys()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_DUPLICATE_KEY);

        $data = 'd3:bar4:spam3:bari42ee';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testObjectDecodeThrowsExceptionOnInvalidKeys()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_INVALID_KEY);

        $data = 'd3:br:spam3:fooi42ee';

        Bencode::decode($data);
    }

    /**
     * @throws BencodeException
     */
    public function testObjectDecodeThrowsExceptionOnUnterminatedDictionary()
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_UNTERMINATED);

        $data = 'd3:bar4:spam3:fooi42e';

        Bencode::decode($data);
    }
}
