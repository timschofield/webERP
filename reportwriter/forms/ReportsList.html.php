<?php echo '<html lang="' . str_replace('_', '-', substr($Language, 0, 5)) . ">"; ?>
<head></head>
<body>
<form name="reporthome" method="post" action="ReportMaker.php?action=go">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
  <input name="GoBackURL" type="hidden" value="<?php echo $GoBackURL; ?>">
  <table align="center" border="1" cellspacing="1" cellpadding="1">
	<tr>
		<td><div align="center"><?php echo RPT_DEFRPT; ?></div></td>
		<td><div align="center"><?php echo RPT_MYRPT; ?></div></td>
	</tr>
	<tr>
		<?php if ($DefReportList <> '') { ?>
		<td><select name="ReportID" size="20" style="padding:5px;" onChange="submit()"><?php echo $DefReportList; ?></select></td>
		<?php } else { echo '<td>'.RPT_NOSHOW.'</td>'; } ?>

		<?php if ($CustReportList <> '') { ?>
		<td><select name="ReportID" size="20" style="padding:5px;" onChange="submit()"><?php echo $CustReportList; ?></select></td>
		<?php } else { echo '<td>'.RPT_NOSHOW.'</td>'; } ?>
	</tr>
  </table>
</form>
</body>
</html>
