<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

function getPhpcLocale()
{
  if(!defined("PhpcLocalesList")) exit;
  $locales=explode(",",PhpcLocalesList);
  if(in_array(PhpcLocale,$locales)) return PhpcLocale;
  $locale="";
  if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
    $accept=$_SERVER["HTTP_ACCEPT_LANGUAGE"];
    preg_match_all("{\w[\w\-]*}",$accept,$matches);
    $matches=array_diff($matches[0],array("q"));
    foreach($matches as $match)
      if(in_array($match,$locales)) { $locale=$match; break; }
  }
  if(defined("PhpcPreferredLocale")) $locale=PhpcPreferredLocale;
  foreach($locales as $name) {
    $host=substr($_SERVER["HTTP_HOST"],0,strlen($name)+1);
    $path=substr($_SERVER["REQUEST_URI"]."/",1,strlen($name)+1);
    if($host=="$name." || $path=="$name/" || $path=="$name?")
      { $locale=$name; break; }
  }
  if(isset($_COOKIE[PhpcLocaleCookie])) $locale=$_COOKIE[PhpcLocaleCookie];
  if(!in_array($locale,$locales)) $locale=$locales[0];
  return $locale;
}

function setPhpcLocale($locale)
{
  phpcsetcookie(PhpcLocaleCookie,$locale,true);
}

function fatalErrorPhpcLocale($locale)
{
  @header("Cache-Control: no-store, no-cache, must-revalidate");
  @header("Pragma: no-cache");
  @header("Content-Type: text/html");
  echo "<b>PHPC Fatal Error:</b> ";
  echo "Missing language folder &quot;language/$locale&quot;.<br>\r\n";
  exit;
}

function getPhpcLocaleFolder($locale)
{
  $path=realpath(dirname(__FILE__)."/../language");
  $path=str_replace("\\","/",$path);
  $result=array();
  $folder=@opendir("$path/$locale");
  while(($filename=@readdir($folder))!==false)
    if(substr($filename,-4)==".php") $result[]="$path/$locale/$filename";
  @closedir($folder);
  if(!count($result)) fatalErrorPhpcLocale($locale);
  sort($result);
  array_unshift($result,"$path/language.php");
  return $result;
}

function getPhpcLocaleArray($locale)
{
  static $cache=array();
  if(!isset($cache[$locale])) {
    $folder=getPhpcLocaleFolder($locale);
    foreach($folder as $filename) require $filename;
    $cache[$locale]=$language;
  }
  return $cache[$locale];
}

function getPhpcLocaleValue($locale, $key)
{
  $array=getPhpcLocaleArray($locale);
  return $array[$key];
}

function getLocalizedColumn($line, $field="")
{
  global $language;
  $locale=$language["locale"];
  if(isset($line[$field.$locale])) return $field.$locale;
  $locales=explode(",",PhpcLocalesList);
  foreach($locales as $locale)
    if(isset($line[$field.$locale])) return $field.$locale;
  return false;
}

function getLocalizedField($line, $field="")
{
  $column=getLocalizedColumn($line,$field);
  return $column!==false?$line[$column]:"";
}

function localizeField(&$line, $field)
{
  $line[$field]=getLocalizedField($line,$field);
}

function localizeFields(&$line, $fields)
{
  if(!is_array($fields)) $fields=explodeSmart(",",$fields);
  foreach($fields as $field) $line[$field]=getLocalizedField($line,$field);
}

$locale=getPhpcLocale();
$folder=getPhpcLocaleFolder($locale);
foreach($folder as $filename) { require $filename; }

?>
