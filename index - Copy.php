<?php
session_start();
require_once 'core/password.php';

// Simulated hashed user list (bcrypt hashes)
$valid_users = array(
    'uploader1' => '$2y$10$ZOuAfU753McnSEehI4ptEefohU5nPUSl7nGQujsH47YbvfeMX/PkO',
    'uploader2' => '$2y$10$6Ynxzv4P6uczuWq6rgUBJOIbT7vmvsTeKPdBuVqW5Rh4HWv6NlzEC',
    'uploader3' => '$2y$10$uYa1WC.roZpP9yncNRP/PuQHd//l7JukdhJ8HihwP1CnyH7K9YQsy'
);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (isset($valid_users[$username]) && password_verify($password, $valid_users[$username])) {
        $_SESSION['logged_in']     = true;
        $_SESSION['username']      = $username;
        $_SESSION['login_success'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Simulated uploaded data
$uploadedData = array(); // Replace with real DB fetch
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EDU Portal - BOL Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f0f2f5;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #4e54c8;
            color: white;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .sidebar.hidden {
            display: none;
        }

        #showSidebarBtn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: #4e54c8;
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            z-index: 999;
        }

        body.sidebar-hidden #showSidebarBtn {
            display: block;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            display: block;
            padding: 6px 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #3d42a8;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        #toggleBtn {
            background: none;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        body.sidebar-hidden .content {
            margin-left: 0;
        }

        .sidebar-collapsed #sidebar {
            display: none;
        }

        .sidebar-collapsed #showSidebarBtn {
            display: block;
        }

        .wrapper {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 30px auto;
            gap: 30px;
            padding: 0 20px;
        }

        .left, .right {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .left {
            flex: 1;
            min-width: 280px;
        }

        .right {
            flex: 2;
            min-width: 280px;
            overflow-x: auto;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
            font-size: 12px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 12px;
            height: 26px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            margin-top: 16px;
            padding: 6px 12px;
            background: #4e54c8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #3c41b5;
        }

        .download-template {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 16px;
            background: #f0f0f0;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-size: 12px;
        }

        .message, .error {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .message {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background: #fdecea;
            color: #d32f2f;
        }

        .text-muted {
            color: #999;
        }

        .login-container {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 60px;
        }

        .login-box {
            width: 300px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .section-header .download-template {
            margin-top: 0;
        }

        .drop-zone {
            border: 2px dashed #aaa;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            background: #fafafa;
            position: relative;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .drop-zone.dragover {
            border-color: #4e54c8;
            background-color: #eef0ff;
        }

        .drop-zone .drop-message {
            font-size: 14px;
            color: #666;
        }

        .file-preview {
            margin-top: 10px;
        }

        .file-name {
            font-size: 14px;
            color: #333;
        }

        .remove-btn {
            margin-top: 8px;
            padding: 5px 10px;
            font-size: 13px;
            background: #ff4d4f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background: #d9363e;
        }

        .action-btn {
            display: inline-block;
            padding: 6px 8px;
            margin-right: 4px;
            color: #555;
            background-color: #f4f6f8;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            font-size: 13px;
        }

        .action-btn:hover {
            background-color: #e0e7ff;
            color: #2c3e50;
        }

        .action-btn.delete {
            color: #e74a3b;
        }

        .action-btn i {
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                display: flex;
                flex-direction: row;
                gap: 10px;
            }
            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
            }
            .sidebar ul li {
                margin-right: 10px;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['sidebar_hidden']) && $_SESSION['sidebar_hidden'] ? 'sidebar-hidden' : ''; ?>">
<button id="showSidebarBtn" style="display:none;">☰</button>

<?php if (!isset($_SESSION['username'])): ?>
    <!-- LOGIN VIEW -->
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
<?php else: ?>
<!-- SIDEBAR + CONTENT LAYOUT -->
<div class="layout">
    <div class="sidebar" id="sidebar">
        <h2>
            🎓 EDU Portal
            <button id="toggleBtn">☰</button>
        </h2>
        <ul>
            <li><a href="#" class="nav-link active" data-target="bol-section"><i class="fa fa-file-excel"></i> BOL Upload</a></li>
            <li><a href="#" class="nav-link" data-target="users-section"><i class="fa fa-users"></i> Users</a></li>
            <li>
                <a href="?action=logout">
                    <i class="fa fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)
                </a>
            </li>
        </ul>
    </div>
    <div class="content">
        <div class="wrapper main-content" id="bol-section" style="display: flex;">
            <?php include 'partials/bol_uploader.php'; ?>
        </div>
        <div class="wrapper main-content" id="users-section" style="display: none;">
            <?php include 'partials/users.php'; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['login_success'])): ?>
    <script>
        $(function () {
            toastr.success('Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!', 'Login Successful');
        });
    </script>
    <?php unset($_SESSION['login_success']); endif; ?>
</body>
<script>
    $(document).ready(function () {
        function initDataTable() {
            $('table').DataTable({
                pageLength: 10,
                lengthChange: false,
                ordering: true,
                language: {
                    search: "Search uploaded files:"
                }
            });
        }

        // Initialize DataTable on page load
        initDataTable();

        // Load uploaded files
        function loadUploadedFiles() {
            showSkeleton(); // Show loading skeleton

            $.ajax({
                url: 'upload_handler.php',
                method: 'GET',
                success: function (response) {
                    // Replace skeleton with actual content
                    $('#uploaded-files').html(response);
                    initDataTable();
                },
                error: function () {
                    $('#uploaded-files').html('<p>Error loading data.</p>');
                }
            });
        }

        // Initial file load
        loadUploadedFiles();

        var $dropZone = $('#drop-zone');
        var $fileInput = $('#bol_file');
        var $fileNameDisplay = $('#file-name');
        var $removeBtn = $('#remove-file');
        var $form = $('#upload-form');

        // Open file picker on drop zone click
        $dropZone.on('click', function (e) {
            // prevent triggering click from internal elements like the file input itself
            if (!$(e.target).is('#bol_file')) {
                $fileInput.trigger('click');
            }
        });

        // Drag and drop effects
        $dropZone.on('dragover', function (e) {
            e.preventDefault();
            $(this).css('background', '#f0f0f0');
        });

        $dropZone.on('dragleave', function (e) {
            e.preventDefault();
            $(this).css('background', '');
        });

        $dropZone.on('drop', function (e) {
            e.preventDefault();
            $(this).css('background', '');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                var file = files[0];
                if (file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) {
                    $fileInput[0].files = files;
                    $fileNameDisplay.text(`Selected: ${file.name}`);
                    $removeBtn.show();
                } else {
                    alert('Only Excel files (.xls, .xlsx) are allowed.');
                    $fileInput.val('');
                    $fileNameDisplay.text('');
                    $removeBtn.hide();
                }
            }
        });

        // File input change
        $fileInput.on('change', function () {
            var file = this.files[0];
            if (file) {
                $fileNameDisplay.text(`Selected: ${file.name}`);
                $removeBtn.show();
            } else {
                $fileNameDisplay.text('');
                $removeBtn.hide();
            }
        });

        // Remove selected file
        $removeBtn.on('click', function () {
            $fileInput.val('');
            $fileNameDisplay.text('');
            $removeBtn.hide();
        });

        // Form submission
        $form.on('submit', function (e) {
            e.preventDefault();

            var file = $fileInput[0].files[0];
            if (!file) {
                toastr.error('Please select an Excel file before submitting.');
                return;
            }

            var allowedTypes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            if (!allowedTypes.includes(file.type)) {
                toastr.error('Only Excel files (.xls, .xlsx) are allowed.');
                return;
            }

            var form = document.getElementById('upload-form');
            var formData = new FormData(form);
            formData.append('action', 'upload');

            $.ajax({
                url: 'upload_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json', // <-- Tells jQuery to parse the JSON automatically
                success: function (data) {
                    if (data.status === 'success') {
                        loadUploadedFiles();
                        $fileInput.val('');
                        $fileNameDisplay.text('');
                        $removeBtn.hide();
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error('Unexpected error occurred.');
                }
            });
        });

        function showSkeleton() {
            let skeletonRows = '';
            for (let i = 0; i < 5; i++) {
                skeletonRows += `
                    <tr>
                        ${'<td class="skeleton-cell"></td>'.repeat(9)}
                        <td><div class="skeleton-cell" style="width:60px;height:20px;"></div></td>
                    </tr>`;
                    }
        
                    $('#uploaded-files').html(`
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Registry</th>
                            <th>Port</th>
                            <th>HBL / AWB</th>
                            <th>Nature Code</th>
                            <th>Dest. Place Code</th>
                            <th>No. of Packs</th>
                            <th>Package Type</th>
                            <th>Gross Weight</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>${skeletonRows}</tbody>
                </table>
            `);
        }



        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();

            const id = $(this).data('id');
            if (!id || !confirm('Are you sure you want to delete this HBL/AWB?')) {
                return;
            }

            $.ajax({
                url: 'upload_handler.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        loadUploadedFiles(); // your function to reload the table
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function () {
                    toastr.error('Unexpected error occurred.');
                }
            });
        });

        $('#toggleBtn').on('click', function () {
            $('#sidebar').addClass('hidden');
            $('#showSidebarBtn').show();
        });

        $('#showSidebarBtn').on('click', function () {
            $('#sidebar').removeClass('hidden');
            $('#showSidebarBtn').hide();
        });

        $('.nav-link').on('click', function (e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            $('.main-content').hide();
            $('#' + target).show();
        });
    });
</script>
</html>
