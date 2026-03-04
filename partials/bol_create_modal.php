<!-- Manual BOL Creation Modal -->
<div class="modal fade" id="manualCreateModal" tabindex="-1" role="dialog" aria-labelledby="manualCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="manual-bol-form" method="POST">
            <div class="modal-content p-4 rounded-lg shadow-sm">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="manualCreateModalLabel">📝 Manually Create BOL</h5>
                    <button type="button" class="close btn btn-light rounded-circle" data-dismiss="modal" aria-label="Close" style="width:32px; height:32px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div id="manual-bol-errors" class="alert alert-danger" style="display:none;"></div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="registry">📘 Registry</label>
                            <input type="text" class="form-control modern-input" name="registry" id="registry" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="port">📍 Port</label>
                            <input type="text" class="form-control modern-input" name="port" id="port" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="hbl_awb">📦 HBL / AWB</label>
                            <input type="text" class="form-control modern-input" name="hbl_awb" id="hbl_awb" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="bl_nature_code">🔢 BL Nature Code</label>
                            <input type="text" class="form-control modern-input" name="bl_nature_code" id="bl_nature_code" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="destination_place_code">Destination Place Code</label>
                            <select class="form-control" name="destination_place_code" id="destination_place_code" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="package_type">Package Type</label>
                            <select class="form-control" name="package_type" id="package_type" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="package_type">📁 Package Type</label>
                            <input type="text" class="form-control modern-input" name="package_type" id="package_type" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="gross_weight">⚖️ Gross Weight</label>
                            <input type="text" class="form-control modern-input" name="gross_weight" id="gross_weight" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline">✅ Create BOL</button>
                </div>
            </div>
        </form>
    </div>
</div>