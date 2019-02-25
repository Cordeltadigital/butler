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

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
