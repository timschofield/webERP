<?php echo '<html lang="' . str_replace('_', '-', substr($Language, 0, 5)) . ">"; ?>
<head></head>
<body>
<h2 align="center"><?php echo $FormParams['heading'].$reportname.' - '.RPT_BTN_FLDSETUP; ?></h2>
<table align="center" width="550" border="0" cellspacing="1" cellpadding="1">
  <form name="RptFieldForm" method="post" action="ReportCreator.php?action=step6">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
	<input name="Type" type="hidden" value="<?php echo $Type; ?>">
	<input name="ReportName" type="hidden" value="<?php echo $reportname; ?>">
    <tr>
      <td><input name="todo" type="submit" value="<?php echo RPT_BTN_BACK; ?>"></td>
      <td align="right"><input name="todo" type="submit" value="<?php echo RPT_BTN_CONT; ?>"></td>
    </tr>
  </form>
</table>
<table align="center"  border="1" cellspacing="1" cellpadding="1">
    <tr bgcolor="#CCCCCC">
      <td colspan="7"><div align="center"><?php echo RPT_ENTRFLD; ?></div></td>
    </tr>
    <tr>
      <td><div align="center"><?php echo RPT_ORDER; ?></div></td>
      <?php if ($Type<>'frm') echo '<td><div align="center">'.RPT_TBLFNAME.'</div></td>'; ?>
      <td><div align="center"><?php echo RPT_DISPNAME; ?></div></td>
      <?php if ($Type<>'frm') echo '<td><div align="center">'.RPT_BREAK.'</div></td>'; ?>
      <td><div align="center"><?php echo RPT_SHOW; ?></div></td>
      <td><div align="center"><?php if ($Type=='frm') echo RPT_TYPE; else echo RPT_TOTAL; ?></div></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
	<form name="RptFieldForm" method="post" action="ReportCreator.php?action=step6">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="ReportID" type="hidden" value="<?php echo $ReportID ?>">
	<input name="Type" type="hidden" value="<?php echo $Type; ?>">
	<input name="ReportName" type="hidden" value="<?php echo $reportname; ?>">
		<td align="center">
	  <?php if ($FieldListings['defaults']['buttonvalue']=='Change') { ?>
	  <input name="SeqNum" type="hidden" value="<?php echo $FieldListings['defaults']['seqnum']; ?>">
	  <?php echo $FieldListings['defaults']['seqnum']; } else { ?>
	  <input name="SeqNum"  type="text" value="<?php echo $FieldListings['defaults']['seqnum']; ?>" size="4" maxlength="3">
	  <?php } // end if ?>
		</td>
	  <?php if ($Type<>'frm') { ?><td>
	    <select name="FieldName"><OPTION Value=""><?php echo RPT_SLCTFIELD; ?>
		<?php echo CreateFieldList($ReportID,$FieldListings['defaults']['fieldname'],''); ?></select>
		</td>
	  <?php } ?>
      <td>
	  <input name="DisplayDesc" type="text" value="<?php echo $FieldListings['defaults']['displaydesc']; ?>" size="20" maxlength="25">
	  </td>
      <?php if ($Type<>'frm') {
		echo '<td align="center">';
	  	if ($FieldListings['defaults']['columnbreak']=='1') $selected = ' checked'; else  $selected = '';
	    echo '<input name="ColumnBreak" type="checkbox" value="1"'.$selected.'></td>'; } ?>
      <td align="center">
	  <?php if ($FieldListings['defaults']['visible']=='1') $selected = ' checked'; else  $selected = ''; ?>
	  <input name="Visible" type="checkbox" value="1"<?php echo $selected ?>></td>
      <td>
		<?php if ($Type=='frm') {
			if ($FieldListings['defaults']['buttonvalue']==RPT_BTN_ADDNEW) {
				echo '<select name="Params">';
				foreach($FormEntries as $key=>$value) {
					if ($FieldListings['defaults']['params']==$key) $selected = ' selected'; else  $selected = '';
					echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
				}
				echo '</select>';
			} else {
				$Temp = unserialize($FieldListings['defaults']['params']);
				$EntryIndex = $Temp['index'];
				echo $FormEntries[$EntryIndex];
			}
		} else {
			echo '<select name="Params">';
			foreach($TotalLevels as $key=>$value) {
				if ($FieldListings['defaults']['params']==$key) $selected = ' selected'; else  $selected = '';
				echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
			}
		} ?>
        </td>
      <td align = "center">
	  <input name="todo" type="submit" value="<?php echo $FieldListings['defaults']['buttonvalue']; ?>">
	  </td>
    </form></tr>
    <tr bgcolor="#CCCCCC"><td colspan="7"><div align="center"><?php echo RPT_FLDLIST; ?></div></td></tr>
	<?php if (!$FieldListings['lists']) {
		echo '<tr><td align="center" colspan="7">'.RPT_NOFIELD.'</td></tr>';
	} else {
	foreach ($FieldListings['lists'] as $FieldDetails) { ?>
    <tr><form name="RptFieldForm" method="post" action="ReportCreator.php?action=step6">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
	<input name="Type" type="hidden" value="<?php echo $Type; ?>">
	<input name="ReportName" type="hidden" value="<?php echo $reportname; ?>">
	<input name="SeqNum" type="hidden" value="<?php echo $FieldDetails['seqnum']; ?>">
	<input name="FieldName" type="hidden" value="<?php echo $FieldDetails['fieldname']; ?>">
	<input name="DisplayDesc" type="hidden" value="<?php echo $FieldDetails['displaydesc']; ?>">
	<?php if ($Type<>'frm') echo '<input name="ColumnBreak" type="hidden" value="'.$FieldDetails['columnbreak'].'">'; ?>
	<input name="Visible" type="hidden" value="<?php echo $FieldDetails['visible']; ?>">
	<input name="Params" type="hidden" value="<?php echo $FieldDetails['params']; ?>">
	  <td align = "center"><?php echo $FieldDetails['seqnum']; ?></td>
      <?php if ($Type<>'frm') echo '<td>'.$FieldDetails['fieldname'].'</td>' ?>
      <td><?php echo $FieldDetails['displaydesc']; ?></td>
	  <?php if ($Type<>'frm') {
	  	if ($FieldDetails['columnbreak']=='1') $selected = ' checked'; else $selected = '';
      	echo'<td align="center"><input disabled type="checkbox"'.$selected.'></td>';
	  }
	  if ($FieldDetails['visible']=='1') $selected=' checked'; else $selected=''; ?>
      <td align="center"><input disabled type="checkbox"<?php echo $selected; ?>></td>
      <td>
	  	<?php if ($Type=='frm') {
			$Temp = unserialize($FieldDetails['params']);
			$EntryIndex = $Temp['index'];
			echo $FormEntries[$EntryIndex];
		} else {
			echo $TotalLevels[$FieldDetails['params']];
		} ?></td>
      <td>
		<input type=image name="up" value="up" src="../images/upicon.PNG" border="0">
		<input type=image name="dn" value="down" src="../images/downicon.PNG" border="0">
		<input type=image name="ed" value="edit" src="../images/editicon.png" border="0">
		<input type=image name="rm" value="delete" src="../images/delicon.png" border="0" onClick="return confirm('Delete this field?')">
		<?php if ($Type=='frm') echo '<input name="todo" type="submit" value="'.RPT_BTN_PROP.'">'; ?>
	  </td>
    </form></tr>
<?php } // end foreach
} // end else ?>
</table>
<table align="center" width="550" border="0" cellspacing="1" cellpadding="1">
  <form name="RptFieldForm" method="post" action="ReportCreator.php?action=step6">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
	<input name="Type" type="hidden" value="<?php echo $Type; ?>">
	<input name="ReportName" type="hidden" value="<?php echo $reportname; ?>">
    <tr>
      <td><input name="todo" type="submit" value="<?php echo RPT_BTN_BACK; ?>"></td>
      <td align="right"><input name="todo" type="submit" value="<?php echo RPT_BTN_CONT; ?>"></td>
    </tr>
  </form>
</table>
</body>
</html>
