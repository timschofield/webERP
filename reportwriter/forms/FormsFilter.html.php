<?php echo '<html lang="' . str_replace('_', '-', substr($Language, 0, 5)) . ">"; ?>
<head></head>
<body>
<h2 align="center"><?php echo $Prefs['reportname'].' - '.RPT_BTN_CRIT; ?></h2>
<form name="formfilter" method="post" action="FormMaker.php<?php echo $QueryString; ?>">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
  <input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
  <input name="GoBackURL" type="hidden" value="<?php echo $GoBackURL; ?>">
  <input name="FormFilter" type="hidden" value="1">
  <table align="center" width="550" border="1" cellspacing="1" cellpadding="1">
	<tr>
      <td colspan="2"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_CANCEL; ?>"></td>
	  <td colspan="2"><div align="right"><input name="todo" type="submit" id="todo" value="<?php echo RPT_BTN_EXPPDF; ?>"></div></td>
    </tr>
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
    if ($Prefs['CritListings'] <> '') {
		foreach ($Prefs['CritListings'] as $LineItem) echo BuildCriteria($LineItem);
	} // end if ($CritListings <> '') ?>
  </table>
</form>
</body>
</html>
