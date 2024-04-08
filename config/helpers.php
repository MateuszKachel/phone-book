<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(mixed ...$variable): void
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

function displayErrors(array $errors): string
{
    $html = '<div class="alert alert-danger mt-4">';
    foreach ($errors as $error) {
        $html .= "<p class='mb-0'>$error</p>";
    }
    $html .= '</div>';

    return $html;
}


function csrfToken(string $formId)
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return hash_hmac('sha256', $formId, $_SESSION['csrf_token']);
}
