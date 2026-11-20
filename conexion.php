<?php
$host = "sql313.infinityfree.com";
$dbname = "if0_43488542_agenda_db";
$username = "if0_43488542";
$password = "INGRESA_SU_CONTRASEÑA_AQUI";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}
?>