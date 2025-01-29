<?php

namespace App\Helper;

use App\Enum\NormalizeMode;

class GeneralHelper
{
    public static function getRandomCode($length = 5): string
    {
        $r = "";
        $chars = array_merge(range('A', 'Z'), range(0, 9));
        $charsLength = count($chars);

        for ($i = 0; $i < $length; $i++) {
            $r .= $chars[mt_rand(0, $charsLength - 1)];
        }
        return $r;
    }
}
