<?php

class Database
{
    private string $dbPath;
    private ?PDO $conn = null;

    public function __construct()
    {
        /*
         * Ruta completamente portable.
         *
         * Funciona sin importar dónde se instale SkyNetwork:
         *
         * C:\Program Files\SkyNetwork\
         * D:\Aplicaciones\SkyNetwork\
         * C:\Users\Usuario\Desktop\SkyNetwork\
         *
         * Siempre busca:
         *
         * ../database/skynetwork.db
         *
         * relativo a este archivo.
         */
        $this->dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'skynetwork.db';
    }

    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {

            /*
             * Verificar que la base exista.
             */
            if (!file_exists($this->dbPath)) {
                throw new RuntimeException(
                    'No se encontró la base de datos SQLite en: '
                    . $this->dbPath
                );
            }

            $this->conn = new PDO(
                'sqlite:' . $this->dbPath
            );

            /*
             * Mostrar errores mediante excepciones.
             */
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            /*
             * Resultados como arrays asociativos.
             */
            $this->conn->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            /*
             * Activar claves foráneas.
             */
            $this->conn->exec(
                'PRAGMA foreign_keys = ON'
            );

            /*
             * Mejor rendimiento para lecturas y escrituras.
             */
            $this->conn->exec(
                'PRAGMA journal_mode = WAL'
            );

            /*
             * Esperar si la base está ocupada.
             */
            $this->conn->exec(
                'PRAGMA busy_timeout = 5000'
            );

        } catch (Throwable $exception) {

            throw new RuntimeException(
                'Error de conexión a SQLite: '
                . $exception->getMessage(),
                0,
                $exception
            );
        }

        return $this->conn;
    }

    public function testConnection(): bool
    {
        try {

            return $this->getConnection() instanceof PDO;

        } catch (Throwable $e) {

            return false;
        }
    }

    public function getDatabasePath(): string
    {
        return $this->dbPath;
    }
}