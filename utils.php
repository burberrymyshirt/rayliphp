<?php

declare(strict_types=1);

function dd(...$args): never {
    dump(...$args);
    exit(1);
}

function dump(...$args): void {
    foreach ($args as $a) {
        var_dump($a);
    }
}

?>
