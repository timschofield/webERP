<?php
/* $Revision: 1.5 $ */
$PageSecurity =10;
include('includes/session.inc');
$title = _('System Check');
include('includes/header.inc');

/* parse php modules from phpinfo */
function parsePHPModules() {
 ob_start();
 phpinfo(INFO_MODULES);
 $s = ob_get_contents();
 ob_end_clean();

 $s = strip_tags($s,'<h2><th><td>');
 $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
 $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
 $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
 $vModules = array();
 for ($i=1;$i<count($vTmp);$i++) {
  if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
   $vName = trim($vMat[1]);
   $vTmp2 = explode("\n",$vTmp[$i+1]);
   foreach ($vTmp2 AS $vOne) {
   $vPat = '<info>([^<]+)<\/info>';
   $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
   $vPat2 = "/$vPat\s*$vPat/";
   if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
     $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
   } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
     $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
   }
   }
  }
 }
 return $vModules;
}

/** get a module setting */
function getModuleSetting($pModuleName,$pSetting) {
 $vModules = parsePHPModules();
 return $vModules[$pModuleName][$pSetting];
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('System Check') . '" alt="">' . ' ' . _('System Check') . '</p>';
echo '<div class="system_check">';
echo '<h3>PHP Settings</h3>';
echo '<p>PHP version: ' . phpversion();
echo '<p>GD Module: ' . getModuleSetting('gd','GD Version') . '</p>';
echo '<p>MYSQL Module: ' . getModuleSetting('mysql','Client API version') . '</p>';
echo '<p>MySQL character set: '.mysql_client_encoding();
echo '<p>Zlib: ' . getModuleSetting('zlib','ZLib Support') . '</p>';
echo '<p>Simple XML: ' . getModuleSetting('SimpleXML','Revision') . '</p>';
echo '<h3>Linux System Settings</h3>';
ob_start();
echo "<p><b>Memory Free</b></br>";
passthru('free');
echo "<p><b>CPU type</b></br>";
passthru('cat /proc/cpuinfo | grep "model name" ');
echo "<p><b>Disk Space Free</b><br>";
system('df');
echo "<p><b>webERP Disk Space Usage</b><br>";
system('du -sh');
echo "<p>";
$fr= ob_get_contents();
ob_end_clean();

echo '<pre>' . $fr . '</pre>';

include('includes/footer.inc');
?>
