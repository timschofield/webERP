<?php echo '<html lang="' . str_replace('_', '-', substr($Language, 0, 5)) . ">"; ?>
<head></head>
<body>
<h2 align="center"><?php echo $Prefs['reportname'].' - '.RPT_BTN_CRIT; ?></h2>
<form name="reporthome" method="post" action="ReportMaker.php?action=go">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
  <input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
  <input name="FilterForm" type="hidden" value="1">
  <input name="GoBackURL" type="hidden" value="<?php echo $GoBackURL; ?>">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
      <td width="20%"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_CANCEL; ?>"></td>
	  <?php if (!$Prefs['defaultreport']) { ?>
      <td width="20%"><div align="center"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_PGSETUP; ?>"></div></td>
	  <?php } else echo '<td width="20%">&nbsp;</td>'; ?>
	  <td><div align="center"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_CPYRPT; ?>"></div></td>
      <td width="20%"><div align="center"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_EXPCSV; ?>"></div></td>
	  <td width="20%"><div align="right"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_EXPPDF; ?>"></div></td>
    </tr>
  </table>
  <table width="100%" border="1" cellspacing="1" cellpadding="1">
	<tr>
		<td colspan="4"><div align="center"><?php echo RPT_CRITERIA; ?></div></td>
	</tr>
	<?php if ($Prefs['DateListings']['displaydesc'] <>'') { ?>
	<tr>
	  <td colspan="2">&nbsp;</td>
      <td><div align="center"><?php echo RPT_FROM; ?></div></td>
      <td><div align="center"><?php echo RPT_TO; ?></div></td>
    </tr>
	<tr>
	  <td><?php echo RPT_DATE; ?></td>
	  <td>
		<select name="DefDate">
		<?php $DateArray = explode(':',$Prefs['DateListings']['params']);
			if (!isset($DateArray[1])) $DateArray[1]='';
			if (!isset($DateArray[2])) $DateArray[2]='';
			foreach($DateChoices as $key=>$value) {
			if (mb_strpos($Prefs['DateListings']['displaydesc'],$key)!==false) {
				if ($DateArray[0]==$key) $Selected = ' selected'; else $Selected = '';
				echo '<option value="'.$key.'"'.$Selected.'>'.$value.'</option>';
			}
		} ?>
        </select></td>
	  <td><input name="DefDateFrom" type="text" value="<?php echo $DateArray[1]; ?>" id="DateFrom" size="11" maxlength="10"></td>
	  <td><input name="DefDateTo" type="text" value="<?php echo $DateArray[2]; ?>" id="DateTo" size="11" maxlength="10"></td>
    </tr>
	<?php } //end if ($Prefs['DateListings']['displaydesc'])
    if ($Prefs['GroupListings'] <> '') { ?>
	<tr>
      <td><?php echo RPT_GROUP; ?></td>
      <td>
		<select name="DefGroup">
		   <option value="0"><?php echo RPT_NONE; ?></option>
		<?php foreach($Prefs['GroupListings'] as $LineItem) {
			if ($LineItem['params']=='1') $Selected = ' selected'; else $Selected = '';
			echo '<option value="'.$LineItem['seqnum'].'"'.$Selected.'>'.$LineItem['displaydesc'].'</option>';
		} ?>
        </select></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
	<?php } // end if ($GroupListings)
    if ($Prefs['SortListings'] <> '') { ?>
	<tr>
      <td><?php echo RPT_SORT; ?></td>
      <td>
		<select name="DefSort">
		   <option value="0"><?php echo RPT_NONE; ?></option>
		<?php foreach($Prefs['SortListings'] as $LineItem) {
			if ($LineItem['params']=='1') $Selected = ' selected'; else $Selected = '';
			echo '<option value="'.$LineItem['seqnum'].'"'.$Selected.'>'.$LineItem['displaydesc'].'</option>';
		} ?>
        </select></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
	<?php } // end if ($SortListings) ?>
	<tr>
	  <td><?php echo RPT_TRUNC; ?></td>
	  <td>
	    <?php if ($Prefs['TruncListings']['params']=='1') $Checked = ' checked'; else $Checked = ''; ?>
          <input type="radio" name="DefTrunc" value="1"<?php echo $Checked; ?>><?php echo RPT_YES; ?>
	    <?php if ($Prefs['TruncListings']['params']=='0') $Checked = ' checked'; else $Checked = ''; ?>
          <input type="radio" name="DefTrunc" value="0"<?php echo $Checked; ?>><?php echo RPT_NO; ?> </td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php if ($Prefs['CritListings'] <> '') { ?>
	<tr>
      <td><?php echo RPT_FILTER; ?></td>
      <td><div align="center"><?php echo RPT_TYPE; ?></div></td>
      <td><div align="center"><?php echo RPT_FROM; ?></div></td>
      <td><div align="center"><?php echo RPT_TO; ?></div></td>
    </tr>
	<?php foreach ($Prefs['CritListings'] as $LineItem) echo BuildCriteria($LineItem);
	} // end if ($CritListings <> '') ?>
  </table>
  <?php if (!$Prefs['defaultreport']) { ?>
	  <table align="center" border="1" cellspacing="1" cellpadding="1">
		<tr>
			<td colspan="4"><div align="center"><?php echo RPT_FIELDS; ?></div></td>
			<td><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_UPDATE; ?>"></td>
		</tr>
		<tr>
			<td><div align="center"><?php echo RPT_FLDNAME; ?></div></td>
			<td><div align="center"><?php echo RPT_SHOW; ?></div></td>
			<td><div align="center"><?php echo RPT_BREAK; ?></div></td>
			<td><div align="center"><?php echo RPT_COLUMN; ?></div></td>
			<td><div align="center"><?php echo RPT_MOVE; ?></div></td>
		</tr>

		<?php echo BuildFieldList($Prefs['FieldListings']); ?>
	  </table>
  <?php } // end if (!$Prefs['defaultreport']) ?>
</form>
</body>
</html>
