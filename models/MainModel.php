<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once __DIR__ . '/../core/Database.php';

class MainModel extends Database
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function applicationExists($applno)
    {
        $sql    = "SELECT ApplNo FROM TBLIMPAPL_FIN WHERE ApplNo = ?";
        $params = array($applno);

        $result = $this->db->select($sql, $params);

        return !empty($result);  // if result array not empty, appl exists
    }

    public function getApplicationData($applno)
    {
        $sql    = "SELECT master.DecTin, master.OffClear, master.MDec, master.Mdec2,
                master.Cexp, master.Lgoods, master.Tport, master.PLoad,
                detail.Pref, detail.InvCurr, detail.COCode, detail.PackCode, detail.ItemNo,
                detail.NoPack, detail.ItemGWeight, fin.TDelivery, fin.Tpayment,
                master.Manifest, detail.AirBill, master.pdest,
                detail.InvValue, fin.CustomVal, fin.FreightCost, fin.InsCost, fin.OtherCost,
                detail.HsRate, detail.SupVal1, detail.SupUnit1, detail.ExciseType,
                detail.ExciseRate, detail.ExciseQty, detail.ExciseUnit, detail.ExciseTotal,
                fin.WOBankCharge, fin.WharCost, fin.ArrasCost,
                master.Brokername, master.BrokAddr1, master.BrokAddr2, master.BrokAddr3,
                 master.ConTIN, master.ConName, master.ConAddr1, master.ConAddr2, master.ConAddr3,
                detail.HsCode, detail.HSCODE_TAR, detail.TARSPEC, detail.MSP
                FROM TBLIMPAPL_MASTER master
                LEFT JOIN TBLIMPAPL_DETAIL AS detail
                    ON master.ApplNo = detail.ApplNo
                LEFT JOIN TBLIMPAPL_FIN AS fin
                    ON master.ApplNo = fin.ApplNo
                    WHERE master.ApplNo = ?";
        $params = array(trim($applno));
        $rows   = $this->db->select($sql, $params);

        if (empty($rows)) {
            return [];
        }

        // Use first row to extract master & fin
        $firstRow = $rows[0];
        $result   = array(
            'master' => array(
                'OffClear'     => $firstRow['OffClear'],
                'Cexp'         => $firstRow['Cexp'],
                'Lgoods'       => $firstRow['Lgoods'],
                'Tport'        => $firstRow['Tport'],
                'PLoad'        => $firstRow['PLoad'],
                'pdest'        => $firstRow['pdest'],
                'DecTin'       => $firstRow['DecTin'],
                'MDec'         => $firstRow['MDec'],
                'Mdec2'        => $firstRow['Mdec2'],
                'Manifest'     => $firstRow['Manifest'],
                'Brokername'   => $firstRow['Brokername'],
                'BrokAddr1'    => $firstRow['BrokAddr1'],
                'BrokAddr2'    => $firstRow['BrokAddr2'],
                'BrokAddr3'    => $firstRow['BrokAddr3'],
                'ConTIN'       => $firstRow['ConTIN'],
                'ConName'      => $firstRow['ConName'],
                'ConAddr1'     => $firstRow['ConAddr1'],
                'ConAddr2'     => $firstRow['ConAddr2'],
                'ConAddr3'     => $firstRow['ConAddr3'],
            ),
            'fin'    => array(
                'TDelivery'     => $firstRow['TDelivery'],
                'Tpayment'      => $firstRow['Tpayment'],
                'CustomVal'     => $firstRow['CustomVal'],
                'FreightCost'   => $firstRow['FreightCost'],
                'InsCost'       => $firstRow['InsCost'],
                'OtherCost'     => $firstRow['OtherCost'],
                'WOBankCharge'  => $firstRow['WOBankCharge'],
                'WharCost'      => $firstRow['WharCost'],
                'ArrasCost'     => $firstRow['ArrasCost'],
            ),
            'items'  => array()
        );

        // Loop through rows to collect detail items
        foreach ($rows as $row) {
            $result['items'][] = array(
                'Pref'        => $row['Pref'],
                'InvCurr'     => $row['InvCurr'],
                'COCode'      => $row['COCode'],
                'PackCode'    => $row['PackCode'],
                'ItemNo'      => $row['ItemNo'],
                'NoPack'      => $row['NoPack'],
                'ItemGWeight' => $row['ItemGWeight'],
                'AirBill'     => $row['AirBill'],
                'InvValue'    => $row['InvValue'],
                'HsRate'      => $row['HsRate'],
                'SupVal1'     => $row['SupVal1'],
                'SupUnit1'    => $row['SupUnit1'],
                'ExciseType'  => $row['ExciseType'],
                'ExciseRate'  => $row['ExciseRate'],
                'ExciseQty'   => $row['ExciseQty'],
                'ExciseUnit'  => $row['ExciseUnit'],
                'ExciseTotal' => $row['ExciseTotal'],
                'HsCode'      => $row['HsCode'],
                'HSCODE_TAR'  => $row['HSCODE_TAR'],
                'TARSPEC'     => $row['TARSPEC'],
                'MSP'         => $row['MSP'],
            );
        }

        return $result;
    }

    public function saveUploadedRows($rows)
    {
        $errors = [];
        $insertedRegNos = [];

        foreach ($rows as $i => $row) {

            list($registry, $port, $blno, $bl_nature, $pl_destination, $package_no, $package_type, $gross_weight) = $row;

            // 1. Check if blno already exists
            $sql    = "SELECT blno FROM bol_manifest WHERE blno = ? AND registry = ? AND port = ?";
            $params = array($blno, $registry, $port);
            $exists = $this->db->select($sql, $params);

            if (!empty($exists)) {
                $errors[] = [
                    'message' => "Duplicate BL No: {$blno} already exists for registry {$registry} and port {$port}.",
                    'row'     => $i + 2
                ];
                continue;
            }

            $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';

            // 2. Insert row
            $insertSql = "
                INSERT INTO bol_manifest (
                    registry, port, blno, bl_nature, pl_destination,
                    package_no, package_type, gross_weight, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $insertParams = array(
                $registry,
                $port,
                $blno,
                $bl_nature,
                $pl_destination,
                $package_no,
                $package_type,
                $gross_weight,
                $created_by
            );

            $this->db->insert($insertSql, $insertParams);

            // collect inserted registry (for TS generation)
            $insertedRegNos[] = $registry;
        }

        // return BOTH errors and inserted registries
        return [
            'errors'   => $errors,
            'inserted' => $insertedRegNos
        ];
    }

    public function uploadedBOLData($searchTerm = '')
    {
        $params = array();
        $sql    = "SELECT * FROM bol_manifest";

        if (!empty($searchTerm)) {
            $sql    .= " WHERE registry LIKE ?
                  OR port LIKE ?
                  OR blno LIKE ?
                  OR bl_nature LIKE ?
                  OR pl_destination LIKE ?
                  OR package_no LIKE ?
                  OR package_type LIKE ?
                  OR gross_weight LIKE ?
                  OR created_by LIKE ?";
            $term   = '%' . $searchTerm . '%';
            $params = array_fill(0, 9, $term);
        }

        $sql .= " ORDER BY date_created DESC";

        return $this->db->select($sql, $params);
    }

    public function deleteUploadedBOL($id)
    {
        $sql = "DELETE FROM bol_manifest WHERE id = '$id'";
        return $this->db->delete($sql);
    }

    public function getValidationRules()
    {
        $sql = "SELECT field, required, validations FROM response_validator";

        return $this->db->select($sql);
    }

    public function valueExists($table, $column, $value)
    {
        $sql    = "SELECT {$column} FROM {$table} WHERE {$column} = ?";
        $params = array($value);

        $result = $this->db->select($sql, $params);

        return isset($result[0][$column]) && !empty($result[0][$column]) ? true : false;
    }

    public function updateMasterStatus($applno, $status)
    {
        $sql = "UPDATE TBLIMPAPL_MASTER SET Stat = '$status' WHERE ApplNo = '$applno'";
        return $this->db->update($sql);
    }

    public function insertRespHead($applno, $data, $status)
    {
        $port    = isset($data['master']['OffClear']) ? $data['master']['OffClear'] : '';
        $dec_tin = isset($data['master']['DecTin']) ? $data['master']['DecTin'] : '';
        $mdec    = isset($data['master']['MDec']) ? $data['master']['MDec'] : '';
        $mdec2   = isset($data['master']['Mdec2']) ? $data['master']['Mdec2'] : '';

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Asia/Manila'));
        $date_slash = $now->format('m/d/Y');
        $date_dash  = $now->format('Y-m-d');
        $time       = $now->format('H:i:s');
        $datetime   = $now->format('Y-m-d H:i:s');

        // Determine entry type based on MDec and Mdec2
        $entry_type = '';
        $mdec_first = substr($mdec, 0, 1);

        // Prioritize special case: IES with Mdec2 = 4
        if ($mdec === 'IES' && $mdec2 == 4) {
            $entry_type = 'I';
        } else {
            // Use switch for better readability
            switch ($mdec_first) {
                case '4':
                case '5':
                    $entry_type = 'C';
                    break;
                case '8':
                    $entry_type = 'T';
                    break;
                case '7':
                    $entry_type = 'W';
                    break;
                default:
                    $entry_type = ''; // Or handle as 'Unknown'
                    break;
            }
        }

        $reg_no = $this->getEntryNumberSeries($entry_type);

        $this->deleteData($applno, 'TBLRESP_HEAD');

        $sql    = "INSERT INTO TBLRESP_HEAD (
            APPLNO, COLOR, DECTIN, PORT, PRODATE, PROTIME, REGNO, RECPNO, ASSESSNO, ASSREF,
                          RECREF, ASSESSDATE, REGDATE, STATUS, REGREF, XMLProcessedDate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?, ?)";
        $params =
            array(
                $applno,
                0,
                $dec_tin,
                $port,
                $date_slash,
                $time,
                $reg_no,
                $reg_no,
                $reg_no,
                'L',
                'R',
                $date_dash,
                $date_dash,
                $status,
                $entry_type,
                $datetime
            );

        $this->db->insert($sql, $params);

        return [
            'entry_type' => $entry_type,
            'entry_no'   => $reg_no
        ];
    }

    public function getEntryNumberSeries($entry_type)
    {
        $sql    = "SELECT TOP(1) REGNO FROM TBLRESP_HEAD WHERE REGREF = ? ORDER BY REGNO DESC";
        $params = array($entry_type);

        $result = $this->db->select($sql, $params);

        if (empty($result)) {
            return '000001';
        } else {
            $lastRegNo = $result[0]['REGNO'];
            $newRegNo  = str_pad((int)$lastRegNo + 1, 6, '0', STR_PAD_LEFT);
            return $newRegNo;
        }
    }

    public function insertError($applno, $response)
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Asia/Manila'));
        $datetime = $now->format('Y-m-d H:i:s');

        foreach ($response as $error) {
            $sql    = "INSERT INTO TBLRESP_ERR (
                APPLNO, ITEMNO, FLDDESC, ERRCODE, XMLProcessedDate
                ) VALUES (?, ?, ?, ?, ?)";
            $params = array($applno, $error['item'], $error['error_desc'], $error['error_code'], $datetime);

            $this->db->insert($sql, $params);
        }

        return true;
    }

    public function portCodeExists($code)
    {
        $sql    = "SELECT 1 FROM DmOffClr WHERE UPPER(offClrcod) = ?";
        $result = $this->db->select($sql, [strtoupper(trim($code))]);
        return !empty($result);
    }

    public function packageTypeExists($code)
    {
        $sql    = "SELECT 1 FROM GBPKGTAB WHERE UPPER(pkg_cod) = ?";
        $result = $this->db->select($sql, [strtoupper(trim($code))]);
        return !empty($result);
    }

    public function destinationCodeExist($code)
    {
        $sql    = "SELECT 1 FROM GBLOCTAB WHERE UPPER(loc_cod) = ?";
        $result = $this->db->select($sql, [strtoupper(trim($code))]);
        return !empty($result);
    }

    public function airbillHasNatureCode24($airbill)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM bol_manifest WHERE blno = ? AND bl_nature = '24'";
        $result = $this->db->select($sql, array($airbill));
        return isset($result[0]['cnt']) && $result[0]['cnt'] > 0 ? 1 : 0;
    }

    public function checkBLNoAvailability($airbill)
    {
        $sql = "SELECT COUNT(head.ApplNo) as cnt
                FROM TBLIMPAPL_DETAIL detail
                LEFT JOIN TBLRESP_HEAD as head
                    ON detail.ApplNo = head.ApplNo
                WHERE detail.AirBill = ?";

        $result = $this->db->select($sql, array($airbill));

        return isset($result[0]['cnt']) && $result[0]['cnt'] > 0 ? 1 : 0;
    }

    public function airbillExists($airbill)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM bol_manifest WHERE blno = ?";
        $result = $this->db->select($sql, array($airbill));

        return isset($result[0]['cnt']) && $result[0]['cnt'] > 0 ? 1 : 0;
    }

    public function getUSDExchRate()
    {
        $sql    = "SELECT TOP(1) RAT_EXC FROM GBRATTAB WHERE CUR_COD = ? ORDER BY EEA_DOV DESC";
        $result = $this->db->select($sql, array('USD'));

        return isset($result[0]['RAT_EXC']) && $result[0]['RAT_EXC'] ? $result[0]['RAT_EXC'] : 0;
    }

    public function insertGTTaxes($applno, $taxes)
    {
     
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Asia/Manila'));
        $datetime = $now->format('Y-m-d H:i:s');

        $this->deleteData($applno, 'TBLRESP_GT');

        foreach ($taxes as $key => $tax) {
            $sql    = "INSERT INTO TBLRESP_GT (
                APPLNO, TAXCODE, TAXAMT, MP, XMLProcessedDate
                ) VALUES (?, ?, ?, ?, ?)";
            $params = array($applno, $key, $tax, 1, $datetime);

            $this->db->insert($sql, $params);
        }

        return true;
    }

    public function insertITTaxes($applno, $taxes)
    {
        $this->deleteData($applno, 'TBLRESP_IT');

        foreach ($taxes as $key => $tax_array) {
            foreach ($tax_array as $tax_code => $tax) {
                $sql    = "INSERT INTO TBLRESP_IT (
                APPLNO, TAXCODE, TAXAMT, ITEMNO
                ) VALUES (?, ?, ?, ?)";
                $params = array($applno, $tax_code, $tax, $key);

                $this->db->insert($sql, $params);
            }
        }

        return true;
    }

    public function deleteData($applno, $table)
    {
        $sql = "DELETE FROM $table WHERE ApplNo = '$applno'";
        return $this->db->delete($sql);
    }

    public function insertTANFAN($applno, $data)
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Asia/Manila'));
        $datetime = $now->format('Y-m-d H:i:s');

        $this->deleteData($applno, 'GBTANFAN');

        $sql    = "INSERT INTO GBTANFAN (
                RefNo, Status, Color, SerialNo, RegNo, CreatedDate, RegDate
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = array($applno, 'AP', 0, $data['entry_type'], $data['entry_no'], $datetime, $datetime);

        return $this->db->insert($sql, $params);
    }

    public function getAICodeRate($hscode, $hscode_tar, $tarspec)
    {
        $sql    = "SELECT Rate FROM CWSAICODE WHERE HSCode = ? AND HSCODE_TAR = ? AND TarSpec = ?";
        $result = $this->db->select($sql, [$hscode, $hscode_tar, $tarspec]);

        return isset($result[0]['Rate']) ? $result[0]['Rate'] : 0;
    }

    public function getHsCodeRulCod($hscode, $hscode_tar)
    {
        $hs6_cod = substr($hscode, 0, 6);
        $tar_pr1 = substr($hscode, 6, 8);

        $sql = "SELECT rul_cod FROM GBTARTAB WHERE hs6_cod = ? AND tar_pr1 = ? AND tar_pr2 = ?";

        $result = $this->db->select($sql, [$hs6_cod, $tar_pr1, $hscode_tar]);

        return isset($result[0]['rul_cod']) ? $result[0]['rul_cod'] : '';
    }

    public function generateTSForRegNo($regNo)
    {
        // Check if qualified (8PP-8 and AP)
        $check = $this->db->select(
            "SELECT RegNo 
            FROM TBLIMPAPL_MASTER 
            WHERE RegNo = ? AND Mdec = 8PP AND Mdec2 = 8 AND Stat = 'AP'",
            [$regNo]
        );

        if (empty($check)) {
            return false;
        }

        $tsRegistry = 'TS' . $regNo;

        // Prevent duplicate TS
        $existing = $this->db->select(
            "SELECT registry 
            FROM bol_manifest 
            WHERE registry = ?",
            [$tsRegistry]
        );

        if (!empty($existing)) {
            return false;
        }

        // Get original BOL records
        $original = $this->db->select(
            "SELECT * 
            FROM bol_manifest 
            WHERE registry = ?",
            [$regNo]
        );

        if (empty($original)) {
            return false;
        }

        // Insert TS records
        foreach ($original as $row) {

            $insertSql = "
                INSERT INTO bol_manifest (
                    registry,
                    port,
                    blno,
                    bl_nature,
                    pl_destination,
                    package_no,
                    package_type,
                    gross_weight,
                    created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $tsRegistry, // NEW TS registry
                "",
                $row['blno'],
                $row['bl_nature'],
                $row['pl_destination'],
                $row['package_no'],
                $row['package_type'],
                $row['gross_weight'],
                'system-ts'
            ];

            $this->db->insert($insertSql, $params);
        }

        return true;
    }

    public function getAllBOLRows()
    {
        return $this->db->select('*')->from('bol_manifest')->get()->result_array();
    }

    public function getBOLData($registry)
    {
        $sql = "SELECT * FROM bol_manifest WHERE registry = ?";
        $result = $this->db->select($sql, array($registry));

        if (!empty($result)) {
            return $result[0];
        }

        return null;
    }

    public function insertTSRow($data)
    {
        return $this->db->insert('bol_manifest', $data);
    }

    public function updateTSRow($registry, $data)
    {
        return $this->db->where('registry', $registry)->update('bol_manifest', $data);
    }

    public function uncmptabExists($contin)
    {
        $sql = "SELECT 1 FROM uncmptab WHERE CONTIN = ?";
        $result = $this->db->select($sql, [$contin]);

        return !empty($result);
    }
}