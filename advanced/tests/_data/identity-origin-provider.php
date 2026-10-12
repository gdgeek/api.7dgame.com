<?php

// Local HTTP fixture: echo only origin metadata and the synthetic test body.
header('Content-Type: application/json');
echo json_encode([
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? null,
    'referer' => $_SERVER['HTTP_REFERER'] ?? null,
    'payload' => json_decode(file_get_contents('php://input'), true),
]);
