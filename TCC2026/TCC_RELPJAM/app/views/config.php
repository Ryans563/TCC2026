<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Segurança HTTP
|--------------------------------------------------------------------------
*/

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

/*
|--------------------------------------------------------------------------
| Sessão Segura
|--------------------------------------------------------------------------
*/

ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');

if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

/*
|--------------------------------------------------------------------------
| Banco de Dados - Supabase PostgreSQL
|--------------------------------------------------------------------------
*/

$host = 'aws-1-us-west-2.pooler.supabase.com';
$port = '5432';
$db   = 'postgres';
$user = 'postgres.enkfnnaebiiqyycmegyp';
$pass = 'KU74wvnR7Zd4x6VeEoaZ';

try {

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$db};sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {

    die('Erro na conexão com o banco de dados: ' . $e->getMessage());

}