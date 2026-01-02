<?php

function dd(...$args): never {
    foreach ($args as $a) {
        var_dump($a);
    }
    exit(1);
}

?>
