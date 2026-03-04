<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/main.js"></script>

<?php if (isset($_SESSION['login_success'])): ?>
    <script>
        $(function () {
            toastr.success('Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!', 'Login Successful');
        });
    </script>
<?php unset($_SESSION['login_success']); endif; ?>
