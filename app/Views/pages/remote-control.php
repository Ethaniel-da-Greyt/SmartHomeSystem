<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Remote Control
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->section('navbar') ?> Remote Control <?= $this->endSection() ?>

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
            </div>
        </div>
    </div>
</div>

<script>
const deviceId = "859E39"; // fallback
const statusBadge = document.getElementById('statusBadge');
const toggleBtn = document.getElementById('toggleBtn');
let currentState = 'OFF';

// ================= LOAD DEVICE STATUS =================
async function loadStatus() {
    try {
        const res = await fetch(`<?= base_url('smarthome/api/device/state') ?>/${deviceId}`);
        const data = await res.json();

        if (data.state) {
            updateUI(data.state.toUpperCase());
        }
    } catch (err) {
        console.error(err);
        statusBadge.className = "badge bg-secondary mb-3";
        statusBadge.innerText = "Offline";
    }
}

// Poll status every 2–3 seconds
loadStatus();
setInterval(loadStatus, 3000);

// ================= TOGGLE DEVICE =================
toggleBtn.addEventListener('click', async () => {
    const newState = currentState === 'ON' ? 'OFF' : 'ON';

    try {
        const res = await fetch("<?= base_url('smarthome/api/device/toggle') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ device_id: deviceId, state: newState })
        });

        const data = await res.json();

        if (data.state) {
            updateUI(data.state.toUpperCase());
        }
    } catch (err) {
        console.error("Toggle failed:", err);
    }
});

// ================= UI UPDATE =================
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
</script>

<?= $this->endSection() ?>
