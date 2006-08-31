From dumitru.popa@blackrivercompanies.com Thu Aug 31 02:10:17 2006
Return-Path: <dumitru.popa@blackrivercompanies.com>
X-Original-To: phil@localhost
Delivered-To: phil@localhost.logicworks.co.nz
Received: from athlon.logicworks.co.nz (localhost [127.0.0.1])
	by athlon.logicworks.co.nz (Postfix) with ESMTP id 5BED736492
	for <phil@localhost>; Wed, 30 Aug 2006 19:21:49 +1200 (NZST)
Delivered-To: weberp@paradise.net.nz
X-Envelope-To: weberp@paradise.net.nz
Received: from pop3.paradise.net.nz [203.96.152.6]
	by athlon.logicworks.co.nz with POP3 (fetchmail-6.3.2)
	for <phil@localhost> (single-drop); Wed, 30 Aug 2006 19:21:49 +1200 (NZST)
Received: (qmail 4795 invoked from network); 30 Aug 2006 07:11:48 -0000
Received: from tclsnelb1-src-1.paradise.net.nz (HELO linda-5.paradise.net.nz) (203.96.152.172)
  by internal-pop3-4.paradise.net.nz with SMTP; 30 Aug 2006 07:11:48 -0000
Received: from smtp-1.paradise.net.nz
 (tclsnelb1-src-1.paradise.net.nz [203.96.152.172]) by linda-5.paradise.net.nz
 (Paradise.net.nz) with ESMTP id <0J4S00B6MUNM43@linda-5.paradise.net.nz> for
 weberp@paradise.net.nz; Wed, 30 Aug 2006 19:11:47 +1200 (NZST)
Received: from server.mh-zelus.com
 (f2.80.5746.static.theplanet.com [70.87.128.242])	by smtp-1.paradise.net.nz
 (Postfix) with ESMTP id 8FE1BB73F2E	for <weberp@paradise.net.nz>; Wed,
 30 Aug 2006 19:11:40 +1200 (NZST)
Received: from [216.234.102.182] (helo=blackrivercompanies.com)
	by server.mh-zelus.com with esmtps (TLSv1:AES256-SHA:256)	(Exim 4.52)
	id 1GIKEQ-0003G3-Lp	for submissions@weberp.org; Wed, 30 Aug 2006 00:11:27 -0700
Received: from brpdpo233 ([::ffff:192.168.1.2]) by mail with esmtp; Wed,
 30 Aug 2006 03:01:25 -0400
Date: Wed, 30 Aug 2006 10:10:17 -0400
From: Dumitru Popa <dumitru.popa@blackrivercompanies.com>
Subject: BOMs.php
To: submissions@weberp.org
Reply-to: dumitru.popa@blackrivercompanies.com
Message-id: <20060830071140.8FE1BB73F2E@smtp-1.paradise.net.nz>
Organization: BRC-RO
MIME-version: 1.0
X-MIMEOLE: Produced By Microsoft MimeOLE V6.00.2900.2962
X-Mailer: Microsoft Office Outlook, Build 11.0.5510
Content-type: multipart/mixed;
  boundary="=_brp_gate-3426-1156921291-0001-2"
Thread-index: AcbMPgUrx2CZv35LSL6SBAuJyc20LQ==
X-BitDefender-SpamStamp: 1.1.4 049000040111ABAEAAAAgAAAAAAAAABAAAAAAAAAAAAAQ
X-BitDefender-Scanner: Clean, Agent: BitDefender Courier MTA Agent 1.6.2 on
 ns.blackrivercompanies.com
X-BitDefender-Spam: No (0)
X-AntiAbuse: This header was added to track abuse,
 please include it with any abuse report
X-AntiAbuse: Primary Hostname - server.mh-zelus.com
X-AntiAbuse: Original Domain - weberp.org
X-AntiAbuse: Originator/Caller UID/GID - [47 12] / [47 12]
X-AntiAbuse: Sender Address Domain - blackrivercompanies.com
X-Source:
X-Source-Args:
X-Source-Dir:
Status: R
X-Status: NT
X-KMail-EncryptionState:  
X-KMail-SignatureState:  
X-KMail-MDN-Sent:  

This is a MIME-formatted message.  If you see this text it means that your
E-mail software does not support MIME-formatted messages.

--=_brp_gate-3426-1156921291-0001-2
Content-Type: multipart/alternative; boundary="=_brp_gate-3426-1156921291-0001-3"

This is a MIME-formatted message.  If you see this text it means that your
E-mail software does not support MIME-formatted messages.

--=_brp_gate-3426-1156921291-0001-3
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit

Hi, Phil

 

I modify:

 

                        $sql = "SELECT bom.component,

                                                stockmaster.description,

                                                locations.locationname,

                                                workcentres.description,

                                                quantity,

                                                effectiveafter,

                                                effectiveto

                                    FROM bom,

                                                stockmaster,

                                                locations,

                                                workcentres

                                    WHERE bom.component='$Component'

                                    AND bom.parent='$Parent'

                                    AND bom.loccode = locations.loccode

                                    AND bom.workcentreadded=workcentres.code

                                    AND stockmaster.stockid=bom.component";

 

I miss bold line from above in TreeBOMs.php version.

 

Best regards !

 

Dumitru Popa, IT

Phone: +40-368-401-183

 <mailto:dumitru.popa@blackrivercompanies.com>
dumitru.popa@blackrivercompanies.com

--------------------------------------------------------------------

Black River Companies (BRC) is the marketing arm for the following
affiliated companies:

* BRP Acquisition Group, Inc. d/b/a Black River Plastics (BRP) 

* Black River Automotive, L.L.C. (BRA) 

* Black River Services, Inc. (BRS) 

* Black River Companies RO, SA (BRC-R0) 

* Black River Chimplast, S.R.L. (BRChP) 

 


--=_brp_gate-3426-1156921291-0001-3
Content-Type: text/html; charset=us-ascii
Content-Transfer-Encoding: quoted-printable

<html xmlns:o=3D"urn:schemas-microsoft-com:office:office" =
xmlns:w=3D"urn:schemas-microsoft-com:office:word" =
xmlns:st1=3D"urn:schemas-microsoft-com:office:smarttags" =
xmlns=3D"http://www.w3.org/TR/REC-html40">

<head>
<META HTTP-EQUIV=3D"Content-Type" CONTENT=3D"text/html; =
charset=3Dus-ascii">
<meta name=3DGenerator content=3D"Microsoft Word 11 (filtered medium)">
<o:SmartTagType =
namespaceuri=3D"urn:schemas-microsoft-com:office:smarttags"
 name=3D"place"/>
<!--[if !mso]>
<style>
st1\:*{behavior:url(#default#ieooui) }
</style>
<![endif]-->
<style>
<!--
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin:0cm;
	margin-bottom:.0001pt;
	font-size:12.0pt;
	font-family:"Times New Roman";}
a:link, span.MsoHyperlink
	{color:blue;
	text-decoration:underline;}
a:visited, span.MsoHyperlinkFollowed
	{color:purple;
	text-decoration:underline;}
span.EmailStyle17
	{mso-style-type:personal-compose;
	font-family:Arial;
	color:windowtext;}
@page Section1
	{size:21.0cm 842.0pt;
	margin:2.0cm 2.0cm 2.0cm 2.0cm;}
div.Section1
	{page:Section1;}
-->
</style>

</head>

<body lang=3DEN-US link=3Dblue vlink=3Dpurple>

<div class=3DSection1>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>Hi, Phil<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'><o:p>&nbsp;</o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>I modify:<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'><o:p>&nbsp;</o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp; $sql =3D &quot;SELECT =
bom.component,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; stockmaster.description,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; locations.locationname,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; workcentres.description,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; quantity,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; effectiveafter,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; effectiveto<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; FROM bom,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; stockmaster,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; locations,<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nb=
sp;&nbsp; workcentres<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; WHERE
bom.component=3D'$Component'<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; <b><span
style=3D'font-weight:bold'>AND =
bom.parent=3D'$Parent'<o:p></o:p></span></b></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; AND bom.loccode =3D
locations.loccode<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; AND
bom.workcentreadded=3Dworkcentres.code<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&=
nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp; AND
stockmaster.stockid=3Dbom.component&quot;;<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'><o:p>&nbsp;</o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>I miss bold line from above in TreeBOMs.php =
version.<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'><o:p>&nbsp;</o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'>Best regards !<o:p></o:p></span></font></p>

<p class=3DMsoNormal><font size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;
font-family:Arial'><o:p>&nbsp;</o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>Dumitru Popa, =
IT<o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>Phone: =
+40-368-401-183</span></font><font
size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;font-family:Arial'><o:p></o:p></span></font></p=
>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'><a
href=3D"mailto:dumitru.popa@blackrivercompanies.com"><font =
color=3Dblack><span
style=3D'color:windowtext;text-decoration:none'>dumitru.popa@blackriverco=
mpanies.com</span></font></a><o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>----------------------------=
----------------------------------------<o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>Black River Companies (BRC) =
is the
marketing arm for the following affiliated =
companies:<o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>* BRP Acquisition Group, =
Inc. d/b/a <u1:place u2:st=3D"on"><st1:place
w:st=3D"on">Black River</u1:place></st1:place> Plastics (BRP) =
<o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>* <u1:place =
u2:st=3D"on"><st1:place
w:st=3D"on">Black River</u1:place></st1:place> Automotive, L.L.C. (BRA) =
<o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>* Black River Services, =
Inc. (BRS) <o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>* Black River Companies RO, =
SA
(BRC-R0) <o:p></o:p></span></font></p>

<p class=3DMsoNormal =
style=3D'mso-margin-top-alt:5.0pt;margin-right:0cm;margin-bottom:
5.0pt;margin-left:0cm;line-height:10.0pt'><font size=3D2 =
face=3DArial><span
style=3D'font-size:10.0pt;font-family:Arial'>* <u1:place =
u2:st=3D"on"><st1:place
w:st=3D"on">Black River</u1:place></st1:place> Chimplast, S.R.L. (BRChP) =
</span></font><font
size=3D2 face=3DArial><span =
style=3D'font-size:10.0pt;font-family:Arial'><o:p></o:p></span></font></p=
>

<p class=3DMsoNormal><font size=3D3 face=3D"Times New Roman"><span =
style=3D'font-size:
12.0pt'><o:p>&nbsp;</o:p></span></font></p>

</div>

</body>

</html>

--=_brp_gate-3426-1156921291-0001-3--

--=_brp_gate-3426-1156921291-0001-2
Content-Type: application/octet-stream; name="BOMs.php"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: attachment;
	filename="BOMs.php"

<?php
/* $Revision: 1.15 $ */

$PageSecurity =3D 9;

include('includes/session.inc');

$title =3D _('Multi-Level Bill Of Materials Maintenance');

include('includes/header.inc');

// *** POPAD&T
function display_children($parent, $level, &$arbore) {
	// retrive all children of parent
	$result =3D mysql_query('select parent, component from bom '.
							'where parent=3D"'.$parent.'";');
	if (mysql_num_rows($result) > 0) {
		echo ("<UL>\n");
		// display each child
		while ($row =3D mysql_fetch_array($result)) {
			if (!($parent =3D=3D $row['component'])) {
				// indent and display the title of this child
				$ID1 =3D $row["component"];
				//echo("<LI>\n");
				//echo("<A HREF=3D\""."?Select=3D".$ID1."\">".$ID1."</A>"."  \n");
				$arbore[] =3D $level; 		// Level
				if ($level > 10) { echo "ERROR"; exit; }
				$arbore[] =3D $parent;		// Assemble
				$arbore[] =3D $row['component'];	// Component
				// call this function again to display this
				// child's children
				display_children($row['component'], $level + 1, $arbore);
			}
		}
		//echo ("</UL>\n");
	}
}=20

function conversie($arbore, &$matrice){
	$j =3D 0;
	//
	for($i =3D 0; $i < count($arbore); $i =3D $i + 3){
		$matrice[$j][0] =3D $arbore[$i];
		$matrice[$j][1] =3D $arbore[$i+1];
		$matrice[$j][2] =3D $arbore[$i+2];
		$j++;
	}
	$maxj =3D $j;
}
// *** end POPAD&T

function CheckForRecursiveBOM ($UltimateParent, $ComponentToCheck, $db) =
{

/* returns true ie 1 if the BOM contains the parent part as a component
ie the BOM is recursive otherwise false ie 0 */

	$sql =3D "SELECT component FROM bom WHERE =
parent=3D'$ComponentToCheck'";
	$ErrMsg =3D _('An error occurred in retrieving the components of the =
BOM during the check for recursion');
	$DbgMsg =3D _('The SQL that was used to retrieve the components of the =
BOM and that failed in the process was');
	$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if ($result!=3D0) {
		while ($myrow=3DDB_fetch_row($result)){
			if ($myrow[0]=3D=3D$UltimateParent){
				return 1;
			}
			if (CheckForRecursiveBOM($UltimateParent, $myrow[0],$db)){
				return 1;
			}
		} //(while loop)
	} //end if $result is true

	return 0;

} //end of function CheckForRecursiveBOM

function DisplayBOMItems($Parent,$Component,$Level,$db) {
		// Modified by POPAD&T
		$sql =3D "SELECT bom.component,
				stockmaster.description,
				locations.locationname,
				workcentres.description,
				quantity,
				effectiveafter,
				effectiveto
			FROM bom,
				stockmaster,
				locations,
				workcentres
			WHERE bom.component=3D'$Component'
			AND bom.parent=3D'$Parent'
			AND bom.loccode =3D locations.loccode
			AND bom.workcentreadded=3Dworkcentres.code
			AND stockmaster.stockid=3Dbom.component";

		$ErrMsg =3D _('Could not retrieve the BOM components because');
		$DbgMsg =3D _('The SQL used to retrieve the components was');
		$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);

		//echo $TableHeader;
		$RowCounter =3D0;
		while ($myrow=3DDB_fetch_row($result)) {
			if ($k=3D=3D1){
				echo "<tr bgcolor=3D'#CCCCCC'>";
				$k=3D0;
			} else {
				echo "<tr bgcolor=3D'#EEEEEE'>";
				$k++;
			}
		=09
			$Level1 =3D str_repeat('.',$Level-1).$Level;

			printf("<td>%s</td>
				<td>%s</td>
			    <td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=3D\"%s&Select=3D%s&SelectedComponent=3D%s\">" . =
_('Edit') . "</a></td>
				 <td><a =
href=3D\"%s&Select=3D%s&SelectedComponent=3D%s&delete=3D1\">" . =
_('Delete') . "</a></td>
				 </tr>",
				$Level1,
				$myrow[0],
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				ConvertSQLDate($myrow[5]),
				ConvertSQLDate($myrow[6]),
				$_SERVER['PHP_SELF'] . '?' . SID,
				$Parent,
				$myrow[0],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$Parent,
				$myrow[0]);

			$RowCounter++;
			if ($RowCounter=3D=3D20){
				echo $TableHeader;
				$RowCounter=3D0;
			}
		} //END WHILE LIST LOOP
} //end of function DisplayBOMItems

//-----------------------------------------------------------------------=
----------

/* SelectedParent could come from a post or a get */
if (isset($_GET['SelectedParent'])){
	$SelectedParent =3D $_GET['SelectedParent'];
}else if (isset($_POST['SelectedParent'])){
	$SelectedParent =3D $_POST['SelectedParent'];
}
/* SelectedComponent could also come from a post or a get */
if (isset($_GET['SelectedComponent'])){
	$SelectedComponent =3D $_GET['SelectedComponent'];
} elseif (isset($_POST['SelectedComponent'])){
	$SelectedComponent =3D $_POST['SelectedComponent'];
}

if (isset($_GET['Select'])){
	$Select =3D $_GET['Select'];
} elseif (isset($_POST['Select'])){
	$Select =3D $_POST['Select'];
}


$msg=3D'';

if (isset($Select)) { //Parent Stock Item selected so display BOM or =
edit Component

	$SelectedParent =3D $Select;
	$Select =3D NULL;


	$sql =3D "SELECT stockmaster.description,
			stockmaster.mbflag
		FROM stockmaster=20
		WHERE stockmaster.stockid=3D'" . $SelectedParent . "'";

	$ErrMsg =3D _('Could not retrieve the description of the parent part =
because');
	$DbgMsg =3D _('The SQL used to retrieve description of the parent part =
was');
	$result=3DDB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow=3DDB_fetch_row($result);
=09
	$ParentMBflag =3D $myrow[1];
=09
	switch ($ParentMBflag){
		case 'A':=20
			$MBdesc =3D _('Assembly');=20
			break;
		case 'B':=20
			$MBdesc =3D _('Purchased');=20
			break;
		case 'M':=20
			$MBdesc =3D _('Manufactured');=20
			break;
		case 'K':=20
			$MBdesc =3D _('Kit Set');=20
			break;
	}
=09
	echo "<BR><FONT COLOR=3DBLUE SIZE=3D3><B>$SelectedParent - " . =
$myrow[0] . ' ('. $MBdesc. ') </FONT></B>';
=09
	echo '<BR><A HREF=3D' . $_SERVER['PHP_SELF'] . '?' . SID . '>' . =
_('Select a Different BOM') . '</A></CENTER>';

	if (isset($SelectedParent)) {
		echo "<Center><a href=3D'" . $_SERVER['PHP_SELF'] . '?' . SID . =
"Select=3D$SelectedParent'>" . _('Review Components') . '</a></Center>';
	}

	If (isset($SelectedParent) AND isset($_POST['Submit'])) {

		//editing a component need to do some validation of inputs

		if (!Is_Date($_POST['EffectiveAfter'])) {
			$InputError =3D 1;
			prnMsg(_('The effective after date field must be a date in the format =
dd/mm/yy or dd/mm/yyyy or ddmmyy or ddmmyyyy or dd-mm-yy or =
dd-mm-yyyy'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif (!Is_Date($_POST['EffectiveTo'])) {
			$InputError =3D 1;
			prnMsg(_('The effective to date field must be a date in the format =
dd/mm/yy or dd/mm/yyyy or ddmmyy or ddmmyyyy or dd-mm-yy or =
dd-mm-yyyy'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif (!is_double((double) $_POST['Quantity'])) {
			$InputError =3D 1;
			prnMsg(_('The quantity entered must be numeric'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif(!Date1GreaterThanDate2($_POST['EffectiveTo'], =
$_POST['EffectiveAfter'])){
			$InputError =3D 1;
			prnMsg(_('The effective to date must be a date after the effective =
after date') . '<BR>' . _('The effective to date is') . ' ' . =
DateDiff($_POST['EffectiveTo'], $_POST['EffectiveAfter'], 'd') . ' ' . =
_('days before the effective after date') . '! ' . _('No updates have =
been performed') . '.<BR>' . _('Effective after was') . ': ' . =
$_POST['EffectiveAfter'] . ' ' . _('and effective to was') . ': ' . =
$_POST['EffectiveTo'],'error');
			includes('includes/footer.inc');
			exit;
		}

		$EffectiveAfterSQL =3D FormatDateForSQL($_POST['EffectiveAfter']);
		$EffectiveToSQL =3D FormatDateForSQL($_POST['EffectiveTo']);

		if (isset($SelectedParent) AND isset($SelectedComponent) AND =
$InputError !=3D 1) {


			$sql =3D "UPDATE bom SET workcentreadded=3D'" . =
$_POST['WorkCentreAdded'] . "',
						loccode=3D'" . $_POST['LocCode'] . "',
						effectiveafter=3D'" . $EffectiveAfterSQL . "',
						effectiveto=3D'" . $EffectiveToSQL . "',
						quantity=3D " . $_POST['Quantity'] . "
					WHERE bom.parent=3D'" . $SelectedParent . "'
					AND bom.component=3D'" . $SelectedComponent . "'";

			$ErrMsg =3D  _('Could not update this BOM component because');
			$DbgMsg =3D  _('The SQL used to update the component was');

			$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$msg =3D _('Details for') . ' - ' . $SelectedComponent . ' ' . =
_('have been updated') . '.';

		} elseIf ($InputError !=3D1 AND ! isset($SelectedComponent) AND =
isset($SelectedParent)) {

		/*Selected component is null cos no item selected on first time round =
so must be				adding a record must be Submitting new entries in the new =
component form */

		//need to check not recursive BOM component of itself!

			If (!CheckForRecursiveBOM ($SelectedParent, $_POST['Component'], =
$db)) {

				/*Now check to see that the component is not already on the BOM */
				$sql =3D "SELECT component
						FROM bom
					WHERE parent=3D'$SelectedParent'
					AND component=3D'" . $_POST['Component'] . "'
					AND workcentreadded=3D'" . $_POST['WorkCentreAdded'] . "'
					AND loccode=3D'" . $_POST['LocCode'] . "'" ;

				$ErrMsg =3D  _('An error occurred in checking the component is not =
already on the BOM');
				$DbgMsg =3D  _('The SQL that was used to check the component was not =
already on the BOM and that failed in the process was');

				$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if (DB_num_rows($result)=3D=3D0) {

					$sql =3D "INSERT INTO bom (parent,
								component,
								workcentreadded,
								loccode,
								quantity,
								effectiveafter,
								effectiveto)
							VALUES ('$SelectedParent',
								'" . $_POST['Component'] . "',
								'" . $_POST['WorkCentreAdded'] . "',
								'" . $_POST['LocCode'] . "',
								" . $_POST['Quantity'] . ",
								'" . $EffectiveAfterSQL . "',
								'" . $EffectiveToSQL . "')";

					$ErrMsg =3D _('Could not insert the BOM component because');
					$DbgMsg =3D _('The SQL used to insert the component was');

					$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);

					$msg =3D _('A new component part') . ' ' . $_POST['Component'] . ' =
' . _('has been added to the bill of material for part') . ' - ' . =
$SelectedParent . '.';


				} else {

				/*The component must already be on the BOM */

					prnMsg( _('The component') . ' ' . $_POST['Component'] . ' ' . =
_('is already recorded as a component of') . ' ' . $SelectedParent . '.' =
. '<BR>' . _('Whilst the quantity of the component required can be =
modified it is inappropriate for a component to appear more than once in =
a bill of material'),'error');
				}


			} //end of if its not a recursive BOM

		} //end of if no input errors

		prnMsg($msg,'success');

	} elseif (isset($_GET['delete']) AND isset($SelectedComponent) AND =
isset($SelectedParent)) {

	//the link to delete a selected record was clicked instead of the =
Submit button

		$sql=3D"DELETE FROM bom WHERE parent=3D'$SelectedParent' AND =
component=3D'$SelectedComponent'";

		$ErrMsg =3D _('Could not delete this BOM components because');
		$DbgMsg =3D _('The SQL used to delete the BOM was');
		$result =3D DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('The component part') . ' - ' . $SelectedComponent . ' - ' . =
_('has been deleted from this BOM'),'success');

	} elseif (isset($SelectedParent) AND !isset($SelectedComponent) AND ! =
isset($_POST['submit'])) {

	/* It could still be the second time the page has been run and a record =
has been selected	for modification - SelectedParent will exist because =
it was sent with the new call. If		its the first time the page has been =
displayed with no parameters then none of the above		are true and the =
list of components will be displayed with links to delete or edit each.		=
These will call the same page again and allow update/input or deletion =
of the records*/
		//DisplayBOMItems($SelectedParent, $db);

	} //BOM editing/insertion ifs

	//DisplayBOMItems($SelectedParent, $db);

?>

	<CENTER><table border=3D1>

<?php // *** POPAD&T
=09
	$arbore[] =3D 1;					// Level
	$arbore[] =3D $SelectedParent;	// Ansemble
	$arbore[] =3D $SelectedParent;	// Component

	display_children($SelectedParent, 1, $arbore);
	conversie($arbore, $matrice);

	$TableHeader =3D  '<tr BGCOLOR =3D#800000>
			<td class=3Dtableheader>' . _('Level') . '</td>
			<td class=3Dtableheader>' . _('Code') . '</td>
			<td class=3Dtableheader>' . _('Description') . '</td>
			<td class=3Dtableheader>' . _('Location') . '</td>
			<td class=3Dtableheader>' . _('Work Centre') . '</td>
			<td class=3Dtableheader>' . _('Quantity') . '</td>
			<td class=3Dtableheader>' . _('Effective After') . '</td>
			<td class=3Dtableheader>' . _('Effective To') . '</td>
			</tr>';
	echo $TableHeader;

	foreach($matrice as $elem){
		$Level =3D $elem[0];
		$Parent =3D $elem[1];
		$Component =3D $elem[2];
		DisplayBOMItems($Parent,$Component,$Level,$db);
	}
	=09
	// *** end POPAD&T
?>
	</table></CENTER>

	<?php

	if (! isset($_GET['delete'])) {

		echo '<FORM METHOD=3D"post" action=3D"' . $_SERVER['PHP_SELF'] . '?' . =
SID . '&Select=3D' . $SelectedParent .'">';

		if (isset($SelectedComponent)) {
		//editing a selected component from the link to the line item

			$sql =3D "SELECT loccode,
					effectiveafter,
					effectiveto,
					workcentreadded,
					quantity
				FROM bom
				WHERE parent=3D'$SelectedParent'
				AND component=3D'$SelectedComponent'";

			$result =3D DB_query($sql, $db);
			$myrow =3D DB_fetch_array($result);

			$_POST['LocCode'] =3D $myrow['loccode'];
			$_POST['EffectiveAfter'] =3D =
ConvertSQLDate($myrow['effectiveafter']);
			$_POST['EffectiveTo'] =3D ConvertSQLDate($myrow['effectiveto']);
			$_POST['WorkCentreAdded']  =3D $myrow['workcentreadded'];
			$_POST['Quantity'] =3D $myrow['quantity'];

			prnMsg(_('Edit the details of the selected component in the fields =
below') . '. <BR>' . _('Click on the Enter Information button to update =
the component details'),'info');
			echo "<INPUT TYPE=3DHIDDEN NAME=3D'SelectedParent' =
VALUE=3D'$SelectedParent'>";
			echo "<INPUT TYPE=3DHIDDEN NAME=3D'SelectedComponent' =
VALUE=3D'$SelectedComponent'>";
			echo '<CENTER><TABLE><TR><TD>' . _('Component') . ':</TD><TD><B>' . =
$SelectedComponent . '</B></TD></TR>';

		} else { //end of if $SelectedComponent

			echo "<INPUT TYPE=3DHIDDEN NAME=3D'SelectedParent' =
VALUE=3D'$SelectedParent'>";
			/* echo "Enter the details of a new component in the fields below. =
<BR>Click on 'Enter Information' to add the new component, once all =
fields are completed.";
			*/
			echo '<CENTER><TABLE><TR><TD>' . _('Component code') . ':</TD><TD>';
			echo "<SELECT name=3D'Component'>";

		=09
			if ($ParentMBflag=3D=3D'A'){ /*Its an assembly */
				$sql =3D "SELECT stockmaster.stockid,=20
						stockmaster.description=20
					FROM stockmaster=20
					WHERE stockmaster.mbflag !=3D'D'=20
					AND stockmaster.mbflag !=3D'K'=20
					AND stockmaster.mbflag !=3D'A'=20
					AND stockmaster.controlled =3D 0=20
					AND stockmaster.stockid !=3D '$SelectedParent'=20
					ORDER BY stockmaster.stockid";
		=09
			} else { /*Its either a normal manufac item or a kitset - controlled =
items ok */
				$sql =3D "SELECT stockmaster.stockid,=20
						stockmaster.description=20
					FROM stockmaster=20
					WHERE stockmaster.mbflag !=3D'D'=20
					AND stockmaster.mbflag !=3D'K'=20
					AND stockmaster.mbflag !=3D'A'=20
					AND stockmaster.stockid !=3D '$SelectedParent'=20
					ORDER BY stockmaster.stockid";
			}
				=09
			$ErrMsg =3D _('Could not retrieve the list of potential components =
because');
			$DbgMsg =3D _('The SQL used to retrieve the list of potential =
components part was');
			$result =3D DB_query($sql,$db,$ErrMsg, $DbgMsg);


			while ($myrow =3D DB_fetch_array($result)) {
				echo "<OPTION VALUE=3D".$myrow["stockid"].'>' . =
str_pad($myrow['stockid'],21, '_', STR_PAD_RIGHT) . =
$myrow['description'];
			} //end while loop

			echo '</SELECT></TD></TR>';
		}
		?>

		<TR><TD><?php echo _('Location') . ':'; ?></TD>
		<TD>
		<SELECT name=3D"LocCode">

		<?php

		DB_free_result($result);
		$sql =3D 'SELECT locationname, loccode FROM locations';
		$result =3D DB_query($sql,$db);

		while ($myrow =3D DB_fetch_array($result)) {
			if ($myrow['loccode']=3D=3D$_POST['LocCode']) {
				echo "<OPTION SELECTED VALUE=3D'";
			} else {
				echo "<OPTION VALUE=3D'";
			}
			echo $myrow['loccode'] . "'>" . $myrow['locationname'];

		} //end while loop

		DB_free_result($result);

		?>
		</SELECT>
		</TD></TR>

		<TR><TD><?php echo _('Work Centre Added') . ':'; ?></TD>
		<TD>
		<SELECT name=3D"WorkCentreAdded">

		<?php

		$sql =3D 'SELECT code, description FROM workcentres';
		$result =3D DB_query($sql,$db);

		if (DB_num_rows($result)=3D=3D0){
			prnMsg( _('There are no work centres set up yet') . '. ' . _('Please =
use the link below to set up work centres'),'warn');
			echo "<BR><A HREF=3D'$rootpath/WorkCentres.php?" . SID . "'>" . =
_('Work Centre Maintenance') . '</A>';
			includes('includes/footer.inc');
			exit;
		}

		while ($myrow =3D DB_fetch_array($result)) {
			if ($myrow['code']=3D=3D$_POST['WorkCentreAdded']) {
				echo "<OPTION SELECTED VALUE=3D'";
			} else {
				echo "<OPTION VALUE=3D'";
			}
			echo $myrow['code'] . "'>" . $myrow['description'];
		} //end while loop

		DB_free_result($result);
		?>

		</SELECT>
		</TD></TR>

		<TR><TD><?php echo _('Quantity') . ':'; ?></TD>
		<TD>
		<INPUT TYPE=3D"Text" name=3D"Quantity" SIZE=3D10 MAXLENGTH=3D8 =
VALUE=3D
		<?php
		if ($_POST['Quantity']){
			echo $_POST['Quantity'];
		} else {
			echo 1;
		}?>>

		</TD></TR>

		<?php
		if (!isset($_POST['EffectiveTo']) OR $_POST['EffectiveTo']=3D=3D'') {
			$_POST['EffectiveTo'] =3D =
Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d'),(Dat=
e('y')+30)));
		}
		if (!isset($_POST['EffectiveAfter']) OR =
$_POST['EffectiveAfter']=3D=3D'') {
			$_POST['EffectiveAfter'] =3D =
Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')-1,Da=
te('y')));
		}

		?>

		<TR><TD><?php echo _('Effective After') . ' (' . =
$_SESSION['DefaultDateFormat'] . '):'; ?></TD>
		<TD>
		<INPUT TYPE=3D"Text" name=3D"EffectiveAfter" SIZE=3D11 MAXLENGTH=3D11 =
VALUE=3D"<?php echo $_POST['EffectiveAfter']; ?>">
		</TD></TR>
		<TR><TD><?php echo _('Effective To') . ' (' . =
$_SESSION['DefaultDateFormat'] . '):'; ?></TD>
		<TD>
		<INPUT TYPE=3D"Text" name=3D"EffectiveTo" SIZE=3D11 MAXLENGTH=3D11 =
VALUE=3D"<?php echo $_POST['EffectiveTo']; ?>">
		</TD></TR>


		</TABLE>

		<CENTER><input type=3D"Submit" name=3D"Submit" value=3D"<?php echo =
_('Enter Information'); ?>">

		</FORM>

		<?php
	} //end if record deleted no point displaying form to add record

	// end of BOM maintenance code - look at the parent selection form if =
not relevant
// =
-------------------------------------------------------------------------=
---------

} elseif (isset($_POST['Search'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=3D_('Stock description keywords have been used in preference to =
the Stock code extract entered');
	}
	If ($_POST['Keywords']=3D=3D'' AND $_POST['StockCode']=3D=3D'') {
		$msg=3D_('At least one stock description keyword or an extract of a =
stock code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=3D0;
			$SearchString =3D '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=3Dstrpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=3D$SearchString . =
substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=3Dstrpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString =3D $SearchString. substr($_POST['Keywords'],$i).'%';


			$sql =3D "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					SUM(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid =3D locstock.stockid
				AND stockmaster.description " . LIKE . " '$SearchString'
				AND (stockmaster.mbflag=3D'M' OR stockmaster.mbflag=3D'K' OR =
stockmaster.mbflag=3D'A')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		} elseif (strlen($_POST['StockCode'])>0){
			$sql =3D "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					sum(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid =3D locstock.stockid
				AND stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'
				AND (stockmaster.mbflag=3D'M'
					OR stockmaster.mbflag=3D'K'
					OR stockmaster.mbflag=3D'A')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		}

		$ErrMsg =3D _('The SQL to find the parts selected failed with the =
message');
		$result =3D DB_query($sql,$db,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
} //end of if search

if (!isset($SelectedParent)) {
?>

<FORM ACTION=3D'<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>' =
METHOD=3DPOST>
<B><BR><?php echo $msg; ?></B>
<?php echo _('Select a manufactured part') . ' (' . _('or Assembly or =
Kit part') . ') ' . _('to maintain the bill of material for using the =
options below') . '.'; ?>
<BR><FONT SIZE=3D1><?php echo _('Parts must be defined in the stock item =
entry') . '/' . _('modification screen as manufactured') . ', ' . =
_('kits or assemblies to be available for construction of a bill of =
material'); ?></FONT>
<TABLE CELLPADDING=3D3 COLSPAN=3D4>
<TR>
<TD><FONT SIZE=3D1><?php echo _('Enter text extracts in the') . ' <B>' . =
_('description'); ?></B>:</FONT></TD>
<TD><INPUT TYPE=3D"Text" NAME=3D"Keywords" SIZE=3D20 =
MAXLENGTH=3D25></TD>
<TD><FONT SIZE=3D3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><FONT SIZE=3D1><?php echo _('Enter extract of the') . ' <B>' . =
_('Stock Code'); ?></B>:</FONT></TD>
<TD><INPUT TYPE=3D"Text" NAME=3D"StockCode" SIZE=3D15 =
MAXLENGTH=3D18></TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=3DSUBMIT NAME=3D"Search" VALUE=3D"<?php echo =
_('Search Now'); ?>">
</CENTER>


<?php

If (isset($result) AND !isset($SelectedParent)) {

	echo '<TABLE CELLPADDING=3D2 COLSPAN=3D7 BORDER=3D1>';
	$TableHeader =3D '<TR><TD class=3Dtableheader>' . _('Code') . '</TD>
				<TD class=3Dtableheader>' . _('Description') . '</TD>
				<TD class=3Dtableheader>' . _('On Hand') . '</TD>
				<TD class=3Dtableheader>' . _('Units') . '</TD>
			</TR>';

	echo $TableHeader;

	$j =3D 1;
	$k=3D0; //row colour counter
	while ($myrow=3DDB_fetch_array($result)) {
		if ($k=3D=3D1){
			echo "<tr bgcolor=3D'#CCCCCC'>";
			$k=3D0;
		} else {
			echo "<tr bgcolor=3D'#EEEEEE'>";
			$k++;
		}
		if ($myrow['mbflag']=3D=3D'A' OR $myrow['mbflag']=3D=3D'K'){
			$StockOnHand =3D 'N/A';
		} else {
			$StockOnHand =3D number_format($myrow['totalonhand'],2);
		}
		printf("<td><INPUT TYPE=3DSUBMIT NAME=3D'Select' VALUE=3D'%s'</td>
		        <td>%s</td>
			<td ALIGN=3DRIGHT>%s</td>
			<td>%s</td></tr>",
			$myrow['stockid'],
			$myrow['description'],
			$StockOnHand,
			$myrow['units']
		);

		$j++;
		If ($j =3D=3D 12){
			$j=3D1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
//end if results to show

?>

</FORM>

<?php } //end StockID already selected

include('includes/footer.inc');
?>

--=_brp_gate-3426-1156921291-0001-2
Content-Type: text/plain; name="BitDefender.txt"; charset=iso-8859-1
Content-Transfer-Encoding: 7bit
Content-Disposition: inline; filename="BitDefender.txt"


--  mail.blackrivercompanies.com --
This message was scanned for spam and viruses by BitDefender.
For more information please visit http://linux.bitdefender.com/

--=_brp_gate-3426-1156921291-0001-2--



