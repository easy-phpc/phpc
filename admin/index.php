<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

require "global.php";

$action=acceptStringParameter("action");

/*********************************** Frames ***********************************/

if($action=="" || $action=="index") {
  makeAdminFrames();
}

/************************************ Menu ************************************/

if($action=="menu") {
  $needUpgrade=checkAdminNeedUpgrade();
  $plugins=$needUpgrade?array():getAdminPlugins();
  makeAdminPage("header","menu");
  makeMenu("header");
  if($needUpgrade) {
    makeMenu("separator");
    makeMenuGroup("header","admin_upgrade_menu");
    makeMenuItem("admin_upgrade_upgrade","index.php?action=upgrade");
    makeMenuGroup("footer");
  }
  foreach($plugins as $plugin) {
    makeMenu("plugin",$plugin["script"]);
    if($plugin["separator"]) makeMenu("separator");
    eval($plugin["menucode"]);
  }
  makeMenu("footer");
  makeAdminPage("footer");
}

/********************************** Upgrade ***********************************/

if($action=="upgrade") {
  makeAdminPage("header");
  makePrompt("admin_upgrade_prompt","index.php?action=doupgrade");
  makeAdminPage("footer");
}

if($action=="doupgrade") {
  adminLog();
  makeAdminPage("header");
  makeNotification("admin_upgrade_start");
  processAdminUpgrade();
  makeNotification("admin_upgrade_success");
  makeBreak();
  makeRefreshMenuLink();
  makeAdminPage("footer");
}

/*********************************** Header ***********************************/

if($action=="head") {
  makeAdminPage("header","head");
  makeAdminHeadline();
  makeAdminPage("footer");
}

/************************************ Home ************************************/

if($action=="home") {
  adminLog();
  $plugins=getAdminPlugins();
  $languages=getAdminLanguages("index.php?action=dolanguage&locale=%s");
  $skins=getAdminSkins("index.php?action=doskin&skin=%s");
  $defaultLanguage=$languages[$language["locale"]]["link"];
  $defaultSkin=searchArrayField($skins,"current","link",true);
  $languages=extractArrayColumns($languages,"link","title");
  $skins=extractArrayColumns($skins,"link","title");
  $memberPanel=defined("PhpcMemberPanel");
  $links=array(
    "http://www.phpc.ru/"=>"admin_toolbar_link1",
    "http://www.phpc.ru/manual"=>"admin_toolbar_link2");
  if(!$memberPanel) $links+=array(
    "http://www.php.net/"=>"admin_toolbar_link3",
    "http://www.php.net/docs.php"=>"admin_toolbar_link4",
    "http://www.mysql.com/"=>"admin_toolbar_link5",
    "http://dev.mysql.com/doc/"=>"admin_toolbar_link6");
  $title=$memberPanel?"admin_welcomemember":"admin_welcome";
  $username=$memberPanel?$userInfo["username"]:"";
  makeAdminPage("header");
  makeNotification($title,$username);
  makeBreak(3);
  makeToolbar("header","admin_toolbar1");
  foreach($plugins as $plugin) eval($plugin["homecode"]);
  if($controlState["toolbar"]) makeToolbar("separator","admin_toolbar2");
  if(!$memberPanel) {
    makeToolbarInput("admin_toolbar_php","","_blank:http://www.php.net/manual-lookup.php","get","function");
    makeToolbarInput("admin_toolbar_mysql","","_blank:http://www.mysql.com/search/","get","q","",array("doc"=>1));
  }
  makeToolbarChooser("admin_toolbar_links","",false,$links,false);
  if(count($languages)>1) makeToolbarChooser("admin_toolbar_language","",$defaultLanguage,$languages,true,true,false);
  if(count($skins)>1) makeToolbarChooser("admin_toolbar_skin","",$defaultSkin,$skins,true,true,false);
  makeToolbar("footer");
  makeAdminPage("footer");
}

/****************************** Choose Language *******************************/

if($action=="dolanguage") {
  adminLog("locale");
  setPhpcLocale(acceptStringParameter("locale"));
  makeAdminPage("header");
  makeNotification("admin_toolbar_langsuccess");
  makeBreak();
  makeRefreshMenuLink();
  makeAdminPage("footer");
}

/******************************** Choose Skin *********************************/

if($action=="doskin") {
  adminLog("skin");
  setAdminSkin(acceptStringParameter("skin"));
  makeAdminPage("header");
  makeNotification("admin_toolbar_skinsuccess");
  makeBreak();
  makeRefreshMenuLink();
  makeAdminPage("footer");
}

?>
