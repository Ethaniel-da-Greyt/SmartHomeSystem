<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Remote Control
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container">

    <!-- ================= HEADER ================= -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">Device Remote Control</h2>
        <p class="text-muted">Turn your device ON or OFF remotely</p>
    </div>

    <!-- ================= CONTROL CARD ================= -->
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-12">

            <div class="card shadow border-0 text-center p-4">
                <h5 class="mb-3">Main Power</h5>

                <!-- STATUS INDICATOR -->
                <div id="statusBadge" class="badge bg-secondary mb-3">
                    Checking status...
                </div>

                <!-- TOGGLE BUTTON -->
                <button id="toggleBtn" class="btn btn-lg btn-outline-secondary w-100 py-3">
                    <i class="bi bi-power"></i>
                    Toggle Power
                </button>

                <!-- GUIDE -->
                <!-- You can add animation or sound alert here later -->
            </div>

        </div>
    </div>

</div>

<script>
    const deviceId = "ESP001";

    const statusBadge = document.getElementById('statusBadge');
    const toggleBtn = document.getElementById('toggleBtn');

    let currentState = 0;

    // ================= LOAD DEVICE STATUS =================
    fetch(`<?= base_url('api/device/status') ?>/${deviceId}`)
        .then(res => res.json())
        .then(data => {
            updateUI(data.is_on);
        });

    // ================= TOGGLE DEVICE =================
    toggleBtn.addEventListener('click', () => {
        const newState = currentState === 1 ? 0 : 1;

        fetch("<?= base_url('api/device/toggle') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                device_id: deviceId,
                is_on: newState
            })
        })
        .then(res => res.json())
        .then(() => {
            updateUI(newState);
        });
    });

    // ================= UI UPDATE =================
    function updateUI(state) {
        currentState = state;

        if (state == 1) {
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
