<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once __DIR__ . '/../models/MainModel.php';
require_once __DIR__ . '/../models/ApplStatModel.php';
require_once __DIR__ . '/../models/AdminModel.php';

class MainController
{
    private $model;
    private $stat_model;
    private $admin_model;

    public function __construct()
    {
        $this->model       = new MainModel();
        $this->stat_model  = new ApplStatModel();
        $this->admin_model = new AdminModel();
    }

    public function checkApplication($applno)
    {
        return $this->model->applicationExists($applno);
    }

    public function getApplicationData($applno)
    {
        return $this->model->getApplicationData($applno);
    }

    public function uploadExcel()
    {
        $this->loadPHPExcel();

        if (!isset($_FILES['bol_file']) || $_FILES['bol_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(array('status' => 'error', 'message' => 'Upload failed.'));
            return;
        }

        $tmp = $_FILES['bol_file']['tmp_name'];

        try {
            $reader = PHPExcel_IOFactory::createReaderForFile($tmp);
            $excel  = $reader->load($tmp);
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid Excel file.'));
            return;
        }

        $sheet      = $excel->getSheet(0);
        $highestCol = $sheet->getHighestColumn();
        $headers    = $sheet->rangeToArray('A1:' . $highestCol . '1', NULL, TRUE, FALSE);
        $headers    = $headers[0];

        $expected = array(
            "REGISTRY", "PORT", "HBL/AWB", "BL NATURE CODE",
            "DESTINATION PLACE CODE", "NO. OF PACKAGES",
            "PACKAGE TYPE", "GROSS WEIGHT"
        );

        $normalize = function ($v) {
            return strtoupper(trim($v));
        };

        $actual             = array_map($normalize, $headers);
        $expectedNormalized = array_map($normalize, $expected);

        if ($actual !== $expectedNormalized) {
            echo json_encode(array('status'  => 'error',
                                   'message' => 'Header mismatch. Please use the correct template.'));
            return;
        }

        $rows     = array();
        $errors      = array();
        $maxRows  = $sheet->getHighestRow(); // Get the last row number
        $startRow = 2;

        for ($rowNum = $startRow; $rowNum <= $maxRows; $rowNum++) {
            $row = $sheet->rangeToArray('A' . $rowNum . ':' . $highestCol . $rowNum, NULL, TRUE, FALSE);

            if (!isset($row[0])) {
                continue; // Skip if no row data
            }

            $currentRow = $row[0];

            // Skip completely empty rows
            if ($this->isRowEmpty($currentRow)) {
                continue 1;
            }

            // Ensure 8 columns
            while (count($currentRow) < 8) {
                $currentRow[] = '';
            }

            $rows[] = $currentRow;
        }

        $this->model->saveUploadedRows($rows);

        echo json_encode(array('status' => 'success', 'message' => 'Upload successful!', 'rows' => count($rows)));
    }

    function isRowEmpty($row)
    {
        if (empty($row)) {
            return false;
        }

        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') {
                return false;
            }
        }
        return true;
    }

    public function fetchUploadedBOL()
    {
        return $this->model->uploadedBOLData();
    }

    public function deleteUploadedBOL($id)
    {
        return $this->model->deleteUploadedBOL($id);
    }

    public function validateFields($data)
    {
        // Fetch validation rules from DB
        $rules = $this->model->getValidationRules();

        // Field mappings
        $fieldMap = array(
            'port'              => 'OffClear',
            'country_export'    => 'Cexp',
            'p_destination'     => 'OffClear',
            'l_goods'           => 'Lgoods',
            'transhipment_port' => 'Tport',
            'p_loading'         => 'PLoad',
            'package_type'      => 'PackCode',
            'co_code'           => 'COCode',
            'preference'        => 'Pref',
            'currency'          => 'InvCurr',
            'terms_delivery'    => 'TDelivery',
            'terms_payment'     => 'Tpayment',
            'package_no'        => 'NoPack',
            'gross_weight'      => 'ItemGWeight',
            'airbill'           => 'AirBill',
            'registry_no'       => 'Manifest',
        );

        $errors = array();

        // Validate master and fin fields
        $baseData = array_merge(
            isset($data['master']) ? $data['master'] : array(),
            isset($data['fin']) ? $data['fin'] : array()
        );

        foreach ($rules as $rule) {
            $field    = $rule['field'];
            $key      = isset($fieldMap[$field]) ? $fieldMap[$field] : null;
            $required = $rule['required'];
            $validate = $rule['validations'];

            $isPerItemField = in_array($field, array(
                'preference', 'currency', 'cd_code', 'package_type',
                'package_no', 'gross_weight'
            ));

            // Validate header (master + fin)
            if (!$isPerItemField) {
                $value = isset($baseData[$key]) ? trim($baseData[$key]) : '';

                // Special condition: TPORT required only if MDec is '8PP' or '8PE'
                if ($field === 'transhipment_port') {
                    $mdec = isset($data['master']['MDec']) ? strtoupper(trim($data['master']['MDec'])) : '';

                    if (in_array($mdec, ['8PP', '8PE']) && $value === '') {
                        $errors[] = [
                            'item'       => 1,
                            'field'      => $field,
                            'error_code' => 1200,
                            'error_desc' => "Transhipment Port is required for MDec $mdec"
                        ];
                        continue;
                    }
                } elseif ($required && $value === '') {
                    $errors[] = [
                        'item'       => 1,
                        'field'      => $field,
                        'error_code' => 1200,
                        'error_desc' => "Invalid Customs site $key"
                    ];
                    continue;
                }

                if (!empty($validate)) {
                    $rulesArr = explode('|', $validate);
                    foreach ($rulesArr as $singleRule) {
                        if (strpos($singleRule, 'exists:') === 0) {
                            list($table, $column) = explode('.', substr($singleRule, 7));
                            if (!$this->model->valueExists($table, $column, $value)) {
                                $errors[] = [
                                    'item'       => 1,
                                    'field'      => $field,
                                    'error_code' => 1200,
                                    'error_desc' => "Invalid Customs site $key"
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Validate each item
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $itemNum = isset($item['ItemNo']) ? intval($item['ItemNo']) : ($index + 1);

                foreach ($rules as $rule) {
                    $field    = $rule['field'];
                    $key      = isset($fieldMap[$field]) ? $fieldMap[$field] : null;
                    $required = $rule['required'];
                    $validate = $rule['validations'];

                    $isPerItemField = in_array($field, array(
                        'preference', 'currency', 'cd_code', 'package_type',
                        'package_no', 'gross_weight'
                    ));

                    if (!$isPerItemField) {
                        continue;
                    }

                    $value = isset($item[$key]) ? trim($item[$key]) : '';

                    if ($required && $value === '') {
                        $errors[] = [
                            'item'       => $itemNum,
                            'field'      => $field,
                            'error_code' => 1200,
                            'error_desc' => "Invalid Customs site $key"
                        ];
                        continue;
                    }

                    if (!empty($validate)) {
                        $rulesArr = explode('|', $validate);
                        foreach ($rulesArr as $singleRule) {
                            if (strpos($singleRule, 'exists:') === 0) {
                                list($table, $column) = explode('.', substr($singleRule, 7));
                                if (!$this->model->valueExists($table, $column, $value)) {
                                    $errors[] = [
                                        'item'       => $itemNum,
                                        'field'      => $field,
                                        'error_code' => 1200,
                                        'error_desc' => "Invalid Customs site $key"
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    public function updateMasterStatus($applno, $status)
    {
        return $this->model->updateMasterStatus($applno, $status);
    }

    public function insertRespHead($data)
    {
        return $this->model->insertRespHead($data);
    }

    public function insertApplStat($applno, $status, $msg)
    {
        return $this->stat_model->insertApplStat($applno, $status, $msg);
    }

    public function insertError($applno, $response)
    {
        return $this->model->insertError($applno, $response);
    }

    public function DecryptValue($strValue)
    {
        $strValue = trim($strValue);

        $DecryptValue = '';

        for ($i = 0; $i < strlen($strValue); $i++) {
            $DecryptValue = $DecryptValue . chr(ord(substr($strValue, $i, 1)) - 1);
        }

        return $DecryptValue;
    }

    public function fetchUploadedUsers()
    {
        return $this->admin_model->uploadedUsersData();
    }

    public function uploadUserExcel()
    {
        $this->loadPHPExcel();

        if (!isset($_FILES['user_file']) || $_FILES['user_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(array('status' => 'error', 'message' => 'Upload failed.'));
            return;
        }

        $tmp = $_FILES['user_file']['tmp_name'];

        try {
            $reader = PHPExcel_IOFactory::createReaderForFile($tmp);
            $excel  = $reader->load($tmp);
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid Excel file.'));
            return;
        }

        $sheet   = $excel->getSheet(0);
        $headers = $sheet->rangeToArray('A1:H1', NULL, TRUE, FALSE);
        $headers = $headers[0];

        $expected = array(
            "SR Code", "First Name", "Last Name", "Address", "Mobile No.",
            "Email", "Year", "Section"
        );

        $normalize = function ($v) {
            return strtoupper(trim($v));
        };

        $actual             = array_map($normalize, $headers);
        $expectedNormalized = array_map($normalize, $expected);

        if ($actual !== $expectedNormalized) {
            echo json_encode(array('status'  => 'error',
                                   'message' => 'Header mismatch. Please use the correct template.'));
            return;
        }

        $rows     = array();
        $maxRows  = $sheet->getHighestRow(); // Get the last row number
        $startRow = 2;

        for ($rowNum = $startRow; $rowNum <= $maxRows; $rowNum++) {
            $row = $sheet->rangeToArray("A{$rowNum}:H{$rowNum}", NULL, TRUE, FALSE);

            if (!isset($row[0])) {
                continue; // Skip if no row data
            }

            $currentRow = $row[0];

            // Skip completely empty rows
            if ($this->isRowEmpty($currentRow)) {
                continue 1;
            }

            // Ensure 8 columns
            while (count($currentRow) < 8) {
                $currentRow[] = '';
            }

            $rows[] = $currentRow;
        }

        try {
            $results = $this->admin_model->saveUploadedUserRows($rows);

            $successCount = count(array_filter($results, function ($r) {
                return $r['status'] === 'success';
            }));

            $failCount = count($results) - $successCount;

            echo json_encode([
                'status'  => $failCount === 0 ? 'success' : 'partial',
                'message' => "$successCount rows uploaded, $failCount failed.",
                'details' => $results
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Critical error: ' . $e->getMessage()
            ]);
        }
    }

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once __DIR__ . '/../libs/PHPExcel/Classes/PHPExcel.php';
        }

        if (!class_exists('PHPExcel_IOFactory')) {
            require_once __DIR__ . '/../libs/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }
    }
}