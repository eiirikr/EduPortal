<div class="left">
    <div class="section-header">
        <h2>Upload Users</h2>
        <a href="/webcws/portal-handler/templates/STUDENT INFO REGISTRATION.xlsx" class="download-template" download>📄 Download
            Template</a>
    </div>
    <?php if (isset($_SESSION['upload_message'])): ?>
        <div class="message"><?php echo $_SESSION['upload_message'];
            unset($_SESSION['upload_message']); ?></div>
    <?php endif; ?>
    <form id="upload-form" action="upload_handler.php" method="POST" enctype="multipart/form-data">
        <label for="bol_file">Choose File:</label>
        <div class="drop-zone" id="drop-zone">
            <div class="drop-message">📂 Drag & drop your excel file here or click to select</div>
            <div class="file-preview" id="file-preview" style="display: none;">
                <span id="file-name" class="file-name"></span>
                <button type="button" class="remove-btn" id="remove-file">Remove</button>
            </div>
        </div>
        <input type="file" name="user_file" id="user_file" style="display: none;" accept=".xml,.xls,.xlsx" />
        <input type="submit" value="Upload">
    </form>
</div>

<div class="right">
    <h2>Uploaded Users</h2>
    <div id="uploaded-user-files"></div>
</div>

<!-- User Error Modal -->
<div class="modal fade" id="userErrorModal" tabindex="-1" role="dialog" aria-labelledby="bolErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-sm rounded-3 border-0">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title font-weight-bold" id="bolErrorModalLabel">🚫 User Upload Errors</h5>
                <button type="button" class="close btn btn-sm btn-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="user-error-table">
                        <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Error Message</th>
                            <th style="width: 100px;">Row</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>