/**
 * LOGISTIA FLEET TRACKER
 * Tracking GPS automatico per tablet sui furgoni
 *
 * USO: FleetTracker.init('TOKEN_DISPOSITIVO')
 */

const FleetTracker = {
    config: {
        apiBaseUrl: '/api/tracking',
        intervalMs: 30000,              // Ogni 30 secondi
        maxOfflinePositions: 1000,
        minAccuracy: 100,
    },

    state: {
        deviceToken: null,
        isActive: false,
        isConfigured: false,
        needsSetup: false,
        intervalId: null,
        offlineBuffer: [],
        mezzo: null,
    },

    async init(deviceToken) {
        if (!deviceToken) {
            console.error('[FleetTracker] Token mancante');
            return false;
        }

        this.state.deviceToken = deviceToken;
        this.loadOfflineBuffer();

        const status = await this.checkStatus();

        if (!status.success) {
            console.error('[FleetTracker] Errore:', status.error);
            return false;
        }

        this.state.isConfigured = status.configurato;
        this.state.needsSetup = status.needs_setup;
        this.state.mezzo = status.mezzo;

        if (this.state.needsSetup) {
            this.showSetupModal();
            return true;
        }

        this.start();
        return true;
    },

    async checkStatus() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({ device_token: this.state.deviceToken })
            });
            return await response.json();
        } catch (e) {
            return { success: false, error: e.message };
        }
    },

    start() {
        if (this.state.isActive) return;

        if (!('geolocation' in navigator)) {
            this.showError('Geolocalizzazione non supportata');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            () => {
                this.state.isActive = true;
                this.state.intervalId = setInterval(() => this.captureAndSend(), this.config.intervalMs);
                this.captureAndSend();
                window.addEventListener('online', () => this.flushOfflineBuffer());
                console.log('[FleetTracker] Tracking avviato ✓');
                this.showToast('Tracking attivo', 'success');
            },
            (error) => {
                this.showError('Attiva la geolocalizzazione');
            },
            { enableHighAccuracy: true }
        );
    },

    stop() {
        if (this.state.intervalId) {
            clearInterval(this.state.intervalId);
            this.state.intervalId = null;
        }
        this.state.isActive = false;
    },

    async captureAndSend() {
        try {
            const position = await this.getCurrentPosition();

            if (position.coords.accuracy > this.config.minAccuracy) return;

            const data = {
                device_token: this.state.deviceToken,
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                speed: this.convertSpeed(position.coords.speed),
                heading: Math.round(position.coords.heading || 0),
                accuracy: Math.round(position.coords.accuracy),
                altitude: position.coords.altitude ? Math.round(position.coords.altitude) : null,
                battery: await this.getBatteryLevel(),
                timestamp: Math.floor(position.timestamp / 1000)
            };

            if (navigator.onLine) {
                await this.sendPosition(data);
            } else {
                this.bufferPosition(data);
            }
        } catch (error) {
            console.error('[FleetTracker] Errore:', error);
        }
    },

    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });
    },

    async sendPosition(data) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/position`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.needs_setup) {
                this.stop();
                this.showSetupModal();
            }
        } catch (error) {
            this.bufferPosition(data);
        }
    },

    bufferPosition(data) {
        if (this.state.offlineBuffer.length >= this.config.maxOfflinePositions) {
            this.state.offlineBuffer.shift();
        }
        this.state.offlineBuffer.push(data);
        this.saveOfflineBuffer();
    },

    async flushOfflineBuffer() {
        if (this.state.offlineBuffer.length === 0) return;

        try {
            const response = await fetch(`${this.config.apiBaseUrl}/batch`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    device_token: this.state.deviceToken,
                    positions: this.state.offlineBuffer
                })
            });

            const result = await response.json();

            if (result.success) {
                this.state.offlineBuffer = [];
                this.saveOfflineBuffer();
                this.showToast(`Sincronizzate ${result.processed} posizioni`, 'success');
            }
        } catch (error) {
            console.error('[FleetTracker] Errore batch:', error);
        }
    },

    async submitSetup(kmContachilometri) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/setup`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    device_token: this.state.deviceToken,
                    km_contachilometri: parseInt(kmContachilometri)
                })
            });

            const result = await response.json();

            if (result.success) {
                this.state.isConfigured = true;
                this.state.needsSetup = false;
                this.hideSetupModal();
                this.showToast('Configurazione completata!', 'success');
                this.start();
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('Errore di connessione');
        }
    },

    // === UTILITY ===

    convertSpeed(speedMs) {
        if (!speedMs || speedMs < 0) return 0;
        return Math.round(speedMs * 3.6 * 10) / 10;
    },

    async getBatteryLevel() {
        try {
            if ('getBattery' in navigator) {
                const battery = await navigator.getBattery();
                return Math.round(battery.level * 100);
            }
        } catch (e) {}
        return null;
    },

    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    },

    saveOfflineBuffer() {
        try {
            localStorage.setItem('fleetTrackerBuffer', JSON.stringify(this.state.offlineBuffer));
        } catch (e) {}
    },

    loadOfflineBuffer() {
        try {
            const saved = localStorage.getItem('fleetTrackerBuffer');
            if (saved) this.state.offlineBuffer = JSON.parse(saved);
        } catch (e) {
            this.state.offlineBuffer = [];
        }
    },

    // === UI ===

    showSetupModal() {
        const existing = document.getElementById('fleetTrackerSetupModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'fleetTrackerSetupModal';
        modal.innerHTML = `
            <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;">
                <div style="background:white;padding:30px;border-radius:12px;max-width:400px;width:90%;">
                    <h3 style="margin:0 0 10px;">🚛 Configurazione Tracking</h3>
                    <p style="color:#666;margin-bottom:20px;">
                        ${this.state.mezzo ? `<strong>${this.state.mezzo.nome}</strong> - ${this.state.mezzo.targa}<br>` : ''}
                        Inserisci i km attuali dal contachilometri.
                    </p>
                    <div style="margin-bottom:20px;">
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Km Contachilometri</label>
                        <input type="number" id="fleetTrackerKmInput"
                            style="width:100%;padding:15px;font-size:24px;text-align:center;border:2px solid #ddd;border-radius:8px;"
                            placeholder="Es: 45000" min="0" max="9999999">
                    </div>
                    <button id="fleetTrackerSetupBtn"
                        style="width:100%;padding:15px;font-size:18px;background:#28a745;color:white;border:none;border-radius:8px;cursor:pointer;">
                        ✓ Conferma e Avvia Tracking
                    </button>
                    <p style="margin-top:15px;font-size:12px;color:#999;text-align:center;">
                        Questa operazione si fa solo una volta.
                    </p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('fleetTrackerSetupBtn').addEventListener('click', () => {
            const km = document.getElementById('fleetTrackerKmInput').value;
            if (km && parseInt(km) >= 0) {
                this.submitSetup(km);
            } else {
                alert('Inserisci un valore valido');
            }
        });
    },

    hideSetupModal() {
        const modal = document.getElementById('fleetTrackerSetupModal');
        if (modal) modal.remove();
    },

    showToast(message, type = 'info') {
        const colors = { success: '#28a745', warning: '#ffc107', error: '#dc3545', info: '#17a2b8' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed;bottom:20px;left:50%;transform:translateX(-50%);
            background:${colors[type]};color:white;padding:12px 24px;
            border-radius:8px;z-index:9998;font-size:14px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    },

    showError(message) {
        this.showToast(message, 'error');
    }
};

// Auto-init se c'è token nel DOM
document.addEventListener('DOMContentLoaded', () => {
    const tokenEl = document.getElementById('fleetTrackerToken');
    if (tokenEl && tokenEl.value) {
        FleetTracker.init(tokenEl.value);
    }
});

window.FleetTracker = FleetTracker;