const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('skyNetworkBackups', {
    list: () => ipcRenderer.invoke('skynetwork:backups:list'),
    restore: (backupFile) => ipcRenderer.invoke('skynetwork:backups:restore', backupFile)
});

contextBridge.exposeInMainWorld('skyNetworkDatabase', {
    import: () => ipcRenderer.invoke('skynetwork:database:import')
});
