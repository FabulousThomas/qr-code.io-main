<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
   <div class="container">
      <a class="navbar-brand" href="<?= URLROOT ?>">
         <h4 class="mb-0">QR&dash;Hub.io</h4>
      </a>
      <button class="navbar-toggler d-lg-none shadow-none border-0 bg-transparent p-0 m-0" type="button"
         data-bs-toggle="offcanvas" data-bs-target="#menu" aria-label="Toggle navigation menu">
         <i class="las la-bars font-color-black" style="font-size: 2rem;"></i>
      </button>
      <div class="collapse navbar-collapse">
         <ul class="navbar-nav m-auto mt-2 mt-lg-0">
            <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle" type="button" id="qr-codes-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  QR Codes
               </a>
               <div class="dropdown-menu border-0 rounded-0 shadow-sm" aria-labelledby="qr-codes-dropdown">
                  <a class="dropdown-item text-black-50" href="<?= URLROOT ?>">Static QR Codes</a>
                  <div class="dropdown-divider"></div>
                  <span class="dropdown-item-text text-muted small">Dynamic QR Codes (Coming Soon)</span>
               </div>
            </li>

            <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle" type="button" id="bar-codes-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  Bar Codes
               </a>
               <div class="dropdown-menu border-0 rounded-0 shadow-sm" aria-labelledby="bar-codes-dropdown">
                  <a class="dropdown-item text-black-50" href="<?= URLROOT ?>/barcode">Static Bar Codes</a>
                  <div class="dropdown-divider"></div>
                  <span class="dropdown-item-text text-muted small">Dynamic Bar Codes (Coming Soon)</span>
               </div>
            </li>

            <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle" type="button" id="products-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  Features
               </a>
               <div class="dropdown-menu border-0 rounded-0 shadow-sm" aria-labelledby="products-dropdown">
                  <a class="dropdown-item text-black-50" href="<?= URLROOT ?>">QR Code Generator</a>
                  <a class="dropdown-item text-black-50" href="<?= URLROOT ?>/barcode">Barcode Generator</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-black-50" href="<?= URLROOT ?>/bulk">Bulk Generation</a>
                  <div class="dropdown-divider"></div>
                  <span class="dropdown-item-text text-muted small">Dynamic QR Codes (Coming Soon)</span>
               </div>
            </li>

            <!-- <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
               <a class="nav-link" href="<?= URLROOT ?>/users/analytics"> Analytics
               </a>
            </li>
            <?php endif; ?> -->

            <li class="nav-item">
               <a class="nav-link" href="<?= URLROOT ?>/pages/about">About</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" href="<?= URLROOT ?>/pages/contact">Contact</a>
            </li>

         </ul>
         <ul class="navbar-nav d-flex my-2 my-lg-0">
            <?php if (isset($_SESSION['user_id'])): ?>
               <a class="btn btn-green btn-sm rounded-0 me-2" href="<?= URLROOT ?>/barcode/myCodes" aria-label="View my codes"> My Codes
               </a>
               <a class="btn btn-orange btn-sm rounded-0 me-2" href="<?= URLROOT ?>/users/logout" aria-label="Log out"> Log Out
               </a>
            <?php else: ?>
               <a class="btn btn-orange btn-sm rounded-0 me-2" href="<?= URLROOT ?>/users/login" aria-label="Log in">
                  <i class="las la-sign-in-alt las-sm"></i> Log In
               </a>
               <a class="btn btn-green btn-sm rounded-0 me-2" href="<?= URLROOT ?>/users/register" aria-label="Sign up">
                  <i class="las la-user-plus las-sm"></i> Sign Up
               </a>
            <?php endif; ?>

            <button class="btn btn-green btn-sm rounded-0" type="button" data-bs-toggle="modal"
               data-bs-target="#cartModal" aria-label="View cart">
               <i class="las la-shopping-cart las-sm"></i> Cart
            </button>
         </ul>
      </div>
   </div>
</nav>
<?php include APPROOT . "/views/inc/menu.php" ?>