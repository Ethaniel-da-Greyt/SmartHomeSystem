<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Remote Control
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->section('navbar') ?> Remote Control <?= $this->endSection() ?>

<style>
    #voiceFeedback {
        transition: all 0.2s ease;
        min-height: 24px;
    }

    .btn-danger i,
    .btn-outline-primary i {
        transition: transform 0.2s ease;
    }

    .btn-danger:hover i {
        transform: scale(1.1);
    }

    .listening-pulse {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }

        100% {
            opacity: 1;
        }
    }
</style>
<div class="container">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Device Remote Control</h2>
        <p class="text-muted">Turn your device ON or OFF remotely</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card shadow border-0 text-center p-4">
                <h5 class="mb-3">Main Power</h5>
                <div id="statusBadge" class="badge bg-secondary mb-3">Checking status...</div>
                <button id="toggleBtn" class="btn btn-lg btn-outline-secondary w-100 py-3">
                    <i class="bi bi-power"></i> Toggle Power
                </button>

                <!-- Voice Control Section -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="mb-3"><i class="bi bi-mic"></i> Voice Control</h6>

                    <button id="voiceBtn" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-mic"></i> Start Voice Command
                    </button>

                    <div id="voiceStatus" class="small text-muted mt-2">
                        Click the button and say "ON" or "OFF"
                    </div>

                    <div id="voiceFeedback" class="small mt-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script>
    const deviceId = "859E39";
    const statusBadge = document.getElementById('statusBadge');
    const toggleBtn = document.getElementById('toggleBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    const voiceFeedback = document.getElementById('voiceFeedback');

    let currentState = 'OFF';
    let recognition = null;
    let isListening = false;

    // ================= INITIALIZE VOICE CONTROL =================
    function initializeVoiceControl() {
        // Check browser support
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            voiceBtn.disabled = true;
            voiceStatus.innerHTML = '<span class="text-danger">Voice not supported in this browser</span>';
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        try {
            recognition = new SpeechRecognition();
        } catch (e) {
            console.error("Speech Recognition not available:", e);
            voiceBtn.disabled = true;
            voiceStatus.innerHTML = '<span class="text-danger">Voice recognition unavailable</span>';
            return;
        }

        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        recognition.maxAlternatives = 1;

        // Event handlers
        recognition.onstart = () => {
            isListening = true;
            voiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i> Listening...';
            voiceBtn.className = "btn btn-danger w-100 mb-2";
            voiceStatus.innerHTML = '<span class="text-primary">Listening... Say "ON" or "OFF"</span>';
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript
                .trim()
                .toLowerCase();

            voiceFeedback.innerHTML = `<span class="text-info">Heard: "${transcript}"</span>`;

            // Normalize words
            const words = transcript.split(/\s+/);

            if (words.includes('on')) {
                if (currentState === 'ON') {
                    voiceFeedback.innerHTML = '<span class="text-warning">Device is already ON</span>';
                    return;
                }
                setDeviceState('ON');

            } else if (words.includes('off')) {
                if (currentState === 'OFF') {
                    voiceFeedback.innerHTML = '<span class="text-warning">Device is already OFF</span>';
                    return;
                }
                setDeviceState('OFF');

            } else {
                voiceFeedback.innerHTML =
                    '<span class="text-warning">Say exactly "ON" or "OFF"</span>';
            }
        };

        recognition.onerror = (event) => {
            isListening = false;
            resetVoiceButton();

            switch (event.error) {
                case 'not-allowed':
                case 'permission-denied':
                    voiceStatus.innerHTML = '<span class="text-danger">Microphone access denied. Please allow microphone permission.</span>';
                    voiceBtn.disabled = true;
                    setTimeout(() => {
                        voiceBtn.disabled = false;
                        voiceStatus.innerHTML = 'Click to try again';
                    }, 3000);
                    break;
                case 'no-speech':
                    voiceStatus.innerHTML = '<span class="text-warning">No speech detected. Try again.</span>';
                    setTimeout(() => startVoiceRecognition(), 1000);
                    break;
                default:
                    voiceStatus.innerHTML = '<span class="text-warning">Error occurred. Try again.</span>';
                    setTimeout(() => {
                        voiceStatus.innerHTML = 'Click the button and say "ON" or "OFF"';
                    }, 2000);
            }
        };

        recognition.onend = () => {
            isListening = false;
            resetVoiceButton();
        };
    }

    function resetVoiceButton() {
        voiceBtn.innerHTML = '<i class="bi bi-mic"></i> Start Voice Command';
        voiceBtn.className = "btn btn-outline-primary w-100 mb-2";
    }

    // ================= START VOICE RECOGNITION =================
    function startVoiceRecognition() {
        if (!recognition || isListening) return;

        // Try direct approach first
        try {
            recognition.start();
        } catch (directError) {
            // Try with getUserMedia first
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    audio: true
                })
                    .then((stream) => {
                        // Stop the stream immediately (we just needed permission)
                        stream.getTracks().forEach(track => track.stop());

                        try {
                            recognition.start();
                        } catch (e) {
                            voiceStatus.innerHTML = '<span class="text-danger">Failed to start voice recognition</span>';
                        }
                    })
                    .catch(err => {
                        voiceStatus.innerHTML = '<span class="text-danger">Microphone access required. Please allow microphone.</span>';
                    });
            } else {
                voiceStatus.innerHTML = '<span class="text-danger">Browser does not support microphone access</span>';
            }
        }
    }

    // ================= VOICE BUTTON CLICK =================
    voiceBtn.addEventListener('click', startVoiceRecognition);

    // ================= TOGGLE DEVICE FUNCTION =================
    async function toggleDevice() {
        // Determine what state to set based on current state
        const newState = currentState === 'ON' ? 'OFF' : 'ON';

        try {
            const res = await fetch("<?= base_url('smarthome/api/device/toggle') ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    device_id: deviceId,
                    state: newState
                })
            });

            const data = await res.json();

            if (data.state) {
                updateUI(data.state.toUpperCase());
                voiceFeedback.innerHTML = `<span class="text-success">Device turned ${data.state.toUpperCase()}!</span>`;

                // Auto-restart listening after 2 seconds
                setTimeout(() => {
                    if (!isListening) {
                        startVoiceRecognition();
                    }
                }, 2000);
            }
        } catch (err) {
            console.error("Toggle failed:", err);
            voiceFeedback.innerHTML = '<span class="text-danger">Failed to toggle device</span>';
        }
    }

    async function setDeviceState(state) {
        try {
            const res = await fetch("<?= base_url('smarthome/api/device/toggle') ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    device_id: deviceId,
                    state: state
                })
            });

            const data = await res.json();

            if (data.state) {
                updateUI(data.state.toUpperCase());
                voiceFeedback.innerHTML =
                    `<span class="text-success">Device turned ${data.state.toUpperCase()}!</span>`;
            }
        } catch (err) {
            console.error(err);
            voiceFeedback.innerHTML =
                '<span class="text-danger">Failed to control device</span>';
        }
    }

    // ================= LOAD DEVICE STATUS =================
    async function loadStatus() {
        try {
            const res = await fetch(`<?= base_url('smarthome/api/device/state') ?>/${deviceId}`);
            const data = await res.json();
            if (data.state) updateUI(data.state.toUpperCase());
        } catch (err) {
            console.error(err);
            statusBadge.className = "badge bg-secondary mb-3";
            statusBadge.innerText = "Offline";
        }
    }

    function updateUI(state) {
        currentState = state;
        if (state === 'ON') {
            statusBadge.className = "badge bg-success mb-3";
            statusBadge.innerText = "DEVICE ON";
            toggleBtn.className = "btn btn-lg btn-danger w-100 py-3";
            toggleBtn.innerHTML = '<i class="bi bi-power"></i> Turn OFF';
        } else {
            statusBadge.className = "badge bg-danger mb-3";
            statusBadge.innerText = "DEVICE OFF";
            toggleBtn.className = "btn btn-lg btn-success w-100 py-3";
            toggleBtn.innerHTML = '<i class="bi bi-power"></i> Turn ON';
        }
    }

    // ================= INITIALIZE =================
    toggleBtn.addEventListener('click', async () => {
        await toggleDevice();
    });

    // Initialize everything
    loadStatus();
    setInterval(loadStatus, 3000);
    initializeVoiceControl();
</script> -->


<script>
    const deviceId = "859E39";
    const statusBadge = document.getElementById('statusBadge');
    const toggleBtn = document.getElementById('toggleBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    const voiceFeedback = document.getElementById('voiceFeedback');

    let currentState = 'OFF';
    let recognition = null;
    let isListening = false;
    let restartTimeout = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 3;

    // ================= INITIALIZE VOICE CONTROL =================
    function initializeVoiceControl() {
        // Check browser support
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            voiceBtn.disabled = true;
            voiceStatus.innerHTML = '<span class="text-danger">Voice not supported in this browser</span>';
            return false;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        try {
            recognition = new SpeechRecognition();
        } catch (e) {
            console.error("Speech Recognition not available:", e);
            voiceBtn.disabled = true;
            voiceStatus.innerHTML = '<span class="text-danger">Voice recognition unavailable</span>';
            return false;
        }

        // OPTIMIZATION 1: Continuous mode for faster subsequent commands
        recognition.continuous = true;  // Changed to continuous
        recognition.interimResults = true;  // Changed to true for faster response
        recognition.lang = 'en-US';
        recognition.maxAlternatives = 1;

        // OPTIMIZATION 2: Reduce pause threshold for faster detection
        if ('continuous' in recognition) {
            // Some browsers support this property
            try {
                // @ts-ignore - non-standard property
                recognition.audioEndTimeout = 1000; // 1 second pause before ending
                // @ts-ignore - non-standard property
                recognition.speechEndTimeout = 500; // 0.5 second after speech
            } catch (e) { }
        }

        // Event handlers
        recognition.onstart = () => {
            isListening = true;
            reconnectAttempts = 0;
            voiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i> Listening...';
            voiceBtn.className = "btn btn-danger w-100 mb-2";
            voiceStatus.innerHTML = '<span class="text-primary">🎤 Always listening... Say "ON" or "OFF"</span>';
        };

        // OPTIMIZATION 3: Process interim results for faster reaction
        recognition.onresult = (event) => {
            let finalTranscript = '';
            let interimTranscript = '';

            // Process results
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                const transcript = event.results[i][0].transcript.trim().toLowerCase();

                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript + ' ';

                    // OPTIMIZATION 4: React to partial matches immediately
                    if (transcript.includes('on') && !finalTranscript.includes('on')) {
                        handleVoiceCommand('on', true); // true indicates interim
                    } else if (transcript.includes('off') && !finalTranscript.includes('off')) {
                        handleVoiceCommand('off', true);
                    }
                }
            }

            // Process final results
            if (finalTranscript) {
                const words = finalTranscript.split(/\s+/);
                if (words.includes('on')) {
                    handleVoiceCommand('on', false);
                } else if (words.includes('off')) {
                    handleVoiceCommand('off', false);
                }
            }

            // Show what we're hearing
            if (interimTranscript) {
                voiceFeedback.innerHTML = `<span class="text-info">Heard: "${interimTranscript}"</span>`;
            }
        };

        recognition.onerror = (event) => {
            console.log("Recognition error:", event.error);

            switch (event.error) {
                case 'not-allowed':
                case 'permission-denied':
                    isListening = false;
                    resetVoiceButton();
                    voiceStatus.innerHTML = '<span class="text-danger">Microphone access denied. Please allow microphone permission.</span>';
                    voiceBtn.disabled = true;
                    setTimeout(() => {
                        voiceBtn.disabled = false;
                        voiceStatus.innerHTML = 'Click to try again';
                    }, 3000);
                    break;

                case 'no-speech':
                    // Don't treat as error, just continue listening
                    break;

                case 'aborted':
                    // Will restart if intentional restart
                    break;

                default:
                    // For other errors, attempt to reconnect
                    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                        reconnectAttempts++;
                        setTimeout(() => {
                            if (!isListening) {
                                startVoiceRecognition();
                            }
                        }, 1000);
                    } else {
                        isListening = false;
                        resetVoiceButton();
                        voiceStatus.innerHTML = '<span class="text-warning">Voice recognition unavailable. Click to restart.</span>';
                    }
            }
        };

        recognition.onend = () => {
            // OPTIMIZATION 5: Auto-restart if we should still be listening
            if (isListening && reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                // Clear any existing restart timeout
                if (restartTimeout) {
                    clearTimeout(restartTimeout);
                }

                // Quick restart
                restartTimeout = setTimeout(() => {
                    if (isListening) {
                        try {
                            recognition.start();
                        } catch (e) {
                            console.log("Restart failed, will retry");
                            reconnectAttempts++;
                        }
                    }
                }, 100);
            } else {
                isListening = false;
                resetVoiceButton();
            }
        };

        return true;
    }

    // OPTIMIZATION 6: Centralized command handler
    function handleVoiceCommand(command, isInterim = false) {
        // Don't act on interim if device is already in that state
        if (isInterim && ((command === 'on' && currentState === 'ON') || (command === 'off' && currentState === 'OFF'))) {
            return;
        }

        // Visual feedback immediately
        voiceFeedback.innerHTML = `<span class="text-success">🎯 Executing: ${command.toUpperCase()}</span>`;

        if (command === 'on') {
            if (currentState === 'ON') {
                voiceFeedback.innerHTML = '<span class="text-warning">Device is already ON</span>';
                return;
            }
            setDeviceState('ON');
        } else if (command === 'off') {
            if (currentState === 'OFF') {
                voiceFeedback.innerHTML = '<span class="text-warning">Device is already OFF</span>';
                return;
            }
            setDeviceState('OFF');
        }
    }

    function resetVoiceButton() {
        voiceBtn.innerHTML = '<i class="bi bi-mic"></i> Start Voice Command';
        voiceBtn.className = "btn btn-outline-primary w-100 mb-2";
    }

    // ================= START VOICE RECOGNITION =================
    function startVoiceRecognition() {
        if (!recognition) {
            if (!initializeVoiceControl()) {
                return;
            }
        }

        if (isListening) {
            // If already listening, stop and restart
            try {
                isListening = false;
                recognition.stop();
                setTimeout(() => {
                    startVoiceRecognition();
                }, 100);
            } catch (e) { }
            return;
        }

        // Request microphone permission
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then((stream) => {
                    // Stop the stream immediately (we just needed permission)
                    stream.getTracks().forEach(track => track.stop());

                    // Start recognition
                    try {
                        isListening = true;
                        recognition.start();
                    } catch (e) {
                        voiceStatus.innerHTML = '<span class="text-danger">Failed to start voice recognition</span>';
                        isListening = false;
                    }
                })
                .catch(err => {
                    voiceStatus.innerHTML = '<span class="text-danger">Microphone access required. Please allow microphone.</span>';
                });
        } else {
            try {
                isListening = true;
                recognition.start();
            } catch (e) {
                voiceStatus.innerHTML = '<span class="text-danger">Failed to start voice recognition</span>';
                isListening = false;
            }
        }
    }

    // ================= STOP VOICE RECOGNITION =================
    function stopVoiceRecognition() {
        isListening = false;
        reconnectAttempts = MAX_RECONNECT_ATTEMPTS; // Prevent auto-restart

        if (restartTimeout) {
            clearTimeout(restartTimeout);
            restartTimeout = null;
        }

        if (recognition) {
            try {
                recognition.stop();
            } catch (e) { }
        }

        resetVoiceButton();
        voiceStatus.innerHTML = 'Click the button and say "ON" or "OFF"';
    }

    // ================= VOICE BUTTON CLICK =================
    voiceBtn.addEventListener('click', () => {
        if (isListening) {
            stopVoiceRecognition();
        } else {
            startVoiceRecognition();
        }
    });

    // ================= TOGGLE DEVICE FUNCTION =================
    async function toggleDevice() {
        const newState = currentState === 'ON' ? 'OFF' : 'ON';
        await setDeviceState(newState);
    }

    async function setDeviceState(state) {
        try {
            const res = await fetch("<?= base_url('smarthome/api/device/toggle') ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    device_id: deviceId,
                    state: state
                })
            });

            const data = await res.json();

            if (data.state) {
                updateUI(data.state.toUpperCase());
                voiceFeedback.innerHTML =
                    `<span class="text-success">✅ Device turned ${data.state.toUpperCase()}!</span>`;
            }
        } catch (err) {
            console.error(err);
            voiceFeedback.innerHTML =
                '<span class="text-danger">❌ Failed to control device</span>';
        }
    }

    // ================= LOAD DEVICE STATUS =================
    async function loadStatus() {
        try {
            const res = await fetch(`<?= base_url('smarthome/api/device/state') ?>/${deviceId}`);
            const data = await res.json();
            if (data.state) updateUI(data.state.toUpperCase());
        } catch (err) {
            console.error(err);
            statusBadge.className = "badge bg-secondary mb-3";
            statusBadge.innerText = "Offline";
        }
    }

    function updateUI(state) {
        currentState = state;
        if (state === 'ON') {
            statusBadge.className = "badge bg-success mb-3";
            statusBadge.innerText = "🔴 DEVICE ON";
            toggleBtn.className = "btn btn-lg btn-danger w-100 py-3";
            toggleBtn.innerHTML = '<i class="bi bi-power"></i> Turn OFF';
        } else {
            statusBadge.className = "badge bg-danger mb-3";
            statusBadge.innerText = "⚫ DEVICE OFF";
            toggleBtn.className = "btn btn-lg btn-success w-100 py-3";
            toggleBtn.innerHTML = '<i class="bi bi-power"></i> Turn ON';
        }
    }

    // ================= INITIALIZE =================
    toggleBtn.addEventListener('click', async () => {
        await toggleDevice();
    });

    // Initialize everything
    loadStatus();
    setInterval(loadStatus, 3000);
    initializeVoiceControl();

    // OPTIMIZATION 7: Start listening automatically after page load
    window.addEventListener('load', () => {
        // Small delay to ensure everything is ready
        setTimeout(() => {
            startVoiceRecognition();
        }, 1000);
    });
</script>

<?= $this->endSection() ?>