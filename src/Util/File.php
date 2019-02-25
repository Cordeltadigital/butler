<?php
namespace Console\Util;

class File
{
    public static function scan($dir)
    {
        $result = [];
        foreach (scandir($dir) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filePath)) {
                foreach (self::scan($filePath) as $childFilename) {
                    $result[] = $filename . DIRECTORY_SEPARATOR . $childFilename;
                }
            } else {
                $result[] = $filename;
            }
        }
        return $result;
    }
}
