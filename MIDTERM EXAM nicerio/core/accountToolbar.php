<div class="account-toolbar">
    <?php $accountUsername = $_SESSION['username'] ?? 'User'; ?>
    <span>Logged in as <?php echo htmlspecialchars($accountUsername, ENT_QUOTES, 'UTF-8'); ?></span>
    <form class="logout-form" action="logout.php" method="POST" data-confirm-logout data-username="<?php echo htmlspecialchars($accountUsername, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit">Logout</button>
    </form>
</div>
<script>
    document.querySelectorAll('[data-confirm-logout]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const username = form.dataset.username;
            if (!window.confirm(`Are you sure you want to log out of the account "${username}"?`)) {
                event.preventDefault();
            }
        });
    });
</script>
