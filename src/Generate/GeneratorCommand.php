<?php
/**
 * copy files
 */
namespace Console\Generate;

use Symfony\Component\Console\Command\Command;

class GeneratorCommand extends Command
{

    public function copyAndReplace()
    {
        // read stub
        // replace keywords
        // save file
    }

    public function loadStub()
    {

    }

    public function replaceKeywords($string, $keywords = [])
    {
        return str_replace(array_keys($keywords), array_values($keywords), $string);
    }

}
