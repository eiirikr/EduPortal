<div class="login-container">
    <div class="login-box">
        <h2>🎓 EDU Portal (Admin)</h2>
        <?php if (isset($login_error)): ?>
            <div class="error-text"><?php echo $login_error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <input type="submit" name="login" value="Login">
        </form>
    </div>
</div>
