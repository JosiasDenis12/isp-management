const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('skyNetworkBackups', {
    list: () => ipcRenderer.invoke('skynetwork:backups:list'),
    restore: (backupFile) => ipcRenderer.invoke('skynetwork:backups:restore', backupFile)
});
