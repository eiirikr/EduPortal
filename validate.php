<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

require_once 'controllers/MainController.php';

$controller = new MainController;

$applno = isset($_GET['applno']) ? $_GET['applno'] : '';
$tin    = isset($_GET['tin']) ? $_GET['tin'] : '';

// check if applno exist
$exists = $controller->checkApplication($applno);

if (!$exists || !$applno) {
    echo '<h2 style="color:red;"> Please provide applno and tin </h2>';
    exit;
}

// get application data
$data = $controller->getApplicationData($applno);

if (empty($data)) {
    echo '<h2 style="color:red;"> Not Found </h2>';
    exit;
}

// validate data base on database validations
$response = $controller->validateFields($data);

// empty response indicates success application
if (empty($response)) {
    // update master status
    $controller->updateMasterStatus($applno, 'AP');
    // insert resp head data
    $entry_details = $controller->insertRespHead($applno, $data, 'AP');

    $data['entry_details'] = $entry_details;

    // add data to tblapplstat
    $controller->insertApplStat($applno);
    // insert gbtanfan
    $controller->insertTANFAN($applno, $entry_details);

    // compute taxes
    $taxes = $controller->getTaxes($data);
    $total_assessment = 0;

    if (!empty($taxes['gt_taxes'])) {
        $controller->insertGTTaxes($applno,$taxes['gt_taxes']);

        // compute for total assessment
        foreach ($taxes['gt_taxes'] as $tax) {
            $total_assessment += $tax;
        }
    }

    if (!empty($taxes['it_taxes'])) {
        $controller->insertITTaxes($applno,$taxes['it_taxes']);
    }

    // insert e2mdata ssdt
    $data['total_assesment'] = $total_assessment;
    $controller->insertSSDT($applno, $data);
} else {
    // update master status
    $controller->updateMasterStatus($applno, 'ER');
    $controller->insertError($applno, $response);
}

echo '<script type="text/javascript">';

if (empty($response)) {
    echo 'alert("Application # ' . $applno . ' has been sent for E2M processing.Please check the Response from time to time.");';
}

if (isset($_GET['return_url'])) {
    $url = $controller->DecryptValue($_GET['return_url']);
    echo 'window.location.href = "' . $url . '"';
} else {
    echo 'window.location.href = "https://student.intercommerce.com.ph//WebCWS/cws_impdec.asp"';
}

echo '</script>';
