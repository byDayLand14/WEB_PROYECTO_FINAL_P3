<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bdradio_fm';
$charset = 'utf8mb4';

$dnsWithoutDb = "mysql:host=$host;charset=$charset";
$dns = "mysql:host=$host;dbname=$database;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dns, $user, $password, $opciones);
    // Auto-migraciones de columnas para portadas de discos y fotos de grupos
    try { $pdo->exec("ALTER TABLE disco ADD COLUMN imagen_url VARCHAR(255) NULL AFTER formato;"); } catch (Throwable $ignored) {}
    try { $pdo->exec("ALTER TABLE grupo ADD COLUMN imagen_url VARCHAR(255) NULL AFTER anio_formacion;"); } catch (Throwable $ignored) {}
    
    // Forzar actualización de nombre para asegurar que la sesión nunca muestre datos de prueba anteriores
    $pdo->exec("UPDATE usuarios SET nombre = 'Administrador FM' WHERE usuario = 'admin';");
    $pdo->exec("UPDATE discjockey SET nombre = 'Alejandro Vega Morales', apodo_dj = 'DJ Alex Wave' WHERE id = 1;");
} catch (PDOException $e) {
    try {
        $pdoInit = new PDO($dnsWithoutDb, $user, $password, $opciones);
        $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo = new PDO($dns, $user, $password, $opciones);
        
        $sqlPath = __DIR__ . '/../init_db/init_radio_fm.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            $pdo->exec($sqlContent);
        }
    } catch (PDOException $ex) {
        if (headers_sent()) {
            die("Error de conexión a la base de datos: " . htmlspecialchars($ex->getMessage()));
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Error de conexión a MySQL: ' . $ex->getMessage()
        ]);
        exit;
    }
}