<?php require APPROOT . '/views/inc/head.php' ?>
<?php require APPROOT . '/views/inc/navbar.php' ?>

<div class="container mt-3">
   <div class="jumbotron jumbotron-fluid py-2 text-center">
      <h1 class="text-uppercase mb-0"><?= $data['title'] ?></h1>
      <p class="lead mb-0"><?= $data['description'] ?></p>
   </div>

   <div class="card card-body px-2 col-lg-6 col-md-12 m-auto shadow border-0 rounded-0">
      <form action="<?= URLROOT; ?>/users/login" method="POST" enctype="multipart/form-data">
         <?php flashMsg('users_msg'); ?>
         <div class="form-group mb-3">
            <label for="email" class="font-color-black">Email</label>
            <input type="email" class="form-control <?= (!empty($data['email_err'])) ? 'is-invalid' : ''; ?> rounded-0 shadow-none font-color-black"
               name="email" placeholder="Enter Email" value="<?= $data['email'] ?>">
            <small class="invalid-feedback"><?= $data['email_err'] ?></small>
         </div>
         <div class="form-group row">
            <div class="col">
               <button type="submit" class="btn btn-success form-control shadow-none">Login</button>
            </div>
            <div class="col">
               <a href="<?= URLROOT; ?>/users/register" class="btn btn-light form-control shadow-none">Or Register</a>
            </div>
         </div>
      </form>

   </div>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>