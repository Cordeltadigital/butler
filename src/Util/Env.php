<?php
namespace Console\Util;

class Env
{
    public static function getGlobalEnvFilePath()
    {
        $path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . '.butler';

        if (!file_exists($path)) {
            mkdir($path);
        }
        return $path . '/globals';
    }

    public static function getGlobalEnv($key)
    {
        $path = self::getGlobalEnvFilePath();

        if (!file_exists($path)) {
            return false;
        }
        $globals = json_decode(file_get_contents($path), true);
        if (!in_array($key, array_keys($globals))) {
            return false;
        }

        return $globals[$key];
    }

    public static function saveGlobalEnv($key, $value)
    {
        $path = self::getGlobalEnvFilePath();
        if (!file_exists($path)) {
            $raw = '';
        } else {
            $raw = file_get_contents($path);
        }
        $config = json_decode($raw, true);
        $config[$key] = $value;

        $txt = json_encode($config);

        file_put_contents($path, $txt);
    }

    public static function generateEnvFile($config, $envFile)
    {
        $myfile = fopen($envFile, "w") or die("Unable to open file!");
        $txt = json_encode($config);
        fwrite($myfile, $txt);
        fclose($myfile);
    }

    public static function loadConfig($envFile = './.butler.env')
    {
        $config = json_decode(file_get_contents($envFile), true);
        array_map(function ($v) {
            return trim($v);
        }, $config); //trim spaces

        return $config;
    }

}
