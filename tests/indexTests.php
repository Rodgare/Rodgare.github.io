<?php

namespace Tests\IndexTests;

require_once '../vendor/autoload.php';

use Webmozart\Assert\Assert;

use Src\Index;

Assert::eq(Index\capitalize('hello'), 'Hello');
Assert::eq(Index\capitalize(''), '');

echo '<br>Все тесты пройдены';