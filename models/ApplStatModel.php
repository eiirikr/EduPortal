<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once __DIR__ . '/../core/DatabaseAPPLSTAT.php';

class ApplStatModel extends DatabaseAPPLSTAT
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseAPPLSTAT;
    }

    public function insertApplStat($applno)
    {
        $statuses = array(
            array(
                'status' => 'Registered',
                'msg'    => '',
            ),
            array(
                'status' => 'Selected',
                'msg'    => 'CSAD status changed from Selected to Selected, by operation Update SAD. See document attached',
            ),
            array(
                'status' => 'Assessed',
                'msg'    => 'CSAD status changed from Selected to Assessed, by operation . See document attached',
            ),
            array(
                'status' => 'Paid',
                'msg'    => 'CSAD status changed from Assessed to Paid, by operation Validate and assess. See document attached',
            )
        );

        $this->deleteData($applno, 'TBLAPPLSTAT');

        foreach ($statuses as $entry) {
            $now = new \DateTime();
            $now->setTimezone(new \DateTimeZone('Asia/Manila'));
            $datetime = $now->format('d.M.Y H:i:s');

            $sql      = "INSERT INTO TBLAPPLSTAT (APPLNO, APPLSTAT, APPLMSG, APPLDATE) VALUES (?, ?, ?, ?)";
            $params   = array($applno, $entry['status'], $entry['msg'], $datetime);
            $this->db->insert($sql, $params);

            sleep(1); // delay 1 second before next insert
        }

        return true;
    }

    public function deleteData($applno, $table)
    {
        $sql = "DELETE FROM $table WHERE ApplNo = '$applno'";
        return $this->db->delete($sql);
    }

}