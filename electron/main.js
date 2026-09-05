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
   ESTADO GLOBAL DE CIERRE Y RESPALDOS
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
 * Cantidad máxima de respaldos automáticos
 * que se conservarán.
 */
const MAX_BACKUPS = 30;


/**
 * Crear automáticamente un respaldo cuando
 * SkyNetwork se cierre correctamente.
 */
const CREATE_BACKUP_ON_EXIT = true;


/* =========================================================
   AUTO UPDATER
========================================================= */

autoUpdater.autoDownload = false;

autoUpdater.autoInstallOnAppQuit = true;


/* =========================================================
   RUTAS DEL PROYECTO Y BASE DE DATOS
========================================================= */

/**
 * Obtiene la ruta real del backend PHP.
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
 * Obtiene la carpeta persistente de datos
 * del usuario.
 *
 * Ejemplo:
 *
 * C:\Users\Usuario\AppData\Roaming\skynetwork
 */
function getUserDataPath() {

    return app.getPath('userData');

}


/**
 * Obtiene la ruta de la base de datos
 * persistente.
 */
function getPersistentDatabasePath() {

    return path.join(
        getUserDataPath(),
        'database',
        'skynetwork.db'
    );

}


/**
 * Obtiene la carpeta donde se guardarán
 * los respaldos automáticos.
 *
 * Ejemplo:
 *
 * C:\Users\Usuario\AppData\Roaming\
 * skynetwork\
 * backups\
 */
function getBackupsDirectory() {

    return path.join(
        getUserDataPath(),
        'backups'
    );

}


/**
 * Obtiene la ruta de la base plantilla
 * incluida dentro del proyecto o instalador.
 */
function getTemplateDatabasePath() {

    return path.join(
        getProjectPath(),
        'database',
        'skynetwork.db'
    );

}


/**
 * Obtiene la ruta del ejecutable PHP Portable.
 */
function getPhpPath() {

    return path.join(
        getProjectPath(),
        'php',
        'php.exe'
    );

}


/* =========================================================
   UTILIDADES DE RESPALDO
========================================================= */

/**
 * Genera una fecha segura para nombres
 * de archivos.
 *
 * Ejemplo:
 *
 * 2026-09-05_12-45-30
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
 * Intenta sincronizar SQLite antes
 * de crear un respaldo.
 *
 * Esto es importante porque usamos WAL.
 *
 * SQLite puede mantener cambios recientes
 * temporalmente en archivos WAL.
 *
 * El checkpoint intenta consolidar esos
 * cambios dentro del archivo principal .db.
 */
function checkpointDatabase() {

    const phpPath =
        getPhpPath();

    const databasePath =
        getPersistentDatabasePath();


    if (!fs.existsSync(phpPath)) {

        console.warn(
            'PHP Portable no encontrado. '
            + 'Se omitirá WAL checkpoint.'
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


        if (
            result.status === 0
        ) {

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
 *
 * Conserva únicamente los últimos
 * MAX_BACKUPS respaldos.
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
                    file.startsWith('skynetwork-')
                    && file.endsWith('.db')
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
                    - a.stats.mtimeMs
            );


        /**
         * Si hay más respaldos de los permitidos,
         * eliminar los más antiguos.
         */
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

                    /**
                     * Eliminar archivo DB.
                     */
                    fs.unlinkSync(
                        backup.fullPath
                    );


                    /**
                     * Eliminar metadata JSON correspondiente.
                     */
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
                        backup.file,
                        error.message
                    );

                }

            }

        }

    } catch (error) {

        console.warn(
            'Error limpiando backups antiguos:',
            error.message
        );

    }

}


/**
 * Crea un respaldo de la base de datos.
 *
 * @param {string} reason
 *
 * Motivo del respaldo.
 *
 * Ejemplos:
 *
 * before-update
 * application-close
 * manual
 * automatic
 *
 * @returns {object}
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


        /**
         * Verificar que exista la BD.
         */
        if (!fs.existsSync(databasePath)) {

            throw new Error(
                'No existe la base de datos persistente: '
                + databasePath
            );

        }


        /**
         * Crear directorio de backups.
         */
        if (!fs.existsSync(backupsDirectory)) {

            fs.mkdirSync(
                backupsDirectory,
                {
                    recursive: true
                }
            );

            console.log(
                'Directorio de backups creado:',
                backupsDirectory
            );

        }


        /**
         * Intentar consolidar WAL.
         */
        checkpointDatabase();


        /**
         * Crear nombre único.
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
         * Copiar la base de datos.
         */
        fs.copyFileSync(
            databasePath,
            backupPath
        );


        /**
         * Verificar que se creó correctamente.
         */
        if (!fs.existsSync(backupPath)) {

            throw new Error(
                'El archivo de respaldo no fue creado.'
            );

        }


        const originalSize =
            fs.statSync(
                databasePath
            ).size;


        const backupSize =
            fs.statSync(
                backupPath
            ).size;


        /**
         * Validación básica.
         */
        if (
            backupSize === 0
        ) {

            throw new Error(
                'El respaldo fue creado vacío.'
            );

        }


        /**
         * Crear metadata JSON.
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
            'Versión:',
            app.getVersion()
        );

        console.log(
            'Motivo:',
            reason
        );

        console.log(
            '========================================'
        );

        console.log('');


        /**
         * Limpiar backups antiguos.
         */
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
            error
        );


        return {

            success: false,

            error:
                error.message

        };

    }

}


/**
 * Obtiene la ruta del icono.
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
   BASE DE DATOS PERSISTENTE
========================================================= */

/**
 * Prepara la base de datos persistente.
 *
 * PRIMERA EJECUCIÓN:
 * - Copia la BD plantilla incluida en la instalación.
 *
 * EJECUCIONES POSTERIORES:
 * - Conserva SIEMPRE la base existente.
 * - Nunca sobrescribe datos del usuario.
 *
 * ACTUALIZACIONES:
 * - La nueva instalación puede reemplazar resources/backend.
 * - La BD persistente permanece intacta en AppData.
 */
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


    /**
     * Crear directorio principal si no existe.
     */
    if (!fs.existsSync(userDataPath)) {

        fs.mkdirSync(
            userDataPath,
            {
                recursive: true
            }
        );

    }


    /**
     * Crear carpeta database si no existe.
     */
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
    if (!fs.existsSync(persistentDatabasePath)) {

        console.log(
            '========================================'
        );

        console.log(
            'PRIMERA EJECUCIÓN'
        );

        console.log(
            'Creando base de datos persistente.'
        );

        console.log(
            'Plantilla:',
            templateDatabasePath
        );

        console.log(
            'Destino:',
            persistentDatabasePath
        );

        console.log(
            '========================================'
        );


        /**
         * Verificar plantilla.
         */
        if (!fs.existsSync(templateDatabasePath)) {

            throw new Error(
                'No se encontró la base de datos plantilla: '
                + templateDatabasePath
            );

        }


        /**
         * Copiar plantilla.
         */
        fs.copyFileSync(
            templateDatabasePath,
            persistentDatabasePath
        );


        console.log(
            'Base de datos persistente creada correctamente.'
        );

    } else {

        console.log(
            'Base de datos persistente encontrada.'
        );

        console.log(
            'Conservando datos existentes del usuario.'
        );

    }


    /**
     * Verificación final.
     */
    if (!fs.existsSync(persistentDatabasePath)) {

        throw new Error(
            'No fue posible preparar la base de datos persistente.'
        );

    }


    console.log(
        'Base de datos ACTIVA:',
        persistentDatabasePath
    );


    return persistentDatabasePath;

}


/* =========================================================
   SERVIDOR PHP
========================================================= */

/**
 * Comprueba si un puerto está disponible.
 */
function isPortAvailable(port) {

    return new Promise((resolve) => {

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

    });

}


/**
 * Espera a que el servidor PHP inicie.
 */
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


        /**
         * Si el puerto ya NO está disponible,
         * PHP probablemente ya inició.
         */
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


/**
 * Inicia el servidor PHP Portable.
 */
async function startPhpServer() {

    const projectPath =
        getProjectPath();

    const phpPath =
        getPhpPath();


    /**
     * Preparar la BD persistente ANTES
     * de iniciar PHP.
     */
    const databasePath =
        prepareDatabase();


    /**
     * Verificar PHP.
     */
    if (!fs.existsSync(phpPath)) {

        throw new Error(
            'No se encontró PHP Portable en: '
            + phpPath
        );

    }


    console.log('');
    console.log('========================================');
    console.log('           SKYNETWORK');
    console.log('========================================');

    console.log(
        'Versión:',
        app.getVersion()
    );

    console.log(
        'Modo empaquetado:',
        app.isPackaged
    );

    console.log(
        'Proyecto PHP:',
        projectPath
    );

    console.log(
        'PHP:',
        phpPath
    );

    console.log(
        'Base de datos persistente:',
        databasePath
    );

    console.log(
        'Carpeta de backups:',
        getBackupsDirectory()
    );

    console.log('========================================');
    console.log('');


    /**
     * Iniciar PHP.
     */
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


    /**
     * Logs PHP.
     */
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


    /**
     * Error PHP.
     */
    phpProcess.on(
        'error',
        (error) => {

            console.error(
                'Error iniciando PHP:',
                error
            );

        }
    );


    /**
     * PHP finalizó.
     */
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
   EVENTOS DEL AUTO UPDATER
========================================================= */


/**
 * Nueva actualización disponible.
 */
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
                    `Hay una nueva versión disponible.

Versión actual: ${app.getVersion()}
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


/**
 * No hay actualización.
 *
 * Completamente silencioso para el usuario.
 */
autoUpdater.on(
    'update-not-available',
    (info) => {

        console.log(
            `SkyNetwork está actualizado. Versión actual: ${info.version}`
        );

    }
);


/**
 * Progreso de descarga.
 */
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


/**
 * Actualización descargada.
 */
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
                    `Antes de instalar la actualización se creará
automáticamente un respaldo de seguridad de tu base de datos.

La aplicación debe reiniciarse para completar
la actualización.`,

                buttons: [
                    'Reiniciar e instalar',
                    'Más tarde'
                ],

                defaultId: 0,

                cancelId: 1

            });


        if (result.response === 0) {


            /* =============================================
               BACKUP ANTES DE ACTUALIZAR
            ============================================= */

            console.log(
                'Creando respaldo antes de instalar actualización...'
            );


            const backupResult =
                createDatabaseBackup(
                    'before-update'
                );


            /**
             * Si el backup falla, avisar.
             */
            if (!backupResult.success) {

                console.error(
                    'La actualización fue detenida porque '
                    + 'no se pudo crear el respaldo.'
                );


                const backupErrorResult =
                    await dialog.showMessageBox({

                        type: 'warning',

                        title:
                            'No se pudo crear el respaldo',

                        message:
                            'SkyNetwork no pudo crear un respaldo automático de seguridad.',

                        detail:
                            `Error: ${backupResult.error}

Se recomienda NO continuar hasta verificar la base de datos.

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
                        'Usuario canceló la actualización '
                        + 'por fallo del backup.'
                    );

                    return;

                }

            } else {

                /**
                 * Evitar crear un segundo backup inmediatamente
                 * cuando quitAndInstall cierre la aplicación.
                 */
                updateBackupCompleted = true;


                console.log(
                    'Respaldo creado exitosamente antes de actualizar.'
                );

                console.log(
                    'Ruta del backup:',
                    backupResult.backupPath
                );

            }


            /* =============================================
               INSTALAR ACTUALIZACIÓN
            ============================================= */

            console.log(
                'Iniciando instalación de actualización...'
            );


            autoUpdater.quitAndInstall();

        }

    }
);


/**
 * Error durante actualización.
 */
autoUpdater.on(
    'error',
    (error) => {

        console.error(
            'Error del Auto Updater:',
            error
        );


        /**
         * No mostrar errores durante desarrollo.
         */
        if (app.isPackaged) {

            dialog.showMessageBox({

                type: 'error',

                title:
                    'Error buscando actualización',

                message:
                    'No fue posible comprobar las actualizaciones.',

                detail:
                    error.message

            });

        }

    }
);


/**
 * Busca actualizaciones silenciosamente.
 */
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
   INICIO DE ELECTRON
========================================================= */

app.whenReady().then(
    async () => {

        try {

            /**
             * Iniciar PHP y preparar BD persistente.
             */
            const started =
                await startPhpServer();


            if (!started) {

                throw new Error(
                    'No fue posible iniciar el servidor PHP.'
                );

            }


            /**
             * Crear interfaz.
             */
            createWindow();


            /**
             * Buscar actualizaciones después
             * de que la aplicación esté funcionando.
             */
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
   CIERRE SEGURO + RESPALDO AUTOMÁTICO
========================================================= */

/**
 * Antes de cerrar completamente SkyNetwork:
 *
 * 1. Crear respaldo automático.
 * 2. Consolidar SQLite WAL.
 * 3. Después cerrar PHP.
 *
 * IMPORTANTE:
 *
 * El backup se crea ANTES de detener PHP
 * para asegurar que todos los cambios recientes
 * estén disponibles.
 */
app.on(
    'before-quit',
    () => {

        /**
         * Evitar ejecutar el proceso varias veces.
         */
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


        /* =============================================
           RESPALDO AUTOMÁTICO AL CERRAR
        ============================================= */

        if (
            CREATE_BACKUP_ON_EXIT
            && !shutdownBackupCompleted
            && !updateBackupCompleted
        ) {

            console.log(
                'Creando respaldo automático antes de cerrar...'
            );


            const backupResult =
                createDatabaseBackup(
                    'application-close'
                );


            if (backupResult.success) {

                shutdownBackupCompleted = true;


                console.log(
                    'Respaldo automático creado correctamente.'
                );

                console.log(
                    'Archivo:',
                    backupResult.backupPath
                );

            } else {

                console.error(
                    'No fue posible crear el respaldo automático:',
                    backupResult.error
                );

            }

        } else if (updateBackupCompleted) {

            console.log(
                'Backup antes de actualización ya creado.'
            );

            console.log(
                'Se evita crear un respaldo duplicado.'
            );

        }


        /* =============================================
           CERRAR PHP
        ============================================= */

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


        console.log(
            'SkyNetwork cerrado correctamente.'
        );

        console.log(
            '========================================'
        );

        console.log('');

    }
);


/**
 * Recrea ventana si es necesario.
 */
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