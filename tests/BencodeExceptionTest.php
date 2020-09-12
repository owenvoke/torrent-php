<?php

use OwenVoke\Torrent\Bencode;
use OwenVoke\Torrent\Exceptions\BencodeException;

it('throws an exception when decoding a string with a leading zero', function () {
    $data = '00';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::STRING_LEADING_ZERO);

it('throws an exception when decoding a string without a colon', function () {
    $data = '0';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::STRING_COLON_NOT_FOUND);

it('throws an exception when decoding a string that is too short', function () {
    $data = '1:';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::STRING_INPUT_TOO_SHORT);

it('throws an exception when decoding an empty string', function () {
    $data = 'ie';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::INT_IS_EMPTY);

it('throws an exception when decoding an integer with a leading zero', function () {
    $data = 'i00e';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::INT_LEADING_ZERO);

it('throws an exception when decoding an integer with non-digit characters', function () {
    $data = 'i1a2e';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::INT_NON_DIGIT_CHARS);

it('throws an exception when decoding an array with an unterminated list', function () {
    $data = 'l2:-e';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::LIST_UNTERMINATED);

it('throws an exception when decoding an object with mis-sorted keys', function () {
    $data = 'd3:fooi42e3:bar4:spame';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::DICTIONARY_MIS_SORTED_KEYS);

it('throws an exception when decoding an object with a duplicate key', function () {
    $data = 'd3:bar4:spam3:bari42ee';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::DICTIONARY_DUPLICATE_KEY);

it('throws an exception when decoding an object with an invalid key', function () {
    $data = 'd3:br:spam3:fooi42ee';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::DICTIONARY_INVALID_KEY);

it('throws an exception when decoding an object with an unterminated dictionary', function () {
    $data = 'd3:bar4:spam3:fooi42e';

    Bencode::decode($data);
})->throws(BencodeException::class, BencodeException::DICTIONARY_UNTERMINATED);
