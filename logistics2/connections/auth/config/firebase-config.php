<?php
// Outputs window.FIREBASE_CONFIG for client-side Firebase initialization
// Sources values from environment variables or falls back to parsing project .env
// Path assumptions: this file lives in logistics2/connections/auth/config/
// .env is at project root: c:/xampp/htdocs/PM-TNVS/.env

header('Content-Type: application/javascript; charset=UTF-8');

$envPath = __DIR__ . '/../../../../.env';
$keys = [
  'FIREBASE_API_KEY',
  'FIREBASE_AUTH_DOMAIN',
  'FIREBASE_PROJECT_ID',
  'FIREBASE_STORAGE_BUCKET',
  'FIREBASE_MESSAGING_SENDER_ID',
  'FIREBASE_APP_ID',
];

$vals = [];
foreach ($keys as $k) {
    $v = getenv($k);
    if ($v !== false && $v !== '') {
        $vals[$k] = $v;
    }
}

// Minimal .env parser (KEY=VALUE per line), used if env vars are not set
if ((!isset($vals['FIREBASE_API_KEY']) || $vals['FIREBASE_API_KEY'] === '') && is_readable($envPath)) {
    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        // strip surrounding quotes
        if (strlen($v) >= 2 && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
            $v = substr($v, 1, -1);
        }
        if (in_array($k, $keys, true)) {
            $vals[$k] = $v;
        }
    }
}

$clientCfg = [
  'apiKey' => $vals['FIREBASE_API_KEY'] ?? '',
  'authDomain' => $vals['FIREBASE_AUTH_DOMAIN'] ?? '',
  'projectId' => $vals['FIREBASE_PROJECT_ID'] ?? '',
  'storageBucket' => $vals['FIREBASE_STORAGE_BUCKET'] ?? '',
  'messagingSenderId' => $vals['FIREBASE_MESSAGING_SENDER_ID'] ?? '',
  'appId' => $vals['FIREBASE_APP_ID'] ?? '',
];

// Emit JS snippet
echo 'window.FIREBASE_CONFIG = ' . json_encode($clientCfg, JSON_UNESCAPED_SLASHES) . ';';
