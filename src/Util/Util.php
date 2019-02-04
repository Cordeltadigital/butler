<?php

namespace Console\Util;

class Util
{
    public static function copyFileAndReplaceContent($src, $dest, $keywords_array)
    {
        $str = file_get_contents($src);
        $newstr = str_replace(array_keys($keywords_array), array_values($keywords_array), $str);
        echo $newstr;
        file_put_contents($dest, $newstr);
    }
}
