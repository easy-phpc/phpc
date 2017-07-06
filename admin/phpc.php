<?php

// PHPC Control Panel Plugin (40.5) - PHPC Control Center v1.0 by Dagdamor

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

/*

// Main Menu Construction Code
if($database->isTablePresent("styles")) {
  if(isAdministrator()) {
    makeMenuGroup("header","phpc_menu");
    makeMenuItem("phpc_manual","phpc.php?action=manual");
    makeMenuItem("phpc_statistics","phpc.php?action=statistics");
    makeMenuItem("phpc_export","phpc.php?action=export");
    makeMenuItem("phpc_import","phpc.php?action=import");
    makeMenuItem("phpc_check","phpc.php?action=check");
    makeMenuItem("phpc_clearcache","phpc.php?action=clearcache");
    makeMenuGroup("footer");
  }
  makeMenuGroup("header","phpcp_menu");
  makeMenuItem("phpc_add","phpc.php?action=addpage");
  makeMenuItem("phpc_addgroup","phpc.php?action=addpagegroup");
  makeMenuItem("phpc_modify","phpc.php?action=modifypages");
  makeMenuItem("phpc_addpair","phpc.php?action=addpair");
  if(PhpcLocale=="") makeMenuItem("phpc_languages","phpc.php?action=modifylanguages");
  makeMenuGroup("footer");
  makeMenuGroup("header","phpct_menu");
  makeMenuItem("phpc_add","phpc.php?action=addtemplate");
  makeMenuItem("phpc_addgroup","phpc.php?action=addtemplategroup");
  makeMenuItem("phpc_modify","phpc.php?action=modifytemplates");
  makeMenuItem("phpc_search","phpc.php?action=searchtemplates");
  makeMenuItem("phpc_replace","phpc.php?action=replacetemplates");
  makeMenuGroup("footer");
  makeMenuGroup("header","phpcb_menu");
  makeMenuItem("phpc_add","phpc.php?action=addbundle");
  makeMenuItem("phpc_addgroup","phpc.php?action=addbundlegroup");
  makeMenuItem("phpc_modify","phpc.php?action=modifybundles");
  makeMenuItem("phpc_search","phpc.php?action=searchbundles");
  makeMenuItem("phpc_replace","phpc.php?action=replacebundles");
  makeMenuGroup("footer");
  if(isAdministrator()) {
    makeMenuGroup("header","phpcr_menu");
    makeMenuItem("phpc_add","phpc.php?action=addreplacement");
    makeMenuItem("phpc_modify","phpc.php?action=modifyreplacements");
    makeMenuGroup("footer");
    makeMenuGroup("header","phpcf_menu");
    makeMenuItem("phpc_add","phpc.php?action=addformatting");
    makeMenuItem("phpc_modify","phpc.php?action=modifyformatting");
    makeMenuGroup("footer");
    makeMenuGroup("header","phpcl_menu");
    makeMenuItem("phpc_add","phpc.php?action=addlinkstyle");
    makeMenuItem("phpc_modify","phpc.php?action=modifylinkstyles");
    makeMenuGroup("footer");
    makeMenuGroup("header","phpcs_menu");
    makeMenuItem("phpc_add","phpc.php?action=addstyle");
    makeMenuItem("phpc_modify","phpc.php?action=modifystyles");
    makeMenuItem("phpc_assign","phpc.php?action=assignstyles");
    makeMenuItem("phpc_addtemplateset","phpc.php?action=addtemplateset");
    makeMenuItem("phpc_addbundleset","phpc.php?action=addbundleset");
    makeMenuItem("phpc_addreplacementset","phpc.php?action=addreplacementset");
    makeMenuGroup("footer");
  }
}
else {
  makeMenuGroup("header","phpc_menu");
  makeMenuItem("admin_install","phpc.php?action=install");
  makeMenuGroup("footer");
}

*/

require "global.php";
require_once "../plugins/colorer.php";
require_once "../plugins/zipfile.php";

define("PhpcSearchInherited",0);
define("PhpcSearchCurrentSet",1);
define("PhpcSearchAllSets",2);

$infoPages=
  "id:key,name:input:phpc_addeditpage_name:phpc_addeditpage_namedesc,".
  "alias:input:phpc_addeditpage_alias:phpc_addeditpage_aliasdesc,".
  "parentid:chooser:phpc_addeditpage_parent:phpc_addeditpage_parentdesc,".
  "template:input:phpc_addeditpage_template:phpc_addeditpage_templatedesc,".
  "bundles:input:phpc_addeditpage_bundles:phpc_addeditpage_bundlesdesc%s,".
  "params:input:phpc_addeditpage_params:phpc_addeditpage_paramsdesc,".
  "visible:yesno:phpc_addeditpage_visible";
$infoPageGroups=
  "id:key,title:input:phpc_addeditpagegroup_title,".
  "prefix:input:phpc_addeditpagegroup_prefix:phpc_addeditpagegroup_prefixdesc,".
  "displayorder:input:phpc_addeditpagegroup_displayorder";
$infoTemplates=
  "id:key,setid:hidden,name:input:phpc_addedittemplate_name,".
  "parent:input:phpc_addedittemplate_parent,".
  "content:tpleditor:phpc_addedittemplate_content,".
  "filedata:file:phpc_addedittemplate_filedata";
$infoTemplateGroups=
  "id:key,title:input:phpc_addedittemplategroup_title,".
  "prefix:input:phpc_addedittemplategroup_prefix:phpc_addedittemplategroup_prefixdesc,".
  "displayorder:input:phpc_addedittemplategroup_displayorder";
$infoBundles=
  "id:key,setid:hidden,name:input:phpc_addeditbundle_name,".
  "plugins:input:phpc_addeditbundle_plugins,".
  "content:phpeditor:phpc_addeditbundle_content,".
  "filedata:file:phpc_addeditbundle_filedata";
$infoBundleGroups=
  "id:key,title:input:phpc_addeditbundlegroup_title,".
  "prefix:input:phpc_addeditbundlegroup_prefix:phpc_addeditbundlegroup_prefixdesc,".
  "displayorder:input:phpc_addeditbundlegroup_displayorder";
$infoReplacements=
  "id:key,setid:hidden,name:input:phpc_addeditreplacement_name,".
  "content:textarea:phpc_addeditreplacement_content";
$infoFormatting=
  "id:key,title:input:phpc_addeditformatting_title,".
  "class:input:phpc_addeditformatting_class:phpc_addeditformatting_classdesc,".
  "pattern:input:phpc_addeditformatting_pattern:phpc_addeditformatting_patterndesc,".
  "content:textarea:phpc_addeditformatting_content:phpc_addeditformatting_contentdesc,".
  "callback:input:phpc_addeditformatting_callback:phpc_addeditformatting_callbackdesc,".
  "sample:textarea:phpc_addeditformatting_sample,".
  "useorder:input:phpc_addeditformatting_useorder";
$infoLinkStyles=
  "id:key,pageid:chooser:phpc_addeditlinkstyle_page,".
  "pattern:input:phpc_addeditlinkstyle_pattern:phpc_addeditlinkstyle_patterndesc,".
  "assign:input:phpc_addeditlinkstyle_assign:phpc_addeditlinkstyle_assigndesc,".
  "useorder:input:phpc_addeditlinkstyle_useorder";
$infoSets=
  "id:key,title:input:phpc_addeditset_title,".
  "parentid:chooser:phpc_addeditset_parent:phpc_addeditset_parentdesc";
$infoStyles=
  "id:key,title:input:phpc_addeditstyle_title,".
  "templatesetid:chooser:phpc_addeditstyle_templateset:phpc_addeditstyle_note,".
  "bundlesetid:chooser:phpc_addeditstyle_bundleset:phpc_addeditstyle_note,".
  "replacementsetid:chooser:phpc_addeditstyle_replacementset:phpc_addeditstyle_note,".
  "host:input:phpc_addeditstyle_host:phpc_addeditstyle_hostdesc,".
  "folder:input:phpc_addeditstyle_folder:phpc_addeditstyle_folderdesc,".
  "visible:yesno:phpc_addeditstyle_visible";

$localizedFields=array(
  "title"=>array("offset"=>6,"type"=>"input","title"=>"phpc_addeditpage_title"));

adminLog("id,locale");
$action=acceptStringParameter("action");

makeAdminPage("header");

/********************************** Samples ***********************************/

$sampleTemplates=array();
$sampleBundles=array();

$sampleTemplates["general"]=<<<EOF
<?-- $language[phpc_deftemplate_general1] --?>
<?-- $language[phpc_deftemplate_general2] --?>
<?-- $language[phpc_deftemplate_general3] --?>

<insert:htmlDesign currentScope>
<area:content>

<var:language:phpc_deftemplate_general4 nofilter><br>

</area:content>
</insert:htmlDesign>
EOF;

$sampleTemplates["index"]=<<<EOF
<?-- $language[phpc_deftemplate_index1] --?>

<area:content>

<var:language:phpc_deftemplate_index2 nofilter><br>

</area:content>
EOF;

$sampleTemplates["404"]=<<<EOF
<?-- $language[phpc_deftemplate_404a] --?>

<area:content>

<var:language:phpc_deftemplate_404b nofilter><br>

</area:content>
EOF;

$sampleTemplates["htmlDesign"]=<<<EOF
<?-- $language[phpc_deftemplate_htmldesign1] --?>
<?-- $language[phpc_deftemplate_htmldesign2] --?>

<html>
<head>
<title><var:currentPage:title></title>
<insert:htmlStyles/>
</head>

<body>
<var:content>
</body>
</html>
EOF;

$sampleTemplates["htmlStandardStyles"]=<<<EOF
<?-- $language[phpc_deftemplate_htmlstandardstyles1] --?>
<?-- $language[phpc_deftemplate_htmlstandardstyles2] --?>

<style type="text/css">
table.content { width:100%; height:95%; border:0px; }
table.message { width:60%; border:1px solid black; }
div.message { padding:10px; font:13px/16px tahoma; text-align:center; }
div.link { padding:0px 10px 10px 10px; font:11px/13px tahoma; text-align:center; }
</style>
EOF;

$sampleTemplates["htmlStyles"]=<<<EOF
<?-- $language[phpc_deftemplate_htmlstyles1] --?>
<?-- $language[phpc_deftemplate_htmlstyles2] --?>

<style type="text/css">
</style>
EOF;

$sampleTemplates["standardError"]=<<<EOF
<?-- $language[phpc_deftemplate_standarderror1] --?>

<html>
<head>
<title><var:language:phpc_deftemplate_standarderror2 nofilter></title>
<insert:htmlStandardStyles/>
</head>

<body>
<table align="center" class="content"><tr><td>
<table align="center" class="message"><tr><td>

<div class="message"><var:message></div>
<div class="link"><a href="javascript:history.back(1)"><var:language:phpc_deftemplate_standarderror3 nofilter></a></div>

</td></tr></table>
</td></tr></table>
</body>
</html>
EOF;

$sampleTemplates["standardRedirect"]=<<<EOF
<?-- $language[phpc_deftemplate_standardredirect1] --?>

<html>
<head>
<meta http-equiv="refresh" content="1; url=<var:link>">
<title><var:language:phpc_deftemplate_standardredirect2 nofilter></title>
<insert:htmlStandardStyles/>
</head>

<body>
<table align="center" class="content"><tr><td>
<table align="center" class="message"><tr><td>

<div class="message"><var:message></div>
<div class="link"><a href="<var:link>"><var:language:phpc_deftemplate_standardredirect3 nofilter></a></div>

</td></tr></table>
</td></tr></table>
</body>
</html>
EOF;

$sampleBundles["general"]=<<<EOF
// $language[phpc_defbundle_general]

if(!\$settings["siteOpen"]) \$compiler->standardError(\$language["phpc_error_siteclosed"]);
if(isClientBanned(\$settings["siteBlackList"])) \$settings["siteEnabled"]=false;
EOF;

$sampleBundles["actionGeneral"]=<<<EOF
// $language[phpc_defbundle_actiongeneral]

\$action=acceptStringParameter("action");

switch(\$action) {
  case "setlocale":
    \$locale=acceptStringParameter("locale",100);
    setPhpcLocale(\$locale);
    redirectBack();
  case "setstyle":
    \$styleid=acceptIntParameter("styleid");
    \$compiler->updateStyle(\$styleid);
    redirectBack();
}

redirect("/");
EOF;

/********************************* Functions **********************************/

function createDefaultFormatting()
{
  return array(
    array("title"=>"phpc_defformatting_title1","sample"=>"phpc_defformatting_sample1","pattern"=>"\[(/?[biu])\]","content"=>"<\\1>"),
    array("title"=>"phpc_defformatting_title2","sample"=>"phpc_defformatting_sample2","pattern"=>"\[url(=[^\]]*)?\](.*?)\[/url\]","callback"=>"Formatter.processTagUrl"),
    array("title"=>"phpc_defformatting_title3","sample"=>"phpc_defformatting_sample3","pattern"=>"\[email(=[^\]]*)?\](.*?)\[/email\]","callback"=>"Formatter.processTagEmail"));
}

function getPrefixGroupingOptions($titleOnce, $titleMany, $skipEmpty=false)
{
  return array(
    "itemField"=>"name",
    "groupField"=>"prefix",
    "groupFieldDefault"=>"",
    "groupTitleOnce"=>$titleOnce,
    "groupTitleMany"=>$titleMany,
    "caselessCompare"=>true,
    "prefixCompare"=>true,
    "skipEmpty"=>$skipEmpty,
    "skipUngrouped"=>false);
}

function createSimpleGroupingArray($table)
{
  global $database;
  $result=array();
  $items=$database->getLines($table);
  foreach($items as $item) $result[phpcstrtolower($item["name"])]=$item;
  ksort($result);
  return $result;
}

function createSimpleGroupingTree($tableGroups, $tableItems, $titleOnce, $titleMany)
{
  global $database;
  $groups=$database->getOrderedLines($tableGroups,"displayorder");
  $items=createSimpleGroupingArray($tableItems);
  $options=getPrefixGroupingOptions($titleOnce,$titleMany);
  return createGroupingTree($groups,$items,$options);
}

function createInheritanceChain($subject, $setid=false)
{
  global $database;
  $result=array();
  if($setid===false) {
    $style=getAdminStyle();
    $setid=$style["{$subject}setid"];
  }
  $sets=$database->getLines("{$subject}sets");
  $sets=extractArrayColumns($sets,"id","parentid");
  while($setid) {
    if(in_array($setid,$result)) break;
    $result[]=$setid;
    $setid=ifset($sets[$setid],0);
  }
  return $result;
}

function createInheritanceGroupingArray($table, $setChain, $fields="id,name")
{
  global $database;
  $result=array();
  foreach($setChain as $setIndex=>$setid) {
    $items=$database->customQuery("SELECT $fields FROM $table WHERE setid=$setid");
    foreach($items as $item) {
      $name=phpcstrtolower($item["name"]);
      if(isset($result[$name])) {
        if($result[$name]["inheritance"]=="default")
          $result[$name]["inheritance"]="inherited";
        continue;
      }
      $result[$name]=$item;
      $result[$name]["inheritance"]=$setIndex?"original":"default";
    }
  }
  ksort($result);
  return $result;
}

function createInheritanceGroupingTree($tableGroups, $tableItems, $setChain, $titleOnce, $titleMany)
{
  global $database;
  $groups=$database->getOrderedLines($tableGroups,"displayorder");
  $items=createInheritanceGroupingArray($tableItems,$setChain);
  $options=getPrefixGroupingOptions($titleOnce,$titleMany);
  return createGroupingTree($groups,$items,$options);
}

function createTemplateContent($template)
{
  global $syntaxColorer;
  $result=$syntaxColorer->processPhpcTemplate($template["content"]);
  $setChain=createInheritanceChain("template");
  $templates=createInheritanceGroupingArray("templates",$setChain);
  $pattern=phpcpattern("{(?<=<insert:)\w+}");
  preg_match_all($pattern,$template["content"],$matches);
  $names=array_unique($matches[0]);
  $translate=array();
  foreach($names as $name) {
    if(!$item=ifset($templates[phpcstrtolower($name)])) continue;
    $link="phpc.php?action=viewtemplate&id=$item[id]";
    $search="&lt;insert:$name";
    $replace="&lt;insert:<a href=\"$link\">$name</a>";
    $translate[$search]=$replace;
    $search="&lt;/insert:$name";
    $replace="&lt;/insert:<a href=\"$link\">$name</a>";
    $translate[$search]=$replace;
  }
  return strtr($result,$translate);
}

function searchMatchingGroup($table, $name, $strict=false)
{
  global $database;
  $groups=$database->getOrderedLines($table,"displayorder");
  $method="searchArrayKey".($strict?"":"ByPrefix");
  $key=$method($groups,"prefix",$name,true);
  return $key!==false?$groups[$key]["id"]:0;
}

function createSearchPattern($text, $case, $words, $regexp)
{
  $pattern=$regexp?$text:preg_quote($text);
  if($words) $pattern="\b$pattern\b";
  $pattern="{{$pattern}}".($case?"ms":"ims");
  return phpcpattern($pattern);
}

function processSearch($subject, $pattern, $area, $grouping=false, $titleOnce="", $titleMany="")
{
  global $database;
  $result=array();
  if($area==PhpcSearchInherited) {
    $setChain=createInheritanceChain($subject);
    $inheritance=createInheritanceGroupingArray("{$subject}s",$setChain);
    $inheritance=extractArrayColumns($inheritance,"id","inheritance");
    $conditions=$database->getListCondition("id",array_keys($inheritance));
    $items=$database->getLines("{$subject}s",$conditions);
    foreach($items as $item) {
      $name=phpcstrtolower($item["name"]);
      $result[$name]=$item;
      $result[$name]["inheritance"]=$inheritance[$item["id"]];
    }
  }
  else {
    if($area==PhpcSearchCurrentSet) {
      $style=getAdminStyle();
      $setid=$style["{$subject}setid"];
      $conditions="setid=$setid";
    }
    else $conditions="";
    $items=$database->getLines("{$subject}s",$conditions);
    foreach($items as $item) {
      $name=phpcstrtolower($item["name"]).chr(1).$item["setid"];
      $result[$name]=$item;
    }
  }
  foreach($result as $name=>$item) {
    preg_match_all($pattern,$item["content"],$matches);
    $count=count($matches[0]);
    if($count) $result[$name]["fragments"]=$count; else unset($result[$name]);
  }
  ksort($result);
  if(!$grouping) return $result;
  $groups=$database->getOrderedLines("{$subject}groups","displayorder");
  $options=getPrefixGroupingOptions($titleOnce,$titleMany,true);
  return createGroupingTree($groups,$result,$options);
}

// ############################################################################
// ############################# General Section ##############################
// ############################################################################

/**************************** Plugin Installation *****************************/

if($action=="install") {
  checkPluginInstalled("styles");
  makeNotification("admin_installstart");
  $title=installTableLocales("title","TINYTEXT NOT NULL");
  $database->customQuery("CREATE TABLE templatesets (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "parentid INT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "PRIMARY KEY (id))");
  makeNotification("admin_installtable","templatesets");
  $database->customQuery("CREATE TABLE templates (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "setid INT NOT NULL,".
    "name TINYTEXT NOT NULL,".
    "parent TINYTEXT NOT NULL,".
    "content LONGTEXT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY setid (setid,name(50)))");
  makeNotification("admin_installtable","templates");
  $database->customQuery("CREATE TABLE templategroups (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "prefix TINYTEXT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "displayorder INT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY prefix (prefix(50)))");
  makeNotification("admin_installtable","templategroups");
  $database->customQuery("CREATE TABLE bundlesets (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "parentid INT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "PRIMARY KEY (id))");
  makeNotification("admin_installtable","bundlesets");
  $database->customQuery("CREATE TABLE bundles (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "setid INT NOT NULL,".
    "name TINYTEXT NOT NULL,".
    "plugins TINYTEXT NOT NULL,".
    "content LONGTEXT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY setid (setid,name(50)))");
  makeNotification("admin_installtable","bundles");
  $database->customQuery("CREATE TABLE bundlegroups (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "prefix TINYTEXT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "displayorder INT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY prefix (prefix(50)))");
  makeNotification("admin_installtable","bundlegroups");
  $database->customQuery("CREATE TABLE replacementsets (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "parentid INT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "PRIMARY KEY (id))");
  makeNotification("admin_installtable","replacementsets");
  $database->customQuery("CREATE TABLE replacements (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "setid INT NOT NULL,".
    "name TINYTEXT NOT NULL,".
    "content LONGTEXT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY setid (setid,name(50)))");
  makeNotification("admin_installtable","replacements");
  $database->customQuery("CREATE TABLE pages (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "parentid INT NOT NULL,".
    "name TINYTEXT NOT NULL,".
    "alias TINYTEXT NOT NULL,".
    "template TINYTEXT NOT NULL,".
    "bundles TINYTEXT NOT NULL,$title,".
    "params TINYTEXT NOT NULL,".
    "visible TINYINT(1) NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY name (name(50)))");
  makeNotification("admin_installtable","pages");
  $database->customQuery("CREATE TABLE pagegroups (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "prefix TINYTEXT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "displayorder INT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY prefix (prefix(50)))");
  makeNotification("admin_installtable","pagegroups");
  $database->customQuery("CREATE TABLE styles (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "templatesetid INT NOT NULL,".
    "bundlesetid INT NOT NULL,".
    "replacementsetid INT NOT NULL,".
    "title TINYTEXT NOT NULL,".
    "host TINYTEXT NOT NULL,".
    "folder TINYTEXT NOT NULL,".
    "visible TINYINT(1) NOT NULL,".
    "forusers TINYINT(1) NOT NULL,".
    "foradmin TINYINT(1) NOT NULL,".
    "PRIMARY KEY (id))");
  makeNotification("admin_installtable","styles");
  $database->customQuery("CREATE TABLE formatting (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "title TINYTEXT NOT NULL,".
    "class TINYTEXT NOT NULL,".
    "pattern TINYTEXT NOT NULL,".
    "content LONGTEXT NOT NULL,".
    "callback TINYTEXT NOT NULL,".
    "sample LONGTEXT NOT NULL,".
    "useorder INT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY pattern (pattern(50)),".
    "KEY useorder (useorder))");
  makeNotification("admin_installtable","formatting");
  $database->customQuery("CREATE TABLE linkstyles (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "pageid INT NOT NULL,".
    "pattern TINYTEXT NOT NULL,".
    "assign TINYTEXT NOT NULL,".
    "useorder INT NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY pattern (pattern(50)),".
    "KEY useorder (useorder))");
  makeNotification("admin_installtable","linkstyles");
  $database->customQuery("CREATE TABLE cachetemplates (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "styleid INT NOT NULL,".
    "template TINYTEXT NOT NULL,".
    "content LONGBLOB NOT NULL,".
    "compressed TINYINT(1) NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY styleid (styleid,template(50)))");
  makeNotification("admin_installtable","cachetemplates");
  $database->customQuery("CREATE TABLE cachebundles (".
    "id INT NOT NULL AUTO_INCREMENT,".
    "styleid INT NOT NULL,".
    "bundles TINYTEXT NOT NULL,".
    "plugins TINYTEXT NOT NULL,".
    "content LONGBLOB NOT NULL,".
    "compressed TINYINT(1) NOT NULL,".
    "PRIMARY KEY (id),".
    "UNIQUE KEY styleid (styleid,bundles(200)))");
  makeNotification("admin_installtable","cachebundles");
  $database->customQuery("CREATE TABLE sessions (".
    "hash CHAR(32) NOT NULL,".
    "ipaddress CHAR(15) NOT NULL,".
    "lastactivity INT NOT NULL,".
    "pageid INT NOT NULL,".
    "params CHAR(200) NOT NULL,".
    "PRIMARY KEY (hash),".
    "KEY ipaddress (ipaddress)) ENGINE=MEMORY");
  makeNotification("admin_installtable","sessions");
  $values=array("id"=>1,"parentid"=>0,"title"=>$language["phpc_defstyle"]);
  $database->addLineStrict("templatesets",$values);
  makeNotification("admin_installdata","templatesets");
  $database->addLineStrict("bundlesets",$values);
  makeNotification("admin_installdata","bundlesets");
  $database->addLineStrict("replacementsets",$values);
  makeNotification("admin_installdata","replacementsets");
  $values=array(
    "templatesetid"=>1,
    "bundlesetid"=>1,
    "replacementsetid"=>1,
    "title"=>$language["phpc_defstyle"],
    "visible"=>1,
    "forusers"=>1,
    "foradmin"=>1);
  $database->addLineStrict("styles",$values);
  makeNotification("admin_installdata","styles");
  $parents=array("index"=>"general","404"=>"general");
  installTemplates($sampleTemplates,$parents);
  installBundles($sampleBundles);
  installPage("general","","general","phpc_defpage_general",0,0);
  installPage("actionGeneral","","actionGeneral","phpc_defpage_actiongeneral");
  installPage("index","index","","phpc_defpage_index");
  installPage("404","404","","phpc_defpage_404");
  installFormatting(createDefaultFormatting());
  makeNotification("admin_installsuccess");
  makeBreak();
  makeRefreshMenuLink();
}

/****************************** Built-in Manual *******************************/

if($action=="manual") {
  $topic=acceptStringParameter("topic");
  if($topic=="") $topic="index";
  $link="phpc.php?action=manual&topic=%s";
  $manual=parsePhpcManual("/language/$locale/phpc_$locale.pcm",$link);
  if(count($manual)) {
    $key="phpc_manual";
    $language[$key]=ifset($manual[$topic]);
    makeNotification($key);
  }
  else makeNotification("phpc_error_nomanual");
}

/********************************* Statistics *********************************/

if($action=="statistics") {
  $totalPages=$database->getLinesCount("pages");
  $totalTemplates=$database->getLinesCount("templates");
  $totalBundles=$database->getLinesCount("bundles");
  $totalReplacements=$database->getLinesCount("replacements");
  $tablesOthers=
    "bundlegroups,bundlesets,formatting,linkstyles,".
    "pagegroups,replacementsets,templategroups,templatesets";
  $tablesOthers=explode(",",$tablesOthers);
  $totalOthers=0;
  foreach($tablesOthers as $table) $totalOthers+=$database->getLinesCount($table);
  $totalStyles=$database->getLinesCount("styles");
  $total=$totalPages+$totalTemplates+$totalBundles+$totalReplacements+$totalOthers+$totalStyles;
  if($total<50) $conclusion=1; else
  if($total<200) $conclusion=2; else
  if($total<1000) $conclusion=3; else
  if($total<5000) $conclusion=4; else $conclusion=5;
  $conclusion=format($language["phpc_stats_conclusion"],
    $language["phpc_stats_conclusion$conclusion"]);
  $statistics=array(
    "phpc_stats_phpversion"=>phpversion(),
    "phpc_stats_phpcversion"=>phpcversion(),
    "phpc_stats_dbtype"=>$database->title,
    "phpc_stats_dbversion"=>$database->getVersion(),
    "phpc_stats_pages"=>$totalPages,
    "phpc_stats_templates"=>$totalTemplates,
    "phpc_stats_bundles"=>$totalBundles,
    "phpc_stats_replacements"=>$totalReplacements,
    "phpc_stats_others"=>$totalOthers,
    "phpc_stats_styles"=>$totalStyles);
  $columns=array(
    array("title"=>"phpc_stats_name","width"=>"80%"),
    "phpc_stats_value");
  makeTable("header",$columns);
  foreach($statistics as $name=>$value) {
    makeTableCellSimple($name,array(),true);
    makeTableCellSimple($value);
  }
  makeTableCellExact($conclusion,array("colspan"=>2,"align"=>"center"));
  makeTable("footer");
  $links=array(
    "phpc_stats_diagnose"=>"phpc.php?action=diagnose",
    "phpc_stats_phpinfo"=>"_blank:phpc.php?action=phpinfo");
  makeBreak();
  makeLinks($links);
}

/****************************** Server Diagnose *******************************/

if($action=="diagnose") {
  $information=checkAdminServerConfig();
  $columns=array(
    array("title"=>"phpc_diagnose_name","width"=>"40%"),
    array("title"=>"phpc_diagnose_value","width"=>"20%"),
    "phpc_diagnose_comment");
  makeTable("header",$columns);
  foreach($information as $item) {
    $value=$item["value"];
    $color=$item["correct"]?"right":"wrong";
    if(is_bool($value)) $value=$language[$value?"common_on":"common_off"];
    $comment=$item["correct"]?"correct":$item["short"];
    makeTableCellSimple("[b]$item[name][/b]");
    makeTableCellSimple("[font=$color]{$value}[/font]");
    makeTableCellSimple($language["phpc_diagnose_$comment"],array("wrap"=>true));
  }
  makeTable("footer");
}

/****************************** PHP Information *******************************/

if($action=="phpinfo") {
  outputErase();
  phpinfo();
  halt();
}

/****************************** Database Export *******************************/

if($action=="export") {
  $tables=$database->getTablesList();
  $tables=combineArrays($tables,$tables);
  makeForm("header","phpc_export_form","phpc","doexport");
  makeFormSelector("phpc_export_tables","phpc_export_tablesdesc","tables[]",$tables);
  makeFormYesNo("phpc_export_structure","","structure");
  makeFormYesNo("phpc_export_data","","data");
  makeFormYesNo("phpc_export_pack","","pack",0);
  makeForm("footer");
  if($database->isLinePresent("styles","foradmin=1")) {
    $templateSetChain=createInheritanceChain("template");
    $bundleSetChain=createInheritanceChain("bundle");
    $templates=createInheritanceGroupingArray("templates",$templateSetChain);
    $bundles=createInheritanceGroupingArray("bundles",$bundleSetChain);
    $templates=extractArrayColumns($templates,"id","name");
    $bundles=extractArrayColumns($bundles,"id","name");
    $subjects=array("templates","bundles");
    foreach($subjects as $subject) if(count($$subject)) {
      makeBreak();
      makeForm("header","phpc_export_form$subject","phpc","doexport$subject");
      makeFormSelector("phpc_export_$subject","phpc_export_{$subject}desc","{$subject}[]",$$subject);
      makeFormYesNo("phpc_export_pack","","pack",0);
      makeForm("footer");
    }
  }
}

if($action=="doexport") {
  @set_time_limit(0);
  $tables=acceptArrayParameter("tables");
  $structure=acceptIntParameter("structure",0,1);
  $data=acceptIntParameter("data",0,1);
  $pack=acceptIntParameter("pack",0,1);
  $optimizer->clearCache();
  $alltables=$database->getTablesList();
  $filename=DatabaseName.".sql";
  $content="# PHP Compiler by Serge Igitov - Project Database Dump\r\n";
  foreach($alltables as $table) {
    if(count($tables) && !in_array($table,$tables)) continue;
    if($structure) $content.="\r\n".$database->exportTableStructure($table);
    if(!$data) continue;
    $lines=$database->getLines($table);
    if(count($lines)) $content.="\r\n";
    foreach($lines as $line) $content.=$database->exportTableLine($table,$line);
  }
  if($pack) {
    $zipfile=new ZipFile;
    $zipfile->addFile($filename,$content);
    $filename=DatabaseName.".zip";
    $content=$zipfile->file();
  }
  contentDisposition($filename,"application/octet-stream",$content);
}

if($action=="doexporttemplates" || $action=="doexportbundles") {
  @set_time_limit(0);
  $subject=substr($action,8);
  $selected=acceptArrayParameter($subject);
  $pack=acceptIntParameter("pack",0,1);
  $source=$database->getOrderedLines($subject,"name");
  $filename="$subject.sql";
  $content="";
  foreach($source as $item) {
    if(count($selected) && !in_array($item["id"],$selected)) continue;
    unset($item["id"]);
    $content.=$database->exportTableLine($subject,$item,true,true);
  }
  if($pack) {
    $zipfile=new ZipFile;
    $zipfile->addFile($filename,$content);
    $filename="$subject.zip";
    $content=$zipfile->file();
  }
  contentDisposition($filename,"application/octet-stream",$content);
}

/****************************** Database Import *******************************/

if($action=="import") {
  makeForm("header","phpc_import_form","phpc","doimport");
  makeFormFile("phpc_import_file","phpc_import_filedesc","file");
  makeForm("footer");
}

if($action=="doimport") {
  @set_time_limit(0);
  $file=$fileSystem->getUploadedFile("file");
  if($file) {
    $queries=$database->parseSQL($file["content"]);
    foreach($queries as $query) {
      $instruction=substr($file["content"],$query["offset"],$query["length"]);
      $success=$database->customQueryBoolean($instruction);
      if(!$success) {
        makeError("phpc_import_failure");
        makeBreak();
        makeQuote($instruction);
        makeAdminPage("footer");
      }
    }
    $optimizer->clearCache();
    $optimizer->clearFileCache();
    makeNotification("phpc_import_success",count($queries));
  }
  else makeError("phpc_import_empty");
}

/****************************** Integrity Check *******************************/

if($action=="check") {
  makePrompt("phpc_check_prompt","phpc.php?action=docheck");
}

if($action=="docheck") {
  makeNotification("phpc_check_start");
  $success=true;
  // Pages Constraints
  $errors=checkConstraints("pages","id","pages","id","parentid",0);
  foreach($errors as $id) {
    $database->modifyField("pages","parentid",0,"id=$id");
    makeError("phpc_check_invalidid",array("parentid","pages"));
    $success=false;
  }
  $errors=checkConstraints("pages","id","linkstyles","id","pageid");
  foreach($errors as $id) {
    $database->deleteLine("linkstyles","id=$id");
    makeError("phpc_check_invalidid",array("pageid","linkstyles"));
    $success=false;
  }
  $errors=checkConstraints("pages","id","sessions","hash","pageid");
  foreach($errors as $hash) {
    $database->deleteLine("sessions","hash=".slashes($hash));
    makeError("phpc_check_invalidid",array("pageid","sessions"));
    $success=false;
  }
  $errors=checkRecursion("pages","id","parentid");
  foreach($errors as $id) {
    $database->modifyField("pages","parentid",0,"id=$id");
    makeError("phpc_check_recursion","pages");
    $success=false;
  }
  // Sets Constraints
  $items=array("template","bundle","replacement");
  foreach($items as $item) {
    $itemstable="{$item}s";
    $setstable="{$item}sets";
    $errors=checkConstraints($setstable,"id",$setstable,"id","parentid",0);
    foreach($errors as $id) {
      $database->modifyField($setstable,"parentid",0,"id=$id");
      makeError("phpc_check_invalidid",array("parentid",$setstable));
      $success=false;
    }
    $errors=checkConstraints($setstable,"id",$itemstable,"id","setid");
    foreach($errors as $id) {
      $database->deleteLine($itemstable,"id=$id");
      makeError("phpc_check_invalidid",array("setid",$itemstable));
      $success=false;
    }
    $field="{$item}setid";
    $errors=checkConstraints($setstable,"id","styles","id",$field);
    foreach($errors as $id) {
      $database->deleteLine("styles","id=$id");
      makeError("phpc_check_invalidid",array($field,"styles"));
      $success=false;
    }
    $errors=checkRecursion($setstable,"id","parentid");
    foreach($errors as $id) {
      $database->modifyField($setstable,"parentid",0,"id=$id");
      makeError("phpc_check_recursion",$setstable);
      $success=false;
    }
  }
  // Styles Constraints
  if(!$database->isLinePresent("styles","forusers=1")) {
    makeWarning("admin_error_nousersstyle");
    $success=false;
  }
  if(!$database->isLinePresent("styles","foradmin=1")) {
    makeWarning("admin_error_noadminstyle");
    $success=false;
  }
  // Templates/Bundles Constraints
  $pages=$database->getOrderedLines("pages","name");
  $templatesets=$database->getOrderedLines("templatesets","id");
  $bundlesets=$database->getOrderedLines("bundlesets","id");
  foreach($templatesets as $set) {
    makeNotification("phpc_check_templateset",$set["title"]);
    $setChain=createInheritanceChain("template",$set["id"]);
    $templates=createInheritanceGroupingArray("templates",$setChain,"name,parent");
    foreach($templates as $name=>$template) if($template["parent"]!="") {
      $parent=phpcstrtolower($template["parent"]);
      if($parent==$name) {
        makeWarning("phpc_check_templaterecursion",$template["name"]);
        $success=false;
      }
      if(!isset($templates[$parent])) {
        makeWarning("phpc_check_templateparent",array($template["name"],$template["parent"]));
        $success=false;
      }
    }
    foreach($pages as $page) {
      $template=phpcstrtolower($page["template"]);
      if($template!="" && !isset($templates[$template])) {
        makeWarning("phpc_check_pagetemplate",array($page["name"],$page["template"]));
        $success=false;
      }
    }
  }
  foreach($bundlesets as $set) {
    makeNotification("phpc_check_bundleset",$set["title"]);
    $setChain=createInheritanceChain("bundle",$set["id"]);
    $bundles=createInheritanceGroupingArray("bundles",$setChain);
    foreach($pages as $page) {
      $bundleList=explodeSmart(",",$page["bundles"]);
      $bundleKeys=explodeSmart(",",phpcstrtolower($page["bundles"]));
      $bundleList=combineArrays($bundleKeys,$bundleList);
      foreach($bundleList as $name=>$bundle) if(!isset($bundles[$name])) {
        makeWarning("phpc_check_pagebundle",array($page["name"],$bundle));
        $success=false;
      }
    }
  }
  $optimizer->clearCache();
  $optimizer->clearFileCache();
  makeNotification($success?"phpc_check_success":"phpc_check_errors");
}

/******************************** Clear Cache *********************************/

if($action=="clearcache") {
  makeNotification("phpc_clearcache_start");
  $optimizer->clearCache();
  makeNotification("phpc_clearcache_success");
  makeNotification("phpc_clearfilecache_start");
  $optimizer->clearFileCache();
  makeNotification("phpc_clearfilecache_success");
}

// ############################################################################
// ############################## Pages Section ###############################
// ############################################################################

/******************************* Add/Edit Page ********************************/

if($action=="addpage" || $action=="editpage") {
  $id=acceptIntParameter("id");
  $name=acceptStringParameter("name");
  $add=$action=="addpage";
  $formtitle=$add?"phpc_addpage_form":"phpc_editpage_form";
  $pages=$database->getOrderedLines("pages","name","id!=$id");
  $info=localizeSmartInfo("pages",$infoPages,$localizedFields);
  $matrix=makeMatrix("pages",$add,$info);
  if($add) {
    $parentid=$database->getField("pages","id","name='general'");
    $matrix["name"]["value"]=$name;
    $matrix["parentid"]["value"]=$parentid;
  }
  $values=array($language["phpc_addeditpage_noparent"]);
  $values+=extractArrayColumns($pages,"id","name");
  $matrix["parentid"]["options"]=$values;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddpage" || $action=="doeditpage") {
  $name=acceptStringParameter("name");
  $add=$action=="doaddpage";
  $info=localizeSmartInfo("pages",$infoPages,$localizedFields);
  $success=processSmartUpdate("pages",$add,$info);
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addpage_success":"phpc_editpage_success");
    else makeError($add?"phpc_addpage_failure":"phpc_editpage_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("pagegroups",$name));
  if($success) $action="modifypages";
}

/******************************** Remove Page *********************************/

if($action=="removepage") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removepage_prompt","phpc.php?action=doremovepage&id=$id");
}

if($action=="doremovepage") {
  $id=acceptIntParameter("id");
  $database->deleteLines("linkstyles","pageid=$id");
  $database->deleteLines("sessions","pageid=$id");
  $database->modifyLines("pages",array("parentid"=>0),"parentid=$id");
  $database->deleteLine("pages","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removepage_success");
  makeBreak();
  $action="modifypages";
}

/**************************** Add/Edit Page Group *****************************/

if($action=="addpagegroup" || $action=="editpagegroup") {
  $add=$action=="addpagegroup";
  $formtitle=$add?"phpc_addpagegroup_form":"phpc_editpagegroup_form";
  $matrix=makeMatrix("pagegroups",$add,$infoPageGroups);
  if($add) $matrix["displayorder"]["value"]=
    $database->getMaxField("pagegroups","displayorder")+1;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddpagegroup" || $action=="doeditpagegroup") {
  $prefix=acceptStringParameter("prefix");
  $add=$action=="doaddpagegroup";
  $success=processSmartUpdate("pagegroups",$add,$infoPageGroups);
  if($success)
    makeNotification($add?"phpc_addpagegroup_success":"phpc_editpagegroup_success");
    else makeError($add?"phpc_addpagegroup_failure":"phpc_editpagegroup_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("pagegroups",$prefix,true));
  if($success) $action="modifypages";
}

/***************************** Remove Page Group ******************************/

if($action=="removepagegroup") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removepagegroup_prompt","phpc.php?action=doremovepagegroup&id=$id");
}

if($action=="doremovepagegroup") {
  $id=acceptIntParameter("id");
  $database->deleteLine("pagegroups","id=$id");
  makeNotification("phpc_removepagegroup_success");
  makeBreak();
  $action="modifypages";
}

/******************************** Order Pages *********************************/

if($action=="orderpages") {
  $groups=$database->getOrderedLines("pagegroups","displayorder");
  foreach($groups as $group) {
    $order=acceptIntParameter(array("order",$group["id"]));
    $database->modifyField("pagegroups","displayorder",$order,"id=$group[id]");
  }
  makeNotification("phpc_orderpages_success");
  makeBreak();
  $action="modifypages";
}

/******************************** Modify Pages ********************************/

if($action=="modifypages") {
  $expandGroup=acceptStringParameter("expand");
  $expandAll=$expandGroup=="all" || AlwaysExpandPagesGroups;
  $groups=createSimpleGroupingTree("pagegroups","pages",
    "phpc_modifypages_defgrouponce","phpc_modifypages_defgroupmany");
  makeTree("header","phpc_modifypages_tree");
  foreach($groups as $group) {
    $id=ifset($group["id"],0);
    $expand=$id==(int)$expandGroup || $expandAll;
    $links=array();
    if(!$expand) $links+=array(
      "phpc_modifypages_expand"=>"phpc.php?action=modifypages&expand=$id",
      "phpc_modifypages_expandall"=>"phpc.php?action=modifypages&expand=all");
    if($id) $links+=array(
      "phpc_modifypages_groupedit"=>"phpc.php?action=editpagegroup&id=$id",
      "phpc_modifypages_groupremove"=>"phpc.php?action=removepagegroup&id=$id",
      "phpc_modifypages_groupadd"=>"phpc.php?action=addpage&name=".phpcurlencode($group["prefix"]));
    if(!$id) $links+=array(
      "phpc_modifypages_groupadd"=>"phpc.php?action=addpage");
    makeTreeGroupSimple("header",$group["title"],$links);
    if($expand) foreach($group["items"] as $item) {
      $title=$item["name"];
      if($item["alias"]!="") $title.=" ($item[alias])";
      if(!$item["visible"]) $title="[font=disabled]{$title}[/font]";
      $links=array(
        "phpc_modifypages_itemedit"=>"phpc.php?action=editpage&id=$item[id]",
        "phpc_modifypages_itemremove"=>"phpc.php?action=removepage&id=$item[id]");
      if($item["visible"]) {
        $openlink=$compiler->createLink($item["name"]);
        $links=array("phpc_modifypages_itemopen"=>"_blank:$openlink")+$links;
      }
      makeTreeItemSimple($title,$links);
    }
    makeTreeGroupSimple("footer");
  }
  makeTree("footer");
  if(count($groups)>1) {
    makeBreak();
    makeForm("header","phpc_orderpages_form","phpc","orderpages");
    foreach($groups as $group) if(ifset($group["id"]))
      makeFormInputOrder($group["title"],"","order[$group[id]]",$group["displayorder"],false);
    makeForm("footer");
  }
}

/*************************** Add Page/Template Pair ***************************/

if($action=="addpair") {
  getAdminStyle();
  $locales=getTableLocales("pages",$localizedFields);
  $content="<area:content>\r\n\r\n</area:content>\r\n";
  $pages=$database->getOrderedLines("pages","name");
  $options=array($language["phpc_addpair_noparent"]);
  $options+=extractArrayColumns($pages,"id","name");
  $parentid=$database->getField("pages","id","name='general'");
  $templateparent=$database->getField("templates","name","name='general'");
  makeForm("header","phpc_addpair_form","phpc","doaddpair");
  makeFormInput("phpc_addpair_name","","name");
  foreach($locales as $locale) {
    $name=$language["locales"][$locale];
    $title=format($language["phpc_addpair_title"],$name);
    makeFormInput($title,"","title$locale","",false);
  }
  makeFormTPLEditor("phpc_addpair_content","","content",$content);
  makeFormFile("phpc_addpair_filedata","","filedata");
  makeForm("separator","phpc_addpair_separator");
  makeFormInput("phpc_addpair_alias","","alias");
  makeFormChooser("phpc_addpair_parent","","parentid",$parentid,$options);
  makeFormInput("phpc_addpair_templateparent","","templateparent",$templateparent);
  makeFormInput("phpc_addpair_template","phpc_addpair_templatedesc","template");
  makeFormInput("phpc_addpair_bundles","","bundles");
  makeForm("footer");
}

if($action=="doaddpair") {
  $style=getAdminStyle();
  $locales=getTableLocales("pages",$localizedFields);
  $name=acceptStringParameter("name");
  $template=acceptStringParameter("template");
  if($template=="") $template=$name;
  $filedata=$fileSystem->getUploadedFile("filedata");
  if($filedata) sendParameter("content",$filedata["content"]);
  $success=!$database->isLinePresent("pages","name=".slashes($name));
  if(!$success) makeAdminError("phpc_addpair_failure1");
  $values=array(
    "setid"=>$style["templatesetid"],
    "name"=>$template,
    "parent"=>acceptStringParameter("templateparent"),
    "content"=>acceptStringParameter("content",false,false));
  $success=$database->addLine("templates",$values);
  if(!$success) makeAdminError("phpc_addpair_failure2");
  $values=array(
    "parentid"=>acceptIntParameter("parentid"),
    "name"=>$name,
    "alias"=>acceptStringParameter("alias"),
    "template"=>$template,
    "bundles"=>acceptStringParameter("bundles"),
    "visible"=>1);
  foreach($locales as $locale)
    $values["title$locale"]=acceptStringParameter("title$locale");
  $database->addLine("pages",$values);
  $optimizer->clearCache();
  makeNotification("phpc_addpair_success");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("templategroups",$template));
  $action="modifytemplates";
}

/****************************** Pages Languages *******************************/

if($action=="addlanguage" || $action=="doaddlanguage") {
  $success=makeTableLocalesAdd("pages",$localizedFields,"phpc",$action);
  if($success) $optimizer->clearCache();
  if($success) $action="modifylanguages";
}

if($action=="removelanguage" || $action=="doremovelanguage") {
  $success=makeTableLocalesRemove("pages",$localizedFields,"phpc",$action);
  if($success) $optimizer->clearCache();
  if($success) $action="modifylanguages";
}

if($action=="modifylanguages") {
  $addlink="phpc.php?action=addlanguage";
  $removelink="phpc.php?action=removelanguage&locale=%s";
  makeTableLocalesList("pages",$localizedFields,$addlink,$removelink);
}

// ############################################################################
// ############################ Templates Section #############################
// ############################################################################

/*************************** Add/Edit/Copy Template ***************************/

$actions="doaddtemplate,doedittemplate,updateaddtemplate,updateedittemplate";

if(in_array($action,explode(",",$actions))) {
  $name=acceptStringParameter("name");
  $add=$action=="doaddtemplate" || $action=="updateaddtemplate";
  $update=substr($action,0,6)=="update";
  $filedata=$fileSystem->getUploadedFile("filedata");
  if($filedata) sendParameter("content",$filedata["content"]);
  $success=processSmartUpdate("templates",$add,$infoTemplates);
  if($add && $success) sendParameter("id",$database->getCounterValue());
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addtemplate_success":"phpc_edittemplate_success");
    else makeError($add?"phpc_addtemplate_failure":"phpc_edittemplate_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("templategroups",$name));
  if($success) $action=$update?"edittemplate":"modifytemplates";
}

if($action=="addtemplate" || $action=="edittemplate") {
  $name=acceptStringParameter("name");
  $style=getAdminStyle();
  $add=$action=="addtemplate";
  $formtitle=$add?"phpc_addtemplate_form":"phpc_edittemplate_form";
  $matrix=makeMatrix("templates",$add,$infoTemplates);
  if($add) $matrix["setid"]["value"]=$style["templatesetid"];
  if($add) $matrix["name"]["value"]=$name;
  makeSmartForm($formtitle,"phpc",array("do$action","update$action"),$matrix);
}

if($action=="copytemplate") {
  $style=getAdminStyle();
  $matrix=makeMatrix("templates",false,$infoTemplates);
  $matrix["setid"]["value"]=$style["templatesetid"];
  makeSmartForm("phpc_addtemplate_form","phpc",array("doaddtemplate","updateaddtemplate"),$matrix);
}

/******************************* View Template ********************************/

if($action=="viewtemplate") {
  $id=acceptIntParameter("id");
  $template=$database->getLine("templates","id=$id");
  $content=createTemplateContent($template);
  makeNotification("phpc_viewtemplate_prompt",$template["name"]);
  makeBreak();
  makeFormattedText($content);
  $links=array(
    "phpc_viewtemplate_edit"=>"phpc.php?action=edittemplate&id=$id",
    "phpc_viewtemplate_execute"=>"_blank:phpc.php?action=executetemplate&id=$id",
    "phpc_viewtemplate_back"=>"phpc.php?action=modifytemplates");
  makeBreak();
  makeLinks($links);
}

/****************************** Execute Template ******************************/

if($action=="executetemplate") {
  $id=acceptIntParameter("id");
  $name=$database->getField("templates","name","id=$id");
  $compiler->prepare();
  $scope=array(
    "currentStyle"=>$compiler->style,
    "currentSession"=>$compiler->session,
    "currentPage"=>$compiler->page);
  $content=$compiler->captureTemplate($name,$scope);
  ob_clean();
  echo $content;
  halt();
}

/*************************** Remove/Revert Template ***************************/

if($action=="removetemplate" || $action=="reverttemplate") {
  $id=acceptIntParameter("id");
  $remove=$action=="removetemplate";
  $prompt=$remove?"phpc_removetemplate_prompt":"phpc_reverttemplate_prompt";
  makePrompt($prompt,"phpc.php?action=doremovetemplate&id=$id");
}

if($action=="doremovetemplate") {
  $id=acceptIntParameter("id");
  $database->deleteLine("templates","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removetemplate_success");
  makeBreak();
  $action="modifytemplates";
}

/************************** Add/Edit Template Group ***************************/

if($action=="addtemplategroup" || $action=="edittemplategroup") {
  $add=$action=="addtemplategroup";
  $formtitle=$add?"phpc_addtemplategroup_form":"phpc_edittemplategroup_form";
  $matrix=makeMatrix("templategroups",$add,$infoTemplateGroups);
  if($add) $matrix["displayorder"]["value"]=
    $database->getMaxField("templategroups","displayorder")+1;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddtemplategroup" || $action=="doedittemplategroup") {
  $prefix=acceptStringParameter("prefix");
  $add=$action=="doaddtemplategroup";
  $success=processSmartUpdate("templategroups",$add,$infoTemplateGroups);
  if($success)
    makeNotification($add?"phpc_addtemplategroup_success":"phpc_edittemplategroup_success");
    else makeError($add?"phpc_addtemplategroup_failure":"phpc_edittemplategroup_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("templategroups",$prefix,true));
  if($success) $action="modifytemplates";
}

/*************************** Remove Template Group ****************************/

if($action=="removetemplategroup") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removetemplategroup_prompt","phpc.php?action=doremovetemplategroup&id=$id");
}

if($action=="doremovetemplategroup") {
  $id=acceptIntParameter("id");
  $database->deleteLine("templategroups","id=$id");
  makeNotification("phpc_removetemplategroup_success");
  makeBreak();
  $action="modifytemplates";
}

/****************************** Order Templates *******************************/

if($action=="ordertemplates") {
  $groups=$database->getOrderedLines("templategroups","displayorder");
  foreach($groups as $group) {
    $order=acceptIntParameter(array("order",$group["id"]));
    $database->modifyField("templategroups","displayorder",$order,"id=$group[id]");
  }
  makeNotification("phpc_ordertemplates_success");
  makeBreak();
  $action="modifytemplates";
}

/****************************** Modify Templates ******************************/

if($action=="modifytemplates") {
  $expandGroup=acceptStringParameter("expand");
  $expandAll=$expandGroup=="all" || AlwaysExpandTemplatesGroups;
  $setChain=createInheritanceChain("template");
  $groups=createInheritanceGroupingTree("templategroups","templates",$setChain,
    "phpc_modifytemplates_defgrouponce","phpc_modifytemplates_defgroupmany");
  makeTree("header","phpc_modifytemplates_tree");
  foreach($groups as $group) {
    $id=ifset($group["id"],0);
    $expand=$id==(int)$expandGroup || $expandAll;
    $links=array();
    if(!$expand) $links+=array(
      "phpc_modifytemplates_expand"=>"phpc.php?action=modifytemplates&expand=$id",
      "phpc_modifytemplates_expandall"=>"phpc.php?action=modifytemplates&expand=all");
    if($id) $links+=array(
      "phpc_modifytemplates_groupedit"=>"phpc.php?action=edittemplategroup&id=$id",
      "phpc_modifytemplates_groupremove"=>"phpc.php?action=removetemplategroup&id=$id",
      "phpc_modifytemplates_groupadd"=>"phpc.php?action=addtemplate&name=".phpcurlencode($group["prefix"]));
    if(!$id) $links+=array(
      "phpc_modifytemplates_groupadd"=>"phpc.php?action=addtemplate");
    makeTreeGroupSimple("header",$group["title"],$links);
    if($expand) foreach($group["items"] as $item) {
      $inheritance=$item["inheritance"];
      $title="[font=$inheritance]$item[name][/font]";
      $links=array(
        "phpc_modifytemplates_itemview"=>"phpc.php?action=viewtemplate&id=$item[id]");
      if($inheritance=="default") $links+=array(
        "phpc_modifytemplates_itemedit"=>"phpc.php?action=edittemplate&id=$item[id]",
        "phpc_modifytemplates_itemremove"=>"phpc.php?action=removetemplate&id=$item[id]");
      if($inheritance=="original") $links+=array(
        "phpc_modifytemplates_itemcopy"=>"phpc.php?action=copytemplate&id=$item[id]");
      if($inheritance=="inherited") $links+=array(
        "phpc_modifytemplates_itemedit"=>"phpc.php?action=edittemplate&id=$item[id]",
        "phpc_modifytemplates_itemrevert"=>"phpc.php?action=reverttemplate&id=$item[id]");
      makeTreeItemSimple($title,$links);
    }
    makeTreeGroupSimple("footer");
  }
  makeTree("footer");
  if(count($groups)>1) {
    makeBreak();
    makeForm("header","phpc_ordertemplates_form","phpc","ordertemplates");
    foreach($groups as $group) if(ifset($group["id"]))
      makeFormInputOrder($group["title"],"","order[$group[id]]",$group["displayorder"],false);
    makeForm("footer");
  }
}

// ############################################################################
// ############################# Bundles Section ##############################
// ############################################################################

/**************************** Add/Edit/Copy Bundle ****************************/

$actions="doaddbundle,doeditbundle,updateaddbundle,updateeditbundle";

if(in_array($action,explode(",",$actions))) {
  $name=acceptStringParameter("name");
  $add=$action=="doaddbundle" || $action=="updateaddbundle";
  $update=substr($action,0,6)=="update";
  $filedata=$fileSystem->getUploadedFile("filedata");
  if($filedata) sendParameter("content",$filedata["content"]);
  $success=processSmartUpdate("bundles",$add,$infoBundles);
  if($add && $success) sendParameter("id",$database->getCounterValue());
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addbundle_success":"phpc_editbundle_success");
    else makeError($add?"phpc_addbundle_failure":"phpc_editbundle_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("bundlegroups",$name));
  if($success) $action=$update?"editbundle":"modifybundles";
}

if($action=="addbundle" || $action=="editbundle") {
  $name=acceptStringParameter("name");
  $style=getAdminStyle();
  $add=$action=="addbundle";
  $formtitle=$add?"phpc_addbundle_form":"phpc_editbundle_form";
  $matrix=makeMatrix("bundles",$add,$infoBundles);
  if($add) $matrix["setid"]["value"]=$style["bundlesetid"];
  if($add) $matrix["name"]["value"]=$name;
  makeSmartForm($formtitle,"phpc",array("do$action","update$action"),$matrix);
}

if($action=="copybundle") {
  $style=getAdminStyle();
  $matrix=makeMatrix("bundles",false,$infoBundles);
  $matrix["setid"]["value"]=$style["bundlesetid"];
  makeSmartForm("phpc_addbundle_form","phpc",array("doaddbundle","updateaddbundle"),$matrix);
}

/******************************** View Bundle *********************************/

if($action=="viewbundle") {
  $id=acceptIntParameter("id");
  $bundle=$database->getLine("bundles","id=$id");
  $content=$syntaxColorer->processPhpcBundle($bundle["content"]);
  makeNotification("phpc_viewbundle_prompt",$bundle["name"]);
  makeBreak();
  makeFormattedText($content);
  $links=array(
    "phpc_viewbundle_edit"=>"phpc.php?action=editbundle&id=$id",
    "phpc_viewbundle_back"=>"phpc.php?action=modifybundles");
  makeBreak();
  makeLinks($links);
}

/**************************** Remove/Revert Bundle ****************************/

if($action=="removebundle" || $action=="revertbundle") {
  $id=acceptIntParameter("id");
  $remove=$action=="removebundle";
  $prompt=$remove?"phpc_removebundle_prompt":"phpc_revertbundle_prompt";
  makePrompt($prompt,"phpc.php?action=doremovebundle&id=$id");
}

if($action=="doremovebundle") {
  $id=acceptIntParameter("id");
  $database->deleteLine("bundles","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removebundle_success");
  makeBreak();
  $action="modifybundles";
}

/*************************** Add/Edit Bundle Group ****************************/

if($action=="addbundlegroup" || $action=="editbundlegroup") {
  $add=$action=="addbundlegroup";
  $formtitle=$add?"phpc_addbundlegroup_form":"phpc_editbundlegroup_form";
  $matrix=makeMatrix("bundlegroups",$add,$infoBundleGroups);
  if($add) $matrix["displayorder"]["value"]=
    $database->getMaxField("bundlegroups","displayorder")+1;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddbundlegroup" || $action=="doeditbundlegroup") {
  $prefix=acceptStringParameter("prefix");
  $add=$action=="doaddbundlegroup";
  $success=processSmartUpdate("bundlegroups",$add,$infoBundleGroups);
  if($success)
    makeNotification($add?"phpc_addbundlegroup_success":"phpc_editbundlegroup_success");
    else makeError($add?"phpc_addbundlegroup_failure":"phpc_editbundlegroup_failure");
  makeBreak();
  sendParameter("expand",searchMatchingGroup("bundlegroups",$prefix,true));
  if($success) $action="modifybundles";
}

/**************************** Remove Bundle Group *****************************/

if($action=="removebundlegroup") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removebundlegroup_prompt","phpc.php?action=doremovebundlegroup&id=$id");
}

if($action=="doremovebundlegroup") {
  $id=acceptIntParameter("id");
  $database->deleteLine("bundlegroups","id=$id");
  makeNotification("phpc_removebundlegroup_success");
  makeBreak();
  $action="modifybundles";
}

/******************************* Order Bundles ********************************/

if($action=="orderbundles") {
  $groups=$database->getOrderedLines("bundlegroups","displayorder");
  foreach($groups as $group) {
    $order=acceptIntParameter(array("order",$group["id"]));
    $database->modifyField("bundlegroups","displayorder",$order,"id=$group[id]");
  }
  makeNotification("phpc_orderbundles_success");
  makeBreak();
  $action="modifybundles";
}

/******************************* Modify Bundles *******************************/

if($action=="modifybundles") {
  $expandGroup=acceptStringParameter("expand");
  $expandAll=$expandGroup=="all" || AlwaysExpandBundlesGroups;
  $setChain=createInheritanceChain("bundle");
  $groups=createInheritanceGroupingTree("bundlegroups","bundles",$setChain,
    "phpc_modifybundles_defgrouponce","phpc_modifybundles_defgroupmany");
  makeTree("header","phpc_modifybundles_tree");
  foreach($groups as $group) {
    $id=ifset($group["id"],0);
    $expand=$id==(int)$expandGroup || $expandAll;
    $links=array();
    if(!$expand) $links+=array(
      "phpc_modifybundles_expand"=>"phpc.php?action=modifybundles&expand=$id",
      "phpc_modifybundles_expandall"=>"phpc.php?action=modifybundles&expand=all");
    if($id) $links+=array(
      "phpc_modifybundles_groupedit"=>"phpc.php?action=editbundlegroup&id=$id",
      "phpc_modifybundles_groupremove"=>"phpc.php?action=removebundlegroup&id=$id",
      "phpc_modifybundles_groupadd"=>"phpc.php?action=addbundle&name=".phpcurlencode($group["prefix"]));
    if(!$id) $links+=array(
      "phpc_modifybundles_groupadd"=>"phpc.php?action=addbundle");
    makeTreeGroupSimple("header",$group["title"],$links);
    if($expand) foreach($group["items"] as $item) {
      $inheritance=$item["inheritance"];
      $title="[font=$inheritance]$item[name][/font]";
      $links=array(
        "phpc_modifybundles_itemview"=>"phpc.php?action=viewbundle&id=$item[id]");
      if($inheritance=="default") $links+=array(
        "phpc_modifybundles_itemedit"=>"phpc.php?action=editbundle&id=$item[id]",
        "phpc_modifybundles_itemremove"=>"phpc.php?action=removebundle&id=$item[id]");
      if($inheritance=="original") $links+=array(
        "phpc_modifybundles_itemcopy"=>"phpc.php?action=copybundle&id=$item[id]");
      if($inheritance=="inherited") $links+=array(
        "phpc_modifybundles_itemedit"=>"phpc.php?action=editbundle&id=$item[id]",
        "phpc_modifybundles_itemrevert"=>"phpc.php?action=revertbundle&id=$item[id]");
      makeTreeItemSimple($title,$links);
    }
    makeTreeGroupSimple("footer");
  }
  makeTree("footer");
  if(count($groups)>1) {
    makeBreak();
    makeForm("header","phpc_orderbundles_form","phpc","orderbundles");
    foreach($groups as $group) if(ifset($group["id"]))
      makeFormInputOrder($group["title"],"","order[$group[id]]",$group["displayorder"],false);
    makeForm("footer");
  }
}

// ############################################################################
// ########################## Search/Replace Section ##########################
// ############################################################################

/************************** Search Templates/Bundles **************************/

if($action=="searchtemplates" || $action=="searchbundles") {
  $subject=substr($action,6,strlen($action)-7);
  getAdminStyle();
  makeForm("header","phpc_search{$subject}s_form","phpc","do$action");
  makeFormInput("phpc_search_text","phpc_search_textdesc","text");
  makeFormYesNo("phpc_search_case","","case",0);
  makeFormYesNo("phpc_search_words","","words",0);
  makeFormYesNo("phpc_search_regexp","","regexp",0);
  makeForm("footer");
}

if($action=="dosearchtemplates" || $action=="dosearchbundles") {
  $text=acceptStringParameter("text",false,false);
  $case=acceptIntParameter("case",0,1);
  $words=acceptIntParameter("words",0,1);
  $regexp=acceptIntParameter("regexp",0,1);
  $subject=substr($action,8,strlen($action)-9);
  if($text!="") {
    $pattern=createSearchPattern($text,$case,$words,$regexp);
    $groups=processSearch($subject,$pattern,PhpcSearchInherited,true,
      "phpc_modify{$subject}s_defgrouponce",
      "phpc_modify{$subject}s_defgroupmany");
    if(count($groups)) {
      makeTree("header","phpc_search{$subject}s_tree");
      foreach($groups as $group) {
        makeTreeGroupSimple("header",$group["title"]);
        foreach($group["items"] as $item) {
          $inheritance=$item["inheritance"];
          $title="[font=$inheritance]$item[name][/font]";
          $links=array(
            "phpc_modify{$subject}s_itemview"=>"phpc.php?action=view{$subject}&id=$item[id]");
          if($inheritance=="default") $links+=array(
            "phpc_modify{$subject}s_itemedit"=>"phpc.php?action=edit{$subject}&id=$item[id]",
            "phpc_modify{$subject}s_itemremove"=>"phpc.php?action=remove{$subject}&id=$item[id]");
          if($inheritance=="original") $links+=array(
            "phpc_modify{$subject}s_itemcopy"=>"phpc.php?action=copy{$subject}&id=$item[id]");
          if($inheritance=="inherited") $links+=array(
            "phpc_modify{$subject}s_itemedit"=>"phpc.php?action=edit{$subject}&id=$item[id]",
            "phpc_modify{$subject}s_itemrevert"=>"phpc.php?action=revert{$subject}&id=$item[id]");
          makeTreeItemSimple($title,$links);
        }
        makeTreeGroupSimple("footer");
      }
      makeTree("footer");
    }
    else makeNotification("phpc_search{$subject}s_notfound");
  }
  else makeError("phpc_error_nosearchtext");
}

/************************* Replace Templates/Bundles **************************/

if($action=="replacetemplates" || $action=="replacebundles") {
  $subject=substr($action,7,strlen($action)-8);
  getAdminStyle();
  makeForm("header","phpc_replace{$subject}s_form","phpc","do$action");
  makeFormInput("phpc_replace_text","phpc_replace_textdesc","text");
  makeFormInput("phpc_replace_replace","phpc_replace_replacedesc","replace");
  makeFormYesNo("phpc_replace_case","","case",0);
  makeFormYesNo("phpc_replace_words","","words",0);
  makeFormYesNo("phpc_replace_regexp","","regexp",0);
  makeFormYesNo("phpc_replace{$subject}s_allsets","phpc_replace{$subject}s_allsetsdesc","allsets",0);
  makeForm("footer");
}

if($action=="doreplacetemplates" || $action=="doreplacebundles") {
  $text=acceptStringParameter("text",false,false);
  $replace=acceptStringParameter("replace",false,false);
  $case=acceptIntParameter("case",0,1);
  $words=acceptIntParameter("words",0,1);
  $regexp=acceptIntParameter("regexp",0,1);
  $allsets=acceptIntParameter("allsets",0,1);
  $confirm=acceptIntParameter("confirm",0,1);
  $subject=substr($action,9,strlen($action)-10);
  if($text!="") {
    $pattern=createSearchPattern($text,$case,$words,$regexp);
    $area=$allsets?PhpcSearchAllSets:PhpcSearchCurrentSet;
    $items=processSearch($subject,$pattern,$area);
    if(count($items)) {
      if($confirm) {
        if(!$regexp) $replace=str_replace("\\","\\\\",$replace);
        if(!$regexp) $replace=str_replace("\$","\\\$",$replace);
        foreach($items as $item) {
          $content=preg_replace($pattern,$replace,$item["content"]);
          $database->modifyField("{$subject}s","content",$content,"id=$item[id]");
          makeNotification("phpc_replace{$subject}s_report",$item["name"]);
        }
        $optimizer->clearCache();
        makeNotification("phpc_replace{$subject}s_success");
      }
      else {
        $fragments=0;
        foreach($items as $item) $fragments+=$item["fragments"];
        $title=$replace!=""?"phpc_replace{$subject}s_prompt":"phpc_replace{$subject}s_promptempty";
        $title=format($language[$title],array($fragments,count($items)));
        makePromptForm("header",$title,"phpc",$action,false);
        makeFormHidden("text",$text);
        makeFormHidden("replace",$replace);
        makeFormHidden("case",$case);
        makeFormHidden("words",$words);
        makeFormHidden("regexp",$regexp);
        makeFormHidden("allsets",$allsets);
        makeFormHidden("confirm",1);
        makePromptForm("footer");
      }
    }
    else makeNotification("phpc_search{$subject}s_notfound");
  }
  else makeError("phpc_error_nosearchtext");
}

// ############################################################################
// ########################### Replacements Section ###########################
// ############################################################################

/************************* Add/Edit/Copy Replacement **************************/

if($action=="addreplacement" || $action=="editreplacement") {
  $name=acceptStringParameter("name");
  $style=getAdminStyle();
  $add=$action=="addreplacement";
  $formtitle=$add?"phpc_addreplacement_form":"phpc_editreplacement_form";
  $matrix=makeMatrix("replacements",$add,$infoReplacements);
  if($add) $matrix["setid"]["value"]=$style["replacementsetid"];
  if($add) $matrix["name"]["value"]=$name;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="copyreplacement") {
  $style=getAdminStyle();
  $matrix=makeMatrix("replacements",false,$infoReplacements);
  $matrix["setid"]["value"]=$style["replacementsetid"];
  makeSmartForm("phpc_addreplacement_form","phpc","doaddreplacement",$matrix);
}

if($action=="doaddreplacement" || $action=="doeditreplacement") {
  $add=$action=="doaddreplacement";
  $success=processSmartUpdate("replacements",$add,$infoReplacements);
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addreplacement_success":"phpc_editreplacement_success");
    else makeError($add?"phpc_addreplacement_failure":"phpc_editreplacement_failure");
  makeBreak();
  if($success) $action="modifyreplacements";
}

/************************* Remove/Revert Replacement **************************/

if($action=="removereplacement" || $action=="revertreplacement") {
  $id=acceptIntParameter("id");
  $remove=$action=="removereplacement";
  $prompt=$remove?"phpc_removereplacement_prompt":"phpc_revertreplacement_prompt";
  makePrompt($prompt,"phpc.php?action=doremovereplacement&id=$id");
}

if($action=="doremovereplacement") {
  $id=acceptIntParameter("id");
  $database->deleteLine("replacements","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removereplacement_success");
  makeBreak();
  $action="modifyreplacements";
}

/**************************** Modify Replacements *****************************/

if($action=="modifyreplacements") {
  $setChain=createInheritanceChain("replacement");
  $items=createInheritanceGroupingArray("replacements",$setChain);
  makeTree("header","phpc_modifyreplacements_tree");
  $links=array("phpc_modifyreplacements_add"=>"phpc.php?action=addreplacement");
  makeTreeGroupSimple("header",$language["phpc_modifyreplacements_defgroup"],$links);
  foreach($items as $item) {
    $inheritance=$item["inheritance"];
    $title="[font=$inheritance]$item[name][/font]";
    $links=array();
    if($inheritance=="default") $links+=array(
      "phpc_modifyreplacements_edit"=>"phpc.php?action=editreplacement&id=$item[id]",
      "phpc_modifyreplacements_remove"=>"phpc.php?action=removereplacement&id=$item[id]");
    if($inheritance=="original") $links+=array(
      "phpc_modifyreplacements_copy"=>"phpc.php?action=copyreplacement&id=$item[id]");
    if($inheritance=="inherited") $links+=array(
      "phpc_modifyreplacements_edit"=>"phpc.php?action=editreplacement&id=$item[id]",
      "phpc_modifyreplacements_revert"=>"phpc.php?action=revertreplacement&id=$item[id]");
    makeTreeItemSimple($title,$links);
  }
  makeTreeGroupSimple("footer");
  makeTree("footer");
}

// ############################################################################
// ############################ Formatting Section ############################
// ############################################################################

/**************************** Add/Edit Formatting *****************************/

if($action=="addformatting" || $action=="editformatting") {
  $add=$action=="addformatting";
  $formtitle=$add?"phpc_addformatting_form":"phpc_editformatting_form";
  $matrix=makeMatrix("formatting",$add,$infoFormatting);
  if($add) $matrix["useorder"]["value"]=
    $database->getMaxField("formatting","useorder")+1;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddformatting" || $action=="doeditformatting") {
  $add=$action=="doaddformatting";
  $success=processSmartUpdate("formatting",$add,$infoFormatting);
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addformatting_success":"phpc_editformatting_success");
    else makeError($add?"phpc_addformatting_failure":"phpc_editformatting_failure");
  makeBreak();
  if($success) $action="modifyformatting";
}

/****************************** View Formatting *******************************/

if($action=="viewformatting") {
  $id=acceptIntParameter("id");
  $sample=$database->getField("formatting","sample","id=$id");
  $formatted=str_replace(PredefinedNewline,"",$formatter->process($sample));
  makeNotification("phpc_viewformatting_sample");
  makeFormattedText(htmlspecialchars($sample));
  makeBreak();
  makeNotification("phpc_viewformatting_result");
  makeFormattedText($formatted);
  makeBreak();
  makeNotification("phpc_viewformatting_source");
  makeFormattedText(htmlspecialchars($formatted));
}

/***************************** Remove Formatting ******************************/

if($action=="removeformatting") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removeformatting_prompt","phpc.php?action=doremoveformatting&id=$id");
}

if($action=="doremoveformatting") {
  $id=acceptIntParameter("id");
  $database->deleteLine("formatting","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removeformatting_success");
  makeBreak();
  $action="modifyformatting";
}

/****************************** Order Formatting ******************************/

if($action=="orderformatting") {
  $formatting=$database->getOrderedLines("formatting","useorder");
  foreach($formatting as $item) {
    $order=acceptIntParameter(array("order",$item["id"]));
    $database->modifyField("formatting","useorder",$order,"id=$item[id]");
  }
  $optimizer->clearCache();
  makeNotification("phpc_orderformatting_success");
  makeBreak();
  $action="modifyformatting";
}

/***************************** Modify Formatting ******************************/

if($action=="modifyformatting") {
  $formatting=$database->getOrderedLines("formatting","useorder");
  $columns=array(
    array("title"=>"phpc_modifyformatting_title","width"=>"15%"),
    array("title"=>"phpc_modifyformatting_class","width"=>"10%"),
    "phpc_modifyformatting_pattern",
    array("title"=>"phpc_modifyformatting_options","width"=>"25%"),
    array("title"=>"phpc_modifyformatting_useorder","width"=>"1%"));
  makeTable("header",$columns,"phpc","orderformatting");
  foreach($formatting as $item) {
    $links=array(
      "phpc_modifyformatting_view"=>"phpc.php?action=viewformatting&id=$item[id]",
      "phpc_modifyformatting_edit"=>"phpc.php?action=editformatting&id=$item[id]",
      "phpc_modifyformatting_remove"=>"phpc.php?action=removeformatting&id=$item[id]");
    makeTableCellSimple($item["title"]);
    makeTableCellSimple($item["class"]);
    makeTableCellPattern($item["pattern"]);
    makeTableCellLinks($links);
    makeTableCellInputOrder("order[$item[id]]",$item["useorder"]);
  }
  makeTable("footer");
}

// ############################################################################
// ########################### Link Styles Section ############################
// ############################################################################

/**************************** Add/Edit Link Style *****************************/

if($action=="addlinkstyle" || $action=="editlinkstyle") {
  $add=$action=="addlinkstyle";
  $formtitle=$add?"phpc_addlinkstyle_form":"phpc_editlinkstyle_form";
  $pages=$database->getOrderedLines("pages","name");
  $pages=extractArrayColumns($pages,"id","name");
  $matrix=makeMatrix("linkstyles",$add,$infoLinkStyles);
  $matrix["pageid"]["options"]=$pages;
  if($add) $matrix["useorder"]["value"]=
    $database->getMaxField("linkstyles","useorder")+1;
  if(count($pages))
    makeSmartForm($formtitle,"phpc","do$action",$matrix);
    else makeError("phpc_error_nopages");
}

if($action=="doaddlinkstyle" || $action=="doeditlinkstyle") {
  $add=$action=="doaddlinkstyle";
  $success=processSmartUpdate("linkstyles",$add,$infoLinkStyles);
  $optimizer->clearCache();
  if($success)
    makeNotification($add?"phpc_addlinkstyle_success":"phpc_editlinkstyle_success");
    else makeError($add?"phpc_addlinkstyle_failure":"phpc_editlinkstyle_failure");
  makeBreak();
  if($success) $action="modifylinkstyles";
}

/***************************** Remove Link Style ******************************/

if($action=="removelinkstyle") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removelinkstyle_prompt","phpc.php?action=doremovelinkstyle&id=$id");
}

if($action=="doremovelinkstyle") {
  $id=acceptIntParameter("id");
  $database->deleteLine("linkstyles","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removelinkstyle_success");
  makeBreak();
  $action="modifylinkstyles";
}

/***************************** Order Link Styles ******************************/

if($action=="orderlinkstyles") {
  $linkstyles=$database->getOrderedLines("linkstyles","useorder");
  foreach($linkstyles as $item) {
    $order=acceptIntParameter(array("order",$item["id"]));
    $database->modifyField("linkstyles","useorder",$order,"id=$item[id]");
  }
  $optimizer->clearCache();
  makeNotification("phpc_orderlinkstyles_success");
  makeBreak();
  $action="modifylinkstyles";
}

/***************************** Modify Link Styles *****************************/

if($action=="modifylinkstyles") {
  $pages=$database->getOrderedLines("pages","name");
  $linkstyles=$database->getOrderedLines("linkstyles","useorder");
  $columns=array(
    array("title"=>"phpc_modifylinkstyles_page","width"=>"15%"),
    "phpc_modifylinkstyles_pattern",
    array("title"=>"phpc_modifylinkstyles_assign","width"=>"15%"),
    array("title"=>"phpc_modifylinkstyles_options","width"=>"20%"),
    array("title"=>"phpc_modifylinkstyles_useorder","width"=>"1%"));
  makeTable("header",$columns,"phpc","orderlinkstyles");
  foreach($linkstyles as $item) {
    $links=array(
      "phpc_modifylinkstyles_edit"=>"phpc.php?action=editlinkstyle&id=$item[id]",
      "phpc_modifylinkstyles_remove"=>"phpc.php?action=removelinkstyle&id=$item[id]");
    makeTableCellSimple(searchArrayField($pages,"id","name",$item["pageid"]));
    makeTableCellPattern($item["pattern"]);
    makeTableCellSimple($item["assign"]);
    makeTableCellLinks($links);
    makeTableCellInputOrder("order[$item[id]]",$item["useorder"]);
  }
  makeTable("footer");
}

// ############################################################################
// ############################### Sets Section ###############################
// ############################################################################

/******************************** Add/Edit Set ********************************/

$actions="addtemplateset,addbundleset,addreplacementset";
$actions.=",edittemplateset,editbundleset,editreplacementset";

if(in_array($action,explode(",",$actions))) {
  $id=acceptIntParameter("id");
  $add=substr($action,0,3)=="add";
  $subject=substr($action,$add?3:4);
  $table="{$subject}s";
  $formtitle=$add?"phpc_add{$subject}_form":"phpc_edit{$subject}_form";
  $sets=$database->getOrderedLines($table,"id","id!=$id");
  $matrix=makeMatrix($table,$add,$infoSets);
  $values=array($language["phpc_addeditset_noparent"]);
  $values+=extractArrayColumns($sets,"id","title");
  $matrix["parentid"]["options"]=$values;
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

$actions="doaddtemplateset,doaddbundleset,doaddreplacementset";
$actions.=",doedittemplateset,doeditbundleset,doeditreplacementset";

if(in_array($action,explode(",",$actions))) {
  $add=substr($action,0,5)=="doadd";
  $subject=substr($action,$add?5:6);
  $table="{$subject}s";
  processSmartUpdate($table,$add,$infoSets);
  $optimizer->clearCache();
  makeNotification($add?"phpc_addset_success":"phpc_editset_success");
  makeBreak();
  $action="modifystyles";
}

/********************************* Remove Set *********************************/

$actions="removetemplateset,removebundleset,removereplacementset";

if(in_array($action,explode(",",$actions))) {
  $id=acceptIntParameter("id");
  makePrompt("phpc_{$action}_prompt","phpc.php?action=do$action&id=$id");
}

$actions="doremovetemplateset,doremovebundleset,doremovereplacementset";

if(in_array($action,explode(",",$actions))) {
  $id=acceptIntParameter("id");
  $subject=substr($action,8,strlen($action)-11);
  $itemstable="{$subject}s";
  $setstable="{$subject}sets";
  $constraint=!$database->isLinePresent("styles","{$subject}setid=$id");
  if($constraint) {
    $database->deleteLines($itemstable,"setid=$id");
    $database->modifyLines($setstable,array("parentid"=>0),"parentid=$id");
    $database->deleteLine($setstable,"id=$id");
    $optimizer->clearCache();
    makeNotification("phpc_removeset_success");
    makeBreak();
    $action="modifystyles";
  }
  else makeError("phpc_removeset_constraint");
}

// ############################################################################
// ############################## Styles Section ##############################
// ############################################################################

/******************************* Add/Edit Style *******************************/

if($action=="addstyle" || $action=="editstyle") {
  $add=$action=="addstyle";
  $formtitle=$add?"phpc_addstyle_form":"phpc_editstyle_form";
  $templatesets=$database->getOrderedLines("templatesets","id");
  $bundlesets=$database->getOrderedLines("bundlesets","id");
  $replacementsets=$database->getOrderedLines("replacementsets","id");
  $matrix=makeMatrix("styles",$add,$infoStyles);
  $matrix["templatesetid"]["options"]=extractArrayColumns($templatesets,"id","title");
  $matrix["bundlesetid"]["options"]=extractArrayColumns($bundlesets,"id","title");
  $matrix["replacementsetid"]["options"]=extractArrayColumns($replacementsets,"id","title");
  if(!count($templatesets)) makeError("phpc_error_notemplatesets"); else
  if(!count($bundlesets)) makeError("phpc_error_nobundlesets"); else
  if(!count($replacementsets)) makeError("phpc_error_noreplacementsets"); else
  makeSmartForm($formtitle,"phpc","do$action",$matrix);
}

if($action=="doaddstyle" || $action=="doeditstyle") {
  $add=$action=="doaddstyle";
  processSmartUpdate("styles",$add,$infoStyles);
  $optimizer->clearCache();
  makeNotification($add?"phpc_addstyle_success":"phpc_editstyle_success");
  makeBreak();
  $action="modifystyles";
}

/******************************** Remove Style ********************************/

if($action=="removestyle") {
  $id=acceptIntParameter("id");
  makePrompt("phpc_removestyle_prompt","phpc.php?action=doremovestyle&id=$id");
}

if($action=="doremovestyle") {
  $id=acceptIntParameter("id");
  $database->deleteLine("styles","id=$id");
  $optimizer->clearCache();
  makeNotification("phpc_removestyle_success");
  makeBreak();
  $action="modifystyles";
}

/******************************* Assign Styles ********************************/

if($action=="assignstyles") {
  $styles1=$database->getOrderedLines("styles","id","visible=1");
  $styles2=$database->getOrderedLines("styles","id");
  $styles1=extractArrayColumns($styles1,"id","title");
  $styles2=extractArrayColumns($styles2,"id","title");
  $id1=$database->getField("styles","id","forusers=1");
  $id2=$database->getField("styles","id","foradmin=1");
  makeForm("header","phpc_assignstyles_form","phpc","doassignstyles");
  makeFormChooser("phpc_assignstyles_forusers","","id1",$id1,$styles1);
  makeFormChooser("phpc_assignstyles_foradmin","","id2",$id2,$styles2);
  makeForm("footer");
}

if($action=="doassignstyles") {
  $id1=acceptIntParameter("id1");
  $id2=acceptIntParameter("id2");
  $database->modifyLines("styles",array("forusers"=>0,"foradmin"=>0));
  $database->modifyField("styles","forusers",1,"id=$id1");
  $database->modifyField("styles","foradmin",1,"id=$id2");
  $optimizer->clearCache();
  makeNotification("phpc_assignstyles_success");
  makeBreak();
  $action="modifystyles";
}

/******************************* Modify Styles ********************************/

if($action=="modifystyles") {
  if(!$database->isLinePresent("styles","forusers=1"))
    $database->modifyField("styles","forusers",1);
  if(!$database->isLinePresent("styles","foradmin=1"))
    $database->modifyField("styles","foradmin",1);
  $styles=$database->getOrderedLines("styles","id");
  $templatesets=$database->getOrderedLines("templatesets","id");
  $bundlesets=$database->getOrderedLines("bundlesets","id");
  $replacementsets=$database->getOrderedLines("replacementsets","id");
  $columns=array(
    "phpc_modifystyles_style",
    "phpc_modifystyles_templateset",
    "phpc_modifystyles_bundleset",
    "phpc_modifystyles_replacementset",
    "phpc_modifystyles_visible",
    "phpc_modifystyles_forusers",
    "phpc_modifystyles_foradmin",
    "phpc_modifystyles_options");
  makeTable("header",$columns);
  foreach($styles as $style) {
    makeTableCellSimple($style["title"]);
    makeTableCellSimple(searchArrayField($templatesets,"id","title",$style["templatesetid"]));
    makeTableCellSimple(searchArrayField($bundlesets,"id","title",$style["bundlesetid"]));
    makeTableCellSimple(searchArrayField($replacementsets,"id","title",$style["replacementsetid"]));
    makeTableCellYesNo($style["visible"]);
    makeTableCellYesNo($style["forusers"]);
    makeTableCellYesNo($style["foradmin"]);
    $links=array(
      "phpc_modifystyles_edit"=>"phpc.php?action=editstyle&id=$style[id]",
      "phpc_modifystyles_remove"=>"phpc.php?action=removestyle&id=$style[id]");
    makeTableCellLinks($links);
  }
  makeTable("footer");
  $items=array("template","bundle","replacement");
  foreach($items as $item) {
    makeBreak();
    $columns=array(
      array("title"=>"phpc_modifystyles_{$item}set","width"=>"33%"),
      array("title"=>"phpc_modifystyles_parent","width"=>"33%"),
      "phpc_modifystyles_options");
    makeTable("header",$columns);
    $variable="{$item}sets";
    foreach($$variable as $set) {
      makeTableCellSimple($set["title"]);
      makeTableCellSimple(searchArrayField($$variable,"id","title",$set["parentid"]));
      $links=array(
        "phpc_modifystyles_edit"=>"phpc.php?action=edit{$item}set&id=$set[id]",
        "phpc_modifystyles_remove"=>"phpc.php?action=remove{$item}set&id=$set[id]");
      makeTableCellLinks($links);
    }
    makeTable("footer");
  }
}

/******************************************************************************/

makeAdminPage("footer");

?>
