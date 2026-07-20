const { app, BrowserWindow, nativeImage } = require('electron');
const path = require('path');
const { spawn, execSync } = require('child_process');
const net = require('net');
const fs = require('fs');

// Application icon
const appIconPath = path.join(__dirname, 'TijaratPro.png');
const appIcon = nativeImage.createFromPath(appIconPath);

// Ignore SSL/Certificate errors
app.commandLine.appendSwitch('ignore-certificate-errors');
app.commandLine.appendSwitch('ignore-ssl-errors', 'true');

let phpServerProcess = null;
let whatsappProcess = null;
let mainWindow = null;

// Find a free port starting from a default port
function getFreePort(startingPort) {
  return new Promise((resolve) => {
    const port = startingPort;
    const server = net.createServer();
    server.once('error', (err) => {
      if (err.code === 'EADDRINUSE') {
        resolve(getFreePort(port + 1));
      } else {
        resolve(port);
      }
    });
    server.once('listening', () => {
      server.close(() => {
        resolve(port);
      });
    });
    server.listen(port, '127.0.0.1');
  });
}

// Find a valid PHP binary executable on the system
function findPhpBinary() {
  // 1. Try global 'php' command
  try {
    execSync('php -v', { stdio: 'ignore' });
    return 'php';
  } catch (e) {
    // Global php not working or not in PATH
  }

  // 2. Try common macOS brew / local paths
  const commonPaths = [
    '/opt/homebrew/bin/php',
    '/usr/local/bin/php',
    '/usr/bin/php',
  ];

  // 3. Try MAMP PHP paths on macOS
  try {
    const mampPhpDir = '/Applications/MAMP/bin/php';
    if (fs.existsSync(mampPhpDir)) {
      const versions = fs.readdirSync(mampPhpDir);
      versions.sort().reverse();
      for (const ver of versions) {
        const fullPath = path.join(mampPhpDir, ver, 'bin', 'php');
        if (fs.existsSync(fullPath)) {
          commonPaths.unshift(fullPath); // Prioritize latest MAMP PHP
        }
      }
    }
  } catch (err) {
    // Ignore MAMP search error
  }

  // 4. Try Laragon/XAMPP PHP paths on Windows
  if (process.platform === 'win32') {
    const winPaths = [
      'C:\\xampp\\php\\php.exe',
      'C:\\php\\php.exe',
    ];
    try {
      const laragonPhpDir = 'C:\\laragon\\bin\\php';
      if (fs.existsSync(laragonPhpDir)) {
        const versions = fs.readdirSync(laragonPhpDir);
        versions.sort().reverse();
        for (const ver of versions) {
          const fullPath = path.join(laragonPhpDir, ver, 'php.exe');
          if (fs.existsSync(fullPath)) {
            winPaths.unshift(fullPath);
          }
        }
      }
    } catch (err) {
      // Ignore Laragon search error
    }
    for (const p of winPaths) {
      if (fs.existsSync(p)) {
        return p;
      }
    }
  }

  // Check which common path exists
  for (const p of commonPaths) {
    if (fs.existsSync(p)) {
      return p;
    }
  }

  return null;
}

// Launch the PHP built-in web server in the background
function startPhpServer(port) {
  return new Promise((resolve, reject) => {
    const phpBinary = findPhpBinary();
    if (!phpBinary) {
      reject(new Error('PHP binary not found on the system. Please ensure PHP is installed (e.g. MAMP/Laragon or Brew).'));
      return;
    }

    console.log(`🚀 Launching PHP Server using binary: ${phpBinary} on port ${port}`);

    // Spawn PHP server: php -S 0.0.0.0:<port> -t <appDir>
    phpServerProcess = spawn(phpBinary, [
      '-S', `0.0.0.0:${port}`,
      '-t', __dirname
    ], {
      cwd: __dirname,
      stdio: 'pipe'
    });

    // Capture PHP server output
    phpServerProcess.stdout.on('data', (data) => {
      console.log(`[PHP STDOUT]: ${data}`);
    });

    phpServerProcess.stderr.on('data', (data) => {
      console.log(`[PHP STDERR]: ${data}`);
    });

    phpServerProcess.on('error', (err) => {
      console.error('❌ Failed to start PHP server process:', err);
      reject(err);
    });

    // Give it a brief moment to bind to the port
    setTimeout(() => {
      resolve();
    }, 800);
  });
}

// Launch the background WhatsApp (Baileys) Node service
function startWhatsappService() {
  return new Promise((resolve) => {
    console.log('🚀 Launching background WhatsApp service daemon...');
    whatsappProcess = spawn('node', [path.join(__dirname, 'whatsapp_service.js')], {
      cwd: __dirname,
      stdio: 'pipe'
    });

    whatsappProcess.stdout.on('data', (data) => {
      console.log(`[WHATSAPP STDOUT]: ${data}`);
    });

    whatsappProcess.stderr.on('data', (data) => {
      console.log(`[WHATSAPP STDERR]: ${data}`);
    });

    whatsappProcess.on('error', (err) => {
      console.error('❌ Failed to start WhatsApp background service:', err);
    });

    setTimeout(() => {
      resolve();
    }, 600);
  });
}

function createWindow(port) {
  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    title: "TijaratPro - Smart POS System",
    icon: appIcon,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true
    },
    show: false
  });

  mainWindow.loadURL(`http://127.0.0.1:${port}/login.php`);
  mainWindow.setMenuBarVisibility(false);

  mainWindow.once('ready-to-show', () => {
    mainWindow.maximize();
    mainWindow.show();
    mainWindow.focus();
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(async () => {
  try {
    // Set macOS dock icon
    if (process.platform === 'darwin' && app.dock) {
      app.dock.setIcon(appIcon);
    }

    const port = await getFreePort(8000);
    await startPhpServer(port);
    await startWhatsappService();
    createWindow(port);
  } catch (err) {
    const { dialog } = require('electron');
    dialog.showErrorBox('Initialization Error', err.message || 'Failed to start POS application.');
    app.quit();
  }
});

// Clean termination of the PHP server on application exit
function killPhpServer() {
  if (phpServerProcess) {
    console.log('🛑 Terminating background PHP server process...');
    phpServerProcess.kill('SIGTERM');
    phpServerProcess = null;
  }
}

function killWhatsappService() {
  if (whatsappProcess) {
    console.log('🛑 Terminating background WhatsApp service process...');
    whatsappProcess.kill('SIGTERM');
    whatsappProcess = null;
  }
}

app.on('will-quit', () => {
  killPhpServer();
  killWhatsappService();
});

app.on('window-all-closed', () => {
  killPhpServer();
  killWhatsappService();
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
