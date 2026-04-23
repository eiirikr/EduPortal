<?php
/*
Document History:
Spagara: 04/13/2023 - Update for AICode under HSCode 25232990 and 25239000
*/

ini_set('max_execution_time', 180);
ini_set('memory_limit', '255M');

// start code nolie
function SupVal($HScode_new,$HScode_new_TARPR1,$HSCODE_TAR){
$table_name = 'dbo.GBTARTAB';
  $serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

  if($conn_INSCUSTSTDB) {
  }else{
    echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
  }

  $sql_oum = "SELECT uom_cod1 FROM $table_name WHERE (hs6_cod = '$HScode_new') AND (tar_pr1 = '$HScode_new_TARPR1') AND (tar_pr2 = '$HSCODE_TAR')";

  $stmt_data_oum = sqlsrv_query($conn_INSCUSTSTDB, $sql_oum);
		if($stmt_data_oum == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows_oum = sqlsrv_fetch_array( $stmt_data_oum, SQLSRV_FETCH_ASSOC))
			{
				$oum = $rows_oum;
			}
		}
	return $oum;

}

function get_CONs($ApplNo,$ItemNo){
$table_name = 'dbo.TBLIMPAPL_CONS';
  $serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

  if($conn_INSCUSTSTDB) {
  }else{
    //echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
  }

  $sql_oum = "SELECT SupVal1 FROM $table_name WHERE (ApplNo = '$ApplNo') AND (ItemNo = '$ItemNo')";

  $stmt_data_oum = sqlsrv_query($conn_INSCUSTSTDB, $sql_oum);
		if($stmt_data_oum == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows_oum = sqlsrv_fetch_array( $stmt_data_oum, SQLSRV_FETCH_ASSOC))
			{
				$supVAL1 = $rows_oum;
			}
		}
	return $supVAL1;

}
// end code nolie
function InsertFLAG($applno){
	$serverName = '192.168.1.81,1477'; //'192.168.5.54 '; //serverName\instanceName, portNumber (default is 1433)
	$connectionInfo = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

	if($conn) {
	}else{
		//echo "Connection could not be established.<br />";
		die( print_r( sqlsrv_errors(), true));
	}
  
	$update_print = "UPDATE dbo.TBLIMPAPL_MASTER SET MAVICFrom = '1' WHERE ApplNo= '$applno'";
	$stmt_print = sqlsrv_prepare($conn, $update_print);
	if($stmt_print == false) {
		die( print_r( sqlsrv_errors(), true));
	}

	if( sqlsrv_execute($stmt_print) == false ) {
		  die( print_r( sqlsrv_errors(), true));
	}else{

	}
}

error_reporting(0);
$applno = $_GET['ApplNo'];

$applnos = explode(';',str_replace(' ', '%', $_GET['ApplNo']));

InsertFLAG($applno);

$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

$data = array();

foreach ($applnos as $key => $applno) {
	if(!$conn_INSCUSTSTDB) {
		echo "Connection could not be established.<br />";
		die( print_r( sqlsrv_errors(), true));
	}else{

		$update_print = "UPDATE TBLIMPAPL_MASTER Set Environment='P' WHERE ApplNo= '$applno'";
		$stmt_print = sqlsrv_prepare($conn_INSCUSTSTDB, $update_print);
		if($stmt_print == false) {
			die( print_r( sqlsrv_errors(), true));
		}

		if( sqlsrv_execute($stmt_print) == false ) {
			  die( print_r( sqlsrv_errors(), true));
		}else{

		} 

		/* START FIN DATA */
		$searchkey = $applno;
		
		$CSWEXP = "SELECT *
					FROM CWSEXPORTER
					WHERE Exp_code =  '" . substr($searchkey, 0, 4) . "'";

		$CSWEXP_data = sqlsrv_query($conn_INSCUSTSTDB, $CSWEXP);
		if($CSWEXP_data == false)
		{
			$CSWEXP_rows = "";
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			$CSWEXP_rows = sqlsrv_fetch_array( $CSWEXP_data, SQLSRV_FETCH_ASSOC);
		}

		if (!empty($CSWEXP_rows)) {
			$condition = substr($searchkey, 0, 4);
		}else{
			$condition = substr($searchkey, 0, 3);
		}

		//TBLIMPAPL_MASTER.IntRef AS INT_REF,
		$FIN_DATA = "SELECT TBLIMPAPL_MASTER.APPLNO AS APPLNO, TBLIMPAPL_MASTER.*,CWSEXPORTER.*,TBLIMPAPL_DETAIL.*,TBLIMPAPL_FIN.*,GBCTYTAB.*,GBBNKTAB.*,GBTOPTAB.*,TBLIMPAPL_MASTER_EXT.UConName,TBLIMPAPL_MASTER_EXT.UConAddr1,TBLIMPAPL_MASTER_EXT.UConAddr2,TBLIMPAPL_MASTER_EXT.UConAddr3
			FROM TBLIMPAPL_MASTER  
			LEFT JOIN CWSEXPORTER ON '$condition' = CWSEXPORTER.Exp_CODE 
			LEFT JOIN GBCTYTAB ON TBLIMPAPL_MASTER.Cexp = GBCTYTAB.cty_cod 
			LEFT JOIN TBLIMPAPL_DETAIL ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_DETAIL.ApplNo 
			LEFT JOIN TBLIMPAPL_FIN ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_FIN.ApplNo 
			LEFT JOIN GBBNKTAB ON TBLIMPAPL_FIN.BankCode = GBBNKTAB.bnk_cod
			LEFT JOIN GBTOPTAB ON TBLIMPAPL_FIN.Tpayment = GBTOPTAB.top_cod
			LEFT JOIN TBLIMPAPL_MASTER_EXT ON TBLIMPAPL_MASTER.APPLNO = TBLIMPAPL_MASTER_EXT.APPLNO
			WHERE TBLIMPAPL_MASTER.APPLNO =  '$searchkey' and CWSEXPORTER.EXP_code='$condition' AND ItemNo = '1'";
		//print_r ($FIN_DATA); die();
		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $FIN_DATA);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data['FIN_data'] = $rows;
			}
		}

		if (!isset($data['FIN_data'])) {
			print_r('No data found');
			die();
		}
		$currency = $data['FIN_data']['CustCurr'];
		$fcurrency = $data['FIN_data']['FreightCurr'];
		$icurrency = $data['FIN_data']['InsCurr'];
		$ocurrency = $data['FIN_data']['OtherCurr'];

		$ExchRate_query = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = '$currency') ORDER BY EEA_DOV DESC";
		$ExchRate_data = sqlsrv_query($conn_INSCUSTSTDB, $ExchRate_query);
		if($ExchRate_data == false){
			echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{

			if ($data['FIN_data']['Stat'] == 'C') {
				while($ExchRate_row = sqlsrv_fetch_array( $ExchRate_data, SQLSRV_FETCH_ASSOC))
				{

					$ExchRate = $ExchRate_row['RAT_EXC'];
				}
				$Exch_query = "UPDATE tblIMPAPL_Fin Set ExchRate='$ExchRate' WHERE ApplNo= '$searchkey'";
				$stmt_exch = sqlsrv_prepare($conn_INSCUSTSTDB, $Exch_query);
				if($stmt_exch == false) {
					die( print_r( sqlsrv_errors(), true));
				}

				if( sqlsrv_execute($stmt_exch) == false ) {
					  die( print_r( sqlsrv_errors(), true));
				}else{

				} 
			}
		}

		$ExchRate_query = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = '$fcurrency') ORDER BY EEA_DOV DESC";
		$ExchRate_data = sqlsrv_query($conn_INSCUSTSTDB, $ExchRate_query);
		if($ExchRate_data == false){
			echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			if ($data['FIN_data']['Stat'] == 'C') {
				while($ExchRate_row = sqlsrv_fetch_array( $ExchRate_data, SQLSRV_FETCH_ASSOC))
				{
					$ExchRate = $ExchRate_row['RAT_EXC'];
				}
				$Exch_query = "UPDATE tblIMPAPL_Fin Set FExchRate='$ExchRate' WHERE ApplNo= '$searchkey'";
				$stmt_exch = sqlsrv_prepare($conn_INSCUSTSTDB, $Exch_query);
				if($stmt_exch == false) {
					die( print_r( sqlsrv_errors(), true));
				}

				if( sqlsrv_execute($stmt_exch) == false ) {
					  die( print_r( sqlsrv_errors(), true));
				}else{

				} 
			}
		}

		$ExchRate_query = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = '$icurrency') ORDER BY EEA_DOV DESC";
		$ExchRate_data = sqlsrv_query($conn_INSCUSTSTDB, $ExchRate_query);
		if($ExchRate_data == false){
			echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			if ($data['FIN_data']['Stat'] == 'C') {
				while($ExchRate_row = sqlsrv_fetch_array( $ExchRate_data, SQLSRV_FETCH_ASSOC))
				{
					$ExchRate = $ExchRate_row['RAT_EXC'];
				}
				$Exch_query = "UPDATE tblIMPAPL_Fin Set IExchRate='$ExchRate' WHERE ApplNo= '$searchkey'";
				$stmt_exch = sqlsrv_prepare($conn_INSCUSTSTDB, $Exch_query);
				if($stmt_exch == false) {
					die( print_r( sqlsrv_errors(), true));
				}

				if( sqlsrv_execute($stmt_exch) == false ) {
					  die( print_r( sqlsrv_errors(), true));
				}else{

				} 
			}
		}

		$ExchRate_query = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = '$ocurrency') ORDER BY EEA_DOV DESC";
		$ExchRate_data = sqlsrv_query($conn_INSCUSTSTDB, $ExchRate_query);
		if($ExchRate_data == false){
			echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			if ($data['FIN_data']['Stat'] == 'C') {
				while($ExchRate_row = sqlsrv_fetch_array( $ExchRate_data, SQLSRV_FETCH_ASSOC))
				{
					$ExchRate = $ExchRate_row['RAT_EXC'];
				}
				$Exch_query = "UPDATE tblIMPAPL_Fin Set OExchRate='$ExchRate' WHERE ApplNo= '$searchkey'";
				$stmt_exch = sqlsrv_prepare($conn_INSCUSTSTDB, $Exch_query);
				if($stmt_exch == false) {
					die( print_r( sqlsrv_errors(), true));
				}

				if( sqlsrv_execute($stmt_exch) == false ) {
					  die( print_r( sqlsrv_errors(), true));
				}else{

				} 
			}
		}



		$co_code = $data['FIN_data']['COCode'];
		$params = array();
		$cocode = "SELECT cty_dsc FROM GBCTYTAB WHERE cty_cod= '$co_code'";
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$cocode_data = sqlsrv_query($conn_INSCUSTSTDB, $cocode, $params, $options);
		$cocode_rows = sqlsrv_num_rows($cocode_data);

		while($cocode_rows = sqlsrv_fetch_array( $cocode_data, SQLSRV_FETCH_ASSOC))
		{
			$data['CO_code'] = $cocode_rows;
		}

		/* End for cocode */


		$pref = "SELECT * FROM tblimpapl_detail WHERE ApplNo = '$searchkey' ORDER BY ItemNo";
		$pref_data = sqlsrv_query($conn_INSCUSTSTDB, $pref);
		if($pref_data == false){
				echo "Error in query preparation/execution. pref\n";
				die( print_r( sqlsrv_errors(), true));
		}else{
			while($pref_row = sqlsrv_fetch_array( $pref_data, SQLSRV_FETCH_ASSOC)){
				
				$strHSCode1 = substr($pref_row['HSCode'], 0, 6);
				$strHSCode2 = substr($pref_row['HSCode'], 6, 3);
				$strTARPR2 = $pref_row['HSCODE_TAR'];
				$PrefRate = $pref_row['Pref'];
				$ApplNo = $pref_row['ApplNo'];
				$ItemNo = $pref_row['ItemNo'];
				$PrefRate = $pref_row['Pref'];

				$rsHsR8 = "SELECT * FROM GBTARTAB where Hs6_cod='$strHSCode1' and tar_pr1='$strHSCode2' and tar_pr2='$strTARPR2'";
				$rsHsR8_data = sqlsrv_query($conn_INSCUSTSTDB, $rsHsR8);
				if($rsHsR8_data == false){
					echo "Error in query preparation/execution. pref\n";
					die( print_r( sqlsrv_errors(), true));
				}else{
					$rsHsR8_row = sqlsrv_fetch_array( $rsHsR8_data, SQLSRV_FETCH_ASSOC);
					
					$ApplNo = $pref_row['ApplNo'];
					$ItemNo = $pref_row['ItemNo'];
					$Tar_t01 = $rsHsR8_row['tar_t01'];
					$Tar_t02 = $rsHsR8_row['tar_t02'];
					$Tar_t03 = $rsHsR8_row['tar_t03'];
					$Tar_t04 = $rsHsR8_row['tar_t04'];
					$Tar_t05 = $rsHsR8_row['tar_t05'];
					$Tar_t06 = $rsHsR8_row['tar_t06'];
					$Tar_t07 = $rsHsR8_row['tar_t07'];
					$Tar_t08 = $rsHsR8_row['tar_t08'];
					$Tar_t09 = $rsHsR8_row['tar_t09'];
					$Tar_t10 = $rsHsR8_row['tar_t10'];
					$Tar_t11 = $rsHsR8_row['tar_t11'];
					$Tar_t12 = $rsHsR8_row['tar_t12'];
					$Tar_t13 = $rsHsR8_row['tar_t13'];
					$Tar_t14 = $rsHsR8_row['tar_t14'];
					$Tar_t15 = $rsHsR8_row['tar_t15'];
					$Tar_t16 = $rsHsR8_row['tar_t16'];	
					$Tar_t17 = $rsHsR8_row['tar_t17'];	
					$Tar_t18 = $rsHsR8_row['tar_t18'];	
					$Tar_t19 = $rsHsR8_row['tar_t19'];	
					$Tar_t20 = $rsHsR8_row['tar_t20'];
					$Tar_t21 = $rsHsR8_row['tar_t21'];
					

					if ($PrefRate == "" || $PrefRate == "NONE" || $PrefRate == "None" || $PrefRate == "none" || $PrefRate == null) {
						if($Tar_t01 == null){
							$Tar_t01 = '';
						}
						$update_query1_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t01' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query1_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query1_a);
						if( !$stmt_update_query1_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query1_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
						
						$update_query1_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t01' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query1_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query1_b);
						if( !$stmt_update_query1_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query1_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
						
						
						
					}
					
					if ($PrefRate == "AFTA" || $PrefRate == "ATIGA"){
						if($Tar_t02 == null){
							$Tar_t02 = '';
						}
						$update_query2_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t02' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query2_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query2_a);
						if( !$stmt_update_query2_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query2_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query2_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t02' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query2_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query2_b);
						if( !$stmt_update_query2_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query2_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AKFTA"){
						if($Tar_t03 == null){
							$Tar_t03 = '';
						}
						$update_query3_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t03' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query3_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query3_a);
						if( !$stmt_update_query3_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query3_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query3_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t03' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query3_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query3_b);
						if( !$stmt_update_query3_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query3_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "BOI"){
						if($Tar_t05 == null){
							$Tar_t05 = '';
						}
						$update_query4_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t05' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query4_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query4_a);
						if( !$stmt_update_query4_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query4_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query4_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t05' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query4_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query4_b);
						if( !$stmt_update_query4_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query4_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "BBCPT"){
						if($Tar_t05 == null){
							$Tar_t05 = '';
						}
						$update_query5_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t05' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query5_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query5_a);
						if( !$stmt_update_query5_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query5_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query5_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t05' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query5_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query5_b);
						if( !$stmt_update_query5_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query5_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "JPEPA"){
						if($Tar_t06 == null){
							$Tar_t06 = '';
						}
						$update_query6_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t06' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query6_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query6_a);
						if( !$stmt_update_query6_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query6_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query6_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t06' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query6_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query6_b);
						if( !$stmt_update_query6_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query6_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					//if ($PrefRate == "AFMA"){
					if ($PrefRate == "EFTA"){
						if($Tar_t07 == null){
							$Tar_t07 = '';
						}
						$update_query7_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t07' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query7_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query7_a);
						if( !$stmt_update_query7_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query7_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query7_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t07' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query7_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query7_b);
						if( !$stmt_update_query7_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query7_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AICO"){
						if($Tar_t08 == null){
							$Tar_t08 = '';
						}
						$update_query8_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t08' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query8_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query8_a);
						if( !$stmt_update_query8_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query8_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query8_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t08' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query8_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query8_b);
						if( !$stmt_update_query8_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query8_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					
					if ($PrefRate == "AICOB" || $PrefRate == "EFTANO"){
						if($Tar_t09 == null){
							$Tar_t09 = '';
						}
						$update_query9_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t09' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query9_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query9_a);
						if( !$stmt_update_query9_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query9_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query9_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t09' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query9_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query9_b);
						if( !$stmt_update_query9_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query9_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AICOC" || $PrefRate == "AIFTA"){
						if ($PrefRate == "AIFTA") {
							if($Tar_t10 == null){
								$Tar_t10 = $Tar_t01;
							}
						} else {
							if($Tar_t10 == null){
								$Tar_t10 = '';
							}
						}
						$update_query10_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t10' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query10_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query10_a);
						if( !$stmt_update_query10_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query10_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query10_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t10' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query10_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query10_b);
						if( !$stmt_update_query10_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query10_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AISP" || $PrefRate == "AJCEP"){
						if($Tar_t11 == null){
							$Tar_t11 = '';
						}
						$update_query11_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t11' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query11_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query11_a);
						if( !$stmt_update_query11_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query11_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query11_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t11' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query11_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query11_b);
						if( !$stmt_update_query11_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query11_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AICOD" || $PrefRate == "EFTACL"){
						if($Tar_t12 == null){
							$Tar_t12 = '';
						}
						$update_query12_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t12' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query12_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query12_a);
						if( !$stmt_update_query12_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query12_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query12_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t12' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query12_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query12_b);
						if( !$stmt_update_query12_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query12_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "ACFTA"){
						if($Tar_t13 == null){
							$Tar_t13 = '';
						}
						$update_query13_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t13' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query13_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query13_a);
						if( !$stmt_update_query13_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query13_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query13_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t13' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query13_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query13_b);
						if( !$stmt_update_query13_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query13_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AICOE" || $PrefRate == "ANFTA"){
						if($Tar_t14 == null){
							$Tar_t14 = '';
						}
						$update_query14_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t14' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query14_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query14_a);
						if( !$stmt_update_query14_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query14_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query14_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t14' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query14_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query14_b);
						if( !$stmt_update_query14_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query14_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
					
					if ($PrefRate == "AICOF"){
						if($Tar_t15 == null){
							$Tar_t15 = '';
						}
						$update_query15_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t15' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query15_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query15_a);
						if( !$stmt_update_query15_a ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query15_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query15_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t15' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query15_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query15_b);
						if( !$stmt_update_query15_b ) {
							die( print_r( sqlsrv_errors(), true));
						}
						
						if( sqlsrv_execute($stmt_update_query15_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					//Spagara: 06252023: additional preference
					if ($PrefRate == "RCEP"){
						if($Tar_t16 == null){
							$Tar_t16 = '';
						}
						$update_query16_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t16' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query16_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query16_a);
						if( !$stmt_update_query16_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query16_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query16_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t16' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query16_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query16_b);
						if( !$stmt_update_query16_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query16_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					if ($PrefRate == "RCEPAUNZ"){
						if($Tar_t17 == null){
							$Tar_t17 = '';
						}
						$update_query17_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t17' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query17_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query17_a);
						if( !$stmt_update_query17_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query17_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query17_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t17' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query17_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query17_b);
						if( !$stmt_update_query17_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query17_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					if ($PrefRate == "RCEPCN"){
						if($Tar_t18 == null){
							$Tar_t18 = '';
						}
						$update_query18_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t18' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query18_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query18_a);
						if( !$stmt_update_query18_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query18_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query18_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t18' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query18_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query18_b);
						if( !$stmt_update_query18_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query18_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					if ($PrefRate == "RCEPJP"){
						if($Tar_t19 == null){
							$Tar_t19 = '';
						}
						$update_query19_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t19' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query19_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query19_a);
						if( !$stmt_update_query19_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query19_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query19_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t19' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query19_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query19_b);
						if( !$stmt_update_query19_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query19_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					if ($PrefRate == "RCEPKR"){
						if($Tar_t20 == null){
							$Tar_t20 = '';
						}
						$update_query20_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t20' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query20_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query20_a);
						if( !$stmt_update_query20_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query20_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query20_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t20' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query20_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query20_b);
						if( !$stmt_update_query20_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query20_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}

					if ($PrefRate == "PHKRFTA" || $PrefRate == "PHKFTA"){
						if($Tar_t21 == null){
							$Tar_t21 = '';
						}
						$update_query21_a = "UPDATE TBLIMPAPL_DETAIL Set HsRate = '$Tar_t21' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query21_a = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query21_a);
						if( !$stmt_update_query21_a ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query21_a) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}

						$update_query21_b = "UPDATE tblIMPAPL_Cons Set HsRate = '$Tar_t21' WHERE ApplNo = '$ApplNo' AND ItemNo= '$ItemNo'";
						$stmt_update_query21_b = sqlsrv_prepare($conn_INSCUSTSTDB, $update_query21_b);
						if( !$stmt_update_query21_b ) {
							die( print_r( sqlsrv_errors(), true));
						}

						if( sqlsrv_execute($stmt_update_query21_b) == false ) {
							  die( print_r( sqlsrv_errors(), true));
						}
					}
				}
			}
		}
	}

}


/* FOR PREF END*/
require('./fpdf.php');

class PDF extends FPDF{
	private $CUD_C;
	private $VAT_C;
	private $IPF_C;


	public function check_if_isset($variable){
		if (!isset($variable)) {
			return null;
		}
	}
	// Load data
	public function Head($data){
		// Logo
		
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
		//if  preassement  august 8 2018 
		}
		else{
		$this->Image('boc.jpeg',183,10,25);
		$this->Image('e2m.jpg',5,10,15);
		}
		// Arial bold 15
		$this->SetFont('Arial','',8);
		// Move to the right
		$this->Cell(80);
		// Title

	}

	public function LoadData_RespHEAD($applno){
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$RespHEAD = "SELECT TBLRESP_HEAD.*
				   FROM TBLRESP_HEAD 
				   WHERE TBLRESP_HEAD.applno =  '$applno'";

		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $RespHEAD);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	public function LoadData_RespGT($applno){
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$RespGT = "SELECT TBLRESP_GT.*
				   FROM TBLRESP_GT 
				   WHERE TBLRESP_GT.applno =  '$applno'";

		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $RespGT);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	public function LoadData_RespIT($applno){
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$RespIT = "SELECT APPLNO, TAXCODE, TAXAMT, ITEMNO, CONVERT(smallint, ITEMNO) AS item_no
								  FROM TBLRESP_IT
								  WHERE APPLNO = '$applno'
								  ORDER BY item_no asc";

		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $RespIT);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	public function LoadData_FIN_multiple($applno){

		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

		$data = array();

		$searchkey = $applno;
		
		$CSWEXP = "SELECT *
					FROM CWSEXPORTER
					WHERE Exp_code =  '" . substr($searchkey, 0, 4) . "'";

		$CSWEXP_data = sqlsrv_query($conn_INSCUSTSTDB, $CSWEXP);
		if($CSWEXP_data == false)
		{
			$CSWEXP_rows = "";
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			$CSWEXP_rows = sqlsrv_fetch_array( $CSWEXP_data, SQLSRV_FETCH_ASSOC);
		}

		if (!empty($CSWEXP_rows)) {
			$condition = substr($searchkey, 0, 4);
		}else{
			$condition = substr($searchkey, 0, 3);
		}

		/* START FIN_data */

		$FIN_multi = "SELECT TBLIMPAPL_MASTER.APPLNO AS APPLNO,TBLIMPAPL_MASTER.*,CWSEXPORTER.*,TBLIMPAPL_DETAIL.*,TBLIMPAPL_FIN.*,GBPKGTAB.*,GBTARTAB.*
				FROM TBLIMPAPL_MASTER  
				LEFT JOIN CWSEXPORTER ON '$condition' = CWSEXPORTER.Exp_CODE 
				LEFT JOIN GBCTYTAB ON TBLIMPAPL_MASTER.Cexp = GBCTYTAB.cty_cod 
				LEFT JOIN TBLIMPAPL_DETAIL ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_DETAIL.ApplNo 
				LEFT JOIN TBLIMPAPL_FIN ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_FIN.ApplNo 
				LEFT JOIN GBPKGTAB ON TBLIMPAPL_DETAIL.PackCode = GBPKGTAB.pkg_cod 
				LEFT JOIN GBTARTAB ON TBLIMPAPL_DETAIL.HSCode = GBTARTAB.HS6_COD + GBTARTAB.TAR_PR1 AND TBLIMPAPL_DETAIL.HSCODE_TAR = GBTARTAB.TAR_PR2
				WHERE TBLIMPAPL_MASTER.APPLNO =  '$searchkey' and CWSEXPORTER.EXP_code='$condition' ORDER BY ItemNo";


		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $FIN_multi);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}


	public function LoadData($applno, $tin){
		
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

		$data = array();
		

		if(!$conn_INSCUSTSTDB) {
			echo "Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			/* START FIN DATA */
			$searchkey = $applno;
			
			$CSWEXP = "SELECT *
					FROM CWSEXPORTER
					WHERE Exp_code =  '" . substr($searchkey, 0, 4) . "'";

			$CSWEXP_data = sqlsrv_query($conn_INSCUSTSTDB, $CSWEXP);
			if($CSWEXP_data == false)
			{
				$CSWEXP_rows = "";
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				$CSWEXP_rows = sqlsrv_fetch_array( $CSWEXP_data, SQLSRV_FETCH_ASSOC);
			}

			if (!empty($CSWEXP_rows)) {
				$condition = substr($searchkey, 0, 4);
			}else{
				$condition = substr($searchkey, 0, 3);
			}

			$FIN_count = "SELECT TBLIMPAPL_MASTER.APPLNO AS APPLNO, TBLIMPAPL_MASTER.*,CWSEXPORTER.*,TBLIMPAPL_DETAIL.*,TBLIMPAPL_FIN.*,GBCTYTAB.*
					FROM TBLIMPAPL_MASTER  
					LEFT JOIN CWSEXPORTER ON '$condition' = CWSEXPORTER.Exp_CODE 
					LEFT JOIN GBCTYTAB ON TBLIMPAPL_MASTER.Cexp = GBCTYTAB.cty_cod 
					LEFT JOIN TBLIMPAPL_DETAIL ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_DETAIL.ApplNo 
					LEFT JOIN TBLIMPAPL_FIN ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_FIN.ApplNo 
					WHERE TBLIMPAPL_MASTER.APPLNO =  '$searchkey' and CWSEXPORTER.EXP_code='$condition' order by itemno asc";

			//
			$FIN_DATA = "SELECT GBTARTAB.rul_cod AS GBTARTAB_rulcod,TBLIMPAPL_MASTER.APPLNO AS APPLNO, TBLIMPAPL_MASTER.*, CWSEXPORTER.*,TBLIMPAPL_DETAIL.*,TBLIMPAPL_FIN.*,GBCTYTAB.*,GBBNKTAB.*,GBTOPTAB.*,TBLIMPAPL_MASTER_EXT.UConName,TBLIMPAPL_MASTER_EXT.UConAddr1,TBLIMPAPL_MASTER_EXT.UConAddr2,TBLIMPAPL_MASTER_EXT.UConAddr3,TBLIMPAPL_MASTER_EXT.IntRef AS INT_REF
				FROM TBLIMPAPL_MASTER  
				LEFT JOIN CWSEXPORTER ON '$condition' = CWSEXPORTER.Exp_CODE 
				LEFT JOIN GBCTYTAB ON TBLIMPAPL_MASTER.Cexp = GBCTYTAB.cty_cod 
				LEFT JOIN TBLIMPAPL_DETAIL ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_DETAIL.ApplNo 
				LEFT JOIN TBLIMPAPL_FIN ON TBLIMPAPL_MASTER.ApplNo = TBLIMPAPL_FIN.ApplNo 
				LEFT JOIN GBBNKTAB ON TBLIMPAPL_FIN.BankCode = GBBNKTAB.bnk_cod
				LEFT JOIN GBTOPTAB ON TBLIMPAPL_FIN.Tpayment = GBTOPTAB.top_cod
				LEFT JOIN GBTARTAB ON TBLIMPAPL_DETAIL.HSCode = GBTARTAB.hs6_cod+GBTARTAB.tar_pr1 AND TBLIMPAPL_DETAIL.HSCODE_TAR = GBTARTAB.tar_pr2
				LEFT JOIN TBLIMPAPL_MASTER_EXT ON TBLIMPAPL_MASTER.APPLNO = TBLIMPAPL_MASTER_EXT.APPLNO
				WHERE TBLIMPAPL_MASTER.APPLNO =  '$searchkey' and CWSEXPORTER.EXP_code='$condition' AND ItemNo = '1'";

			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$countrows = sqlsrv_query( $conn_INSCUSTSTDB, $FIN_count , $params, $options );
			$row_number = sqlsrv_num_rows( $countrows );
			$data['row_count'] = (round(sqlsrv_num_rows( $countrows ) / 3 + 1));
			$data['max_rows'] = (round(sqlsrv_num_rows($countrows)));

			$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $FIN_DATA);
			if($stmt_data == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				$data['FIN_data'] = array();
				while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
				{	
					$data['FIN_data'] = $rows;
				}
			}


			$pdest = $data['FIN_data']['Pdest'];
			$FIN_others = "SELECT * FROM TBLIMPAPL_CONS
				LEFT JOIN GBPKGTAB ON TBLIMPAPL_CONS.PackCode = GBPKGTAB.pkg_cod 
				LEFT JOIN GBTARTAB ON TBLIMPAPL_CONS.HSCode = GBTARTAB.HS6_COD + GBTARTAB.TAR_PR1 AND TBLIMPAPL_CONS.HSCODE_TAR = GBTARTAB.TAR_PR2
				LEFT JOIN GBCUOTAB8ZN ON '$pdest' = GBCUOTAB8ZN.cuo_cod 
				LEFT JOIN TBLIMPAPL_FIN on TBLIMPAPL_CONS.applno = TBLIMPAPL_FIN.applno
				where TBLIMPAPL_CONS.APPLNO = '$searchkey' order by TBLIMPAPL_CONS.ItemNo DESC";
			
			$stmt_FIN_others = sqlsrv_query($conn_INSCUSTSTDB, $FIN_others);
			if($stmt_FIN_others == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_FIN_others, SQLSRV_FETCH_ASSOC))
				{	
					$data['FIN_others'] = $rows;
				}
			}
	
			$Tport = $data['FIN_data']['Tport'];
			$FIN_Tport = "SELECT * FROM DmOffClr
				where offClrcod = '$Tport'";

			$stmt_FIN_Tport = sqlsrv_query($conn_INSCUSTSTDB, $FIN_Tport);
			if($stmt_FIN_Tport == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $tport_rows = sqlsrv_fetch_array( $stmt_FIN_Tport, SQLSRV_FETCH_ASSOC))
				{	
					$data['FIN_tport'] = $tport_rows;
				}
			}

			$PLoad = $data['FIN_data']['PLoad'];
			$FIN_PLoad = "SELECT loc_dsc FROM GBLOCTAB
				where loc_cod = '$PLoad'";

			$stmt_FIN_PLoad = sqlsrv_query($conn_INSCUSTSTDB, $FIN_PLoad);
			if($stmt_FIN_PLoad == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $PLoad_rows = sqlsrv_fetch_array( $stmt_FIN_PLoad, SQLSRV_FETCH_ASSOC))
				{	
					$data['FIN_PLoad'] = $PLoad_rows;
				}
			}
			
			/* END FIN DATA */

			/* START CRF */

			/*change $tin to $DecTin if necessary in broker tin*/
			$ConTIN = $data['FIN_data']['ConTIN'];
			$DecTIN = $data['FIN_data']['DecTin'];
			$params = array();
			$crf = "SELECT BROKERTIN, BROKERNAME, BROKERADD1, BROKERADD2, BROKERADD3, COUNTRY FROM undectab WHERE BROKERTIN= '$DecTIN'";
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$crf_data = sqlsrv_query($conn_INSCUSTSTDB, $crf, $params, $options);
			$crf_rows = sqlsrv_num_rows($crf_data);

			$crf_2 = "SELECT CONTIN, CONNAME, CONADDR1, CONADDR2, CONADDR3, CONCTY FROM uncmptab where CONTIN =  '$ConTIN'";
			$crf_data_2 = sqlsrv_query($conn_INSCUSTSTDB, $crf_2);
			if($crf_data_2 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				/* echo 'crf 2';
				die(); */
			}
			
			while($crf_row_2 = sqlsrv_fetch_array( $crf_data_2, SQLSRV_FETCH_ASSOC))
			{
				$data['crf'] = $crf_row_2;
			}

			while($crf_row = sqlsrv_fetch_array( $crf_data, SQLSRV_FETCH_ASSOC))
			{
				$data['crf_2'] = $crf_row;
			}

			/* END CRF */

			/* Start for cocode */
			$co_code = $data['FIN_data']['COCode'];
			$params = array();
			$cocode = "SELECT cty_dsc FROM GBCTYTAB WHERE cty_cod= '$co_code'";
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$cocode_data = sqlsrv_query($conn_INSCUSTSTDB, $cocode, $params, $options);
			$cocode_rows = sqlsrv_num_rows($cocode_data);

			while($cocode_rows = sqlsrv_fetch_array( $cocode_data, SQLSRV_FETCH_ASSOC))
			{
				$data['CO_code'] = $cocode_rows;
			}

			/* End for cocode */
			
			//07082024: Spagara: GBRATTAB to USD
			$params = array();
			$ExchRate_queryUSD = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = 'USD') ORDER BY EEA_DOV DESC";
			$stmt_ExchRate_queryUSD = sqlsrv_query($conn_INSCUSTSTDB, $ExchRate_queryUSD);
			if($stmt_ExchRate_queryUSD == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $ExchRate_queryUSD_rows = sqlsrv_fetch_array( $stmt_ExchRate_queryUSD, SQLSRV_FETCH_ASSOC))
				{	
					$data['ExchRate_queryUSD'] = $ExchRate_queryUSD_rows;
				}
			}
		}

		// Read file lines
		return $data;

	}

	public function ItemCount($applno){
		$applno = $_GET['ApplNo'];
		
		$serverName = 'WEBCWSDB';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$RespHEAD = "SELECT TBLIMPAPL_DETAIL.*, DmCityOrigin.cityDisc
					   FROM TBLIMPAPL_DETAIL
					   LEFT JOIN DmCityOrigin ON TBLIMPAPL_DETAIL.COCode = DmCityOrigin.cityCode
					   WHERE ApplNo =  '$applno' ORDER BY ItemNo";


		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $RespHEAD);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	public function box_9(){
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);

		$data = array();
		$tin = $_GET['tin'];

		if(!$conn_INSCUSTSTDB) {
			echo "Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{
			$params = array();
			$crf = "SELECT BROKERTIN, BROKERNAME, BROKERADD1, BROKERADD2, BROKERADD3, COUNTRY FROM undectab WHERE BROKERTIN= '$tin'";
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$crf_data = sqlsrv_query($conn_INSCUSTSTDB, $crf, $params, $options);
			$crf_rows = sqlsrv_num_rows($crf_data);

			$crf_2 = "SELECT CONTIN, CONNAME, CONADDR1, CONADDR2, CONADDR3, CONCTY FROM uncmptab where CONTIN =  '$tin'";
			$crf_data_2 = sqlsrv_query($conn_INSCUSTSTDB, $crf_2);
			if($crf_data == false || $crf_data_2 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while($crf_row = sqlsrv_fetch_array( $crf_data, SQLSRV_FETCH_ASSOC))
				{
					$data['crf'] = $crf_row;
				}

				while($crf_row_2 = sqlsrv_fetch_array( $crf_data_2, SQLSRV_FETCH_ASSOC))
				{
					$data['crf_2'] = $crf_row_2;
				}
			}

			return $data;
		}
	}


	public function LoadData_backnote($applno){
		$serverName = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$containers = "SELECT container FROM tblimpapl_container WHERE Applno='$applno' order by Id asc";

		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $containers);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	//LObligado: Adding of AI Coce to compute the Excise Tax HS Code
	public function checkAICODE($hscode, $hscodeTar, $tarspec) {

		if ($tarspec == "") { $tarspec = "BLANK"; }

		$serverName = 'WEBCWSDB';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDB = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDB = sqlsrv_connect( $serverName, $connectionINSCUSTSTDB);
		$data = array();
		$query = "SELECT * FROM cwsaicode WHERE hscode='$hscode' and hscode_tar='$hscodeTar'  and tarspec='$tarspec'";

		$stmt_data = sqlsrv_query($conn_INSCUSTSTDB, $query);
		if($stmt_data == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
			{	
				$data[] = $rows;
			}
		}
		
		return $data;
	}

	// Front page
	public function front_page1($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD){
		$DM = @$_GET['DM'];
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S' && $data['FIN_data']['MDec'] != '8') && $data['FIN_data']['MDec'] != 'ID') {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');
			$this->Image('newbg.png',8.27,11.69,200);
		}
		
		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');

			$this->SetXY(152, 5);
			$this->SetFont('Arial','',8);
			//$this->Write(0,'Subject to final BOC Assessment','C');
			$this->Image('draft.png',4,30,200);

			if ($DM == 1) {
			// 	// $this->SetXY(152, 5);
			// 	// $this->SetFont('Arial','',8);
			// 	// $this->Write(0,'Subject to final BOC Assessment','C');
				$this->Image('DEMINIMIS.png',4,30,200);
			}
		
		}
		$this->SetXY(5, 15);
		$this->SetFont('Arial','B',8);
		$this->Write(0,'TEMPORARY SINGLE ADMINISTRATIVE DOCUMENT','');

		// Line break
		$this->Ln(40);
		// if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] != 'C' || $data['FIN_data']['Stat'] != 'S')) {
		// 	$this->Image('DEMINIMIS.png',4,30,200);
		// }
		//$this->Image('ins.jpg',172,286,30);
		
		

		
		

		
	}
	public function front_page($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD){

	
		$DM = @$_GET['DM'];
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S' && $data['FIN_data']['MDec'] != '8') && $data['FIN_data']['MDec'] != 'ID') {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');
			$this->Image('newbg.png',8.27,11.69,200);
		}

		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');

			$this->SetXY(152, 5);
			$this->SetFont('Arial','',8);
			//$this->Write(0,'Subject to final BOC Assessment','C');
			$this->Image('draft.png',4,30,200);

			if ($DM == 1) {
			// 	// $this->SetXY(152, 5);
			// 	// $this->SetFont('Arial','',8);
			// 	// $this->Write(0,'Subject to final BOC Assessment','C');
				$this->Image('DEMINIMIS.png',4,30,200);
			}
		}

		$this->SetXY(5, 15);
		$this->SetFont('Arial','B',8);
		$this->Write(0,'TEMPORARY SINGLE ADMINISTRATIVE DOCUMENT','');

		// Line breakt
		$this->Ln(40);
		// if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] != 'C' || $data['FIN_data']['Stat'] != 'S')) {
		// 	$this->Image('DEMINIMIS.png',4,30,200);
		// }
	//	$this->Image('ins.jpg',172,286,30);
		
		




		$ext = array(
				'001',
				'011', 
				'021', 
				'026', 
				'054', 
				'056', 
				'058',
				'060',
				'0N2',
				'0N4',
				'0R1',
				'101',
				'201',
				'211',
				'301',
				'311',
				'401',
				'501',
				'511',
				'601',
				'611',
				'701',
				'801',
				'DPS',
				'E01',
				'L03',
				'L05',
				'L07',
				'M00',
				'M20',
				'M30',
				'M40',
				'M50',
				'M60',
				'N21',
				'N30',
				'N31',
				'N36',
				'N41',
				'N46',
				'N51',
				'N56',
				'N61',
				'N71',
				'N81',
				'N91',
				'P01',
				'P10',
				'P20',
				'P23',
				'P25',
				'P30',
				'P40',
				'P50',
				'P60',
				'P71',
				'P81',
				'P91',
				'P92',
				'R01',
				'R04',
				'R06',
				'R08',
				'R10',
				'R13',
				'R14',
				'R16',
				'R18',
				'R20',
				'R23',
				'R24',
				'R26',
				'R28',
				'R31',
				'R32',
				'R34',
				'R36',
				'R39',
				'R40',
				'R42',
				'R44',
				'R46',
				'R48',
				'R50',
				'R52',
				'R54',
				'R57',
				'R58',
				'R63',
				'R65',
				'R67',
				'R69',
				'R70',
				'R73',
				'R75',
				'R77',
				'R79',
				'R80',
				'R83',
				'R85',
				'R87',
				'R89',
				'R90',
				'R93',
				'R95',
				'R97',
				'R99',
				'T01',
				'T05',
				'T11',
				'T14',
				'T16',
				'T18',
				'T20',
				'T22',
				'T24',
				'T26',
				'T28',
				'T31',
				'T34',
				'T36',
				'T38',
				'T40',
				'T42',
				'T44',
				'T46',
				'T49',
				'T50',
				'T53',
				'T55',
				'T57',
				'T60',
				'T70',
				'T80',
				'T90',
				'TE1',
				'TN2',
				'TN4',
				'TN6',
				'TN8',
				'L09',
				'LK2Z',
				'L13');
		
		$this->SetXY(5, 23);
		$this->SetFont('Times','',20);
		$this->Cell(12,12,'1',1,0,'C');
		$this->SetXY(5, 23);
		$this->SetFont('Times','',20);
		$this->Cell(12,12,'',1,0,'C');

		$this->SetXY(5, 35);
		$this->SetFont('Times','',15);
		$this->Cell(12,70,'',1,0,'C');
		$this->SetXY(5, 35);
		$this->SetFont('Times','',15);
		$this->Cell(12,70,'',1,0,'C');

		/* CUSTOM WORD */
		$this->SetXY(8, 40);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'C');

		$this->SetXY(8, 50);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'U');

		$this->SetXY(8, 60);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'S');

		$this->SetXY(8, 70);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'T');

		$this->SetXY(8, 80);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'O');

		$this->SetXY(7, 90);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'M');

		$this->SetXY(8, 100);
		$this->SetFont('Times','B',15);
		$this->Write(0, 'S');

		$this->SetXY(5, 105);
		$this->SetFont('Times','',20);
		$this->Cell(12,12,'1',1,0,'C');
		$this->SetXY(5, 105);
		$this->SetFont('Times','',20);
		$this->Cell(12,12,'',1,0,'C');

		/* CUSTOM WORD END */

		/* BOX 2, 8, 14, 18, 19, 21, 25, 26, 27, 29, 30 */

		$this->SetXY(17, 23);
		$this->SetFont('Times','',20);
		$this->Cell(82,94,'',1,0,'C');
		$this->SetXY(19, 23);
		$this->SetFont('Times','',20);
		$this->Cell(80,12,'','T',0,'C');

		
		/* BOX 2 */

		$this->SetXY(17, 23);
		$this->SetFont('Arial','',20);
		$this->Cell(82,22,'','LTB',0,'');

		$this->SetXY(19, 25);
		$this->SetFont('Arial','',6);
		$this->Write(0, '2  Exporter / Supplier Address');
		
		$this->SetXY(17, 28.5);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupCode']);

		$this->SetXY(17, 31.4);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupName']);

		$this->SetXY(17, 34.3);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupAddr1']);

		$this->SetXY(17, 37.2);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupAddr2']);

		$this->SetXY(17, 40.1);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupAddr3']);
		
		$this->SetXY(17, 43);
		$this->SetFont('Arial','',6.7);
		$this->Write(0, $data['FIN_data']['SupAddr4']);


		/* END BOX 2 */

		/* BOX 8 */
		
		$this->SetXY(17, 45);
		$this->SetFont('Arial','',20);
		$this->Cell(82,20,'','LB',0,'');

		$this->SetXY(19, 47);
		$this->SetFont('Arial','',6);
		$this->Write(0, '8  Importer / Consignee, Address');

		$this->SetXY(63, 47);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'TIN: ');

		$ConTIN = $data['FIN_data']['ConTIN'];
		if (@$data['crf'] == null) {
			$expname = $data['FIN_data']['ConName'];
			$expaddress1 = $data['FIN_data']['ConAddr1'];
			$expaddress2 = $data['FIN_data']['ConAddr2'];
			$expaddress3 = $data['FIN_data']['ConAddr3'];
			$expaddress4 = '';
		}else{
			$expname = @$data['crf']['CONNAME'];
			$expaddress1 = @$data['crf']['CONADDR1'];
			$expaddress2 = @$data['crf']['CONADDR2'];
			$expaddress3 = @$data['crf']['CONADDR3'];
			$expaddress4 = @$data['crf']['CONCTY'];
		}
		//SPagara: 12012022: removed
		//if($ConTIN == '000400016000'){
		//	$expaddress1 = '8TH FLOOR TERA TOWER BRIDGETOWNE E. RODRIGUEZ';
		//	$expaddress2 = 'AVENUE C5 ROAD';
		//	$expaddress3 = 'UGONG NORTE QUEZON CITY 1100';
		//	$expaddress4 = 'PHILIPPINES';
		//}
		
		if($ConTIN == '003254875000'){
			$expaddress1 = 'DON CELSO S TUAZON AVE CAINTA RIZAL';
			$expaddress2 = ' PHILIPPINES';
			$expaddress3 = 'CAINTA 1900';
			$expaddress4 = 'PHILIPPINES';
		}
		//Spagara: Comment out  requested by Sir TJ
		//if($ConTIN == '217749284000'){
		//	$expaddress1 = 'ON A.P.C. B.V. 2ND STREET, PHASE 2,';
		//	$expaddress2 = 'CEZ, ROSARIO';
		//	$expaddress3 = 'CAVITE 4106';
		//	$expaddress4 = '';
		//}

		if($ConTIN == '000428573000'){
			$expaddress1 = 'SARANGANI ECONOMIC DEVT ZONE';
			$expaddress2 = 'POLOMOLOK SOUTH COTABATO 9504';
			$expaddress3 = 'PHILIPPINES';
			$expaddress4 = '';
		}

		if($ConTIN == '000079915000'){
			$expaddress1 = 'KM. 27 E. AGUINALDO HIGHWAY ANABU I';
			$expaddress2 = 'I IMUS';
			$expaddress3 = 'CAVITE 4103';
			$expaddress4 = '';
		}

		//if($ConTIN == '000254013000'){
		//	$expaddress1 = 'CENTERPOINT BLDG FORMERLY 284 CANDA';
		//	$expaddress2 = 'NO BLDG ZIGA AVE TABACO CITY ALBAY';
		//	$expaddress3 = 'TABACO CITY 4511';
		//	$expaddress4 = 'PHILIPPINES';
		//}
		
		if($ConTIN == '008550093000'){
			$expaddress1 = '10TH FLOOR SALCEDO TOWER 169 HV DELA COSTA ST';
			$expaddress2 = 'SALCEDO VILLAGE';
			$expaddress3 = 'MAKATI CITY 1227';
			$expaddress4 = 'PHILIPPINES';
		}

		//05072021: SPagara: additional - requested by Doms
		if($ConTIN == '006330680000'){
			$expaddress1 = 'WAREHOUSE NO.3 NO.1 PUNTURIN INDUST';
			$expaddress2 = 'RIAL KABESANG PORONG ST';
			$expaddress3 = 'PUNTURIN VALENZUELA CITY 1447';
			$expaddress4 = 'PHILIPPINES';
		}

		//05072021: SPagara: additional - requested by Doms
		if($ConTIN == '005883632000'){
			$expaddress1 = 'CP GARCIA HIGHWAY, CATITIPAN, BRGY.';
			$expaddress2 = 'SASA DAVAO CITY';
			$expaddress3 = 'DAVAO CITY 8000';
			$expaddress4 = 'PHILIPPINES';
		}

		//05112021: SPagara: additional - requested by Doms
		if($ConTIN == '000107873000'){
			$expname = 'MITSUBISHI POWER (PHILIPPINES) INC.';
			$expaddress1 = 'AG AND P SPECIAL ECONOMIC ZONE BRGY';
			$expaddress2 = '. SAN ROQUE, BAUAN';
			$expaddress3 = 'BAUAN, BATANGAS 4201';
			$expaddress4 = 'PHILIPPINES';
		}

		//05172021: SPagara: additional - requested by Doms
		if($ConTIN == '008189565000'){
			$expname = 'DTN PHILIPPINES INC';
			$expaddress1 = 'UNIT 1601B TO 1603 16TH FLOOR THE F';
			$expaddress2 = 'INANCE CENTRE 26TH ST BGC';
			$expaddress3 = 'TAGUIG CITY 1634';
			$expaddress4 = 'PHILIPPINES';
		}

		//05282021: SPagara: additional - requested by Aileen
		if($ConTIN == '009761773000'){
			$expaddress1 = 'LOTS 1-8, PHASE 3, GOLDEN GATE BUSINESS';
			$expaddress2 = ' PARK BUENAVISTA 2';
			$expaddress3 = ' GENERAL TRIAS 4107';
			$expaddress4 = 'PHILIPPINES';
		}
		
		if($ConTIN == '008964875000'){
			$expaddress1 = 'INC. SECOND FLOOR CHING BEE BLDG., RIZAL ST.';
			$expaddress2 = 'TABACO CITY';
			$expaddress3 = 'TABACO CITY 4511';
			$expaddress4 = 'PHILIPPINES';
		}
		
		//if($ConTIN == '000254013000'){
		//	$expaddress1 = 'P-4 ARELLANO  ST. ZIGA';
		//	$expaddress2 = 'AVENUE, TAYHI TABACO';
		//	$expaddress3 = 'CITY  ALBAY 4511';
		//	$expaddress4 = '';
		//}
		//12202022: SPagara: additional - request by Doms; removed 0731023 as per Sir Michael
		//if($ConTIN == '010489027000'){
		//	$expname = 'PETVALUE PHILIPPINES CORPORATION';
		//	$expaddress1 = '2F NO. 24 SOTTO ST. BRGY. BAGUMBAYAN';
		//	$expaddress2 = 'GENERAL TRIAS 4107';
		//	$expaddress3 = '';
		//	$expaddress4 = '';
		//}
		//01112023: SPagara: additional - request by Aileen
		//if($ConTIN == '000409675000'){
		//	$expname = 'TELENGTAN BROTHERS & SONS INC.';
		//	$expaddress1 = 'TELENGTAN BLDG KM14 SOUTH SUPER HI-';
		//	$expaddress2 = 'WAY MERVILLE PARANAQUE MM';
		//	$expaddress3 = 'PARANAQUE 1700';
		//	$expaddress4 = 'PHILIPPINES';
		//}

		//04192023: SPagara: additional - request by Sir TJ
		// if($ConTIN == '005303019000'){
			// $expname = 'EPSON PHILIPPINES CORPORATION';
			// $expaddress1 = 'GF ANF 9F EXQUADRA TOWER 1 JADE DRI';
			// $expaddress2 = 'VE SAN ANTONION ORTIGAS CENTER';
			// $expaddress3 = 'PASIG 1605';
			// $expaddress4 = '';
		// }
		

		
		$AspacCLTCODE = array("ASPACA", "JCAA", "BABANTAOA", "RSECOBARA", "ABARCEBALA", "GPARNALAA", "APTUVILLOA", "JLEONARA", "JPAULMIA", "GPIEDADA");

		if (in_array($data['FIN_data']['cltcode'], $AspacCLTCODE)) {
			$isAspacClient = "YES";
		} else {
			$isAspacClient = "NO";
		}
		
		if ($isAspacClient === "YES" && $ConTIN === "000000000000") {
			$expname = $data['FIN_data']['ConName'];
			$expaddress1 = $data['FIN_data']['ConAddr1'];
			$expaddress2 = $data['FIN_data']['ConAddr2'];
			$expaddress3 = $data['FIN_data']['ConAddr3'];
			$expaddress4 = $data['FIN_data']['ConAddr4'];
		}
		
		$ConTIN = $data['FIN_data']['ConTIN'];
		

		$this->SetXY(69, 47);
		$this->SetFont('Arial','',7);
		if ($ConTIN == "000000000000") {
			$this->Write(0, '');
		}else{
			$this->Write(0, $ConTIN);
		}

		$this->SetXY(17, 50);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expname);

		$this->SetXY(17, 53);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress1);

		$this->SetXY(17, 56);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress2);

		$this->SetXY(17, 59);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress3);

		$this->SetXY(17, 62);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress4);

		/* END BOX 8 */

		/* BOX 14 */
		
		$this->SetXY(17, 63);
		$this->SetFont('Arial','',20);
		$this->Cell(82,21,'','LB',0,'');

		$this->SetXY(19, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '14  Broker / Attorney-In-Fact, Address');

		$this->SetXY(63, 67);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'TIN: ');

		$this->SetXY(69, 67);
		$this->SetFont('Arial','',7);
		$this->Write(0, $data['FIN_data']['DecTin']);
		
		$this->SetXY(17, 70);
		$this->SetFont('Arial','',8);
		$this->Write(0, @$data['crf_2']['BROKERNAME']);

		$this->SetXY(17, 73);
		$this->SetFont('Arial','',8);
		$this->Write(0, @$data['crf_2']['BROKERADD1']);

		$this->SetXY(17, 76);
		$this->SetFont('Arial','',8);
		$this->Write(0, @$data['crf_2']['BROKERADD2']);

		$this->SetXY(17, 79);
		$this->SetFont('Arial','',8);
		$this->Write(0, @$data['crf_2']['BROKERADD3']);

		$this->SetXY(17, 82);
		$this->SetFont('Arial','',8);
		$this->Write(0, @$data['crf_2']['COUNTRY']);

		/* END BOX 14 */

		/* BOX 18 */
		
		$this->SetXY(17, 85);
		$this->SetFont('Arial','',20);
		$this->Cell(82,6,'','LB',0,'');

		$this->SetXY(19, 86);
		$this->SetFont('Arial','',6);
		$this->Write(0, '18  Vessel / Aircraft');

		$this->SetXY(17, 89);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Vessel']);

		$this->SetXY(65, 87);
		$this->SetFont('Arial','',20);
		$this->Cell(5,4,'','L',0,'');

		if ($data['FIN_data']['MDec'] != 'ID'){
			$this->SetXY(65, 89);
			$this->SetFont('Arial','',8);
			$this->Write(0, $data['FIN_data']['RegNo']);
		}

		/* END BOX 18 */

		/* BOX 19 */
		
		$this->SetXY(83, 84);
		$this->SetFont('Arial','',20);
		$this->Cell(5,7,'','L',0,'');

		$this->SetXY(83, 86);
		$this->SetFont('Arial','',6);
		$this->Write(0, '19  Ct');

		$CF = $data['FIN_data']['ConFlag'];
		if ($CF == 1) {
			$this->Image('check.png',90,87,2.5);
		}else{
			$this->Image('cross.png',90,87.5,2);
		}
		
		// if ($data['FIN_data']['MDec'] != 'ID'){
		// 	$this->SetXY(85, 89);
		// 	$this->SetFont('Arial','',8);
		// 	$this->Write(0, $data['FIN_data']['ConFlag']);
		// }

		/* END BOX 19 */

		/* BOX 21 */
		
		$this->SetXY(17, 92);
		$this->SetFont('Arial','',20);
		$this->Cell(82,5,'','LB',0,'');

		$this->SetXY(19, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '21  Local Carrier (if any)');

		//$this->SetXY(73, 91);
		$this->SetXY(21, 95);
		$this->SetFont('Arial','',8);
		//05082024:Spagara: For Aspac
		//07252024:Spagara: Update on Flight Date
		//if ($isAspacClient == 'YES'){}
		// if ($data['FIN_data']['MDec'] == 'IES'){
			$this->Write(0, $data['FIN_data']['LocalC']);
			// }
		//else{
		//	$this->Write(0, $data['FIN_data']['DtArrv']);
		//}
		

		/* END BOX 21 */

		/* BOX 25 */
		
		$this->SetXY(17, 103);
		$this->SetFont('Arial','',20);
		$this->Cell(82,4,'','B',0,'');

		$this->SetXY(19, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, '25');

		$this->SetXY(21, 102);
		$this->SetFont('Arial','',8);
		//05082024:Spagara: For Aspac
		/*if ($isAspacClient == "YES"){
			if ($data['FIN_data']['MDec'] == 'IES'){
			$this->Write(0, $data['FIN_data']['LocalC']);
			}else{
			$this->Write(0, $data['FIN_data']['DtArrv']);
			}
		}else{
			$this->Write(0, $data['FIN_data']['DtArrv']);
		}*/
		if ($data['FIN_data']['MDec'] != 'IES'){
				$this->Write(0, $data['FIN_data']['DtArrv']);
		}
		/* END BOX 25 */

		/* BOX 26 */
		
		$this->SetXY(41, 97);
		$this->SetFont('Arial','',20);
		$this->Cell(82,10,'','L',0,'');

		$this->SetXY(42, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, '26');

		$this->SetXY(17, 102);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		/* END BOX 26 */

		/* BOX 27 */
		
		$this->SetXY(61, 97);
		$this->SetFont('Arial','',20);
		$this->Cell(80,9,'','L',0,'');

		$this->SetXY(62, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, '27 Transhipment Port');

		$AspacCLTCODE = array("ASPACA", "JCAA", "BABANTAOA", "RSECOBARA", "ABARCEBALA", "GPARNALAA", "APTUVILLOA", "JLEONARA", "JPAULMIA", "GPIEDADA");

		if (in_array($data['FIN_data']['cltcode'], $AspacCLTCODE)) {
			$isAspacClient = "YES";
		} else {
			$isAspacClient = "NO";
		}

		if ($data['FIN_data']['MDec'] == '4' || $data['FIN_data']['MDec']== '4ES' || $data['FIN_data']['MDec']== 'IE-4' || $data['FIN_data']['MDec']== 'ied-4' || $data['FIN_data']['MDec']== 'IED' || $data['FIN_data']['MDec']== 'ID') {
			$OffClear = '';
			$OffClearance = '';
		}elseif ($data['FIN_data']['MDec'] == '8PP' || ($data['FIN_data']['cltcode'] == 'FEDEX' && ($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8ZE'))) {
			$OffClear = $data['FIN_data']['PLoad'];
			$OffClearance = @$data['FIN_PLoad']['loc_dsc'];
		}elseif ($data['FIN_data']['cltcode'] == 'DHLEXA' && ($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8ZE')) {
			$OffClear = $data['FIN_data']['PLoad'];
			$OffClearance = @$data['FIN_PLoad']['loc_dsc'];
		}elseif (($isAspacClient == "YES" || ($data['FIN_data']['cltcode'] == 'DHLEXA')) && ($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8ZE')) {
			$OffClear = $data['FIN_data']['PLoad'];
			$OffClearance = @$data['FIN_PLoad']['loc_dsc'];
		}elseif ($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8ZE') {
			$OffClear = $data['FIN_data']['PLoad'];
			$OffClearance = @$data['FIN_PLoad']['loc_dsc'];
		}else{
			$OffClear = $data['FIN_data']['Tport'];
			$OffClearance = @$data['FIN_tport']['offClrName'];
		}

		if ($data['FIN_data']['MDec'] != 'ID'){
			$this->SetXY(62, 102);
			$this->SetFont('Arial','',6);
			$this->Write(0, $OffClear);

			$this->SetXY(62, 105);
			$this->SetFont('Arial','',6);
			$this->Write(0, $OffClearance);
		}

		/* END BOX 27 */

		/* BOX 29 */
		
		$this->SetXY(17, 113);
		$this->SetFont('Arial','',20);
		$this->Cell(80,3,'','',0,'');

		$this->SetXY(19, 109);
		$this->SetFont('Arial','',6);
		$this->Write(0, '29  Port of Destination');

		$this->SetXY(17, 111.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, $data['FIN_data']['Pdest']);

		$this->SetXY(17, 114.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, $data['FIN_others']['cuo_nam']);

		/* END BOX 29 */

		/* BOX 30 */

		$this->SetXY(61, 106);
		$this->SetFont('Arial','',20);
		$this->Cell(82,11,'','L',0,'');

		$this->SetXY(61, 109);
		$this->SetFont('Arial','',6);
		$this->Write(0, '30  Location of Goods');

		$this->SetXY(72, 114);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Lgoods']);

		/* END BOX 30 */

		$this->SetXY(129, 20);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Office Code');

		$this->SetXY(147, 20);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['OffClear']);

		$this->SetXY(132, 24);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['OffClearance']);

		$this->SetXY(129, 28);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Customs Reference');
		if ($data['FIN_data']['Stat'] == 'S' || $data['FIN_data']['Stat'] == 'C') {
			$REGREF = '';
			$REGNO = '';
			$REGDATE = '';
			$REGDATEYEAR = '';
		}else{
		//	$REGREF = $RespHEAD[0]['REGREF'];
	    //		$REGNO = $RespHEAD[0]['REGNO'];
		
		$REGREF = "";
	   	$REGNO = "";
			
			//if ($RespHEAD[0]['REGDATE'] == NULL) {
				$REGDATE = '';
				$REGDATEYEAR = '';
		//	}else{
			//	$REGDATE = date('m-d-Y', strtotime($RespHEAD[0]['REGDATE']));
			//	$REGDATEYEAR = date('Y', strtotime($RespHEAD[0]['REGDATE']));
		//	}

		}

		// if($data['FIN_data']['MDec'] == 'ID'){
			

		// 	if (strlen($data['FIN_data']['OffClear']) == 3) {
		// 		$port = $data['FIN_data']['OffClear'].'  ';
		// 	}elseif (strlen($data['FIN_data']['OffClear']) == 4) {
		// 		$port = $data['FIN_data']['OffClear'].' ';
		// 	}elseif (strlen($data['FIN_data']['OffClear']) == 5) {
		// 		$port = $data['FIN_data']['OffClear'];
		// 	}

		// 	if ($data['FIN_data']['Stat'] == 'S' || $data['FIN_data']['Stat'] == 'C') {
		// 		$cust_ref = '';
		// 	}else{
		// 		$cust_ref = $REGDATEYEAR.$port.'II'.$REGNO;
		// 	}

		// 	$this->SetXY(132, 32);
		// 	$this->SetFont('Arial','',8);
		// 	$this->Write(0, $cust_ref);

		// 	if ($cust_ref != '') {
		// 		$code = $cust_ref;
		// 		$this->Code128(23,2,$code,70,8);

		// 		$this->SetXY(22, 12);
		// 		$this->SetFont('Arial','',6);
		// 		$this->Write(0,$cust_ref,'');
		// 	}

		// 	$this->SetXY(162, 32);
		// 	$this->SetFont('Arial','',8);
		// 	$this->Write(0, $REGDATE);
		// }else{
			$this->SetXY(132, 32);
			$this->SetFont('Arial','',8);
			//$this->Write(0, $REGREF);

			$this->SetXY(137, 32);
			$this->SetFont('Arial','',8);
			$this->Write(0, $REGNO);

			if ($data['FIN_data']['Stat'] == 'S' || $data['FIN_data']['Stat'] == 'C') {
				$cust_ref = '';
			}else{
				$cust_ref = $REGREF.$REGNO;
			}

			// $this->SetXY(132, 32);
			// $this->SetFont('Arial','',8);
			// $this->Write(0, $cust_ref);

			if($data['FIN_data']['MDec'] == 'ID'){

				if ($cust_ref != '') {
					$code = $cust_ref;
					$this->Code128(23,2,$code,35,8);

					$this->SetXY(22, 12);
					$this->SetFont('Arial','',6);
					$this->Write(0,$cust_ref,'');
				}
			}

			//INC
			$this->SetXY(152, 32);
			$this->SetFont('Arial','',8);
			$this->Write(0, $REGDATE);
		// }


		//INC
		$this->SetXY(129, 36);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Registry Number');

		$this->SetXY(147, 36);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Manifest']);


		/* BOX 2, 8, 14, 18, 19, 21, 25, 26, 27, 29, 30 END */


		/* BOX 1 */
		

		$this->SetXY(99, 19);
		$this->SetFont('Arial','',20);
		$this->Cell(30,12,'','1',0,'');
		$this->SetXY(99, 19);
		$this->SetFont('Arial','',20);
		$this->Cell(30,12,'','1',0,'');

		$this->SetXY(99, 21);
		$this->SetFont('Arial','',6);
		$this->Write(0, '1  DECLARATION');

		$this->SetXY(99, 24);
		$this->SetFont('Arial','',8);
		$this->Cell(10,6,$data['FIN_data']['MDec'],0,0,'C');

		$this->SetXY(109, 25);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		$this->SetXY(109, 24);
		$this->SetFont('Arial','',8);
		$this->Cell(10,6,$data['FIN_data']['Mdec2'],0,0,'C');

		$this->SetXY(119, 25);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		/* END BOX 1 */


		/* BOX 3 */
		
		$this->SetXY(99, 31);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','R',0,'');

		$this->SetXY(99, 33);
		$this->SetFont('Arial','',6);
		$this->Write(0, '3  Page');

		$this->SetXY(101, 36);
		$this->SetFont('Arial','B',9);
		$this->Write(0, '1');

		$this->SetXY(106, 35);
		$this->SetFont('Arial','',20);
		$this->Cell(30,3,'','L',0,'');

		$this->SetXY(108, 36);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['row_count']);


		/* END BOX 3 */

		/* BOX 4 */

		$this->SetXY(114, 31);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','L',0,'');

		$this->SetXY(114, 33);
		$this->SetFont('Arial','',6);
		$this->Write(0, '4');


		/* END BOX 4 */

		/* BOX 5, 6, 7, 9, 10, 11, 12, 13, 15, 16, 17, 20, 22, 23, 24, 28 */

		$this->SetXY(99, 38);
		$this->SetFont('Arial','',20);
		$this->Cell(108,7,'','TR',0,'');

		$this->SetXY(99, 45);
		$this->SetFont('Times','',20);
		$this->Cell(108,72,'','TBR',0,'C');
		$this->SetXY(30, 28);

		/* BOX 5 */

		$this->SetXY(99, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, '5  Items');

		$this->SetXY(103, 43);
		$this->SetFont('Arial','',9);
		$this->Write(0, $data['max_rows']);
		
		$this->SetXY(114, 38);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','L',0,'');

		/* END BOX 5 */

		/* BOX 6 */
		
		$this->SetXY(114, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, '6  Tot Pack');

		$this->SetXY(123, 43);
		$this->SetFont('Arial','',9);
		$this->Write(0, $data['FIN_data']['Packs']);

		/* END BOX 6 */

		/* BOX 7 */

		$this->SetXY(139, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, '7  Reference Number');

		$this->SetXY(139, 38);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','L',0,'');

		$this->SetXY(140, 43);
		$this->SetFont('Arial','',8);	
		$max_char = 70;
		
		if (!empty($data['FIN_data']['IEIRD'])) {
			$refno = $data['FIN_data']['APPLNO'] . ' / ' . $data['FIN_data']['IEIRD'];
			
		}else{
			$refno = $data['FIN_data']['APPLNO'];
		}
		
		//if($data['FIN_data']['MDec'] == 'ID' || $data['FIN_data']['MDec'] == 'IE'){
			if (!empty($data['FIN_data']['INT_REF'])) {
			$ref=	substr(@$data['FIN_data']['INT_REF'], 0, $max_char);
				$refno = $data['FIN_data']['APPLNO'] . ' / ' . $ref;
			}else{
				$refno = $data['FIN_data']['APPLNO'];
			}
			//print_r($ref);die();
		//}
			$this->SetXY(140, 43);
		$this->MultiCell(80,0,$refno,0,'L');

		/* END BOX 7 */

		/* Box 9 */

		$this->SetXY(99, 47);
		$this->SetFont('Arial','',6);
		$this->Write(0, '9  CRF NUMBER :');
		$box_9 = $this->box_9();
		if (((($data['FIN_data']['MDec'] == '4ID') || ($data['FIN_data']['MDec'] == '4FD') ) && ($data['FIN_data']['Mdec2'] == 4)) && $_GET['ppa'] == '' ) {
			//if ($_GET['ppa'] == '' ) {
				
				if (isset($box_9['crf']['BROKERNAME'])) {
					$max_char= 70;
					$UConName=$box_9['crf_2']['CONNAME'].' - '.$box_9['crf_2']['CONTIN'];
					$addr1=	substr(@$box_9['crf_2']['CONADDR1'], 0, $max_char);
					$addr2 =substr(@$box_9['crf_2']['CONADDR2'], 0, $max_char);
					$addr3 =substr(@$box_9['crf_2']['CONADDR3'], 0, $max_char);
					$count_name = strlen($box_9['crf_2']['CONNAME']);
					$count_nameA1 = strlen($box_9['crf_2']['CONADDR1']);
					$count_nameA2 = strlen($box_9['crf_2']['CONADDR2']);
					$count_nameA3 = strlen($box_9['crf_2']['CONADDR3']);
					
					if($count_name >= 70 && $count_nameA1 >= 70  && $count_nameA2 >= 70 && $count_nameA3 >= 70){
	
						$this->SetFont('Arial','',6);
					}else{
	
						$this->SetFont('Arial','',5);
					}
	
					$this->SetXY(99, 49.5);
					$this->SetFont('Arial','',6);
					$this->Write(0, $UConName);
	
					$this->SetXY(99, 52);
					$this->SetFont('Arial','',6);
					$this->Write(0, $addr1);
	
					$this->SetXY(99, 54.5);
					$this->SetFont('Arial','',6);
					$this->Write(0, $addr2);
	
					$this->SetXY(99, 57);
					$this->SetFont('Arial','',6);
					$this->Write(0, $addr3);
				}else{
					
					$this->SetXY(99, 49.5);
					$max_char= 70;
					$UConName=$box_9['crf_2']['CONNAME'].' - '.$box_9['crf_2']['CONTIN'];
					$addr1=	substr(@$data['crf_2']['CONADDR1'], 0, $max_char);
					$addr2 =substr(@$data['crf_2']['CONADDR2'], 0, $max_char);
					$addr3 =substr(@$data['crf_2']['CONADDR3'], 0, $max_char);
					$count_name = strlen($box_9['crf_2']['CONNAME']);
					$count_nameA1 = strlen($data['crf_2']['CONADDR1']);
					$count_nameA2 = strlen($data['crf_2']['CONADDR2']);
					$count_nameA3 = strlen($data['crf_2']['CONADDR3']);
	
					if($count_name >= 70 && $count_nameA1 >= 70  && $count_nameA2 >= 70 && $count_nameA3 >= 70){
	
						$this->SetFont('Arial','',6);
					}else{
	
						$this->SetFont('Arial','',5);
					}
	
	
					$this->SetFont('Arial','',6);
					$this->Write(0,$UConName);
	
					$this->SetXY(99, 52);
					$this->SetFont('Arial','',6);
					$this->Write(0,$addr1);
	
					$this->SetXY(99, 54.5);
					$this->SetFont('Arial','',6);
					$this->Write(0, $addr2);
	
					$this->SetXY(99, 57);
					$this->SetFont('Arial','',6);
					$this->Write(0, $addr3);
				}
			}else{
				//print_r("test"); die();
				/*$max_char= 70;
				$UConName=substr(@$data['FIN_data']['UConName'], 0, $max_char);
				$addr1=	substr(@$data['FIN_data']['UConAddr1'], 0, $max_char);
				$addr2 =substr(@$data['FIN_data']['UConAddr2'], 0, $max_char);
				$addr3 =substr(@$data['FIN_data']['UConAddr3'], 0, $max_char);
				$this->SetXY(99, 49.5);
				$count_name = strlen($data['FIN_data']['UConName']);
				$count_nameA1 = strlen($data['FIN_data']['UConAddr1']);
				$count_nameA2 = strlen($data['FIN_data']['UConAddr2']);
				$count_nameA3 = strlen($data['FIN_data']['UConAddr3']);
	
				if($count_name >= 70 && $count_nameA1 >= 70  && $count_nameA2 >= 70 && $count_nameA3 >= 70){
					$this->SetFont('Arial','',6);
				}else{
	
					$this->SetFont('Arial','',5);
				}
				$this->Write(0,$UConName);
	
				$this->SetXY(99, 52);
				$this->Write(0, $addr1);
	
				$this->SetXY(99, 54.5);
				$this->Write(0, $addr2);
	
				$this->SetXY(99, 57);
				$this->Write(0, $addr3);*/
			}
	
			//if ((($data['FIN_data']['MDec'] == 'ID' || $data['FIN_data']['MDec'] == 'IE') && $data['FIN_data']['ConTIN'] == '777777777777') || ($data['FIN_data']['MDec'] == 'IES') || (($data['FIN_data']['MDec'] == '8PP' || $data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '4FD') && $_GET['ppa'] != '')) {
			if ((($data['FIN_data']['MDec'] == 'ID' || $data['FIN_data']['MDec'] == 'IE') && $data['FIN_data']['ConTIN'] == '777777777777') || (($data['FIN_data']['MDec'] == '8PP' || $data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '4FD' || $data['FIN_data']['MDec'] == 'IES' || $data['FIN_data']['MDec'] == '8ZE') && $_GET['ppa'] != '')) { 
				$max_char= 70;
				$UConName=substr(@$data['FIN_data']['UConName'], 0, $max_char);
				$addr1=	substr(@$data['FIN_data']['UConAddr1'], 0, $max_char);
				$addr2 =substr(@$data['FIN_data']['UConAddr2'], 0, $max_char);
				$addr3 =substr(@$data['FIN_data']['UConAddr3'], 0, $max_char);
				$this->SetXY(99, 49.5);
				$count_name = strlen($data['FIN_data']['UConName']);
				$count_nameA1 = strlen($data['FIN_data']['UConAddr1']);
				$count_nameA2 = strlen($data['FIN_data']['UConAddr2']);
				$count_nameA3 = strlen($data['FIN_data']['UConAddr3']);
	
				if($count_name >= 70 && $count_nameA1 >= 70  && $count_nameA2 >= 70 && $count_nameA3 >= 70){
					$this->SetFont('Arial','',6);
				}else{
	
					$this->SetFont('Arial','',5);
				}
				$this->Write(0,$UConName);
	
				$this->SetXY(99, 52);
				$this->Write(0, $addr1);
	
				$this->SetXY(99, 54.5);
				$this->Write(0, $addr2);
	
				$this->SetXY(99, 57);
					$this->Write(0, $addr3);
			}else{
				$this->SetXY(99, 52);
				$this->SetFont('Arial','',8);
				if ($isAspacClient != "YES") {
					$this->Write(0, 'T/S or W/S Entry No. : '.$data['FIN_data']['EntryNumber']);
				} else {
					$this->Write(0,$data['FIN_data']['EntryNumber']);
				}
				//$this->Write(0, 'T/S or W/S Entry No. : '.$data['FIN_data']['EntryNumber'].' / '.$data['FIN_data']['IntRef']);
				$this->Write(0, '' );
			}


		$this->SetXY(99, 51);
		$this->SetFont('Arial','',20);
		$this->Cell(108,8,'','B',0,'');

		/* END BOX 9 */

		/* Box 10 */

		$this->SetXY(99, 61);
		$this->SetFont('Arial','',6);
		$this->Write(0, '10');

		$this->SetXY(82, 61);
		$this->SetFont('Arial','',20);
		$this->Cell(30,4,'','R',0,'');

		$this->SetXY(99, 56);
		$this->SetFont('Arial','',20);
		$this->Cell(108,9,'','B',0,'');

		/* END BOX 10 */

		/* BOX 11 */

		$this->SetXY(134, 61);
		$this->SetFont('Arial','',6);
		$this->Write(0, '11');

		$this->SetXY(104, 59);
		$this->SetFont('Arial','',20);
		$this->Cell(30,6,'','R',0,'');

		/* BOX 11 END*/

		/* BOX 12 */
		
		$this->SetXY(155, 61);
		$this->SetFont('Arial','',6);
		$this->Write(0, '12  Tot. F/I/O (Php)');

		$this->SetXY(155, 59);
		$this->SetFont('Arial','',20);
		$this->Cell(30,6,'','LR',0,'');

		$Freight = 0;
		$Insurance = 0;
		$OtherCost = 0;
		foreach ($RespIT as $key => $items) {

			if ($items['TAXCODE'] == 'EFR') {
				$Freight += $items['TAXAMT'];
			}

			if ($items['TAXCODE'] == 'INS') {
				$Insurance += $items['TAXAMT'];
			}

			if ($items['TAXCODE'] == 'OTH') {
				$OtherCost += $items['TAXAMT'];
			}
		}

		if ($data['FIN_data']['Stat'] == 'S' || $data['FIN_data']['Stat'] == 'C') {
			if($data['FIN_data']['InvCurr'] == 'PHP' && $data['FIN_data']['CustCurr'] == 'PHP' && $data['FIN_data']['FreightCurr'] == 'PHP' && $data['FIN_data']['InsCurr'] == 'PHP' && $data['FIN_data']['OtherCurr'] == 'PHP'){
				$FIO = number_format((str_replace(',', '', $data['FIN_data']['FreightCost']) + str_replace(',', '', $data['FIN_data']['InsCost']) + str_replace(',', '', $data['FIN_data']['OtherCost'])),2);
			}else{
				$FIO = number_format(round(((str_replace(',', '', $data['FIN_data']['FreightCost']) * $data['FIN_data']['FExchRate']) + (str_replace(',', '', $data['FIN_data']['InsCost']) * $data['FIN_data']['IExchRate']) + (str_replace(',', '', $data['FIN_data']['OtherCost']) * $data['FIN_data']['OExchRate'])),2),2);
			}
		}else{
			$FIO = number_format(($Freight + $Insurance + $OtherCost),2);
		}

		
		$this->SetXY(170, 60);
		$this->SetFont('Arial','',8);
		$this->Cell(15,6,$FIO,0,0,'R');

		/* BOX 12 END*/

		/* BOX 13 */

		$this->SetXY(185, 61);
		$this->SetFont('Arial','',6);
		$this->Write(0, '13  T. Ref.');

		$this->SetXY(167, 60);
		$this->SetFont('Arial','',20);
		$this->Cell(32,5,'','R',0,'');

		/* BOX 13 END*/

		/* Box 15 */

		$this->SetXY(99, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '15  Country of Export');

		$this->SetXY(99, 67);
		$this->SetFont('Arial','',20);
		$this->Cell(108,8,'','B',0,'');

		$this->SetXY(102.5, 71);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['cty_dsc']);

		$this->SetXY(158, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '15  C.E. Code');

		$this->SetXY(158, 65);
		$this->SetFont('Arial','',8);
		$this->Cell(29,10,$data['FIN_data']['cty_cod'],'LR',0,'C');

		/* END BOX 15 */

		/* Box 17 */

		$this->SetXY(187, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '17');

		$this->SetXY(187, 65);
		$this->SetFont('Arial','',8);
		$this->Cell(20,10,'PH',0,0,'C');

		/* END BOX 17 */

		/* Box 16 */
		$data2 = $this->ItemCount(['applno']);
    //   print_r($data2); die();
		//print_r($data['ItemCount']); die();
		if (count($data2) == 1){
			$countryoforigin = trim($data2[0]['cityDisc'], " ");
		}else{
			$isMANY = 0;
			for ($i = 0; $i < count($data2); $i++){
				if ($data2[0]['COCode'] != $data2[$i]['COCode']){
					$isMANY = 1;
				}
			}
			
			if ($isMANY) {
				$countryoforigin = "MANY";
			}
			else{
				$countryoforigin = trim($data2[0]['cityDisc'], " ");
			}
			
		}
		$this->SetXY(99, 77);
		$this->SetFont('Arial','',6);
		$this->Write(0, '16  Country of Origin');

		$this->SetXY(99, 76);
		$this->SetFont('Arial','',20);
		$this->Cell(108,8,'','B',0,'');

		$this->SetXY(102.5, 81);
		$this->SetFont('Arial','',8);
		//$this->Write(0, $data['CO_code']['cty_dsc']);
		$this->Write(0, $countryoforigin);

		/* END BOX 16 */

		/* Box 17 */

		$this->SetXY(158, 77);
		$this->SetFont('Arial','',6);
		$this->Write(0, '17  Country of Destination');

		$this->SetXY(158, 75);
		$this->SetFont('Arial','',8);
		$this->Cell(29,9,'','L',0,'C');

		$this->SetXY(162, 81);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'PHILIPPINES');

		/* END Box 17 */

		/* Box 20 */

		$this->SetXY(99, 85);
		$this->SetFont('Arial','',20);
		$this->Cell(108,6,'','B',0,'');

		$this->SetXY(99, 86);
		$this->SetFont('Arial','',6);
		$this->Write(0, '20  Terms of Delivery');

		$this->SetXY(104, 89);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['TDelivery']);

		/* END Box 20 */

		/* Box 22 */

		$this->SetXY(99, 93);
		$this->SetFont('Arial','',20);
		$this->Cell(108,6,'','B',0,'');

		$this->SetXY(99, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '22  F. Cur.');

		$this->SetXY(102, 96.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['CustCurr']);

		$this->SetXY(129, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Customs Value');

		$this->SetXY(132, 96.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, number_format(str_replace(',', '', $data['FIN_data']['CustomVal']), 2));

		/* END Box 22 */

		/* Box 23 */

		$this->SetXY(163, 91);
		$this->SetFont('Arial','',20);
		$this->Cell(21,8,'','LR',0,'');

		$this->SetXY(163, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '23  Exch Rate');

		$this->SetXY(163, 92);
		$this->SetFont('Arial','',8);
		$this->Cell(21,9,$data['FIN_data']['ExchRate'],0,0,'C');

		/* END Box 23*/

		/* Box 24 */

		$this->SetXY(184, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '24  Thru Bank');
		
		if($data['FIN_data']['WOBankCharge'] == 1){
			$TB1 = '2';
			$TB2 = 'X';
		}else{
			$TB1 = '';
			$TB2 = 'X';
		}

		$this->SetXY(188, 96.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $TB1);

		$this->SetXY(196, 94.5);
		$this->SetFont('Arial','',8);
		$this->Cell(11,4,$TB2,0,0,'C');

		$this->SetXY(196, 95);
		$this->SetFont('Arial','',8);
		$this->Cell(21,4,'','L',0,'');

		/* END Box 24*/

		/* Box 28 */

		$this->SetXY(99, 101);
		$this->SetFont('Arial','',6);
		$this->Write(0, '28  Financial and Banking Data -');

		$this->SetXY(143, 101);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Code');

		$this->SetXY(99, 105);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Terms of Payment');

		$this->SetXY(156, 101);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['BankCode']);
		
		$this->SetXY(127, 105);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Tpayment'].'  -  '.$data['FIN_data']['top_dsc']);

		$this->SetXY(99, 109.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Name');

		$this->SetXY(99, 114);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Branch');

		$this->SetXY(147, 114);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Ref Number:');

		$this->SetXY(127, 109.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['bnk_nam']);

		$this->SetXY(127, 114);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['BranchCode']);
	
		$this->SetXY(167, 114);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['BankRef']);

		/* END Box 28 */

		/* BOX 5, 6, 7, 9, 10, 11, 12, 13, 15, 16, 17, 20, 22, 23, 24, 28 END */

		/* BOX 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46 */

		$this->SetXY(5, 117);
		$this->SetFont('Times','',20);
		$this->Cell(202,72,'','LBR',0,'C');

		/* BOX 31 */
		
		$this->SetXY(5, 117);
		$this->SetFont('Times','',20);
		$this->Cell(126,40,'','BR',0,'C');

		$this->SetXY(5, 117);
		$this->SetFont('Times','',6);
		$this->MultiCell(15,40,'','R');

		$this->SetXY(5, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, '31  Packages');

		$this->SetXY(14, 123);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'and');

		$this->SetXY(7, 126);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Description');

		$this->SetXY(9, 129);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Goods');

		$this->SetXY(21, 119.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks and Numbers - Container No(s)');

		$this->SetXY(21, 124);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks & No');

		$this->SetXY(21, 127);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Packages');
		
		$this->SetXY(39, 124);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Marks1']);

		$this->SetXY(39, 127);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Marks2']);

		$this->SetXY(21, 131);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Number and Kind');

		$this->SetXY(39, 131);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['NoPack']);

		$this->SetXY(52, 131);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['PackCode']);

		$this->SetXY(59, 131);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_others']['pkg_dsc']);

		$this->SetXY(21, 134);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(39, 134.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Cont1']);

		$this->SetXY(61, 134.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Cont2']);

		$this->SetXY(83, 134.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Cont3']);

		$this->SetXY(105, 134.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Cont4']);

		$this->SetXY(21, 137);
		$this->SetFont('Arial','',8);
		$this->Write(0, substr($data['FIN_others']['tar_dsc'], 0, 85));
		//$this->MultiCell(110,2.5,substr($data['FIN_others']['tar_dsc'], 0, 100),0,'L');
		
		$this->SetXY(21, 140);
		$this->SetFont('Arial','',8);
		
		$hscod = $data['FIN_data']['HSCode'];
		$hscodt = $data['FIN_data']['HSCODE_TAR'];
		$spccod = $data['FIN_data']['SupUnit2'];
		$serverNames = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSCUSTSTDBs = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSCUSTSTDBs = sqlsrv_connect( $serverNames, $connectionINSCUSTSTDBs);
		$datas = array();
		$containerss = "SELECT DISTINCT b.spc_dsc FROM GBTARTAB a INNER JOIN GBSPECTAB b ON a.hs6_cod = b.hs6_cod AND a.tar_pr1 = b.tar_pr1 AND a.tar_pr2 = b.tar_pr2 WHERE a.hs6_cod + a.tar_pr1='$hscod' AND a.tar_pr2='$hscodt' AND b.spc_cod = '$spccod' AND a.EEA_EOV = ''";
		
		$stmt_datas = sqlsrv_query($conn_INSCUSTSTDBs, $containerss);
		if($stmt_datas == false)
		{
			 echo "Error in query preparation/execution.\n";
			 die( print_r( sqlsrv_errors(), true));
		}else{
			while( $rowss = sqlsrv_fetch_array( $stmt_datas, SQLSRV_FETCH_ASSOC))
			{	
				$datas['$containerss'] = $rowss;
			}
		}

		if(!empty($datas['$containerss'])){
			//$this->MultiCell(110,2.5,($datas['$containerss']['spc_dsc']),0,'L');
			$this->Write(0,substr($datas['$containerss']['spc_dsc'], 0, 80));
		}else{
			$this->Write(0,substr($data['FIN_others']['tar_dsc'], 85, 200));
		}
		
		$gdesc = $data['FIN_data']['GoodsDesc'] . ' ' . $data['FIN_data']['gDesc2'] . ' ' . $data['FIN_data']['gDesc3'];

		$this->SetXY(21, 142);
		$this->SetFont('Arial','',8);
		$this->MultiCell(110,2.5,strtoupper($gdesc),0,'L');

		$this->SetXY(21, 152.5);
		$this->SetFont('Arial','B',6);
		$this->Write(0, $data['FIN_data']['gDesc2']);

		$this->SetXY(21, 155);
		$this->SetFont('Arial','B',6);
		$this->Write(0, $data['FIN_data']['gDesc3']);

		/* END BOX 31 */ 

		/* Box 32 */

		$this->SetXY(113, 117);
		$this->SetFont('Times','',20);
		$this->Cell(94,8,'','LB',0,'C');

		$this->SetXY(113, 119);
		$this->SetFont('Arial','',6);
		$this->Write(0, '32  Item No.');

		$this->SetXY(120, 122);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['ItemNo']);

		/* END BOX 32 */

		/* Box 33 */

		$this->SetXY(131, 119);
		$this->SetFont('Arial','',6);
		$this->Write(0, '33  HS Code');

		$this->SetXY(133, 122.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['HSCode']);

		$this->SetXY(148, 122.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['HSCODE_TAR']);

		$this->SetXY(162, 119);
		$this->SetFont('Arial','',6);
		//$this->Write(0, 'Tar Spec');

		$this->SetXY(163, 122.5);
		$this->SetFont('Arial','',8);
		//$this->Write(0, $data['FIN_data']['TARSPEC'].'     '.$data['FIN_data']['HsRate'].'%');
		//$this->Write(0, $data['FIN_data']['TARSPEC']);
		
		$this->SetXY(178, 119);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Spec Code');

		$this->SetXY(180, 122.5);
		$this->SetFont('Arial','',8);
		//$this->Write(0, $data['FIN_data']['TARSPEC'].'     '.$data['FIN_data']['HsRate'].'%');
		$this->Write(0, $data['FIN_data']['SupUnit2']);

		/* END BOX 33 */

		/* Box 34 */

		$this->SetXY(131, 125);
		$this->SetFont('Times','',20);
		$this->Cell(76,8,'','B',0,'C');

		$this->SetXY(131, 127);
		$this->SetFont('Arial','',6);
		$this->Write(0, '34  C.O. Code');

		$this->SetXY(133, 130);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['COCode']);
		
		/* END BOX 34 */

		/* Box 35 */

		$this->SetXY(157, 125);
		$this->SetFont('Times','',20);
		$this->Cell(76,8,'','L',0,'C');

		$this->SetXY(157, 127);
		$this->SetFont('Arial','',6);
		$this->Write(0, '35  Item Gross Weight (kg)');
		
		$this->SetXY(157, 125);
		$this->SetFont('Arial','',8);
		$this->Cell(28,8,'','R',0,'R');

		$this->SetXY(157, 126.5);
		$this->SetFont('Arial','',8);
		$this->Cell(28,8,number_format($data['FIN_data']['ItemGWeight'],2),'',0,'R');

		/* END BOX 35 */

		/* Box 36 */

		$this->SetXY(185, 127);
		$this->SetFont('Arial','',6);
		$this->Write(0, '36  Pref');

		$this->SetXY(187, 130.5);
		$this->SetFont('Arial','',5);
		$this->Write(0, $data['FIN_data']['Pref']);

		/* END Box 36 */

		/* Box 37 */

		$this->SetXY(131, 133);
		$this->SetFont('Times','',20);
		$this->Cell(76,8,'','B',0,'C');

		$this->SetXY(131, 135);
		$this->SetFont('Arial','',6);
		$this->Write(0, '37  Procedure');

		$this->SetXY(133, 138.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['ProcDsc']);

		$this->SetXY(143, 138.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['ExtCode']);
		
		/* END BOX 37 */

		/* Box 38 */

		$this->SetXY(157, 133);
		$this->SetFont('Times','',20);
		$this->Cell(76,8,'','L',0,'C');

		$this->SetXY(157, 135);
		$this->SetFont('Arial','',6);
		$this->Write(0, '38  Item Net Weight (kg)');
		
		$this->SetXY(157, 133);
		$this->SetFont('Arial','',8);
		$this->Cell(28,8,'','R',0,'R');

		$this->SetXY(157, 134.5);
		$this->SetFont('Arial','',8);
		$this->Cell(28,8,number_format($data['FIN_data']['ItemNweight'],2),'',0,'R');
		
		/* END BOX 38 */

		/* Box 39 */

		$this->SetXY(185, 135);
		$this->SetFont('Arial','',6);
		$this->Write(0, '39  Qouta');

		$this->SetXY(187, 138.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['quo_cod']);

		/* END Box 39 */

		/* Box 40a */

		$this->SetXY(131, 141);
		$this->SetFont('Times','',20);
		$this->Cell(76,9,'','B',0,'C');

		$this->SetXY(173, 141);
		$this->SetFont('Times','',20);
		$this->Cell(10,9,'','L',0,'C');

		$this->SetXY(131, 143);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40a  AWB / BL');

		$this->SetXY(131, 146.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['AirBill']);
		
		/* END Box 40a */

		/* Box 40b */

		$this->SetXY(131, 141);
		$this->SetFont('Times','',20);
		$this->Cell(76,9,'','B',0,'C');

		$this->SetXY(173, 143);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40b  Previous Doc No.');
		
		$this->SetXY(173, 144.5);
		$this->SetFont('Arial','',8);
		// $this->Write(0,$data['FIN_data']['PrevDoc']);
		$this->MultiCell(34,2.5,$data['FIN_data']['PrevDoc'],0,'C');

		/* END Box 40b */

		/* Box 41 */
		$data['HScode_new'] = substr($data['FIN_data']['HSCode'],0,6);
		$data['HScode_new_TARPR1'] = substr($data['FIN_data']['HSCode'],6,8);
		$uom_Val =  SupVal($data['HScode_new'],$data['HScode_new_TARPR1'],$data['FIN_data']['HSCODE_TAR']);
		$this->SetXY(131, 150);
		$this->SetFont('Times','',20);
		$this->Cell(76,10,'','B',0,'C');

		$this->SetXY(131, 152);
		$this->SetFont('Arial','',6);
		$this->Write(0, '41  Suppl. Units');

		$this->SetXY(131, 156);
		$this->SetFont('Arial','B',8);
		if($uom_Val['uom_cod1'] == ''){
		$this->Write(0, '');
		}else{
		$data['HScode_new'] = substr($data['FIN_data']['HSCode'],0,6);
		$data['HScode_new_TARPR1'] = substr($data['FIN_data']['HSCode'],6,8);
		$cONS = get_CONs($data['FIN_data']['ApplNo'],$data['FIN_data']['ItemNo']);
		$this->Write(0,$uom_Val['uom_cod1'].'    '.number_format($cONS['SupVal1'],2),0,0,'R');
		/*$this->Write(0, $data['FIN_data']['SupVal1']);*/
		}
		/* END Box 41 */

		/* Box 42 */

		$this->SetXY(157, 152);
		$this->SetFont('Arial','',6);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		$this->SetXY(159, 151.5);
		$this->SetFont('Arial','B',8);
		$this->Cell(30,10,number_format($data['FIN_data']['InvValue'],2),0,0,'R');

		$this->SetXY(159, 150);
		$this->SetFont('Times','',20);
		$this->Cell(30,10,'','R',0,'C');
		
		/* END Box 42 */

		/* Box 43 */

		$this->SetXY(189, 152);
		$this->SetFont('Arial','',6);
		$this->Write(0, '43  V.M.');

		$this->SetXY(192, 156.5);
		$this->SetFont('Arial','B',8);
		$this->Write(0, $data['FIN_data']['ValMethodNum']);

		/* END Box 43 */

		/* BOX 44 */

		$this->SetXY(5, 157);
		$this->SetFont('Times','',6);
		$this->MultiCell(15,32,'','R');

		$this->SetXY(116, 157);
		$this->SetFont('Times','',6);
		$this->MultiCell(15,32,'','R');

		$this->SetXY(5, 160);
		$this->SetFont('Arial','',6);
		$this->Write(0, '44  Add Infos');

		$this->SetXY(5, 163);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Doc / Product');

		$this->SetXY(10, 166);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Certif. &');

		$this->SetXY(14.5, 169);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Aut');

		$this->SetXY(20, 159);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'OTHinEV :');

		$this->SetXY(40, 159);
		$this->SetFont('Arial','',6);
		$this->Write(0, $data['FIN_data']['OCharges']);

		$this->SetXY(53, 159);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'INSinFRT :');

		$this->SetXY(73, 159);
		$this->SetFont('Arial','',6);
		$this->Write(0, $data['FIN_data']['IFreight']);

		$this->SetXY(86, 159);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Fine :');

		// $this->SetXY(73, 159);
		// $this->SetFont('Arial','',6);
		// $this->Write(0, $data['FIN_data']['IFreight']);

		if ($data['max_rows'] > 1) {
			$CUD = array();
			$Cbs = array();
			$VAT = array();
			$Vbs = array();
			$Freight = array();
			$Insurance = array();
			$OtherCost = array();
			$Wharfage = array();
			$Arrastre = array();
			$InvValue = array();
			foreach ($RespIT as $key => $items) {
				if ($items['TAXCODE'] == 'CUD') {
					$CUDAMOUNT = $items['TAXAMT'];
					$CUD[] = $CUDAMOUNT;
				}

				if ($items['TAXCODE'] == 'Cbs') {
					$CUDBASE = $items['TAXAMT'];
					$Cbs[] = $CUDBASE;
				}

				if ($items['TAXCODE'] == 'VAT') {
					$VATAMOUNT = $items['TAXAMT'];
					$VAT[] = $VATAMOUNT;
				}

				if ($items['TAXCODE'] == 'Vbs') {
					$VATBASE = $items['TAXAMT'];
					$Vbs[] = $VATBASE;
				}

				if ($items['TAXCODE'] == 'EFR') {
					$EFRAMOUNT = $items['TAXAMT'];
					$Freight[] = $EFRAMOUNT;
				}

				if ($items['TAXCODE'] == 'INS') {
					$INSAMOUNT = $items['TAXAMT'];
					$Insurance[] = $INSAMOUNT;
				}

				if ($items['TAXCODE'] == 'OTH') {
					$OTHAMOUNT = $items['TAXAMT'];
					$OtherCost[] = $OTHAMOUNT;
				}

				if ($items['TAXCODE'] == 'IFR') {
					$IFRAMOUNT = $items['TAXAMT'];
					$Wharfage[] = $IFRAMOUNT;
				}

				if ($items['TAXCODE'] == 'DED') {
					$DEDAMOUNT = $items['TAXAMT'];
					$Arrastre[] = $DEDAMOUNT;
				}

				if ($items['TAXCODE'] == 'INV') {
					$INVAMOUNT = $items['TAXAMT'];
					$InvValue[] = $INVAMOUNT;
				}
			}
			$c = 0;
			foreach ($CUD as $key => $CUDs) {
				$c ++;
				$FIN_multi[$c - 1]['CUD'] = $CUDs;
			}

			$cb = 0;
			foreach ($Cbs as $key => $Cbss) {
				$cb ++;
				$FIN_multi[$cb - 1]['Cbs'] = $Cbss;
			}

			$v = 0;
			
			foreach ($VAT as $key => $VATs) {
				$v ++;
				$FIN_multi[$v - 1]['VAT'] = $VATs;
			}

			$vb = 0;
			foreach ($Vbs as $key => $Vbss) {
				$vb ++;
				$FIN_multi[$vb - 1]['Vbs'] = $Vbss;
			}

			$f = 0;
			foreach ($Freight as $key => $Freights) {
				$f ++;
				$FIN_multi[$f - 1]['Freight'] = $Freights;
			}
			

			$i = 0;
			foreach ($Insurance as $key => $Insurances) {
				$i ++;
				$FIN_multi[$i - 1]['Insurance'] = $Insurances;
			}

			$o = 0;
			foreach ($OtherCost as $key => $OtherCosts) {
				$o ++;
				$FIN_multi[$o - 1]['Other_cost'] = $OtherCosts;
			}

			$w = 0;
			foreach ($Wharfage as $key => $Wharfages) {
				$w ++;
				$FIN_multi[$w - 1]['Wharfage'] = $Wharfages;
			}

			$a = 0;
			foreach ($Arrastre as $key => $Arrastres) {
				$a ++;
				$FIN_multi[$a - 1]['Arrastre'] = $Arrastres;
			}

			$iv = 0;
			foreach ($InvValue as $key => $InvValues) {
				$iv ++;
				$FIN_multi[$iv - 1]['InvVal'] = $InvValues;
			}
			
			$tot_dutiable_value = 0;
			$freight_total  = 0;
			$inscost_total   = 0;
			$othercost_total  = 0;
			$invval_total   = 0;
			$custom = 0;

			foreach ($FIN_multi as $key => $FIN_multis_total) {
				if($FIN_multis_total['InvCurr'] == 'PHP' && $FIN_multis_total['CustCurr'] == 'PHP' && $FIN_multis_total['FreightCurr'] == 'PHP' && $FIN_multis_total['InsCurr'] == 'PHP' && $FIN_multis_total['OtherCurr'] == 'PHP'){
					$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost']))),2);
					$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']))),2);
					$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']))),2);
					$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue'])),2);

					$FIO_total = $freight_total + $inscost_total + $othercost_total;
					$tot_dutiable_value += $FIO_total + $invval_total;
					$custom = round((str_replace(',', '', $FIN_multis_total['CustomVal'])),2);

				}else{
					//06062024:SPagara: Roundup update
					//$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost'])) * $FIN_multis_total['FExchRate']),2);
					//$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost'])) * $FIN_multis_total['IExchRate']),2);
					//$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost'])) * $FIN_multis_total['OExchRate']),2);
					
					$freight_total = Round(((Round(str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal']) * str_replace(',', '', $FIN_multis_total['FreightCost']), 2)) * $FIN_multis_total['FExchRate']),2, PHP_ROUND_HALF_UP);
					$inscost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']), 2) * $FIN_multis_total['IExchRate']),2, PHP_ROUND_HALF_UP);
					$othercost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']), 2) * $FIN_multis_total['OExchRate']),2, PHP_ROUND_HALF_UP);
					$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue']) * $FIN_multis_total['ExchRate']),2);
					
					
					$FIO_total = $freight_total + $inscost_total + $othercost_total;

					$tot_dutiable_value += $FIO_total + $invval_total;
					
				
					$custom = round((str_replace(',', '', $FIN_multis_total['CustomVal']) * $FIN_multis_total['ExchRate']),2);
				}
			}

			if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
				if ($custom > 10000) {
					$this->Image('DEMINIMIS.png',4,30,200);
				}
			}

			foreach ($FIN_multi as $key => $FIN_multis) {
				if ($FIN_multis['ItemNo']== 1) {
					if($FIN_multis['InvCurr'] == 'PHP' && $FIN_multis['CustCurr'] == 'PHP' && $FIN_multis['FreightCurr'] == 'PHP' && $FIN_multis['InsCurr'] == 'PHP' && $FIN_multis['OtherCurr'] == 'PHP'){
						if (@$FIN_multis['Freight'] != null) {
							$freight = $FIN_multis['Freight'];
						}else{
							$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost']))),2);
						}

						if (@$FIN_multis['Insurance'] != null) {
							$inscost = $FIN_multis['Insurance'];
						}else{
							$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']))),2);
						}

						if (@$FIN_multis['Other_cost'] != null) {
							$othercost = $FIN_multis['Other_cost'];
						}else{
							$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']))),2);
						}
 
						if (@$FIN_multis['InvVal'] != null) {
							$invval = $FIN_multis['InvVal'];
						}else{
							$invval = round((str_replace(',', '', $FIN_multis['InvValue'])),2);
						}

						$FIO = $freight + $inscost + $othercost;

						if (@$FIN_multis['Cbs'] != null) {
							$dutiable_value = $FIN_multis['Cbs'];
						}else{
							$dutiable_value = $FIO + $invval;
						}

					}else{
						if (@$FIN_multis['Freight'] != null) {
							$freight = $FIN_multis['Freight'];
						}else{
							//06062024: SPagara: Roundup 
							//$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost'])) * $FIN_multis['FExchRate']),2);
							$freight = Round(((Round(str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal']) * str_replace(',', '', $FIN_multis['FreightCost']), 3)) * $FIN_multis['FExchRate']),2, PHP_ROUND_HALF_UP);
						}

						if (@$FIN_multis['Insurance'] != null) {
							$inscost = $FIN_multis['Insurance'];
						}else{
							//06062024: SPagara: Roundup 
							//$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost'])) * $FIN_multis['IExchRate']),2);
							$inscost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']), 3) * $FIN_multis['IExchRate']),2, PHP_ROUND_HALF_UP);
						}

						if (@$FIN_multis['Other_cost'] != null) {
							$othercost = $FIN_multis['Other_cost'];
						}else{
							//06062024: SPagara: Roundup 
							//$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost'])) * $FIN_multis['OExchRate']),2);
							$othercost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']), 3) * $FIN_multis['OExchRate']),2, PHP_ROUND_HALF_UP);
						}

						if (@$FIN_multis['InvVal'] != null) {
							$invval = $FIN_multis['InvVal'];
						}else{
							$invval = round((str_replace(',', '', $FIN_multis['InvValue']) * $FIN_multis['ExchRate']),2);
						}

						$FIO = $freight + $inscost + $othercost;

						if (@$FIN_multis['Cbs'] != null) {
							$dutiable_value = $FIN_multis['Cbs'];
						}else{
							$dutiable_value = $FIO + $invval;
						}

						
					}

					/* Start WHarfage and Arrastre Computation */

					$whar = (str_replace(',', '', $FIN_multis['WharCost']));
					$arras = (str_replace(',', '', $FIN_multis['ArrasCost']));
				
					/* Start Wharfage Computation */
					$whar = round((str_replace(',', '', $FIN_multis['InvValue'])/str_replace(',', '', $FIN_multis['CustomVal']) * $whar),2);

					// if (in_array($FIN_multis['ExtCode'] ,$ext)) {
					// 	$ssw = 17;
					// }else{
					// 	$ssw = 34;
					// }

					// $whar_comp2 = round((($FIN_multis['ItemGWeight']/1000) * $ssw), 2);
					
					/* End Wharfage Computation */

					/* Start Arrastre Computation */
					$arras = round((str_replace(',', '', $FIN_multis['InvValue'])/str_replace(',', '', $FIN_multis['CustomVal']) * $arras),2);

					// if (in_array($FIN_multis['ExtCode'] ,$ext)) {
					// 	$ssa = 8;
					// }else{
					// 	$ssa = 110;
					// }

					// $arras_comp2 = round((($FIN_multis['ItemGWeight']/1000) * $ssa), 2);

					/* End Arrastre Computation */

					/* End WHarfage and Arrastre Computation */
				}

				/* Start Total Values */
				if($FIN_multis['InvCurr'] == 'PHP' && $FIN_multis['CustCurr'] == 'PHP' && $FIN_multis['FreightCurr'] == 'PHP' && $FIN_multis['InsCurr'] == 'PHP' && $FIN_multis['OtherCurr'] == 'PHP'){
					if (@$FIN_multis['Freight'] != null) {
						$freight_total += $FIN_multis['Freight'];
					}else{
						$freight_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost']))),2);
					}

					if (@$FIN_multis['Insurance'] != null) {
						$inscost_total += $FIN_multis['Insurance'];
					}else{
						$inscost_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']))),2);
					}

					if (@$FIN_multis['Other_cost'] != null) {
						$othercost_total += $FIN_multis['Other_cost'];
					}else{
						$othercost_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']))),2);
					}

					if (@$FIN_multis['InvVal'] != null) {
						$invval_total += $FIN_multis['InvVal'];
					}else{
						$invval_total += round((str_replace(',', '', $FIN_multis['InvValue'])),2);
					}

					$FIO_total = $freight_total + $inscost_total + $othercost_total;

				}else{
					if (@$FIN_multis['Freight'] != null) {
						$freight_total += $FIN_multis['Freight'];
					}else{
						//06062024: SPagara: Roundup 
						//$freight_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost'])) * $FIN_multis['FExchRate']),2);
						$freight_total += Round(((Round(str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal']) * str_replace(',', '', $FIN_multis['FreightCost']), 2)) * $FIN_multis['FExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['Insurance'] != null) {
						$inscost_total += $FIN_multis['Insurance'];
					}else{
						//06062024: SPagara: Roundup 
						//$inscost_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost'])) * $FIN_multis['IExchRate']),2);
						$inscost_total += Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']), 2) * $FIN_multis['IExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['Other_cost'] != null) {
						$othercost_total += $FIN_multis['Other_cost'];
					}else{
						//06062024: SPagara: Roundup 
						//$othercost_total += round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost'])) * $FIN_multis['OExchRate']),2);
						$othercost_total += Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']), 2) * $FIN_multis['OExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['InvVal'] != null) {
						$invval_total += $FIN_multis['InvVal'];
					}else{
						$invval_total += round((str_replace(',', '', $FIN_multis['InvValue']) * $FIN_multis['ExchRate']),2);
					}

					$FIO_total = $freight_total + $inscost_total + $othercost_total;

				}

				/* End Total Values */
			}
		}else{
			$CUD = array();
			$Cbs = array();
			$VAT = array();
			$Vbs = array();
			$Freight = array();
			$Insurance = array();
			$OtherCost = array();
			$Wharfage = array();
			$Arrastre = array();
			$InvValue = array();

			$tot_dutiable_value = 0;
			foreach ($FIN_multi as $key => $FIN_multis_total) {
				if($FIN_multis_total['InvCurr'] == 'PHP' && $FIN_multis_total['CustCurr'] == 'PHP' && $FIN_multis_total['FreightCurr'] == 'PHP' && $FIN_multis_total['InsCurr'] == 'PHP' && $FIN_multis_total['OtherCurr'] == 'PHP'){
					$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost']))),2);
					$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']))),2);
					$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']))),2);
					$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue'])),2);

					$FIO_total = $freight_total + $inscost_total + $othercost_total;
					$tot_dutiable_value += $FIO_total + $invval_total;
					
				}else{
					//06062024: SPagara: Roundup 
					//$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost'])) * $FIN_multis_total['FExchRate']),2);
					//$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost'])) * $FIN_multis_total['IExchRate']),2);
					//$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost'])) * $FIN_multis_total['OExchRate']),2);

					$freight_total = Round(((Round(str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal']) * str_replace(',', '', $FIN_multis_total['FreightCost']), 2)) * $FIN_multis_total['FExchRate']),2, PHP_ROUND_HALF_UP);
					$inscost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']), 2) * $FIN_multis_total['IExchRate']),2, PHP_ROUND_HALF_UP);
					$othercost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']), 2) * $FIN_multis_total['OExchRate']),2, PHP_ROUND_HALF_UP);

					$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue']) * $FIN_multis_total['ExchRate']),2);

					$FIO_total = $freight_total + $inscost_total + $othercost_total;

					$tot_dutiable_value += $FIO_total + $invval_total;
				}
			}
			//print_r($freight_total); die();
			foreach ($RespIT as $key => $items) {
				if ($items['TAXCODE'] == 'CUD') {
					$CUDAMOUNT = $items['TAXAMT'];
					$CUD[] = $CUDAMOUNT;
				}

				if ($items['TAXCODE'] == 'Cbs') {
					$CUDBASE = $items['TAXAMT'];
					$Cbs[] = $CUDBASE;
				}

				if ($items['TAXCODE'] == 'VAT') {
					$VATAMOUNT = $items['TAXAMT'];
					$VAT[] = $VATAMOUNT;
				}

				if ($items['TAXCODE'] == 'Vbs') {
					$VATBASE = $items['TAXAMT'];
					$Vbs[] = $VATBASE;
				}

				if ($items['TAXCODE'] == 'EFR') {
					$EFRAMOUNT = $items['TAXAMT'];
					$Freight[] = $EFRAMOUNT;
				}

				if ($items['TAXCODE'] == 'INS') {
					$INSAMOUNT = $items['TAXAMT'];
					$Insurance[] = $INSAMOUNT;
				}

				if ($items['TAXCODE'] == 'OTH') {
					$OTHAMOUNT = $items['TAXAMT'];
					$OtherCost[] = $OTHAMOUNT;
				}

				if ($items['TAXCODE'] == 'IFR') {
					$IFRAMOUNT = $items['TAXAMT'];
					$Wharfage[] = $IFRAMOUNT;
				}

				if ($items['TAXCODE'] == 'DED') {
					$DEDAMOUNT = $items['TAXAMT'];
					$Arrastre[] = $DEDAMOUNT;
				}

				if ($items['TAXCODE'] == 'INV') {
					$INVAMOUNT = $items['TAXAMT'];
					$InvValue[] = $INVAMOUNT;
				}
			} 
			$c = 0;
			foreach ($CUD as $key => $CUDs) {
				$c ++;
				$FIN_multi[$c - 1]['CUD'] = $CUDs;
			}

			$cb = 0;
			foreach ($Cbs as $key => $Cbss) {
				$cb ++;
				$FIN_multi[$cb - 1]['Cbs'] = $Cbss;
			}

			$v = 0;
			foreach ($VAT as $key => $VATs) {
				$v ++;
				$FIN_multi[$v - 1]['VAT'] = $VATs;
			}

			$vb = 0;
			foreach ($Vbs as $key => $Vbss) {
				$vb ++;
				$FIN_multi[$vb - 1]['Vbs'] = $Vbss;
			}

			$f = 0;
			foreach ($Freight as $key => $Freights) {
				$f ++;
				$FIN_multi[$f - 1]['Freight'] = $Freights;
			}

			$i = 0;
			foreach ($Insurance as $key => $Insurances) {
				$i ++;
				$FIN_multi[$i - 1]['Insurance'] = $Insurances;
			}

			$o = 0;
			foreach ($OtherCost as $key => $OtherCosts) {
				$o ++;
				$FIN_multi[$o - 1]['Other_cost'] = $OtherCosts;
			}

			$w = 0;
			foreach ($Wharfage as $key => $Wharfages) {
				$w ++;
				$FIN_multi[$w - 1]['Wharfage'] = $Wharfages;
			}

			$a = 0;
			foreach ($Arrastre as $key => $Arrastres) {
				$a ++;
				$FIN_multi[$a - 1]['Arrastre'] = $Arrastres;
			}

			$iv = 0;
			foreach ($InvValue as $key => $InvValues) {
				$iv ++;
				$FIN_multi[$iv - 1]['InvVal'] = $InvValues;
			}



			if($data['FIN_data']['InvCurr'] == 'PHP' && $data['FIN_data']['CustCurr'] == 'PHP' && $data['FIN_data']['FreightCurr'] == 'PHP' && $data['FIN_data']['InsCurr'] == 'PHP' && $data['FIN_data']['OtherCurr'] == 'PHP'){
				if (@$FIN_multi[0]['Freight'] != null) {
					$freight = $FIN_multi[0]['Freight'];
				}else{
					$freight = round(str_replace(',', '', $data['FIN_data']['FreightCost']),2);
				}

				if (@$FIN_multi[0]['Insurance'] != null) {
					$inscost = $FIN_multi[0]['Insurance'];
				}else{
					$inscost = round(str_replace(',', '', $data['FIN_data']['InsCost']),2);
				}

				if (@$FIN_multi[0]['Other_cost'] != null) {
					$othercost = $FIN_multi[0]['Other_cost'];
				}else{
					$othercost = round(str_replace(',', '', $data['FIN_data']['OtherCost']),2);
				}

				if (@$FIN_multi[0]['InvVal'] != null) {
					$invval = $FIN_multi[0]['InvVal'];
				}else{
					$invval = round((str_replace(',', '', $data['FIN_data']['InvValue'])),2);
				}
				
				$FIO = $freight + $inscost + $othercost;

				if (@$FIN_multi[0]['Cbs'] != null) {
					$dutiable_value = $FIN_multi[0]['Cbs'];
				}else{
					$dutiable_value = $FIO + $invval;
				}


			}else{
				if (@$FIN_multi[0]['Freight'] != null) {
					$freight = $FIN_multi[0]['Freight'];
				}else{
					$freight = round((str_replace(',', '', $data['FIN_data']['FreightCost']) * $data['FIN_data']['FExchRate']),2);
				}

				if (@$FIN_multi[0]['Insurance'] != null) {
					$inscost = $FIN_multi[0]['Insurance'];
				}else{
					$inscost = round((str_replace(',', '', $data['FIN_data']['InsCost']) * $data['FIN_data']['IExchRate']),2);
				}

				if (@$FIN_multi[0]['Other_cost'] != null) {
					$othercost = $FIN_multi[0]['Other_cost'];
				}else{
					$othercost = round((str_replace(',', '', $data['FIN_data']['OtherCost']) * $data['FIN_data']['OExchRate']),2);
				}

				if (@$FIN_multi[0]['InvVal'] != null) {
					$invval = $FIN_multi[0]['InvVal'];
				}else{
					$invval = round((str_replace(',', '', $data['FIN_data']['InvValue']) * $data['FIN_data']['ExchRate']),2);
				}


				$FIO = $freight + $inscost + $othercost;
			
				if (@$FIN_multi[0]['Cbs'] != null) {
					$dutiable_value = $FIN_multi[0]['Cbs'];
				}else{
					$dutiable_value = $FIO + $invval;
				}
			}

			/* Start WHarfage and Arrastre Computation */

			
			if (empty($data['FIN_data']['WharCost'])) {
				$whar = 0;
			}else{
				$whar = (str_replace(',', '', $data['FIN_data']['WharCost']));
			}

			if (empty($data['FIN_data']['WharCost'])) {
				$arras = 0;
			}else{
				$arras = (str_replace(',', '', $data['FIN_data']['ArrasCost']));
			}
			
		

			/* End WHarfage and Arrastre Computation */


		}

		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$wharfage = $whar;
			$arrastre = $arras;
			
		}else{
			if (@$FIN_multi[0]['Wharfage'] != null) {
				$wharfage = $FIN_multi[0]['Wharfage'];
			}

			if (@$FIN_multi[0]['Arrastre'] != null) {
				$arrastre = $FIN_multi[0]['Arrastre'];
			}
		}
		
		

		


		if ($data['FIN_data']['OffClearance'] == 'Ninoy Aquino Intl Airport ') {
			$wharfage = 0;
			$arrastre = 0;
		}

		$this->SetXY(25, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, number_format($freight,2));
		
		$this->SetXY(46, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '+ '.number_format($inscost,2));

		$this->SetXY(74, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '+ '.number_format(str_replace(',', '', $othercost),2));

		$this->SetXY(95, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '+ '.number_format(str_replace(',', '', $wharfage),2));

		$this->SetXY(113, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '- '.number_format(str_replace(',', '', $arrastre),2));

		$this->SetXY(20, 171);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'A.D.');

		$this->SetXY(20, 186);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Invoice No. :');

		$this->SetXY(33, 185);
		$this->SetFont('Arial','',8);
		//$this->Write(0, $data['FIN_data']['InvNo']);
		$this->MultiCell(65,2, $data['FIN_data']['InvNo'],0,'L');

		$this->SetXY(100, 186);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Dump Bond :');

		$this->SetXY(113, 186);
		$this->SetFont('Arial','',6);
		$this->Write(0, $data['FIN_data']['DumpBond']);
		
		/* END BOX 44 */

		/* Box 45 */

		$this->SetXY(131, 164);
		$this->SetFont('Times','',20);
		$this->Cell(76,10,'','B',0,'C');

		$this->SetXY(127, 164);
		$this->SetFont('Times','',20);
		$this->Cell(30,25,'','R',0,'C');

		$this->SetXY(137, 160);
		$this->SetFont('Times','',20);
		$this->Cell(30,14,'','R',0,'C');

		$this->SetXY(153.5, 162.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'A.I. Code');
		
		$this->SetXY(158, 169);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['TARSPEC']);

		$this->SetXY(167, 162.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '45  Adjustment');

		$this->SetXY(167, 163);
		$this->SetFont('Arial','B',8);
		$this->Cell(40,12,$data['FIN_data']['Adjustment'],0,0,'C');

		/* END Box 45 */

		/* Box 46 */
		$this->SetXY(157, 176);
		$this->SetFont('Arial','',6);
		$this->Write(0, '46  Dutiable Value (PHP)');

		$this->SetXY(167, 176);
		$this->SetFont('Arial','B',8);

		//$dutiable_value = round($dutiable_value, 2);
		$dutiable_value = round($dutiable_value, 2, PHP_ROUND_HALF_UP);
		$this->Cell(40,12,number_format($dutiable_value, 2),0,0,'R');

		/* END Box 46 */
		
		/* END BOX 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46 */
		
		/* BOX 47 */

		/* Start Response Taxes */
		$TAXAMT1 = null;
		$TAXAMT1_1 = null;
		$TAXAMT2 = null;
		$TAXAMT2_2 = null;
		$TAXAMT3 = null;
		$TAXAMT4 = null;
		$TAXAMT5 = null;
		$TAXAMT6 = null;
		$TAXAMT7 = null;
		$TAXAMT8 = null;
		$TAXAMT9 = null;
		$TAXAMT10 = null;
		$TAXAMT11 = null;
		$TAXAMT12 = null;
		$TAXAMT13 = null;
		$TAXAMT14 = null;
		$TAXAMT15 = null;
		$TAXCODE1 = null;
		$TAXCODE1_1 = null;
		$TAXCODE2 = null;
		$TAXCODE2_1 = null;
		$TAXCODE3 = null;
		$TAXCODE4 = null;
		$TAXCODE5 = null;
		$TAXCODE6 = null;
		$TAXCODE7 = null;
		$TAXCODE8 = null;
		$TAXCODE9 = null;
		$TAXCODE10 = null;
		$TAXCODE11 = null;
		$TAXCODE12 = null;
		$TAXCODE13 = null;
		$TAXCODE14 = null;
		$TAXCODE15 = null;
	
		foreach ($RespIT as $key => $item_taxes) {
		
			if ($item_taxes['ITEMNO'] == 1) {
				$TAXAMTS = array(
					$item_taxes['TAXCODE'] => $item_taxes['TAXAMT']
				);

				if (array_key_exists('CUD', $TAXAMTS)) {
					$TAXCODE1 = 'CUD';
					$TAXAMT1 = $TAXAMTS['CUD'];
				}

				if (array_key_exists('Cbs', $TAXAMTS)) {
					$TAXCODE1_1 = 'Cbs';
					$TAXAMT1_1 = $TAXAMTS['Cbs'];
				}

				if (array_key_exists('VAT', $TAXAMTS)) {
					$TAXCODE2 = 'VAT';
					$TAXAMT2 = $TAXAMTS['VAT'];
				}

				if (array_key_exists('Vbs', $TAXAMTS)) {
					$TAXCODE2_2 = 'Vbs';
					$TAXAMT2_2 = $TAXAMTS['Vbs'];
				}

				if (array_key_exists('EXC', $TAXAMTS)) {
					$TAXCODE3 = 'EXC';
					$TAXAMT3 = $TAXAMTS['EXC'];
				}

				if (array_key_exists('AVT', $TAXAMTS)) {
					$TAXCODE4 = 'AVT';
					$TAXAMT4 = $TAXAMTS['AVT'];
				}

				if (array_key_exists('CSD', $TAXAMTS)) {
					$TAXCODE5 = 'CSD';
					$TAXAMT5 = $TAXAMTS['CSD'];
				}

				if (array_key_exists('FIN', $TAXAMTS)) {
					$TAXCODE6 = 'FIN';
					$TAXAMT6 = $TAXAMTS['FIN'];
				}

				if (array_key_exists('DPD', $TAXAMTS)) {
					$TAXCODE7 = 'DPD';
					$TAXAMT7 = $TAXAMTS['DPD'];
				}
				
				if (array_key_exists('IPF', $TAXAMTS)) {
					$TAXCODE8 = 'IPF';
					$TAXAMT8 = $TAXAMTS['IPF'];
				}

				if (array_key_exists('SGD', $TAXAMTS)) {
					$TAXCODE9 = 'SGD';
					$TAXAMT9 = $TAXAMTS['SGD'];
				}

				if (array_key_exists('D&F', $TAXAMTS)) {
					$TAXCODE10 = 'D&amp;F';
					$TAXAMT10 = $TAXAMTS['D&F'];
				}

				if (array_key_exists('FF', $TAXAMTS)) {
					$TAXCODE11 = 'FF';
					$TAXAMT11 = $TAXAMTS['FF'];
				}

				if (array_key_exists('PSI', $TAXAMTS)) {
					$TAXCODE12 = 'PSI';
					$TAXAMT12 = $TAXAMTS['PSI'];
				}

				if (array_key_exists('TSF', $TAXAMTS) || array_key_exists('CTF', $TAXAMTS)) {
					if(array_key_exists('TSF', $TAXAMTS)){
						$TAXCODE13 = 'TSF';
						$TAXAMT13 = $TAXAMTS['TSF'];
					}elseif(array_key_exists('CTF', $TAXAMTS)){
						$TAXCODE13 = 'CTF';
						$TAXAMT13 = $TAXAMTS['CTF'];
					}else{
					//none
					}
					
				}
				
				if (array_key_exists('SGL', $TAXAMTS)) {
					$TAXCODE14 = 'SGL';
					$TAXAMT14 = $TAXAMTS['SGL'];
				}
				
				if (array_key_exists('CSF', $TAXAMTS)) {
					$TAXCODE15 = 'CSF';
					$TAXAMT15 = $TAXAMTS['CSF'];
				}
			}
		}

		//07042024:SPagara: SGL for 4ES
		$CustomVal1 = str_replace(',', '', $data['FIN_data']['CustomVal']);

		//07042024:SPagara: SGL for 4ES
		if ($data['FIN_data']['MDec']== '4ES') {
		if ($data['FIN_data']['InvCurr'] == "USD"){
			if ((float)$CustomVal1 <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}elseif ($data['FIN_data']['InvCurr'] == "PHP"){
			$NewVal = (float)$CustomVal1 /$data['ExchRate_queryUSD']['RAT_EXC'];
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}else{
			$NewVal = ((float)$CustomVal1 * $data['FIN_data']['ExchRate']) / $data['ExchRate_queryUSD']['RAT_EXC'];
			//print_r($NewVal); die();
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}
	}

		
		$TAXAMT3 = null;
		$TAXAMT4 = null;
		$TAXAMT5 = null;
		$TAXAMT6 = null;
		$TAXAMT7 = null;
		$TAXAMT11 = null;
		$TAXCODE3 = null;
		$TAXCODE4 = null;
		$TAXCODE5 = null;
		$TAXCODE6 = null;
		$TAXCODE7 = null;
		$TAXCODE11 = null;
		$RECPNO  = null;
		foreach ($RespGT as $key => $global_taxes) {
			$TAXAMTS = array(
				$global_taxes['TAXCODE'] => $global_taxes['TAXAMT']
			);

			if (array_key_exists('EXC', $TAXAMTS)) {
				$TAXCODE3 = 'EXC';
				$TAXAMT3 = $TAXAMTS['EXC'];
			}

			if (array_key_exists('AVT', $TAXAMTS)) {
				$TAXCODE4 = 'AVT';
				$TAXAMT4 = $TAXAMTS['AVT'];
			}

			if (array_key_exists('CSD', $TAXAMTS)) {
				$TAXCODE5 = 'CSD';
				$TAXAMT5 = $TAXAMTS['CSD'];
			}

			if (array_key_exists('FIN', $TAXAMTS)) {
				$TAXCODE6 = 'FIN';
				$TAXAMT6 = $TAXAMTS['FIN'];
			}

			if (array_key_exists('DPD', $TAXAMTS) || array_key_exists('FMF', $TAXAMTS)) {
				if (array_key_exists('DPD', $TAXAMTS)){
					$TAXCODE7 = 'DPD';
					$TAXAMT7 = $TAXAMTS['DPD'];
				}elseif (array_key_exists('FMF', $TAXAMTS)){
					$TAXCODE7 = 'FMF';
					$TAXAMT7 = $TAXAMTS['FMF'];
				}else{
				//none
				}
			}

			if (array_key_exists('FF', $TAXAMTS)) {
				$TAXCODE11 = 'FF';
				$TAXAMT11 = $TAXAMTS['FF'];
			}
		} 

		/* End Response Taxes */

		$this->SetXY(5, 189);
		$this->SetFont('Times','',20);
		$this->Cell(108,45,'','LBR',0,'C');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(80,45,'','LR');

		$this->SetXY(5, 191);
		$this->SetFont('Arial','',6);
		$this->Write(0, '47  Calculation');

		$this->SetXY(10, 194);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Taxes');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',20);
		$this->Cell(93,5,'','B',0,'C');

		$this->SetXY(20, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(37, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tax Base');

		$this->SetXY(64, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Rate');

		$this->SetXY(89, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(104, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MP');



		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$CUDBASE = number_format($dutiable_value, 2);
		}else{
			if (!empty($TAXAMT1_1)) {
				$CUDBASE = number_format($dutiable_value, 2);
			}else{
				$CUDBASE = number_format($TAXAMT1_1, 2);
			}
		}
		
		$this->SetXY(20, 198);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'CUD');

		$this->SetXY(32, 196.5);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,$CUDBASE,0,0,'R');
		
		$this->SetXY(60, 196.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,$data['FIN_data']['HsRate'].' %',0,0,'C');


		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$CUDAMOUNT = $dutiable_value * ($data['FIN_data']['HsRate']/100);
		}else{
			$CUDAMOUNT = $TAXAMT1;
		}

		$this->CUD_C = number_format($CUDAMOUNT,2);

		if ($data['FIN_data']['Stat'] == 'ER') {
			$CUDAMOUNT = $dutiable_value * ($data['FIN_data']['HsRate']/100);
		}

		if (empty($CUDAMOUNT)) {
			$CUDAMOUNT = $dutiable_value * ($data['FIN_data']['HsRate']/100);
		}

		
		
		$this->SetXY(75, 196.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,number_format($CUDAMOUNT, 2),0,0,'R');
		
		$this->SetXY(83, 196.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'0',0,0,'R');

		$this->SetXY(20, 202);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'VAT');
	

		/* Start Computations for VAT */
		

		/* Start Broker Fee */
		$BrokerFee = 0;
		if($tot_dutiable_value >= 0 && $tot_dutiable_value <= 10000){$BrokerFee = 1300;}
		if($tot_dutiable_value >= 10001 && $tot_dutiable_value <= 20000){$BrokerFee = 2000;}
		if($tot_dutiable_value >= 20001 && $tot_dutiable_value <= 30000){$BrokerFee = 2700;}
		if($tot_dutiable_value >= 30001 && $tot_dutiable_value <= 40000){$BrokerFee = 3300;}
		if($tot_dutiable_value >= 40001 && $tot_dutiable_value <= 50000){$BrokerFee = 3600;}
		if($tot_dutiable_value >= 50001 && $tot_dutiable_value <= 60000){$BrokerFee = 4000;}
		if($tot_dutiable_value >= 60001 && $tot_dutiable_value <= 100000){$BrokerFee = 4700;}
		if($tot_dutiable_value >= 100001 && $tot_dutiable_value <= 200000){$BrokerFee = 5300;}
		if($tot_dutiable_value >= 200001){$BrokerFee = round(((($tot_dutiable_value - 200000) * 0.00125) + 5300),2);}
		/* End Broker Fee */

		/* Start IPF */
		$IPF = 0;
		/*06062024: SPagara: update for IPF
		if(round($tot_dutiable_value) >= 0 && round($tot_dutiable_value) <= 25000){$IPF = 250;}
		if(round($tot_dutiable_value) >= 25001 && round($tot_dutiable_value) <= 50000){$IPF = 500;}
		if(round($tot_dutiable_value) >= 50001 && round($tot_dutiable_value) <= 250000){$IPF = 750;}
		if(round($tot_dutiable_value) >= 250001 && round($tot_dutiable_value) <= 500000){$IPF = 1000;}
		if(round($tot_dutiable_value) >= 500001 && round($tot_dutiable_value) <= 750000){$IPF = 1500;}
		if(round($tot_dutiable_value) >= 750001 && round($tot_dutiable_value) <= 999999999999){$IPF = 2000;}*/

		if(round($tot_dutiable_value) >= 0 && round($tot_dutiable_value) <= 25000){$IPF = 250;}
		if(round($tot_dutiable_value) > 25000 && round($tot_dutiable_value) <= 50000){$IPF = 500;}
		if(round($tot_dutiable_value) > 50000 && round($tot_dutiable_value) <= 250000){$IPF = 750;}
		if(round($tot_dutiable_value) > 250000 && round($tot_dutiable_value) <= 500000){$IPF = 1000;}
		if(round($tot_dutiable_value) > 500000 && round($tot_dutiable_value) <= 750000){$IPF = 1500;}
		if(round($tot_dutiable_value) > 750000){$IPF = 2000;}		

		if (($data['FIN_data']['MDec'] == '8ZN') || ($data['FIN_data']['MDec'] == '8PP') || ($data['FIN_data']['MDec'] == '8PE') || ($data['FIN_data']['MDec'] == '8ZE')){
			$IPF = 250;
		}
		/* End IPF*/

		/* Start Doc Fee */
		// if ($data['max_rows'] > 1) {
		// 	$DOCFEE = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * 265),2);
		// }else{
		// 	$DOCFEE = 265;
		// }

		if ($data['FIN_data']['MDec'] != 'IES' && $data['FIN_data']['MDec'] != 'IE') {
			if ($data['max_rows'] > 1) {
				//$DOCFEE = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * 265),2);
				//$DOCFEE = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * 280),2);
				//$DOCFEE = round((280/$data['max_rows']),2);
				$DOCFEE = round((130/$data['max_rows']),2);
			}else{
				//$DOCFEE = 265;
				//$DOCFEE = 280;
				$DOCFEE = round((130/$data['max_rows']),2);	
			}
		}else{
			//$DOCFEE = 30 / $data['max_rows'];
			//$DOCFEE = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * 30),2);
			$DOCFEE = round((130/$data['max_rows']),2);
		}

		/* End Doc Fee */

		/* Start Bank Charge */
		if($data['FIN_data']['WOBankCharge'] == 1){
			$BANKCHARGE = 0;
		}else{
			$BANKCHARGE = round(($dutiable_value * 0.00125),2);
		}
		/* End Bank Charge */

		/* Start VAT Computation */
		if ($data['max_rows'] > 1) {
			//$BrokerFee = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * $BrokerFee),2);
			//$IPF = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * $IPF),2);
			$BrokerFee = round(($BrokerFee/$data['max_rows']),2);
			$IPF = round(($IPF/$data['max_rows']),2);
		}else{
			$BrokerFee = round($BrokerFee, 2);
		}

		//if ($data['FIN_data']['MDec'] == '7' || $data['FIN_data']['MDec'] == '7T') {
			//$IPF = 250;
		//}

		if (($data['FIN_data']['MDec'] == 'IES') || ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4')) {
			//06062024: SPagara: removed IPF for IES
			// $IPF = 0;

			//$BrokerFee = round(((str_replace(',', '', $data['FIN_data']['InvValue'])/str_replace(',', '', $data['FIN_data']['CustomVal'])) * 700),2);
			$BrokerFee = round((700/$data['max_rows']),2);
		}

		// TAX BASE EXC Carie: 05252023: Tariff Spec/AI code for IES-4 Enhancement
		if ($data['FIN_data']['cltcode'] == 'FEDEX' || $data['FIN_data']['cltcode'] == 'DHLEXA' ) {
			$TAXExcisePerItem = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
			$VATBASE = round(($dutiable_value + $CUDAMOUNT + $BrokerFee + $IPF + $DOCFEE + $BANKCHARGE + str_replace(',', '', $wharfage) + str_replace(',', '', $arrastre) + $TAXExcisePerItem),2);
		} else {
			$TAXExcisePerItem = !empty($data['FIN_data']['ExciseTotal']) ? $data['FIN_data']['ExciseTotal'] :" ";
			$VATBASE = round(($dutiable_value + $CUDAMOUNT + $BrokerFee + $IPF + $DOCFEE + $BANKCHARGE + str_replace(',', '', $wharfage) + str_replace(',', '', $arrastre) +  $TAXExcisePerItem),2);
		}

		if ($data['FIN_data']['MDec'] == 'IED') {
			$VATBASE = 0;
		}


		/* End VAT Computation */

		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$VATBASE = $VATBASE;
		}else{
			if (!isset($TAXAMT2_2)) {
				$VATBASE = $VATBASE;
			}else{
				$VATBASE = $TAXAMT2_2;
			}
		}
		
		$this->SetXY(32, 200.5);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,number_format($VATBASE,2),0,0,'R');
		
		$this->SetXY(60, 200.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'12'.' %',0,0,'C');

		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$VATAMOUNT = round(($VATBASE * 0.12),2);
		}else{
			$VATAMOUNT = $TAXAMT2;
		}

		if ($TAXAMT2 != NULL) {
			$VATAMOUNT = $TAXAMT2;
		}else{
			$VATAMOUNT = round(($VATBASE * 0.12),2);
		}

		// $this->VAT_C = number_format($VATAMOUNT,2);
		
		$this->SetXY(75, 200.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,number_format($VATAMOUNT,2),0,0,'R');

		$this->SetXY(83, 200.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'0',0,0,'R');

		/* End Computations for VAT*/

		/* START AVT */

		if ($data['FIN_data']['GBTARTAB_rulcod'] == "AVT-AUTO" AND $data['FIN_data']['MSP'] != ""){
			// TYPE
			$this->SetXY(20, 206);
			$this->SetFont('Arial','B',8);
			$this->Write(0, 'AVT');
			
			if ($data['FIN_data']['MSP'] <= 600000)
			{
				$AVTRate = 4;
				$MSP = ($data['FIN_data']['MSP'] * 0.04) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 600000 && $data['FIN_data']['MSP'] <= 1000000)
			{
				$AVTRate = 10;
				$MSP = ($data['FIN_data']['MSP'] * 0.10) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 1000000 && $data['FIN_data']['MSP'] <= 4000000)
			{
				$AVTRate = 20;
				$MSP = ($data['FIN_data']['MSP'] * 0.20) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 4000000)
			{
				$AVTRate = 50;
				$MSP = ($data['FIN_data']['MSP'] * 0.50) * $data['FIN_data']['SupVal1'];
			}
			
			// TAX BASE AVT
			$this->SetXY(32, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(24,3,number_format($data['FIN_data']['MSP'],2),0,0,'R');
			
			// RATE			
			$this->SetXY(60, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(15,3,$AVTRate.' %',0,0,'C');
			
			// AMOUNT
			$this->SetXY(75, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(25,3,number_format($MSP,2),0,0,'R');
		}
		elseif ($data['FIN_data']['GBTARTAB_rulcod'] == "AVT_HYBRID" AND $data['FIN_data']['MSP'] != "") {
			// TYPE
			$this->SetXY(20, 206);
			$this->SetFont('Arial','B',8);
			$this->Write(0, 'AVT');
			
			if ($data['FIN_data']['MSP'] <= 600000)
			{
				$AVTRate = 2;
				$MSP = ($data['FIN_data']['MSP'] * 0.02) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 600000 && $data['FIN_data']['MSP'] <= 1000000)
			{
				$AVTRate = 5;
				$MSP = ($data['FIN_data']['MSP'] * 0.05) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 1000000 && $data['FIN_data']['MSP'] <= 4000000)
			{
				$AVTRate = 10;
				$MSP = ($data['FIN_data']['MSP'] * 0.10) * $data['FIN_data']['SupVal1'];
			}
			elseif($data['FIN_data']['MSP'] > 4000000)
			{
				$AVTRate = 25;
				$MSP = ($data['FIN_data']['MSP'] * 0.25) * $data['FIN_data']['SupVal1'];
			}
			
			
			// TAX BASE AVT
			$this->SetXY(32, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(24,3,number_format($data['FIN_data']['MSP'],2),0,0,'R');
			
			// RATE
			$this->SetXY(60, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(15,3,$AVTRate.' %',0,0,'C');
			
			// AMOUNT
			$this->SetXY(75, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(25,3,number_format($MSP,2),0,0,'R');
		}
		
		
		$this->SetXY(133, 176);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MSP');
		
		$this->SetXY(133, 184);
		$this->SetFont('Arial','',6);
		//$this->Write(0, number_format($MSP, 2));
		$this->Write(0, number_format($data['FIN_data']['MSP'], 2));
		
		if ($TAXAMT4 != null) {
			$TAXAMT4 = number_format($TAXAMT4, 2);
		}else{
			$TAXAMT4 = number_format($MSP, 2);
		}
		
		if ($data['FIN_data']['MSP'] != "" && $data['FIN_data']['MSP'] != 0) {
			$this->SetXY(20, 206);
			$this->SetFont('Arial','B',8);
			$this->Write(0, 'AVT');
		}
		// print_r($data['FIN_data']); die();
		
		// TAX BASE
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') && $data['FIN_data']['MSP'] != "") 
		{
			$this->SetXY(32, 204.5);
			$this->SetFont('Arial','',8);
			$this->Cell(24,3,number_format($data['FIN_data']['MSP'],2),0,0,'R');
			// $this->Cell(24,3,$TAXAMT4,0,0,'R');
		}

		$this->SetXY(60, 204.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'',0,0,'C');
		//if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') && $data['FIN_data']['MSP'] == "") {
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			if ($data['FIN_data']['MSP'] == "") {
				$TAXExciseTotal = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
				$TAXExciseTotalAmount = $TAXExciseTotal * 0.12;
				$this->SetXY(75, 204.5);
				$this->SetFont('Arial','',8);
				$this->Cell(25,3,number_format($TAXExciseTotal,2),0,0,'R');
			}
			else
			{
				$this->SetXY(75, 204.5);
				$this->SetFont('Arial','',8);
				$this->Cell(25,3,number_format($MSP,2),0,0,'R');
				
				
			}
		}
		
		$this->SetXY(83, 204.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'0',0,0,'R');


		/* END AVT */

		if ($TAXAMT3 != null) {
			$TAXAMT3 = number_format($TAXAMT3, 2);
		}else{
			$TAXAMT3 = '';
		}

		
		$this->SetXY(20, 205);
		$this->SetFont('Arial','B',8);
		if($TAXAMT3 !=''){
		//$this->Write(0, $TAXCODE3);// variable tax code    EXC TEXT FOR BOX 47
		}

		$this->SetXY(32, 205);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,'',0,0,'R');

		$this->SetXY(60, 205);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'',0,0,'C');
		/* Removed  since this is total  August 24 2018 
		$this->SetXY(75, 205);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,$TAXAMT3,0,0,'R'); //BOX 47 EXC EDIT HERE*/

		$this->SetXY(83, 205);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'',0,0,'R');
		
		$AiCodeData = $this->checkAICODE($data['FIN_data']['HSCode'], $data['FIN_data']['HSCODE_TAR'], $data['FIN_data']['TARSPEC']); // Check if the HSCODE, HECODE_TAR and TarSpec is in CWSAICODE
		if (strtoupper($data['FIN_data']['ExciseType']) == "PHARMACEUTICALS") {
			if ($data['FIN_data']['GBTARTAB_rulcod'] == "EXC-300390") {
				$ExcItem = $dutiable_value;

				$TAXExcisePerItem = ($cONS['SupVal1']  * $AiCodeData[0]['Rate']);
				$TAXExcise = !empty($data['FIN_others']['ExciseRate']) ? $data['FIN_others']['ExciseRate'] : $AiCodeData[0]['Rate'];
				$TAXExciseUnit = !empty($data['FIN_others']['ExciseUnit']) ? $data['FIN_others']['ExciseUnit'] :" ";

				$this->SetXY(20, 206);
				$this->SetFont('Arial','B',8);
				$this->Write(0, 'EXC');
				
				$this->SetXY(32, 205);
				$this->SetFont('Arial','',8);
				$this->Cell(24,3,number_format($cONS['SupVal1'], 2),0,0,'R');

				$this->SetXY(47, 205);
				$this->SetFont('Arial','',8);
				if ($FIN_multis['HSCode'] != "71171920" && $FIN_multis['HSCode'] != "71131990" && $FIN_multis['HSCode'] != "33030000" && substr(($FIN_multis['HSCode']),0,4) != "8703" && !isset($FIN_multis['MSP']) && $TAXExciseUnit != " ") {
					$this->Cell(25,3,$TAXExcise.'/'.$TAXExciseUnit,0,0,'R');
				}else{
					$this->Cell(25,3,$TAXExcise.' %',0,0,'R');
				}
				
				$this->SetXY(75, 205);
				$this->SetFont('Arial','',8);
				$this->Cell(25,3,number_format($TAXExcisePerItem,2),0,0,'R');
				$TAXAMT3 = $TAXExcisePerItem;
			}
		} else {
			$TAXExcise = !empty($data['FIN_others']['ExciseRate']) ? $data['FIN_others']['ExciseRate'] :" ";
			$TAXExciseUnit = !empty($data['FIN_others']['ExciseUnit']) ? $data['FIN_others']['ExciseUnit'] :" ";
			$TAXExcisePerItem = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
			//if($TAXAMT3 !=''){ Edited by larren  May 21 2018
			
			if($TAXExcise != '' && $TAXExcise != 0 && $TAXExcise != NULL){
				$this->SetXY(20, 206);
				$this->SetFont('Arial','B',8);
				if (substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "") {
					$this->Write(0, 'EXC');
				}else{
					$this->Write(0, 'AVT');
				}
				
				
				
				// TAX BASE Excise
				if ($data['FIN_data']['MSP'] == "") {
					$this->SetXY(32, 204.5);
					$this->SetFont('Arial','',8);
					//$this->Cell(24,3,number_format($data['FIN_others']['ExciseQty'],2),0,0,'R');
				}
			
				$this->SetXY(47, 205);
				//06202024: Spagara: if taxexciseunit is blank
				//if (substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "" && $TAXExciseUnit != " ") {
				if ($data['FIN_data']['HSCode'] != "71171920" && $data['FIN_data']['HSCode'] != "71131990" && $data['FIN_data']['HSCode'] != "33030000" && substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "" && $TAXExciseUnit != " ") {
					if (strlen($TAXExcise . '/' . $TAXExciseUnit) >= 8) {
						$this->SetFont('Arial', '', 6.5);
					} else {
						$this->SetFont('Arial', '', 8);
					}
					$this->Cell(25,3,$TAXExcise.'/'.$TAXExciseUnit.'',0,0,'R');
				//}elseif ($data['FIN_data']['MSP'] == ""){
				}else{
					$this->SetFont('Arial','',8);
					$this->Cell(25,3,$TAXExcise.' %',0,0,'R');
				}
				/*if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
					$this->SetXY(75, 205);
					$this->SetFont('Arial','',8);
					$this->Cell(25,3,number_format($TAXExcisePerItem,2),0,0,'R');
				}*/
				$TAXExciseQty = !empty($data['FIN_others']['ExciseQty']) ? $data['FIN_others']['ExciseQty'] :" ";	
					
				$this->SetXY(32, 205);	
				$this->SetFont('Arial','',8);	
				$this->Cell(24,3,number_format($TAXExciseQty,2),0,0,'R');	
					
				$TAXAMT3 = $TAXExcisePerItem;	
				$this->SetXY(75, 205);	
				$this->SetFont('Arial','',8);	
				//$this->Cell(25,3,number_format($TAXAMT3,2),0,0,'R');
			}
		}

		

		/* END EXC */
		
		/* START CSD */

		if ($TAXAMT5 != null) {
			$TAXAMT5 = number_format($TAXAMT5, 2);
		}else{
			$TAXAMT5 = '';
		}

		
		$this->SetXY(20, 214);
		$this->SetFont('Arial','B',8);
		$this->Write(0, $TAXCODE5);

		$this->SetXY(32, 212.5);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,'',0,0,'R');

		$this->SetXY(60, 212.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'',0,0,'C');

		$this->SetXY(75, 212.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,$TAXAMT5,0,0,'R');

		$this->SetXY(83, 212.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'',0,0,'R');

		/* END CSD */

		/* START FIN */
		if ($TAXAMT6 != null) {
			$TAXAMT6 = number_format($TAXAMT6, 2);
		}else{
			//$TAXAMT6 = '';
			if ($data['FIN_others']['SupUnit3'] > 0){	
				$TAXCODE6 = 'FIN';	
				$TAXAMT6 = $data['FIN_others']['SupUnit3'];	
			}else{	
				$TAXAMT6 = '';	
			}
		}

		
		$this->SetXY(20, 218);
		$this->SetFont('Arial','B',8);
		$this->Write(0, $TAXCODE6);

		$this->SetXY(32, 216.5);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,'',0,0,'R');

		$this->SetXY(60, 216.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'',0,0,'C');

		$this->SetXY(75, 216.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,$TAXAMT6,0,0,'R');

		$this->SetXY(83, 216.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'',0,0,'R');

		/* END FIN */

		/* START FF */

		if ($TAXAMT11 != null) {
			$TAXAMT11 = number_format($TAXAMT11, 2);
		}else{
			$TAXAMT11 = '';
		}

		
		$this->SetXY(20, 222);
		$this->SetFont('Arial','B',8);
		$this->Write(0, $TAXCODE11);

		$this->SetXY(32, 220.5);
		$this->SetFont('Arial','',8);
		$this->Cell(24,3,'',0,0,'R');

		$this->SetXY(60, 220.5);
		$this->SetFont('Arial','',8);
		$this->Cell(15,3,'',0,0,'C');

		$this->SetXY(75, 220.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,$TAXAMT11,0,0,'R');

		$this->SetXY(83, 220.5);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,'',0,0,'R');

		/* END FF */

		/* START DPD */

		if ($TAXAMT7 != null) {
			$TAXAMT7 = number_format($TAXAMT7, 2);
		}else{
			$TAXAMT7 = '';
		}
		
		if ($data['FIN_others']['HSCode'] == "27101211" || $data['FIN_others']['HSCode'] == "27101971" || $data['FIN_others']['HSCode'] == "27101972" || $data['FIN_others']['HSCode'] == "27101225" || $data['FIN_others']['HSCode'] == "27101222" || $data['FIN_others']['HSCode'] == "27101228" || $data['FIN_others']['HSCode'] == "27101983" || $data['FIN_others']['HSCode'] == "27101229" || $data['FIN_others']['HSCode'] == "27101223" || $data['FIN_others']['HSCode'] == "27101224" || $data['FIN_others']['HSCode'] == "27101226" || $data['FIN_others']['HSCode'] == "27101227" || $data['FIN_others']['HSCode'] == "27101212" || $data['FIN_others']['HSCode'] == "27101213" || $data['FIN_others']['HSCode'] == "27101221"){
			if ($data['FIN_others']['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] == "100") {
				$TAXAMT7 = $data['FIN_others']['SupVal1'] * 1 * 0.06146428571;
				$TAXFMF = "";
			}elseif ($data['FIN_others']['HSCode'] == "27101211" && $data['FIN_others']['HSCode_Tar'] != "100") {
				$TAXAMT7 = $data['FIN_others']['SupVal1'] * 1.10 * 0.06146428571;
				$TAXFMF = 1.10;
			//09052024: SPagara: update from 1.02 to 1.03 as per memo 13-2024
			}elseif ($data['FIN_others']['HSCode'] == "27101971" || $data['FIN_others']['HSCode'] == "27101972") {
				$TAXAMT7 = $data['FIN_others']['SupVal1'] * 1.03 * 0.06146428571;
				$TAXFMF = 1.03;
			}elseif ($data['FIN_others']['HSCode'] == "27101225" || $data['FIN_others']['HSCode'] == "27101222" || $data['FIN_others']['HSCode'] == "27101228" || $data['FIN_others']['HSCode'] == "27101983") {
				$TAXAMT7 = $data['FIN_others']['SupVal1'] * 1 * 0.06146428571;
				$TAXFMF = "";
			}elseif ($data['FIN_others']['HSCode'] == "27101229" || $data['FIN_others']['HSCode'] == "27101223" || $data['FIN_others']['HSCode'] == "27101224" || $data['FIN_others']['HSCode'] == "27101226" || $data['FIN_others']['HSCode'] == "27101227" || $data['FIN_others']['HSCode'] == "27101212" || $data['FIN_others']['HSCode'] == "27101213" || $data['FIN_others']['HSCode'] == "27101221") {
				$TAXAMT7 = $data['FIN_others']['SupVal1'] * 1.10 * 0.06146428571;
				$TAXFMF = 1.10;
			}
			
			$this->SetXY(16, 223);
			$this->SetFont('Arial','B',8);
			$this->Cell(15,5,'FMF',0,0,'C');

			$this->SetXY(45, 224);
			$this->SetFont('Arial','',8);
			$this->Cell(25,3,$TAXFMF,0,0,'R');

			$this->SetXY(73, 224.5);
			$this->SetFont('Arial','',8);
			$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');
		}

		if ($data['FIN_others']['HSCode'] == "25232990" || $data['FIN_others']['HSCode'] == "25239000"){	
			if ($data['FIN_others']['TARSPEC'] == "1001") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0233;	
				$TAXFMF = "2.33";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1002") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0276;	
				$TAXFMF = "2.76";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1003") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0341;	
				$TAXFMF = "3.41";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1004") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0619;	
				$TAXFMF = "6.19";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1005") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0794;	
				$TAXFMF = "7.94";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1006") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0948;	
				$TAXFMF = "9.48";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1007") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.0951;	
				$TAXFMF = "9.51";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1008") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.1067;	
				$TAXFMF = "10.67";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1009") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.1099;	
				$TAXFMF = "10.99";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1010") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.1158;	
				$TAXFMF = "11.58";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1011") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.1206;	
				$TAXFMF = "12.06";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1012") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.1529;	
				$TAXFMF = "15.29";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1013") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.2307;	
				$TAXFMF = "23.07";	
			}elseif ($data['FIN_others']['TARSPEC'] == "1014") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.00;	
				$TAXFMF = "0.00";	
			}elseif ($data['FIN_others']['TARSPEC'] == "") {	
				$TAXVALUE = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'];	
				$TAXAMT7 = $data['FIN_others']['InvValue'] * $data['FIN_others']['ExchRate'] * 0.2333;	
				$TAXFMF = "23.33";	
			}	
			$this->SetXY(16, 223);	
			$this->SetFont('Arial','B',8);	
			$this->Cell(15,5,'DPD',0,0,'C');	
			
			$this->SetXY(33, 224);		
			$this->SetFont('Arial','',8);		
			$this->Cell(24,3,number_format($TAXVALUE,2),0,0,'R');	
			
			$this->SetXY(45, 224);	
			$this->SetFont('Arial','',8);	
			$this->Cell(25,3,$TAXFMF.' %',0,0,'R');	
			
			$this->SetXY(73, 224.5);	
			$this->SetFont('Arial','',8);	
			$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');	
				
				
		}
		
		//$this->SetXY(20, 226);
		//$this->SetFont('Arial','B',8);
		//$this->Write(0, $TAXCODE7);

		//$this->SetXY(32, 224.5);
		//$this->SetFont('Arial','',8);
		//$this->Cell(24,3,'',0,0,'R');

		//$this->SetXY(60, 224.5);
		//$this->SetFont('Arial','',8);
		//$this->Cell(15,3,'',0,0,'C');

		//$this->SetXY(75, 224.5);
		//$this->SetFont('Arial','',8);
		//$this->Cell(25,3,$TAXAMT7,0,0,'R');

		//$this->SetXY(83, 224.5);
		//$this->SetFont('Arial','',8);
		//$this->Cell(25,3,'',0,0,'R');

		/* END DPD */


		$this->SetXY(20, 222);
		$this->SetFont('Times','',20);
		$this->Cell(93,5,'','B',0,'C');

		$this->SetXY(20, 229);
		$this->SetFont('Arial','',6);
		$this->Cell(80,3,'Total Item:',0,0,'C');
		
		//$TOTALTAXES = str_replace(',', '', $CUDAMOUNT) + str_replace(',', '', $VATAMOUNT) + str_replace(',', '', $TAXAMT3) + str_replace(',', '', $TAXAMT4) + str_replace(',', '', $TAXAMT5) + str_replace(',', '', $TAXAMT6) + str_replace(',', '', $TAXAMT11) + str_replace(',', '', $TAXAMT7) + str_replace(',', '', $TAXExciseTotalAmount);;
		$TOTALTAXES = str_replace(',', '', $CUDAMOUNT) + str_replace(',', '', $VATAMOUNT) + str_replace(',', '', $TAXAMT3) + str_replace(',', '', $TAXAMT4) + str_replace(',', '', $TAXAMT5) + str_replace(',', '', $TAXAMT6) + str_replace(',', '', $TAXAMT11) + str_replace(',', '', $TAXAMT7);

		$this->SetXY(75, 229);
		$this->SetFont('Arial','',8);
		$this->Cell(25,3,number_format($TOTALTAXES, 2),'0',0,'R');
			

		/* END Box 47 */

		/* BOX 48, 49, 47b */

		/* BOX 48 */

		$this->SetXY(113, 189);
		$this->SetFont('Times','',20);
		$this->Cell(94,45,'','LTR',0,'C');

		$this->SetXY(113, 189);
		$this->SetFont('Times','',20);
		$this->Cell(94,9,'','B',0,'C');

		$this->SetXY(113, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '48  Prepaid Account No.');


		$this->SetXY(113, 193.5);
		$this->SetFont('Arial','B',6);
		$this->Cell(47,3,$data['FIN_data']['PrePAcct'],0,0,'C');

		$this->SetXY(113, 189);
		$this->SetFont('Arial','B',6);
		$this->Cell(47,9,'','R',0,'C');

		/* END BOX 48*/

		/* BOX 49 */

		$this->SetXY(160, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '49  Identification of Warehouse');

		$this->SetXY(160, 193.5);
		$this->SetFont('Arial','',8);
		if ($data['FIN_data']['WareCode'] != NULL || $data['FIN_data']['WareDelay'] != NULL) {
			$warehouse = $data['FIN_data']['WareCode'].' / '.$data['FIN_data']['WareDelay'];
		}else{
			$warehouse = '/';
		}
		$this->Cell(47,3,$warehouse,0,0,'C');

		/* END BOX 49 */

		/* BOX 47b */

		$this->SetXY(113, 200);
		$this->SetFont('Arial','',6);
		$this->Write(0, '47b  ACCOUNTING DETAILS');

		$this->SetXY(115, 205);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Method of Payment      :');


		if($data['FIN_data']['PrePAcct'] != ""){
			$MOD = "CREDIT";
		}else{
			$MOD = "CASH";
		}
		$this->SetXY(141, 205);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MOD);

		$this->SetXY(115, 210);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Assessment Number    :');

		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$ASSREF = '';
			$ASSESSNO =  '';
			$ASSESSDATE =  '';
			$RECREF = '';
			$RECPDATE = '';
			$RECPNO = '';
		}

		/*}else{
			$ASSREF = $RespHEAD[0]['ASSREF'];
			$ASSESSNO = $RespHEAD[0]['ASSESSNO'];
			if ($RespHEAD[0]['ASSESSDATE'] == NULL) {
				$ASSESSDATE = '';
			}else{
				$ASSESSDATE = date('m-d-Y', strtotime($RespHEAD[0]['ASSESSDATE']));
			}
			$RECREF = $RespHEAD[0]['RECREF'];
			$RECPNO = $RespHEAD[0]['RECPNO'];
			if ($RespHEAD[0]['RECPDATE'] == NULL) {
				$RECPDATE = '';
			}else{
				$RECPDATE = date('m-d-Y', strtotime($RespHEAD[0]['RECPDATE']));
			}
		} */
		$this->SetXY(141, 210);
		$this->SetFont('Arial','',8);
		$this->Write(0, $ASSREF);

		$this->SetXY(144, 210);
		$this->SetFont('Arial','',8);
		$this->Write(0, $ASSESSNO);

		$this->SetXY(165, 210);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date    :');

		$this->SetXY(175, 210);
		$this->SetFont('Arial','',8);
		$this->Write(0, $ASSESSDATE);

		$this->SetXY(115, 215);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Reciept Number            :');

		$this->SetXY(141, 215);
		$this->SetFont('Arial','',8);
		$this->Write(0, $RECREF);

		$this->SetXY(144, 215);
		$this->SetFont('Arial','',8);
		$this->Write(0, $RECPNO);

		$this->SetXY(165, 215);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date    :');

		$this->SetXY(175, 215);
		$this->SetFont('Arial','',8);
		$this->Write(0, $RECPDATE);

		$this->SetXY(115, 220);
		$this->SetFont('Arial','B',6);
		$this->Write(0, 'Guarantee');

		$TAXAMT1 = null;
		$TAXAMT2 = null;
		$TAXAMT3 = null;
		$TAXAMT4 = null;
		$TAXAMT5 = null;
		$TAXAMT6 = null;
		$TAXAMT7 = null;
		$TAXAMT8 = null;
		$TAXAMT9 = null;
		$TAXAMT10 = null;
		$TAXAMT11 = null;
		$TAXAMT12 = null;
		$TAXAMT13 = null;
		$TAXAMT14 = null;
		$TAXAMT15 = null;
		$TAXAMT16 = null;
		$TAXAMT17 = null;
		foreach ($RespGT as $key => $global_taxes) {
			$TAXAMTS = array(
				$global_taxes['TAXCODE'] => $global_taxes['TAXAMT']
			);

			if (array_key_exists('CUD', $TAXAMTS)) {
				$TAXCODE1 = 'CUD';
				$TAXAMT1 = $TAXAMTS['CUD'];
			}

			if (array_key_exists('VAT', $TAXAMTS)) {
				$TAXCODE2 = 'VAT';
				$TAXAMT2 = $TAXAMTS['VAT'];
			}

			if (array_key_exists('EXC', $TAXAMTS)) {
				$TAXCODE3 = 'EXC';
				$TAXAMT3 = $TAXAMTS['EXC'];
			}

			if (array_key_exists('AVT', $TAXAMTS)) {
				$TAXCODE4 = 'AVT';
				$TAXAMT4 = $TAXAMTS['AVT'];
			}

			if (array_key_exists('CSD', $TAXAMTS)) {
				$TAXCODE5 = 'CSD';
				$TAXAMT5 = $TAXAMTS['CSD'];
			}

			if (array_key_exists('FIN', $TAXAMTS)) {
				$TAXCODE6 = 'FIN';
				$TAXAMT6 = $TAXAMTS['FIN'];
			}

			if (array_key_exists('DPD', $TAXAMTS)) {
				$TAXCODE7 = 'DPD';
				$TAXAMT7 = $TAXAMTS['DPD'];
			}

			if (array_key_exists('IPF', $TAXAMTS)) {
				$TAXCODE8 = 'IPF';
				$TAXAMT8 = $TAXAMTS['IPF'];
			}

			if (array_key_exists('SGD', $TAXAMTS)) {
				$TAXCODE9 = 'SGD';
				$TAXAMT9 = $TAXAMTS['SGD'];
			}

			if (array_key_exists('D&F', $TAXAMTS)) {
				$TAXCODE10 = 'D&amp;F';
				$TAXAMT10 = $TAXAMTS['D&F'];
			}

			if (array_key_exists('FF', $TAXAMTS)) {
				$TAXCODE11 = 'FF';
				$TAXAMT11 = $TAXAMTS['FF'];
			}

			if (array_key_exists('PSI', $TAXAMTS)) {
				$TAXCODE12 = 'PSI';
				$TAXAMT12 = $TAXAMTS['PSI'];
			}

			if (array_key_exists('TSF', $TAXAMTS) || array_key_exists('CTF', $TAXAMTS)) {
				if(array_key_exists('TSF', $TAXAMTS)){
					$TAXCODE13 = 'TSF';
					$TAXAMT13 = $TAXAMTS['TSF'];
				}elseif(array_key_exists('CTF', $TAXAMTS)){
					$TAXCODE13 = 'CTF';
					$TAXAMT13 = $TAXAMTS['CTF'];
				}else{
				//none
				}
				
			}
			
			if (array_key_exists('SGL', $TAXAMTS)) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = $TAXAMTS['SGL'];
			}
			
			if (array_key_exists('CSF', $TAXAMTS)) {
				$TAXCODE15 = 'CSF';
				$TAXAMT15 = $TAXAMTS['CSF'];
			}

			if (array_key_exists('CDS', $TAXAMTS)) {
				$TAXCODE16 = 'CDS';
				$TAXAMT16 = $TAXAMTS['CDS'];
			}

			if (array_key_exists('IRS', $TAXAMTS)) {
				$TAXCODE17 = 'IRS';
				$TAXAMT17 = $TAXAMTS['IRS'];
			}
		}

		// if ($data['FIN_data']['Mdec2'] == '7' || $data['FIN_data']['Mdec2'] == '5' || $data['FIN_data']['Mdec2'] == '8') {
		// 	$TAXAMT1 = '';
		// 	$TAXAMT2 = '';
		// 	$TAXAMT3 = '';
		// 	$TAXAMT4 = '';
		// }

		//07042024:SPagara: SGL for 4ES
			$CustomVal1 = str_replace(',', '', $data['FIN_data']['CustomVal']);

		//07042024:SPagara: SGL for 4ES
	if ($data['FIN_data']['MDec']== '4ES') {
		if ($data['FIN_data']['InvCurr'] == "USD"){
			if ((float)$CustomVal1 <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}elseif ($data['FIN_data']['InvCurr'] == "PHP"){
			$NewVal = (float)$CustomVal1 /$data['ExchRate_queryUSD']['RAT_EXC'];
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}else{
			$NewVal = ((float)$CustomVal1 * $data['FIN_data']['ExchRate']) / $data['ExchRate_queryUSD']['RAT_EXC'];
			//print_r($NewVal); die();
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}
	}

		if ($data['FIN_data']['MDec'] == '8ZN') {
			$ITAX1 = $TAXAMT3 + $TAXAMT4 + $TAXAMT5 + $TAXAMT6 + $TAXAMT7;
		}else{
			$ITAX1 = $TAXAMT1 + $TAXAMT2 + $TAXAMT3 + $TAXAMT4 + $TAXAMT5 + $TAXAMT6 + $TAXAMT7;
		}

		$GTAX = $TAXAMT8 + $TAXAMT9 + $TAXAMT10 + $TAXAMT11 + $TAXAMT12 + $TAXAMT13 + $TAXAMT14 + $TAXAMT15 + $TAXAMT16 + $TAXAMT17;

		if ($data['FIN_data']['MDec'] == '8ZN') {
			$TTAX = $GTAX;
		}else{
			$TTAX = $GTAX + $ITAX1;
		}


		$TAXAMT8 = $TAXAMT3 + $TAXAMT4 + $TAXAMT5 + $TAXAMT6 + $TAXAMT7 + $TAXAMT8 + $TAXAMT9 + $TAXAMT10 + $TAXAMT11 + $TAXAMT12 + $TAXAMT13 + $TAXAMT14 + $TAXAMT15 + $TAXAMT16 + $TAXAMT17;

		$this->SetXY(141, 220);
		$this->SetFont('Arial','',8);
		$this->Write(0, '0.00');

		$this->SetXY(165, 220);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date    :');

		$this->SetXY(175, 220);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(115, 225);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Fees');

		$this->SetXY(141, 225);
		$this->SetFont('Arial','',8);
		$this->Write(0, number_format($TAXAMT8, 2));

		$this->SetXY(115, 230);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Assessment');

		$this->SetXY(141, 230);
		$this->SetFont('Arial','',8);
		$this->Write(0, number_format($TTAX, 2));

		/* END BOX 47b */

		/* END BOX 48, 49, 47b */

		/* BOX 51, 50, 52, 53 */

		$this->SetXY(5, 234);
		$this->SetFont('Times','',20);
		$this->Cell(133,35,'','TLB',0,'C');

		$this->SetXY(5, 253.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '51  AUTHO-');

		$this->SetXY(5, 256);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'RIZATION');

		$this->SetXY(20, 253.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Broker :');

		$this->SetXY(20, 258);
		if ($data['FIN_data']['APPLNO'] = 'TA7X2101801'){
			$this->SetFont('Arial','',6);
		}else {
			$this->SetFont('Arial','',8);
		}
		$this->Write(0, @$data['crf_2']['BROKERNAME']);

		$this->SetXY(65, 253.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date :');

		$this->SetXY(65, 258);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(80, 253.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Importer / Attorney-in-Fact :');

		$this->SetXY(75, 258);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expname);

		$this->SetXY(5, 234);
		$this->SetFont('Times','',20);
		$this->Cell(15,35,'','R',0,'C');

		$this->SetXY(5, 234);
		$this->SetFont('Times','',20);
		$this->Cell(15,17.5,'','B',0,'C');

		$this->SetXY(20, 262);
		$this->SetFont('Times','',20);
		$this->Cell(118,7,'','TR',0,'C');

		$this->SetXY(20, 236);
		$this->SetFont('Arial','',6);
		$this->Write(0, '50  We hereby certify that the information contained in all pages');

		$this->SetXY(20, 239);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of this Declaration and the documents submitted are to the best');

		$this->SetXY(20, 242);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of our knowledge and belief true and correct');

		$this->SetXY(60, 262);
		$this->SetFont('Times','',20);
		$this->Cell(39.33,7,'','LR',0,'C');

		$this->SetXY(138, 267);
		$this->SetFont('Times','',20);
		$this->Cell(69,2,'','B',0,'C');



		/*  Broken Borders*/

		$this->SetXY(138, 235);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 239);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 243);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 247);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 251);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 255);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		$this->SetXY(138, 259);
		$this->SetFont('Times','',20);
		$this->Cell(133,2,'','L',0,'C');

		

		$this->SetXY(205, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,35,'','R',0,'C');

		

		$this->SetXY(139, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(143, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(147, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(151, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(155, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(159, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(163, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(167, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(171, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(175, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(179, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(183, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(187, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(191, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(195, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(199, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');

		$this->SetXY(203, 234);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','T',0,'C');




		$this->SetXY(139, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(143, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(147, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(151, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(155, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(159, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(163, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(167, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(171, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(175, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(179, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(183, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(187, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(191, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(195, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(199, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(203, 260);
		$this->SetFont('Times','',20);
		$this->Cell(2,2,'','B',0,'C');

		$this->SetXY(156, 236);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Subscribed and sworn bofore me');

		$this->SetXY(162, 256);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Administering Officer');

		$this->SetXY(166, 259);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Notary Public');

		$this->SetXY(166, 262);
		$this->SetFont('Times','',20);
		$this->Cell(8,7,'','R',0,'C');

		/* END BROKEN BORDERS */
		
		/* END BOX 51, 50*/

		/* BOX 52, 53  */

		/* BOX 52 */

		$this->SetXY(5, 269);
		$this->SetFont('Times','',20);
		$this->Cell(202,7,'','LBR',0,'C');

		$this->SetXY(5, 271);
		$this->SetFont('Arial','',6);
		$this->Write(0, '52  Control');

		$this->SetXY(5, 274);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'at Office Destination');

		$this->SetXY(5, 272);
		$this->SetFont('Times','',20);
		$this->Cell(123,13,'','R',0,'C');

		$this->SetXY(128, 271);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Code');

		$this->SetXY(15, 269);
		$this->SetFont('Times','',20);
		$this->Cell(123,7,'','R',0,'C');

		$this->SetXY(10, 278);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'CONTROL AT OFFICE OF DESTINATION');

		$this->SetXY(10, 283);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Signature');

		$this->SetXY(95, 278);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Stamp');

		/* END BOX 52 */

		/* BOX 53 */

		$this->SetXY(138, 271);
		$this->SetFont('Arial','',6);
		$this->Write(0, '53  Office of Destination and Country');

		/* END BOX 53 */

		/* BOX 54 */

		$this->SetXY(5, 276);
		$this->SetFont('Times','',20);
		$this->Cell(202,9,'','LBR',0,'C');

		$this->SetXY(128, 278);
		$this->SetFont('Arial','',6);
		$this->Write(0, '54  Place and date');

		/* END BOX 54 */

		/* END BOX 52, 53  */
	}

	public function rider_page($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD){
		$DM = @$_GET['DM'];
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S' && $data['FIN_data']['MDec'] != '8') && $data['FIN_data']['MDec'] != 'ID') {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');
			$this->Image('newbg.png',8.27,11.69,200);
		}
		
		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');

			$this->SetXY(152, 5);
			$this->SetFont('Arial','',8);
			$this->Image('draft.png',4,30,200);
			
			if ($DM == 1) {
				$this->Image('DEMINIMIS.png',4,30,200);
			}
		}

		$this->SetXY(5, 13);
		$this->SetFont('Arial','B',8);
		$this->Write(0,'TEMPORARY SINGLE ADMINISTRATIVE DOCUMENT','');

		// Line break
		$this->Ln(40);


	//	$this->Image('ins.jpg',172,286,30);
		$ext = array(
				'001',
				'011', 
				'021', 
				'026', 
				'054', 
				'056', 
				'058',
				'060',
				'0N2',
				'0N4',
				'0R1',
				'101',
				'201',
				'211',
				'301',
				'311',
				'401',
				'501',
				'511',
				'601',
				'611',
				'701',
				'801',
				'DPS',
				'E01',
				'L03',
				'L05',
				'L07',
				'M00',
				'M20',
				'M30',
				'M40',
				'M50',
				'M60',
				'N21',
				'N30',
				'N31',
				'N36',
				'N41',
				'N46',
				'N51',
				'N56',
				'N61',
				'N71',
				'N81',
				'N91',
				'P01',
				'P10',
				'P20',
				'P23',
				'P25',
				'P30',
				'P40',
				'P50',
				'P60',
				'P71',
				'P81',
				'P91',
				'P92',
				'R01',
				'R04',
				'R06',
				'R08',
				'R10',
				'R13',
				'R14',
				'R16',
				'R18',
				'R20',
				'R23',
				'R24',
				'R26',
				'R28',
				'R31',
				'R32',
				'R34',
				'R36',
				'R39',
				'R40',
				'R42',
				'R44',
				'R46',
				'R48',
				'R50',
				'R52',
				'R54',
				'R57',
				'R58',
				'R63',
				'R65',
				'R67',
				'R69',
				'R70',
				'R73',
				'R75',
				'R77',
				'R79',
				'R80',
				'R83',
				'R85',
				'R87',
				'R89',
				'R90',
				'R93',
				'R95',
				'R97',
				'R99',
				'T01',
				'T05',
				'T11',
				'T14',
				'T16',
				'T18',
				'T20',
				'T22',
				'T24',
				'T26',
				'T28',
				'T31',
				'T34',
				'T36',
				'T38',
				'T40',
				'T42',
				'T44',
				'T46',
				'T49',
				'T50',
				'T53',
				'T55',
				'T57',
				'T60',
				'T70',
				'T80',
				'T90',
				'TE1',
				'TN2',
				'TN4',
				'TN6',
				'TN8',
				'L09',
				'LK2Z',
				'L13');


		//$this->SetXY(5, 10);
		//$this->SetFont('Arial','',8);
		//$this->Write(0, 'TEMPORARY SINGLE ADMINISTRATIVE DOCUMENT');

		$this->SetXY(132, 15);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Office Code');

		$this->SetXY(150, 15);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['OffClear']);

		$this->SetXY(135, 19);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['OffClearance']);

		$this->SetXY(132, 23);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'CUstoms Reference');
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$REGREF = '';
			$REGNO = '';
			$REGDATE = '';
			$REGDATEYEAR = '';
		}else{
			$REGREF = $RespHEAD[0]['REGREF'];
			$REGNO = $RespHEAD[0]['REGNO'];
			$REGDATEYEAR = date('Y', strtotime($RespHEAD[0]['REGDATE']));
			if ($RespHEAD[0]['REGDATE'] == NULL) {
				$REGDATE = '';
			}else{
				$REGDATE = date('m-d-Y', strtotime($RespHEAD[0]['REGDATE']));
			}
		}

	
		$this->SetXY(135, 27);
		$this->SetFont('Arial','',8);
		$this->Write(0, $REGREF);

		$this->SetXY(140, 27);
		$this->SetFont('Arial','',8);
		$this->Write(0, $REGNO);

		$this->SetXY(150, 27);
		$this->SetFont('Arial','',8);
		$this->Write(0, $REGDATE);

		$this->SetXY(132, 31);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Registry Number');

		$this->SetXY(150, 31);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Manifest']);

		/* Start Box 8 */

		$this->SetXY(17, 15);
		$this->SetFont('Times','',20);
		$this->Cell(85,20,'','TLR',0,'C');

		$this->SetXY(19, 17);
		$this->SetFont('Arial','',6);
		$this->Write(0, '8  Importer / Consignee, Address');

		$this->SetXY(63, 17);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'TIN: ');

		$this->SetXY(69, 17);
		$this->SetFont('Arial','',7);
		$this->Write(0, $data['FIN_data']['ConTIN']);

		$ConTIN = $data['FIN_data']['ConTIN'];
		if (@$data['crf'] == null) {
			$expname = $data['FIN_data']['ConName'];
			$expaddress1 = $data['FIN_data']['ConAddr1'];
			$expaddress2 = $data['FIN_data']['ConAddr2'];
			$expaddress3 = $data['FIN_data']['ConAddr3'];
			$expaddress4 = '';
		}else{
			$expname = $data['crf']['CONNAME'];
			$expaddress1 = $data['crf']['CONADDR1'];
			$expaddress2 = $data['crf']['CONADDR2'];
			$expaddress3 = $data['crf']['CONADDR3'];
			$expaddress4 = @$data['crf']['CONCTY'];
		}

		if($ConTIN == '000400016000'){
			$expaddress1 = '8TH FLOOR TERA TOWER BRIDGETOWNE E. RODRIGUEZ';
			$expaddress2 = 'AVENUE C5 ROAD';
			$expaddress3 = 'UGONG NORTE QUEZON CITY 1100';
			$expaddress4 = 'PHILIPPINES';
		}
		
		if($ConTIN == '003254875000'){
			$expaddress1 = 'DON CELSO S TUAZON AVE CAINTA RIZAL';
			$expaddress2 = ' PHILIPPINES';
			$expaddress3 = 'CAINTA 1900';
			$expaddress4 = 'PHILIPPINES';
		}
		
		if($ConTIN == '217749284000'){
			$expaddress1 = 'ON A.P.C. B.V. 2ND STREET, PHASE 2,';
			$expaddress2 = 'CEZ, ROSARIO';
			$expaddress3 = 'CAVITE 4106';
			$expaddress4 = '';
		}

		if($ConTIN == '000428573000'){
			$expaddress1 = 'SARANGANI ECONOMIC DEVT ZONE';
			$expaddress2 = 'POLOMOLOK SOUTH COTABATO 9504';
			$expaddress3 = 'PHILIPPINES';
			$expaddress4 = '';
		}

		if($ConTIN == '000079915000'){
			$expaddress1 = 'SAN MIGUEL YAMAMURA ASIA CORP.';
			$expaddress2 = 'KM. 27 E. AGUINALDO HIGHWAY, ANABU';
			$expaddress3 = 'II IMUS CAVITE 4103 PHILIPPINES';
			$expaddress4 = '';
		}

		//if($ConTIN == '000254013000'){
		//	$expaddress1 = 'CENTERPOINT BLDG FORMERLY 284 CANDA';
		//	$expaddress2 = 'NO BLDG ZIGA AVE TABACO CITY ALBAY';
		//	$expaddress3 = 'TABACO CITY 4511';
		//	$expaddress4 = 'PHILIPPINES';
		//}
		
		if($ConTIN == '008550093000'){
			$expaddress1 = '10TH FLOOR SALCEDO TOWER 169 HV DELA COSTA ST';
			$expaddress2 = 'SALCEDO VILLAGE';
			$expaddress3 = 'MAKATI CITY 1227';
			$expaddress4 = 'PHILIPPINES';
		}

		$this->SetXY(21, 21);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expname);

		$this->SetXY(21, 24);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress1);

		$this->SetXY(21, 27);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress2);

		$this->SetXY(21, 30);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress3);

		$this->SetXY(21, 33);
		$this->SetFont('Arial','',8);
		$this->Write(0, $expaddress4);

		$this->SetXY(5, 35);
		$this->SetFont('Times','',20);
		$this->Cell(202,250,'',1,0,'C');

		$this->SetXY(20, 35);
		$this->SetFont('Times','',20);
		$this->Cell(98,250,'','LR',0,'C');

		/* Start Box 31 */
		
		$this->SetXY(5, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, '31  Packages');

		$this->SetXY(14.3, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'and');

		$this->SetXY(7, 43);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Description');

		$this->SetXY(9, 46);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Goods');

		$this->SetXY(20, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks and Numbers - Container No(s) - Number and Kind');

		$this->SetXY(20, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks & No');

		$this->SetXY(20, 43);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Packages');

		$this->SetXY(20, 46);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Number and Kind');

		$this->SetXY(20, 49);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 52.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Goods Desc.');

		/* End Box 31 */

		/* Start Box 44 */

		$this->SetXY(5, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, '44  Add Infos');

		$this->SetXY(5, 73);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Doc / Product');

		$this->SetXY(10.5, 76);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Certif &');

		$this->SetXY(14, 79);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Aut');

		$this->SetXY(20, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'OTHinEV : ');

		$this->SetXY(55, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'INSinFRT : ');

		$this->SetXY(90, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Fine : ');

		$this->SetXY(20, 82);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Invoice No. : ');

		/* End Box 44 */

		/* Start Box 32 */
		
		$this->SetXY(118, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, '32  Item No.');

		/* End Box 32 */

		/* Start Box 33 */

		$this->SetXY(138, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, '33  Hs Code');

		$this->SetXY(169, 37);
		$this->SetFont('Arial','',6);
		//$this->Write(0, 'Tar Spec');
		
		$this->SetXY(185, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Spec Code');
		
		/* End Box 33 */

		/* Start Box 34 */

		$this->SetXY(118, 45.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '34  C.O. Code');

		/* End Box 34 */

		/* Start Box 35 */

		$this->SetXY(147.5, 45.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '35  Item Gross Weight');

		/* End Box 35 */

		/* Start Box 36 */

		$this->SetXY(177, 45.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '36  Pref');

		/* End Box 36 */

		/* Start Box 37 */

		$this->SetXY(118, 53);
		$this->SetFont('Arial','',6);
		$this->Write(0, '37  Procedure');

		/* End Box 37 */

		/* Start Box 38 */

		$this->SetXY(147.5, 53);
		$this->SetFont('Arial','',6);
		$this->Write(0, '38  Item Net Weight');

		/* End Box 38 */

		/* Start Box 39 */

		$this->SetXY(177, 53);
		$this->SetFont('Arial','',6);
		$this->Write(0, '39  Quota');

		/* End Box 39 */

		/* Start Box 40a */

		$this->SetXY(118, 61.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40a  AWB / BL');

		/* End Box 40a */

		/* Start Box 40b */

		$this->SetXY(162, 61.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40b  Previous Doc No.');

		/* End Box 40b */

		/* Start Box 41 */

		$this->SetXY(118, 70);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 70);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 70);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 48 */

		//$this->SetXY(118, 78);
		$this->SetXY(158, 80);
		$this->SetFont('Arial','',6);
		$this->Write(0, '46  Dutiable Value (PHP)');
		
		//$this->SetXY(158, 78);
		$this->SetXY(158, 75);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'A.I. Code');

		/* End Box 48 */

		/* Start Box 49 */

		//$this->SetXY(172, 78);
		$this->SetXY(172, 75);
		$this->SetFont('Arial','',6);
		$this->Write(0, '45  Adjustment');

		/* End Box 49 */
		$this->SetXY(118, 80);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MSP');
		
		$this->SetXY(118, 35);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 43.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 51.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		//$this->SetXY(118, 68);
		$this->SetXY(118, 65);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');
		
		//$this->SetXY(118, 68);
		$this->SetXY(118, 70);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');
		
		$this->SetXY(5, 68);
		$this->SetFont('Times','',20);
		$this->Cell(202,17,'','TB',0,'C');

		$this->SetXY(118, 35);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');

		$this->SetXY(118, 43.25);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(147.7, 43.25);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 51.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		//$this->SetXY(118, 54.50);
		$this->SetXY(120, 78.5);
		$this->SetFont('Times','',20);
		//$this->Cell(13.5,5.24,'','R',0,'C');
		$this->Cell(34,6.5,'','R',0,'C');

		$this->SetXY(147.7, 51.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 59.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');
		//$this->Cell(44.5,5,'','R',0,'C');

		$this->SetXY(118, 68);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');

		$this->SetXY(152, 68);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');
		
		//$this->SetXY(152, 76.25);
		$this->SetXY(152, 73);
		$this->SetFont('Times','',20);
		//$this->Cell(20,8.24,'','R',0,'C');
		$this->Cell(20,5,'','R',0,'C');

		
		/* Start Box 31 */

		$this->SetXY(5, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, '31  Packages');

		$this->SetXY(14.3, 90);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'and');

		$this->SetXY(7, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Description');

		$this->SetXY(9, 96);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Goods');

		$this->SetXY(20, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks and Numbers - Container No(s) - Number and Kind');

		$this->SetXY(20, 90);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks & No');

		$this->SetXY(20, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Packages');

		$this->SetXY(20, 96);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Number and Kind');

		$this->SetXY(20, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 102.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Goods Desc.');

		/* End Box 31 */

		/* Start Box 44 */

		$this->SetXY(5, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, '44  Add Infos');

		$this->SetXY(5, 123);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Doc / Product');

		$this->SetXY(10.5, 126);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Certif &');

		$this->SetXY(14, 129);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Aut');

		$this->SetXY(20, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'OTHinEV : ');

		$this->SetXY(55, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'INSinFRT : ');

		$this->SetXY(90, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Fine : ');

		$this->SetXY(20, 132);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Invoice No. : ');

		/* End Box 44 */

		/* Start Box 32 */
		
		$this->SetXY(118, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, '32  Item No.');
		
		/* End Box 32 */

		/* Start Box 33 */
		
		$this->SetXY(138, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, '33  Hs Code');

		$this->SetXY(169, 87);
		$this->SetFont('Arial','',6);
		//$this->Write(0, 'Tar Spec');
		
		$this->SetXY(185, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Spec Code');
		
		/* End Box 33 */

		/* Start Box 34 */

		$this->SetXY(118, 95.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '34  C.O. Code');

		/* End Box 34 */

		/* Start Box 35 */

		$this->SetXY(147.5, 95.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '35  Item Gross Weight');

		/* End Box 35 */

		/* Start Box 36 */

		$this->SetXY(177, 95.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '36  Pref');

		/* End Box 36 */

		/* Start Box 37 */

		$this->SetXY(118, 103);
		$this->SetFont('Arial','',6);
		$this->Write(0, '37  Procedure');

		/* End Box 37 */

		/* Start Box 38 */

		$this->SetXY(147.5, 103);
		$this->SetFont('Arial','',6);
		$this->Write(0, '38  Item Net Weight');

		/* End Box 38 */

		/* Start Box 39 */

		$this->SetXY(177, 103);
		$this->SetFont('Arial','',6);
		$this->Write(0, '39  Quota');

		/* End Box 39 */

		/* Start Box 40a */

		$this->SetXY(118, 111.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40a  AWB / BL');

		/* End Box 40a */

		/* Start Box 40b */

		$this->SetXY(162, 111.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40b  Previous Doc No.');

		/* End Box 40b */

		/* Start Box 41 */

		$this->SetXY(118, 120);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 120);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 120);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 46 */

		$this->SetXY(118, 130);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MSP');
		
		//$this->SetXY(118, 128);
		$this->SetXY(158, 130);
		$this->SetFont('Arial','',6);
		$this->Write(0, '46  Dutiable Value (PHP)');
		
		//$this->SetXY(158, 128);
		$this->SetXY(158, 125);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'A.I. Code');

		/* End Box 46 */

		/* Start Box 45 */

		//$this->SetXY(172, 128);
		$this->SetXY(172, 125);
		$this->SetFont('Arial','',6);
		$this->Write(0, '45  Adjustment');

		/* End Box 45 */

		$this->SetXY(118, 85);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 93.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		//$this->SetXY(118, 118);
		$this->SetXY(118, 115);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');
		
		//$this->SetXY(118, 68);
		$this->SetXY(118, 120);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(5, 118);
		$this->SetFont('Times','',20);
		$this->Cell(202,17,'','TB',0,'C');

		$this->SetXY(118, 85);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');

		$this->SetXY(118, 93.25);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(147.7, 93.25);
		$this->SetFont('Times','',20);
		//$this->Cell(29.7,8.24,'','R',0,'C');
		$this->Cell(29.7,5,'','R',0,'C');

		$this->SetXY(118, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		//$this->SetXY(118, 104.50);
		$this->SetXY(120, 128.5);
		$this->SetFont('Times','',20);
		//$this->Cell(13.5,5.24,'','R',0,'C');
		$this->Cell(34,6.5,'','R',0,'C');

		$this->SetXY(147.7, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 109.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');
		

		$this->SetXY(118, 118);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');

		$this->SetXY(152, 118);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');

		//$this->SetXY(152, 126.25);
		$this->SetXY(152, 123);
		$this->SetFont('Times','',20);
		//$this->Cell(20,8.24,'','R',0,'C');
		$this->Cell(20,5,'','R',0,'C');

		

		/* Start Box 31 */
		
		$this->SetXY(5, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, '31  Packages');

		$this->SetXY(14.3, 140);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'and');

		$this->SetXY(7, 143);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Description');

		$this->SetXY(9, 146);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Goods');

		$this->SetXY(20, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks and Numbers - Container No(s) - Number and Kind');

		$this->SetXY(20, 140);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Marks & No');

		$this->SetXY(20, 143);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Packages');

		$this->SetXY(20, 146);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Number and Kind');

		$this->SetXY(20, 149);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 152.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Goods Desc.');

		/* End Box 31 */

		/* Start Box 44 */

		$this->SetXY(5, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, '44  Add Infos');

		$this->SetXY(5, 173);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Doc / Product');

		$this->SetXY(10.5, 176);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Certif &');

		$this->SetXY(14, 179);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Aut');

		$this->SetXY(20, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'OTHinEV : ');

		$this->SetXY(55, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'INSinFRT : ');

		$this->SetXY(90, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Fine : ');

		$this->SetXY(20, 182);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Invoice No. : ');

		/* End Box 44 */
		
		/* Start Box 32 */
		
		$this->SetXY(118, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, '32  Item No.');
		
		/* End Box 32 */

		/* Start Box 33 */
		
		$this->SetXY(138, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, '33  Hs Code');

		$this->SetXY(169, 137);
		$this->SetFont('Arial','',6);
		//$this->Write(0, 'Tar Spec');
		
		$this->SetXY(185, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Spec Code');
		
		/* End Box 33 */

		/* Start Box 34 */

		$this->SetXY(118, 145.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '34  C.O. Code');

		/* End Box 34 */

		/* Start Box 35 */

		$this->SetXY(147.5, 145.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '35  Item Gross Weight');

		/* End Box 35 */

		/* Start Box 36 */

		$this->SetXY(177, 145.2);
		$this->SetFont('Arial','',6);
		$this->Write(0, '36  Pref');

		/* End Box 36 */

		/* Start Box 37 */

		$this->SetXY(118, 153);
		$this->SetFont('Arial','',6);
		$this->Write(0, '37  Procedure');

		/* End Box 37 */

		/* Start Box 38 */

		$this->SetXY(147.5, 153);
		$this->SetFont('Arial','',6);
		$this->Write(0, '38  Item Net Weight');

		/* End Box 38 */

		/* Start Box 39 */

		$this->SetXY(177, 153);
		$this->SetFont('Arial','',6);
		$this->Write(0, '39  Quota');

		/* End Box 39 */

		/* Start Box 40a */

		$this->SetXY(118, 161.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40a  AWB / BL');

		/* End Box 40a */

		/* Start Box 40b */

		$this->SetXY(162, 161.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '40b  Previous Doc No.');

		/* End Box 40b */

		/* Start Box 41 */

		$this->SetXY(118, 170);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 170);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 170);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 46 */

		//$this->SetXY(118, 178);
		$this->SetXY(158, 180);
		$this->SetFont('Arial','',6);
		$this->Write(0, '46  Dutiable Value (PHP)');
		
		//$this->SetXY(158, 178);
		$this->SetXY(158, 175);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'A.I. Code');

		/* End Box 46 */

		/* Start Box 45 */

		//$this->SetXY(172, 178);
		$this->SetXY(172, 175);
		$this->SetFont('Arial','',4.5);
		$this->Write(0, '45  Adjustment');

		/* End Box 45 */

		$this->SetXY(118, 180);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MSP');
		
		$this->SetXY(118, 135);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 143.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 151.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		//$this->SetXY(118, 168);
		$this->SetXY(118, 165);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');
		
		//$this->SetXY(118, 68);
		$this->SetXY(118, 170);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 135);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');

		$this->SetXY(118, 143.25);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(147.7, 143.25);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 151.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		//$this->SetXY(118, 154.50);
		$this->SetXY(120, 178.5);
		$this->SetFont('Times','',20);
		//$this->Cell(13.5,5.24,'','R',0,'C');
		$this->Cell(34,6.5,'','R',0,'C');

		$this->SetXY(147.7, 151.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 159.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');

		$this->SetXY(118, 168);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');

		$this->SetXY(152, 168);
		$this->SetFont('Times','',20);
		//$this->Cell(34,8.24,'','R',0,'C');
		$this->Cell(34,5,'','R',0,'C');

		//$this->SetXY(152, 176.25);
		$this->SetXY(152, 173);
		$this->SetFont('Times','',20);
		//$this->Cell(20,8.24,'','R',0,'C');
		$this->Cell(20,5,'','R',0,'C');

		$this->SetXY(5, 168);
		$this->SetFont('Times','',20);
		$this->Cell(202,17,'','TB',0,'C');

		$this->SetXY(5, 185);
		$this->SetFont('Times','',20);
		$this->Cell(202,50,'','TB',0,'C');

		/* End Box 8 */

		/* Start BOX 1 */

		$this->SetXY(102, 13);
		$this->SetFont('Arial','',20);
		$this->Cell(30,12,'','1',0,'');
		$this->SetXY(102, 13);
		$this->SetFont('Arial','',20);
		$this->Cell(30,12,'','1',0,'');

		$this->SetXY(102, 15);
		$this->SetFont('Arial','',6);
		$this->Write(0, '1  DECLARATION');

		$this->SetXY(105, 22);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['MDec']);

		$this->SetXY(112, 19);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		$this->SetXY(112, 22);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['FIN_data']['Mdec2']);

		$this->SetXY(122, 19);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		/* END BOX 1 */


		/* Start BOX 3 */
		
		$this->SetXY(102, 25);
		$this->SetFont('Arial','',20);
		$this->Cell(30,10,'','R',0,'');

		$this->SetXY(102, 27);
		$this->SetFont('Arial','',6);
		$this->Write(0, '3  Page');

		$this->SetXY(109, 30);
		$this->SetFont('Arial','',20);
		$this->Cell(30,5,'','L',0,'');

		$this->SetXY(111, 32);
		$this->SetFont('Arial','',8);
		$this->Write(0, $data['row_count']);


		/* END BOX 3 */

		/* Start BOX 4 */

		$this->SetXY(118, 25);
		$this->SetFont('Arial','',20);
		$this->Cell(30,10,'','L',0,'');

		$this->SetXY(118, 27);
		$this->SetFont('Arial','',6);
		$this->Write(0, '4');


		/* END BOX 4 */

		/* Start BOX 47 */

		$this->SetXY(5, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, '47  Calculation');

		$this->SetXY(8.5, 190);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Taxes');

		/* Start Item 2 */
		
		$this->SetXY(35, 185);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(75, 185);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(20, 189);
		$this->SetFont('Arial','',20);
		$this->Cell(98,39,'','TB',0,'');

		$this->SetXY(24, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(43, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tax Base');

		$this->SetXY(65, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Rate');

		$this->SetXY(83, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(107.5, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MP');

		$this->SetXY(20, 228);
		$this->SetFont('Arial','',6);
		$this->Cell(82,7,'Total first item of this rider',0,0,'C');

		/* End Item 2 */

		/* Start Item 3 */
		
		$this->SetXY(133, 185);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(171, 185);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(118, 189);
		$this->SetFont('Arial','',20);
		$this->Cell(89,39,'','TB',0,'');

		$this->SetXY(122, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(140.5, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tax Base');

		$this->SetXY(162, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Rate');

		$this->SetXY(179, 187);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(198, 185);
		$this->SetFont('Arial','',6);
		$this->Cell(9,4,'MP',0,0,'C');

		$this->SetXY(118, 228);
		$this->SetFont('Arial','',6);
		$this->Cell(80,7,'Total second item of this rider',0,0,'C');

		/* End Item 3 */

		/* Start Item 4 */
		
		$this->SetXY(35, 235);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(75, 235);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(20, 239);
		$this->SetFont('Arial','',20);
		$this->Cell(98,39,'','TB',0,'');

		$this->SetXY(24, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(43, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tax Base');

		$this->SetXY(65, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Rate');

		$this->SetXY(83, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(107.5, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MP');

		$this->SetXY(20, 278);
		$this->SetFont('Arial','',6);
		$this->Cell(82,7,'Total third item of this rider',0,0,'C');

		/* End Item 4 */

		$this->SetXY(133, 235);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','LR',0,'');

		$this->SetXY(176, 235);
		$this->SetFont('Arial','',20);
		$this->Cell(27,43,'','L',0,'');

		$this->SetXY(118, 239);
		$this->SetFont('Arial','',20);
		$this->Cell(89,39,'','TB',0,'');

		$this->SetXY(122, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(141, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(165, 237);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MP');

		$this->SetXY(123, 278);
		$this->SetFont('Arial','',6);
		$this->Cell(80,7,'G.T.',0,0,'L');

		/* END BOX 47 */
		
	}

	public function rider_data($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD){

		$ext = array(
				'001',
				'011', 
				'021', 
				'026', 
				'054', 
				'056', 
				'058',
				'060',
				'0N2',
				'0N4',
				'0R1',
				'101',
				'201',
				'211',
				'301',
				'311',
				'401',
				'501',
				'511',
				'601',
				'611',
				'701',
				'801',
				'DPS',
				'E01',
				'L03',
				'L05',
				'L07',
				'M00',
				'M20',
				'M30',
				'M40',
				'M50',
				'M60',
				'N21',
				'N30',
				'N31',
				'N36',
				'N41',
				'N46',
				'N51',
				'N56',
				'N61',
				'N71',
				'N81',
				'N91',
				'P01',
				'P10',
				'P20',
				'P23',
				'P25',
				'P30',
				'P40',
				'P50',
				'P60',
				'P71',
				'P81',
				'P91',
				'P92',
				'R01',
				'R04',
				'R06',
				'R08',
				'R10',
				'R13',
				'R14',
				'R16',
				'R18',
				'R20',
				'R23',
				'R24',
				'R26',
				'R28',
				'R31',
				'R32',
				'R34',
				'R36',
				'R39',
				'R40',
				'R42',
				'R44',
				'R46',
				'R48',
				'R50',
				'R52',
				'R54',
				'R57',
				'R58',
				'R63',
				'R65',
				'R67',
				'R69',
				'R70',
				'R73',
				'R75',
				'R77',
				'R79',
				'R80',
				'R83',
				'R85',
				'R87',
				'R89',
				'R90',
				'R93',
				'R95',
				'R97',
				'R99',
				'T01',
				'T05',
				'T11',
				'T14',
				'T16',
				'T18',
				'T20',
				'T22',
				'T24',
				'T26',
				'T28',
				'T31',
				'T34',
				'T36',
				'T38',
				'T40',
				'T42',
				'T44',
				'T46',
				'T49',
				'T50',
				'T53',
				'T55',
				'T57',
				'T60',
				'T70',
				'T80',
				'T90',
				'TE1',
				'TN2',
				'TN4',
				'TN6',
				'TN8',
				'L09',
				'LK2Z',
				'L13');
		$y_2ndpage = 40;
		$CUD = array();
		$Cbs = array();
		$VAT = array();
		$AVT = array();
		$Vbs = array();
		foreach ($RespIT as $key => $items) {
			if ($items['ITEMNO'] != 1) {
				if ($items['TAXCODE'] == 'CUD') {
					$CUDAMOUNT = $items['TAXAMT'];
					$CUD[] = $CUDAMOUNT;
				}

				if ($items['TAXCODE'] == 'Cbs') {
					$CUDBASE = $items['TAXAMT'];
					$Cbs[] = $CUDBASE;
				}
				

				if ($items['TAXCODE'] == 'VAT') {
					$VATAMOUNT = $items['TAXAMT'];
					$VAT[] = $VATAMOUNT;
				}
				if ($items['TAXCODE'] == 'AVT') {
					$AVTAMOUNT = $items['TAXAMT'];
					$AVT[] = $AVTAMOUNT;
				}

				if ($items['TAXCODE'] == 'Vbs') {
					$VATBASE = $items['TAXAMT'];
					$Vbs[] = $VATBASE;
				}
			}
		}

		$c = 0;
		foreach ($CUD as $key => $CUDs) {
			$c ++;
			$FIN_multi[$c]['CUD'] = $CUDs;
		}

		$cb = 0;
		foreach ($Cbs as $key => $Cbss) {
			$cb ++;
			$FIN_multi[$c]['Cbs'] = $Cbss;
		}

		$v = 0;
		foreach ($VAT as $key => $VATs) {
			$v ++;
			$FIN_multi[$v]['VAT'] = $VATs;
		}
		$avt_muli = 0;
		foreach ($AVT as $key => $AVTs) {
			$avt_muli ++;
			$FIN_multi[$avt_muli]['AVT'] = $AVTs;
		}

		$vb = 0;
		foreach ($Vbs as $key => $Vbss) {
			$v ++;
			$FIN_multi[$vb]['Vbs'] = $Vbss;
		}

		$dutiable_value_total = 0;
		$custom = 0;


		foreach ($FIN_multi as $key => $FIN_multis_total) {
			if($FIN_multis_total['InvCurr'] == 'PHP' && $FIN_multis_total['CustCurr'] == 'PHP' && $FIN_multis_total['FreightCurr'] == 'PHP' && $FIN_multis_total['InsCurr'] == 'PHP' && $FIN_multis_total['OtherCurr'] == 'PHP'){
				$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost']))),2);
				$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']))),2);
				$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']))),2);
				$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue'])),2);

				$FIO_total = $freight_total + $inscost_total + $othercost_total;
				$dutiable_value_total += $FIO_total + $invval_total;
				$custom = round((str_replace(',', '', $FIN_multis_total['CustomVal'])),2);
				
			}else{
				//05032024: SPagara: Round up update
				//$freight_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['FreightCost'])) * $FIN_multis_total['FExchRate']),2);
				//$inscost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost'])) * $FIN_multis_total['IExchRate']),2);
				//$othercost_total = round((((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost'])) * $FIN_multis_total['OExchRate']),2);

				$freight_total = Round(((Round(str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal']) * str_replace(',', '', $FIN_multis_total['FreightCost']), 2)) * $FIN_multis_total['FExchRate']),2, PHP_ROUND_HALF_UP);
				$inscost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['InsCost']), 2) * $FIN_multis_total['IExchRate']),2, PHP_ROUND_HALF_UP);
				$othercost_total = Round((Round((str_replace(',', '', $FIN_multis_total['InvValue']) / str_replace(',', '', $FIN_multis_total['CustomVal'])) * str_replace(',', '', $FIN_multis_total['OtherCost']), 2) * $FIN_multis_total['OExchRate']),2, PHP_ROUND_HALF_UP);

				$invval_total = round((str_replace(',', '', $FIN_multis_total['InvValue']) * $FIN_multis_total['ExchRate']),2);

				$FIO_total = $freight_total + $inscost_total + $othercost_total;

				$dutiable_value_total += $FIO_total + $invval_total;
				
				$custom = round((str_replace(',', '', $FIN_multis_total['CustomVal']) * $FIN_multis_total['ExchRate']),2);
			}
		}

		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			if ($custom > 10000) {
				$this->Image('DEMINIMIS.png',4,30,200);
			}
		}



		/* Start Broker Fee*/
		$Broker_Fee = 0;
		if($dutiable_value_total >= 0 && $dutiable_value_total <= 10000){$Broker_Fee = 1300;}
		if($dutiable_value_total >= 10001 && $dutiable_value_total <= 20000){$Broker_Fee = 2000;}
		if($dutiable_value_total >= 20001 && $dutiable_value_total <= 30000){$Broker_Fee = 2700;}
		if($dutiable_value_total >= 30001 && $dutiable_value_total <= 40000){$Broker_Fee = 3300;}
		if($dutiable_value_total >= 40001 && $dutiable_value_total <= 50000){$Broker_Fee = 3600;}
		if($dutiable_value_total >= 50001 && $dutiable_value_total <= 60000){$Broker_Fee = 4000;}
		if($dutiable_value_total >= 60001 && $dutiable_value_total <= 100000){$Broker_Fee = 4700;}
		if($dutiable_value_total >= 100001 && $dutiable_value_total <= 200000){$Broker_Fee = 5300;}
		if($dutiable_value_total >= 200001){$Broker_Fee = ($dutiable_value_total - 200000) * 0.00125 + 5300;}
		/* End Broker Fee */

		/* Start IPF */
		$IPF_val = 0;
		/*06062024: SPagara: update on IPF
		if($dutiable_value_total >= 0 && $dutiable_value_total <= 250000){$IPF_val = 250;}
		if($dutiable_value_total >= 250001 && $dutiable_value_total <= 500000){$IPF_val = 500;}
		if($dutiable_value_total >= 500001 && $dutiable_value_total <= 750000){$IPF_val = 750;}
		if($dutiable_value_total >= 750001 && $dutiable_value_total <= 999999999999){$IPF_val = 1000;}*/
		
		if($dutiable_value_total >= 0 && $dutiable_value_total <= 250000){$IPF_val = 250;}
		if($dutiable_value_total > 25000 && $dutiable_value_total <= 50000){$IPF_val = 500;}
		if($dutiable_value_total > 50000 && $dutiable_value_total <= 250000){$IPF_val = 750;}
		if($dutiable_value_total > 250000 && $dutiable_value_total <= 500000){$IPF_val = 1000;}
		if($dutiable_value_total > 500000 && $dutiable_value_total <= 750000){$IPF_val = 1500;}
		if($dutiable_value_total > 750000 ){$IPF_val = 2000;}

		if (($data['FIN_data']['MDec'] == '8ZN') || ($data['FIN_data']['MDec'] == '8PP') || ($data['FIN_data']['MDec'] == '8PE') || ($data['FIN_data']['MDec'] == '8ZE')){
			$IPF_val = 250;
		}
		
		/* End IPF*/

		/* Start Bank Charge */
		if($FIN_multi[0]['WOBankCharge'] == 1){
			$BANKCHARGE = 0;
		}else{
			$BANKCHARGE = $dutiable_value_total * 0.00125;
		}
		/* End Bank Charge */
		
		$CUD = array();
		$Cbs = array();
		$VAT = array();
		$AVT = array();
		$Vbs = array();
		$Freight = array();
		$Insurance = array();
		$OtherCost = array();
		$Wharfage = array();
		$Arrastre = array();
		$InvValue = array();
		foreach ($RespIT as $key => $items) {
			if ($items['TAXCODE'] == 'CUD') {
				$CUDAMOUNT = $items['TAXAMT'];
				$CUD[] = $CUDAMOUNT;
			}

			if ($items['TAXCODE'] == 'Cbs') {
				$CUDBASE = $items['TAXAMT'];
				$Cbs[] = $CUDBASE;
			}

			if ($items['TAXCODE'] == 'VAT') {
				$VATAMOUNT = $items['TAXAMT'];
				$VAT[] = $VATAMOUNT;
			}
			if ($items['TAXCODE'] == 'AVT') {
				$AVTAMOUNT = $items['TAXAMT'];
				$AVT[] = $AVTAMOUNT;
			}

			if ($items['TAXCODE'] == 'Vbs') {
				$VATBASE = $items['TAXAMT'];
				$Vbs[] = $VATBASE;
			}

			if ($items['TAXCODE'] == 'EFR') {
				$EFRAMOUNT = $items['TAXAMT'];
				$Freight[] = $EFRAMOUNT;
			}

			if ($items['TAXCODE'] == 'INS') {
				$INSAMOUNT = $items['TAXAMT'];
				$Insurance[] = $INSAMOUNT;
			}

			if ($items['TAXCODE'] == 'OTH') {
				$OTHAMOUNT = $items['TAXAMT'];
				$OtherCost[] = $OTHAMOUNT;
			}

			if ($items['TAXCODE'] == 'IFR') {
				$IFRAMOUNT = $items['TAXAMT'];
				$Wharfage[] = $IFRAMOUNT;
			}

			if ($items['TAXCODE'] == 'DED') {
				$DEDAMOUNT = $items['TAXAMT'];
				$Arrastre[] = $DEDAMOUNT;
			}

			if ($items['TAXCODE'] == 'INV') {
				$INVAMOUNT = $items['TAXAMT'];
				$InvValue[] = $INVAMOUNT;
			}
		}
		$c = 0;
		foreach ($CUD as $key => $CUDs) {
			$c ++;
			$FIN_multi[$c - 1]['CUD'] = $CUDs;
		}

		$cb = 0;
		foreach ($Cbs as $key => $Cbss) {
			$cb ++;
			$FIN_multi[$cb - 1]['Cbs'] = $Cbss;
		}

		$v = 0;
		foreach ($VAT as $key => $VATs) {
			$v ++;
			$FIN_multi[$v - 1]['VAT'] = $VATs;
		}
		$avt_muli = 0;
		foreach ($AVT as $key => $AVTs) {
			$avt_muli ++;
			$FIN_multi[$avt_muli - 1]['MSP'] = $AVTs;
		}

		$vb = 0;
		foreach ($Vbs as $key => $Vbss) {
			$vb ++;
			$FIN_multi[$vb - 1]['Vbs'] = $Vbss;
		}

		$f = 0;
		foreach ($Freight as $key => $Freights) {
			$f ++;
			$FIN_multi[$f - 1]['Freight'] = $Freights;
		}

		$i = 0;
		foreach ($Insurance as $key => $Insurances) {
			$i ++;
			$FIN_multi[$i - 1]['Insurance'] = $Insurances;
		}

		$o = 0;
		foreach ($OtherCost as $key => $OtherCosts) {
			$o ++;
			$FIN_multi[$o - 1]['Other_cost'] = $OtherCosts;
		}

		$w = 0;
		foreach ($Wharfage as $key => $Wharfages) {
			$w ++;
			$FIN_multi[$w - 1]['Wharfage'] = $Wharfages;
		}

		$a = 0;
		foreach ($Arrastre as $key => $Arrastres) {
			$a ++;
			$FIN_multi[$a - 1]['Arrastre'] = $Arrastres;
		}

		$iv = 0;
		foreach ($InvValue as $key => $InvValues) {
			$iv ++;
			$FIN_multi[$iv - 1]['InvVal'] = $InvValues;
		}

		$counter = 0;
		$item_count = 0;
		$len = count($FIN_multi);
		$itemnums4 = array();	
		$itemnums3 = array();	
		$itemnums2 = array();	
		$start = 4;	
		$start3 = 3;	
		$start2 = 2;	
		while($start <= 1000) {	
			$itemnums4[] = (string)$start;	
			$start+=3;	
		}	
		while($start3 <= 1000) {	
			$itemnums3[] = (string)$start3;	
			$start3+=3;	
		}	
		while($start2 <= 1000) {	
			$itemnums2[] = (string)$start2;	
			$start2+=3;	
		}
		foreach ($FIN_multi as $key => $FIN_multis) {
			$y_2ndpage += 50;
			$item_count ++;
			if ($FIN_multis['ItemNo'] != 1) {
				$counter ++;
			
				/* Start Marks & Numbers */

				$this->SetXY(40, $y_2ndpage - 100);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Marks1']);

				$this->SetXY(40, $y_2ndpage - 97);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Marks2']);

				/* End Marks & Numbers */

				/* Start Number and Kind */

				$this->SetXY(40, $y_2ndpage - 94);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['NoPack']);

				$this->SetXY(53, $y_2ndpage - 94);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['PackCode']);

				$this->SetXY(59, $y_2ndpage - 94);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['pkg_dsc']);

				/* End Number and Kind */

				/* Start Container No(s) */

				$this->SetXY(40, $y_2ndpage - 91.5);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Cont1']);

				$this->SetXY(59, $y_2ndpage - 91.5);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Cont2']);

				$this->SetXY(77, $y_2ndpage - 91.5);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Cont3']);

				$this->SetXY(96, $y_2ndpage - 91.5);
				$this->SetFont('Arial','',7);
				$this->Write(0, $FIN_multis['Cont4']);

				/* End Container No(s) */

				/* Start Goods Desc */
				
				$this->SetXY(35, $y_2ndpage - 89);
				$this->SetFont('Arial','',7);
				$this->MultiCell(80,2,substr($FIN_multis['tar_dsc'],0,53),0,'L');
				
				$this->SetXY(35, $y_2ndpage - 86.5);
				$this->SetFont('Arial','',7);
				
				$hscods = $FIN_multis['HSCode'];
				$hscodts = $FIN_multis['HSCODE_TAR'];
				$spccods = $FIN_multis['SupUnit2'];
				$serverNamess = '192.168.1.81,1477';//'COMINS'; //serverName\instanceName, portNumber (default is 1433)
				$connectionINSCUSTSTDBss = array( "Database"=>'PL-INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
				$conn_INSCUSTSTDBss = sqlsrv_connect( $serverNamess, $connectionINSCUSTSTDBss);
				$datass = array();
				$containersss = "SELECT DISTINCT b.spc_dsc FROM GBTARTAB a INNER JOIN GBSPECTAB b ON a.hs6_cod = b.hs6_cod AND a.tar_pr1 = b.tar_pr1 AND a.tar_pr2 = b.tar_pr2 WHERE a.hs6_cod + a.tar_pr1='$hscods' AND a.tar_pr2='$hscodts' AND b.spc_cod = '$spccods' AND a.EEA_EOV = ''";
				
				$stmt_datass = sqlsrv_query($conn_INSCUSTSTDBss, $containersss);
				if($stmt_datass == false)
				{
					 echo "Error in query preparation/execution.\n";
					 die( print_r( sqlsrv_errors(), true));
				}else{
					while( $rowsss = sqlsrv_fetch_array( $stmt_datass, SQLSRV_FETCH_ASSOC))
					{	
						$datass['$containersss'] = $rowsss;
					}
				}
				if(!empty($datass['$containersss'])){
					$this->MultiCell(80,2.5,substr($datass['$containersss']['spc_dsc'], 0, 65),0,'L');
				}

				$this->SetXY(35, $y_2ndpage - 84.5);
				$this->SetFont('Arial','',7);
				$this->MultiCell(80,2.5,substr(strtoupper($FIN_multis['GoodsDesc']), 0, 255),0,'L');

				$this->SetXY(35, $y_2ndpage - 83);
				$this->SetFont('Arial','B',6);
				$this->MultiCell(80,2,$FIN_multis['gDesc2'],0,'L');

				$this->SetXY(35, $y_2ndpage - 81);
				$this->SetFont('Arial','B',6);
				$this->MultiCell(80,2, $FIN_multis['gDesc3'],0,'L');

				/* End Goods Desc */

				/* Start 32  Item No. */

				$this->SetXY(125, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['ItemNo']);

				/* End 32  Item No. */

				/* Start 33 Hs Code */

				$this->SetXY(140, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['HSCode']);

				$this->SetXY(155, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['HSCODE_TAR']);

				$this->SetXY(170, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				//$this->Write(0, $FIN_multis['TARSPEC']);

				$this->SetXY(187, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['SupUnit2']);

				/* End 33 Hs Code */

				/* Start 34 C.O. Code */

				$this->SetXY(129, $y_2ndpage - 91);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['COCode']);

				/* End 34 C.O. Code */

				/* Start 35 Item Gross Weight */

				$this->SetXY(147.7, $y_2ndpage - 95);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,number_format($FIN_multis['ItemGWeight'],2).' KG',0,0,'R');

				/* End 35 Item Gross Weight */

				/* Start 36 Pref */

				$this->SetXY(177.3, $y_2ndpage - 95);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,$FIN_multis['Pref'],0,0,'C');

				/* End 36 Pref */

				/* Start 37 Procedure */

				$this->SetXY(121, $y_2ndpage - 83);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['ProcDsc']);

				$this->SetXY(136, $y_2ndpage - 83);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['ExtCode']);

				/* End 37 Procedure */

				/* Start 38 Item Net Weight */

				$this->SetXY(147.7, $y_2ndpage - 87);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,number_format($FIN_multis['ItemNweight'],2).' KG',0,0,'R');

				/* End 38 Item Net Weight */

				/* Start 39 Quota */
				if ($data['FIN_data']['MDec'] != 'ID'){
					$this->SetXY(177.3, $y_2ndpage - 87);
					$this->SetFont('Arial','',8);
					$this->Cell(29.7,8,$FIN_multis['quo_cod'],0,0,'C');
				}

				/* End 39 Quota */

				/* Start 40a AWB / BL */

				$this->SetXY(118, $y_2ndpage - 79);
				$this->SetFont('Arial','',8);
				$this->Cell(44.6,8,$FIN_multis['AirBill'],0,0,'C');

				/* End 40a AWB / BL */

				/* Start 40b Previous Doc No. */

				$this->SetXY(162.4, $y_2ndpage - 79);
				$this->SetFont('Arial','',8);
				$this->Cell(44.6,8,$FIN_multis['PrevDoc'],0,0,'C');

				/* End 40b Previous Doc No. */
				
				/* Start 41 Suppl. Units */
				//$data['HScode_new'] = substr($data['FIN_data']['HSCode'],0,6);
				//$data['HScode_new_TARPR1'] = substr($data['FIN_data']['HSCode'],6,8);
				//$uom_Val =  SupVal($data['HScode_new'],$data['HScode_new_TARPR1'],$data['FIN_data']['HSCODE_TAR']);
				//$this->SetXY(119, $y_2ndpage - 66.5);
				$this->SetXY(119, $y_2ndpage - 68);
				$this->SetFont('Arial','',6);
				if($FIN_multis['SupUnit1'] == ''){
				$this->Write(0, '');
				}else{
				//$data['HScode_new'] = substr($data['FIN_data']['HSCode'],0,6);
				//$data['HScode_new_TARPR1'] = substr($data['FIN_data']['HSCode'],6,8);
				//$cONS = get_CONs($data['FIN_data']['ApplNo'],$data['FIN_data']['ItemNo']);
				$this->Write(0,$FIN_multis['SupUnit1'].'  '.number_format($FIN_multis['SupVal1'],2),0,0,'R');
				/*$this->Write(0, 'test');*/
				}
				

				/* End 41 Suppl. Units */

				/* Start 42 Item Customs Value (F. Cur) */

				//$this->SetXY(152, $y_2ndpage - 70.5);
				$this->SetXY(152, $y_2ndpage - 72);
				$this->SetFont('Arial','',6);
				$this->Cell(34,8,number_format($FIN_multis['InvValue'],2),0,0,'R');

				/* End 42 Item Customs Value (F. Cur) */

				/* Start 43 V.M */

				if ($data['FIN_data']['MDec'] != 'ID'){
					//$this->SetXY(186, $y_2ndpage - 70.5);
					$this->SetXY(186, $y_2ndpage - 72);
					$this->SetFont('Arial','',6);
					$this->Cell(20,8,$FIN_multis['ValMethodNum'],0,0,'R');
				}

				/* End 43 V.M */

				/* Start OTHinEV */

				$this->SetXY(35, $y_2ndpage - 70);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['OCharges']);

				/* End OTHinEV */

				/* Start INSinFRT */

				$this->SetXY(76, $y_2ndpage - 70);
				$this->SetFont('Arial','',8);
				$this->Write(0, $FIN_multis['IFreight']);

				/* End INSinFRT */

				/* Start INSinFRT */

				// $this->SetXY(50, $y_2ndpage - 70);
				// $this->SetFont('Arial','',8);
				// $this->Write(0, $FIN_multis['IFreight']);

				/* End INSinFRT */


				/* Start FIO */

				if($FIN_multis['InvCurr'] == 'PHP' && $FIN_multis['CustCurr'] == 'PHP' && $FIN_multis['FreightCurr'] == 'PHP' && $FIN_multis['InsCurr'] == 'PHP' && $FIN_multis['OtherCurr'] == 'PHP'){
					if (@$FIN_multis['Freight'] != null) {
						$freight = $FIN_multis['Freight'];
					}else{
						$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost']))),2);
					}

					if (@$FIN_multis['Insurance'] != null) {
						$inscost = $FIN_multis['Insurance'];
					}else{
						$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']))),2);
					}

					if (@$FIN_multis['Other_cost'] != null) {
						$othercost = $FIN_multis['Other_cost'];
					}else{
						$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']))),2);
					}

					if (@$FIN_multis['InvVal'] != null) {
						$invval = $FIN_multis['InvVal'];
					}else{
						$invval = round((str_replace(',', '', $FIN_multis['InvValue'])),2);
					}

					$FIO = $freight + $inscost + $othercost;
					$dutiable_value = $FIO + $invval;
					
				}else{
					if (@$FIN_multis['Freight'] != null) {
						$freight = $FIN_multis['Freight'];
					}else{
						//06062024:Spagara: Round up update
						//$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost'])) * $FIN_multis['FExchRate']),2);
						$freight = Round(((Round(str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal']) * str_replace(',', '', $FIN_multis['FreightCost']), 3)) * $FIN_multis['FExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['Insurance'] != null) {
						$inscost = $FIN_multis['Insurance'];
					}else{
						//06062024:Spagara: Round up update
						//$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost'])) * $FIN_multis['IExchRate']),2);
						$inscost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']), 3) * $FIN_multis['IExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['Other_cost'] != null) {
						$othercost = $FIN_multis['Other_cost'];
					}else{
						//06062024:Spagara: Round up update
						//$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost'])) * $FIN_multis['OExchRate']),2);
						$othercost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']), 3) * $FIN_multis['OExchRate']),2, PHP_ROUND_HALF_UP);
					}

					if (@$FIN_multis['InvVal'] != null) {
						$invval = $FIN_multis['InvVal'];
					}else{
						$invval = round((str_replace(',', '', $FIN_multis['InvValue']) * $FIN_multis['ExchRate']),2);
					}

					$FIO = $freight + $inscost + $othercost;

					$dutiable_value = $FIO + $invval;
				}

				/* Start WHarfage and Arrastre Computation */

				$whar = (str_replace(',', '', $FIN_multis['WharCost']));
				$arras = (str_replace(',', '', $FIN_multis['ArrasCost']));
			
				/* Start Wharfage Computation */
				$whar = round((str_replace(',', '', $FIN_multis['InvValue'])/str_replace(',', '', $FIN_multis['CustomVal']) * $whar),2);
				
				/* End Wharfage Computation */

				/* Start Arrastre Computation */

				$arras = round((str_replace(',', '', $FIN_multis['InvValue'])/str_replace(',', '', $FIN_multis['CustomVal']) * $arras),2);

				/* End Arrastre Computation */

				if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
					$wharfage = $whar;
					$arrastre = $arras;
				}else{
					if (@$FIN_multis['Wharfage'] != null) {
						$wharfage = $FIN_multis['Wharfage'];
					}

					if (@$FIN_multis['Arrastre'] != null) {
						$arrastre = $FIN_multis['Arrastre'];
					}
				}

				/* End WHarfage and Arrastre Computation */

				

				
				if ($data['FIN_data']['OffClearance'] == 'Ninoy Aquino Intl Airport ') {
					$wharfage = 0;
					$arrastre = 0;
				}
				/* Start Freight */

				$this->SetXY(20, $y_2ndpage - 67);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,number_format(str_replace(',', '', $freight), 2),0,0,'R');

				/* End Freight */

				/* Start Insurance */

				$this->SetXY(39, $y_2ndpage - 67);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,'+ '.number_format(str_replace(',', '', $inscost), 2),0,0,'R');

				/* End Insurance */

				/* Start Other Charges */

				$this->SetXY(58, $y_2ndpage - 67);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,'+ '.number_format(str_replace(',', '', $othercost), 2),0,0,'R');

				/* End Other Charges */

				/* Start Wharfage */

				$this->SetXY(77, $y_2ndpage - 67);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,'+ '.number_format(str_replace(',', '', $wharfage), 2),0,0,'R');

				/* End Wharfage */

				/* Start Arrastre */
				
				$this->SetXY(96, $y_2ndpage - 67);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,'- '.number_format(str_replace(',', '', $arrastre), 2),0,0,'R');
				

				/* End Arrastre */

				/* Start Invoice No. */

				$this->SetXY(32, $y_2ndpage - 62);
				$this->SetFont('Arial','',8);
				$this->Cell(19,8,$FIN_multis['InvNo'],0,0,'L');

				/* End Invoice No. */

				/* End FIO */

				/* Start 48 Dutiable Value (PHP)  */
				
				$dutiable_value = $dutiable_value;


				/* Start Doc Fee */

				if ($FIN_multis['InvCurr'] == 'PHP' && $FIN_multis['CustCurr'] == 'PHP') {
					$InvValue = str_replace(',', '', $FIN_multis['InvValue']);
					$CustomVal = str_replace(',', '', $FIN_multis['CustomVal']);
				}else{
					$InvValue = str_replace(',', '', $FIN_multis['InvValue']) * $FIN_multis['ExchRate'];
					$CustomVal = str_replace(',', '', $FIN_multis['CustomVal']) * $FIN_multis['ExchRate'];
				}

				if ($data['FIN_data']['MDec'] != 'IES') {
				//$DOCFEE = round((($InvValue/$CustomVal) * 265),2);
				//$DOCFEE = round((($InvValue/$CustomVal) * 280),2);
					//$DOCFEE = round((280/$data['max_rows']),2);
					$DOCFEE = round((130/$data['max_rows']),2);
				}else {
					//$DOCFEE = 30 / $data['max_rows'];
					//$DOCFEE = round((($InvValue/$CustomVal) * 30),2);
					$DOCFEE = round((130/$data['max_rows']),2);
				}
				/* End Doc Fee */

				

				/* Start VAT Computation */
				if ($data['FIN_data']['MDec'] == 'ID' || $data['FIN_data']['MDec'] == 'IES'){
					//$BrokerFee = 700 / $data['max_rows'];
					//$BrokerFee = round((($InvValue/$CustomVal) * 700),2);
					$BrokerFee = round((700/$data['max_rows']),2);
				}else{
					//$BrokerFee = round((($InvValue/$CustomVal) * round($Broker_Fee,2)),2);
					$BrokerFee = round(($Broker_Fee/$data['max_rows']),2);
				}
				//06062024: Spagara: Update on charges
				//$IPF = round((($InvValue/$CustomVal) * round($IPF_val,2)),2);
				$IPF = round($IPF_val/$data['max_rows'],2);
				//$IPF = round($IPF/$data['max_rows'],2);

				//$BANKCHARGE = round((($InvValue/$CustomVal) * round($BANKCHARGE,2)),2);
				$BANKCHARGE = round($BANKCHARGE/$data['max_rows'],2);

				/*06062024:SPagara: Removed IPF
				if ($data['FIN_data']['MDec'] == '7' || $data['FIN_data']['MDec'] == '7T') {
					$IPF = 250;
				}

				if (($data['FIN_data']['MDec'] == 'IES') || ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4')) {
					$IPF = 0;
				}*/
				

				//$this->SetXY(85, $y_2ndpage - 62);
				$this->SetXY(140, $y_2ndpage - 62);
				$this->SetFont('Arial','',6);
				$this->Cell(54,8,number_format($dutiable_value, 2),0,0,'R');
				
				//$this->SetXY(159, $y_2ndpage - 58);
				//$this->SetXY(159, $y_2ndpage - 13.5);
				$this->SetXY(159, $y_2ndpage - 63);
				$this->SetFont('Arial','',6);
				$this->Write(0, $FIN_multis['TARSPEC']);

				/* End 48 Dutiable Value (PHP)  */
				
				/*MSP*/
				$this->SetXY(85, $y_2ndpage - 62);
				$this->SetFont('Arial','',6);
				$this->Cell(54,8,number_format($FIN_multis['MSP'], 2),0,0,'R');
				
				/* Start 49 Adjustment */
			
				//$this->SetXY(172, $y_2ndpage - 62);
				$this->SetXY(172, $y_2ndpage - 67);
				$this->SetFont('Arial','',6);
				$this->Cell(35,8,$FIN_multis['Adjustment'],0,0,'R');
			
				/* End 49 Adjustment */
				
				/* Start Item 2 */
				if ($FIN_multis['ItemNo'] == '2') {
					$this->SetXY(104, 32);
					$this->SetFont('Arial','B',9);
					$this->Write(0, '2');
				}

				//if ($FIN_multis['ItemNo'] == '2' || $FIN_multis['ItemNo'] == '5' || $FIN_multis['ItemNo'] == '8' || $FIN_multis['ItemNo'] == '11' || $FIN_multis['ItemNo'] == '14' || $FIN_multis['ItemNo'] == '17' || $FIN_multis['ItemNo'] == '20' || $FIN_multis['ItemNo'] == '23' || $FIN_multis['ItemNo'] == '26' || $FIN_multis['ItemNo'] == '29' || $FIN_multis['ItemNo'] == '32' || $FIN_multis['ItemNo'] == '35' || $FIN_multis['ItemNo'] == '38' || $FIN_multis['ItemNo'] == '41' || $FIN_multis['ItemNo'] == '44' || $FIN_multis['ItemNo'] == '47' || $FIN_multis['ItemNo'] == '50' || $FIN_multis['ItemNo'] == '53' || $FIN_multis['ItemNo'] == '56' || $FIN_multis['ItemNo'] == '59' || $FIN_multis['ItemNo'] == '62' || $FIN_multis['ItemNo'] == '65' || $FIN_multis['ItemNo'] == '68' || $FIN_multis['ItemNo'] == '71' || $FIN_multis['ItemNo'] == '74' || $FIN_multis['ItemNo'] == '77' || $FIN_multis['ItemNo'] == '80' || $FIN_multis['ItemNo'] == '83' || $FIN_multis['ItemNo'] == '86' || $FIN_multis['ItemNo'] == '89' || $FIN_multis['ItemNo'] == '92' || $FIN_multis['ItemNo'] == '95' || $FIN_multis['ItemNo'] == '98' || $FIN_multis['ItemNo'] == '101' || $FIN_multis['ItemNo'] == '104' || $FIN_multis['ItemNo'] == '107' || $FIN_multis['ItemNo'] == '110' || $FIN_multis['ItemNo'] == '113' || $FIN_multis['ItemNo'] == '116' || $FIN_multis['ItemNo'] == '119' || $FIN_multis['ItemNo'] == '122' || $FIN_multis['ItemNo'] == '125' || $FIN_multis['ItemNo'] == '128' || $FIN_multis['ItemNo'] == '131' || $FIN_multis['ItemNo'] == '134' || $FIN_multis['ItemNo'] == '137' || $FIN_multis['ItemNo'] == '140' || $FIN_multis['ItemNo'] == '143' || $FIN_multis['ItemNo'] == '146' || $FIN_multis['ItemNo'] == '149' || $FIN_multis['ItemNo'] == '152' || $FIN_multis['ItemNo'] == '155' || $FIN_multis['ItemNo'] == '158' || $FIN_multis['ItemNo'] == '161' || $FIN_multis['ItemNo'] == '164' || $FIN_multis['ItemNo'] == '167' || $FIN_multis['ItemNo'] == '170' || $FIN_multis['ItemNo'] == '173' || $FIN_multis['ItemNo'] == '176' || $FIN_multis['ItemNo'] == '179' || $FIN_multis['ItemNo'] == '182' || $FIN_multis['ItemNo'] == '185' || $FIN_multis['ItemNo'] == '188' || $FIN_multis['ItemNo'] == '191' || $FIN_multis['ItemNo'] == '194' || $FIN_multis['ItemNo'] == '197' || $FIN_multis['ItemNo'] == '200' || $FIN_multis['ItemNo'] == '203' || $FIN_multis['ItemNo'] == '206' || $FIN_multis['ItemNo'] == '209' || $FIN_multis['ItemNo'] == '212' || $FIN_multis['ItemNo'] == '215' || $FIN_multis['ItemNo'] == '218' || $FIN_multis['ItemNo'] == '221' || $FIN_multis['ItemNo'] == '224' || $FIN_multis['ItemNo'] == '227' || $FIN_multis['ItemNo'] == '230' || $FIN_multis['ItemNo'] == '233' || $FIN_multis['ItemNo'] == '236' || $FIN_multis['ItemNo'] == '239' || $FIN_multis['ItemNo'] == '242' || $FIN_multis['ItemNo'] == '245' || $FIN_multis['ItemNo'] == '248' || $FIN_multis['ItemNo'] == '251' || $FIN_multis['ItemNo'] == '254' || $FIN_multis['ItemNo'] == '257' || $FIN_multis['ItemNo'] == '260' || $FIN_multis['ItemNo'] == '263' || $FIN_multis['ItemNo'] == '266' || $FIN_multis['ItemNo'] == '269' || $FIN_multis['ItemNo'] == '272' || $FIN_multis['ItemNo'] == '275' || $FIN_multis['ItemNo'] == '278' || $FIN_multis['ItemNo'] == '281' || $FIN_multis['ItemNo'] == '284' || $FIN_multis['ItemNo'] == '287' || $FIN_multis['ItemNo'] == '290' || $FIN_multis['ItemNo'] == '293' || $FIN_multis['ItemNo'] == '296' || $FIN_multis['ItemNo'] == '299' || $FIN_multis['ItemNo'] == '302' || $FIN_multis['ItemNo'] == '305' || $FIN_multis['ItemNo'] == '308' || $FIN_multis['ItemNo'] == '311' || $FIN_multis['ItemNo'] == '314' || $FIN_multis['ItemNo'] == '317' || $FIN_multis['ItemNo'] == '320' || $FIN_multis['ItemNo'] == '323' || $FIN_multis['ItemNo'] == '326' || $FIN_multis['ItemNo'] == '329' || $FIN_multis['ItemNo'] == '332' || $FIN_multis['ItemNo'] == '335' || $FIN_multis['ItemNo'] == '338' || $FIN_multis['ItemNo'] == '341' || $FIN_multis['ItemNo'] == '344' || $FIN_multis['ItemNo'] == '347' || $FIN_multis['ItemNo'] == '350' || $FIN_multis['ItemNo'] == '353' || $FIN_multis['ItemNo'] == '356' || $FIN_multis['ItemNo'] == '359' || $FIN_multis['ItemNo'] == '362' || $FIN_multis['ItemNo'] == '365' || $FIN_multis['ItemNo'] == '368' || $FIN_multis['ItemNo'] == '371' || $FIN_multis['ItemNo'] == '374' || $FIN_multis['ItemNo'] == '377' || $FIN_multis['ItemNo'] == '380' || $FIN_multis['ItemNo'] == '383' || $FIN_multis['ItemNo'] == '386' || $FIN_multis['ItemNo'] == '389' || $FIN_multis['ItemNo'] == '392' || $FIN_multis['ItemNo'] == '395' || $FIN_multis['ItemNo'] == '398' || $FIN_multis['ItemNo'] == '401' || $FIN_multis['ItemNo'] == '404' || $FIN_multis['ItemNo'] == '407' || $FIN_multis['ItemNo'] == '410' || $FIN_multis['ItemNo'] == '413' || $FIN_multis['ItemNo'] == '416' || $FIN_multis['ItemNo'] == '419' || $FIN_multis['ItemNo'] == '422' || $FIN_multis['ItemNo'] == '425' || $FIN_multis['ItemNo'] == '428' || $FIN_multis['ItemNo'] == '431' || $FIN_multis['ItemNo'] == '434' || $FIN_multis['ItemNo'] == '437' || $FIN_multis['ItemNo'] == '440' || $FIN_multis['ItemNo'] == '443' || $FIN_multis['ItemNo'] == '446' || $FIN_multis['ItemNo'] == '449' || $FIN_multis['ItemNo'] == '452' || $FIN_multis['ItemNo'] == '455' || $FIN_multis['ItemNo'] == '458' || $FIN_multis['ItemNo'] == '461' || $FIN_multis['ItemNo'] == '464' || $FIN_multis['ItemNo'] == '467' || $FIN_multis['ItemNo'] == '470' || $FIN_multis['ItemNo'] == '473' || $FIN_multis['ItemNo'] == '476' || $FIN_multis['ItemNo'] == '479' || $FIN_multis['ItemNo'] == '482' || $FIN_multis['ItemNo'] == '485' || $FIN_multis['ItemNo'] == '488' || $FIN_multis['ItemNo'] == '491' || $FIN_multis['ItemNo'] == '494' || $FIN_multis['ItemNo'] == '497' || $FIN_multis['ItemNo'] == '500' || $FIN_multis['ItemNo'] == '503' || $FIN_multis['ItemNo'] == '506' || $FIN_multis['ItemNo'] == '509' || $FIN_multis['ItemNo'] == '512' || $FIN_multis['ItemNo'] == '515' || $FIN_multis['ItemNo'] == '518' || $FIN_multis['ItemNo'] == '521' || $FIN_multis['ItemNo'] == '524' || $FIN_multis['ItemNo'] == '527' || $FIN_multis['ItemNo'] == '530' || $FIN_multis['ItemNo'] == '533' || $FIN_multis['ItemNo'] == '536' || $FIN_multis['ItemNo'] == '539' || $FIN_multis['ItemNo'] == '542' || $FIN_multis['ItemNo'] == '545' || $FIN_multis['ItemNo'] == '548' || $FIN_multis['ItemNo'] == '551' || $FIN_multis['ItemNo'] == '554' || $FIN_multis['ItemNo'] == '557' || $FIN_multis['ItemNo'] == '560' || $FIN_multis['ItemNo'] == '563' || $FIN_multis['ItemNo'] == '566' || $FIN_multis['ItemNo'] == '569' || $FIN_multis['ItemNo'] == '572' || $FIN_multis['ItemNo'] == '575' || $FIN_multis['ItemNo'] == '578' || $FIN_multis['ItemNo'] == '581' || $FIN_multis['ItemNo'] == '584' || $FIN_multis['ItemNo'] == '587' || $FIN_multis['ItemNo'] == '590' || $FIN_multis['ItemNo'] == '593' || $FIN_multis['ItemNo'] == '596' || $FIN_multis['ItemNo'] == '599' || $FIN_multis['ItemNo'] == '602' || $FIN_multis['ItemNo'] == '605' || $FIN_multis['ItemNo'] == '608' || $FIN_multis['ItemNo'] == '611' || $FIN_multis['ItemNo'] == '614' || $FIN_multis['ItemNo'] == '617' || $FIN_multis['ItemNo'] == '620' || $FIN_multis['ItemNo'] == '623' || $FIN_multis['ItemNo'] == '626' || $FIN_multis['ItemNo'] == '629' || $FIN_multis['ItemNo'] == '632' || $FIN_multis['ItemNo'] == '635' || $FIN_multis['ItemNo'] == '638' || $FIN_multis['ItemNo'] == '641' || $FIN_multis['ItemNo'] == '644' || $FIN_multis['ItemNo'] == '647' || $FIN_multis['ItemNo'] == '650' || $FIN_multis['ItemNo'] == '653' || $FIN_multis['ItemNo'] == '656' || $FIN_multis['ItemNo'] == '659' || $FIN_multis['ItemNo'] == '662' || $FIN_multis['ItemNo'] == '665' || $FIN_multis['ItemNo'] == '668' || $FIN_multis['ItemNo'] == '671' || $FIN_multis['ItemNo'] == '674' || $FIN_multis['ItemNo'] == '677' || $FIN_multis['ItemNo'] == '680' || $FIN_multis['ItemNo'] == '683' || $FIN_multis['ItemNo'] == '686' || $FIN_multis['ItemNo'] == '689' || $FIN_multis['ItemNo'] == '692' || $FIN_multis['ItemNo'] == '695' || $FIN_multis['ItemNo'] == '698' || $FIN_multis['ItemNo'] == '701' || $FIN_multis['ItemNo'] == '704' || $FIN_multis['ItemNo'] == '707' || $FIN_multis['ItemNo'] == '710' || $FIN_multis['ItemNo'] == '713' || $FIN_multis['ItemNo'] == '716' || $FIN_multis['ItemNo'] == '719' || $FIN_multis['ItemNo'] == '722' || $FIN_multis['ItemNo'] == '725' || $FIN_multis['ItemNo'] == '728' || $FIN_multis['ItemNo'] == '731' || $FIN_multis['ItemNo'] == '734' || $FIN_multis['ItemNo'] == '737' || $FIN_multis['ItemNo'] == '740' || $FIN_multis['ItemNo'] == '743' || $FIN_multis['ItemNo'] == '746' || $FIN_multis['ItemNo'] == '749' || $FIN_multis['ItemNo'] == '752' || $FIN_multis['ItemNo'] == '755' || $FIN_multis['ItemNo'] == '758' || $FIN_multis['ItemNo'] == '761' || $FIN_multis['ItemNo'] == '764' || $FIN_multis['ItemNo'] == '767' || $FIN_multis['ItemNo'] == '770' || $FIN_multis['ItemNo'] == '773' || $FIN_multis['ItemNo'] == '776' || $FIN_multis['ItemNo'] == '779' || $FIN_multis['ItemNo'] == '782' || $FIN_multis['ItemNo'] == '785' || $FIN_multis['ItemNo'] == '788' || $FIN_multis['ItemNo'] == '791' || $FIN_multis['ItemNo'] == '794'|| $FIN_multis['ItemNo'] == '797' || $FIN_multis['ItemNo'] == '800' || $FIN_multis['ItemNo'] == '803' || $FIN_multis['ItemNo'] == '806' || $FIN_multis['ItemNo'] == '809' || $FIN_multis['ItemNo'] == '812' || $FIN_multis['ItemNo'] == '815' || $FIN_multis['ItemNo'] == '818' || $FIN_multis['ItemNo'] == '821' || $FIN_multis['ItemNo'] == '824' || $FIN_multis['ItemNo'] == '827' || $FIN_multis['ItemNo'] == '830' || $FIN_multis['ItemNo'] == '833' || $FIN_multis['ItemNo'] == '836' || $FIN_multis['ItemNo'] == '839' || $FIN_multis['ItemNo'] == '842' || $FIN_multis['ItemNo'] == '845' || $FIN_multis['ItemNo'] == '848' || $FIN_multis['ItemNo'] == '851' || $FIN_multis['ItemNo'] == '854' || $FIN_multis['ItemNo'] == '857' || $FIN_multis['ItemNo'] == '860' || $FIN_multis['ItemNo'] == '863' || $FIN_multis['ItemNo'] == '866' || $FIN_multis['ItemNo'] == '869' || $FIN_multis['ItemNo'] == '872' || $FIN_multis['ItemNo'] == '875' || $FIN_multis['ItemNo'] == '878' || $FIN_multis['ItemNo'] == '881' || $FIN_multis['ItemNo'] == '884' || $FIN_multis['ItemNo'] == '887' || $FIN_multis['ItemNo'] == '890' || $FIN_multis['ItemNo'] == '893' || $FIN_multis['ItemNo'] == '896' || $FIN_multis['ItemNo'] == '899' || $FIN_multis['ItemNo'] == '902' || $FIN_multis['ItemNo'] == '905' || $FIN_multis['ItemNo'] == '908' || $FIN_multis['ItemNo'] == '911' || $FIN_multis['ItemNo'] == '914' || $FIN_multis['ItemNo'] == '917' || $FIN_multis['ItemNo'] == '920' || $FIN_multis['ItemNo'] == '923' || $FIN_multis['ItemNo'] == '926' || $FIN_multis['ItemNo'] == '929' || $FIN_multis['ItemNo'] == '932' || $FIN_multis['ItemNo'] == '935' || $FIN_multis['ItemNo'] == '938' || $FIN_multis['ItemNo'] == '941' || $FIN_multis['ItemNo'] == '944' || $FIN_multis['ItemNo'] == '947' || $FIN_multis['ItemNo'] == '950' || $FIN_multis['ItemNo'] == '953' || $FIN_multis['ItemNo'] == '956' || $FIN_multis['ItemNo'] == '959'|| $FIN_multis['ItemNo'] == '962' || $FIN_multis['ItemNo'] == '965' || $FIN_multis['ItemNo'] == '968' || $FIN_multis['ItemNo'] == '971' || $FIN_multis['ItemNo'] == '974' || $FIN_multis['ItemNo'] == '977' || $FIN_multis['ItemNo'] == '980' || $FIN_multis['ItemNo'] == '983' || $FIN_multis['ItemNo'] == '986' || $FIN_multis['ItemNo'] == '989' || $FIN_multis['ItemNo'] == '992' || $FIN_multis['ItemNo'] == '995' || $FIN_multis['ItemNo'] == '998') {
				if (in_array($FIN_multis['ItemNo'], $itemnums2)) {	

					$this->SetXY(20, 189);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'CUD',0,0,'C');

					if (@$FIN_multis['Cbs'] == NULL) {
						$dutiable_value = $dutiable_value;
					}else{
						$dutiable_value = $FIN_multis['Cbs'];
					}


					$this->SetXY(35, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($dutiable_value, 2),0,0,'R');
					
					$this->SetXY(62, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(13,5,$FIN_multis['HsRate'].' %',0,0,'C');


					if (@$FIN_multis['CUD'] == NULL) {
						$FIN_multis['CUD'] = $dutiable_value * ($FIN_multis['HsRate']/100);
					}else{
						$FIN_multis['CUD'] = $FIN_multis['CUD'];
					}


					$this->SetXY(75, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($FIN_multis['CUD'], 2),0,0,'R');
					
					$this->SetXY(102, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(16,5,'0',0,0,'C');

					if (@$FIN_multis['Vbs'] == NULL) {
						// TAX BASE EXC Carie: 05252023: Tariff Spec/AI code for IES-4 Enhancement
						if ($data['FIN_data']['cltcode'] == 'FEDEX' || $data['FIN_data']['cltcode'] == 'DHLEXA' ) {
							$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :"";
							$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre)) + (float)($TAXAMT3)),2);
						} else {
							//$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre))),2);
							$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] : "";
							$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre)) + (float)$TAXAMT3),2);
						}
					}else{
						$VATBASE = $FIN_multis['Vbs'];
					}


					if ($data['FIN_data']['MDec'] == 'IED') {
						$VATBASE = 0;
					}

					
					$this->SetXY(20, 193);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'VAT',0,0,'C');
					
					$this->SetXY(35, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATBASE, 2),0,0,'R');
					
					$this->SetXY(62, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(13,5,'12 %',0,0,'C');

					if (!@$FIN_multis['VAT']) {
						$VATAMOUNT = round(($VATBASE * 0.12),2);
					}else{
						$VATAMOUNT = $FIN_multis['VAT'];
					}


					$this->SetXY(75, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATAMOUNT, 2),0,0,'R');
					
					$this->SetXY(102, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(16,5,'0',0,0,'C');
					
					// 1st item of this rider
				$AiCodeData = $this->checkAICODE($FIN_multis['HSCode'], $FIN_multis['HSCODE_TAR'], $FIN_multis['TARSPEC']); // Check if the HSCODE, HECODE_TAR and TarSpec is in CWSAICODE
				if ($FIN_multis['HSCode'] == "90049090" && $FIN_multis['HSCODE_TAR'] == "000") {
					$ExcItem = $dutiable_value;
					if (substr($FIN_multis['MDec'], 0, 1) != '7' && substr($FIN_multis['MDec'], 0, 1) != '8')  {
						
						$TAXExcisePerItem = ($ExcItem  * (str_replace("%", "", $AiCodeData[0]['Rate']) / 100));
						$this->SetXY(20, 197);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'EXC',0,0,'C');
						
						$this->SetXY(35, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(24,3,number_format($ExcItem, 2),0,0,'R');

						$this->SetXY(62, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(13,5,'20%',0,0,'C');
						
						$this->SetXY(75, 198);
						$this->SetFont('Arial','',8);
						$this->Cell(25,3,number_format($TAXExcisePerItem,2),0,0,'R');
						$TAXAMT3 = $TAXExcisePerItem;
					}
					else
					{
						//print_r($dutiable_value); die();	
						//$TAXExcisePerItem = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
							$TAXExcisePerItem = $ExcItem * 0.20 ;
							$this->SetXY(20, 197);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,5,'EXC',0,0,'C');
							
							$this->SetXY(35, 197);
							$this->SetFont('Arial','',8);
							$this->Cell(24,3,number_format($ExcItem, 2),0,0,'R');

							$this->SetXY(62, 197);
							$this->SetFont('Arial','',8);
							$this->Cell(13,5,'20%',0,0,'C');
							
							//if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
							$this->SetXY(75, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(25,3,number_format($TAXExcisePerItem,2),0,0,'R');
							//}
							$TAXAMT3 = $TAXExcisePerItem;
					}
				}
				else
				{
					$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] : "";
					$AVTAMT3 = 0;
					
					if($TAXAMT3 !='' && $FIN_multis['MSP'] == ""){
						$this->SetXY(20, 197);
						$this->SetFont('Arial','B',8);
						//06202024: Spagara: if taxexciseunit is blank
						$TAXExcise = !empty($FIN_multis['ExciseRate']) ? $FIN_multis['ExciseRate'] :" ";
						$TAXExciseUnit = !empty($FIN_multis['ExciseUnit']) ? $FIN_multis['ExciseUnit'] :" ";
						if (substr(($FIN_multis['HSCode']),0,4) != "8703" && $FIN_multis['MSP'] == "" ) {
							$this->Cell(15,5,'EXC',0,0,'C');
						}else{
							$this->Cell(15,7,'AVT',0,0,'C');
						}
						
						// TAX BASE
						$this->SetXY(35, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($FIN_multis['ExciseQty'], 2),0,0,'R');
					
						$this->SetXY(50, 198);
						//if (substr(($FIN_multis['HSCode']),0,4) != "8703" && $FIN_multis['MSP'] == "" && $TAXExciseUnit != " ") {
						if ($data['FIN_data']['HSCode'] != "71171920" && $data['FIN_data']['HSCode'] != "71131990" && $data['FIN_data']['HSCode'] != "33030000" && substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "" && $TAXExciseUnit != " ") {
							if (strlen($TAXExcise . '/' . $TAXExciseUnit) >= 8) {
								$this->SetFont('Arial', '', 6.5);
							} else {
								$this->SetFont('Arial', '', 8);
							}
							$this->Cell(25,3,$TAXExcise.'/'.$TAXExciseUnit,0,0,'R');
						}else{
							$this->SetFont('Arial','',8);
							$this->Cell(25,3,$TAXExcise.' %',0,0,'R');
						}
			
						$this->SetXY(75, 198);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT3, 2),0,0,'R');
					}
					else{
						//2nd Page 1st Item of Rider

						if ($FIN_multis['rul_cod'] == "AVT-AUTO" AND $FIN_multis['MSP'] != ""){
							$this->SetXY(20, 196);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,7,'AVT',0,0,'C');
							
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 4;
								$MSP = ($FIN_multis['MSP'] * 0.04) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 20;
								$MSP = ($FIN_multis['MSP'] * 0.20) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 50;
								$MSP = ($FIN_multis['MSP'] * 0.50) * $FIN_multis['SupVal1'];
							}
							
							$this->SetXY(50, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(23,3,$AVTRate.' %',0,0,'R');
							
							$AVTAMT3 = $MSP;
							
							$this->SetXY(35, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							$this->SetXY(75, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2),0,0,'R');
							
							$this->SetXY(102, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(16,3,'',0,0,'C');
							
						}
						elseif ($FIN_multis['rul_cod'] == "AVT_HYBRID" AND $FIN_multis['MSP'] != "") {
							//print_r($FIN_multis); die();
							$this->SetXY(20, 196);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,7,'AVT',0,0,'C');
							
							
							
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 2;
								$MSP = ($FIN_multis['MSP'] * 0.02) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 5;
								$MSP = ($FIN_multis['MSP'] * 0.05) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 25;
								$MSP = ($FIN_multis['MSP'] * 0.25) * $FIN_multis['SupVal1'];
							}
							
							$AVTAMT3 = $MSP;
							
							$this->SetXY(50, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(23,3,$AVTRate.' %',0,0,'R');
							
							$this->SetXY(35, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							$this->SetXY(75, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2),0,0,'R');
							
							$this->SetXY(102, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(16,3,'',0,0,'C');
						}
					}
					
				}
		
					$AVTTAX = $AVTTAX + $TAXAMT3;
					if ($FIN_multis['HSCode'] == "27101211" || $FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972" || $FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983" || $FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221"){
						if ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] == "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = "";
						}elseif ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] != "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}elseif ($FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.03 * 0.06146428571;
							$TAXFMF = 1.03;
						}elseif ($FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = "";
						}elseif ($FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}
						
						$this->SetXY(20, 201);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'FMF',0,0,'C');

						$this->SetXY(48, 201);
						$this->SetFont('Arial','',8);
						$this->Cell(25,3,$TAXFMF,0,0,'R');

						$this->SetXY(75, 201);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');
					}

					if ($FIN_multis['HSCode'] == "25232990" || $FIN_multis['HSCode'] == "25239000"){	
						if ($FIN_multis['TARSPEC'] == "1001") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0233;	
							$TAXFMF = "2.33";	
						}elseif ($FIN_multis['TARSPEC'] == "1002") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0276;	
							$TAXFMF = "2.76";	
						}elseif ($FIN_multis['TARSPEC'] == "1003") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0341;	
							$TAXFMF = "3.41";	
						}elseif ($FIN_multis['TARSPEC'] == "1004") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0619;	
							$TAXFMF = "6.19";	
						}elseif ($FIN_multis['TARSPEC'] == "1005") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0794;	
							$TAXFMF = "7.94";	
						}elseif ($FIN_multis['TARSPEC'] == "1006") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0948;	
							$TAXFMF = "9.48";	
						}elseif ($FIN_multis['TARSPEC'] == "1007") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0951;	
							$TAXFMF = "9.51";	
						}elseif ($FIN_multis['TARSPEC'] == "1008") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1067;	
							$TAXFMF = "10.67";	
						}elseif ($FIN_multis['TARSPEC'] == "1009") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1099;	
							$TAXFMF = "10.99";	
						}elseif ($FIN_multis['TARSPEC'] == "1010") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1158;	
							$TAXFMF = "11.58";	
						}elseif ($FIN_multis['TARSPEC'] == "1011") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1206;	
							$TAXFMF = "12.06";	
						}elseif ($FIN_multis['TARSPEC'] == "1012") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1529;	
							$TAXExcise = "15.29";	
						}elseif ($FIN_multis['TARSPEC'] == "1013") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2307;	
							$TAXFMF = "23.07";	
						}elseif ($data['FIN_others']['TARSPEC'] == "1014") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.00;	
							$TAXFMF = "0.00";	
						}elseif ($FIN_multis['TARSPEC'] == "") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2333;	
							$TAXFMF = "23.33";	
						}		
								
						$this->SetXY(20, 201);	
						$this->SetFont('Arial','B',8);	
						$this->Cell(15,5,'DPD',0,0,'C');
						
						$this->SetXY(38, 201);	
						$this->SetFont('Arial','',8);	
						$this->Cell(23,5,number_format($TAXVALUE, 2),0,0,'R');
						
						$this->SetXY(50, 202);	
						$this->SetFont('Arial','',8);	
						$this->Cell(25,3,$TAXFMF.' %',0,0,'R');	
						
						$this->SetXY(75, 202);	
						$this->SetFont('Arial','',8);	
						$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');
					}
					
					if ($FIN_multis['MSP'] != "" && $FIN_multis['ExciseType'] == "AUTOMOBILE"){
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT7 + $AVTAMT3;
						
					}
					elseif ($FIN_multis['MSP'] != "" && $FIN_multis['ExciseType'] != "AUTOMOBILE") {
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT3 + $TAXAMT7 + $AVTAMT3;
					}
					else
					{
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT3 + $TAXAMT7;
					}
					$this->SetXY(20, 228);
					$this->SetFont('Arial','',8);
					$this->Cell(82,7,number_format($TOTAL_TAXES, 2),0,0,'R');
				}

				//if ($FIN_multis['ItemNo'] == '3' || $FIN_multis['ItemNo'] == '6' || $FIN_multis['ItemNo'] == '9' || $FIN_multis['ItemNo'] == '12' || $FIN_multis['ItemNo'] == '15' || $FIN_multis['ItemNo'] == '18' || $FIN_multis['ItemNo'] == '21' || $FIN_multis['ItemNo'] == '24' || $FIN_multis['ItemNo'] == '27' || $FIN_multis['ItemNo'] == '30' || $FIN_multis['ItemNo'] == '33' || $FIN_multis['ItemNo'] == '36' || $FIN_multis['ItemNo'] == '39' || $FIN_multis['ItemNo'] == '42' || $FIN_multis['ItemNo'] == '45' || $FIN_multis['ItemNo'] == '48' || $FIN_multis['ItemNo'] == '51' || $FIN_multis['ItemNo'] == '54' || $FIN_multis['ItemNo'] == '57' || $FIN_multis['ItemNo'] == '60' || $FIN_multis['ItemNo'] == '63' || $FIN_multis['ItemNo'] == '66' || $FIN_multis['ItemNo'] == '69' || $FIN_multis['ItemNo'] == '72' || $FIN_multis['ItemNo'] == '75' || $FIN_multis['ItemNo'] == '78' || $FIN_multis['ItemNo'] == '81' || $FIN_multis['ItemNo'] == '84' || $FIN_multis['ItemNo'] == '87' || $FIN_multis['ItemNo'] == '90' || $FIN_multis['ItemNo'] == '93' || $FIN_multis['ItemNo'] == '96' || $FIN_multis['ItemNo'] == '99' || $FIN_multis['ItemNo'] == '102' || $FIN_multis['ItemNo'] == '105' || $FIN_multis['ItemNo'] == '108' || $FIN_multis['ItemNo'] == '111' || $FIN_multis['ItemNo'] == '114' || $FIN_multis['ItemNo'] == '117' || $FIN_multis['ItemNo'] == '120' || $FIN_multis['ItemNo'] == '123' || $FIN_multis['ItemNo'] == '126' || $FIN_multis['ItemNo'] == '129' || $FIN_multis['ItemNo'] == '132' || $FIN_multis['ItemNo'] == '135' || $FIN_multis['ItemNo'] == '138' || $FIN_multis['ItemNo'] == '141' || $FIN_multis['ItemNo'] == '144' || $FIN_multis['ItemNo'] == '147' || $FIN_multis['ItemNo'] == '150' || $FIN_multis['ItemNo'] == '153' || $FIN_multis['ItemNo'] == '156' || $FIN_multis['ItemNo'] == '159' || $FIN_multis['ItemNo'] == '162' || $FIN_multis['ItemNo'] == '165' || $FIN_multis['ItemNo'] == '168' || $FIN_multis['ItemNo'] == '171' || $FIN_multis['ItemNo'] == '174' || $FIN_multis['ItemNo'] == '177' || $FIN_multis['ItemNo'] == '180' || $FIN_multis['ItemNo'] == '183' || $FIN_multis['ItemNo'] == '186' || $FIN_multis['ItemNo'] == '189' || $FIN_multis['ItemNo'] == '192' || $FIN_multis['ItemNo'] == '195' || $FIN_multis['ItemNo'] == '198' || $FIN_multis['ItemNo'] == '201' || $FIN_multis['ItemNo'] == '204' || $FIN_multis['ItemNo'] == '207' || $FIN_multis['ItemNo'] == '210' || $FIN_multis['ItemNo'] == '213' || $FIN_multis['ItemNo'] == '216' || $FIN_multis['ItemNo'] == '219' || $FIN_multis['ItemNo'] == '222' || $FIN_multis['ItemNo'] == '225' || $FIN_multis['ItemNo'] == '228' || $FIN_multis['ItemNo'] == '231' || $FIN_multis['ItemNo'] == '234' || $FIN_multis['ItemNo'] == '237' || $FIN_multis['ItemNo'] == '240' || $FIN_multis['ItemNo'] == '243' || $FIN_multis['ItemNo'] == '246' || $FIN_multis['ItemNo'] == '249' || $FIN_multis['ItemNo'] == '252' || $FIN_multis['ItemNo'] == '255' || $FIN_multis['ItemNo'] == '258' || $FIN_multis['ItemNo'] == '261' || $FIN_multis['ItemNo'] == '264' || $FIN_multis['ItemNo'] == '267' || $FIN_multis['ItemNo'] == '270' || $FIN_multis['ItemNo'] == '273' || $FIN_multis['ItemNo'] == '276' || $FIN_multis['ItemNo'] == '279' || $FIN_multis['ItemNo'] == '282' || $FIN_multis['ItemNo'] == '285' || $FIN_multis['ItemNo'] == '288' || $FIN_multis['ItemNo'] == '291' || $FIN_multis['ItemNo'] == '294' || $FIN_multis['ItemNo'] == '297' || $FIN_multis['ItemNo'] == '300' || $FIN_multis['ItemNo'] == '303' || $FIN_multis['ItemNo'] == '306' || $FIN_multis['ItemNo'] == '309' || $FIN_multis['ItemNo'] == '312' || $FIN_multis['ItemNo'] == '315' || $FIN_multis['ItemNo'] == '318' || $FIN_multis['ItemNo'] == '321' || $FIN_multis['ItemNo'] == '324' || $FIN_multis['ItemNo'] == '327' || $FIN_multis['ItemNo'] == '330' || $FIN_multis['ItemNo'] == '333' || $FIN_multis['ItemNo'] == '336' || $FIN_multis['ItemNo'] == '339' || $FIN_multis['ItemNo'] == '342' || $FIN_multis['ItemNo'] == '345' || $FIN_multis['ItemNo'] == '348' || $FIN_multis['ItemNo'] == '351' || $FIN_multis['ItemNo'] == '354' || $FIN_multis['ItemNo'] == '357' || $FIN_multis['ItemNo'] == '360' || $FIN_multis['ItemNo'] == '363' || $FIN_multis['ItemNo'] == '366' || $FIN_multis['ItemNo'] == '369' || $FIN_multis['ItemNo'] == '372' || $FIN_multis['ItemNo'] == '375' || $FIN_multis['ItemNo'] == '378' || $FIN_multis['ItemNo'] == '381' || $FIN_multis['ItemNo'] == '384' || $FIN_multis['ItemNo'] == '387' || $FIN_multis['ItemNo'] == '390' || $FIN_multis['ItemNo'] == '393' || $FIN_multis['ItemNo'] == '396' || $FIN_multis['ItemNo'] == '399' || $FIN_multis['ItemNo'] == '402' || $FIN_multis['ItemNo'] == '405' || $FIN_multis['ItemNo'] == '408' || $FIN_multis['ItemNo'] == '411' || $FIN_multis['ItemNo'] == '414' || $FIN_multis['ItemNo'] == '417' || $FIN_multis['ItemNo'] == '420' || $FIN_multis['ItemNo'] == '423' || $FIN_multis['ItemNo'] == '426' || $FIN_multis['ItemNo'] == '429' || $FIN_multis['ItemNo'] == '432' || $FIN_multis['ItemNo'] == '435' || $FIN_multis['ItemNo'] == '438' || $FIN_multis['ItemNo'] == '441' || $FIN_multis['ItemNo'] == '444' || $FIN_multis['ItemNo'] == '447' || $FIN_multis['ItemNo'] == '450' || $FIN_multis['ItemNo'] == '453' || $FIN_multis['ItemNo'] == '456' || $FIN_multis['ItemNo'] == '459' || $FIN_multis['ItemNo'] == '462' || $FIN_multis['ItemNo'] == '465' || $FIN_multis['ItemNo'] == '468' || $FIN_multis['ItemNo'] == '471' || $FIN_multis['ItemNo'] == '474' || $FIN_multis['ItemNo'] == '477' || $FIN_multis['ItemNo'] == '480' || $FIN_multis['ItemNo'] == '483' || $FIN_multis['ItemNo'] == '486' || $FIN_multis['ItemNo'] == '489' || $FIN_multis['ItemNo'] == '492' || $FIN_multis['ItemNo'] == '495' || $FIN_multis['ItemNo'] == '498' || $FIN_multis['ItemNo'] == '501' || $FIN_multis['ItemNo'] == '504' || $FIN_multis['ItemNo'] == '507' || $FIN_multis['ItemNo'] == '510' || $FIN_multis['ItemNo'] == '513' || $FIN_multis['ItemNo'] == '516' || $FIN_multis['ItemNo'] == '519' || $FIN_multis['ItemNo'] == '522' || $FIN_multis['ItemNo'] == '525' || $FIN_multis['ItemNo'] == '528' || $FIN_multis['ItemNo'] == '531' || $FIN_multis['ItemNo'] == '534' || $FIN_multis['ItemNo'] == '537' || $FIN_multis['ItemNo'] == '540' || $FIN_multis['ItemNo'] == '543' || $FIN_multis['ItemNo'] == '546' || $FIN_multis['ItemNo'] == '549' || $FIN_multis['ItemNo'] == '552' || $FIN_multis['ItemNo'] == '555' || $FIN_multis['ItemNo'] == '558' || $FIN_multis['ItemNo'] == '561' || $FIN_multis['ItemNo'] == '564' || $FIN_multis['ItemNo'] == '567' || $FIN_multis['ItemNo'] == '570' || $FIN_multis['ItemNo'] == '573' || $FIN_multis['ItemNo'] == '576' || $FIN_multis['ItemNo'] == '579' || $FIN_multis['ItemNo'] == '582' || $FIN_multis['ItemNo'] == '585' || $FIN_multis['ItemNo'] == '588' || $FIN_multis['ItemNo'] == '591' || $FIN_multis['ItemNo'] == '594' || $FIN_multis['ItemNo'] == '597' || $FIN_multis['ItemNo'] == '600' || $FIN_multis['ItemNo'] == '603' || $FIN_multis['ItemNo'] == '606' || $FIN_multis['ItemNo'] == '609'|| $FIN_multis['ItemNo'] == '612' || $FIN_multis['ItemNo'] == '615' || $FIN_multis['ItemNo'] == '618' || $FIN_multis['ItemNo'] == '621' || $FIN_multis['ItemNo'] == '624' || $FIN_multis['ItemNo'] == '627' || $FIN_multis['ItemNo'] == '630' || $FIN_multis['ItemNo'] == '633' || $FIN_multis['ItemNo'] == '636' || $FIN_multis['ItemNo'] == '639' || $FIN_multis['ItemNo'] == '642' || $FIN_multis['ItemNo'] == '645' || $FIN_multis['ItemNo'] == '648' || $FIN_multis['ItemNo'] == '651' || $FIN_multis['ItemNo'] == '654' || $FIN_multis['ItemNo'] == '657' || $FIN_multis['ItemNo'] == '660' || $FIN_multis['ItemNo'] == '663' || $FIN_multis['ItemNo'] == '666' || $FIN_multis['ItemNo'] == '669' || $FIN_multis['ItemNo'] == '672' || $FIN_multis['ItemNo'] == '675' || $FIN_multis['ItemNo'] == '678' || $FIN_multis['ItemNo'] == '681' || $FIN_multis['ItemNo'] == '684' || $FIN_multis['ItemNo'] == '687' || $FIN_multis['ItemNo'] == '690' || $FIN_multis['ItemNo'] == '693' || $FIN_multis['ItemNo'] == '696' || $FIN_multis['ItemNo'] == '699' || $FIN_multis['ItemNo'] == '702' || $FIN_multis['ItemNo'] == '705' || $FIN_multis['ItemNo'] == '708' || $FIN_multis['ItemNo'] == '711' || $FIN_multis['ItemNo'] == '714' || $FIN_multis['ItemNo'] == '717' || $FIN_multis['ItemNo'] == '720' || $FIN_multis['ItemNo'] == '723' || $FIN_multis['ItemNo'] == '726' || $FIN_multis['ItemNo'] == '729' || $FIN_multis['ItemNo'] == '732' || $FIN_multis['ItemNo'] == '735' || $FIN_multis['ItemNo'] == '738' || $FIN_multis['ItemNo'] == '741' || $FIN_multis['ItemNo'] == '744' || $FIN_multis['ItemNo'] == '747' || $FIN_multis['ItemNo'] == '750' || $FIN_multis['ItemNo'] == '753' || $FIN_multis['ItemNo'] == '756' || $FIN_multis['ItemNo'] == '759' || $FIN_multis['ItemNo'] == '762' || $FIN_multis['ItemNo'] == '765' || $FIN_multis['ItemNo'] == '768' || $FIN_multis['ItemNo'] == '771' || $FIN_multis['ItemNo'] == '774' || $FIN_multis['ItemNo'] == '777' || $FIN_multis['ItemNo'] == '780' || $FIN_multis['ItemNo'] == '783' || $FIN_multis['ItemNo'] == '786' || $FIN_multis['ItemNo'] == '789' || $FIN_multis['ItemNo'] == '792' || $FIN_multis['ItemNo'] == '795' || $FIN_multis['ItemNo'] == '798' || $FIN_multis['ItemNo'] == '801' || $FIN_multis['ItemNo'] == '804' || $FIN_multis['ItemNo'] == '807' || $FIN_multis['ItemNo'] == '810' || $FIN_multis['ItemNo'] == '813' || $FIN_multis['ItemNo'] == '816' || $FIN_multis['ItemNo'] == '819' || $FIN_multis['ItemNo'] == '822' || $FIN_multis['ItemNo'] == '825' || $FIN_multis['ItemNo'] == '828' || $FIN_multis['ItemNo'] == '831' || $FIN_multis['ItemNo'] == '834' || $FIN_multis['ItemNo'] == '837' || $FIN_multis['ItemNo'] == '840' || $FIN_multis['ItemNo'] == '843' || $FIN_multis['ItemNo'] == '846' || $FIN_multis['ItemNo'] == '849' || $FIN_multis['ItemNo'] == '852' || $FIN_multis['ItemNo'] == '855' || $FIN_multis['ItemNo'] == '858' || $FIN_multis['ItemNo'] == '861' || $FIN_multis['ItemNo'] == '864' || $FIN_multis['ItemNo'] == '867' || $FIN_multis['ItemNo'] == '870' || $FIN_multis['ItemNo'] == '873' || $FIN_multis['ItemNo'] == '876' || $FIN_multis['ItemNo'] == '879' || $FIN_multis['ItemNo'] == '882' || $FIN_multis['ItemNo'] == '885' || $FIN_multis['ItemNo'] == '888' || $FIN_multis['ItemNo'] == '891' || $FIN_multis['ItemNo'] == '894' || $FIN_multis['ItemNo'] == '897' || $FIN_multis['ItemNo'] == '900' || $FIN_multis['ItemNo'] == '903' || $FIN_multis['ItemNo'] == '906' || $FIN_multis['ItemNo'] == '909' || $FIN_multis['ItemNo'] == '912' || $FIN_multis['ItemNo'] == '915' || $FIN_multis['ItemNo'] == '918' || $FIN_multis['ItemNo'] == '921' || $FIN_multis['ItemNo'] == '924' || $FIN_multis['ItemNo'] == '927' || $FIN_multis['ItemNo'] == '930' || $FIN_multis['ItemNo'] == '933' || $FIN_multis['ItemNo'] == '936' || $FIN_multis['ItemNo'] == '939' || $FIN_multis['ItemNo'] == '942' || $FIN_multis['ItemNo'] == '945' || $FIN_multis['ItemNo'] == '948' || $FIN_multis['ItemNo'] == '951' || $FIN_multis['ItemNo'] == '954' || $FIN_multis['ItemNo'] == '957' || $FIN_multis['ItemNo'] == '960' || $FIN_multis['ItemNo'] == '963' || $FIN_multis['ItemNo'] == '966' || $FIN_multis['ItemNo'] == '969' || $FIN_multis['ItemNo'] == '972' || $FIN_multis['ItemNo'] == '975' || $FIN_multis['ItemNo'] == '978' || $FIN_multis['ItemNo'] == '981' || $FIN_multis['ItemNo'] == '984' || $FIN_multis['ItemNo'] == '987' || $FIN_multis['ItemNo'] == '990' || $FIN_multis['ItemNo'] == '993' || $FIN_multis['ItemNo'] == '996' || $FIN_multis['ItemNo'] == '999') {
				if (in_array($FIN_multis['ItemNo'], $itemnums3)) {

					$this->SetXY(118, 189);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'CUD',0,0,'C');

					if (@$FIN_multis['Cbs'] == NULL) {
						$dutiable_value = $dutiable_value;
					}else{
						$dutiable_value = $FIN_multis['Cbs'];
					}


					$this->SetXY(133, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($dutiable_value, 2),0,0,'R');
					
					$this->SetXY(160, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(11,5,$FIN_multis['HsRate'].' %',0,0,'C');
					
					if (@$FIN_multis['CUD'] == NULL) {
						$FIN_multis['CUD'] = $dutiable_value * ($FIN_multis['HsRate']/100);
					}else{
						$FIN_multis['CUD'] = $FIN_multis['CUD'];
					}


					$this->SetXY(171, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($FIN_multis['CUD'], 2),0,0,'R');
					
					$this->SetXY(198, 189);
					$this->SetFont('Arial','',8);
					$this->Cell(9,5,'0',0,0,'C');
					//$IPF = $IPF * $data['max_rows'];

					if (@$FIN_multis['Vbs'] == NULL) {
						// TAX BASE EXC Carie: 05252023: Tariff Spec/AI code for IES-4 Enhancement
						if ($data['FIN_data']['cltcode'] == 'FEDEX' || $data['FIN_data']['cltcode'] == 'DHLEXA' ) {
							$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :"";
							$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre)) + (float)($TAXAMT3)),2);
						} else {
							//$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre))),2);
							$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :"";
							$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre)) + (float)($TAXAMT3)),2);
						}
					}else{
						$VATBASE = $FIN_multis['Vbs'];
					}

					if ($data['FIN_data']['MDec'] == 'IED') {
						$VATBASE = 0;
					}

					$this->SetXY(118, 193);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'VAT',0,0,'C');
					
					$this->SetXY(133, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATBASE, 2),0,0,'R');

					$this->SetXY(160, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(11,5,'12 %',0,0,'C');

					if (!@$FIN_multis['VAT']) {
						$VATAMOUNT = round(($VATBASE * 0.12),2);
					}else{
						$VATAMOUNT = $FIN_multis['VAT'];
					}


					$this->SetXY(171, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATAMOUNT, 2).'',0,0,'R');
					
					$this->SetXY(198, 193);
					$this->SetFont('Arial','',8);
					$this->Cell(9,5,'0',0,0,'C');
					
				$AiCodeData = $this->checkAICODE($FIN_multis['HSCode'], $FIN_multis['HSCODE_TAR'], $FIN_multis['TARSPEC']); // Check if the HSCODE, HECODE_TAR and TarSpec is in CWSAICODE
				if ($FIN_multis['HSCode'] == "90049090" && $FIN_multis['HSCODE_TAR'] == "000") {
					$ExcItem = $dutiable_value;
					if (substr($FIN_multis['MDec'], 0, 1) != '7' && substr($FIN_multis['MDec'], 0, 1) != '8')  {
						
						$TAXExcisePerItem = ($ExcItem  * (str_replace("%", "", $AiCodeData[0]['Rate']) / 100));
						
						$this->SetXY(118, 197);
						$this->SetFont('Arial','B',8);
						//$this->Write(0, 'EXC');
						$this->Cell(15,5,'EXC',0,0,'C');
						
						$this->SetXY(133, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($ExcItem, 2),0,0,'R');

						$this->SetXY(160, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(11,5,'20%',0,0,'R');
						
						//if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
						$this->SetXY(171, 198);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXExcisePerItem,2),0,0,'R');
						//}
						$TAXAMT3 = $TAXExcisePerItem;
					}
					else
					{

							//$TAXExcisePerItem = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
							
							$TAXExcisePerItem = ($ExcItem  * 0.20)  ;
							
							$this->SetXY(118, 197);
							$this->SetFont('Arial','B',8);
							//$this->Write(0, 'EXC');
							$this->Cell(15,5,'EXC',0,0,'C');
							
							$this->SetXY(133, 197);
							$this->SetFont('Arial','',8);
							$this->Cell(27,5,number_format($ExcItem, 2),0,0,'R');

							$this->SetXY(160, 197);
							$this->SetFont('Arial','',8);
							$this->Cell(11,5,'20%',0,0,'R');
							
							//if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
							$this->SetXY(171, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($TAXExcisePerItem,2),0,0,'R');
							//}
							$TAXAMT3 = $TAXExcisePerItem;
						
					}
				}
				else
				{
					$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :"";
					$AVTAMT3 = 0;
					if($TAXAMT3 !='' && $FIN_multis['MSP'] == ""){
						$this->SetXY(118, 197);
						$this->SetFont('Arial','B',8);
						if (substr(($FIN_multis['HSCode']),0,4) != "8703") {
							$this->Cell(15,5,'EXC',0,0,'C');
						}else{
							$this->Cell(15,5,'AVT',0,0,'C');
						}
						
						// TAX BASE
						$this->SetXY(133, 197);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($FIN_multis['ExciseQty'], 2),0,0,'R');
						
						$TAXExcise = !empty($FIN_multis['ExciseRate']) ? $FIN_multis['ExciseRate'] :" ";
						$TAXExciseUnit = !empty($FIN_multis['ExciseUnit']) ? $FIN_multis['ExciseUnit'] :" ";
						$this->SetXY(143, 198);
						//05232023: Spagara: if taxexciseunit is blank
						//if (substr(($FIN_multis['HSCode']),0,4) != "8703" && $TAXExciseUnit != " ") {
						if ($data['FIN_data']['HSCode'] != "71171920" && $data['FIN_data']['HSCode'] != "71131990" && $data['FIN_data']['HSCode'] != "33030000" && substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "" && $TAXExciseUnit != " ") {
							if (strlen($TAXExcise . '/' . $TAXExciseUnit) >= 8) {
								$this->SetFont('Arial', '', 6.5);
								$this->Cell(29,3,$TAXExcise.'/'.$TAXExciseUnit,0,0,'R');
							} else {
								$this->SetFont('Arial', '', 8);
								$this->Cell(28,3,$TAXExcise.'/'.$TAXExciseUnit,0,0,'R');
							}
						}else{
							$this->SetFont('Arial','',8);
							$this->Cell(28,3,$TAXExcise.' %',0,0,'R');
						}
						
						$this->SetXY(171, 198);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT3, 2),0,0,'R');
						
						}else if($FIN_multis['APPLNO'] == 'BALS8081402'){
						//Created Static as advice by Superior
						$this->SetXY(118, 197);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'EXC',0,0,'C');
					
						$this->SetXY(171, 198);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,'15,400.00',0,0,'R');
					}
					else
					{
						$MSP = 0;
						if ($FIN_multis['rul_cod'] == "AVT-AUTO" AND $FIN_multis['MSP'] != ""){
							
							$this->SetXY(118, 197);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,5,'AVT',0,0,'C');
												
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 4;
								$MSP = ($FIN_multis['MSP'] * 0.04) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 20;
								$MSP = ($FIN_multis['MSP'] * 0.20) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 50;
								$MSP = ($FIN_multis['MSP'] * 0.50) * $FIN_multis['SupVal1'];
							}
							
							
							$AVTAMT3 = $MSP;
							// RATE
							$this->SetXY(143, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,$AVTRate.' %',0,0,'R');
							
							// TAX BASE
							$this->SetXY(133, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							// AMOUNT
							$this->SetXY(171, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2),0,0,'R');
							
							// MP
							$this->SetXY(198, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(9,3,'',0,0,'C');
						}
						elseif ($FIN_multis['rul_cod'] == "AVT_HYBRID" AND $FIN_multis['MSP'] != "") {
							$this->SetXY(118, 197);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,5,'AVT',0,0,'C');
												
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 2;
								$MSP = ($FIN_multis['MSP'] * 0.02) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 5;
								$MSP = ($FIN_multis['MSP'] * 0.05) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 25;
								$MSP = ($FIN_multis['MSP'] * 0.25) * $FIN_multis['SupVal1'];
							}

							$AVTAMT3 = $MSP;
							// RATE
							$this->SetXY(143, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,$AVTRate.' %',0,0,'R');
							
							// TAX BASE
							$this->SetXY(133, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							// AMOUNT
							$this->SetXY(171, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2),0,0,'R');
							
							// MP
							$this->SetXY(198, 198);
							$this->SetFont('Arial','',8);
							$this->Cell(9,3,'',0,0,'C');
						
						}
					}
					
					
				}		

					$AVTTAX = $AVTTAX + $TAXAMT3;
					
					if ($FIN_multis['HSCode'] == "27101211" || $FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972" || $FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983" || $FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221"){
						if ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] == "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = 1;
						}elseif ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] != "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}elseif ($FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.03 * 0.06146428571;
							$TAXFMF = 1.03;
						}elseif ($FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = 1;
						}elseif ($FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}
						
						$this->SetXY(118, 203);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'FMF',0,0,'C');

						$this->SetXY(143, 203);
						$this->SetFont('Arial','',8);
						$this->Cell(25,3,$TAXFMF,0,0,'R');

						$this->SetXY(171, 203);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');
					}
					
					if ($FIN_multis['HSCode'] == "25232990" || $FIN_multis['HSCode'] == "25239000"){	
						if ($FIN_multis['TARSPEC'] == "1001") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0233;	
							$TAXFMF = "2.33";	
						}elseif ($FIN_multis['TARSPEC'] == "1002") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0276;	
							$TAXFMF = "2.76";	
						}elseif ($FIN_multis['TARSPEC'] == "1003") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0341;	
							$TAXFMF = "3.41";	
						}elseif ($FIN_multis['TARSPEC'] == "1004") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0619;	
							$TAXFMF = "6.19";	
						}elseif ($FIN_multis['TARSPEC'] == "1005") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0794;	
							$TAXFMF = "7.94";	
						}elseif ($FIN_multis['TARSPEC'] == "1006") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0948;	
							$TAXFMF = "9.48";	
						}elseif ($FIN_multis['TARSPEC'] == "1007") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0951;	
							$TAXFMF = "9.51";	
						}elseif ($FIN_multis['TARSPEC'] == "1008") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1067;	
							$TAXFMF = "10.67";	
						}elseif ($FIN_multis['TARSPEC'] == "1009") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1099;	
							$TAXFMF = "10.99";	
						}elseif ($FIN_multis['TARSPEC'] == "1010") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1158;	
							$TAXFMF = "11.58";	
						}elseif ($FIN_multis['TARSPEC'] == "1011") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1206;	
							$TAXFMF = "12.06";	
						}elseif ($FIN_multis['TARSPEC'] == "1012") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1529;	
							$TAXFMF = "15.29";	
						}elseif ($FIN_multis['TARSPEC'] == "1013") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2307;	
							$TAXFMF = "23.07";	
						}elseif ($FIN_multis['TARSPEC'] == "1014") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.00;	
							$TAXFMF = "0.00";	
						}elseif ($FIN_multis['TARSPEC'] == "") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2333;	
							$TAXFMF = "23.33";	
						}		
								
						$this->SetXY(118, 203);	
						$this->SetFont('Arial','B',8);	
						$this->Cell(15,5,'DPD',0,0,'C');	
						$this->SetXY(135, 203);		
						$this->SetFont('Arial','',8);		
						$this->Cell(24,3,number_format($TAXVALUE,2),0,0,'R');		
						$this->SetXY(146, 203);	
						$this->SetFont('Arial','',8);	
						$this->Cell(26,3,$TAXFMF.' %',0,0,'R');	
						$this->SetXY(171, 203);	
						$this->SetFont('Arial','',8);	
						$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');	
					}

					if($FIN_multis['APPLNO'] == 'BALS8081402'){
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT3 + 15400;
					}elseif ($FIN_multis['ExciseType'] == "AUTOMOBILE"){
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT7 + $AVTAMT3;
					}else{
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT3 + $TAXAMT7 + $AVTAMT3;
					}
					
					
					// TOTAL 2nd item of rider
					$this->SetXY(118, 228);
					$this->SetFont('Arial','',8);
					$this->Cell(80,7,number_format($TOTAL_TAXES, 2),0,0,'R');
				}

				//if ($FIN_multis['ItemNo'] == '4' || $FIN_multis['ItemNo'] == '7' || $FIN_multis['ItemNo'] == '10'|| $FIN_multis['ItemNo'] == '13' || $FIN_multis['ItemNo'] == '16' || $FIN_multis['ItemNo'] == '19' || $FIN_multis['ItemNo'] == '22' || $FIN_multis['ItemNo'] == '25' || $FIN_multis['ItemNo'] == '28' || $FIN_multis['ItemNo'] == '31' || $FIN_multis['ItemNo'] == '34' || $FIN_multis['ItemNo'] == '37' || $FIN_multis['ItemNo'] == '40' || $FIN_multis['ItemNo'] == '43' || $FIN_multis['ItemNo'] == '46' || $FIN_multis['ItemNo'] == '49' || $FIN_multis['ItemNo'] == '52' || $FIN_multis['ItemNo'] == '55' || $FIN_multis['ItemNo'] == '58' || $FIN_multis['ItemNo'] == '61' || $FIN_multis['ItemNo'] == '64' || $FIN_multis['ItemNo'] == '67' || $FIN_multis['ItemNo'] == '70' || $FIN_multis['ItemNo'] == '73' || $FIN_multis['ItemNo'] == '76' || $FIN_multis['ItemNo'] == '79' || $FIN_multis['ItemNo'] == '82' || $FIN_multis['ItemNo'] == '85' || $FIN_multis['ItemNo'] == '88' || $FIN_multis['ItemNo'] == '91' || $FIN_multis['ItemNo'] == '94' || $FIN_multis['ItemNo'] == '97' || $FIN_multis['ItemNo'] == '100' || $FIN_multis['ItemNo'] == '103' || $FIN_multis['ItemNo'] == '106' || $FIN_multis['ItemNo'] == '109' || $FIN_multis['ItemNo'] == '112' || $FIN_multis['ItemNo'] == '115' || $FIN_multis['ItemNo'] == '118' || $FIN_multis['ItemNo'] == '121' || $FIN_multis['ItemNo'] == '124' || $FIN_multis['ItemNo'] == '127' || $FIN_multis['ItemNo'] == '130' || $FIN_multis['ItemNo'] == '133' || $FIN_multis['ItemNo'] == '136' || $FIN_multis['ItemNo'] == '139' || $FIN_multis['ItemNo'] == '142' || $FIN_multis['ItemNo'] == '145' || $FIN_multis['ItemNo'] == '148' || $FIN_multis['ItemNo'] == '151' || $FIN_multis['ItemNo'] == '154' || $FIN_multis['ItemNo'] == '157' || $FIN_multis['ItemNo'] == '160' || $FIN_multis['ItemNo'] == '163' || $FIN_multis['ItemNo'] == '166' || $FIN_multis['ItemNo'] == '169' || $FIN_multis['ItemNo'] == '172' || $FIN_multis['ItemNo'] == '175' || $FIN_multis['ItemNo'] == '178' || $FIN_multis['ItemNo'] == '181' || $FIN_multis['ItemNo'] == '184' || $FIN_multis['ItemNo'] == '187' || $FIN_multis['ItemNo'] == '190' || $FIN_multis['ItemNo'] == '193' || $FIN_multis['ItemNo'] == '196' || $FIN_multis['ItemNo'] == '199' || $FIN_multis['ItemNo'] == '202' || $FIN_multis['ItemNo'] == '205' || $FIN_multis['ItemNo'] == '208' || $FIN_multis['ItemNo'] == '211' || $FIN_multis['ItemNo'] == '214' || $FIN_multis['ItemNo'] == '217' || $FIN_multis['ItemNo'] == '220' || $FIN_multis['ItemNo'] == '223' || $FIN_multis['ItemNo'] == '226' || $FIN_multis['ItemNo'] == '229' || $FIN_multis['ItemNo'] == '232' || $FIN_multis['ItemNo'] == '235' || $FIN_multis['ItemNo'] == '238' || $FIN_multis['ItemNo'] == '241' || $FIN_multis['ItemNo'] == '244' || $FIN_multis['ItemNo'] == '247' || $FIN_multis['ItemNo'] == '250' || $FIN_multis['ItemNo'] == '253' || $FIN_multis['ItemNo'] == '256' || $FIN_multis['ItemNo'] == '259' || $FIN_multis['ItemNo'] == '262' || $FIN_multis['ItemNo'] == '265' || $FIN_multis['ItemNo'] == '268' || $FIN_multis['ItemNo'] == '271' || $FIN_multis['ItemNo'] == '274' || $FIN_multis['ItemNo'] == '277' || $FIN_multis['ItemNo'] == '280' || $FIN_multis['ItemNo'] == '283' || $FIN_multis['ItemNo'] == '286' || $FIN_multis['ItemNo'] == '289' || $FIN_multis['ItemNo'] == '292' || $FIN_multis['ItemNo'] == '295' || $FIN_multis['ItemNo'] == '298' || $FIN_multis['ItemNo'] == '301' || $FIN_multis['ItemNo'] == '304' || $FIN_multis['ItemNo'] == '307' || $FIN_multis['ItemNo'] == '310' || $FIN_multis['ItemNo'] == '313' || $FIN_multis['ItemNo'] == '316' || $FIN_multis['ItemNo'] == '319' || $FIN_multis['ItemNo'] == '322' || $FIN_multis['ItemNo'] == '325' || $FIN_multis['ItemNo'] == '328' || $FIN_multis['ItemNo'] == '331' || $FIN_multis['ItemNo'] == '334' || $FIN_multis['ItemNo'] == '337' || $FIN_multis['ItemNo'] == '340' || $FIN_multis['ItemNo'] == '343' || $FIN_multis['ItemNo'] == '346' || $FIN_multis['ItemNo'] == '349' || $FIN_multis['ItemNo'] == '352' || $FIN_multis['ItemNo'] == '355' || $FIN_multis['ItemNo'] == '357' || $FIN_multis['ItemNo'] == '360' || $FIN_multis['ItemNo'] == '363' || $FIN_multis['ItemNo'] == '366' || $FIN_multis['ItemNo'] == '369' || $FIN_multis['ItemNo'] == '372' || $FIN_multis['ItemNo'] == '375' || $FIN_multis['ItemNo'] == '378' || $FIN_multis['ItemNo'] == '381' || $FIN_multis['ItemNo'] == '384' || $FIN_multis['ItemNo'] == '387' || $FIN_multis['ItemNo'] == '390' || $FIN_multis['ItemNo'] == '393' || $FIN_multis['ItemNo'] == '396' || $FIN_multis['ItemNo'] == '399' || $FIN_multis['ItemNo'] == '402' || $FIN_multis['ItemNo'] == '405' || $FIN_multis['ItemNo'] == '408' || $FIN_multis['ItemNo'] == '411' || $FIN_multis['ItemNo'] == '414' || $FIN_multis['ItemNo'] == '417' || $FIN_multis['ItemNo'] == '420' || $FIN_multis['ItemNo'] == '423' || $FIN_multis['ItemNo'] == '426' || $FIN_multis['ItemNo'] == '429' || $FIN_multis['ItemNo'] == '432' || $FIN_multis['ItemNo'] == '435' || $FIN_multis['ItemNo'] == '438' || $FIN_multis['ItemNo'] == '441' || $FIN_multis['ItemNo'] == '444' || $FIN_multis['ItemNo'] == '447' || $FIN_multis['ItemNo'] == '450' || $FIN_multis['ItemNo'] == '453' || $FIN_multis['ItemNo'] == '456' || $FIN_multis['ItemNo'] == '459' || $FIN_multis['ItemNo'] == '462' || $FIN_multis['ItemNo'] == '465' || $FIN_multis['ItemNo'] == '468' || $FIN_multis['ItemNo'] == '471' || $FIN_multis['ItemNo'] == '474' || $FIN_multis['ItemNo'] == '477' || $FIN_multis['ItemNo'] == '480' || $FIN_multis['ItemNo'] == '483' || $FIN_multis['ItemNo'] == '486' || $FIN_multis['ItemNo'] == '489' || $FIN_multis['ItemNo'] == '492' || $FIN_multis['ItemNo'] == '495' || $FIN_multis['ItemNo'] == '498' || $FIN_multis['ItemNo'] == '501' || $FIN_multis['ItemNo'] == '504' || $FIN_multis['ItemNo'] == '507' || $FIN_multis['ItemNo'] == '510' || $FIN_multis['ItemNo'] == '513' || $FIN_multis['ItemNo'] == '516' || $FIN_multis['ItemNo'] == '519' || $FIN_multis['ItemNo'] == '522' || $FIN_multis['ItemNo'] == '525' || $FIN_multis['ItemNo'] == '528' || $FIN_multis['ItemNo'] == '531' || $FIN_multis['ItemNo'] == '534' || $FIN_multis['ItemNo'] == '537' || $FIN_multis['ItemNo'] == '540' || $FIN_multis['ItemNo'] == '543' || $FIN_multis['ItemNo'] == '546' || $FIN_multis['ItemNo'] == '549' || $FIN_multis['ItemNo'] == '552' || $FIN_multis['ItemNo'] == '555' || $FIN_multis['ItemNo'] == '558' || $FIN_multis['ItemNo'] == '561' || $FIN_multis['ItemNo'] == '564' || $FIN_multis['ItemNo'] == '567' || $FIN_multis['ItemNo'] == '570' || $FIN_multis['ItemNo'] == '573' || $FIN_multis['ItemNo'] == '576' || $FIN_multis['ItemNo'] == '579' || $FIN_multis['ItemNo'] == '582' || $FIN_multis['ItemNo'] == '585' || $FIN_multis['ItemNo'] == '588' || $FIN_multis['ItemNo'] == '591' || $FIN_multis['ItemNo'] == '594' || $FIN_multis['ItemNo'] == '597' || $FIN_multis['ItemNo'] == '600' || $FIN_multis['ItemNo'] == '603' || $FIN_multis['ItemNo'] == '606' || $FIN_multis['ItemNo'] == '609' || $FIN_multis['ItemNo'] == '612' || $FIN_multis['ItemNo'] == '615' || $FIN_multis['ItemNo'] == '618' || $FIN_multis['ItemNo'] == '621' || $FIN_multis['ItemNo'] == '624' || $FIN_multis['ItemNo'] == '627' || $FIN_multis['ItemNo'] == '630' || $FIN_multis['ItemNo'] == '633' || $FIN_multis['ItemNo'] == '636' || $FIN_multis['ItemNo'] == '639' || $FIN_multis['ItemNo'] == '642' || $FIN_multis['ItemNo'] == '645' || $FIN_multis['ItemNo'] == '648' || $FIN_multis['ItemNo'] == '651' || $FIN_multis['ItemNo'] == '654' || $FIN_multis['ItemNo'] == '657' || $FIN_multis['ItemNo'] == '660' || $FIN_multis['ItemNo'] == '663' || $FIN_multis['ItemNo'] == '666' || $FIN_multis['ItemNo'] == '669' || $FIN_multis['ItemNo'] == '672' || $FIN_multis['ItemNo'] == '675' || $FIN_multis['ItemNo'] == '678' || $FIN_multis['ItemNo'] == '681' || $FIN_multis['ItemNo'] == '684' || $FIN_multis['ItemNo'] == '687' || $FIN_multis['ItemNo'] == '690' || $FIN_multis['ItemNo'] == '693' || $FIN_multis['ItemNo'] == '696' || $FIN_multis['ItemNo'] == '699' || $FIN_multis['ItemNo'] == '702' || $FIN_multis['ItemNo'] == '705' || $FIN_multis['ItemNo'] == '708' || $FIN_multis['ItemNo'] == '711' || $FIN_multis['ItemNo'] == '714' || $FIN_multis['ItemNo'] == '717' || $FIN_multis['ItemNo'] == '720' || $FIN_multis['ItemNo'] == '723' || $FIN_multis['ItemNo'] == '726' || $FIN_multis['ItemNo'] == '729' || $FIN_multis['ItemNo'] == '732' || $FIN_multis['ItemNo'] == '735' || $FIN_multis['ItemNo'] == '738' || $FIN_multis['ItemNo'] == '741' || $FIN_multis['ItemNo'] == '744' || $FIN_multis['ItemNo'] == '747' || $FIN_multis['ItemNo'] == '750' || $FIN_multis['ItemNo'] == '753' || $FIN_multis['ItemNo'] == '756' || $FIN_multis['ItemNo'] == '759' || $FIN_multis['ItemNo'] == '762' || $FIN_multis['ItemNo'] == '765' || $FIN_multis['ItemNo'] == '768' || $FIN_multis['ItemNo'] == '771' || $FIN_multis['ItemNo'] == '774' || $FIN_multis['ItemNo'] == '777' || $FIN_multis['ItemNo'] == '780' || $FIN_multis['ItemNo'] == '783' || $FIN_multis['ItemNo'] == '786' || $FIN_multis['ItemNo'] == '789' || $FIN_multis['ItemNo'] == '792' || $FIN_multis['ItemNo'] == '795' || $FIN_multis['ItemNo'] == '798' || $FIN_multis['ItemNo'] == '801' || $FIN_multis['ItemNo'] == '804' || $FIN_multis['ItemNo'] == '807' || $FIN_multis['ItemNo'] == '810' || $FIN_multis['ItemNo'] == '813' || $FIN_multis['ItemNo'] == '816' || $FIN_multis['ItemNo'] == '819' || $FIN_multis['ItemNo'] == '822' || $FIN_multis['ItemNo'] == '825' || $FIN_multis['ItemNo'] == '828' || $FIN_multis['ItemNo'] == '831' || $FIN_multis['ItemNo'] == '834' || $FIN_multis['ItemNo'] == '837' || $FIN_multis['ItemNo'] == '840' || $FIN_multis['ItemNo'] == '843' || $FIN_multis['ItemNo'] == '846' || $FIN_multis['ItemNo'] == '849' || $FIN_multis['ItemNo'] == '852' || $FIN_multis['ItemNo'] == '855' || $FIN_multis['ItemNo'] == '858' || $FIN_multis['ItemNo'] == '861' || $FIN_multis['ItemNo'] == '864' || $FIN_multis['ItemNo'] == '867' || $FIN_multis['ItemNo'] == '870' || $FIN_multis['ItemNo'] == '873' || $FIN_multis['ItemNo'] == '876' || $FIN_multis['ItemNo'] == '879' || $FIN_multis['ItemNo'] == '882' || $FIN_multis['ItemNo'] == '885' || $FIN_multis['ItemNo'] == '888' || $FIN_multis['ItemNo'] == '891' || $FIN_multis['ItemNo'] == '894' || $FIN_multis['ItemNo'] == '897' || $FIN_multis['ItemNo'] == '900' || $FIN_multis['ItemNo'] == '903' || $FIN_multis['ItemNo'] == '906' || $FIN_multis['ItemNo'] == '909' || $FIN_multis['ItemNo'] == '912' || $FIN_multis['ItemNo'] == '915' || $FIN_multis['ItemNo'] == '918' || $FIN_multis['ItemNo'] == '921' || $FIN_multis['ItemNo'] == '924' || $FIN_multis['ItemNo'] == '927' || $FIN_multis['ItemNo'] == '930' || $FIN_multis['ItemNo'] == '933' || $FIN_multis['ItemNo'] == '936' || $FIN_multis['ItemNo'] == '939' || $FIN_multis['ItemNo'] == '942' || $FIN_multis['ItemNo'] == '945' || $FIN_multis['ItemNo'] == '948' || $FIN_multis['ItemNo'] == '951' || $FIN_multis['ItemNo'] == '954' || $FIN_multis['ItemNo'] == '957' || $FIN_multis['ItemNo'] == '960' || $FIN_multis['ItemNo'] == '963' || $FIN_multis['ItemNo'] == '966' || $FIN_multis['ItemNo'] == '969' || $FIN_multis['ItemNo'] == '972' || $FIN_multis['ItemNo'] == '975' || $FIN_multis['ItemNo'] == '978' || $FIN_multis['ItemNo'] == '981' || $FIN_multis['ItemNo'] == '984' || $FIN_multis['ItemNo'] == '987' || $FIN_multis['ItemNo'] == '990' || $FIN_multis['ItemNo'] == '993' || $FIN_multis['ItemNo'] == '996' || $FIN_multis['ItemNo'] == '999') {
				if (in_array($FIN_multis['ItemNo'], $itemnums4)) {
					
					$this->SetXY(20, 239);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'CUD',0,0,'C');

					if (@$FIN_multis['Cbs'] == NULL) {
						$dutiable_value = $dutiable_value;
					}else{
						$dutiable_value = $FIN_multis['Cbs'];
					}

					
					$this->SetXY(35, 239);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($dutiable_value, 2),0,0,'R');
					
					$this->SetXY(62, 239);
					$this->SetFont('Arial','',8);
					$this->Cell(13,5,$FIN_multis['HsRate'].' %',0,0,'C');

					if (@$FIN_multis['CUD'] == NULL) {
						$FIN_multis['CUD'] = $dutiable_value * ($FIN_multis['HsRate']/100);
					}else{
						$FIN_multis['CUD'] = $FIN_multis['CUD'];
					}


					$this->SetXY(75, 239);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($FIN_multis['CUD'], 2),0,0,'R');
					
					$this->SetXY(102, 239);
					$this->SetFont('Arial','',8);
					$this->Cell(16,5,'0',0,0,'C');

					if (@$FIN_multis['Vbs'] == NULL) {
						//$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre))),2);
						$TAXExcisePerItem = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :" ";
						$VATBASE = round(((float)$dutiable_value + (float)$FIN_multis['CUD'] + (float)$BrokerFee + (float)$IPF + (float)$DOCFEE + (float)$BANKCHARGE + (float)(str_replace(',', '', $wharfage)) + (float)(str_replace(',', '', $arrastre)) + (float)$TAXExcisePerItem),2);
					}else{
						$VATBASE = $FIN_multis['Vbs'];
					}

					if ($data['FIN_data']['MDec'] == 'IED') {
						$VATBASE = 0;
					}

					$this->SetXY(20, 243);
					$this->SetFont('Arial','B',8);
					$this->Cell(15,5,'VAT',0,0,'C');
					
					$this->SetXY(35, 243);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATBASE, 2),0,0,'R');
					
					$this->SetXY(62, 243);
					$this->SetFont('Arial','',8);
					$this->Cell(13,5,'12 %',0,0,'C');

					if (!@$FIN_multis['VAT']) {
						$VATAMOUNT = round(($VATBASE * 0.12),2);
					}else{
						$VATAMOUNT = $FIN_multis['VAT'];
					}


					$this->SetXY(75, 243);
					$this->SetFont('Arial','',8);
					$this->Cell(27,5,number_format($VATAMOUNT, 2),0,0,'R');
					
					$this->SetXY(102, 243);
					$this->SetFont('Arial','',8);
					$this->Cell(16,5,'0',0,0,'C');
					
					$TAXAMT3 = !empty($FIN_multis['ExciseTotal']) ? $FIN_multis['ExciseTotal'] :"";
					$AVTAMT3 = 0;

				$AiCodeData = $this->checkAICODE($FIN_multis['HSCode'], $FIN_multis['HSCODE_TAR'], $FIN_multis['TARSPEC']); // Check if the HSCODE, HECODE_TAR and TarSpec is in CWSAICODE
				if ($FIN_multis['HSCode'] == "90049090" && $FIN_multis['HSCODE_TAR'] == "000") {
					$ExcItem = $dutiable_value;
					if (substr($FIN_multis['MDec'], 0, 1) != '7' && substr($FIN_multis['MDec'], 0, 1) != '8')  {

						$TAXExcisePerItem = ($ExcItem  * (str_replace("%", "", $AiCodeData[0]['Rate']) / 100));
						$this->SetXY(20, 247);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'EXC',0,0,'C');
						
						$this->SetXY(35, 247);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($ExcItem, 2),0,0,'R');

						$this->SetXY(62, 247);
						$this->SetFont('Arial','',8);
						$this->Cell(13,5,'20%',0,0,'R');
						
						//if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
						$this->SetXY(75, 248);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXExcisePerItem,2),0,0,'R');
						//}
						$TAXAMT3 = $TAXExcisePerItem;
					}
					else
					{

						//$TAXExcisePerItem = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
						$TAXExcisePerItem = ($ExcItem  * 0.20)  ;
						$this->SetXY(20, 247);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'EXC',0,0,'C');
						
						$this->SetXY(35, 247);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($ExcItem, 2),0,0,'R');

						$this->SetXY(62, 247);
						$this->SetFont('Arial','',8);
						$this->Cell(13,5,'20%',0,0,'R');
						
						//if ($data['FIN_data']['Stat'] != 'C' && $data['FIN_data']['Stat'] != 'S') {
						$this->SetXY(75, 248);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXExcisePerItem,2),0,0,'R');
						//}
						$TAXAMT3 = $TAXExcisePerItem;
					}
				}
				else
				{
					if($TAXAMT3 !='' && $FIN_multis['MSP'] == ""){
						$this->SetXY(20, 247);
						$this->SetFont('Arial','B',8);
						if (substr(($FIN_multis['HSCode']),0,4) != "8703") {
							$this->Cell(15,5,'EXC',0,0,'C');
						}else{
							$this->Cell(15,5,'AVT',0,0,'C');
							
						}
						
						// TAX BASE
						$this->SetXY(35, 247);
						$this->SetFont('Arial','',8);
						$this->Cell(27,5,number_format($FIN_multis['ExciseQty'], 2),0,0,'R');						
						
						$TAXExcise = !empty($FIN_multis['ExciseRate']) ? $FIN_multis['ExciseRate'] :" ";
						$TAXExciseUnit = !empty($FIN_multis['ExciseUnit']) ? $FIN_multis['ExciseUnit'] :" ";
						
						$this->SetXY(45, 247);
						//06202024: Spagara: if taxexciseunit is blank
						//if (substr(($FIN_multis['HSCode']),0,4) != "8703" && $TAXExciseUnit != " ") {
						if ($data['FIN_data']['HSCode'] != "71171920" && $data['FIN_data']['HSCode'] != "71131990" && $data['FIN_data']['HSCode'] != "33030000" && substr(($data['FIN_data']['HSCode']),0,4) != "8703" && $data['FIN_data']['MSP'] == "" && $TAXExciseUnit != " ") {
							if (strlen($TAXExcise . '/' . $TAXExciseUnit) >= 8) {
								$this->SetFont('Arial', '', 6.5);
							} else {
								$this->SetFont('Arial', '', 8);
							}
							$this->Cell(29,5,$TAXExcise.'/'.$TAXExciseUnit,0,0,'R');
						}else{
							$this->SetFont('Arial','',8);
							$this->Cell(29,5,$TAXExcise.' %',0,0,'R');
						}
						
						$this->SetXY(75, 248);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT3, 2).'',0,0,'R');
						
					}
					else
					{
						//2nd Page 3rd Item of Rider
						
						$MSP = 0;
						if ($FIN_multis['rul_cod'] == "AVT-AUTO" AND $FIN_multis['MSP'] != ""){
							$this->SetXY(20, 247);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,5,'AVT',0,0,'C');
							
							$this->SetXY(33, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(29,5,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 4;
								$MSP = ($FIN_multis['MSP'] * 0.04) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 20;
								$MSP = ($FIN_multis['MSP'] * 0.20) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 50;
								$MSP = ($FIN_multis['MSP'] * 0.50) * $FIN_multis['SupVal1'];
							}
							
							$AVTAMT3 = $MSP;
							
							$this->SetXY(45, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(29,5,$AVTRate.' %',0,0,'R');
							
							$this->SetXY(75, 248);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2).'',0,0,'R');
							
							$this->SetXY(102, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(16,5,'',0,0,'C');
				
						}
						elseif ($FIN_multis['rul_cod'] == "AVT_HYBRID" AND $FIN_multis['MSP'] != "") {
							$this->SetXY(20, 247);
							$this->SetFont('Arial','B',8);
							$this->Cell(15,5,'AVT',0,0,'C');
							
							$this->SetXY(33, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(29,5,number_format($FIN_multis['MSP'], 2),0,0,'R');
							
							if ($FIN_multis['MSP'] <= 600000)
							{
								$AVTRate = 2;
								$MSP = ($FIN_multis['MSP'] * 0.02) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 600000 && $FIN_multis['MSP'] <= 1000000)
							{
								$AVTRate = 5;
								$MSP = ($FIN_multis['MSP'] * 0.05) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 1000000 && $FIN_multis['MSP'] <= 4000000)
							{
								$AVTRate = 10;
								$MSP = ($FIN_multis['MSP'] * 0.10) * $FIN_multis['SupVal1'];
							}
							elseif($FIN_multis['MSP'] > 4000000)
							{
								$AVTRate = 25;
								$MSP = ($FIN_multis['MSP'] * 0.25) * $FIN_multis['SupVal1'];
							}
							
							$AVTAMT3 = $MSP;
							
							$this->SetXY(45, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(29,5,$AVTRate.' %',0,0,'R');
							
							$this->SetXY(75, 248);
							$this->SetFont('Arial','',8);
							$this->Cell(27,3,number_format($AVTAMT3, 2).'',0,0,'R');
							
							$this->SetXY(102, 247);
							$this->SetFont('Arial','',8);
							$this->Cell(16,5,'',0,0,'C');
						}
						
					}
				}		
					
					$AVTTAX = $AVTTAX + $TAXAMT3;
					
					if ($FIN_multis['HSCode'] == "27101211" || $FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972" || $FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983" || $FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221"){
						if ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] == "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = "";
						}elseif ($FIN_multis['HSCode'] == "27101211" && $FIN_multis['HSCode_Tar'] != "100") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}elseif ($FIN_multis['HSCode'] == "27101971" || $FIN_multis['HSCode'] == "27101972") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.03 * 0.06146428571;
							$TAXFMF = 1.03;
						}elseif ($FIN_multis['HSCode'] == "27101225" || $FIN_multis['HSCode'] == "27101222" || $FIN_multis['HSCode'] == "27101228" || $FIN_multis['HSCode'] == "27101983") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1 * 0.06146428571;
							$TAXFMF = "";
						}elseif ($FIN_multis['HSCode'] == "27101229" || $FIN_multis['HSCode'] == "27101223" || $FIN_multis['HSCode'] == "27101224" || $FIN_multis['HSCode'] == "27101226" || $FIN_multis['HSCode'] == "27101227" || $FIN_multis['HSCode'] == "27101212" || $FIN_multis['HSCode'] == "27101213" || $FIN_multis['HSCode'] == "27101221") {
							$TAXAMT7 = $FIN_multis['SupVal1'] * 1.10 * 0.06146428571;
							$TAXFMF = 1.10;
						}
						
						$this->SetXY(20, 250);
						$this->SetFont('Arial','B',8);
						$this->Cell(15,5,'FMF',0,0,'C');

						$this->SetXY(45, 251);
						$this->SetFont('Arial','',8);
						$this->Cell(25,3,$TAXFMF,0,0,'R');

						$this->SetXY(75, 250.5);
						$this->SetFont('Arial','',8);
						$this->Cell(27,3,number_format($TAXAMT7, 2),0,0,'R');
					}
					
					if ($FIN_multis['HSCode'] == "25232990" || $FIN_multis['HSCode'] == "25239000"){
						if ($FIN_multis['TARSPEC'] == "1001") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0233;	
							$TAXFMF = "2.33";	
						}elseif ($FIN_multis['TARSPEC'] == "1002") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['InvValue'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0276;	
							$TAXFMF = "2.76";	
						}elseif ($FIN_multis['TARSPEC'] == "1003") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0341;	
							$TAXFMF = "3.41";	
						}elseif ($FIN_multis['TARSPEC'] == "1004") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0619;	
							$TAXFMF = "6.19";	
						}elseif ($FIN_multis['TARSPEC'] == "1005") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0794;	
							$TAXFMF = "7.94";	
						}elseif ($FIN_multis['TARSPEC'] == "1006") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0948;	
							$TAXFMF = "9.48";	
						}elseif ($FIN_multis['TARSPEC'] == "1007") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.0951;	
							$TAXFMF = "9.51";	
						}elseif ($FIN_multis['TARSPEC'] == "1008") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1067;	
							$TAXFMF = "10.67";	
						}elseif ($FIN_multis['TARSPEC'] == "1009") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1099;	
							$TAXFMF = "10.99";	
						}elseif ($FIN_multis['TARSPEC'] == "1010") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1158;	
							$TAXFMF = "11.58";	
						}elseif ($FIN_multis['TARSPEC'] == "1011") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1206;	
							$TAXFMF = "12.06";	
						}elseif ($FIN_multis['TARSPEC'] == "1012") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.1529;	
							$TAXFMF = "15.29";	
						}elseif ($FIN_multis['TARSPEC'] == "1013") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2307;	
							$TAXFMF = "23.07";	
						}elseif ($FIN_multis['TARSPEC'] == "1014") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.00;	
							$TAXFMF = "0.00";	
						}elseif ($FIN_multis['TARSPEC'] == "") {	
							$TAXVALUE = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'];	
							$TAXAMT7 = $FIN_multis['InvValue'] * $FIN_multis['ExchRate'] * 0.2333;	
							$TAXFMF = "23.33";	
						}		
								
						$this->SetXY(20, 250);	
						$this->SetFont('Arial','B',8);	
						$this->Cell(15,5,'DPD',0,0,'C');	
						$this->SetXY(30, 251);		
						$this->SetFont('Arial','',8);		
						$this->Cell(24,3,number_format($TAXVALUE,2),0,0,'R');		
						$this->SetXY(45, 251);	
						$this->SetFont('Arial','',8);	
						$this->Cell(26,3,$TAXFMF.' %',0,0,'R');	
					}
					
					if ($FIN_multis['ExciseType'] == "AUTOMOBILE"){
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT7 + $AVTAMT3;
					} else {
						$TOTAL_TAXES = $FIN_multis['CUD'] + $VATAMOUNT + $TAXAMT3 + $TAXAMT7 + $AVTAMT3;
					}


					$this->SetXY(20, 278);
					$this->SetFont('Arial','',8);
					$this->Cell(82,7,number_format($TOTAL_TAXES, 2),0,0,'R');
					
				 /*$TAXAMT3 = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :"";
				if($TAXAMT3 !=''){
				$this->SetXY(20, 205);
				$this->SetFont('Arial','B',8);
				$this->Write(0, 'EXC');
			
				$this->SetXY(75, 205);
				$this->SetFont('Arial','',8);
				$this->Cell(25,3,number_format($TAXAMT3, 2),0,0,'R');
				}*/
				}

				/* End Item 2 */

				if ($counter % 3 == 0) {
					if ($counter != $len - 1) {
						$this->AddPage();

						if ($FIN_multis['ItemNo'] != 1) {
							$page_number = round($counter/3) + 2;
							$this->SetXY(102, 30);
							$this->SetFont('Arial','B',9);
							$this->Cell(7,5,$page_number,0,0,'C');
						}
					}
					$this->rider_page($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD);
					$y_2ndpage = -206.5;

				}
			}
		}
	}

	public function back_page($data, $tin, $FIN_multi, $RespGT, $RespIT, $RespHEAD, $Containers){
	
		$DM = @$_GET['DM'];
		
		if (($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S' && $data['FIN_data']['MDec'] != '8') && $data['FIN_data']['MDec'] != 'ID') {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');
			$this->Image('newbg.png',8.27,11.69,200);
		}
		
		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$this->SetXY(5, 5);
			$this->SetFont('Arial','B',8);
			$this->Write(3,'Disclaimer:','');
			$this->SetFont('Arial','',8);
			$this->Write(3,' This document is for information purposes only and shall NOT be submitted or used for processing at the Bureau of Customs nor be relied upon as a basis for compliance with any legal requirement.','');

			$this->SetXY(152, 5);
			$this->SetFont('Arial','',8);
			$this->Image('draft.png',4,30,200);

			if ($DM == 1) {
				$this->Image('DEMINIMIS.png',4,30,200);
			}
		}
		$this->SetXY(5, 15);
		$this->SetFont('Arial','B',8);
		$this->Write(0,'TEMPORARY SINGLE ADMINISTRATIVE DOCUMENT','');

		// Line break
		$this->Ln(40);

		//$this->Image('ins.jpg',172,286,30);
		if ($data['FIN_data']['MDec'] == 'ID' && ($data['FIN_data']['Stat'] != 'C' || $data['FIN_data']['Stat'] != 'S')) {
			$this->SetXY(152, 5);
			$this->SetFont('Arial','',8);
		}
	//	$this->Image('ins.jpg',172,286,30);


		$ext = array(
				'001',
				'011', 
				'021', 
				'026', 
				'054', 
				'056', 
				'058',
				'060',
				'0N2',
				'0N4',
				'0R1',
				'101',
				'201',
				'211',
				'301',
				'311',
				'401',
				'501',
				'511',
				'601',
				'611',
				'701',
				'801',
				'DPS',
				'E01',
				'L03',
				'L05',
				'L07',
				'M00',
				'M20',
				'M30',
				'M40',
				'M50',
				'M60',
				'N21',
				'N30',
				'N31',
				'N36',
				'N41',
				'N46',
				'N51',
				'N56',
				'N61',
				'N71',
				'N81',
				'N91',
				'P01',
				'P10',
				'P20',
				'P23',
				'P25',
				'P30',
				'P40',
				'P50',
				'P60',
				'P71',
				'P81',
				'P91',
				'P92',
				'R01',
				'R04',
				'R06',
				'R08',
				'R10',
				'R13',
				'R14',
				'R16',
				'R18',
				'R20',
				'R23',
				'R24',
				'R26',
				'R28',
				'R31',
				'R32',
				'R34',
				'R36',
				'R39',
				'R40',
				'R42',
				'R44',
				'R46',
				'R48',
				'R50',
				'R52',
				'R54',
				'R57',
				'R58',
				'R63',
				'R65',
				'R67',
				'R69',
				'R70',
				'R73',
				'R75',
				'R77',
				'R79',
				'R80',
				'R83',
				'R85',
				'R87',
				'R89',
				'R90',
				'R93',
				'R95',
				'R97',
				'R99',
				'T01',
				'T05',
				'T11',
				'T14',
				'T16',
				'T18',
				'T20',
				'T22',
				'T24',
				'T26',
				'T28',
				'T31',
				'T34',
				'T36',
				'T38',
				'T40',
				'T42',
				'T44',
				'T46',
				'T49',
				'T50',
				'T53',
				'T55',
				'T57',
				'T60',
				'T70',
				'T80',
				'T90',
				'TE1',
				'TN2',
				'TN4',
				'TN6',
				'TN8',
				'L09',
				'LK2Z',
				'L13');

		$this->SetXY(5, 20);
		$this->SetFont('Times','',20);
		$this->Cell(202,40,'',1,0,'C');

		/* Start 53 INTERNAL REVENUE (TAX PER BOX #45 & #47) */

		$this->SetXY(5, 23);
		$this->SetFont('Arial','',6);
		$this->Write(0, '53  INTERNAL REVENUE (TAX PER BOX #45 & #47)');

		$this->SetXY(5, 33);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TAXABLE VALUE PH');

		$dutiable_value = 0;
		$CUD_AMOUNT = 0;
		$BF = 0;
		$DV_backpage = 0;
		$wharfage = 0;
		$arrastre = 0;
		$w = 0;
		$a = 0;
		$total_vat = 0;

		$CUD = array();
		$Cbs = array();
		$VAT = array();
		$Vbs = array();
		$Freight = array();
		$Insurance = array();
		$OtherCost = array();
		$Wharfage = array();
		$Arrastre = array();
		$InvValue = array();
		foreach ($RespIT as $key => $items) {
			if ($items['TAXCODE'] == 'CUD') {
				$CUDAMOUNT = $items['TAXAMT'];
				$CUD[] = $CUDAMOUNT;
			}

			if ($items['TAXCODE'] == 'Cbs') {
				$CUDBASE = $items['TAXAMT'];
				$Cbs[] = $CUDBASE;
			}

			if ($items['TAXCODE'] == 'VAT') {
				$VATAMOUNT = $items['TAXAMT'];
				$VAT[] = $VATAMOUNT;
			}

			if ($items['TAXCODE'] == 'Vbs') {
				$VATBASE = $items['TAXAMT'];
				$Vbs[] = $VATBASE;
			}

			if ($items['TAXCODE'] == 'EFR') {
				$EFRAMOUNT = $items['TAXAMT'];
				$Freight[] = $EFRAMOUNT;
			}

			if ($items['TAXCODE'] == 'INS') {
				$INSAMOUNT = $items['TAXAMT'];
				$Insurance[] = $INSAMOUNT;
			}

			if ($items['TAXCODE'] == 'OTH') {
				$OTHAMOUNT = $items['TAXAMT'];
				$OtherCost[] = $OTHAMOUNT;
			}

			if ($items['TAXCODE'] == 'IFR') {
				$IFRAMOUNT = $items['TAXAMT'];
				$Wharfage[] = $IFRAMOUNT;
			}

			if ($items['TAXCODE'] == 'DED') {
				$DEDAMOUNT = $items['TAXAMT'];
				$Arrastre[] = $DEDAMOUNT;
			}

			if ($items['TAXCODE'] == 'INV') {
				$INVAMOUNT = $items['TAXAMT'];
				$InvValue[] = $INVAMOUNT;
			}
		}
		$c = 0;
		foreach ($CUD as $key => $CUDs) {
			$c ++;
			$FIN_multi[$c - 1]['CUD'] = $CUDs;
		}

		$cb = 0;
		foreach ($Cbs as $key => $Cbss) {
			$cb ++;
			$FIN_multi[$cb- 1]['Cbs'] = $Cbss;
		}

		$v = 0;
		foreach ($VAT as $key => $VATs) {
			$v ++;
			$FIN_multi[$v - 1]['VAT'] = $VATs;
		}
		$avt_multi = 0;
		foreach ($AVT as $key => $AVTs) {
			$avt_multi ++;
			$FIN_multi[$avt_multi - 1]['AVT'] = $AVTs;
		}

		$vb = 0;
		foreach ($Vbs as $key => $Vbss) {
			$vb ++;
			$FIN_multi[$vb - 1]['Vbs'] = $Vbss;
		}

		$f = 0;
		foreach ($Freight as $key => $Freights) {
			$f ++;
			$FIN_multi[$f - 1]['Freight'] = $Freights;
		}

		$i = 0;
		foreach ($Insurance as $key => $Insurances) {
			$i ++;
			$FIN_multi[$i - 1]['Insurance'] = $Insurances;
		}

		$o = 0;
		foreach ($OtherCost as $key => $OtherCosts) {
			$o ++;
			$FIN_multi[$o - 1]['Other_cost'] = $OtherCosts;
		}

		$wr = 0;
		foreach ($Wharfage as $key => $Wharfages) {
			$wr ++;
			$FIN_multi[$wr - 1]['Wharfage'] = $Wharfages;
		}

		$ar = 0;
		foreach ($Arrastre as $key => $Arrastres) {
			$ar ++;
			$FIN_multi[$ar - 1]['Arrastre'] = $Arrastres;
		}

		$iv = 0;
		foreach ($InvValue as $key => $InvValues) {
			$iv ++;
			$FIN_multi[$iv - 1]['InvVal'] = $InvValues;
		}

		$avt = 0;
		$vat = 0;
		$freight = 0;
		$inscost = 0;
		$othercost = 0;
		$invval = 0;
		$wharfage = 0;
		$arrastre = 0;
		
		/* Start Taxes */
		$TAXAMT1 = null;
		$TAXAMT2 = null;
		$TAXAMT3 = null;
		$TAXAMT4 = null;
		$TAXAMT5 = null;
		$TAXAMT6 = null;
		$TAXAMT7 = null;
		$TAXAMT8 = null;
		$TAXAMT9 = null;
		$TAXAMT10 = null;
		$TAXAMT11 = null;
		$TAXAMT12 = null;
		$TAXAMT13 = null;
		$TAXAMT14 = null;
		$TAXAMT15 = null;
		$TAXAMT16 = null;
		$TAXAMT17 = null;
		$TAXCODE1 = null;
		$TAXCODE2 = null;
		$TAXCODE3 = null;
		$TAXCODE4 = null;
		$TAXCODE5 = null;
		$TAXCODE6 = null;
		$TAXCODE7 = null;
		$TAXCODE8 = null;
		$TAXCODE9 = null;
		$TAXCODE10 = null;
		$TAXCODE11 = null;
		$TAXCODE12 = null;
		$TAXCODE13 = null;
		$TAXCODE14 = null;
		$TAXCODE15 = null;
		$TAXCODE16 = null;
		$TAXCODE17 = null;
		$TAXCODE18 = null;
		$TAXCODE19 = null;
		
		foreach ($RespGT as $key => $RespGTs) {
			$TAXAMTS = array(
				$RespGTs['TAXCODE'] => $RespGTs['TAXAMT']
			);

			if (array_key_exists('CUD', $TAXAMTS)) {
				$TAXCODE1 = 'CUD';
				$TAXAMT1 = $TAXAMTS['CUD'];
			}

			if (array_key_exists('VAT', $TAXAMTS)) {
				$TAXCODE2 = 'VAT';
				$TAXAMT2 = $TAXAMTS['VAT'];
			}

			if (array_key_exists('EXC', $TAXAMTS)) {
				$TAXCODE3 = 'EXC';
				$TAXAMT3 = $TAXAMTS['EXC'];
			}

			if (array_key_exists('AVT', $TAXAMTS)) {
				$TAXCODE4 = 'AVT';
				$TAXAMT4 = $TAXAMTS['AVT'];
			}

			if (array_key_exists('CSD', $TAXAMTS)) {
				$TAXCODE5 = 'CSD';
				$TAXAMT5 = $TAXAMTS['CSD'];
			}

			if (array_key_exists('FIN', $TAXAMTS)) {
				$TAXCODE6 = 'FIN';
				$TAXAMT6 = $TAXAMTS['FIN'];
			}

			if (array_key_exists('DPD', $TAXAMTS)) {
				$TAXCODE7 = 'DPD';
				$TAXAMT7 = $TAXAMTS['DPD'];
			}

			if (array_key_exists('SGD', $TAXAMTS)) {
				$TAXCODE9 = 'SGD';
				$TAXAMT9 = $TAXAMTS['SGD'];
			}

			if (array_key_exists('IPF', $TAXAMTS)) {
				$TAXCODE8 = 'IPF';
				$TAXAMT8 = $TAXAMTS['IPF'];
			}

			if (array_key_exists('D&F', $TAXAMTS)) {
				$TAXCODE10 = 'D&F';
				$TAXAMT10 = $TAXAMTS['D&F'];
			}

			if (array_key_exists('BNF', $TAXAMTS)) {
				$TAXCODE18 = 'BNF';
				$TAXAMT18 = $TAXAMTS['BNF'];
			}

			if (array_key_exists('FF', $TAXAMTS)) {
				$TAXCODE11 = 'FF';
				$TAXAMT11 = $TAXAMTS['FF'];
			}

			if (array_key_exists('PSI', $TAXAMTS)) {
				$TAXCODE12 = 'PSI';
				$TAXAMT12 = $TAXAMTS['PSI'];
			}

			if (array_key_exists('TSF', $TAXAMTS) || array_key_exists('CTF', $TAXAMTS)) {
				if(array_key_exists('TSF', $TAXAMTS)){
					$TAXCODE13 = 'TSF';
					$TAXAMT13 = $TAXAMTS['TSF'];
				}elseif(array_key_exists('CTF', $TAXAMTS)){
					$TAXCODE13 = 'CTF';
					$TAXAMT13 = $TAXAMTS['CTF'];
				}else{
				//none
				}
				
			}
			
			if (array_key_exists('SGL', $TAXAMTS)) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = $TAXAMTS['SGL'];
			}
			
			if (array_key_exists('CSF', $TAXAMTS)) {
				$TAXCODE15 = 'CSF';
				$TAXAMT15 = $TAXAMTS['CSF'];
			}

			if (array_key_exists('CDS', $TAXAMTS)) {
				$TAXCODE16 = 'CDS';
				$TAXAMT16 = $TAXAMTS['CDS'];
			}

			if (array_key_exists('IRS', $TAXAMTS)) {
				$TAXCODE17 = 'IRS';
				$TAXAMT17 = $TAXAMTS['IRS'];
			}

			if (array_key_exists('IPC', $TAXAMTS)) {
				$TAXCODE18 = 'IPC';
				$TAXAMT18 = $TAXAMTS['IPC'];
			}

			if (array_key_exists('TC', $TAXAMTS)) {
				$TAXCODE19 = 'TC';
				$TAXAMT19 = $TAXAMTS['TC'];
			}
		}

		//07042024:SPagara: SGL for 4ES
	$CustomVal1 = str_replace(',', '', $data['FIN_data']['CustomVal']);

	//07042024:SPagara: SGL for 4ES
	if ($data['FIN_data']['MDec']== '4ES') {
		if ($data['FIN_data']['InvCurr'] == "USD"){
			if ((float)$CustomVal1 <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}elseif ($data['FIN_data']['InvCurr'] == "PHP"){
			$NewVal = (float)$CustomVal1 /$data['ExchRate_queryUSD']['RAT_EXC'];
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}else{
			$NewVal = ((float)$CustomVal1 * $data['FIN_data']['ExchRate']) / $data['ExchRate_queryUSD']['RAT_EXC'];
			//print_r($NewVal); die();
			if ($NewVal <= 200000) {
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 2500;
			}else{
				$TAXCODE14 = 'SGL';
				$TAXAMT14 = 3000;
			}
		}
	}
		foreach ($FIN_multi as $key => $FIN_multis) {
			// print_r($FIN_multis);
		/* Start FIO */
			if(@$FIN_multis['InvCurr'] == 'PHP' && $FIN_multis['CustCurr'] == 'PHP' && $FIN_multis['FreightCurr'] == 'PHP' && $FIN_multis['InsCurr'] == 'PHP' && $FIN_multis['OtherCurr'] == 'PHP'){
				if (@$FIN_multis['Freight'] != null) {
					$freight = $FIN_multis['Freight'];
				}else{
					$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost']))),2);
				}

				if (@$FIN_multis['Insurance'] != null) {
					$inscost = $FIN_multis['Insurance'];
				}else{
					$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']))),2);
				}

				if (@$FIN_multis['Other_cost'] != null) {
					$othercost = $FIN_multis['Other_cost'];
				}else{
					$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']))),2);
				}

				if (@$FIN_multis['InvVal'] != null) {
					$invval = $FIN_multis['InvVal'];
				}else{
					$invval = round((str_replace(',', '', $FIN_multis['InvValue'])),2);
				}

				$FIO = $freight + $inscost + $othercost;

				$dutiable_value += $FIO + $invval;

				

				if (@$FIN_multis['CUD']) {
					$CUD_AMOUNT += (float)$FIN_multis['CUD'];
				}else{
					$CUD_AMOUNT += round((($FIO + $invval) * $FIN_multis['HsRate']/100),2);
				}

			}else{
				if (@$FIN_multis['Freight'] != null) {
					$freight = str_replace(',', '', $FIN_multis['Freight']);
				}else{
					//06062024: SPagara: Round up update
					//$freight = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['FreightCost'])) * $FIN_multis['FExchRate']),2);
					$freight = Round(((Round(str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal']) * str_replace(',', '', $FIN_multis['FreightCost']), 3)) * $FIN_multis['FExchRate']),2, PHP_ROUND_HALF_UP);
				}

				if (@$FIN_multis['Insurance'] != null) {
					$inscost = str_replace(',', '', $FIN_multis['Insurance']);
				}else{
					//06062024: SPagara: Round up update
					//$inscost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost'])) * $FIN_multis['IExchRate']),2);
					$inscost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['InsCost']), 3) * $FIN_multis['IExchRate']),2, PHP_ROUND_HALF_UP);
				}

				if (@$FIN_multis['Other_cost'] != null) {
					$othercost = str_replace(',', '', $FIN_multis['Other_cost']);
				}else{
					//06062024: SPagara: Round up update
					//$othercost = round((((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost'])) * $FIN_multis['OExchRate']),2);
					$othercost = Round((Round((str_replace(',', '', $FIN_multis['InvValue']) / str_replace(',', '', $FIN_multis['CustomVal'])) * str_replace(',', '', $FIN_multis['OtherCost']), 3) * $FIN_multis['OExchRate']),2, PHP_ROUND_HALF_UP);
				}

				if (@$FIN_multis['InvVal'] != null) {
					$invval = str_replace(',', '', $FIN_multis['InvVal']);
				}else{
					$invval = round((str_replace(',', '', $FIN_multis['InvValue']) * $FIN_multis['ExchRate']),2);
				}	

				$FIO = $freight + $inscost + $othercost;

				$dutiable_value += $FIO + $invval;


				if (@$FIN_multis['CUD']) {
					$CUD_AMOUNT += (float)$FIN_multis['CUD'];
				}else{
					$CUD_AMOUNT += round((($FIO + $invval) * $FIN_multis['HsRate']/100),2);
				}

			}

			/* Start WHarfage and Arrastre Computation */
			$whar = (str_replace(',', '', @$FIN_multis['WharCost']));
			$arras = (str_replace(',', '', @$FIN_multis['ArrasCost']));


			if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S' || $data['FIN_data']['Stat'] == 'AS') {
				$wharfage = $whar;
				$arrastre = $arras;
			}else{
				$wharfage += (str_replace(',', '', $FIN_multis['Wharfage']));
				$arrastre += (str_replace(',', '', $FIN_multis['Arrastre']));
			}


			/* End WHarfage and Arrastre Computation */
			if (@$FIN_multis['Cbs']) {
				$DV_backpage += (float)$FIN_multis['Cbs'];
			}else{
				$DV_backpage += $FIO + $invval;
			}

			if (@$FIN_multis['VAT']) {
				$total_vat += $FIN_multis['VAT'];
			}

			$vat += @$FIN_multis['VAT'];
			
			//if (@$FIN_multis['ExciseTotal']) {
			//	$total_excise += $FIN_multis['ExciseTotal'];
			//}

		}
		
		/* Start Broker Fee */
	

		$BrokerFee = 0;
	 
		$count = strlen(explode('.', $DV_backpage));
		 
		if($DV_backpage >= 0 && $DV_backpage < 10000){$BrokerFee = 1300;}
		if($DV_backpage >= 10000 && $DV_backpage <= 20000){$BrokerFee = 2000;}
		if($DV_backpage >= 20000 && $DV_backpage <= 30000){$BrokerFee = 2700;}
		if($DV_backpage >= 30000 && $DV_backpage <= 40000){$BrokerFee = 3300;}
		if($DV_backpage >= 40000 && $DV_backpage <= 50000){$BrokerFee = 3600;}
		if($DV_backpage >= 50000 && $DV_backpage <= 60000){$BrokerFee = 4000;}
		if($DV_backpage >= 60000 && $DV_backpage <= 100000){$BrokerFee = 4700;}
		if($DV_backpage >= 100000 && $DV_backpage <= 200000){$BrokerFee = 5300;}
		if($DV_backpage >= 200000){$BrokerFee = round(((($DV_backpage - 200000) * 0.00125) + 5300),2);}
		 
		if ($data['FIN_data']['MDec'] == 'ID' || $data['FIN_data']['MDec'] == 'IES'){
			$BrokerFee = 700;
		}
		/* End Broker Fee */

		/* Start IPF as per ms. Aileen 11/6/2018 */
		//06062024: SPagara: Update on IPC including mode of dec 7 and IE*/
		/*if($DV_backpage >= 0 && $DV_backpage <= 250000){$IPF = 250;}
		if($DV_backpage > 250000 && $DV_backpage <= 500000){$IPF = 500;}
		if($DV_backpage > 500000 && $DV_backpage <= 750000){$IPF = 750;}
		if($DV_backpage > 750000 && $DV_backpage <= 999999999999){$IPF = 1000;}*/

		if($DV_backpage >= 0 && $DV_backpage <= 250000){$IPF = 250;}
		if($DV_backpage > 25000 && $DV_backpage <= 50000){$IPF = 500;}
		if($DV_backpage > 50000 && $DV_backpage <= 250000){$IPF = 750;}
		if($DV_backpage > 250000 && $DV_backpage <= 500000){$IPF = 1000;}
		if($DV_backpage > 500000 && $DV_backpage <= 750000){$IPF = 1500;}
		if($DV_backpage > 750000 ){$IPF = 2000;}

		/*if ($data['FIN_data']['MDec'] == '7' || $data['FIN_data']['MDec'] == '7T') {
			$IPF = 250;
		}

		if (($data['FIN_data']['MDec'] == 'IES') || ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4')){
			$IPF = 0;
		}*/
		if (($data['FIN_data']['MDec'] == '8ZN') || ($data['FIN_data']['MDec'] == '8PP') || ($data['FIN_data']['MDec'] == '8PE') || ($data['FIN_data']['MDec'] == '8ZE')){
			$IPF = 250;
		}

		if (isset($TAXAMT8) && !empty($TAXAMT8)) {
			$IPF = $TAXAMT8;
		}
		/* End IPF*/

		$this->IPF_C = number_format($IPF,2);

		/* Start Doc Fee */
		/*
		$filterdate =date_format($data['FIN_data']['SentDate'],"d/m/Y H:i:s");
		 
		//added conditon 
		if($filterdate  < '11/14/2018 19:00' && $filterdate != NULL){
		$DOCFEE = 265;
		}else{
		$DOCFEE = 280;
		}
		*/
		$filterdate =date_format($data['FIN_data']['SentDate'],"d/m/Y H:i");
		$filterdate1 = strtotime($filterdate);
		$olddate = strtotime('11/14/2018 19:00');

		//$olddate = date('d/m/Y H"',$time); 
		

		 // GET THE DOCFEE FROM  TBLRESP_GT  by larren
			foreach($RespGT as $key){
				if($key['TAXCODE'] =='CDS'){
					$cds_amt = $key['TAXAMT'];
				}
				if($key['TAXCODE'] =='IRS'){
					$irs_amt = $key['TAXAMT'];
				}
			}
			
			$DOCFEE = $cds_amt + $irs_amt;
		 
			if($DOCFEE==''){
			
			if($filterdate1 < $olddate && $filterdate1!= NULL && $filterdate1!= ''){
				$DOCFEE = 130;
				}else{
				//$DOCFEE = 280;
				//06062024: Spagara: Update
				$DOCFEE = 130;
				}
			}else{
				$DOCFEE;
			}
			
			if ($data['FIN_data']['MDec'] == 'IES'){
				//$DOCFEE = 30;
				//06062024: Spagara: Update
				$DOCFEE = 130;
			}
			
		if ($data['FIN_data']['MDec'] == 'ID'){
			$DOCFEE = 130;
		}
		
		if ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4'){
			$DOCFEE = 130;
		}
		/* End Doc Fee */

		/* Start Bank Charge */
		if(@$FIN_multis['WOBankCharge'] == 1){
			$BANKCHARGE = 0;
		}else{
			$BANKCHARGE = round(($DV_backpage * 0.00125),2);
		}

		if ($data['FIN_data']['OffClearance'] == 'Ninoy Aquino Intl Airport ') {
			$wharfage = 0;
			$arrastre = 0;
		}
		

		$this->SetXY(40, 30);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,number_format($DV_backpage, 2),'B',0,'R');


		$this->SetXY(5, 40);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'BANK CHARGES');

		$this->SetXY(40, 37);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,number_format($BANKCHARGE,2),'B',0,'R');

		$this->SetXY(5, 47);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'CUSTOMS DUTY');

		$this->SetXY(40, 44);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,number_format($CUD_AMOUNT,2),'B',0,'R');


		$this->SetXY(5, 54);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'BROKERAGE FEE');
		 
		$this->SetXY(40, 51);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,number_format($BrokerFee,2),'B',0,'R');

		
		$this->SetXY(75, 33);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'WHARFAGE');

		$this->SetXY(114, 30);
		$this->SetFont('Arial','B',9);
		if ($wharfage == NULL || $wharfage == '') {
			$wharfage = 0;
		}


		$this->Cell(30,5,number_format((float)$wharfage, 2),'B',0,'R');


		$this->SetXY(75, 44);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'ARRASTRE CHARGE');

		$this->SetXY(114, 41);
		$this->SetFont('Arial','B',9);
		if ($arrastre == NULL || $arrastre == '') {
			$arrastre = '0.00';
		}

		$this->Cell(30,5,number_format((float)$arrastre,2),'B',0,'R');


		$this->SetXY(75, 54);
		$this->SetFont('Arial','B',9);
		//$this->Write(0, 'DOCUMENTARY STAMP');
		if ($data['FIN_data']['MDec'] == 'IES'){
			//$this->Write(0, 'IRS');
			$this->Write(0, 'DOCUMENTARY STAMP');
		}else{
			$this->Write(0, 'DOCUMENTARY STAMP');
		}

		$this->SetXY(114, 52);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,number_format($DOCFEE,2),'B',0,'R');


		$this->SetXY(148, 33);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'OTHERS');

		$this->SetXY(178, 30);
		$this->SetFont('Arial','B',9);
		$this->Cell(27,5,number_format($IPF,2),'B',0,'R');


		$this->SetXY(148, 40);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TOTAL');
		
		$TAXAMT3 = 0;	
		foreach ($FIN_multi as $key => $FIN_multis_exctotal) {	
			$TAXAMT3 += $FIN_multis_exctotal['ExciseTotal'];	
		}	
		if (isset($_GET['avt'])) {	
			$totAVT = $_GET['avt'];		
			$avttax1 = $totAVT * 0.12;	
		}else{	
			$totAVT = 0;	
			$avttax1 = 0;	
		}
		
		//if ($vat == 0) {
			//if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] != 'S')) {
				//$LANDEDCOST = 0;
			//}
		//}else
//			$LANDEDCOST = round(($DV_backpage + $CUD_AMOUNT + $BANKCHARGE + $BrokerFee + $wharfage + $arrastre + $DOCFEE + $IPF),2);
		//}

$LANDEDCOST = ($totAVT + $TAXAMT3 + $DV_backpage + $CUD_AMOUNT + $BANKCHARGE + $BrokerFee + $wharfage + $arrastre + $DOCFEE + $IPF);


		$this->SetXY(178, 38);
		$this->SetFont('Arial','B',9);
		$this->Cell(27,5,number_format($LANDEDCOST,2),0,0,'R');

		$this->SetXY(148, 47);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'LANDED COST PH');

		$this->SetXY(178, 45);
		$this->SetFont('Times','B',9);
		$this->Cell(27,5,'x 12%','B',0,'R');


		$this->SetXY(148, 54);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TOTAL VAT PH');


		if (isset($vat) && !empty($vat)) {
			// $TotalVat = $vat;
			$TotalVat = round(($LANDEDCOST * 0.12), 2);
		}elseif (isset($vat)) {
			if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] != 'S') {
				$TotalVat = round(($LANDEDCOST*0.12), 2);
				//$totAVTNew = $_GET['avt'];
				//$avttax1New = $totAVTNew * 0.12;
				$TotalVatNew = $avttax1New + $TotalVat;
				//$TotalVat = number_format($sumAVTVATNew, 2);
			}else{
				$TotalVat = 0;
			}
		}else{
			$TotalVat = round(($LANDEDCOST*0.12), 2);
		}

		if ($data['FIN_data']['MDec'] == 'IED') {
			$TotalVat = 0;
		}

		
		$this->VAT_C = $TotalVat;
		
		/*$TAXExciseTotal = !empty($data['FIN_others']['ExciseTotal']) ? $data['FIN_others']['ExciseTotal'] :" ";
		$TAXExciseTotalAmount = $TAXExciseTotal * 0.12;
		$TotalValue = $TAXExciseTotalAmount + $TotalVat;*/
		$this->SetXY(178, 51);
		$this->SetFont('Arial','B',9);
		//$this->Cell(27,5,number_format($TotalVatNew,2),'B',0,'R');
		$this->Cell(27,5,number_format($TotalVat,2),'B',0,'R');

		/* End 53 INTERNAL REVENUE (TAX PER BOX #45 & #47) */

		$this->SetXY(5, 60);
		$this->SetFont('Arial','',9);
		$this->Cell(202,10,'DESCRIPTION IN TARIFF TERMS SHOULD BE',0,0,'C');

		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		/* Start Box 54 SECTION */

		$this->SetXY(5, 72);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '54  SECTION');

		$this->SetXY(25, 70);
		$this->SetFont('Times','',20);
		$this->Cell(38,10,'','LR',0,'C');

		$this->SetXY(130, 70);
		$this->SetFont('Times','',20);
		$this->Cell(30,53,'','LR',0,'C');

		/* End Box 54 SECTION */

		/* Start 55  NO. OF PACKAGES EXAMINED */

		$this->SetXY(25, 72);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '55  NO. OF PACKAGES EXAMINED');
		
		/* End 65  NO. OF PACKAGES EXAMINED */

		/* Start EXAMINATION RETURN */

		$this->SetXY(78, 75);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'EXAMINATION RETURN');
		
		/* End EXAMINATION RETURN */

		/* Start 56  DATE RECIEVED */

		$this->SetXY(130, 72);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '56  DATE RECIEVED');
		
		/* End 56  DATE RECIEVED */

		/* Start 57  DATE RELEASED */

		$this->SetXY(160, 72);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '57  DATE RELEASED');
		
		/* End 56  DATE RELEASED */

		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(202,23,'','B',0,'C');

		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(202,23,'','B',0,'C');

		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(202,53,'','B',0,'C');

		$this->SetXY(5, 80);
		$this->SetFont('Times','',20);
		$this->Cell(10,43,'','R',0,'C');

		$this->SetXY(93, 80);
		$this->SetFont('Times','',20);
		$this->Cell(17,43,'','LR',0,'C');


		$this->SetXY(184, 80);
		$this->SetFont('Arial','B',8);
		$this->Cell(17,43,'','L',0,'C');

		$this->SetXY(80, 93);
		$this->SetFont('Times','',20);
		$this->Cell(17,30,'','L',0,'C');

		$this->SetXY(80, 93);
		$this->SetFont('Times','',20);
		$this->Cell(127,10,'','B',0,'C');

		$this->SetXY(80, 103);
		$this->SetFont('Times','',20);
		$this->Cell(127,10,'','B',0,'C');

		/* Start ITEM NO */

		$this->SetXY(6.5, 85);
		$this->SetFont('Arial','B',6);
		$this->Write(0, 'ITEM');

		$this->SetXY(7.5, 88);
		$this->SetFont('Arial','B',6);
		$this->Write(0, 'NO');
		
		/* End ITEM NO */


		/* Start 55 Description in Tariff terms should be */

		$this->SetXY(15, 82);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '58');

		$this->SetXY(20.5, 87);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'DESCRIPTION IN TARIFF TERMS SHOULD BE');
		
		/* End 55 Description in Tariff terms should be */

		/* Start QTY */

		$this->SetXY(97.5, 87);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'QTY');
		
		/* End QTY */

		/* Start UNIT */

		$this->SetXY(115.5, 87);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'UNIT');
		
		/* End UNIT */


		/* Start UNIT VALUE */

		$this->SetXY(130, 81);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(30,12,'UNIT VALUE',0,'C');
		
		/* End UNIT VALUE */

		/* Start TARIFF HEADING */

		$this->SetXY(166, 85);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'TARIFF');

		$this->SetXY(164, 88);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'HEADING');
		
		/* End TARIFF HEADING */

		/* Start RATE */

		$this->SetXY(181, 81);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(30,12,'RATE',0,'C');
		
		/* End RATE */


		/* Start PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS */

		$this->SetXY(5, 124.4);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(202,5,'PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS',0,'C');
		
		/* End PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS */


		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(202,60,'',1,0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(202,75,'',1,0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(115,75,'','R',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 155);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 155);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 165);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 170);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 175);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 180);
		$this->SetFont('Times','',20);
		$this->Cell(202,5,'','B',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Arial','B',10);
		$this->Cell(115,10,'REVISED CHARGES',0,0,'C');

		$this->SetXY(120, 135);
		$this->SetFont('Arial','B',10);
		$this->Cell(87,10,'LIQUIDATION',0,0,'C');

		$this->SetXY(5, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '59 CHARGES');

		$this->SetXY(7, 152.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Duty');

		$this->SetXY(7, 157.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'BIR Taxes');

		$this->SetXY(7, 162.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'VAT');

		$this->SetXY(5, 167.5);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'Excise Tax/Ad Valorem');

		$this->SetXY(7, 172.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Others');

		$this->SetXY(7, 177.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Surcharges');

		$this->SetXY(7, 182.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'TOTAL');

		$this->SetXY(5, 187.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '63  ACTION DIRECTED/RECOMMENDED');

		$this->SetXY(63, 187.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '64');

		$this->SetXY(73, 195);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(73, 208);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(34, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '60 DECLARATION');

		$this->SetXY(67, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '61 FINDINGS');

		$this->SetXY(92.5, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '62 DIFFERENCES');

		$this->SetXY(120, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '65 LIQUIDATED Amount');

		$this->SetXY(120, 157.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '66  SHORT/EXCESS');

		$this->SetXY(120, 167.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '67  REMARKS');

		$this->SetXY(120, 187.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '68');

		$this->SetXY(150, 195);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'COO III');

		$this->SetXY(178, 195);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(120, 199.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '69');

		$this->SetXY(150, 208);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'COO V');

		$this->SetXY(178, 208);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(62.5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(144.5,52,'','LB',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(28.75,40,'','R',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(86.25,40,'','R',0,'C');

		$this->SetXY(5, 215);
		$this->SetFont('Times','',20);
		$this->Cell(202,68,'',1,0,'C');

		/* Start CONTINUATION FROM BOX # 31 */

		$this->SetXY(5, 215);
		$this->SetFont('Arial','B',10);
		$this->Cell(202,10,'FREE DISPOSAL',0,0,'C');

		$this->SetXY(7, 219);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'CONTINUATION FROM BOX # 31');


		/* Start Containers */

		$this->SetXY(7, 223);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Container Numbers continuation:');

		$cont_y = 224;
		$count = 1;
		$x = 7;
		foreach ($Containers as $key => $Container) {
			$count++;
			if ($count == 26) {
				$x = $x + 25;
				$cont_y = 224;
			}

			if ($count == 50) {
				$x = $x + 25;
				$cont_y = 224;
			}

			if ($count == 74) {
				$x = $x + 25;
				$cont_y = 224;
			}

			if ($count == 98) {
				$x = $x + 25;
				$cont_y = 224;
			}

			if ($count == 122) {
				$x = $x + 25;
				$cont_y = 224;
			}

			$cont_y += 3;
			$this->SetXY($x, $cont_y);
			$this->SetFont('Arial','',8);
			$this->Write(0, $Container['container']);

		}

		/* End Containers */




		/* Start CUD */
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$TAXCODE1 = "CUD";
			//$TAXAMT1 = $this->CUD_C;//added by sir tim
			$TAXAMT1 = number_format($CUD_AMOUNT,2); //added by larren 05282019

		}else{
			if ($TAXAMT1 == NULL) {
				$TAXAMT1 = NULL;
			}else{
				$TAXAMT1 = number_format($TAXAMT1, 2);
			}
		}

		$this->SetXY(147, 220);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE1);

		$this->SetXY(167, 218);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,4,$TAXAMT1,0,0,'R');

		/* End CUD */

		/* Start VAT */
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$TAXCODE2 = "VAT";
			$TAXAMT222 = $this->VAT_C; //Sir Tim
			//$totAVT = $_GET['avt'];
			//$avttax1 = $totAVT * 0.12;
			$sumAVTVAT = $avttax1 + $TAXAMT222;
			//$TAXAMT2 = number_format($sumAVTVAT, 2);
			$TAXAMT2 = number_format($TAXAMT222, 2);

		}else{
			if ($TAXAMT2 == NULL) {
				$TAXAMT2 = NULL;
			}else{
				$TAXAMT2 = number_format($TAXAMT2, 2);
			}
		}

		$this->SetXY(147, 224);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE2);

		$this->SetXY(167, 222);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,4,$TAXAMT2,0,0,'R');

		/* End VAT */

		/* Start EXC */
		
		if ($TAXAMT3 == NULL) {
			$TAXAMT3 = NULL;
		}else{
			$TAXAMT3 = number_format($TAXAMT3, 2);
		}
		
		// print_r($total_excise); die();
	//comment ount 12072022
	/*if ($total_excise != '' && $total_excise != 0 && $total_excise != NULL) {
			if ($_GET['avt'] == "" || $_GET['avt'] == 0) {
				$this->SetXY(147, 228);
				$this->SetFont('Arial','B',9);
				$this->Write(0, 'EXC');

				$this->SetXY(167, 226);
				$this->SetFont('Arial','B',9);
				$this->Cell(40,4,number_format($total_excise, 2),0,0,'R');
			}
			
		}*/

	if (isset($TAXAMT3) && !is_null($TAXAMT3)) {	
			$this->SetXY(147, 228);	
			$this->SetFont('Arial','B',9);	
			$this->Write(0, "EXC");	
			$this->SetXY(167, 226);	
			$this->SetFont('Arial','B',9);	
			$this->Cell(40,4,$TAXAMT3,0,0,'R');
	}
		/* End EXC */

		/* Start AVT */
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$TAXCODE4 = "AVT";
			$avttax = $_GET['avt'];
			$TAXAMT4 = number_format($avttax, 2);
			
		}else{
			//if ($TAXAMT4 != "" || $TAXAMT4 != NULL || $TAXAMT4 != 0) {
			if ($TAXAMT4 == NULL) {
				$TAXAMT4 = NULL;
			}else{
				$TAXAMT4 = number_format($TAXAMT4, 2);
			}
		}
		
		if ($_GET['avt'] != "" && $_GET['avt'] != 0) {
			$this->SetXY(147, 228);
			$this->SetFont('Arial','B',9);
			$this->Write(0, $TAXCODE4);

			$this->SetXY(167, 226);
			$this->SetFont('Arial','B',9);
			$this->Cell(40,4,$TAXAMT4,0,0,'R');
		}


		/* End AVT */

		/* Start CSD */

		if ($TAXAMT5 == NULL) {
			$TAXAMT5 = NULL;
		}else{
			$TAXAMT5 = number_format($TAXAMT5, 2);
		}

		$this->SetXY(147, 231);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE5);

		$this->SetXY(167, 229);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,4,$TAXAMT5,0,0,'R');

		/* End CSD */

		/* Start FIN */

		if ($TAXAMT6 == NULL) {
			$TAXAMT6 = NULL;
		}else{
			$TAXAMT6 = number_format($TAXAMT6, 2);
		}

		$this->SetXY(147, 229.5);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE6);

		$this->SetXY(167, 227.5);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,4,$TAXAMT6,0,0,'R');

		/* End FIN */

		/* Start DPD */

		if ($TAXAMT7 == NULL) {
			$TAXAMT7 = NULL;
		}else{
			$TAXAMT7 = number_format($TAXAMT7, 2);
		}

		$this->SetXY(147, 229);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE7);

		$this->SetXY(147, 227);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT7,'B',0,'R');

		/* End DPD */

		
		/* Start Total Item Tax */
		if ($data['FIN_data']['MDec'] == '8ZN') {
			$ITAX1 = str_replace(',', '', $TAXAMT1) + str_replace(',', '', $TAXAMT2) + str_replace(',', '', $TAXAMT3) + str_replace(',', '', $TAXAMT4) + str_replace(',', '', $TAXAMT5) + str_replace(',', '', $TAXAMT6) + str_replace(',', '', $TAXAMT7) + str_replace(',', '', $total_excise);
		}elseif ($TAXAMT4 != "" && $TAXAMT4 != 0.00){
			$ITAX1 = str_replace(',', '', $TAXAMT1) + str_replace(',', '', $TAXAMT2) + str_replace(',', '', $TAXAMT3) + str_replace(',', '', $TAXAMT4) + str_replace(',', '', $TAXAMT5) + str_replace(',', '', $TAXAMT6) + str_replace(',', '', $TAXAMT7);
		}else{
			$ITAX1 = str_replace(',', '', $TAXAMT1) + str_replace(',', '', $TAXAMT2) + str_replace(',', '', $TAXAMT3) + str_replace(',', '', $TAXAMT4) + str_replace(',', '', $TAXAMT5) + str_replace(',', '', $TAXAMT6) + str_replace(',', '', $TAXAMT7) + str_replace(',', '', $total_excise);
		}
		//print_r("test"); die();
		$this->SetXY(147, 231);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,'Total Item Tax','B',0,'L');

		$this->SetXY(147, 231);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,number_format($ITAX1, 2),0,0,'R');

		/* End Total Item Tax */

		/* Start IPF */
		if (($data['FIN_data']['MDec'] != '8ZN' && $data['FIN_data']['MDec'] != '8PP') && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			//06062024: SPagara: Update
			$TAXCODE8 = "IPC";
			$TAXAMT8 = $this->IPF_C;
		}else{
			if ($TAXAMT8 == NULL) {
				$TAXAMT8 = NULL;
			}else{
				$TAXAMT8 = number_format($TAXAMT8, 2);
			}
		}

		$this->SetXY(147, 237);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE8);
		// if ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4'){
		// 	$this->Write(0, '');
		// }
		// else{
		// $this->Write(0, $TAXCODE8);
		// }

		$this->SetXY(147, 235);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT8,0,0,'R');
		// if ($data['FIN_data']['MDec'] == 'IE' && $data['FIN_data']['Mdec2'] == '4') {
		// 	$this->Cell(60,4,'',0,0,'R');
		// }
		// else{
		// $this->Cell(60,4,$TAXAMT8,0,0,'R');
		// }
		
		/* End IPF */

		/* Start SGD */

		if ($TAXAMT9 == NULL) {
			$TAXAMT9 = NULL;
		}else{
			$TAXAMT9 = number_format($TAXAMT9, 2);
		}

		$this->SetXY(147, 241);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE9);

		$this->SetXY(147, 239);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT9,0,0,'R');

		/* End SGD */

		/* Start D&F */
		//06062024:SPagara: Removed
		/*if (($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8PP') && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$TAXCODE10 = "D&F";
			$TAXAMT10 = "40.00";
		}else{
			if ($TAXAMT10 == NULL) {
				$TAXAMT10 = NULL;
			}else{
				$TAXAMT10 = number_format($TAXAMT10, 2);
			}
		}

		$this->SetXY(147, 245);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE10);

		$this->SetXY(147, 243);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT10,0,0,'R');*/

		/* End D&F */

		/* Start FF */

		if ($TAXAMT11 == NULL) {
			$TAXAMT11 = NULL;
		}else{
			$TAXAMT11 = number_format($TAXAMT11, 2);
		}

		$this->SetXY(147, 249);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE11);

		$this->SetXY(147, 247);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT11,0,0,'R');

		/* End FF */

		/* Start PSI */

		if ($TAXAMT12 == NULL) {
			$TAXAMT12 = NULL;
		}else{
			$TAXAMT12 = number_format($TAXAMT12, 2);
		}

		$this->SetXY(147, 253);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE12);

		$this->SetXY(147, 251);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT12,0,0,'R');

		/* End PSI */

		/* Start TSF */

		if ($TAXAMT13 == NULL) {
			$TAXAMT13 = NULL;
		}else{
			$TAXAMT13 = number_format($TAXAMT13, 2);
		}
		
		if (($data['FIN_data']['MDec'] == '8ZN' || $data['FIN_data']['MDec'] == '8PP') && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			$TAXCODE13 = "TC";
			//06062024: SPagara: update 
			$TAXAMT13 = "1000.00";
			//$TAXAMT13 = "710.00";
		}else{
			if ($TAXAMT13 == NULL) {
				$TAXAMT13 = NULL;
			}else{
				$TAXAMT13 = number_format($TAXAMT13, 2);
			}
		}

		$this->SetXY(147, 249);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE13);

		$this->SetXY(147, 247);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT13,0,0,'R');

		/* End TSF */

		/* Start SGL */

		if ($TAXAMT14 == NULL) {
			$TAXAMT14 = NULL;
		}else{
			$TAXAMT14 = number_format($TAXAMT14, 2);
		}

		$this->SetXY(147, 251);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE14);

		$this->SetXY(167, 249);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,4,$TAXAMT14,0,0,'R');

		/* End SGL */

		/* Start CSF */

		if ($TAXAMT15 == NULL) {
			$TAXAMT15 = NULL;
		}else{
			$TAXAMT15 = number_format($TAXAMT15, 2);
		}

		$this->SetXY(147, 255);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE15);

		$this->SetXY(147, 253);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT15,0,0,'R');

		/* End CSF */

		/* Start IPC */
		if ($TAXAMT18 == NULL) {
			$TAXAMT18 = NULL;
		}else{
			$TAXAMT18 = number_format($TAXAMT18, 2);
		}
		//05162024:SPagara: For Transshipments
		if (($data['FIN_data']['MDec'] == '8ZN') || ($data['FIN_data']['MDec'] == '8PP') || ($data['FIN_data']['MDec'] == '8PE') || ($data['FIN_data']['MDec'] == '8ZE')){
			$TAXCODE18 = "IPC";
			$TAXAMT18 = 250;
		}
		$this->SetXY(147, 256);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE18);

		$this->SetXY(147, 254);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT18,0,0,'R');

		/* End IPC */

		/* Start CDS */
		//if ($data['FIN_data']['MDec'] != 'IES' && $data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
		if ($data['FIN_data']['MDec'] != 'IES' && $data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
		// if (($data['FIN_data']['MDec'] != 'IES' && ($data['FIN_data']['MDec'] != 'IE' && $data['FIN_data']['Mdec2'] != '4')) && ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S')) {
			if ($data['FIN_data']['MDec'] != 'IES' && $data['FIN_data']['MDec'] != 'IE'){
		//SPagara: 08072023: Update on charges
		//05132024: SPagara: update 
			$TAXCODE16 = "CDS";
			//$TAXAMT16 = "250.00";
			//$TAXAMT16 = "280.00";
			$TAXAMT16 = "100.00";
			}elseif ($data['FIN_data']['MDec'] = 'IED') {
				$TAXCODE16 = "CDS";
				//$TAXAMT16 = "250.00";
				$TAXAMT16 = "100.00";
			}else
			{
				$TAXCODE16 = "CDS";
				$TAXAMT16 = "100.00";
			}
		}elseif ($data['FIN_data']['MDec'] == 'IES'){
			$TAXCODE16 = "CDS";
			$TAXAMT16 = "100.00";
		}else{
			if ($TAXAMT16 == NULL) {
				$TAXCODE16 = "CDS";
				$TAXAMT16 = "100.00";
			}else{
				//$TAXAMT16 = number_format($TAXAMT16, 2);
				$TAXAMT16 = number_format($TAXAMT16/$data['max_rows'], 2);
			}
		}

		$this->SetXY(147, 260);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE16);

		$this->SetXY(147, 258);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT16,0,0,'R');

		/* End CDS */

		/* Start IRS */
		if ($data['FIN_data']['Stat'] == 'C' || $data['FIN_data']['Stat'] == 'S') {
			$TAXCODE17 = "IRS";
			$TAXAMT17 = "30.00";
		}else{
			if ($TAXAMT17 == NULL) {
				$TAXCODE17 = "IRS";
				$TAXAMT17 = "30.00";
			}else{
				$TAXAMT17 = number_format($TAXAMT17, 2);
			}
		}

		$this->SetXY(147, 263);
		$this->SetFont('Arial','B',9);
		$this->Write(0, $TAXCODE17);

		$this->SetXY(147, 261);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,4,$TAXAMT17,'B',0,'R');

		/* End IRS */

		/* Start Total GLOBAL Tax 
		str_replace(',', '', $TAXAMT9) + */
		$GTAX1 = str_replace(',', '', $TAXAMT8) + str_replace(',', '', $TAXAMT9) + str_replace(',', '', $TAXAMT10) + str_replace(',', '', $TAXAMT11) + str_replace(',', '', $TAXAMT12) + str_replace(',', '', $TAXAMT13) + str_replace(',', '', $TAXAMT14) + str_replace(',', '', $TAXAMT15) + str_replace(',', '', $TAXAMT16) + str_replace(',', '', $TAXAMT17) + str_replace(',', '', $TAXAMT18) + str_replace(',', '', $TAXAMT19);

		$this->SetXY(147, 265);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(60, 4, 'Total Global Tax', 'B', 0, 'L');

		$this->SetXY(147, 265);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(60, 4, number_format($GTAX1, 2), 0, 0, 'R');

		/* End Total GLOBAL Tax */

		/* Start Total Tax */

		$TTAX1 = $ITAX1 + $GTAX1;

		$this->SetXY(147, 270);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(60, 4, number_format($TTAX1, 2), 0, 0, 'R');

		/* End Total Tax */

		/* End Taxes */

		/* End CONTINUATION FROM BOX # 31 */



	}

	protected $T128;                                         // Tableau des codes 128
	protected $ABCset = "";                                  // jeu des caractères éligibles au C128
	protected $Aset = "";                                    // Set A du jeu des caractères éligibles
	protected $Bset = "";                                    // Set B du jeu des caractères éligibles
	protected $Cset = "";                                    // Set C du jeu des caractères éligibles
	protected $SetFrom;                                      // Convertisseur source des jeux vers le tableau
	protected $SetTo;                                        // Convertisseur destination des jeux vers le tableau
	protected $JStart = array("A"=>103, "B"=>104, "C"=>105); // Caractères de sélection de jeu au début du C128
	protected $JSwap = array("A"=>101, "B"=>100, "C"=>99);   // Caractères de changement de jeu

	function __construct($orientation='P', $unit='mm', $format='A4') {

	    parent::__construct($orientation,$unit,$format);

	    $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
	    $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
	    $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
	    $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
	    $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
	    $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
	    $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
	    $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
	    $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
	    $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
	    $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
	    $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
	    $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
	    $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
	    $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
	    $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
	    $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
	    $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
	    $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
	    $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
	    $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
	    $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
	    $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
	    $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
	    $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
	    $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
	    $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
	    $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
	    $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
	    $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
	    $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
	    $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
	    $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
	    $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
	    $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
	    $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
	    $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
	    $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
	    $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
	    $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
	    $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
	    $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
	    $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
	    $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
	    $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
	    $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
	    $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
	    $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
	    $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
	    $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
	    $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
	    $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
	    $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
	    $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
	    $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
	    $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
	    $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
	    $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
	    $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
	    $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
	    $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
	    $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
	    $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
	    $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
	    $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
	    $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
	    $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
	    $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
	    $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
	    $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
	    $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
	    $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
	    $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
	    $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
	    $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
	    $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
	    $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
	    $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
	    $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
	    $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
	    $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
	    $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
	    $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
	    $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
	    $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
	    $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
	    $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
	    $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
	    $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
	    $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
	    $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
	    $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
	    $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
	    $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
	    $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
	    $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
	    $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
	    $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
	    $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
	    $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
	    $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]                
	    $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
	    $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
	    $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
	    $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
	    $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
	    $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
	    $this->T128[] = array(2, 1);                       //107 : [END BAR]

	    for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
	        $this->ABCset .= chr($i);
	    }
	    $this->Aset = $this->ABCset;
	    $this->Bset = $this->ABCset;
	    
	    for ($i = 0; $i <= 31; $i++) {
	        $this->ABCset .= chr($i);
	        $this->Aset .= chr($i);
	    }
	    for ($i = 96; $i <= 127; $i++) {
	        $this->ABCset .= chr($i);
	        $this->Bset .= chr($i);
	    }
	    for ($i = 200; $i <= 210; $i++) {                                           // controle 128
	        $this->ABCset .= chr($i);
	        $this->Aset .= chr($i);
	        $this->Bset .= chr($i);
	    }
	    $this->Cset="0123456789".chr(206);

	    for ($i=0; $i<96; $i++) {                                                   // convertisseurs des jeux A & B
	        @$this->SetFrom["A"] .= chr($i);
	        @$this->SetFrom["B"] .= chr($i + 32);
	        @$this->SetTo["A"] .= chr(($i < 32) ? $i+64 : $i-32);
	        @$this->SetTo["B"] .= chr($i);
	    }
	    for ($i=96; $i<107; $i++) {                                                 // contrôle des jeux A & B
	        @$this->SetFrom["A"] .= chr($i + 104);
	        @$this->SetFrom["B"] .= chr($i + 104);
	        @$this->SetTo["A"] .= chr($i);
	        @$this->SetTo["B"] .= chr($i);
	    }
	}

	//________________ Fonction encodage et dessin du code 128 _____________________
	function Code128($x, $y, $code, $w, $h) {
	    $Aguid = "";                                                                      // Création des guides de choix ABC
	    $Bguid = "";
	    $Cguid = "";
	    for ($i=0; $i < strlen($code); $i++) {
	        $needle = substr($code,$i,1);
	        $Aguid .= ((strpos($this->Aset,$needle)===false) ? "N" : "O"); 
	        $Bguid .= ((strpos($this->Bset,$needle)===false) ? "N" : "O"); 
	        $Cguid .= ((strpos($this->Cset,$needle)===false) ? "N" : "O");
	    }

	    $SminiC = "OOOO";
	    $IminiC = 4;

	    $crypt = "";
	    while ($code > "") {
	                                                                                    // BOUCLE PRINCIPALE DE CODAGE
	        $i = strpos($Cguid,$SminiC);                                                // forçage du jeu C, si possible
	        if ($i!==false) {
	            $Aguid [$i] = "N";
	            $Bguid [$i] = "N";
	        }

	        if (substr($Cguid,0,$IminiC) == $SminiC) {                                  // jeu C
	            $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
	            $made = strpos($Cguid,"N");                                             // étendu du set C
	            if ($made === false) {
	                $made = strlen($Cguid);
	            }
	            if (fmod($made,2)==1) {
	                $made--;                                                            // seulement un nombre pair
	            }
	            for ($i=0; $i < $made; $i += 2) {
	                $crypt .= chr(strval(substr($code,$i,2)));                          // conversion 2 par 2
	            }
	            $jeu = "C";
	        } else {
	            $madeA = strpos($Aguid,"N");                                            // étendu du set A
	            if ($madeA === false) {
	                $madeA = strlen($Aguid);
	            }
	            $madeB = strpos($Bguid,"N");                                            // étendu du set B
	            if ($madeB === false) {
	                $madeB = strlen($Bguid);
	            }
	            $made = (($madeA < $madeB) ? $madeB : $madeA );                         // étendu traitée
	            $jeu = (($madeA < $madeB) ? "B" : "A" );                                // Jeu en cours

	            $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

	            $crypt .= strtr(substr($code, 0,$made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

	        }
	        $code = substr($code,$made);                                           // raccourcir légende et guides de la zone traitée
	        $Aguid = substr($Aguid,$made);
	        $Bguid = substr($Bguid,$made);
	        $Cguid = substr($Cguid,$made);
	    }                                                                          // FIN BOUCLE PRINCIPALE

	    $check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
	    for ($i=0; $i<strlen($crypt); $i++) {
	        $check += (ord($crypt[$i]) * $i);
	    }
	    $check %= 103;

	    $crypt .= chr($check) . chr(106) . chr(107);                               // Chaine cryptée complète

	    $i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
	    $modul = $w/$i;

	    for ($i=0; $i<strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
	        $c = $this->T128[ord($crypt[$i])];
	        for ($j=0; $j<count($c); $j++) {
	            $this->Rect($x,$y,$c[$j]*$modul,$h,"F");
	            $x += ($c[$j++]+$c[$j])*$modul;
	        }
	    }
	}
}

$pdf = new PDF('P','mm','A4');
// Column headings
// Data loading
// $pdf->SetFont('Arial','',10);
// $pdf->SetTitle($applno.'.pdf');
$tin = $_GET['tin'];

$sad = array();
$FIN_multi = array();
$RespHEAD = array();
$RespGT = array();
$RespIT = array();
$Containers = array();

foreach ($applnos as $key => $applnoss) {
	$sad[] = $pdf->LoadData($applnoss, $tin);
	$FIN_multi[] = $pdf->LoadData_FIN_multiple($applnoss);
	
	//$RespHEAD[] = $pdf->LoadData_RespHEAD($applnoss);
//	$RespIT[] = $pdf->LoadData_RespIT($applnoss);
	//$RespGT[] = $pdf->LoadData_RespGT($applnoss);
	
	
	if($pdf->LoadData_RespGT($applnoss)){
			$RespGT[] = $pdf->LoadData_RespGT($applnoss);
	}else{
			$RespGT[] = array();
	}
	
	if($pdf->LoadData_RespIT($applnoss)){
			$RespIT[] = $pdf->LoadData_RespIT($applnoss);
	}else{
			$RespIT[] = array();
	}
	
	if($pdf->LoadData_RespHEAD($applnoss)){
			$RespHEAD[] = $pdf->LoadData_RespHEAD($applnoss);
	}else{
			$RespHEAD[] = array();
	}
	
	if($pdf->LoadData_backnote($applnoss)){
			$Containers[] = $pdf->LoadData_backnote($applnoss);
	}else{
			$Containers[] = array();
	}

	//$Containers[] = $pdf->LoadData_backnote($applnoss);
}

// echo "d2";
// exit;
$RespIT_count = 0;
//echo'<pre>';
//print_r($RespIT[0]);
//echo'</pre>';
foreach ($RespIT[0] as $key => $RespITs) {
	if ($RespITs['TAXCODE'] == 'CUD') {
		$RespIT_count ++;
	}
}

if (@$sad[0]['FIN_data']['Stat'] != 'C' && @$sad[0]['FIN_data']['Stat'] != 'S') {
	//print_r($RespIT_count.count($FIN_multi[0]));
	//die();
	if ($RespIT_count == 0) {
?>
		<html>
			<head>
				<style type="text/css">
					body
					{
						background-color: rgb(95, 95, 95);
					}
					#display-error
					{
					display: inline-block;
				    position: fixed;
				    top: 0;
				    bottom: 0;
				    left: 0;
				    right: 0;
				    width: 500px;
				    height: 40px;
				    margin: auto;
					border: 1px solid #D8D8D8;
					padding: 5px;
					border-radius: 5px;
					font-family: Arial;
					font-size: 11px;
					text-transform: uppercase;
					background-color: rgb(176,224,230);
					color: rgb(63, 63, 191);
					text-align: center;
					}

					img
					{
					float: left;
					}


				</style>
			</head>
			<body>
				<div id="display-error">
					<img src='spinner.gif' alt='Error' width = '40' height = '40'/>
					E2M Response is still being processed at the moment. <br/>
					Please try again later. <br/>
					If the problem persists please contact INS Customer's Support.
				</div>
			</body>
			<style>
				
			</style>
		</html>
<?php
		die();
	}else{
		if ($RespIT_count != count($FIN_multi[0]) && $applno != "AMZ28052402") {
			?>
			<html>
				<head>
					<style type="text/css">
						body
						{
							background-color: rgb(95, 95, 95);
						}
						#display-error
						{
						display: inline-block;
					    position: fixed;
					    top: 0;
					    bottom: 0;
					    left: 0;
					    right: 0;
					    width: 500px;
					    height: 40px;
					    margin: auto;
						border: 1px solid #D8D8D8;
						padding: 5px;
						border-radius: 5px;
						font-family: Arial;
						font-size: 11px;
						text-transform: uppercase;
						background-color: rgb(176,224,230);
						color: rgb(63, 63, 191);
						text-align: center;
						}

						img
						{
						float: left;
						}
					</style>
				</head>
				<body>
					<div id='display-error'>
						<img src='spinner.gif' alt='Error' width = '40' height = '40'/>
						There is still no E2M Response at the moment. <br/>
						If the problem persists please contact INS Customer's Support.
					</div>
				</body>
				<style>
					
				</style>
			</html>
<?php
			print_r("");
			die();
		}
	}
}

foreach ($sad as $key => $sad_data) {
	$pdf->SetAutoPageBreak(0, 0);
	$pdf->AddPage();
	$pdf->Head($data);
	//print_r($RespGT[$key);
	if($RespGT[$key]==NULL){
		$RespGT1[$key] = array();
	}else{
		$RespGT1[$key] = $RespGT[$key];
	}
	if($RespIT[$key]==NULL){
		$RespIT1[$key] = array();
	}else{
		$RespIT1[$key] = $RespIT[$key];
	}
	if($RespHEAD[$key]==NULL){
		$RespHEAD1[$key] = array();
	}else{
		$RespHEAD1[$key] = $RespHEAD[$key];
	}
	
	
	$pdf->front_page($sad_data, $tin, $FIN_multi[$key], $RespGT1[$key], $RespIT1[$key], $RespHEAD1[$key]);
	if ($sad_data['max_rows'] > 1) {
		$pdf->AddPage();
		$pdf->rider_page($sad_data, $tin, $FIN_multi[$key], $RespGT[$key], $RespIT[$key], $RespHEAD[$key]);
		$pdf->rider_data($sad_data, $tin, $FIN_multi[$key], $RespGT[$key], $RespIT[$key], $RespHEAD[$key]);
	}
	$pdf->AddPage();
	$pdf->back_page($sad_data, $tin, $FIN_multi[$key], $RespGT[$key], $RespIT[$key], $RespHEAD[$key], $Containers[$key]);
}


$pdf->Output();


?>
