<div class="dashboard-header">
    <h1 class="dashboard-title">BOL Management</h1>
    <div class="dashboard-actions">
        <a href="/webcws/portal-handler/templates/BOL_Template.xls" class="btn btn-primary download-template" download>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            Download BOL Template
        </a>
    </div>
</div>

<div id="bol-upload-area" class="upload-area">
    <form id="bol-upload-form" action="upload_handler.php" method="POST" enctype="multipart/form-data">
        <?php if (isset($_SESSION['upload_message'])): ?>
            <div class="message"><?php echo $_SESSION['upload_message']; unset($_SESSION['upload_message']); ?></div>
        <?php endif; ?>

        <div class="upload-icon mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707V11.5z"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
            </svg>
        </div>

        <h3 class="text-center">Upload your BOL Excel</h3>

        <div class="upload-message">📂 Drag and drop your Excel file here, or click to browse</div>

        <div class="file-preview" id="file-preview" style="display: none;">
            <span id="file-name" class="file-name"></span>
            <button type="button" class="remove-btn" id="remove-file">Remove</button>
        </div>

        <input type="file" name="bol_file" id="bol_file" style="display: none;" accept=".xml,.xls,.xlsx"/>
        <button type="submit" class="btn btn-outline mt-3">Upload File</button>
    </form>
</div>

<div class="search-container mt-4">
    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
         viewBox="0 0 16 16">
        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
    </svg>
    <input type="text" class="search-input" id="searchBOL" placeholder="Search uploaded BOLs...">
</div>

<div id="uploaded-files" class="mt-4"></div>

<?php include 'partials/bol_error_modal.php'; ?>
