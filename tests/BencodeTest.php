<?php

use OwenVoke\Torrent\Bencode;

it('can encode a string', function () {
    $data = 'test';

    $result = Bencode::encode($data);

    expect($result)->toEqual('4:test');
});

it('can encode an integer', function () {
    $data = 10;

    $result = Bencode::encode($data);

    expect($result)->toEqual('i10e');
});

it('can encode an array', function () {
    $data = [
        'test',
    ];

    $result = Bencode::encode($data);

    expect($result)->toEqual('l4:teste');
});

it('can encode an object', function () {
    $data = new \stdClass();
    $data->test = 1;

    $result = Bencode::encode($data);

    expect($result)->toEqual('d4:testi1ee');
});

it('can decode a string', function () {
    $data = '4:test';

    $result = Bencode::decode($data);

    expect($result)->toEqual('test');
});

it('can decode an integer', function () {
    $data = 'i10e';

    $result = Bencode::decode($data);

    expect($result)->toEqual(10);
});

it('can decode an array', function () {
    $data = 'l4:teste';

    $result = Bencode::decode($data);

    expect($result)->toEqual(['test']);
});

it('can decode an object', function () {
    $data = 'd4:testi1ee';

    $result = Bencode::decode($data);

    expect($result)->toEqual(['test' => 1]);
});
