<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <!-- FONTAWESOME -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" />
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/dash/refactor.css" />
    <title><?= SITENAME?> Dashboard</title>
</head>

<body>

    <input type="checkbox" name="" id="nav-toggle">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="las la-qrcode"></span> <span><?= SITENAME?></span></h2>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="<?= URLROOT ?>/users/index" class="active"><span class="las la-igloo"></span>
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
                </label> <span class="name">Dashboard</span>
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
            <div class="cards">
                <div class="card-single">
                    <div>
                        <h1><?= $data['total_codes'] ?? 0 ?></h1>
                        <span>Total Codes</span>
                    </div>
                    <div>
                        <span class="las la-qrcode"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= $data['analytics']['overall_stats']->total_scans ?? 0 ?></h1>
                        <span>Total Scans</span>
                    </div>
                    <div>
                        <span class="las la-eye"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= number_format($data['analytics']['overall_stats']->avg_scans_per_qr ?? 0, 1) ?></h1>
                        <span>Avg Scans/QR</span>
                    </div>
                    <div>
                        <span class="las la-chart-bar"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= $data['analytics']['overall_stats']->unique_countries ?? 0 ?></h1>
                        <span>Countries</span>
                    </div>
                    <div>
                        <span class="las la-globe"></span>
                    </div>
                </div>
            </div>

            <div class="recent-grid">
                <div class="projects">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent QR Codes</h3>
                            <button onclick="window.location.href='<?= URLROOT ?>/users/myCodes'">See all <span class="las la-arrow-right"> </span></button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table width="100%">
                                    <thead>
                                        <tr>
                                            <td>QR Code</td>
                                            <td>Type</td>
                                            <td>Created</td>
                                            <td>Actions</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data['recent_codes'])): ?>
                                            <?php foreach ($data['recent_codes'] as $code): ?>
                                                <tr>
                                                    <td>
                                                        <div class="qr-td">
                                                            <img src="<?= $code['image'] ?>" alt="QR Code" style="width: 40px; height: 40px;">
                                                            <span><?= htmlspecialchars(substr($code['value'], 0, 20)) ?>...</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="status <?= $code['format'] === 'qrcode' ? 'purple' : 'pink' ?>">
                                                            <?= ucfirst($code['format']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M d, Y', strtotime($code['created_at'])) ?></td>
                                                    <td>
                                                        <a href="<?= URLROOT ?>/users/downloadCode/<?= $code['id'] ?>" class="action-btn">
                                                            <span class="las la-download"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 20px;">
                                                    No QR codes yet. <a href="<?= URLROOT ?>/barcode">Create your first QR code</a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customers">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                            <button onclick="window.location.href='<?= URLROOT ?>/users/analytics'">View all <span class="las la-arrow-right"> </span></button>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($data['analytics']['recent_scans'])): ?>
                                <?php foreach (array_slice($data['analytics']['recent_scans'], 0, 6) as $scan): ?>
                                    <div class="customer">
                                        <div class="info">
                                            <div class="scan-icon">
                                                <span class="las la-eye"></span>
                                            </div>
                                            <div>
                                                <h4><?= htmlspecialchars(substr($scan->qr_value, 0, 20)) ?>...</h4>
                                                <small><?= date('M d, Y H:i', strtotime($scan->scanned_at)) ?></small>
                                            </div>
                                        </div>
                                        <div class="contact">
                                            <span class="las la-map-marker-alt" title="<?= htmlspecialchars($scan->country ?? 'Unknown') ?>"></span>
                                            <span class="las la-desktop" title="<?= htmlspecialchars($scan->device_type ?? 'Unknown') ?>"></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 20px;">
                                    <span class="las la-chart-line" style="font-size: 2rem; color: #ccc;"></span>
                                    <p style="color: #999; margin-top: 10px;">No scan activity yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .qr-td {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qr-td img {
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .scan-icon {
            width: 40px;
            height: 40px;
            background: #f0f8ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #007bff;
        }
        
        .action-btn {
            color: #007bff;
            text-decoration: none;
            padding: 5px;
        }
        
        .action-btn:hover {
            color: #0056b3;
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
    </style>
</body>

</html>