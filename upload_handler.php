<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

session_start();

require_once 'controllers/MainController.php';

$controller = new MainController();

// Handle DELETE request first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    header('Content-Type: application/json');

    $success = $controller->deleteUploadedBOL($_POST['id']);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete file.']);
    }
    exit;
}

// Handle UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload' && isset($_POST['type']) && $_POST['type'] === 'bol') {
    $controller->uploadExcel();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload'&& isset($_POST['type']) && $_POST['type'] === 'users') {
    $controller->uploadUserExcel();
    exit;
}

// Added new header (School)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'users') {
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $usersUploadedData = $controller->fetchUploadedUsers($searchTerm);

    if (empty($usersUploadedData)) {
        echo '<p class="text-muted">No uploaded User records found.</p>';
    } else {
        ?>
        <table id="users-table"> 
            <thead>
            <tr>
                <th>SR Code</th>
                <th>Status</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Address</th>
				<th>School</th> 
                <th>Account Type</th> 
            </tr>
            </thead>
            <tbody>
            <?php foreach ($usersUploadedData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['cltcode']); ?></td>
                    <td><?= htmlspecialchars($row['Status']); ?></td>
                    <td><?= htmlspecialchars($row['AIEmail']); ?></td>
                    <td><?= htmlspecialchars($row['AIFirstName']); ?></td>
                    <td><?= htmlspecialchars($row['AILastName']); ?></td>
                    <td><?= htmlspecialchars($row['AIAddrs']); ?></td>
                    <td><?= htmlspecialchars($row['School']); ?></td>
                    <td><?= htmlspecialchars($row['AccountType']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'bol') {
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $uploadedData = $controller->fetchUploadedBOL($searchTerm);

    if (empty($uploadedData)) {
        echo '<p class="text-muted">No uploaded BOL records found.</p>';
    } else {
        ?>
        <table id="bol-table">
            <thead>
            <tr>
                <th>Registry</th>
                <th>Port</th>
                <th>HBL/AWB</th>
                <th>BL Nature</th>
                <th>Destination</th>
                <th>Packs</th>
                <th>Type</th>
                <th>Weight</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($uploadedData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['registry']); ?></td>
                    <td><?= htmlspecialchars($row['port']); ?></td>
                    <td><?= htmlspecialchars($row['blno']); ?></td>
                    <td><?= htmlspecialchars($row['bl_nature']); ?></td>
                    <td><?= htmlspecialchars($row['pl_destination']); ?></td>
                    <td><?= htmlspecialchars($row['package_no']); ?></td>
                    <td><?= htmlspecialchars($row['package_type']); ?></td>
                    <td><?= htmlspecialchars($row['gross_weight']); ?></td>
                    <td>
                        <a href="#"
                           data-id="<?= $row['id'] ?>"
                           class="action-btn delete delete-btn"
                           title="Delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

// Fallback for unrecognized requests
//header('Content-Type: application/json');
//echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
