<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

error_reporting(7);

define("InstallDatabaseHost","localhost");
define("InstallDatabaseName","");
define("InstallDatabaseUser","root");
define("InstallDatabasePass","");
define("InstallConfigFilename","../phpc/config.php");

/**************************** System Installation *****************************/

define("PhpcLocale",isset($_REQUEST["locale"])?$_REQUEST["locale"]:"");

require "../phpc/constant.php";
require "../phpc/language.php";
require "../phpc/function.php";
require "../phpc/filesyst.php";
require "../phpc/database.php";
require "../phpc/compiler.php";
require "../phpc/backcomp.php";

require "../admin/constant.php";
require "../admin/controls.php";
require "../admin/function.php";

define("PhpcRoot",normalizeAdminPath("/../",true));

$fileSystem=new FileSystem;
$compiler=new Compiler;

outputStart();

$action=acceptStringParameter("action");
if($action=="") $action="intro";
if($fileSystem->isFileExists(InstallConfigFilename)) $action="already";

makeInstallerPage("header");

/******************************** Introduction ********************************/

if($action=="intro") {
  makeNotification("admin_installer_intro");
  makeBreak();
  makeLinks(array("admin_installer_nextstep"=>"index.php?action=input"));
}

/*********************************** Input ************************************/

if($action=="input") {
  $database=Database::getDatabaseInformation();
  if(!$database) makeAdminError("admin_installer_checknodb");
  $admin=generatePassword(8,true,true,false);
  $encode=generatePassword(16);
  $locales=array(""=>$language["admin_installer_inputlocaleall"]);
  $locales+=$language["locales"];
  makeNotification("admin_installer_input");
  makeBreak();
  makeForm("header","admin_installer_inputform","index","check");
  makeFormInput("admin_installer_inputadmin","admin_installer_inputadmindesc","admin",$admin);
  makeFormInput("admin_installer_inputhost","admin_installer_inputhostdesc","host",InstallDatabaseHost);
  makeFormInput("admin_installer_inputdb","admin_installer_inputdbdesc","db",InstallDatabaseName);
  makeFormInput("admin_installer_inputuser","admin_installer_inputuserdesc","user",InstallDatabaseUser);
  makeFormInput("admin_installer_inputpass","admin_installer_inputpassdesc","pass",InstallDatabasePass);
  if($compiler->isPluginInstalled("dbprefix"))
    makeFormInput("admin_installer_inputprefix","admin_installer_inputprefixdesc","prefix");
  makeFormInput("admin_installer_inputencode","admin_installer_inputencodedesc","encode",$encode);
  makeFormChooser("admin_installer_inputlocale","admin_installer_inputlocaledesc","locale",$locale,$locales);
  if($compiler->isPluginInstalled("unicode"))
    makeFormYesNo("admin_installer_inputunicode","admin_installer_inputunicodedesc","unicode");
  makeForm("footer");
  makeBreak();
  makeLinks(array("admin_installer_firststep"=>"index.php"));
}

/*********************************** Check ************************************/

if($action=="check") {
  makeNotification("admin_installer_check");
  $admin=acceptStringParameter("admin");
  $host=acceptStringParameter("host");
  $db=acceptStringParameter("db");
  $user=acceptStringParameter("user");
  $pass=acceptStringParameter("pass");
  $prefix=acceptAlphaParameter("prefix");
  $encode=acceptStringParameter("encode");
  $locale=acceptStringParameter("locale");
  $unicode=acceptIntParameter("unicode",0,1);
  $charset=$unicode?"utf8":$language["charset_sql"];
  $params=compact("host","db","user","pass","charset");
  $database=Database::getDatabaseInformation($params);
  $success=$database && ifset($database["connected"]);
  makeNotification($success?"admin_installer_checksuccess":"admin_installer_checkfailure");
  if($success) {
    makeBreak();
    makeNotification("admin_installer_checkreport");
    makeBreak();
    makeNotification("admin_installer_checkadmin",$admin);
    makeNotification("admin_installer_checkhost",$host);
    makeNotification("admin_installer_checkdb",$db);
    makeNotification("admin_installer_checkuser",$user);
    makeNotification("admin_installer_checkpass",$pass);
    if($prefix!="") makeNotification("admin_installer_checkprefix",$prefix);
    makeBreak();
    makePromptForm("header","admin_installer_checkprompt","index","save");
    makeFormHidden("admin",$admin);
    makeFormHidden("host",$host);
    makeFormHidden("db",$db);
    makeFormHidden("user",$user);
    makeFormHidden("pass",$pass);
    makeFormHidden("prefix",$prefix);
    makeFormHidden("encode",$encode);
    makeFormHidden("locale",$locale);
    makeFormHidden("unicode",$unicode);
    makeFormHidden("charset",ifset($database["charset"]));
    makePromptForm("footer");
  }
  makeBreak();
  makeLinks(array("admin_installer_firststep"=>"index.php"));
}

/************************************ Save ************************************/

if($action=="save") {
  $admin=acceptStringParameter("admin");
  $host=acceptStringParameter("host");
  $db=acceptStringParameter("db");
  $user=acceptStringParameter("user");
  $pass=acceptStringParameter("pass");
  $prefix=acceptStringParameter("prefix");
  $encode=acceptStringParameter("encode");
  $locale=acceptStringParameter("locale");
  $unicode=acceptIntParameter("unicode",0,1);
  $charset=acceptStringParameter("charset");
  $plugins=array();
  if($prefix!="") $plugins[]="dbprefix";
  if($unicode) $plugins[]="unicode";
  $content=
    "<?php\r\n\r\n".
    "define(\"DatabaseHost\",".quoteText($host).");\r\n".
    "define(\"DatabaseName\",".quoteText($db).");\r\n".
    "define(\"DatabaseUser\",".quoteText($user).");\r\n".
    "define(\"DatabasePass\",".quoteText($pass).");\r\n";
  if($prefix!="") $content.=
    "define(\"DatabasePrefix\",".quoteText($prefix).");\r\n";
  if($charset!="") $content.=
    "define(\"DatabaseCharset\",".quoteText($charset).");\r\n";
  $content.=
    "\r\ndefine(\"EncodingPrefix\",".quoteText($encode).");\r\n".
    "define(\"PhpcLocale\",".quoteText($locale).");\r\n";
  if(PhpcRoot!="/") $content.=
    "define(\"PhpcRoot\",".quoteText(PhpcRoot).");\r\n";
  if(count($plugins)) $content.=
    "define(\"PhpcPreloadPlugins\",".quoteText(implode(",",$plugins)).");\r\n";
  $content.=
    "\r\n\$adminAccessRights=array(".quoteText($admin)."=>\"*\");\r\n\r\n?>\r\n";
  $fileSystem->saveFile(InstallConfigFilename,$content);
  $success=$fileSystem->isFileExists(InstallConfigFilename);
  makeNotification($success?"admin_installer_savesuccess":"admin_installer_savefailure");
  if($success) {
    makeBreak();
    makeNotification("admin_installer_notice");
    makeBreak();
    makeLinks(array("admin_installer_admin"=>normalizeAdminPath()));
  }
}

/***************************** Already Installed ******************************/

if($action=="already") {
  makeNotification("admin_installer_already");
  makeBreak();
  makeNotification("admin_installer_notice");
  makeBreak();
  makeLinks(array("admin_installer_admin"=>normalizeAdminPath()));
}

/******************************************************************************/

makeInstallerPage("footer");

?>
