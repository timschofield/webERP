<script type="text/javascript">
<!--
var fActiveMenu = false;
var oOverMenu = false;

function mouseSelect(e) {
	if (fActiveMenu) {
		if (oOverMenu == false) {
			oOverMenu = false;
			document.getElementById(fActiveMenu).style.display = "none";
			fActiveMenu = false;
			return false;
		}
		return false;
	}
	return true;
}

function menuActivate(idEdit, idMenu, idSel) {
	if (fActiveMenu) return mouseSelect(0);

	oMenu = document.getElementById(idMenu);
	oEdit = document.getElementById(idEdit);
	nTop = oEdit.offsetTop + oEdit.offsetHeight;
	nLeft = oEdit.offsetLeft;
	while (oEdit.offsetParent != document.body) {
		oEdit = oEdit.offsetParent;
		nTop += oEdit.offsetTop;
		nLeft += oEdit.offsetLeft;
	}
	oMenu.style.left = nLeft;
	oMenu.style.top = nTop;
	oMenu.style.display = "";
	fActiveMenu = idMenu;
	document.getElementById(idSel).value = document.getElementById(idEdit).value;
	document.getElementById(idSel).focus();
	return false;
}

function textSet(idEdit, text) {
	document.getElementById(idEdit).value = text;
	oOverMenu = false;
	mouseSelect(0);
	document.getElementById(idEdit).focus();
}

function comboKey(idEdit, idSel) {
	if (window.event.keyCode == 13 || window.event.keyCode == 32)
		textSet(idEdit,idSel.value);
	else if (window.event.keyCode == 27) {
		mouseSelect(0);
		document.getElementById(idEdit).focus();
	}
}

//pre-load images
if (document.images) {
 img_on =new Image();  img_on.src ="../images/pull_down_active.gif";
 img_off=new Image();  img_off.src="../images/pull_down_inactive.gif";
}

function handleOver() {
 if (document.images) document.imgName.src=img_on.src;
}

function handleOut() {
 if (document.images) document.imgName.src=img_off.src;
}

document.onmousedown = mouseSelect;
//--></script>

<h2 align="center"><?php echo 'Form Field: '.$DisplayName.' - '.RPT_BTN_PROP; ?></h2>
<form name="DBRptLine" method="post" action="ReportCreator.php?action=step6a">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="DisplayName" type="hidden" value="<?php echo $DisplayName; ?>">
	<input name="index" type="hidden" value="<?php echo $Params['index']; ?>">
	<input name="ID" type="hidden" value="<?php echo $FormParams['id']; ?>">
	<input name="SeqNum" type="hidden" value="<?php echo $SeqNum; ?>">
	<input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
	<input name="ReportName" type="hidden" value="<?php echo $reportname; ?>">
  <table align="center" width="550"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_CANCEL; ?>"></td>
      <td><div align="center"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_UPDATE; ?>"></div></td>
      <td><div align="right"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_FINISH; ?>"></div></td>
    </tr>
  </table>
  <table align="center" border="2" cellspacing="1" cellpadding="1">
    <tr bgcolor="#CCCCCC">
      <td colspan="2"><div align="center"><?php echo RPT_STARTPOS; ?></div></td>
      <td colspan="2"><div align="center"><?php echo RPT_BOXDIM; ?></div></td>
    </tr>
    <tr>
      <td><div align="center"><?php echo RPT_ABSCISSA; ?>
	  	<?php if (!$Params['LineXStrt']) $Params['LineXStrt']='10'; ?>
		<input name="LineXStrt" type="text" value="<?php echo $Params['LineXStrt']; ?>" size="4" maxlength="3">
      </div></td>
      <td><div align="center"><?php echo RPT_ODINATE; ?>
	  	<?php if (!$Params['LineYStrt']) $Params['LineYStrt']='10'; ?>
        <input name="LineYStrt" type="text" value="<?php echo $Params['LineYStrt']; ?>" size="4" maxlength="3">
      </div></td>
      <td><div align="center"><?php echo RPT_WIDTH; ?>
	  	<?php if (!$Params['BoxWidth']) $Params['BoxWidth']='60'; ?>
          <input name="BoxWidth" type="text" value="<?php echo $Params['BoxWidth']; ?>" size="4" maxlength="3">
      </div></td>
      <td><div align="center"><?php echo RPT_HEIGHT; ?>
	  	<?php if (!$Params['BoxHeight']) $Params['BoxHeight']='50'; ?>
          <input name="BoxHeight" type="text" value="<?php echo $Params['BoxHeight']; ?>" size="4" maxlength="3">
      </div></td>
    </tr>
    <tr bordercolor="#FFFFFF" bgcolor="#CCCCCC">
      <td colspan="2"><div align="center"><?php echo RPT_BRDRLINE; ?></div></td>
      <td colspan="2"><div align="center"><?php echo RPT_FILL; ?></div></td>
    </tr>
    <tr>
		<?php if (!$Params['Line'] OR $Params['Line']=='0') $selected=' checked'; else $selected=''; ?>
      <td><input name="Line" type="radio" value="0"<?php echo $selected; ?>><?php echo RPT_NOBRDR; ?> </td>
      <td><?php echo RPT_LINEWIDTH; ?><select name="LineSize">
	  <?php foreach($LineSizes as $key => $value) {
			if ($Params['LineSize']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></td>
		<?php if (!$Params['Fill'] OR $Params['Fill']=='0') $selected=' checked'; else $selected=''; ?>
      <td colspan="2"><input name="Fill" type="radio" value="0"<?php echo $selected; ?>><?php echo RPT_NOFILL; ?></td>
    </tr>
    <tr>
		<?php if ($Params['Line']=='1') $selected=' checked'; else $selected=''; ?>
      <td><input name="Line" type="radio" value="1"<?php echo $selected; ?>><?php echo RPT_STDCOLOR; ?><br />
		<div align="center"><select name="BrdrColor">
	  <?php foreach($FontColors as $key => $value) {
			if ($Params['BrdrColor']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
		<?php if ($Params['Line']=='2') $selected=' checked'; else $selected=''; ?>
      <td><input name="Line" type="radio" value="2"<?php echo $selected; ?>><?php echo RPT_CUSTCOLOR; ?><br />
		<div align="center">
		<?php echo RPT_RED; ?> <input name="BrdrRed" type="text" value="<?php echo $Params['BrdrRed']; ?>" size="4" maxlength="3">
    	<?php echo RPT_GREEN; ?> <input name="BrdrGreen" type="text" value="<?php echo $Params['BrdrGreen']; ?>" size="4" maxlength="3">
    	<?php echo RPT_BLUE; ?> <input name="BrdrBlue" type="text" value="<?php echo $Params['BrdrBlue']; ?>" size="4" maxlength="3">
	  </div></td>
		<?php if ($Params['Fill']=='1') $selected=' checked'; else $selected=''; ?>
      <td><input name="Fill" type="radio" value="1"<?php echo $selected; ?>><?php echo RPT_STDCOLOR; ?><br />
		<div align="center"><select name="FillColor">
	  <?php foreach($FontColors as $key => $value) {
			if ($Params['FillColor']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
		<?php if ($Params['Fill']=='2') $selected=' checked'; else $selected=''; ?>
      <td><input name="Fill" type="radio" value="2"<?php echo $selected; ?>><?php echo RPT_CUSTCOLOR; ?><br />
		<div align="center">
		<?php echo RPT_RED; ?> <input name="FillRed" type="text" value="<?php echo $Params['FillRed']; ?>" size="4" maxlength="3">
    	<?php echo RPT_GREEN; ?> <input name="FillGreen" type="text" value="<?php echo $Params['FillGreen']; ?>" size="4" maxlength="3">
    	<?php echo RPT_BLUE; ?> <input name="FillBlue" type="text" value="<?php echo $Params['FillBlue']; ?>" size="4" maxlength="3">
	  </div></td>
    </tr>
</table>
<table align="center" border="2" cellspacing="1" cellpadding="1">
    <tr bordercolor="#FFFFFF" bgcolor="#CCCCCC">
      <td colspan="8"><div align="center"><?php echo RPT_FIELDS; ?></div></td>
    </tr>
    <tr>
      <td><div align="center"><?php echo RPT_SEQ; ?><br /></div></td>
      <td colspan="2"><div align="center"><?php echo RPT_TBLFNAME; ?></div></td>
      <td colspan="2"><div align="center"><?php echo RPT_DISPNAME; ?></div></td>
      <td colspan="2"><div align="center"><?php echo RPT_TEXTPROC; ?></div></td>
      <td rowspan="4" align="center">
	  	<input name="todo" type="submit" id="todo" value="<?php echo $ButtonValue; ?>"></td>
    </tr>
    <tr>
      <td rowspan="3"><div align="center">
		  <?php if ($ButtonValue=='Change') { ?>
		  <input name="TblSeqNum" type="hidden" value="<?php echo $Params['TblSeqNum']; ?>">
		  <?php echo $Params['TblSeqNum']; } else { ?>
		  <input name="TblSeqNum"  type="text" value="<?php echo $Params['TblSeqNum']; ?>" size="3" maxlength="2">
		  <?php } // end if ?></div></td>
      <td colspan="2">
		<input type="text" name="TblField" id="TblField" value="<?php echo $Params['TblField']; ?>" size="30">
		<img name="imgName" src="../images/pull_down_inactive.gif" height="20" width="19"
		onMouseOver="handleOver(); return true;" onMouseOut="handleOut(); return true;"
		onclick="JavaScript:menuActivate('TblField', 'combodiv', 'combosel')">
		<div id="combodiv" style="position:absolute; display:none; top:0px; left:0px;z-index:10000" onmouseover="javascript:oOverMenu='combodiv';"
		onmouseout="javascript:oOverMenu=false;">
		<select size="10" id="combosel" style="width: 220; border-style: none" onclick="JavaScript:textSet('TblField',this.value);"
		onkeypress="JavaScript:comboKey('TblField', this);">
		<?php echo CreateFieldList($ReportID, $FieldListings['defaults']['fieldname'],''); ?>
		</select></div>
	  </td>
      <td colspan="2"><input type="text" name="TblDesc" value="<?php echo $Params['TblDesc']; ?>"></td>
      <td colspan="2"><select name="Processing">
		<?php foreach($FormProcessing as $key => $value) {
			if ($Params['Processing']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></td>
    </tr>
    <tr>
      <td><div align="center"><?php echo RPT_FONT; ?></div></td>
      <td><div align="center"><?php echo RPT_SIZE; ?></div></td>
      <td><div align="center"><?php echo RPT_ALIGN; ?></div></td>
      <td><div align="center"><?php echo RPT_COLOR; ?></div></td>
      <td><div align="center"><?php echo RPT_WIDTH; ?></div></td>
      <td><div align="center"><?php echo RPT_SHOW; ?></div></td>
    </tr>
    <tr>
      <td><div align="center"><select name="Font">
		<?php foreach($Fonts as $key => $value) {
			if ($Params['Font']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
      <td><div align="center"><select name="FontSize">
		<?php foreach($FontSizes as $key => $value) {
			if ($Params['FontSize']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
      <td><div align="center"><select name="FontAlign">
		<?php foreach($FontAlign as $key => $value) {
			if ($Params['FontAlign']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
      <td><div align="center"><select name="FontColor">
		<?php foreach($FontColors as $key => $value) {
			if ($Params['FontColor']==$key) $selected = ' selected'; else  $selected = '';
			echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		} ?> </select></div></td>
      <td><div align="center">
	  	<?php if ($Params['TblColWidth']=='') $Params['TblColWidth']='20'; ?>
        <input type="text" name="TblColWidth" value="<?php echo $Params['TblColWidth']; ?>" size="3" maxlength="4">
      </div></td>
      <td><div align="center">
	  	<?php if ($Params['TblShow']=='1' OR $Params['TblShow']=='') $checked = ' checked'; else $checked = ''; ?>
        <input type="checkbox" name="TblShow" value="1"<?php echo $checked; ?>>
      </div></td>
    </tr>
    <tr bordercolor="#FFFFFF" bgcolor="#CCCCCC">
      <td colspan="8"><div align="center"><?php echo RPT_FLDLIST; ?></div></td>
    </tr>
	<?php if (is_array($Params['Seq'])) {
		while ($myrow = array_shift($Params['Seq'])) {
			echo'<tr><td align="center">'.$myrow['TblSeqNum'].'</td>';
			echo'<td align="center" colspan="2">'.$myrow['TblField'].'</td>';
			echo'<td align="center" colspan="2">'.$myrow['TblDesc'].'</td>';
			echo'<td align="center" colspan="2">'.$FormProcessing[$myrow['Processing']].'</td>';
			echo'<td><input type="image" name="up'.$myrow['TblSeqNum'].'" value="up" src="../images/upicon.png" border="0">
			<input type="image" name="dn'.$myrow['TblSeqNum'].'" value="dn" src="../images/downicon.png" border="0">
			<input type="image" name="ed'.$myrow['TblSeqNum'].'" value="ed" src="../images/editicon.png" border="0">
			<input type="image" name="rm'.$myrow['TblSeqNum'].'" value="rm" src="../images/delicon.png" border="0" onClick="return confirm(\'Delete this field?\')">
			</td></tr>';
		}
	} else {
		echo '<tr><td align="center" colspan="8">'.RPT_NOROWS.'</td></tr>';
	}?>
  </table>
</form>
