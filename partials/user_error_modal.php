<!-- User Upload Error Modal -->
<div class="modal fade" id="userErrorModal" tabindex="-1" role="dialog" aria-labelledby="userErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-sm rounded-3 border-0">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title font-weight-bold" id="userErrorModalLabel">🚫 User Upload Errors</h5>
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
