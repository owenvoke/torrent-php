<?php

namespace pxgamer\Torrent;

/**
 * Class Bencode
 */
class Bencode
{
    /**
     * @param mixed $mixed data to encode
     * @return string torrent encoded data
     */
    public static function encode($mixed)
    {
        switch (gettype($mixed)) {
            case 'integer':
            case 'double':
                return self::encodeInteger($mixed);
                break;
            case 'object':
                $mixed = get_object_vars($mixed);
                return self::encodeArray($mixed);
            case 'array':
                return self::encodeArray($mixed);
            default:
                return self::encodeString((string)$mixed);
        }
    }

    /**
     * @param string string to encode
     * @return string encoded string
     */
    private static function encodeString($string)
    {
        return strlen($string) . ':' . $string;
    }

    /**
     * @param integer integer to encode
     * @return string encoded integer
     */
    private static function encodeInteger($integer)
    {
        return 'i' . $integer . 'e';
    }

    /**
     * @param array array to encode
     * @return string encoded dictionary or list
     */
    private static function encodeArray($array)
    {
        if (self::isList($array)) {
            $return = 'l';
            foreach ($array as $value) {
                $return .= self::encode($value);
            }
        } else {
            ksort($array, SORT_STRING);
            $return = 'd';
            foreach ($array as $key => $value) {
                $return .= self::encode(strval($key)) . self::encode($value);
            }
        }
        return $return . 'e';
    }

    /**
     * @param string $string data or file path to decode
     * @return array decoded torrent data
     * @throws \Exception
     */
    public static function decode(string $string)
    {
        $data = is_file($string) ?
            file_get_contents($string) :
            $string;
        return (array)self::decodeData($data);
    }

    /**
     * @param string $data data to decode
     * @return array|string decoded torrent data
     * @throws \Exception
     */
    private static function decodeData(& $data)
    {
        switch (self::char($data)) {
            case 'i':
                $data = substr($data, 1);
                return self::decodeInteger($data);
            case 'l':
                $data = substr($data, 1);
                return self::decodeList($data);
            case 'd':
                $data = substr($data, 1);
                return self::decodeDictionary($data);
            default:
                return self::decodeString($data);
        }
    }

    /**
     * @param string $data data to decode
     * @return array decoded dictionary
     * @throws \Exception
     */
    private static function decodeDictionary(& $data)
    {
        $dictionary = array();
        $previous = null;
        while (($char = self::char($data)) != 'e') {
            if ($char === false) {
                throw new \Exception('Unterminated dictionary');
            }
            if (!ctype_digit($char)) {
                throw new \Exception('Invalid dictionary key');
            }
            $key = self::decodeString($data);
            if (isset($dictionary[$key])) {
                throw new \Exception('Duplicate dictionary key');
            }
            if ($key < $previous) {
                throw new \Exception('Mis-sorted dictionary key');
            }
            $dictionary[$key] = self::decodeData($data);
            $previous = $key;
        }
        $data = substr($data, 1);
        return $dictionary;
    }

    /**
     * @param string $data data to decode
     * @return array decoded list
     * @throws \Exception
     */
    private static function decodeList(& $data)
    {
        $list = array();
        while (($char = self::char($data)) != 'e') {
            if ($char === false) {
                throw new \Exception('Unterminated list');
            }
            $list[] = self::decodeData($data);
        }
        $data = substr($data, 1);
        return $list;
    }

    /**
     * @param string $data data to decode
     * @return string decoded string
     * @throws \Exception
     */
    private static function decodeString(& $data)
    {
        if (self::char($data) === '0' && substr($data, 1, 1) != ':') {
            throw new \Exception('Invalid string length, leading zero');
        }
        if (!$colon = @strpos($data, ':')) {
            throw new \Exception('Invalid string length, colon not found');
        }
        $length = intval(substr($data, 0, $colon));
        if ($length + $colon + 1 > strlen($data)) {
            throw new \Exception('Invalid string, input too short for string length');
        }
        $string = substr($data, $colon + 1, $length);
        $data = substr($data, $colon + $length + 1);
        return $string;
    }

    /**
     * @param string $data data to decode
     * @return integer decoded integer
     * @throws \Exception
     */
    private static function decodeInteger(& $data)
    {
        $start = 0;
        $end = strpos($data, 'e');
        if ($end === 0) {
            throw new \Exception('Empty integer');
        }
        if (self::char($data) == '-') {
            $start++;
        }
        if (substr($data, $start, 1) == '0' && $end > $start + 1) {
            throw new \Exception('Leading zero in integer');
        }
        if (!ctype_digit(substr($data, $start, $start ? $end - 1 : $end))) {
            throw new \Exception('Non-digit characters in integer');
        }
        $integer = substr($data, 0, $end);
        $data = substr($data, $end + 1);
        return 0 + $integer;
    }

    /**
     * @param $array
     * @return bool
     */
    protected static function isList($array)
    {
        foreach (array_keys($array) as $key) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $data
     * @return bool|string
     */
    private static function char($data)
    {
        return empty($data) ?
            false :
            substr($data, 0, 1);
    }
}
