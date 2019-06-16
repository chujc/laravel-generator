<?php

namespace ChuJC\LaravelGenerator\Support;

/**
 * Class VariableConversion.
 */
class VariableConversion
{

    /**
     * 把数组转换为数组字符串
     * @param $array
     * @return string
     */
    public static function convertArrayToString($array)
    {
        $string = '[';
        if (!empty($array)) {
            $string .= "\n        '";
            $string .= implode("',\n        '", $array);
            $string .= "'\n    ";
        }
        $string .= ']';

        return $string;
    }

    /**
     * 二维数组转换为一维数组，值等于指定二位数组中的某个键
     * @param $array
     * @param $key
     * @return string
     */
    public static function arrayKey($array, $key)
    {
        foreach ($array as $item => &$value) {
            $array[$item] = $value[$key];
        };
        return static::convertArrayToString($array);
    }
}
