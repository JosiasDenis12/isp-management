const {
    app,
    BrowserWindow,
    dialog
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
let shutdownBackupCompleted = false;
let updateBackupCompleted = false;


const HOST = '127.0.0.1';
const PORT = 8080;


/* =========================================================
   CONFIGURACIÓN DE RESPALDOS
========================================================= */

/**
 * Cantidad máxima de respaldos automáticos.
 */
const MAX_BACKUPS = 30;


/**
 * Activar respaldo automático al cerrar.
 */
const CREATE_BACKUP_ON_EXIT = true;


/* =========================================================
   AUTO UPDATER
========================================================= */

autoUpdater.autoDownload = false;
autoUpdater.autoInstallOnAppQuit = true;


/* =========================================================
   RUTAS DEL PROYECTO
========================================================= */

/**
 * Obtiene la ruta real del backend.
 *
 * Desarrollo:
 * C:\wamp64\www\isp-management
 *
 * Producción:
 * resources\backend
 */
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


/**
 * Directorio persistente del usuario.
 *
 * Ejemplo:
 *
 * C:\Users\Admin\AppData\Roaming\skynetwork
 */
function getUserDataPath() {

    return app.getPath(
        'userData'
    );

}


/**
 * Ruta de la base persistente.
 */
function getPersistentDatabasePath() {

    return path.join(
        getUserDataPath(),
        'database',
        'skynetwork.db'
    );

}


/**
 * Carpeta de respaldos.
 */
function getBackupsDirectory() {

    return path.join(
        getUserDataPath(),
        'backups'
    );

}


/**
 * Base plantilla incluida con el programa.
 */
function getTemplateDatabasePath() {

    return path.join(
        getProjectPath(),
        'database',
        'skynetwork.db'
    );

}


/**
 * PHP Portable.
 */
function getPhpPath() {

    return path.join(
        getProjectPath(),
        'php',
        'php.exe'
    );

}


/**
 * Icono de SkyNetwork.
 */
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
   UTILIDADES DE RESPALDO
========================================================= */

/**
 * Genera timestamp para archivos.
 *
 * Ejemplo:
 * 2026-09-05_01-42-30
 */
function getBackupTimestamp() {

    const now = new Date();

    const year =
        now.getFullYear();

    const month =
        String(
            now.getMonth() + 1
        ).padStart(
            2,
            '0'
        );

    const day =
        String(
            now.getDate()
        ).padStart(
            2,
            '0'
        );

    const hours =
        String(
            now.getHours()
        ).padStart(
            2,
            '0'
        );

    const minutes =
        String(
            now.getMinutes()
        ).padStart(
            2,
            '0'
        );

    const seconds =
        String(
            now.getSeconds()
        ).padStart(
            2,
            '0'
        );

    return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;

}


/**
 * Consolida SQLite WAL antes del respaldo.
 */
function checkpointDatabase() {

    const phpPath =
        getPhpPath();

    const databasePath =
        getPersistentDatabasePath();


    if (!fs.existsSync(phpPath)) {

        console.warn(
            'PHP Portable no encontrado.'
        );

        return false;

    }


    if (!fs.existsSync(databasePath)) {

        console.warn(
            'Base de datos no encontrada para checkpoint.'
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
                    'PRAGMA busy_timeout = 5000'
                );

                $pdo->exec(
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

                timeout: 10000

            }

        );


        if (result.status === 0) {

            console.log(
                'SQLite WAL checkpoint completado.'
            );

            return true;

        }


        console.warn(
            'No fue posible completar WAL checkpoint.'
        );


        if (result.stderr) {

            console.warn(
                result.stderr
            );

        }


        return false;

    } catch (error) {

        console.warn(
            'Error realizando WAL checkpoint:',
            error.message
        );

        return false;

    }

}


/**
 * Elimina respaldos antiguos.
 */
function cleanupOldBackups() {

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


                    console.log(
                        'Backup antiguo eliminado:',
                        backup.file
                    );

                } catch (error) {

                    console.warn(
                        'No se pudo eliminar backup antiguo:',
                        backup.file
                    );

                }

            }

        }

    } catch (error) {

        console.warn(
            'Error limpiando backups:',
            error.message
        );

    }

}


/**
 * Crea un respaldo completo.
 */
function createDatabaseBackup(
    reason = 'automatic'
) {

    const databasePath =
        getPersistentDatabasePath();

    const backupsDirectory =
        getBackupsDirectory();


    try {

        console.log('');
        console.log(
            '========================================'
        );

        console.log(
            'INICIANDO RESPALDO DE BASE DE DATOS'
        );

        console.log(
            '========================================'
        );


        console.log(
            'Motivo:',
            reason
        );


        console.log(
            'Base origen:',
            databasePath
        );


        if (!fs.existsSync(databasePath)) {

            throw new Error(
                'No existe la base persistente.'
            );

        }


        if (!fs.existsSync(backupsDirectory)) {

            fs.mkdirSync(
                backupsDirectory,
                {
                    recursive: true
                }
            );

            console.log(
                'Directorio de backups creado.'
            );

        }


        /**
         * Consolidar WAL.
         */
        checkpointDatabase();


        /**
         * Generar nombre.
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


        /**
         * Copiar base.
         */
        fs.copyFileSync(
            databasePath,
            backupPath
        );


        /**
         * Verificar existencia.
         */
        if (!fs.existsSync(backupPath)) {

            throw new Error(
                'El archivo de respaldo no fue creado.'
            );

        }


        /**
         * IMPORTANTE:
         * Actualizamos explícitamente la fecha
         * del archivo de backup.
         *
         * Esto evita confusión al ordenar backups,
         * ya que copyFile puede conservar timestamps
         * relacionados con el archivo original.
         */
        const now =
            new Date();

        fs.utimesSync(
            backupPath,
            now,
            now
        );


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


        /**
         * Metadata.
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

            database:
                'skynetwork.db',

            originalSize,

            backupSize,

            backupFile:
                backupFileName

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


        console.log(
            'Backup creado correctamente.'
        );

        console.log(
            'Archivo:',
            backupPath
        );

        console.log(
            'Tamaño:',
            backupSize,
            'bytes'
        );

        console.log(
            '========================================'
        );

        console.log('');


        cleanupOldBackups();


        return {

            success: true,

            backupPath,

            backupFileName,

            originalSize,

            backupSize

        };

    } catch (error) {

        console.error(
            'ERROR CREANDO BACKUP:',
            error.message
        );


        return {

            success: false,

            error:
                error.message

        };

    }

}


/**
 * Ejecuta el backup de cierre una sola vez.
 *
 * Esta función centralizada evita depender
 * exclusivamente de un evento de Electron.
 */
function performShutdownBackup() {

    if (!CREATE_BACKUP_ON_EXIT) {

        return;

    }


    if (shutdownBackupCompleted) {

        console.log(
            'El backup de cierre ya fue creado.'
        );

        return;

    }


    if (updateBackupCompleted) {

        console.log(
            'Ya existe backup previo a actualización.'
        );

        shutdownBackupCompleted = true;

        return;

    }


    console.log('');
    console.log(
        '========================================'
    );

    console.log(
        'RESPALDO DE CIERRE SKYNETWORK'
    );

    console.log(
        '========================================'
    );


    const backupResult =
        createDatabaseBackup(
            'application-close'
        );


    if (backupResult.success) {

        shutdownBackupCompleted = true;


        console.log(
            'RESPALDO DE CIERRE EXITOSO'
        );

        console.log(
            'Archivo:',
            backupResult.backupPath
        );

    } else {

        console.error(
            'ERROR EN RESPALDO DE CIERRE:',
            backupResult.error
        );

    }


    console.log(
        '========================================'
    );

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


    /**
     * Primera ejecución.
     */
    if (
        !fs.existsSync(
            persistentDatabasePath
        )
    ) {

        console.log(
            '========================================'
        );

        console.log(
            'PRIMERA EJECUCIÓN'
        );

        console.log(
            'Creando base persistente.'
        );

        console.log(
            '========================================'
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


        console.log(
            'Base persistente creada.'
        );

    } else {

        console.log(
            'Base persistente encontrada.'
        );

        console.log(
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


    console.log(
        'Base ACTIVA:',
        persistentDatabasePath
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


    console.log('');
    console.log(
        '========================================'
    );

    console.log(
        'SKYNETWORK'
    );

    console.log(
        '========================================'
    );

    console.log(
        'Versión:',
        app.getVersion()
    );

    console.log(
        'Modo empaquetado:',
        app.isPackaged
    );

    console.log(
        'Proyecto:',
        projectPath
    );

    console.log(
        'PHP:',
        phpPath
    );

    console.log(
        'Base persistente:',
        databasePath
    );

    console.log(
        'Backups:',
        getBackupsDirectory()
    );

    console.log(
        '========================================'
    );

    console.log('');


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

            console.error(
                'Error iniciando PHP:',
                error
            );

        }
    );


    phpProcess.on(
        'exit',
        (code) => {

            console.log(
                'PHP finalizó con código:',
                code
            );

        }
    );


    return await waitForServer();

}


/* =========================================================
   CERRAR PHP
========================================================= */

function stopPhpServer() {

    if (
        phpProcess &&
        !phpProcess.killed
    ) {

        try {

            phpProcess.kill();

            console.log(
                'Servidor PHP detenido.'
            );

        } catch (error) {

            console.error(
                'Error cerrando PHP:',
                error.message
            );

        }

    }

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

                nodeIntegration: false

            }

        });


    mainWindow.loadURL(
        `http://${HOST}:${PORT}`
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

        console.log(
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

            console.log(
                'Usuario aceptó descargar actualización.'
            );

            autoUpdater.downloadUpdate();

        }

    }
);


autoUpdater.on(
    'update-not-available',
    (info) => {

        console.log(
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


        console.log(
            `Descargando actualización: ${percent}%`
        );

    }
);


autoUpdater.on(
    'update-downloaded',
    async (info) => {

        console.log(
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

            console.log(
                'Creando backup antes de actualizar...'
            );


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

                    console.log(
                        'Usuario canceló actualización.'
                    );

                    return;

                }

            } else {

                updateBackupCompleted = true;

                console.log(
                    'Backup previo a actualización creado.'
                );

            }


            console.log(
                'Iniciando instalación.'
            );


            autoUpdater.quitAndInstall();

        }

    }
);


autoUpdater.on(
    'error',
    (error) => {

        console.error(
            'Error del Auto Updater:',
            error
        );


        if (app.isPackaged) {

            dialog.showMessageBox({

                type: 'error',

                title:
                    'Error buscando actualización',

                message:
                    'No fue posible comprobar actualizaciones.',

                detail:
                    error.message

            });

        }

    }
);


function checkForUpdates() {

    if (!app.isPackaged) {

        console.log(
            'Modo desarrollo: Auto Updater desactivado.'
        );

        return;

    }


    console.log(
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

            console.error(error);


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

/**
 * PRIMER NIVEL DE PROTECCIÓN.
 *
 * Cuando se cierra la última ventana,
 * hacemos el backup ANTES de pedir
 * que Electron termine.
 */
app.on(
    'window-all-closed',
    () => {

        if (
            process.platform === 'darwin'
        ) {

            return;

        }


        console.log(
            'Última ventana cerrada.'
        );


        if (
            !isQuitting
        ) {

            performShutdownBackup();

        }


        app.quit();

    }
);


/* =========================================================
   CIERRE FINAL
========================================================= */

/**
 * SEGUNDO NIVEL DE PROTECCIÓN.
 *
 * Si Electron intenta cerrar por cualquier otra vía,
 * verificamos nuevamente que exista el backup.
 */
app.on(
    'before-quit',
    () => {

        if (isQuitting) {

            return;

        }


        isQuitting = true;


        console.log('');
        console.log(
            '========================================'
        );

        console.log(
            'CERRANDO SKYNETWORK'
        );

        console.log(
            '========================================'
        );


        /**
         * Backup de seguridad.
         */
        if (
            CREATE_BACKUP_ON_EXIT
            &&
            !shutdownBackupCompleted
            &&
            !updateBackupCompleted
        ) {

            performShutdownBackup();

        }


        /**
         * Detener PHP.
         */
        stopPhpServer();


        console.log(
            'SkyNetwork cerrado correctamente.'
        );

        console.log(
            '========================================'
        );

        console.log('');

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
        ) {

            createWindow();

        }

    }
);