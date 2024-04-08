<?php

// Do not store a cookie to permanent storage
ini_set('session.cookie_lifetime', 0);

// Prevent Session ID from being passed through  URLs
ini_set('session.use_only_cookies', 1);

// Prevents the session module to use an uninitialized session ID
// (the session module only accepts valid session IDs generated by the session module)
ini_set('session.use_strict_mode', 1);

// Prevent javascript XSS attacks (refuses access to the session cookie from JavaScript)
ini_set('session.cookie_httponly', 1);

if ($_SERVER['SERVER_PORT'] === '443') {
    // Allow access to the session ID cookie only when the protocol is HTTPS
    ini_set('session.cookie_secure', 1);
}

// Mitigates CSRF (Cross Site Request Forgery) attacks.
ini_set('session.cookie_samesite', "Lax");