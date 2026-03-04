$(document).ready(function () {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",          // Toast fades out after 5 sec
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Prevent default drag and drop on the page
    $(document).on('dragover drop', function (e) {
        e.preventDefault();
    });

    function initDataTable(target) {
        const tableSelector = target === 'users-section' ? '#users-table' : '#bol-table';

        if ($.fn.DataTable.isDataTable(tableSelector)) {
            $(tableSelector).DataTable().destroy();
        }

        $(tableSelector).DataTable({
            pageLength: 10,
            lengthChange: false,
            ordering:false,
            language: {
                search: 'Search uploaded files:'
            }
        });
    }

    // Initial load
    initDataTable('bol-section');
    loadUploadedUserFiles();
    loadUploadedFiles();

    function loadUploadedFiles() {
        showSkeletonBOL();

        $.ajax({
            url: 'upload_handler.php',
            method: 'GET',
            data: { type: 'bol' },
            success: function (response) {
                $('#uploaded-files').html(response);
                initDataTable('bol-section');
            },
            error: function () {
                $('#uploaded-files').html('<p>Error loading data.</p>');
            }
        });
    }

    function showSkeletonBOL() {
        let skeletonRows = '';
        for (let i = 0; i < 5; i++) {
            skeletonRows += `<tr>${'<td class="skeleton-cell"></td>'.repeat(9)}<td><div class="skeleton-cell" style="width:60px;height:20px;"></div></td></tr>`;
        }

        $('#uploaded-files').html(`
            <table id="bol-table" class="table table-striped">
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
            </table>`);
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
                    loadUploadedFiles();
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

        initDataTable(target);
    });

    // Load uploaded user files
    function loadUploadedUserFiles() {
        showSkeletonUsers(); // Show loading skeleton

        $.ajax({
            url: 'upload_handler.php',
            method: 'GET',
            data: { type: 'users' },
            success: function (response) {
                // Replace skeleton with actual content
                $('#uploaded-user-files').html(response);
                initDataTable('users-section');
            },
            error: function () {
                $('#uploaded-user-files').html('<p>Error loading data.</p>');
            }
        });
    }

    function showSkeletonUsers() {
        let skeletonRows = '';
        for (let i = 0; i < 5; i++) {
            skeletonRows +=
                `<tr>
                    ${'<td class="skeleton-cell"></td>'.repeat(9)}
                    <td><div class="skeleton-cell" style="width:60px;height:20px;"></div></td>
                </tr>`;
        }

        $('#uploaded-user-files').html(
            `<table id="users-table" class="table table-striped">
                <thead>
                <tr>
                    <th>SR Code</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Address</th>
                </tr>
                </thead>
                <tbody>${skeletonRows}</tbody>
            </table>`
        );
    }

    function bindDropUpload(sectionId) {
        const $section = $(sectionId);
        const $dropZone = $section.find('.drop-zone');
        const $fileInput = $section.find('input[type="file"]');
        const $fileNameDisplay = $section.find('.file-name');
        const $removeBtn = $section.find('.remove-btn');
        const $form = $section.find('form');

        $dropZone.on('click', function (e) {
            if (!$(e.target).is($fileInput)) {
                $fileInput.trigger('click');
            }
        });

        $dropZone.on('dragover', function (e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        $dropZone.on('dragleave', function (e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });

        $dropZone.on('drop', function (e) {
            e.preventDefault();
            $(this).removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                var file = files[0];
                if (file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) {
                    $fileInput[0].files = files;
                    $fileNameDisplay.text('Selected: ' + file.name);
                    $section.find('.file-preview').show();
                } else {
                    alert('Only Excel files (.xls, .xlsx) are allowed.');
                    $fileInput.val('');
                    $fileNameDisplay.text('');
                    $section.find('.file-preview').hide();
                }
            }
        });

        $fileInput.on('change', function () {
            var file = this.files[0];
            if (file) {
                $fileNameDisplay.text('Selected: ' + file.name);
                $section.find('.file-preview').show();
            } else {
                $fileNameDisplay.text('');
                $section.find('.file-preview').hide();
            }
        });

        $removeBtn.on('click', function () {
            $fileInput.val('');
            $fileNameDisplay.text('');
            $section.find('.file-preview').hide();
        });

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

            var formData = new FormData($form[0]);
            formData.append('action', 'upload');

            // Determine type based on section ID
            if (sectionId === '#users-section') {
                formData.append('type', 'users');
            } else {
                formData.append('type', 'bol');
            }

            $.ajax({
                url: 'upload_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (data) {
                    if (data.status === 'success') {
                        if (sectionId === '#users-section') {
                            loadUploadedUserFiles();
                        } else {
                            loadUploadedFiles();
                        }

                        $fileInput.val('');
                        $fileNameDisplay.text('');
                        $section.find('.file-preview').hide();
                        toastr.success(data.message);
                    } else if (data.status === 'error' && data.errors) {
                        if (sectionId === '#users-section') {
                            loadUploadedUserFiles();
                        } else {
                            loadUploadedFiles();
                        }

                        let modalId = '';
                        let tableId = '';

                        if (sectionId === '#users-section') {
                            modalId = '#userErrorModal';
                            tableId = '#user-error-table';
                        } else {
                            modalId = '#bolErrorModal';
                            tableId = '#bol-error-table';
                        }

                        const $tbody = $(`${tableId} tbody`);
                        $tbody.empty();

                        data.errors.forEach((err, i) => {
                            $tbody.append(`<tr>
                                <td>${i + 1}</td>
                                <td>${err.message}</td>
                                <td>${err.row ? 'Row ' + err.row : '-'}</td>
                            </tr>`);
                        });

                        $(modalId).modal('show');
                    } else {
                        toastr.error(data.message || 'Upload failed.');
                    }
                },
                error: function () {
                    toastr.error('Unexpected error occurred.');
                }
            });
        });
    }

    bindDropUpload('#bol-section');
    bindDropUpload('#users-section');
});
