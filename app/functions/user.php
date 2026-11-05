<?php

/**
 * Retorna o IP do usuário
 * @return string
 */
function getUserIP()
{
    // MDH_USER_IP e REMOTE_ADDR normalmente trazem um único endereço.
    $direct = $_SERVER['MDH_USER_IP'] ?? null;
    if (is_string($direct) && filter_var(trim($direct), FILTER_VALIDATE_IP)) {
        return trim($direct);
    }

    // X-Forwarded-For pode conter uma lista com vários proxies; grave só o primeiro IP válido.
    $headers = [
        $_SERVER['HTTP_CLIENT_IP'] ?? null,
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        $_SERVER['HTTP_X_FORWARDED'] ?? null,
        $_SERVER['HTTP_FORWARDED_FOR'] ?? null,
        $_SERVER['HTTP_FORWARDED'] ?? null,
    ];
    foreach ($headers as $header) {
        if (!is_string($header) || trim($header) === '') {
            continue;
        }
        foreach (preg_split('/[,;]/', $header) as $candidate) {
            $candidate = trim($candidate);
            if (preg_match('/^for=(.*)$/i', $candidate, $m)) {
                $candidate = trim($m[1], " \"\t\n\r\0\x0B[]");
            } else {
                $candidate = trim($candidate, " \"\t\n\r\0\x0B[]");
            }
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
    }

    $remote = $_SERVER['REMOTE_ADDR'] ?? null;
    return is_string($remote) && filter_var(trim($remote), FILTER_VALIDATE_IP)
        ? trim($remote)
        : null;
}
