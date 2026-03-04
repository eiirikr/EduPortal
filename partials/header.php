<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EDU Portal - BOL Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css"> <!-- Your custom styles -->
</head>
<body class="<?php echo isset($_SESSION['sidebar_hidden']) && $_SESSION['sidebar_hidden'] ? 'sidebar-hidden' : ''; ?>">
<button id="showSidebarBtn" style="display:none;">☰</button>
