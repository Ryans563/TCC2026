<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

/* =========================
   SUPABASE CONFIG
========================= */
$envPath = __DIR__ . '/../../.env';

if (!file_exists($envPath)) {
    die("ERRO: .env não encontrado em: " . $envPath);
}

$env = parse_ini_file($envPath);


if ($env === false) {
    die("ERRO: .env inválido");
}

// Variáveis da API do Supabase
$SUPABASE_URL = $env['SUPABASE_URL'] ?? null;
$SUPABASE_KEY = $env['SUPABASE_KEY'] ?? null;

/* =========================
   BANCO
========================= */
$host = $env['DB_HOST'] ?? null;
$port = $env['DB_PORT'] ?? '5432';
$db   = $env['DB_NAME'] ?? null;
$user = $env['DB_USER'] ?? null;
$pass = $env['DB_PASS'] ?? null;

if (!$host || !$db || !$user || !$pass) {
    die("ERRO: Variáveis do banco não encontradas no .env");
}

try {

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$db};sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {
    die("Erro DB: " . $e->getMessage());
}