// ipc-shim.js - Mocks Electron's ipcRenderer for Web Browser and Electron HTTP views
// Implements an Offline-Online Sync Queue for process-sale events.
(function() {
    const listeners = {};

    function saveOfflineSale(payload) {
        let queue = JSON.parse(localStorage.getItem('offline_sales_queue') || '[]');
        queue.push({
            id: 'OFFLINE_' + Date.now(),
            payload: payload
        });
        localStorage.setItem('offline_sales_queue', JSON.stringify(queue));
        if (window.showCustomAlert) {
            window.showCustomAlert('Processed Offline! Queued for auto-sync.', 'warning');
        } else {
            console.log('Processed Offline! Queued for auto-sync.');
        }
    }

    function syncOfflineSales() {
        if (!navigator.onLine) return;
        let queue = JSON.parse(localStorage.getItem('offline_sales_queue') || '[]');
        if (queue.length === 0) return;

        console.log(`Syncing ${queue.length} offline sales...`);
        
        let promises = queue.map(item => {
            return fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'process-sale',
                    data: item.payload
                })
            })
            .then(response => response.json())
            .then(resData => {
                return item.id;
            })
            .catch(err => {
                console.error("Sync failed for item:", item.id, err);
                return null;
            });
        });

        Promise.all(promises).then(results => {
            const syncedIds = results.filter(id => id !== null);
            if (syncedIds.length > 0) {
                let currentQueue = JSON.parse(localStorage.getItem('offline_sales_queue') || '[]');
                currentQueue = currentQueue.filter(item => !syncedIds.includes(item.id));
                localStorage.setItem('offline_sales_queue', JSON.stringify(currentQueue));
                
                if (window.showCustomAlert) {
                    window.showCustomAlert(`Synced ${syncedIds.length} offline bills to database!`, 'success');
                } else {
                    console.log(`Synced ${syncedIds.length} offline bills to database!`);
                }
            }
        });
    }

    // Auto-sync listeners
    window.addEventListener('online', syncOfflineSales);
    setInterval(syncOfflineSales, 30000);

    window.ipcRenderer = {
        on: function(channel, callback) {
            if (!listeners[channel]) {
                listeners[channel] = [];
            }
            listeners[channel].push(callback);
            return this;
        },
        once: function(channel, callback) {
            const onceCallback = (event, ...args) => {
                this.off(channel, onceCallback);
                callback(event, ...args);
            };
            this.on(channel, onceCallback);
            return this;
        },
        off: function(channel, callback) {
            if (!listeners[channel]) return this;
            listeners[channel] = listeners[channel].filter(cb => cb !== callback);
            return this;
        },
        removeAllListeners: function(channel) {
            if (channel) {
                delete listeners[channel];
            } else {
                for (let key in listeners) {
                    delete listeners[key];
                }
            }
            return this;
        },
        rawListeners: function(channel) {
            return listeners[channel] || [];
        },
        send: function(channel, data) {
            // Handle load-page directly client-side
            if (channel === 'load-page') {
                let target = data;
                if (target.endsWith('.html')) {
                    target = target.replace('.html', '.php');
                }
                window.location.href = target;
                return;
            }

            // Offline handle for process-sale
            if (channel === 'process-sale' && !navigator.onLine) {
                saveOfflineSale(data);
                const tempSaleId = 'OFFLINE_' + Date.now();
                setTimeout(() => {
                    this.trigger('sale-success', { saleId: tempSaleId, shouldPrint: data.shouldPrint, offline: true });
                }, 100);
                return;
            }

            // Forward the event to api.php via POST
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: channel,
                    data: data
                })
            })
            .then(response => response.json())
            .then(resData => {
                if (resData && resData.replies && Array.isArray(resData.replies)) {
                    resData.replies.forEach(reply => {
                        this.trigger(reply.channel, reply.data);
                    });
                }
            })
            .catch(err => {
                console.error("IPC Shim Error on channel '" + channel + "':", err);
                if (channel === 'process-sale') {
                    saveOfflineSale(data);
                    const tempSaleId = 'OFFLINE_' + Date.now();
                    this.trigger('sale-success', { saleId: tempSaleId, shouldPrint: data.shouldPrint, offline: true });
                }
            });
        },
        // Helper to trigger listeners programmatically
        trigger: function(channel, data) {
            if (listeners[channel]) {
                const callbacks = [...listeners[channel]];
                callbacks.forEach(callback => {
                    try {
                        callback(null, data);
                    } catch (e) {
                        console.error("Error in listener for channel '" + channel + "':", e);
                    }
                });
            }

            if (channel === 'login-response' && data && data.success) {
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 500);
            }
        }
    };
})();

