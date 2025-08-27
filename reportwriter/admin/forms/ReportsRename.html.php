<h2 align="center"><?php echo RPT_RPRBLDR.RPT_RNRPT; ?></h2>
<form name="reportidform" method="post" action="ReportCreator.php?action=step3">
	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<input name="ReportID" type="hidden" value="<?php echo $ReportID; ?>">
  <table align="center"  border="0" cellspacing="0" cellpadding="0">
   <tr>
     <td><?php echo RPT_RPTENTER; ?></td>
     <td><input name="ReportName" type="text" value="<?php if (isset($_POST['ReportName'])) echo $_POST['ReportName']; ?>">
	 <?php echo RPT_MAX30; ?></td>
   </tr>
   <tr>
     <td><div align="left"><input name="todo" type="submit" id="back" value="<?php echo RPT_BTN_BACK; ?>"></div></td>
     <td><div align="right"><input name="todo" type="submit" id="continue" value="<?php echo RPT_BTN_RENAME; ?>"></div></td>
   </tr>
  </table>
 </form>
