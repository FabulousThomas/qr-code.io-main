<?php require APPROOT . '/views/inc/head.php' ?>
<?php require APPROOT . '/views/inc/navbar.php' ?>

<div class="container mt-3">
   <div class="jumbotron jumbotron-fluid py-2 text-center">
      <h1 class="text-uppercase mb-0"><?= $data['title'] ?></h1>
      <p class="lead mb-0"><?= $data['description'] ?></p>
   </div>

   <div class="card card-body px-0 col-lg-6 col-md-12 m-auto shadow border-0 rounded-0">
      <div class="container">
         <form action="<?= URLROOT; ?>/users/register" method="POST" enctype="multipart/form-data">

            <div class="form-group mb-2">
               <label class="mb-0 font-color-black" for="name">Name</label>
               <input type="text" class="form-control <?= (!empty($data['name_err'])) ? 'is-invalid' : ''; ?> rounded-0 shadow-none font-color-black" name="name" placeholder="Enter Full Name" value="<?= $data['name'] ?? '' ?>">
               <small class="invalid-feedback"><?= $data['name_err'] ?></small>
            </div>
            <div class="form-group mb-2">
               <label class="mb-0 font-color-black" for="username">Username</label>
               <input type="text" class="form-control <?= (!empty($data['username_err'])) ? 'is-invalid' : ''; ?> rounded-0 shadow-none font-color-black" name="username" placeholder="Enter Username" value="<?= $data['username'] ?? '' ?>">
               <small class="invalid-feedback"><?= $data['username_err'] ?></small>
            </div>
            <div class="form-group mb-2">
               <label class="mb-0 font-color-black" for="email">Email</label>
               <input type="email" class="form-control <?= (!empty($data['email_err'])) ? 'is-invalid' : ''; ?> rounded-0 shadow-none font-color-black" name="email" placeholder="Enter Email" value="<?= $data['email'] ?? '' ?>">
               <small class="invalid-feedback"><?= $data['email_err'] ?></small>
            </div>
            <div class="form-group row">
               <div class="col">
                  <button type="submit" class="btn btn-success shadow-none form-control">Register</button>
               </div>
               <div class="col">
                  <a href="<?= URLROOT; ?>/users/login" class="btn btn-light shadow-none form-control">Or Login</a>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>