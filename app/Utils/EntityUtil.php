<?php

namespace ShellreanDev\Utils;

use Illuminate\Support\Str;

class EntityUtil
{
    public static function randName()
    {
        return Str::random(4);
    }
}
