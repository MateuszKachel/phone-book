<?php

function dd(mixed ...$variable)
{
    var_dump(...$variable);
    exit;
}

function e(?string $text): string
{
    if (is_null($text)) {
        return '';
    }

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
