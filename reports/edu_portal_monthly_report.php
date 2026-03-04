<?php
require_once(__DIR__ . '/EduPortalMonthlyReport.php');

try {
    $report = new EduPortalMonthlyReport();

    $previousMonth = date('m', strtotime('first day of last month'));
    $previousYear  = date('Y', strtotime('first day of last month'));
    $monthName     = date('F Y', mktime(0, 0, 0, $previousMonth, 1, $previousYear));

    $reportInfo = $report->generateExcelReport($previousMonth, $previousYear);
    $sent = $report->sendReportEmail($reportInfo);

} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    exit(1);
}
