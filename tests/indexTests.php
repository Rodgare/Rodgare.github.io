<?php

namespace Tests\IndexTests;

require_once '../vendor/autoload.php';

use Src\Index;

if (Index\capitalize('hello') !== 'Hello') {
    throw new \Exception("Ожидалось Hello");
}

if (Index\capitalize('') !== '') {
    throw new \Exception("С пустой строкой проблема");
}

echo '<br>Все тесты пройдены';