<%@LANGUAGE="VBSCRIPT"%> 
<!--#include file="../Connections/constrTIMcomins.asp" -->
<% 

' Document History
' mflores: 04/13/2022 - static id for MSP Avt rate
' SPagara: 04/13/2022 - update excise tax for VAPE if tarspec = "", from 4500 to 5500
' SPagara: 11/03/2022 - added AHKFTA in checking of tar rate
'LObligado: 11/21/2022 - hide ArrasCostperIem and WharCostperIem If Session("cltcode") <> "SKYFREIGHTTEST" and Session("cltcode") <> "FEDEX"
'LObligado: 12/06/2022 - Total Duties and Taxes Added in UI
'Spagara: 04/13/2023 - Update for AICode under HSCode 25232990 and 25239000
'Spagara: 06232023: additional Preference 	
'Spagara: 06062024: for Round up of valuesIPF

ApplNumber = request.QueryString("ApplNo")
Stats = request.QueryString("Status")
if Session("UserID") = "" then
      Response.redirect("https://student.intercommerce.com.ph") 
end if

Function EncryptPassword(strPassword)
  strPassword=ucase(trim(strPassword))
  for i = 1 to len(strPassword)
     EncryptPassword = EncryptPassword & chr(asc(mid(strPassword,i,1))+100)
  next
End Function

Function DecryptPassword(strPassword)
  strPassword=ucase(trim(strPassword))
  for i = 1 to len(strPassword)
     DecryptPassword = DecryptPassword & chr(asc(mid(strPassword,i,1))-100)
  next
End Function

Function RoundUp(n)
n = Int(n*1000+0.999999)/1000
RoundUp = formatnumber(n, 2)
End Function

If UCASE(session("cltcode")) = "ASPACA" OR UCASE(session("cltcode")) = "JCAA" OR UCASE(session("cltcode")) = "BABANTAOA" OR UCASE(session("cltcode")) = "RSECOBARA" OR UCASE(session("cltcode")) = "ABARCEBALA" OR UCASE(session("cltcode")) = "GPARNALAA" OR UCASE(session("cltcode")) = "APTUVILLOA" OR UCASE(session("cltcode")) = "JLEONARA" OR UCASE(session("cltcode")) = "JPAULMIA" OR UCASE(session("cltcode")) = "GPIEDADA" Then
	isAspacClient = "YES"
else
	isAspacClient = "NO"
End If

MM_DB = DecryptPassword(CStr(Request("cn")))
MM_DB = Session("db")

Dim rsCons__MMColParam
Dim IPF__MMColParam
Dim DocStamp__MMColParam
Dim BrokerTIN__MMColParam
Dim MODEDEC1__MMColParam
Dim IINS
Dim strICurr
Dim strInsCost
Dim OCost
Dim strOCurr
Dim strOCost
Dim strDVCurr
Dim DVAL
Dim TOTARRAS
Dim TOTWHAR
Dim TOTARRAS4
Dim TOTWHAR4
Dim VAT
Dim intXTotal
Dim dblIpf

'rsCons__MMColParam = "AGE00120504"	    'Get the "Application No." from CUSDEC main module; Temp value = "AGE00120504" 
'IPF__MMColParam = 250					'Get the "Import Processing Fee(IPF)" value from CUSDEC main module; Temp value = 250
'DocStamp__MMColParam = 265				'Get the "Doc Stamp" value from CUSDEC main module; Temp value = 265
'BrokerTIN__MMColParam = "99999999999"	'Get the "Broker TIN" value from CUSDEC main module; Temp value = "99999999999"

rsCons__MMColParam = ApplNumber	    
IPF__MMColParam = Session("IPF")					

BrokerTIN__MMColParam = Session("BrkTIN")
MODEDEC1__MMColParam = Session("mod_cod")

'Response.Write("Applno = " & rsCons__MMColParam & " ")
'Response.Write("DocStamp = " & DocStamp__MMColParam & " ")
'Response.Write("BrkTIN = " & BrokerTIN__MMColParam & " ")
'Response.Write("MDec = " & MODEDEC1__MMColParam & " ")

if (Request("MM_EmptyValue") <> "") then rsCons__MMColParam = Request("MM_EmptyValue")

set rsCons = Server.CreateObject("ADODB.Recordset")
rsCons.ActiveConnection = constrCOMINScd
rsCons.Source = "SELECT * FROM dbo.TBLIMPAPL_CONS WHERE ApplNo = '" + Replace(rsCons__MMColParam, "'", "''") + "' ORDER BY ItemNo"
rsCons.CursorType = 1
rsCons.CursorLocation = 3
rsCons.LockType = 3
rsCons.Open()
rsCons_numRows = 0

set rstPDest = Server.CreateObject("ADODB.Recordset")
rstPDest.ActiveConnection = constrCOMINScd
rstPDest.Source = "SELECT applno, pdest, offclear, sentdate, stat FROM TBLIMPAPL_MASTER where applno='" & ApplNumber & "'"
rstPDest.CursorType = 0
rstPDest.CursorLocation = 2
rstPDest.LockType = 3
rstPDest.Open()
rstPDest_numRows = 0

if NOT rstPDest.EOF then
    strstatus = rstPDest("stat")
	strOFFCLEAR = trim(rstPDest("offclear"))
	
	'as per memo
	'DocStamp__MMColParam = Session("DocStamp")
	'response.write rstPDest("sentdate")
	set rstDOCS = Server.CreateObject("ADODB.Recordset")
	rstDOCS.ActiveConnection = constrCOMINScd
	rstDOCS.Source = "SELECT Sentdate FROM TBLIMPAPL_MASTER WHERE Applno='" & ApplNumber & "' AND SentDate < '11/14/2018 19:00'"
	rstDOCS.CursorType = 0
	rstDOCS.CursorLocation = 2
	rstDOCS.LockType = 3
	rstDOCS.Open()
	rstDOCS_numRows = 0
	
    '006062024:SPagara: update on CDS
	if NOT rstDOCS.EOF then                       
		'DocStamp__MMColParam = 265
        DocStamp__MMColParam = 130
	else
		'DocStamp__MMColParam = 280
        DocStamp__MMColParam = 130
	end if
else
	'DocStamp__MMColParam = 280
    DocStamp__MMColParam = 130
end if
rstPDest.Close
Set rstPDest = Nothing

if Session("mod_cod") = "IES" then
	DocStamp__MMColParam = 30
end if

TotalFINTax = 0
TotalFMFTax = 0		   
Dim Repeat1__numRows
Dim TOTALDVAL
TOTALDVAL = 0
Dim TotCUD
TotCUD = 0
Dim ITEMCUD
ITEMCUD = 0
Dim curTotalExcise
curTotalExcise = 0
Dim IBROKE
'IBROKE = 0
Repeat1__numRows = -1
Dim Repeat1__index
Repeat1__index = 0
rsCons_numRows = Recordset1_numRows + Repeat1__numRows

Dim TOTALVALUE
set rsConsTot = Server.CreateObject("ADODB.Recordset")
rsConsTot.ActiveConnection = constrCOMINScd
rsConsTot.Source = "Select Sum(InvValue) as TOTALVALUE from TBLIMPAPL_CONS where APPLNO='" + Replace(rsCons__MMColParam, "'", "''") + "'"
rsConsTot.CursorType = 1
rsConsTot.CursorLocation = 3
rsConsTot.LockType = 3
rsConsTot.Open()
rsConsTot_numRows = 0
TOTALVALUE = rsConsTot.Fields.Item("TOTALVALUE").Value
'Response.Write ("TOTAL VALUE - " & TOTALVALUE & " ")

Dim rsFIN__MMColParam
rsFIN__MMColParam = rsCons__MMColParam
if (Request("MM_EmptyValue") <> "") then rsFIN__MMColParam = Request("MM_EmptyValue")

Dim strRate
Dim strErtDate

If Request("txtRate") <> "" then
	strRate = Request("txtRate")
	strErtDate = Month(Now) & "/" & Day(Now) & "/" & Year(Now)
	set rsFIN = Server.CreateObject("ADODB.Recordset")
	rsFIN.ActiveConnection = constrCOMINScd	
	rsFIN.Source = "SELECT * FROM dbo.TBLIMPAPL_FIN WHERE ApplNo = '" + Replace(rsFIN__MMColParam, "'", "''") + "'"
	rsFIN.CursorType = 1
	rsFIN.CursorLocation = 3
	rsFIN.LockType = 3
	rsFIN.Open()
	rsFIN_numRows = 0
	
	'Auto update exchange rate if click recompute: Atoralde: 02242025
	' strErtDate1 = Year(Now) & Right("0" & Month(Now), 2) & Right("0" & Day(Now), 2)
	' set rsRate = Server.CreateObject("ADODB.Recordset")
	' rsRate.ActiveConnection = constrCOMINScd	
	' rsRate.Source = "SELECT * FROM GBRATTAB WHERE '"&strErtDate1&"' BETWEEN EEA_DOV AND EEA_EOV AND CUR_COD ='USD'"
	' rsRate.CursorType = 1
	' rsRate.CursorLocation = 3
	' rsRate.LockType = 3
	' rsRate.Open()
	' rsRate_numRows = 0  
	' strRate= rsRate("RAT_EXC")
Else 
	set rsFIN = Server.CreateObject("ADODB.Recordset")
	rsFIN.ActiveConnection = constrCOMINScd	
	rsFIN.Source = "SELECT * FROM dbo.TBLIMPAPL_FIN WHERE ApplNo = '" + Replace(rsFIN__MMColParam, "'", "''") + "'"
	rsFIN.CursorType = 1
	rsFIN.CursorLocation = 3
	rsFIN.LockType = 3
	rsFIN.Open()
	rsFIN_numRows = 0
	'Set rsFIN = Server.CreateObject("ADODB.RecordSet") 
	'rsFIN.Open "SELECT * FROM dbo.TBLIMPAPL_FIN WHERE ApplNo = '" + Replace(rsFIN__MMColParam, "'", "''") + "'", "Driver={SQL Server};Server=COMINS;Database=BOCREC;Uid=sa;pwd=df0rc3;", 3, 3, 1 
	
	strErtDate = Month(Now) & "/" & Day(Now) & "/" & Year(Now)
	
	If rsFIN.RecordCount > 0 then
		If Trim(rsFIN.Fields.Item("EXCHRATE").Value) <> "" or IsNull(rsFIN.Fields.Item("EXCHRATE").Value) = False Then
			strRate = rsFIN.Fields.Item("EXCHRATE").Value
			set rsDuty = Server.CreateObject("ADODB.Recordset")
			rsDuty.ActiveConnection = constrCOMINScd
			rsDuty.Source = "Select * from GBRATTAB where CUR_COD='"& rsFIN("CUSTCURR") &"' and RAT_EXC = '" & Trim(strRate) & "' order by eea_dov DESC"
			rsDuty.CursorType = 1
			rsDuty.CursorLocation = 3
			rsDuty.LockType = 3
			rsDuty.Open()
			rsDuty_numRows = 0
			If Not rsDuty.EOF Then
				strErtDate = Mid(rsDuty.Fields.Item("eea_dov").Value, 5, 2) & "/" & Mid(rsDuty.Fields.Item("eea_dov").Value, 7, 2) & "/" & Mid(rsDuty.Fields.Item("eea_dov").Value, 1, 4)
				'strErtDate = rsDuty.Fields.Item("eea_dov").Value
			End If
		Else
			set rsDuty = Server.CreateObject("ADODB.Recordset")
			rsDuty.ActiveConnection = constrCOMINScd
			rsDuty.Source = "Select * from GBRATTAB where CUR_COD='"& rsFIN("CUSTCURR") &"' order by eea_dov DESC"
			rsDuty.CursorType = 1
			rsDuty.CursorLocation = 3
			rsDuty.LockType = 3
			rsDuty.Open()
			rsDuty_numRows = 0
			strErtDate = Mid(rsDuty.Fields.Item("eea_dov").Value, 5, 2) & "/" & Mid(rsDuty.Fields.Item("eea_dov").Value, 7, 2) & "/" & Mid(rsDuty.Fields.Item("eea_dov").Value, 1, 4)
			'strErtDate = rsDuty.Fields.Item("eea_dov").Value
			strRate = rsDuty.Fields.Item("rat_exc").Value
		End If
		
	End If
End If
%>
<html>
<head>
<title>InterCommerce Network Services - Preassessment</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../global.css" type="text/css">
<script language="JavaScript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
</head>
<body bgcolor="#666666" text="#000000">
<form name="frmCreate" method="get" action="cws_preassessment.asp">
	<input type="hidden" name="ApplNo" value="<%=ApplNumber%>">
	<input type="hidden" name="Status" value="<%=Stats%>">
  <table width="630" border="0" cellspacing="0" cellpadding="0" align="center" height="600">
    <tr> 
      <td colspan="6" height="1" bgcolor="#333333"><img src="../Customs/Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td bgcolor="#333333" width="1" rowspan="11"><img src="../Customs/Images/spacer.gif" width="1" height="1"> 
      </td>
      <td bgcolor="#FFFFFF" height="61" colspan="4"><img src="../Images/INS-logo.jpg" width="299" height="80"></td>
      <td bgcolor="#333333" width="1" rowspan="11"><img src="../Customs/Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td bgcolor="#DBDBDB" bordercolor="#FFFFFF" height="12" colspan="4">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" height="93" colspan="2"> 
        <table width="185" border="0">
          <tr> 
            <td>&nbsp;</td>
          </tr>
        </table>
        <br>
      </td>
      <td bgcolor="#FFFFFF" valign="top" height="93" colspan="2"><img src="../Images/bnr-products.jpg" width="445" height="109"></td>
    </tr>
    <tr> 
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
      <td bgcolor="f7f7f7" valign="top" height="1" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2">&nbsp;</td>
      <td bgcolor="f7f7f7" valign="top" height="12" colspan="2">
        <div align="right"><a href="../logout.asp"><img src="../Images/btn-logout.gif" height="23" border="0"></a></div>
      </td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
      <td bgcolor="f7f7f7" valign="top" height="1" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="35">&nbsp;</td>
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="183">&nbsp;</td>
      <td bgcolor="#FFFFFF" valign="top" height="12" width="425">&nbsp;</td>
      <td bgcolor="#FFFFFF" valign="top" height="12" width="20">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="35" height="16"> 
        <p align="center"><br>
        </p>
        <p>&nbsp;</p>
      </td>
      <td bgcolor="#666666" bordercolor="#FFFFFF" align="center" valign="top" colspan="2"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="1">
          <tr> 
            <td bgcolor="#666699"> 
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td><img src="../Images/win-preassessment.jpg" width="156" height="32"></td>
                  <td> 
                    <div align="right"><a href="cws_impdec.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>"><img src="../Images/win-close.jpg" width="30" height="32" border="0"></a></div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr> 
            <td bgcolor="e5e5e5"> 
              <div align="center"><br>
                <br>
                <table border="0" cellspacing="0" cellpadding="5" width="562" align="left" height="115">
                  <tr bgcolor="#FCF4D6"> 
                    <td valign="top" height="0" bgcolor="e5e5e5">&nbsp;</td>
                    <td valign="top" height="0" colspan="3" bgcolor="e5e5e5"> 
                      <p> 
                        <% If Not rsFin.Recordcount > 0 then %>
                      </p>
                      <p><font color="#FF3300"><font color="#000000"><span class="desc">Financial data does not exist. </span><br>
                        <br>
                        <span class="body">Please enter the necessary information before proceeding to this module. Duty value and bank charges computation will not be completed.</span></font></font></p>
                      <p> 
                        <% Else %>
                        <% If Trim(MODEDEC1__MMColParam) = "" then %>
                        <font color="#FF3300"><font color="#000000"><span class="desc">Declarant's TIN does not exist.</span> <br>
                        <br>
                        <span class="body">This is mandatory for the computation of pre-assessment.</span></font></font><span class="body"><font color="#FF3300" face="Arial, Helvetica, sans-serif" size="2"><b><font color="#000000"></font></b></font></span><font color="#FF3300" face="Arial, Helvetica, sans-serif" size="2"><b><font color="#000000"> 
                        </font> 
                        <% Else %>
                        </b></font></p>
                      <table width="100%" border="0" cellspacing="0">
                        <tr bgcolor="#666699"> 
                          <td width="1%">&nbsp;</td>
                          <td width="36%" valign="middle"> 
                            <div align="right">&nbsp;&nbsp; <font color="#FFFFFF" class="body"><b>Using Exchange Rate of:</b></font></div>
                          </td>
                          <td class="body" width="4%">&nbsp;</td>
                          <td class="body" width="33%"> 
                            <div align="left"><font color="#FFFFFF"> 
                              <% Response.Write(strErtDate) %>
                              </font><font color="#FFFFFF" class="body"> </font></div>
                          </td>
                          <td width="2%"><font color="#FFFFFF"> </font></td>
                          <td width="24%" rowspan="2"> 
                            <div align="center"><font color="#FFFFFF"> 
                              <input type="submit" name="btnRecomp" value="Re-compute" class="button">
                              &nbsp;&nbsp; </font></div>
                          </td>
                        </tr>
                        <tr bgcolor="#666699"> 
                          <td width="1%" height="2">&nbsp;</td>
                          <td width="36%" height="2"> 
                            <div align="right"><font color="#FFFFFF" class="body"><b><%=rsFIN("CUSTCURR")%>:&nbsp;&nbsp;</b></font></div>
                          </td>
                          <td class="body" width="4%">&nbsp;</td>
                          <td class="body" width="33%"><font color="#FFFFFF"> 
                            <input type="text" name="txtRate" value="<%=(strRate)%>" align="right" maxlength="8">
                            </font></td>
                          <td width="2%">&nbsp;</td>
                        </tr>
                      </table>
                      <br>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#666666">
                        <tr> 
                          <td bgcolor="#666666"> 
                            <table width="100%" border="0" height="21" cellspacing="1">
                              <tr bgcolor="#999999"> 
                                <td width="8%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Item</font></b></div>
                                </td>
                                <td width="31%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Transaction Value (F.Cur)</font></b></div>
                                </td>
                                <td width="24%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Duty Value (PHP)</font></b></div>
                                </td>
                                <td width="10%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Rate</font></b></div>
                                </td>
                                <td width="13%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">CUD</font></b></div>
                                </td>
                              </tr>
                              <% 
While ((Repeat1__numRows <> 0) AND (NOT rsCons.EOF)) 
%>
                              <tr bgcolor="#FFFFFF"> 
                                <td class="body" height="25"> 
                                  <div align="center"> 
                                    <p align="right"><%=(rsCons.Fields.Item("ItemNo").Value)%></p>
                                  </div>
                                </td>
                                <td class="body" height="25"> 
                                  <div align="center"><%= FormatNumber((rsCons.Fields.Item("InvValue").Value), 2, -2, -2, -2) %></div>
                                </td>
                                <td class="body" height="25"> 
                                  <div align="right"> 
                                    <%
    ' NOTE DUTIABLE VALUE = ITEM VALUE + ITEMIZED FREIGHT + ITEMIZED INSURANCE CHARGES + ITEMIZED OTHER CHARGES
    '---- where Item Value = Item Value x Exchange Rate
    ' COMPUTING FOR ITEMIZED FREIGHT
    ' IFREIGHT = TOTAL FREIGHT(Exchange Rate) (Item Value / Total Value)

	Dim ITEMVALUE
	Dim strFCurr	
	Dim intFCost
	Dim IFREIGHT

	set rsFIN = Server.CreateObject("ADODB.Recordset")
	rsFIN.ActiveConnection = constrCOMINScd
	rsFIN.Source = "SELECT * FROM dbo.TBLIMPAPL_FIN WHERE ApplNo = '" + Replace(rsFIN__MMColParam, "'", "''") + "'"
	rsFIN.CursorType = 1
	rsFIN.CursorLocation = 3
	rsFIN.LockType = 3
	rsFIN.Open()
	rsFIN_numRows = 0
If rsFIN.RecordCount > 0 then
	'--- Put the item value to variable
    ITEMVALUE = cDbl(rsCons.Fields.Item("InvValue").Value)

    '--- Compute Itemized Freight
    IFREIGHT = 0
	strFCurr = rsFIN.Fields.Item("FreightCurr").Value
	if Trim("" & rsFIN.Fields.Item("FreightCost").Value) = "" then
		intFCost = 0
	Else
		intFCost = cDbl(rsFIN.Fields.Item("FreightCost").Value)
	End If
	'Response.write("Freight Cost - " & intFCost & " ")
    If intFCost <> 0 Then
       If strFCurr <> "PHP" Then
            IFREIGHT = RoundUp(Round(cDbl(ITEMVALUE) / cDbl(TOTALVALUE) * (cDbl(intFCost)),2) * cDbl(strRate))
            'IFREIGHT = (cDbl(intFCost) * cDbl(strRate)) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
       Else
            IFREIGHT = RoundUp(cDbl(intFCost) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE)))
            'IFREIGHT = cDbl(intFCost) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
       End If
    End If

    '--- Compute Itemized Insurance
    IINS = 0
	strICurr = rsFIN.Fields.Item("InsCurr").Value
 	strInsCost	= rsFIN.Fields.Item("InsCost").Value
	'Response.write("Insurance Cost - " & strInsCost & " ")
	
	'even if ifreight is tagged
    'If rsCons.Fields.Item("Ifreight").Value = True Then
        'IINS = 0                   '---- if INSinFRT is set to 1
    'Else
        If trim(strInsCost) = "" or IsNull(strInsCost) = True then
			strInsCost = 0
		Else
			strInsCost = cDbl(strInsCost)
		End If
		'If strInsCost = 0 Then
            '---- Itemized Insurance = Item Value x Exchange Rate x 0.04
            'If strICurr <> "PHP" Then
               'IINS = cDbl(ITEMVALUE) * cDbl(strRate) * 0.04
            'Else
               'IINS = cDbl(ITEMVALUE) * 0.04
            'End If
        'Else                          '---- if with documents get value of which ever is higher
            '---- Itemized Insurance = Total Insurance x Insurance Exchange Rate x (Item Value/Total Value)
            If strICurr <> "PHP" Then
                C1 = RoundUp(Round(cDbl(ITEMVALUE) / cDbl(TOTALVALUE) * (cDbl(strInsCost)),2) * cDbl(strRate))
               'C1 = cDbl(strInsCost) * cDbl(strRate) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
            Else
               C1 = RoundUp(cDbl(strInsCost) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE)))
               'C1 = cDbl(strInsCost) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
            End If
            '---- Itemized Insurance = Item Value x Exchange Rate x 0
            If strICurr <> "PHP" Then
               C2 = cDbl(ITEMVALUE) * cDbl(strRate) * 0
            Else
               C2 = cDbl(ITEMVALUE) * 0
            End If
			'Response.write ("C1 - " & C1 & " ")
			'Response.write ("C2 - " & C2 & "   ")
				'IINS = IIf(C1 > C2, C1, C2)
				If C1 > C2 then
					IINS = RoundUp(cDbl(C1))
                    'IINS = cDbl(C1)
				Else
					IINS = cDbl(C2)
				end if
        'End If
    'End If

    '--- Compute Other Charges
	OCOST = 0
	strOCurr = rsFIN.Fields.Item("OtherCurr").Value
    strOCost = rsFIN.Fields.Item("OtherCost").Value
	'Response.write ("Other Cost - " & strOCost & " ")
    If rsCons.Fields.Item("OCharges").Value =  True Then
        If Session("cltcode") = "FEDEX" or Session("cltcode") = "DHLEXA" then
			OCOST = cDbl(strOCost)
		Else
			OCOST = 0                    '--- if ETHinEV is set to 0
		End If
    Else
        If trim(strOCost) = "" or IsNull(strOCost) = True then
			strOCost = 0
		Else
			strOCost = cDbl(strOCost)
		End If
        If strOCost = 0 Then   '--- if other charge is not declared then ETHinEV is set to 0
            '---- Itemized Other Charges = (Item Value x Exchange Rate) x 0.03
            If strOCurr <> "PHP" Then
               OCOST = cDbl(ITEMVALUE) * cDbl(strRate) * 0.03
            Else
               OCOST = cDbl(ITEMVALUE) * 0.03
            End If
        Else                           '--- if ETHinEV is not set to 1
            '---- Itemized Other Charges = Total Other Charges x Other Charges Exchange Rate x (Item Value / Total Value)
            If strOCurr <> "PHP" Then
               OCOST =  RoundUp(Round(cDbl(ITEMVALUE) / cDbl(TOTALVALUE) * (cDbl(strOCost)),2) * cDbl(strRate))
               'OCOST = cDbl(strOCost) * cDbl(strRate) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
            Else
               OCOST = cDbl(strOCost) * (cDbl(ITEMVALUE) / cDbl(TOTALVALUE))
            End If
        End If
    End If

    '--- Compute Dutiable Value
    '---- Dutiable Value = Item Value x Exchange Rate + (Itemized Freight + Itemized Insurance + Itemized Other Charges)
    '---- do not multiply item value to exchange rate if value is in peso
    strDVCurr = rsFIN.Fields.Item("CustCurr").Value
	'Response.write ("Currency - " & strDVCurr & " ")
	
	If strDVCurr <> "PHP" Then
       DVAL = RoundUp((cDbl(ITEMVALUE) * cDbl(strRate)) + cDbl(IFREIGHT) + cDbl(IINS) + cDbl(OCOST))
       'DVAL = Round((cDbl(ITEMVALUE) * cDbl(strRate)),0) + Round(cDbl(IFREIGHT),0) + Round(cDbl(IINS),0) + Round(cDbl(OCOST),0)
           Response.write ("ITEM VAL = " & Round(cDbl(ITEMVALUE) * cDbl(strRate),0) & " ") & "<br>"
'           Response.write ("RATE - " & cDbl(strRate) & " ") & "<br>"
           Response.write ("FREIGHT = " & Round(IFREIGHT,0) & " ") & "<br>"     
           Response.write ("INSURANCE = " & Round(IINS,0) & " ") & "<br>"
           Response.write ("OTHER COST = " & Round(OCOST,0) & " ") & "<br>"
'           Response.write ("ITEMVALUE * RATE = " & Round(cDbl(ITEMVALUE) * cDbl(strRate),0) & " ") & "<br>"       
'           Response.write ("FREIGHT + INS + OCOST = " & round(cDbl(IFREIGHT + IINS + OCOST),0) & " ") & "<br>"                           
           Response.write ("DVAL = " & formatnumber(round(DVAL,0)) & " ")
    Else
       DVAL = ROUNDUP(cDbl(ITEMVALUE) + cDbl(IFREIGHT) + cDbl(IINS) + cDbl(OCOST))
       'DVAL = Round(cDbl(ITEMVALUE),0) + Round(cDbl(IFREIGHT),0) + Round(cDbl(IINS),0) + Round(cDbl(OCOST),0)
           Response.write ("ITEM VAL = " & Round(cDbl(ITEMVALUE),0) & " ") & "<br>"
'           Response.write ("RATE - " & cDbl(strRate) & " ") & "<br>"      
           Response.write ("FREIGHT = " & Round(IFREIGHT,0) & " ") & "<br>"       
           Response.write ("INSURANCE = " & Round(IINS,0) & " ") & "<br>"       
           Response.write ("OTHER COST = " & Round(OCOST,0) & " ") & "<br>"
'           Response.write ("ITEMVALUE * RATE = " & Round(cDbl(ITEMVALUE) * cDbl(strRate),0) & " ") & "<br>"       
'           Response.write ("FREIGHT + INS + OCOST = " & round(cDbl(IFREIGHT + IINS + OCOST),0) & " ") & "<br>"                           
           Response.write ("DVAL = " & formatnumber(round(DVAL,0)) & " ")
    End If
	
	'check Excise type
	set rsExcise = Server.CreateObject("ADODB.Recordset")
	rsExcise.ActiveConnection = constrCOMINScd
	rsExcise.Source = "SELECT Type, Rate FROM tblExcise WHERE AHTN = '"& rsCons("HSCode") &"' AND TarSpec = '" & rsCons("TARSPEC") & "' AND Type <> 'GASOLINE' AND Type <> 'DIESEL' AND Type <> 'KEROSENE'"
	rsExcise.CursorType = 0
	rsExcise.CursorLocation = 2
	rsExcise.LockType = 3
	rsExcise.Open()
	rsExcise_numRows = 0
	
	if NOT rsExcise.EOF then
		EXTAX = rsExcise("Type")
		exciseRate = rsExcise("Rate")
	else
	
		set rsExcise1 = Server.CreateObject("ADODB.Recordset")
		rsExcise1.ActiveConnection = constrCOMINScd
		rsExcise1.Source = "SELECT Type, Rate FROM tblExcise WHERE AHTN = '"& rsCons("HSCode") &"' AND Type <> 'GASOLINE' AND Type <> 'DIESEL' AND Type <> 'KEROSENE'"
		rsExcise1.CursorType = 0
		rsExcise1.CursorLocation = 2
		rsExcise1.LockType = 3
		rsExcise1.Open()
		rsExcise1_numRows = 0
	
		if not rsExcise1.eof then
			EXTAX = rsExcise1("Type")
			exciseRate = rsExcise1("Rate")
		else
			EXTAX = ""
			exciseRate = 0
		end if
		
	end if
	'rsExcise.Close()
	
	Dim TarSpec
	TarSpec = ""
	
	If NOT rsCons.EOF Then	
		If rsCons("TarSpec") = "" then
			TarSpec = "BLANK"
		else
			TarSpec = rsCons("TarSpec")
		end if
	End If
	
	'check AICODE
	set rsAiCode = Server.CreateObject("ADODB.Recordset")
	rsAiCode.ActiveConnection = constrCOMINScd
	rsAiCode.Source  = "SELECT Type, Rate FROM CWSAICODE WHERE HSCODE = '"& rsCons("HSCode") &"' AND HSCODE_TAR = '"& rsCons("HSCode_TAR") &"' AND TarSpec = '"& TarSpec &"' "
	rsAiCode.CursorType = 0
	rsAiCode.CursorLocation = 2
	rsAiCode.LockType = 3
	rsAiCode.Open()
	rsAiCode_numRows = 0
			
	If NOT rsAiCode.EOF Then
		aicodeRate = rsAiCode("Rate")
	Else
		aicodeRate = 0
	End IF
	
	
	if UCase(EXTAX) = "PETROLEUM" OR UCase(EXTAX) = "WINE" OR UCase(EXTAX) = "SPIRITS" OR UCase(EXTAX) = "LIQUOR" then
		ExciseTax = rsCons("SupVal1") * cdbl(exciseRate)
	elseif UCase(EXTAX) = "BEVERAGE" then
		ExciseTax = rsCons("SupVal1") * cdbl(aicodeRate)
	elseif UCase(EXTAX) = "MINERALS" then
        set rsExchRate = Server.CreateObject("ADODB.Recordset")	
	    rsExchRate.ActiveConnection = constrCOMINScd	
	    rsExchRate.Source = "SELECT TOP (1) RAT_EXC FROM GBRATTAB WHERE (CUR_COD = 'USD') ORDER BY EEA_DOV DESC"	
	    rsExchRate.CursorType = 0	
	    rsExchRate.CursorLocation = 2	
	    rsExchRate.LockType = 3	
	    rsExchRate.Open()	

        if strstatus = "C" then 	
            Set cmdFINupdate = Server.CreateObject("ADODB.Command")	
			cmdFINupdate.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=PL-INSCUSTSTDB;Uid=sa;Pwd=df0rc3;"	
			cmdFINupdate.CommandText = "UPDATE tblIMPAPL_FIN SET ExchRate = '" & rsExchRate("RAT_EXC") & "' WHERE ApplNo = '" & ApplNumber & "'"	
			cmdFINupdate.Execute	
        end if	

        set rsExchRate1 = Server.CreateObject("ADODB.Recordset")	
	    rsExchRate1.ActiveConnection = constrCOMINScd	
	    rsExchRate1.Source = "SELECT ExchRate FROM tblimpapl_fin WHERE ApplNo = '" & ApplNumber & "'"	
	    rsExchRate1.CursorType = 0	
	    rsExchRate1.CursorLocation = 2	
	    rsExchRate1.LockType = 3	
	    rsExchRate1.Open()

		'ExciseTax = DVAL * 0.04
		'ExciseTax = 0
        DpdTaxAmt = rsCons("InvValue") * CDbl(Replace(aicodeRate, "%", "")) / 100
		
	elseif UCase(EXTAX) = "TOBACCO" then
		ExciseTax = rsCons("SupVal1") * cdbl(exciseRate)
	elseif UCase(EXTAX) = "VAPE" then
		ExciseTax = rsCons("SupVal1") * (aicodeRate * 100)
	elseif UCase(EXTAX) = "PHARMACEUTICALS" then
		set chkRULCOD = Server.CreateObject("ADODB.Recordset")
		chkRULCOD.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=PL-INSCUSTSTDB;Uid=sa;pwd=df0rc3;"
		chkRULCOD.Source = "SELECT rul_cod FROM GBTARTAB WHERE hs6_cod+tar_pr1='" & rsCons("HSCode") & "' AND tar_pr2='" & rsCons("HSCODE_TAR") & "'"
		chkRULCOD.CursorType = 0
		chkRULCOD.CursorLocation = 2
		chkRULCOD.LockType = 3
		chkRULCOD.Open()

		If NOT chkRULCOD.EOF THEN
			If chkRULCOD("rul_cod") = "EXC-300390" Then
					ExciseTax = aicodeRate * rsCons("SupVal1")
			End If
		END IF
	else
		ExciseTax = 0
	end if
	
	ExcTax = ExciseTax
	if ExcTax <> "" then
		Response.write ("<br> Excise = " & formatnumber(ExcTax,2) & " ")
	end if
	
    if DpdTax <> "" then 	
        Response.write ("<br> DPD = " & formatnumber(DpdTax,2) & " ")
    end if
	'compute FMF
	set rstFMF = Server.CreateObject("ADODB.Recordset")
	rstFMF.ActiveConnection = constrCOMINScd
	rstFMF.Source = "SELECT Type, Rate FROM tblExcise WHERE AHTN = '"& rsCons("HSCode") &"' AND (Type = 'GASOLINE' OR Type = 'DIESEL' OR Type = 'KEROSENE')"
	rstFMF.CursorType = 0
	rstFMF.CursorLocation = 2
	rstFMF.LockType = 3
	rstFMF.Open()
	rstFMF_numRows = 0
	
	if NOT rstFMF.EOF then
		FMFTAX = rstFMF("Type")
		FMFRate = rstFMF("Rate")
	else
		FMFTAX = ""
		FMFRate = 0
	end if
	
	if UCase(FMFTAX) = "GASOLINE" OR UCase(FMFTAX) = "DIESEL" OR UCase(FMFTAX) = "KEROSENE" then
		if rsCons("HSCode") = "27101211" AND rsCons("HSCode_TAR") = "100" then
			FMFTAXES = rsCons("SupVal1") * 1 * 0.06146428571
			FMFRate = 1
			FMFQty = rsCons("SupVal1")
		else
			FMFTAXES = rsCons("SupVal1") * rstFMF("Rate") * 0.06146428571
			FMFRate = exciseRate
			FMFQty = rsCons("SupVal1")
		end if
	else
		FMFTAXES = 0
		FMFRate = 0
		FMFQty = 0
	end if
	
	FUELTAX = FMFTAXES
	if FUELTAX <> "" then
		Response.write ("<br> FMF = " & formatnumber(FUELTAX,2) & " ")
	end if
	rstFMF.Close()
	
	'Compute SGL Fee
	if Session("cltcode") = "DSPEPANIOA" OR Session("cltcode") = "P8SL180605" then
		if MODEDEC1__MMColParam = "4ES" then
			if TOTALVALUE < 5001 then
				Response.write ("<br> SGL Fee = 500.00 ")
			elseif TOTALVALUE >= 5001 AND TOTALVALUE < 100001 then
				Response.write ("<br> SGL Fee = 1000.00 ")
			elseif TOTALVALUE >= 100001 AND TOTALVALUE < 200001 then
				Response.write ("<br> SGL Fee = 1500.00 ")
			elseif TOTALVALUE >= 200001 AND TOTALVALUE < 500001 then
				Response.write ("<br> SGL Fee = 2000.00 ")
			elseif TOTALVALUE >= 500001 then
				Response.write ("<br> SGL Fee = 2500.00 ")
			end if
		end if
	end if
	
	'Display the Duty Value on the Page
 	'Response.Write(FormatNumber((cDbl(DVAL)), 2, -2, -2, -2))
    TOTALDVAL = cdbl(TOTALDVAL) + cdbl(DVAL)
    'TOTALDVAL = cdbl(TOTALDVAL) + Round(cdbl(DVAL),2)
	TotalExciseTax = cdbl(TotalExciseTax) + cdbl(ExcTax)
	TotalFMFTax = cdbl(TotalFMFTax) + cdbl(FUELTAX)

Else
	Response.Write("0")
End If

Dim strHSCode1
Dim strHSCode2
Dim strTARPR2
Dim TarRate
Dim PrefRate

strHSCode1 = Mid((rsCons.Fields.Item("HSCode").Value), 1, 6)
strHSCode2 = Mid((rsCons.Fields.Item("HSCode").Value), 7, 2)
strTARPR2 = rsCons.Fields.item("HSCODE_TAR")
PrefRate = rsCons.Fields.item("Pref")

set rsTar = Server.CreateObject("ADODB.Recordset")
rsTar.ActiveConnection = constrCOMINScd
rsTar.Source = "Select *, Isnull(Tar_t01,'') as Tar_t01, Isnull(Tar_t02,'') as Tar_t02, Isnull(Tar_t03,'') as Tar_t03, Isnull(Tar_t05,'') as Tar_t05, Isnull(Tar_t06,'') as Tar_t06, Isnull(Tar_t07,'') as Tar_t07, Isnull(Tar_t08,'') as Tar_t08, Isnull(Tar_t09,'') as Tar_t09, Isnull(Tar_t10,'') as Tar_t10, Isnull(Tar_t11,'') as Tar_t11, Isnull(Tar_t12,'') as Tar_t12, Isnull(Tar_t13,'') as Tar_t13, Isnull(Tar_t14,'') as Tar_t14 from GBTARTAB where Hs6_cod='" & strHSCode1 & "' and tar_pr1='" & strHSCode2 & "' and tar_pr2='" & strTARPR2 & "'"
rsTar.CursorType = 1
rsTar.CursorLocation = 3
rsTar.LockType = 3
rsTar.Open()
rsTar_numRows = 0

if PrefRate = "" or PrefRate = "None" or Prefrate = "NONE" then
			if NOT rsTar.EOF then
				if rsTar("Tar_t01") <> "" and NOT IsNull(rsTar("Tar_t01")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t01").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t01").Value)
		end if
		if Prefrate = "AFTA" OR Prefrate = "ATIGA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t02") <> "" and NOT IsNull(rsTar("Tar_t02")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t02").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t02").Value)
		end if
		if Prefrate = "AKFTA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t03") <> "" and NOT IsNull(rsTar("Tar_t03")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t03").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t03").Value)
		end if

		if Prefrate = "TAR_T04" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t04") <> "" and NOT IsNull(rsTar("Tar_t04")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t04").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t03").Value)
		end if

		if Prefrate = "BOI" then
			if NOT rsTar.EOF then
				if rsTar("Tar_t05") <> "" and NOT IsNull(rsTar("Tar_t05")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t05").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t05").Value)
		end if

		if Prefrate = "JPEPA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t06") <> "" and NOT IsNull(rsTar("Tar_t06")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t06").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t06").Value)
		end if

		if Prefrate = "EFTA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t07") <> "" and NOT IsNull(rsTar("Tar_t07")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t07").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t07").Value)
		end if

		' Carie: CRF - Formal Entry Lodgement Enhancement v1.0 - cltcode: FEDEX,  mded: Start at 4, IED-4, IE-4 ,5A-A 
		if PrefRate = "AHKFTA" then	'Prefrate = "AICO" OR	
			if NOT rsTar.EOF then
				if rsTar("Tar_t08") <> "" and NOT IsNull(rsTar("Tar_t08")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t08").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			' Response.Write(rsTar.Fields.Item("Tar_t08").Value)
		end if

		if Prefrate = "EFTANO" then 'Prefrate = "AICOB" or 
			if NOT rsTar.EOF then
				if rsTar("Tar_t09") <> "" and NOT IsNull(rsTar("Tar_t09")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t09").Value)
				else
					TarRate = ""
				end if	
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t09").Value)
		end if

		if Prefrate = "AIFTA" then	'Prefrate = "AICOC" OR 
			if NOT rsTar.EOF then
				if rsTar("Tar_t10") <> "" and NOT IsNull(rsTar("Tar_t10")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t10").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t10").Value)
		end if

		if Prefrate = "AJCEP" then	'Prefrate = "AISP" OR 
			if NOT rsTar.EOF then
				if rsTar("Tar_t11") <> "" and NOT IsNull(rsTar("Tar_t11")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t11").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t11").Value)
		end if

		if Prefrate = "EFTACL" then	'Prefrate = "AICOD" or 
			if NOT rsTar.EOF then
				if rsTar("Tar_t12") <> "" and NOT IsNull(rsTar("Tar_t12")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t12").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t12").Value)
		end if

		if Prefrate = "ACFTA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t13") <> "" and NOT IsNull(rsTar("Tar_t13")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t13").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t13").Value)
		end if

		if Prefrate = "ANFTA" then	'Prefrate = "AICOE" OR 
			if NOT rsTar.EOF then
				if rsTar("Tar_t14") <> "" and NOT IsNull(rsTar("Tar_t14")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t14").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t14").Value)
		end if

		if Prefrate = "TAR_T15" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t15") <> "" and NOT IsNull(rsTar("Tar_t15")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t15").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t03").Value)
		end if

        'Spagara: 06252023: additional Preference 	
        if Prefrate = "RCEP" then		
	        if NOT rsTar.EOF then	
		        if rsTar("Tar_t16") <> "" and NOT IsNull(rsTar("Tar_t16")) then	
			        TarRate = cDbl(rsTar.Fields.Item("Tar_t16").Value)	
		        else	
			        TarRate = ""	
		        end if	
	        else	
		        TarRate = ""	
	        end if	
	        'Response.Write(rsTar.Fields.Item("Tar_t03").Value)	
        end if	
        if Prefrate = "RCEPAUNZ" then		
	        if NOT rsTar.EOF then	
		        if rsTar("Tar_t17") <> "" and NOT IsNull(rsTar("Tar_t17")) then	
			        TarRate = cDbl(rsTar.Fields.Item("Tar_t17").Value)	
		        else	
			        TarRate = ""	
		        end if	
	        else	
		        TarRate = ""	
	        end if	
	        'Response.Write(rsTar.Fields.Item("Tar_t03").Value)	
        end if	
        if Prefrate = "RCEPCN" then		
	        if NOT rsTar.EOF then	
		        if rsTar("Tar_t18") <> "" and NOT IsNull(rsTar("Tar_t18")) then	
			        TarRate = cDbl(rsTar.Fields.Item("Tar_t18").Value)	
		        else	
			        TarRate = ""	
		        end if	
	        else	
		        TarRate = ""	
	        end if	
	        'Response.Write(rsTar.Fields.Item("Tar_t03").Value)	
        end if	
        if Prefrate = "RCEPJP" then		
	        if NOT rsTar.EOF then	
		        if rsTar("Tar_t19") <> "" and NOT IsNull(rsTar("Tar_t19")) then	
			        TarRate = cDbl(rsTar.Fields.Item("Tar_t19").Value)	
		        else	
			        TarRate = ""	
		        end if	
	        else	
		        TarRate = ""	
	        end if	
	        'Response.Write(rsTar.Fields.Item("Tar_t03").Value)	
        end if	
        if Prefrate = "RCEPKR" then		
	        if NOT rsTar.EOF then	
		        if rsTar("Tar_t20") <> "" and NOT IsNull(rsTar("Tar_t20")) then	
			        TarRate = cDbl(rsTar.Fields.Item("Tar_t20").Value)	
		        else	
			        TarRate = ""	
		        end if	
	        else	
		        TarRate = ""	
	        end if	
	        'Response.Write(rsTar.Fields.Item("Tar_t03").Value)	
        end if
		'Carie 01022025: BOC Deployment for January 1, 2025 - Customs Memorandum Circular Relative to Executive Order No. 80, Implementing the Philippines-Korea Free Trade Agreement
		if Prefrate = "PHKRFTA" OR Prefrate = "PHKFTA" then	
			if NOT rsTar.EOF then
				if rsTar("Tar_t21") <> "" and NOT IsNull(rsTar("Tar_t21")) then
					TarRate = cDbl(rsTar.Fields.Item("Tar_t21").Value)
				else
					TarRate = ""
				end if
			else
				TarRate = ""
			end if
			'Response.Write(rsTar.Fields.Item("Tar_t03").Value)
		end if
If TarRate <> "" then
	ITEMCUD = cDbl(DVAL) * (cDbl(cDbl(TarRate) / 100))
	ITEMCUDdesc = ITEMCUD
	TotCUD = cDbl(TotCUD + round(cDbl(ITEMCUD),2))
	TCUD = ""
	'Display the Item CUD on the Page
	'Response.Write(Round(FormatNumber((cDbl(ITEMCUD)), 2, -2, -2, -2),0))
else
	'ITEMCUDdesc = "Preference has No Rate from BOC table"
	ITEMCUDdesc = "Note: If you send this entry this will be computed to MFN rate/regular rate"
	ITEMCUD = 0
	TotCUD = round(cDbl(TotCUD),2)
	TCUD = "NO"
	'Response.Write ITEMCUD
end if

If strFCurr <> "PHP" Then
	iFrghtTOT = intFCost * cDbl(strRate)
else
	iFrghtTOT = intFCost
end if

If strICurr <> "PHP" Then
	iINStot = strInsCost * cDbl(strRate)
else
	iINStot = strInsCost
end if

If strOCurr <> "PHP" Then
	iOCSTtot = strOCost * cDbl(strRate)
else
	iOCSTtot = strOCost
end if

If strDVCurr <> "PHP" Then
   'DVALtot = Round((cDbl(TOTALVALUE) * cDbl(strRate)),0) + Round(cDbl(iFrghtTOT),0) + Round(cDbl(iINStot),0) + Round(cDbl(iOCSTtot),0)
    DVALtot = (cDbl(TOTALVALUE) * cDbl(strRate)) + cDbl(iFrghtTOT) + cDbl(iINStot) + cDbl(iOCSTtot)
Else
   'DVALtot = Round(cDbl(TOTALVALUE),0) + Round(cDbl(iFrghtTOT),0) + Round(cDbl(iINStot),0) + Round(cDbl(iOCSTtot),0)
   DVALtot = cDbl(TOTALVALUE) + cDbl(iFrghtTOT) + cDbl(iINStot) + cDbl(iOCSTtot)
End If

Dim IBANK
If rsCons.RecordCount > 0 then
	If rsFin.RecordCount > 0 then
		If rsFIN.Fields.Item("WOBankCharge").Value = True Then
			IBANK = 0                      '---- if no bank is involved in payment
		Else
			'---- Itemized Bank Charges = (Total Dutiable Value x 0.00125) / Total Number of Items
			IBANK = (DVALtot * 0.00125) '/ TOTALITEM
		End If
		IBANK = Round(IBANK, 1)
	End If
End If

IBankperItem = (ITEMVALUE / TOTALVALUE) * IBANK
'response.write "<br>Bank Charge= " & formatnumber(IBankperItem, 2)

set recset1 = Server.CreateObject("ADODB.Recordset")
recset1.ActiveConnection = constrCOMINScdV
recset1.Source = "Select * from dbo.ITMBRKFEE WHERE (NOT (DUTMIN IS NULL)) order by DUTMIN"
recset1.CursorType = 1
recset1.CursorLocation = 3
recset1.LockType = 3
recset1.Open()
recset1_numRows = 0

If recset1.RecordCount > 0 then
	'---- Get Brokerage fee
	If cDbl(DVALtot) > 200000 Then             
	'---- if total dutiable value is greater than 200,000.00 ||| replaced TotDuty
		'IBROKE = ((cDbl(TotDuty) - 200000) * 0.00125) + 3543.75
		recset1.MoveLast
		IBROKE = ((cDbl(DVALtot) - 200000) * 0.00125) + cDbl(recset1.Fields.Item("BrokFee").Value)
		'response.write IBROKE
	Else
		Do While Not recset1.EOF         '---- check for item brokerage fee in the brokerage fee table
			'response.write TOTALDVAL
			'response.write cdbl(Round(TotDuty)) & "|||" & recset1("DUTMIN") & " | " & recset1("DUTMAX")
			If cdbl(DVALtot) >= cdbl(recset1.Fields.Item("DUTMIN").Value) AND cdbl(DVALtot) <= cdbl(recset1.Fields.Item("DUTMAX").Value) Then
				IBROKE = cDbl(recset1.Fields.Item("BrokFee").Value)
				'response.write TOTALDVAL
			End If
			recset1.MoveNext
		Loop
	End If
End If

'LObligado 12062022
if Session("mod_cod") = "IES" then
	IBROKE = 700
end if	  

    '06062024: SPagara: update on doc stamp
	if Session("mod_cod") = "IES" then
		'DocStampperItem = 130 / rsCons.RecordCount
        DocStampperItem = 100 / rsCons.RecordCount
	else
		'DocStampperItem = 280 / rsCons.RecordCount
        DocStampperItem = 100 / rsCons.RecordCount
	end if
'response.write "<br>Broker Fee= " & formatnumber(IBROKEperItem, 2)

DocStampperItem = 130 / rsCons.RecordCount
'response.write "<br>Doc Stamp= " & formatnumber(DocStampperItem, 2) 

'---5/3/2000 to user dynamic value of Declaration mode IPF and Documentary stamp
If Mid(Session("mod_cod"), 1, 1) = "4" Then
	'DtaBroke.RecordSource = "Select * from IMPPROCFEE order by DUTMIN"
	'DtaBroke.Refresh
	set rsBroke = Server.CreateObject("ADODB.Recordset")
	rsBroke.ActiveConnection = constrCOMINScd
	rsBroke.Source = "Select * from dbo.IMPPROCFEE WHERE (NOT (DUTMIN IS NULL)) order by DUTMIN"
	rsBroke.CursorType = 1
	rsBroke.CursorLocation = 3
	rsBroke.LockType = 3
	rsBroke.Open()
	rsBroke_numRows = 0

	Do While Not rsBroke.EOF
		If DVALtot >= rsBroke.Fields.Item("DUTMIN").Value And DVALtot <= rsBroke.Fields.Item("DUTMAX").Value Then
			dblIpf = rsBroke.Fields.Item("IPF").Value
		End If
		rsBroke.MoveNext
	Loop
Else
    If CDbl(DVALtot) >= 0 And CDbl(DVALtot) <= 25000 Then
        dblIpf = 250
    ElseIf CDbl(DVALtot) > 25000 And CDbl(DVALtot) <= 50000 Then
        dblIpf = 500
    ElseIf CDbl(DVALtot) > 50000 And CDbl(DVALtot) <= 250000 Then
        dblIpf = 750
    ElseIf CDbl(DVALtot) > 250000 And CDbl(DVALtot) <= 500000 Then
        dblIpf = 1000
    ElseIf CDbl(DVALtot) > 500000 And CDbl(DVALtot) <= 750000 Then
        dblIpf = 1500
    ElseIf CDbl(DVALtot) > 750000 Then
        dblIpf = 2000
    End If
End If

'06062024: Spagara: Update for IPF
'if Session("mod_cod") = "IES" then
'	IPFperIem = 0
'	dblIpf = 0
'else	
	IPFperIem = dblIpf / rsCons.RecordCount
'end if
'response.write "<br>IPF= " & formatnumber(IPFperIem, 2)

If Session("cltcode") <> "SKYFREIGHTTEST" and Session("cltcode") <> "FEDEX" then							
ArrasCostperIem = (ITEMVALUE / TOTALVALUE) * rsFin("ArrasCost")
'response.write "<br>Arrastre= " & formatnumber(ArrasCostperIem, 2)

WharCostperIem = (ITEMVALUE / TOTALVALUE) * rsFin("WharCost")
'response.write "<br>Wharfage= " & formatnumber(WharCostperIem, 2)
end if	  

'LandedCost = TOTALDVAL + TotCUD + IBANK + IBROKE + TotArras + TotWhar + DocStamp__MMColParam + dblIpf
LandedCostPerItem = DVAL + ITEMCUD + IBankperItem + IBROKEperItem + ArrasCostperIem + WharCostperIem + DocStampperItem + IPFperIem
'response.write "<br>Landed Cost= " & formatnumber(LandedCostPerItem, 2)

'if EXTAX = "AUTOMOBILE" then
'	if rsCons("TarSpec") = "" then
'		AVTRate = 0.50
'	elseif rsCons("TarSpec") = "1003" then
'		AVTRate = 0.20
'	elseif rsCons("TarSpec") = "1002" then
'		AVTRate = 0.10
'	elseif rsCons("TarSpec") = "1001" then
'		AVTRate = 0.04
'	else
'		AVTRate = 1
'	end if
'end if
set rsGBTARTAB = Server.CreateObject("ADODB.Recordset")
rsGBTARTAB.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=PL-INSCUSTSTDB;Uid=sa;pwd=df0rc3;"
rsGBTARTAB.Source = "SELECT rul_cod FROM GBTARTAB WHERE hs6_cod+tar_pr1='" & rsCons("HSCode") & "' AND tar_pr2='" & rsCons("HSCODE_TAR") & "'"
rsGBTARTAB.CursorType = 0
rsGBTARTAB.CursorLocation = 2
rsGBTARTAB.LockType = 3
rsGBTARTAB.Open()

if NOT rsGBTARTAB.EOF then
	If rsGBTARTAB("rul_cod") = "AVT-AUTO" AND rsCons("MSP") <> "" then
		if rsCons("MSP") <= 600000 then
			AVTRate = 0.04
		elseif rsCons("MSP") > 600000 AND rsCons("MSP") <= 1000000 then
			AVTRate = 0.10
		elseif rsCons("MSP") > 1000000 AND rsCons("MSP") <= 4000000 then
			AVTRate = 0.20
		elseif rsCons("MSP") > 4000000 then
			AVTRate = 0.50
		end if
	elseif rsGBTARTAB("rul_cod") = "AVT_HYBRID" AND rsCons("MSP") <> "" then
		if rsCons("MSP") <= 600000 then
			AVTRate = 0.02
		elseif rsCons("MSP") > 600000 AND rsCons("MSP") <= 1000000 then
			AVTRate = 0.05
		elseif rsCons("MSP") > 1000000 AND rsCons("MSP") <= 4000000 then
			AVTRate = 0.10
		elseif rsCons("MSP") > 4000000 then
			AVTRate = 0.25
		end if
	end if
end if
if rsCons("MSP") <> "" then
	AVTPerItem = ((rsCons("MSP") * AVTRate) * rsCons("SupVal1"))
else
	'AVTPerItem = LandedCostPerItem * AVTRate
	AVTPerItem = 0
end if
'if cdbl(AVTPerItem) <> 0 then
	response.write "<br>AVT= " & formatnumber(AVTPerItem, 2)
'end if

AVTCost = AVTCost + AVTPerItem
TotalFINTax = TotalFINTax + rsCons("SupUnit3")									
%>
                                  </div>
                                </td>
                                <td class="body" height="25"> 
                                  <div align="center"> 
<%if TarRate <> "" then
	response.write TarRate
else
	response.write "<font color='red'>Preference has No Rate from BOC table</font>"
end if
%>
                                  </div>
                                </td>
                                <td class="body" height="25"> 
                                  <div align="center"> 
<% If TarRate <> "" then
	'ITEMCUD = cDbl(DVAL) * (cDbl(cDbl(TarRate) / 100))
	'TotCUD = cDbl(TotCUD + round(cDbl(ITEMCUD),0))
	'TCUD = ""
	'Display the Item CUD on the Page
	Response.Write(Round(FormatNumber((cDbl(ITEMCUDdesc)), 2, -2, -2, -2),0))
else
	'ITEMCUD = "Preference has No Rate from BOC table"
	'TotCUD = cDbl(TotCUD)
	'TCUD = "NO"
	Response.Write "<font color='red'>" & ITEMCUDdesc & "</font>"
end if

If Not IsNull(rsCons.Fields.Item("ExciseTotal").Value) AND  IsNull(rsCons.Fields.Item("MSP").Value) Then
	curTotalExcise = curTotalExcise + rsCons.Fields.Item("ExciseTotal").Value
End If%>
                                  </div>
                                </td>
                              </tr>
                              <% 
  Repeat1__index=Repeat1__index+1
  Repeat1__numRows=Repeat1__numRows-1
  rsCons.MoveNext()
Wend
TotExcisTax = TotalExciseTax * 0.12
%>
                            </table>
                          </td>
                        </tr>
                      </table>
                      <br>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFCC66">
                        <tr> 
                          <td bgcolor="#666666"> 
                            <table width="100%" border="0" cellspacing="1">
                              <tr bgcolor="#FF6600"> 
                                <td width="100" bgcolor="#999999" class="body" height="40"> 
                                  <div align="right"><font color="#FFFFFF">Brokerage Fee </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  <%
'#### IMPORTANT ####
'Get the value of BrkTin from the Main Module; Sample data = '99999999999'
'Function CompTotBrkFee(BrkTin As String, ByVal ApplNum As String, ByVal ExchRt As Double) As Double
'VBScript -> Function CompTotBrkFee('999999999', rsCons__MMColParam, strRate)

'Dim dtb1, dtb2 As Database
'Dim recset1, recset2 As Recordset

Dim TotDuty, ITMVAL, TotItmVal, Frt, Ins, Oth
Dim FC, IC, OC
Dim bFrt, bOth
TotDuty = 0

    If BrokerTIN__MMColParam = "99999999999" Then           '---- if in house broker
        IBROKE = 0
    Else
		set rsDetTot = Server.CreateObject("ADODB.Recordset")
		rsDetTot.ActiveConnection = constrCOMINScd
		rsDetTot.Source = "Select Sum(InvValue) as TOTALVALUE from TBLIMPAPL_DETAIL where APPLNO='" + Replace(rsCons__MMColParam, "'", "''") + "'"
		rsDetTot.CursorType = 1
		rsDetTot.CursorLocation = 3
		rsDetTot.LockType = 3
		rsDetTot.Open()
		rsDetTot_numRows = 0
		TotItmVal = rsDetTot.Fields.Item("TOTALVALUE").Value

		set recset1 = Server.CreateObject("ADODB.Recordset")
		recset1.ActiveConnection = constrCOMINScd
		recset1.Source = "Select * from TBLIMPAPL_DETAIL where Applno = '" + Replace(rsCons__MMColParam, "'", "''") + "'"
		recset1.CursorType = 1
		recset1.CursorLocation = 3
		recset1.LockType = 3
		recset1.Open()
		recset1_numRows = 0
If recset1.RecordCount > 0 then
		set recset2 = Server.CreateObject("ADODB.Recordset")
		recset2.ActiveConnection = constrCOMINScd
		recset2.Source = "Select * from TBLIMPAPL_FIN where Applno = '" + Replace(rsCons__MMColParam, "'", "''") + "'"
		recset2.CursorType = 1
		recset2.CursorLocation = 3
		recset2.LockType = 3
		recset2.Open()
		recset2_numRows = 0
If recset2.RecordCount > 0 then
		if Trim("" & recset2.Fields.Item("FreightCost").Value) = "" then
			Frt = 0
		Else
			Frt = CDbl(recset2.Fields.Item("FreightCost").Value)
		End If
		if Trim("" & recset2.Fields.Item("InsCost").Value) = "" then
			Ins = 0
		Else
			Ins = CDbl(recset2.Fields.Item("InsCost").Value)
		End If   
		if Trim("" & recset2.Fields.Item("OtherCost").Value) = "" then
			Oth = 0
		Else
			Oth = CDbl(recset2.Fields.Item("OtherCost").Value)
		End If      
        Do While Not recset1.EOF
            ITMVAL = CDbl(recset1.Fields.Item("invvalue").Value)
            bFrt = recset1.Fields.Item("IFREIGHT").Value
            bOth = recset1.Fields.Item("OCharges").Value
			'COMPUTE FREIGHT CHARGES
            'FC = CompFreight(Frt, ExchRt, ITMVAL, TotItmVal)
    		If Frt <> 0 Then
       			If strFCurr <> "PHP" Then
          			FC = (cDbl(Frt) * cDbl(strRate)) * (cDbl(ITMVAL) / cDbl(TotItmVal))
       			Else
         			FC = cDbl(Frt) * (cDbl(ITMVAL) / cDbl(TotItmVal))
       			End If
    		End If
			'COMPUTE INSURANCE CHARGES
            'IC = CompIns(bFrt, Ins, ExchRt, ITMVAL, TotItmVal)
			Dim C1
			Dim C2
			'---- Do not multiply insurance value to exchange rate if value is in peso
    		If bFrt Then
        		IC = 0                   '---- if INSinFRT is set to 1
    		Else
        		If Ins = 0 Then
            		'---- Itemized Insurance = Item Value x Exchange Rate x 0.04
            		If strICurr <> "PHP" Then
               			IC = cDbl(ITMVAL) * cDbl(strRate) * 0.04
            		Else
               			IC = cDbl(ITMVAL) * 0.04
            		End If
        		Else                          '---- if with documents get value of which ever is higher
            		'---- Itemized Insurance = Total Insurance x Insurance Exchange Rate x (Item Value/Total Value)
            		If strICurr <> "PHP" Then
               			C1 = cDbl(Ins) * cDbl(strRate) * (cDbl(ITMVAL) / cDbl(TotItmVal))
            		Else
               			C1 = cDbl(Ins) * (cDbl(ITMVAL) / cDbl(TotItmVal))
            		End If
            		'---- Itemized Insurance = Item Value x Exchange Rate x 0
            		If strICurr <> "PHP" Then
               			C2 = cDbl(ITMVAL) * cDbl(strRate) * 0
            		Else
               			C2 = cDbl(ITMVAL) * 0
            		End If
            		'IC = IIf(C1 > C2, C1, C2)
					If C1 > C2 then
						IC = C1
					Else
						IC = C2
					End If
        		End If
    		End If
			'COMPUTE OTHER CHARGES
            'OC = CompOth(bOth, Oth, ExchRt, ITMVAL, TotItmVal)
			'----Do not multiply other cost to exchange rate if value is in peso
    		If bOth Then
        		OC = 0                    '--- if ETHinEV is set to 0
    		Else
        		If Oth = 0 Then                '--- if other charge is not declared then ETHinEV is set to 0
            		'---- Itemized Other Charges = (Item Value x Exchange Rate) x 0.03
            		If strOCurr <> "PHP" Then
               			OC = cDbl(ITMVAL) * cDbl(strRate) * 0.03
            		Else
               			OC = cDbl(ITMVAL) * 0.03
            		End If
        		Else                           '--- if ETHinEV is not set to 1
            		'---- Itemized Other Charges = Total Other Charges x Other Charges Exchange Rate x (Item Value / Total Value)
            		If strOCurr <> "PHP" Then
               			OC = Oth * cDbl(strRate) * (cDbl(ITMVAL) / cDbl(TotItmVal))
            		Else
               			OC = Oth * (cDbl(ITMVAL) / cDbl(TotItmVal))
            		End If
        		End If
    		End If

            'TotDuty = TotDuty + CompDutyVal(ITMVAL, ExchRt, FC, IC, OC)
    		'---- Dutiable Value = Item Value x Exchange Rate + (Itemized Freight + Itemized Insurance + Itemized Other Charges)
    		'---- Do not multiply item value to exchange rate if value is in peso
    		If strDVCurr <> "PHP" Then
       			TotDuty = RoundUp(TotDuty + cDbl(ITMVAL) * cDbl(strRate) + Int(FC + IC + OC))
                'TotDuty = TotDuty + cDbl(ITMVAL) * cDbl(strRate) + Int(FC + IC + OC)
    		Else
       			TotDuty = TotDuty + cDbl(ITMVAL) + Int(FC + IC + OC)
    		End If
            recset1.MoveNext
        Loop

End If
End If
		
    End If
%>
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtBFee" value="<%=(FormatNumber((IBROKE), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font></font><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#000000"></font></td>
                                <td width="100" bgcolor="#999999" class="body" height="40"> 
                                  <div align="right"><font color="#FFFFFF">Total CUD </font></div>
                                </td>
                                <td width="35%" bgcolor="f7f7f7" height="40"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  <% TotCUD = TotCUD 
								  
									'Response.Write(TotCUD)
									' CARIE 05242023: Tariff Spec/AI code for IES-4 Enhancement
									' if TCUD = "NO" then
									'	TotCUD = 0
									' Carie: CRF - Formal Entry Lodgement Enhancement v1.0 - cltcode: FEDEX,  mded: Start at 4, IED-4, IE-4 ,5A-A 
									if TCUD = "" then
										TotCUD = (FormatNumber((TotCUD), 2, -2, -2, -2))
									else
										TotCUD = 0
									end if%>
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtTCUD" value="<%=TotCUD%>" disabled="True" align="right">
                                  </font></font><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#000000"></font></td>
                              </tr>
                              <tr bgcolor="#FF6600"> 
                                <td width="100" bgcolor="#999999" class="body" height="40"> 
                                  <div align="right"><font color="#FFFFFF">Bank Charges </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtBChrg" value="<%=(FormatNumber((cDbl(IBANK)), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font></font><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#000000"></font></td>
                                <td width="100" bgcolor="#999999" class="body" height="40"> 
                                  <div align="right"><font color="#FFFFFF">Total VAT </font></div>
                                </td>
                                <td width="35%" bgcolor="f7f7f7" height="40"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  <%
	Dim Proc
	Dim GrsWt
	Dim IVAL
If rsCons.RecordCount > 0 then
If rsFin.RecordCount > 0 then
	' Arrastre and Wharfage
    TotArras = rsFin.Fields.Item("ArrasCost").Value
    TotWhar = rsFin.Fields.Item("WharCost").Value

	set rsVAT = Server.CreateObject("ADODB.Recordset")
	rsVAT.ActiveConnection = constrCOMINScd
	rsVAT.Source = "SELECT * FROM dbo.TBLIMPAPL_CONS WHERE ApplNo = '" + Replace(rsCons__MMColParam, "'", "''") + "'"
	rsVAT.CursorType = 1
	rsVAT.CursorLocation = 3
	rsVAT.LockType = 3
	rsVAT.Open()
	rsVAT_numRows = 0
	
    TOTARRAS4 = 0
    TOTWHAR4 = 0
    Do Until rsVAT.EOF
		Proc = rsVAT.Fields.Item("ExtCode").Value
		GrsWt = rsVAT.Fields.Item("ItemGweight").Value
		IVAL = rsVAT.Fields.Item("InvValue").Value

       	'TOTARRAS4 = TOTARRAS4 + CompArras(rsVAT.Fields.Item("ProdDesc").Value, rsVAT.Fields.Item("ItemGweight").Value, 
		'TotArras, rsVAT.Fields.Item("InvValue"), TOTALVALUE)
		Dim A1
		Dim A2 
    	A1 = 0
    	If Mid(Proc, 3, 1) = "1" Then         '---- if shipside loading
        		'---- Itemized Arrastre Charges = 7.8 x Item Gross Weight/1000
        		A1 = (8 * cDbl(GrsWt)) / 1000
    	Else
        	If Mid(Proc, 3, 1) = "0" Then           '---- if pierside loading
            	'---- Itemized Arrastre Charges = 100 x Item Gross Weight/1000
            	'A1 = (110 * Val(Format(Val(GrsWt), "###0.00"))) / 1000
				A1 = (110 * cDbl(GrsWt)) / 1000
        	End If
    	End If
    	'---- Itemized Arrastre Charges = Arrastre Charges x (Item Value/Total Value)
		If Trim(TotArras) = "" Or IsNull(TotArras) = True then TotArras = 0
		If Trim(IVAL) = "" Or IsNull(IVAL) = True then IVAL = 0
		If Trim(TOTALVALUE) = "" Or IsNull(TOTALVALUE) = True then TOTALVALUE = 0
    	If cDbl(TotArras) <> 0 And cDbl(IVAL) <> 0 And cDbl(TOTALVALUE) <> 0 Then
       		A2 = cDbl(TotArras) * (cDbl(IVAL) / cDbl(TOTALVALUE))
    	Else
       		A2 = 0
    	End If
    	'---- get arrastre which ever is higher
		'TOTARRAS4 = IIf(A1 > A2, A1, A2)
		If A1 > A2 then
			TOTARRAS4 = TOTARRAS4 + A1
		Else
			TOTARRAS4 = TOTARRAS4 + A2
		End If
       
		'TOTWHAR4 = TOTWHAR4 + CompWhar(DtaApp.Recordset("procedure"), DtaApp.Recordset("ItemGweight"),
		'TotWhar, DtaApp.Recordset("InvValue"), TOTALVALUE)
		Dim W1
		Dim W2
    	W1 = 0
    	If Mid(Proc, 3, 1) = "1" Then             '---- if shipside loading
        	'---- Itemized Wharfage Fee = 17.00 x Item Gross Weight / 1000
        	'W1 = (17 * Val(Format(GrsWt, "###0.00"))) / 1000
			W1 = (17 * cDbl(GrsWt)) / 1000
    	Else
        	If Mid(Proc, 3, 1) = "0" Then         '---- if pierside loading
            	'---- Itemized Wharfage Fee = 34.00 x Item Gross Weight / 1000
            	'W1 = (34 * Val(Format(GrsWt, "###0.00"))) / 1000
				W1 = (34 * CDbl(GrsWt)) / 1000
        	End If
    	End If
    	'---- Itemized Wharfage Fee = Wharfage Fee x (Item Value/Total Value)
		If Trim(TotWhar) = "" Or IsNull(TotWhar) = True then TotWhar = 0
    	If cDbl(TotWhar) <> 0 And cDbl(IVAL) <> 0 And cDbl(TOTALVALUE) <> 0 Then
       		'W2 = TotWhar * (ITMVAL / TOTALVALUE)
			W2 = cDbl(TotWhar) * (cDbl(IVAL) / cDbl(TOTALVALUE))
    	Else
       		W2 = 0
    	End If

    	'--- get wharfage fee which ever is higher
    	'CompWhar = IIf(W1 > W2, W1, W2)
		If W1 > W2 then
			TOTWHAR4 = TOTWHAR4 + W1
		Else
			TOTWHAR4 = TOTWHAR4 + W2
		End If

		rsVAT.MoveNext
    Loop
	
	'Vat = ((TOTALDVAL + TotCUD + curTotalExcise + IBROKE + IBANK + TOTARRAS4 + TOTWHAR4 + FrmImp_Open.dblIpf + 
	'FrmImp_Open.dblDocStamp) / 10)    '----CSD COMPUTATION NOT YET INCLUDED
	'####IMPORTANT####
	'Get the value of FrmImp_Open.dblDocStamp	; Sample data = '265'

	'VAT = ((cDbl(TOTALDVAL) + cDbl(TotCUD) + cDbl(curTotalExcise) + cDbl(IBROKE) + cDbl(IBANK) + cDbl(TOTARRAS4) + cDbl(TOTWHAR4) + cDbl(IPF__MMColParam) + cDbl(DocStamp__MMColParam)) / 10)

	If strOFFCLEAR = "P03" or strOFFCLEAR = "P14" or strOFFCLEAR = "P07B" then
		TOTARRAS4 = 0
		TOTWHAR4 = 0
	end if 
	'response.write (TOTALDVAL) & " | " & (TotCUD) & " | " & (curTotalExcise) & " | " & (IBROKE) & " | " & (IBANK) & " | " & (TOTARRAS4) & " | " & (TOTWHAR4) & " | " & (dblIpf) & " | " & (DocStamp__MMColParam)
    VAT = ((cDbl(AVTcost) + cDbl(TOTALDVAL) + cDbl(TotCUD) + cDbl(curTotalExcise) + cDbl(IBROKE) + cDbl(IBANK) + cDbl(TOTARRAS4) + cDbl(TOTWHAR4) + cDbl(dblIpf) + cDbl(DocStamp__MMColParam)) * 0.12)
	'response.write "VAT = " & TOTALDVAL & " | " & TotCUD & " | " & curTotalExcise & " | " & IBROKE & " | " & IBANK & " | " & TOTARRAS4 & " | " & TOTWHAR4 & " | " & dblIpf & " | " & DocStamp__MMColParam
	VAT = Round(VAT, 2)
	
'Response.Write "AVTcost: " & AVTcost & "<br>"
'Response.Write "TOTALDVAL: " & TOTALDVAL & "<br>"
'Response.Write "TotCUD: " & TotCUD & "<br>"
'Response.Write "Excise: " & curTotalExcise & "<br>"
'Response.Write "Broker: " & IBROKE & "<br>"
'Response.Write "Bank: " & IBANK & "<br>"
'Response.Write "Arrastre: " & TOTARRAS4 & "<br>"
'Response.Write "Wharfage: " & TOTWHAR4 & "<br>"
'Response.Write "IPF: " & dblIpf & "<br>"
'Response.Write "DocStamp: " & DocStamp__MMColParam & "<br>"
'Response.Write "<b>VAT: " & VAT & "</b><br>"

'Response.End
End If
End If
		
If Session("cltcode") = "FEDEX" AND (Session("mod_cod") = "IES") then	
	TotDutiesNTaxes = TotCUD + VAT
ElseIf (isAspacClient = "YES") Then
	TotDutiesNTaxes = TotCUD + VAT
' Carie: CRF - Formal Entry Lodgement Enhancement v1.0 - cltcode: FEDEX,  mded: Start at 4, IED-4, IE-4 ,5A-A 	
ElseIf	Session("cltcode") = "FEDEX" AND (LEFT(Session("mod_cod"),1) = "4" OR UCase(Session("mod_cod")) = "5A" OR UCase(Session("mod_cod")) = "IE" OR UCase(Session("mod_cod")) = "IED") then	
	TotDutiesNTaxes = TotCUD + VAT + cDbl(dblIpf) + cDbl(DocStamp__MMColParam) + TotalExciseTax	
end if	
%>
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtTVAT" value="<%=(FormatNumber((VAT), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font></font><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#000000"></font></td>
                              </tr>
                              <tr bgcolor="#FF6600"> 
                                <td width="100" bgcolor="#999999" height="40" class="body"> 
                                  <div align="right"><font color="#FFFFFF"><%if Session("mod_cod") <> "IES" then%>CDS and <%end if%> IRS </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  <% DocStamp__MMColParam = cDbl(DocStamp__MMColParam) %>
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtDocStp" value="<%=(FormatNumber((DocStamp__MMColParam), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font></font><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#000000"></font></td>
                                <td width="100" bgcolor="#999999" height="40" class="body"> 
                                
                                  <div align="right"><font color="#FFFFFF">Total EXC Tax </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40">
                                <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtExcise" value="<%=(FormatNumber((TotalExciseTax), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font>
                                </font>
                                </td>
                              </tr>
                              <tr bgcolor="#FF6600"> 
                                <td width="100" bgcolor="#999999" height="40" class="body">
                                	<div align="right"><font color="#FFFFFF">Total AVT </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40">
                                <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtDocStp" value="<%=(FormatNumber((AVTCost), 2, -2, -2, -2))%>" disabled="True" align="right">
                                  </font>
                                </font>
                                </td>
                                <td width="100" bgcolor="#999999" height="40" class="body"> 
                                  <div align="right"><font color="#FFFFFF">Total FMF Tax </font></div>
                                </td>
                                <td width="32%" bgcolor="f7f7f7" height="40">
                                <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                                  <font color="#000000"> 
                                  &nbsp;&nbsp; 
                                  <input type="text" name="txtFMF" value="<%if TotalFMFTax > 0 then%>
								  <%=(FormatNumber((TotalFMFTax), 2, -2, -2, -2))%>
								  <%elseif TotalFINTax > 0 then%>
								  <%=(FormatNumber((TotalFINTax), 2, -2, -2, -2))%>
								  <%end if%>" disabled="True" align="right">
                                  </font>
                                </font>
                                </td>
                              </tr>
                              <%if TotalDpdTax > 0 then %>	
                               <tr bgcolor="#FF6600"> 	
                                <td width="100" bgcolor="#999999" height="40" class="body">	
                                	<div align="right"><font color="#FFFFFF">Total DPD Tax</font></div>	
                                </td>	
                                <td width="32%" bgcolor="f7f7f7" height="40">	
                                <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 	
                                  <font color="#000000"> 	
                                  &nbsp;&nbsp; 	
                                  <input type="text" name="txtDdp" value="<%=(FormatNumber((TotalDpdTax), 2, -2, -2, -2))%>" disabled="True" align="right">	
                                  </font>	
                                </font>	
                                </td>	
                                <td width="100" bgcolor="#999999" height="40" class="body"> 	
                                  <div align="right"><font color="#FFFFFF">&nbsp;</font></div>	
                                </td>	
                                <td width="32%" bgcolor="f7f7f7" height="40">	
                                <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 	
                                  <font color="#000000"> 	
                                  &nbsp;&nbsp; 	
                                  <input type="text" name="txtFMF" value="" disabled="True" align="right">	
                                  </font>	
                                </font>	
                                </td>	
                              </tr>	
                            <%end if %>
							  <!--	
							  LOblogado: 11292022 - Total Final Assessment Viewable in Pre-Assessment   	
                              Spagara: 06012023: Total Freight and Insurance 	
							  -->	
							  <%If Session("cltcode") = "FEDEX" then%>
                                <tr bgcolor="#FF6600"> 
									<td width="100" bgcolor="#999999" height="40" class="body">
										<div align="right"><font color="#FFFFFF">Total Freight </font></div>
									</td>
									<td width="32%" bgcolor="f7f7f7" height="40">
									<font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
									  <font color="#000000"> 
									  &nbsp;&nbsp; 
									  <input type="text" name="txtFreight" value="<%=(FormatNumber((iFrghtTOT), 2, -2, -2, -2))%>" disabled="True" align="right">
									  </font>
									</font>
									</td>
                                    <td width="100" bgcolor="#999999" height="40" class="body">
										<div align="right"><font color="#FFFFFF">Total Insurance </font></div>
									</td>
									<td width="32%" bgcolor="f7f7f7" height="40">
									<font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
									  <font color="#000000"> 
									  &nbsp;&nbsp; 
									  <input type="text" name="txtInsurance" value="<%=(FormatNumber((iINStot), 2, -2, -2, -2))%>" disabled="True" align="right">
									  </font>
									</font>
									</td>
								  </tr>
							<%End If%>
							<%If Session("cltcode") = "FEDEX" OR isAspacClient = "YES" then%>
								  <tr bgcolor="#FF6600"> 
									<td width="100" bgcolor="#999999" height="40" class="body">
										<div align="right"><font color="#FFFFFF">Duties and Taxes </font></div>
									</td>
									<td width="32%" bgcolor="f7f7f7" height="40">
									<font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
									  <font color="#000000"> 
									  &nbsp;&nbsp; 
									  <input type="text" name="txtDutiesTaxes" value="<%=(FormatNumber((TotDutiesNTaxes), 2, -2, -2, -2))%>" disabled="True" align="right">
									  </font>
									</font>
									</td>
								  </tr>
							<%End If%>
                            </table>
                          </td>
                        </tr>
                      </table>
                      <table width="100%" border="0" height="8">
                        <tr bgcolor="#FFE375"> </tr>
                      </table>
                      <table width="96%" border="0" cellspacing="2" cellpadding="2">
                      </table>
                      <p> 
                        <% End If %>
                        <% End If %>
                        <br>
                      </p>
                    </td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr> 
            <td bgcolor="#868686"> 
              <div align="center"> 
                <input type="button" name="btnBack" value="&lt;&lt; Back  " onClick="MM_goToURL('parent','cws_impdec.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>');return document.MM_returnValue">
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td bgcolor="#FFFFFF" valign="top" height="16" width="20">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="35">&nbsp;</td>
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="183">&nbsp;</td>
      <td bgcolor="ffffff" bordercolor="#FFFFFF" align="center" valign="top" width="425">&nbsp;</td>
      <td bgcolor="#FFFFFF" valign="top" height="12" width="20">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#666699" align="left" valign="top" height="133" colspan="4"> 
        <p>&nbsp;</p>
        <font color="#E2E2E2"></font> </td>
    </tr>
    <tr> 
      <td bgcolor="#003366" bordercolor="#FFFFFF" align="left" valign="middle" height="18" colspan="4">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="6" height="1" bgcolor="#333333"><img src="../Customs/Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td colspan="6" height="10" bgcolor="#333333"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="4">
          <tr> 
            <!--#Include File="../nav-btm.html"-->
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
</body>
</html>