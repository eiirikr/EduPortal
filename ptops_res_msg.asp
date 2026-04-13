<%@LANGUAGE="VBSCRIPT"%>
<!--#include file="../Connections/constrTIMcomins.asp" -->
<!--#include file="jsonObject.class.asp" -->
<!--#include file="../URL/baseURL.asp" -->
<% 
ApplNumber = request.QueryString("ApplNo")							
Stats = request.QueryString("Status")
ALLPEZAIMP = Replace(Session("ALLpezaimp"), ",", "','")

if Session("UserID") = "" then
      Response.redirect("https://www.intercommerce.com.ph") 
end if
%>
<%
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

MM_DB = DecryptPassword(CStr(Request("cn")))
MM_DB = Session("db")

Dim rsApplNo__MMColParam
Dim strStatus
Dim nConsErr
Dim nFinErr
Dim nSystemErr
Dim nICRErr
Dim nNetworkErr
Dim nMasterErr
Dim CusdecChecked

nConsErr = 0
nFinErr = 0
nSystemErr = 0
nICRErr = 0
nNetworkErr = 0
nMasterErr = 0

rsApplNo__MMColParam = ApplNumber

if (Request("MM_EmptyValue") <> "") then rsApplNo__MMColParam = Request("MM_EmptyValue")

set rsMaster = Server.CreateObject("ADODB.Recordset")
rsMaster.ActiveConnection = constrCOMINSpezaEXP
rsMaster.Source = "SELECT Stat, Purpose, OffClear, ChkDeclarant, SenderID FROM dbo.tblEXPApl_Master WHERE ApplNo = '" + Replace(rsApplNo__MMColParam, "'", "''") + "'"
rsMaster.CursorType = 1
rsMaster.CursorLocation = 3
rsMaster.LockType = 3
rsMaster.Open()

If rsMaster.RecordCount = 0 then
	nMasterErr = 1
Else
	strStatus = rsMaster.Fields.Item("Stat")
End If

' check method of payment
' if cash advance check balance

'zcCDC = "CDC"

set rstMpayment = Server.CreateObject("ADODB.Recordset")
rstMpayment.ActiveConnection = constrCOMINSpezaEXP
rstMpayment.Source = "SELECT ePayMethod, OffClear, regofc, isnull(ExpDocNo,'') as ExpDocNo FROM dbo.tblEXPApl_Master WHERE ApplNo = '" + Replace(rsApplNo__MMColParam, "'", "''") + "'"
rstMpayment.CursorType = 0
rstMpayment.CursorLocation = 2
rstMpayment.LockType = 3
rstMpayment.Open()
rstMpayment_numRows = 0

	'check importables if monitored
	set rstDtail1 = Server.CreateObject("ADODB.Recordset")
	rstDtail1.ActiveConnection = constrCOMINSpezaEXP
	rstDtail1.Source = "SELECT ItemNo, Regulated FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' ORDER by Itemno"
	rstDtail1.CursorType = 0
	rstDtail1.CursorLocation = 2
	rstDtail1.LockType = 3
	rstDtail1.Open()
	rstDtail1_numRows = 0
	
	While not rstDtail1.EOF		
		If rstDtail1("Regulated") = "True" Then
		
			ImpMon = ImpMon &  "&nbsp;&nbsp;&nbsp;" & rstDtail1("ItemNo")
		
		End if
		
		rstDtail1.movenext
	wend
	rstDtail1.Close			
	
	set DATA5 = Server.CreateObject("ADODB.Recordset")
	DATA5.ActiveConnection = constrCOMINSpezaEXP
	DATA5.Source="SELECT COUNT(container) as contcount FROM tblexpapl_contPEZA where applno='" & ApplNumber & "'"  
	DATA5.CursorType = 0
	DATA5.CursorLocation = 2
	DATA5.LockType = 3
	DATA5.Open()
	DATA5_numRows = 0
	
	if DATA5("contcount") > 2 then
		'mults = cint(DATA5("contcount")) mod 2
		'if mults <> 0 then
			'mult = (cint(DATA5("contcount")) / 2) + 0.5
		'else
			'mult = cint(DATA5("contcount")) / 2
		'end if
		
		mult = cint(DATA5("contcount")) - 2
	else
		mult = 0
		'mult = 1
	end if
		
	DATA5.Close

' PTOPS IP
set rstCheckEcaiNo = Server.CreateObject("ADODB.Recordset")
rstCheckEcaiNo.ActiveConnection = constrCOMINSpezaEXP
rstCheckEcaiNo.Source = "SELECT ITEMNO, HSCODE, HSCODE_TAR, ITEMCODE, PTOPS_ROWID  FROM TBLEXPAPL_DETAIL Where ApplNo='" & ApplNumber & "'"
rstCheckEcaiNo.CursorType = 0
rstCheckEcaiNo.CursorLocation = 2
rstCheckEcaiNo.LockType = 3
rstCheckEcaiNo.Open()
rstCheckEcaiNo_numRows = 0

Dim hasEcaiNo, allHaveEcaiNo
hasEcaiNo = False
allHaveEcaiNo = True ' Assume all have ecai_no initially
	
if not rstCheckEcaiNo.EOF then
	while (not rstCheckEcaiNo.EOF)
		
		If Not IsNull(rstCheckEcaiNo("PTOPS_ROWID")) Then
			If Trim(rstCheckEcaiNo("PTOPS_ROWID")) <> "" Then
				hasEcaiNo = True ' At least one item has PTOPS_ROWID

				' Append ITEMNO to hidden field values
				If hiddenFieldValues = "" Then
					hiddenFieldValues = rstCheckEcaiNo("ITEMNO")
				Else
					hiddenFieldValues = hiddenFieldValues & ", " & rstCheckEcaiNo("ITEMNO")
				End If
			Else
				allHaveEcaiNo = False ' Found an item without PTOPS_ROWID
			End If
		Else
			allHaveEcaiNo = False ' PTOPS_ROWID is null
		End If

        rstCheckEcaiNo.MoveNext()
	wend
end if

hasPtopsMessage = ""
' Validation Check: If at least one has ecai_no, then all must have it
If hasEcaiNo And Not allHaveEcaiNo Then
    hasPtopsMessage = "YES" 
End If
'END: PTOPS IP

	'IPFEES = ((112.50) * mult) + 2225
	
	if Ucase(rsMaster("Purpose")) <> "SAMPLE" then
		'IPFEES = 150 + 1000
		IPFEES = ((112.50) * mult) + 225
		'check if for 50% discount
		set rsPEZADtail = Server.CreateObject("ADODB.Recordset")
		rsPEZADtail.ActiveConnection = constrCOMINSpezaEXP
		rsPEZADtail.Source = "SELECT ApplNo from tblEXPApl_ContPEZA WHERE ApplNo= '" & ApplNumber & "'"
		rsPEZADtail.CursorType = 0
		rsPEZADtail.CursorLocation = 2
		rsPEZADtail.LockType = 3
		rsPEZADtail.Open
		rsPEZADtail_numRows = 0
		
		if NOT rsPEZADtail.EOF then
			if rsMaster("OffClear") = "P04" OR rsMaster("OffClear") = "P04C" then
				'IPFEES = ((IPFEES - 1000) / 2) + 1000
				IPFEES = ((IPFEES) / 2) 
			end if
		end if
		rsPEZADtail.Close
	else
		'IPFEES = 1000
		IPFEES = 225
	end if
	
	'IPFEES = IPFEES + 1000	

if IPFEES <> "NA" then	
	' IF FALSE.. ALERT USER THAT ACCOUNT BALANCE IS INSUFFICIENT
	' VALIDATE LIST OF IMPORTABLES
		
		cbf = 0
		
		set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
		rstCASHBAL.ActiveConnection = constrPEZAimp
		rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
		rstCASHBAL.CursorType = 0
		rstCASHBAL.CursorLocation = 2
		rstCASHBAL.LockType = 3
		rstCASHBAL.Open()
		rstCASHBAL_numRows = 0
		
		'response.write "TEST1"
		'response.write "Recordcount=" & rstCASHBAL.Recordcount
		'response.write "<p> Session(PIC) = " & Session("PIC") & "</p>"
		'response.write "<p> Session(btin) = " & Session("btin") & "</p>"
		If rstCASHBAL.EOF  then
			rstCASHBAL.Close()	
			set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
			rstCASHBAL.ActiveConnection = constrPEZAimp
			'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
			rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
			rstCASHBAL.CursorType = 0
			rstCASHBAL.CursorLocation = 2
			rstCASHBAL.LockType = 3
			rstCASHBAL.Open()
			rstCASHBAL_numRows = 0
			
			'response.write "TEST2"
			If rstCASHBAL.EOF  then
				rstCASHBAL.Close()	
				set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
				rstCASHBAL.ActiveConnection = constrPEZAimp
				'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
				rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
				rstCASHBAL.CursorType = 0
				rstCASHBAL.CursorLocation = 2
				rstCASHBAL.LockType = 3
				rstCASHBAL.Open()
				rstCASHBAL_numRows = 0
				
				If rstCASHBAL.EOF then
					CASHBAL = 0
				else
					rstCASHBAL.Close()	
					set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
					rstCASHBAL.ActiveConnection = constrPEZAimp
					'rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
					rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
					rstCASHBAL.CursorType = 0
					rstCASHBAL.CursorLocation = 2
					rstCASHBAL.LockType = 3
					rstCASHBAL.Open()
					rstCASHBAL_numRows = 0
					CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
					CASHBALeu = cdbl(rstCASHBAL("CASHBAL"))
					cbf = 3
					
					if (CASHBAL - 1000) < IPFEES then
						rstCASHBAL.Close()	
						CASHBAL = 0
					end if
				end if
			else
				rstCASHBAL.Close()	
				set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
				rstCASHBAL.ActiveConnection = constrPEZAimp
				'rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
				rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
				rstCASHBAL.CursorType = 0
				rstCASHBAL.CursorLocation = 2
				rstCASHBAL.LockType = 3
				rstCASHBAL.Open()
				rstCASHBAL_numRows = 0
				CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
				CASHBALfu = cdbl(rstCASHBAL("CASHBAL"))
				cbf = 2
				
				if (CASHBAL - 1000) < IPFEES then
					rstCASHBAL.Close()	
					set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
					rstCASHBAL.ActiveConnection = constrPEZAimp
					'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
					rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
					rstCASHBAL.CursorType = 0
					rstCASHBAL.CursorLocation = 2
					rstCASHBAL.LockType = 3
					rstCASHBAL.Open()
					rstCASHBAL_numRows = 0
					
					If rstCASHBAL.EOF then
						CASHBAL = 0
					else
						rstCASHBAL.Close()	
						set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
						rstCASHBAL.ActiveConnection = constrPEZAimp
						'rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
						rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
						rstCASHBAL.CursorType = 0
						rstCASHBAL.CursorLocation = 2
						rstCASHBAL.LockType = 3
						rstCASHBAL.Open()
						rstCASHBAL_numRows = 0
						CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
						CASHBALeu = cdbl(rstCASHBAL("CASHBAL"))
						cbf = 3
						
						if (CASHBAL - 1000) < IPFEES then
							rstCASHBAL.Close()	
							CASHBAL = 0
						end if
					end if
				end if	
			end if
			
		else
				'response.write "TEST3"
				set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
				rstCASHBAL.ActiveConnection = constrPEZAimp
				rstCASHBAL.Source = "SELECT sum(TranAmt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
				rstCASHBAL.CursorType = 0
				rstCASHBAL.CursorLocation = 2
				rstCASHBAL.LockType = 3
				rstCASHBAL.Open()
				rstCASHBAL_numRows = 0
				CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
				CASHBAL1it1 = cdbl(rstCASHBAL("CASHBAL"))
				cbf = 1
				
				if (CASHBAL - 1000) < IPFEES then
					rstCASHBAL.Close()	
					set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
					rstCASHBAL.ActiveConnection = constrPEZAimp
					'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
					rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
					rstCASHBAL.CursorType = 0
					rstCASHBAL.CursorLocation = 2
					rstCASHBAL.LockType = 3
					rstCASHBAL.Open()
					rstCASHBAL_numRows = 0
					'response.write "TEST4"
					
					if rstCASHBAL.eof then
						rstCASHBAL.Close()	
						set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
						rstCASHBAL.ActiveConnection = constrPEZAimp
						'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
						rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
						rstCASHBAL.CursorType = 0
						rstCASHBAL.CursorLocation = 2
						rstCASHBAL.LockType = 3
						rstCASHBAL.Open()
						rstCASHBAL_numRows = 0
						
						If rstCASHBAL.EOF then
							CASHBAL = 0
						else
							rstCASHBAL.Close()	
							set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
							rstCASHBAL.ActiveConnection = constrPEZAimp
							'rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
							rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
							rstCASHBAL.CursorType = 0
							rstCASHBAL.CursorLocation = 2
							rstCASHBAL.LockType = 3
							rstCASHBAL.Open()
							rstCASHBAL_numRows = 0
							CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
							CASHBALeu = cdbl(rstCASHBAL("CASHBAL"))
							cbf = 3
							
							if (CASHBAL - 1000) < IPFEES then
								rstCASHBAL.Close()	
								CASHBAL = 0
							end if
						end if
						'response.write "TEST5"
					else
						rstCASHBAL.Close()	
						set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
						rstCASHBAL.ActiveConnection = constrPEZAimp
						'rstCASHBAL.Source = "SELECT sum(TranAmt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
						rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
						rstCASHBAL.CursorType = 0
						rstCASHBAL.CursorLocation = 2
						rstCASHBAL.LockType = 3
						rstCASHBAL.Open()
						rstCASHBAL_numRows = 0
						CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
						CASHBALfu = cdbl(rstCASHBAL("CASHBAL"))
						cbf = 2
						
						if (CASHBAL - 1000) < IPFEES then
							rstCASHBAL.Close()	
							set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
							rstCASHBAL.ActiveConnection = constrPEZAimp
							'rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
							rstCASHBAL.Source = "SELECT TOP 1 TranAmt as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
							rstCASHBAL.CursorType = 0
							rstCASHBAL.CursorLocation = 2
							rstCASHBAL.LockType = 3
							rstCASHBAL.Open()
							rstCASHBAL_numRows = 0
							
							If rstCASHBAL.EOF then
								CASHBAL = 0
							else
								rstCASHBAL.Close()	
								set rstCASHBAL = Server.CreateObject("ADODB.Recordset")
								rstCASHBAL.ActiveConnection = constrPEZAimp
								'rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '" & Session("bTIN") & "' AND PezaImpCode= 'PEZA' AND Type='EXP'"
								rstCASHBAL.Source = "SELECT sum(tranamt) as CASHBAL FROM TBLCASHADV WHERE DECTIN = '999999999' AND PezaImpCode IN ('" & ALLPEZAIMP & "') AND Type='EXP'"
								rstCASHBAL.CursorType = 0
								rstCASHBAL.CursorLocation = 2
								rstCASHBAL.LockType = 3
								rstCASHBAL.Open()
								rstCASHBAL_numRows = 0
								CASHBAL = cdbl(rstCASHBAL("CASHBAL"))
								CASHBALeu = cdbl(rstCASHBAL("CASHBAL"))
								cbf = 3
								
								if (CASHBAL - 1000) < IPFEES then
									rstCASHBAL.Close()	
									CASHBAL = 0
								end if
							end if
						end if
						'response.write "TEST6"
					end if
				end if					
		end if
else
	CASHBAL = "NA"		
end if	
 
	' If true ... Create transaction in tblCASHADV with IP_FEES AMOUNT (NEGATIVE)
	strDate = FormatDateTime(Now)
	strdate1 = Date()
		
	'TranNo = "0810100001"
If rstMpayment("ExpDocNo") = "" AND CASHBAL <> "NA" Then
	if (CASHBAL - 1000) >= IPFEES then
		'Spagara: 08122023: transaction fee should be charged whether what is the purpose
		'if Ucase(rsMaster("Purpose")) <> "SAMPLE" then
			'IPFEE = (IPFEES - 1000) * (-1)
			IPFEE = (IPFEES) * (-1)
		'else
		'	IPFEE = 0
		'end if
				
		'  Update status to AG and Create TAP Entry Number
		
		' INSERT ALL RECORDS TO CDC DATABASE
		'Open master
		set rsCDCm = Server.CreateObject("ADODB.Recordset")
		rsCDCm.ActiveConnection = constrCOMINSpezaEXP
		rsCDCm.Source = "SELECT * from tblEXPApl_Master WHERE ApplNo= '" & ApplNumber & "'"
		rsCDCm.CursorType = 0
		rsCDCm.CursorLocation = 2
		rsCDCm.LockType = 3
		rsCDCm.Open
		rsCDCm_numRows = 0

		'Open detail
		set rsCDCd = Server.CreateObject("ADODB.Recordset")
		rsCDCd.ActiveConnection = constrCOMINSpezaEXP
		rsCDCd.Source = "SELECT * from tblEXPApl_Detail WHERE ApplNo= '" & ApplNumber & "'"
		rsCDCd.CursorType = 0
		rsCDCd.CursorLocation = 2
		rsCDCd.LockType = 3
		rsCDCd.Open
		rsCDCd_numRows = 0
		
		'Open financial
		set rsCDCf = Server.CreateObject("ADODB.Recordset")
		rsCDCf.ActiveConnection = constrCOMINSpezaEXP
		rsCDCf.Source = "SELECT * from tblEXPApl_Fin WHERE ApplNo= '" & ApplNumber & "'"
		rsCDCf.CursorType = 0
		rsCDCf.CursorLocation = 2
		rsCDCf.LockType = 3
		rsCDCf.Open
		rsCDCf_numRows = 0
				
		'---Get Latest Exchange Rate
		set rstExchRate = Server.CreateObject("ADODB.Recordset")
		rstExchRate.ActiveConnection = constrCOMINScd
		rstExchRate.Source = "SELECT * from GBRATTAB where cur_cod='USD' order by eea_dov desc"
		rstExchRate.CursorType = 0
		rstExchRate.CursorLocation = 2
		rstExchRate.LockType = 3
		rstExchRate.Open
		rstExchRate_numRows = 0

		if rsCDCd("InvCurr") = "USD" then
			Session("exr8") = rstExchRate("rat_exc")
		else
			Session("exr8") = 1
		end if
		rstExchRate.Close
		
		'DELETE Current Record if Exists (Stat="C")
		'Open master
		set rsCDCmD = Server.CreateObject("ADODB.Recordset")
		rsCDCmD.ActiveConnection = constrPEZAexp
		rsCDCmD.Source = "SELECT Applno, Stat from tblEXPApl_Master WHERE ApplNo= '" & ApplNumber & "'"
		rsCDCmD.CursorType = 0
		rsCDCmD.CursorLocation = 2
		rsCDCmD.LockType = 3
		rsCDCmD.Open
		rsCDCmD_numRows = 0
		
		if NOT rsCDCmD.EOF then
		if rsCDCmD("Stat") = "C" then
			set rstDelRec1 = Server.CreateObject("ADODB.Command")
			rstDelRec1.ActiveConnection = constrPEZAexp
			rstDelRec1.CommandText = "DELETE from tblEXPApl_Master WHERE Applno='" & ApplNumber & "'"		
			rstDelRec1.CommandType = 1
			rstDelRec1.CommandTimeout = 0
			rstDelRec1.Prepared = true
			rstDelRec1.Execute()
		
			set rstDelRec2 = Server.CreateObject("ADODB.Command")
			rstDelRec2.ActiveConnection = constrPEZAexp
			rstDelRec2.CommandText = "DELETE from tblEXPApl_Detail WHERE Applno='" & ApplNumber & "'"		
			rstDelRec2.CommandType = 1
			rstDelRec2.CommandTimeout = 0
			rstDelRec2.Prepared = true
			rstDelRec2.Execute()

			set rstDelRec3 = Server.CreateObject("ADODB.Command")
			rstDelRec3.ActiveConnection = constrPEZAexp
			rstDelRec3.CommandText = "DELETE from tblEXPApl_Fin WHERE Applno='" & ApplNumber & "'"		
			rstDelRec3.CommandType = 1
			rstDelRec3.CommandTimeout = 0
			rstDelRec3.Prepared = true
			rstDelRec3.Execute()
		end if
		end if
		rsCDCmD.Close
		
		'---COPY TO MASTER				
		set cmdInsert = Server.CreateObject("ADODB.Command")
		cmdInsert.ActiveConnection = constrPEZAexp
		cmdInsert.CommandText = "INSERT INTO tblEXPApl_Master (ApplNo, OffClear, Manifest, ExpDocNo, ExpDocDate, MDec, Mdec2, ExpCode, ExpName, ExpAdr1, ExpAdr2, ExpAdr3, Items, ItemCon, Packs, ConName, ConAdr1, ConAdr2, ConAdr3, DecTIN, DecName, DecAdr1, DecAdr2, DecAdr3, Cexp, Cdest, Vessel, RegOfc, ExhRate, LocalCarrier, PortofLoad, PortofDept, PortOfDest, ProvofOrig, CreationDate, TermDelivery, InvAmt, InvCurr, BankCode, ModePay, AcctNo, Warehouse, WareCode, WareDelay, Stat, ModPay, ApvlNo, TotFees, TotDec, REFNO, ConTIN, IAN, LGoods, TPort, TotContainers, Seal1, Seal2, Seal3, Seal4, Seal5, ShipDate, SentDate, Status, Regulated, Approvedby, ApprovedDate, Remarks, WayBill, Purpose, ePayMethod, cltcode, Reason) VALUES ('" & rsCDCm("ApplNo") & "', '" & rsCDCm("OffClear") & "', '" & rsCDCm("Manifest") & "', '" & rsCDCm("ExpDocNo") & "', '" & rsCDCm("ExpDocDate") & "', '" & rsCDCm("MDec") & "', '" & rsCDCm("Mdec2") & "', '" & rsCDCm("ExpCode") & "', '" & rsCDCm("ExpName") & "', '" & rsCDCm("ExpAdr1") & "', '" & rsCDCm("ExpAdr2") & "', '" & rsCDCm("ExpAdr3") & "', '" & rsCDCm("Items") & "', '" & rsCDCm("ItemCon") & "', '" & rsCDCm("Packs") & "', '" & rsCDCm("ConName") & "', '" & rsCDCm("ConAdr1") & "', '" & rsCDCm("ConAdr2") & "', '" & rsCDCm("ConAdr3") & "', '" & rsCDCm("DecTIN") & "', '" & rsCDCm("DecName") & "', '" & rsCDCm("DecAdr1") & "', '" & rsCDCm("DecAdr2") & "', '" & rsCDCm("DecAdr3") & "', '" & rsCDCm("Cexp") & "', '" & rsCDCm("Cdest") & "', '" & rsCDCm("Vessel") & "', '" & rsCDCm("RegOfc") & "', '" & rsCDCm("ExhRate") & "', '" & rsCDCm("LocalCarrier") & "', '" & rsCDCm("PortofLoad") & "', '" & rsCDCm("PortofDept") & "', '" & rsCDCm("PortOfDest") & "', '" & rsCDCm("ProvofOrig") & "', '" & rsCDCm("CreationDate") & "', '" & rsCDCm("TermDelivery") & "', '" & rsCDCm("InvAmt") & "', '" & rsCDCm("InvCurr") & "', '" & rsCDCm("BankCode") & "', '" & rsCDCm("ModePay") & "', '" & rsCDCm("AcctNo") & "', '" & rsCDCm("Warehouse") & "', '" & rsCDCm("WareCode") & "', '" & rsCDCm("WareDelay") & "', '" & rsCDCm("Stat") & "', '" & rsCDCm("ModPay") & "', '" & rsCDCm("ApvlNo") & "', '" & rsCDCm("TotFees") & "', '" & rsCDCm("TotDec") & "', '" & rsCDCm("REFNO") & "', '" & rsCDCm("ConTIN") & "', '" & rsCDCm("IAN") & "', '" & rsCDCm("LGoods") & "', '" & rsCDCm("TPort") & "', '" & rsCDCm("TotContainers") & "', '" & rsCDCm("Seal1") & "', '" & rsCDCm("Seal2") & "', '" & rsCDCm("Seal3") & "', '" & rsCDCm("Seal4") & "', '" & rsCDCm("Seal5") & "', '" & rsCDCm("ShipDate") & "', '" & rsCDCm("SentDate") & "', '" & rsCDCm("Status") & "', '" & rsCDCm("Regulated") & "', '" & rsCDCm("Approvedby") & "', '" & rsCDCm("ApprovedDate") & "', '" & rsCDCm("Remarks") & "', '" & rsCDCm("WayBill") & "', '" & rsCDCm("Purpose") & "', '" & rsCDCm("ePayMethod") & "', '" & rsCDCm("cltcode") & "', '" & rsCDCm("Reason") & "')"
		cmdInsert.CommandType = 1
		cmdInsert.CommandTimeout = 0
		cmdInsert.Prepared = true
		cmdInsert.Execute()
		
		'---COPY TO ITEM DETAIL
While NOT rsCDCd.EOF		
		set cmdInsert = Server.CreateObject("ADODB.Command")
		cmdInsert.ActiveConnection = constrPEZAexp		
		cmdInsert.CommandText = "INSERT INTO tblEXPApl_Detail (ApplNo, ItemNo, Marks1, Marks2, NoPack, PackCode, GTIN, Cont1, Cont2, Cont3, Cont4, GoodsDesc1, GoodsDesc2, GoodsDesc3, OCharges, IFreight, HSCode, HSCODE_TAR, TARSPEC, ItemGWeight, ItemNweight, ProcDesc, ExtCode, AirBill, FOBValue, SupVal1, SupVal2, SupVal3, InvNo, InvDate, InvValue, InvCurr, quo_cod, quo_dsc, ValMethodNum, ValMethodDesc, CoCode, Pref, ECLicense, ECAmtDeducted, ECQtyDeducted, InvoiceValue2, InternalFreight, Deduction, Regulated, PrevDoc, PTOPS_ROWID, otherUOM, ecai_no_list) VALUES ('" & rsCDCd("ApplNo") & "', '" & rsCDCd("ItemNo") & "', '" & rsCDCd("Marks1") & "', '" & rsCDCd("Marks2") & "', '" & rsCDCd("NoPack") & "', '" & rsCDCd("PackCode") & "', '" & rsCDCd("GTIN") & "', '" & rsCDCd("Cont1") & "', '" & rsCDCd("Cont2") & "', '" & rsCDCd("Cont3") & "', '" & rsCDCd("Cont4") & "', '" & rsCDCd("GoodsDesc1") & "', '" & rsCDCd("GoodsDesc2") & "', '" & rsCDCd("GoodsDesc3") & "', '" & rsCDCd("OCharges") & "', '" & rsCDCd("IFreight") & "', '" & rsCDCd("HSCode") & "', '" & rsCDCd("HSCODE_TAR") & "', '" & rsCDCd("TARSPEC") & "', '" & rsCDCd("ItemGWeight") & "', '" & rsCDCd("ItemNweight") & "', '" & rsCDCd("ProcDesc") & "', '" & rsCDCd("ExtCode") & "', '" & rsCDCd("AirBill") & "', '" & rsCDCd("FOBValue") & "', '" & rsCDCd("SupVal1") & "', '" & rsCDCd("SupVal2") & "', '" & rsCDCd("SupVal3") & "', '" & rsCDCd("InvNo") & "', '" & rsCDCd("InvDate") & "', '" & rsCDCd("InvValue") & "', '" & rsCDCd("InvCurr") & "', '" & rsCDCd("quo_cod") & "', '" & rsCDCd("quo_dsc") & "', '" & rsCDCd("ValMethodNum") & "', '" & rsCDCd("ValMethodDesc") & "', '" & rsCDCd("CoCode") & "', '" & rsCDCd("Pref") & "', '" & rsCDCd("ECLicense") & "', '" & rsCDCd("ECAmtDeducted") & "', '" & rsCDCd("ECQtyDeducted") & "', '" & rsCDCd("InvoiceValue2") & "', '" & rsCDCd("InternalFreight") & "', '" & rsCDCd("Deduction") & "', '" & rsCDCd("Regulated") & "', '" & rsCDCd("PrevDoc") & "', '" & rsCDCd("PTOPS_ROWID") & "', '" & rsCDCd("otherUOM") & "', '" & rsCDCd("ecai_no_list") & "')"
		cmdInsert.CommandType = 1
		cmdInsert.CommandTimeout = 0
		cmdInsert.Prepared = true
		cmdInsert.Execute()
		
		rsCDCd.movenext		
Wend
		
		'---COPY TO FINANCIAL		
		set cmdInsert = Server.CreateObject("ADODB.Command")
		cmdInsert.ActiveConnection = constrPEZAexp		
		cmdInsert.CommandText = "INSERT INTO tblEXPApl_Fin (ApplNo, TDelivery, TPaymentDesc, Tpayment, BankName, BankCode, BranchCode, BankRef, CustomVal, CustCurr, FreightCost, FreightCurr, WharCost, WharCurr, InsCost, InsCurr, OtherCost, OtherCurr, ArrasCost, ArrasCurr, WareCode, WareDelay, ExchRate, WOBankCharge, XMLProcessedDate, FExchRate, IExchRate, OExchRate, PrePAcct, IntRef, Forex) VALUES ('" & rsCDCf("ApplNo") & "', '" & rsCDCf("TDelivery") & "', '" & rsCDCf("TPaymentDesc") & "', '" & rsCDCf("TPayment") & "', '" & rsCDCf("BankName") & "', '" & rsCDCf("BankCode") & "', '" & rsCDCf("BranchCode") & "', '" & rsCDCf("BankRef") & "', '" & rsCDCf("CustomVal") & "', '" & rsCDCf("CustCurr") & "', '" & rsCDCf("FreightCost") & "', '" & rsCDCf("FreightCurr") & "', '" & rsCDCf("WharCost") & "', '" & rsCDCf("WharCurr") & "', '" & rsCDCf("InsCost") & "', '" & rsCDCf("InsCurr") & "', '" & rsCDCf("OtherCost") & "', '" & rsCDCf("OtherCurr") & "', '" & rsCDCf("ArrasCost") & "', '" & rsCDCf("ArrasCurr") & "', '" & rsCDCf("WareCode") & "', '" & rsCDCf("WareDelay") & "', '" & rsCDCf("ExchRate") & "', '" & rsCDCf("WOBankCharge") & "', '" & rsCDCf("XMLProcessedDate") & "', '" & rsCDCf("FExchRate") & "', '" & rsCDCf("IExchRate") & "', '" & rsCDCf("OExchRate") & "', '" & rsCDCf("PrePAcct") & "', '" & rsCDCf("IntRef") & "', '" & rsCDCf("Forex") & "')"
		cmdInsert.CommandType = 1
		cmdInsert.CommandTimeout = 0
		cmdInsert.Prepared = true
		cmdInsert.Execute()
		
		set rstIPNo = Server.CreateObject("ADODB.Recordset")
		rstIPNo.ActiveConnection = constrPEZAexp
		rstIPNo.Source = "SELECT EDSeries,Applno FROM tblEXPApl_Master WHERE Applno= '"&ApplNumber&"'"
		rstIPNo.CursorType = 0
		rstIPNo.CursorLocation = 2
		rstIPNo.LockType = 3
		rstIPNo.Open()
		rstIPNo_numRows = 0				
			
		ENCtr = "X" & Right("0000" & Trim(rstMpayment("regofc")),4) & "" & Right("00000" & Trim(cstr(Cdbl(rstIPNo("EDSeries")))), 6) & "" & Right("00" & Trim(CStr(Year(Date))), 2) & "" & "I"	
		rstIPNo.Close		
		
		' PTOPS IP
		If Not allHaveEcaiNo Then
			ENCtr1 = ENCtr
		Else
			ENCtr1 = ""
		End If
		' END: PTOPS IP	
		
			'locator fund
			If cbf = 1 Then
				zcCDC = Session("PIC")
				tinnum = Session("bTIN")
			End If
			
			'broker (all) fund
			If cbf = 2 Then
				zcCDC = "PEZA"
				tinnum = Session("bTIN")
				sen = Session("PIC")
			End If
			
			'locator (all) fund
			If cbf = 3 Then
				zcCDC = Session("PIC")
				tinnum = "999999999"
				sen = Session("bTIN")
			End If		
		
		set rsCDCd1 = Server.CreateObject("ADODB.Recordset")
		rsCDCd1.ActiveConnection = constrCOMINSpezaEXP
		'rsCDCd1.Source = "SELECT TOP 1 AirBill from tblEXPApl_Detail WHERE ApplNo= '" & ApplNumber & "'"
		rsCDCd1.Source = "SELECT WayBill FROM tblEXPAPL_Master WHERE Applno = '" & ApplNumber & "'"
		rsCDCd1.CursorType = 0
		rsCDCd1.CursorLocation = 2
		rsCDCd1.LockType = 3
		rsCDCd1.Open
		rsCDCd1_numRows = 0
		
		'insert into tblautonum
		set rstDduct1 = Server.CreateObject("ADODB.Command")
		rstDduct1.ActiveConnection = constrPEZAimp
		rstDduct1.CommandText = "INSERT INTO tblAutoNum (PezaRefNo) VALUES ('" & ENCtr & "') "
		rstDduct1.CommandType = 1
		rstDduct1.CommandTimeout = 0
		rstDduct1.Prepared = true
		rstDduct1.Execute()
		
		'get the transaction number from tblautonum
		set rstIPNo11 = Server.CreateObject("ADODB.Recordset")
		rstIPNo11.ActiveConnection = constrPEZAimp
		rstIPNo11.Source = "SELECT * FROM tblAutoNum WHERE PezaRefNo = '"& ENCtr &"'"
		rstIPNo11.CursorType = 0
		rstIPNo11.CursorLocation = 2
		rstIPNo11.LockType = 3
		rstIPNo11.Open()
		rstIPNo11_numRows = 0
		
		TranNos = rstIPNO11("AutoSeries")
		rstIPNo11.Close
				
		set rsChkCA = Server.CreateObject("ADODB.Recordset")
		rsChkCA.ActiveConnection = constrPEZAimp
		rsChkCA.Source = "SELECT ApplNo from tblCashAdv WHERE ApplNo= '" & ApplNumber & "' AND cltcode='"& Session("cltcode") &"' AND Type='EXP'"
		rsChkCA.CursorType = 0
		rsChkCA.CursorLocation = 2
		rsChkCA.LockType = 3
		rsChkCA.Open
		rsChkCA_numRows = 0
		
		' set cash advance transaction		
		if rsChkCA.EOF then
			set rstDduct = Server.CreateObject("ADODB.Command")
			rstDduct.ActiveConnection = constrPEZAimp
			rstDduct.CommandText = "INSERT INTO tblCashAdv (TranNo, TranDate, DecTIN, PezaImpCode, TranAmt, Remarks, ApplNo, cltcode, LoginID, SenderID, Type, PEZARefNo, ZoneCode) Values ('"& TranNos &"','" & strDate & "', '" & tinnum & "', '" & zcCDC & "', '"& IPFEE &"', '" & "PEZA Export DOC FEE for AirBill: " & rsCDCd1("WayBill") &  "', '" & ApplNumber & "', '" & Session("cltcode") & "', '" & Session("UserID") & "', '" & sen & "', 'EXP', '"& ENCtr &"', '" & rstMpayment("regofc") & "')"
			rstDduct.CommandType = 1
			rstDduct.CommandTimeout = 0
			rstDduct.Prepared = true
			rstDduct.Execute()
		end if
		rsChkCA.Close
		rsCDCd1.Close
		
		'if rstMpayment("ePayMethod") = "CASHADV" then			
			'set rstDduct = Server.CreateObject("ADODB.Command")
			'rstDduct.ActiveConnection = constrPEZAimp
			'rstDduct.CommandText = "UPDATE tblcashadv Set Tranno='" & TranNos & "' WHERE Applno='" & ApplNumber & "' AND Type='EXP'"
			'rstDduct.CommandType = 1
			'rstDduct.CommandTimeout = 0
			'rstDduct.Prepared = true
			'rstDduct.Execute()
		'end if
		
		'set rsimpval = Server.CreateObject("ADODB.Recordset")
		'rsimpval.ActiveConnection = constrPEZAimp
		'rsimpval.Source = "SELECT pezaimpcode, ipvalidityext FROM tblImporters WHERE Pezaimpcode  IN ('" & ALLPEZAIMP & "')"
		'rsimpval.CursorType = 1
		'rsimpval.CursorLocation = 3
		'rsimpval.LockType = 3
		'rsimpval.Open()
						
		'ipvalext = rsimpval("ipvalidityext")
		
		set rsimpval1 = Server.CreateObject("ADODB.Recordset")
		rsimpval1.ActiveConnection = constrPEZAexp
		rsimpval1.Source = "SELECT InvAmt, InvCurr FROM tblEXPAPL_Master WHERE Applno = '" & ApplNumber & "'"
		rsimpval1.CursorType = 1
		rsimpval1.CursorLocation = 3
		rsimpval1.LockType = 3
		rsimpval1.Open()		
		
		CV = rsimpval1("InvAmt")
		CC = rsimpval1("InvCurr")
		rsimpval1.Close	
		'regitem = "True"
		'if rstMpayment("ePayMethod") = "CASHADV" then
		edval = DateAdd("d", +15, strDate)
		'update master if "AG"
		if ImpMon = "" then
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrCOMINSpezaEXP	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "AG" & "', Remarks='" & "Auto-Approved" & "', Status='" & "A" & "', SentDate='" & strdate & "', ApprovedDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', EXPDocDate='" & edval & "', ApvlNo='"& ENCtr &"', ShipDate=NULL, ExpDocNo='"& ENCtr1 &"', TotFees='" & (IPFEE * -1) & "', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "' WHERE Applno='" & ApplNumber & "'"
			'response.write rstCSat.CommandText			
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()
		else
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrCOMINSpezaEXP	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "FP" & "', Remarks='" & "Under Review by Zone Manager (Regulated Item/s)" & "', Status='" & "N" & "', SentDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', ApvlNo='"& ENCtr &"', ShipDate=NULL, TotFees='" & (IPFEE * -1) & "', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "' WHERE Applno='" & ApplNumber & "'"				
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()
		end if
		
		'set rsimpval2 = Server.CreateObject("ADODB.Recordset")
		'rsimpval2.ActiveConnection = constrPEZAexp
		'rsimpval2.Source = "SELECT WayBill FROM tblEXPAPL_Master WHERE Applno = '" & ApplNumber & "'"
		'rsimpval2.CursorType = 1
		'rsimpval2.CursorLocation = 3
		'rsimpval2.LockType = 3
		'rsimpval2.Open()
		
		'Update tblexpapl_Detail, set AirBill
		'set rstCSat1 = Server.CreateObject("ADODB.Command")	
		'rstCSat1.ActiveConnection = constrCOMINSpezaEXP	
		'rstCSat1.CommandText = "UPDATE tblEXPApl_Detail Set AirBill='" & rsimpval2("WayBill") & "' WHERE Applno='" & ApplNumber & "'"	
		'rstCSat1.CommandType = 1
		'rstCSat1.CommandTimeout = 0
		'rstCSat1.Prepared = true		
		'rstCSat1.Execute()
		
		if rstMpayment("ePayMethod") = "LBPEPAY" then
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrCOMINSpezaEXP	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "FP" & "', Status='" & "P" & "', SentDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "', ShipDate=NULL WHERE Applno='" & ApplNumber & "'"	
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()					
		end if
		
		'update fin if "AG"
		set rstCSat = Server.CreateObject("ADODB.Command")	
		rstCSat.ActiveConnection = constrCOMINSpezaEXP	
		rstCSat.CommandText = "UPDATE tblEXPApl_FIN Set ExchRate='" & Session("exr8") & "', FExchRate='" & Session("exr8") & "', IExchRate='" & Session("exr8") & "', OExchRate='" & Session("exr8") & "', XMLProcessedDate='" & strDate & "' WHERE Applno='" & ApplNumber & "'"	
		rstCSat.CommandType = 1
		rstCSat.CommandTimeout = 0
		rstCSat.Prepared = true		
		rstCSat.Execute()
		
		'if rstMpayment("ePayMethod") = "CASHADV" then
		edval = DateAdd("d", +15, strDate)
		'update trade system if "AG"
		if ImpMon = "" then
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrPEZAexp	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "AG" & "', Remarks='" & "Auto-Approved" & "', Status='" & "A" & "', SentDate='" & strdate & "', ApprovedDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', EXPDocDate='" & edval & "', ApvlNo='"& ENCtr &"', ShipDate=NULL, ExpDocNo='"& ENCtr1 &"', TotFees='" & FormatNumber(IPFEE * -1, 2) & "', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "' WHERE Applno='" & ApplNumber & "'"
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()
		else
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrPEZAexp	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "M" & "', Remarks='" & "Under Review by Zone Manager (Regulated Item/s)" & "', Status='" & "N" & "', SentDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', ApvlNo='"& ENCtr &"', ShipDate=NULL, ExpDocDate=NULL, ApprovedDate=NULL, AllowedtoExit=NULL, TotFees='" & FormatNumber(IPFEE * -1, 2) & "', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "' WHERE Applno='" & ApplNumber & "'"
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()
		end if
		
		if rstMpayment("ePayMethod") = "LBPEPAY" then
			'update trade system if "AG"		
			set rstCSat = Server.CreateObject("ADODB.Command")	
			rstCSat.ActiveConnection = constrPEZAexp	
			rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='" & "FP" & "', Status='" & "P" & "', SentDate='" & strdate & "', ExhRate='" & Session("exr8") & "', TermDelivery='" & rsCDCf("TDelivery") & "', WareCode='" & rsCDCf("WareCode") & "', WareDelay='" & rsCDCf("WareDelay") & "', InvAmt='" & CV & "', InvCurr ='"& CC &"', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "', ShipDate=NULL WHERE Applno='" & ApplNumber & "'"	
			rstCSat.CommandType = 1
			rstCSat.CommandTimeout = 0
			rstCSat.Prepared = true		
			rstCSat.Execute()
		end if
		
		'update trade system if "AG"
		set rstCSat = Server.CreateObject("ADODB.Command")	
		rstCSat.ActiveConnection = constrPEZAexp	
		rstCSat.CommandText = "UPDATE tblEXPApl_FIN Set ExchRate='" & Session("exr8") & "', FExchRate='" & Session("exr8") & "', IExchRate='" & Session("exr8") & "', OExchRate='" & Session("exr8") & "', XMLProcessedDate='" & strDate & "' WHERE Applno='" & ApplNumber & "'"	
		rstCSat.CommandType = 1
		rstCSat.CommandTimeout = 0
		rstCSat.Prepared = true		
		rstCSat.Execute()
	
		lid = "IPPEZA"
		'inscharge = 45 '0 from 09012011
		if UCase(Session("cltcode")) <> "FEDEX" AND UCase(Session("cltcode")) <> "FEDEXP" AND Session("PIC") <> "CIP31099" AND Session("PIC") <> "CIP28496" AND Session("PIC") <> "CEM10105" AND Session("PIC") <> "CIP31099" AND Session("PIC") <> "CIP28496" then
		'If Session("Membership") = "AFPI" OR Session("Membership") = "PISFA" Then
			'INSCharge = 35
		'Else
			INSCharge = 45
		End If
		'Spagara: 03142023: Update INS charge 
		if UCase(Session("cltcode")) = "FEDEX" OR UCase(Session("cltcode")) = "FEDEXP" then
			'INSCharge = 44.80
			INSCharge = 33.60
		end if
		
		'for Eaton New Rates
		if Session("PIC") = "CIP31099" OR Session("PIC") = "CIP28496" then
			INSCharge = 40
		end if
		
        'Spagara: 07172023: for molex -MOL26753
        'for testing purpose: analog expcode has been used ADG10038
        'if UCase(Session("cltcode")) = "EN4W210809" and Session("PIC") = "ADG10038" then
	    if UCase(Session("cltcode")) = "EN4W210809" then
            if Session("PIC") = "MOL26753" then
		        INSCharge = 30
            else
                INSCharge = 45
            end if
	    end if          
        
        'Spagara: 09182024: Update for Cebu Mitsumi
		if Session("PIC") = "CEM10105" OR UCase(Session("cltcode")) = "LK5B240807" then
				INSCharge = 30
		end if

		tc = "C"
		
		'check ins cash balance
		INSCASHBAL = 0
	
		'Check INS Account Balance
		set rstINSCASHBAL = Server.CreateObject("ADODB.Recordset")
		rstINSCASHBAL.ActiveConnection = constrCOMINSad
		rstINSCASHBAL.Source = "SELECT sum(TranAmt) as INSCASHBAL FROM dbo.TBLCASHADV WHERE cltcode = '" & Session("cltcode")& "'"
		rstINSCASHBAL.CursorType = 0
		rstINSCASHBAL.CursorLocation = 2
		rstINSCASHBAL.LockType = 3
		rstINSCASHBAL.Open()
		rstINSCASHBAL_numRows = 0
	
		INSCASHBAL = rstINSCASHBAL("INSCASHBAL")
		'If session("membership") = "SPC1" Then
			'INSCharge = 45
		'Else
			'INSCharge = 55
		'End If
		sid = "PEZA-AEDS"
		rstINSCASHBAL.Close
		
		set rstINSREM = Server.CreateObject("ADODB.Recordset")
		rstINSREM.ActiveConnection = constrCOMINSpezaEXP
		rstINSREM.Source = "SELECT * FROM tblEXPApl_Master WHERE applno = '" & ApplNumber& "'"
		rstINSREM.CursorType = 0
		rstINSREM.CursorLocation = 2
		rstINSREM.LockType = 3
		rstINSREM.Open()
		rstINSREM_numRows = 0
		Session("WB") = rstINSREM("WayBill")
		rstINSREM.Close()
		
		set rsChkCAins = Server.CreateObject("ADODB.Recordset")
		rsChkCAins.ActiveConnection = constrCOMINSad
		rsChkCAins.Source = "SELECT refno from tblCashAdv WHERE RefNo= '" & ApplNumber & "' AND cltcode='"& Session("cltcode") &"'"
		rsChkCAins.CursorType = 0
		rsChkCAins.CursorLocation = 2
		rsChkCAins.LockType = 3
		rsChkCAins.Open
		rsChkCAins_numRows = 0
		
			' set INS cash advance transaction
			if rsChkCAins.EOF then
				INSCharged = INSCharge * -1
				set rstInsDduct = Server.CreateObject("ADODB.Command")
				rstInsDduct.ActiveConnection = constrCOMINSad
				rstInsDduct.CommandText = "INSERT INTO tblCashAdv (TranDate, LoginID, RefNo, TranAmt, TranCode, Remarks, cltcode) Values ('" & strDate & "', '" & sid & "', '" & ApplNumber & "', '"& INSCharged &"', '" & tc & "', '" & "PEZA Export DOC FEE for WayBill: " & Session("WB") &  "', '" & Session("cltcode") & "')"
				rstInsDduct.CommandType = 1
				rstInsDduct.CommandTimeout = 0
				rstInsDduct.Prepared = true
				rstInsDduct.Execute()
			end if
		rsChkCAins.Close
			
		'insert to tblExpVersion
		set rstOpen = Server.CreateObject("ADODB.Recordset")
		rstOpen.ActiveConnection = constrCOMINSpezaEXP
		rstOpen.Source = "SELECT Status, Remarks from tblExpApl_Master WHERE ApplNo= '" & ApplNumber & "'"
		rstOpen.CursorType = 0
		rstOpen.CursorLocation = 2
		rstOpen.LockType = 3
		rstOpen.Open
		rstOpen_numRows = 0

		set rstUpd8tblExpVersion = Server.CreateObject("ADODB.Command")
		rstUpd8tblExpVersion.ActiveConnection = constrPEZAexp
		rstUpd8tblExpVersion.CommandText = "INSERT INTO tblExpVersion (ApplNo, LoginID, Status, Remarks, VerDate) Values ('" & ApplNumber & "', '" & session("userid") & "', '" & rstOpen("Status") & "', '" & rstOpen("Remarks") &  "', '"& strdate &"')"
		rstUpd8tblExpVersion.CommandType = 1
		rstUpd8tblExpVersion.CommandTimeout = 0
		rstUpd8tblExpVersion.Prepared = true
		rstUpd8tblExpVersion.Execute()
				
		set rstZone = Server.CreateObject("ADODB.Command")	
		rstZone.ActiveConnection = constrPEZAexp	
		rstZone.CommandText = "UPDATE tblEXPApl_Master Set Zone='" & rstMpayment("regofc") & "', SenderID='"& rsMaster("SenderID") & " --- " & Session("UserID") & "' WHERE Applno='" & ApplNumber & "'"	
		rstZone.CommandType = 1
		rstZone.CommandTimeout = 0
		rstZone.Prepared = true		
		rstZone.Execute()
		
		rstOpen.Close
		rsCDCm.Close
		rsCDCd.Close
		rsCDCf.Close	
	end if	
End If

' PTOPS ED
if allHaveEcaiNo Then
	' Check if PTOPS_ROWID exists and is valid
	Response.LCID = 1046

	On Error Resume Next ' Catch API errors

	hasErrorResponse = "NO"

	' --- Call API ---
	Set http = Server.CreateObject("MSXML2.XMLHTTP.6.0")
	url = ptopsEDSendApplication
	http.Open "POST", url, False
	http.setRequestHeader "Accept", "application/json"
	http.setRequestHeader "Content-Type", "application/json"

	jsonPayload = "{""applno"":""" & ApplNumber & """}"

	http.Send jsonPayload

	' Parse JSON response regardless of status
	Set json = New JSONobject
	json.parse http.responseText

	If Err.Number = 0 Then
		Set jsonData = json

		if http.status = 400 then
			' Check if "data" exists and has items
			If jsonData.Exists("data") Then
				Set dataArray = jsonData("data")
				If dataArray.Length > 0 Then
					Dim i, messages
					messages = ""
					For i = 0 To dataArray.Length - 1
						If messages <> "" Then messages = messages 
						messages = messages & "<p><font color='red'>" & dataArray.ItemAt(i)("message") & "</font></p>"
					Next
					hasErrorResponse = "YES"
					ptopsResponse = messages 
				End If
			Else
				' Fallback: check if "message" exists at top-level
				If jsonData.Exists("message") Then
					response.write jsonData("message")   ' <-- message even if status <> 200
				End If
			End If
		Elseif http.status = 200 then
			' Check if "data" exists and has items
			If jsonData.Exists("data") Then
				Set dataArray = jsonData("data")
				If dataArray.Length > 0 Then
					messages = ""
					For i = 0 To dataArray.Length - 1
						If messages <> "" Then messages = messages 
						messages = messages & "<p><font>" & dataArray.ItemAt(i)("message") & "</font></p>"
					Next
					hasErrorResponse = "NO"
					ptopsResponse = messages 
				End If
			Else
				' Fallback: check if "message" exists at top-level
				If jsonData.Exists("message") Then
					response.write jsonData("message")   ' <-- message even if status <> 200
				End If
			End If
		Else ' other status response
			hasErrorResponse = "YES"
			ptopsResponse = "<p><font color='red'> There was an issue processing your application. Please contact InterCommerce Helpdesk. (EDx-000)</font></p>"
		End If
	End If

	On Error GoTo 0 ' Reset error handling

	Response.LCID = 1033

	set rstCSat = Server.CreateObject("ADODB.Command")	
	rstCSat.ActiveConnection = constrCOMINSpezaEXP	
	rstCSat.CommandText = "UPDATE tblEXPApl_Master Set ApprovedDate = NULL WHERE Applno='" & ApplNumber & "'"
	rstCSat.CommandType = 1
	rstCSat.CommandTimeout = 0
	rstCSat.Prepared = true		
	rstCSat.Execute()

	if hasErrorResponse = "YES" then
		set rstCSat = Server.CreateObject("ADODB.Command")	
		rstCSat.ActiveConnection = constrCOMINSpezaEXP	
		rstCSat.CommandText = "UPDATE tblEXPApl_Master Set Stat='C', Status=NULL, Remarks='', ExpDocNo = NULL, ExpDocDate = NULL, TotFees = NULL, SentDate = NULL, ApprovedDate = NULL WHERE Applno='" & ApplNumber & "'"
		rstCSat.CommandType = 1
		rstCSat.CommandTimeout = 0
		rstCSat.Prepared = true		
		rstCSat.Execute()
		
		Stats = "C"

		set rstDelRec1 = Server.CreateObject("ADODB.Command")
		rstDelRec1.ActiveConnection = constrPEZAexp
		rstDelRec1.CommandText = "DELETE from tblEXPApl_Master WHERE Applno='" & ApplNumber & "'"		
		rstDelRec1.CommandType = 1
		rstDelRec1.CommandTimeout = 0
		rstDelRec1.Prepared = true
		rstDelRec1.Execute()

		set rstDelRec2 = Server.CreateObject("ADODB.Command")
		rstDelRec2.ActiveConnection = constrPEZAexp
		rstDelRec2.CommandText = "DELETE from tblEXPApl_Detail WHERE Applno='" & ApplNumber & "'"		
		rstDelRec2.CommandType = 1
		rstDelRec2.CommandTimeout = 0
		rstDelRec2.Prepared = true
		rstDelRec2.Execute()

		set rstDelRec3 = Server.CreateObject("ADODB.Command")
		rstDelRec3.ActiveConnection = constrPEZAexp
		rstDelRec3.CommandText = "DELETE from tblEXPApl_Fin WHERE Applno='" & ApplNumber & "'"		
		rstDelRec3.CommandType = 1
		rstDelRec3.CommandTimeout = 0
		rstDelRec3.Prepared = true
		rstDelRec3.Execute()
		
		set rstDelINSCashAdv = Server.CreateObject("ADODB.Command")
		rstDelINSCashAdv.ActiveConnection = constrCOMINSad
		rstDelINSCashAdv.CommandText = "DELETE from TBLCASHADV WHERE refno='" & ApplNumber & "'"		
		rstDelINSCashAdv.CommandType = 1
		rstDelINSCashAdv.CommandTimeout = 0
		rstDelINSCashAdv.Prepared = true
		rstDelINSCashAdv.Execute()
		
		set rstDelPEZACashAdv = Server.CreateObject("ADODB.Command")
		rstDelPEZACashAdv.ActiveConnection = constrPEZAimp
		rstDelPEZACashAdv.CommandText = "DELETE from TBLCASHADV WHERE Applno='" & ApplNumber & "'"		
		rstDelPEZACashAdv.CommandType = 1
		rstDelPEZACashAdv.CommandTimeout = 0
		rstDelPEZACashAdv.Prepared = true
		rstDelPEZACashAdv.Execute()
		
		set rstDelExpVersion = Server.CreateObject("ADODB.Command")
		rstDelExpVersion.ActiveConnection = constrPEZAexp
		rstDelExpVersion.CommandText = "DELETE from tblExpVersion WHERE Applno='" & ApplNumber & "'"		
		rstDelExpVersion.CommandType = 1
		rstDelExpVersion.CommandTimeout = 0
		rstDelExpVersion.Prepared = true
		rstDelExpVersion.Execute()
	end if

End If
' END: PTOPS ED

'03.24.2020: SPagara: Added for sending emails
' PTOPS IP				
If Not allHaveEcaiNo Then
    strDate = Formatdatetime(Now)
	set rstIPNo = Server.CreateObject("ADODB.Recordset")
	rstIPNo.ActiveConnection = constrPEZAexp
	rstIPNo.Source = "SELECT expdocno, expcode, conname, decname, RegOfc FROM dbo.TBLEXPAPL_MASTER where applno='" & ApplNumber & "'"
	rstIPNo.CursorType = 0
	rstIPNo.CursorLocation = 2
	rstIPNo.LockType = 3
	rstIPNo.Open()
	rstIPNo_numRows = 0

	ENCtr = rstIPNo("expdocno")

    set rsbrokmailchk = Server.CreateObject("ADODB.Recordset")
    rsbrokmailchk.ActiveConnection = constrPEZAimp
    rsbrokmailchk.Source = "SELECT TOP 1 IsNull(Email, '') as EmailAdd FROM tblImporters WHERE PEZAImpCode = '" & rstIPNo("expcode") & "'"
    rsbrokmailchk.CursorType = 0
    rsbrokmailchk.CursorLocation = 2
    rsbrokmailchk.LockType = 3
    rsbrokmailchk.Open()
    rsbrokmailchk_numRows = 0

    'set values
    BrokerName = rstIPNo("DecName")

    'Getting Zone Information
    set rstGBCUOTAB3 = Server.CreateObject("ADODB.Recordset")
    rstGBCUOTAB3.ActiveConnection = constrPEZAimp
    rstGBCUOTAB3.Source = "SELECT ZoneCode, ZoneDesc FROM tblZone WHERE ZoneCode='" & rstIPNo("RegOfc") & "'"
    rstGBCUOTAB3.CursorType = 0
    rstGBCUOTAB3.CursorLocation = 2
    rstGBCUOTAB3.LockType = 3
    rstGBCUOTAB3.Open
    rstGBCUOTAB3_numRows = 0

    'Getting Exporter Information
    set rstImp = Server.CreateObject("ADODB.Recordset")
    rstImp.ActiveConnection = constrPEZAimp
    rstImp.Source = "SELECT Pezaimpcode, zonecode, companyname FROM TblImporters WHERE Pezaimpcode='" & rstIPNo("expcode") & "'"
    rstImp.CursorType = 0
    rstImp.CursorLocation = 2
    rstImp.LockType = 3
    rstImp.Open
    rstImp_numRows = 0

    CompName = rstImp("CompanyName")
    CompZone = rstGBCUOTAB3("ZoneDesc") & "(" & rstImp("ZoneCode") & ")"
    'OffClear = rstGBCUOTAB3("ZoneDesc") & "(" & rstIPNo("RegOfc") & ")"
    rstGBCUOTAB3.Close
    SupName = rstIPNo("ConName")
    'IPF = FormatNumber(Cdbl(rstIPNo("ipfeE")) * -1,2)
    'GoodsDesc = mid(GoodsDesc,1,len(GoodsDesc) -2)


    'open master for new status
    set rsMasterStat = Server.CreateObject("ADODB.Recordset")
    rsMasterStat.ActiveConnection = constrPEZAexp
    rsMasterStat.Source = "SELECT Status, Remarks from TBLEXPAPL_MASTER WHERE ApplNo = '"&ApplNumber&"'"
    rsMasterStat.CursorType = 0
    rsMasterStat.CursorLocation = 2
    rsMasterStat.LockType = 3
    rsMasterStat.Open

    if UCase(rsMasterStat("Status")) = "N" then
	    eIPStat = "For Approval"
    elseif UCase(rsMasterStat("Status")) = "R" then
	    eIPStat = "Rejected"
    elseif UCase(rsMasterStat("Status")) = "A" then
	    eIPStat = "Approved"
    elseif UCase(rsMasterStat("Status")) = "AT" then
	    eIPStat = "Transferred"
    elseif UCase(rsMasterStat("Status")) = "X" then
	    eIPStat = "Cancelled"
    elseif UCase(rsMasterStat("Status")) = "FX" then
	    eIPStat = "For Cancellation"
    elseif UCase(rsMasterStat("Status")) = "IN" then
	    eIPStat = "Released"
    elseif UCase(rsMasterStat("Status")) = "DS" then
	    eIPStat = "Documents Submitted"
    elseif UCase(rsMasterStat("Status")) = "DX" then
	    eIPStat = "Wrong Documents Submitted"
    elseif UCase(rsMasterStat("Status")) = "D" then
	    eIPStat = "Documents Confirmed"
    elseif UCase(rsMasterStat("Status")) = "H" then
	    eIPStat = "For Inspection"
    elseif UCase(rsMasterStat("Status")) = "R" then
	    eIPStat = "Rejected"
    end if

    remarks = rsMasterStat("Remarks")

    'Spagara: 06/08/2020 - Hide Tagged by, requested by Ms Zheila on 06/08/2020 1:45 PM
    'Additonal : 06/19/2020 - Hide Tagged Date when status is For Approval 

    if rsMasterStat("Status") <> "N" then
        ExpDocNum = " <br><br>AEDS NUMBER : " & rstIPNo("expdocno")
        ExpDocNum2 = "PEZA AEDS Number : " & rstIPNo("expdocno")
        Tagged = "  <br><br>Date Tagged : " & strDate '& "   <br><br>Tagged by : " & session("userid")
    else
        ExpDocNum = " "
        ExpDocNum2 = "PEZA AEDS Application Number : " & ApplNumber
        Tagged = " "
    end if
    rsMasterStat.Close

    

	'sending email alert
	if NOT rsbrokmailchk.EOF then
		if rsbrokmailchk("EmailAdd") <> "" then
			Dim message2
			message2="<head></head><body><br><p style='text-indent:40px;'>A PEZA AEDS was filed on " & FormatDateTime(now) & " by " & BrokerName & " with the following details: <br><br><br><br> INS APPLICATION NO. : " & ApplNumber &_ 
			ExpDocNum & " <br><br> Company Name: " & CompName & " <br><br> Zone: " & CompZone & "<br><br> STATUS : " & eIPStat & Tagged &_ 
			"<br><br>Remarks : " & remarks & "<br><br><br><br>Please log in at <a href='https://peza.intercommerce.com.ph' target='_blank'>https://peza.intercommerce.com.ph</a> with your username and password to view the transaction details. " &_
			" <br><br>If you did not authorize the lodgement in the AEDS, please call your Zone Manager immediately so that a Hold and Alert Order can be issued on the Transit Cargo. <br><br>" &_
			"<p><font size='5' color='red'>***This is a system generated email, do not REPLY.***</font></p><br><br><br><p>Your Value Added Service Provider,<br><font size='4'><b>InterCommerce Network Services, Inc.</b></font></p>" &_
			"<br>Phone: 8-888-4674 dial 1 | Helpdesk Department <br> Email:<a href='mailto:helpdesk@intercommerce.com.ph' target='_blank'>helpdesk@intercommerce.com.ph</a> or <a href='mailto:marketing@intercommerce.com.ph' target='_blank'>marketing@intercommerce.com.ph</a> "&_
			"<br>FB Page: <a href='https://facebook.com/InterCommerceNetworkServices' target='_blank'>@InterCommerceNetworkServices</a>" &_
			"<br><br><br><font size='1'>NOTICE: This e-mail message and all attachments transmitted with it are intended solely for the use of the addressee and contain legally privileged and confidential information. If the reader of this message is not the intended recipient, " &_
			"or an employee or agent responsible for delivering this message to the intended recipient, you are hereby notified that any dissemination, distribution, copying or other use of this message or its attachments is strictly prohibited. If you have received this message in " &_
			"error, please notify the sender immediately by replying to this message and please delete it from your computer.</font></body>"
			
			set objMessage = createobject("cdo.message") 
			set objConfig = createobject("cdo.configuration") 
			Set Flds = objConfig.Fields 

			Flds.Item("https://schemas.microsoft.com/cdo/configuration/sendusing") = 2 
			Flds.Item("https://schemas.microsoft.com/cdo/configuration/smtpserver") ="192.168.1.51" 

			' ' Passing SMTP authentication 
			Flds.Item ("https://schemas.microsoft.com/cdo/configuration/smtpauthenticate") = 1 'basic (clear-text) authentication 
			Flds.Item ("https://schemas.microsoft.com/cdo/configuration/sendusername") ="insmail" 
			Flds.Item ("https://schemas.microsoft.com/cdo/configuration/sendpassword") ="Ins2015" 

			Flds.update 
			Set objMessage.Configuration = objConfig 
			objMessage.To = rsbrokmailchk("EmailAdd")
			objMessage.From = "insmail@intercommerce.com.ph" 
			objMessage.BCC = "peza.insmail@intercommerce.com.ph" 
			objMessage.Subject = "Email notification for " + ExpDocNum2
			objMessage.fields.update 
			objMessage.HTMLBody = message2
			objMessage.Send

		end if
	end if
	rsbrokmailchk.Close()		
End If
' END: PTOPS IP					

if hasErrorResponse = "NO" then
	'Open exporter
	set rsCDCm02 = Server.CreateObject("ADODB.Recordset")
	rsCDCm02.ActiveConnection = constrPEZAimp
	rsCDCm02.Source = "SELECT DUNS from tblImporters WHERE PezaImpCode IN ('" & ALLPEZAIMP & "') "
	rsCDCm02.CursorType = 0
	rsCDCm02.CursorLocation = 2
	rsCDCm02.LockType = 3
	rsCDCm02.Open
	rsCDCm02_numRows = 0

	set rsEXPORTER = Server.CreateObject("ADODB.Recordset")
	rsEXPORTER.ActiveConnection = constrCOMINScd
	rsEXPORTER.Source = "SELECT exp_code FROM CWSEXPORTER WHERE cltcode='" & Session("cltcode") & "' AND exp_tin = '"& rsCDCm02("DUNS") &"' ORDER BY exp_name"
	rsEXPORTER.CursorType = 0
	rsEXPORTER.CursorLocation = 2
	rsEXPORTER.LockType = 3
	rsEXPORTER.Open()
	rsEXPORTER_numRows = 0

	if NOT rsEXPORTER.EOF then
		BOCexp = "OK"
		if ImpMon = "" then
			'generate a unique ApplNo(BOC-AEDS for E2M)
			Dim DataConnection, cmdDC, cmdDC2, RecordSet, RecordSet2, ApplNo, cntr
			
			Set DataConnection = Server.CreateObject("ADODB.Connection")
			DataConnection.Open constrCOMINScd
			
			Set cmdDC = Server.CreateObject("ADODB.Command")
			cmdDC.ActiveConnection = DataConnection
			Set cmdDC2 = Server.CreateObject("ADODB.Command")
			cmdDC2.ActiveConnection = DataConnection
			
			SQL1 = "SELECT ApplNo FROM tblexpAPL_MASTER"
			
			cmdDC.CommandText = SQL1
			
			Set RecordSet = Server.CreateObject("ADODB.Recordset")
			
			RecordSet.Open cmdDC, , 0, 2
			cntr = 1
			Do While True
				ApplNo = rsEXPORTER("Exp_Code") & "D" & Right("00" & Trim(CStr(Month(Date))), 2) & Right("00" & Trim(CStr(Day(Date))), 2) & Right("0" & Trim(CStr(cntr)), 2)
				SQL2 = "SELECT ApplNo FROM tblexpAPL_MASTER where APPLNO = '" & Trim(ApplNo) & "'"
				cmdDC2.CommandText = SQL2
				Set RecordSet2 = Server.CreateObject("ADODB.Recordset")
				RecordSet2.Open cmdDC2, , 0, 2
				If RecordSet2.EOF Then
					Exit Do
				End If
				cntr = cntr + 1
			Loop
			
			'INSERT ALL RECORDS FROM PEZA DATABASE
			'Open master
			set rsCDCm01 = Server.CreateObject("ADODB.Recordset")
			rsCDCm01.ActiveConnection = constrPEZAexp
			'04262024: SPagara update
			rsCDCm01.Source = "SELECT * from tblEXPApl_Master WHERE ApplNo= '" & ApplNumber & "'"
			'rsCDCm01.Source = "SELECT * from tblEXPApl_Master WHERE ApplNo= '" & ApplNumber & "'"
			rsCDCm01.CursorType = 0
			rsCDCm01.CursorLocation = 2
			rsCDCm01.LockType = 3
			rsCDCm01.Open
			rsCDCm01_numRows = 0			
			
			'determine conflag
			set rsDetail0 = Server.CreateObject("ADODB.Recordset")
			rsDetail0.ActiveConnection = constrCOMINSpezaEXP  
			rsDetail0.Source = "SELECT TOP 1 Container FROM tblEXPAPL_ContPEZA WHERE ApplNo = '" & ApplNumber & "'"
			rsDetail0.CursorType = 0
			rsDetail0.CursorLocation = 2
			rsDetail0.LockType = 3
			rsDetail0.Open
			intConFlag = 0
			
			'Open importers
			set rsCDCm02 = Server.CreateObject("ADODB.Recordset")
			rsCDCm02.ActiveConnection = constrPEZAimp
			rsCDCm02.Source = "SELECT DUNS from tblImporters WHERE PezaImpCode IN ('" & ALLPEZAIMP & "') "
			rsCDCm02.CursorType = 0
			rsCDCm02.CursorLocation = 2
			rsCDCm02.LockType = 3
			rsCDCm02.Open
			rsCDCm02_numRows = 0
			
			'Open declarant
			set rsCDCm03 = Server.CreateObject("ADODB.Recordset")
			rsCDCm03.ActiveConnection = constrPEZAexpPTOPS
			rsCDCm03.Source = "SELECT TIN12 from tblForwarders WHERE For_TIN= '" & rsCDCm01("DecTIN") & "'"
			rsCDCm03.CursorType = 0
			rsCDCm03.CursorLocation = 2
			rsCDCm03.LockType = 3
			rsCDCm03.Open
			rsCDCm03_numRows = 0
			
			if NOT rsDetail0.EOF then
				intConFlag = 1
			end if
			
			if rsCDCm01("ChkDeclarant") = True then
				set cmdInsert = Server.CreateObject("ADODB.Command")
				cmdInsert.ActiveConnection = constrCOMINScd
				cmdInsert.CommandText = "INSERT INTO tblexpapl_master (applno, DecTin, MDec, Mdec2, SupName, SupAddr1, SupAddr2, SupAddr3, OffClear, ConTIN, Manifest, ItemCon, Items, Packs, Stat, Vessel, LocalC, Tport, Pdest, Lgoods, Cexp, Brokername, BrokAddr1, ConFlag, CreationDate, RegOfc, ProvOfOrig, PortofLoad, PEZANo) VALUES ('" & ApplNo & "', '111111111111', '"& rsCDCm01("MDec") &"', '"& rsCDCm01("MDec2") &"', '"& rsCDCm01("ConName") &"', '"& rsCDCm01("ConAdr1") &"', '"& rsCDCm01("ConAdr2") &"', '"& rsCDCm01("ConAdr3") &"', '"& rsCDCm01("OffClear") &"', '"& rsCDCm02("DUNS") &"', '"& rsCDCm01("Manifest") &"', '"& rsCDCm01("Items") &"', '"& rsCDCm01("Items") &"', '"& rsCDCm01("Packs") &"', 'S', '"& rsCDCm01("Vessel") &"', '"& rsCDCm01("DecName") &"', 'None', '"& rsCDCm01("PortOfDept") &"', '"& rsCDCm01("LGoods") &"', '"& rsCDCm01("Cdest") &"', 'EXPORTER AS DECLARANT', 'FOR EXPORT USE ONLY', '"& intConFlag &"', '" & strDate & "', 'PEZA', '"& rsCDCm01("ProvOfOrig") &"', '"& rsCDCm01("PortofLoad") &"', '"& ApplNumber &"')"
				cmdInsert.CommandType = 1
				cmdInsert.CommandTimeout = 0
				cmdInsert.Prepared = true
				cmdInsert.Execute()
			else
				set cmdInsert = Server.CreateObject("ADODB.Command")
				cmdInsert.ActiveConnection = constrCOMINScd
				cmdInsert.CommandText = "INSERT INTO tblexpapl_master (applno, DecTin, MDec, Mdec2, SupName, SupAddr1, SupAddr2, SupAddr3, OffClear, ConTIN, Manifest, ItemCon, Items, Packs, Stat, Vessel, LocalC, Tport, Pdest, Lgoods, Cexp, Brokername, BrokAddr1, ConFlag, CreationDate, RegOfc, ProvOfOrig, PortofLoad, PEZANo) VALUES ('" & ApplNo & "', '" & rsCDCm03("TIN12") & "', '"& rsCDCm01("MDec") &"', '"& rsCDCm01("MDec2") &"', '"& rsCDCm01("ConName") &"', '"& rsCDCm01("ConAdr1") &"', '"& rsCDCm01("ConAdr2") &"', '"& rsCDCm01("ConAdr3") &"', '"& rsCDCm01("OffClear") &"', '"& rsCDCm02("DUNS") &"', '"& rsCDCm01("Manifest") &"', '"& rsCDCm01("Items") &"', '"& rsCDCm01("Items") &"', '"& rsCDCm01("Packs") &"', 'S', '"& rsCDCm01("Vessel") &"', '', 'None', '"& rsCDCm01("PortOfDept") &"', '"& rsCDCm01("LGoods") &"', '"& rsCDCm01("Cdest") &"', '" & rsCDCm01("DecName") & "', '" & rsCDCm01("DecAdr1") & "', '"& intConFlag &"', '" & strDate & "', 'PEZA', '"& rsCDCm01("ProvOfOrig") &"', '"& rsCDCm01("PortofLoad") &"', '"& ApplNumber &"')"
				cmdInsert.CommandType = 1
				cmdInsert.CommandTimeout = 0
				cmdInsert.Prepared = true
				cmdInsert.Execute()
			end if
				
			'Open detail
			set rsCDCd01 = Server.CreateObject("ADODB.Recordset")
			rsCDCd01.ActiveConnection = constrPEZAexp
			'04262024: SPagara update
			rsCDCd01.Source = "SELECT ItemNo, Marks1, Marks2, NoPack, PackCode, GoodsDesc1, InvNo, HSCode, HSCode_Tar, TARSPEC, COCode, ItemGWeight, Pref, ProcDesc, ItemNweight, InvValue, InvCurr,  GoodsDesc2, GoodsDesc3, SupVal1, SupVal2, SupVal3, LOANo from tblEXPApl_Detail WHERE ApplNo= '" & ApplNumber & "'"
			'rsCDCd01.Source = "SELECT * from tblEXPApl_Detail WHERE ApplNo= '" & ApplNumber & "'"
			rsCDCd01.CursorType = 0
			rsCDCd01.CursorLocation = 2
			rsCDCd01.LockType = 3
			rsCDCd01.Open
			rsCDCd01_numRows = 0
				
			while NOT rsCDCd01.EOF			
					
				'get hsrate from GBTARTAB
				set rstHSCode0 = Server.CreateObject("ADODB.Recordset")
				rstHSCode0.ActiveConnection = constrCOMINScd
				rstHSCode0.Source = "SELECT TOP 1 tar_t01 FROM dbo.GBTARTAB where hs6_cod='" & Left(rsCDCd01("HSCode"),6) & "' AND tar_pr1='" & Mid(rsCDCd01("HSCode"),7,8) & "' AND tar_pr2='" & rsCDCd01("HSCODE_TAR") & "' ORDER BY eea_dov DESC"
				rstHSCode0.CursorType = 0
				rstHSCode0.CursorLocation = 2
				rstHSCode0.LockType = 3
				rstHSCode0.Open()
				rstHSCode0_numRows = 0
					
				if NOT rstHSCode0.EOF then
					HSR8 = rstHSCode0("tar_t01")
				else
					HSR8 = ""
				end if
									
				'---COPY TO ITEM DETAIL
				set cmdInsert = Server.CreateObject("ADODB.Command")
				cmdInsert.ActiveConnection = constrCOMINScd		
				cmdInsert.CommandText = "INSERT INTO tblexpapl_detail (ApplNo, ItemNo, Marks1, Marks2, NoPack, PackCode, GoodsDesc, OCharges, IFreight, InvNo, HSCode, HSCODE_TAR, TARSPEC, COCode, ItemGWeight, Pref, ProcDsc, ExtCode, ItemNweight, InvValue, InvCurr, Adjustment, gDesc2, gDesc3, SupVal1, SupVal2, SupVal3, HsRate, SupUnit2, AirBill) VALUES ('" & ApplNo & "', '"& rsCDCd01("ItemNo") &"', '"& rsCDCd01("Marks1") &"', '"& rsCDCd01("Marks2") &"', '"& rsCDCd01("NoPack") &"', '"& rsCDCd01("PackCode") &"', '"& mid(Replace(Replace(rsCDCd01("GoodsDesc1"), "(", ""), ")", ""),1,135) &"', '0', '0', '"& rsCDCd01("InvNo") &"', '"& rsCDCd01("HSCode") &"', '"& rsCDCd01("HSCode_Tar") &"', '"& rsCDCd01("TARSPEC") &"', '"& rsCDCd01("COCode") &"', '"& rsCDCd01("ItemGWeight") &"', '"& rsCDCd01("Pref") &"', '"& rsCDCd01("ProcDesc") &"', '000', '"& rsCDCd01("ItemNweight") &"', '"& rsCDCd01("InvValue") &"', '"& rsCDCd01("InvCurr") &"', '1', '"& rsCDCd01("GoodsDesc2") &"', '"& rsCDCd01("GoodsDesc3") &"', '"& rsCDCd01("SupVal1") &"', '"& rsCDCd01("SupVal2") &"', '"& rsCDCd01("SupVal3") &"', '"& HSR8 &"', '"& rsCDCd01("LOANo") &"', '"& rsCDCm01("WayBill") &"')"
				cmdInsert.CommandType = 1
				cmdInsert.CommandTimeout = 0
				cmdInsert.Prepared = true
				cmdInsert.Execute()	
					
				'---COPY TO ITEM CONSOLIDATED ITEM DETAIL
				set cmdInsert = Server.CreateObject("ADODB.Command")
				cmdInsert.ActiveConnection = constrCOMINScd		
				cmdInsert.CommandText = "insert into tblexpapl_cons (ApplNo, ItemNo, Marks1, Marks2, NoPack, PackCode, GoodsDesc, OCharges, IFreight, InvNo, HSCode, HSCODE_TAR, TARSPEC, COCode, ItemGWeight, Pref, ProcDsc, ExtCode, ItemNweight, InvValue, InvCurr, Adjustment, gDesc2, gDesc3, SupVal1, SupVal2, SupVal3, HsRate, SupUnit2, AirBill) VALUES ('" & ApplNo & "', '"& rsCDCd01("ItemNo") &"', '"& rsCDCd01("Marks1") &"', '"& rsCDCd01("Marks2") &"', '"& rsCDCd01("NoPack") &"', '"& rsCDCd01("PackCode") &"', '"& mid(rsCDCd01("GoodsDesc1"),1,135) &"', '0', '0', '"& rsCDCd01("InvNo") &"', '"& rsCDCd01("HSCode") &"', '"& rsCDCd01("HSCode_Tar") &"', '"& rsCDCd01("TARSPEC") &"', '"& rsCDCd01("COCode") &"', '"& rsCDCd01("ItemGWeight") &"', '"& rsCDCd01("Pref") &"', '"& rsCDCd01("ProcDesc") &"', '000', '"& rsCDCd01("ItemNweight") &"', '"& rsCDCd01("InvValue") &"', '"& rsCDCd01("InvCurr") &"', '1', '"& rsCDCd01("GoodsDesc2") &"', '"& rsCDCd01("GoodsDesc3") &"', '"& rsCDCd01("SupVal1") &"', '"& rsCDCd01("SupVal2") &"', '"& rsCDCd01("SupVal3") &"', '"& HSR8 &"', '"& rsCDCd01("LOANo") &"', '"& rsCDCm01("WayBill") &"')"
				cmdInsert.CommandType = 1
				cmdInsert.CommandTimeout = 0
				cmdInsert.Prepared = true
				cmdInsert.Execute()
					
				rsCDCd01.movenext
			wend
			
			'Open additional containers
			set rsPEZAcont = Server.CreateObject("ADODB.Recordset")
			rsPEZAcont.ActiveConnection = constrCOMINSpezaEXP
			rsPEZAcont.Source = "SELECT Container from tblEXPAPL_ContPEZA WHERE ApplNo= '" & ApplNumber & "'"
			rsPEZAcont.CursorType = 0
			rsPEZAcont.CursorLocation = 2
			rsPEZAcont.LockType = 3
			rsPEZAcont.Open
			rsPEZAcont_numRows = 0
			cntr = 1
			
			'---COPY TO tblImpApl_Container
			if NOT rsPEZAcont.EOF then
				WHILE NOT rsPEZAcont.EOF
					if cntr = 1 or cntr = 2 or cntr = 3 or cntr = 4 then
						Set cmdUpdateItemsd = Server.CreateObject("ADODB.Command")
						cmdUpdateItemsd.ActiveConnection = constrCOMINScd
						cmdUpdateItemsd.CommandText = "UPDATE tblexpapl_detail SET Cont" & cntr & " = '" & rsPEZAcont("Container") & "' WHERE ApplNo='" & ApplNo & "'"
						cmdUpdateItemsd.Execute
						cmdUpdateItemsd.ActiveConnection.Close
						
						Set cmdUpdateItemsc = Server.CreateObject("ADODB.Command")
						cmdUpdateItemsc.ActiveConnection = constrCOMINScd
						cmdUpdateItemsc.CommandText = "UPDATE tblexpapl_cons SET Cont" & cntr & " = '" & rsPEZAcont("Container") & "' WHERE ApplNo='" & ApplNo & "'"
						cmdUpdateItemsc.Execute
						cmdUpdateItemsc.ActiveConnection.Close
					else
						set cmdInsertCONT = Server.CreateObject("ADODB.Command")
						cmdInsertCONT.ActiveConnection = constrCOMINScd		
						cmdInsertCONT.CommandText = "INSERT INTO tblImpApl_Container (ApplNo, Container) VALUES ('" & ApplNo & "', '"& rsPEZAcont("Container") &"')"
						cmdInsertCONT.CommandType = 1
						cmdInsertCONT.CommandTimeout = 0
						cmdInsertCONT.Prepared = true
						cmdInsertCONT.Execute()
					end if
					
					cntr = cntr + 1
					rsPEZAcont.movenext
				WEND
			end if
			
			'Open fin
			set rsCDCf01 = Server.CreateObject("ADODB.Recordset")
			rsCDCf01.ActiveConnection = constrPEZAexp
			'04262024: SPagara update
			rsCDCf01.Source = "SELECT TDelivery, Tpayment, BankName, BankCode, BranchCode, BankRef, CustomVal, WareCode, WareDelay, WOBankCharge from tblEXPApl_Fin WHERE ApplNo= '" & ApplNumber & "'"
			'rsCDCf01.Source = "SELECT * from tblEXPApl_Fin WHERE ApplNo= '" & ApplNumber & "'"
			rsCDCf01.CursorType = 0
			rsCDCf01.CursorLocation = 2
			rsCDCf01.LockType = 3
			rsCDCf01.Open
			rsCDCf01_numRows = 0
			
			'---COPY TO FINANCIAL
			set cmdInsert = Server.CreateObject("ADODB.Command")
			cmdInsert.ActiveConnection = constrCOMINScd		
			cmdInsert.CommandText = "INSERT INTO tblexpapl_fin (ApplNo, TDelivery, Tpayment, BankName, BankCode, BranchCode, BankRef, CustomVal, CustCurr, FreightCost, FreightCurr, WharCost, WharCurr, InsCost, InsCurr, OtherCost, OtherCurr, ArrasCost, ArrasCurr, WareCode, WareDelay, WOBankCharge) VALUES ('" & ApplNo & "', '"& rsCDCf01("TDelivery") &"', '"& rsCDCf01("Tpayment") &"', '"& rsCDCf01("BankName") &"', '"& rsCDCf01("BankCode") &"', '"& rsCDCf01("BranchCode") &"', '"& rsCDCf01("BankRef") &"', '"& rsCDCf01("CustomVal") &"', 'USD', '0', 'USD', '0', 'PHP', '0', 'USD', '0', 'USD', '0', 'PHP', '"& rsCDCf01("WareCode") &"', '"& rsCDCf01("WareDelay") &"', '"& rsCDCf01("WOBankCharge") &"')"
			cmdInsert.CommandType = 1
			cmdInsert.CommandTimeout = 0
			cmdInsert.Prepared = true
			cmdInsert.Execute()
			
			'deduct from INS account
			Session("App") = ApplNo
			set rsChkCAinsx = Server.CreateObject("ADODB.Recordset")
			rsChkCAinsx.ActiveConnection = constrCOMINSad
			rsChkCAinsx.Source = "SELECT refno from tblCashAdv WHERE RefNo= '" & Session("App") & "' AND cltcode='"& Session("cltcode") &"'"
			rsChkCAinsx.CursorType = 0
			rsChkCAinsx.CursorLocation = 2
			rsChkCAinsx.LockType = 3
			rsChkCAinsx.Open
			rsChkCAinsx_numRows = 0
			
			if rsChkCAinsx.EOF then
				set rstInsDductx = Server.CreateObject("ADODB.Command")
				rstInsDductx.ActiveConnection = constrCOMINSad
				rstInsDductx.CommandText = "INSERT INTO tblCashAdv (TranDate, LoginID, RefNo, TranAmt, TranCode, Remarks, cltcode) Values ('" & strDate & "', 'E2M-E', '" & Session("App") & "', '-20', 'C', '" & "Export Dec for " & Session("App") &  "', '" & Session("cltcode") & "')"
				rstInsDductx.CommandType = 1
				rstInsDductx.CommandTimeout = 0
				rstInsDductx.Prepared = true
				rstInsDductx.Execute()
			end if

			'call asp to create XML for sending
			' response.redirect("cws_expdec.asp?applno=" & ApplNumber)
			Response.redirect("http:/testweb.intercommerce.com.ph/WebCWS/pdf/sadPTOPSPEZAEXP.php?aplid=" & ApplNumber&"")
			'response.write "Sent to BOC-E2M"
			'response.redirect("http:/testweb.intercommerce.com.ph/inswebexp/webcws/cws_send-expdec-xml3.aspx?an=" & ApplNo & "&port=" & session("OffClear") & "&netsend=E2M")
			' response.redirect("http://testmanifest.intercommerce.com.ph/xml/xml_generate_exporter.php?applno=" & ApplNo & "&tin=" & "&section=")
		end if
	else
		BOCexp = ""
		
		Response.redirect("http:/testweb.intercommerce.com.ph/WebCWS/pdf/sadPTOPSPEZAEXP.php?aplid=" & ApplNumber&"")
	end if
end if
%>
<html>
<head>
<title>InterCommerce Network Services - Create/Open Application</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../global.css" type="text/css">
<script language="JavaScript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
function Set_action(x) {
	document.frmPreassess.rdoSend[x].checked = true;
}
function goto_next() {
}
//-->
</script>
</head>
<body bgcolor="#666666" text="#000000">
<form name="frmPreassess" method="post">
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
      <td bgcolor="#FFFFFF" valign="top" height="1" width="425">&nbsp;</td>
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
            <td bgcolor="e5e5e5"> 
              <div align="center"><br>
                <table width="100%" border="0" height="0" cellspacing="0">
                  <tr> 
                    <td>&nbsp;</td>
                    <td colspan="3" bgcolor="#666699" class="heading"><font color="#FFFFFF">&nbsp;&nbsp;PEZA Export Documentation - Message Checking </font></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr> 
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td height="50">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="4%" height="14">&nbsp;</td>
                    <td height="14" colspan="3" class="body"> 
						<p>
							<font color="#333333" class="desc">Application #
								<% Response.Write(rsApplNo__MMColParam) %>
							</font>
						</p>
						
					<%If Not allHaveEcaiNo Then%>
						<p>
							<font color="#333333"> 
								<% If nMasterErr = 0 And Ucase(strStatus) = "AG" Or Ucase(strStatus) = "AS" Then %>
								The status of this application 
								is &quot;Approved&quot; already. Please choose another 
								application.
								<% End If %>
								<% If nMasterErr = 0 And Ucase(strStatus) = "I" Then %>
								The status of this application 
								is &quot;Incomplete&quot;. Please choose another application.
								<% End If %>
								<% If nMasterErr = 0 And Ucase(strStatus) = "ER" Then %>
								The status of this application 
								is &quot;Error&quot;. Please choose another application.
								<% End If %>
								
								<%If nMasterErr = 0 Then
								'-- remove comment after testing
									If Ucase(Trim(strStatus)) = "C"  or Left(Ucase(Trim(strStatus)),1) = "S" Then 
									'if true then
								%>                        
									<font face="Verdana, Arial, Helvetica, sans-serif" size="2">
									<%if ImpMon="" then%> 
									Your PEZA Export Documentation Number: <b><%=ENCtr%></b> is now <b>APPROVED</b> with an Export Shipment Transfer Fee of <b>Php <%=FormatNumber(IPFEE*-1,2)%></b> deducted to your PEZA account and an INS Fee of <b>Php <%=FormatNumber(INSCharge,2)%></b> deducted to your INS account. 
									<%else%>
									Your PEZA Export Application is <b>Under Review</b> by <b>PEZA-Zone Manager</b>. Please coordinate with PEZA for the approval of this application.  Thank You. <br>An amount of <b>Php <%=FormatNumber(IPFEE*-1,2)%></b> was deducted from your PEZA Account and an INS Fee of <b>Php <%=FormatNumber(INSCharge,2)%></b> deducted to your INS account.
									<%end if
									End If						
								End If%>
							</font>
						</p>
					<%Else%>
						<p> 
							<% 
								Dim txtColor
								If hasErrorResponse = "YES" Then
									txtColor = "#FF0000"
								Else
									txtColor = "#333333"
								End If
							%>
							<font color="#333333" face="Verdana, Arial, Helvetica, sans-serif" size="2">
								<span style="color:<%=txtColor%>;">
									<%=ptopsResponse%>
								</span>
								
								<%'If Ucase(Trim(Stats)) = "AG"  Or Ucase(Stats) = "AS" Then 
								%>
									<%'if showFEE = "YES" then
									%>
									<%If hasErrorResponse = "NO" Then %>
										<br/><br/>
										An amount of <b>Php <%=FormatNumber(IPFEE*-1,2)%></b> was deducted from your PEZA Account and <b><%=FormatNumber(INSCharge,2)%></b> from your <b>INS</b> Account.
									<%End If%>
									<%'End If
									%>
								<%'End If
								%>
							</font>
						</p>
					<%End If%>
                      <p> 
                      <h3>&nbsp;</h3>
                      <p> 
                      </font></p>
                    </td>
                    <td width="6%" height="14">&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="4%" height="25">&nbsp;</td>
                    <td width="31%" height="25">&nbsp;</td>
                    <td width="27%" height="25">&nbsp;</td>
                    <td width="32%" height="25">&nbsp;</td>
                    <td width="6%" height="25">&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="4%">&nbsp;</td>
                    <td width="31%" bgcolor="#666699"> 
                      <input type="button" name="btnBack" value="&lt;&lt; Back " onClick="MM_goToURL('parent','ptops_ed_impdecPEZAEXPexpress.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>');return document.MM_returnValue">
                    </td>
                    <td width="27%" bgcolor="#666699">&nbsp;</td>
                    <td width="32%" bgcolor="#666699"> 
                      <div align="right"> </div>
                    </td>
                    <td width="6%">&nbsp;</td>
                  </tr>
                  <tr> 
                    <td colspan="5" height="2">&nbsp;</td>
                  </tr>
                </table>
                <br>
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
        <font color="#E2E2E2">&nbsp;</font> </td>
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
<%
On Error Resume Next
rsMaster.Close
rstMpayment.Close
%>