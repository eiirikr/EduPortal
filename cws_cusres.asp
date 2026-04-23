<%@LANGUAGE="VBSCRIPT"%> 
<!--#include file="../Connections/constrTIMcomins.asp" -->
<% 

ApplNumber = request.QueryString("ApplNo")
Stats = request.QueryString("Status")
'update 8zn AG status that is paid to AP status
if UCase(Session("mod_cod")) = "8ZN" then
	set rsFIN = Server.CreateObject("ADODB.Recordset")
	rsFIN.ActiveConnection = "Driver={SQL Server};Server=COMINS;Database=PL-INSCUSTSTDB;Uid=sa;Pwd=df0rc3;"
	rsFIN.Source = "SELECT PrePAcct FROM TBLIMPAPL_FIN WHERE APPLNO = '" & ApplNumber & "'"
	rsFIN.CursorType = 0
	rsFIN.CursorLocation = 2
	rsFIN.LockType = 3
	rsFIN.Open()
	rsFIN_numRows = 0
	
	if NOT rsFIN.EOF then
		Session("PAcct") = rsFIN("PrePAcct")
	end if
	rsFIN.Close()
	
	if UCase(Stats) = "AG" then
		if UCase(Session("PAcct")) <> "" then
			'check status of payment
			set rstAPPLSTAT = Server.CreateObject("ADODB.Recordset")
			rstAPPLSTAT.ActiveConnection = "Driver={SQL Server};Server=COMINS;Database=PL-INSAPPLSTAT;Uid=sa;Pwd=df0rc3;"  
			rstAPPLSTAT.Source = "SELECT ApplStat FROM TBLAPPLSTAT WHERE Applno='" & ApplNumber & "' AND ApplStat='PAID'"
			rstAPPLSTAT.CursorType = 0
			rstAPPLSTAT.CursorLocation = 2
			rstAPPLSTAT.LockType = 3
			rstAPPLSTAT.Open
			rstAPPLSTAT_numRows = 0
			
			if NOT rstAPPLSTAT.EOF then
				'if app status is paid, update master status to AP since 8ZN does not have a PMT response
				if UCase(Stats) = "AG" then
					set cmdStatus1 = Server.CreateObject("ADODB.Command")
					cmdStatus1.ActiveConnection = "Driver={SQL Server};Server=COMINS;Database=PL-INSCUSTSTDB;Uid=sa;Pwd=df0rc3;"		
					cmdStatus1.CommandText = "UPDATE TBLIMPAPL_MASTER SET Stat = 'AP' WHERE ApplNo='" & ApplNumber & "'"
					cmdStatus1.CommandType = 1
					cmdStatus1.CommandTimeout = 0
					cmdStatus1.Prepared = true
					cmdStatus1.Execute()
					Session("Status") = "AP"
				end if
			end if
			rstAPPLSTAT.Close
		end if
	end if
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

MM_DB = DecryptPassword(CStr(Request("cn")))
MM_DB = Session("db")
'response.Write(MM_DB)
Dim rsApplError__MMColParam
Dim strStatus
'#### NOTE ##### Change the value of "rsApplError__MMColParam" with actual data; "AGE00102701" Sample data only
'rsApplError__MMColParam = "AGE00102701"
rsApplError__MMColParam = ApplNumber
strStatus = Stats
if (Request("MM_EmptyValue") <> "") then rsApplError__MMColParam = Request("MM_EmptyValue")
%>
<%
set rsApplError = Server.CreateObject("ADODB.Recordset")
rsApplError.ActiveConnection = constrCOMINScd
rsApplError.Source = "SELECT APPLNO, ERRCODE, FLDDESC FROM dbo.TBLRESP_ERR WHERE APPLNO = '" + Replace(ApplNumber, "'", "''") + "'"
rsApplError.CursorType = 0
rsApplError.CursorLocation = 2
rsApplError.LockType = 3
rsApplError.Open()
rsApplError_numRows = 0

set rstTBLIMPAPL = Server.CreateObject("ADODB.Recordset")
rstTBLIMPAPL.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=" & MM_DB & ";Uid=sa;Pwd=df0rc3;"  
rstTBLIMPAPL.Source = "SELECT *  FROM TBLIMPAPL_MASTER  WHERE Applno = '" & ApplNumber & "'"
'response.write "test" & rstTBLIMPAPL.Source
rstTBLIMPAPL.CursorType = 0
rstTBLIMPAPL.CursorLocation = 2
rstTBLIMPAPL.LockType = 3
rstTBLIMPAPL.Open()
rstTBLIMPAPL_numRows = 0

if UCASE(Session("cltcode")) = "FEDEX" then
	if NOT rstTBLIMPAPL.EOF then
		if rstTBLIMPAPL("MDec") = "IES" OR rstTBLIMPAPL("MDec") = "8ZN" OR rstTBLIMPAPL("MDec") = "8ZE" OR rstTBLIMPAPL("MDec") = "4FD" then
			strCRF = "275540614000"
		elseif (LEFT(Session("lstExporter1"),3) = "IES") then
			strCRF = "275540614000"
		end if
	elseif (Session("mod_cod") = "IES") or Session("mod_cod") = "8ZN" or Session("mod_cod") = "8ZE" or Session("mod_cod") = "4FD" then
			strCRF = "275540614000"
	end if
elseif UCASE(Session("cltcode")) = "DHLEXA" then
	if NOT rstTBLIMPAPL.EOF then
		if rstTBLIMPAPL("MDec") = "IES" OR rstTBLIMPAPL("MDec") = "8ZN" OR rstTBLIMPAPL("MDec") = "8ZE" OR rstTBLIMPAPL("MDec") = "4FD" then
			strCRF = "212186731000"
		elseif (LEFT(Session("lstExporter1"),3) = "IES") then
			strCRF = "212186731000"
		end if
	elseif Session("mod_cod") = "IES" or Session("mod_cod") = "8ZN" or Session("mod_cod") = "8ZE" or Session("mod_cod") = "4FD" then
		strCRF = "212186731000"
	end if
elseif UCASE(Session("cltcode") = "ASPACA") OR UCASE(Session("cltcode")) = "BABANTAOA" OR UCASE(Session("cltcode")) = "ABARCEBALA" OR UCASE(Session("cltcode")) = "RSECOBARA" then
	if NOT rstTBLIMPAPL.EOF then
		if rstTBLIMPAPL("MDec") = "IES" OR rstTBLIMPAPL("MDec") = "8ZN" OR rstTBLIMPAPL("MDec") = "8ZE" OR rstTBLIMPAPL("MDec") = "4FD" then
			strCRF = "213688055000"
		elseif (LEFT(Session("lstExporter1"),3) = "IES") then
			strCRF = "213688055000"
		end if
	elseif Session("mod_cod") = "IES" or Session("mod_cod") = "8ZN" or Session("mod_cod") = "8ZE" or  Session("mod_cod") = "4FD" then
		strCRF = "213688055000"
	end if
end if

%>
<html>
<head>
<title>InterCommerce Network Services - Create/Open Application</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../global.css" type="text/css">
<SCRIPT LANGUAGE = "JavaScript">
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
</SCRIPT>
</head>
<body bgcolor="#666666" text="#000000">
<form name="frmCreate" method="get" action="newup-impdec2.asp">
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
                  <td><img src="../Images/win-response.jpg" width="111" height="32"></td>
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
                <table border="0" cellspacing="0" cellpadding="3" width="100%" align="left" height="42">
                  <tr bgcolor="#FCF4D6"> 
                    <td valign="top" height="2" width="25" bgcolor="e5e5e5">&nbsp;</td>
                    <td valign="top" height="2" colspan="3" bgcolor="e5e5e5"> 
                      <p> 
                        <% 
If cStr(strStatus) = "ER" then
 %>
                      </p>
                      <p>
                        <%

'rsApplOK__MMColParam = "AGE00102702"
rsApplError__MMColParam = ApplNumber
if (Request("MM_EmptyValue") <> "") then rsApplError__MMColParam = Request("MM_EmptyValue")
%>
                        <%
Dim Repeat2__numRows
Repeat2__numRows = 10
Dim Repeat2__index
Repeat2__index = 0
rsApplError_numRows = rsApplError_numRows + Repeat2__numRows
%>
                      </p>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr> 
                          <td bgcolor="#666666"> <table width="100%" border="0" cellspacing="1">
                              <tr bgcolor="#666699"> 
                                <td colspan="2" height="25"> <div align="center"><font color="#FFFFFF" class="heading">Application 
                                    No. - <%=ApplNumber%></font></div></td>
                              </tr>
                              <tr bgcolor="#999999"> 
                                <td width="18%" class="body" height="25"> <div align="center"><b><font color="#FFFFFF">Error 
                                    Code </font></b></div></td>
                                <td width="82%" class="body" height="25"> <div align="center"><b><font color="#FFFFFF">Error 
                                    Description </font></b></div></td>
                              </tr>
                              <% 
While ((Repeat2__numRows <> 0) AND (NOT rsApplError.EOF)) 
%>
                              <tr bgcolor="#FFFFFF"> 
                                <td width="18%" class="body" height="25"> <div align="center"><%=(rsApplError.Fields.Item("ERRCODE").Value)%></div></td>
                                <td width="82%" class="body" height="25"> <div align="right"><%=(rsApplError.Fields.Item("FLDDESC").Value)%> 
                                  
                                  </div></td>
                              </tr>
                              <% 
  Repeat2__index=Repeat2__index+1
  Repeat2__numRows=Repeat2__numRows-1
  rsApplError.MoveNext()
Wend
%>
                            </table></td>
                        </tr>
                      </table>
                      <p> 
                        
                      </p>
                      <p> 
                        <%Else
%>
                      </p>
                      <p> 
                        <% 
'Response.Write("If stat = 'AG' or 'AS' then ")
If cStr(strStatus) = "AG" or cStr(strStatus) = "AS" or cStr(strStatus) = "AP" then
%>
                      </p>
                      <p>
                        <%
Dim rsApplOK__MMColParam
'rsApplOK__MMColParam = "AGE00102702"
rsApplOK__MMColParam = ApplNumber
if (Request("MM_EmptyValue") <> "") then rsApplOK__MMColParam = Request("MM_EmptyValue")
%>
                        <%
set rsApplOK = Server.CreateObject("ADODB.Recordset")
rsApplOK.ActiveConnection = constrCOMINScd
rsApplOK.Source = "SELECT APPLNO, TAXCODE, TAXAMT FROM dbo.TBLRESP_GT WHERE APPLNO = '" + Replace(rsApplOK__MMColParam, "'", "''") + "' AND TAXCODE <> 'GRT'"
rsApplOK.CursorType = 0
rsApplOK.CursorLocation = 2
rsApplOK.LockType = 3
rsApplOK.Open()
rsApplOK_numRows = 0
%>
                        <%
Dim Repeat1__numRows
Repeat1__numRows = 10
Dim Repeat1__index
Repeat1__index = 0
rsApplOK_numRows = rsApplOK_numRows + Repeat1__numRows
%>
                      </p>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr> 
                          <td bgcolor="#666666"> 
                            <table width="100%" border="0" cellspacing="1">
                              <tr bgcolor="#666699"> 
                                <td colspan="2" height="25"> 
                                  <div align="center"><font color="#FFFFFF" class="heading">Application 
                                    No. - <%=(rsApplOK.Fields.Item("APPLNO").Value)%></font></div>
                                </td>
                              </tr>
                              <tr bgcolor="#999999"> 
                                <td width="49%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Tax 
                                    Code </font></b></div>
                                </td>
                                <td width="51%" class="body" height="25"> 
                                  <div align="center"><b><font color="#FFFFFF">Tax 
                                    Amount </font></b></div>
                                </td>
                              </tr>
                              <% 
While ((Repeat1__numRows <> 0) AND (NOT rsApplOK.EOF)) 
%>
                              <tr bgcolor="#FFFFFF"> 
                                <td width="49%" class="body" height="25"> 
                                  <div align="center"><%=(rsApplOK.Fields.Item("TAXCODE").Value)%></div>
                                </td>
                                <td width="51%" class="body" height="25"> 
								
                                  <div align="right"><%
									  	if VarType(rsApplOK.Fields.Item("TAXAMT").Value) = VBNULL then
											response.Write("0.00")
										else
										 	response.Write(FormatNumber((rsApplOK.Fields.Item("TAXAMT").Value), 2, -2, -2, -2))
										end if
								  %></div>
                                </td>
                              </tr>
                              <% 
  Repeat1__index=Repeat1__index+1
  Repeat1__numRows=Repeat1__numRows-1
  rsApplOK.MoveNext()
Wend
%>
                            </table>
                          </td>
                        </tr>
                      </table>
                      <p> 
                        <% 
'Response.Write("End If - AG & AS stat")
End If
 %>
                      </p>
                      <p> 
                        <% 
'Response.Write("End If")
End If
 %>
                      </p>
                      <table width="96%" border="0" cellspacing="2" cellpadding="2">
                      </table>
                      <p> 
                        <%
'If cStr(strStatus) <> "ER" Or cStr(strStatus) <> "AG" Or cStr(strStatus) <> "AS" Or cStr(strStatus) = "" Or cStr(strStatus) = "C" Or cStr(strStatus) = "S" Then
If cStr(strStatus) = "" Or cStr(strStatus) = "C" Or cStr(strStatus) = "S" Or cStr(strStatus) = "I" Then
%>
                      </p>
                      <p><font color="#000000"><span class="desc">No Response 
                        Available</span><br>
                        <br>
                        <span class="body">Sorry, there is currently no response 
                        available for this message at this time. Please check 
                        the application.</span></font></p>
                      <p>&nbsp;</p>
                      <p> 
                        <% 
End If
%>
                      </p>
                    </td>
                    <td valign="top" height="2" width="25" bgcolor="e5e5e5">&nbsp;</td>
                  </tr>
                </table>
                <br>
              </div>
            </td>
          </tr>
          <%
            set rsTan = Server.CreateObject("ADODB.Recordset")
            rsTan.ActiveConnection = constrCOMINScd
            rsTan.Source = "SELECT * FROM GBTANFAN WHERE refno = '" + Replace(rsApplOK__MMColParam, "'", "''") + "'"
            rsTan.CursorType = 0
            rsTan.CursorLocation = 2
            rsTan.LockType = 3
            rsTan.Open()

			set rsTanprint = Server.CreateObject("ADODB.Recordset")
            rsTanprint.ActiveConnection = constrCOMINScd
            rsTanprint.Source = "SELECT ISNULL(sentdate, '') as sentdate FROM tblimpapl_master WHERE applno = '" + Replace(rsApplOK__MMColParam, "'", "''") + "'"
            rsTanprint.CursorType = 0
            rsTanprint.CursorLocation = 2
            rsTanprint.LockType = 3
            rsTanprint.Open()
			
			if cdate(sentdate) > "01/04/2024" then
				newTan = "YES" 
			else
				newTan = "NO"
			end if
			
            %>
          <tr> 
            <td bgcolor="#868686"> 
            <div align="center"> 
            <input type="submit" name="Submit" value="&lt;&lt; Back " onClick="MM_goToURL('parent','cws_impdec.asp?Applno=<%=ApplNumber%>&Status=<%=Stats%>');return document.MM_returnValue">
			<%
				if strCRF <> "" then
					set rstPPA = Server.CreateObject("ADODB.Recordset")
					rstPPA.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=PL-INSCUSTSTDB;Uid=sa;Pwd=df0rc3;"
					rstPPA.Source = "SELECT PPA FROM tblEO a INNER JOIN TBLIMPAPL_FIN b ON a.PPA=b.PrePAcct WHERE b.ApplNo='" & ApplNumber & "' and a.cltcode='" & Session("cltcode") & "'"
					rstPPA.CursorType = 0
					rstPPA.CursorLocation = 2
					rstPPA.LockType = 3
					rstPPA.Open()
					
					if NOT rstPPA.EOF then
						ppa = "ok"
					else
						set rstBRN = Server.CreateObject("ADODB.Recordset")
						rstBRN.ActiveConnection = "Driver={SQL Server};Server=WEBCWSDB;Database=PL-INSCUSTSTDB;Uid=sa;Pwd=df0rc3;"
						rstBRN.Source = "SELECT BankRef FROM TBLIMPAPL_FIN WHERE ApplNo='" & ApplNumber & "'"
						rstBRN.CursorType = 0
						rstBRN.CursorLocation = 2
						rstBRN.LockType = 3
						rstBRN.Open()
						
						if Session("mod_cod") = "4FD" AND NOT rstBRN.EOF then
							ppa = "ok"
						else
							ppa = ""
						end if
						
						rstBRN.Close()
					end if
				
					rstPPA.Close()
				else
					ppa = ""
				end if

				If cStr(strStatus) = "AG" or cStr(strStatus) = "AS" or cStr(strStatus) = "AP" then %>
               &nbsp; 
                   <%if NOT rsTan.EOF then
                       if cStr(strStatus) = "AG" or cStr(strStatus) = "AS" or cStr(strStatus) = "AP" then %>
                           <!-- [<a href=<%if session("cbpro") = "True" then 
			   		            response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				            else
							Userid = replace(Session("UserID"), " ", "" )
							
								if newTan = "NO" then 
									response.write "https://student.intercommerce.com.ph/WebCWS/pdf/sad_and_assessmentsection2Tan.php?ApplNo=" & ApplNumber & "&tin=" & Session("B4LTIN") & "&DM=" & DM & "&cltcode=" & Session("cltcode") & "&ppa=" & ppa & "&Section=" & Session("Secti") & "&UserID=" & Session("UserID")
								else
									response.write "https://student.intercommerce.com.ph/WebCWS/pdf/sad_and_assessmentsection2TanNew.php?ApplNo=" & ApplNumber & "&tin=" & Session("B4LTIN") & "&DM=" & DM & "&cltcode=" & Session("cltcode") & "&ppa=" & ppa & "&Section=" & Session("Secti") & "&UserID=" & Session("UserID")
								end if
				            end if%> class="btmlink"> Print SAD and TAN </a>] -->
                       <%end if
                       if cStr(strStatus) = "AG" or cStr(strStatus) = "AP" then %>
                           <!-- [<a href=<%if session("cbpro") = "True" then 
			   		            response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				            else
							Userid = replace(Session("UserID"), " ", "" )
					            response.write "https://student.intercommerce.com.ph/WebCWS/pdf/sad_and_assessmentsection2Fan.php?ApplNo=" & ApplNumber & "&tin=" & Session("B4LTIN") & "&DM=" & DM & "&cltcode=" & Session("cltcode") & "&ppa=" & ppa & "&Section=" & Session("Secti") & "&UserID=" & Session("UserID")
				            end if%> class="btmlink"> Print SAD and FAN </a>] -->
                        <%end if 
                    else%>
                        [<a href=<%if session("cbpro") = "True" then 
			   		        response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				        else
					        response.write "https://student.intercommerce.com.ph/WebCWS/pdf/sad_and_assessmentsection2.php?ApplNo=" & ApplNumber & "&tin=" & Session("B4LTIN") & "&DM=" & DM & "&cltcode=" & Session("cltcode") & "&ppa=" & ppa & "&Section=" & Session("Secti") & "&UserID=" & Session("UserID")
				        end if%> class="btmlink"> Print SAD and Assessment </a>]
                    <%  end if
                    end if%>
					
			<% If cStr(strStatus) = "AG" or cStr(strStatus) = "AS" or cStr(strStatus) = "AP" then %>
               &nbsp; 
                   <%if NOT rsTan.EOF then
                       if cStr(strStatus) = "AG" or cStr(strStatus) = "AS" or cStr(strStatus) = "AP" then %>
                           [<a href=<%if session("cbpro") = "True" then 
			   		            response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				            else
							Userid = replace(Session("UserID"), " ", "" )
							
								if newTan = "NO" then 
									response.write "https://student.intercommerce.com.ph/WebCWS/pdf/assessmentsection2Tan.php?applno=" & ApplNumber  & "&Section=" & Session("Secti")   & "&UserID=" & Userid & "&cltcode=" & Session("cltcode") 
								else
									response.write "https://student.intercommerce.com.ph/WebCWS/pdf/assessmentsection2TanNew.php?applno="& ApplNumber  & "&Section=" & Session("Secti")  & "&UserID=" & Userid & "&cltcode=" & Session("cltcode") 
								end if
				            end if%> class="btmlink"> Print TAN </a>] 
                       <%end if
                       if cStr(strStatus) = "AG" or cStr(strStatus) = "AP" then %>
                           [<a href=<%if session("cbpro") = "True" then 
			   		            response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				            else
							Userid = replace(Session("UserID"), " ", "" )
					            response.write "https://student.intercommerce.com.ph/WebCWS/pdf/assessmentsection2Fan.php?applno=" & ApplNumber  & "&Section=" & Session("Secti")  & "&UserID=" & Userid & "&cltcode=" & Session("cltcode") 
				            end if%> class="btmlink"> Print FAN </a>] 
                        <%end if 
                    else%>
                        [<a href=<%if session("cbpro") = "True" then 
			   		        response.write "cws_assessCBPro.asp?applno=" & ApplNumber & "" 
				        else
					        response.write "https://student.intercommerce.com.ph/WebCWS/pdf/assessmentsection2.php?applno=" & ApplNumber & "&cltcode=" & Session("cltcode") & "&Section=" & Session("Secti") & "&UserID=" & Session("UserID") 
				        end if%> class="btmlink"> Print Assessment </a>]
                    <%  end if
                    end if%>
				<%set rsmoddec = Server.CreateObject("ADODB.Recordset")
				rsmoddec.ActiveConnection = constrCOMINScd
				rsmoddec.Source = "SELECT mdec FROM TBLIMPAPL_MASTER WHERE APPLNO = '" + Replace(ApplNumber, "'", "''") + "'"
				rsmoddec.CursorType = 0
				rsmoddec.CursorLocation = 2
				rsmoddec.LockType = 3
				rsmoddec.Open()
				rsmoddec_numRows = 0
				
				If cStr(strStatus) = "AP" AND rsmoddec("mdec") <> "IED" Then
					'if UCase(Session("mod_cod")) = "8ZN" AND UCase(Session("PAcct")) <> "" then%> 
                <!--&nbsp; [<a href="https://student.intercommerce.com.ph/WebCWS/ssdt/ssdt.php?applno=<%=ApplNumber%>" class="btmlink"> Print SSDT </a>]-->
                	<%'else%>
				&nbsp; [<a href="https://student.intercommerce.com.ph/WebCWS/pdf/ssdt.php?applno=<%=ApplNumber%>" class="btmlink"> Print SSDT </a>]
                <!--&nbsp; [<a href="cws_SSDT.asp" class="btmlink"> Print SSDT </a>] -->
                	<%'end if
				elseif cStr(strStatus) = "AP" AND rsmoddec("mdec") = "IED" Then%>
                &nbsp; [<a href="https://student.intercommerce.com.ph/WebCWS/pdf/ssdt.php?applno=<%=ApplNumber%>" class="btmlink"> Print SSDT </a>]
				<%End If
			'End If %>
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