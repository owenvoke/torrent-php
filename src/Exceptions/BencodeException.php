<?php

namespace pxgamer\Torrent\Exceptions;

class BencodeException extends \Exception
{
    const STRING_LEADING_ZERO = 'Invalid string length, leading zero.';
    const STRING_COLON_NOT_FOUND = 'Invalid string length, colon not found.';
    const STRING_INPUT_TOO_SHORT = 'Invalid string, input too short for string length.';

    const INT_LEADING_ZERO = 'Leading zero in integer.';
    const INT_NON_DIGIT_CHARS = 'Non-digit characters in integer.';

    const LIST_UNTERMINATED = 'Unterminated list.';

    const DICTIONARY_MIS_SORTED_KEYS = 'Mis-sorted dictionary key.';
    const DICTIONARY_DUPLICATE_KEY = 'Duplicate dictionary key.';
    const DICTIONARY_INVALID_KEY = 'Invalid dictionary key.';
    const DICTIONARY_UNTERMINATED = 'Unterminated dictionary.';
}
