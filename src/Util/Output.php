<?php
namespace Console\Util;

use Symfony\Component\Console\Output\OutputInterface;

class Output
{

    public static function signature(OutputInterface $output)
    {
        $output->writeln("<info>

    BBBBBBBBBBBBBBBBB                            tttt          lllllll
    B::::::::::::::::B                        ttt:::t          l:::::l
    B::::::BBBBBB:::::B                       t:::::t          l:::::l
    BB:::::B     B:::::B                      t:::::t          l:::::l
      B::::B     B:::::Buuuuuu    uuuuuuttttttt:::::ttttttt     l::::l     eeeeeeeeeeee    rrrrr   rrrrrrrrr
      B::::B     B:::::Bu::::u    u::::ut:::::::::::::::::t     l::::l   ee::::::::::::ee  r::::rrr:::::::::r
      B::::BBBBBB:::::B u::::u    u::::ut:::::::::::::::::t     l::::l  e::::::eeeee:::::eer:::::::::::::::::r
      B:::::::::::::BB  u::::u    u::::utttttt:::::::tttttt     l::::l e::::::e     e:::::err::::::rrrrr::::::r
      B::::BBBBBB:::::B u::::u    u::::u      t:::::t           l::::l e:::::::eeeee::::::e r:::::r     r:::::r
      B::::B     B:::::Bu::::u    u::::u      t:::::t           l::::l e:::::::::::::::::e  r:::::r     rrrrrrr
      B::::B     B:::::Bu::::u    u::::u      t:::::t           l::::l e::::::eeeeeeeeeee   r:::::r
      B::::B     B:::::Bu:::::uuuu:::::u      t:::::t    tttttt l::::l e:::::::e            r:::::r
    BB:::::BBBBBB::::::Bu:::::::::::::::uu    t::::::tttt:::::tl::::::le::::::::e           r:::::r
    B:::::::::::::::::B  u:::::::::::::::u    tt::::::::::::::tl::::::l e::::::::eeeeeeee   r:::::r
    B::::::::::::::::B    uu::::::::uu:::u      tt:::::::::::ttl::::::l  ee:::::::::::::e   r:::::r
    BBBBBBBBBBBBBBBBB       uuuuuuuu  uuuu        ttttttttttt  llllllll    eeeeeeeeeeeeee   rrrrrrr            v" . BUTLER_VER . "

    By
    _____            __    ____
    / ___/__  _______/ /__ / / /____ _
   / /__/ _ \/ __/ _  / -_) / __/ _ `/
   \___/\___/_/  \_,_/\__/_/\__/\_,_/
             ___                    _
             ( / \ o     o _/_      //
              /  /,  _, ,  /  __,  //
            (/\_/ (_(_)_(_(__(_/(_(/_
                     /|
                    (/
=====================================================================================================================
</info>");
    }

}
