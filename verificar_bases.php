<?php

$paths = [
    'DESARROLLO' => 'C:\wamp64\www\isp-management\database\skynetwork.db',

    'APPDATA (BASE PERSISTENTE)' =>
        getenv('APPDATA')
        . DIRECTORY_SEPARATOR
        . 'skynetwork'
        . DIRECTORY_SEPARATOR
        . 'database'
        . DIRECTORY_SEPARATOR
        . 'skynetwork.db',

    'INSTALACION SKYNETWORK' =>
        getenv('LOCALAPPDATA')
        . DIRECTORY_SEPARATOR
        . 'Programs'
        . DIRECTORY_SEPARATOR
        . 'SkyNetwork'
        . DIRECTORY_SEPARATOR
        . 'resources'
        . DIRECTORY_SEPARATOR
        . 'backend'
        . DIRECTORY_SEPARATOR
        . 'database'
        . DIRECTORY_SEPARATOR
        . 'skynetwork.db'
];

foreach ($paths as $label => $path) {

    echo PHP_EOL;
    echo "========================================" . PHP_EOL;
    echo $label . PHP_EOL;
    echo $path . PHP_EOL;
    echo "========================================" . PHP_EOL;

    if (!file_exists($path)) {

        echo "❌ BASE NO ENCONTRADA" . PHP_EOL;
        continue;

    }

    try {

        $db = new PDO(
            'sqlite:' . $path
        );

        $stmt = $db->query(
            'SELECT id, nombre FROM clientes ORDER BY id'
        );

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        if (empty($rows)) {

            echo "Sin clientes." . PHP_EOL;

        } else {

            foreach ($rows as $row) {

                echo $row['id']
                    . ' - '
                    . $row['nombre']
                    . PHP_EOL;

            }

        }

    } catch (Throwable $e) {

        echo "❌ ERROR: "
            . $e->getMessage()
            . PHP_EOL;

    }

}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo "FIN DE VERIFICACION" . PHP_EOL;
echo "========================================" . PHP_EOL;