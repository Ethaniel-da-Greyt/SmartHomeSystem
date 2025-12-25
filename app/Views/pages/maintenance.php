<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
Maintenance Monitoring
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- ================= HEADER CARD ================= -->
    <div class="row mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Devices</h5>
                    <h3 class="fw-bold"><?= $totalDevices ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Devices Active</h5>
                    <h3 class="fw-bold"><?= $activeDevices ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Devices Under Maintenance</h5>
                    <h3 class="fw-bold"><?= $maintenanceDevices ?? 0 ?></h3>
                </div>
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
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Device Name</th>
                            <th>Status</th>
                            <th>Last Checked</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($devices)) : ?>
                            <?php foreach ($devices as $index => $device) : ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= $device['name'] ?></td>
                                    <td>
                                        <?php if ($device['status'] == 'Active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($device['status'] == 'Maintenance'): ?>
                                            <span class="badge bg-warning text-dark">Maintenance</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($device['last_checked'])) ?></td>
                                    <td><?= $device['remarks'] ?? '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No devices found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
