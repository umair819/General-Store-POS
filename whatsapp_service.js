const { default: makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const express = require('express');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

// Enable CORS for client-side API requests from PHP pages on port 8085
app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    if (req.method === 'OPTIONS') {
        return res.sendStatus(200);
    }
    next();
});

const authFolder = path.join(__dirname, '.whatsapp_session');
let sock = null;
let qrCodeData = null;
let connectionState = 'DISCONNECTED'; // DISCONNECTED, CONNECTING, QR_READY, CONNECTED
let linkedUser = null;

async function startWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState(authFolder);
        
        sock = makeWASocket({
            auth: state,
            printQRInTerminal: false,
            logger: pino({ level: 'silent' }),
            browser: ['TijaratPro', 'Chrome', '1.0.0']
        });

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;
            
            if (qr) {
                qrCodeData = await qrcode.toDataURL(qr);
                connectionState = 'QR_READY';
            }

            if (connection === 'connecting') {
                connectionState = 'CONNECTING';
            }

            if (connection === 'open') {
                connectionState = 'CONNECTED';
                qrCodeData = null;
                // Extract clean phone number
                linkedUser = sock.user;
                console.log('✅ WhatsApp Background Service Connected: ', linkedUser.id);
            }

            if (connection === 'close') {
                const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
                connectionState = 'DISCONNECTED';
                qrCodeData = null;
                linkedUser = null;
                
                console.log(`❌ Connection closed. Reconnecting: ${shouldReconnect}`);
                if (shouldReconnect) {
                    setTimeout(startWhatsApp, 3000);
                } else {
                    // Session wiped by logout, delete files
                    try {
                        fs.rmSync(authFolder, { recursive: true, force: true });
                    } catch (e) {}
                }
            }
        });

        sock.ev.on('creds.update', saveCreds);

    } catch (err) {
        console.error('❌ Failed to start WhatsApp Socket:', err);
    }
}

// API: Get current connection state and paired user details
app.get('/status', (req, res) => {
    res.json({
        state: connectionState,
        user: linkedUser ? { id: linkedUser.id, name: linkedUser.name } : null
    });
});

// API: Fetch latest connection QR code as data URL
app.get('/qr', (req, res) => {
    res.json({ qr: qrCodeData });
});

// API: Disconnect WhatsApp session and wipe credentials
app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {}
            sock.end();
            sock = null;
        }
        
        // Delete authorization session folder
        try {
            fs.rmSync(authFolder, { recursive: true, force: true });
        } catch (e) {}

        connectionState = 'DISCONNECTED';
        qrCodeData = null;
        linkedUser = null;

        res.json({ success: true, message: 'Wiped WhatsApp session successfully.' });
        
        // Spawn fresh socket client to listen for new QR
        setTimeout(startWhatsApp, 1500);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// API: Dispatch a text message in the background
app.post('/send-message', async (req, res) => {
    const { phone, message } = req.body;
    if (!phone || !message) {
        return res.status(400).json({ success: false, error: 'Phone and message fields are required.' });
    }

    if (connectionState !== 'CONNECTED' || !sock) {
        return res.status(400).json({ success: false, error: 'WhatsApp backend service is not connected.' });
    }

    try {
        // Clean formatting: remove spaces, symbols, leading zeros, and append whatsapp domain
        let formattedPhone = phone.replace(/[+\s-]/g, '');
        if (formattedPhone.startsWith('0')) {
            formattedPhone = '92' + formattedPhone.substring(1);
        }
        if (!formattedPhone.endsWith('@s.whatsapp.net')) {
            formattedPhone = formattedPhone + '@s.whatsapp.net';
        }

        const result = await sock.sendMessage(formattedPhone, { text: message });
        res.json({ success: true, result });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Start Express API server & launch Baileys socket connection
const PORT = 9001;
app.listen(PORT, '127.0.0.1', () => {
    console.log(`🚀 Background WhatsApp API active on http://127.0.0.1:${PORT}`);
    startWhatsApp();
});
