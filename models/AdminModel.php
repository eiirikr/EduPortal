<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */
require_once __DIR__ . '/../core/DatabaseINSCUSADMIN.php';
require_once __DIR__ . '/../core/Database.php';

require_once __DIR__ . '/../../../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../../phpmailer/SMTP.php';
require_once __DIR__ . '/../../../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminModel extends DatabaseINSCUSADMIN
{
    private $db;
    private $db_main;

    public function __construct()
    {
        $this->db      = new DatabaseINSCUSADMIN;
        $this->db_main = new Database;
    }
    // Added School & AccountType, updated parameter count, show all role
    public function uploadedUsersData($searchTerm = '')
    {
        $params = array();

        $sql = "SELECT TOP(50) 
                UserID, 
                StudentNo, 
                AIFirstName, 
                AILastName, 
                AIEmail, 
                AIAddrs, 
                cltcode,
                School,
                AccountType,
                CASE 
                    WHEN DateCreated < DATEADD(MONTH, -5, GETDATE()) THEN 'Expired'
                    ELSE 'Active'
                END AS Status
            FROM Registration
            WHERE Role IN (1, 2, 3)
              AND cltcode IS NOT NULL
              AND cltcode != ''";

        if (!empty($searchTerm)) {
            $sql    .= " AND (
                    AIFirstName LIKE ?
                    OR AILastName LIKE ?
                    OR AIEmail LIKE ?
                    OR AIAddrs LIKE ?
                    OR cltcode LIKE ?
                    OR StudentNo LIKE ?
                    OR School LIKE ? 
                    OR AccountType LIKE ?
                    OR 
                    CASE 
                        WHEN DateCreated < DATEADD(MONTH, -5, GETDATE()) THEN 'Expired'
                        ELSE 'Active'
                    END LIKE ?
                 )";
            $term   = '%' . $searchTerm . '%';
            $params = array_fill(0, 8, $term);  
        }

        $sql .= " ORDER BY DateCreated DESC";

        return $this->db->select($sql, $params);
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
    // Added School in saveUploadedUserRows
    public function saveUploadedUserRows($rows)
    {
        $database_name = "PL-INSCUSTSTDB";
        $results = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            list($StudentNo, $first_name, $last_name, $address, $mobile, $email, $year, $section, $school, $account_type) = $row;

            $StudentNo = trim((string)$StudentNo);
            $mobile    = preg_replace('/[^0-9]/', '', (string)$mobile);

            $cltcode = $this->generateCltCode();

            if ($this->isExistingUser($email, $cltcode)) {
                $results[] = [
                    'row' => $rowNumber,
                    'status' => 'skipped',
                    'message' => 'Email or SR Code already exists'
                ];
                continue;
            }

            $default_password = $this->generateRandomString();
            $encrypted_password = $this->encryptPassword($default_password);

            // Added logic to determine role based on account type
            $account_type_lower = preg_replace('/\s+/', ' ', strtolower(trim($account_type)));

            switch ($account_type_lower) {
                case 'super admin':
                    $role = 3;
                    break;
                case 'faculty':
                    $role = 2;
                    break;
                case 'student':
                    $role = 1;
                    break;
                default:
                    // Skip if invalid account type
                    $results[] = [
                        'row' => $rowNumber,
                        'status' => 'skipped',
                        'message' => "Invalid Account Type: {$account_type}."
                    ];
                    continue 2;
            }

            $registered = $this->insertRegistration(
                $email,
                $encrypted_password,
                $database_name,
                $first_name,
                $last_name,
                $address,
                $cltcode,
                $StudentNo,
                $mobile,
                $year,
                $section,
                $school,
                $account_type,
                $role
            );

            if ($registered) {
                $tin = $this->generateNewBrokerTin();
                $this->insertBrokerTable($tin, $first_name, $last_name, $address, $cltcode);
                $this->insertCwsBroker($tin, $first_name, $last_name, $address, $cltcode, $email, $mobile);

                $this->sendConfirmationEmail($email, $first_name, $last_name, $default_password);

                $results[] = ['row' => $rowNumber, 'status' => 'success'];
            } else {
                $results[] = ['row' => $rowNumber, 'status' => 'error', 'message' => 'Insert failed'];
            }
        }

        return $results;
    }

    function encryptPassword($strPassword)
    {
        $strPassword = strtoupper(trim($strPassword));
        $result      = "";

        for ($i = 0; $i < strlen($strPassword); $i++) {
            $char   = $strPassword[$i];
            $ascii  = ord($char);
            $result .= chr($ascii + 100);
        }

        return $result;
    }

    function decryptPassword($strPassword)
    {
        $strPassword = strtoupper(trim($strPassword));
        $result      = '';

        $length = strlen($strPassword);
        for ($i = 0; $i < $length; $i++) {
            $ascii  = ord($strPassword[$i]);
            $result .= chr($ascii - 100);
        }

        return $result;
    }

    private function isExistingUser($email, $cltcode)
    {
        $sql = "SELECT AIEmail FROM Registration WHERE AIEmail = ? OR cltcode = ?";
        $params = array($email, $cltcode);
        $exists = $this->db->select($sql, $params);
        return !empty($exists);
    }
    // Added School and AccountType in inserRegistration
    private function insertRegistration($email, $encrypted_password, $db_name, $fname, $lname, $addr, $cltcode, $StudentNo, $mobile, $year, $section, $school, $account_type, $role)
    {
        $sql = "INSERT INTO Registration (
                UserID, [Password], DatabaseName, AIFirstName, AILastName, AIAddrs, AIEmail, cltcode, StudentNo, MobileNO, Section, [Year], School, AccountType,
                AICompany, AIDesignation,
                Role, isPasswordTemp, [Status], ImpDecRights, Environment, AdminRights, ImpPermit, BOI_CAI, IC_DA,
                AFAB, OLRS, ExpDecRights, InvoiceRights, BillingRights, ImporterRights, EpayRights, Emanifest, ProdFLAG
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Combine fname and lname for AICompany
        $ai_company = trim($fname . ' ' . $lname);
        $prod_flag = 1;

        $params = array(
            $email, $encrypted_password, $db_name,              
            $fname, $lname, $addr, $email, $cltcode, $StudentNo, $mobile,   
            $section, $year, $school, $account_type,                          
            $ai_company, 'BROKER',                              
            $role, 1, 1, 1, 1, 0, 0, 0, 0,                          
            0, 0, 0, 0, 0, 0, 0, 0, 1                           
        );

        return $this->db->insert($sql, $params);
    }

    private function generateNewBrokerTin()
    {
        $sql = "SELECT TOP 1 BROKERTIN FROM undectab 
            WHERE ISNUMERIC(BROKERTIN) = 1 
            ORDER BY Id DESC";

        $result = $this->db_main->select($sql);

        if (!empty($result)) {
            $lastTin = preg_replace('/\D/', '', $result[0]['BROKERTIN']); // Remove non-digits
            $nextTin = bcadd($lastTin, '1');
        } else {
            $nextTin = '1'; // First TIN
        }

        return str_pad($nextTin, 12, '0', STR_PAD_LEFT);
    }

    private function insertBrokerTable($tin, $fname, $lname, $address, $cltcode)
    {
        $addr = strtoupper(trim($address));

        // Safe substr function for PHP 5.6
        $part1 = substr($addr, 0, 35);
        $part2 = substr($addr, 35, 35);
        $part3 = substr($addr, 70, 35);

        $brokeradd1 = ($part1 === false || trim($part1) === '') ? '' : trim($part1);
        $brokeradd2 = ($part2 === false || trim($part2) === '') ? '' : trim($part2);
        $brokeradd3 = ($part3 === false || trim($part3) === '') ? '' : trim($part3);

        $params = array(
            $tin,
            strtoupper(trim($fname . ' ' . $lname)),
            $brokeradd1,
            $brokeradd2,
            $brokeradd3,
            'PHILIPPINES',
            '', // BROKERTEL
            '', // BROKERFAX
            strtoupper($cltcode)
        );

        $sql = "INSERT INTO undectab (
            BROKERTIN, BROKERNAME, BROKERADD1, BROKERADD2, BROKERADD3,
            COUNTRY, BROKERTEL, BROKERFAX, BROKERPAD4
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db_main->insert($sql, $params);
    }

    private function insertCwsBroker($tin, $fname, $lname, $address, $cltcode, $email, $mobile)
    {
        $addr = strtoupper(trim($address));

        // Safely extract address parts
        $part1 = substr($addr, 0, 35);
        $part2 = substr($addr, 35, 35);
        $part3 = substr($addr, 70, 35);

        $brokeradd1 = ($part1 === false || trim($part1) === '') ? '' : trim($part1);
        $brokeradd2 = ($part2 === false || trim($part2) === '') ? '' : trim($part2);
        $brokeradd3 = ($part3 === false || trim($part3) === '') ? '' : trim($part3);

        $brk_code = $this->generateUniqueBRKCode();
        $broker_name = strtoupper(trim($fname . ' ' . $lname));
        $broker_contact = $broker_name;

        $params = array(
            $brk_code,
            $broker_name,
            $tin,
            $brokeradd1,
            $brokeradd2,
            $brokeradd3,
            '',       // BRK_adr4
            $tin,       // brk_duns
            $broker_contact,
            $email,
            '',       // BRK_FaxNo
            $mobile,
            $cltcode
        );

        $sql = "INSERT INTO CWSBROKER (
            BRK_code, BRK_name, BRK_tin, BRK_adr1, BRK_adr2, BRK_adr3, BRK_adr4, brk_duns,
            BRK_ContactName, BRK_eMail, BRK_FaxNo, BRK_PhoneNo, cltcode
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db_main->insert($sql, $params);
    }

    private function generateUniqueBRKCode()
    {
        $tries = 0;
        do {
            $length = mt_rand(3, 4);
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            $checkSql = "SELECT COUNT(*) AS total FROM CWSBROKER WHERE BRK_code = ?";
            $exists = $this->db_main->select($checkSql, array($code));
            $exists = isset($exists[0]['total']) ? $exists[0]['total'] : 0;

            $tries++;
        } while ($exists > 0 && $tries < 10);

        return $code;
    }

    function generateRandomString($length = 12) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function sendConfirmationEmail($email, $fname, $lname, $password)
    {
        try {
            $mail = $this->setupMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Registration Confirmation';

            // Inject template variables
            $first_name = $fname;
            $last_name = $lname;
            $default_password = $password;

            ob_start();
            include __DIR__ . '/../partials/email_templates/registration_confirmation.php';
            $mail->Body = ob_get_clean();

            if (!$mail->send()) {
                error_log('Mailer Error: ' . $mail->ErrorInfo);
                return $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            error_log('Exception while sending mail: ' . $e->getMessage());
            return $e->getMessage();
        }

        return true;
    }

    private function generateCltCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 8;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            $sql = "SELECT COUNT(*) AS total FROM Registration WHERE cltcode = ?";
            $result = $this->db->select($sql, array($code));

            $exists = isset($result[0]['total']) ? $result[0]['total'] : 0;

        } while ($exists > 0);

        return $code;
    }
}