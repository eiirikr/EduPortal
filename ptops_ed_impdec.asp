<%@LANGUAGE="VBSCRIPT"%>
<!--#include file="../Connections/constrTIMcomins.asp" -->
<!--#include file="../URL/baseURL.asp" -->
<%
'Document History
'Spagara: 05022022: for Checking of WAybill and Location of Goods for FEDEX
'LObligado: 11292023:  DHLTICLARK account disabled the autocomplete feature (oninput="searchDatabase()")

'response.write Session("mod_cod") & " - " & Session("mod_cod2")
'Response.write (Session("db"))
'response.write (Session("lstExporter"))
'response.write (Session("uTIN"))
'response.write (Session("csncod"))
'response.write(Session("PIC"))
'Response.write(Session("brknam"))

ApplNumber = request.QueryString("ApplNo")							
Stats = request.QueryString("Status")

Function IsManifestOptional()
    Dim userID, cltCode
    userID = UCase(Session("UserID"))
    cltCode = UCase(Session("cltcode"))

    If userID = "ASTECLTI" OR userID = "ASTECCEZO" OR userID = "JCDAGDAG" OR userID = "DSCMPI-TEST" OR _
       cltCode = "DOLEPHILSEXP" OR cltCode = "TESTA" Then
        IsManifestOptional = True
    Else
        IsManifestOptional = False
    End If
End Function

'Check DHLTI Account if valid
'DHL-TI PEZ AEDS Importer auto suggest dropdown removal: Atoralde
set rstDHLTI = Server.CreateObject("ADODB.Recordset")
rstDHLTI.ActiveConnection = constrCOMINSpezaEXP	
rstDHLTI.Source = "SELECT UserID FROM DHLTIUserID WHERE UserID='" & Session("userID") & "'"
rstDHLTI.CursorType = 0
rstDHLTI.CursorLocation = 2
rstDHLTI.LockType = 3
rstDHLTI.Open()
rstDHLTI_numRows = 0

	If NOT rstDHLTI.EOF Then
		AllowedDHLTI = "TRUE"
	else
		AllowedDHLTI = "FALSE"
	End if

'update app status
set rstDF = Server.CreateObject("ADODB.Recordset")
rstDF.ActiveConnection = constrCOMINSpezaEXP	
rstDF.Source = "SELECT TOP 1 ApplNo FROM tblEXPApl_FIN WHERE ApplNo='" & ApplNumber & "'"
rstDF.CursorType = 0
rstDF.CursorLocation = 2
rstDF.LockType = 3
rstDF.Open()
rstDF_numRows = 0

set rstDF1 = Server.CreateObject("ADODB.Recordset")
rstDF1.ActiveConnection = constrCOMINSpezaEXP	
rstDF1.Source = "SELECT ApplNo FROM tblEXPApl_Detail WHERE ApplNo='" & ApplNumber & "'"
rstDF1.CursorType = 0
rstDF1.CursorLocation = 2
rstDF1.LockType = 3
rstDF1.Open()
rstDF1_numRows = 0

set rstDF11 = Server.CreateObject("ADODB.Recordset")
rstDF11.ActiveConnection = constrCOMINSpezaEXP	
rstDF11.Source = "SELECT Stat FROM tblEXPApl_Master WHERE ApplNo='" & ApplNumber & "'"
rstDF11.CursorType = 0
rstDF11.CursorLocation = 2
rstDF11.LockType = 3
rstDF11.Open()
rstDF11_numRows = 0

if NOT rstDF.EOF then
	Session("SBFin") = "HASFIN"
	if NOT rstDF1.EOF then
		if rstDF11("Stat") = "I" then
			Set cmdmaster = Server.CreateObject("ADODB.Command")
			cmdmaster.ActiveConnection = constrCOMINSpezaEXP	
			cmdmaster.CommandText = "UPDATE tblEXPApl_Master Set Stat='C' WHERE ApplNo='" & ApplNumber & "'"
			cmdmaster.Execute
			cmdmaster.ActiveConnection.Close
		end if
	else
		if rstDF11("Stat") = "C" then
			Set cmdmaster = Server.CreateObject("ADODB.Command")
			cmdmaster.ActiveConnection = constrCOMINSpezaEXP	
			cmdmaster.CommandText = "UPDATE tblEXPApl_Master Set Stat='I' WHERE ApplNo='" & ApplNumber & "'"
			cmdmaster.Execute
			cmdmaster.ActiveConnection.Close
		end if
	end if
else
	Session("SBFin") = "NOFIN"
	Set cmdmaster = Server.CreateObject("ADODB.Command")
	cmdmaster.ActiveConnection = constrCOMINSpezaEXP	
	cmdmaster.CommandText = "UPDATE tblEXPApl_Master Set Stat='I' WHERE ApplNo='" & ApplNumber & "'"
	cmdmaster.Execute
	cmdmaster.ActiveConnection.Close
end if

rstDF.Close
rstDF1.Close
rstDF11.Close

'---Update all item nos. in TBLIMPAPL_DETAIL, to make them sequential
set rstUpdateItem = Server.CreateObject("ADODB.Recordset")
rstUpdateItem.ActiveConnection = constrCOMINSpezaEXP	
rstUpdateItem.Source = "SELECT * FROM tblEXPApl_Detail WHERE ApplNo='" & ApplNumber & "' ORDER BY ItemNo"
rstUpdateItem.CursorType = 0
rstUpdateItem.CursorLocation = 2
rstUpdateItem.LockType = 3
rstUpdateItem.Open()
rstUpdateItem_numRows = 0		
intCounter = 1

strConSQL = ""
	
While NOT(rstUpdateItem.EOF)
	strConSQL = strConSQL & "UPDATE tblEXPApl_Detail Set ItemNo='" & intCounter & "' Where applno='" & ApplNumber & "' and ItemNo='" & rstUpdateItem("ItemNo") & "' "
	intCounter = intCounter + 1
	rstUpdateItem.MoveNext
Wend
If strConSQL = "" Then strConSQL="Delete from tblEXPApl_Detail where applno='" & ApplNumber & "'"	

set cmdNewItem = Server.CreateObject("ADODB.Command")
cmdNewItem.ActiveConnection = constrCOMINSpezaEXP	
cmdNewItem.CommandText = strConSQL
cmdNewItem.CommandType = 1
cmdNewItem.CommandTimeout = 0
cmdNewItem.Prepared = true
cmdNewItem.Execute()

'---Update set CustomVal
set rstTotal = Server.CreateObject("ADODB.Recordset")
rstTotal.ActiveConnection = constrCOMINSpezaEXP	
rstTotal.Source = "Select invvalue from tblEXPApl_Detail where applno='" & ApplNumber & "'"
rstTotal.CursorType = 0
rstTotal.CursorLocation = 2
rstTotal.LockType = 3
rstTotal.Open
rstTotal_numRows = 0

intTotal = 0
if NOT rstTotal.EOF then
	While Not rstTotal.EOF
		intTotal = intTotal + rstTotal("InvValue")
		rstTotal.MoveNext
	Wend

	set rstTotal1 = Server.CreateObject("ADODB.Recordset")
	rstTotal1.ActiveConnection = constrCOMINSpezaEXP	
	rstTotal1.Source = "Select top 1 invcurr from tblEXPApl_Detail where applno='" & ApplNumber & "'"
	rstTotal1.CursorType = 0
	rstTotal1.CursorLocation = 2
	rstTotal1.LockType = 3
	rstTotal1.Open
	rstTotal1_numRows = 0
	
	Set cmdFin1 = Server.CreateObject("ADODB.Command")
	cmdFin1.ActiveConnection = constrCOMINSpezaEXP	
	cmdFin1.CommandText = "UPDATE tblEXPApl_Master Set InvAmt='" & intTotal & "', InvCurr='"& rstTotal1("InvCurr") &"' WHERE ApplNo='" & ApplNumber & "'"
	cmdFin1.Execute
	cmdFin1.ActiveConnection.Close
	
	Set cmdFin = Server.CreateObject("ADODB.Command")
	cmdFin.ActiveConnection = constrCOMINSpezaEXP
	cmdFin.CommandText = "UPDATE TBLEXPAPL_FIN Set CustomVal='" & intTotal & "', CustCurr = 'USD' WHERE ApplNo='" & ApplNumber & "'"
	cmdFin.Execute
	cmdFin.ActiveConnection.Close
	
	rstTotal1.Close
	Set rstTotal1 = Nothing
	
	rstTotal.Close
	Set rstTotal = Nothing
end if

set rstimpget = Server.CreateObject("ADODB.Recordset")
rstimpget.ActiveConnection = constrPEZAimp    
rstimpget.Source = "SELECT pezaimpcode, companyname, address1, address2, zonecode FROM tblImporters WHERE pezaimpcode='"  & Session("PIC") & "'"
rstimpget.CursorType = 0
rstimpget.CursorLocation = 2
rstimpget.LockType = 3
rstimpget.Open()
rstimpget_numRows = 0

if NOT 	rstimpget.EOF then
impcode = rstimpget("pezaimpcode")
impname = rstimpget("companyname")
impadd1 = rstimpget("address1")
impadd2 = rstimpget("address2")
impzoncod = rstimpget("zonecode")
end if
'get default province of origin
set rstimpget1 = Server.CreateObject("ADODB.Recordset")
rstimpget1.ActiveConnection = constrPEZAimp    
rstimpget1.Source = "SELECT prov_cod FROM tblZone WHERE ZoneCode = '" & impzoncod & "'"
rstimpget1.CursorType = 0
rstimpget1.CursorLocation = 2
rstimpget1.LockType = 3
rstimpget1.Open()
rstimpget1_numRows = 0

if NOT rstimpget1.EOF then
	Session("PO") = rstimpget1("prov_cod")
end if
'response.write Session("PO")

rstimpget.Close()
rstimpget1.Close()

set rstHSCode = Server.CreateObject("ADODB.Recordset")
rstHSCode.ActiveConnection = constrCOMINSpezaEXP	
rstHSCode.Source = "SELECT itemno, NoPack, InvValue, PackCode FROM tblEXPApl_Detail WHERE ApplNo='" & ApplNumber & "' ORDER BY ItemNo"
rstHSCode.CursorType = 0
rstHSCode.CursorLocation = 2
rstHSCode.LockType = 3
rstHSCode.Open()
rstHSCode_numRows = 0				
intNoPack = 0
intItemNo = 0
intConTotal = 0
intPackTotal = 0
intVal = 0

While NOT(rstHSCode.EOF)
    If IsNull(rstHSCode("NoPack")) Or rstHSCode("NoPack") = "" Then
        intNoPack = intNoPack + 0
    Else
        intNoPack = intNoPack + rstHSCode("NoPack")
    End If

    If IsNull(rstHSCode("PackCode")) Or rstHSCode("PackCode") = "" Then
        intPackCode = 0
    Else
        intPackCode = rstHSCode("PackCode")
    End If

    If IsNull(rstHSCode("InvValue")) Or rstHSCode("InvValue") = "" Then
        intVal = intVal + 0
    Else
        intVal = intVal + rstHSCode("InvValue")
    End If

	intItemNo = intItemNo + 1
	intConTotal = intConTotal + 1
	rstHSCode.MoveNext
Wend

set cmdInsert0 = Server.CreateObject("ADODB.Command")	
cmdInsert0.ActiveConnection = constrCOMINSpezaEXP	
cmdInsert0.CommandText = "UPDATE tblEXPApl_Master Set Items='" & intItemNo & "', ItemCon='" & intConTotal & "', Packs='" & intNoPack & "' WHERE ApplNo='" & ApplNumber & "'"	
cmdInsert0.CommandType = 1
cmdInsert0.CommandTimeout = 0
cmdInsert0.Prepared = true		
cmdInsert0.Execute()

set itemstat = Server.CreateObject("ADODB.Recordset")
itemstat.ActiveConnection = constrCOMINSpezaEXP  
itemstat.Source = "SELECT ItemCode, ItemNo, PTOPS_ROWID from tblEXPApl_Detail where applno = '"&ApplNumber&"' order by ItemNo desc"
itemstat.CursorType = 0
itemstat.CursorLocation = 2
itemstat.LockType = 3
itemstat.Open
	
if NOT itemstat.EOF then
	while NOT itemstat.EOF
		Dim hsCode, hsCodeTar, status
		hsCode = ""
		hsCodeTar = ""
		status = ""

		Dim useLocalDB
		useLocalDB = False

		' Check if PTOPS_ROWID exists and is valid
		If Not IsNull(itemstat("PTOPS_ROWID")) And itemstat("PTOPS_ROWID") <> 0 Then
			Response.LCID = 1046

			On Error Resume Next ' Catch API errors

			' --- Call API ---
			Dim http, url, jsonPayload, JSON, jsonData, dataArray, firstItem
			Set http = Server.CreateObject("MSXML2.XMLHTTP.6.0")
			url = ptopsLOELookupSingle
			http.Open "POST", url, False
			http.setRequestHeader "Accept", "application/json"
			http.setRequestHeader "Content-Type", "application/json"

			jsonPayload = "{""commodityCode"":""" & itemstat("Itemcode") & """,""PTOPS_ROWID"":" & itemstat("PTOPS_ROWID") & "}"
			http.Send jsonPayload

			If Err.Number <> 0 Or http.Status <> 200 Then
				useLocalDB = True ' API failed
				Err.Clear
			Else
				Set JSON = New JSONobject
				Set jsonData = JSON.parse(http.responseText)

				' Make sure data exists
				If jsonData.Exists("data") Then
					Set dataArray = jsonData("data")
					If dataArray.Count > 0 Then
						Set firstItem = dataArray.ItemAt(0)
						hsCode = firstItem("HsCode")
						hsCodeTar = firstItem("HsCode_Tar")
						itemStatus = firstItem("status")
					Else
						useLocalDB = True ' No data returned
					End If
				Else
					useLocalDB = True ' Invalid response
				End If
			End If

			On Error GoTo 0 ' Reset error handling
			
			Response.LCID = 1033

		Else
			useLocalDB = True ' PTOPS_ROWID missing or zero
		End If

		' --- Fallback to local database if needed ---
		If useLocalDB Then
			Dim ptopsROWID, itemstat1
			If IsNull(itemstat("PTOPS_ROWID")) Or itemstat("PTOPS_ROWID") = 0 Then
				ptopsROWID = " AND PTOPS_ROWID IS NULL"
			Else
				ptopsROWID = " AND PTOPS_ROWID = '" & itemstat("PTOPS_ROWID") & "'"
			End If

			Set itemstat1 = Server.CreateObject("ADODB.Recordset")
			itemstat1.ActiveConnection = constrPEZAexpPTOPS
			itemstat1.Source = "SELECT Status, HSCode, HSCode_Tar FROM tblExItem WHERE commoditycode = '" & itemstat("Itemcode") & "' " & ptopsROWID
			itemstat1.CursorType = 0
			itemstat1.CursorLocation = 2
			itemstat1.LockType = 3
			itemstat1.Open

			If Not itemstat1.EOF Then
				hsCode = itemstat1("HSCode")
				hsCodeTar = itemstat1("HSCode_Tar")
				itemStatus = itemstat1("Status")
			End If

		End If

		if hsCode <> "" then
			if itemStatus = "M" then
				timtim = "True"
			end if
			if itemStatus = "A" then
				timtim = "False"
			end if		  
			if itemStatus = "R" then
				rejectedItems = itemstat("ItemNo") & ", "  & rejectedItems
			end if		 
			
			set rstUTAP = Server.CreateObject("ADODB.Command")	
			rstUTAP.ActiveConnection = constrCOMINSpezaEXP
			rstUTAP.CommandText = "UPDATE tblEXPApl_Detail Set Regulated='" & timtim & "' WHERE itemno = '"&itemstat("ItemNo")&"' and applno = '"& ApplNumber&"'"	
			rstUTAP.CommandType = 1
			rstUTAP.CommandTimeout = 0
			rstUTAP.Prepared = true		
			rstUTAP.Execute()
			
			items = items
		else
			Items = items & "&nbsp;&nbsp;&nbsp;" & itemstat("ItemNo")
		end if
		 
		itemstat.movenext
		If useLocalDB Then
			itemstat1.Close
		End If
	wend
end if
itemstat.Close

if Session("UserID") = "" then
	  Response.redirect("https://www.intercommerce.com.ph") 
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

If Session("MM_conEpay") <> "" Then
	MM_DB = DecryptPassword(CStr(Session("MM_conEpay")))
Else
	MM_DB = DecryptPassword(CStr(Request("cn")))
End If

MM_DB = Session("db")

Response.Buffer = True
Response.Expires = -1441

'set forSGL = Server.CreateObject("ADODB.Recordset")
'forSGL.ActiveConnection = constrCOMINSpezaEXP  
'forSGL.Source = "SELECT count(*) as in_num from tblEXPApl_Detail"
'forSGL.CursorType = 0
'forSGL.CursorLocation = 2
'forSGL.LockType = 3
'forSGL.Open

set rstMode = Server.CreateObject("ADODB.Recordset")
rstMode.ActiveConnection = constrCOMINSpezaEXP  
if request.QueryString("ApplNo") <> "" then
	ApplNumber = request.QueryString("ApplNo")
end if
rstMode.Source = "SELECT * from tblEXPApl_Master where applno='" & ApplNumber & "'"
rstMode.CursorType = 0
rstMode.CursorLocation = 2
rstMode.LockType = 3
rstMode.Open
rstMode_numRows = 0

if not rstMode.EOF Then
	if session("mod_cod") = "" then
		Session("mod_cod") = rstMode("mdec")
	end if
	if session("mod_cod2") = "" then
		Session("mod_cod2") = rstMode("mdec2")
	end if
	strStatus = rstMode("Stat")
	Stats = strstatus
	strCreationDate = rstMode("CreationDate")
	'strCreationDate  
end if
rstMode.Close
Set rstMode = Nothing

'response.write session("mod_cod")
set rstDesc = Server.CreateObject("ADODB.Recordset")
rstDesc.ActiveConnection = constrCOMINScd  
rstDesc.Source = "SELECT moddesc from DmModDecexp where modcode = '" & Session("mod_cod") & "' AND cpl_cod = '"& Session("mod_cod2") &"'"
rstDesc.CursorType = 0
rstDesc.CursorLocation = 2
rstDesc.LockType = 3
rstDesc.Open
rstDesc_numRows = 0
	
If not rstDesc.EOF Then
	strMod_Dsc = rstDesc("moddesc")
else
	set rstDesc0 = Server.CreateObject("ADODB.Recordset")
	rstDesc0.ActiveConnection = constrCOMINSpezaEXP  
	rstDesc0.Source = "SELECT MDec, Mdec2 from tblEXPApl_MASTER where Applno = '" & ApplNumber & "'"
	rstDesc0.CursorType = 0
	rstDesc0.CursorLocation = 2
	rstDesc0.LockType = 3
	rstDesc0.Open
	rstDesc0_numRows = 0
	
	set rstDesc1 = Server.CreateObject("ADODB.Recordset")
	rstDesc1.ActiveConnection = constrCOMINScd  
	rstDesc1.Source = "SELECT moddesc from DmModDecexp where modcode = '" & rstDesc0("Mdec") & "' AND cpl_cod = '"& rstDesc0("Mdec2") &"'"
	rstDesc1.CursorType = 0
	rstDesc1.CursorLocation = 2
	rstDesc1.LockType = 3
	rstDesc1.Open
	rstDesc1_numRows = 0	
	
	if NOT rstDesc1.EOF then
		strMod_Dsc = rstDesc1("moddesc")
	end if
	rstDesc0.Close
	rstDesc1.Close
end if
rstDesc.Close

'---If session expires, redirect use to an error page.
If ApplNumber = "" Then 
	Response.redirect("err-expire.asp")
End If

'Session for Lookup modules
Session("DBSess") = CStr(Request("cn"))
Session("flgImpdec") = "True"

' *** Edit Operations: declare variables
MM_editAction = CStr(Request("URL"))
If (Request.QueryString <> "") Then
	MM_editAction = MM_editAction & "?" & Request.QueryString
End If

' boolean to abort record edit
MM_abortEdit = false

' query string to execute
MM_editQuery = ""

if request.form("txtHSCode") <> "" then

	set rstTBLIMPAPL13 = Server.CreateObject("ADODB.Recordset")
	rstTBLIMPAPL13.ActiveConnection = constrCOMINSpezaEXP    
	rstTBLIMPAPL13.Source = "SELECT TOP 1 ItemNo  FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' Order by ItemNo DESC"
	rstTBLIMPAPL13.CursorType = 0
	rstTBLIMPAPL13.CursorLocation = 2
	rstTBLIMPAPL13.LockType = 3
	rstTBLIMPAPL13.Open()
	rstTBLIMPAPL13_numRows = 0
					
	if NOT rstTBLIMPAPL13.EOF then
		newnum = rstTBLIMPAPL13("ItemNo") + 1
	else
		newnum = 1
	end if
	rstTBLIMPAPL13.Close

	If Trim(Request.form("timestamp")) = "" Then
		InvDate = "NULL"
	Else
		InvDate = "'" & Request.form("timestamp") & "'"
	End If

	Set MM_ADDITEM = Server.CreateObject("ADODB.Command")
	MM_ADDITEM.ActiveConnection = constrCOMINSpezaEXP  	
	MM_ADDITEM.CommandText = "INSERT INTO tblEXPApl_Detail (OCharges, IFreight, Applno, ItemNo, Marks1, Marks2, NoPack, PackCode, GoodsDesc1, InvNo, InvDate, HSCode, HSCode_Tar, COCode, ItemGWeight, ItemNWeight, ProcDesc, ExtCode, InvValue, InvCurr, SupVal1, LOANO, PTOPS_ROWID, ecai_no_list, quo_cod, Quo_dsc, ValMethodNum, ValMethodDesc, PrevDoc, itemcode) values ('0', '0', '"  & ApplNumber & "', '"& newnum &"', '" & UCase(Request.form("txtMarks1")) & "', '" & UCase(Request.form("txtMarks2")) & "', '" & Request.form("txtNoPack") & "', '" & UCase(Request.form("lstPkg_dsc")) & "', '" & UCase(Request.form("txtHSDsc")) & "', '" & UCase(Request.form("txtInvNo")) & "', " & InvDate & ", '" & Request.form("txtHSCode") & "', '" & Request.form("txtHSCode_Tar") & "', '" & Request.form("lstCOCode") & "', '" & Request.form("txtItemGWeight") & "', '" & Request.form("txtItemNWeight") & "', '" & Request.form("lstProcedure") & "', '000', '" & Request.form("txtInvValue") & "', 'USD', '" & Request.form("txtSupVal1") & "', '" & Request.form("HSUOM") & "', '" & Request.form("txtPTOPSRowId") & "', '" & Request.form("txtEcaiList") & "', '" & Request.form("txtQuo_cod") & "', '" & Request.form("txtQuo_desc") & "', '" & Request.form("txtValMethodNum") & "', '" & Request.form("txtValMethodDesc") & "', '" & UCase(Request.form("txtPrevDoc")) & "', '" & UCase(Request.form("txtitemcode")) & "')"
	'response.write MM_ADDITEM.CommandText
	MM_ADDITEM.Execute
end if

' *** Insert Record: set variables
If (CStr(Request("MM_insert")) <> "") Then
	'MM_editConnection = constrCOMINScd
	MM_editConnection = constrCOMINSpezaEXP  
	MM_editTable = "dbo.tblEXPApl_Master"
	
	'---Set target URLs before saving
	If Request.form("txtButton")  = "btnItem" Then
		'MM_editRedirectUrl = "item-page.asp?cn=" & EncryptPassword(CStr(MM_DB))
		MM_editRedirectUrl = "ptops_ed_itemPEZAEXPexpress.asp"
	ElseIf  Request.form("txtButton")  = "btnFinancial" or Request.form("txtButton")  = "btnFinancial2" Then
		MM_editRedirectUrl = "ptops_ed_finPEZAEXPexpress.asp"
	'ElseIf  Request.form("txtButton")  = "btnConsolidated9" or Request.form("txtButton")  = "btnConsolidated2" Then
		'MM_editRedirectUrl = "cws_c-itemEXP.asp"	  
	ElseIf  Request.form("txtButton")  = "btnSave" Then
		MM_editRedirectUrl = "ptops_ed_impdecPEZAEXPexpress.asp"	  	 
		Session("flgSave") = "True" 
	Else	  
		MM_editRedirectUrl = "ptops_ed_impdecPEZAEXPexpress.asp"
	End If

	'---Redirect immediately (dont save) if status <> C or I
	If Stats = "S" OR Stats = "AG" OR Stats = "AS" OR Stats = "ER" OR Stats = "FP" Then
		'Response.redirect(MM_editRedirectUrl)
		
		MM_editRedirectUrl = MM_editRedirectUrl & "?cn=" & EncryptPassword(CStr(MM_DB)) & "&rl=" & Request("rl")
		Response.redirect(MM_editRedirectUrl)
	End If
	
	MM_fieldsStr  = "txtItems|value|txtPackages|value|txtManifest|value|lstExporter|value|txtSuppAddr1|value|txtSuppAddr2|value|txtSuppAddr3|value|txtSuppAddr4|value|txtVessel|value|txtLocalC|value|lstCountry|value|lstCountry1|value|lstTransPort|value|lstDestPort|value|lstModeOfTransport|value"
	MM_columnsStr = "Items|',none,''|Packs|',none,''|Manifest|',none,''|ConName|',none,''|ConAdr1|',none,''|ConAdr2|',none,''|ConAdr3|',none,''|ConAdr4|',none,''|Vessel|',none,''|LocalCarrier|',none,''|Cexp|',none,''|Cdest|',none,''|PortOfLoad|',none,''|PortOfDept|',none,''|modeOfTransport|',none,''"

	' create the MM_fields and MM_columns arrays
	MM_fields = Split(MM_fieldsStr, "|")
	MM_columns = Split(MM_columnsStr, "|")
  
	' set the form values
	For i = LBound(MM_fields) To UBound(MM_fields) Step 2
		MM_fields(i+1) = CStr(Ucase(Request.Form(MM_fields(i))))
	Next

	' append the query string to the redirect URL
	If (MM_editRedirectUrl <> "" And Request.QueryString <> "") Then
		If (InStr(1, MM_editRedirectUrl, "?", vbTextCompare) = 0 And Request.QueryString <> "") Then
			MM_editRedirectUrl = MM_editRedirectUrl & "?" & Request.QueryString
		Else
			MM_editRedirectUrl = MM_editRedirectUrl & "&" & Request.QueryString
		End If
	End If
End If

' *** Insert Record: construct a sql insert statement and execute it
If (CStr(Request("MM_insert")) <> "") Then
	' create the sql insert statement
	MM_tableValues = ""
	MM_dbValues = ""
	For i = LBound(MM_fields) To UBound(MM_fields) Step 2
		FormVal = MM_fields(i+1)
		MM_typeArray = Split(MM_columns(i+1),",")
		Delim = MM_typeArray(0)
		If (Delim = "none") Then Delim = ""
		AltVal = MM_typeArray(1)
		If (AltVal = "none") Then AltVal = ""
		EmptyVal = MM_typeArray(2)
		If (EmptyVal = "none") Then EmptyVal = ""
		If (FormVal = "") Then
			FormVal = EmptyVal
		Else
			If (AltVal <> "") Then
				FormVal = AltVal
			ElseIf (Delim = "'") Then  ' escape quotes
				FormVal = "'" & Replace(FormVal,"'","''") & "'"
			Else
				FormVal = Delim + FormVal + Delim
			End If
		End If
		If (i <> LBound(MM_fields)) Then
			MM_tableValues = MM_tableValues & ","
			MM_dbValues = MM_dbValues & ","
		End if
		MM_tableValues = MM_tableValues & MM_columns(i)
		MM_dbValues = MM_dbValues & FormVal
	Next

	'-- determine country of origin
	set rsDetail = Server.CreateObject("ADODB.Recordset")
	rsDetail.ActiveConnection = constrCOMINSpezaEXP  
	rsDetail.Source = "SELECT * FROM tblEXPApl_Detail WHERE ApplNo = '" & ApplNumber & "'"
	rsDetail.CursorType = 0
	rsDetail.CursorLocation = 2
	rsDetail.LockType = 3
	rsDetail.Open
	
	if not rsDetail.EOF then
		strCOrg = rsDetail("CoCode")
	else
		strCOrg = ""
	end if
	
	intConFlag = 0
	do while not rsDetail.Eof
		if strCOrg <> rsDetail("CoCode") then 
			strCOrg = "MANY"
		end if
		if trim(rsDetail("Cont1")) + trim(rsDetail("Cont2")) + trim(rsDetail("Cont3")) + trim(rsDetail("Cont4")) <> "" then
		intConFlag = 1
		end if
		rsDetail.movenext
	loop
	rsDetail.Close
	  	
	'-- set office clearance variables
	set rstOffClear = Server.CreateObject("ADODB.Recordset")
	rstOffClear.ActiveConnection = constrCOMINScd    
	rstOffClear.Source = "SELECT OffClrCod, OffClrName FROM DmOffClr WHERE OffClrCod = '" & Request("lstOffClear") & "'"
	rstOffClear.CursorType = 0
	rstOffClear.CursorLocation = 2
	rstOffClear.LockType = 3
	rstOffClear.Open()
	rstOffClear_numRows = 0
	
	If Not rstOffClear.EOF Then
		strOffCode = Request.form("lstOffClear")
		strOffName = rstOffClear("OffClrName")
	End If
	rstOffClear.Close
	Set rstOffClear = Nothing

	'--Opens rstTBLIMPAPL Recordset to check if record exists
	set rstTBLIMPAPL = Server.CreateObject("ADODB.Recordset")
	rstTBLIMPAPL.ActiveConnection = constrCOMINSpezaEXP    
	rstTBLIMPAPL.Source = "SELECT *  FROM dbo.tblEXPApl_Master   WHERE Applno = '" & ApplNumber & "'"
	rstTBLIMPAPL.CursorType = 0
	rstTBLIMPAPL.CursorLocation = 2
	rstTBLIMPAPL.LockType = 3
	rstTBLIMPAPL.Open()
	rstTBLIMPAPL_numRows = 0
	
	If Request("cboMonth") <> "" AND Request("cboDay") <> "" AND Request("cboYear") <> "" Then
		strDate = Request("cboMonth") & "/" & Request("cboDay") & "/" & Request("cboYear")
	Else
		strDate = ""
	End If
  

   '--If Record Doesn't Exist, INSERT into table.  Otherwise, UPDATE the corresponding record.  		
   If rstTBLIMPAPL.EOF Then  				
	
	  'Create a New Record	  
	  MM_editQuery = "insert into " & MM_editTable & " (DecName, DecAdr1, DecAdr2, DecAdr3, DecTin, ProvOfOrig, LGoods, CreationDate, ApplNo, MDec, MDec2, OffClear, Stat,  Purpose, WayBill, TotContainers, Reason, IAN, " & MM_tableValues & ") values ('" & strDate & "','" & UCase(Session("brknam")) & "','" & UCase(Session("brkad1")) & "','" & UCase(Session("brkad2")) & "','" & UCase(Session("brkad3")) & "','" & Session("uTin") & "','" & Request.form("ProvofOrig") & "','" & Request.form("lstLGoods") & "', '" & Year(Now) & "/" & Month(Now) & "/" & Day(Now) & "','" & ApplNumber & "', '" & Session("mod_cod") & "', '" & Session("mod_cod2") & "', '" & strOffCode & "', '" & strStatus & "', '" & Request.form("lstPurpose") & "', '" & UCase(Request.form("txtBOL")) & "', '" & Request.form("txtTotCont") & "', '" & Request.form("txtOth") & "', 'isPTOPS', " & MM_dbValues & ")"

	Else
		'Update the Existing Record
		MM_editQuery = "update dbo.tblEXPApl_Master Set ConName='" & UCase(Request.form("lstExporter")) & "', ConAdr1='" & UCase(Request.form("txtSuppAddr1")) & "', ConAdr2='" & UCase(Request.form("txtSuppAddr2")) & "', ConAdr3='" & UCase(Request.form("txtSuppAddr3")) & "', ConAdr4='" & UCase(Request.form("txtSuppAddr4")) & "', Mdec='" & Session("mod_cod") & "', MDec2='" & Session("mod_cod2") & "', OffClear='" & strOffCode & "', Purpose='" & Request.form("lstPurpose") & "', Manifest='" & Ucase(Request.form("txtManifest")) & "', DecName='" & UCase(Replace(Session("brknam"), "'", "''")) & "', DecAdr1='" & UCase(Replace(Session("brkad1"), "'", "''")) & "', DecAdr2='" & UCase(Session("brkad2")) & "', DecAdr3='" & UCase(Session("brkad3")) & "', DecTin='" & Session("uTin") & "', Vessel='" & UCase(Request.form("txtVessel")) & "', LocalCarrier='" & UCase(Request.form("txtLocalC")) & "', Cexp='" & Request.form("lstCountry") & "', CDest='" & Request.form("lstCountry1") & "', PortOfLoad='" & Request.form("lstTransPort") & "', PortOfDept='" & Request.form("lstDestPort") & "', ProvOfOrig='" & Request.form("ProvofOrig") & "', LGoods='" & Request.form("lstLGoods") & "', WayBill= '" & UCase(Request.form("txtBOL")) & "', TotContainers= '" & Request.form("txtTotCont") & "', Reason= '" & UCase(Request.form("txtOth")) & "',	ExpCode='" & impcode & "', ExpName='" & UCase(impname) & "', ExpAdr1='" & UCase(impadd1) & "', ExpAdr2='" & UCase(impadd2) & "', modeOfTransport = '" & Request.form("lstModeOfTransport") & "' WHERE ApplNo='" & ApplNumber & "'" 
	Session("Query") = MM_editQuery
    Response.write("<script type=""text/javascript"">alert(""Application Updated"")</script>")  
	End If	  
	'---Check If Record already exists for FIN
	set rstFinancial = Server.CreateObject("ADODB.Recordset")
	rstFinancial.ActiveConnection = constrCOMINSpezaEXP
	rstFinancial.Source = "SELECT * FROM tblEXPApl_FIN WHERE ApplNo='" & ApplNumber & "'"
	rstFinancial.CursorType = 0
	rstFinancial.CursorLocation = 2
	rstFinancial.LockType = 3
	rstFinancial.Open()
	rstFinancial_numRows = 0
	
	If (rstFinancial.EOF) Then
		  MM_editQueryFIN = "insert into tblEXPApl_FIN (Applno, TDelivery, TPayment, BankCode, BranchCode, BankRef, IntRef, WOBankCharge, Forex) values ('" & ApplNumber & "', '" & Request.form("lstTDelivery") & "', '" & Request.form("lstTPayment") & "', '" & Request.form("txtBank") & "', '" & Request.Form("txtBranch") & "', '" & Ucase(Request.form("txtBankRef")) & "', '" & Request.Form("txtIntRef") & "','0','0')"			  
	Else	  
		  MM_editQueryFIN = "Update tblEXPApl_FIN Set TDelivery='" & Request.form("lstTDelivery") & "', TPayment='" & Request.form("lstTPayment") & "', BankCode='" & Request.form("txtBank") & "', BranchCode='" & Request.form("txtBranch") & "', BankRef='" &  Ucase(Request.form("txtBankRef")) & "', IntRef='" & Request.form("txtIntRef") & "' WHERE ApplNo='" & ApplNumber & "'"
	End If
	rstFinancial.Close()
  
	If NOT rstTBLIMPAPL.EOF Then 
		strStatus = rstTBLIMPAPL("Stat")
	ELSEIf Session("Fin_status") = "True" AND Session("Item_Status") = "True"  Then
		If strStatus = "I" Then	strStatus = "C"
			rstTBLIMPAPL("Stat") = strStatus
			rstTBLIMPAPL.Update
		Else 
			if Session("Importer") then
				strStatus = Stats
			else
				strStatus = "I"
			end if
	End If
	
	Set rstTBLIMPAPL = Nothing
	
	If (Not MM_abortEdit) Then
		' execute the insert (Master)
		Set MM_editCmd = Server.CreateObject("ADODB.Command")
		MM_editCmd.ActiveConnection = constrCOMINSpezaEXP  	
		MM_editCmd.CommandText = MM_editQuery
		'response.write MM_editQuery
		MM_editCmd.Execute
		
		MM_editCmd.ActiveConnection.Close
		
		' execute the insert (FIN)
		Set MM_editCmdF = Server.CreateObject("ADODB.Command")
		MM_editCmdF.ActiveConnection = constrCOMINSpezaEXP  	
		MM_editCmdF.CommandText = MM_editQueryFIN
		MM_editCmdF.Execute
		
		MM_editCmdF.ActiveConnection.Close
		
		If (MM_editRedirectUrl <> "") Then
			Response.Redirect(MM_editRedirectUrl)
		End If
	End If
End If

Dim rstTBLIMPAPL__strApplNo
rstTBLIMPAPL__strApplNo = "%"

if (ApplNumber <> "") then rstTBLIMPAPL__strApplNo = ApplNumber
set rstTBLIMPAPL = Server.CreateObject("ADODB.Recordset")
rstTBLIMPAPL.ActiveConnection = constrCOMINSpezaEXP  
rstTBLIMPAPL.Source = "SELECT *  FROM tblEXPApl_Master  WHERE Applno = '" & ApplNumber & "'"
rstTBLIMPAPL.CursorType = 0
rstTBLIMPAPL.CursorLocation = 2
rstTBLIMPAPL.LockType = 3
rstTBLIMPAPL.Open()
rstTBLIMPAPL_numRows = 0

if not rstTBLIMPAPL.EOF then
	session("OffClear") = rstTBLIMPAPL("OffClear")
else
	session("OffClear") = ""
end if

If NOT(rstTBLIMPAPL.EOF) Then
	intItemCon = rstTBLIMPAPL("ItemCon")
	intItem = rstTBLIMPAPL("Items")
	intPack = rstTBLIMPAPL("Packs")
	
	If IsNull(rstTBLIMPAPL("ItemCon")) Then intItemCon = 0
	If IsNull(rstTBLIMPAPL("Items")) Then intItem = 0
	If IsNull(rstTBLIMPAPL("Packs")) = "" Then intPack = 0		
	if session("Importer") then
		Stats = rstTBLIMPAPL("Stat")
	end if
Else
	intItemCon = 0
	intItem = 0
	intPack = 0
	Stats = "I"
End If	

If (Stats = "C" OR Stats = "I" OR Stats = "") and not session("Importer") Then 
	flgEnabled = "True"
end if
strStatus = Stats

    if UCASE(session("cltcode")) = "DHLEXA" then
        if NOT rstTBLIMPAPL.EOF then
            set rsBLCheck = Server.CreateObject("ADODB.Recordset")
	        rsBLCheck.ActiveConnection = constrPEZAexp  
	        rsBLCheck.Source = "SELECT WayBill FROM tblexpapl_master where waybill='" & rstTBLIMPAPL("WayBill") & "' and applno <> '"& ApplNumber &"'"
	        rsBLCheck.CursorType = 0
	        rsBLCheck.CursorLocation = 2
	        rsBLCheck.LockType = 3
	        rsBLCheck.Open
	        rsBLCheck_numRows = 0

            if NOT rsBLCheck.EOF then
                WaybillCheck = rsBLCheck("wayBill")
            else
                WaybillCheck = ""
            end if
        end if 
    else
        WaybillCheck = ""
    end if
set rstGBCTYTAB = Server.CreateObject("ADODB.Recordset")
rstGBCTYTAB.ActiveConnection = constrCOMINScd  
rstGBCTYTAB.Source = "SELECT *  FROM dbo.DmCityOrigin  ORDER BY cityDisc"
rstGBCTYTAB.CursorType = 0
rstGBCTYTAB.CursorLocation = 2
rstGBCTYTAB.LockType = 3
rstGBCTYTAB.Open
rstGBCTYTAB_numRows = 0

set rstGBCTYTAB0 = Server.CreateObject("ADODB.Recordset")
rstGBCTYTAB0.ActiveConnection = constrCOMINScd  
rstGBCTYTAB0.Source = "SELECT *  FROM dbo.DmCityOrigin  ORDER BY cityDisc"
rstGBCTYTAB0.CursorType = 0
rstGBCTYTAB0.CursorLocation = 2
rstGBCTYTAB0.LockType = 3
rstGBCTYTAB0.Open
rstGBCTYTAB0_numRows = 0

set rstGBCUOTAB2 = Server.CreateObject("ADODB.Recordset")
rstGBCUOTAB2.ActiveConnection = constrCOMINScd  
rstGBCUOTAB2.Source = "SELECT * FROM dbo.DmTransMod ORDER BY transDesc ASC"
rstGBCUOTAB2.CursorType = 0
rstGBCUOTAB2.CursorLocation = 2
rstGBCUOTAB2.LockType = 3
rstGBCUOTAB2.Open
rstGBCUOTAB2_numRows = 0

set rstGBCUOTAB3 = Server.CreateObject("ADODB.Recordset")
rstGBCUOTAB3.ActiveConnection = constrCOMINScd  
rstGBCUOTAB3.Source = "SELECT *  FROM dbo.DmOffClr ORDER BY offClrName ASC"
rstGBCUOTAB3.CursorType = 0
rstGBCUOTAB3.CursorLocation = 2
rstGBCUOTAB3.LockType = 3
rstGBCUOTAB3.Open
rstGBCUOTAB3_numRows = 0

set rstGBCUOTAB3a = Server.CreateObject("ADODB.Recordset")
rstGBCUOTAB3a.ActiveConnection = constrCOMINScd
rstGBCUOTAB3a.Source = "SELECT *  FROM dbo.DmOffClr ORDER BY offClrName ASC"
rstGBCUOTAB3a.CursorType = 0
rstGBCUOTAB3a.CursorLocation = 2
rstGBCUOTAB3a.LockType = 3
rstGBCUOTAB3a.Open
rstGBCUOTAB3a_numRows = 0

set rstPURP = Server.CreateObject("ADODB.Recordset")
rstPURP.ActiveConnection = constrCOMINSpezaEXP  
rstPURP.Source = "SELECT *  FROM tblPURPOSE ORDER BY PurposeName ASC"
rstPURP.CursorType = 0
rstPURP.CursorLocation = 2
rstPURP.LockType = 3
rstPURP.Open
rstPURP_numRows = 0

set rstPAYP = Server.CreateObject("ADODB.Recordset")
rstPAYP.ActiveConnection = constrCOMINSpezaEXP  
rstPAYP.Source = "SELECT *  FROM tblPayProc ORDER BY PayProcName ASC"
rstPAYP.CursorType = 0
rstPAYP.CursorLocation = 2
rstPAYP.LockType = 3
rstPAYP.Open
rstPAYP_numRows = 0

set rstGBSHDTAB = Server.CreateObject("ADODB.Recordset")
rstGBSHDTAB.ActiveConnection = constrCOMINScd  
rstGBSHDTAB.Source = "SELECT SHD_COD, SHD_NAM as SHD_NAM_Adj, SHD_COD + ' - ' + SHD_NAM AS LocGood, CASE WHEN UPPER(SHD_NAM) = '999 - IMPORTERS PREMISE' THEN '999 - OTHER LOCATION' ELSE SHD_NAM END AS SHD_NAM FROM dbo.GBSHDTAB_D ORDER BY SHD_NAM_Adj"
rstGBSHDTAB.CursorType = 0
rstGBSHDTAB.CursorLocation = 2
rstGBSHDTAB.LockType = 3
rstGBSHDTAB.Open
rstGBSHDTAB_numRows = 0

set rstGBPRVORG = Server.CreateObject("ADODB.Recordset")
rstGBPRVORG.ActiveConnection = constrCOMINScd  
rstGBPRVORG.Source = "SELECT prov_cod, prov_dsc from GBPRVORG order by prov_dsc"
rstGBPRVORG.CursorType = 0
rstGBPRVORG.CursorLocation = 2
rstGBPRVORG.LockType = 3
rstGBPRVORG.Open
rstGBPRVORG_numRows = 0

set rstFin = Server.CreateObject("ADODB.Recordset")
rstFin.ActiveConnection = constrCOMINSpezaEXP
rstFin.Source = "SELECT * FROM tblEXPApl_FIN Where ApplNo='" & ApplNumber & "'"
rstFin.CursorType = 0
rstFin.CursorLocation = 2
rstFin.LockType = 3
rstFin.Open()
rstFin_numRows = 0

' save file if not existing
set rstCreateRec = Server.CreateObject("ADODB.Recordset")
rstCreateRec.ActiveConnection = constrCOMINSpezaEXP  
rstCreateRec.Source = "SELECT Applno, ExpCode, DecTIN FROM tblEXPApl_Master where applno='" & ApplNumber & "'"
rstCreateRec.CursorType = 0
rstCreateRec.CursorLocation = 2
rstCreateRec.LockType = 3
rstCreateRec.Open
rstCreateRec_numRows = 0

'---Get Latest Exchange Rate
set rstExchRate = Server.CreateObject("ADODB.Recordset")
rstExchRate.ActiveConnection = constrCOMINScd
rstExchRate.Source = "SELECT rat_exc from GBRATTAB where cur_cod='USD' order by eea_dov desc"
rstExchRate.CursorType = 0
rstExchRate.CursorLocation = 2
rstExchRate.LockType = 3
rstExchRate.Open
rstExchRate_numRows = 0

ExchRate = rstExchRate("rat_exc")
rstExchRate.Close
set rstExchRate = Nothing

if rstCreateRec.eof then

	Set MM_SAVEREC1 = Server.CreateObject("ADODB.Command")
	MM_SAVEREC1.ActiveConnection = constrCOMINSpezaEXP  	
	'SET IDENTITY_INSERT tblEXPApl_Master ON
	MM_SAVEREC1.CommandText = "insert into tblEXPApl_Master (Applno, DECTIN, CreationDate, Stat, DECname, cltcode, ExpCode, ExpName, ExpAdr1, ExpAdr2, RegOfc, ConTIN, mdec, mdec2, Exhrate, SenderID, IAN) values ('"  & ApplNumber & "', '" & Session("Btin") & "','" & FormatDateTime(Now) & "', 'I', '" & UCase(mid(Session("brknam"),1,35)) & "', '" & UCase(Session("cltcode")) & "', '" & impcode & "', '" & UCase(impname) & "', '" & UCase(impadd1) & "', '" & UCase(impadd2) & "', '" & impzoncod & "', '" & Session("cTIN") & "', '" & Session("mod_cod") & "', '" & Session("mod_cod2") & "','"& ExchRate &"', '" & Session("UserID") & "', 'isPTOPS')"
	'response.write rstimpget.source
	'response.write MM_SAVEREC1.CommandText
	MM_SAVEREC1.Execute
	
	'MM_SAVEREC1.ActiveConnection.Close
else
	'check analog devices applnos
	if rstCreateRec("ExpCode") = "AND26372" AND rstCreateRec("DecTIN") <> "000657268" then
		if strStatus = "" OR strStatus = "I" OR strStatus = "C" then
			Set MM_SAVEREC1 = Server.CreateObject("ADODB.Command")
			MM_SAVEREC1.ActiveConnection = constrCOMINSpezaEXP	
			MM_SAVEREC1.CommandText = "UPDATE tblEXPApl_Master Set ExpCode='ADG10038', ExpName='ANALOG DEVICES GEN. TRIAS, INC.', ExpAdr1='GATEWAY BUSINESS PARK' WHERE ApplNo='" & ApplNumber & "'"
			MM_SAVEREC1.Execute
			MM_SAVEREC1.ActiveConnection.Close
		end if
	end if
end if

set FedexTIN = Server.CreateObject("ADODB.Recordset")	
FedexTIN.ActiveConnection = constrCOMINScd	
FedexTIN.Source = "SELECT EXP_TIN FROM cwsexporter where cltcode like 'FEDEX%' AND (exp_tin = '" & Session("cTIN") & "' or exp_tin = '" & Session("cTIN") & "000')"	
FedexTIN.CursorType = 0	
FedexTIN.CursorLocation = 2	
FedexTIN.LockType = 3	
FedexTIN.Open	
FedexTIN_numRows = 0	
if NOT FedexTIN.EOF AND session("btin") = "10825015300" then	
    FedexClient = "YES" 	
else	
    FedexClient = "NO"	
end if 

<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: STMO -->
If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") OR (UCase(Session("UserID")) = "KWETICLARK" AND Session("cltcode") ="KWECLARK") Then
	If UCase(Session("lstExporter")) = "TI (PHILIPPINES) INC. (BCEZ)" THEN
		SQL = "SELECT *  FROM CWSSTMO  WHERE cltcode='" & Session("cltcode") & "' AND (STMO_Tin='" & Session("cTIN") & "' OR STMO_Tin='" & Session("cTIN") & "000') ORDER BY STMO_name "
	Elseif UCase(Session("lstExporter")) = "TI (PHILIPPINES) INC. (CLTI)" THEN
		SQL = "SELECT *  FROM CWSSTMO  WHERE cltcode='" & Session("cltcode") & "' AND (STMO_Tin='" & Session("cTIN") & "' OR STMO_Tin='" & Session("cTIN") & "001') ORDER BY STMO_name "
	Else
		SQL = "SELECT *  FROM CWSSTMO  WHERE cltcode='" & Session("cltcode") & "' AND (STMO_Tin='" & Session("cTIN") & "' OR STMO_Tin='" & Session("cTIN") & "000') ORDER BY STMO_name "
	End If

	set rsSTMO = Server.CreateObject("ADODB.Recordset")
	rsSTMO.ActiveConnection = constrCOMINScd  
	rsSTMO.Source = SQL
	rsSTMO.CursorType = 0
	rsSTMO.CursorLocation = 2
	rsSTMO.LockType = 3
	rsSTMO.Open
	rsSTMO_numRows = 0
End If

' check if the HSCode Outdated or nah
set rsHSCode = Server.CreateObject("ADODB.Recordset")
rsHSCode.ActiveConnection = constrCOMINSpezaEXP
rsHSCode.Source = "SELECT ItemNo, HSCode, HSCODE_TAR FROM TBLEXPAPL_DETAIL WHERE applno = '" & ApplNumber & "' order by itemno asc"
rsHSCode.CursorType = 0
rsHSCode.CursorLocation = 2
rsHSCode.LockType = 3
rsHSCode.Open()
rsHSCode_numRows = 0

while not rsHSCOde.eof 

	set rsHSCodeStat = Server.CreateObject("ADODB.Recordset")
	rsHSCodeStat.ActiveConnection = constrPEZAimp
	rsHSCodeStat.Source = "SELECT * FROM gbtartab WHERE hs6_cod+tar_pr1='"&rsHSCode("HSCode")&"' AND tar_pr2='"&rsHSCode("HSCode_Tar")&"'"
	rsHSCodeStat.CursorType = 0
	rsHSCodeStat.CursorLocation = 2
	rsHSCodeStat.LockType = 3
	rsHSCodeStat.Open()
	rsHSCodeStat_numRows = 0
	if rsHSCodeStat.eof then
		HSCodestat_ItemNo = rsHSCode("ItemNo") &  ", " & HSCodestat_ItemNo
	end if
rsHSCode.MoveNext()
wend

If AllowedDHLTI = "TRUE" Then
	'check if there's regulated item/s
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
	
			ImpMon = ImpMon &  " " & rstDtail1("ItemNo")
		
		End if
		
		rstDtail1.movenext
	wend
	rstDtail1.Close
	
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
	
	'if NOT rstINSCASHBAL.EOF then
	if NOT IsNull(rstINSCASHBAL("INSCASHBAL")) then
		INSCASHBAL = rstINSCASHBAL("INSCASHBAL")
	else
		INSCASHBAL = 0
	end if
	
	'INSCharge = 45
	'If Session("Membership") = "AFPI" OR Session("Membership") = "PISFA" Then
		'INSCharge = 35
	'Else
		INSCharge = 45
	'End If
	
	if UCase(Session("cltcode")) = "FEDEX" OR UCase(Session("cltcode")) = "FEDEXP" then
		INSCharge = 44.80
	end if
	
	'for Eaton New Rates
	if Session("PIC") = "CIP31099" OR Session("PIC") = "CIP28496" then
		INSCharge = 40
	end if
	
	rstINSCASHBAL.Close
End If
' response.write HSCodestat_ItemNo

' PTOPS AEDS
set rstCheckEcaiNo = Server.CreateObject("ADODB.Recordset")
rstCheckEcaiNo.ActiveConnection = constrCOMINSpezaEXP
rstCheckEcaiNo.Source = "SELECT ITEMNO, HSCODE, HSCODE_TAR, ITEMCODE, PTOPS_ROWID  FROM tblEXPApl_Detail Where ApplNo='" & ApplNumber & "'"
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

' Validation Check: If at least one has ecai_no, then all must have it
If hasEcaiNo And Not allHaveEcaiNo Then
    Response.Write "<script>alert('The following Item No. have ECAI number: " & hiddenFieldValues & "\nPlease ensure that all item no. have ecai number.');</script>"
End If

rstCheckEcaiNo.Close()
Set rstCheckEcaiNo = Nothing
'END: PTOPS AEDS
%>

<html>
<head>
<title>InterCommerce Network Services - Create/Open Application</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../global.css" type="text/css">
<SCRIPT LANGUAGE="Javascript">
<!--
function Set_Button(strButton) {
        var varImporter = document.frmAdd["hdnImporter"].value;
        document.forms[0].txtButton.value = strButton;
        Validate_form();       
//        if (!varImporter) {
//                Validate_form();
//        } else {
//              document.frmAdd.submit();   
//        } 
}

function Set_Button1(strButton1) {
        var varImporter1 = document.frmAdd["hdnImporter"].value;
        document.forms[0].txtButton.value = strButton1;
        Validate_form1();       
}

// start: CRF PEZA - ENHANCEMENT OF THE E-AEDS v1.1 
function onModeOfTransport(port) {
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "getModeOfTransport.asp", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	
	xhr.onload = function () {
		if (xhr.status === 200) {
			var response = JSON.parse(xhr.responseText);
			var inputElement = document.getElementById("lstModeOfTransport");
			if (Array.isArray(response) && response.length > 0) {
                offclrmode = response[0].offclrmode;
            } else if (response && response.offclrmode) {
                offclrmode = response.offclrmode;
            }

			// Set the value of the input field
			inputElement.value = offclrmode || "None";
			
            var btnCont = document.getElementById("btnCont");
			
			if (inputElement.value == "BY AIR") {
                btnCont.disabled = true;
                btnCont.style.cursor = "not-allowed";
            } else {
                btnCont.disabled = false;
                btnCont.style.cursor = "pointer";
            }
			
		} else {
			console.error("Failed to fetch data. Status: " + xhr.status);
		}
	};
	
	xhr.send("port=" + encodeURIComponent(port));
}

document.addEventListener('DOMContentLoaded', function() {
	var inputElement = document.getElementById("lstModeOfTransport");
	var btnCont = document.getElementById("btnCont");
	
	if (inputElement.value == "BY AIR") {
		btnCont.disabled = true;
		btnCont.style.cursor = "not-allowed";
	} else {
		btnCont.disabled = false;
		btnCont.style.cursor = "pointer";
	}
});
// end: CRF PEZA - ENHANCEMENT OF THE E-AEDS v1.1

function Validate_form1(){

if (document.frmAdd.txtHSCode.value == ""){
    alert("Please select exportables.");
	document.frmAdd.txtHSCode.focus();
	}
<%if UCASE(session("cltcode")) = "DHLEXA" then %>	
else if (document.frmAdd.txtHSCode.value != "" && document.frmAdd.txtHSCode.value.substring(0,2).match("01")) {
	alert("HSCode starting from 01 is not allowed.");
	document.frmAdd.txtHSCode.focus();
	} 
<%end if %>
else if (document.frmAdd.txtHSCode_Tar.value == ""){
    alert("Please select exportables.");
	document.frmAdd.btnHSCode.focus();
	}
else if (document.frmAdd.txtMarks1.value == ""){
	alert("Please enter marks and numbers1.");
	document.frmAdd.txtMarks1.focus();
	}
else if (document.frmAdd.txtNoPack.value == ""){
	alert("Please enter number of packages.");
	document.frmAdd.txtNoPack.focus();
	}
else if (isNaN(document.frmAdd.txtNoPack.value)) {
	alert("Please enter a valid value for Number of Packages.");document.frmAdd.txtNoPack.focus();
	}
else if (document.frmAdd.txtNoPack.value == "0"){
	alert("No. of Packages in item 1 must not be 0");
	document.frmAdd.txtNoPack.focus();
	}
else if (document.frmAdd.txtNoPack.value.indexOf('.') >= 0) {
	alert("Decimals are not allowed for the number of packages.");
	document.frmAdd.txtNoPack.focus();
	}
else if (parseFloat(document.frmAdd.txtNoPack.value) > 9999999999) {
	alert("Please enter a valid number for the number of packages.");
	document.frmAdd.txtNoPack.focus();
	}
else if (document.frmAdd.txtInvNo.value == ""){
	alert("Please enter Invoice Number.");
	document.frmAdd.txtInvNo.focus();
	}
<%if len(request.form("timestamp")) = 10 then%>
else if (document.frmAdd.timestamp.value == "" || document.frmAdd.timestamp.value.length != 10 || document.frmAdd.timestamp.value.substring(2,3) != "/" || document.frmAdd.timestamp.value.substring(5,6) != "/"){
	alert("Please enter Invoice Date.");
	document.frmAdd.timestamp.focus();
	}
<%end if%>
<%if len(request.form("timestamp")) = 9 then%>
else if (document.frmAdd.timestamp.value == "" || document.frmAdd.timestamp.value.length != 9 || (document.frmAdd.timestamp.value.substring(2,3) != "/" && document.frmAdd.timestamp.value.substring(1,2) != "/") || (document.frmAdd.timestamp.value.substring(5,6) != "/" && document.frmAdd.timestamp.value.substring(3,4) != "/")){
	alert("Please enter Invoice Date.");
	document.frmAdd.timestamp.focus();
	}
<%end if%>
<%if len(request.form("timestamp")) = 8 then%>
else if (document.frmAdd.timestamp.value == "" || document.frmAdd.timestamp.value.length != 8 || document.frmAdd.timestamp.value.substring(1,2) != "/" || document.frmAdd.timestamp.value.substring(3,4) != "/"){
	alert("Please enter Invoice Date.");
	document.frmAdd.timestamp.focus();
	}
<%end if%>
else if (document.frmAdd.txtItemGWeight.value == "") {
	alert("Please enter a value for Gross Weight.");
	document.frmAdd.txtItemGWeight.focus();
	}
else if (isNaN(document.frmAdd.txtItemGWeight.value)){
	alert("Please enter a valid number for Item Gross Weight.");
	document.frmAdd.txtItemGWeight.focus();
	}
else if (isNaN(document.frmAdd.txtItemGWeight.value)){
	alert("Please enter a valid number for Item Gross Weight.");
	document.frmAdd.txtItemGWeight.focus();
	}											
else if (!/^\d+(\.\d{1,2})?$/.test(document.frmAdd.txtItemGWeight.value) || Number(document.frmAdd.txtItemGWeight.value) <= 0) {
	alert("Please enter a valid number for the Item Gross Weight.");
	document.frmAdd.txtItemGWeight.focus();
}
else if (document.frmAdd.txtItemGWeight.value == 0) {
	alert("Item Gross Weight cannot be zero.");
	document.frmAdd.txtItemGWeight.focus();
}
else if (document.frmAdd.txtItemNWeight.value == "") {
	alert("Please enter a value for Net Weight.");
	document.frmAdd.txtItemNWeight.focus();
	}
else if (isNaN(document.frmAdd.txtItemNWeight.value)){
	alert("Please enter a valid number for Item Net Weight.");
	document.frmAdd.txtItemNWeight.focus();
	}																	
else if (!/^\d+(\.\d{1,2})?$/.test(document.frmAdd.txtItemNWeight.value) || Number(document.frmAdd.txtItemNWeight.value) <= 0) {
	alert("Please enter a valid number for the Item Net Weight.");
	document.frmAdd.txtItemNWeight.focus();
}
else if (document.frmAdd.txtItemNWeight.value == 0) {
	alert("Item Net Weight cannot be zero.");
	document.frmAdd.txtItemNWeight.focus();
}
else if (parseFloat(document.frmAdd.txtItemGWeight.value) < parseFloat(document.frmAdd.txtItemNWeight.value)){
	alert("Item Net Weight must not be greater than Item Gross Weight.");
	document.frmAdd.txtItemNWeight.focus();
	}
else if (document.frmAdd.txtInvValue.value == ""){
	alert("Please enter FOB value.");
	document.frmAdd.txtInvValue.focus();
	}
else if (isNaN(document.frmAdd.txtInvValue.value)) {
	alert("Please enter a valid value for FOB.");
	document.frmAdd.txtInvValue.focus();
	}																	
else if (!/^\d+(\.\d{1,2})?$/.test(document.frmAdd.txtInvValue.value) || Number(document.frmAdd.txtInvValue.value) <= 0) {
	alert("Please enter a valid number for the Item Invoice Value.");
	document.frmAdd.txtInvValue.focus();
}
else if (document.frmAdd.txtInvValue.value == 0) {
	alert("Item Invoice Value cannot be zero.");
	document.frmAdd.txtInvValue.focus();
}
else if ((document.frmAdd.HSUOM.value != "" && document.frmAdd.txtSupVal1.value == "")){
        alert("Please enter Supplementary value for this item");
        document.frmAdd.txtSupVal1.focus();
	}
else {
	document.frmAdd.submit();
	}
}

function bago(){
	//alert(document.frmAdd.lstOffClear.value);
	//document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	if (document.frmAdd.lstOffClear.value == "P03") {
		document.frmAdd.lstTransPort.value = "PHMN3"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P04") {
		document.frmAdd.lstTransPort.value = "PHBTG"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P04C") {
		document.frmAdd.lstTransPort.value = "PHBAU"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P02A") {
		document.frmAdd.lstTransPort.value = "PHMN1"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P02B") {
		document.frmAdd.lstTransPort.value = "PHMN2"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P13") {
		document.frmAdd.lstTransPort.value = "PHSFS"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P14") {
		document.frmAdd.lstTransPort.value = "PHZ12"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P07") {
		document.frmAdd.lstTransPort.value = "PHCEB"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	else if (document.frmAdd.lstOffClear.value == "P07B") {
		document.frmAdd.lstTransPort.value = "PHNOP"
		document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
	}
	document.frmAdd.lstDestPort.value = document.frmAdd.lstOffClear.value
}

function Validate_form(){
//alert(document.frmAdd.txtManifest.value);
//alert(document.frmAdd.lstoffClear.value+"");

if (document.frmAdd.lstOffClear.value == "NONE"){
	alert("Please select an Office of Clearance.");
    document.frmAdd.lstOffClear.focus();
	}
<%If IsManifestOptional() Then %>
else if (document.frmAdd.txtManifest.value == ""){	
	alert("Please enter manifest number.");
	document.frmAdd.txtManifest.focus();
	}
<%End If%>
/* else if (document.frmAdd.txtManifest.value != "" && document.frmAdd.txtManifest.value.length != 10){
	alert("Please enter a valid length for the manifest number.");
	document.frmAdd.txtManifest.focus();
	}
else if (document.frmAdd.txtManifest.value != "" && document.frmAdd.txtManifest.value.substring(7,8) != "-"){
	alert("Please include the dash to your manifest number.");
	document.frmAdd.txtManifest.focus();
	}
else if (document.frmAdd.txtManifest.value != "" && document.frmAdd.txtManifest.value.substring(8).match(/[^0-9]/img)){
	alert("Only numbers are allowed for your manifest number");
	document.frmAdd.txtManifest.focus();
	}
else if (document.frmAdd.txtManifest.value != "" && document.frmAdd.txtManifest.value.substring(0,3).match(/[^A-Z]/img)){
	alert("Only capital letters are allowed for the first 3 characters of your manifest number");
	document.frmAdd.txtManifest.focus();
	}
else if (document.frmAdd.txtManifest.value != "" && document.frmAdd.txtManifest.value.substring(3,7).match(/[^0-9]/img)){
	alert("Only numbers are allowed for for the first 4th to 7th characters of your manifest number");
	document.frmAdd.txtManifest.focus();
	}*/

else if (document.frmAdd.lstPurpose.value == "OTHERS" && document.frmAdd.txtOth.value == ""){
    alert("Please enter reason.");
	document.frmAdd.txtOth.focus();
	}
else if (document.frmAdd.lstExporter.value == ""){
	alert("Please enter Importer's name or click on the lookup button.");
	document.frmAdd.lstExporter.focus();
	}	
else if (document.frmAdd.txtSuppAddr1.value == ""){
	alert("Please enter Importer's address or click on the lookup button.");
	document.frmAdd.txtSuppAddr1.focus();
	}
<%'for renie escobar mandating of airbill 12/10/2019
if UCase(Session("UserID")) = "DHLHRD" OR Session("bTIN") = "200475719000" OR Session("bTIN") = "200475719001"  OR Session("bTIN") = "738464204" OR Session("bTIN") = "738464204000" OR Session("cTIN") = "310614642000" OR Session("cTIN") = "200615811" OR Session("bTIN") = "430428413000" OR Session("bTIN") = "924889826000" OR Session("bTIN") = "924889826" then%>
else if (document.frmAdd.txtBOL.value == ""){
	alert("Please enter the bill of lading/airway bill.");
	document.frmAdd.txtBOL.focus();
	}
<%end if%>
<%'fedex	
if FedexClient = "YES" then%>	
	else if (document.frmAdd.txtBOL.value == ""){	
		alert("Please enter the bill of lading/airway bill.");	
		document.frmAdd.txtBOL.focus();	
	}	
    else if (document.frmAdd.txtBOL.value.length != 9 && document.frmAdd.txtBOL.value.length != 12) {	
			alert("Please enter a valid bill of lading/airway bill.");	
			document.frmAdd.txtBOL.focus();	
		}	
<%end if%>
<% if ((session("btin") = "10825015300") AND (Session("cTIN") = "005865295")) then %>
else if (document.frmAdd.txtBOL.value == ""){
		alert("Please enter the bill of lading/airway bil3.");
		document.frmAdd.txtBOL.focus();
	}
<%end if %>
else if (document.frmAdd.txtVessel.value == ""){
	alert("Please enter vessel or aircraft ID.");
	document.frmAdd.txtVessel.focus();
	}
else if (document.frmAdd.lstTransPort.value == "None"){
	alert("Please select a port of entry.");
	document.frmAdd.lstTransPort.focus();
	}
else if (document.frmAdd.lstCountry1.value == "None"){
	alert("Please select a country of destintaion.");
	document.frmAdd.lstCountry1.focus();
	}
else if (document.frmAdd.lstDestPort.value == "None"){
	alert("Please select a port of departure.");
	document.frmAdd.lstDestPort.focus();
	}
else if (document.frmAdd.lstLGoods.value == "None"){
    alert("Please select a Location of goods.");
    document.frmAdd.lstLGoods.focus();
	}
//else if (document.frmAdd.txtBankRef.value == ""){
//    alert("Please enter the bank reference number.");
//    document.frmAdd.txtBankRef.focus();
//	}
//else if (document.frmAdd.txtBankRef.value == ""){
//    alert("Please enter the bank reference number.");
//   document.frmAdd.txtBankRef.focus();
//	}
//added BRN Validations
//else if (document.frmAdd.txtBankRef.value == "" && document.frmAdd.txtPrepaid.value == ""){
//	alert("Please enter bank reference or prepaid account number only.");
//	document.frmAdd.txtPrepaid.focus();
//	}
else if (document.frmAdd.lstTDelivery.value == "None"){
	alert("Please select the Terms of Delivery.");
	document.frmAdd.lstTDelivery.focus();
	}
<%if (session("btin") <> "10825015300" AND Session("cTIN") <> "005865295") then %>
else if (document.frmAdd.txtBankRef.value != "" && document.frmAdd.txtPrepaid.value != ""){
	alert("Please enter bank reference or prepaid account number only.");
	document.frmAdd.txtPrepaid.focus();
	}
else if (document.frmAdd.txtPrepaid.value == "" && document.frmAdd.txtBankRef.value != "" && document.frmAdd.txtBankRef.value.length != 17){
	alert("Please enter a valid length for bank reference number.");
	document.frmAdd.txtBankRef.focus();
	}
else if (document.frmAdd.txtPrepaid.value == "" && document.frmAdd.txtBankRef.value != "" && document.frmAdd.txtBankRef.value.substring(9,10) != "-"){
	alert("Please include the dash to your bank reference number.");
	document.frmAdd.txtBankRef.focus();
	}
else if (document.frmAdd.txtPrepaid.value == "" && document.frmAdd.txtBankRef.value != "" && document.frmAdd.txtBankRef.value.substring(10).match(/[^0-9]/img)){
	alert("Only numbers are allowed for your bank reference number");
	document.frmAdd.txtBankRef.focus();
	}
else if (document.frmAdd.txtPrepaid.value == "" && document.frmAdd.txtBankRef.value != "" && document.frmAdd.txtBankRef.value.substring(0,9).match(/[^0-9]/img)){
	alert("Only numbers are allowed for your bank reference number");
	document.frmAdd.txtBankRef.focus();
	}

//added PPA Validations
else if (document.frmAdd.txtPrepaid.value.match(/[^A-z,0-9,-]/img)){
	alert("Only numbers and letters are allowed for your prepaid account number");
	document.frmAdd.txtPrepaid.focus();
	}
<%end if %>
//require BL for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
else if (document.frmAdd.txtDecTIN.value == "200615811" || document.frmAdd.txtDecTIN.value == "200615811000" || document.frmAdd.txtDecTIN.value == "215722696" || document.frmAdd.txtDecTIN.value == "215722696000" || document.frmAdd.txtDecTIN.value == "225879904" || document.frmAdd.txtDecTIN.value == "225879904000" || document.frmAdd.txtDecTIN.value == "204867435" || document.frmAdd.txtDecTIN.value == "204867435000" || document.frmAdd.txtDecTIN.value == "432899304" || document.frmAdd.txtDecTIN.value == "432899304000" || document.frmAdd.txtDecTIN.value == "738464204" || document.frmAdd.txtDecTIN.value == "738464204000") {
	if (document.frmAdd.txtBOL.value == ""){
		alert("Please enter the bill of lading/airway bill.");
		document.frmAdd.txtBOL.focus();
	}
	else if (document.frmAdd.txtBOL.value == "1111111116" || document.frmAdd.txtBOL.value == "7777777770" || document.frmAdd.txtBOL.value == "0000000" || document.frmAdd.txtBOL.value == "1111111" || document.frmAdd.txtBOL.value == "2222222" || document.frmAdd.txtBOL.value == "3333333" || document.frmAdd.txtBOL.value == "4444444" || document.frmAdd.txtBOL.value == "5555555" || document.frmAdd.txtBOL.value == "6666666" || document.frmAdd.txtBOL.value == "7777777" || document.frmAdd.txtBOL.value == "8888888" || document.frmAdd.txtBOL.value == "9999999" || document.frmAdd.txtBOL.value == "00000000" || document.frmAdd.txtBOL.value == "11111111" || document.frmAdd.txtBOL.value == "22222222" || document.frmAdd.txtBOL.value == "33333333" || document.frmAdd.txtBOL.value == "44444444" || document.frmAdd.txtBOL.value == "55555555" || document.frmAdd.txtBOL.value == "66666666" || document.frmAdd.txtBOL.value == "77777777" || document.frmAdd.txtBOL.value == "88888888" || document.frmAdd.txtBOL.value == "99999999" || document.frmAdd.txtBOL.value == "000000000" || document.frmAdd.txtBOL.value == "111111111" || document.frmAdd.txtBOL.value == "222222222" || document.frmAdd.txtBOL.value == "333333333" || document.frmAdd.txtBOL.value == "444444444" || document.frmAdd.txtBOL.value == "555555555" || document.frmAdd.txtBOL.value == "666666666" || document.frmAdd.txtBOL.value == "777777777" || document.frmAdd.txtBOL.value == "888888888" || document.frmAdd.txtBOL.value == "999999999" || document.frmAdd.txtBOL.value == "0000000000" || document.frmAdd.txtBOL.value == "1111111111" || document.frmAdd.txtBOL.value == "2222222222" || document.frmAdd.txtBOL.value == "3333333333" || document.frmAdd.txtBOL.value == "4444444444" || document.frmAdd.txtBOL.value == "5555555555" || document.frmAdd.txtBOL.value == "6666666666" || document.frmAdd.txtBOL.value == "7777777777" || document.frmAdd.txtBOL.value == "8888888888" || document.frmAdd.txtBOL.value == "9999999999"){
		alert("Please enter a valid bill of lading/airway bill.");
		document.frmAdd.txtBOL.focus();
	}
	else if (document.frmAdd.txtBOL.value != ""){
		if (document.frmAdd.txtBOL.value.length < 10) {
			alert("Please enter a valid bill of lading/airway bill.");
			document.frmAdd.txtBOL.focus();
		}
		else if (document.frmAdd.txtBOL.value.length > 10) {
			alert("Please enter a valid bill of lading/airway bill.");
			document.frmAdd.txtBOL.focus();
		}
		else if (document.frmAdd.txtBOL.value.length = 10) {
			var awb = document.frmAdd.txtBOL.value.substring(0,1) + document.frmAdd.txtBOL.value.substring(1,2) + document.frmAdd.txtBOL.value.substring(2,3) + document.frmAdd.txtBOL.value.substring(3,4) + document.frmAdd.txtBOL.value.substring(4,5) + document.frmAdd.txtBOL.value.substring(5,6) + document.frmAdd.txtBOL.value.substring(6,7) + document.frmAdd.txtBOL.value.substring(7,8) + document.frmAdd.txtBOL.value.substring(8,9);
			var bol = parseInt(awb);
			if ((bol % 7) != document.frmAdd.txtBOL.value.substring(9,10)) {
				alert("Please enter a valid bill of lading/airway bill.");
				document.frmAdd.txtBOL.focus();
			}else {
				document.frmAdd.submit();
				}
		}
	}
	else {
		return true;
	}
}
	
else {
	document.frmAdd.submit();
	}
}

function show_calendar(str_target, str_datetime) {
	var arr_months = ["January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November", "December"];
	var week_days = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
	var n_weekstart = 1; // day week starts from (normally 0 or 1)

	var dt_datetime = (str_datetime == null || str_datetime =="" ?  new Date() : str2dt(str_datetime));
	var dt_prev_month = new Date(dt_datetime);
	dt_prev_month.setMonth(dt_datetime.getMonth()-1);
	var dt_next_month = new Date(dt_datetime);
	dt_next_month.setMonth(dt_datetime.getMonth()+1);
	var dt_firstday = new Date(dt_datetime);
	dt_firstday.setDate(1);
	dt_firstday.setDate(1-(7+dt_firstday.getDay()-n_weekstart)%7);
	var dt_lastday = new Date(dt_next_month);
	dt_lastday.setDate(0);
	
	// html generation (feel free to tune it for your particular application)
	// print calendar header
	var str_buffer = new String (
		"<html>\n"+
		"<head>\n"+
		"	<title>Calendar</title>\n"+
		"</head>\n"+
		"<body bgcolor=\"White\">\n"+
		"<table class=\"clsOTable\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n"+
		"<tr><td bgcolor=\"#4682B4\">\n"+
		"<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"100%\">\n"+
		"<tr>\n	<td bgcolor=\"#4682B4\"><a href=\"javascript:window.opener.show_calendar('"+
		str_target+"', '"+ dt2dtstr(dt_prev_month)+"'+document.cal.time.value);\">"+
		"<img src=\"prev.gif\" width=\"16\" height=\"16\" border=\"0\""+
		" alt=\"previous month\"></a></td>\n"+
		"	<td bgcolor=\"#4682B4\" colspan=\"5\">"+
		"<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">"
		+arr_months[dt_datetime.getMonth()]+" "+dt_datetime.getFullYear()+"</font></td>\n"+
		"	<td bgcolor=\"#4682B4\" align=\"right\"><a href=\"javascript:window.opener.show_calendar('"
		+str_target+"', '"+dt2dtstr(dt_next_month)+"'+document.cal.time.value);\">"+
		"<img src=\"next.gif\" width=\"16\" height=\"16\" border=\"0\""+
		" alt=\"next month\"></a></td>\n</tr>\n"
	);

	var dt_current_day = new Date(dt_firstday);
	// print weekdays titles
	str_buffer += "<tr>\n";
	for (var n=0; n<7; n++)
		str_buffer += "	<td bgcolor=\"#87CEFA\">"+
		"<font color=\"white\" face=\"tahoma, verdana\" size=\"2\">"+
		week_days[(n_weekstart+n)%7]+"</font></td>\n";
	// print calendar table
	str_buffer += "</tr>\n";
	while (dt_current_day.getMonth() == dt_datetime.getMonth() ||
		dt_current_day.getMonth() == dt_firstday.getMonth()) {
		// print row heder
		str_buffer += "<tr>\n";
		for (var n_current_wday=0; n_current_wday<7; n_current_wday++) {
				if (dt_current_day.getDate() == dt_datetime.getDate() &&
					dt_current_day.getMonth() == dt_datetime.getMonth())
					// print current date
					str_buffer += "	<td bgcolor=\"#FFB6C1\" align=\"right\">";
				else if (dt_current_day.getDay() == 0 || dt_current_day.getDay() == 6)
					// weekend days
					str_buffer += "	<td bgcolor=\"#DBEAF5\" align=\"right\">";
				else
					// print working days of current month
					str_buffer += "	<td bgcolor=\"white\" align=\"right\">";

				if (dt_current_day.getMonth() == dt_datetime.getMonth())
					// print days of current month
					str_buffer += "<a href=\"javascript:window.opener."+str_target+
					".value='"+dt2dtstr(dt_current_day)+"'+document.cal.time.value; window.close();\">"+
					"<font color=\"black\" face=\"tahoma, verdana\" size=\"2\">";
				else 
					// print days of other months
					str_buffer += "<a href=\"javascript:window.opener."+str_target+
					".value='"+dt2dtstr(dt_current_day)+"'+document.cal.time.value; window.close();\">"+
					"<font color=\"gray\" face=\"tahoma, verdana\" size=\"2\">";
				str_buffer += dt_current_day.getDate()+"</font></a></td>\n";
				dt_current_day.setDate(dt_current_day.getDate()+1);
		}
		// print row footer
		str_buffer += "</tr>\n";
	}
	// print calendar footer
    str_buffer +=
        "<form name=\"cal\">\n<tr><td colspan=\"7\" bgcolor=\"#87CEFA\">" +
        "<input type=\"hidden\" name=\"time\" </font></td></tr>\n</form>\n" +
        "</table>\n" +
        "</tr>\n</td>\n</table>\n" +
        "</body>\n" +
        "</html>\n";

    var vWinCal = window.open("", "Calendar",
        "width=200,height=200,status=no,resizable=no,top=200,left=200");
	vWinCal.opener = self;
	var calc_doc = vWinCal.document;
	calc_doc.write (str_buffer);
	calc_doc.close();
}
// datetime parsing and formatting routimes. modify them if you wish other datetime format
function str2dt (str_datetime) {
    var re_date = /^(\d+)\/(\d+)\/(\d+)\s*$/;
	if (!re_date.exec(str_datetime))
		return alert("Invalid Datetime format: "+ str_datetime);
	return (new Date (RegExp.$3, RegExp.$1-1, RegExp.$2));
}
function dt2dtstr (dt_datetime) {
	return (new String (
            dt_datetime.getMonth() + 1) + "/" + (dt_datetime.getDate() + "/" + dt_datetime.getFullYear()));
}
function dt2tmstr (dt_datetime) {
	return (new String (
			dt_datetime.getHours()+":"+dt_datetime.getMinutes()+":"+dt_datetime.getSeconds()));
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}
//-->

const handleSendButton = () => {
	alert('<% If rejectedItem = "" Then %>' +
    'The Following Item/s are Rejected by PEZA, please reselect item/s: <%=rejectedItems%>' +
    '<% End If %>' +
    '<% If HSCodestat_ItemNo <> "" Then %>' +
    'The Following Item/s have Outdated HS Codes: <%=Trim(HSCodestat_ItemNo)%>' +
    '<% End If %>');
}
<!--11/14/2024 validation for duplicate bl before sending-->
function confirmSend() {
	if (document.frmAdd.txtBOL1.value == document.frmAdd.txtBOL.value) {
		document.frmAdd.txtBOL.focus();
		return confirm("BL Already Exist! Would you like to proceed?");				
		} else {
			return true;
		}    
    }
</SCRIPT>
</head>
<body bgcolor="#666666" text="#000000" onLoad="MM_preloadImages('../Images/sub-calc_2.jpg','../Images/sub-send_2.jpg','../Images/sub-resp_2.jpg','../Images/sub-print_2.jpg','../sgl/images/subsgl2.jpg')">
<form name="frmAdd" method="POST" action="<%=MM_editAction%>">
  <table width="630" border="0" cellspacing="0" cellpadding="0" align="center" height="600">
    <tr> 
      <td colspan="6" height="1" bgcolor="#333333"><img src="../Customs/Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td bgcolor="#333333" width="1" rowspan="14"><img src="../Customs/Images/spacer.gif" width="1" height="1"> 
      </td>
      <td bgcolor="#FFFFFF" height="61" colspan="4"><img src="../Images/INS-logo.jpg" width="299" height="80"></td>
      <td bgcolor="#333333" width="1" rowspan="14"><img src="../Customs/Images/spacer.gif" width="1" height="1"></td>
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
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2" background="../Images/dot_H.gif" height="1"><img src="../Images/spacer.gif" width="1" height="1"></td>
      <td bgcolor="#FFFFFF" valign="top" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <tr> 
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2">&nbsp;</td>
      <td bgcolor="f7f7f7" valign="top" height="12" colspan="2"> 
        <div align="right"><a href="../logout.asp"><img src="../Images/btn-logout.gif" height="23" border="0"></a></div>
      </td>
    </tr>
    <tr> 
      <td bgcolor="#CCCCCC" bordercolor="#FFFFFF" align="center" valign="top" colspan="2" background="../Images/dot_H.gif" height="1"><img src="../Images/spacer.gif" width="1" height="1"></td>
      <td bgcolor="#FFFFFF" valign="top" height="1" colspan="2" background="../Images/dot_H.gif"><img src="../Images/spacer.gif" width="1" height="1"></td>
    </tr>
    <% If Session("FromApp") <> "" Then %>
    <% set rstAPPLSTAT = Server.CreateObject("ADODB.Recordset")
	rstAPPLSTAT.ActiveConnection = constrPEZAexp  
	rstAPPLSTAT.Source = "SELECT * FROM tblExpVersion WHERE applno='" & ApplNumber & "' ORDER BY VerDate"
	rstAPPLSTAT.CursorType = 0
	rstAPPLSTAT.CursorLocation = 2
	rstAPPLSTAT.LockType = 3
	rstAPPLSTAT.Open
	rstAPPLSTAT_numRows = 0
	
	set rstAPPLSTAT1 = Server.CreateObject("ADODB.Recordset")
	rstAPPLSTAT1.ActiveConnection = constrPEZAexp  
	rstAPPLSTAT1.Source = "SELECT TOP 1 Remarks FROM tblExpVersion WHERE applno='" & ApplNumber & "' AND Status='H'"
	rstAPPLSTAT1.CursorType = 0
	rstAPPLSTAT1.CursorLocation = 2
	rstAPPLSTAT1.LockType = 3
	rstAPPLSTAT1.Open
	rstAPPLSTAT1_numRows = 0
	
	if NOT rstAPPLSTAT1.EOF then
		HOLDStat = "1"
	else
		HOLDStat = "0"
	end if
	rstAPPLSTAT1.Close%>  
    <tr> 
    <% if not rstAPPLSTAT.EOF then %>
      <td bgcolor="#FFFFFF" bordercolor="#FFFFFF" align="center" colspan="2">&nbsp;</td>
      <td bgcolor="#FFFFFF" bordercolor="#FFFFFF" align="center" valign="top" colspan="2"> 
        <div align="left">
		 <%	end if
			if not rstAPPLSTAT.EOF then%> 
          <table border="1" cellspacing="0" >
            <tr bgcolor="#99CCCC" bordercolor="#FFFFFF" > 
              <td width="85" height="20" ><Font size = "1">Status</Font></td>
              <td width="207" ><font size = "1" > Remarks</font></td>
              <td width="127"  ><font size = "1" >Processed Date</font></td>              
            </tr>
          </table>
		  <% End if
While NOT rstAPPLSTAT.EOF

	if Repeat1__index/2 - INT(Repeat1__index/2) > 0 then
		strBGColor = "#C8C8C8"  '"#E6E6E6"
	else
		strBGColor = "#FFFFFF" '"#FFFFCC"
	end if%>
		  <table border="1" cellspacing="0" >
            <tr bgcolor="<%=strBGColor%>" > 
              <td width="85" height="20" ><Font size = "1"> 
			  <%if rstAPPLSTAT("Status") = "A" then
					if rstAPPLSTAT("Remarks") = "Auto-Approved" then
			  			response.write "Approved"
					else
						' response.write "Approved-Inspected"
						response.write "Approved"
					end if
				end if					
				if rstAPPLSTAT("Status") = "TI" then
					response.write "Transferred-Inspected"
				end if
				if rstAPPLSTAT("Status") = "AT" then
					response.write "Transferred"
				end if
				if rstAPPLSTAT("Status") = "RI" then
					response.write "Released-Inspected"
				end if
				if rstAPPLSTAT("Status") = "IN" then
					response.write "Released"
				end if	
				if rstAPPLSTAT("Status") = "D" then
			  		response.write "Documents Confirmed"
				end if
				if rstAPPLSTAT("Status") = "DS" then
			  		response.write "Documents Submitted"
				end if				
				if rstAPPLSTAT("Status") = "DX" then
			  		response.write "Wrong Documents Submitted"
				end if
				if rstAPPLSTAT("Status") = "H" then
			  		response.write "For Inspection"
				end if
				if rstAPPLSTAT("Status") = "X" then
			  		response.write "Cancelled"
				end if
				if rstAPPLSTAT("Status") = "R" then
			  		response.write "Rejected"
				end if
				if rstAPPLSTAT("Status") = "N" then
			  		response.write "For Approval"
				end if
				if rstAPPLSTAT("Status") = "FX" then
			  		response.write "For Cancellation"
				end if
				if rstAPPLSTAT("Status") = "E" then
			  		response.write "Expired"
				end if
				if rstAPPLSTAT("Status") = "L" then
			  		response.write "Lapsed"
				end if
				if rstAPPLSTAT("Status") = "AC" then
			  		response.write "Added Container"
				end if
				if rstAPPLSTAT("Status") = "EC" then
			  		response.write "Edited Container"
				end if
				if rstAPPLSTAT("Status") = "FI" then
			  		response.write "For Inspection"
				end if
				if rstAPPLSTAT("Status") = "IP" then
			  		response.write "Inspected"
				end if%> </Font></td>
              <td width="207" ><font size = "1" ><%=rstAPPLSTAT("Remarks") %></font></td>
              <td width="127"  ><font size = "1" ><%=rstAPPLSTAT("VerDate") %></font></td>              
            </tr>
          </table>
<%Repeat1__index=Repeat1__index+1
  Repeat1__numRows=Repeat1__numRows-1
  rstAPPLSTAT.MoveNext()
  
Wend
rstAPPLSTAT.close%>		   
		  </div>
      </td>
    </tr>
    <% End If %>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" colspan="2">&nbsp;</td>
      <td bgcolor="#FFFFFF" colspan="2"><font face="Verdana, Arial, Helvetica, sans-serif" color="red" size="1"> <i>The following characters are not allowed: ' " ? & # : ;</i> </font></td>
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
                    <td bgcolor="#666699" valign="top" height="30" width="24">&nbsp;</td>
                    <td bgcolor="#666699" valign="middle" height="30" colspan="4" class="heading"><font color="#FFFFFF"><strong>PEZA - EXPORT DOCUMENTATION (PTOPS)</strong></font></td>
                  <td width="8%"> 
                    <div align="right"><a href=<%if session("Importer") then response.write "cws_step2_i.asp" else response.write "ptops_ed_step1PEZAEXPexpress.asp"%>><img src="../Images/win-close.jpg" width="30" height="32" alt="Close the current application" border="0"></a></div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr> 
            <td bgcolor="#CCCCCC"> 
              <div align="center"><br>
                <table width="520" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="100" class="body"> 
                      <p><b> Application No.:</b></p>
                    </td>
                    <td width="166" class="body"> 
                      <input type="text" name="txtAppNo" size="<%=len(ApplNumber) + 1%>" disabled="True" value="<%=ApplNumber%>" onFocus="blur()">
                    </td>
                    <td width="14" class="body">&nbsp;</td>
                    <td width="150" class="body">Items / Packages:</td>
                    <td width="80" class="body"> 
                      <input type="text" name="txtItems" size="<%=len(intItem)%>" value="<%=intItem%>" onFocus="blur()" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>><br>
                      <input type="text" name="txtPackages" size="<%=len(intPack)%>" value="<%=intPack%>" onFocus="blur()" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                      <%if intPackCode <> "" then%> 
                      <input type="text" name="txtPackageCode" size="<%=len(intPackCode)%>" value="<%=intPackCode%>" onFocus="blur()" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                      <%end if%>
                    </td>
                  </tr>
                  <tr> 
                    <td width="100" class="body">Exporter TIN / Name:</td>
                    <td width="166" class="body"> 
                      <input type="text" name="txtTIN" size="<%=len(Session("cTIN"))%>" value="<%=Session("cTIN")%>" onFocus="blur()" disabled="true"><br>
                      <input type="text" name="txtItemCon" size="<%=len(Session("lstExporter"))%>" value="<%=Session("lstExporter")%>" disabled="true">
                    </td>
					<td width="14" class="body">&nbsp;</td>
                    <td width="150" class="body">Total Containers:</td>
                    <td class="body" width="80">
					<%set rsExporter = Server.CreateObject("ADODB.Recordset")
					rsExporter.ActiveConnection = constrCOMINSpezaEXP
					rsExporter.Source = "SELECT Count(container) as CCont FROM tblexpapl_contPEZA where applno='" & ApplNumber & "'"
					rsExporter.CursorType = 0
					rsExporter.CursorLocation = 2
					rsExporter.LockType = 3
					rsExporter.Open
					rsExporter_numRows = 0
					
					set cmdUpdt = Server.CreateObject("ADODB.Command")	
					cmdUpdt.ActiveConnection = constrCOMINSpezaEXP	
					cmdUpdt.CommandText = "UPDATE tblEXPApl_Master Set TotContainers='" & rsExporter("CCont") & "' WHERE ApplNo='" & ApplNumber & "'"	
					cmdUpdt.CommandType = 1
					cmdUpdt.CommandTimeout = 0
					cmdUpdt.Prepared = true		
					cmdUpdt.Execute()%>
                    
                      <input type="text" name="txtTotCont" size="<%=len(rsExporter("CCont"))%>" value="<%=rsExporter("CCont")%>" onFocus="blur()">
                      <%if strStatus <> "I" then%>
					  <input type="text" name="txtTotContType" size="3" maxlength="5" value="<%if rsExporter("CCont") = 0 then
							response.write "LCL"
						else
							response.write "FCL"
						end if%>" onFocus="blur()">
                      <%end if%>
                    </td>
                  </tr>
                  <tr>
				  
                    <td width="100" class="body">Forwarder TIN / Name:</td>
                    <td class="body" width="80">
                      <input type="text" name="txtDecTIN" size="<%=len(Session("utin"))%>" value="<%=Session("utin")%>" Disabled="True"><br>
                      <input type="text" name="txtItemCon" size="<%=len(Session("brknam"))%>" value="<%=Session("brknam")%>" disabled="true">
                    </td>
					<td width="14" class="body">&nbsp;</td> 
                    <td width="114" class="body">Status:</td>
                    <td width="136" class="body"> 
                      <% If strStatus="AG" then %>
                     		<input type="text" name="txtStatus" size="<%=len(strStatus)%>" disabled="True" onFocus="blur()" value="<%=strStatus%>" style=background-color:#00FF00>
			<% end if %>
                    
			<% If strStatus="AS" then 
				set rstColor = Server.CreateObject("ADODB.Recordset")
				rstColor.ActiveConnection = constrCOMINSpezaEXP  
				rstColor.Source = "SELECT color FROM tblEXPApl_Master  where APPLNO='" & ApplNumber & "'"
				rstColor.CursorType = 0
				rstColor.CursorLocation = 2
				rstColor.LockType = 3
				rstColor.Open
				rstColor_numRows = 0

				

				if rstColor("Color") = "1" then %>
                     		<input type="text" name="txtStatus" size="<%=len(strStatus)%>" disabled="True" onFocus="blur()" value="<%=strStatus%>" style="background-color:#0066FF; color:white;">
				<% End if %>			

				<% if rstColor("Color") = "2" then %>
                     		<input type="text" name="txtStatus" size="<%=len(strStatus)%>" disabled="True" onFocus="blur()" value="<%=strStatus%>" style=background-color:#FFFF66>
				<% End if %>			


				<% if rstColor("Color") = "3" then %>
                     		<input type="text" name="txtStatus" size="<%=len(strStatus)%>" disabled="True" onFocus="blur()" value="<%=strStatus%>" style="background-color:#FF0033; color:white;" %>
				<% End if

				rstColor.close
				 %>
			

			<% end if %>

			<% If not strStatus="AS" and Not strStatus="AG" then %>
                     		<input type="text" name="txtStatus" size="<%=len(strStatus)%>" disabled="True" onFocus="blur()" value="<%=strStatus%>" >
			<% end if %>

                    </td>
                  </tr>
				  <% 	set rsRemarks = Server.CreateObject("ADODB.Recordset")
						rsRemarks.ActiveConnection = constrCOMINSpezaEXP  
						rsRemarks.Source = "SELECT Remarks FROM TBLEXPAPL_MASTER where applno='" & ApplNumber & "'"
						rsRemarks.CursorType = 0
						rsRemarks.CursorLocation = 2
						rsRemarks.LockType = 3
						rsRemarks.Open
						rsRemarks_numRows = 0
						
						if NOT rsRemarks.EOF Then
							tim = rsRemarks("remarks")
						else
							tim = ""
						end if%>
				  <tr> 
					<td width="100" class="body"><%If not strStatus="I" and Not strStatus="C" and Not strStatus="" and Not strStatus="FP" then %>Remarks:<%else%>&nbsp;<%end if%></td>
                    <td class="body" width="80">
					<% If not strStatus="I" and Not strStatus="C" and Not strStatus="" and Not strStatus="FP" then %>			
				<textarea name="txtStatus" cols="<%=len(tim)%>" rows="2" disabled onFocus="blur()"><%=tim%></textarea><% end if %></td>
				    <td width="14" class="body">&nbsp;</td>
						<%set rsRemarks22 = Server.CreateObject("ADODB.Recordset")
						rsRemarks22.ActiveConnection = constrPEZAexp  
						rsRemarks22.Source = "SELECT ATLRef FROM TBLEXPAPL_MASTER where applno='" & ApplNumber & "'"
						rsRemarks22.CursorType = 0
						rsRemarks22.CursorLocation = 2
						rsRemarks22.LockType = 3
						rsRemarks22.Open
						rsRemarks22_numRows = 0
						
						if NOT rsRemarks22.EOF then
							ATLRef = rsRemarks22("ATLRef")							
						else
							ATLRef = ""
						end if
						
						set rsRemarks33 = Server.CreateObject("ADODB.Recordset")
						rsRemarks33.ActiveConnection = constrPEZAexp  
						rsRemarks33.Source = "SELECT SealNum, GatePassNum FROM tblExpDoc_RAI where applno='" & ApplNumber & "'"
						rsRemarks33.CursorType = 0
						rsRemarks33.CursorLocation = 2
						rsRemarks33.LockType = 3
						rsRemarks33.Open
						rsRemarks33_numRows = 0
						
						if NOT rsRemarks33.EOF then
							SealNum = rsRemarks33("SealNum")
							GatePass = rsRemarks33("GatePassNum")
						else
							SealNum = ""
							GatePass = ""
						end if%>
						<% If not strStatus="I" and Not strStatus="C" and Not strStatus="" and Not strStatus="FP" then 
								if ATLRef <> "" or GatePass <> "" or SealNum <> "" then%>					
                    <td width="136" class="body">ATL No.:<br>Gate Pass No.:<br>Seal No.:</td>
					<td width="14" class="body"><input type="text" name="txtATL" size="15" value="<%=ATLRef%>" disabled="true"><input type="text" name="txtGatePass" size="15" value="<%=GatePass%>" disabled="true"><input type="text" name="txtSeal" size="15" value="<%=SealNum%>" disabled="true"></td>
						<%		end if
							end if%>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
		  
		  <%If AllowedDHLTI <> "TRUE" Then%>
          <tr> 
            <td bgcolor="868686"> 
              <div align="center"> 
                <input type="hidden" name="txtButton" value="Default">
				<%if not session("Importer") then %>
	                <input type="button" name="btnSave" value="     Save     " onClick="Set_Button(this.form.btnSave.name)" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>  class="button">
				<% end if %>
              </div>
            </td>
          </tr>
		  <%End If%>
		  
          <tr> 
            <td bgcolor="e5e5e5"> 
              <div align="center"><br>
                <table width="620" border="0" cellspacing="0" align="center" class="body">
                  <tr bgcolor="e5e5e5"> 
                    <td bgcolor="#666699">&nbsp;</td>
                    <td bgcolor="#666699" align="right" class="body"><font color="#FFFFFF"><b>Importer / Buyer</b></font></td>
                    <td bgcolor="#666699" class="body"><font color="#FFFFFF"><b>&nbsp;/ Consignee Information</b></font></td>
					<td bgcolor="#666699">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">&nbsp;</td>
                    <td width="55%">&nbsp; </td>
                    <td width="4%">&nbsp;</td>
                  </tr>				  
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">
                    <a href="" onClick="window.open('Code_Maintenance/cd-PEZAexporter.asp','popuppage','width=870,height=655,top=50,left=50');" class="toplink" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>><img src="Images/importer_icon.png" width="25" height="25" border="0"></a>
                    </td>
                    <td width="36%" class="body"> 
                      <div align="left">Name:</div>
                    </td>
                    <td width="55%"> <font face="Verdana, Arial, Helvetica, sans-serif"> 
                      <input class="parent-input" type="text" name="lstExporter" id="lstExporter" size="44" maxlength="70" <%If AllowedDHLTI <> "TRUE" Then%> oninput="searchDatabase()" <%end if%> value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("ConName") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                      </font>
					  
					  <div id="results" class="autocomplete-results"></div>
					  <font face="Verdana, Arial, Helvetica, sans-serif">
					<%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") OR (UCase(Session("UserID")) = "KWETICLARK" AND Session("cltcode") ="KWECLARK") Then%>
					  <input type=button name="Submit2" value="..." onClick="window.open('Lookup-ExporterPEZA2.asp','popuppage','width=924,height=598,top=100,left=100');" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>	
					<%Else%>
					  <input type=button name="Submit2" value="..." onClick="window.open('Lookup-ExporterPEZA.asp','popuppage','width=420,height=255,top=100,left=100');" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
					<%End If%>
                      <!--<input type=button name="Submit7" value="maintenance" onClick="window.open('Code_Maintenance/cd-PEZAexporter.asp','popuppage','width=870,height=655,top=50,left=50');" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>-->
					   </font> </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body"> 
                      <div align="left">Address:</div>
                    </td>
                    <td width="55%"> 
                      <input class="parent-input" type="text" name="txtSuppAddr1" id="txtSuppAddr1" size="44" maxlength="35" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("ConAdr1") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" align="center">&nbsp;</td>
                    <td width="55%"> <font face="Verdana, Arial, Helvetica, sans-serif"> 
                      <input class="parent-input" type="text" name="txtSuppAddr2" id="txtSuppAddr2" size="44" maxlength="35" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("ConAdr2") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
                      </font></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                   <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%">&nbsp;</td>
                    <td width="55%"> <font face="Verdana, Arial, Helvetica, sans-serif"> 
                      <input class="parent-input" type="text" name="txtSuppAddr3" id="txtSuppAddr3" size="44" maxlength="35" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("ConAdr3") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
                  </font></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%">&nbsp;</td>
                    <td width="55%"> <font face="Verdana, Arial, Helvetica, sans-serif"> 
                      <input class="parent-input" type="text" name="txtSuppAddr4" id="txtSuppAddr4" size="44" maxlength="35" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("ConAdr4") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
                  </font></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'set default Port for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Office of Clearance:</td>
                    <td width="55%"> 
                          <select name="lstOffClear" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> onChange="bago(); onModeOfTransport(this.value);">
                                <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="P14"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("OffClear") = "P14"  Then Response.writef "Selected"
					                End If%>>P14 - Port of Clark</option>
									
								<%Elseif AllowedDHLTI = "TRUE" Then%>
								<option value="P03"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("OffClear") = "P03"  Then Response.write "Selected"
					                End If%>>P03 - Ninoy Aquino Intl Airport</option>	
                                <%else%>
                                    <option value = "NONE" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>None</option> 
                                    <%While (NOT rstGBCUOTAB3.EOF)%>
                                    <option value="<%=(rstGBCUOTAB3.Fields.Item("offClrcod").Value)%>"
							            <% 
							            If NOT(rstTBLIMPAPL.EOF) Then
								            If rstGBCUOTAB3("offClrCod") = rstTBLIMPAPL("OffClear") then Response.Write("Selected")
							            else 
                                            if (Session("bTIN") = "200615811" OR Session("bTIN") = "200615811000" OR Session("bTIN") = "215722696" OR Session("bTIN") = "215722696000" OR Session("bTIN") = "225879904" OR Session("bTIN") = "225879904000") AND rstGBCUOTAB3("offClrCod") = "P03" then
									            Response.Write("Selected")
                                           
								            elseif (Session("bTIN") = "204867435" OR Session("bTIN") = "204867435000" OR Session("bTIN") = "432899304" OR Session("bTIN") = "432899304000") AND rstGBCUOTAB3("offClrCod") = "P07B" then
									            Response.Write("Selected")
								            end if
											
											<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: Default to P03 But editable -->
											if UCase(Session("UserID")) = "KWETICLARK" then
												if rstGBCUOTAB3("offClrCod") = "P03" then Response.Write("Selected") end if
											end if 
							            End If
							            %>> <%=rstGBCUOTAB3("offClrCod") & " - " & rstGBCUOTAB3("offClrName")%></option>
                                    <%rstGBCUOTAB3.MoveNext()
						            Wend
						            If (rstGBCUOTAB3.CursorType > 0) Then
						              rstGBCUOTAB3.MoveFirst
						            Else
						              rstGBCUOTAB3.Requery
						            End If%>
                                <%end if %>
                              </select> 
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Purpose of Exportation:</td>
                    <td width="55%">
					<select name="lstPurpose" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                      <option value="SALE"
					  <%If NOT(rstTBLIMPAPL.EOF) Then
					  	If rstTBLIMPAPL("Purpose") = "SALE"  Then Response.write "Selected"
					  End If%>>SALE</option>
					  <option value="RETURN TO SOURCE"
					  <%If NOT(rstTBLIMPAPL.EOF) Then
					  	If rstTBLIMPAPL("Purpose") = "RETURN TO SOURCE"  Then Response.write "Selected"
					  End If%>>RETURN TO SOURCE</option>
					  <option value="FOR REPAIR"
					  <%If NOT(rstTBLIMPAPL.EOF) Then
					  	If rstTBLIMPAPL("Purpose") = "FOR REPAIR"  Then Response.write "Selected"
					  End If%>>FOR REPAIR</option>
					  <option value="OTHERS"
					  <%If NOT(rstTBLIMPAPL.EOF) Then
					  	If rstTBLIMPAPL("Purpose") = "OTHERS"  Then Response.write "Selected"
					  End If%>>OTHERS</option>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">&nbsp;Reason<font face="Verdana, Arial, Helvetica, sans-serif" color="red" size="1">(if others)</font>:</td>
                    <td width="55%"> 
                      <input type="text" name="txtOth" size="55" maxlength="50" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("Reason") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Manifest No.:</td>
                    <td width="55%"> 
                      <input type="text" name="txtManifest" size="15" maxlength="10" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("Manifest") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> <% If Not IsManifestOptional() Then Response.Write "class=""optional""" %> placeholder="e.g. AAA1234-22">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'require AWB/BOL for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  'for renie escobar mandating of airbill 12/10/2019
				  %>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Bill Of Lading/AirBill:</td>
                    <td width="55%"> 
                      <input type="text" name="txtBOL" size="30" maxlength="26" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("Waybill") %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> <%if Session("bTIN") <> "430428413000" OR Session("bTIN") <> "924889826000" OR Session("bTIN") <> "924889826" OR Session("bTIN") <> "200475719000" AND Session("bTIN") <> "200475719001" AND (UCase(Session("UserID")) <> "DHLHRD") AND ((Session("bTIN") <> "200615811" AND Session("bTIN") <> "200615811000" AND Session("bTIN") <> "215722696" AND Session("bTIN") <> "215722696000" AND Session("bTIN") <> "225879904" AND Session("bTIN") <> "225879904000" AND Session("bTIN") <> "204867435" AND Session("bTIN") <> "204867435000" AND Session("bTIN") <> "432899304" AND Session("bTIN") <> "432899304000" AND Session("bTIN") <> "738464204" AND Session("bTIN") <> "738464204000")) AND FedexClient = "NO" AND  (session("btin") <> "10825015300" AND Session("cTIN") <> "005865295" AND Session("cTIN") <> "310614642000" AND Session("cTIN") <> "200615811") then%>class="optional"<%end if%>>
						<input type="hidden" name="txtBOL1" size="30" maxlength="26" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write WaybillCheck %>" <%' If NOT(flgEnabled = "True") Then Response.write "Disabled=True"%> class="optional">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Mode of Transport:</td>
                    <td width="55%">
						<input type="text" name="lstModeOfTransport" id="lstModeOfTransport" value="<% If NOT(rstTBLIMPAPL.EOF) Then Response.write rstTBLIMPAPL("modeOfTransport") Else Response.Write "None"%>" onFocus="blur()">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'set value as "DHL" for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Vessel/Aircraft ID:</td>
                    <td width="55%">
					<%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then%>
						<input type="text" name="txtVessel" size="30" maxlength="27" value="AIR" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
					<%Else%>
                      <input type="text" name="txtVessel" size="30" maxlength="27" value="<% If NOT(rstTBLIMPAPL.EOF) Then
					  		Response.write rstTBLIMPAPL("Vessel") 
						else
							if (Session("bTIN") = "200615811" OR Session("bTIN") = "200615811000" OR Session("bTIN") = "215722696" OR Session("bTIN") = "215722696000" OR Session("bTIN") = "225879904" OR Session("bTIN") = "225879904000" OR Session("bTIN") = "204867435" OR Session("bTIN") = "204867435000" OR Session("bTIN") = "432899304" OR Session("bTIN") = "432899304000") then
								Response.write "DHL"
							end if
						end if %>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
					<%End If%>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'set value as "DHL Express" for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  %>
				  <%if session("btin") <> "10825015300" and Session("cTIN") <> "005865295" then %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Local Carrier:</td>
                    <td width="55%"> 
					<%If (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then%>
					  <input type="text" name="txtLocalC" size="30" maxlength="27" value="DHL Express" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
					<%Else%>
                      <input type="text" name="txtLocalC" size="30" maxlength="27" value="<% If NOT(rstTBLIMPAPL.EOF) Then
					  		Response.write rstTBLIMPAPL("LocalCarrier") 
						else
							if (Session("bTIN") = "200615811" OR Session("bTIN") = "200615811000" OR Session("bTIN") = "215722696" OR Session("bTIN") = "215722696000" OR Session("bTIN") = "225879904" OR Session("bTIN") = "225879904000" OR Session("bTIN") = "204867435" OR Session("bTIN") = "204867435000" OR Session("bTIN") = "432899304" OR Session("bTIN") = "432899304000") then
								Response.write "DHL Express"
							end if
						end if%>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
					<%End If%>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%end if %>
                   <%' if session("btin") <> "10825015300" and Session("cTIN") <> "005865295" then 
				   %>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Location of Goods:</td>
                    <td class="body" width="55%"> 
                      <select name="lstLGoods" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>> 
					   <!-- PEZA AEDS (Express) and Export Declaration for TI Clark: remove for FedexTiClark  -->
					   <%If UCase(Session("UserID")) <> "FEDEXTICLARK" then%>
						<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>--Please select--</option> 
					   <%End If%>
					   
                    <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="A17"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("LGoods") = "A17"  Then Response.write "Selected"
					                End If%>>A17 - FEDEX EXPRESS PHIL LCC - PHIL BRANCH</option>  
					<%Elseif (AllowedDHLTI = "TRUE" AND UCase(Session("cltcode")) ="DHLEXA") Then%>
						<option value="A06" selected> A06 - DHL EXPRESS PHILIPPINES CORP.</option>
                        <%else %>                                          
						<%While (NOT rstGBSHDTAB.EOF)%>
                        <option value="<%=(rstGBSHDTAB.Fields.Item("shd_cod").Value)%>"
							<% 
							If NOT(rstTBLIMPAPL.EOF) Then
								If rstGBSHDTAB("shd_cod") = rstTBLIMPAPL("LGoods") then Response.Write("Selected")
							else
								if FedexClient = "YES" OR (session("btin") = "10825015300" and Session("cTIN") = "005865295") then 
                                    If rstGBSHDTAB("shd_cod") = "A17" Then Response.write "Selected"
                                end if
							End If
							%>> <%=(rstGBSHDTAB.Fields.Item("shd_nam").Value)%></option>
                        <%
  rstGBSHDTAB.MoveNext()
Wend
If (rstGBSHDTAB.CursorType > 0) Then
  rstGBSHDTAB.MoveFirst
Else
  rstGBSHDTAB.Requery
End If
%>
                        <%end if %>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'end if 
				  %>
                  <%' if session("btin") <> "10825015300" and Session("cTIN") <> "005865295" then 
				  %>
				  <!-- TI clark Enhancement v2 -->
				  <tr bgcolor="e5e5e5" <%IF UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN response.write "hidden"%>> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Province of Origin:</td>
                    <td class="body" width="55%">
					<select name="ProvofOrig" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                    <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="035400000"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("ProvofOrig") = "035400000"  Then Response.write "Selected"
					                End If%>>PAMPANGA</option> 
					<%Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN%>
						<option value="035400000" selected> PAMPANGA</option>
                        <%else %>
<% While (NOT rstGBPRVORG.EOF)
%>
<option value="<%=(rstGBPRVORG.Fields.Item("prov_cod").Value)%>"
<% 
If NOT(rstTBLIMPAPL.EOF) Then
	If rstGBPRVORG("prov_cod") = rstTBLIMPAPL("ProvofOrig") then Response.Write("Selected")
else
	<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: Kweticlark defaulted to pampanga, editable -->
	if (session("btin") = "10825015300" and Session("cTIN") = "005865295") OR UCase(Session("UserID")) = "KWETICLARK" then 
		if rstGBPRVORG("prov_cod") = "035400000" then Response.Write("Selected")
	else
		if rstGBPRVORG("prov_cod") = Session("PO") then Response.Write("Selected")
	end if
End If
%>> <%=(rstGBPRVORG.Fields.Item("prov_dsc").Value)%></option>
<%
  rstGBPRVORG.MoveNext()
Wend
If rstGBPRVORG.CursorType > 0 Then
  rstGBPRVORG.MoveFirst
Else
  rstGBPRVORG.Requery
End If
%>
                    <%end if %>
</select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <% 'end if
				  %>
                  <input type="hidden" name="lstCountry" value="PH">
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Country of Destination:</td>
                    <td class="body" width="55%"> 
                      <select name="lstCountry1" id="lstCountry1" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
					  <%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") OR (UCase(Session("UserID")) = "KWETICLARK" AND Session("cltcode") ="KWECLARK") Then%>
						<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
					  <%End If%>
                        <%
While (NOT rstGBCTYTAB0.EOF)
%>
                        <option value="<%=(rstGBCTYTAB0.Fields.Item("cityCode").Value)%>"
							<% 
							If NOT(rstTBLIMPAPL.EOF) Then
								If rstGBCTYTAB0("cityCode") = rstTBLIMPAPL("Cdest") Then Response.Write("Selected")
							End If
							%>> <%=(rstGBCTYTAB0.Fields.Item("cityDisc").Value)%></option>
                        <%
  rstGBCTYTAB0.MoveNext()
Wend
If (rstGBCTYTAB0.CursorType > 0) Then
  rstGBCTYTAB0.MoveFirst
Else
  rstGBCTYTAB.Requery
End If
%>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>	
                  <%'set value of port for Dennis Orbon or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  %>
				  <!-- TI clark Enhancement v2 -->
                  <tr bgcolor="e5e5e5" <%IF UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN response.write "hidden"%>> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Port of Loading:</td>
                    <td class="body" width="55%">
					<%set rstPLoading = Server.CreateObject("ADODB.Recordset")
					rstPLoading.ActiveConnection = constrCOMINScd  
					rstPLoading.Source = "SELECT * FROM GBLOCTAB where loc_cod like 'PH%' ORDER BY loc_dsc"
					rstPLoading.CursorType = 0
					rstPLoading.CursorLocation = 2
					rstPLoading.LockType = 3
					rstPLoading.Open
					rstPLoading_numRows = 0%> 
                      <select name="lstTransPort" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>	
                        <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="PHZ12"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("PortOfLoad") = "PHZ12"  Then Response.write "Selected"
					                End If%>>Clark</option> 
						<%Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN%>
							<option value="PHMN3" selected> NINOY AQUINO INTL. AIRPORT</option>										
                        <%else %>											  					  
                    <%While (NOT rstPLoading.EOF)%>
                    <option value="<%=rstPLoading("loc_cod")%>"
					<% 
					If NOT(rstTBLIMPAPL.EOF) Then
						If rstPloading("loc_cod") = rstTBLIMPAPL("PortOfLoad") Then Response.Write("Selected")
					Else
						if (Session("bTIN") = "200615811" OR Session("bTIN") = "200615811000" OR Session("bTIN") = "215722696" OR Session("bTIN") = "215722696000" OR Session("bTIN") = "225879904" OR Session("bTIN") = "225879904000") AND rstPloading("loc_cod") = "PHMN3" then
							Response.Write("Selected")
						elseif (Session("bTIN") = "204867435" OR Session("bTIN") = "204867435000" OR Session("bTIN") = "432899304" OR Session("bTIN") = "432899304000") AND rstPloading("loc_cod") = "PHCEB" then
							Response.Write("Selected")
						end if
					End If%>> <%=rstPLoading("loc_dsc")%> </option>
					<%
					rstPLoading.MoveNext()
					Wend
					If (rstPLoading.CursorType > 0) Then
					rstPLoading.MoveFirst
					Else
					rstPLoading.Requery
					End If%>
                    <%end if %>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'set value of port for Dennis Orbon  or Cecilia Paras or Erwin Muli or Nerwin Cacho or Lady Jangalay broker only FOR Toyota Tsusho and EHS Lens
				  %>
				  <!-- TI clark Enhancement v2 -->
                  <tr bgcolor="e5e5e5" <%IF UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN response.write "hidden"%>> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">Port of Departure:</td>
                    <td class="body" width="55%"> 
                      <select name="lstDestPort" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                      <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="P14"
					                <%If NOT(rstTBLIMPAPL.EOF) Then
					  	                    If rstTBLIMPAPL("PortOfDept") = "P14"  Then Response.write "Selected"
					                End If%>>P14 - Port of Clark</option>   
						<%Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN%>
							<option value="P03" selected> P03 - Ninoy Aquino Intl Airport </option>
                        <%else %>
                        <option <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>None</option>
                        <%While (NOT rstGBCUOTAB3a.EOF)%>
                        <option value="<%=(rstGBCUOTAB3a.Fields.Item("offClrcod").Value)%>"
							<% 
							If NOT(rstTBLIMPAPL.EOF) Then
								If rstGBCUOTAB3a("offClrCod") = rstTBLIMPAPL("PortOfDept") then Response.Write("Selected")
							Else
								if (Session("bTIN") = "200615811" OR Session("bTIN") = "200615811000" OR Session("bTIN") = "215722696" OR Session("bTIN") = "215722696000" OR Session("bTIN") = "225879904" OR Session("bTIN") = "225879904000") AND rstGBCUOTAB3a("offClrCod") = "P03" then
									Response.Write("Selected")
								elseif (Session("bTIN") = "204867435" OR Session("bTIN") = "204867435000" OR Session("bTIN") = "432899304" OR Session("bTIN") = "432899304000") AND rstGBCUOTAB3a("offClrCod") = "P07B" then
									Response.Write("Selected")
								end if
							End If
							%>> <%=rstGBCUOTAB3a("offClrCod") & " - " & rstGBCUOTAB3a("offClrName")%></option>
                        <%rstGBCUOTAB3a.MoveNext()
						Wend
						If (rstGBCUOTAB3a.CursorType > 0) Then
						  rstGBCUOTAB3a.MoveFirst
						Else
						  rstGBCUOTAB3a.Requery
						End If%>
                        <%end if %>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                   <% if session("btin") <> "10825015300" AND  Session("cTIN") <> "005865295" then %>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Container/Seal No:</td>
                    <td width="67%" bgcolor="e5e5e5"><input type=button name="btnCont" id="btnCont" value="Add/Edit/Delete" onClick="window.open('add-contPEZAexp.asp?ApplNo=<%=ApplNumber%>&cn=<%Response.Write(EncryptPassword(CStr(MM_DB)))%>','popuppage','width=820,height=560,top=100,left=100,scrollbars=yes,resizable=yes');" class="button" 
					<% If NOT(flgEnabled = "True") Then
							Session("locked") = "TRUE"
					   Else
					   		Session("locked") = ""
					   End if %>><font face="Verdana, Arial, Helvetica, sans-serif" color="red" size="1"> <i>(Add the Container and Seal details if FCL only)</i></font></td>
                  </tr>
                  <%end if %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td bgcolor="#666699">&nbsp;</td>
                    <td bgcolor="#666699" align="right" class="body"><font color="#FFFFFF"><b>Financial&nbsp;</b></font></td>
                    <td bgcolor="#666699" class="body"><font color="#FFFFFF"><b>&nbsp;Section</b></font></td>
					<td bgcolor="#666699">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>	
                  			  
                  <input type="hidden" name="txtBank" value="998">
                  <input type="hidden" name="txtBranch" value="NA">
                  
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Terms of Delivery:</td>
                    <td width="55%">
                    <%set rstTDelivery = Server.CreateObject("ADODB.Recordset")
                    rstTDelivery.ActiveConnection = constrCOMINScd
                    rstTDelivery.Source = "SELECT Distinct(tod_dsc), tod_cod FROM GBTODTAB ORDER BY tod_dsc ASC"
                    rstTDelivery.CursorType = 0
                    rstTDelivery.CursorLocation = 2
                    rstTDelivery.LockType = 3
                    rstTDelivery.Open()
                    rstTDelivery_numRows = 0%>
                    
                    <select name="lstTDelivery" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
						<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
                        <%
While (NOT rstTDelivery.EOF)
%>
                        <option value="<%=(rstTDelivery.Fields.Item("tod_cod").Value)%>" 
					  <%
					  If NOT(rstFin.EOF) Then
					  	If rstFin("TDelivery") = rstTDelivery("tod_cod")  Then Response.write "Selected"
					  Else
					  	if UCase(Session("UserID")) = "DHLHRD" then
					  		If rstTDelivery("tod_cod") = "DDP"  Then 
								Response.write "Selected"
							End If
						elseif UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP" then
							If rstTDelivery("tod_cod") = "FCA"  Then 
								Response.write "Selected"
							End If
						' Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then
							' If rstTDelivery("tod_cod") = "CIP"  Then 
								' Response.write "Selected"
							' End If
						else
							if (AllowedDHLTI <> "TRUE") Then
								If rstTDelivery("tod_cod") = "FOB"  Then Response.write "Selected"
							end if
						end if
					  End If
					  %>
					  ><%=UCase(rstTDelivery.Fields.Item("tod_cod").Value)%> - (<%=rstTDelivery.Fields.Item("tod_dsc").Value%>)</option> 
					   <!-- PEZA AEDS (Express) and Export Declaration for TI Clark: add top_cod in option -->
                        <%
  rstTDelivery.MoveNext()
Wend
If (rstTDelivery.CursorType > 0) Then
  rstTDelivery.MoveFirst
Else
  rstTDelivery.Requery
End If
%>
                      </select></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Terms of Payment:</td>
                    <td width="55%">
                    <%set rstTPayment = Server.CreateObject("ADODB.Recordset")
					rstTPayment.ActiveConnection = constrCOMINScd
					rstTPayment.Source = "SELECT * FROM GBTOPTAB ORDER BY top_dsc ASC"
					rstTPayment.CursorType = 0
					rstTPayment.CursorLocation = 2
					rstTPayment.LockType = 3
					rstTPayment.Open()
					rstTPayment_numRows = 0%>
                    
                    <select name="lstTPayment" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                                        <% if session("btin") = "10825015300" AND  Session("cTIN") = "005865295" then %>
                                <option value="01"
					                <%If NOT(rstFin.EOF) Then
					  	                    If rstFin("TPayment") = "01"  Then Response.write "Selected"
					                End If%>>BASIC</option>   
                        <%else %>
                        <%
While (NOT rstTPayment.EOF)
%>
                        <option value="<%=(rstTPayment.Fields.Item("top_cod").Value)%>" 
					 <%
					  If NOT(rstFin.EOF) Then
					  	If rstFin("TPayment") = rstTPayment("top_cod")  Then Response.write "Selected"
					  Else
					  	If rstTPayment("top_cod") = "01" then Response.Write "Selected"
					  End If
					  %>
					  ><%=UCase(rstTPayment.Fields.Item("top_dsc").Value)%></option>
                        <%
  rstTPayment.MoveNext()
Wend
If (rstTPayment.CursorType > 0) Then
  rstTPayment.MoveFirst
Else
  rstTPayment.Requery
End If
%>
                    <%end if %>
                      </select></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <% if session("btin") <> "10825015300" AND  Session("cTIN") <> "005865295" then %>
                  <tr bgcolor="e5e5e5" style="display:none;"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Bank Ref / AAB No.:</td>
                    <td width="55%"><input type="text" name="txtBankRef" size="25" maxlength="17" value="<% If NOT(rstFin.EOF) Then
						Response.Write rstFin("Bankref")
					else
						Response.Write "000000000-0000000"
					end if%>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> <%if (session("btin") = "10825015300" AND Session("cTIN") = "005865295") then %>class="optional" <%end if %>>
                    <font face="Verdana, Arial, Helvetica, sans-serif" color="red" size="1"> <i>e.g. NNNNNNNNN-NNNNNNN</i></font>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <tr style="display:none;"> 
                    <td width="29">&nbsp;</td>
                    <td width="113" class="body">Pre-Payment Account:</td>
                    <td colspan="2"> <input type="text" name="txtPrepaid" size="15" maxlength="15" value="<% If NOT(rstFin.EOF) Then Response.Write rstFin("PrePAcct")%>" onFocus="this.form.txtPrepaid.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
                    </td>
		  		  </tr>
                  <%end if %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Internal Reference:</td>
                    <td width="55%"><input type="text" name="txtIntRef" size="55" maxlength="80" value="<% If NOT(rstFin.EOF) Then Response.Write rstFin("IntRef")%>" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional"></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
		<%If AllowedDHLTI = "TRUE" AND Session("SBFin") <> "NOFIN" Then%>
                </table>
              </div>
            </td>
          </tr>
          <tr> 
            <td bgcolor="868686"> 
              <div align="center"> 
                <input type="hidden" name="txtButton" value="Default">
				<%if not session("Importer") then %>
	                <input type="button" name="btnSave" value="     Save     " onClick="Set_Button(this.form.btnSave.name)" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>  class="button">
				<% end if %>
              </div>
            </td>
          </tr>
          <tr> 
            <td bgcolor="e5e5e5"> 
              <div align="center">
                <table width="620" border="0" cellspacing="0" align="center" class="body">
		<%End If%>
                  <%set rstTBLIMPAPL1 = Server.CreateObject("ADODB.Recordset")
					rstTBLIMPAPL1.ActiveConnection = constrCOMINSpezaEXP    
					rstTBLIMPAPL1.Source = "SELECT *  FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' Order by ItemNo"
					rstTBLIMPAPL1.CursorType = 0
					rstTBLIMPAPL1.CursorLocation = 2
					rstTBLIMPAPL1.LockType = 3
					rstTBLIMPAPL1.Open()
					rstTBLIMPAPL1_numRows = 0
					
				  if NOT rstTBLIMPAPL1.EOF then%>
				  <tr bgcolor="e5e5e5"> 
                    <td bgcolor="#666699" class="body"><font color="#FFFFFF"><b>Item</b></font></td>
                    <td bgcolor="#666699" align="center" class="body"><font color="#FFFFFF"><b>Code / Value </b></font></td>
                    <td bgcolor="#666699" class="body"><font color="#FFFFFF"><b>Description / Number of Packages </b></font></td>
					<td bgcolor="#666699">&nbsp;</td>
                  </tr>
				  <%end if
				  while NOT rstTBLIMPAPL1.EOF%>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <tr bgcolor="e5e5e5"><font face="Verdana, Arial, Helvetica, sans-serif"> 
                    <td width="5%" align="center">
						<a href="" onClick="window.open('ptops_ed_item-detailPEZAEXPvexpress.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>&ItemNo=<%=rstTBLIMPAPL1("ItemNo")%>&cn=<%Response.Write(EncryptPassword(CStr(MM_DB)))%>&rl=<%Response.Write(Request("rl"))%>','popuppage-itemdetail','width=680,height=650,top=100,left=100'); Set_Button('btnSave'); " class="toplink">
							<% if rstTBLIMPAPL1("Regulated") = "True" then %>
								<font color="#FF0000">
							<% end if %>
							<%= rstTBLIMPAPL1("ItemNo") %>
							<% if rstTBLIMPAPL1("Regulated") = "True" then %>
								</font>
							<% end if %>
						</a>
					</td>
                    <td class="body" width="36%"><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%><%=rstTBLIMPAPL1("ItemCode")%><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "</font>"%></td>
                    <td class="body" width="55%"><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%><%=rstTBLIMPAPL1("Goodsdesc1")%><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "</font>"%></td>
                    <td width="4%"><input type=button name="btnHSCode" value="delete" onClick="window.open('del_itemsPEZAEXPexpressPTOPS.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>&ItemNo=<%=rstTBLIMPAPL1("ItemNo")%>&cn=<%Response.Write(EncryptPassword(CStr(MM_DB)))%>&vcode=<%=rstTBLIMPAPL1("ItemNo")%>','popuppage','width=530,height=380,top=100,left=100');" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="button"></td>
                  </font></tr>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%" align="center">&nbsp;</td>
                    <td class="body" width="36%"><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%><%=rstTBLIMPAPL1("InvValue")%>&nbsp;<%=rstTBLIMPAPL1("InvCurr")%> <!--<br> <font color="blue"><%'=rstTBLIMPAPL1("InvValueOrig")%>&nbsp;<%'=rstTBLIMPAPL1("InvCurrOrig")
					%></font>--><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "</font>"%></td>
                    <td class="body" width="55%"><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%><%=rstTBLIMPAPL1("NoPack")%>&nbsp;&nbsp;&nbsp;<%response.write rstTBLIMPAPL1("PackCode")
					set rstPackCode = Server.CreateObject("ADODB.Recordset")
					rstPackCode.ActiveConnection = constrCOMINScd
					rstPackCode.Source = "SELECT pkg_dsc, pkg_cod FROM GBPKGTAB where pkg_cod='"& rstTBLIMPAPL1("PackCode") &"'"
					rstPackCode.CursorType = 0
					rstPackCode.CursorLocation = 2
					rstPackCode.LockType = 3
					rstPackCode.Open()
					rstPackCode_numRows = 0
					 
					response.write " - " & rstPackCode("pkg_dsc")
					rstPackCode.Close()%><%if rstTBLIMPAPL1("Regulated") = "True" then response.write "</font>"%></td>
                    <td width="4%">&nbsp;</td>
                  </tr>

				  <tr bgcolor="e5e5e5"> 
                    <td width="5%" align="center">&nbsp;</td>
					<td colspan="3" class="body" style="width:300px; min-width:70px; max-width:300px; 
										white-space: nowrap; overflow: hidden; 
										text-overflow: ellipsis; text-align:center;">
						<%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%>
						<%=rstTBLIMPAPL1("ecai_no_list")%>
						<%if rstTBLIMPAPL1("Regulated") = "True" then response.write "<font color=#FF0000>"%>
					</td>
                  </tr>
				  <%rstTBLIMPAPL1.movenext
				  wend%>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td class="body" width="36%">&nbsp;</td>
                    <td class="body" width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <%set rstFinancial121 = Server.CreateObject("ADODB.Recordset")
					rstFinancial121.ActiveConnection = constrCOMINSpezaEXP  
					rstFinancial121.Source = "SELECT OffClear FROM tblEXPApl_Master WHERE ApplNo='" & ApplNumber & "'"
					rstFinancial121.CursorType = 0
					rstFinancial121.CursorLocation = 2
					rstFinancial121.LockType = 3
					rstFinancial121.Open()
					rstFinancial121_numRows = 0
				  if rstFinancial121("OffClear") <> "" then%>
                  <tr bgcolor="e5e5e5"> 
                    <td bgcolor="#666699">&nbsp;</td>
                    <td bgcolor="#666699" align="right" class="body"><font color="#FFFFFF"><b>Item&nbsp;</b></font></td>
                    <td bgcolor="#666699" class="body"><font color="#FFFFFF"><b>&nbsp;Section</b></font></td>
					<td bgcolor="#666699">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%set rstTBLIMPAPL111 = Server.CreateObject("ADODB.Recordset")
					rstTBLIMPAPL111.ActiveConnection = constrCOMINSpezaEXP    
					rstTBLIMPAPL111.Source = "SELECT InvNo, InvDate, Marks1, Marks2 FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' AND ItemNo='1'"
					rstTBLIMPAPL111.CursorType = 0
					rstTBLIMPAPL111.CursorLocation = 2
					rstTBLIMPAPL111.LockType = 3
					rstTBLIMPAPL111.Open()
					rstTBLIMPAPL111_numRows = 0
					
					if NOT rstTBLIMPAPL111.EOF then
						if rstTBLIMPAPL111("InvNo") <> "" then
							invn = rstTBLIMPAPL111("InvNo")
						else
							invn = ""
						end if
						if rstTBLIMPAPL111("InvDate") <> "" then 
							invd = Right("00" & Trim(CStr(Month(rstTBLIMPAPL111("InvDate")))), 2) & "/" & Right("00" & Trim(CStr(Day(rstTBLIMPAPL111("InvDate")))), 2) & "/" & Right("00" & Trim(CStr(Year(rstTBLIMPAPL111("InvDate")))), 4) 'formatdatetime(rscheck("InvDate"))
						else
							invd = ""
						end if
						mrks1 = rstTBLIMPAPL111("Marks1")
						mrks2 = rstTBLIMPAPL111("Marks2")
						dis = "True"
					else
						invn = ""
						
                        if session("btin") = "10825015300" AND Session("cTIN") = "005865295" then
						    mrks1 = "AS ADDRESSED"
                            invd = date()
						<!-- PEZA AEDS (Express) and Export Declaration for TI Clark -->
						Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then
							mrks1 = "AS ADDRESSED"
                            invd = date()
						<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: Kweticlark must defaulted to current date, editable -->
						ElseIf UCase(Session("UserID")) = "KWETICLARK" Then
							invd = date()
                        else
                            invd = ""
                            mrks1 = ""
                        end if
						mrks2 = ""
						dis = ""
					end if
					rstTBLIMPAPL111.Close%>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Exportables:</td>
                    <td width="55%"><input type="text" name="txtHSCode" size="10" onFocus="blur()" value="" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True" %>> <input type="text" name="txtHSCode_Tar" size="3" onFocus="blur()" value="" <%If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>> <input type=button name="btnHSCode" value="Item Lookup" onClick="window.open('ptops-lookup-importables-pezaexp.asp?cn=<%Response.Write(EncryptPassword(CStr(MM_DB)))%>','popuppage','width=1000,height=500,top=100,left=150');" class="button"></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
<tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Goods Description:</td>
                    <td width="55%"><input type="text" name="txtHSDsc" size="45" value="" onFocus="blur()" ></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr bgcolor="e5e5e5" <%IF (UCase(Session("UserID")) = "FEDEXTICLARK") OR (AllowedDHLTI = "TRUE") OR (UCase(Session("UserID")) = "KWETICLARK") THEN response.write "hidden"%>> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">HS Code Description:</td>
                    <td width="55%"><input type="text" name="txtRate" size="45" onFocus="blur()" value=""></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <input type="hidden" name="txtHSDesc" value="">
                  <input type="hidden" name="txtitemcode" value="">
				  <input type="hidden" name="txtPTOPSRowId" value="">
				  <input type="hidden" name="txtEcaiList" value="">
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">Supplementary Value 1:</td>
                    <td width="55%"><input type="text" name="txtSupVal1" size="45" maxlength="35" value="" onFocus="this.form.txtSupVal1.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>> <input type="text" name="HSUOM" value="" size="10"></td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'if (session("btin") <> "10825015300" AND Session("cTIN") <> "005865295") then
				  %>
                  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">
						<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: Marks and No/STMO no-->
						<%If UCase(Session("UserID")) = "FEDEXTICLARK" OR AllowedDHLTI = "TRUE" OR UCase(Session("UserID")) = "KWETICLARK" Then%>
						Marks and No/STMO no:
						<%Else%>
						Marks and Numbers:
						<%End If%>
					</td>
                    <td width="55%">
						<%If UCase(Session("UserID")) = "FEDEXTICLARK" OR UCase(Session("UserID")) = "KWETICLARK" Then%>
						<select name="txtMarks1" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>
						
						<%If rsSTMO.EOF Then%>
						<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
						<option value="AS ADDRESSED" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>AS ADDRESSED</option>
						<%Else%>
						<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
						<option value="AS ADDRESSED">AS ADDRESSED</option>
						<%
						set rstTBLIMPAPL2 = Server.CreateObject("ADODB.Recordset")
						rstTBLIMPAPL2.ActiveConnection = constrCOMINSpezaEXP    
						rstTBLIMPAPL2.Source = "SELECT InvNo, InvDate, Marks1, Marks2 FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' AND ItemNo='1'"
						rstTBLIMPAPL2.CursorType = 0
						rstTBLIMPAPL2.CursorLocation = 2
						rstTBLIMPAPL2.LockType = 3
						rstTBLIMPAPL2.Open()
						rstTBLIMPAPL2_numRows = 0
						
							While (NOT rsSTMO.EOF)%>
							<option value="<%=(rsSTMO.Fields.Item("STMO_number").Value)%>"
								<% 
								If NOT(rstTBLIMPAPL2.EOF) Then
									If rsSTMO("STMO_name") = rstTBLIMPAPL2("Marks1") Then Response.Write("Selected")
								End If
								%>> <%=(rsSTMO.Fields.Item("STMO_number").Value)%></option>
							<%
							  rsSTMO.MoveNext()
							Wend
						End If%>

						<%
						If (rsSTMO.CursorType > 0) Then
						  rsSTMO.MoveFirst
						End If
						%>
                      </select>
						<%Else%>
							<input type="text" name="txtMarks1" size="45" maxlength="35" value="<%=mrks1%>" onFocus="this.form.txtMarks1.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>
						<%End If%>
					</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">&nbsp;</td>
                    <td width="67%" bgcolor="e5e5e5"> 
					<%If AllowedDHLTI = "TRUE" Then%>
						<select name="txtMarks2" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %> class="optional">
				
							<%If rsSTMO.EOF Then%>
							<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
							<%Else%>
							<option value="None" <% If NOT(rstTBLIMPAPL.EOF) Then Response.write "Selected"%>>Please Select</option>
							<%
							set rstTBLIMPAPL2 = Server.CreateObject("ADODB.Recordset")
							rstTBLIMPAPL2.ActiveConnection = constrCOMINSpezaEXP    
							rstTBLIMPAPL2.Source = "SELECT InvNo, InvDate, Marks1, Marks2 FROM tblEXPApl_Detail WHERE Applno = '" & ApplNumber & "' AND ItemNo='1'"
							rstTBLIMPAPL2.CursorType = 0
							rstTBLIMPAPL2.CursorLocation = 2
							rstTBLIMPAPL2.LockType = 3
							rstTBLIMPAPL2.Open()
							rstTBLIMPAPL2_numRows = 0
							
								While (NOT rsSTMO.EOF)%>
								<option value="<%=(rsSTMO.Fields.Item("STMO_number").Value)%>"
									<% 
									If NOT(rstTBLIMPAPL2.EOF) Then
										If rsSTMO("STMO_name") = rstTBLIMPAPL2("Marks1") Then Response.Write("Selected")
									End If
									%>> <%=(rsSTMO.Fields.Item("STMO_number").Value)%></option>
								<%
								  rsSTMO.MoveNext()
								Wend
							End If%>

							<%
							If (rsSTMO.CursorType > 0) Then
							  rsSTMO.MoveFirst
							End If
							%>
						</select>
					<%Else%>
						<input type="text" name="txtMarks2" size="45" maxlength="35" value="<%=mrks2%>" onFocus="this.form.txtMarks2.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%> class="optional">
					<%End If%>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%'end if
				  %> 
                  <%
					set rstPackCode = Server.CreateObject("ADODB.Recordset")
					rstPackCode.ActiveConnection = constrCOMINScd
					rstPackCode.Source = "SELECT pkg_dsc, pkg_cod FROM GBPKGTAB ORDER BY pkg_dsc ASC"
					rstPackCode.CursorType = 0
					rstPackCode.CursorLocation = 2
					rstPackCode.LockType = 3
					rstPackCode.Open()
					rstPackCode_numRows = 0
				  %>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Packages/Units:</td>
                    <td width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtNoPack" size="10" maxlength="10" value="" onFocus="this.form.txtNoPack.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>
                      <select name="lstPkg_dsc" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>
<%While (NOT rstPackCode.EOF)
strPackCode = rstPackCode("pkg_cod") & " - " & UCase(Trim(rstPackCode("pkg_dsc")))
If len(strPackCode) > 30 Then strPackCode = Left(strPackCode, 27)  & "..."%>
                        <option value="<%=(rstPackCode.Fields.Item("pkg_cod").Value)%>"
                        <%if (session("btin") = "10825015300" AND Session("cTIN") = "005865295") then
							if UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP" Then
								If rstPackCode("pkg_cod") = "CT" Then 
									Response.write "Selected"
								End If
							Else
								if rstPackCode("pkg_cod") = "PK" Then 
									Response.write "Selected"
								End If
							End If
						 Elseif (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then 
							If rstPackCode("pkg_cod") = "CT" Then 
								Response.write "Selected"
							End If
						 Elseif UCase(Session("UserID")) = "KWETICLARK" Then 
							If rstPackCode("pkg_cod") = "PK" Then 
								Response.write "Selected"
							End If
						Else
                         end if%>><%=UCase(strPackCode)%></option>
<%rstPackCode.MoveNext()
Wend
If (rstPackCode.CursorType > 0) Then
  rstPackCode.MoveFirst
Else
  rstPackCode.Requery
End If%>
                      </select>
                      </td>
                      <td width="4%">&nbsp;</td>
                  </tr>
                  <%rstPackCode.Close%>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%"> 
                      <div align="left">Invoice Number:</div>
                    </td>
                    <td width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtInvNo" value="<%=invn%>" onFocus="this.form.txtInvNo.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%> maxlength="35">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
				  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%"> 
                      <div align="left">Invoice Date:</div>
                    </td>
                    <td width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="timestamp" value="<%=invd%>" onFocus="this.form.timestamp.select();" onClick="show_calendar('document.frmAdd.timestamp', document.frmAdd.timestamp.value);" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%> maxlength="300" class="optional">
                    <%if strStatus = "C" OR strStatus = "I" then%>
                      <a href="javascript:show_calendar('document.frmAdd.timestamp', document.frmAdd.timestamp.value);"><img src="cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the Date"></a>
                    <%end if%>
                      <font face="Verdana, Arial, Helvetica, sans-serif" color="red" size="1">&nbsp;e.g. mm/dd/yyyy</font>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%
					set rstCOCode = Server.CreateObject("ADODB.Recordset")
					rstCOCode.ActiveConnection = constrCOMINScd
					rstCOCode.Source = "SELECT * FROM DMCityOrigin ORDER BY cityDisc"
					rstCOCode.CursorType = 0
					rstCOCode.CursorLocation = 2
					rstCOCode.LockType = 3
					rstCOCode.Open()
					rstCOCode_numRows = 0
				  %>
				  <!-- TI clark Enhancement v2 -->
                  <tr bgcolor="e5e5e5" <%IF UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN response.write "hidden"%>>  
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">C.O. Code:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                      <select name="lstCOCode" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>                        
<%While (NOT rstCOCode.EOF)%>
                        <option value="<%=UCase(rstCOCode.Fields.Item("cityCode").Value)%>" 
						<%if rstCOCode("cityCode") = "PH" Then Response.write "Selected"%>>
						<%=UCase(rstCOCode.Fields.Item("cityCode").Value)%> - <%=UCase(rstCOCode.Fields.Item("cityDisc").Value)%>
                        </option>
<%rstCOCode.MoveNext()
Wend
If (rstCOCode.CursorType > 0) Then
  rstCOCode.MoveFirst
Else
  rstCOCode.Requery
End If%>
                      </select>
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%rstCOCode.Close
				  	set rstProcedure = Server.CreateObject("ADODB.Recordset")
					rstProcedure.ActiveConnection = constrCOMINScd
					rstProcedure.Source = "SELECT Distinct(cp4_cod) FROM dbo.GBCP4CP3 Where cp4_cod LIKE '" & rstTBLIMPAPL("MDec2") & "%' ORDER BY cp4_cod"
					rstProcedure.CursorType = 0
					rstProcedure.CursorLocation = 2
					rstProcedure.LockType = 3
					rstProcedure.Open()
					rstProcedure_numRows = 0%>
				  <!-- TI clark Enhancement v2 -->
                  <tr bgcolor="e5e5e5" <%IF UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") THEN response.write "hidden"%>> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Procedure/Extended Code:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                    <select name="lstProcedure" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>  
					  <%If UCase(Session("UserID")) = "FEDEXTICLARK" OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") then%>	
							<option value="1000" selected>
							1000 000 - NORMAL PROCEDURE, DUTIES & TAXES SUBJECT FOR PAYMENT
							</option>
					  <%Else%>
						<%While (NOT rstProcedure.EOF)%>
							<option value="<%=UCase(rstProcedure.Fields.Item("cp4_cod").Value)%>">
							<%=UCase(rstProcedure.Fields.Item("cp4_cod").Value)%> 000 - NORMAL PROCEDURE, DUTIES & TAXES SUBJECT FOR PAYMENT
							</option>
						<%rstProcedure.MoveNext()
						Wend
						If (rstProcedure.CursorType > 0) Then
						  rstProcedure.MoveFirst
						Else
						  rstProcedure.Requery
						End If%>
					  <%End If%>
                      </select>
                      <input type="hidden" name="lstExtCode" value="000">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%rstProcedure.Close%>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Item Gross Weight:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtItemGWeight" size="15" maxlength="10" value="" onFocus="this.form.txtItemGWeight.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>
                      <font face="Verdana, Arial, Helvetica, sans-serif" size="1">KG </font></td>
                      <td width="4%">&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Item Net Weight:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtItemNWeight" size="15" maxlength="10" value="" onFocus="this.form.txtItemNWeight.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>>
                      <font face="Verdana, Arial, Helvetica, sans-serif" size="1">KG </font></td>
                      <td width="4%">&nbsp;</td>
                  </tr>
				  <input type="hidden" name="txtQuo_cod" value="NNNNN">
				  <input type="hidden" name="txtQuo_desc" value="NOT RELATED, NO RSTRCTN/CNDTN/RYLTS/ARRNGMNTS">
				  <input type="hidden" name="txtValMethodNum" value="NV">
				  <input type="hidden" name="txtValMethodDesc" value="TRANSACTION VALUE">
                  <%if session("btin") <> "10825015300" AND Session("cTIN") <> "005865295" then %>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Previous Document:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtPrevDoc" size="40" maxlength="50" value="" onFocus="this.form.txtPrevDoc.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%> class="optional">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%end if %>
                  <tr> 
                    <td width="3%" bgcolor="e5e5e5">&nbsp;</td>
                    <td class="body" bgcolor="e5e5e5" width="27%">Item Invoice Value:</td>
                    <td class="body" width="67%" bgcolor="e5e5e5"> 
                      <input type="text" name="txtInvValue" size="12" maxlength="11" value="" onFocus="this.form.txtInvValue.select();" <% If NOT(flgEnabled = "True") Then Response.write "Disabled = True"%>><font face="Verdana, Arial, Helvetica, sans-serif" size="1">&nbsp;USD&nbsp;</font>           
                    <!--<input type=button name="btnConverter" value="Converter" onClick="window.open('USDConverter.asp','popuppage','width=430,height=150,top=100,left=100');" class="button" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>>-->
                      <input type="hidden" name="txtInvValueOrig" size="12" maxlength="11" value="" onFocus="blur()">
                      <input type="hidden" name="lstcurrorig" size="5" maxlength="4" value="" onFocus="blur()">
                    </td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                  <%end if%>
				  <tr bgcolor="e5e5e5"> 
                    <td width="5%">&nbsp;</td>
                    <td width="36%" class="body">&nbsp;</td>
                    <td width="55%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr> 
            <td bgcolor="868686"> 
              <div align="center">
			  	<%if Session("SBFin") = "NOFIN" then %>
					<input type="hidden" name="txtButton" value="Default">
	                <input type="button" name="btnSave" value="     Save     " onClick="Set_Button(this.form.btnSave.name)" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>  class="button">
				<% end if %>
				<%if not session("Importer") then 
					if rstFinancial121("OffClear") <> "" then%>
                    	<input type="button" name="btnAdditem" value="   Save Item   " onClick="Set_Button1(this.form.btnAdditem.name)" <% If NOT(flgEnabled = "True") Then Response.write "Disabled=True" %>  class="button">
				<% 	end if
				end if %>
                
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td bgcolor="#FFFFFF" valign="top" height="16" width="20">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="35" height="1">&nbsp;</td>
      <td bgcolor="#999999" bordercolor="#FFFFFF" align="center" valign="top" width="183" height="1">&nbsp;</td>
      <td bgcolor="ffffff" bordercolor="#FFFFFF" align="center" valign="top" width="425" height="1">&nbsp;</td>
      <td bgcolor="#FFFFFF" valign="top" height="1" width="20">&nbsp;</td>
    </tr>
    <tr> 
      <td bgcolor="#666699" align="left" valign="top" height="133" colspan="4"> 
        <p> 
          <input type="hidden" name="hdnImporter" value=<%=session("Importer")%>>
          <input type="hidden" name="MM_insert" value="true">
        </p>
        <br>
        <table width="90%" border="0" cellspacing="0" align="center">
          <!--DWLayoutTable-->
          <tr> 
            <td width="13%">
				<div align="center">
                <%if not session("Importer") and applno = "1" then%>
					<a href="cws_preassessment.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image91','','../Images/sub-calc_2.jpg',1)"><img src="../Images/sub-calc_1.jpg" name="Image91" width="50" height="50" border="0" id="Image91"></a> 
                
					<img name="Image9" border="0" src="../Images/sub-calc_1.jpg" width="50" height="50"> 
                <%end if%>
				</div>
			</td>
			  
            <td width="13%"> <div align="center">
                <%if not session("Importer") and (Stats="C" or Stats="S") then%>
				<!-- PEZA AEDS (Express) and Export Declaration for TI Clark -->
				<a 
					<% 
					'-- Check if the user is "dhlticlark" account --
					If (AllowedDHLTI = "TRUE") Then 
					%>
						<% 
						'-- If INSUFFICIENT Balance INS Account, show popup window --
						If INSCASHBAL < INSCharge Then 
						%>
							onClick="if (!confirmSend()) return false; window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
						<% 
						'-- Sufficient balance --
						Else 
						%>
							<%
							'-- If the Item/s isn't rejected -- 
							' If rejectedItems = "" AND HSCodestat_ItemNo = "" Then 
							If rejectedItems = "" Then 
							%>
								<%
								'-- Regulated item/s with popup windows -- 
								If ImpMon <> "" Then 
								%>
								<% If AllowedDHLTI = "TRUE" Then %>
									onClick="if (!confirmSend()) return false; window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
								<% Else %>
									onClick="window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
								<% End If %>
								<%
								'-- Unregulated Item/s, no popup window --
								Else 
								%>
									href="ptops_res-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>"
									<%'--Validation of BL no. before sending 12/04/2024 Atoralde--
									If AllowedDHLTI = "TRUE" Then%>
										onClick="return confirmSend()"
									<% End If %>
									target="_blank"
								<% End If %>
								onclick="reloadPage()"
							<%
							'-- If the Item/s is rejected --
							Else 
							%>
								onclick="handleSendButton();"
							<% End If %>
						<% End If %>
						
					<%
					'-- For all accounts except dhlticlark --
					Else 
					%>
						href="ptops_check-msgPEZAEXPexpress.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" 
					<% End If %>
						
						<%If AllowedDHLTI = "TRUE" Then%>
							onClick="return confirmSend()"
						<% End If %>
					onMouseOut="MM_swapImgRestore()" 
					onMouseOver="MM_swapImage('Image101','','../Images/sub-send_2.jpg',1)"
					>
					<img src="../Images/sub-send_1.jpg" name="Image101" width="50" height="50" border="0" id="Image101">
					</a>
				
				<%else%>
                <img name="Image10" border="0" src="../Images/sub-send_1.jpg" width="50" height="50"> 
                <%end if%>
              </div></td>
			  
            <td width="13%"> 
			<%  Set rsINSBank = Server.CreateObject("ADODB.Recordset")
				rsINSBank.ActiveConnection = constrPEZAimp
				rsINSBank.Source = "SELECT * FROM tblCashAdv WHERE DecTIN = '"&Session("regBtin")&"' AND Applno = '"&ApplNumber&"'"
				rsINSBank.CursorType = 3
				rsINSBank.CursorLocation = 2
				rsINSBank.LockType = 1
				rsINSBank.Open()
			%>
			<% If rsINSBank.EOF = False then
				If Stats <> "I" AND Stats <> "C" AND Stats <> "" AND Stats <> "ER" Then %>			
				<div align="center">
					<a href="http://testweb.intercommerce.com.ph/WebCWS/pdf/cws_insrcptPEZAexp.php?applno=<%=ApplNumber%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image111','','../Images/sub-resp_2.jpg',1)"><img src="../Images/sub-resp_1.jpg" name="Image111" width="50" height="50" border="0" id="Image111"></a>
				</div>
				<!-- <div align="center"><img name="Image11" border="0" src="../Images/sub-resp_1.jpg" width="50" height="50"></div> -->
				<%end if
			end if%> 
			</td>
            <td width="13%">
            <% If Stats <> "I" AND Stats <> "" AND Stats <> "ER" Then %>
				<div align="center">
					<a target = "_blank" href="http://testweb.intercommerce.com.ph/WebCWS/pdf/sadPTOPSPEZAEXP.php?aplid=<%=ApplNumber%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image15','','../sgl/images/subsad2.jpg',1)"><img src="../sgl/images/subsad1.jpg" name="Image15"  width="50" height="50" border="0" id="Image15"></a> 
				</div>
            <% End If %>
			</td>
			<td width="13%">
			<!-- for SGL -->
			<%  Set rsINSBank1 = Server.CreateObject("ADODB.Recordset")
				rsINSBank1.ActiveConnection = constrCOMINSad
				rsINSBank1.Source = "SELECT * FROM tblCashAdv WHERE Refno = '"&ApplNumber&"' and cltcode='"&Session("cltcode")&"'"
				rsINSBank1.CursorType = 3
				rsINSBank1.CursorLocation = 2
				rsINSBank1.LockType = 1
				rsINSBank1.Open()
			%>
			<% If rsINSBank1.EOF = False Then
				If Stats <> "I" AND Stats <> "" AND Stats <> "ER" Then %>
					<div align="center">
						<a href="/webcws/receipts/cws_insrcptPTOPSexp.php?TRANNO=<%=rsINSBank1("tranno")%>&refno=<%=ApplNumber%>&cltcode=<%=Session("cltcode")%>&UserID=<%=Session("UserID")%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image111','','../Images/sub-resp_2.jpg',1)"><img src="../Images/sub-resp_1.jpg" name="Image111" width="50" height="50" border="0" id="Image111"></a>
					</div>
				<%end if
			 end if%>
			 </td>
			 <td width="13%">
			 <%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR UCase(Session("UserID")) = "KWETICLARK" then%>
				<div align="center">
						<a href="ptops_ed_impdecPEZAEXPexpressCreateFrom.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image112','','../Images/sub-send_2.jpg',1)"><img src="../Images/sub-send_1.jpg" name="Image112" width="50" height="50" border="0" id="Img112"></a> 
					</div>
			 <%end if%>
			 <%If(AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then%>
				<%If Stats <> "I" AND Stats <> "C" Then%>
					<div align="center">
						<a href="ptops_ed_impdecPEZAEXPexpressCreateFrom.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image112','','../Images/sub-send_2.jpg',1)"><img src="../Images/sub-send_1.jpg" name="Image112" width="50" height="50" border="0" id="Img112"></a> 
					</div>
				<%End If%>
			 <%End If%>
             </td>
          </tr>
          <tr> 
            <td width="13%" height="90"> 
			<%if not session("Importer") and applno = "1" then %>
				<div align="center" class="btmlink">
					<a href="cws_preassessment.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" class="btmlink">Pre-assessment</a>
				</div>
				<div align="center" class="Body">Pre-assessment</div>
            <%end if%>
			</td>
			  
            <td width="13%">
			<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: DHLTICLARK open new window if send button clicked -->
			<%if not session("Importer") and (Stats="C" or Stats="S") then%> 
				<div align="center" class="btmlink">	
				  <a 
					<% 
					'-- Check if the user is "dhlticlark" account --
					If (AllowedDHLTI = "TRUE") Then 
					%>
						<% 
						'-- If INSUFFICIENT Balance INS Account, show popup window --
						If INSCASHBAL < INSCharge Then 
						%>
							onClick="if (!confirmSend()) return false; window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
						<% 
						'-- Sufficient balance --
						Else 
						%>
							<%
							'-- If the Item/s isn't rejected -- 
							' If rejectedItems = "" AND HSCodestat_ItemNo = "" Then 
							If rejectedItems = "" Then 
							%>
								<%
								'-- Regulated item/s with popup windows -- 
								If ImpMon <> "" Then 
								%>
									<% If AllowedDHLTI = "TRUE" Then %>
										onClick="if (!confirmSend()) return false; window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
									<% Else %>
										onClick="window.open('ptops_check-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>','popupcheckmsg','width=545,height=585,top=100,left=100');"
									<% End If %>

								<% 
								'-- Unregulated Item/s, no popup window --
								Else 
								%>
									href="ptops_res-msgPEZAEXPexpressp.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>"
									
									<%If AllowedDHLTI = "TRUE" Then%>								
										onClick="return confirmSend()"
									<% End If %>
									target="_blank"
								<% End If %>
								onclick="reloadPage()"
							<%
							'-- If the Item/s is rejected --
							Else 
							%>
								onclick="handleSendButton();"
							<% End If %>
						<% End If %>
						
						class="popbtmlink"
					<%
					'-- For all accounts except dhlticlark --
					Else 
					%>
						href="ptops_check-msgPEZAEXPexpress.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>"
						<%
							If AllowedDHLTI = "TRUE" Then%>
								onClick="return confirmSend()"
							<% End If %>
						class="btmlink"
					<% End If %>
					
				  >Send</a>
				</div>
            <%else%> 
				<div align="center" class="Body">Send</div>
            <%end if%> 
			</td>
            <td width="13%"> 
			<% If rsINSBank.EOF = False then
				If Stats <> "I" AND Stats <> "C" AND Stats <> "" AND Stats <> "ER" Then %>
					<div align="center" class="btmlink">
						<a href="http://testweb.intercommerce.com.ph/WebCWS/pdf/cws_insrcptPEZAexp.php?applno=<%=ApplNumber%>" class="btmlink">EPF Receipt</a>
					</div>
					<!--<div align="center" class="body">Response</div>-->
				<%end if
			End if%>
			</td>
            <td width="13%">
            <% If Stats <> "I" AND Stats <> "" AND Stats <> "ER" Then %>
				<div align="center" class="btmlink">
					<a target = "_blank" href="http://testweb.intercommerce.com.ph/WebCWS/pdf/sadPTOPSPEZAEXP.php?aplid=<%=ApplNumber%>" class="btmlink">SAD</a>
				</div>
            <% End If %>
			</td>
			<td width="13%"> 
			<% If rsINSBank1.EOF = False Then
				If Stats <> "I" AND Stats <> "" AND Stats <> "ER" Then %> 
					<div align="center" class="btmlink">
						<a href="/webcws/receipts/cws_insrcptPTOPSexp.php?TRANNO=<%=rsINSBank1("tranno")%>&refno=<%=ApplNumber%>&cltcode=<%=Session("cltcode")%>&UserID=<%=Session("UserID")%>" class="btmlink">INS Receipt</a>
					</div>
					<!--<div align="center" class="body">Response</div>-->
				<%end if
			end if%>
			</td>
			<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: create from existing -->
			<td width="13%"> 
				<%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR UCase(Session("UserID")) = "KWETICLARK" then%>
					<div align="center" class="btmlink"><a href="ptops_ed_impdecPEZAEXPexpressCreateFrom.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" class="btmlink">Create From Existing</a></div>
				<%End If%>
				<%If (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") Then%>
					<%If Stats <> "I" AND Stats <> "C" Then%>
						<div align="center" class="btmlink"><a href="ptops_ed_impdecPEZAEXPexpressCreateFrom.asp?ApplNo=<%=ApplNumber%>&Status=<%=Stats%>" class="btmlink">Create From Existing</a></div>
					<%End If%>
				<%End If%>
			</td>
			</tr>
        </table>
      </td>
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
rstHSCode.Close
'forSGL.Close
rstTBLIMPAPL.Close
rstGBCTYTAB.Close
rstGBCTYTAB0.Close
rstGBCUOTAB2.Close
rstGBCUOTAB3.Close
rstGBCUOTAB3a.Close
rstPURP.Close
rstPAYP.Close
rstGBSHDTAB.Close
rstGBPRVORG.Close
rstCreateRec.Close
rsRemarks.Close
rsRemarks22.Close
rsRemarks33.Close
rsExporter.Close
rstPLoading.Close
rsINSBank1.Close
rsINSBank.Close
rstTDelivery.Close
rstTPayment.Close
rstFin.Close
rstFinancial121.Close
%>
<!-- PEZA AEDS (Express) and Export Declaration for TI Clark: Autosuggest -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
<%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (AllowedDHLTI = "TRUE" AND Session("cltcode") ="DHLEXA") OR (UCase(Session("UserID")) = "KWETICLARK") Then%>

	function searchDatabase() {
	  var xmlhttp = new XMLHttpRequest();
	  var url = "ptops_ed_impdecPEZAEXPexpressSearch.asp";
	  var Exporter = document.getElementById("lstExporter").value;
	  
	  url += "?lstExporter=" + Exporter;
	  xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
		  var response = JSON.parse(this.responseText);
		  
		console.log(response)
		  document.getElementById("txtSuppAddr1").value = response.txtSuppAddr1;
		  document.getElementById("txtSuppAddr2").value = response.txtSuppAddr2;
		  document.getElementById("txtSuppAddr3").value = response.txtSuppAddr3;
		  document.getElementById("txtSuppAddr4").value = response.txtSuppAddr4;
		  document.getElementById("lstCountry1").value  = response.lstCountry1;
		}
		else {
		  document.getElementById("txtSuppAddr1").value = "";
		  document.getElementById("txtSuppAddr2").value = "";
		  document.getElementById("txtSuppAddr3").value = "";
		  document.getElementById("txtSuppAddr4").value = "";
		  document.getElementById("lstCountry1").value = "";
		}
	  };
	  xmlhttp.open("GET", url, true);
	  xmlhttp.send();
	}
<%End If%>
</script>
<script>
//DHL-TI PEZ AEDS Importer auto suggest dropdown removal: remove the cltcode in condition to remove the auto suggest: Atoralde
<%If (UCase(Session("UserID")) = "FEDEXTICLARK" AND Session("cltcode") ="FEDEXP") OR (UCase(Session("UserID")) = "KWETICLARK") Then%>
	let childWindow;
    
	$(document).ready(function(){
        $('#lstExporter').keyup(function(){
            var query = $(this).val();
            if(query != ''){
                $.ajax({
                    url: 'ptops_ed_impdecPEZAEXPexpressAutocomplete.asp',
                    method: 'GET',
                    data: {search: query},
                    success: function(data){
                        $('#results').html(data);
                    }
                });
            } else {
                $('#results').html('');
            }
        });
        // Add click event listener to the autocomplete list items
        $(document).on('click', '#results li', function(){
            var Exporter = $(this).data('exporter');
            var SuppAddr1 = $(this).data('suppaddr1');
            var SuppAddr2 = $(this).data('suppaddr2');
            var SuppAddr3 = $(this).data('suppaddr3');
            var SuppAddr4 = $(this).data('suppaddr4');
			var lstCountry1 = $(this).data('lstcountry1');
			
            $('#lstExporter').val(Exporter);
            $('#txtSuppAddr1').val(SuppAddr1);
            $('#txtSuppAddr2').val(SuppAddr2);
            $('#txtSuppAddr3').val(SuppAddr3);
            $('#txtSuppAddr4').val(SuppAddr4);
            $('#lstCountry1').val(lstCountry1);
			
			updateChildWindow();
			
            $('#results').html('');
        });
		
		function updateChildWindow() {
			if (childWindow && !childWindow.closed) {
				var inputValues = {
					lstExporter: $('#lstExporter').val(),
					txtSuppAddr1: $('#txtSuppAddr1').val(),
					txtSuppAddr2: $('#txtSuppAddr2').val(),
					txtSuppAddr3: $('#txtSuppAddr3').val(),
					txtSuppAddr4: $('#txtSuppAddr4').val()
				};
				
				childWindow.postMessage(inputValues, '*');
			}
		}
    });
<%End If%>
</script>


<script>
  function reloadPage() {
    //event.preventDefault(); // Prevents the default behavior of the link
    setTimeout(function() {
      window.location.reload();
    }, 5000);
  }
</script>