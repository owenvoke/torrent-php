<?php

namespace pxgamer\Torrent\Exceptions;

/**
 * Class BencodeException
 */
class BencodeException extends \Exception
{
    public const STRING_LEADING_ZERO = 'Invalid string length, leading zero.';
    public const STRING_COLON_NOT_FOUND = 'Invalid string length, colon not found.';
    public const STRING_INPUT_TOO_SHORT = 'Invalid string, input too short for string length.';

    public const INT_IS_EMPTY = 'Empty integer.';
    public const INT_LEADING_ZERO = 'Leading zero in integer.';
    public const INT_NON_DIGIT_CHARS = 'Non-digit characters in integer.';

    public const LIST_UNTERMINATED = 'Unterminated list.';

    public const DICTIONARY_MIS_SORTED_KEYS = 'Mis-sorted dictionary key.';
    public const DICTIONARY_DUPLICATE_KEY = 'Duplicate dictionary key.';
    public const DICTIONARY_INVALID_KEY = 'Invalid dictionary key.';
    public const DICTIONARY_UNTERMINATED = 'Unterminated dictionary.';
}
