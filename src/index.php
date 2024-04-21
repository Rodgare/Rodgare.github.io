<?php

namespace Src\Index;

require_once '../vendor/autoload.php';

use Funct\Strings;
use Funct\Collection;
use function Functional\{
    reduce_left,
    map,
};

function capitalize($str)
{
    if ($str === '') {
        return '';
    }
    return strtoupper($str[0]) . substr($str, 1);
}

print_r(capitalize("hello"));
