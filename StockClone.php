<?php
/* $Id: StockClone.php 6669 2014-04-05 23:31:54Z rchacon $ */

include('includes/session.php');
$Title = _('Clone Item');
/* webERP manual links before header.php */
$ViewTopic= 'Inventory';
$BookMark = 'CloneItem';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['OldStockID']) || isset($_POST['OldStockID']) ){ //we are cloning
    $_POST['OldStockID'] = isset($_GET['OldStockID']) && !empty($_GET['OldStockID']) ? $_GET['OldStockID']:$_POST['OldStockID'];
	$_POST['OldStockID'] =trim(mb_strtoupper($_POST['OldStockID']));
	$_POST['New']= 1;
	if (isset($_POST['StockID']) ) {
        $_POST['StockID'] =trim(mb_strtoupper($_POST['StockID']));
    } else {
         $_POST['StockID'] = '';
    }
} else {
    $_POST['New'] = 0;
    $_POST['OldStockID'] = '';
    $_POST['StockID'] = '';
    $InputError = 1;
    $Errors[0] = 'OldStockID';
    prnMsg(_('To use this script it must be called with the Stock ID of the item to be cloned passed in as $OldStockID. Please use the Clone This Item option in the Items Menu.'),'error');
}

$ItemDescriptionLanguagesArray = explode(',',$_SESSION['ItemDescriptionLanguages']);

if (isset($_POST['StockID']) && !empty($_POST['StockID']) && !isset($_POST['UpdateCategories'])) {
	$sql = "SELECT COUNT(stockid)
			FROM stockmaster
			WHERE stockid='".$_POST['StockID']."'
			GROUP BY stockid";

	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if (($myrow[0]==0) && ($_POST['OldStockID'] != '')) {
		 $_POST['New'] =1;
	} else {
    	prnMsg(_('The stock code entered is already in the database - duplicate stock codes are prohibited by the system. Try choosing an alternative stock code'),'error');
		$Errors[1] = 'DuplicateStockID';
		$_POST['New']=0;
		$_POST['StockID'] = $_POST['OldStockID'];
	}
}

echo '<a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a>
	<br />
	<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Stock') . '" alt="" />' . ' ' . $Title . '
	</p>';
echo '<div class="page_help_text">' . _('Cloning will create a new item with the same properties, image, cost, purchasing and pricing data as the selected item. Item image and general item details can be changed below prior to cloning.') . '.</div><br />';

$SupportedImgExt = array('png','jpg','jpeg');

// Check extention for existing old file
foreach ($SupportedImgExt as $ext) {
	$oldfile = $_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '.' . $ext;
	if (file_exists ($oldfile) ) {
			break;
			$ext = pathinfo($oldfile, PATHINFO_EXTENSION);
	}
}

if (!empty($_POST['OldStockID'])) { //only show this if there is a valid call to this script
    if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') { //we are uploading a new file
		$newfilename = ($_POST['OldStockID'] == $_POST['StockID']) || $_POST['StockID'] == ''? $_POST['OldStockID'].'-TEMP': $_POST['StockID'] ; //so we can add a new file but not remove an existing item file
        $result	= $_FILES['ItemPicture']['error'];
        $UploadTheFile = 'Yes'; //Assume all is well to start off with
		if (pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION) != $ext) {
			$ext = pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION);
		}

        $filename = $_SESSION['part_pics_dir'] . '/' . $newfilename . '.' . $ext;

         //But check for the worst
        if (!in_array ($ext, $SupportedImgExt)) {
			prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
			$UploadTheFile ='No';
        } elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
            prnMsg(_('The image file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
            $UploadTheFile ='No';
        } elseif ( $_FILES['ItemPicture']['type'] == 'text/plain' ) {  //File Type Check
            prnMsg( _('Only graphics files can be uploaded'),'warn');
                $UploadTheFile ='No';
        } elseif ( $_FILES['ItemPicture']['error'] == 6 ) {  //upload temp directory check
            prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
                $UploadTheFile ='No';
        } elseif (file_exists($filename)){
            prnMsg(_('Attempting to overwrite an existing item image'),'warn');
            $result = unlink($filename);
            if (!$result){
                prnMsg(_('The existing image could not be removed'),'error');
                $UploadTheFile ='No';
            }
        }
        //first remove any temp file that ight be there
          @unlink($filename);
        if ($UploadTheFile=='Yes'){
            $result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
            $message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
        }
    } elseif (!empty($_POST['StockID']) AND ($_POST['StockID'] != $_POST['OldStockID']) AND file_exists($_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '-TEMP' . '.' . $ext) )  {
		//rename the temp one to the new name
        $oldfile = $_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'-TEMP' . '.' . $ext;
        if (!copy($oldfile, $_SESSION['part_pics_dir'] . '/' .$_POST['StockID'] . '.' . $ext)) {
             prnMsg(_('There was an image file to clone but there was an error copying. Please upload a new image if required.'),'warn');
        }
         @unlink($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'-TEMP' . '.' . $ext);
        if (is_file($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'-TEMP' . '.' . $ext)) {
             prnMsg(_('Unable to delete the temporary image file for cloned item.'),'error');
        } else {
            $StockImgLink = _('No Image');
        }
    } elseif (isset( $_POST['OldStockID']) AND file_exists($_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '.' . $ext)  AND !file_exists($_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'].'-TEMP' . '.' . $ext) ) {
		//we should copy
        if (!copy($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'] . '.' . $ext, $_SESSION['part_pics_dir'] . '/' . $_POST['StockID'] . '.' . $ext)) {
            prnMsg(_('There was an image file to clone but there was an error copying. Please upload a new image if required.'),'warn');
        }
    }
}
if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (!isset($_POST['Description']) OR mb_strlen($_POST['Description']) > 50 OR mb_strlen($_POST['Description'])==0) {
		$InputError = 1;
		prnMsg (_('The stock item description must be entered and be fifty characters or less long') . '. ' . _('It cannot be a zero length string either') . ' - ' . _('a description is required'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (mb_strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		prnMsg (_('The stock item description cannot be a zero length string') . ' - ' . _('a long description is required'),'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}
	if ($_POST['StockID'] == $_POST['OldStockID']) {
	    $InputError = 1;
		prnMsg (_('The Stock Item code must be unique. Please re-enter a unique Stock Item code.'),'error');
		$Errors[$i] = 'StockID';
		$i++;
	}
	if (mb_strlen($_POST['StockID']) ==0) {
		$InputError = 1;
		prnMsg (_('The Stock Item code cannot be empty. Please enter a unique Stock Item code.'),'error');
		$Errors[$i] = 'StockID';
		$i++;
	}
	if (ContainsIllegalCharacters($_POST['StockID']) OR mb_strpos($_POST['StockID'],' ')) {
		$InputError = 1;
		prnMsg(_('The stock item code cannot contain any of the following characters') . " - ' &amp; + \" \\ ." . _('or a space'),'error');
		$Errors[$i] = 'StockID';
		$i++;
		$_POST['StockID']='';
	}
	if (mb_strlen($_POST['Units']) >20) {
		$InputError = 1;
		prnMsg(_('The unit of measure must be 20 characters or less long'),'error');
		$Errors[$i] = 'Units';
		$i++;
	}
	if (mb_strlen($_POST['BarCode']) >20) {
		$InputError = 1;
		prnMsg(_('The barcode must be 20 characters or less long'),'error');
		$Errors[$i] = 'BarCode';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['Volume']))) {
		$InputError = 1;
		prnMsg (_('The volume of the packaged item in cubic metres must be numeric') ,'error');
		$Errors[$i] = 'Volume';
		$i++;
	}
	if (filter_number_format($_POST['Volume']) <0) {
		$InputError = 1;
		prnMsg(_('The volume of the packaged item must be a positive number'),'error');
		$Errors[$i] = 'Volume';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['GrossWeight']))) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item in Gross Weight must be numeric'),'error');
		$Errors[$i] = 'GrossWeight';
		$i++;
	}
	if (filter_number_format($_POST['GrossWeight'])<0) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item must be a positive number'),'error');
		$Errors[$i] = 'GrossWeight';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['NetWeight']))) {
		$InputError = 1;
		prnMsg(_('The net weight of the item in Net Weight must be numeric'),'error');
		$Errors[$i] = 'NetWeight';
		$i++;
	}
	if (filter_number_format($_POST['NetWeight'])<0) {
		$InputError = 1;
		prnMsg(_('The net weight of the item must be a positive number'),'error');
		$Errors[$i] = 'NetWeight';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['EOQ']))) {
		$InputError = 1;
		prnMsg(_('The economic order quantity must be numeric'),'error');
		$Errors[$i] = 'EOQ';
		$i++;
	}
	if (filter_number_format($_POST['EOQ']) <0) {
		$InputError = 1;
		prnMsg (_('The economic order quantity must be a positive number'),'error');
		$Errors[$i] = 'EOQ';
		$i++;
	}
	if ($_POST['Controlled']==0 AND $_POST['Serialised']==1){
		$InputError = 1;
		prnMsg(_('The item can only be serialised if there is lot control enabled already') . '. ' . _('Batch control') . ' - ' . _('with any number of items in a lot/bundle/roll is enabled when controlled is enabled') . '. ' . _('Serialised control requires that only one item is in the batch') . '. ' . _('For serialised control') . ', ' . _('both controlled and serialised must be enabled'),'error');
		$Errors[$i] = 'Serialised';
		$i++;
	}
	if ($_POST['NextSerialNo']!=0 AND $_POST['Serialised']==0){
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a serialised item'),'error');
		$Errors[$i] = 'NextSerialNo';
		$i++;
	}
	if ($_POST['NextSerialNo']!=0 AND $_POST['MBFlag']!='M'){
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a manufactured item'),'error');
		$Errors[$i] = 'NextSerialNo';
		$i++;
	}
	if (($_POST['MBFlag']=='A'
			OR $_POST['MBFlag']=='K'
			OR $_POST['MBFlag']=='D'
			OR $_POST['MBFlag']=='G')
		AND $_POST['Controlled']==1){

		$InputError = 1;
		prnMsg(_('Assembly/Kitset/Phantom/Service/Labour items cannot also be controlled items') . '. ' . _('Assemblies/Dummies/Phantom and Kitsets are not physical items and batch/serial control is therefore not appropriate'),'error');
		$Errors[$i] = 'Controlled';
		$i++;
	}
	if (trim($_POST['CategoryID'])==''){
		$InputError = 1;
		prnMsg(_('There are no inventory categories defined. All inventory items must belong to a valid inventory category,'),'error');
		$Errors[$i] = 'CategoryID';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['Pansize']))) {
		$InputError = 1;
		prnMsg(_('Pansize quantity must be numeric'),'error');
		$Errors[$i] = 'Pansize';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['ShrinkFactor']))) {
		$InputError = 1;
		prnMsg(_('Shrinkage factor quantity must be numeric'),'error');
		$Errors[$i] = 'ShrinkFactor';
		$i++;
	}

	if ($InputError !=1){
		if ($_POST['Serialised']==1){ /*Not appropriate to have several dp on serial items */
			$_POST['DecimalPlaces']=0;
		}
		if ($_POST['New'] !=0) { //it is a NEW CLONED part
			//but lets be really sure here
			$result = DB_query("SELECT stockid
								FROM stockmaster
								WHERE stockid='" . $_POST['StockID'] ."'");
			if (DB_num_rows($result)==1){
				prnMsg(_('The stock code entered is already in the database - duplicate stock codes are prohibited by the system. Try choosing an alternative stock code'),'error');
				$Errors[$i] = 'DuplicateStockID';
				//exit;
			} else {
    			DB_Txn_Begin();
				$sql = "INSERT INTO stockmaster (stockid,
												description,
												longdescription,
												categoryid,
												units,
												mbflag,
												eoq,
												discontinued,
												controlled,
												serialised,
												perishable,
												volume,
												grossweight,
												netweight,
												barcode,
												discountcategory,
												taxcatid,
												decimalplaces,
												shrinkfactor,
												pansize)
							VALUES ('".$_POST['StockID']."',
								'" . $_POST['Description'] . "',
								'" . $_POST['LongDescription'] . "',
								'" . $_POST['CategoryID'] . "',
								'" . $_POST['Units'] . "',
								'" . $_POST['MBFlag'] . "',
								'" . filter_number_format($_POST['EOQ']) . "',
								'" . $_POST['Discontinued'] . "',
								'" . $_POST['Controlled'] . "',
								'" . $_POST['Serialised']. "',
								'" . $_POST['Perishable']. "',
								'" . filter_number_format($_POST['Volume']) . "',
								'" . filter_number_format($_POST['GrossWeight']) . "',
								'" . filter_number_format($_POST['NetWeight']) . "',
								'" . $_POST['BarCode'] . "',
								'" . $_POST['DiscountCategory'] . "',
								'" . $_POST['TaxCat'] . "',
								'" . $_POST['DecimalPlaces']. "',
								'" . filter_number_format($_POST['ShrinkFactor']) . "',
								'" . filter_number_format($_POST['Pansize']) . "')";

				$ErrMsg =  _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);
				if (DB_error_no() ==0) {
					//now insert the language descriptions
					$ErrMsg = _('Could not update the language description because');
					$DbgMsg = _('The SQL that was used to update the language description and failed was');
					if (count($ItemDescriptionLanguagesArray)>0){
						foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
							if ($LanguageId!=''){
								$result = DB_query("INSERT INTO stockdescriptiontranslations (stockid,
																							language_id,
																							descriptiontranslation,
																							longdescriptiontranslation)
													VALUES('" . $_POST['StockID'] . "','" .
																$LanguageId . "', '" .
																$_POST['Description_' . str_replace('.','_',$LanguageId)]  . "', '" .
																$_POST['LongDescription_' . str_replace('.','_',$LanguageId)].
																"')",$ErrMsg,$DbgMsg,true);
							}
						}
					}
					//now insert any item properties
					for ($i=0;$i<$_POST['PropertyCounter'];$i++){

						if ($_POST['PropType' . $i] ==2){
							if ($_POST['PropValue' . $i]=='on'){
								$_POST['PropValue' . $i]=1;
							} else {
								$_POST['PropValue' . $i]=0;
							}
			            }

						if ($_POST['PropNumeric' .$i]==1){
							$_POST['PropValue' . $i]=filter_number_format($_POST['PropValue' . $i]);
						} else {
							$_POST['PropValue' . $i]=$_POST['PropValue' . $i];
						}

					$result = DB_query("INSERT INTO stockitemproperties (stockid,
													stkcatpropid,
													value)
													VALUES ('" . $_POST['StockID'] . "',
														'" . $_POST['PropID' . $i] . "',
														'" . $_POST['PropValue' . $i] . "')",
										$ErrMsg,
										$DbgMsg,
										true);
					} //end of loop around properties defined for the category

					//Add data to locstock

					$sql = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $_POST['StockID'] . "'
										FROM locations";

					$ErrMsg =  _('The locations for the item') . ' ' . $_POST['StockID'] .  ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($sql,$ErrMsg,$DbgMsg);
                    DB_Txn_Commit();
                    //check for any purchase data
                    $sql = "SELECT purchdata.supplierno,
                                suppliers.suppname,
                                purchdata.price,
                                suppliers.currcode,
                                purchdata.effectivefrom,
                                purchdata.suppliersuom,
                                purchdata.supplierdescription,
                                purchdata.leadtime,
                                purchdata.suppliers_partno,
                                purchdata.minorderqty,
                                purchdata.preferred,
                                purchdata.conversionfactor,
                                currencies.decimalplaces AS currdecimalplaces
                            FROM purchdata INNER JOIN suppliers
                                ON purchdata.supplierno=suppliers.supplierid
                            INNER JOIN currencies
                                ON suppliers.currcode=currencies.currabrev
                            WHERE purchdata.stockid = '" . $_POST['OldStockID'] . "'
                            ORDER BY purchdata.effectivefrom DESC";
                    $ErrMsg = _('The supplier purchasing details for the selected part could not be retrieved because');
                    $PurchDataResult = DB_query($sql, $ErrMsg);
                    if (DB_num_rows($PurchDataResult) == 0 and $_POST['OldStockID'] != '') {
                        //prnMsg(_('There is no purchasing data set up for the part selected'), 'info');
                        $NoPurchasingData=1;
                    } else {
                        while ($myrow = DB_fetch_array($PurchDataResult)) { //clone the purchase data

                            $sql = "INSERT INTO purchdata (supplierno,
										stockid,
										price,
										effectivefrom,
										suppliersuom,
										conversionfactor,
										supplierdescription,
										suppliers_partno,
										leadtime,
										minorderqty,
										preferred)
                                VALUES ('" . $myrow['supplierno'] . "',
                                    '" . $_POST['StockID'] . "',
                                    '" . $myrow['price'] . "',
                                    '" . $myrow['effectivefrom'] . "',
                                    '" . $myrow['suppliersuom'] . "',
                                    '" . $myrow['conversionfactor'] . "',
                                    '" . DB_escape_string($myrow['supplierdescription']) . "',
                                    '" . $myrow['suppliers_partno'] . "',
                                    '" . $myrow['leadtime'] . "',
                                    '" . $myrow['minorderqty'] . "',
                                    '" . $myrow['preferred'] . "')";
                                $ErrMsg = _('The cloned supplier purchasing details could not be added to the database because');
                                $DbgMsg = _('The SQL that failed was');
                                $AddResult = DB_query($sql, $ErrMsg, $DbgMsg);
                        }
                    }

                    //For both the following - assume the data taken from the tables has already been validated.
                    //check for price data
                    $sql = "SELECT currencies.currency,
                                salestypes.sales_type,
                            prices.price,
                            prices.stockid,
                            prices.typeabbrev,
                            prices.currabrev,
                            prices.startdate,
                            prices.enddate,
                            prices.debtorno,
                            currencies.decimalplaces AS currdecimalplaces
                        FROM prices
                        INNER JOIN salestypes
                            ON prices.typeabbrev = salestypes.typeabbrev
                        INNER JOIN currencies
                            ON prices.currabrev=currencies.currabrev
                        WHERE prices.stockid='".$_POST['OldStockID']."'

                        ORDER BY prices.currabrev,
                            prices.typeabbrev,
                            prices.startdate";

                    $PricingDataResult = DB_query($sql);
                        //AND prices.debtorno=''
                    if (DB_num_rows($PricingDataResult) == 0 and $_POST['OldStockID'] != '') {
                        prnMsg(_('There is no purchasing data set up for the part selected'), 'info');
                        $NoPricingData=1;
                    } else {
                        while ($myrow = DB_fetch_array($PricingDataResult)) { //clone the purchase data
                            $sql = "INSERT INTO prices (stockid,
                                        typeabbrev,
                                        currabrev,
                                        debtorno,
                                        startdate,
                                        enddate,
                                        price)
                                VALUES ('" . $_POST['StockID']. "',
                                    '" . $myrow['typeabbrev'] . "',
                                    '" . $myrow['currabrev'] . "',
                                    '" . $myrow['debtorno'] . "',
                                    '" . $myrow['startdate'] . "',
                                    '" . $myrow['enddate']. "',
                                    '" . $myrow['price'] . "')";
                                 $ErrMsg = _('The cloned pricing could not be added');
                                 $result = DB_query($sql,$ErrMsg);
                          }
                    }
                    //What about cost data?
                    //get any existing cost data
                    $sql = "SELECT materialcost,
									labourcost,
									overheadcost,
									lastcost
							FROM stockmaster
							WHERE stockmaster.stockid='".$_POST['OldStockID']."'";
                        $ErrMsg = _('The entered item code does not exist');
                        $OldResult = DB_query($sql,$ErrMsg);
                        $OldRow = DB_fetch_array($OldResult);

                    //now update cloned item costs
                        $Result = DB_Txn_Begin();
                        $SQL = "UPDATE stockmaster SET	materialcost='" . $OldRow['materialcost'] . "',
										labourcost     ='" . $OldRow['labourcost'] . "',
										overheadcost   ='" . $OldRow['overheadcost'] . "',
										lastcost       ='" . $OldRow['lastcost'] . "',
										lastcostupdate ='" . Date('Y-m-d')."'
								WHERE stockid='" . $_POST['StockID'] . "'";
                        $ErrMsg = _('The cost details for the cloned stock item could not be updated because');
		                $DbgMsg = _('The SQL that failed was');
		                $Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		                $Result = DB_Txn_Commit();

                    //finish up
					if (DB_error_no() ==0) {
						prnMsg( _('New cloned Item') .' ' . '<a href="SelectProduct.php?StockID=' . $_POST['StockID'] . '">' . $_POST['StockID'] . '</a> '. _('has been added to the database') .
							'<br />' . _('We also attempted to setup item purchase data and pricing.'));

                            if (isset($NoPricingData))
                            {
                                prnMsg(_('There is no pricing data to clone. Use the following link to add pricing.'));
                            }

                            prnMsg('<br />' . '<a target="_blank" href="Prices.php?Item=' . $_POST['StockID'] . '">' . _('Review Item Prices') . '</a> ','success');

                            if ($NoPurchasingData==1)
                            {
                                 prnMsg(_('There is no purchasing data to clone .Use the following link to add purchasing data.'));
                            }
                            prnMsg('<br />' . '<a target="_blank" href="PurchData.php?StockID=' . $_POST['StockID'] . '">' . _('Review Item Purchase Data.') . '</a> ','success') .
                            prnMsg(_('Costing was updated for this cloned item.').
                            '<br />' . '<a target="_blank" href="StockCostUpdate.php?StockID=' . $_POST['StockID'] . '">' . _('Review Item Cost') . '</a>', 'success');

						echo '<br />';
						unset($_POST['Description']);
						unset($_POST['LongDescription']);
						unset($_POST['EOQ']);
                        // Leave Category ID set for ease of batch entry
                        //unset($_POST['CategoryID']);
						unset($_POST['Units']);
						unset($_POST['MBFlag']);
						unset($_POST['Discontinued']);
						unset($_POST['Controlled']);
						unset($_POST['Serialised']);
						unset($_POST['Perishable']);
						unset($_POST['Volume']);
						unset($_POST['GrossWeight']);
						unset($_POST['NetWeight']);
						unset($_POST['BarCode']);
						unset($_POST['ReorderLevel']);
						unset($_POST['DiscountCategory']);
						unset($_POST['DecimalPlaces']);
						unset($_POST['ShrinkFactor']);
						unset($_POST['Pansize']);
						unset($_POST['StockID']);
						//unset($_POST['OldStockID']);
						foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
						unset($_POST['Description_' . str_replace('.','_',$LanguageId)]);
						 $_POST['New']   = 1; //do not show input form again
						}
					}//Reset the form variables
				}//Stock records finished
			}//End of check for existing item
		} //END Cloned item
	} else {
	    $_POST['New']   = 1;
		echo '<br />'. "\n";
		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
	}

}


echo '<form name="ItemForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="New" value="'.$_POST['New'].'" />
	<table class="selection">';

if (empty($_POST['StockID']) || ($_POST['StockID'] == $_POST['OldStockID']) || isset($_POST['UpdateCategories'])) {

/*If the page was called without $StockID or empty $StockID  then a new cloned stock item is to be entered. Show a form with a part Code field,
  otherwise show form for editing with only a hidden OldStockID field. */

	    $StockIDStyle= !empty($_POST['StockID'])  && ($_POST['StockID'] != $_POST['OldStockID'])? '' : ' style="color:red;border: 2px solid red;background-color:#fddbdb;" ';
	    $StockID= !empty($_POST['StockID'])? $_POST['StockID']:$_POST['OldStockID'];
		echo '<tr>
				<td>'. _('Cloned Item Code'). ':</td>
				<td><input type="text" ' . (in_array('StockID',$Errors) ?  'class="inputerror"' : '' ) .'"  "'.$StockIDStyle.'" data-type="no-illegal-chars" autofocus="autofocus" required="required" value="' . $StockID . '" name="StockID" size="21" maxlength="20" />
				<input type="hidden" name="OldStockID" value="'.$_POST['OldStockID'].'" />  '. _('Enter a unique item code for the new item.') .'
				</td>

			</tr>';

}
if ( (!isset($_POST['UpdateCategories']) AND ($InputError!=1))  OR $_POST['New']== 1 ) { // Must be modifying an existing item and no changes made yet

    $selectedStockID = $_POST['OldStockID'];
	$sql = "SELECT stockid,
					description,
					longdescription,
					categoryid,
					units,
					mbflag,
					discontinued,
					controlled,
					serialised,
					perishable,
					eoq,
					volume,
					grossweight,
					netweight,
					barcode,
					discountcategory,
					taxcatid,
					decimalplaces,
					nextserialno,
					pansize,
					shrinkfactor
			FROM stockmaster
			WHERE stockid = '".$selectedStockID."'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['LongDescription'] = $myrow['longdescription'];
	$_POST['Description'] = $myrow['description'];
	$_POST['EOQ']  = $myrow['eoq'];
	$_POST['CategoryID']  = $myrow['categoryid'];
	$_POST['Units']  = $myrow['units'];
	$_POST['MBFlag']  = $myrow['mbflag'];
	$_POST['Discontinued']  = $myrow['discontinued'];
	$_POST['Controlled']  = $myrow['controlled'];
	$_POST['Serialised']  = $myrow['serialised'];
	$_POST['Perishable']  = $myrow['perishable'];
	$_POST['Volume']  = $myrow['volume'];
	$_POST['GrossWeight']  = $myrow['grossweight'];
	$_POST['NetWeight']  = $myrow['netweight'];
	$_POST['BarCode']  = $myrow['barcode'];
	$_POST['DiscountCategory']  = $myrow['discountcategory'];
	$_POST['TaxCat'] = $myrow['taxcatid'];
	$_POST['DecimalPlaces'] = $myrow['decimalplaces'];
	$_POST['NextSerialNo'] = $myrow['nextserialno'];
	$_POST['Pansize'] = $myrow['pansize'];
	$_POST['ShrinkFactor'] = $myrow['shrinkfactor'];

	$sql = "SELECT descriptiontranslation,
					longdescriptiontranslation,
					language_id
			FROM stockdescriptiontranslations
			WHERE stockid='" . $selectedStockID . "' AND (";
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		$sql .= "language_id='" . $LanguageId ."' OR ";
	}
	$sql = mb_substr($sql,0,mb_strlen($sql)-3) . ')';
	$result = DB_query($sql);
	while ($myrow = DB_fetch_array($result)){
		$_POST['Description_' . str_replace('.','_',$myrow['language_id'])] = $myrow['descriptiontranslation'];
		$_POST['LongDescription_' . str_replace('.','_',$myrow['language_id'])] = $myrow['longdescriptiontranslation'];
	}

}

//if ($_POST['New'] == 1) {
    if (isset($_POST['Description'])) {
        $Description = $_POST['Description'];
    } else {
        $Description ='';
    }
    echo '<tr>
            <td>' . _('Part Description') . ' (' . _('short') . '):</td>
            <td><input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Description" size="52" maxlength="50" value="' . $Description . '" /></td>
        </tr>';

    foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
        if ($LanguageId!=''){
            //unfortunately cannot have points in POST variables so have to mess with the language id
            $PostVariableName = 'Description_' . str_replace('.','_',$LanguageId);
	    $LongDescriptionTranslated = 'LongDescription_' . str_replace('.','_',$LanguageId);
            if (!isset($_POST[$PostVariableName])){
                $_POST[$PostVariableName] ='';
            }
            echo '<tr>
                <td>' . $LanguagesArray[$LanguageId]['LanguageName'] . ' ' . _('Description') . ':</td>
                <td><input type="text" name="'. $PostVariableName . '" size="52" maxlength="50" value="' . $_POST[$PostVariableName] . '" /></td>
		<td><input type="hidden" name="' . $LongDescriptionTranslated . '" value="' . $_POST['LongDescription_' . str_replace('.','_',$LanguageId)] . '" />
            </tr>';
        }
    }

    if (isset($_POST['LongDescription'])) {
        $LongDescription = AddCarriageReturns($_POST['LongDescription']);
    } else {
        $LongDescription ='';
    }
    echo '<tr>
            <td>' . _('Part Description') . ' (' . _('long') . '):</td>
            <td><textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" cols="40" rows="3">' . stripslashes($LongDescription) . '</textarea></td>
        </tr>
        <tr>
            <td>'. _('Image File (.jpg)') . ':</td>
            <td><input type="file" id="ItemPicture" name="ItemPicture" />
            <br />
            <input type="checkbox" name="ClearImage" id="ClearImage" value="1" /> '._('Clear Image').'
            </td>';

    // if this is the first time displaying the form, there will only be a picture with the OldStockID name, if any, else there can be a $_POST['OldStockID'].'-TEMP'. '.jpg' file if one was uploaded
    if (empty($_POST['StockID']) OR ($_POST['StockID'] == $_POST['OldStockID'])) {
        $tempid = $_POST['OldStockID'].'-TEMP';
    } else {
        $tempid = $_POST['StockID'];
    }

    if (extension_loaded('gd') && function_exists ('gd_info') && isset ($tempfile) ) {
        $StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
            '&amp;StockID='.urlencode($tempid).
            '&amp;text='.
            '&amp;width=100'.
            '&amp;height=100'.
            '" alt="" />';
    } else {
        if( !empty($tempid) AND file_exists($_SESSION['part_pics_dir'] . '/' .$tempid.'.' . $ext) ) {
            $StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . $tempid . '.' . $ext . '" height="100" width="100" />';
            if (isset($_POST['ClearImage']) ) {
                //workaround for many variations of permission issues that could cause unlink fail
                @unlink($_SESSION['part_pics_dir'] . '/' .$tempid.'.' . $ext);
                if (is_file($_SESSION['part_pics_dir'] . '/' .$tempid.'.' . $ext)) {
                     prnMsg(_('You do not have access to delete this item image file.'),'error');
                } else {
                    $StockImgLink = _('No Image');
                }
            }
        } elseif ( !empty($tempid) AND !file_exists($_SESSION['part_pics_dir'] . '/' .$tempid.'.' . $ext) AND file_exists($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.' . $ext)) {
			if (!copy($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.' . $ext, $_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'-TEMP' . '.' . $ext)) {
                $StockImgLink = _('No Image');
            } else {
                $StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'-TEMP' . '.' . $ext . '" height="100" width="100" />';
            }
        } else {
            $StockImgLink = _('No Image');
        }
    }

    if ($StockImgLink!=_('No Image')) {
        echo '<td>' . _('Image') . '<br />' . $StockImgLink . '</td>';
    }
    echo '</tr>';

    echo '<tr>
            <td>' . _('Category') . ':</td>
            <td><select name="CategoryID" onchange="ReloadForm(ItemForm.UpdateCategories)">';

    $sql = "SELECT categoryid, categorydescription FROM stockcategory";
    $ErrMsg = _('The stock categories could not be retrieved because');
    $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
    $result = DB_query($sql,$ErrMsg,$DbgMsg);

    while ($myrow=DB_fetch_array($result)){
        if (!isset($_POST['CategoryID']) OR  $myrow['categoryid']==$_POST['CategoryID']){
            echo '<option selected="selected" value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
        } else {
            echo '<option value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
        }
        $Category=$myrow['categoryid'];
    }

    if (!isset($_POST['CategoryID'])) {
        $_POST['CategoryID']=$Category;
    }

    echo '</select><a target="_blank" href="'. $RootPath . '/StockCategories.php">' . _('Add or Modify Stock Categories') . '</a></td>
        </tr>';

    if (!isset($_POST['EOQ']) OR $_POST['EOQ']==''){
        $_POST['EOQ']=0;
    }

    if (!isset($_POST['Volume']) OR $_POST['Volume']==''){
        $_POST['Volume']=0;
    }
    if (!isset($_POST['GrossWeight']) OR $_POST['GrossWeight']==''){
        $_POST['GrossWeight']=0;
    }
    if (!isset($_POST['NetWeight']) OR $_POST['NetWeight']==''){
        $_POST['NetWeight']=0;
    }
    if (!isset($_POST['Controlled']) OR $_POST['Controlled']==''){
        $_POST['Controlled']=0;
    }
    if (!isset($_POST['Serialised']) OR  $_POST['Serialised']=='' || $_POST['Controlled']==0){
        $_POST['Serialised']=0;
    }
    if (!isset($_POST['DecimalPlaces']) OR  $_POST['DecimalPlaces']==''){
        $_POST['DecimalPlaces']=0;
    }
    if (!isset($_POST['Discontinued']) OR  $_POST['Discontinued']==''){
        $_POST['Discontinued']=0;
    }
    if (!isset($_POST['Pansize'])) {
        $_POST['Pansize']=0;
    }
    if (!isset($_POST['ShrinkFactor'])) {
        $_POST['ShrinkFactor']=0;
    }
    if (!isset($_POST['NextSerialNo'])) {
        $_POST['NextSerialNo']=0;
    }


    echo '<tr>
            <td>' . _('Economic Order Quantity') . ':</td>
            <td><input ' . (in_array('EOQ',$Errors) ?  'class="inputerror"' : '' ) .'   type="text" class="number" name="EOQ" size="12" maxlength="10" value="' . locale_number_format($_POST['EOQ'],'Variable') . '" /></td></tr>';

    echo '<tr>
            <td>' . _('Packaged Volume (metres cubed)') . ':</td>
            <td><input ' . (in_array('Volume',$Errors) ?  'class="inputerror"' : '' ) .'   type="text" class="number" name="Volume" size="12" maxlength="10" value="' . locale_number_format($_POST['Volume'],'Variable') . '" /></td>
        </tr>';

    echo '<tr>
            <td>' . _('Packaged Gross Weight (KGs)') . ':</td><td><input ' . (in_array('GrossWeight',$Errors) ?  'class="inputerror"' : '' ) .'   type="text" class="number" name="GrossWeight" size="12" maxlength="10" value="' . locale_number_format($_POST['GrossWeight'],'Variable') . '" /></td>
        </tr>';

    echo '<tr>
            <td>' . _('Net Weight (KGs)') . ':</td><td><input ' . (in_array('NetWeight',$Errors) ?  'class="inputerror"' : '' ) .'   type="text" class="number" name="NetWeight" size="12" maxlength="10" value="' . locale_number_format($_POST['NetWeight'],'Variable') . '" /></td>
        </tr>';

        echo '<tr>
            <td>' . _('Units of Measure') . ':</td>
            <td><select ' . (in_array('Description',$Errors) ?  'class="selecterror"' : '' ) .'  name="Units">';

    $sql = "SELECT unitname FROM unitsofmeasure ORDER by unitname";
    $UOMResult = DB_query($sql);

    if (!isset($_POST['Units'])) {
        $UOMrow['unitname']=_('each');
    }
    while( $UOMrow = DB_fetch_array($UOMResult) ) {
         if (isset($_POST['Units']) AND $_POST['Units']==$UOMrow['unitname']){
            echo '<option selected="selected" value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname'] . '</option>';
         } else {
            echo '<option value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname']  . '</option>';
         }
    }

    echo '</select></td>
        </tr>';

    echo '<tr>
            <td>' . _('Assembly, Kit, Manufactured or Service/Labour') . ':</td>
            <td><select name="MBFlag">';
    if ($_POST['MBFlag']=='A'){
        echo '<option selected="selected" value="A">' . _('Assembly') . '</option>';
    } else {
        echo '<option value="A">' . _('Assembly') . '</option>';
    }
    if (!isset($_POST['MBFlag']) OR  $_POST['MBFlag']=='K'){
        echo '<option selected="selected" value="K">' . _('Kit') . '</option>';
    } else {
        echo '<option value="K">' . _('Kit') . '</option>';
    }
    if (!isset($_POST['MBFlag']) OR  $_POST['MBFlag']=='M'){
        echo '<option selected="selected" value="M">' . _('Manufactured') . '</option>';
    } else {
        echo '<option value="M">' . _('Manufactured') . '</option>';
    }
    if (!isset($_POST['MBFlag']) OR  $_POST['MBFlag']=='G' OR !isset($_POST['MBFlag']) OR $_POST['MBFlag']==''){
        echo '<option selected="selected" value="G">' . _('Phantom') . '</option>';
    } else {
        echo '<option value="G">' . _('Phantom') . '</option>';
    }
    if (!isset($_POST['MBFlag']) OR  $_POST['MBFlag']=='B' OR !isset($_POST['MBFlag']) OR $_POST['MBFlag']==''){
        echo '<option selected="selected" value="B">' . _('Purchased') . '</option>';
    } else {
        echo '<option value="B">' . _('Purchased') . '</option>';
    }

    if (isset($_POST['MBFlag']) AND $_POST['MBFlag']=='D'){
        echo '<option selected="selected" value="D">' . _('Service/Labour') . '</option>';
    } else {
        echo '<option value="D">' . _('Service/Labour') . '</option>';
    }

    echo '</select></td>
        </tr>';

    echo '<tr>
            <td>' . _('Current or Obsolete') . ':</td>
            <td><select name="Discontinued">';

    if ($_POST['Discontinued']==0){
        echo '<option selected="selected" value="0">' . _('Current') . '</option>';
    } else {
        echo '<option value="0">' . _('Current') . '</option>';
    }
    if ($_POST['Discontinued']==1){
        echo '<option selected="selected" value="1">' . _('Obsolete') . '</option>';
    } else {
        echo '<option value="1">' . _('Obsolete') . '</option>';
    }
    echo '</select></td>
            </tr>';

    echo '<tr>
            <td>' . _('Batch, Serial or Lot Control') . ':</td>
            <td><select name="Controlled">';

    if ($_POST['Controlled']==0){
        echo '<option selected="selected" value="0">' . _('No Control') . '</option>';
    } else {
            echo '<option value="0">' . _('No Control') . '</option>';
    }
    if ($_POST['Controlled']==1){
        echo '<option selected="selected" value="1">' . _('Controlled'). '</option>';
    } else {
        echo '<option value="1">' . _('Controlled'). '</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><td>' . _('Serialised') . ':</td><td><select ' . (in_array('Serialised',$Errors) ?  'class="selecterror"' : '' ) .'  name="Serialised">';

    if ($_POST['Serialised']==0){
            echo '<option selected="selected" value="0">' . _('No'). '</option>';
    } else {
            echo '<option value="0">' . _('No'). '</option>';
    }
    if ($_POST['Serialised']==1){
            echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
    } else {
            echo '<option value="1">' . _('Yes'). '</option>';
    }
    echo '</select><i>' . _('Note') . ', ' . _('this has no effect if the item is not Controlled') . '</i></td>
        </tr>';

    if ($_POST['Serialised']==1 AND $_POST['MBFlag']=='M'){
        echo '<tr>
                <td>' . _('Next Serial No (>0 for auto numbering)') . ':</td>
                <td><input ' . (in_array('NextSerialNo',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="NextSerialNo" size="15" maxlength="15" value="' . $_POST['NextSerialNo'] . '" /></td></tr>';
    } else {
        echo '<tr><td><input type="hidden" name="NextSerialNo" value="0" /></td></tr>';
    }

    echo '<tr>
            <td>' . _('Perishable') . ':</td>
            <td><select name="Perishable">';

    if (!isset($_POST['Perishable']) OR  $_POST['Perishable']==0){
            echo '<option selected="selected" value="0">' . _('No'). '</option>';
    } else {
            echo '<option value="0">' . _('No'). '</option>';
    }
    if (isset($_POST['Perishable']) AND $_POST['Perishable']==1){
            echo '<option selected="selected" value="1">' . _('Yes'). '</option>';
    } else {
            echo '<option value="1">' . _('Yes'). '</option>';
    }
    echo '</select></td></tr>';

    echo '<tr>
            <td>' . _('Decimal Places for display Quantity') . ':</td>
            <td><input type="text" class="number" name="DecimalPlaces" size="1" maxlength="1" value="' . $_POST['DecimalPlaces'] . '" /></td>
        </tr>';

    if (isset($_POST['BarCode'])) {
        $BarCode = $_POST['BarCode'];
    } else {
        $BarCode='';
    }
    echo '<tr>
            <td>' . _('Bar Code') . ':</td>
            <td><input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="BarCode" size="22" maxlength="20" value="' . $BarCode . '" /></td>
        </tr>';

    if (isset($_POST['DiscountCategory'])) {
        $DiscountCategory = $_POST['DiscountCategory'];
    } else {
        $DiscountCategory='';
    }
    echo '<tr>
            <td>' . _('Discount Category') . ':</td>
            <td><input type="text" name="DiscountCategory" size="2" maxlength="2" value="' . $DiscountCategory . '" /></td>
        </tr>';

    echo '<tr>
            <td>' . _('Tax Category') . ':</td>
            <td><select name="TaxCat">';
    $sql = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
    $result = DB_query($sql);

    if (!isset($_POST['TaxCat'])){
        $_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
    }

    while ($myrow = DB_fetch_array($result)) {
        if ($_POST['TaxCat'] == $myrow['taxcatid']){
            echo '<option selected="selected" value="' . $myrow['taxcatid'] . '">' . $myrow['taxcatname'] . '</option>';
        } else {
            echo '<option value="' . $myrow['taxcatid'] . '">' . $myrow['taxcatname'] . '</option>';
        }
    } //end while loop

    echo '</select></td>
        </tr>';

    echo '<tr>
            <td>' . _('Pan Size') . ':</td>
            <td><input type="text" class="number" name="Pansize" size="6" maxlength="6" value="' . locale_number_format($_POST['Pansize'],0) . '" /></td>
        </tr>
         <tr>
            <td>' . _('Shrinkage Factor') . ':</td>
            <td><input type="text" class="number" name="ShrinkFactor" size="6" maxlength="6" value="' . locale_number_format($_POST['ShrinkFactor'],0) . '" /></td>
        </tr>';

    echo '</table>
        <div class="centre">';

    if (!isset($_POST['CategoryID'])) {
        $_POST['CategoryID'] = '';
    }

    $sql = "SELECT stkcatpropid,
                    label,
                    controltype,
                    defaultvalue,
                    numericvalue,
                    minimumvalue,
                    maximumvalue
            FROM stockcatproperties
            WHERE categoryid ='" . $_POST['CategoryID'] . "'
            AND reqatsalesorder =0
            ORDER BY stkcatpropid";

    $PropertiesResult = DB_query($sql);
    $PropertyCounter = 0;
    $PropertyWidth = array();

    if (DB_num_rows($PropertiesResult)>0) {
    echo '<br />
        <table class="selection">';
        echo '<tr>
                <th colspan="2">' . _('Item Category Properties') . '</th>
            </tr>';

        while ($PropertyRow=DB_fetch_array($PropertiesResult)){

            if (isset($_POST['StockID']) && !empty($_POST['StockID'])) {
                $PropValResult = DB_query("SELECT value FROM
                                            stockitemproperties
                                            WHERE stockid='" . $_POST['StockID'] . "'
                                            AND stkcatpropid ='" . $PropertyRow['stkcatpropid']."'");
                $PropValRow = DB_fetch_row($PropValResult);
                $PropertyValue = $PropValRow[0];
            } else {
                $PropertyValue =  '';
            }
            echo '<tr>
                    <td>';
                    echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value="' .$PropertyRow['stkcatpropid'] .'" />';
                    echo '<input type="hidden" name="PropNumeric' . $PropertyCounter . '" value="' .$PropertyRow['numericvalue'] .'" />';
                    echo $PropertyRow['label'] . '</td>

                    <td>';
            switch ($PropertyRow['controltype']) {
                case 0; //textbox
                    if ($PropertyRow['numericvalue']==1) {
                        echo '<input type="hidden" name="PropMin' . $PropertyCounter . '" value="' . $PropertyRow['minimumvalue'] . '" />';
                        echo '<input type="hidden" name="PropMax' . $PropertyCounter . '" value="' . $PropertyRow['maximumvalue'] . '" />';

                        echo '<input type="text" class="number" name="PropValue' . $PropertyCounter . '" size="20" maxlength="100" value="' . locale_number_format($PropertyValue,'Variable') . '" />';
                        echo _('A number between') . ' ' . locale_number_format($PropertyRow['minimumvalue'],'Variable') . ' ' . _('and') . ' ' . locale_number_format($PropertyRow['maximumvalue'],'Variable') . ' ' . _('is expected');
                    } else {
                        echo '<input type="text" name="PropValue' . $PropertyCounter . '" size="20" maxlength="100" value="' . $PropertyValue . '" />';
                    }
                    break;
                case 1; //select box
                    $OptionValues = explode(',',$PropertyRow['defaultvalue']);
                    echo '<select name="PropValue' . $PropertyCounter . '">';
                    foreach ($OptionValues as $PropertyOptionValue){
                        if ($PropertyOptionValue == $PropertyValue){
                            echo '<option selected="selected" value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
                        } else {
                            echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
                        }
                    }
                    echo '</select>';
                    break;
                case 2; //checkbox
                    echo '<input type="checkbox" name="PropValue' . $PropertyCounter . '"';
                    if ($PropertyValue==1){
                        echo 'checked';
                    }
                    echo ' />';
                    break;
            } //end switch
            echo '<input type="hidden" name="PropType' . $PropertyCounter .'" value="' . $PropertyRow['controltype'] . '" />';
            echo '</td></tr>';
            $PropertyCounter++;

        } //end loop round properties for the item category
       unset($StockID);
        echo '</table>';
    }
    echo '<input type="hidden" name="PropertyCounter" value="' . $PropertyCounter . '" />';
    echo '<br />';

	echo '<input type="submit" name="submit" value="' . _('Insert New Item') . '" />';
	echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';

//}
echo '</div>
    </div>
	</form>';
include('includes/footer.php');

?>
