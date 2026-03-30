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
    <title>My Codes - <?= SITENAME ?></title>
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
                    <a href="<?= URLROOT ?>/users/myCodes" class="active"><span class="las la-qrcode"></span>
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
                </label> <span class="name">My Codes</span>
            </h2>
            <div class="search-wrapper">
                <span class="las la-search"></span>
                <input type="search" placeholder="Search codes...">
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
            <!-- Stats Overview -->
            <div class="cards">
                <div class="card-single">
                    <div>
                        <h1><?= count($data['codes'] ?? []) ?></h1>
                        <span>Total Codes</span>
                    </div>
                    <div>
                        <span class="las la-qrcode"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= count(array_filter($data['codes'] ?? [], fn($c) => $c['type'] === 'qrcode')) ?></h1>
                        <span>QR Codes</span>
                    </div>
                    <div>
                        <span class="las la-qrcode"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= count(array_filter($data['codes'] ?? [], fn($c) => $c['type'] === 'barcode')) ?></h1>
                        <span>Barcodes</span>
                    </div>
                    <div>
                        <span class="las fa-barcode"></span>
                    </div>
                </div>

                <div class="card-single">
                    <div>
                        <h1><?= count(array_filter($data['codes'] ?? [], fn($c) => !empty($c['format']))) ?></h1>
                        <span>With Format</span>
                    </div>
                    <div>
                        <span class="las la-palette"></span>
                    </div>
                </div>
            </div>

            <!-- Codes Grid -->
            <div class="projects">
                <div class="card">
                    <div class="card-header">
                        <h3>Your QR Codes</h3>
                        <button onclick="window.location.href='<?= URLROOT ?>/barcode'">Generate New <span class="las la-arrow-right"> </span></button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['codes'])): ?>
                            <div class="codes-grid">
                                <?php foreach ($data['codes'] as $code): ?>
                                    <div class="code-card">
                                        <div class="code-image">
                                            <img src="<?= htmlspecialchars($code['image_data_uri']) ?>" alt="Code Image">
                                        </div>
                                        <div class="code-info">
                                            <div class="code-type">
                                                <span class="status <?= $code['type'] === 'qrcode' ? 'purple' : 'pink' ?>">
                                                    <?= htmlspecialchars(strtoupper($code['type'])) ?>
                                                </span>
                                                <?php if (!empty($code['format'])): ?>
                                                    <span class="format"><?= htmlspecialchars($code['format']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="code-value">
                                                <small><?= htmlspecialchars(substr($code['value'], 0, 30)) ?>...</small>
                                            </div>
                                            <div class="code-date">
                                                <small><?= date('M d, Y', strtotime($code['created_at'] ?? 'now')) ?></small>
                                            </div>
                                        </div>
                                        <div class="code-actions">
                                            <a href="<?= URLROOT ?>/users/downloadCode/<?= $code['id'] ?>" class="action-btn" title="Download">
                                                <span class="las la-download"></span>
                                            </a>
                                            <a href="<?= URLROOT ?>/users/qrDetails/<?= $code['id'] ?>" class="action-btn" title="Analytics">
                                                <span class="las la-chart-line"></span>
                                            </a>
                                            <button class="action-btn delete-btn" title="Delete" data-id="<?= $code['id'] ?>">
                                                <span class="las la-trash"></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <span class="las la-qrcode fa-4x text-muted mb-3"></span>
                                <h4 class="text-muted">No QR codes yet</h4>
                                <p class="text-muted mb-4">Create your first QR code to get started!</p>
                                <button onclick="window.location.href='<?= URLROOT ?>/barcode'" class="btn btn-primary">
                                    <i class="las la-plus"></i> Create Your First QR Code
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Download All Section -->
            <?php if (!empty($data['codes'])): ?>
            <div class="projects">
                <div class="card">
                    <div class="card-header">
                        <h3>Bulk Actions</h3>
                    </div>
                    <div class="card-body">
                        <button onclick="window.location.href='<?= URLROOT ?>/barcode/downloadAll'" class="btn btn-success">
                            <i class="las la-download"></i> Download All Codes
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <style>
        .codes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .code-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .code-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .code-image {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            padding: 20px;
        }

        .code-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .code-info {
            padding: 15px;
        }

        .code-type {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .code-value {
            margin-bottom: 8px;
            min-height: 40px;
        }

        .code-date {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .code-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .code-card:hover .code-actions {
            opacity: 1;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #6c757d;
        }

        .action-btn:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .action-btn.delete-btn:hover {
            background: #dc3545;
            border-color: #dc3545;
        }

        .status.purple {
            background: #8b5cf6;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status.pink {
            background: #ec4899;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .format {
            background: #f1f3f4;
            color: #5f6368;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.7rem;
        }

        .text-center.py-5 {
            text-align: center;
            padding: 3rem 0;
        }

        .fa-4x {
            font-size: 4rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        @media (max-width: 768px) {
            .codes-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .codes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Delete functionality
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this QR code?')) {
                    const codeId = this.dataset.id;
                    // Add delete functionality here
                    console.log('Delete code:', codeId);
                }
            });
        });

        // Search functionality
        document.querySelector('input[type="search"]').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.code-card').forEach(card => {
                const value = card.querySelector('.code-value small').textContent.toLowerCase();
                card.style.display = value.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>

</html>
