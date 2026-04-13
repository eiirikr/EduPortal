<?php
require('fpdf.php');

class PDF extends FPDF{
	public function Head(){
		$this->SetFont('Arial','',8);
		$this->Cell(80);

		$this->SetXY(22, 15);
		$this->SetFont('Arial','B',8);
		$this->Write(0,'BOC SINGLE ADMINISTRATIVE DOCUMENT','');

		$this->Ln(40);
	}

	public function LoadData($applno){
		
		$serverNamePEZA506 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA = array( "Database"=>'INSIPPEZA', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSIPPEZA = sqlsrv_connect( $serverNamePEZA506, $connectionINSIPPEZA);

		$serverNamePEZA5061 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA1 = array( "Database"=>'PEZA', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSPEZA = sqlsrv_connect( $serverNamePEZA5061, $connectionINSIPPEZA1);

		$serverNamePEZA5062 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA2 = array( "Database"=>'PEZAexp', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_PEZAexp = sqlsrv_connect( $serverNamePEZA5062, $connectionINSIPPEZA2);


		$serverNameCOMINS = 'WEBCWSDB'; //serverName\instanceName, portNumber (default is 1433)
		$connectionCOMINS = array( "Database"=>'INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_COMINS = sqlsrv_connect( $serverNameCOMINS, $connectionCOMINS);

		$data = array();
		

		if(!$conn_INSIPPEZA) {
			echo "a Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{
			$MASTER_DATA = "SELECT a.ProvofOrig, e.prov_dsc, b.COcode, a.APPLNO AS APPLNO, a.DecTIN AS dectin2, a.*, b.ECQtyDeducted AS ECQtyDeducted1, 
					b.*,c.*, d.* , (select cty_dsc from GBCTYTAB e where cty_cod = a.cexp) as cexpdesc
					FROM TBLEXPAPL_MASTER a
					LEFT JOIN TBLEXPAPL_DETAIL b ON a.ApplNo = b.ApplNo 
					LEFT JOIN GBCTYTAB d ON b.cocode = d.cty_cod 
					LEFT JOIN TBLEXPAPL_FIN c ON a.ApplNo = c.ApplNo 
					LEFT JOIN PEZA.dbo.GBPRVORG e ON a.ProvofOrig = e.prov_cod
					WHERE a.APPLNO = '" .$applno. "'";
			
			$stmt_data = sqlsrv_query($conn_INSIPPEZA, $MASTER_DATA);
			if($stmt_data == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
				{	
					$data['MASTER'] = $rows;
				}
			}
	

			$row_count = "SELECT * FROM TBLEXPAPL_DETAIL WHERE APPLNO =  '" .$applno. "' ORDER BY ITEMNO";
				
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$countrows = sqlsrv_query( $conn_INSIPPEZA, $row_count , $params, $options );
			$row_number = sqlsrv_num_rows( $countrows );
			$data['row_count'] = (round(sqlsrv_num_rows( $countrows ) / 3 + 1));
			$data['max_rows'] = (round(sqlsrv_num_rows($countrows)));

		}


		if(!$conn_INSPEZA) {
			echo "b Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{
			$rstGBSHDTAB = "SELECT * FROM GBSHDTAB_D where shd_cod = '".$data['MASTER']['LGoods']."'";

			$stmt_data = sqlsrv_query($conn_COMINS, $rstGBSHDTAB);
			if($stmt_data == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
				{	
					$data['rstGBSHDTAB'] = $rows;
				}
			}


			$DMOffClear = "SELECT OffClrName FROM DmOffClr WHERE Offclrcod = '" . $data['MASTER']['OffClear'] . "'";

			$stmt_data1 = sqlsrv_query($conn_INSPEZA, $DMOffClear);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['DMOffClear'] = $rows;
				}
			}

			$tblZone = "SELECT ZoneDesc FROM tblZone WHERE ZoneCode = '" . $data['MASTER']['RegOfc'] . "'";

			$stmt_data2 = sqlsrv_query($conn_INSPEZA, $tblZone);
			if($stmt_data2 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data2, SQLSRV_FETCH_ASSOC))
				{	
					$data['tblZone'] = $rows;
				}
			}

			//05212024: SPagara: Add Exporter Name for item 50
			//$importers = "SELECT DUNS, COmpanyName, Address1, Address2 FROM tblImporters WHERE TIN = '" . $data['MASTER']['ConTIN'] . "'";
			$importers = "SELECT DUNS, COmpanyName, Address1, Address2 FROM tblImporters WHERE PEZAimpCode = '" . $data['MASTER']['ExpCode'] . "'";
			$stmt_data2 = sqlsrv_query($conn_INSPEZA, $importers);
			if($stmt_data2 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data2, SQLSRV_FETCH_ASSOC))
				{	
					$data['importers'] = $rows;
				}
			}
		}

		if(!$conn_PEZAexp) { 
			echo "Connection could not be established.<br />";
			die(print_r(sqlsrv_errors(), true));
		} else {

			$decTIN = $data['MASTER']['DecTIN'];

			$query1 = "SELECT DISTINCT PTOPS_ROWID 
					   FROM tblForwarders 
					   WHERE For_tin = '$decTIN'";

			$stmt1 = sqlsrv_query($conn_PEZAexp, $query1);

			if($stmt1 === false) {
				die(print_r(sqlsrv_errors(), true));
			}

			if(sqlsrv_has_rows($stmt1)) {
				while($rows = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
					$data['forwarders'] = $rows;
				}

			} else {
				$query2 = "SELECT DISTINCT PTOPS_ROWID 
						   FROM tblForwarders 
						   WHERE TIN12 = '$decTIN'";

				$stmt2 = sqlsrv_query($conn_PEZAexp, $query2);

				if($stmt2 === false) {
					die(print_r(sqlsrv_errors(), true));
				}

				while($rows = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
					$data['forwarders'] = $rows;
				}
			}
		}

		$serverNamePEZAexpPTOPS = '192.168.5.70, 1477';
		$connectionPEZAexpPTOPS = array( "Database"=>'PEZAexpPTOPS', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_PEZAexpPTOPS = sqlsrv_connect($serverNamePEZAexpPTOPS, $connectionPEZAexpPTOPS);
		if(!$conn_PEZAexpPTOPS) {
			echo "b Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{
			$brokers = "SELECT TIN12, For_name, For_Adr1, For_Adr2, For_Adr3 FROM tblForwarders WHERE PTOPS_ROWID = '" . $data['forwarders']['PTOPS_ROWID'] . "'";

			$stmt_data2 = sqlsrv_query($conn_PEZAexpPTOPS, $brokers);
			if($stmt_data2 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data2, SQLSRV_FETCH_ASSOC))
				{	
					$data['brokers'] = $rows;
				}
			}

		}

		if(!$conn_COMINS) {
			echo "c Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			$DMOffClear1 = "SELECT loc_dsc FROM GBLOCTAB WHERE loc_cod like '" .$data['MASTER']['PortofLoad']. "'";

			$stmt_data1 = sqlsrv_query($conn_COMINS, $DMOffClear1);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['DMOffClear1'] = $rows;
				}
			}

			$DMOffClear2 = "SELECT offClrName FROM DmOffClr WHERE Offclrcod like '".$data['MASTER']['PortofDept']."'";

			$stmt_data1 = sqlsrv_query($conn_COMINS, $DMOffClear2);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['DMOffClear2'] = $rows;
				}
			}
			
			$DMOffClear3 = "SELECT offClrName FROM DmOffClr WHERE Offclrcod like '".$data['MASTER']['OffClear']."'";

			$stmt_data13 = sqlsrv_query($conn_COMINS, $DMOffClear3);
			if($stmt_data13 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data13, SQLSRV_FETCH_ASSOC))
				{	
					$data['DMOffClear3'] = $rows;
				}
			}


			$country = "SELECT cty_dsc FROM GBCTYTAB WHERE cty_cod like '%" . $data['MASTER']['Cdest'] . "%'";

			$stmt_data1 = sqlsrv_query($conn_COMINS, $country);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['country'] = $rows;
				}
			}

			$PAY1 = "SELECT tod_dsc from GBTODTAB WHERE tod_cod = '" . $data['MASTER']['TDelivery'] . "'";

			$stmt_data1 = sqlsrv_query($conn_COMINS, $PAY1);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['PAY1'] = $rows;
				}
			}

			if ($data['MASTER']['BankCode'] == 'PCHC') {
				$data['MASTER']['BankCode'] = '998';
			}

			$bnkName = "SELECT bnkName FROM DmBnkName WHERE bnkCode = '". $data['MASTER']['BankCode'] ."'";

			$stmt_data1 = sqlsrv_query($conn_COMINS, $bnkName);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['bnkName'] = $rows;

				}
			}


			$PAY2 = "SELECT top_dsc from GBTOPTAB WHERE top_cod = '" . $data['MASTER']['Tpayment'] . "'";
//var_dump($PAY2);
//die;
			$stmt_data1 = sqlsrv_query($conn_COMINS, $PAY2);
			if($stmt_data1 == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data1, SQLSRV_FETCH_ASSOC))
				{	
					$data['PAY2'] = $rows;
				}
			}

		}
		// echo "<pre>";
		// var_dump($data);
		// echo "</pre>";
		
		return $data;
		//CONTIN
	}

	public function getEdNumber($applno)
	{
		$url = "http://192.168.5.26:90/api/get-ed-number";

		$postData = json_encode(array(
			"applno" => $applno
		));

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($postData)
		));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			curl_close($ch);
			return null;
		}

		curl_close($ch);

		$result = json_decode($response, true);

		if (isset($result['data']['edNumber']) && $result['data']['edNumber'] != '') {
			return $result['data']['edNumber'];
		}

		return null;
	}

	public function ItemCount($applno){
		$data = [];

		// --- First Database Connection ---
		$serverNamePEZA506 = 'PEZA506';
		$connectionINSIPPEZA = [ "Database"=>'INSIPPEZA', "UID"=>'sa', "PWD"=>'df0rc3' ];
		$conn_INSIPPEZA = sqlsrv_connect($serverNamePEZA506, $connectionINSIPPEZA);
		if(!$conn_INSIPPEZA){
			die(print_r(sqlsrv_errors(), true));
		}

		$detailQuery = "SELECT * FROM TBLEXPAPL_DETAIL WHERE ApplNo = ?";
		$stmt_data1 = sqlsrv_query($conn_INSIPPEZA, $detailQuery, [$applno]);
		if(!$stmt_data1){
			die(print_r(sqlsrv_errors(), true));
		}

		$tblDetails = []; // Collect all rows grouped by COCode
		while($row = sqlsrv_fetch_array($stmt_data1, SQLSRV_FETCH_ASSOC)){
			$tblDetails[$row['COCode']][] = $row;
		}

		// --- Second Database Connection ---
		$serverNameCOMINS = 'WEBCWSDB';
		$connectionCOMINS = [ "Database"=>'INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3' ];
		$conn_COMINS = sqlsrv_connect($serverNameCOMINS, $connectionCOMINS);
		if(!$conn_COMINS){
			die(print_r(sqlsrv_errors(), true));
		}

		$cityQuery = "SELECT cityCode, cityDisc FROM DmCityOrigin";
		$stmt2 = sqlsrv_query($conn_COMINS, $cityQuery);
		if(!$stmt2){
			die(print_r(sqlsrv_errors(), true));
		}

		$cityMap = [];
		while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
			$cityMap[$row['cityCode']] = $row['cityDisc'];
		}

		// --- Merge Data ---
		foreach($tblDetails as $coCode => $items){
			foreach($items as $item){
				
				$cleanCode = trim(strtoupper($item['CoCode']));
				if(isset($cityMap[$cleanCode])) {
					$item['cityDisc'] = $cityMap[$cleanCode];
				} else {
					$item['cityDisc'] = null;
				}
				$data[] = $item;
			}
		}
		

		return $data;

	}




	public function LOADDATA_ASSET($applno){
		
		$serverNamePEZA506 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA = array( "Database"=>'INSIPPEZA', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSIPPEZA = sqlsrv_connect( $serverNamePEZA506, $connectionINSIPPEZA);

		$data = array();
		if(!$conn_INSIPPEZA) {
			echo "d Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			$ASSET = "SELECT GoodsDesc1 AS GoodsDesc, GBPKGTAB.pkg_dsc, * 
					  FROM TBLEXPAPL_DETAIL 
					  LEFT JOIN GBPKGTAB ON TBLEXPAPL_DETAIL.PackCode = GBPKGTAB.pkg_cod 
					  WHERE ApplNo =  '" . $applno . "' ORDER BY ItemNo";

			$stmt_data = sqlsrv_query($conn_INSIPPEZA, $ASSET);
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
		}

		// echo "<pre>";
		// var_dump($data);
		// echo "</pre>";

		return $data;

	}

	public function LOADDATA_CONT($applno){
		
		$serverNamePEZA506 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA = array( "Database"=>'INSIPPEZA', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSIPPEZA = sqlsrv_connect( $serverNamePEZA506, $connectionINSIPPEZA);

		$data = array();
		if(!$conn_INSIPPEZA) {
			echo "e Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			$CONT = "SELECT container 
					  FROM tblExpApl_ContPEZA 
					  WHERE ApplNo = '" . $applno . "' ORDER BY Container, Seal";

			$stmt_data = sqlsrv_query($conn_INSIPPEZA, $CONT);
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
		}

		// echo "<pre>";
		// var_dump($data);
		// echo "</pre>";

		return $data;

	}

	public function LOADDATA_COMP($applno){
		
		$serverNameCOMINS = 'WEBCWSDB'; //serverName\instanceName, portNumber (default is 1433)
		$connectionCOMINS = array( "Database"=>'INSCUSTSTDB', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_COMINS = sqlsrv_connect( $serverNameCOMINS, $connectionCOMINS);

		$data = array();
		if(!$conn_COMINS) {
			echo "f Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			$COMP = "SELECT *
					 FROM TBLRESP_GT
					 WHERE applno =  '" . $applno . "'";

			$stmt_data = sqlsrv_query($conn_COMINS, $COMP);
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
		}

		return $data;

	}

	public function LOADDATA_GBTARTAB($hscode){
		
		$serverNamePEZA506 = 'PEZA506'; //serverName\instanceName, portNumber (default is 1433)
		$connectionINSIPPEZA = array( "Database"=>'INSIPPEZA', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_INSIPPEZA = sqlsrv_connect( $serverNamePEZA506, $connectionINSIPPEZA);

		$data = array();
		if(!$conn_INSIPPEZA) {
			echo "e Connection could not be established.<br />";
			die( print_r( sqlsrv_errors(), true));
		}else{

			$strHSCode1 = substr($hscode, 0, 6);
			$strHSCode2 = substr($hscode, 6, 3);

			$hsrate = "SELECT *
					 FROM GBTARTAB
					 WHERE hs6_cod =  '" . $strHSCode1 . "' AND tar_pr1 =  '" . $strHSCode2 . "'";

			$stmt_data = sqlsrv_query($conn_INSIPPEZA, $hsrate);
			if($stmt_data == false)
			{
				 echo "Error in query preparation/execution.\n";
				 die( print_r( sqlsrv_errors(), true));
			}else{
				while( $rows = sqlsrv_fetch_array( $stmt_data, SQLSRV_FETCH_ASSOC))
				{	
					return $rows;
				}
			}
		}

		

	}

	public function LOADDATA_LOANO($itemCode, $ptopsRowID){
		$serverNamePEZAexpPTOPS = '192.168.5.70, 1477';
		$connectionPEZAexpPTOPS = array( "Database"=>'PEZAexpPTOPS', "UID"=>'sa', "PWD"=>'df0rc3');
		$conn_PEZAexpPTOPS = sqlsrv_connect($serverNamePEZAexpPTOPS, $connectionPEZAexpPTOPS);

		if(!$conn_PEZAexpPTOPS) {
			throw new Exception("Connection could not be established: " . print_r(sqlsrv_errors(), true));
		}

		$strCommodityCode = $itemCode;
		$strPTOPSRowID = $ptopsRowID;
		
		$loano = "SELECT LOANO FROM tblExItem WHERE CommodityCode = ? AND PTOPS_ROWID = ?";
		$params = array($strCommodityCode, $strPTOPSRowID);

		$stmt_data = sqlsrv_query($conn_PEZAexpPTOPS, $loano, $params);

		if($stmt_data === false) {
			throw new Exception("Error in query execution: " . print_r(sqlsrv_errors(), true));
		}

		$row = sqlsrv_fetch_array($stmt_data, SQLSRV_FETCH_ASSOC);
		return $row['LOANO'];  // return string or empty if not found
	}



	// Front page
	public function front_page($MASTER, $ASSET, $CONT){
		$edNumber = $this->getEdNumber($MASTER['MASTER']['APPLNO']);

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
		//05312024: Spagara: checking for Customs Word
		$this->SetXY(8, 40);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'C');
		}else{
			$this->Write(0, '');
		}
		
		$this->SetXY(8, 50);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'U');
		}else{
			$this->Write(0, '');
		}

		$this->SetXY(8, 60);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'S');
		}else{
			$this->Write(0, '');
		}

		$this->SetXY(8, 70);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'T');
		}else{
			$this->Write(0, '');
		}

		$this->SetXY(8, 80);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'O');
		}else{
			$this->Write(0, '');
		}

		$this->SetXY(7, 90);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'M');
		}else{
			$this->Write(0, '');
		}
		
		$this->SetXY(8, 100);
		$this->SetFont('Times','B',15);
		if ($MASTER['MASTER']['cltcode'] != "004027128000" && $MASTER['MASTER']['cltcode'] != "214695079002" && $MASTER['MASTER']['cltcode'] != "HZHH240725"){
			$this->Write(0, 'S');
		}else{
			$this->Write(0, '');
		}

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

		$this->SetXY(63, 25);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'TIN: ');

		$this->SetXY(69, 25);
		$this->SetFont('Arial','',7);
		$this->Write(0, $MASTER['importers']['DUNS']);
		
		$this->SetXY(17, 29);
		$this->SetFont('Arial','',8);
		if ($MASTER['MASTER']['ExpName'] == 'ANALOG DEVICES GEN. TRIAS, INC. - WAREHOUSING DIVISION' || $MASTER['importers']['DUNS'] == '417272052001') {
			$this->MultiCell(80,2.8,$MASTER['MASTER']['ExpName'],0);
		}elseif($MASTER['MASTER']['ExpName'] == 'AMERICAN POWER CONVERSION CORP B.V. PHILIPPINE BRANCH' || $MASTER['importers']['DUNS'] == '217749284000'){
			$this->MultiCell(80,2.8,$MASTER['MASTER']['ExpName'],0);
		//05242024: SPagara: Hard Code for TI as per Doms
		}elseif($MASTER['MASTER']['ExpName'] == 'TI (PHILIPPINES) INC. (CLTI)'){
			$this->MultiCell(80,2.8,'TI (PHILIPPINES) INC.',0);
		}else{
			$MasterExp=$MASTER['MASTER']['ExpName'];
			$strcount= strlen($MasterExp);
			if($strcount <= 45){
				$this->Write(0, $MasterExp);
			}else{
				$this->SetXY(17,26);
				$this->SetFont('Arial','',7.5);
				$this->MultiCell(82, 3,$MasterExp,0,'L',false);
			}
		}

		if ($MASTER['MASTER']['ExpName'] == 'ANALOG DEVICES GEN. TRIAS, INC. - WAREHOUSING DIVISION' || $MASTER['importers']['DUNS'] == '417272052001') {
			$this->SetXY(17, 34);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr1'],0,0,'L');
		}elseif($MASTER['MASTER']['ExpName'] == 'AMERICAN POWER CONVERSION CORP B.V. PHILIPPINE BRANCH' || $MASTER['importers']['DUNS'] == '217749284000'){
			$this->SetXY(17, 34);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr1'],0,0,'L');
		}else{
			$this->SetXY(17, 33);
			$this->SetFont('Arial','',8);
			$this->Write(0, $MASTER['MASTER']['ExpAdr1']);
		}

		if ($MASTER['MASTER']['ExpName'] == 'ANALOG DEVICES GEN. TRIAS, INC. - WAREHOUSING DIVISION' || $MASTER['importers']['DUNS'] == '417272052001') {
			$this->SetXY(17, 37);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr2'],0,0,'L');
		}elseif($MASTER['MASTER']['ExpName'] == 'AMERICAN POWER CONVERSION CORP B.V. PHILIPPINE BRANCH' || $MASTER['importers']['DUNS'] == '217749284000'){
			$this->SetXY(17, 37);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr2'],0,0,'L');
		}else{
			$this->SetXY(17, 37);
			$this->SetFont('Arial','',8);
			$this->Write(0, $MASTER['MASTER']['ExpAdr2']);
		}


		if ($MASTER['MASTER']['ExpName'] == 'ANALOG DEVICES GEN. TRIAS, INC. - WAREHOUSING DIVISION' || $MASTER['importers']['DUNS'] == '417272052001') {
			$this->SetXY(17, 41);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr3'],0,0,'L');
		}elseif($MASTER['MASTER']['ExpName'] == 'AMERICAN POWER CONVERSION CORP B.V. PHILIPPINE BRANCH' || $MASTER['importers']['DUNS'] == '217749284000'){
			$this->SetXY(17, 41);
			$this->SetFont('Arial','',8);
			$this->Cell(80,5,$MASTER['MASTER']['ExpAdr3'],0,0,'L');
		}else{
			$this->SetXY(17, 41);
			$this->SetFont('Arial','',8);
			$this->Write(0, $MASTER['MASTER']['ExpAdr3']);
		}


		/* END BOX 2 */

		/* BOX 8 */
		
		$this->SetXY(17, 45);
		$this->SetFont('Arial','',20);
		$this->Cell(82,20,'','LB',0,'');

		$this->SetXY(19, 47);
		$this->SetFont('Arial','',6);
		$this->Write(0, '8  Importer / Consignee, Address');

		// $this->SetXY(17, 51);
		// $this->SetFont('Arial','',6.7);
		// $this->Write(0, $MASTER['MASTER']['ConName']);

		// $this->SetXY(17, 54);
		// $this->SetFont('Arial','',6.7);
		// $this->Write(0, $MASTER['MASTER']['ConAdr1']);

		// $this->SetXY(17, 57);
		// $this->SetFont('Arial','',6.7);
		// $this->Write(0, $MASTER['MASTER']['ConAdr2']);

		// $this->SetXY(17, 60);
		// $this->SetFont('Arial','',6.7);
		// $this->Write(0, $MASTER['MASTER']['ConAdr3']);

		// $this->SetXY(17, 63);
		// $this->SetFont('Arial','',6.7);
		// $this->Write(0, $MASTER['MASTER']['ConAdr4']);

		// Get ConName and set the font size and line spacing based on its length
		$conName = $MASTER['MASTER']['ConName'];
		$conFontSize = (strlen($conName) > 37) ? "6.5" : "6.7"; // Smaller font if name is too long
		$baseY = 50;  // Starting Y position for the first line of ConName
		$lineSpacing = (strlen($conName) > 37) ? 2.7 : 3;  // Adjust spacing between lines

		$this->SetFont('Arial', '', $conFontSize);  // Set the font and size
		$this->SetXY(17, $baseY);  // Set the initial X and Y position

		// Check if the ConName is long and needs to be split into two lines
		if (strlen($conName) > 44) {
			$firstLine = substr($conName, 0, 37);  // Get the first 44 characters
			$secondLine = substr($conName, 37);    // Get the remaining characters

			// Write the first and second lines with adjusted Y positions
			$this->Write(0, $firstLine);
			$this->SetXY(17, $baseY + $lineSpacing);  // Move down for the second line
			$this->Write(0, $secondLine);

			$baseY += $lineSpacing;  // Adjust the base Y position for the address section
		} else {
			// If the name is short, just write it in one line
			$this->Write(0, $conName);
		}

		// Set new Y positions for the address fields based on the updated baseY
		$addressYPositions = [
			$baseY + $lineSpacing * 1,  // First address line
			$baseY + $lineSpacing * 2,  // Second address line
			$baseY + $lineSpacing * 3,  // Third address line
			$baseY + $lineSpacing * 4   // Fourth address line
		];

		// Address fields to display (ConAdr1, ConAdr2, etc.)
		$addressFields = ['ConAdr1', 'ConAdr2', 'ConAdr3', 'ConAdr4'];

		// Loop through each address line and write it at the correct position
		foreach ($addressFields as $index => $field) {
			$this->SetXY(17, $addressYPositions[$index]);  // Set the position
			$this->Write(0, $MASTER['MASTER'][$field]);    // Write the address line
		}


		/* END BOX 8 */

		/* BOX 14 */
		
		$this->SetXY(17, 63);
		$this->SetFont('Arial','',20);
		$this->Cell(82,21,'','LB',0,'');

		$this->SetXY(19, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '14  Declarant address');

		$this->SetXY(63, 67);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'TIN: ');

		$this->SetXY(69, 67);
		$this->SetFont('Arial','',7);
		$this->Write(0, $MASTER['brokers']['TIN12']);
		
		$this->SetXY(17, 70);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['brokers']['For_name']);

		$this->SetXY(17, 74);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['brokers']['For_Adr1']);

		$this->SetXY(17, 78);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['brokers']['For_Adr2']);

		$this->SetXY(17, 82);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['brokers']['For_Adr3']);

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
		$this->Write(0, $MASTER['MASTER']['Vessel']);

		$this->SetXY(65, 86);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Registry No.');

		$this->SetXY(65, 87);
		$this->SetFont('Arial','',20);
		$this->Cell(5,4,'','L',0,'');

		$this->SetXY(65, 89);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		/* END BOX 18 */

		/* BOX 19 */
		
		$this->SetXY(83, 84);
		$this->SetFont('Arial','',20);
		$this->Cell(5,7,'','L',0,'');

		$this->SetXY(83, 86);
		$this->SetFont('Arial','',6);
		$this->Write(0, '19  Ct');

		$cont = $this->LOADDATA_CONT($MASTER['MASTER']['ApplNo']);
		if ((count($cont) == 0)){
			$CF="0";
		}else{
			$CF="1";
		}

		if ($CF == 1) {
			$this->Image('check.png',90,87,2.5);
		}else{
			$this->Image('cross.png',90,87.5,2);
		}

		// $this->SetXY(85, 89);
		// $this->SetFont('Arial','',8);
		// $this->Write(0, $CF);

		/* END BOX 19 */

		/* BOX 21 */
		
		$this->SetXY(17, 92);
		$this->SetFont('Arial','',20);
		$this->Cell(82,5,'','LB',0,'');

		$this->SetXY(19, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '21  Local Carrier (if any)');

		$this->SetXY(73, 91);
		$this->SetFont('Arial','',20);
		$this->Cell(5,6,'','L',0,'');

		$this->SetXY(19, 95.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['LocalCarrier']);

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
		$this->Write(0, '');

		/* END BOX 25 */

		/* BOX 26 */
		
		$this->SetXY(23, 97);
		$this->SetFont('Arial','',20);
		$this->Cell(82,10,'','L',0,'');

		$this->SetXY(23, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, '26 Province of Origin');

		$this->SetXY(23, 102);
		$this->SetFont('Arial','',6);
		$this->Write(0, $MASTER['MASTER']['ProvofOrig']);
		
		$this->SetXY(23, 104);
		$this->SetFont('Arial','',6);
		//$this->Write(0, $MASTER['MASTER']['prov_dsc']);
		//$this->MultiCell(50,3,substr($MASTER['MASTER']['prov_dsc'],0,27),0,'L');
		if(!empty($MASTER['MASTER']['prov_dsc'])){
			$this->MultiCell(50,3,substr($MASTER['MASTER']['prov_dsc'],0,27),0,'L');
		}elseif ($MASTER['MASTER']['prov_dsc'] == "098400000"){
			$this->MultiCell(50,3,'METRO MANILA',0,'L');
		}else{
			$this->MultiCell(50,3,'',0,'L');
		}

		/* END BOX 26 */

		/* BOX 27 */
		
		$this->SetXY(61, 97);
		$this->SetFont('Arial','',20);
		$this->Cell(80,9,'','L',0,'');

		$this->SetXY(62, 99);
		$this->SetFont('Arial','',6);
		$this->Write(0, '27 Port of Loading');
		
		$this->SetXY(62, 101);
		$this->SetFont('Arial','',6);
		$this->Write(0, $MASTER['MASTER']['PortofLoad']);

		$this->SetXY(62, 102);
		$this->SetFont('Arial','',6);
		//$this->Write(0, $MASTER['DMOffClear1']['loc_dsc']);
		$string1 = isset($MASTER['DMOffClear1']['loc_dsc']) ? $MASTER['DMOffClear1']['loc_dsc'] : '';
		 
		$value_gbshdtab1 =  preg_replace('/\s+/', '', $string1);
		$count = $value_gbshdtab1;
		
		if($count <30)
		{
		$value_gbshdtab1 =  preg_replace('/\s+/', '', $string1);
		$this->SetFont('Arial','',5);
		$this->MultiCell(30,2,$value_gbshdtab1,'C');
		}else{
		$this->MultiCell(30,2,$value_gbshdtab1.'asdfads','C');
		}
		
		/* END BOX 27 */

		/* BOX 29 */
		
		$this->SetXY(17, 113);
		$this->SetFont('Arial','',20);
		$this->Cell(80,3,'','',0,'');

		$this->SetXY(19, 109);
		$this->SetFont('Arial','',6);
		$this->Write(0, '29  Port of Departure');

		$this->SetXY(17, 112);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['PortofDept']);

		$this->SetXY(17, 115);
		$this->SetFont('Arial','',8);
		$this->Write(0, isset($MASTER['DMOffClear2']['offClrName']) ? $MASTER['DMOffClear2']['offClrName'] : '');

		/* END BOX 29 */

		/* BOX 30 */

		$this->SetXY(61, 106);
		$this->SetFont('Arial','',20);
		$this->Cell(82,11,'','L',0,'');

		$this->SetXY(61, 109);
		$this->SetFont('Arial','',6);
		$this->Write(0, '30  Location of Goods');
		
		$this->SetXY(61, 111);
		$this->SetFont('Arial','',8);
		//$this->Cell(38,3, $MASTER['rstGBSHDTAB']['shd_nam'], 0,0,'C');
		$string = $MASTER['rstGBSHDTAB']['shd_nam'];
		if (stripos($string, '999 - IMPORTERS PREMISE') !== false) {
			$string = '999 - OTHER LOCATION';
		}
		
		$value_gbshdtab =  preg_replace('/\s+/', '', $string);
		$count = $value_gbshdtab;
		if($count <30)
		{
		$value_gbshdtab =  preg_replace('/\s+/', '', $string);
		$this->SetFont('Arial','',5);
		$this->MultiCell(35,3,$value_gbshdtab,'C');
		}else{
		$this->MultiCell(35,3,$value_gbshdtab,'C');
		}

		/* END BOX 30 */

		$this->SetXY(129, 17);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Office of DISPATCH/EXPORT');

		$this->SetXY(129, 20);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Office Code');

		$this->SetXY(147, 20);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['OffClear']);
		
		$this->SetXY(132, 24);
		$this->SetFont('Arial','',8);
		$this->Write(0,  $MASTER['DMOffClear3']['offClrName']);

		$this->SetXY(129, 28);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Manifest Number');
		
		$this->SetXY(132, 32);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['Manifest']);

		$this->SetXY(137, 32);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(152, 32);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(129, 36);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Entry Number');

		$this->SetXY(143, 36);
		$this->SetFont('Arial','',8);
		//$this->Write(0, $MASTER['MASTER']['ExpDocNo']);
		$this->Write(0, @$_GET['appno']);

		$date = (array) $MASTER['MASTER']['CreationDate'];
		$CreationDate = @date('m/d/Y', strtotime($date['date']));

		$this->SetXY(168, 36);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date');

		$this->SetXY(178, 36);
		$this->SetFont('Arial','',8);
		$this->Write(0, $CreationDate);

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
		$this->Cell(10,6,$MASTER['MASTER']['MDec'],0,0,'C');

		$this->SetXY(109, 25);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		$this->SetXY(109, 24);
		$this->SetFont('Arial','',8);
		$this->Cell(10,6,$MASTER['MASTER']['Mdec2'],0,0,'C');

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
		$this->Write(0, $MASTER['row_count']);


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
		$this->Write(0, $MASTER['max_rows']);
		
		$this->SetXY(114, 38);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','L',0,'');

		/* END BOX 5 */

		/* BOX 6 */
		
		$this->SetXY(114, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, '6  Tot Pack');

		$this->SetXY(119, 43);
		$this->SetFont('Arial','',9);
		$this->Write(0, $MASTER['MASTER']['Packs']);

		/* END BOX 6 */

		/* BOX 7 */

		$this->SetXY(139, 40);
		$this->SetFont('Arial','',6);
		$this->Write(0, '7  Declarant Reference Number');

		$this->SetXY(139, 38);
		$this->SetFont('Arial','',20);
		$this->Cell(30,7,'','L',0,'');

		$application_no = date('Y').' / '.$MASTER['MASTER']['APPLNO'];

		$this->SetXY(153, 43);
		$this->SetFont('Arial','',8);
		$this->Write(0, $application_no);

		/* END BOX 7 */

		/* Box 9 */
		
		$this->SetXY(99, 47);
		$this->SetFont('Arial','',6);
		$this->Write(0, '9  Registry Office :');

		$this->SetXY(99, 50);
		$this->SetFont('Arial','',8);
		$this->Cell(108,7,$MASTER['tblZone']['ZoneDesc'],0,0,'C');

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
		$this->Write(0, '12  Tot. F/I/O');

		$this->SetXY(155, 59);
		$this->SetFont('Arial','',20);
		$this->Cell(30,6,'','LR',0,'');

		$this->SetXY(170, 60);
		$this->SetFont('Arial','',8);
		$this->Cell(15,6,'',0,0,'R');

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
		$this->Write(0, $MASTER['MASTER']['cexpdesc']);
		
		$this->SetXY(158, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '15  C.E. Code');

		$this->SetXY(155, 65);
		$this->SetFont('Arial','',8);
		$this->Cell(29,10,$MASTER['MASTER']['Cexp'],'LR',0,'C');

		/* END BOX 15 */

		/* Box 17 */

		$this->SetXY(184, 67);
		$this->SetFont('Arial','',6);
		$this->Write(0, '17 C.D. Code');

		$this->SetXY(184, 65);
		$this->SetFont('Arial','',8);
		$this->Cell(23,10,$MASTER['MASTER']['Cdest'],0,0,'C');

		/* END BOX 17 */

		/* Box 16 */
		$data2 = $this->ItemCount($MASTER['MASTER']['APPLNO'] );
		
		if (count($data2) == 1){
			$countryoforigin = trim($data2[0]['cityDisc'], " ");
		}else{
			$isMANY = "0";
			for ($i = 0; $i < count($data2); $i++){
				if ($data2[0]['CoCode'] != $data2[$i]['CoCode']){
					$isMANY = "1";
				}
			}
			
			if ($isMANY == "1") {
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
		$this->SetFont('Arial','',6);
		$this->Write(0, !empty($MASTER['country']['cty_dsc']) ? $MASTER['country']['cty_dsc'] :"");

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
		$this->Write(0, $MASTER['MASTER']['TDelivery'].' | '.@$MASTER['PAY1']['tod_dsc']);


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
		$this->Write(0, $MASTER['MASTER']['CustCurr']);

		$this->SetXY(129, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Customs Value');

		$this->SetXY(132, 96.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['CustomVal']);

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
		$this->Cell(21,9,$MASTER['MASTER']['ExchRate'],0,0,'C');

		/* END Box 23*/

		/* Box 24 */

		$this->SetXY(184, 93);
		$this->SetFont('Arial','',6);
		$this->Write(0, '24  Forex');
		

		$this->SetXY(188, 96.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(196, 95);
		$this->SetFont('Arial','',8);
		$this->Cell(21,4,'','L',0,'');

		if ($MASTER['MASTER']['Forex'] == true){
			$TB2="1";
		}else{
			$TB2="0";
		}

		$this->SetXY(196, 94.5);
		$this->SetFont('Arial','',8);
		$this->Cell(11,4,$TB2,0,0,'C');

		/* END Box 24*/

		/* Box 28 */

		$this->SetXY(99, 101);
		$this->SetFont('Arial','',6);
		$this->Write(0, '28  Financial and Banking Data -');

		$this->SetXY(143, 101);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Code');

		$this->SetXY(156, 101);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['BankCode']);
		$this->SetXY(99, 105);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Terms of Payment');

		$this->SetXY(127, 105);
		$this->SetFont('Arial','',8);
		/*if ($MASTER['MASTER']['Tpayment'] == 'B3') {
			$this->Write(0, $MASTER['MASTER']['Tpayment'].'  - '.'Inward Remittance & Payment');
		}else{
			$this->Write(0, $MASTER['MASTER']['Tpayment'].'  - '.'Basic');
		}*/
		$this->Write(0, $MASTER['MASTER']['Tpayment'].' | '.@$MASTER['PAY2']['top_dsc']);

		$this->SetXY(99, 109.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Name');

		$this->SetXY(127, 109.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['bnkName']['bnkName']);

		$this->SetXY(99, 114);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Branch');

		$this->SetXY(127, 114);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['BranchCode']);

		$this->SetXY(147, 114);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Bank Ref Number:');

		$this->SetXY(167, 114);
		$this->SetFont('Arial','',8);
		$this->Write(0, ($MASTER['MASTER']['BankRef'] === "000000000-0000000") ? "" : $MASTER['MASTER']['BankRef']);


		/* END Box 28 */

		/* BOX 5, 6, 7, 9, 10, 11, 12, 13, 15, 16, 17, 20, 22, 23, 24, 28 END */

		/* BOX 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46 */

		$this->SetXY(5, 117);
		$this->SetFont('Times','',20);
		$this->Cell(202,72,'','LBR',0,'C');

		/* BOX 31 */
		foreach ($ASSET as $key => $ASSETS) {
			if ($ASSETS['ItemNo'] == 1) {
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
				$this->Write(0, 'Marks and Numbers - Container No(s) - Number and Kind');

				$this->SetXY(21, 124);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'Marks & No');

				$this->SetXY(21, 127);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'of Packages');
				
				$this->SetXY(39, 123);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $ASSETS['Marks1']);
				
				$this->SetXY(39, 125);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $ASSETS['Marks2']);
				
				$LOANO = $this->LOADDATA_LOANO($ASSETS['itemcode'], $ASSETS['PTOPS_ROWID']);
				$this->SetXY(39, 127);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $LOANO);

				$this->SetXY(21, 131);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'Number and Kind');

				$this->SetXY(39, 131);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['NoPack']);
				
				$this->SetXY(56, 131);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['PackCode']);

				$this->SetXY(56, 135);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['pkg_dsc']);

				$this->SetXY(21, 139);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'Container No(s)');

				
				if ($ASSETS['Cont1'] == null) {
					$count = 0;
					foreach ($CONT as $key => $CONTS) {
						$count ++;
						if ($count == 1) {
							$this->SetXY(39, 139.5);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 3) {
							$this->SetXY(64, 139.5);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 2) {
							$this->SetXY(39, 143.5);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 4) {
							$this->SetXY(64, 143.5);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}
					}
				}else{
					$this->SetXY(39, 139.5);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont1']);

					$this->SetXY(64, 139.5);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont3']);

					$this->SetXY(39, 143.5);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont2']);

					$this->SetXY(64, 143.5);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont4']);
				}


				$this->SetXY(21, 148);
				$this->SetFont('Arial','B',6);
				$this->Write(0, '--Goods Desc');

				$this->SetXY(21, 149);
				$this->SetFont('Arial','B',8);
				$this->MultiCell(110,2.5,$ASSETS['GoodsDesc'],0,'L');
				
				$this->SetXY(21, 152.5);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $ASSETS['GoodsDesc2']);

				$this->SetXY(21, 155);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $ASSETS['GoodsDesc3']);
				
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
				$this->Write(0, $ASSETS['ItemNo']);

				/* END BOX 32 */
				
				/* Box 33 */

				$this->SetXY(131, 119);
				$this->SetFont('Arial','',6);
				$this->Write(0, '33  HS Code');

				$this->SetXY(133, 122.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['HSCode']);

				$this->SetXY(153, 122.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['HSCODE_TAR']);

				$this->SetXY(178, 119);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'Tar Spec');
				
				$tariffs = $this->LOADDATA_GBTARTAB($ASSETS['HSCode']);
				if ($ASSETS['Pref'] == "" || $ASSETS['Pref'] == "NONE" || $ASSETS['Pref'] == "None" || $ASSETS['Pref'] == "none" || $ASSETS['Pref'] == null) {
					$hsrate = $tariffs['tar_t01'];
				}
				if ($ASSETS['Pref'] == "AFTA" || $ASSETS['Pref'] == "ATIGA"){
					$hsrate = $tariffs['tar_t02'];
				}
				if ($ASSETS['Pref'] == "AKFTA"){
					$hsrate = $tariffs['tar_t03'];
				}
				if ($ASSETS['Pref'] == "BOI"){
					$hsrate = $tariffs['tar_t04'];
				}
				if ($ASSETS['Pref'] == "BBCPT"){
					$hsrate = $tariffs['tar_t05'];
				}
				if ($ASSETS['Pref'] == "JPEPA"){
					$hsrate = $tariffs['tar_t06'];
				}
				if ($ASSETS['Pref'] == "AFMA"){
					$hsrate = $tariffs['tar_t07'];
				}
				if ($ASSETS['Pref'] == "AICO"){
					$hsrate = $tariffs['tar_t08'];
				}
				if ($ASSETS['Pref'] == "AICOB"){
					$hsrate = $tariffs['tar_t09'];
				}
				if ($ASSETS['Pref'] == "AICOC" || $ASSETS['Pref'] == "AIFTA"){
					$hsrate = $tariffs['tar_t10'];
				}
				if ($ASSETS['Pref'] == "AISP" || $ASSETS['Pref'] == "AJCEP"){
					$hsrate = $tariffs['tar_t11'];
				}
				if ($ASSETS['Pref'] == "AICOD"){
					$hsrate = $tariffs['tar_t12'];
				}
				if ($ASSETS['Pref'] == "ACFTA"){
					$hsrate = $tariffs['tar_t13'];
				}
				if ($ASSETS['Pref'] == "AICOE" || $ASSETS['Pref'] == "ANFTA"){
					$hsrate = $tariffs['tar_t14'];
				}
				if ($ASSETS['Pref'] == "AICOF"){
					$hsrate = $tariffs['tar_t15'];
				}

				$this->SetXY(190, 122.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['TARSPEC'].' '.@$hsrate.' %');

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
				$this->Write(0, $ASSETS['CoCode']);
				
				/* END BOX 34 */

				/* Box 35 */

				$this->SetXY(157, 125);
				$this->SetFont('Times','',20);
				$this->Cell(76,8,'','L',0,'C');

				$this->SetXY(157, 127);
				$this->SetFont('Arial','',6);
				$this->Write(0, '35  Item Gross Weight');
				
				$this->SetXY(157, 125);
				$this->SetFont('Arial','',8);
				$this->Cell(28,8,'','R',0,'R');

				$this->SetXY(157, 126.5);
				$this->SetFont('Arial','',8);
				$this->Cell(28,8,number_format($ASSETS['ItemGWeight'],2).' Kg.','',0,'R');

				/* END BOX 35 */

				/* Box 36 */

				$this->SetXY(185, 127);
				$this->SetFont('Arial','',6);
				$this->Write(0, '36  Pref');

				$this->SetXY(187, 130.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['Pref']);

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
				$this->Write(0, $ASSETS['ProcDesc']);

				$this->SetXY(143, 138.5);
				$this->SetFont('Arial','',8);
				//$this->Write(0, $ASSETS['ExtCode']);
				$this->Write(0, '000');
				
				/* END BOX 37 */

				/* Box 38 */

				$this->SetXY(157, 133);
				$this->SetFont('Times','',20);
				$this->Cell(76,8,'','L',0,'C');

				$this->SetXY(157, 135);
				$this->SetFont('Arial','',6);
				$this->Write(0, '38  Item Net Weight');
				
				$this->SetXY(157, 133);
				$this->SetFont('Arial','',8);
				$this->Cell(28,8,'','R',0,'R');

				$this->SetXY(157, 134.5);
				$this->SetFont('Arial','',8);
				$this->Cell(28,8,number_format($ASSETS['ItemNweight'],2).' Kg.','',0,'R');
				
				/* END BOX 38 */

				/* Box 39 */

				$this->SetXY(185, 135);
				$this->SetFont('Arial','',6);
				$this->Write(0, '39  Val Code');

				$this->SetXY(187, 138.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['quo_cod']);

				/* END Box 39 */

				/* Box 40b */

				$this->SetXY(131, 141);
				$this->SetFont('Times','',20);
				$this->Cell(76,9,'','B',0,'C');

				$this->SetXY(173, 143);
				$this->SetFont('Arial','',6);
				$this->Write(0, '40b  Previous Doc No.');

				$this->SetXY(173, 147);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['PrevDoc']);

				/* END Box 40b */

				/* Box 41 */

				$this->SetXY(131, 150);
				$this->SetFont('Times','',20);
				$this->Cell(76,10,'','B',0,'C');

				$this->SetXY(131, 152);
				$this->SetFont('Arial','',6);
				$this->Write(0, '41  Suppl. Units');

				$this->SetXY(131, 156);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['SupVal1']);

				$this->SetXY(131, 150);
				$this->SetFont('Times','',20);
				$this->Cell(18,10,'','R',0,'C');
				
				/* END Box 41 */

				/* Box 42 */

				$this->SetXY(150, 152);
				$this->SetFont('Arial','',6);
				$this->Write(0, '42  Item Customs Value (F. Cur)');

				$this->SetXY(152, 151.5);
				$this->SetFont('Arial','B',8);
				$this->Cell(30,10,number_format($ASSETS['InvValue'],2),0,0,'R');

				$this->SetXY(152, 150);
				$this->SetFont('Times','',20);
				$this->Cell(30,10,'','R',0,'C');
				
				/* END Box 42 */

				/* Box 43 */

				$this->SetXY(182, 152);
				$this->SetFont('Arial','',6);
				$this->Write(0, '43  Val Method.');

				$this->SetXY(185, 156.5);
				$this->SetFont('Arial','B',8);
				if ($ASSETS['ValMethodNum'] > 0) {
					$ValMethodNum = $ASSETS['ValMethodNum'];
				}else{
					$ValMethodNum = "";
				}
				$this->Write(0, $ValMethodNum);

				/* END Box 43 */

				$this->SetXY(20, 186);
				$this->SetFont('Arial','',6);
				$this->Write(0, 'Invoice No. :');

				$this->SetXY(33, 186);
				$this->SetFont('Arial','',8);
				//07302024:SPagarA: update
				//$this->Write(0, $ASSETS['InvNo']);
				$this->MultiCell(123,2,$ASSETS['InvNo'],0,'L');
			}
			
		}

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
		$this->Write(0, $MASTER['MASTER']['WayBill']);
		
		/* END Box 40a */



		/* BOX 44 */

		$this->SetXY(5, 157);
		$this->SetFont('Times','',6);
		$this->MultiCell(15,32,'','R');

		$this->SetXY(116, 157);
		$this->SetFont('Times','',6);
		$this->MultiCell(15,3,'','R');

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
		$this->Write(0, 'Export Clearance No. :');

		$this->SetXY(42, 159);
		$this->SetFont('Arial','',8);
		$this->Write(0, $edNumber);

		
		$this->SetXY(25, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(46, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(74, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(95, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(113, 166);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		/* END BOX 44 */

		/* Box 45 */

		$this->SetXY(157, 164);
		$this->SetFont('Times','',20);
		$this->Cell(50,10,'','B',0,'C');

		$this->SetXY(127, 160);
		$this->SetFont('Times','',20);
		$this->Cell(30,29,'','R',0,'C');

		$this->SetXY(157, 162.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '45  Adjustment');

		$this->SetXY(167, 163);
		$this->SetFont('Arial','B',8);
		$this->Cell(40,12,'',0,0,'C');

		/* END Box 45 */

		/* Box 46 */
		$this->SetXY(157, 176);
		$this->SetFont('Arial','',6);
		$this->Write(0, '46  Statistical (PHP) Value');

		$this->SetXY(167, 176);
		$this->SetFont('Arial','B',8);
		$CustomVal = str_replace(',', '', $MASTER['MASTER']['CustomVal']);
		$TOT_DUTY = $CustomVal * $MASTER['MASTER']['ExchRate'];
		$this->Cell(40,12,number_format($TOT_DUTY, 2),0,0,'R');

		/* END Box 46 */
		
		/* END BOX 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46 */
		
		/* BOX 47 */

		$this->SetXY(5, 189);
		$this->SetFont('Times','',20);
		$this->Cell(108,55,'','LBR',0,'C');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(80,40,'','L');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(80,33,'','R');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(12,33,'','R');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(35,33,'','R');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',6);
		$this->MultiCell(60.5,33,'','R');

		$this->SetXY(5, 191);
		$this->SetFont('Arial','',6);
		$this->Write(0, '47  Calculation');

		$this->SetXY(10, 194);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'of Taxes');

		$this->SetXY(20, 189);
		$this->SetFont('Times','',20);
		$this->Cell(93,5,'','B',0,'C');

		$this->SetXY(22.5, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Type');

		$this->SetXY(38, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tax Base');

		$this->SetXY(64, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Rate');

		$this->SetXY(86, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Amount');

		$this->SetXY(104, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'MP');


		$this->SetXY(20, 217);
		$this->SetFont('Times','',20);
		$this->Cell(93,5,'','B',0,'C');

		$this->SetXY(20, 224);
		$this->SetFont('Arial','',10);
		$this->Cell(80,3,'Date/Time Approved:',0,0,'L');

		$this->SetXY(70, 224);
		$this->SetFont('Arial','',10);
		if ($MASTER['MASTER']['ApprovedDate']) {
			$datea = (array)$MASTER['MASTER']['ApprovedDate'];
			$ApprovedDate = @date('m/d/Y g:i:s A', strtotime($datea['date']));
		}else{
			$ApprovedDate = '';
		}
		$this->Cell(80,3,$ApprovedDate,0,0,'L');

		$this->SetXY(20, 229);
		$this->SetFont('Arial','',10);
		$this->Cell(80,3,'Date/Time Allowed to Exit:',0,0,'L');

		$this->SetXY(70, 229);
		$this->SetFont('Arial','',10);
		if ($ApprovedDate == '') {
			$dateallowed = "";
		}else{
			$dateallowed = @date("m/d/Y g:i:s A", strtotime("+30 minutes",strtotime($datea['date'])));
		}
		$this->Cell(80,3,$dateallowed,0,0,'L');

		// $this->SetXY(75, 244);
		// $this->SetFont('Arial','',8);
		// $this->Cell(25,3,'','0',0,'R');

		if ($edNumber) {
			$code = $edNumber;
			$this->Code128(32,234,$code,70,8);
		}

		/* END Box 47 */

		/* BOX 48, 49, 47b */

		/* BOX 48 */

		$this->SetXY(113, 189);
		$this->SetFont('Times','',20);
		$this->Cell(94,55,'','LTR',0,'C');

		$this->SetXY(113, 194);
		$this->SetFont('Times','',20);
		$this->Cell(94,9,'','B',0,'C');

		$this->SetXY(113, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '48  Prepaid Account No.');


		$this->SetXY(113, 193.5);
		$this->SetFont('Arial','B',6);
		$this->Cell(47,3,$MASTER['MASTER']['PrePAcct'],0,0,'C');

		$this->SetXY(113, 189);
		$this->SetFont('Arial','B',6);
		$this->Cell(47,14,'','R',0,'C');

		/* END BOX 48*/

		/* BOX 49 */

		$this->SetXY(160, 191.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '49  Identification of Warehouse');

		$this->SetXY(160, 193.5);
		$this->SetFont('Arial','',8);
		$this->Cell(47,3,$MASTER['MASTER']['WareCode'].' / '.$MASTER['MASTER']['WareDelay'],0,0,'C');

		/* END BOX 49 */

		/* BOX 47b */

		$this->SetXY(113, 205);
		$this->SetFont('Arial','',6);
		$this->Write(0, '47b  ACCOUNTING DETAILS');

		$this->SetXY(115, 212);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Method of Payment      :');

		$this->SetXY(141, 212);
		$this->SetFont('Arial','',8);
		if (strlen($MASTER['MASTER']['PrePAcct']) == 0) {
			$PayMethod = "CASH";
		}else{
			$PayMethod = "CREDIT";
		}
		$this->Write(0, $PayMethod);

		$this->SetXY(115, 217);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Assessment Number    :');

		$this->SetXY(141, 215);
		$this->SetFont('Arial','',6);
		$this->Write(0, '');

		$this->SetXY(165, 217);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date    :');

		$this->SetXY(175, 217);
		$this->SetFont('Arial','',6);
		$this->Write(0, '');
		
		/* Start Broken line */
		
		$this->SetXY(113, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(115, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(117, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(119, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');
		
		$this->SetXY(121, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(123, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(125, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(127, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(129, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(131, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(133, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(135, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(137, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(139, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(141, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(143, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(145, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(147, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(149, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(151, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(153, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(155, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(157, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(159, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(161, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(163, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(165, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(167, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(169, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(171, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(173, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(175, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(177, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(179, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(181, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(183, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(185, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(187, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(189, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(191, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(193, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(195, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(197, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(199, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(201, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(203, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		$this->SetXY(205, 215);
		$this->SetFont('Times','',20);
		$this->Cell(1,9,'','B',0,'C');

		/* End Broken line */

		$this->SetXY(115, 228);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Fees');

		$this->SetXY(141, 228);
		$this->SetFont('Arial','',8);
		$this->Write(0, '0.00');

		$this->SetXY(115, 234);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Total Assessment');

		$this->SetXY(141, 234);
		$this->SetFont('Arial','',8);
		$this->Write(0, '0.00');

		/* END BOX 47b */

		/* END BOX 48, 49, 47b */

		/* BOX 51, 50, 52, 53 */

		/* BOX 50 */

		$this->SetXY(113, 244);
		$this->SetFont('Times','',20);
		$this->Cell(94,41,'','L',0,'C');

		$this->SetXY(113, 238);
		$this->SetFont('Times','',20);
		$this->Cell(94,27,'','T',0,'C');

		$this->SetXY(99, 244);
		$this->SetFont('Times','',20);
		$this->Cell(108,27,'','R',0,'C');

		$this->SetXY(113, 240);
		$this->SetFont('Arial','',6);
		$this->Write(0, '50 Exporter/Declarant');

		//05212024: SPagara: Add Exporter Name
		$this->SetXY(115, 242);
		$this->SetFont('Times','',8);
		if($MASTER['MASTER']['ExpCode'] == "TII23058"){
			$this->Cell(50,5,$MASTER['importers']['COmpanyName'],'B',0,'C');
		}else{
			$this->Cell(50,5,'','B',0,'C');
		}

		$this->SetXY(115, 247);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Printed Name and Signature of Declarant',0,0,'C');

		$this->SetXY(115, 256);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'','T',0,'C');

		$this->SetXY(115.5, 256);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Place and Date',0,0,'L');
		
		/* END BOX 50 */

		/* BOX 51 */

		$this->SetXY(5, 244);
		$this->SetFont('Times','',20);
		$this->Cell(108,27,'','TL',0,'C');

		$this->SetXY(5, 229);
		$this->SetFont('Times','',20);
		$this->Cell(15,56,'','R',0,'C');

		$this->SetXY(20, 246);
		$this->SetFont('Arial','',6);
		$this->Write(0, '51  AUTHORIZATION');

		$this->SetXY(22, 254);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Name of Agency:');

		$this->SetXY(22, 263);
		$this->SetFont('Times','',8);
		$this->Cell(40,5,'','T',0,'C');

		$this->SetXY(22, 263);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Printed Name',0,0,'L');

		$this->SetXY(72, 263);
		$this->SetFont('Times','',8);
		$this->Cell(40,5,'','T',0,'C');

		$this->SetXY(72, 263);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Position',0,0,'L');


		$this->SetXY(22, 273);
		$this->SetFont('Times','',8);
		$this->Cell(40,5,'','T',0,'C');

		$this->SetXY(22, 273);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Date',0,0,'L');

		$this->SetXY(72, 273);
		$this->SetFont('Times','',8);
		$this->Cell(40,5,'','T',0,'C');

		$this->SetXY(72, 273);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Valid Until',0,0,'L');

		/* END BOX 51 */

		/* BOX 52 */

		$this->SetXY(113, 262);
		$this->SetFont('Times','',20);
		$this->Cell(94,27,'','T',0,'C');

		$this->SetXY(113, 264);
		$this->SetFont('Arial','',6);
		$this->Write(0, '52  Bureau of Customs (BOC) Control');

		$this->SetXY(115, 271);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'','T',0,'C');

		$this->SetXY(115.5, 271);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Printed Name and Signature',0,0,'L');

		$this->SetXY(170, 271);
		$this->SetFont('Times','',8);
		$this->Cell(35,5,'','T',0,'C');

		$this->SetXY(170, 271);
		$this->SetFont('Times','',8);
		$this->Cell(35,5,'Position',0,0,'L');

		$this->SetXY(115, 280);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'','T',0,'C');

		$this->SetXY(115.5, 280);
		$this->SetFont('Times','',8);
		$this->Cell(50,5,'Date',0,0,'L');

		/* END BOX 52 */

		/* BOX 54 */

		$this->SetXY(5, 271);
		$this->SetFont('Times','',20);
		$this->Cell(202,14,'','LBR',0,'C');

		/* END BOX 54 */

		/* END BOX 52, 53  */
	}

	public function rider_page($MASTER, $ASSET, $CONT){

		$this->SetXY(5, 10);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'BOC SINGLE ADMINISTRATIVE DOCUMENT');


		$this->SetXY(132, 15);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Office Code');

		$this->SetXY(150, 15);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['OffClear']);

		$this->SetXY(135, 19);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['DMOffClear']['OffClrName']);

		$this->SetXY(132, 33);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Entry Number');

		$this->SetXY(150, 31);
		$this->SetFont('Arial','',8);
		$this->Write(0, '');

		$this->SetXY(167, 33);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Date');

		$date = (array) $MASTER['MASTER']['CreationDate'];
		$CreationDate = @date('m/d/Y', strtotime($date['date']));

		$this->SetXY(175, 33);
		$this->SetFont('Arial','',8);
		$this->Write(0, $CreationDate);

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
		$this->Write(0, $MASTER['importers']['DUNS']);

		$this->SetXY(21, 21);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['ConName']);

		$this->SetXY(21, 25);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['ConAdr1']);

		$this->SetXY(21, 29);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['ConAdr2']);

		$this->SetXY(21, 33);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['ConAdr3']);

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

		$this->SetXY(20, 52);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 58);
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

		$this->SetXY(178, 37);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tar Spec');
		
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
		$this->SetFont('Arial','',6);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 70);
		$this->SetFont('Arial','',6);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 48 */

		$this->SetXY(118, 78);
		$this->SetFont('Arial','',6);
		$this->Write(0, '48  Dutiable Value (PHP)');

		/* End Box 48 */

		/* Start Box 49 */

		$this->SetXY(172, 78);
		$this->SetFont('Arial','',6);
		$this->Write(0, '49  Adjustment');

		/* End Box 49 */
		
		$this->SetXY(118, 35);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 43.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 51.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 68);
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

		$this->SetXY(118, 54.50);
		$this->SetFont('Times','',20);
		$this->Cell(13.5,5.24,'','R',0,'C');

		$this->SetXY(147.7, 51.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 59.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');

		$this->SetXY(118, 68);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 68);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 76.25);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');


		
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

		$this->SetXY(20, 102);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 108);
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

		$this->SetXY(178, 87);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tar Spec');
		
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
		$this->SetFont('Arial','',6);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 120);
		$this->SetFont('Arial','',6);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 48 */

		$this->SetXY(118, 128);
		$this->SetFont('Arial','',6);
		$this->Write(0, '48  Dutiable Value (PHP)');

		/* End Box 48 */

		/* Start Box 49 */

		$this->SetXY(172, 128);
		$this->SetFont('Arial','',6);
		$this->Write(0, '49  Adjustment');

		/* End Box 49 */

		$this->SetXY(118, 85);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 93.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 118);
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
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 104.50);
		$this->SetFont('Times','',20);
		$this->Cell(13.5,5.24,'','R',0,'C');

		$this->SetXY(147.7, 101.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 109.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');

		$this->SetXY(118, 118);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 118);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 126.25);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');

		

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

		$this->SetXY(20, 152);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Container No(s)');

		$this->SetXY(20, 158);
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

		$this->SetXY(178, 137);
		$this->SetFont('Arial','',6);
		$this->Write(0, 'Tar Spec');
		
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
		$this->SetFont('Arial','',6);
		$this->Write(0, '41  Suppl. Units');

		/* End Box 41 */

		/* Start Box 42 */

		$this->SetXY(152, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, '42  Item Customs Value (F. Cur)');

		/* End Box 42 */

		/* Start Box 43 */

		$this->SetXY(186, 170);
		$this->SetFont('Arial','',6);
		$this->Write(0, '43  V.M');

		/* End Box 43 */

		/* Start Box 48 */

		$this->SetXY(118, 178);
		$this->SetFont('Arial','',6);
		$this->Write(0, '48  Dutiable Value (PHP)');

		/* End Box 48 */

		/* Start Box 49 */

		$this->SetXY(172, 178);
		$this->SetFont('Arial','',6);
		$this->Write(0, '49  Adjustment');

		/* End Box 49 */


		$this->SetXY(118, 135);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.24,'','B',0,'C');

		$this->SetXY(118, 143.25);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 151.50);
		$this->SetFont('Times','',20);
		$this->Cell(89,8.25,'','B',0,'C');

		$this->SetXY(118, 168);
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

		$this->SetXY(118, 154.50);
		$this->SetFont('Times','',20);
		$this->Cell(13.5,5.24,'','R',0,'C');

		$this->SetXY(147.7, 151.50);
		$this->SetFont('Times','',20);
		$this->Cell(29.7,8.24,'','R',0,'C');

		$this->SetXY(118, 159.75);
		$this->SetFont('Times','',20);
		$this->Cell(44.5,8.25,'','R',0,'C');

		$this->SetXY(118, 168);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 168);
		$this->SetFont('Times','',20);
		$this->Cell(34,8.24,'','R',0,'C');

		$this->SetXY(152, 176.25);
		$this->SetFont('Times','',20);
		$this->Cell(20,8.24,'','R',0,'C');




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
		$this->Write(0, $MASTER['MASTER']['MDec']);

		$this->SetXY(112, 19);
		$this->SetFont('Arial','',20);
		$this->Cell(80,6,'','L',0,'');

		$this->SetXY(112, 22);
		$this->SetFont('Arial','',8);
		$this->Write(0, $MASTER['MASTER']['Mdec2']);

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
		$this->Write(0, $MASTER['row_count']);


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

	public function rider_data($MASTER, $ASSET, $CONT){
		$y_2ndpage = 40;
		$counter = 0;
		$item_count = 0;
		$len = count($ASSET);
		foreach ($ASSET as $key => $ASSETS) {
			$y_2ndpage += 50;
			$item_count ++;
			if ($ASSETS['ItemNo'] != 1) {
				$counter ++;
			
				/* Start Marks & Numbers */

				$this->SetXY(40, $y_2ndpage - 100);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['Marks1']);

				
				$LOANO = $this->LOADDATA_LOANO($ASSETS['itemcode'], $ASSETS['PTOPS_ROWID']);
				$this->SetXY(40, $y_2ndpage - 97);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $LOANO);

				/* End Marks & Numbers */

				/* Start Number and Kind */

				$this->SetXY(40, $y_2ndpage - 94);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['NoPack']);

				$this->SetXY(70, $y_2ndpage - 94);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['PackCode']);

				$this->SetXY(70, $y_2ndpage - 91);
				$this->SetFont('Arial','B',6);
				$this->Write(0, $ASSETS['pkg_dsc']);

				/* End Number and Kind */

				/* Start Container No(s) */
				if ($ASSETS['Cont1'] == null) {
					$count = 0;
					foreach ($CONT as $key => $CONTS) {
						$count ++;
						if ($count == 1) {
							$this->SetXY(40, $y_2ndpage - 88);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 3) {
							$this->SetXY(65, $y_2ndpage - 88);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 2) {
							$this->SetXY(40, $y_2ndpage - 85);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}

						if ($count == 4) {
							$this->SetXY(65, $y_2ndpage - 85);
							$this->SetFont('Arial','B',8);
							$this->Write(0, $CONTS['container']);
						}
					}
				}else{
					$this->SetXY(40, $y_2ndpage - 88);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont1']);

					$this->SetXY(65, $y_2ndpage - 88);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont3']);

					$this->SetXY(40, $y_2ndpage - 85);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont2']);

					$this->SetXY(65, $y_2ndpage - 85);
					$this->SetFont('Arial','B',8);
					$this->Write(0, $ASSETS['Cont4']);
				}
				/*$this->SetXY(40, $y_2ndpage - 88);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['Cont1']);

				$this->SetXY(65, $y_2ndpage - 88);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['Cont2']);

				$this->SetXY(40, $y_2ndpage - 85);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['Cont3']);

				$this->SetXY(65, $y_2ndpage - 85);
				$this->SetFont('Arial','B',8);
				$this->Write(0, $ASSETS['Cont4']);*/

				/* End Container No(s) */

				/* Start Goods Desc */
				
				$this->SetXY(35, $y_2ndpage - 79);
				$this->SetFont('Arial','B',6);
				$this->MultiCell(80,2,$ASSETS['GoodsDesc'],0,'L');

				$this->SetXY(35, $y_2ndpage - 76);
				$this->SetFont('Arial','B',6);
				$this->MultiCell(80,2,$ASSETS['GoodsDesc2'],0,'L');

				$this->SetXY(35, $y_2ndpage - 74);
				$this->SetFont('Arial','B',6);
				$this->MultiCell(80,2, $ASSETS['GoodsDesc3'],0,'L');

				/* End Goods Desc */

				/* Start 32  Item No. */

				$this->SetXY(125, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['ItemNo']);

				/* End 32  Item No. */

				/* Start 33 Hs Code */

				$this->SetXY(140, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['HSCode']);

				$this->SetXY(155, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['HSCODE_TAR']);

				$this->SetXY(162, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['TARSPEC']);

				$tariffs = $this->LOADDATA_GBTARTAB($ASSETS['HSCode']);

				if ($ASSETS['Pref'] == "" || $ASSETS['Pref'] == "NONE" || $ASSETS['Pref'] == "None" || $ASSETS['Pref'] == "none" || $ASSETS['Pref'] == null) {
					$hsrate = $tariffs['tar_t01'];
				}
				if ($ASSETS['Pref'] == "AFTA" || $ASSETS['Pref'] == "ATIGA"){
					$hsrate = $tariffs['tar_t02'];
				}
				if ($ASSETS['Pref'] == "AKFTA"){
					$hsrate = $tariffs['tar_t03'];
				}
				if ($ASSETS['Pref'] == "BOI"){
					$hsrate = $tariffs['tar_t04'];
				}
				if ($ASSETS['Pref'] == "BBCPT"){
					$hsrate = $tariffs['tar_t05'];
				}
				if ($ASSETS['Pref'] == "JPEPA"){
					$hsrate = $tariffs['tar_t06'];
				}
				if ($ASSETS['Pref'] == "AFMA"){
					$hsrate = $tariffs['tar_t07'];
				}
				if ($ASSETS['Pref'] == "AICO"){
					$hsrate = $tariffs['tar_t08'];
				}
				if ($ASSETS['Pref'] == "AICOB"){
					$hsrate = $tariffs['tar_t09'];
				}
				if ($ASSETS['Pref'] == "AICOC" || $ASSETS['Pref'] == "AIFTA"){
					$hsrate = $tariffs['tar_t10'];
				}
				if ($ASSETS['Pref'] == "AISP" || $ASSETS['Pref'] == "AJCEP"){
					$hsrate = $tariffs['tar_t11'];
				}
				if ($ASSETS['Pref'] == "AICOD"){
					$hsrate = $tariffs['tar_t12'];
				}
				if ($ASSETS['Pref'] == "ACFTA"){
					$hsrate = $tariffs['tar_t13'];
				}
				if ($ASSETS['Pref'] == "AICOE" || $ASSETS['Pref'] == "ANFTA"){
					$hsrate = $tariffs['tar_t14'];
				}
				if ($ASSETS['Pref'] == "AICOF"){
					$hsrate = $tariffs['tar_t15'];
				}

				$this->SetXY(185, $y_2ndpage - 100);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['TARSPEC'].' '.@$hsrate.' %');

				/* End 33 Hs Code */

				/* Start 34 C.O. Code */

				$this->SetXY(129, $y_2ndpage - 91);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['CoCode']);

				/* End 34 C.O. Code */

				/* Start 35 Item Gross Weight */

				$this->SetXY(147.7, $y_2ndpage - 95);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,number_format($ASSETS['ItemGWeight'], 2).' Kg.',0,0,'R');

				/* End 35 Item Gross Weight */

				/* Start 36 Pref */

				$this->SetXY(177.3, $y_2ndpage - 95);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,$ASSETS['Pref'],0,0,'C');

				/* End 36 Pref */

				/* Start 37 Procedure */

				$this->SetXY(121, $y_2ndpage - 83);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['ProcDesc']);

				$this->SetXY(136, $y_2ndpage - 83);
				$this->SetFont('Arial','',8);
				//$this->Write(0, $ASSETS['ExtCode']);
				$this->Write(0, '000');

				/* End 37 Procedure */

				/* Start 38 Item Net Weight */

				$this->SetXY(147.7, $y_2ndpage - 87);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,number_format($ASSETS['ItemNweight'],2).' Kg.',0,0,'R');

				/* End 38 Item Net Weight */

				/* Start 39 Quota */

				$this->SetXY(177.3, $y_2ndpage - 87);
				$this->SetFont('Arial','',8);
				$this->Cell(29.7,8,$ASSETS['quo_cod'],0,0,'C');

				/* End 39 Quota */

				/* Start 40a AWB / BL */

				// $this->SetXY(118, $y_2ndpage - 79);
				// $this->SetFont('Arial','',8);
				// $this->Cell(44.6,8,$MASTER['MASTER']['WayBill'],0,0,'C');

				/* End 40a AWB / BL */
				
				/* Start 40b Previous Doc No. */

				$this->SetXY(162.4, $y_2ndpage - 79);
				$this->SetFont('Arial','',8);
				$this->Cell(44.6,8,$ASSETS['PrevDoc'],0,0,'C');

				/* End 40b Previous Doc No. */

				/* Start 41 Suppl. Units */

				$this->SetXY(132, $y_2ndpage - 66.5);
				$this->SetFont('Arial','',8);
				$this->Write(0, $ASSETS['SupVal1']);

				/* End 41 Suppl. Units */

				/* Start 42 Item Customs Value (F. Cur) */

				$this->SetXY(152, $y_2ndpage - 70.5);
				$this->SetFont('Arial','',8);
				$this->Cell(34,8,number_format($ASSETS['InvValue'],2),0,0,'R');

				/* End 42 Item Customs Value (F. Cur) */

				/* Start 43 V.M */

				$this->SetXY(186, $y_2ndpage - 70.5);
				$this->SetFont('Arial','',8);
				if ($ASSETS['ValMethodNum'] > 0) {
					$ValMethodNum = $ASSETS['ValMethodNum'];
				}else{
					$ValMethodNum = "";
				}
				$this->Cell(20,8,$ValMethodNum,0,0,'R');

				/* End 43 V.M */

				$this->SetXY(32, $y_2ndpage - 62);
				$this->SetFont('Arial','',8);
				//07302024:SPagara: update
				//$this->Cell(19,8,$ASSETS['InvNo'],0,0,'L');
				$this->MultiCell(87,2,$ASSETS['InvNo'],0,'L');

				/* Start 44 */
				/* End 44 */

				if ($ASSETS['ItemNo'] == '2') {
					$this->SetXY(104, 32);
					$this->SetFont('Arial','B',9);
					$this->Write(0, '2');
				}

				
				if ($counter % 3 == 0) {
					if ($counter != $len - 1) {
						$this->AddPage();

						if ($ASSETS['ItemNo'] != 1) {
							$page_number = round($counter/3) + 2;
							$this->SetXY(102, 30);
							$this->SetFont('Arial','B',9);
							$this->Cell(7,5,$page_number,0,0,'C');
						}
					}
					$this->rider_page($MASTER, $ASSET, $CONT);
					$y_2ndpage = -206.5;

				}
			}
		}
	}

	

	public function back_page($MASTER, $ASSET, $CONT){

		$this->SetXY(5, 10);
		$this->SetFont('Times','',20);
		$this->Cell(202,40,'',1,0,'C');

		/* Start 53 INTERNAL REVENUE (TAX PER BOX #45 & #47) */

		$this->SetXY(5, 13);
		$this->SetFont('Arial','',6);
		$this->Write(0, '53  INTERNAL REVENUE (TAX PER BOX #45 & #47)');

		$this->SetXY(5, 23);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TAXABLE VALUE PH');



		$this->SetXY(40, 20);
		$this->SetFont('Arial','B',9);
		$CustomVal = str_replace(',', '', $MASTER['MASTER']['CustomVal']);
		$TOT_DUTY = $CustomVal * $MASTER['MASTER']['ExchRate'];
		$this->Cell(30,5,number_format($TOT_DUTY, 2),'B',0,'R');


		$this->SetXY(5, 30);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'BANK CHARGES');

		$this->SetXY(40, 27);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'0','B',0,'R');


		$this->SetXY(5, 37);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'CUSTOMS DUTY');

		$this->SetXY(40, 34);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'0','B',0,'R');


		$this->SetXY(5, 44);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'BROKERAGE FEE');

		$this->SetXY(40, 41);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'','B',0,'R');

		
		$this->SetXY(75, 23);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'WHARFAGE');

		$this->SetXY(114, 20);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'0','B',0,'R');


		$this->SetXY(75, 34);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'ARRASTRE CHARGE');

		$this->SetXY(114, 31);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'0','B',0,'R');


		$this->SetXY(75, 44);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'DOCUMENTARY STAMP');

		$this->SetXY(114, 42);
		$this->SetFont('Arial','B',9);
		$this->Cell(30,5,'','B',0,'R');


		$this->SetXY(148, 23);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'OTHERS');

		$this->SetXY(178, 20);
		$this->SetFont('Arial','B',9);
		$this->Cell(27,5,'0','B',0,'R');


		$this->SetXY(148, 30);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TOTAL');
		

		$this->SetXY(178, 28);
		$this->SetFont('Arial','B',9);
		$this->Cell(27,5,number_format($TOT_DUTY, 2),0,0,'R');

		$this->SetXY(148, 37);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'LANDED COST PH');

		$this->SetXY(178, 35);
		$this->SetFont('Times','B',9);
		$this->Cell(27,5,'x 12%','B',0,'R');


		$this->SetXY(148, 44);
		$this->SetFont('Arial','B',9);
		$this->Write(0, 'TOTAL VAT PH');

		$this->SetXY(178, 41);
		$this->SetFont('Arial','B',9);
		$this->Cell(27,5,'','B',0,'R');


		/* End 53 INTERNAL REVENUE (TAX PER BOX #45 & #47) */

		$this->SetXY(5, 50);
		$this->SetFont('Arial','',9);
		$this->Cell(202,10,'DESCRIPTION IN TARIFF TERMS SHOULD BE',0,0,'C');

		$this->SetXY(5, 60);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		/* Start Box 54 SECTION */

		$this->SetXY(5, 62);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '54  SECTION');

		$this->SetXY(25, 60);
		$this->SetFont('Times','',20);
		$this->Cell(38,10,'','LR',0,'C');

		$this->SetXY(130, 60);
		$this->SetFont('Times','',20);
		$this->Cell(30,53,'','LR',0,'C');

		/* End Box 54 SECTION */

		/* Start 55  NO. OF PACKAGES EXAMINED */

		$this->SetXY(25, 62);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '55  NO. OF PACKAGES EXAMINED');
		
		/* End 65  NO. OF PACKAGES EXAMINED */

		/* Start EXAMINATION RETURN */

		$this->SetXY(78, 65);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'EXAMINATION RETURN');
		
		/* End EXAMINATION RETURN */

		/* Start 56  DATE RECIEVED */

		$this->SetXY(130, 62);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '56  DATE RECIEVED');
		
		/* End 56  DATE RECIEVED */

		/* Start 57  DATE RELEASED */

		$this->SetXY(160, 62);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '57  DATE RELEASED');
		
		/* End 56  DATE RELEASED */

		$this->SetXY(5, 60);
		$this->SetFont('Times','',20);
		$this->Cell(202,23,'','B',0,'C');

		$this->SetXY(5, 60);
		$this->SetFont('Times','',20);
		$this->Cell(202,23,'','B',0,'C');

		$this->SetXY(5, 60);
		$this->SetFont('Times','',20);
		$this->Cell(202,53,'','B',0,'C');

		$this->SetXY(5, 70);
		$this->SetFont('Times','',20);
		$this->Cell(10,43,'','R',0,'C');

		$this->SetXY(93, 70);
		$this->SetFont('Times','',20);
		$this->Cell(17,43,'','LR',0,'C');


		$this->SetXY(184, 70);
		$this->SetFont('Arial','B',8);
		$this->Cell(17,43,'','L',0,'C');

		$this->SetXY(80, 83);
		$this->SetFont('Times','',20);
		$this->Cell(17,30,'','L',0,'C');

		$this->SetXY(80, 83);
		$this->SetFont('Times','',20);
		$this->Cell(127,10,'','B',0,'C');

		$this->SetXY(80, 93);
		$this->SetFont('Times','',20);
		$this->Cell(127,10,'','B',0,'C');

		/* Start ITEM NO */

		$this->SetXY(6.5, 75);
		$this->SetFont('Arial','B',6);
		$this->Write(0, 'ITEM');

		$this->SetXY(7.5, 78);
		$this->SetFont('Arial','B',6);
		$this->Write(0, 'NO');
		
		/* End ITEM NO */


		/* Start 55 Description in Tariff terms should be */

		$this->SetXY(15, 72);
		$this->SetFont('Arial','B',6);
		$this->Write(0, '58');

		$this->SetXY(20.5, 77);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'DESCRIPTION IN TARIFF TERMS SHOULD BE');
		
		/* End 55 Description in Tariff terms should be */

		/* Start QTY */

		$this->SetXY(97.5, 77);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'QTY');
		
		/* End QTY */

		/* Start UNIT */

		$this->SetXY(115.5, 77);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'UNIT');
		
		/* End UNIT */


		/* Start UNIT VALUE */

		$this->SetXY(130, 71);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(30,12,'UNIT VALUE',0,'C');
		
		/* End UNIT VALUE */

		/* Start TARIFF HEADING */

		$this->SetXY(166, 75);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'TARIFF');

		$this->SetXY(164, 78);
		$this->SetFont('Arial','B',8);
		$this->Write(0, 'HEADING');
		
		/* End TARIFF HEADING */

		/* Start RATE */

		$this->SetXY(181, 71);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(30,12,'RATE',0,'C');
		
		/* End RATE */


		/* Start PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS */

		$this->SetXY(5, 114.4);
		$this->SetFont('Arial','B',8);
		$this->MultiCell(202,5,'PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS',0,'C');
		
		/* End PLEASE REFER TO RIDERS FOR FINDINGS ON OTHER ITEMS */


		$this->SetXY(5, 60);
		$this->SetFont('Times','',20);
		$this->Cell(202,60,'',1,0,'C');

		$this->SetXY(5, 125);
		$this->SetFont('Times','',20);
		$this->Cell(202,75,'',1,0,'C');

		$this->SetXY(5, 125);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 125);
		$this->SetFont('Times','',20);
		$this->Cell(115,75,'','R',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 145);
		$this->SetFont('Times','',20);
		$this->Cell(202,10,'','B',0,'C');

		$this->SetXY(5, 155);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 160);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 165);
		$this->SetFont('Times','',20);
		$this->Cell(115,5,'','B',0,'C');

		$this->SetXY(5, 170);
		$this->SetFont('Times','',20);
		$this->Cell(202,5,'','B',0,'C');

		$this->SetXY(5, 125);
		$this->SetFont('Arial','B',10);
		$this->Cell(115,10,'REVISED CHARGES',0,0,'C');

		$this->SetXY(120, 125);
		$this->SetFont('Arial','B',10);
		$this->Cell(87,10,'LIQUIDATION',0,0,'C');

		$this->SetXY(5, 137.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '59 CHARGES');

		$this->SetXY(7, 142.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Duty');

		$this->SetXY(7, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'BIR Taxes');

		$this->SetXY(7, 152.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'VAT');

		$this->SetXY(5, 157.5);
		$this->SetFont('Arial','',7);
		$this->Write(0, 'Excise Tax/Ad Valorem');

		$this->SetXY(7, 162.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Others');

		$this->SetXY(7, 167.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Surcharges');

		$this->SetXY(7, 172.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'TOTAL');

		$this->SetXY(5, 177.5);
		$this->SetFont('Arial','',6);
		$this->Write(0, '63  ACTION DIRECTED/RECOMMENDED');

		$this->SetXY(63, 177.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '64');

		$this->SetXY(73, 185);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(73, 198);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(34, 137.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '60 DECLARATION');

		$this->SetXY(67, 137.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '61 FINDINGS');

		$this->SetXY(92.5, 137.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '62 DIFFERENCES');

		$this->SetXY(120, 137.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '65 LIQUIDATED Amount');

		$this->SetXY(120, 147.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '66  SHORT/EXCESS');

		$this->SetXY(120, 157.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '67  REMARKS');

		$this->SetXY(120, 177.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '68');

		$this->SetXY(150, 185);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'COO III');

		$this->SetXY(178, 185);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(120, 189.5);
		$this->SetFont('Arial','',8);
		$this->Write(0, '69');

		$this->SetXY(150, 198);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'COO V');

		$this->SetXY(178, 198);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'DATE');

		$this->SetXY(62.5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(144.5,52,'','LB',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(28.75,40,'','R',0,'C');

		$this->SetXY(5, 135);
		$this->SetFont('Times','',20);
		$this->Cell(86.25,40,'','R',0,'C');

		$this->SetXY(5, 205);
		$this->SetFont('Times','',20);
		$this->Cell(202,85,'',1,0,'C');

		/* Start CONTINUATION FROM BOX # 31 */

		$this->SetXY(5, 205);
		$this->SetFont('Arial','B',10);
		$this->Cell(202,10,'FREE DISPOSAL',0,0,'C');

		$this->SetXY(7, 209);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'CONTINUATION FROM BOX # 31');


		/* Start Containers */

		$this->SetXY(7, 213);
		$this->SetFont('Arial','',8);
		$this->Write(0, 'Container Numbers continuation:');

		$cont_y = 214;
		$count = 1;
		$count_cont = 0;
		$x = 7;
		foreach ($CONT as $key => $CONTS) {
			$count_cont++;
			if ($count_cont != 1 && $count_cont != 2 && $count_cont != 3 && $count_cont != 4) {
				$count++;
				if ($count == 26) {
					$x = $x + 25;
					$cont_y = 214;
				}

				if ($count == 50) {
					$x = $x + 25;
					$cont_y = 214;
				}

				if ($count == 74) {
					$x = $x + 25;
					$cont_y = 214;
				}

				if ($count == 98) {
					$x = $x + 25;
					$cont_y = 214;
				}

				if ($count == 122) {
					$x = $x + 25;
					$cont_y = 214;
				}

				$cont_y += 3;
				$this->SetXY($x, $cont_y);
				$this->SetFont('Arial','',8);
				$this->Write(0, $CONTS['container']);
			}
		}

		/* End Containers */


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

	//____________________________ Extension du constructeur _______________________
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

$pdf = new PDF();

// Column headings
// Data loading
$pdf->SetFont('Arial','',10);
$applno = $_GET['aplid'];

$pdf->SetTitle($applno.'.pdf');
$MASTER = $pdf->LoadData($applno);
$ASSET = $pdf->LOADDATA_ASSET($applno);
$CONT = $pdf->LOADDATA_CONT($applno);
$COMP = $pdf->LOADDATA_COMP($applno);

$pdf->SetAutoPageBreak(0, 0);
$pdf->AddPage();
$pdf->Head();

$pdf->front_page($MASTER, $ASSET, $CONT);
if ($MASTER['row_count'] > 1) {
	$pdf->AddPage();
	$pdf->rider_page($MASTER, $ASSET, $CONT);
	$pdf->rider_data($MASTER, $ASSET, $CONT);
}

$pdf->AddPage();
$pdf->back_page($MASTER, $ASSET, $CONT);
$pdf->Output();
?>
