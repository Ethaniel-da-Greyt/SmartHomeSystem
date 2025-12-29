<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->section('navbar') ?> Dashboard <?= $this->endSection() ?>
<div class="container-fluid">
    <form method="get" action="<?= base_url('dashboard') ?>" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="month" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-2">
            <select name="year" class="form-select">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>


    <!-- ================= SUMMARY CARDS ================= -->
    <div class="row g-3 mb-4">

        <!-- TOTAL CONSUMPTION -->
        <div class="col-md-6 col-sm-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Consumption (This Month)</h6>
                    <h3 class="fw-bold"><?= number_format(array_sum($monthlyKwh ?? []), 4) ?> kWh</h3>
                </div>
            </div>
        </div>

        <!-- AVERAGE DAILY -->
        <div class="col-md-6 col-sm-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Average Daily Usage</h6>
                    <?php
                    $daysCount = count($monthlyKwh ?? []);
                    $totalKwh = array_sum($monthlyKwh ?? []);
                    $avgDaily = $daysCount ? ($totalKwh / $daysCount) : 0;
                    ?>
                    <h3 class="fw-bold"><?= number_format($avgDaily, 4) ?> kWh</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= CHART SECTION ================= -->
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <?php
            $monthName = isset($selectedMonth)
                ? date('F', mktime(0, 0, 0, $selectedMonth, 1))
                : date('F');
            ?>
            <h5 class="mb-3 fw-bold">Monthly Electricity Consumption
                <strong class="text-success">(<?= esc(strtoupper($monthName)) ?> <?= esc($selectedYear ?? date('Y')) ?>)</strong></h5>
            <canvas id="electricityChart" height="100"></canvas>

        </div>
    </div>

</div>

<!-- ================= CHART.JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Preserve small decimals in chart

    const monthlyKwh = <?= json_encode(array_map('floatval', $monthlyKwh ?? [])) ?>;
    // Use full date labels passed from controller (e.g., "Dec 27, 2025")
    const labels = <?= json_encode($labels ?? []) ?>;

    const data = {
        labels: labels,
        datasets: [{
            label: 'kWh Used',
            data: monthlyKwh,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0,123,255,0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'kWh' }
                },
                x: {
                    title: { display: true, text: 'Date' }
                }
            }
        }
    };

    new Chart(document.getElementById('electricityChart'), config);
</script>

<?= $this->endSection() ?>