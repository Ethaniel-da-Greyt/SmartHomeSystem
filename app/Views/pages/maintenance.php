<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Maintenance Monitoring
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->section('navbar') ?> Maintenance Monitoring <?= $this->endSection() ?>

<div class="container-fluid">

    <!-- ================= HEADER CARDS ================= -->

    <div class="col-12 col-md-6 col-lg-4 mb-4 w-100">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Maintenance Monitoring</h5>
            </div>
        </div>
    </div>

    <!-- ================= DEVICES TABLE ================= -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Device Maintenance Details</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table">
                        <tr>
                            <th>#</th>
                            <th>Device ID</th>
                            <th>Remarks</th>
                            <th>Date Reported</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faults as $f): ?>
                            <tr>
                                <td><?= esc($f['device_id']) ?></td>
                                <td><?= esc($f['fault_message']) ?></td>
                                <td><?= esc($f['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?= $this->endSection() ?>