<div class="layout">
    <div class="sidebar" id="sidebar">
        <h2>
            🎓 EDU Portal
            <button id="toggleBtn">☰</button>
        </h2>
        <ul>
            <li><a href="#" class="nav-link active" data-target="bol-section"><i class="fa fa-file-excel"></i> BOL Upload</a></li>
            <li><a href="#" class="nav-link" data-target="users-section"><i class="fa fa-users"></i> Users</a></li>
            <li><a href="?action=logout"><i class="fa fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
        </ul>
    </div>

    <div class="content">
        <main class="main-content" id="bol-section" style="display: block;">
            <?php include 'partials/bol_uploader.php'; ?>
        </main>

        <main class="main-content" id="users-section" style="display: none;">
            <?php include 'partials/users.php'; ?>
        </main>
    </div>
</div>
