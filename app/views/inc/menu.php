<div class="offcanvas offcanvas-start bg-light" data-bs-backdrop="static" tabindex="-1" id="menu">
   <div class="offcanvas-header">
      <a class="navbar-brand" href="">
         <h4 class="mb-0">QR&dash;Hub.io</h4>
      </a>
      <button type="button" class="bg-transparent border-0 shadow-none" data-bs-dismiss="offcanvas"
         aria-label="Close"><i class="las la-times font-color-black" style="font-size: 2rem;"></i></button>
   </div>
   <div class="offcanvas-body">
      <ul class="navbar-nav m-auto mt-2 mt-lg-0">
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown">
               QR Codes
            </a>
            <div class="dropdown-menu border-0 rounded-0 shadow-sm">
               <a class="dropdown-item text-black-50" href="<?= URLROOT ?>">Static QR Codes</a>
               <a class="dropdown-item text-black-50" href="#">Dynamic QR Codes</a>
            </div>
         </li>

         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown">
               Bar Codes
            </a>
            <div class="dropdown-menu border-0 rounded-0 shadow-sm">
               <a class="dropdown-item text-black-50" href="<?= URLROOT ?>/barcode">Static Bar Codes</a>
               <a class="dropdown-item text-black-50" href="#">Dynamic Bar Codes</a>
            </div>
         </li>

         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown">
               Products
            </a>
            <div class="dropdown-menu border-0 rounded-0 shadow-sm">
               <a class="dropdown-item text-black-50" href="#">QR Codes</a>
               <a class="dropdown-item text-black-50" href="#">Bar Codes</a>
               <a class="dropdown-item text-black-50" href="#">Features</a>
            </div>
         </li>

         <li class="nav-item">
            <a class="nav-link" href="#">Why Us?</a>
         </li>
         <li class="nav-item">
            <a class="nav-link" href="#">Contact Us</a>
         </li>

      </ul>
      <ul class="navbar-nav d-flex my-2 my-lg-0">
         <?php if (isset($_SESSION['user_id'])): ?>
            <a class="btn btn-green btn-sm rounded-0 mb-3" href="<?= URLROOT ?>/barcode/myCodes"><i
                  class="las la-qrcode las-sm"></i> My Codes</a>
            <a class="btn btn-orange btn-sm rounded-0 me-2 mb-lg-0 mb-2" href="<?= URLROOT ?>/users/logout"><i
                  class="las la-sign-in-alt las-sm"></i>
               Log Out</a>
         <?php else: ?>
            <a class="btn btn-orange btn-sm rounded-0 me-2 mb-lg-0 mb-2" href="<?= URLROOT ?>/users/login"><i
                  class="las la-sign-in-alt las-sm"></i>
               Log In</a>
            <a class="btn btn-green btn-sm rounded-0" href="<?= URLROOT ?>/users/register"><i
                  class="las la-user-plus las-sm"></i> Sign Up</a>
         <?php endif; ?>

         <button class="btn btn-green btn-sm rounded-0 mt-2" type="button" data-bs-toggle="modal"
            data-bs-target="#cartModal"><i class="las la-user-plus las-sm"></i> Cart</button>
      </ul>
   </div>
</div>