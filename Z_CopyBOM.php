<?php
/** 
 * Author: Ashish Shukla <gmail.com!wahjava>
 *
 * Script to duplicate BoMs.
 */

$PageSecurity=9;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['Submit'])) {
  $stkid = $_POST['stkid'];
  $type = $_POST['type'];
  $newstkid = '';

  if($type == 'N')
    $newstkid = $_POST['tostkid'];
  else
    $newstkid = $_POST['exstkid'];
    
  $result = DB_query("begin", $db);

  if($type == 'N')
    {
      /* duplicate rows into stockmaster */
      $sql = "insert into stockmaster
			              select '".$newstkid."' as stockid,
						categoryid,
						description,
						longdescription,
						units,
						mbflag,
						lastcurcostdate,
						actualcost,
						lastcost,
						materialcost,
						labourcost,
						overheadcost,
						lowestlevel,
						discontinued,
						controlled,
						eoq,
						volume,
						kgs,
						barcode,
						discountcategory,
						taxcatid,
						serialised,
						appendfile,
						perishable,
						decimalplaces
					from stockmaster
					where stockid='".$stkid."';";
      $result = DB_query($sql, $db);
    }
  else
    {
      $sql = "SELECT lastcurcostdate, actualcost, lastcost, materialcost, labourcost, overheadcost, lowestlevel
              FROM stockmaster WHERE stockid='".$stkid."';";
      $result = DB_query($sql, $db);

      $row = DB_fetch_row($result);
      
      $sql = "update stockmaster set
              lastcurcostdate = '".$row[0]."',
              actualcost      = ".$row[1].",
              lastcost        = ".$row[2].",
              materialcost    = ".$row[3].",
              labourcost      = ".$row[4].",
              overheadcost    = ".$row[5].",
              lowestlevel     = ".$row[6]."
              where stockid='".$newstkid."';";
      $result = DB_query($sql, $db);
    }

  $sql = "insert into bom
				select '".$newstkid."' as parent,
						component,
						workcentreadded,
						loccode,
						effectiveafter,
						effectiveto,
						quantity,
						autoissue
				from bom
				where parent='".$stkid."';";
  $result = DB_query($sql, $db);

  if($type == 'N')
    {
      $sql = "insert into locstock
	      select loccode, '".$newstkid."' as stockid,0 as quantity,
	      reorderlevel
	      from locstock
	      where stockid='".$stkid."';";
      $result = DB_query($sql, $db);
    }

  $result = DB_query('commit', $db);

  UpdateCost($db, $newstkid);

  header('Location: BOMs.php?Select='.$newstkid);
 }

 else
   {
     $title = _('UTILITY PAGE To Copy a BOM');
     include('includes/header.inc');

     echo "<form method=\"post\" action=\"Z_CopyBOM.php\">";

     $sql = "SELECT stockid, description FROM stockmaster WHERE stockid IN (SELECT DISTINCT parent FROM bom) AND  mbflag IN ('M', 'A', 'K');";
     $result = DB_query($sql, $db);

     echo "<p>"._("From Stock ID");
     echo ": <select name=\"stkid\">";
     while($row = DB_fetch_row($result))
       {
	 echo "<option value=\"$row[0]\">".$row[0]." -- ".$row[1]."</option>";
       }
     echo "</select><br/><input type=\"radio\" name=\"type\" value=\"N\" checked=\"\"/>"._(" To New Stock ID");
     echo ": <input type=\"text\" maxlength=\"20\" name=\"tostkid\"/>";

     $sql = "SELECT stockid, description FROM stockmaster WHERE stockid NOT IN (SELECT DISTINCT parent FROM bom) AND mbflag IN ('M', 'A', 'K');";
     $result = DB_query($sql, $db);

     if(DB_num_rows($result) > 0)
       {
	 echo "<br/><input type=\"radio\" name=\"type\" value=\"E\"/>"._("To Existing Stock ID");
	 echo ": <select name=\"exstkid\">";
	 while($row = DB_fetch_row($result))
	   {
	     echo "<option value=\"$row[0]\">".$row[0]." -- ".$row[1]."</option>";
	   }
	 echo "</select>";
       } 
     echo "</p>";
     echo "<input type=\"submit\" name=\"Submit\" value=\"Submit\"/></p>";
    
     include("includes/footer.inc");
   }
?>
