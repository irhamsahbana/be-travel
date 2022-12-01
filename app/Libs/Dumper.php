<?php

namespace App\Libs;


trait Dumper
{
    public function dump($var, $withPre = false, $die = true)
    {
        if ($withPre) echo '<pre>';
        var_dump($var);
        if ($withPre)  echo '</pre>';

        if ($die) die();
    }
}
