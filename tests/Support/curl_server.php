<?php

/**
 * Roteador do servidor embutido do PHP usado pelo CurlTransportTest.
 * Uso: php -S 127.0.0.1:<porta> tests/Support/curl_server.php
 */

$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

header('Content-Type: application/json');

if ($path === '/api/echo') {
    $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

    echo json_encode(['data' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'query' => $_GET,
        'headers' => $headers,
        'body' => file_get_contents('php://input'),
    ]]);

    return;
}

if (preg_match('#^/api/status/(\d{3})$#', $path, $m) === 1) {
    http_response_code((int) $m[1]);
    header('Retry-After: 0');
    echo json_encode(['message' => 'status ' . $m[1]]);

    return;
}

if ($path === '/api/redirect') {
    header('Location: /api/echo', true, 302);

    return;
}

if ($path === '/api/big') {
    echo str_repeat('a', 2 * 1024 * 1024);

    return;
}

if ($path === '/api/header-flood') {
    // ~200 KB de headers: um servidor malicioso tentando esgotar a memória do cliente
    for ($i = 0; $i < 200; $i++) {
        header('X-Flood-' . $i . ': ' . str_repeat('a', 1000));
    }
    echo '{}';

    return;
}

if ($path === '/api/slow') {
    sleep(2);
    echo '{}';

    return;
}

http_response_code(404);
echo json_encode(['message' => 'not found']);
