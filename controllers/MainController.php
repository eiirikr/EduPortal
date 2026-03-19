<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once __DIR__ . '/../models/MainModel.php';
require_once __DIR__ . '/../models/ApplStatModel.php';
require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../models/E2MDataModel.php';

class MainController
{
    private $model;
    private $stat_model;
    private $admin_model;
    private $e2m_model;

    public function __construct()
    {
        $this->model       = new MainModel();
        $this->stat_model  = new ApplStatModel();
        $this->admin_model = new AdminModel();
        $this->e2m_model   = new E2MDataModel();
    }

    public function generateTS()
    {
        // Fetch all recently inserted/updated BOL rows
        $bolRows = $this->model->getAllBOLRows();

        $tsCreated = 0;
        foreach ($bolRows as $row) {
            $this->syncTSRow($row, $tsCreated);
        }

        echo json_encode([
            'status' => 'success',
            'message' => "TS Manifest synced successfully",
            'ts_generated' => $tsCreated
        ]);
    }

    private function syncTSRow($row, &$tsCreated)
    {
        $tsRegistry = 'TS' . $row['registry'];
        $tsExists   = $this->model->getBOLData($tsRegistry);

        $mdec = isset($row['Mdec']) ? strtoupper($row['Mdec']) : null;
        $mdec2 = isset($row['Mdec2']) ? (int)$row['Mdec2'] : null;
        $stat  = isset($row['Stat']) ? strtoupper($row['Stat']) : '';

        // Condition: Mdec = 8PP AND Mdec2 = 8 AND Stat = AP
        if ($mdec === '8PP' && $mdec2 === 8 && $stat === 'AP') {
            $tsRow = $row;
            $tsRow['registry'] = $tsRegistry;
            $tsRow['port'] = '';  // TS row port is empty
            unset($tsRow['id']);  // Remove original ID for insert

            if ($tsExists) {
                $this->model->updateTSRow($tsRegistry, $tsRow);
            } else {
                $this->model->insertTSRow($tsRow);
                $tsCreated++;
            }
        }
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
            $row = $sheet->rangeToArray('A' . $rowNum . ':' . $highestCol . $rowNum, NULL, TRUE, FALSE)[0];

            if ($this->isRowEmpty($row)) {
                continue;
            }

            // Pad missing columns
            while (count($row) < 8) {
                $row[] = '';
            }

            // Basic validation per field (customize as needed)
            list($registry, $port, $blno, $blNature, $destination, $packCount, $packageType, $grossWeight) = $row;

            if (empty($registry)) {
                $errors[] = ['message' => 'Registry is required.', 'row' => $rowNum];
            } elseif (!preg_match("/^[A-Z]{3}[0-9]{4}-[0-9]{2}$/", $registry)) {
                if (preg_match("/^[A-Z]{5}[0-9]{4}-[0-9]{2}$/", $registry)) {
                    
                
                }else{
                    $errors[] = ['message' => 'Registry format is invalid. Must be like ABC1234-56.', 'row' => $rowNum];
         
                }
            }

            if (empty($port)) {
                $errors[] = ['message' => 'Port is required.', 'row' => $rowNum];
            } elseif (!$this->model->portCodeExists($port)) {
                $errors[] = ['message' => "Port code '{$port}' does not exist in BOC records.", 'row' => $rowNum];
            }

            if (empty($blno)) {
                $errors[] = ['message' => 'HBL/AWB is required.', 'row' => $rowNum];
            } elseif (strlen(trim($blno)) > 17) {
                $errors[] = ['message' => 'HBL/AWB must not exceed 17 characters.', 'row' => $rowNum];
            }

            if (empty($blNature)) {
                $errors[] = ['message' => 'BL Nature is required.', 'row' => $rowNum];
            } elseif (!in_array($blNature, array('23', '24'))) {
                $errors[] = ['message' => 'BL Nature must not be either 23 or 24', 'row' => $rowNum];
            }

            if (!is_numeric($packCount)) {
                $errors[] = ['message' => 'No. of Packages must be numeric.', 'row' => $rowNum];
            }

            if (!is_numeric($grossWeight)) {
                $errors[] = ['message' => 'Gross Weight must be numeric.', 'row' => $rowNum];
            }

            if (empty($destination)) {
                $errors[] = ['message' => 'Destination Place Code is required.', 'row' => $rowNum];
            } elseif (!$this->model->destinationCodeExist($destination)) {
                $errors[] = ['message' => "Destination Place Code '{$destination}' does not exist in BOC records.", 'row' => $rowNum];
            }

            if (empty($packageType)) {
                $errors[] = ['message' => 'Package Type is required.', 'row' => $rowNum];
            } elseif (!$this->model->packageTypeExists($packageType)) {
                $errors[] = ['message' => "Package type '{$packageType}' does not exist in BOC records.", 'row' => $rowNum];
            }

            $rows[] = $row;
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Upload failed due to validation errors.',
                'errors' => $errors
            ]);
            return;
        }

        $result = $this->model->saveUploadedRows($rows);
        $duplicateErrors = $result['errors'];
        $insertedRegNos = isset($result['inserted']) ? $result['inserted'] : [];

        if (!empty($duplicateErrors)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Upload failed due to duplicate BL numbers.',
                'errors'  => $duplicateErrors
            ]);
            return;
        } else {
            $tsCreated = 0;
            $insertedRegNos = array_unique($insertedRegNos);
            foreach ($insertedRegNos as $regNo) {
                if ($this->model->generateTSForRegNo($regNo)) {
                    $tsCreated++;
                }
            }

            $response = [
                'status' => empty($duplicateErrors) ? 'success' : 'partial',
                'message' => empty($duplicateErrors)
                    ? 'Upload successful!'
                    : 'Upload partially successful. Some duplicates found.',
                'rows' => count($rows),
                'ts_generated' => $tsCreated
            ];

            if (!empty($duplicateErrors)) {
                $response['errors'] = $duplicateErrors;
            }

            echo json_encode($response);
        }

        // Sync TS rows dynamically for all inserted/updated BOLs
        $tsCreated = 0;
        foreach ($insertedRegNos as $regNo) {
            $rowData = $this->model->getBOLData($regNo);
            if ($rowData) {
                $this->syncTSRow($rowData, $tsCreated);
            }
        }
    }

    function isRowEmpty($row) {
        foreach ($row as $cell) {
            if (isset($cell) && trim((string)$cell) !== '') return false;
        }
        return true;
    }

    public function fetchUploadedBOL($searchTerm)
    {
        return $this->model->uploadedBOLData($searchTerm);
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
            //            'preference'        => 'Pref',
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
                'preference', 'currency', 'co_code', 'package_type',
                'package_no', 'gross_weight', 'airbill'
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
                } elseif ($field === 'p_loading') {
                    $mdec = isset($data['master']['MDec']) ? strtoupper(trim($data['master']['MDec'])) : '';

                    if (in_array($mdec, ['8ZN']) && $value === '') {
                        $errors[] = [
                            'item'       => 1,
                            'field'      => $field,
                            'error_code' => 1200,
                            'error_desc' => "Port of Loading is required for MDec $mdec"
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
                        'preference', 'currency', 'co_code', 'package_type',
                        'package_no', 'gross_weight', 'airbill'
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

                    // Add B/L Nature validation for airbill if MDec starts with 8
                    if ($field === 'airbill') {
                        $mdec = isset($data['master']['Mdec2']) ? strtoupper(trim($data['master']['Mdec2'])) : '';

                        if ($value !== '') {
                            // 1. First: check if the BOL exists in bol_manifest
                            $blExists = $this->model->airbillExists($value);
                            if (!$blExists) {
                                $errors[] = [
                                    'item'       => $itemNum,
                                    'field'      => $field,
                                    'error_code' => 1331,
                                    'error_desc' => "Declared BOL not found"
                                ];
                                continue; // Stop further checks for this airbill
                            }

                            // 2. Then: check if Mdec2 starts with '8' AND BOL has bl_nature != 24
                            if (preg_match('/^8/', $mdec)) {
                                $blnValid = $this->model->airbillHasNatureCode24($value);
                                if (!$blnValid) {
                                    $errors[] = [
                                        'item'       => $itemNum,
                                        'field'      => $field,
                                        'error_code' => 1330,
                                        'error_desc' => "Declaration General Procedure code and B/L nature code incompatible"
                                    ];
                                }
                            }

                            // 3. Finally: check for availability / reuse
                            $BLisUsed = $this->model->checkBLNoAvailability($value);
                            if ($BLisUsed) {
                                $errors[] = [
                                    'item'       => $itemNum,
                                    'field'      => $field,
                                    'error_code' => 1481,
                                    'error_desc' => "BOL not available"
                                ];
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

    public function insertRespHead($applno, $data, $status)
    {
        return $this->model->insertRespHead($applno, $data, $status);
    }

    public function insertApplStat($applno)
    {
        return $this->stat_model->insertApplStat($applno);
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

    public function fetchUploadedUsers($searchTerm)
    {
        return $this->admin_model->uploadedUsersData($searchTerm);
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
        // Now expect 10 columns including the new 'School' column
        $headers = $sheet->rangeToArray('A1:J1', NULL, TRUE, FALSE);
        $headers = $headers[0];
        // Updated expected headers to include 'School, Account Type'
        $expected = array(
            "Student Number", "First Name", "Last Name", "Address", "Mobile No.",
            "Email", "Year", "Section", "School", "Account Type"
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
        $errors   = array();
        $maxRows  = $sheet->getHighestRow(); // Get the last row number
        $startRow = 2;

        for ($rowNum = $startRow; $rowNum <= $maxRows; $rowNum++) {
            // A-J in excel
            $row = $sheet->rangeToArray("A{$rowNum}:J{$rowNum}", NULL, TRUE, FALSE)[0];

            if (!isset($row)) {
                continue;
            }

            if ($this->isRowEmpty($row)) {
                continue;
            }

            // Ensure 10 columns
            while (count($row) < 10) {
                $row[] = '';
            }

            list($StudentNo, $firstName, $lastName, $address, $mobile, $email, $year, $section, $school, $account_type) = $row;

            // Row validation
            if (empty($StudentNo)) {
                $errors[] = ['message' => 'Student Number is required.', 'row' => $rowNum];
            }
            if (empty($firstName)) {
                $errors[] = ['message' => 'First Name is required.', 'row' => $rowNum];
            }
            if (empty($lastName)) {
                $errors[] = ['message' => 'Last Name is required.', 'row' => $rowNum];
            }
            if (empty($address)) {
                $errors[] = ['message' => 'Address is required.', 'row' => $rowNum];
            }
            if (empty($mobile) || !preg_match('/^[0-9]{7,15}$/', $mobile)) {
                $errors[] = ['message' => 'Mobile No. must be numeric and at least 7 digits.', 'row' => $rowNum];
            }
            if (empty($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['message' => 'Invalid Email format.', 'row' => $rowNum];
            }
            if (empty($year)) {
                $errors[] = ['message' => 'Year is required.', 'row' => $rowNum];
            }
            if (empty($section)) {
                $errors[] = ['message' => 'Section is required.', 'row' => $rowNum];
            }
            if (empty($school)) {
                $errors[] = ['message' => 'School is required.', 'row' => $rowNum];
            }
            if (empty($account_type)) {
                $errors[] = ['message' => 'Account Type is required.', 'row' => $rowNum];
            }

            $rows[] = $row;
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Upload failed due to validation errors.',
                'errors' => $errors
            ]);
            return;
        }

        try {
            $results = $this->admin_model->saveUploadedUserRows($rows);

            $errorRows = array_filter($results, function ($res) {
                return isset($res['message']); // Only keep rows that have an error message
            });

            if (!empty($errorRows)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Some rows were skipped or failed.',
                    'errors'  => array_values($errorRows) // return only problematic rows
                ]);
                return;
            }

            echo json_encode([
                'status'  => 'success',
                'message' => count($rows) . ' rows uploaded successfully.'
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

    public function getTaxes($details)
    {
        $master = $details['master'];
        $fin    = $details['fin'];
        $items  = $details['items'];

        $gt_taxes = array();
        $it_taxes = array();

        $CUD                  = 0;
        $total_dutiable_value = 0;
        $IPC                  = 0;
        $bank_charge          = 0;
        $broker_fee           = 0;
        $total_exc            = 0;
        $total_fmf            = 0;
        $total_avt            = 0;

        $exchange_rate = $this->model->getUSDExchRate();

        $finCustomVal = (float)str_replace(',', '', $fin['CustomVal']);
        $freightCost  = (float)str_replace(',', '', $fin['FreightCost']);
        $insCost      = (float)str_replace(',', '', $fin['InsCost']);
        $otherCost    = (float)str_replace(',', '', $fin['OtherCost']);

        foreach ($items as $item) {
            $invValue = (float)str_replace(',', '', $item['InvValue']);
            $share    = $finCustomVal > 0 ? $invValue / $finCustomVal : 0;

            $freight   = round($share * $freightCost * $exchange_rate, 2);
            $insurance = round($share * $insCost * $exchange_rate, 2);
            $other     = round($share * $otherCost * $exchange_rate, 2);

            $inv_val = round($invValue * $exchange_rate, 2);
            $fio     = round($freight + $insurance + $other, 2);

            $dutiable = round($fio + $inv_val, 2);
            $item_cud = round($dutiable * $item['HsRate'] / 100, 2);

            $total_dutiable_value += $dutiable;
            $CUD                  += $item_cud;

            // Per-item VAT calculation
            $doc_fee   = 280;
            $item_ipc  = 0;
            $item_bank = 0;
            if (empty($fin['WOBankCharge'])) {
                $item_bank = round($dutiable * 0.00125, 2);
            }

            $item_broker = 0;
            if ($master['MDec'] == 'IES') {
                $item_broker = 700;
            } elseif ($dutiable <= 10000) {
                $item_broker = 1300;
            } elseif ($dutiable <= 20000) {
                $item_broker = 2000;
            } elseif ($dutiable <= 30000) {
                $item_broker = 2700;
            } elseif ($dutiable <= 40000) {
                $item_broker = 3300;
            } elseif ($dutiable <= 50000) {
                $item_broker = 3600;
            } elseif ($dutiable <= 60000) {
                $item_broker = 4000;
            } elseif ($dutiable <= 100000) {
                $item_broker = 4700;
            } elseif ($dutiable <= 200000) {
                $item_broker = 5300;
            } else {
                $item_broker = round(($dutiable - 200000) * 0.00125 + 5300, 2);
            }

            $item_landed = round($dutiable + $item_cud + $item_bank + $item_broker + 0 + 0 + $doc_fee + $item_ipc, 2);
            $item_vat    = round($item_landed * 0.12, 2);

            $item_no = isset($item['ItemNo']) ? $item['ItemNo'] : 'unknown_' . uniqid();

            $excise      = 0;
            $exciseRate  = '';
            $exciseUnit  = '';
            $exciseTotal = '';

            $ai_rate = $this->model->getAICodeRate($item['HsCode'], $item['HSCODE_TAR'], $item['TARSPEC']);
            $rul_cod = $this->model->getHsCodeRulCod($item['HsCode'], $item['HSCODE_TAR']);

            if (strtoupper($item['ExciseType']) == 'PHARMACEUTICALS') {
                if (isset($item['GBTARTAB_rulcod']) && $rul_cod == 'EXC-300390') {
                    if (!empty($aiCodeData)) {
                        $exciseRate = $ai_rate;
                        $excise     = round($item['SupVal1'] * ($exciseRate / 100), 2);
                    }
                }
            } else {
                $exciseRate  = isset($item['ExciseRate']) ? $item['ExciseRate'] : '';
                $exciseUnit  = isset($item['ExciseUnit']) ? $item['ExciseUnit'] : '';
                $exciseTotal = isset($item['ExciseTotal']) ? $item['ExciseTotal'] : '';

                if ($exciseTotal != '' && $exciseTotal != 0 && $exciseTotal != null) {
                    $excise = (float)$exciseTotal;
                }
            }

            $total_exc += $excise;

            // ----- FMF -----
            $fmf      = 0;
            $fmf_rate = 0;
            $fmf_base = $item['SupVal1'];

            $hs   = $item['HsCode'];
            $tar  = $item['HSCODE_TAR'];
            $spec = $item['TARSPEC'];

            if (in_array($hs, array(
                "27101211",
                "27101971",
                "27101972",
                "27101225",
                "27101222",
                "27101228",
                "27101983",
                "27101229",
                "27101223",
                "27101224",
                "27101226",
                "27101227",
                "27101212",
                "27101213",
                "27101221",
                "25232990",
                "25239000"
            ))) {
                if ($hs == "27101211" && $tar == "100") {
                    $fmf_rate = 1 * 0.06146428571;
                } elseif ($hs == "27101211") {
                    $fmf_rate = 1.10 * 0.06146428571;
                } elseif ($hs == "27101971" || $hs == "27101972") {
                    $fmf_rate = 1.03 * 0.06146428571;
                } elseif (in_array($hs, array("27101225", "27101222", "27101228", "27101983"))) {
                    $fmf_rate = 1 * 0.06146428571;
                } elseif (in_array(
                    $hs,
                    array(
                        "27101229",
                        "27101223",
                        "27101224",
                        "27101226",
                        "27101227",
                        "27101212",
                        "27101213",
                        "27101221"
                    )
                )) {
                    $fmf_rate = 1.10 * 0.06146428571;
                } elseif ($hs == "25232990" || $hs == "25239000") {
                    $rates    = array(
                        '1001' => 0.0233,
                        '1002' => 0.0276,
                        '1003' => 0.0341,
                        '1004' => 0.0619,
                        '1005' => 0.0794,
                        '1006' => 0.0948,
                        '1007' => 0.0951,
                        '1008' => 0.1067,
                        '1009' => 0.1099,
                        '1010' => 0.1158,
                        '1011' => 0.1206,
                        '1012' => 0.1529,
                        '1013' => 0.2307,
                        '1014' => 0.00,
                        ''     => 0.2333
                    );
                    $fmf_rate = isset($rates[$spec]) ? $rates[$spec] : 0;
                }

                $fmf = round($fmf_base * $fmf_rate, 2);
            }

            $total_fmf += $fmf;

            $MSP = $item['MSP'];
            if ($rul_cod == 'AVT-AUTO') {
                if ($MSP <= 600000) {
                    $MSP = ($MSP * 0.04) * $item['SupVal1'];
                } elseif ($MSP > 600000 && $item['MSP'] <= 1000000) {
                    $MSP = ($MSP * 0.10) * $item['SupVal1'];
                } elseif ($MSP > 1000000 && $item['MSP'] <= 4000000) {
                    $MSP = ($MSP * 0.20) * $item['SupVal1'];
                } elseif ($MSP > 4000000) {
                    $MSP = ($MSP * 0.50) * $item['SupVal1'];
                }
            } elseif ($rul_cod == 'AVT_HYBRID') {
                if ($MSP <= 600000) {
                    $MSP = ($MSP * 0.02) * $item['SupVal1'];
                } elseif ($MSP > 600000 && $item['MSP'] <= 1000000) {
                    $MSP = ($MSP * 0.05) * $item['SupVal1'];
                } elseif ($MSP > 1000000 && $item['MSP'] <= 4000000) {
                    $MSP = ($MSP * 0.10) * $item['SupVal1'];
                } elseif ($MSP > 4000000) {
                    $MSP = ($MSP * 0.25) * $item['SupVal1'];
                }
            }

            $total_avt += $MSP;

            $it_taxes[$item_no] = array(
                'CUD' => $item_cud,
                'VAT' => $item_vat,
                'EXC' => $excise,
                'FMF' => $fmf,
                'AVT' => $MSP
            );
        }

        $total_dutiable_value = round($total_dutiable_value, 2);
        $CUD                  = round($CUD, 2);

        $rounded_val = round($total_dutiable_value);
        if ($rounded_val <= 25000) {
            $IPC = 250;
        } elseif ($rounded_val <= 50000) {
            $IPC = 500;
        } elseif ($rounded_val <= 250000) {
            $IPC = 750;
        } elseif ($rounded_val <= 500000) {
            $IPC = 1000;
        } elseif ($rounded_val <= 750000) {
            $IPC = 1500;
        } else {
            $IPC = 2000;
        }

        if (empty($fin['WOBankCharge'])) {
            $bank_charge = round($total_dutiable_value * 0.00125, 2);
        }

        if ($master['MDec'] == 'IES') {
            $broker_fee = 700;
        }

        if ($total_dutiable_value <= 10000) {
            $broker_fee = 1300;
        } elseif ($total_dutiable_value <= 20000) {
            $broker_fee = 2000;
        } elseif ($total_dutiable_value <= 30000) {
            $broker_fee = 2700;
        } elseif ($total_dutiable_value <= 40000) {
            $broker_fee = 3300;
        } elseif ($total_dutiable_value <= 50000) {
            $broker_fee = 3600;
        } elseif ($total_dutiable_value <= 60000) {
            $broker_fee = 4000;
        } elseif ($total_dutiable_value <= 100000) {
            $broker_fee = 4700;
        } elseif ($total_dutiable_value <= 200000) {
            $broker_fee = 5300;
        } else {
            $broker_fee = round(($total_dutiable_value - 200000) * 0.00125 + 5300, 2);
        }

        $wharfage = 0;
        $arrastre = 0;
        $doc_fee  = 280;

        $landedcost = round(
            $total_dutiable_value + $CUD + $bank_charge + $broker_fee + $wharfage + $arrastre + $doc_fee + $IPC,
            2
        );
        $vat        = round($landedcost * 0.12, 2);

        // VAT adjustment: ensure sum(it_taxes[*]['VAT']) == $vat
        $vat_sum = 0;
        foreach ($it_taxes as $tx) {
            $vat_sum += $tx['VAT'];
        }

        $vat_diff = round($vat - $vat_sum, 2);
        if (abs($vat_diff) > 0 && count($it_taxes) > 0) {
            end($it_taxes);
            $last_key                   = key($it_taxes);
            $it_taxes[$last_key]['VAT'] = round($it_taxes[$last_key]['VAT'] + $vat_diff, 2);
        }

        $gt_taxes = array(
            'CUD' => $CUD,
            'IPC' => $IPC,
            'VAT' => $vat,
            'EXC' => $total_exc,
            'FMF' => $total_fmf,
            'AVT' => $total_avt
        );

        return array(
            'gt_taxes' => $gt_taxes,
            'it_taxes' => $it_taxes
        );
    }

    public function insertGTTaxes($applno, $taxes)
    {
        return $this->model->insertGTTaxes($applno, $taxes);
    }

    public function insertITTaxes($applno, $taxes)
    {
        return $this->model->insertITTaxes($applno, $taxes);
    }

    public function insertSSDT($applno, $data)
    {
        return $this->e2m_model->insertSSDT($applno, $data);
    }

    public function insertTANFAN($applno, $data)
    {
        return $this->model->insertTANFAN($applno, $data);
    }
}