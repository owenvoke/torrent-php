<?php

namespace OwenVoke\Torrent;

use OwenVoke\Torrent\Exceptions\BencodeException;
use PHPUnit\Framework\TestCase;

class BencodeExceptionTest extends TestCase
{
    /**
     * @test
     * @throws BencodeException
     */
    public function stringDecodeThrowsExceptionOnInvalidLength(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_LEADING_ZERO);

        $data = '00';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function stringDecodeThrowsExceptionOnColonNotFound(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_COLON_NOT_FOUND);

        $data = '0';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function stringDecodeThrowsExceptionOnInputTooShort(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::STRING_INPUT_TOO_SHORT);

        $data = '1:';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function intDecodeThrowsExceptionOnEmpty(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_IS_EMPTY);

        $data = 'ie';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function intDecodeThrowsExceptionOnLeadingZero(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_LEADING_ZERO);

        $data = 'i00e';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function intDecodeThrowsExceptionOnNonDigitCharacters(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::INT_NON_DIGIT_CHARS);

        $data = 'i1a2e';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function arrayDecodeThrowsExceptionOnUnterminatedList(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::LIST_UNTERMINATED);

        $data = 'l2:-e';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function objectDecodeThrowsExceptionOnMisSortedKeys(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_MIS_SORTED_KEYS);

        $data = 'd3:fooi42e3:bar4:spame';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function objectDecodeThrowsExceptionOnDuplicateKeys(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_DUPLICATE_KEY);

        $data = 'd3:bar4:spam3:bari42ee';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function objectDecodeThrowsExceptionOnInvalidKeys(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_INVALID_KEY);

        $data = 'd3:br:spam3:fooi42ee';

        Bencode::decode($data);
    }

    /**
     * @test
     * @throws BencodeException
     */
    public function objectDecodeThrowsExceptionOnUnterminatedDictionary(): void
    {
        $this->expectException(BencodeException::class);
        $this->expectExceptionMessage(BencodeException::DICTIONARY_UNTERMINATED);

        $data = 'd3:bar4:spam3:fooi42e';

        Bencode::decode($data);
    }
}
