<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once __DIR__ . '/../core/DatabaseE2MData.php';

class E2MDataModel extends DatabaseE2MData
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseE2MData;
    }

    public function insertSSDT($applno, $data)
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Asia/Manila'));
        $datetime = $now->format('Y-m-d H:i:s');
        $year     = $now->format('Y');

        $master          = $data['master'];
        $entry_details   = $data['entry_details'];
        $total_assesment = $data['total_assesment'];

        $this->deleteData($applno, 'tblSSDT');

        $sql    = "INSERT INTO tblSSDT (
                    ApplNo, CustomsOfficeCode, TypeOfDeclaration, DeclarationGeneralProcedure, SADRegistrationYear,
                    SADRegistrationSerial, SADRegistrationNumber, SADRegistrationDate, DeclarantReferenceYear,
                     SADAssessmentYear, SADAssessmentSerial, SADAssessmentNumber, SADAssessmentDate,DeclarantTIN,
                     DeclarantName, DeclarantAddress1, DeclarantAddress2, DeclarantAddress3, ImporterTIN,
                     ImporterName, ImporterAddress1, ImporterAddress2, ImporterAddress3, ModeOfPayment,
                     TotalAssessedAmount, Amount, CollectionDate
                ) VALUES (
                          ?, ?, ? , ?, ?,
                          ?, ?, ?, ?,
                          ?, ?, ?, ?, ?,
                          ?, ?, ?, ?, ?,
                          ?, ?, ?, ?, ?,
                          ?, ?, ?
                )";
        $params = array(
            $applno,
            $master['OffClear'],
            $master['MDec'],
            $master['Mdec2'],
            $year,
            $entry_details['entry_type'],
            $entry_details['entry_no'],
            $datetime,
            $year,
            $year,
            'L',
            $entry_details['entry_no'],
            $datetime,
            $master['DecTin'],
            $master['Brokername'],
            $master['BrokAddr1'],
            $master['BrokAddr2'],
            $master['BrokAddr3'],
            $master['ConTIN'],
            $master['ConName'],
            $master['ConAddr1'],
            $master['ConAddr2'],
            $master['ConAddr3'],
            'CASH',
            $total_assesment,
            $total_assesment,
            $datetime
        );

        return $this->db->insert($sql, $params);
    }

    public function deleteData($applno, $table)
    {
        $sql = "DELETE FROM $table WHERE ApplNo = '$applno'";
        return $this->db->delete($sql);
    }
}