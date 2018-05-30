<?php

// PHPC Control Panel Plugin (50.5) - Database Management v1.1 by Dagdamor

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.4.4, Copyright 2006
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

/*

// Main Menu Construction Code
makeMenuGroup("header","dbadmin_menu");
makeMenuItem("dbadmin_add","dbadmin.php?action=add");
makeMenuItem("dbadmin_modify","dbadmin.php?action=modify");
makeMenuItem("dbadmin_sql","dbadmin.php?action=sql");
makeMenuItem("dbadmin_export","dbadmin.php?action=export");
makeMenuItem("dbadmin_import","dbadmin.php?action=import");
makeMenuGroup("footer");

*/

require "global.php";
require_once "../plugins/zipfile.php";

define("RowsOnPage",100);
define("ValueShowLengthLimit",100);
define("ValueEditLengthLimit",100000);

adminLog("table,field,index");
$action=acceptStringParameter("action");

makeAdminPage("header");

/********************************* Functions **********************************/

function acceptTableName($name="table")
{
  global $database;
  $tables=$database->getTablesList();
  $result=acceptStringParameter($name);
  if(!in_array($result,$tables)) makeAdminError("dbadmin_error_notable");
  return $result;
}

function acceptTableOrder($name="order")
{
  $ascorder=acceptStringParameter("asc$name");
  $descorder=acceptStringParameter("desc$name");
  $reverse=$ascorder=="" && $descorder!="";
  $field=$reverse?$descorder:$ascorder;
  $sql=$field.($reverse?" DESC":"");
  $param=$reverse?"desc$name":"asc$name";
  $param=$field!=""?"&$param=$field":"";
  return compact("field","reverse","sql","param");
}

function getFieldTypes()
{
  global $database;
  static $cache;
  if(!isset($cache)) {
    $cache=array();
    $types=$database->getFieldTypes();
    foreach($types as $type) $cache[$type]=ucfirst(strtolower($type));
  }
  return $cache;
}

function getCompareOptions($null=true)
{
  $result=array(
    "equal"=>array("title"=>"=","sql"=>"%s=%s"),
    "notEqual"=>array("title"=>"!=","sql"=>"%s!=%s"),
    "less"=>array("title"=>"<","sql"=>"%s<%s"),
    "greater"=>array("title"=>">","sql"=>"%s>%s"),
    "lessEqual"=>array("title"=>"<=","sql"=>"%s<=%s"),
    "greaterEqual"=>array("title"=>">=","sql"=>"%s>=%s"),
    "like"=>array("title"=>"LIKE","sql"=>"%s LIKE %s"),
    "notLike"=>array("title"=>"NOT LIKE","sql"=>"%s NOT LIKE %s"),
    "null"=>array("title"=>"NULL","sql"=>"%s IS NULL"),
    "notNull"=>array("title"=>"NOT NULL","sql"=>"%s IS NOT NULL"));
  if(!$null) unset($result["null"],$result["notNull"]);
  return $result;
}

function getSelectedTables($name="tables")
{
  global $database;
  $tables=$database->getTablesList();
  $selected=acceptArrayParameter($name);
  $result=array_intersect($tables,$selected);
  return array_values($result);
}

function getSelectedFields($table, $name="fields")
{
  global $database;
  $fields=$database->getFieldsList($table);
  $selected=acceptArrayParameter($name);
  $result=array_intersect($fields,$selected);
  return array_values($result);
}

function getRowConditions($table)
{
  global $database;
  $primary=$database->getKeyFields($table);
  $result=array();
  foreach($primary as $key) {
    $value=acceptStringParameter("key$key");
    $result[$key]=slashes($value);
  }
  return implodeAssigns(" AND ",$result);
}

function getSelectedRowsConditions($table, $name="rows")
{
  $rows=acceptArrayParameter($name);
  $result=array();
  foreach($rows as $params) {
    $parts=explodeAssigns("&",$params);
    $array=$sql=array();
    foreach($parts as $key=>$value) {
      if(!preg_match("{^key[\w\-]+\$}",$key)) continue;
      $key=substr($key,3);
      $array[$key]=urldecode($value);
      $sql[]="$key=".slashes($array[$key]);
    }
    $sql=implode(" AND ",$sql);
    $result[]=compact("params","array","sql");
  }
  return $result;
}

function prepareSqlFieldPart($name, $type, $size, $attrs, $default)
{
  $type=strtoupper($type);
  if($size!="") $type.="($size)";
  $attr=array();
  if($attrs[1]) $attr[]="BINARY";
  if($attrs[2]) $attr[]="UNSIGNED";
  if($attrs[3]) $attr[]="ZEROFILL";
  $attr[]=$attrs[0]?"NULL":"NOT NULL";
  if($default!="") $attr[]="DEFAULT ".slashes($default);
  if($attrs[4]) $attr[]="AUTO_INCREMENT";
  return "$name $type ".implode(" ",$attr);
}

function prepareSqlCreateTable($name, $fieldparts, $keyfields)
{
  if(count($keyfields))
    $fieldparts[]="PRIMARY KEY (".implode(",",$keyfields).")";
  return "CREATE TABLE $name (\r\n  ".implode(",\r\n  ",$fieldparts).")";
}

function prepareOutputValue($value)
{
  if(is_null($value)) return null;
  $value=chopText($value,ValueShowLengthLimit);
  $value=strtr($value,"\0\t\r","   ");
  $value=preg_replace("{[\x01-\x08\x0b\x0c\x0e-\x1f]}","?",$value);
  $value=htmlspecialchars($value);
  $value=str_replace("\n",PredefinedNewline,$value);
  return $value;
}

function isValueEditable($value)
{
  if(strlen($value)>ValueEditLengthLimit) return false;
  return !preg_match("{[\\x00\x01-\x09\x0b\x0c\x0e-\x1f]}",$value);
}

function processQuery($query)
{
  global $database;
  makeNotification("dbadmin_message_query");
  makeBreak();
  makeQuote($query);
  makeBreak();
  $database->customQuerySilent($query,$error);
  if($error===false)
    makeNotification("dbadmin_message_querysuccess");
    else makeAdminError("dbadmin_error_query",$error);
}

function makeFormChooserInput($title, $description, $name1, $name2, $options=array(), $default=false, $value="", $lang=true, $filter=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<select class=\"cell\" name=\"$name1\">\r\n";
  foreach($options as $key=>$option) {
    if($default===false) $default=$key;
    $selected=$key==$default?" selected":"";
    $key=htmlspecialchars($key);
    if($filter) $option=formatAdminText($option);
    echo "<option value=\"$key\"$selected>$option</option>\r\n";
  }
  echo "</select>\r\n";
  echo "<input class=\"cell\" type=\"text\" name=\"$name2\" value=\"$value\">";
  makeFormItem("footer");
}

function makeTableCellNull()
{
  global $language;
  makeTableCell("header",array("align"=>"center"));
  echo "<b>$language[dbadmin_browse_null]</b>";
  makeTableCell("footer");
}

function makeTableCellAttrs($name, $values=0)
{
  global $database;
  makeTableCell("header",array("align"=>"center"));
  $supported=$database->getSupportedAttributes();
  $separator="";
  for($index=0; $index<ColumnAttributesCount; $index++) {
    $disabled=($supported&(1<<$index))?"":" disabled";
    $checked=($values&(1<<$index))?" checked":"";
    echo "$separator<input class=\"checkbox\" type=\"checkbox\" name=\"{$name}[$index]\" value=\"1\"$disabled$checked>";
    $separator="\r\n";
  }
  makeTableCell("footer");
}

function makeTableShort($what, $columns=array(), $script="", $action="", $lang=true)
{
  if($what=="header") {
    ob_start();
    makeTable($what,$columns,$script,$action,$lang);
    $content=ob_get_clean();
    $pattern="{^(<table id=\"\w+\") width=\"\d+%\"}";
    $content=preg_replace($pattern,"\\1",$content);
    echo $content;
  }
  else makeTable($what,$columns,$script,$action,$lang);
}

function makeTableActionShort($what, $columns=array(), $script="", $prompt="", $actions=array(), $lang=true)
{
  if($what=="header") {
    ob_start();
    makeTableAction($what,$columns,$script,$prompt,$actions,$lang);
    $content=ob_get_clean();
    $pattern="{^(<table id=\"\w+\") width=\"\d+%\"}";
    $content=preg_replace($pattern,"\\1",$content);
    echo $content;
  }
  else makeTableAction($what,$columns,$script,$prompt,$actions,$lang);
}

function makeQueryResult($result, $table=false)
{
  global $language, $database;
  if(!count($result)) return;
  $primary=$table!==false?$database->getKeyFields($table):array();
  $columns=array_keys($result[0]);
  if(count($primary)) $columns[]=$language["dbadmin_browse_options"];
  makeTableShort("header",$columns,"","",false);
  foreach($result as $row) {
    foreach($row as $value) {
      $decimal=isTrueInteger($value) || isTrueFloat($value);
      $value=prepareOutputValue($value);
      $style=$decimal?array("align"=>"right"):array("wrap"=>true);
      if(is_null($value))
        makeTableCellNull(); else makeTableCellExact($value,$style);
    }
    if(count($primary)) {
      $conditions=array();
      foreach($primary as $key) $conditions["key$key"]=urlencode($row[$key]);
      $conditions=implodeAssigns("&",$conditions);
      $links=array(
        "dbadmin_browse_edit"=>"dbadmin.php?action=editrow&table=$table&$conditions",
        "dbadmin_browse_remove"=>"dbadmin.php?action=removerow&table=$table&$conditions");
      makeTableCellLinks($links);
    }
  }
  makeTableShort("footer");
}

function makeTableNavigation($table)
{
  global $language, $database;
  $count=$database->getLinesCount($table);
  $title=format($language["dbadmin_navigation_headline"],array($table,$count));
  $links=array(
    "dbadmin_navigation_browse"=>"dbadmin.php?action=browse&table=$table",
    "dbadmin_navigation_fields"=>"dbadmin.php?action=fields&table=$table",
    "dbadmin_navigation_search"=>"dbadmin.php?action=search&table=$table",
    "dbadmin_navigation_edit"=>"dbadmin.php?action=edit&table=$table",
    "dbadmin_navigation_clear"=>"dbadmin.php?action=clear&table=$table",
    "dbadmin_navigation_remove"=>"dbadmin.php?action=remove&table=$table");
  makeHeadline($title,false);
  makeLinks($links);
  makeBreak();
}

/************************************ Add *************************************/

if($action=="add") {
  makeForm("header","dbadmin_add_form","dbadmin","create");
  makeFormInput("dbadmin_add_name","","table");
  makeFormInput("dbadmin_add_fields","","fields",1);
  makeForm("footer");
}

if($action=="create") {
  $table=acceptStringParameter("table");
  $fields=acceptIntParameter("fields",1,100);
  if(preg_match("{^[\w\-]+\$}",$table)) {
    $columns=array(
      "dbadmin_create_name",
      "dbadmin_create_type",
      "dbadmin_create_size",
      array("title"=>"dbadmin_create_attrs","width"=>"100%"),
      "dbadmin_create_default",
      "dbadmin_create_primary");
    makeTable("header",$columns,"dbadmin","docreate");
    makeFormHidden("table",$table);
    makeFormHidden("fields",$fields);
    for($index=0; $index<$fields; $index++) {
      makeTableCellInput("name[$index]");
      makeTableCellChooser("type[$index]",getFieldTypes());
      makeTableCellInput("size[$index]");
      makeTableCellAttrs("attrs[$index]");
      makeTableCellInput("default[$index]");
      makeTableCellCheckbox("key[$index]");
    }
    makeTable("footer");
    makeBreak();
    makeNotification("dbadmin_message_fieldnotes");
  }
  else makeError("dbadmin_error_wrongtablename");
}

if($action=="docreate") {
  $table=acceptStringParameter("table");
  $fields=acceptIntParameter("fields",1,100);
  $fieldparts=array();
  $keyfields=array();
  for($index=0; $index<$fields; $index++) {
    $name=acceptStringParameter(array("name",$index));
    $type=acceptStringParameter(array("type",$index));
    $size=acceptStringParameter(array("size",$index));
    $attrs=array();
    for($attr=0; $attr<ColumnAttributesCount; $attr++)
      $attrs[]=acceptIntParameter(array("attrs",$index,$attr),0,1);
    $default=acceptStringParameter(array("default",$index));
    $key=acceptIntParameter(array("key",$index),0,1);
    if($name=="") continue;
    if(!preg_match("{^[\w\-]+\$}",$name))
      makeAdminError("dbadmin_error_wrongfieldname");
    $fieldparts[]=prepareSqlFieldPart($name,$type,$size,$attrs,$default);
    if($key) $keyfields[]=$name;
  }
  if(count($fieldparts)) {
    $query=prepareSqlCreateTable($table,$fieldparts,$keyfields);
    processQuery($query);
    makeBreak();
    $action="modify";
  }
  else makeError("dbadmin_error_nofields");
}

/*********************** Optimize/Arrange/Repair Tables ***********************/

$actions="optimizegroup,arrangegroup,repairgroup";

if(in_array($action,explode(",",$actions))) {
  $tables=getSelectedTables();
  foreach($tables as $table) {
    switch($action) {
      case "optimizegroup": $database->optimizeTable($table); break;
      case "arrangegroup": $database->arrangeTable($table); break;
      case "repairgroup": $database->repairTable($table); break;
    }
    makeNotification("dbadmin_action_$action",$table);
  }
  if(count($tables)) {
    makeNotification("dbadmin_action_success");
    makeBreak();
    $action="modify";
  }
  else makeError("dbadmin_action_failure");
}

/***************************** Clear/Remove Table *****************************/

if($action=="clear" || $action=="remove") {
  $table=acceptTableName();
  $_REQUEST["tables"]=array(addSlashesSmart($table));
  $action="{$action}group";
}

/**************************** Clear/Remove Tables *****************************/

if($action=="cleargroup" || $action=="removegroup") {
  $tables=getSelectedTables();
  $count=count($tables);
  if($count) {
    $prompt="dbadmin_action_{$action}_prompt".($count==1?"once":"multi");
    $info=$count==1?$tables[0]:$count;
    $prompt=format($language[$prompt],htmlspecialchars($info));
    makePromptForm("header",$prompt,"dbadmin","do$action",false);
    foreach($tables as $table) makeFormHidden("tables[]",$table);
    makePromptForm("footer");
  }
  else makeError("dbadmin_action_failure");
}

if($action=="docleargroup" || $action=="doremovegroup") {
  $action=substr($action,2);
  $tables=getSelectedTables();
  foreach($tables as $table) {
    switch($action) {
      case "cleargroup": $database->clearTable($table); break;
      case "removegroup": $database->deleteTable($table); break;
    }
    makeNotification("dbadmin_action_$action",$table);
  }
  makeNotification("dbadmin_action_success");
  makeBreak();
  $action="modify";
}

/********************************* SQL/Import *********************************/

if($action=="sql") {
  makeForm("header","dbadmin_import_formtext","dbadmin","doimport");
  makeFormTextarea("dbadmin_import_text","","text");
  makeForm("footer");
}

if($action=="import") {
  makeForm("header","dbadmin_import_formfile","dbadmin","doimport");
  makeFormHidden("filemode",1);
  makeFormFile("dbadmin_import_file","dbadmin_import_filedesc","file");
  makeForm("footer");
}

if($action=="doimport") {
  @set_time_limit(0);
  $filemode=acceptIntParameter("filemode",0,1);
  if($filemode) {
    $file=$fileSystem->getUploadedFile("file");
    $content=$file?$file["content"]:"";
  }
  else $content=acceptStringParameter("text",false,false);
  $queries=$database->parseSQL($content);
  if(!count($queries))
    makeAdminError("dbadmin_import_empty".($filemode?"file":"text"));
  if(!$filemode && count($queries)==1) {
    $query=substr($content,$queries[0]["offset"],$queries[0]["length"]);
    $result=$database->customQuerySilent($query,$error);
    if($error===false) {
      if(is_array($result) && count($result)) {
        makeNotification("dbadmin_import_successonce");
        makeBreak();
        makeQueryResult($result);
      }
      else makeNotification("dbadmin_import_successempty");
    }
    else makeError("dbadmin_import_failureonce",$error);
    makeAdminPage("footer");
    exit;
  }
  foreach($queries as $query) {
    $instruction=substr($content,$query["offset"],$query["length"]);
    $success=$database->customQueryBoolean($instruction);
    if(!$success) {
      makeError("dbadmin_import_failuremulti");
      makeBreak();
      makeQuote($instruction);
      makeAdminPage("footer");
      exit;
    }
  }
  $optimizer->clearCache();
  $optimizer->clearFileCache();
  makeNotification("dbadmin_import_successmulti",count($queries));
  makeBreak();
  $action="modify";
}

/*********************************** Export ***********************************/

if($action=="export" || $action=="exportgroup") {
  $tables=$action=="export"?$database->getTablesList():getSelectedTables();
  if($action=="export" || count($tables)) {
    $tables=combineArrays($tables,$tables);
    makeForm("header","dbadmin_export_form","dbadmin","doexport");
    foreach($tables as $table) makeFormHidden("alltables[]",$table);
    makeFormSelector("dbadmin_export_tables","dbadmin_export_tablesdesc","tables[]",$tables);
    makeFormYesNo("dbadmin_export_structure","","structure");
    makeFormYesNo("dbadmin_export_data","","data");
    makeFormYesNo("dbadmin_export_send","","send");
    makeFormYesNo("dbadmin_export_pack","","pack",0);
    makeForm("footer");
  }
  else makeError("dbadmin_export_failure");
}

if($action=="doexport") {
  @set_time_limit(0);
  $tables=getSelectedTables();
  if(!count($tables)) $tables=getSelectedTables("alltables");
  $structure=acceptIntParameter("structure",0,1);
  $data=acceptIntParameter("data",0,1);
  $send=acceptIntParameter("send",0,1);
  $pack=acceptIntParameter("pack",0,1);
  $optimizer->clearCache();
  $filename=DatabaseName.".sql";
  $content="# PHP Compiler by Serge Igitov - Project Database Dump\r\n";
  foreach($tables as $table) {
    if($structure) $content.="\r\n".$database->exportTableStructure($table);
    if(!$data) continue;
    $lines=$database->getLines($table);
    if(count($lines)) $content.="\r\n";
    foreach($lines as $line) $content.=$database->exportTableLine($table,$line);
  }
  if(!$send) {
    makeNotification("dbadmin_export_success");
    makeBreak();
    makeQuote($content);
    makeAdminPage("footer");
    exit;
  }
  if($pack) {
    $zipfile=new ZipFile;
    $zipfile->addFile($filename,$content);
    $filename=DatabaseName.".zip";
    $content=$zipfile->file();
  }
  contentDisposition($filename,"application/octet-stream",$content);
}

/*********************************** Modify ***********************************/

if($action=="modify") {
  $tables=$database->getTablesInformation();
  $columns=array(
    array("title"=>"","width"=>"14"),
    array("title"=>"dbadmin_modify_name","width"=>"20%"),
    array("title"=>"dbadmin_modify_type","width"=>"1%"),
    array("title"=>"dbadmin_modify_rows","width"=>"1%"),
    array("title"=>"dbadmin_modify_size","width"=>"1%"),
    "dbadmin_modify_comment",
    array("title"=>"dbadmin_modify_options","width"=>"30%"),
    array("title"=>"table:checkbox:tables[]","width"=>"1%"));
  $actions=array(
    "optimizegroup"=>"dbadmin_modify_optimize",
    "arrangegroup"=>"dbadmin_modify_arrange",
    "repairgroup"=>"dbadmin_modify_repair",
    "exportgroup"=>"dbadmin_modify_export",
    "cleargroup"=>"dbadmin_modify_clear",
    "removegroup"=>"dbadmin_modify_remove");
  makeTableScripts();
  makeTableAction("header",$columns,"dbadmin","dbadmin_modify_action",$actions);
  $totalRows=$totalSize=0;
  foreach($tables as $table=>$info) {
    $icon=$info["rows"]?"normal":"empty";
    $info["size"]=round($info["size"]/1024);
    $links=array(
      "dbadmin_modify_browse"=>"dbadmin.php?action=browse&table=$table",
      "dbadmin_modify_fields"=>"dbadmin.php?action=fields&table=$table",
      "dbadmin_modify_search"=>"dbadmin.php?action=search&table=$table",
      "dbadmin_modify_addrow"=>"dbadmin.php?action=addrow&table=$table");
    makeTableCellImageSize(14,16,"dbadmin.php?action=image&image=icon$icon");
    makeTableCellSimple("[b]{$table}[/b]");
    makeTableCellSimple($info["type"]);
    makeTableCellSimple(formatInteger($info["rows"]),array("align"=>"right"));
    makeTableCellSimple(format($language["dbadmin_modify_kb"],$info["size"]),array("align"=>"right"));
    makeTableCellSimple($info["comment"],array("wrap"=>true));
    makeTableCellLinks($links);
    makeTableCellCheckboxArray("tables[]",$table);
    $totalRows+=$info["rows"];
    $totalSize+=$info["size"];
  }
  $columns=array(
    array("value"=>count($tables),"colspan"=>3),
    formatInteger($totalRows),
    format($language["dbadmin_modify_kb"],$totalSize));
  if(count($tables)>1) makeTableTotals($columns);
  makeTableAction("footer");
}

/******************************** Add/Edit Row ********************************/

if($action=="addrow" || $action=="editrow") {
  $add=$action=="addrow";
  $table=acceptTableName();
  $formtitle=$add?"dbadmin_addrow_form":"dbadmin_editrow_form";
  $formtitle=format($language[$formtitle],$table);
  $columns=$database->getTableInformation($table);
  $columns=$columns["columns"];
  $primary=$database->getKeyFields($table);
  if($add) {
    $row=array();
    foreach($columns as $column) $row[$column["name"]]=null;
  }
  else $row=$database->getLine($table,getRowConditions($table));
  $editable=array();
  foreach($columns as $column)
    /*if(isValueEditable($row[$column["name"]]))*/ $editable[]=$column["name"];
  makeForm("header",$formtitle,"dbadmin","do$action",false);
  makeFormHidden("table",$table);
  if(!$add) foreach($primary as $key)
    makeFormHidden("key$key",acceptStringParameter("key$key"));
  foreach($editable as $field) {
    makeFormHidden("fields[$field]",true);
    $column=searchArrayLine($columns,"name",$field);
    $type=$column["type"];
    if($column["size"]) $type.="($column[size])";
    $meaning=$database->getColumnMeaning($type);
    $title=format($language["dbadmin_addeditrow_field"],$field);
    switch($meaning) {
      case "yesno": makeFormYesNo($title,"","values[$field]",$row[$field],false); break;
      case "textarea": makeFormTextarea($title,"","values[$field]",$row[$field],false); break;
      default: makeFormInput($title,"","values[$field]",$row[$field],false);
    }
    if($column["attrs"]&ColumnAttributeNull) {
      $title=format($language["dbadmin_addeditrow_null"],$field);
      makeFormYesNo($title,"","nulls[$field]",is_null($row[$field]),false);
    }
  }
  makeForm("footer");
  $d=$GLOBALS;
  unset($d["GLOBALS"]);
  /*echo "<pre style=\"border:1px solid red;\">";
  echo var_dump($d);
  echo "</pre>";*/
}

if($action=="doaddrow" || $action=="doeditrow") {
  $add=$action=="doaddrow";
  $table=acceptTableName();
  $columns=$database->getTableInformation($table);
  $columns=$columns["columns"];
  $values=array();
  foreach($columns as $column) {
    $field=$column["name"];
    if(!acceptIntParameter(array("fields",$field),0,1)) continue;
    $value=acceptStringParameter(array("values",$field),false,false);
    if($column["attrs"]&ColumnAttributeNull)
      if(acceptIntParameter(array("nulls",$field),0,1)) $value=null;
    $values[$field]=$value;
  }
  if($add)
    $success=$database->addLine($table,$values);
    else $success=$database->modifyLine($table,$values,getRowConditions($table));
  if($success) {
    makeNotification($add?"dbadmin_addrow_success":"dbadmin_editrow_success");
    makeBreak();
    $action="browse";
  }
  else makeError($add?"dbadmin_addrow_failure":"dbadmin_editrow_failure");
}

/********************************* Remove Row *********************************/

if($action=="removerow") {
  $table=acceptTableName();
  $primary=$database->getKeyFields($table);
  makePromptForm("header","dbadmin_removerow_prompt","dbadmin","doremoverow");
  makeFormHidden("table",$table);
  foreach($primary as $key)
    makeFormHidden("key$key",acceptStringParameter("key$key"));
  makePromptForm("footer");
}

if($action=="doremoverow") {
  $table=acceptTableName();
  $success=$database->deleteLine($table,getRowConditions($table));
  if($success) {
    makeNotification("dbadmin_removerow_success");
    makeBreak();
    $action="browse";
  }
  else makeError("dbadmin_removerow_failure");
}

/******************************** Remove Rows *********************************/

if($action=="removerows") {
  $table=acceptTableName();
  $conditions=getSelectedRowsConditions($table);
  if(count($conditions)) {
    $title=format($language["dbadmin_removerows_prompt"],count($conditions));
    makePromptForm("header",$title,"dbadmin","doremoverows",false);
    makeFormHidden("table",$table);
    foreach($conditions as $condition)
      makeFormHidden("rows[]",$condition["params"]);
    makePromptForm("footer");
  }
  else makeError("dbadmin_removerows_failure");
}

if($action=="doremoverows") {
  $table=acceptTableName();
  $conditions=getSelectedRowsConditions($table);
  foreach($conditions as $condition)
    $database->deleteLine($table,$condition["sql"]);
  makeNotification("dbadmin_removerows_success");
  makeBreak();
  $action="browse";
}

/******************************** Export Rows *********************************/

if($action=="exportrows") {
  $table=acceptTableName();
  $conditions=getSelectedRowsConditions($table);
  if(count($conditions)) {
    makeTableNavigation($table);
    $query="";
    foreach($conditions as $condition) {
      $values=$database->getLine($table,$condition["sql"]);
      $query.=$database->exportTableLine($table,$values);
    }
    makeNotification("dbadmin_exportrows_success");
    makeBreak();
    makeQuote($query);
  }
  else makeError("dbadmin_exportrows_failure");
}

/******************************** Browse Table ********************************/

if($action=="browse") {
  $table=acceptTableName();
  $order=acceptTableOrder();
  makeTableNavigation($table);
  $addlink=array("dbadmin_browse_add"=>"dbadmin.php?action=addrow&table=$table");
  makeLinks($addlink);
  makeBreak();
  $fields=$database->getFieldsList($table);
  $columns=$database->getTableInformation($table);
  $meanings=array();
  foreach($columns["columns"] as $column) {
    $type=$column["type"];
    if($column["size"]) $type.="($column[size])";
    $meanings[]=$database->getColumnMeaning($type);
  }
  $primary=$database->getKeyFields($table);
  $data=getTablePagePortion($table,$order["sql"],"",RowsOnPage,$page,$total);
  $columns=array();
  foreach($fields as $title) {
    $selected=$order["field"]==$title;
    $param=($selected && !$order["reverse"])?"descorder":"ascorder";
    $link="dbadmin.php?action=browse&table=$table&$param=$title";
    $columns[]=compact("title","link","selected");
  }
  if(count($primary)) {
    $columns[]=$language["dbadmin_browse_options"];
    $columns[]="table:checkbox:rows[]";
    $actions=array(
      "removerows"=>$language["dbadmin_browse_removerows"],
      "exportrows"=>$language["dbadmin_browse_exportrows"]);
    makeTableScripts();
    makeTableActionShort("header",$columns,"dbadmin",$language["dbadmin_browse_action"],$actions,false);
    makeFormHidden("table",$table);
  }
  else makeTableShort("header",$columns,"","",false);
  foreach($data as $row) {
    foreach($fields as $index=>$field) {
      $meaning=is_null($row[$field])?"null":$meanings[$index];
      $decimal=$meaning=="int" || $meaning=="float";
      $style=$decimal?array("align"=>"right"):array("wrap"=>true);
      switch($meaning) {
        case "null": makeTableCellNull(); break;
        case "yesno": makeTableCellYesNo($row[$field]); break;
        default: makeTableCellExact(prepareOutputValue($row[$field]),$style);
      }
    }
    if(count($primary)) {
      $conditions=array();
      foreach($primary as $key) $conditions["key$key"]=urlencode($row[$key]);
      $conditions=implodeAssigns("&",$conditions);
      $links=array(
        "dbadmin_browse_edit"=>"dbadmin.php?action=editrow&table=$table&$conditions",
        "dbadmin_browse_remove"=>"dbadmin.php?action=removerow&table=$table&$conditions");
      makeTableCellLinks($links);
      makeTableCellCheckboxArray("rows[]",$conditions);
    }
  }
  $link="dbadmin.php?action=browse&table=$table$order[param]&page=%s";
  makeTablePager($page,$total,$link);
  if(count($primary))
    makeTableActionShort("footer"); else makeTableShort("footer");
  makeBreak();
  makeLinks($addlink);
}

/********************************* Add Fields *********************************/

if($action=="addfields") {
  $table=acceptTableName();
  $count=acceptIntParameter("count",1,100);
  $totalColumns=count($database->getFieldsList($table));
  $place=acceptIntParameter("place",0,$totalColumns);
  $columns=array(
    "dbadmin_create_name",
    "dbadmin_create_type",
    "dbadmin_create_size",
    array("title"=>"dbadmin_create_attrs","width"=>"100%"),
    "dbadmin_create_default");
  makeTable("header",$columns,"dbadmin","doaddfields");
  makeFormHidden("table",$table);
  makeFormHidden("count",$count);
  makeFormHidden("place",$place);
  for($index=0; $index<$count; $index++) {
    makeTableCellInput("name[$index]");
    makeTableCellChooser("type[$index]",getFieldTypes());
    makeTableCellInput("size[$index]");
    makeTableCellAttrs("attrs[$index]");
    makeTableCellInput("default[$index]");
  }
  makeTable("footer");
  makeBreak();
  makeNotification("dbadmin_message_fieldnotes");
}

if($action=="doaddfields") {
  $table=acceptTableName();
  $count=acceptIntParameter("count",1,100);
  $totalColumns=count($database->getFieldsList($table));
  $place=acceptIntParameter("place",0,$totalColumns);
  $fields=array();
  for($index=0; $index<$count; $index++) {
    $name=acceptStringParameter(array("name",$index));
    $type=acceptStringParameter(array("type",$index));
    $size=acceptStringParameter(array("size",$index));
    $attrs=0;
    for($attr=0; $attr<ColumnAttributesCount; $attr++)
      $attrs+=acceptIntParameter(array("attrs",$index,$attr),0,1)<<$attr;
    $default=acceptStringParameter(array("default",$index));
    if($name=="") continue;
    if(!preg_match("{^[\w\-]+\$}",$name))
      makeAdminError("dbadmin_error_wrongfieldname");
    $fields[]=compact("name","type","size","attrs","default");
  }
  if(!count($fields)) makeAdminError("dbadmin_error_nofields");
  foreach($fields as $field) {
    $type=$field["type"].($field["size"]?"($field[size])":"");
    $success=$database->addColumn
      ($table,$field["name"],$type,$field["attrs"],$place++,$field["default"]);
    if(!$success) makeAdminError("dbadmin_addfields_failure",$field["name"]);
    makeNotification("dbadmin_addfields_success",$field["name"]);
  }
  makeBreak();
  $action="fields";
}

/***************************** Edit Field/Fields ******************************/

if($action=="editfield" || $action=="editfields") {
  $table=acceptTableName();
  $field=acceptStringParameter("field");
  $fields=$action=="editfield"?array($field):getSelectedFields($table);
  if(count($fields)) {
    $information=$database->getTableInformation($table);
    $information=$information["columns"];
    $columns=array(
      "dbadmin_create_name",
      "dbadmin_create_type",
      "dbadmin_create_size",
      array("title"=>"dbadmin_create_attrs","width"=>"100%"),
      "dbadmin_create_default");
    makeTable("header",$columns,"dbadmin","doeditfields");
    makeFormHidden("table",$table);
    foreach($fields as $field) {
      $info=searchArrayLine($information,"name",$field);
      makeFormHidden("fields[$field]",1);
      makeTableCellInput("name[$field]",$info["name"]);
      makeTableCellChooser("type[$field]",getFieldTypes(),$info["type"]);
      makeTableCellInput("size[$field]",$info["size"]);
      makeTableCellAttrs("attrs[$field]",$info["attrs"]);
      makeTableCellInput("default[$field]",$info["default"]);
    }
    makeTable("footer");
    makeBreak();
    makeNotification("dbadmin_message_fieldnotes");
  }
  else makeError("dbadmin_error_nofields");
}

if($action=="doeditfields") {
  $table=acceptTableName();
  $fields=$database->getFieldsList($table);
  foreach($fields as $field) {
    if(!acceptIntParameter(array("fields",$field),0,1)) continue;
    $name=acceptStringParameter(array("name",$field));
    $type=acceptStringParameter(array("type",$field));
    $size=acceptStringParameter(array("size",$field));
    $attrs=0;
    for($attr=0; $attr<ColumnAttributesCount; $attr++)
      $attrs+=acceptIntParameter(array("attrs",$field,$attr),0,1)<<$attr;
    $default=acceptStringParameter(array("default",$field));
    if(!preg_match("{^[\w\-]+\$}",$name))
      makeAdminError("dbadmin_error_wrongfieldname");
    if($size) $type.="($size)";
    $success=$database->modifyColumn($table,$field,$name,$type,$attrs,$default);
    if(!$success) makeAdminError("dbadmin_editfields_failure",$field);
    makeNotification("dbadmin_editfields_success",$field);
  }
  makeBreak();
  $action="fields";
}

/**************************** Remove Field/Fields *****************************/

if($action=="removefield" || $action=="removefields") {
  $table=acceptTableName();
  $field=acceptStringParameter("field");
  $fields=$action=="removefield"?array($field):getSelectedFields($table);
  $count=count($fields);
  if($count) {
    $prompt="dbadmin_removefields_prompt".($count==1?"once":"multi");
    $info=$count==1?$fields[0]:$count;
    $prompt=format($language[$prompt],htmlspecialchars($info));
    makePromptForm("header",$prompt,"dbadmin","doremovefields",false);
    makeFormHidden("table",$table);
    foreach($fields as $field) makeFormHidden("fields[]",$field);
    makePromptForm("footer");
  }
  else makeError("dbadmin_error_nofields");
}

if($action=="doremovefields") {
  $table=acceptTableName();
  $fields=getSelectedFields($table);
  foreach($fields as $field) {
    $success=$database->deleteColumn($table,$field);
    if(!$success) makeAdminError("dbadmin_removefields_failure",$field);
    makeNotification("dbadmin_removefields_success",$field);
  }
  makeBreak();
  $action="fields";
}

/********************************* Add Index **********************************/

if($action=="addindex") {
  $table=acceptTableName();
  $count=acceptIntParameter("count",1,100);
  $information=$database->getTableInformation($table);
  $name=isset($information["uniques"]["PRIMARY"])?"":"PRIMARY";
  $fields=$database->getFieldsList($table);
  $fields=combineArrays($fields,$fields);
  makeForm("header","dbadmin_addindex_form2","dbadmin","doaddindex");
  makeFormHidden("table",$table);
  makeFormHidden("count",$count);
  makeFormInput("dbadmin_addindex_name","dbadmin_addindex_namedesc","name",$name);
  makeFormYesNo("dbadmin_addindex_unique","","unique",$name=="PRIMARY");
  for($index=0; $index<$count; $index++) makeFormChooserInput
    ("dbadmin_addindex_fieldsize","","fields[$index]","sizes[$index]",$fields);
  makeForm("footer");
}

if($action=="doaddindex") {
  $table=acceptTableName();
  $count=acceptIntParameter("count",1,100);
  $indexname=acceptStringParameter("name");
  $unique=acceptIntParameter("unique",0,1);
  if(preg_match("{^[\w\-]*\$}",$indexname)) {
    $fields=array();
    for($index=0; $index<$count; $index++) {
      $name=acceptStringParameter(array("fields",$index));
      $size=acceptIntParameter(array("sizes",$index),0);
      $fields[]=compact("name","size");
    }
    $success=$database->addIndex($table,$indexname,$unique,$fields);
    if($success) {
      makeNotification("dbadmin_addindex_success");
      makeBreak();
      $action="fields";
    }
    else makeError("dbadmin_addindex_failure");
  }
  else makeError("dbadmin_error_wrongindexname");
}

/******************************** Remove Index ********************************/

if($action=="removeindex") {
  $table=acceptTableName();
  $index=acceptStringParameter("index");
  makePrompt("dbadmin_removeindex_prompt","dbadmin.php?action=doremoveindex&table=$table&index=$index");
}

if($action=="doremoveindex") {
  $table=acceptTableName();
  $index=acceptStringParameter("index");
  $success=$database->deleteIndex($table,$index);
  if($success) {
    makeNotification("dbadmin_removeindex_success");
    makeBreak();
    $action="fields";
  }
  else makeError("dbadmin_removeindex_failure");
}

/****************************** Table Structure *******************************/

if($action=="fields") {
  $table=acceptTableName();
  makeTableNavigation($table);
  $information=$database->getTableInformation($table);
  $primary=$database->getKeyFields($table);
  $columns=array(
    "dbadmin_fields_name",
    "dbadmin_fields_type",
    "dbadmin_fields_size",
    "dbadmin_fields_attrs",
    "dbadmin_fields_default",
    array("title"=>"dbadmin_fields_options","width"=>"20%"),
    array("title"=>"table:checkbox:fields[]","width"=>"1%"));
  $actions=array(
    "editfields"=>"dbadmin_fields_edit",
    "removefields"=>"dbadmin_fields_remove");
  makeTableScripts();
  makeTableAction("header",$columns,"dbadmin","dbadmin_fields_actions",$actions);
  makeFormHidden("table",$table);
  foreach($information["columns"] as $column) {
    $title="[b]$column[name][/b]";
    if(in_array($column["name"],$primary)) $title="[u]{$title}[/u]";
    $attrs=array();
    for($index=0; $index<ColumnAttributesCount; $index++)
      if($column["attrs"]&(1<<$index))
        $attrs[]=$language["dbadmin_fields_attr$index"];
    $links=array(
      "dbadmin_fields_edit"=>"dbadmin.php?action=editfield&table=$table&field=$column[name]",
      "dbadmin_fields_remove"=>"dbadmin.php?action=removefield&table=$table&field=$column[name]");
    makeTableCellSimple($title);
    makeTableCellSimple(ucfirst(strtolower($column["type"])));
    makeTableCellSimple($column["size"]);
    makeTableCellSimple(implode(", ",$attrs));
    makeTableCellSimple($column["default"]);
    makeTableCellLinks($links);
    makeTableCellCheckboxArray("fields[]",$column["name"]);
  }
  makeTableAction("footer");
  makeBreak();
  $totalColumns=count($information["columns"]);
  $options=array($language["dbadmin_addfields_first"]);
  foreach($information["columns"] as $column) $options[]=
    format($language["dbadmin_addfields_after"],$column["name"]);
  $options[$totalColumns]=$language["dbadmin_addfields_last"];
  makeForm("header","dbadmin_addfields_form","dbadmin","addfields");
  makeFormHidden("table",$table);
  makeFormInput("dbadmin_addfields_count","","count",1);
  makeFormChooser("dbadmin_addfields_place","","place",$totalColumns,$options);
  makeForm("footer");
  makeBreak(2);
  $subjects=array("uniques","indexes");
  $columns=array(
    array("title"=>"dbadmin_indexes_name","width"=>"20%"),
    array("title"=>"dbadmin_indexes_unique","width"=>"15%"),
    "dbadmin_indexes_fields",
    array("title"=>"dbadmin_indexes_options","width"=>"15%"));
  makeTable("header",$columns);
  if(!isset($information["uniques"]["PRIMARY"]))
    makeTableCellSimple($language["dbadmin_indexes_noprimary"],
      array("colspan"=>4,"align"=>"center"));
  foreach($subjects as $subject)
    foreach($information[$subject] as $name=>$index) {
      $fields=array();
      foreach($index as $part)
        $fields[]=$part["name"].($part["size"]?"($part[size])":"");
      $links=array("dbadmin_indexes_remove"=>"dbadmin.php?action=removeindex&table=$table&index=$name");
      makeTableCellSimple("[b]{$name}[/b]");
      makeTableCellYesNo($subject=="uniques");
      makeTableCellSimple(implode(", ",$fields));
      makeTableCellLinks($links);
    }
  makeTable("footer");
  makeBreak();
  makeForm("header","dbadmin_addindex_form1","dbadmin","addindex");
  makeFormHidden("table",$table);
  makeFormInput("dbadmin_addindex_count","","count",1);
  makeForm("footer");
}

/******************************** Table Search ********************************/

if($action=="search") {
  $table=acceptTableName();
  makeTableNavigation($table);
  $information=$database->getTableInformation($table);
  makeForm("header","dbadmin_search_form","dbadmin","dosearch");
  makeFormHidden("table",$table);
  foreach($information["columns"] as $index=>$column) {
    $title=format($language["dbadmin_search_field"],$column["name"]);
    $options=getCompareOptions($column["attrs"]&ColumnAttributeNull);
    $options=extractArrayColumn($options,"title");
    $meaning=$database->getColumnMeaning($column["type"]);
    $default=($meaning=="input" || $meaning=="textarea")?"like":false;
    makeFormChooserInput($title,"","condition[$index]","value[$index]",$options,$default,"",false);
  }
  makeForm("separator","dbadmin_search_separator");
  $options=array(""=>$language["dbadmin_search_noorder"]);
  foreach($information["columns"] as $column) {
    $name=$column["name"];
    $options[$name]=format($language["dbadmin_search_orderasc"],$name);
    $options["$name DESC"]=format($language["dbadmin_search_orderdesc"],$name);
  }
  makeFormChooser("dbadmin_search_order","","order",false,$options);
  makeFormInput("dbadmin_search_offset","","offset",0);
  makeFormInput("dbadmin_search_count","","count",100);
  makeForm("footer");
}

if($action=="dosearch") {
  $table=acceptTableName();
  $order=acceptStringParameter("order");
  $offset=acceptIntParameter("offset",0);
  $count=acceptIntParameter("count",0);
  $columns=$database->getFieldsList($table);
  $options=getCompareOptions();
  $conditions=array();
  foreach($columns as $index=>$column) {
    $condition=acceptStringParameter(array("condition",$index));
    $value=acceptStringParameter(array("value",$index));
    if($value=="") continue;
    $sql=$options[$condition]["sql"];
    $conditions[]=format($sql,array($column,slashes($value)));
  }
  if(count($conditions)) {
    makeTableNavigation($table);
    $conditions=implode(" AND ",$conditions);
    if($order!="")
      $result=$database->getOrderedLinesRange($table,$order,$offset,$count,$conditions);
      else $result=$database->getLinesRange($table,$offset,$count,$conditions);
    if(count($result)) {
      makeNotification("dbadmin_search_success1");
      makeBreak();
      makeQueryResult($result,$table);
    }
    else makeNotification("dbadmin_search_success2");
  }
  else makeError("dbadmin_search_failure");
}

/****************************** Table Properties ******************************/

if($action=="doedit") {
  $table=acceptTableName();
  $name=acceptStringParameter("name");
  $type=acceptStringParameter("type");
  $comment=acceptStringParameter("comment");
  $information=$database->getTablesInformation();
  $information=$information[$table];
  $changes=false;
  if($name!=$table) {
    if(!preg_match("{^[\w\-]+\$}",$name))
      makeAdminError("dbadmin_error_wrongtablename");
    $success=$database->renameTable($table,$name);
    if(!$success) makeAdminError("dbadmin_edit_changename_failure");
    $table=$name;
    sendParameter("table",$table);
    makeNotification("dbadmin_edit_changename_success");
    $changes=true;
  }
  if($type!=$information["type"]) {
    $success=$database->changeTableType($table,$type);
    if(!$success) makeAdminError("dbadmin_edit_changetype_failure");
    makeNotification("dbadmin_edit_changetype_success");
    $changes=true;
  }
  if($comment!=$information["comment"]) {
    $success=$database->changeTableComment($table,$comment);
    if(!$success) makeAdminError("dbadmin_edit_changecomment_failure");
    makeNotification("dbadmin_edit_changecomment_success");
    $changes=true;
  }
  if($changes) makeBreak();
  $action="edit";
}

if($action=="edit") {
  $table=acceptTableName();
  makeTableNavigation($table);
  $information=$database->getTablesInformation();
  $information=$information[$table];
  $columns=array(
    array("title"=>"dbadmin_statistics_param","width"=>"50%"),
    "dbadmin_statistics_value");
  $statistics=array(
    "name"=>$table,
    "type"=>$information["type"],
    "comment"=>$information["comment"],
    "rows"=>formatInteger($information["rows"]),
    "datasize"=>format($language["dbadmin_statistics_bytes"],$information["datasize"]),
    "indexsize"=>format($language["dbadmin_statistics_bytes"],$information["indexsize"]),
    "size"=>format($language["dbadmin_statistics_bytes"],$information["size"]),
    "counter"=>formatInteger($information["counter"]));
  makeTable("header",$columns);
  foreach($statistics as $param=>$value) {
    makeTableCellSimple($language["dbadmin_statistics_$param"]);
    makeTableCellSimple($value);
  }
  makeTable("footer");
  makeBreak();
  $types=$database->getTableTypes();
  $types=combineArrays($types,$types);
  makeForm("header","dbadmin_edit_form","dbadmin","doedit");
  makeFormHidden("table",$table);
  makeFormInput("dbadmin_edit_name","","name",$table);
  makeFormChooser("dbadmin_edit_type","","type",$information["type"],$types);
  makeFormInput("dbadmin_edit_comment","","comment",$information["comment"]);
  makeForm("footer");
}

/*********************************** Image ************************************/

if($action=="image") {
  $image=acceptStringParameter("image",100);
  switch($image) {
    case "iconempty": $content="R0lGODlhDgAQAMQeANra7e3t9+fn9PX1+vf3+9jY7OHh8d/f8La23Ozs9szM5re33enp9c7O6NnZ7eTk8r+/4Pb2++jo9ODg8MbG4/Hx+Lq63tTU6vLy+fPz+u3t9sTE47Cw2QAAAAAAAAAAACH5BAEAAB4ALAAAAAAOABAAAAVWoCeOZCl2aKp2YxcRxIAFwgSw56AlwnFRCA7O08kwVhxhq/IwABqQpPIUuKmSw45EsVlIsS2DpfMFnwBSVPrkwAqFZFyn8CWv7en6ie7GDokrKCaDJSEAOw=="; break;
    case "iconnormal": $content="R0lGODlhDgAQAMQfAP/96P/7z//7zP/94f/+8v/4rP/94P/82P/6vf/5tf/83P/81v/81//4oP/95//+7v/6wP/82f/+7f/7x//+8P/8z//94//4o//7zv/5s//+9f/4pv/+8//3mQAAAAAAACH5BAEAAB8ALAAAAAAOABAAAAVW4CeOZCl6aKp6o8dpGvEABhOwJwE4wzIljQ7u46FYVh1hS6KIBCCFpPIEuKmSQ88AkblIsa3DxvMFnypSVPqEwQqFZJxH8CWv7en6ie7GDokrKCaDJSEAOw=="; break;
    default: $content="R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
  }
  makeImage("image/gif",base64_decode($content));
}

/******************************************************************************/

makeAdminPage("footer");

?>
