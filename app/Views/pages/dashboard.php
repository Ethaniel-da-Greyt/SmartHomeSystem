<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- =========================================================
 DASHBOARD OVERVIEW
 You can replace static values with database values later
========================================================= -->

<div class="container-fluid">

    <!-- ================= SUMMARY CARDS ================= -->
    <div class="row g-3 mb-4">

        <!-- TOTAL CONSUMPTION -->
        <div class="col-md-4 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Consumption (This Month)</h6>
                    <h3 class="fw-bold">320 kWh</h3>
                    <!-- GUIDE: Replace 320 with computed monthly total -->
                </div>
            </div>
        </div>

        <!-- AVERAGE DAILY -->
        <div class="col-md-4 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Average Daily Usage</h6>
                    <h3 class="fw-bold">10.6 kWh</h3>
                    <!-- GUIDE: total_kwh / days_of_month -->
                </div>
            </div>
        </div>

        <!-- ESTIMATED COST -->
        <div class="col-md-4 col-sm-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Estimated Cost</h6>
                    <h3 class="fw-bold">₱ 3,840.00</h3>
                    <!-- GUIDE: kWh * rate_per_kwh -->
                </div>
            </div>
        </div>

    </div>

    <!-- ================= CHART SECTION ================= -->
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h5 class="mb-3 fw-bold">Monthly Electricity Consumption</h5>

            <!-- GUIDE:
             This canvas is where Chart.js will render the graph.
             You can switch to bar, line, or area chart easily.
            -->
            <canvas id="electricityChart" height="100"></canvas>

        </div>
    </div>

</div>

<!-- ================= CHART.JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    /*
    ==========================================================
    GUIDE: MONTHLY ELECTRICITY DATA
    - Replace these values with PHP variables later
    - Example: pass data from controller to view
    ==========================================================
    */

    const days = 31;
    const labels = [];

    for (let i = 1; i <= days; i++) {
        labels.push(`Day ${i}`);
    }

    const data = {
        labels: labels,
        datasets: [{
            label: 'kWh Used',
            data: [8, 10, 12, 9, 11, 13, 10, 14, 12, 9, 10, 11, 15, 13, 12, 10, 20, 80, 100, 240, 34, 23, 10, 12, 10, 11, 90, 80, 40, 50, 10    ],
            borderWidth: 2,
            fill: true,
            tension: 0.3
            // GUIDE: Remove "fill" if you want a simple line chart
        }]
    };

    const config = {
        type: 'line', // GUIDE: change to 'bar' if you want bar chart
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'kWh'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day of Month'
                    }
                }
            }
        }
    };

    new Chart(
        document.getElementById('electricityChart'),
        config
    );
</script>

<?= $this->endSection() ?>