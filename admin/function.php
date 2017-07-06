<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

function formatAdminText($text)
{
  $text=filterText($text);
  if(strpos($text,"[")===false) return $text;
  $text=preg_replace("{\[(/?[biu])\]}is","<\\1>",$text);
  $text=preg_replace("{\[font=(\w+)\](.*?)\[/font\]}is","<font class=\"\\1\">\\2</font>",$text);
  $text=preg_replace_callback("{\[url(=[^\]]*)?\](.*?)\[/url\]}is",array("Formatter","processTagUrl"),$text);
  $text=preg_replace_callback("{\[email(=[^\]]*)?\](.*?)\[/email\]}is",array("Formatter","processTagEmail"),$text);
  return $text;
}

function normalizeAdminPath($path="", $calculate=false)
{
  if($calculate) {
    $root=dirname($_SERVER["PHP_SELF"]).$path;
    $pattern="{(?<=/)[^/]*[^./]/\.\./}";
    do { $saved=$root; $root=preg_replace($pattern,"",$root); } while($root!=$saved);
    return $root;
  }
  $simple=$path!="" && char($path,0)!="/" && strpos($path,"?")===false;
  $skin=acceptAlphaParameter(AdminSkinCookie);
  if(defined("AdminPreferredSkin")) $skin=AdminPreferredSkin;
  if($simple && $skin!="") {
    global $fileSystem;
    $filename=AdminFolderLocation."$skin/$path";
    if($fileSystem->isFileExists($filename)) $path="$skin/$path";
  }
  if(char($path,0)!="/") $path=AdminFolderLocation.$path;
  if(defined("PhpcRoot")) $path=PhpcRoot.substr($path,1);
  return $path;
}

function checkAdminAuthorization()
{
  unset($GLOBALS["userInfo"],$GLOBALS["usergroupInfo"]);
  if(isAdministrator(basename($_SERVER["PHP_SELF"],".php"))) return;
  makeAdminPage("header");
  makeAdminAuthorization();
  makeAdminPage("footer");
}

function checkAdminValidity()
{
  if(!AdminRefererCheckEnabled) return;
  $request=stripSlashesSmart($_SERVER["REQUEST_URI"]);
  $request=preg_replace("{(?<=/)index\.php\$}","",$request);
  $request=AdminRefererCheckProtocol.$_SERVER["HTTP_HOST"].$request;
  $referer=stripSlashesSmart(ifset($_SERVER["HTTP_REFERER"],""));
  if($referer=="" && !AdminRefererCheckStrict) return;
  $pattern=preg_replace("{\?.*}","",$request);
  $pattern=preg_replace("{(?<=/)[^/]*\$}","",$pattern);
  if($request==$pattern || !strncmp($referer,$pattern,strlen($pattern))) return;
  $title=$referer==""?"admin_auth_noreferer":"admin_auth_wrongreferer";
  makeAdminPage("header");
  makeError($title,$referer);
  makeAdminPage("footer");
}

function checkAdminNeedUpgrade()
{
  global $database;
  if(defined("AdminSkipCheckUpgrade")) return false;
  if(!$database->isTablePresent("settings")) return false;
  if(!$database->isTablePresent("styles")) return false;
  if(!$database->isTablePresent("cachetemplates")) return true;
  if(!$database->isTablePresent("cachebundles")) return true;
  if(!$database->isFieldPresent("settings","options")) return true;
  if($database->isFieldPresent("messages","special")) return true;
  if(!$database->isFieldPresent("relations","callback")) return true;
  return false;
}

function checkAdminServerConfig()
{
  $settings=array(
    "register_globals"=>false,
    "register_long_arrays"=>false,
    "magic_quotes_gpc"=>false,
    "magic_quotes_runtime"=>false,
    "magic_quotes_sybase"=>false,
    "allow_url_include"=>false);
  $allsettings=array_keys(ini_get_all());
  $result=array();
  foreach($settings as $name=>$right) {
    if(!in_array($name,$allsettings)) continue;
    $value=(boolean)ini_get($name);
    $short=preg_replace("{([a-z])[a-z]*_?}","\\1",$name);
    $correct=$value==$right;
    $result[]=compact("name","short","value","correct");
  }
  $localhost=isLocalhost() && strpos($_SERVER["HTTP_HOST"],".")===false;
  $serverCorrect=getServerAddress()!=PredefinedLocalhost;
  $clientCorrect=getClientAddress()!=PredefinedLocalhost && getClientAddress()!=getServerAddress();
  $result[]=array(
    "name"=>"SERVER_ADDR",
    "short"=>"sa",
    "value"=>getServerAddress(),
    "correct"=>$serverCorrect || $localhost);
  $result[]=array(
    "name"=>"REMOTE_ADDR",
    "short"=>"ra",
    "value"=>getClientAddress(),
    "correct"=>$clientCorrect || $localhost);
  return $result;
}

function checkConstraints($masterTable, $masterKey, $slaveTable, $slaveKey, $slaveField, $zero=false)
{
  global $database;
  $errors=array();
  $masterData=$database->getOrderedLines($masterTable,$masterKey);
  $slaveData=$database->getOrderedLines($slaveTable,$slaveKey);
  $valid=$zero===false?array():array($zero);
  foreach($masterData as $line) $valid[]=$line[$masterKey];
  foreach($slaveData as $line)
    if(!in_array($line[$slaveField],$valid)) $errors[]=$line[$slaveKey];
  return $errors;
}

function checkRecursion($table, $key, $field, $zero=0)
{
  global $database;
  $errors=array();
  $data=$database->getOrderedLines($table,$key);
  $data=extractArrayColumns($data,$key,$field);
  foreach($data as $index=>$value) {
    $visited=array($index);
    while($value!=$zero) {
      if(in_array($value,$visited)) {
        foreach($visited as $position=>$item)
          if($item==$value) break; else unset($visited[$position]);
        $errors[]=min($visited);
        break;
      }
      $visited[]=$value;
      $data[$index]=$zero;
      $index=$value;
      $value=$data[$index];
    }
  }
  return $errors;
}

function checkPluginInstalled($table)
{
  global $database;
  if(!$database->isTablePresent($table)) return;
  makeError("admin_error_already");
  makeBreak();
  makeRefreshMenuLink();
  makeAdminPage("footer");
}

function adminLog($params="")
{
  global $database, $userInfo;
  $action=acceptStringParameter("action");
  if($action=="" || $action=="image" || $action=="icon") return;
  $params=explodeSmart(",",$params);
  foreach($params as $index=>$param) {
    $value=acceptStringParameter($param);
    if($value=="") { unset($params[$index]); continue; }
    $params[$index]="$param=$value";
  }
  $values=array(
    "userid"=>defined("PhpcMemberPanel")?ifset($userInfo["id"],0):false,
    "script"=>basename($_SERVER["PHP_SELF"],".php"),
    "action"=>$action,
    "extra"=>implode(",",$params),
    "dateline"=>phpctime(),
    "ipaddress"=>getClientAddress());
  if($values["userid"]===false) unset($values["userid"]);
  $table=isset($values["userid"])?"memberlog":"adminlog";
  if($database->isTablePresent($table)) $database->addLine($table,$values);
}

function adminActions($actions)
{
  if(!defined("PhpcMemberPanel") && isAdministrator()) return;
  if(!is_array($actions)) $actions=explodeSmart(",",$actions);
  $action=acceptStringParameter("action");
  if(substr($action,0,2)=="do") $action=substr($action,2);
  if(in_array($action,$actions)) sendParameter("action","");
}

function checkAdminRights($rights, $strict=true)
{
  global $usergroupInfo;
  if(!defined("PhpcMemberPanel") && isAdministrator()) return true;
  if(!defined("PhpcMemberPanel")) return $strict?makeAdminPage("footer"):false;
  if(!is_array($rights)) $rights=explodeSmart(",",$rights);
  foreach($rights as $right) if(!ifset($usergroupInfo[$right]))
    return $strict?makeAdminPage("footer"):false;
  return true;
}

function processGlobalCache()
{
  global $language, $database, $settings;
  $prefix=defined("DatabasePrefix")?DatabasePrefix:"";
  if(!$database->isTablePresent($prefix."settings")) return;
  $cache=$database->getField($prefix."settings","value","id=1");
  $settings=$cache?unserialize($cache):array();
  if(PhpcLocale!="") return;
  $cache=$database->getLine($prefix."messages","id=1");
  if($cache) $cache=getLocalizedField($cache,"content");
  if($cache) $cache=unserialize($cache);
  if($cache) foreach($cache as $key=>$value) $language[$key]=$value;
}

function updateGlobalCache()
{
  global $database;
  $settings=$database->getOrderedLines("settings","groupid,id","groupid!=0");
  $settings=arrangeArrayValues(extractArrayColumns($settings,"name","value"));
  $database->modifyField("settings","value",serialize($settings),"id=1");
  $locales=explode(",",PhpcLocalesList);
  $fields=$database->getFieldsList("messages");
  $messages=$database->getOrderedLines("messages","id","id!=1");
  $cache=array();
  foreach($locales as $locale) {
    $field="content$locale";
    if(!in_array($field,$fields)) continue;
    $cache[$field]=extractArrayColumns($messages,"name",$field);
    $cache[$field]=serialize(arrangeArrayValues($cache[$field]));
  }
  $database->modifyLine("messages",$cache,"id=1");
  processGlobalCache();
}

function processAdminUpgrade()
{
  global $database, $optimizer;
  $tables=$database->getTablesList();
  $settings=!$database->isFieldPresent("settings","options");
  $messages=$database->isFieldPresent("messages","special");
  $sessions=$database->getTableInformation("sessions");
  $sessions=!isset($sessions["indexes"]["ipaddress"]);
  $relations=!$database->isFieldPresent("relations","callback");
  if($relations) $relations=getAdminTableDefinition("options","relations");
  $cachetemplates=in_array("cachetemplates",$tables)?false:getAdminTableDefinition("phpc","cachetemplates");
  $cachebundles=in_array("cachebundles",$tables)?false:getAdminTableDefinition("phpc","cachebundles");
  $cache=($cachetemplates || $cachebundles) && in_array("cache",$tables);
  if($settings) {
    $type=$database->getColumnType("textarea");
    $database->addColumn("settings","options",$type,0,"kind");
    makeNotification("admin_installalter","settings");
  }
  if($messages) {
    $database->deleteColumn("messages","special");
    makeNotification("admin_installalter","messages");
  }
  if($sessions) {
    $database->addIndex("sessions","ipaddress",false,"ipaddress");
    makeNotification("admin_installalter","sessions");
  }
  if($relations) {
    if($database->isLinePresent("relations"))
      $database->renameTable("relations","relations_save");
      else $database->deleteTable("relations");
    $database->customQueryBoolean($relations);
    makeNotification("admin_installtable","relations");
  }
  if($cachetemplates) {
    $database->customQueryBoolean($cachetemplates);
    makeNotification("admin_installtable","cachetemplates");
  }
  if($cachebundles) {
    $database->customQueryBoolean($cachebundles);
    makeNotification("admin_installtable","cachebundles");
  }
  if($cache) {
    $database->deleteTable("cache");
    makeNotification("admin_installdrop","cache");
  }
  updateGlobalCache();
  $optimizer->clearCache();
}

function getAdminStyle()
{
  global $database;
  static $cache;
  if(!isset($cache)) {
    $cache=$database->getLine("styles","foradmin=1");
    if(!$cache) makeAdminError("admin_error_noadminstyle");
  }
  return $cache;
}

function getAdminStylesList()
{
  global $fileSystem;
  $result=$fileSystem->getFolder(AdminFolderLocation,".css");
  $result=array_diff($result,array(AdminStylesLocation));
  sort($result);
  array_unshift($result,AdminStylesLocation);
  return array_map("normalizeAdminPath",$result);
}

function getAdminPlugins()
{
  global $fileSystem, $database;
  $result=array();
  $signature="PHPC Control Panel Plugin";
  $patternRedirect="{<\?php\s+require\s+\"([^\"]*)\";\s+\?>}";
  $patternSignature=
    "{^<\?php\s+// $signature \(([\d.]+)\)[^\n]*\n".
    "\s*(?://[^\n]*\n\s*)*/\*(.*?)\*/\s*(/\*(.*?)\*/)?}s";
  $folder=$fileSystem->getFolder(".",".php");
  $restrict=!$database->isTablePresent("settings") || !$database->isTablePresent("styles");
  foreach($folder as $filename) {
    if(!isAdministrator($script=basename($filename,".php"))) continue;
    if($restrict && !phpcmatch(AdminHighPriorityPlugins,$script)) continue;
    $content=$fileSystem->openFile($filename);
    if(preg_match($patternRedirect,$content,$matches))
      $content=$fileSystem->openFile($matches[1]);
    if(!preg_match($patternSignature,$content,$matches)) continue;
    while(count($matches)<=4) $matches[]="";
    $result[]=array(
      "script"=>$script,
      "weight"=>(float)$matches[1],
      "filename"=>$filename,
      "menucode"=>trim($matches[2])."\r\n",
      "homecode"=>trim($matches[4])."\r\n");
  }
  $columns=array("weight"=>array(),"filename"=>array("caseless"=>true));
  $callback=createComplexCompareFunction($columns);
  usort($result,$callback);
  $oldweight=-1;
  foreach($result as $index=>$plugin) {
    $weight=floor($plugin["weight"]);
    $result[$index]["separator"]=$weight!=$oldweight;
    $oldweight=$weight;
  }
  return $result;
}

function getAdminLanguages($linkmask=false)
{
  global $language;
  $result=array();
  $locales=PhpcLocale==""?explode(",",PhpcLocalesList):array();
  $locales[]=PhpcLocale;
  foreach($locales as $locale) {
    $title=$locale!=""?$language["locales"][$locale]:$language["admin_nolanguage"];
    if($linkmask!==false) $link=format($linkmask,$locale);
    $result[$locale]=compact("title","link");
  }
  return $result;
}

function getAdminSkins($linkmask=false)
{
  global $language, $fileSystem;
  $result=array();
  $cookie=acceptAlphaParameter(AdminSkinCookie);
  $folders=$fileSystem->getSubfolders(AdminFolderLocation);
  array_unshift($folders,"");
  foreach($folders as $folder) {
    if($folder!="") {
      $styles=AdminFolderLocation."$folder/".AdminStylesLocation;
      $content=$fileSystem->openFile($styles);
      $pattern="{^/\*(.*?)\*/}s";
      if(!$content || !preg_match($pattern,$content,$matches)) continue;
      $title=optimizeTextStrict($matches[1]);
    }
    else $title=$language["admin_toolbar_skindefault"];
    if($linkmask!==false) $link=format($linkmask,$folder);
    $current=$folder==$cookie;
    $result[$folder]=compact("title","link","current");
  }
  if(defined("AdminPreferredSkin"))
    $result=selectArrayKeys($result,array(AdminPreferredSkin));
  return $result;
}

function setAdminSkin($folder)
{
  phpcsetcookie(AdminSkinCookie,$folder,true);
}

function getAdminTableDefinition($script, $table)
{
  global $fileSystem;
  $content=$fileSystem->openFile("$script.php");
  $header="\\\$database->customQuery\w*\((?=\"CREATE TABLE $table\b)";
  $footer="\);\s*makeNotification\(\"admin_installtable\",\"$table\"\);";
  $pattern=phpcpattern("{{$header}(.*){$footer}}s");
  if(!preg_match($pattern,$content,$matches)) return false;
  $result=preg_replace("{\"\s*\.\s*\"}","",$matches[1]);
  return strpos($result,"\$")===false?unquoteText($result):false;
}

function getDefaultGroupingOptions()
{
  return array(
    "itemField"=>"groupid",
    "groupField"=>"id",
    "groupFieldDefault"=>0,
    "groupTitleField"=>"title",
    "groupTitleOnce"=>"admin_treeonce",
    "groupTitleMany"=>"admin_treemany",
    "groupTitleLang"=>true,
    "caselessCompare"=>false,
    "prefixCompare"=>false,
    "skipEmpty"=>false,
    "skipUngrouped"=>true);
}

function createGroupingTree($groups, $items, $options=array())
{
  global $language;
  $options+=getDefaultGroupingOptions();
  $itemField=$options["itemField"];
  $groupField=$options["groupField"];
  foreach($groups as $index=>$group) $groups[$index]["items"]=array();
  foreach($items as $itemKey=>$item) {
    $method="searchArrayKey".($options["prefixCompare"]?"ByPrefix":"");
    $groupKey=$method($groups,$groupField,$item[$itemField],$options["caselessCompare"]);
    if($groupKey===false) continue;
    $groups[$groupKey]["items"][$itemKey]=$item;
    unset($items[$itemKey]);
  }
  if($options["skipEmpty"]) foreach($groups as $index=>$group)
    if(!count($group["items"])) unset($groups[$index]);
  if($options["skipUngrouped"]) return $groups;
  if($options["skipEmpty"] && !count($items)) return $groups;
  $title=$options[count($groups)?"groupTitleMany":"groupTitleOnce"];
  if($options["groupTitleLang"]) $title=$language[$title];
  $defaultGroup=array(
    $groupField=>$options["groupFieldDefault"],
    $options["groupTitleField"]=>$title,
    "items"=>$items);
  $groups[]=$defaultGroup;
  return $groups;
}

function getTablePagePortion($table, $order, $conditions, $size, &$page, &$total, $all=true)
{
  global $database;
  if($all && acceptStringParameter("page")=="all") {
    $page=$total=false;
    if($order!="")
      return $database->getOrderedLines($table,$order,$conditions);
      else return $database->getLines($table,$conditions);
  }
  $count=$database->getLinesCount($table,$conditions);
  $total=max(ceil($count/$size),1);
  $page=acceptIntParameter("page",1,$total);
  $offset=($page-1)*$size;
  if($order!="")
    return $database->getOrderedLinesRange($table,$order,$offset,$size,$conditions);
    else return $database->getLinesRange($table,$offset,$size,$conditions);
}

function prepareTablePager($script, $action, $append="", $remember=false)
{
  $navigate=$action==acceptStringParameter("action");
  if(!$navigate) $remember=true;
  $page=acceptStringParameter("page");
  $cookieName="adminpager_{$script}_$action$append";
  $cookieValue=acceptStringParameter($cookieName);
  if($navigate && $cookieValue=="all") $cookieValue="";
  if(!$page && $remember) $page=$cookieValue;
  if($page!="all") $page=$page?(int)$page:"";
  sendParameter("page",$page);
  phpcsetcookie($cookieName,$page);
}

/******************************************************************************/

function installFolder($folder)
{
  global $fileSystem;
  $path="";
  $folder=rtrim($folder,"/");
  if(char($folder,0)=="/") { $path="/"; $folder=substr($folder,1); }
  $folders=explodeSmart("/",$folder);
  if(!count($folders)) return;
  $success=true;
  foreach($folders as $folderpart) {
    $file=$path.$folderpart;
    if(!$fileSystem->isFileExists($file)) $fileSystem->createFolder($file);
    if(!$fileSystem->isFileExists($file)) { $success=false; break; }
    $path.="$folderpart/";
  }
  $report="admin_installfolder_".($success?"success":"failure");
  makeNotification($report,$folder);
}

function installTableLocales($field, $type)
{
  global $language;
  if(PhpcLocale!="") return "$field$language[locale] $type";
  $locales=explode(",",PhpcLocalesList);
  $result=array();
  foreach($locales as $locale) $result[]="$field$locale $type";
  if(!count($result)) $result[]="$field $type";
  return implode(",",$result);
}

function installOptions($title, $settings, $lang=true)
{
  global $language, $database;
  if($lang) $title=ifset($language[$title],"");
  $displayorder=$database->getMaxField("settinggroups","displayorder")+1;
  $values=compact("title","displayorder");
  $database->addLineStrict("settinggroups",$values);
  $groupid=$database->getCounterValue();
  foreach($settings as $setting) {
    if(!isset($setting["groupid"])) $setting["groupid"]=$groupid;
    if(isset($setting["title"]) && $lang)
      $setting["title"]=ifset($language[$setting["title"]],"");
    if(isset($setting["description"]) && $lang)
      $setting["description"]=ifset($language[$setting["description"]],"");
    if(!isset($setting["kind"])) $setting["kind"]="input";
    if(!isset($setting["visible"])) $setting["visible"]=1;
    $database->addLineStrict("settings",$setting);
  }
  updateGlobalCache();
  makeNotification("admin_installoptions");
}

function installRelations($master, $slave, $operation, $callback)
{
  global $database;
  if(!phpccallback($callback)) $callback="";
  $values=compact("master","slave","operation","callback");
  $database->addLineStrict("relations",$values,true);
  makeNotification("admin_installrelations");
}

function installPage($name, $template, $bundles, $title, $visible=1, $parentid=-1, $alias="", $params="", $strict=true)
{
  global $database, $optimizer;
  static $fields;
  if(!isset($fields)) $fields=$database->getFieldsList("pages");
  if($parentid==-1)
    $parentid=(int)$database->getField("pages","id","name='general'");
  $values=compact("parentid","name","alias","template","bundles","params","visible");
  $locales=explode(",",PhpcLocalesList);
  foreach($locales as $locale) if(in_array("title$locale",$fields))
    $values["title$locale"]=getPhpcLocaleValue($locale,$title);
  $swapname="{$name}_save";
  if(!$database->addLine("pages",$values)) do {
    if(!$strict) return $database->getField("pages","id","name=".slashes($name));
    $modify=array("name"=>$swapname,"alias"=>"","visible"=>0);
    $success=$database->modifyLine("pages",$modify,"name=".slashes($name));
    if($success) { $database->addLineStrict("pages",$values); break; }
    $swapname=incrementIdentifier($swapname);
  } while(true);
  $result=$database->getCounterValue();
  $optimizer->clearCache();
  makeNotification("admin_installpage",$name);
  return $result;
}

function installTemplate($name, $content, $parent="", $localize=true, $strict=true)
{
  global $database, $optimizer;
  $style=getAdminStyle();
  $setid=$style["templatesetid"];
  $function="global \$language; return ifset(\$language[\$matches[1]],\"\");";
  $callback=create_function("\$matches",$function);
  $pattern="{<var:language:(\w+) nofilter>}";
  if($localize && PhpcLocale!="")
    $content=preg_replace_callback($pattern,$callback,$content);
  $content=trim($content)."\r\n";
  $values=compact("setid","name","parent","content");
  $swapname="{$name}_save";
  if(!$database->addLine("templates",$values)) do {
    if(!$strict) return;
    $success=$database->modifyField
      ("templates","name",$swapname,"setid=$setid AND name=".slashes($name));
    if($success) { $database->addLineStrict("templates",$values); break; }
    $swapname=incrementIdentifier($swapname);
  } while(true);
  $optimizer->clearCache();
  makeNotification("admin_installtemplate",$name);
}

function installTemplates($templates, $parents=false, $caseless=true)
{
  if($parents===false) $parents=array("html"=>"",""=>"general");
  foreach($templates as $name=>$content) {
    $success=false;
    foreach($parents as $prefix=>$parent) {
      $length=phpcstrlen($prefix);
      if($caseless && !phpcstrncasecmp($name,$prefix,$length)) { $success=true; break; }
      if(!$caseless && !phpcstrncmp($name,$prefix,$length)) { $success=true; break; }
    }
    if(!$success) $parent="";
    installTemplate($name,$content,$parent);
  }
}

function installBundle($name, $content, $plugins="", $strict=true)
{
  global $database, $optimizer;
  $style=getAdminStyle();
  $setid=$style["bundlesetid"];
  $content=trim($content)."\r\n";
  $values=compact("setid","name","plugins","content");
  $swapname="{$name}_save";
  if(!$database->addLine("bundles",$values)) do {
    if(!$strict) return;
    $success=$database->modifyField
      ("bundles","name",$swapname,"setid=$setid AND name=".slashes($name));
    if($success) { $database->addLineStrict("bundles",$values); break; }
    $swapname=incrementIdentifier($swapname);
  } while(true);
  $optimizer->clearCache();
  makeNotification("admin_installbundle",$name);
}

function installBundles($bundles, $plugins=array(), $caseless=true)
{
  foreach($bundles as $name=>$content) {
    $success=false;
    foreach($plugins as $prefix=>$plugin) {
      $length=phpcstrlen($prefix);
      if($caseless && !phpcstrncasecmp($name,$prefix,$length)) { $success=true; break; }
      if(!$caseless && !phpcstrncmp($name,$prefix,$length)) { $success=true; break; }
    }
    if(!$success) $plugin="";
    installBundle($name,$content,$plugin);
  }
}

function installReplacements($replacements, $strict=true)
{
  global $database, $optimizer;
  $style=getAdminStyle();
  $setid=$style["replacementsetid"];
  foreach($replacements as $name=>$content) {
    $values=compact("setid","name","content");
    $database->addLine("replacements",$values,$strict);
  }
  $optimizer->clearCache();
  makeNotification("admin_installreplacements");
}

function installFormatting($formatting, $lang=true, $strict=true)
{
  global $language, $database, $optimizer;
  $useorder=$database->getMaxField("formatting","useorder");
  foreach($formatting as $entry) {
    if(isset($entry["title"]) && $lang)
      $entry["title"]=ifset($language[$entry["title"]],"");
    if(isset($entry["sample"]) && $lang)
      $entry["sample"]=ifset($language[$entry["sample"]],"");
    $entry["useorder"]=++$useorder;
    $database->addLine("formatting",$entry,$strict);
  }
  $optimizer->clearCache();
  makeNotification("admin_installformatting");
}

function installLinkStyles($linkstyles, $strict=true)
{
  global $database, $optimizer;
  $useorder=$database->getMaxField("linkstyles","useorder");
  foreach($linkstyles as $linkstyle) {
    $linkstyle["useorder"]=++$useorder;
    $database->addLine("linkstyles",$linkstyle,$strict);
  }
  $optimizer->clearCache();
  makeNotification("admin_installlinkstyles");
}

/******************************************************************************/

function updatePageBundlesList($name, $bundles, $remove=false)
{
  global $database, $optimizer;
  if(!is_array($bundles)) $bundles=explodeSmart(",",$bundles);
  $page=$database->getLine("pages","name=".slashes($name));
  if(!$page) return;
  $list=explodeSmart(",",$page["bundles"]);
  if($remove)
    $list=array_unique(array_diff($list,$bundles));
    else $list=array_unique(array_merge($list,$bundles));
  $database->modifyField("pages","bundles",implode(",",$list),"id=$page[id]");
  $optimizer->clearCache();
  makeNotification("admin_installalterpage",$name);
}

function updateTemplateTagParams($name, $tag, $params, $remove=false)
{
  global $database, $optimizer;
  $style=getAdminStyle();
  $setid=$style["templatesetid"];
  $template=$database->getLine("templates","setid=$setid AND name=".slashes($name));
  if(!$template) return;
  $tagStringBlock="\"(?:\\\\\\\\|\\\\\"|.)*?\"";
  $tagPattern="{<$tag\b((?:$tagStringBlock|.)*?)(/?)>}s";
  $tagParamsPattern="{(\w+)(?:=(\w+(?::\w+)*)|=($tagStringBlock))?}s";
  if(!preg_match($tagPattern,$template["content"],$matches)) return;
  $original=$matches[0];
  $trail=$matches[2];
  preg_match_all($tagParamsPattern,$matches[1],$matches);
  $parameters=array();
  for($index=0; $index<count($matches[0]); $index++)
    $parameters[$matches[1][$index]]=$matches[2][$index].$matches[3][$index];
  foreach($params as $param=>$value) if($value===true) $params[$param]="";
  if($remove)
    $parameters=array_diff_assoc($parameters,$params);
    else $parameters=array_merge($parameters,$params);
  foreach($parameters as $param=>$value)
    $tag.=" $param".($value!=""?"=$value":"");
  $content=str_replace($original,"<$tag$trail>",$template["content"]);
  $database->modifyField("templates","content",$content,"id=$template[id]");
  $optimizer->clearCache();
  makeNotification("admin_installaltertemplate",$name);
}

function updateBundlePluginsList($name, $plugins, $remove=false)
{
  global $database, $optimizer;
  if(!is_array($plugins)) $plugins=explodeSmart(",",$plugins);
  $style=getAdminStyle();
  $setid=$style["bundlesetid"];
  $bundle=$database->getLine("bundles","setid=$setid AND name=".slashes($name));
  if(!$bundle) return;
  $list=explodeSmart(",",$bundle["plugins"]);
  if($remove)
    $list=array_unique(array_diff($list,$plugins));
    else $list=array_unique(array_merge($list,$plugins));
  $database->modifyField("bundles","plugins",implode(",",$list),"id=$bundle[id]");
  $optimizer->clearCache();
  makeNotification("admin_installalterbundle",$name);
}

/******************************************************************************/

function makeMatrix($table, $add, $info, $strict=true)
{
  global $database;
  prepareSmartData($add,$info,$items,$conditions);
  $record=$add?array():$database->getLine($table,$conditions);
  if(!is_array($record)) $record=array();
  $result=array();
  $partialUpdate=false;
  $ignore=$add?array("","none","key"):array("","none");
  foreach($items as $item) {
    extract($item);
    if(in_array($type,$ignore)) continue;
    $class="CustomControl".phpcucfirst($type);
    if($add) switch($type) {
      case "separator": break;
      case "date": $record[$field]=false; break;
      case "datetime": $record[$field]=false; break;
      case "file": break;
      case "chooser": $record[$field]=false; break;
      case "groupchooser": $record[$field]=false; break;
      case "radio": $record[$field]=false; break;
      case "yesno": $record[$field]=1; break;
      case "yesnoany": $record[$field]=1; break;
      case "yesnonull": $record[$field]=1; break;
      case "approval": $record[$field]=ApprovalValueAccept; break;
      default: $record[$field]="";
    }
    if($add && class_exists($class)) {
      $callback=array($class,"setDefaultValue");
      call_user_func_array($callback,array($item,&$record));
    }
    if(!$add && $partial) {
      $result["{$field}_oldvalue"]=makeMatrixItemHidden($record[$field]);
      $partialUpdate=true;
    }
    $value=array_key_exists($field,$record)?$record[$field]:"";
    if(!$value && !$strict) $value="";
    switch($type) {
      case "separator": $result[$field]=makeMatrixSeparator($title,$lang); break;
      case "key": $result[$field]=makeMatrixItemHidden($value); break;
      case "hidden": $result[$field]=makeMatrixItemHidden($value); break;
      case "input": $result[$field]=makeMatrixItemInput($title,$description,$value,$lang); break;
      case "inputorder": $result[$field]=makeMatrixItemInputOrder($title,$description,$value,$lang); break;
      case "password": $result[$field]=makeMatrixItemPassword($title,$description,$value,$lang); break;
      case "date": $result[$field]=makeMatrixItemDate($title,$description,$value,$lang); break;
      case "datetime": $result[$field]=makeMatrixItemDatetime($title,$description,$value,$lang); break;
      case "file": $result[$field]=makeMatrixItemFile($title,$description,$lang); break;
      case "textarea": $result[$field]=makeMatrixItemTextarea($title,$description,$value,$lang); break;
      case "editor": $result[$field]=makeMatrixItemEditor($title,$description,$value,$lang); break;
      case "htmleditor": $result[$field]=makeMatrixItemHTMLEditor($title,$description,$value,$lang); break;
      case "tpleditor": $result[$field]=makeMatrixItemTPLEditor($title,$description,$value,$lang); break;
      case "phpeditor": $result[$field]=makeMatrixItemPHPEditor($title,$description,$value,$lang); break;
      case "chooser": $result[$field]=makeMatrixItemChooser($title,$description,$value,array(),$lang); break;
      case "groupchooser": $result[$field]=makeMatrixItemGroupChooser($title,$description,$value,array(),$lang); break;
      case "selector": $result[$field]=makeMatrixItemSelector($title,$description,array(),explodeSmart(",",$value),$lang); break;
      case "radio": $result[$field]=makeMatrixItemRadio($title,$description,$value,array(),$lang); break;
      case "yesno": $result[$field]=makeMatrixItemYesNo($title,$description,$value,$lang); break;
      case "yesnoany": $result[$field]=makeMatrixItemYesNoAny($title,$description,$value,$lang); break;
      case "yesnonull": $result[$field]=makeMatrixItemYesNoNull($title,$description,$value,$lang); break;
      case "approval": $result[$field]=makeMatrixItemApproval($title,$description,$value,$lang); break;
      default: $result[$field]=makeMatrixItemUnknown($type,$title,$description,$value,$lang);
    }
    if(class_exists($class)) {
      $callback=array($class,"makeMatrixItem");
      $result[$field]=call_user_func($callback,$item,$record);
    }
  }
  if($partialUpdate) $result["phpcpartialupdate"]=makeMatrixItemHidden(1);
  return $result;
}

function makeMatrixSeparator($title, $lang=true)
{
  $type="separator";
  return compact("type","title","lang");
}

function makeMatrixItemHidden($value="")
{
  $type="hidden";
  return compact("type","value");
}

function makeMatrixItemInput($title, $description, $value="", $lang=true)
{
  $type="input";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemInputOrder($title, $description, $value="", $lang=true)
{
  $type="inputorder";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemPassword($title, $description, $value="", $lang=true)
{
  $type="password";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemDate($title, $description, $value=false, $lang=true)
{
  $type="date";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemDatetime($title, $description, $value=false, $lang=true)
{
  $type="datetime";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemFile($title, $description, $lang=true)
{
  $type="file";
  return compact("type","title","description","lang");
}

function makeMatrixItemTextarea($title, $description, $value="", $lang=true)
{
  $type="textarea";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemEditor($title, $description, $value="", $lang=true)
{
  $type="editor";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemHTMLEditor($title, $description, $value="", $lang=true)
{
  $type="htmleditor";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemTPLEditor($title, $description, $value="", $lang=true)
{
  $type="tpleditor";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemPHPEditor($title, $description, $value="", $lang=true)
{
  $type="phpeditor";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemChooser($title, $description, $value=false, $options=array(), $lang=true)
{
  $type="chooser";
  return compact("type","title","description","value","options","lang");
}

function makeMatrixItemGroupChooser($title, $description, $value=false, $groups=array(), $lang=true)
{
  $type="groupchooser";
  return compact("type","title","description","value","groups","lang");
}

function makeMatrixItemSelector($title, $description, $options=array(), $selected=array(), $lang=true)
{
  $type="selector";
  return compact("type","title","description","options","selected","lang");
}

function makeMatrixItemRadio($title, $description, $value=false, $options=array(), $lang=true)
{
  $type="radio";
  return compact("type","title","description","value","options","lang");
}

function makeMatrixItemYesNo($title, $description, $value=1, $lang=true)
{
  $type="yesno";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemYesNoAny($title, $description, $value=1, $lang=true)
{
  $type="yesnoany";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemYesNoNull($title, $description, $value=1, $lang=true)
{
  $type="yesnonull";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemApproval($title, $description, $value=ApprovalValueAccept, $lang=true)
{
  $type="approval";
  return compact("type","title","description","value","lang");
}

function makeMatrixItemUnknown($type, $title, $description, $value="", $lang=true)
{
  return compact("type","title","description","value","lang");
}

/******************************************************************************/

function localizeSmartInfo($table, $info, $localizedFields)
{
  global $language;
  $locales=getTableLocales($table,$localizedFields);
  $insert=array();
  foreach($localizedFields as $field=>$data) $insert[$field]="";
  foreach($locales as $locale) {
    $name=$language["locales"][$locale];
    foreach($localizedFields as $field=>$data) {
      if(!isset($data["title"])) $data["title"]="";
      if(!isset($data["description"])) $data["description"]="";
      $title=format(ifset($language[$data["title"]],""),$name);
      $title=str_replace(",","&#44;",$title);
      $title=str_replace(":","&#58;",$title);
      $description=format(ifset($language[$data["description"]],""),$name);
      $description=str_replace(",","&#44;",$description);
      $description=str_replace(":","&#58;",$description);
      $insert[$field].=",$field$locale:$data[type]:$title:$description:0";
    }
  }
  return format($info,array_values($insert));
}

function prepareSmartData($add, $info, &$items, &$conditions)
{
  $items=explodeSmart(",",$info);
  foreach($items as $index=>$item) {
    $split=explode(":",$item);
    $partial=count($split)>2 && $split[1]=="key";
    if($partial) array_splice($split,1,1);
    $items[$index]=array(
      "field"=>$split[0],
      "type"=>ifset($split[1],"none"),
      "title"=>ifset($split[2],""),
      "description"=>ifset($split[3],""),
      "lang"=>(boolean)ifset($split[4],true),
      "partial"=>$partial);
  }
  $conditions=array();
  if(!$add) {
    $partialUpdate=acceptIntParameter("phpcpartialupdate",0,1);
    foreach($items as $item) if($item["type"]=="key" || $item["partial"]) {
      $field=$item["field"];
      $integer=substr($field,-2)=="id";
      if($item["partial"] && $partialUpdate) $field.="_oldvalue";
      $value=$integer?acceptIntParameter($field):
        slashes(acceptStringParameter($field));
      $conditions[]="$item[field]=$value";
    }
  }
  $conditions=implode(" AND ",$conditions);
}

function makeSmartForm($title, $script, $action, $matrix, $lang=true)
{
  makeForm("header",$title,$script,$action,$lang);
  foreach($matrix as $field=>$item) {
    if(($type=ifset($item["type"],""))=="") continue;
    $title=ifset($item["title"]);
    $description=ifset($item["description"]);
    $value=array_key_exists("value",$item)?$item["value"]:"";
    $lang=ifset($item["lang"]);
    $class="CustomControl".phpcucfirst($type);
    if(class_exists($class))
      call_user_func(array($class,"makeFormItem"),$field,$item);
    else switch($type) {
      case "separator": makeForm("separator",$title,"","",$lang); break;
      case "hidden": makeFormHidden($field,$value); break;
      case "input": makeFormInput($title,$description,$field,$value,$lang); break;
      case "inputorder": makeFormInputOrder($title,$description,$field,$value,$lang); break;
      case "password": makeFormPassword($title,$description,$field,$value,$lang); break;
      case "date": makeFormDate($title,$description,$field,$value,$lang); break;
      case "datetime": makeFormDatetime($title,$description,$field,$value,$lang); break;
      case "file": makeFormFile($title,$description,$field,$lang); break;
      case "textarea": makeFormTextarea($title,$description,$field,$value,$lang); break;
      case "editor": makeFormEditor($title,$description,$field,$value,$lang); break;
      case "htmleditor": makeFormHTMLEditor($title,$description,$field,$value,$lang); break;
      case "tpleditor": makeFormTPLEditor($title,$description,$field,$value,$lang); break;
      case "phpeditor": makeFormPHPEditor($title,$description,$field,$value,$lang); break;
      case "chooser": makeFormChooser($title,$description,$field,$value,$item["options"],$lang); break;
      case "groupchooser": makeFormGroupChooser($title,$description,$field,$value,$item["groups"],$lang); break;
      case "selector": makeFormSelector($title,$description,"{$field}[]",$item["options"],$item["selected"],$lang); break;
      case "radio": makeFormRadio($title,$description,$field,$value,$item["options"],$lang); break;
      case "yesno": makeFormYesNo($title,$description,$field,$value,$lang); break;
      case "yesnoany": makeFormYesNoAny($title,$description,$field,$value,$lang); break;
      case "yesnonull": makeFormYesNoNull($title,$description,$field,$value,$lang); break;
      case "approval": makeFormApproval($title,$description,$field,$value,$lang); break;
      default: makeFormUnknown($type,$title,$description,$field,$value,$lang);
    }
  }
  makeForm("footer");
}

function acceptSmartForm($add, $info, &$conditions)
{
  prepareSmartData($add,$info,$items,$conditions);
  $record=array();
  $ignore=array("","separator","none","key","file");
  foreach($items as $item) {
    extract($item);
    if(in_array($type,$ignore)) continue;
    $class="CustomControl".phpcucfirst($type);
    switch($type) {
      case "inputorder": $record[$field]=acceptIntParameter($field); break;
      case "date": $record[$field]=acceptDateParameter($field); break;
      case "datetime": $record[$field]=datetime2timestamp(acceptStringParameter($field)); break;
      case "editor": $record[$field]=acceptStringParameter($field,false,false); break;
      case "htmleditor": $record[$field]=acceptStringParameter($field,false,false); break;
      case "tpleditor": $record[$field]=acceptStringParameter($field,false,false); break;
      case "phpeditor": $record[$field]=acceptStringParameter($field,false,false); break;
      case "selector": $record[$field]=implode(",",acceptArrayParameter($field)); break;
      case "yesno": $record[$field]=acceptIntParameter($field,0,1); break;
      case "yesnoany": $record[$field]=acceptIntParameter($field,-1,1); break;
      case "yesnonull": $record[$field]=acceptStringParameter($field)!=""?acceptIntParameter($field,0,1):null; break;
      case "approval": $record[$field]=acceptIntParameter($field,0,2); break;
      default: $record[$field]=acceptStringParameter($field);
    }
    if(class_exists($class)) {
      $callback=array($class,"acceptFormValue");
      call_user_func_array($callback,array($item,&$record));
    }
  }
  return $record;
}

function makeSmartUpdate($table, $add, $values, $conditions)
{
  global $database;
  if($add)
    return $database->addLine($table,$values);
    else return $database->modifyLine($table,$values,$conditions);
}

function processSmartUpdate($table, $add, $info)
{
  $values=acceptSmartForm($add,$info,$conditions);
  return makeSmartUpdate($table,$add,$values,$conditions);
}

function processSmartColumnsUpdate($masterTable, $slaveTables, $add, $names=array(), $options=array())
{
  global $database;
  if(!is_array($slaveTables)) $slaveTables=explodeSmart(",",$slaveTables);
  $names+=array("id"=>"id","name"=>"name","kind"=>"kind");
  $options+=array("attrs"=>0,"position"=>false,"default"=>"");
  $id=acceptIntParameter($names["id"]);
  $name=acceptStringParameter($names["name"]);
  $kind=acceptStringParameter($names["kind"]);
  $type=$database->getColumnType($kind);
  $olditem=$id?$database->getLine($masterTable,"$names[id]=$id"):false;
  $oldname=$olditem?$olditem[$names["name"]]:"";
  $oldkind=$olditem?$olditem[$names["kind"]]:"";
  $oldtype=$database->getColumnType($oldkind);
  if(!preg_match("{^[A-Za-z_][A-Za-z_\d]*\$}",$name)) return false;
  if(!$add && $name==$oldname && $kind==$oldkind) return true;
  if($add || $name!=$oldname) {
    foreach($slaveTables as $table) {
      $fields=$database->getFieldsList($table);
      $fields=array_map("phpcstrtolower",$fields);
      if(in_array(phpcstrtolower($name),$fields)) return false;
    }
    $conditions="$names[name]=".slashes($name);
    if($database->isLinePresent($masterTable,$conditions)) return false;
  }
  $processed=array();
  foreach($slaveTables as $table) {
    if($add)
      $success=$database->addColumn($table,$name,$type,$options["attrs"],$options["position"],$options["default"]);
      else $success=$database->modifyColumn($table,$oldname,$name,$type,$options["attrs"],$options["default"]);
    if(!$success) break;
    $processed[]=$table;
  }
  if(count($processed)==count($slaveTables)) return true;
  foreach($processed as $table)
    if($add) $database->deleteColumn($table,$name);
      else $database->modifyColumn($table,$name,$oldname,$oldtype,$options["attrs"],$options["default"]);
  return false;
}

/******************************************************************************/

function getTableLocales($table, $localizedFields)
{
  global $database;
  $result=array();
  $names=array_keys($localizedFields);
  $locales=explode(",",PhpcLocalesList);
  $fields=$database->getFieldsList($table);
  $offset=$localizedFields[$names[0]]["offset"];
  while($offset<count($fields)) {
    if(!preg_match("{^$names[0](\w+)\$}",$fields[$offset],$matches)) break;
    if(!in_array($matches[1],$locales)) break;
    $result[]=$matches[1];
    $offset++;
  }
  $starting=0;
  $success=true;
  foreach($localizedFields as $field=>$info) {
    $offset=$info["offset"]+$starting*count($result);
    foreach($result as $locale) if($offset>=count($fields) ||
      $fields[$offset++]!=$field.$locale) $success=false;
    $starting++;
  }
  if(!$success) makeAdminError("admin_error_structure",$table);
  return $result;
}

function getTableMatchingLocale($table, $localizedFields)
{
  global $language;
  $locales=getTableLocales($table,$localizedFields);
  if(!count($locales)) makeAdminError("admin_error_nolocales");
  $locale=$language["locale"];
  return in_array($locale,$locales)?$locale:$locales[0];
}

function makeTableLocalesList($table, $localizedFields, $addlink, $removelink)
{
  global $language;
  $locales=getTableLocales($table,$localizedFields);
  $columns=array(
    array("title"=>"admin_modifylocales_locale","width"=>"50%"),
    "admin_modifylocales_options");
  makeTable("header",$columns);
  foreach($locales as $locale) {
    makeTableCellSimple($language["locales"][$locale]);
    $links=array("admin_modifylocales_remove"=>format($removelink,$locale));
    makeTableCellLinks($links);
  }
  makeTable("footer");
  makeBreak();
  makeLinks(array("admin_modifylocales_add"=>$addlink));
}

function makeTableLocalesAdd($table, $localizedFields, $script, $action, $echo=true)
{
  global $language, $database;
  $finish=substr($action,0,2)=="do";
  if(!$finish) {
    $current=getTableLocales($table,$localizedFields);
    $available=array_diff(explode(",",PhpcLocalesList),$current);
    $locales=array();
    $positions=array($language["admin_addlocale_first"]);
    $methods=array(""=>$language["admin_addlocale_empty"]);
    foreach($available as $locale)
      $locales[$locale]=$language["locales"][$locale];
    foreach($current as $locale) {
      $name=$language["locales"][$locale];
      $positions[]=format($language["admin_addlocale_after"],$name);
      $methods[$locale]=format($language["admin_addlocale_copy"],$name);
    }
    if(count($available)) {
      makeForm("header","admin_addlocale_form",$script,"do$action");
      makeFormChooser("admin_addlocale_locale","","locale",false,$locales);
      makeFormChooser("admin_addlocale_position","","position",count($positions)-1,$positions);
      makeFormChooser("admin_addlocale_method","","method","",$methods);
      makeForm("footer");
    }
    else makeError("admin_error_nofreelocales");
  }
  else {
    $locales=explode(",",PhpcLocalesList);
    $current=getTableLocales($table,$localizedFields);
    $locale=(int)array_search(acceptStringParameter("locale"),$locales);
    $position=acceptIntParameter("position",0,count($current));
    $method=array_search(acceptStringParameter("method"),$locales);
    $starting=0;
    foreach($localizedFields as $field=>$info) {
      $name=$field.$locales[$locale];
      $type=$database->getColumnType($info["type"]);
      $offset=$info["offset"]+$starting*(count($current)+1)+$position;
      $database->addColumn($table,$name,$type,0,$offset);
      if($method!==false) {
        $oldname=$field.$locales[$method];
        $database->customQueryBoolean("UPDATE $table SET $name=$oldname");
      }
      $starting++;
    }
    $database->optimizeTable($table);
    if($echo) makeNotification("admin_addlocale_success");
    if($echo) makeBreak();
  }
  return $finish;
}

function makeTableLocalesRemove($table, $localizedFields, $script, $action, $echo=true)
{
  global $database;
  $finish=substr($action,0,2)=="do";
  if(!$finish) {
    $locale=acceptStringParameter("locale");
    makePromptForm("header","admin_removelocale_prompt",$script,"do$action");
    makeFormHidden("locale",$locale);
    makePromptForm("footer");
  }
  else {
    $locale=acceptStringParameter("locale");
    foreach($localizedFields as $field=>$info)
      $database->deleteColumn($table,$field.$locale);
    if($echo) makeNotification("admin_removelocale_success");
    if($echo) makeBreak();
  }
  return $finish;
}

/******************************************************************************/

function getNestedSetObject()
{
  $props=getNestedSetProperties();
  $autoOrder=ifset($props["autoOrder"]);
  $enablePaths=ifset($props["enablePaths"]);
  $conditions=ifset($props["conditions"],"");
  $nestedSet=new NestedSet($props["table"],$autoOrder,$enablePaths,$conditions);
  return $nestedSet;
}

function getNestedSetTree()
{
  $nestedSet=getNestedSetObject();
  return $nestedSet->getTree();
}

function recalculateNestedSetTree()
{
  $nestedSet=getNestedSetObject();
  $nestedSet->recalculate();
}

function createNestedSetParentOptions($root=false, $denyid=0)
{
  global $language, $database;
  $props=getNestedSetProperties();
  $deny=$denyid?$database->getLine($props["table"],"id=$denyid"):false;
  $conditions=$deny?"itemleft<$deny[itemleft] OR itemright>$deny[itemright]":"";
  $nodes=$database->getOrderedLines($props["table"],"itemleft",$conditions);
  $result=$root?array($language[$props["noParentText"]]):array();
  foreach($nodes as $node) {
    $prefix=str_repeat("--",max($node["itemlevel"],1)-1);
    $result[$node["id"]]=trim("$prefix $node[title]");
  }
  return $result;
}

function createNestedSetOrderOptions($id)
{
  global $language, $database;
  $props=getNestedSetProperties();
  $current=$id?$database->getLine($props["table"],"id=$id"):false;
  if(!$current) return array($language[$props["orderLastText"]]);
  $result=array(1=>$language[$props["orderFirstText"]]);
  $nodes=$database->getOrderedLines($props["table"],"itemorder","parentid=$current[parentid]");
  foreach($nodes as $node) if($node["id"]!=$id)
    $result[]=format($language[$props["orderAfterText"]],$node["title"]);
  $result[count($result)]=$language[$props["orderLastText"]];
  return $result;
}

function checkNestedSetOrder($id, $oldNode)
{
  global $database;
  $props=getNestedSetProperties();
  $newNode=$id?$database->getLine($props["table"],"id=$id"):false;
  if(!$newNode) return;
  $simpleEdit=$oldNode && $oldNode["parentid"]==$newNode["parentid"];
  if($simpleEdit) {
    $oldOrder=$oldNode["itemorder"];
    $newOrder=$newNode["itemorder"];
    if($newOrder!=$oldOrder) {
      $minOrder=min($oldOrder,$newOrder);
      $maxOrder=max($oldOrder,$newOrder);
      $conditions="parentid=$newNode[parentid] AND itemorder>=$minOrder AND itemorder<=$maxOrder";
      $delta=$newOrder<$oldOrder?"+1":"-1";
      $database->customQueryBoolean("UPDATE $props[table] SET itemorder=itemorder$delta WHERE $conditions");
      $database->modifyField($props["table"],"itemorder",$newOrder,"id=$id");
    }
  }
  else {
    $order=$database->getMaxField($props["table"],"itemorder","parentid=$newNode[parentid]")+1;
    $database->modifyField($props["table"],"itemorder",$order,"id=$id");
  }
}

function checkNestedSetVisibility($id, $oldNode)
{
  global $database;
  $props=getNestedSetProperties();
  $newNode=$id?$database->getLine($props["table"],"id=$id"):false;
  if(!$newNode) return;
  $parentid=$newNode["parentid"];
  $parent=$parentid?$database->getLine($props["table"],"id=$parentid"):false;
  $parentHidden=($parent && !$parent["visible"]) || !$newNode["visible"];
  $makeVisible=$oldNode && !$oldNode["visible"] && $newNode["visible"];
  if($parentHidden) {
    $conditions="itemleft>=$newNode[itemleft] AND itemright<=$newNode[itemright]";
    $database->modifyLines($props["table"],array("visible"=>false),$conditions);
  }
  if($makeVisible) {
    $conditions="itemleft<$newNode[itemright] AND itemright>$newNode[itemleft]";
    $database->modifyLines($props["table"],array("visible"=>true),$conditions);
  }
}

?>
