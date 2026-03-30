<script src="<?= URLROOT ?>/assets/js/jquery3.7.1.js"></script>
<script src="<?= URLROOT ?>/assets/js/bootstrap5.2.3.min.js"></script>
<script src="<?= URLROOT ?>/assets/js/main.js"></script>
<script src="<?= URLROOT ?>/assets/js/form-validator.js"></script>
<script src="<?= URLROOT ?>/assets/js/htmx.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="<?= URLROOT ?>/assets/js/swipper-10.2.0.js"></script>
<script src="<?= URLROOT ?>/assets/js/swipper-script.js"></script>
<script src="<?= URLROOT ?>/assets/js/sweet-alert.js" defer></script>
<!-- Paystack -->
<script src="https://js.paystack.co/v1/inline.js" defer></script>

<?php if ((getenv('APP_DEBUG') ?: '0') === '1') : ?>
<script>
   const paystackPublicKey = "<?= htmlspecialchars(getenv('PAYSTACK_PUBLIC') ?: '') ?>";

   (function attachPaystackDemo() {
      const btn = document.getElementById('paystackButton');
      if (!btn || !paystackPublicKey) return;
      btn.addEventListener('click', function() {
         const handler = PaystackPop.setup({
            key: paystackPublicKey,
            email: "customer@example.com",
            amount: 5000 * 100,
            currency: "NGN",
            ref: "BARCODE_" + Math.floor((Math.random() * 1000000000) + 1),
            callback: function(response) {
               alert('Payment successful! Transaction reference: ' + response.reference);
               demoVerifyPayment(response.reference);
            },
            onClose: function() {
               alert('Payment canceled.');
            }
         });
         handler.openIframe();
      });
   })();
</script>

<script>
   // Function to verify payment on the server (demo)
   function demoVerifyPayment(reference) {
      fetch('<?= URLROOT ?>/barcode/verifyPayment', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
         },
         body: JSON.stringify({ reference })
      })
      .then(response => response.json())
      .then(data => {
         if (data && data.success) {
            alert('Payment verified successfully!');
         } else {
            alert('Payment verification failed.');
         }
      })
      .catch(error => console.error('Error:', error));
   }
</script>
<?php endif; ?>

<script>
   function showPassword() {
      var x = document.getElementById("show_pass");
      if (x.type === "password") {
         x.type = "text";
      } else {
         x.type = "password";
      }
   }

   $(document).ready(function() {

      $('.toast').toast('show');
   });
</script>

