const {
    app,
    BrowserWindow,
    dialog,
    ipcMain
} = require('electron');

const path = require('path');
const fs = require('fs');

const {
    spawn,
    spawnSync
} = require('child_process');

const net = require('net');

const {
    autoUpdater
} = require('electron-updater');


let mainWindow = null;
let phpProcess = null;


/* =========================================================
   ESTADO GLOBAL
========================================================= */

let isQuitting = false;
let shutdownInProgress = false;
let shutdownBackupCompleted = false;
let updateBackupCompleted = false;
let allowWindowClose = false;
let restoreInProgress = false;
let importInProgress = false;


const HOST = '127.0.0.1';
const PORT = 8080;


/* =========================================================
   CONFIGURACIÓN DE RESPALDOS
========================================================= */

const MAX_BACKUPS = 30;

const CREATE_BACKUP_ON_EXIT = true;


/* =========================================================
   AUTO UPDATER
========================================================= */

autoUpdater.autoDownload = false;
autoUpdater.autoInstallOnAppQuit = true;


/* =========================================================
   RUTAS DEL PROYECTO
========================================================= */

function getProjectPath() {

    if (app.isPackaged) {

        return path.join(
            process.resourcesPath,
            'backend'
        );

    }

    return path.resolve(
        __dirname,
        '..'
    );

}


function getUserDataPath() {

    return app.getPath(
        'userData'
    );

}


function getPersistentDatabasePath() {

    return path.join(
        getUserDataPath(),
        'database',
        'skynetwork.db'
    );

}


function getBackupsDirectory() {

    return path.join(
        getUserDataPath(),
        'backups'
    );

}


function getLogsDirectory() {

    return path.join(
        getUserDataPath(),
        'logs'
    );

}


function getTemplateDatabasePath() {

    return path.join(
        getProjectPath(),
        'database',
        'skynetwork.db'
    );

}


function getPhpPath() {

    return path.join(
        getProjectPath(),
        'php',
        'php.exe'
    );

}


function getIconPath() {

    if (app.isPackaged) {

        return path.join(
            process.resourcesPath,
            'backend',
            'assets',
            'icon.ico'
        );

    }

    return path.join(
        __dirname,
        '..',
        'assets',
        'icon.ico'
    );

}


/* =========================================================
   SISTEMA DE LOG PERSISTENTE
========================================================= */

function writeLog(message, type = 'INFO') {

    try {

        const logsDirectory =
            getLogsDirectory();


        if (!fs.existsSync(logsDirectory)) {

            fs.mkdirSync(
                logsDirectory,
                {
                    recursive: true
                }
            );

        }


        const now =
            new Date();


        const timestamp =
            now.toISOString()
                .replace('T', ' ')
                .replace('Z', '');


        const logLine =
            `[${timestamp}] [${type}] ${message}\n`;


        const logPath =
            path.join(
                logsDirectory,
                'skynetwork.log'
            );


        fs.appendFileSync(
            logPath,
            logLine,
            'utf8'
        );


        if (type === 'ERROR') {

            console.error(logLine.trim());

        } else if (type === 'WARN') {

            console.warn(logLine.trim());

        } else {

            console.log(logLine.trim());

        }

    } catch (error) {

        console.error(
            'No fue posible escribir log:',
            error.message
        );

    }

}


/* =========================================================
   UTILIDADES DE RESPALDO
========================================================= */

function getBackupTimestamp() {

    const now = new Date();

    const year =
        now.getFullYear();

    const month =
        String(
            now.getMonth() + 1
        ).padStart(2, '0');

    const day =
        String(
            now.getDate()
        ).padStart(2, '0');

    const hours =
        String(
            now.getHours()
        ).padStart(2, '0');

    const minutes =
        String(
            now.getMinutes()
        ).padStart(2, '0');

    const seconds =
        String(
            now.getSeconds()
        ).padStart(2, '0');


    return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;

}


function validateSqliteDatabase(databasePath, label = 'La base de datos') {
    if (!fs.existsSync(databasePath)) {
        throw new Error(`${label} no existe.`);
    }

    if (fs.statSync(databasePath).size === 0) {
        throw new Error(`${label} está vacío.`);
    }

    const phpPath = getPhpPath();
    if (!fs.existsSync(phpPath)) {
        throw new Error('No se encontró PHP Portable para validar SQLite.');
    }

    const escapedDatabasePath = databasePath
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");

    const validationCode = `
        try {
            $pdo = new PDO('sqlite:${escapedDatabasePath}');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();
            if ($result !== 'ok') throw new Exception('SQLite integrity_check failed: ' . $result);
            echo 'SQLITE_VALID';
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    `;

    const result = spawnSync(phpPath, ['-r', validationCode], {
        windowsHide: true,
        encoding: 'utf8',
        timeout: 30000
    });

    if (result.error) throw result.error;
    if (result.status !== 0) {
        throw new Error(`${label} no pasó la validación SQLite: ${result.stderr || `Código ${result.status}`}`);
    }

    return true;
}


function validateSkyNetworkDatabaseStructure(databasePath, label = 'La base de datos') {
    const phpPath = getPhpPath();
    if (!fs.existsSync(phpPath)) {
        throw new Error('No se encontró PHP Portable para validar la estructura de la base.');
    }

    const escapedDatabasePath = databasePath
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");

    // Estas tablas y columnas son el contrato mínimo de una instalación SkyNetwork.
    // No se exige una versión exacta para conservar compatibilidad con bases anteriores.
    const validationCode = `
        try {
            $pdo = new PDO('sqlite:${escapedDatabasePath}');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $required = [
                'clientes' => ['id', 'nombre'],
                'equipos' => ['id', 'cliente_id'],
                'pagos' => ['id', 'cliente_id'],
                'usuarios' => ['id']
            ];
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")
                ->fetchAll(PDO::FETCH_COLUMN);
            foreach ($required as $table => $columns) {
                if (!in_array($table, $tables, true)) {
                    throw new Exception("Falta la tabla requerida: {$table}.");
                }
                $actualColumns = $pdo->query("PRAGMA table_info('" . str_replace("'", "''", $table) . "')")
                    ->fetchAll(PDO::FETCH_COLUMN, 1);
                foreach ($columns as $column) {
                    if (!in_array($column, $actualColumns, true)) {
                        throw new Exception("Falta la columna requerida {$table}.{$column}.");
                    }
                }
            }
            echo 'SKYNETWORK_SCHEMA_VALID';
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    `;

    const result = spawnSync(phpPath, ['-r', validationCode], {
        windowsHide: true,
        encoding: 'utf8',
        timeout: 30000
    });

    if (result.error) throw result.error;
    if (result.status !== 0) {
        throw new Error(`${label} no es una base compatible con SkyNetwork: ${result.stderr || `Código ${result.status}`}`);
    }

    return true;
}


function getBackupPathFromFile(backupFile) {
    if (
        typeof backupFile !== 'string'
        || path.basename(backupFile) !== backupFile
        || !backupFile.startsWith('skynetwork-')
        || !backupFile.endsWith('.db')
    ) {
        throw new Error('El backup seleccionado no es válido.');
    }

    const backupPath = path.join(getBackupsDirectory(), backupFile);
    if (!fs.existsSync(backupPath) || !fs.statSync(backupPath).isFile()) {
        throw new Error('El archivo de backup seleccionado ya no existe.');
    }

    return backupPath;
}


function listDatabaseBackups() {
    const backupsDirectory = getBackupsDirectory();
    if (!fs.existsSync(backupsDirectory)) return [];

    return fs.readdirSync(backupsDirectory)
        .filter((file) => file.startsWith('skynetwork-') && file.endsWith('.db'))
        .map((file) => {
            const backupPath = path.join(backupsDirectory, file);
            const stats = fs.statSync(backupPath);
            const metadataPath = backupPath.replace(/\.db$/, '.json');
            let metadata = {};

            try {
                if (fs.existsSync(metadataPath)) {
                    metadata = JSON.parse(fs.readFileSync(metadataPath, 'utf8'));
                }
            } catch (error) {
                writeLog(`No fue posible leer metadata de ${file}: ${error.message}`, 'WARN');
            }

            return {
                file,
                reason: typeof metadata.reason === 'string' ? metadata.reason : 'Desconocido',
                createdAt: metadata.createdAt || stats.mtime.toISOString(),
                size: stats.size,
                validation: metadata.validation || 'Sin metadata'
            };
        })
        .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
}


/* =========================================================
   ESPERAR A QUE PHP TERMINE
========================================================= */

function waitForPhpProcessToExit(
    timeout = 10000
) {

    return new Promise(
        (resolve) => {

            if (!phpProcess) {

                resolve(true);

                return;

            }


            if (phpProcess.killed) {

                phpProcess = null;

                resolve(true);

                return;

            }


            let resolved = false;


            const finish = () => {

                if (resolved) {

                    return;

                }

                resolved = true;

                phpProcess = null;

                resolve(true);

            };


            phpProcess.once(
                'exit',
                finish
            );


            phpProcess.once(
                'close',
                finish
            );


            setTimeout(
                () => {

                    if (!resolved) {

                        writeLog(
                            'Tiempo de espera agotado al detener PHP.',
                            'WARN'
                        );

                        finish();

                    }

                },
                timeout
            );

        }
    );

}


/* =========================================================
   DETENER PHP COMPLETAMENTE
========================================================= */

async function stopPhpServer() {

    if (!phpProcess) {

        writeLog(
            'No existe proceso PHP activo.'
        );

        return true;

    }


    try {

        writeLog(
            `Deteniendo servidor PHP. PID: ${phpProcess.pid}`
        );


        const waitPromise =
            waitForPhpProcessToExit(
                10000
            );


        /*
         * En Windows usamos taskkill para asegurar
         * que php.exe realmente termine.
         */
        if (
            process.platform === 'win32'
            &&
            phpProcess.pid
        ) {

            try {

                spawnSync(

                    'taskkill',

                    [
                        '/PID',
                        String(
                            phpProcess.pid
                        ),
                        '/T',
                        '/F'
                    ],

                    {
                        windowsHide: true,
                        timeout: 10000
                    }

                );

            } catch (error) {

                writeLog(
                    'Error usando taskkill: '
                    + error.message,
                    'WARN'
                );

            }

        } else {

            try {

                phpProcess.kill();

            } catch (error) {

                writeLog(
                    'Error cerrando PHP: '
                    + error.message,
                    'WARN'
                );

            }

        }


        await waitPromise;


        writeLog(
            'Servidor PHP detenido correctamente.'
        );


        return true;

    } catch (error) {

        writeLog(
            'Error deteniendo PHP: '
            + error.message,
            'ERROR'
        );


        return false;

    }

}


/* =========================================================
   CHECKPOINT SQLITE
========================================================= */

function checkpointDatabase() {

    const phpPath =
        getPhpPath();

    const databasePath =
        getPersistentDatabasePath();


    writeLog(
        'Iniciando SQLite WAL checkpoint.'
    );


    if (!fs.existsSync(phpPath)) {

        writeLog(
            'PHP Portable no encontrado: '
            + phpPath,
            'ERROR'
        );

        return false;

    }


    if (!fs.existsSync(databasePath)) {

        writeLog(
            'Base de datos no encontrada: '
            + databasePath,
            'ERROR'
        );

        return false;

    }


    try {

        const escapedDatabasePath =
            databasePath
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'");


        const phpCode = `
            try {

                $pdo = new PDO(
                    'sqlite:${escapedDatabasePath}'
                );

                $pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                $pdo->exec(
                    'PRAGMA busy_timeout = 10000'
                );

                $result = $pdo->query(
                    'PRAGMA wal_checkpoint(TRUNCATE)'
                );

                echo 'CHECKPOINT_OK';

            } catch (Throwable $e) {

                fwrite(
                    STDERR,
                    $e->getMessage()
                );

                exit(1);

            }
        `;


        const result = spawnSync(

            phpPath,

            [
                '-r',
                phpCode
            ],

            {

                windowsHide: true,

                encoding: 'utf8',

                timeout: 30000

            }

        );


        if (
            result.error
        ) {

            throw result.error;

        }


        if (
            result.status === 0
        ) {

            writeLog(
                'SQLite WAL checkpoint completado correctamente.'
            );

            return true;

        }


        writeLog(
            'Error en WAL checkpoint: '
            + (
                result.stderr
                || 'Código: ' + result.status
            ),
            'ERROR'
        );


        return false;

    } catch (error) {

        writeLog(
            'Excepción realizando WAL checkpoint: '
            + error.message,
            'ERROR'
        );

        return false;

    }

}


/* =========================================================
   LIMPIAR BACKUPS ANTIGUOS
========================================================= */

function cleanupOldBackups(protectedBackupFiles = []) {

    const backupsDirectory =
        getBackupsDirectory();


    if (!fs.existsSync(backupsDirectory)) {

        return;

    }


    try {

        const backups = fs.readdirSync(
            backupsDirectory
        )
            .filter(
                (file) =>
                    file.startsWith(
                        'skynetwork-'
                    )
                    &&
                    file.endsWith(
                        '.db'
                    )
            )
            .map(
                (file) => {

                    const fullPath =
                        path.join(
                            backupsDirectory,
                            file
                        );

                    return {

                        file,

                        fullPath,

                        stats:
                            fs.statSync(
                                fullPath
                            )

                    };

                }
            )
            .sort(
                (a, b) =>
                    b.stats.mtimeMs
                    -
                    a.stats.mtimeMs
            );


        if (
            backups.length > MAX_BACKUPS
        ) {

            const oldBackups =
                backups.slice(
                    MAX_BACKUPS
                );


            for (
                const backup of oldBackups
            ) {

                if (protectedBackupFiles.includes(backup.file)) {

                    writeLog(
                        'Se conserva temporalmente el backup seleccionado: '
                        + backup.file
                    );

                    continue;

                }

                try {

                    fs.unlinkSync(
                        backup.fullPath
                    );


                    const metadataPath =
                        backup.fullPath.replace(
                            '.db',
                            '.json'
                        );


                    if (
                        fs.existsSync(
                            metadataPath
                        )
                    ) {

                        fs.unlinkSync(
                            metadataPath
                        );

                    }


                    writeLog(
                        'Backup antiguo eliminado: '
                        + backup.file
                    );

                } catch (error) {

                    writeLog(
                        'No se pudo eliminar backup antiguo: '
                        + backup.file
                        + ' | '
                        + error.message,
                        'WARN'
                    );

                }

            }

        }

    } catch (error) {

        writeLog(
            'Error limpiando backups: '
            + error.message,
            'WARN'
        );

    }

}


/* =========================================================
   CREAR BACKUP
========================================================= */

function createDatabaseBackup(
    reason = 'automatic',
    protectedBackupFiles = []
) {

    const databasePath =
        getPersistentDatabasePath();

    const backupsDirectory =
        getBackupsDirectory();


    try {

        writeLog(
            '========================================'
        );

        writeLog(
            'INICIANDO RESPALDO DE BASE DE DATOS'
        );

        writeLog(
            'Motivo: ' + reason
        );

        writeLog(
            'Modo empaquetado: '
            + app.isPackaged
        );

        writeLog(
            'Base origen: '
            + databasePath
        );

        writeLog(
            'Carpeta backups: '
            + backupsDirectory
        );


        /*
         * VALIDAR BASE
         */
        if (!fs.existsSync(databasePath)) {

            throw new Error(
                'No existe la base persistente: '
                + databasePath
            );

        }


        /*
         * VALIDAR PHP
         */
        const phpPath =
            getPhpPath();


        if (!fs.existsSync(phpPath)) {

            throw new Error(
                'No se encontró PHP Portable: '
                + phpPath
            );

        }


        /*
         * CREAR DIRECTORIO
         */
        if (!fs.existsSync(backupsDirectory)) {

            fs.mkdirSync(
                backupsDirectory,
                {
                    recursive: true
                }
            );

            writeLog(
                'Directorio de backups creado.'
            );

        }


        /*
         * IMPORTANTE:
         *
         * En este punto PHP ya debe estar detenido.
         * Esto permite consolidar correctamente WAL
         * antes de copiar la base.
         */
        const checkpointSuccess =
            checkpointDatabase();


        if (!checkpointSuccess) {

            throw new Error(
                'No fue posible consolidar SQLite WAL antes del backup.'
            );

        }


        /*
         * GENERAR NOMBRE
         */
        const timestamp =
            getBackupTimestamp();


        const backupFileName =
            `skynetwork-${reason}-${timestamp}.db`;


        const backupPath =
            path.join(
                backupsDirectory,
                backupFileName
            );


        /*
         * ELIMINAR ARCHIVO PREVIO SI EXISTIERA
         */
        if (fs.existsSync(backupPath)) {

            fs.unlinkSync(
                backupPath
            );

        }


        /*
         * COPIAR BASE
         */
        writeLog(
            'Copiando base hacia: '
            + backupPath
        );


        fs.copyFileSync(
            databasePath,
            backupPath
        );


        /*
         * VERIFICAR EXISTENCIA
         */
        if (!fs.existsSync(backupPath)) {

            throw new Error(
                'El archivo de respaldo no fue creado.'
            );

        }


        /*
         * ACTUALIZAR FECHA
         */
        const now =
            new Date();


        fs.utimesSync(
            backupPath,
            now,
            now
        );


        /*
         * VALIDAR TAMAÑOS
         */
        const originalSize =
            fs.statSync(
                databasePath
            ).size;


        const backupSize =
            fs.statSync(
                backupPath
            ).size;


        if (backupSize === 0) {

            throw new Error(
                'El respaldo fue creado vacío.'
            );

        }


        /*
         * VALIDAR QUE SQLITE PUEDA ABRIR EL BACKUP
         */
        const escapedBackupPath =
            backupPath
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'");


        const validationCode = `
            try {

                $pdo = new PDO(
                    'sqlite:${escapedBackupPath}'
                );

                $pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                $stmt = $pdo->query(
                    'PRAGMA integrity_check'
                );

                $result = $stmt->fetchColumn();

                if ($result !== 'ok') {

                    throw new Exception(
                        'SQLite integrity_check failed: '
                        . $result
                    );

                }

                echo 'BACKUP_VALID';

            } catch (Throwable $e) {

                fwrite(
                    STDERR,
                    $e->getMessage()
                );

                exit(1);

            }
        `;


        const validationResult =
            spawnSync(

                phpPath,

                [
                    '-r',
                    validationCode
                ],

                {

                    windowsHide: true,

                    encoding: 'utf8',

                    timeout: 30000

                }

            );


        if (
            validationResult.error
        ) {

            throw validationResult.error;

        }


        if (
            validationResult.status !== 0
        ) {

            throw new Error(
                'El backup fue creado pero no pasó la validación SQLite: '
                + (
                    validationResult.stderr
                    || 'Código '
                    + validationResult.status
                )
            );

        }


        /*
         * CREAR METADATA
         */
        const metadataPath =
            backupPath.replace(
                '.db',
                '.json'
            );


        const metadata = {

            application:
                'SkyNetwork',

            version:
                app.getVersion(),

            reason,

            createdAt:
                new Date().toISOString(),

            mode:
                app.isPackaged
                    ? 'production'
                    : 'development',

            database:
                'skynetwork.db',

            databasePath,

            backupPath,

            originalSize,

            backupSize,

            backupFile:
                backupFileName,

            checkpoint:
                'WAL TRUNCATE',

            validation:
                'SQLite integrity_check OK'

        };


        fs.writeFileSync(

            metadataPath,

            JSON.stringify(
                metadata,
                null,
                4
            ),

            'utf8'

        );


        writeLog(
            'BACKUP CREADO CORRECTAMENTE'
        );

        writeLog(
            'Archivo: '
            + backupPath
        );

        writeLog(
            'Tamaño origen: '
            + originalSize
            + ' bytes'
        );

        writeLog(
            'Tamaño backup: '
            + backupSize
            + ' bytes'
        );

        writeLog(
            'Validación SQLite: OK'
        );

        writeLog(
            '========================================'
        );


        cleanupOldBackups(protectedBackupFiles);


        return {

            success: true,

            backupPath,

            backupFileName,

            originalSize,

            backupSize

        };

    } catch (error) {

        writeLog(
            'ERROR CREANDO BACKUP: '
            + error.message,
            'ERROR'
        );


        return {

            success: false,

            error:
                error.message

        };

    }

}


async function restoreDatabaseBackup(backupFile) {
    const databasePath = getPersistentDatabasePath();
    let safetyBackupPath = null;
    let databaseWasReplaced = false;
    let phpWasStopped = false;

    try {
        writeLog('========================================');
        writeLog('INICIANDO RESTAURACIÓN DE BASE DE DATOS');
        writeLog(`Backup seleccionado: ${backupFile}`);
        writeLog(`Base destino persistente: ${databasePath}`);

        const backupPath = getBackupPathFromFile(backupFile);
        validateSqliteDatabase(backupPath, 'El backup seleccionado');
        writeLog('Backup seleccionado validado correctamente.');

        if (!fs.existsSync(databasePath)) {
            throw new Error('No existe la base persistente que se va a restaurar.');
        }

        if (!await stopPhpServer()) {
            throw new Error('No fue posible detener PHP antes de restaurar.');
        }

        phpWasStopped = true;

        const safetyBackup = createDatabaseBackup(
            'before-restore',
            [backupFile]
        );
        if (!safetyBackup.success) {
            throw new Error(`No fue posible crear el backup de seguridad: ${safetyBackup.error}`);
        }

        safetyBackupPath = safetyBackup.backupPath;
        writeLog(`Backup de seguridad creado: ${safetyBackupPath}`);

        const restoreTempPath = path.join(
            path.dirname(databasePath),
            `skynetwork.restore-${getBackupTimestamp()}.tmp`
        );

        try {
            fs.copyFileSync(backupPath, restoreTempPath);
            validateSqliteDatabase(restoreTempPath, 'La copia temporal de restauración');

            // PHP ya está detenido: no pueden quedar sidecars WAL/SHM de la base anterior.
            for (const sidecar of [`${databasePath}-wal`, `${databasePath}-shm`]) {
                if (fs.existsSync(sidecar)) fs.unlinkSync(sidecar);
            }

            fs.copyFileSync(restoreTempPath, databasePath);
            databaseWasReplaced = true;
            validateSqliteDatabase(databasePath, 'La base restaurada');
        } finally {
            if (fs.existsSync(restoreTempPath)) fs.unlinkSync(restoreTempPath);
        }

        writeLog('BASE RESTAURADA CORRECTAMENTE');
        writeLog(`Origen: ${backupPath}`);
        writeLog(`Destino persistente: ${databasePath}`);
        writeLog('Validación SQLite posterior: OK');
        writeLog('========================================');

        return {
            success: true,
            safetyBackupFile: safetyBackup.backupFileName,
            phpWasStopped
        };
    } catch (error) {
        writeLog(`ERROR RESTAURANDO BACKUP: ${error.message}`, 'ERROR');

        if (databaseWasReplaced && safetyBackupPath) {
            try {
                fs.copyFileSync(safetyBackupPath, databasePath);
                validateSqliteDatabase(databasePath, 'La recuperación automática');
                writeLog('Se recuperó automáticamente la base previa a la restauración.', 'WARN');
            } catch (recoveryError) {
                writeLog(`ERROR EN RECUPERACIÓN AUTOMÁTICA: ${recoveryError.message}`, 'ERROR');
            }
        }

        return { success: false, error: error.message, phpWasStopped };
    }
}


async function importExternalDatabase(sourcePath) {
    const databasePath = getPersistentDatabasePath();
    const temporaryPath = path.join(
        path.dirname(databasePath),
        `skynetwork.import-${getBackupTimestamp()}.tmp`
    );
    let safetyBackupPath = null;
    let databaseWasReplaced = false;
    let phpWasStopped = false;

    try {
        writeLog('========================================');
        writeLog('INICIANDO IMPORTACIÓN DE BASE DE DATOS');
        writeLog(`Archivo seleccionado: ${sourcePath}`);

        // La primera validación no modifica nada y permite rechazar archivos ajenos pronto.
        validateSqliteDatabase(sourcePath, 'El archivo seleccionado');
        validateSkyNetworkDatabaseStructure(sourcePath, 'El archivo seleccionado');

        fs.mkdirSync(path.dirname(databasePath), { recursive: true });
        fs.copyFileSync(sourcePath, temporaryPath);
        validateSqliteDatabase(temporaryPath, 'La copia temporal importada');
        validateSkyNetworkDatabaseStructure(temporaryPath, 'La copia temporal importada');

        if (!fs.existsSync(databasePath)) {
            throw new Error('No existe la base persistente que se va a reemplazar.');
        }

        if (!await stopPhpServer()) {
            throw new Error('No fue posible detener PHP antes de importar la base.');
        }
        phpWasStopped = true;

        const safetyBackup = createDatabaseBackup('before-import');
        if (!safetyBackup.success) {
            throw new Error(`No fue posible crear el backup de seguridad: ${safetyBackup.error}`);
        }
        safetyBackupPath = safetyBackup.backupPath;

        // Sin PHP activo no hay escrituras ni sidecars WAL pendientes sobre la base destino.
        for (const sidecar of [`${databasePath}-wal`, `${databasePath}-shm`]) {
            if (fs.existsSync(sidecar)) fs.unlinkSync(sidecar);
        }

        fs.copyFileSync(temporaryPath, databasePath);
        databaseWasReplaced = true;
        validateSqliteDatabase(databasePath, 'La base importada');
        validateSkyNetworkDatabaseStructure(databasePath, 'La base importada');

        writeLog('BASE IMPORTADA CORRECTAMENTE');
        writeLog(`Backup preventivo: ${safetyBackupPath}`);
        writeLog('========================================');
        return { success: true, safetyBackupFile: safetyBackup.backupFileName, phpWasStopped };
    } catch (error) {
        writeLog(`ERROR IMPORTANDO BASE: ${error.message}`, 'ERROR');

        if (databaseWasReplaced && safetyBackupPath) {
            try {
                fs.copyFileSync(safetyBackupPath, databasePath);
                validateSqliteDatabase(databasePath, 'La recuperación automática');
                writeLog('Se recuperó automáticamente la base previa a la importación.', 'WARN');
            } catch (recoveryError) {
                writeLog(`ERROR EN RECUPERACIÓN AUTOMÁTICA: ${recoveryError.message}`, 'ERROR');
            }
        }

        return { success: false, error: error.message, phpWasStopped };
    } finally {
        if (fs.existsSync(temporaryPath)) {
            try { fs.unlinkSync(temporaryPath); } catch (cleanupError) {
                writeLog(`No se pudo eliminar el archivo temporal de importación: ${cleanupError.message}`, 'WARN');
            }
        }
    }
}


/* =========================================================
   IPC: RESPALDOS
========================================================= */

function isTrustedBackupRenderer(event) {
    const senderUrl = event.senderFrame && event.senderFrame.url;
    return typeof senderUrl === 'string'
        && senderUrl.startsWith(`http://${HOST}:${PORT}/`);
}


ipcMain.handle('skynetwork:backups:list', (event) => {
    if (!isTrustedBackupRenderer(event)) {
        return { success: false, error: 'Origen no autorizado.', backups: [] };
    }

    try {
        return { success: true, backups: listDatabaseBackups() };
    } catch (error) {
        writeLog(`ERROR LISTANDO BACKUPS: ${error.message}`, 'ERROR');
        return { success: false, error: error.message, backups: [] };
    }
});


ipcMain.handle('skynetwork:backups:restore', async (event, backupFile) => {
    if (!isTrustedBackupRenderer(event)) {
        return { success: false, error: 'Origen no autorizado.' };
    }

    if (restoreInProgress) {
        return { success: false, error: 'Ya hay una restauración en progreso.' };
    }

    const confirmation = await dialog.showMessageBox(mainWindow, {
        type: 'warning',
        title: 'Restaurar respaldo',
        message: 'Se reemplazará la base de datos actual.',
        detail: 'Antes de continuar se creará un backup de seguridad. La aplicación se reiniciará al terminar.',
        buttons: ['Cancelar', 'Restaurar y reiniciar'],
        defaultId: 0,
        cancelId: 0,
        noLink: true
    });

    if (confirmation.response !== 1) {
        writeLog('Restauración cancelada por el usuario.');
        return { success: false, cancelled: true };
    }

    restoreInProgress = true;
    const result = await restoreDatabaseBackup(backupFile);

    if (result.success) {
        // Dejamos que el renderer reciba la confirmación antes de cerrar el proceso.
        setTimeout(() => {
            isQuitting = true;
            allowWindowClose = true;
            app.relaunch();
            app.exit(0);
        }, 700);
    } else if (result.phpWasStopped) {
        // Si PHP ya fue detenido, recuperamos una aplicación operativa tras el error.
        try {
            await startPhpServer();
        } catch (restartError) {
            writeLog(`ERROR REINICIANDO PHP TRAS RESTAURACIÓN FALLIDA: ${restartError.message}`, 'ERROR');
        }
    }

    restoreInProgress = false;
    return result;
});


ipcMain.handle('skynetwork:database:import', async (event) => {
    if (!isTrustedBackupRenderer(event)) {
        return { success: false, error: 'Origen no autorizado.' };
    }

    if (restoreInProgress || importInProgress) {
        return { success: false, error: 'Ya hay una operación de base de datos en progreso.' };
    }

    const selection = await dialog.showOpenDialog(mainWindow, {
        title: 'Seleccionar base de datos',
        properties: ['openFile'],
        filters: [{ name: 'SQLite Database', extensions: ['db', 'sqlite', 'sqlite3'] }]
    });
    if (selection.canceled || !selection.filePaths[0]) {
        return { success: false, cancelled: true };
    }

    const confirmation = await dialog.showMessageBox(mainWindow, {
        type: 'warning',
        title: 'Confirmar importación',
        message: 'Esta acción reemplazará la base de datos actual de SkyNetwork.',
        detail: 'Se validará el archivo seleccionado, se creará un backup automático y la aplicación se reiniciará al finalizar.',
        buttons: ['Cancelar', 'Importar base'],
        defaultId: 0,
        cancelId: 0,
        noLink: true
    });
    if (confirmation.response !== 1) {
        writeLog('Importación cancelada por el usuario.');
        return { success: false, cancelled: true };
    }

    importInProgress = true;
    const result = await importExternalDatabase(selection.filePaths[0]);

    if (result.success) {
        setTimeout(() => {
            isQuitting = true;
            allowWindowClose = true;
            app.relaunch();
            app.exit(0);
        }, 700);
    } else if (result.phpWasStopped) {
        try {
            await startPhpServer();
        } catch (restartError) {
            writeLog(`ERROR REINICIANDO PHP TRAS IMPORTACIÓN FALLIDA: ${restartError.message}`, 'ERROR');
        }
    }

    importInProgress = false;
    return result;
});


/* =========================================================
   PROCESO SEGURO DE APAGADO
========================================================= */

async function performSafeShutdown(
    reason = 'application-close'
) {

    /*
     * Evitar múltiples cierres simultáneos.
     */
    if (shutdownInProgress) {

        writeLog(
            'Ya existe un proceso de cierre en progreso.'
        );

        return;

    }


    shutdownInProgress = true;


    writeLog(
        '========================================'
    );

    writeLog(
        'INICIANDO CIERRE SEGURO SKYNETWORK'
    );

    writeLog(
        'Motivo: ' + reason
    );


    try {

        /*
         * PASO 1
         *
         * Detener completamente PHP.
         *
         * Esto es crítico porque SQLite puede
         * tener datos todavía en WAL mientras
         * PHP mantiene conexiones abiertas.
         */
        writeLog(
            'PASO 1: Deteniendo servidor PHP.'
        );


        await stopPhpServer();


        /*
         * PASO 2
         *
         * Crear backup solo después de que
         * PHP terminó completamente.
         */
        if (
            CREATE_BACKUP_ON_EXIT
            &&
            !shutdownBackupCompleted
            &&
            !updateBackupCompleted
        ) {

            writeLog(
                'PASO 2: Creando backup final.'
            );


            const backupResult =
                createDatabaseBackup(
                    reason
                );


            if (backupResult.success) {

                shutdownBackupCompleted = true;

                writeLog(
                    'PASO 3: Backup confirmado correctamente.'
                );

            } else {

                writeLog(
                    'BACKUP FALLÓ: '
                    + backupResult.error,
                    'ERROR'
                );

            }

        } else {

            writeLog(
                'No es necesario crear otro backup.'
            );

        }

    } catch (error) {

        writeLog(
            'ERROR DURANTE CIERRE SEGURO: '
            + error.message,
            'ERROR'
        );

    }


    writeLog(
        'FINALIZANDO CIERRE SEGURO.'
    );

    writeLog(
        '========================================'
    );


    isQuitting = true;

}


/* =========================================================
   BASE DE DATOS PERSISTENTE
========================================================= */

function prepareDatabase() {

    const userDataPath =
        getUserDataPath();


    const databaseDirectory =
        path.join(
            userDataPath,
            'database'
        );


    const persistentDatabasePath =
        getPersistentDatabasePath();


    const templateDatabasePath =
        getTemplateDatabasePath();


    if (!fs.existsSync(userDataPath)) {

        fs.mkdirSync(
            userDataPath,
            {
                recursive: true
            }
        );

    }


    if (!fs.existsSync(databaseDirectory)) {

        fs.mkdirSync(
            databaseDirectory,
            {
                recursive: true
            }
        );

    }


    if (
        !fs.existsSync(
            persistentDatabasePath
        )
    ) {

        writeLog(
            'PRIMERA EJECUCIÓN: Creando base persistente.'
        );


        if (
            !fs.existsSync(
                templateDatabasePath
            )
        ) {

            throw new Error(
                'No se encontró la base plantilla: '
                + templateDatabasePath
            );

        }


        fs.copyFileSync(
            templateDatabasePath,
            persistentDatabasePath
        );


        writeLog(
            'Base persistente creada correctamente.'
        );

    } else {

        writeLog(
            'Base persistente encontrada.'
        );

        writeLog(
            'Conservando datos existentes.'
        );

    }


    if (
        !fs.existsSync(
            persistentDatabasePath
        )
    ) {

        throw new Error(
            'No fue posible preparar la base persistente.'
        );

    }


    writeLog(
        'Base ACTIVA: '
        + persistentDatabasePath
    );


    return persistentDatabasePath;

}


/* =========================================================
   SERVIDOR PHP
========================================================= */

function isPortAvailable(port) {

    return new Promise(
        (resolve) => {

            const server =
                net.createServer();


            server.once(
                'error',
                () => {

                    resolve(false);

                }
            );


            server.once(
                'listening',
                () => {

                    server.close(
                        () => {

                            resolve(true);

                        }
                    );

                }
            );


            server.listen(
                port,
                HOST
            );

        }
    );

}


async function waitForServer(
    retries = 60
) {

    for (
        let i = 0;
        i < retries;
        i++
    ) {

        const available =
            await isPortAvailable(
                PORT
            );


        if (!available) {

            return true;

        }


        await new Promise(
            (resolve) => {

                setTimeout(
                    resolve,
                    250
                );

            }
        );

    }


    return false;

}


async function startPhpServer() {

    const projectPath =
        getProjectPath();


    const phpPath =
        getPhpPath();


    const databasePath =
        prepareDatabase();


    if (!fs.existsSync(phpPath)) {

        throw new Error(
            'No se encontró PHP Portable en: '
            + phpPath
        );

    }


    writeLog(
        '========================================'
    );

    writeLog(
        'INICIANDO SKYNETWORK'
    );

    writeLog(
        'Versión: '
        + app.getVersion()
    );

    writeLog(
        'Modo empaquetado: '
        + app.isPackaged
    );

    writeLog(
        'Proyecto: '
        + projectPath
    );

    writeLog(
        'PHP: '
        + phpPath
    );

    writeLog(
        'Base persistente: '
        + databasePath
    );

    writeLog(
        'Backups: '
        + getBackupsDirectory()
    );

    writeLog(
        'Logs: '
        + getLogsDirectory()
    );

    writeLog(
        '========================================'
    );


    phpProcess = spawn(

        phpPath,

        [
            '-S',
            `${HOST}:${PORT}`,
            '-t',
            projectPath
        ],

        {

            cwd: projectPath,

            windowsHide: true,

            env: {

                ...process.env,

                SKYNETWORK_DB_PATH:
                    databasePath

            }

        }

    );


    phpProcess.stdout.on(
        'data',
        (data) => {

            console.log(
                '[PHP]',
                data.toString()
            );

        }
    );


    phpProcess.stderr.on(
        'data',
        (data) => {

            console.log(
                '[PHP]',
                data.toString()
            );

        }
    );


    phpProcess.on(
        'error',
        (error) => {

            writeLog(
                'Error iniciando PHP: '
                + error.message,
                'ERROR'
            );

        }
    );


    phpProcess.on(
        'exit',
        (code) => {

            writeLog(
                'PHP finalizó con código: '
                + code
            );

        }
    );


    return await waitForServer();

}


/* =========================================================
   VENTANA PRINCIPAL
========================================================= */

function createWindow() {

    const iconPath =
        getIconPath();


    mainWindow =
        new BrowserWindow({

            width: 1440,
            height: 900,

            minWidth: 1024,
            minHeight: 700,

            title: 'SkyNetwork',

            icon: iconPath,

            autoHideMenuBar: true,

            webPreferences: {

                contextIsolation: true,

                nodeIntegration: false,

                preload: path.join(
                    __dirname,
                    'preload.js'
                )

            }

        });


    mainWindow.loadURL(
        `http://${HOST}:${PORT}`
    );


    /*
     * CIERRE CONTROLADO.
     *
     * Aquí está una de las correcciones principales.
     *
     * Electron NO podrá destruir la ventana inmediatamente.
     * Primero detenemos PHP, hacemos backup y solamente
     * después permitimos cerrar.
     */
    mainWindow.on(
        'close',
        async (event) => {

            if (allowWindowClose) {

                return;

            }


            if (shutdownInProgress) {

                event.preventDefault();

                return;

            }


            event.preventDefault();


            writeLog(
                'Usuario solicitó cerrar SkyNetwork.'
            );


            /*
             * Evitar interacción mientras se realiza
             * el cierre seguro.
             */
            try {

                mainWindow.setEnabled(false);

            } catch (error) {

                // Ignorar.

            }


            await performSafeShutdown(
                'application-close'
            );


            /*
             * Ahora sí permitimos destruir ventana.
             */
            allowWindowClose = true;


            if (
                mainWindow
                &&
                !mainWindow.isDestroyed()
            ) {

                mainWindow.close();

            }

        }
    );


    mainWindow.on(
        'closed',
        () => {

            mainWindow = null;

        }
    );

}


/* =========================================================
   AUTO UPDATER
========================================================= */

autoUpdater.on(
    'update-available',
    async (info) => {

        writeLog(
            `Nueva actualización disponible: ${info.version}`
        );


        const result =
            await dialog.showMessageBox({

                type: 'info',

                title:
                    'Actualización disponible',

                message:
                    `SkyNetwork ${info.version} está disponible.`,

                detail:
                    `Versión actual: ${app.getVersion()}
Nueva versión: ${info.version}

¿Deseas descargarla ahora?`,

                buttons: [
                    'Actualizar ahora',
                    'Más tarde'
                ],

                defaultId: 0,

                cancelId: 1

            });


        if (result.response === 0) {

            writeLog(
                'Usuario aceptó descargar actualización.'
            );

            autoUpdater.downloadUpdate();

        }

    }
);


autoUpdater.on(
    'update-not-available',
    (info) => {

        writeLog(
            `SkyNetwork está actualizado. Versión: ${info.version}`
        );

    }
);


autoUpdater.on(
    'download-progress',
    (progressObj) => {

        const percent =
            Math.round(
                progressObj.percent
            );


        writeLog(
            `Descargando actualización: ${percent}%`
        );

    }
);


autoUpdater.on(
    'update-downloaded',
    async (info) => {

        writeLog(
            `Actualización descargada: ${info.version}`
        );


        const result =
            await dialog.showMessageBox({

                type: 'info',

                title:
                    'Actualización lista',

                message:
                    `SkyNetwork ${info.version} está listo para instalar.`,

                detail:
                    'Se creará un respaldo antes de actualizar.',

                buttons: [
                    'Reiniciar e instalar',
                    'Más tarde'
                ],

                defaultId: 0,

                cancelId: 1

            });


        if (result.response === 0) {

            writeLog(
                'Creando backup antes de actualizar.'
            );


            /*
             * Detenemos PHP primero para garantizar
             * que SQLite consolide completamente WAL.
             */
            await stopPhpServer();


            const backupResult =
                createDatabaseBackup(
                    'before-update'
                );


            if (!backupResult.success) {

                const backupErrorResult =
                    await dialog.showMessageBox({

                        type: 'warning',

                        title:
                            'Error creando respaldo',

                        message:
                            'No fue posible crear el respaldo.',

                        detail:
                            `Error: ${backupResult.error}

¿Deseas instalar la actualización de todos modos?`,

                        buttons: [
                            'Cancelar actualización',
                            'Instalar de todos modos'
                        ],

                        defaultId: 0,

                        cancelId: 0

                    });


                if (
                    backupErrorResult.response === 0
                ) {

                    writeLog(
                        'Usuario canceló actualización.'
                    );

                    /*
                     * Si canceló, reiniciamos la app
                     * para volver a levantar PHP.
                     */
                    app.relaunch();
                    app.exit(0);

                    return;

                }

            } else {

                updateBackupCompleted = true;

                shutdownBackupCompleted = true;

                writeLog(
                    'Backup previo a actualización creado.'
                );

            }


            writeLog(
                'Iniciando instalación.'
            );


            isQuitting = true;
            allowWindowClose = true;


            autoUpdater.quitAndInstall();

        }

    }
);


autoUpdater.on(
    'error',
    (error) => {

        writeLog(
            'Error del Auto Updater: '
            + error.message,
            'ERROR'
        );

    }
);


function checkForUpdates() {

    if (!app.isPackaged) {

        writeLog(
            'Modo desarrollo: Auto Updater desactivado.'
        );

        return;

    }


    writeLog(
        `Buscando actualizaciones desde versión ${app.getVersion()}...`
    );


    autoUpdater.checkForUpdates();

}


/* =========================================================
   INICIO
========================================================= */

app.whenReady().then(
    async () => {

        try {

            const started =
                await startPhpServer();


            if (!started) {

                throw new Error(
                    'No fue posible iniciar el servidor PHP.'
                );

            }


            createWindow();


            setTimeout(
                () => {

                    checkForUpdates();

                },
                5000
            );

        } catch (error) {

            writeLog(
                'Error al iniciar SkyNetwork: '
                + error.message,
                'ERROR'
            );


            dialog.showErrorBox(

                'Error al iniciar SkyNetwork',

                error.message

            );


            app.quit();

        }

    }
);


/* =========================================================
   CIERRE DE VENTANAS
========================================================= */

app.on(
    'window-all-closed',
    () => {

        if (
            process.platform === 'darwin'
        ) {

            return;

        }


        /*
         * La ventana ya pasó por el flujo seguro
         * de cierre.
         */
        writeLog(
            'Todas las ventanas fueron cerradas.'
        );


        isQuitting = true;


        app.quit();

    }
);


/* =========================================================
   PROTECCIÓN ADICIONAL ANTES DE SALIR
========================================================= */

app.on(
    'before-quit',
    (event) => {

        /*
         * Si ya pasó por el cierre seguro,
         * permitimos salir normalmente.
         */
        if (
            isQuitting
            ||
            shutdownBackupCompleted
            ||
            updateBackupCompleted
        ) {

            return;

        }


        /*
         * Protección para casos donde Electron
         * intente salir sin pasar por el botón
         * normal de cerrar.
         */
        if (
            CREATE_BACKUP_ON_EXIT
            &&
            !shutdownInProgress
        ) {

            event.preventDefault();


            writeLog(
                'before-quit detectado. Ejecutando cierre seguro.'
            );


            performSafeShutdown(
                'before-quit'
            ).finally(
                () => {

                    isQuitting = true;

                    allowWindowClose = true;

                    app.quit();

                }
            );

        }

    }
);


/* =========================================================
   ACTIVACIÓN
========================================================= */

app.on(
    'activate',
    () => {

        if (
            BrowserWindow.getAllWindows().length === 0
            &&
            !isQuitting
        ) {

            createWindow();

        }

    }
);


/* =========================================================
   ERRORES GLOBALES
========================================================= */

process.on(
    'uncaughtException',
    (error) => {

        writeLog(
            'UNCAUGHT EXCEPTION: '
            + error.stack,
            'ERROR'
        );

    }
);


process.on(
    'unhandledRejection',
    (reason) => {

        writeLog(
            'UNHANDLED REJECTION: '
            + String(reason),
            'ERROR'
        );

    }
);
