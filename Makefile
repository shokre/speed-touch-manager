
al:
	composer.phar dump-autoload -o

ajd:
	composer.phar dump-autoload -o
	php tst.php

test:
	phpunit --bootstrap vendor/autoload.php tests/PortMapMatchTest.php
