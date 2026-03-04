<?php

/**
 * Monthly Report
 * Author: Cielo Mae Suico
 */

date_default_timezone_set('Asia/Manila');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

require_once(__DIR__ . '/PHPExcel/Classes/PHPExcel.php');
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';
require_once __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EduPortalMonthlyReport
{
    private $conn_edu;
    private $conn_pl;

    public function __construct()
    {
        $server   = "192.168.1.81";
        $username = "sa";
        $password = "df0rc3";

        $this->conn_edu = sqlsrv_connect($server, [
            "Database" => "EDU-INSCUSTSTDB",
            "UID" => $username,
            "PWD" => $password,
            "CharacterSet" => "UTF-8"
        ]);

        $this->conn_pl = sqlsrv_connect($server, [
            "Database" => "PL-INSCUSADMIN",
            "UID" => $username,
            "PWD" => $password,
            "CharacterSet" => "UTF-8"
        ]);

        if (!$this->conn_edu) {
            throw new Exception("Connection failed (EDU-INSCUSTSTDB): " . print_r(sqlsrv_errors(), true));
        }
        if (!$this->conn_pl) {
            throw new Exception("Connection failed (PL-INSCUSADMIN): " . print_r(sqlsrv_errors(), true));
        }
    }

    private function setupMailer()
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host     = '192.168.1.80';
        $mail->SMTPAuth = true;
        $mail->Username = 'insmail2@intercommerce.com.ph';
        $mail->Password = getenv('SMTP_PASSWORD') ?: '2022Ins2022$$1nt3Rc0mm3rc3';
        $mail->Port     = 25;
        $mail->setFrom('insmail2@intercommerce.com.ph', 'Intercommerce');
        $mail->isHTML(true);
        return $mail;
    }

    public function generateExcelReport($month, $year)
    {
        $dateFrom = date('Y-m-01 00:00:00', strtotime("$year-$month-01"));
        $dateTo   = date('Y-m-t 23:59:59', strtotime("$year-$month-01"));
        $monthName = date('F Y', strtotime($dateFrom));

        $sql = "
            SELECT 
                M.sentdate AS DateSent,
                M.ApplNo,
                R.REGREF + '-' + R.REGNO AS EntryNumber,
                M.MDec + '-' + M.Mdec2 AS ModeOfDeclaration,
                M.ConTIN,
                (
                    SELECT TOP (1) Exp_name
                    FROM CWSEXPORTER
                    WHERE Exp_TIN = M.ConTIN
                    ORDER BY Id DESC
                ) AS Consignee,
                M.OffClear AS PortOfClearance,
                D.AirBill AS BL_ABL,
                M.Stat AS Status,
                M.DecTin AS BrokerTIN,
                M.Brokername AS BrokerName,
                F.CustomVal AS Value,
                F.FreightCost AS Freight,
                F.WharCost AS Wharfage,
                F.InsCost AS Insurance,
                F.ArrasCost AS Arrastre,
                F.OtherCost
            FROM TBLIMPAPL_MASTER M
            LEFT JOIN TBLIMPAPL_FIN F ON M.ApplNo = F.ApplNo
            LEFT JOIN TBLIMPAPL_DETAIL D ON M.ApplNo = D.ApplNo
            LEFT JOIN TBLRESP_HEAD R ON M.ApplNo = R.APPLNO
            WHERE 
                D.ItemNo = '1'
                AND M.Stat IN ('ER', 'AS', 'AG', 'AP')
                AND M.sentdate BETWEEN ? AND ?
            ORDER BY DateSent ASC
        ";

        $params = [$dateFrom, $dateTo];
        $stmt = sqlsrv_query($this->conn_edu, $sql, $params);
        if ($stmt === false) {
            throw new Exception("Query error: " . print_r(sqlsrv_errors(), true));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }

        if (empty($data)) {
            throw new Exception("No transactions found for $monthName");
        }

        $excel = new PHPExcel();
        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle('Monthly Report');

        $headers = array_keys($data[0]);
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D3D3D3');
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $rowNum = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                if ($cell instanceof DateTime) {
                    $cell = $cell->format('Y-m-d H:i:s');
                }
                $sheet->setCellValue($col . $rowNum, $cell);
                $col++;
            }
            $rowNum++;
        }

        $backupDir = __DIR__ . '/backup';
        if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

        // Format date range for excel filename
        $firstDay = date('F_j', strtotime('first day of ' . $monthName));  // e.g. October_1
        $lastDay  = date('F_j', strtotime('last day of ' . $monthName));   // e.g. October_31
        $yearFull = date('Y', strtotime($monthName));                      // e.g. 2025
        $fileName = "{$firstDay}_to_{$lastDay}_{$yearFull}";

        // $pathMain = $backupDir . "/EDUPORTAL_Monthly_Report_{$year}_{$month}.xls";
        // $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        // $writer->save($pathMain);

        $pathMain = $backupDir . "/EDUPORTAL_Monthly_Report_{$fileName}.xls";
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save($pathMain);

        $countSql = "
            SELECT 
                M.Stat AS STATUS,
                COUNT(*) AS TOTAL_COUNT
            FROM TBLIMPAPL_MASTER M
            LEFT JOIN TBLIMPAPL_DETAIL D ON M.ApplNo = D.ApplNo
            WHERE 
                D.ItemNo = '1'
                AND M.Stat IN ('ER', 'AS', 'AG', 'AP')
                AND M.sentdate BETWEEN ? AND ?
            GROUP BY M.Stat
        ";

        $stmtCount = sqlsrv_query($this->conn_edu, $countSql, $params);
        if ($stmtCount === false) {
            throw new Exception("Count query error: " . print_r(sqlsrv_errors(), true));
        }

        $countData = [];
        while ($row = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC)) {
            $countData[] = $row;
        }

        $excelCount = new PHPExcel();
        $sheetCount = $excelCount->setActiveSheetIndex(0);
        $sheetCount->setTitle('Total Count');

        $sheetCount->setCellValue('A1', 'STATUS');
        $sheetCount->setCellValue('B1', 'TOTAL_COUNT');
        $sheetCount->getStyle('A1:B1')->getFont()->setBold(true);
        $sheetCount->getStyle('A1:B1')->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D3D3D3');

        $row = 2;
        foreach ($countData as $r) {
            $sheetCount->setCellValue("A{$row}", $r['STATUS']);
            $sheetCount->setCellValue("B{$row}", $r['TOTAL_COUNT']);
            $row++;
        }

        // $pathCount = $backupDir . "/EDUPORTAL_Counted_Report_{$year}_{$month}.xls";
        // $writer2 = PHPExcel_IOFactory::createWriter($excelCount, 'Excel5');
        // $writer2->save($pathCount);

        $pathCount = $backupDir . "/EDUPORTAL_Counted_Report_{$fileName}.xls";
        $writer2 = PHPExcel_IOFactory::createWriter($excelCount, 'Excel5');
        $writer2->save($pathCount);

        sqlsrv_close($this->conn_edu);
        sqlsrv_close($this->conn_pl);

        return [
            'path_main' => $pathMain,
            'path_count' => $pathCount
        ];
    }

    public function sendReportEmail($reportInfo)
    {
        try {
            $mail = $this->setupMailer();

            $recipients = [
                'marketing@intercommerce.com.ph',
            ];

            foreach ($recipients as $email) {
                $mail->addAddress($email);
            }

            $firstDayLastMonth = date('F 1, Y', strtotime('first day of last month'));
            $lastDayLastMonth  = date('F t, Y', strtotime('last day of last month'));
            $monthLabel = "{$firstDayLastMonth} to {$lastDayLastMonth}";

            $mail->Subject = "WebCWS EduPortal - Monthly Report ({$monthLabel})";

            $body = "
                <p>Good day,</p>
                <p>Please find the attached transaction report for the subject above.</p>
            ";

            $mail->Body = $body;

            $mail->addAttachment($reportInfo['path_main']);
            $mail->addAttachment($reportInfo['path_count']);

            if (!$mail->send()) {
                echo "Email failed: " . $mail->ErrorInfo . "\n";
                return false;
            }
            return true;

        } catch (Exception $e) {
            echo "Mail Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
