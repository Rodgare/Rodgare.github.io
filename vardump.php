<?php

namespace Vardump;

function vardump($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function showMatrix($arr)
{
    echo implode("<br>", array_map(fn($n) => implode(" ", $n), $arr));
}