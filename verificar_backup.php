<?php

$dbPath = 'C:/Users/Admin/AppData/Roaming/skynetwork/database/skynetwork.db';

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo PHP_EOL;
    echo "========================================" . PHP_EOL;
    echo "BASE PERSISTENTE SKYNETWORK" . PHP_EOL;
    echo $dbPath . PHP_EOL;
    echo "========================================" . PHP_EOL;

    $stmt = $db->query("
        SELECT id, nombre
        FROM clientes
        WHERE nombre LIKE '%PRUEBA BACKUP%'
        ORDER BY id ASC
    ");

    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clientes)) {
        echo "No se encontraron clientes de prueba." . PHP_EOL;
    } else {
        foreach ($clientes as $cliente) {
            echo $cliente['id']
                . " - "
                . $cliente['nombre']
                . PHP_EOL;
        }
    }

    echo "========================================" . PHP_EOL;
    echo "FIN DE VERIFICACION" . PHP_EOL;
    echo "========================================" . PHP_EOL;

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}