<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $this->renderSection('title') ?? 'Dashboard' ?></title>

    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">

    <!-- Bootstrap Icons CSS -->
    <link href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">



    <style>
        body {
            overflow-x: hidden;
        }

        /* Sidebar */
        #sidebar {
            width: 300px;
            /* extended width */
            min-height: 100vh;
            transition: all 0.3s ease;
            z-index: 1040;
        }

        #sidebar.collapsed {
            margin-left: -300px;
        }

        #sidebar .nav-link {
            white-space: normal;
            line-height: 1.4;
        }

        #sidebar .nav-link i {
            margin-right: 8px;
        }

        /* Content */
        #content {
            width: 100%;
            transition: margin-left 0.3s ease;
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -300px;
                /* start hidden */
                height: 100%;
                background: #212529;
            }

            #sidebar.show {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.4);
                z-index: 1039;
            }

            .overlay.show {
                display: block;
            }
        }
    </style>
</head>

<body>

    <div class="d-flex">

        <!-- SIDEBAR -->
        <nav id="sidebar" class="bg-dark text-white p-3">
            <h5 class="text-center mb-4">Smart Home System</h5>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="<?= base_url('/dashboard') ?>" class="nav-link text-white"><i
                            class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('maintenance') ?>" class="nav-link text-white"><i class="bi bi-cpu-fill"></i>
                        Maintenance Monitoring</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('remote-control') ?>" class="nav-link text-white"><i
                            class="bi bi-toggle-on"></i> Remote Control</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white"><i class="bi bi-gear"></i> Settings</a>
                </li>
            </ul>
        </nav>

        <!-- OVERLAY -->
        <div id="overlay" class="overlay"></div>

        <!-- CONTENT -->
        <div id="content">

            <!-- NAVBAR -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm px-3">
                <button id="toggleSidebar" class="btn btn-outline-secondary me-2"><i class="bi bi-list"></i></button>
                <span class="navbar-brand fw-bold">Dashboard</span>
                <div class="ms-auto">
                    <a href="/auth/logout" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </nav>

            <main class="p-4">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            const overlay = document.getElementById('overlay');

            if (!sidebar || !toggleBtn) return;

            /* ================= TOGGLE ================= */
            toggleBtn.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    // Mobile: slide in/out
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                } else {
                    // Desktop: collapse
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                }
            });

            /* ================= MOBILE OVERLAY CLICK ================= */
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            /* ================= DESKTOP: RESTORE STATE ================= */
            if (window.innerWidth > 768 && localStorage.getItem('sidebar-collapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }

            /* ================= HANDLE RESIZE ================= */
            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                } else {
                    if (sidebar.classList.contains('collapsed')) {
                        sidebar.classList.remove('collapsed');
                    }
                }
            });
        });
    </script>

</body>

</html>