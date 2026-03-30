<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" />
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/dash/refactor.css" />
    <title>QR Code Details - <?= SITENAME ?></title>
</head>

<body>

    <input type="checkbox" name="" id="nav-toggle">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="las la-qrcode"></span> <span><?= SITENAME ?></span></h2>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="<?= URLROOT ?>/users/index"><span class="las la-igloo"></span>
                        <span>Dashboard</span></a>
                </li>
                <li>
                    <a href="<?= URLROOT ?>/users/myCodes"><span class="las la-qrcode"></span>
                        <span>My Codes</span></a>
                </li>
                <li>
                    <a href="<?= URLROOT ?>/users/analytics"><span class="las la-chart-line"></span>
                        <span>Analytics</span></a>
                </li>
                <li>
                    <a href="<?= URLROOT ?>/bulk"><span class="las la-layer-group"></span>
                        <span>Bulk Generate</span></a>
                </li>
                <li>
                    <a href="<?= URLROOT ?>/barcode"><span class="las la-plus"></span>
                        <span>Generate New</span></a>
                </li>
                <li>
                    <a href="<?= URLROOT ?>/users/logout"><span class="las la-sign-out-alt"></span>
                        <span>Logout</span></a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <header>
            <h2>
                <label for="nav-toggle">
                    <span class="las la-bars"></span>
                </label> <span class="name">QR Code Details</span>
            </h2>
            <div class="search-wrapper">
                <span class="las la-search"></span>
                <input type="search" placeholder="Search here...">
            </div>
            <div class="user-wrapper">
                <img src="<?= URLROOT ?>/public/assets/img/user-avatar.png" width="40px" height="40px" alt="User">
                <div>
                    <h4><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></h4>
                    <small><?= htmlspecialchars($_SESSION['email'] ?? '') ?></small>
                </div>
            </div>
        </header>

        <main>
            <!-- QR Code Overview -->
            <div class="recent-grid">
                <div class="projects">
                    <div class="card">
                        <div class="card-header">
                            <h3>QR Code Information</h3>
                            <button onclick="window.location.href='<?= URLROOT ?>/users/analytics'">Back to Analytics
                                <span class="las la-arrow-left"> </span></button>
                        </div>
                        <div class="card-body">
                            <div class="qr-overview">
                                <div class="qr-image">
                                    <?php if (!empty($data['qr_code']->image_data)): ?>
                                        <img src="<?= $data['qr_code']->image_data ?>" alt="QR Code">
                                    <?php else: ?>
                                        <div class="qr-placeholder">
                                            <span class="las la-qrcode fa-5x text-muted"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="qr-info">
                                    <h4><?= htmlspecialchars($data['qr_code']->value ?? 'Unknown') ?></h4>
                                    <p class="text-muted">Created:
                                        <?= date('M d, Y', strtotime($data['qr_code']->created_at ?? 'now')) ?></p>
                                    <div class="qr-actions">
                                        <a href="<?= URLROOT ?>/users/downloadCode/<?= $data['qr_code']->id ?? '#' ?>"
                                            class="btn btn-primary">
                                            <i class="las la-download"></i> Download
                                        </a>
                                        <a href="<?= URLROOT ?>/users/myCodes" class="btn btn-outline-secondary">
                                            <i class="las la-arrow-left"></i> Back to My Codes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customers">
                    <div class="card">
                        <div class="card-header">
                            <h3>Performance Overview</h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-overview">
                                <div class="stat-item">
                                    <h2><?= $data['stats']->total_scans ?? 0 ?></h2>
                                    <span>Total Scans</span>
                                </div>
                                <div class="stat-item">
                                    <h2><?= $data['stats']->unique_scans ?? 0 ?></h2>
                                    <span>Unique Scans</span>
                                </div>
                                <div class="stat-item">
                                    <h2><?= $data['stats']->unique_countries ?? 0 ?></h2>
                                    <span>Countries</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scan Timeline Chart -->
            <div class="projects">
                <div class="card">
                    <div class="card-header">
                        <h3>Scan Timeline</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; position: relative;">
                            <canvas id="scanTimelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device and Location Stats -->
            <div class="recent-grid">
                <div class="projects">
                    <div class="card">
                        <div class="card-header">
                            <h3>Device Types</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($data['device_stats'])): ?>
                                <?php foreach ($data['device_stats'] as $device): ?>
                                    <div class="device-stat">
                                        <div class="device-info">
                                            <span><?= htmlspecialchars($device->device_type ?? 'Unknown') ?></span>
                                            <strong><?= $device->count ?? $device->scan_count ?? 0 ?></strong>
                                        </div>
                                        <div class="device-progress">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar"
                                                    style="width: <?= (($device->count ?? $device->scan_count ?? 0) / ($data['stats']->total_scans ?? 1) * 100) ?>%">
                                                </div>
                                            </div>
                                            <small><?= round(($device->count ?? $device->scan_count ?? 0) / ($data['stats']->total_scans ?? 1) * 100) ?>%</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No device data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="customers">
                    <div class="card">
                        <div class="card-header">
                            <h3>Top Countries</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($data['country_stats'])): ?>
                                <?php foreach ($data['country_stats'] as $country): ?>
                                    <div class="country-stat">
                                        <div class="country-info">
                                            <span><?= htmlspecialchars($country->country ?? 'Unknown') ?></span>
                                            <strong><?= $country->count ?? $country->scan_count ?? 0 ?></strong>
                                        </div>
                                        <div class="country-progress">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar"
                                                    style="width: <?= (($country->count ?? $country->scan_count ?? 0) / ($data['stats']->total_scans ?? 1) * 100) ?>%">
                                                </div>
                                            </div>
                                            <small><?= round(($country->count ?? $country->scan_count ?? 0) / ($data['stats']->total_scans ?? 1) * 100) ?>%</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No location data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans Table -->
            <div class="projects">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Scans</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['recent_scans'])): ?>
                            <div class="table-responsive">
                                <table width="100%">
                                    <thead>
                                        <tr>
                                            <td>Scanned At</td>
                                            <td>Country</td>
                                            <td>City</td>
                                            <td>Device Type</td>
                                            <td>Browser</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['recent_scans'] as $scan): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i:s', strtotime($scan->scanned_at)) ?></td>
                                                <td>
                                                    <span class="status purple">
                                                        <?= htmlspecialchars($scan->country ?? 'Unknown') ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($scan->city ?? 'Unknown') ?></td>
                                                <td>
                                                    <span class="status pink">
                                                        <?= htmlspecialchars($scan->device_type ?? 'Unknown') ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($scan->browser ?? 'Unknown') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <span class="las fa-history fa-3x text-muted mb-3"></span>
                                <p class="text-muted">No scan activity yet for this QR code</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <style>
        .qr-overview {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .qr-image {
            flex-shrink: 0;
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }

        .qr-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .qr-placeholder {
            text-align: center;
        }

        .qr-info {
            flex: 1;
        }

        .qr-info h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .qr-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .stats-overview {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-item h2 {
            color: #007bff;
            margin-bottom: 5px;
        }

        .stat-item span {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .device-stat,
        .country-stat {
            margin-bottom: 15px;
        }

        .device-info,
        .country-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .device-progress,
        .country-progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress {
            flex: 1;
            background: #e9ecef;
            border-radius: 3px;
        }

        .progress-bar {
            background: #007bff;
            height: 100%;
            border-radius: 3px;
        }

        .status.purple {
            background: #8b5cf6;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .status.pink {
            background: #ec4899;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-outline-secondary {
            background: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 768px) {
            .qr-overview {
                flex-direction: column;
                text-align: center;
            }

            .qr-actions {
                justify-content: center;
            }

            .stats-overview {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Scan Timeline Chart
        const ctx = document.getElementById('scanTimelineChart').getContext('2d');
        const timelineData = <?= json_encode($data['scan_timeline'] ?? []) ?>;

        // Only create chart if there's data
        if (timelineData.length > 0) {
            const labels = timelineData.map(item => item.scan_date);
            const data = timelineData.map(item => item.scans_count);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Scans per Day',
                        data: data,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            // Show "No data available" message
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('No scan data available yet', ctx.canvas.width / 2, ctx.canvas.height / 2);
        }
    </script>
</body>

</html>