const { app, BrowserWindow, dialog } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const net = require('net');

let mainWindow = null;
let phpProcess = null;

const HOST = '127.0.0.1';
const PORT = 8080;


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
 *
 * Desarrollo:
 * assets/icon.ico
 *
 * Producción:
 * resources/backend/assets/icon.ico
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

        server.once('error', () => {

            resolve(false);

        });

        server.once('listening', () => {

            server.close(() => {

                resolve(true);

            });

        });

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

        const available = await isPortAvailable(PORT);

        /*
         * Si el puerto ya no está disponible,
         * significa que el servidor PHP lo está utilizando.
         */
        if (!available) {

            return true;

        }

        await new Promise((resolve) => {

            setTimeout(
                resolve,
                250
            );

        });

    }

    return false;

}


/**
 * Inicia el servidor PHP Portable.
 */
async function startPhpServer() {

    const projectPath = getProjectPath();
    const phpPath = getPhpPath();

    console.log('==============================');
    console.log('SkyNetwork');
    console.log('Modo empaquetado:', app.isPackaged);
    console.log('Proyecto PHP:', projectPath);
    console.log('PHP:', phpPath);
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
 * Crea la ventana principal de SkyNetwork.
 */
function createWindow() {

    const iconPath = getIconPath();


    console.log(
        'Icono:',
        iconPath
    );


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


/**
 * Inicio de Electron.
 */
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
 * Cierra el servidor PHP
 * cuando SkyNetwork termina.
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
 * Recrea la ventana cuando
 * la aplicación se vuelve a activar.
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