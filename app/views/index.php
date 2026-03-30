<!-- HEAD START -->
<?php include APPROOT . "/views/inc/head.php" ?>

<body>
    <!-- NAVBAR START -->
    <?php include APPROOT . "/views/inc/navbar.php" ?>

    <div class="container mt-5">
        <header class="text-center mb-5 w-75 mx-auto">
            <h1 class="mb-3">QR-Hub.io Universal Generator</h1>
            <p class="mb-3" style="font-size: 1.1rem;">Create <strong>beautiful, scannable QR codes</strong> for links,
                contact details, Wi-Fi, WhatsApp and more. Customize the design and keep everything in one place.</p>
            <div class="progress-steps d-flex justify-content-center align-items-center mb-4" role="progressbar" aria-label="QR generation steps">
                <div class="step active" aria-current="step">
                    <span class="step-number">1</span>
                    <span class="step-text">Choose Type</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-text">Fill Details</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Generate</span>
                </div>
            </div>
        </header>
        <div class="d-flex justify-content-center mb-4">
            <button type="button" class="btn btn-orange me-2">
                <i class="las la-qrcode me-1"></i> QR Code
            </button>

            <a href="<?= URLROOT ?>/barcode" class="btn btn-outline-light">
                <i class="las la-barcode me-1"></i> Barcode
            </a>
        </div>

        <main class="row shadow rounded-0 dark-box">
            <!-- Left Column: QR Code Generator -->
            <section class="col-lg-8 col-md-12 dark-box">
                    <div class="card rounded-0 my-4 border-0">
                        <div class="card-body p-0 border-0 dark-nav">
                            <div class="px-3 pt-3 pb-1 d-flex justify-content-between align-items-center">
                                <span class="text-white-50 small">Pick a QR type</span>
                                <div class="preview-header mb-0">
                                    <h6 class="text-center text-muted mb-0">Step 1 &mdash; Choose Type</h6>
                                </div>
                            </div>
                            <nav class="nav nav-tabs border-0" role="tablist" aria-label="QR code types">
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3 active" id="link-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#link-tab" aria-controls="link-tab" aria-selected="true">
                                        <i class="me-1 las la-link icon-sm" aria-hidden="true"></i>
                                        Link
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="text-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#text-tab" aria-controls="text-tab" aria-selected="false">
                                        <i class="me-1 las la-comment icon-sm" aria-hidden="true"></i>
                                        Text
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="email-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#email-tab" aria-controls="email-tab" aria-selected="false">
                                        <i class="me-1 las la-envelope icon-sm" aria-hidden="true"></i>
                                        E&dash;mail
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="call-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#call-tab" aria-controls="call-tab" aria-selected="false">
                                        <i class="me-1 las la-phone-square icon-sm" aria-hidden="true"></i>
                                        Call
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="sms-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#sms-tab" aria-controls="sms-tab" aria-selected="false">
                                        <i class="me-1 las la-sms icon-sm" aria-hidden="true"></i>
                                        SMS
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="vcard-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#vcard-tab" aria-controls="vcard-tab" aria-selected="false">
                                        <i class="me-1 las la-id-card icon-sm" aria-hidden="true"></i>
                                        V&dash;card
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="whatsapp-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#whatsapp-tab" aria-controls="whatsapp-tab" aria-selected="false">
                                        <i class="me-1 lab la-whatsapp icon-sm" aria-hidden="true"></i>
                                        Whatsapp
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="wifi-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#wifi-tab" aria-controls="wifi-tab" aria-selected="false">
                                        <i class="me-1 las la-wifi icon-sm" aria-hidden="true"></i>
                                        WI&dash;FI
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="paypal-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#paypal-tab" aria-controls="paypal-tab" aria-selected="false">
                                        <i class="me-1 lab la-paypal icon-sm" aria-hidden="true"></i>
                                        Paypal
                                    </button>
                                </li>
                                <li class="nav-item qr-item" role="presentation">
                                    <button class="qr-link p-3" id="app-tab-btn" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#app-tab" aria-controls="app-tab" aria-selected="false">
                                        <i class="me-1 las la-mobile icon-sm" aria-hidden="true"></i>
                                        App
                                    </button>
                                </li>
                            </nav>
                        </div>
                    </div>

                    <div class="card rounded-0 my- border-0 bg-transparent">
                        <div class="card-body pt-0">
                            <div id="tabs" class="tab-content" role="tabpanel">
                                <!-- LINK TAB -->
                                <div class="tab-pane fade show active" id="link-tab" role="tabpanel" aria-labelledby="link-tab-btn">
                                    <form method="POST" id="qrForm" novalidate>
                                        <?php csrf_input(); ?>
                                        <fieldset>
                                            <div class="mb-3">
                                                <label for="qrUrl" class="form-label mb-0">Link Url
                                                    <small class="text-black-50"> (The QR Code will open this URL.) </small>
                                                </label>
                                                <input type="url" name="qrUrl" id="qrUrl"
                                                    class="form-control shadow-none border" placeholder="https://example.com"
                                                    data-validation="url required" required aria-describedby="qrUrlHelp" />
                                                <div id="qrUrlHelp" class="form-text text-muted">Enter a valid URL including https://</div>
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" name="btnUrl"
                                                    class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center" aria-label="Generate QR Code for URL">
                                                    <i class="las la-plus-square las-sm" aria-hidden="true"></i> Generate QR Code
                                                </button>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            <!-- TEXT TAB -->
                            <div id="text-tab" class="tab-pane">
                                <form method="POST" id="qrFormText" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrText" class="form-label mb-0">Text</label>
                                            <div class="mb-3">
                                                <textarea name="qrText" id="qrText" class="form-control shadow-none border"
                                                    placeholder="Write your text here" required data-validation="required"></textarea>
                                                <small class="text-black-50">Scanning the QR code will show this text</small>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" name="btnText"
                                                class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                <i class="las la-plus-square las-sm" aria-hidden="true"></i> Generate QR Code
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- EMAIL TAB -->
                            <div id="email-tab" class="tab-pane">
                                <form method="POST" id="qrFormEmail" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrEmail" class="form-label mb-0">E&dash;mail Content</label>
                                            <input type="email" name="qrEmail" id="qrEmail"
                                                class="form-control shadow-none border"
                                                placeholder="Your E&dash;mail Address" data-validation="email required" required />
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" name="qrSubject" id="qrSubject"
                                                class="form-control shadow-none border" placeholder="E&dash;mail Subject"
                                                data-validation="required" required />
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="qrMessage" id="qrMessage"
                                                class="form-control shadow-none border" placeholder="Your Message"
                                                data-validation="required" required></textarea>
                                            <small class="text-black-50">Scanning the QR code will send this e&dash;mail</small>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" name="btnEmail"
                                                class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- CALL TAB -->
                            <div id="call-tab" class="tab-pane">
                                <form method="POST" id="qrFormCall" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrPhoneNumber" class="form-label mb-0">Phone Call</label>
                                            <div class="mb-3">
                                                <input type="tel" name="qrPhoneNumber" id="qrPhoneNumber"
                                                    class="form-control shadow-none border"
                                                    placeholder="Your Number (+123456789)" data-validation="phone required" required />
                                                <small class="text-black-50">Scanning the QR code will call this number</small>
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" name="btnCall"
                                                    class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                    <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- SMS TAB -->
                            <div id="sms-tab" class="tab-pane">
                                <form method="POST" id="qrFormSMS" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrSMSnum" class="form-label mb-0">SMS</label>
                                            <div class="mb-3">
                                                <input type="tel" name="qrSMSnum" id="qrSMSnum"
                                                    pattern="[+][1-9]{2}\d{3}?\d{6,14}"
                                                    class="form-control shadow-none border"
                                                    placeholder="Your Number (+123456789)" required data-validation="phone required" />
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="qrSMSmsg" id="qrSMSmsg"
                                                    class="form-control shadow-none border" placeholder="Message"
                                                    required data-validation="required"></textarea>
                                                <small class="text-black-50">Scanning the QR code will send SMS to this number</small>
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" name="btnSMS"
                                                    class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                    <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- V-CARD TAB -->
                            <div id="vcard-tab" class="tab-pane">
                                <form method="POST" id="qrFormVCard" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrVcardName" class="form-label mb-0">V-Card</label>
                                            <div class="mb-3">
                                                <input type="text" name="qrVcardName" id="qrVcardName"
                                                    class="form-control shadow-none border" placeholder="Your Name"
                                                    required data-validation="required" />
                                            </div>
                                            <div class="mb-3">
                                                <input type="tel" name="qrVcardNum" id="qrVcardNum"
                                                    pattern="[+][1-9]{2}\d{3}?\d{6,14}"
                                                    class="form-control shadow-none border"
                                                    placeholder="Your Number (+123456789)" required data-validation="phone required" />
                                            </div>
                                            <div class="mb-3">
                                                <input type="email" name="qrVcardEmail" id="qrVcardEmail"
                                                    class="form-control shadow-none border"
                                                    placeholder="Your Email (example@email.com)" data-validation="email" />
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="qrVcardAdd" id="qrVcardAdd"
                                                    class="form-control shadow-none border" placeholder="Address"
                                                    rows="1"></textarea>
                                                <small class="text-black-50">Scanning the QR code will create a contact</small>
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" name="btnVCard"
                                                    class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                    <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- WI-FI TAB -->
                            <div id="wifi-tab" class="tab-pane">
                                <form method="POST" id="qrFormWifi" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrWifiName" class="form-label mb-0">Wi-Fi Content</label>
                                            <input type="text" name="qrWifiName" id="qrWifiName"
                                                class="form-control shadow-none border"
                                                placeholder="Your Network Name (SSID)" required data-validation="required" />
                                        </div>
                                        <div class="mb-3">
                                            <input type="password" name="qrWifiPass" id="qrWifiPass"
                                                class="form-control shadow-none border" placeholder="Network Password" />
                                        </div>
                                        <div class="mb-2 d-flex justify-content-evenly">
                                            <label for="qrWifiSec4" class="form-label mb-0">
                                                <input type="radio" name="qrWifiSec" id="qrWifiSec4" class="shadow-none"
                                                    value="None" checked> None
                                            </label>
                                            <label for="qrWifiSec1" class="form-label mb-0">
                                                <input type="radio" name="qrWifiSec" id="qrWifiSec1" class="shadow-none"
                                                    value="WPA"> WPA
                                            </label>
                                            <label for="qrWifiSec2" class="form-label mb-0">
                                                <input type="radio" name="qrWifiSec" id="qrWifiSec2" class="shadow-none"
                                                    value="WEP"> WEP
                                            </label>
                                            <label for="qrWifiSec3" class="form-label mb-0">
                                                <input type="radio" name="qrWifiSec" id="qrWifiSec3" class="shadow-none"
                                                    value="WPA2"> WPA2
                                            </label>
                                        </div>
                                        <small class="text-black-50">Scanning the QR code will connect you to the network</small>
                                        <div class="mb-3 mt-2">
                                            <button type="submit" name="btnWifi"
                                                class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- WHATSAPP TAB -->
                            <div id="whatsapp-tab" class="tab-pane">
                                <form method="POST" id="qrFormWhatsApp" novalidate>
                                    <?php csrf_input(); ?>
                                    <fieldset>
                                        <div class="mb-3">
                                            <label for="qrWhatsappNum" class="form-label mb-0">WhatsApp Content</label>
                                            <input type="tel" name="qrWhatsappNum" id="qrWhatsappNum"
                                                class="form-control shadow-none border" placeholder="Your WhatsApp Number"
                                                pattern="[+][1-9]{1}[0-9]{3,14}" required data-validation="phone required" />
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="qrWhatsappMsg" id="qrWhatsappMsg"
                                                class="form-control shadow-none border" placeholder="Your WhatsApp Message"
                                                rows="1"></textarea>
                                        </div>
                                        <small class="text-black-50">Scanning the QR code will send a WhatsApp Message</small>
                                        <div class="mb-3 mt-2">
                                            <button type="submit" name="btnWhatsApp"
                                                class="btn btn-orange form-control rounded-0 d-flex align-items-center justify-content-center">
                                                <i class="las la-plus-square" style="font-size: 1.1rem;" aria-hidden="true"></i> Generate QR Code
                                            </button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- PAYPAL TAB -->
                            <div id="paypal-tab" class="tab-pane">
                                <h3 class="text-center">Not Available</h3>
                            </div>
                            <!-- APP TAB -->
                            <div id="app-tab" class="tab-pane">
                                <h3 class="text-center">Not Available</h3>
                            </div>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Right Column: QR Code Display -->
            <aside class="col-lg-4 col-md-12 p-0 bg-light d-flex align-items-stretch">
                <?php $qrLocked = empty($_SESSION['payment_completed']); ?>
                <div class="card rounded-0 rounded-end border-0 bg-light w-100 sticky-top">
                    <div class="card-body text-center">
                        <?php if (!empty($_SESSION['qrImageData'])): ?>
                            <div class="preview-header mb-3">
                                <h5 class="text-center mb-2">Generated QR Code</h5>
                                <?php if ($qrLocked): ?>
                                    <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                        <i class="las la-lock me-2"></i>
                                        <small class="mb-0">Preview locked - Complete checkout to unlock full image</small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success py-2 px-3 mb-2" role="alert">
                                        <i class="las la-check-circle me-2"></i>
                                        <small class="mb-0">Preview unlocked - Ready to download</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="position-relative d-inline-block">
                                <img src="<?= $_SESSION['qrImageData'] ?? '' ?>" alt="QR Code" class="img-fluid qr-preview"
                                    style="width: 250px; <?= $qrLocked ? 'filter: blur(8px); pointer-events: none;' : '' ?>">
                                <?php if ($qrLocked): ?>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center bg-white shadow rounded p-3"
                                        style="width: 210px;">
                                        <i class="las la-lock text-warning" style="font-size: 2.5rem;"></i>
                                        <p class="fw-semibold mb-1">Preview Locked</p>
                                        <p class="text-muted small mb-0">Customize your QR code, then complete checkout to unlock the full image.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                <a data-bs-toggle="modal" data-bs-target="#customize" class="btn btn-green rounded-0 me-2">
                                    <i class="las la-pen las-sm"></i> Customize QR Code
                                </a>
                                <?php if (!$qrLocked): ?>
                                    <a href="<?= $_SESSION['qrImageData'] ?? '#' ?>" download="qrcode.png" class="btn btn-orange rounded-0">
                                        <i class="las la-download las-sm"></i> Download
                                    </a>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <h5 class="text-center mb-3">Sample QR Code</h5>
                            <div class="d-flex justify-content-center">
                                <div class="border rounded bg-white shadow-sm p-3 d-inline-block">
                                    <img src="<?= URLROOT ?>/images/qrimage/sample-code.png" alt="Sample QR Code"
                                        class="img-fluid" style="width: 200px;">
                                </div>
                            </div>
                            <p class="text-center text-muted mt-3 mb-0">Generate your QR code to see the preview here</p>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </main>     </div>
        </div>


        <?php require_once APPROOT . '/views/inc/script-links.php' ?>

        <script>
            $(document).ready(function () {
                // Function to activate a tab
                function activateTab(tab) {
                    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
                    localStorage.setItem('activeTab', tab);
                }

                // Check if there's a saved active tab in local storage
                var activeTab = localStorage.getItem('activeTab');
                if (activeTab) {
                    activateTab(activeTab);
                } else {
                    activateTab('link-tab'); // Default tab
                }

                // Handle tab clicks
                $('.nav-tabs a').on('click', function (e) {
                    e.preventDefault();
                    var tabId = $(this).attr('href').substring(1);
                    activateTab(tabId);
                });

                // Debug form submission
                const forms = document.querySelectorAll('form[id^="qrForm"]');
                
                forms.forEach(function(form, index) {
                    form.addEventListener('submit', function(e) {
                        // Form validation bypassed - allowing submission
                    });
                    
                    // Debug button clicks
                    const submitButtons = form.querySelectorAll('button[type="submit"]');
                    submitButtons.forEach(button => {
                        button.addEventListener('click', function(e) {
                            // Button click tracking
                        });
                    });
                });

                // Handle Add QR Code to Cart
                $('#addQrToCart').on('click', function () {
                    fetch('<?= URLROOT ?>/index/addQrToCart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                swal("Added!", "QR Code has been added to your cart.", "success");
                            } else {
                                swal("Error!", data.message || 'Failed to add QR code to cart', "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            swal("Error!", "An error occurred. Please try again.", "error");
                        });
                });
            });
        </script>

        <!-- <img id="barcode"></img> -->

        <?php $randNumber = random_int(-234, 999999999999); ?>

        <script>
            JsBarcode("#barcode", "<?= $randNumber ?>", {
                format: "ean13",
                lineColor: "#000",
                width: 2,
                height: 40,
                displayValue: true
            });
        </script>

        <?php include APPROOT . '/views/inc/footer.php'; ?>

</body>

</html>