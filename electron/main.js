const {
    app,
    BrowserWindow,
    dialog
} = require('electron');

const path = require('path');
const { spawn } = require('child_process');
const net = require('net');

const {
    autoUpdater
} = require('electron-updater');


let mainWindow = null;
let phpProcess = null;

const HOST = '127.0.0.1';
const PORT = 8080;


/* =========================================================
   AUTO UPDATER
========================================================= */

/**
 * No descarga automáticamente.
 * El usuario decide cuándo actualizar.
 */
autoUpdater.autoDownload = false;


/**
 * Instala automáticamente al cerrar únicamente
 * después de que una actualización haya sido descargada.
 */
autoUpdater.autoInstallOnAppQuit = true;


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
 * Obtiene la ruta del ejecutable PHP Portable.
 */
function getPhpPath() {

    return path.join(
        getProjectPath(),
        'php',
        'php.exe'
    );

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


/**
 * Comprueba si un puerto está disponible.
 */
function isPortAvailable(port) {

    return new Promise((resolve) => {

        const server = net.createServer();

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
async function waitForServer(retries = 60) {

    for (let i = 0; i < retries; i++) {

        const available =
            await isPortAvailable(PORT);

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


    console.log('==============================');
    console.log('SkyNetwork');
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
    console.log('==============================');


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

            windowsHide: true

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


/**
 * Crea la ventana principal.
 */
function createWindow() {

    const iconPath =
        getIconPath();


    mainWindow = new BrowserWindow({

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
 * Se encontró una actualización.
 *
 * SOLO aquí mostramos una ventana al usuario.
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
 * NO hay actualización.
 *
 * IMPORTANTE:
 * Aquí NO mostramos ningún diálogo.
 * Solo registramos en consola.
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
                    'La aplicación debe reiniciarse para completar la actualización.',

                buttons: [
                    'Reiniciar e instalar',
                    'Más tarde'
                ],

                defaultId: 0,

                cancelId: 1

            });


        if (result.response === 0) {

            autoUpdater.quitAndInstall();

        }

    }
);


/**
 * Error durante la actualización.
 */
autoUpdater.on(
    'error',
    (error) => {

        console.error(
            'Error del Auto Updater:',
            error
        );


        /**
         * Mostramos error únicamente si la aplicación
         * está empaquetada.
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

    /**
     * En desarrollo no buscamos actualizaciones.
     */
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

            const started =
                await startPhpServer();


            if (!started) {

                throw new Error(
                    'No fue posible iniciar el servidor PHP.'
                );

            }


            createWindow();


            /**
             * Esperamos 5 segundos para que:
             *
             * 1. PHP termine de iniciar.
             * 2. La interfaz cargue.
             * 3. No afectemos la experiencia inicial.
             *
             * La búsqueda ocurre en segundo plano.
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


/**
 * Cierra PHP cuando SkyNetwork termina.
 */
app.on(
    'before-quit',
    () => {

        if (
            phpProcess &&
            !phpProcess.killed
        ) {

            phpProcess.kill();

        }

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