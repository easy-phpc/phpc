<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

function phpcversion()
{
  return "2.5.2";
}

function random($value=false)
{
  if($value===false) return mt_rand();
  return mt_rand(0,$value-1);
}

function ifset(&$value, $default=false)
{
  return isset($value)?$value:$default;
}

function swap(&$value1, &$value2)
{
  $value3=$value1;
  $value1=$value2;
  $value2=$value3;
}

function char($text, $index)
{
  if($index<0) return "\x00";
  if($index>=strlen($text)) return "\x00";
  return $text[$index];
}

function slashes($text, $symbol="'")
{
  if(defined("CompilerSimpleSlashes"))
    return $symbol.str_replace($symbol,$symbol.$symbol,$text).$symbol;
  return CompilerEfficientSlashes && $symbol=="'"?
    $symbol.str_replace("\\\"","\"",addslashes($text)).$symbol:
    $symbol.addslashes($text).$symbol;
}

function pushVariable(&$variable, $reverse=false)
{
  static $stack=array();
  if($reverse) $variable=array_pop($stack); else $stack[]=$variable;
}

function popVariable(&$variable)
{
  pushVariable($variable,true);
}

function getIncrementalValue($scope="")
{
  static $values=array();
  if(!isset($values[$scope])) $values[$scope]=0;
  return ++$values[$scope];
}

function isTrueInteger($value)
{
  if(!is_scalar($value) || is_bool($value)) return false;
  return (string)(int)$value===(string)$value;
}

function isTrueFloat($value)
{
  if(!is_scalar($value) || is_bool($value)) return false;
  return (string)(float)$value===(string)$value;
}

if(!function_exists("hex2bin")) {
  function hex2bin($text)
  {
    $text=strtr($text,"ABCDEF","abcdef");
    $result=@pack("H*",$text);
    return bin2hex($result)==$text?$result:"";
  }
}

if(!function_exists("file_get_contents_timeout")) {
  function file_get_contents_timeout($filename, $timeout=FileSystemTimeout)
  {
    if(strpos($filename,"://")===false) return file_get_contents($filename);
    if(!function_exists("curl_init")) return false;
    $session=curl_init($filename);
    curl_setopt($session,CURLOPT_FAILONERROR,true);
    curl_setopt($session,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($session,CURLOPT_CONNECTTIMEOUT,$timeout);
    curl_setopt($session,CURLOPT_TIMEOUT,$timeout);
    curl_setopt($session,CURLOPT_USERAGENT,FileSystemUserAgent);
    $result=curl_exec($session);
    curl_close($session);
    return $result;
  }
}

if(!function_exists("file_put_contents")) {
  function file_put_contents($filename, $content)
  {
    if(!$file=fopen($filename,"wb")) return false;
    $result=fwrite($file,$content);
    fclose($file);
    return $result;
  }
}

if(!function_exists("iconv")) {
  function iconv() { fatalError("fatal_function","iconv"); }
}

function phpcStackEnd($scope) { return $scope[count($scope)-1][0]; }
function phpcStackPush($scope, $args, $num) { $scope[]=array($args,$num); return $scope; }
function phpcStackPop($scope, &$name, $num) { $name=array_pop($scope); $name="_phpc{$num}_exec$name[1]"; return $scope; }
function phpcStackMerge(&$scope, $args) { $scope[count($scope)-1][0]=array_merge($scope[count($scope)-1][0],$args); }

/******************************************************************************/

function phpcchar($text, $index)
{
  if(class_exists("UnicodeSupport")) return UnicodeSupport::char($text,$index);
  return char($text,$index);
}

function phpcstrlen($text)
{
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strlen($text);
  return strlen($text);
}

function phpcsubstr($text, $offset, $length=false)
{
  if(class_exists("UnicodeSupport")) return UnicodeSupport::substr($text,$offset,$length);
  if($length===false) return (string)substr($text,$offset);
  return (string)substr($text,$offset,$length);
}

function phpcstrncmp($text1, $text2, $length)
{
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strncmp($text1,$text2,$length);
  return strncmp($text1,$text2,$length);
}

function phpcstrcasecmp($text1, $text2)
{
  global $language;
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strcasecmp($text1,$text2);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  $subst1=$language["charset_subst1"];
  $subst2=$language["charset_subst2"];
  $text1=strtr(strtr($text1,$subst1,$subst2),$uppers,$lowers);
  $text2=strtr(strtr($text2,$subst1,$subst2),$uppers,$lowers);
  return strcmp($text1,$text2);
}

function phpcstrncasecmp($text1, $text2, $length)
{
  global $language;
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strncasecmp($text1,$text2,$length);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  $subst1=$language["charset_subst1"];
  $subst2=$language["charset_subst2"];
  $text1=strtr(strtr($text1,$subst1,$subst2),$uppers,$lowers);
  $text2=strtr(strtr($text2,$subst1,$subst2),$uppers,$lowers);
  return strncmp($text1,$text2,$length);
}

function phpcstrfix($text1, $text2, $html=false)
{
  if(!$html || strpos($text1,"&")===false) return $text2;
  preg_match_all("{&[A-Za-z\d]+;}",$text1,$matches,PREG_OFFSET_CAPTURE);
  foreach($matches[0] as $match)
    $text2=substr_replace($text2,$match[0],$match[1],strlen($match[0]));
  return $text2;
}

function phpcstrtoupper($text, $html=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strtoupper($text,$html);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  return phpcstrfix($text,strtr($text,$lowers,$uppers),$html);
}

function phpcstrtolower($text, $html=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(class_exists("UnicodeSupport")) return UnicodeSupport::strtolower($text,$html);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  return phpcstrfix($text,strtr($text,$uppers,$lowers),$html);
}

function phpcucfirst($text, $html=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(class_exists("UnicodeSupport")) return UnicodeSupport::ucfirst($text,$html);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  $result=$text; $result[0]=strtr($result[0],$lowers,$uppers);
  return phpcstrfix($text,$result,$html);
}

function phpcucwords($text, $html=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(class_exists("UnicodeSupport")) return UnicodeSupport::ucwords($text,$html);
  $uppers=$language["charset_uppers"];
  $lowers=$language["charset_lowers"];
  $result=$text; $result[0]=strtr($result[0],$lowers,$uppers);
  $pattern="{[^$language[charset_regexp]][$language[charset_regexp]]}";
  preg_match_all($pattern,$result,$matches,PREG_OFFSET_CAPTURE);
  foreach($matches[0] as $match)
    $result[$match[1]+1]=strtr($result[$match[1]+1],$lowers,$uppers);
  return phpcstrfix($text,$result,$html);
}

function phpcpad($text, $length=2, $string="0")
{
  $textlength=phpcstrlen($text);
  $strlength=phpcstrlen($string);
  if($textlength>=$length || !$strlength) return (string)$text;
  if($textlength+$strlength==$length) return $string.$text;
  $count=ceil(($length-$textlength)/$strlength);
  $prefix=phpcsubstr(str_repeat($string,$count),0,$length-$textlength);
  return $prefix.$text;
}

function phpcpattern($pattern)
{
  if(class_exists("UnicodeSupport")) return UnicodeSupport::processPattern($pattern);
  return $pattern;
}

/******************************************************************************/

function phpchash($text, $limit=false, $binary=false, $isfile=false)
{
  $function=defined("PhpcHashFunction")?PhpcHashFunction:"md5";
  $prefix=defined("PhpcHashPrefix")?PhpcHashPrefix:"";
  if($isfile) { $function.="_file"; $prefix=""; }
  $result=(string)@$function($prefix.$text);
  if($binary) $result=hex2bin($result);
  if($limit===false || strlen($result)<=$limit) return $result;
  return substr($result,0,$limit);
}

function phpcmatch($mask, $text=false, $caseless=false)
{
  if($mask=="*") return true;
  if($mask=="" || $text===false) return false;
  $pattern="{^(".preg_quote($caseless?phpcstrtolower($mask):$mask).")\$}";
  $pattern=str_replace("\\*",".*",$pattern);
  $pattern=str_replace(",","|",$pattern);
  return preg_match($pattern,$caseless?phpcstrtolower($text):$text);
}

function phpccallback($callback)
{
  if(!is_string($callback) || $callback=="") return false;
  $identifier="[A-Za-z_][A-Za-z_\d]*";
  $pattern="{^($identifier)(?:(?:\.|::)($identifier))?\$}";
  if(!preg_match($pattern,$callback,$matches)) return false;
  return isset($matches[2])?array($matches[1],$matches[2]):$matches[1];
}

function phpchtmldecode($text, $extra=false)
{
  if(($text=(string)$text)==="") return "";
  if(strpos($text,"&")===false) return $text;
  if($extra) {
    $text=str_replace(array("&ndash;","&mdash;","&minus;"),"-",$text);
    $text=str_replace(array("&laquo;","&raquo;","&ldquo;","&rdquo;"),"\"",$text);
    $text=str_replace(array("&lsquo;","&rsquo;"),"'",$text);
    $text=str_replace("&hellip;","...",$text);
    $text=str_replace("&nbsp;"," ",$text);
  }
  $search=array("&lt;","&gt;","&quot;","&#34;","&#034;","&#39;","&#039;","&amp;");
  $replace=array("<",">","\"","\"","\"","'","'","&");
  return str_replace($search,$replace,$text);
}

function phpcurlencode($text, $address=false, $recode=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(!$address) return str_replace("%2F","/",rawurlencode($text));
  if(CompilerRecodeSpaces) $text=str_replace(" ","_",$text);
  if(CompilerRecodeRequest && $recode) {
    $charset=phpcstrtoupper($language["charset_iconv"]);
    $text=@iconv($charset,"UTF-8",$text);
  }
  if($recode) return str_replace("%2F","/",rawurlencode($text));
  $pattern=phpcpattern("{[^".CompilerRecodePreserve."]+}e");
  $text=preg_replace($pattern,"rawurlencode(\"\\0\")",$text);
  return str_replace("%5C%27","%27",$text);
}

function phpcurldecode($text, $address=false, $recode=false)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(!$address) return rawurldecode($text);
  $text=rawurldecode(str_replace("+"," ",$text));
  if(CompilerRecodeRequest && $recode) {
    $charset=phpcstrtoupper($language["charset_iconv"]);
    $success=@iconv("UTF-8","UTF-8",$text)==$text;
    if($success) $text=@iconv("UTF-8",$charset,$text);
  }
  return CompilerRecodeSpaces?str_replace("_"," ",$text):$text;
}

/******************************************************************************/

function combineArrays($keys, $values)
{
  $keycount=count($keys);
  if(!$keycount) return array();
  if(function_exists("array_combine")) {
    while(count($values)<$keycount) $values[]=false;
    if($keycount==count($values)) return array_combine($keys,$values);
    return array_combine($keys,array_slice($values,0,$keycount));
  }
  $values=array_values($values);
  while(count($values)<$keycount) $values[]=false;
  $result=array();
  $index=0;
  foreach($keys as $key) $result[$key]=$values[$index++];
  return $result;
}

function conjunctArrays($scheme)
{
  $result=array();
  if(!count($scheme)) return $result;
  $keys=array_keys($scheme);
  $indexes=array_keys($scheme[$keys[0]]);
  foreach($indexes as $index) {
    $line=array();
    foreach($keys as $key) $line[$key]=$scheme[$key][$index];
    $result[]=$line;
  }
  return $result;
}

function arrangeArrayValues($array)
{
  foreach($array as $index=>$value)
    if(isTrueInteger($value)) $array[$index]=(int)$value;
    else if(isTrueFloat($value)) $array[$index]=(float)$value;
  return $array;
}

function arrangeArraysSet($arrays)
{
  foreach($arrays as $index=>$array)
    $arrays[$index]=arrangeArrayValues($array);
  return $arrays;
}

function selectArrayKeys($array, $keys)
{
  $result=array();
  if(!is_array($keys)) $keys=$keys==""?array():explode(",",$keys);
  foreach($keys as $key) if(isset($array[$key])) $result[$key]=$array[$key];
  return $result;
}

function groupArrayByColumn($array, $column)
{
  $result=array();
  foreach($array as $line) {
    $key=$line[$column];
    if(!isset($result[$key])) $result[$key]=array();
    $result[$key][]=$line;
  }
  return $result;
}

function areArraysEqual($array1, $array2)
{
  foreach($array1 as $value) {
    $key=array_search($value,$array2);
    if($key===false) return false;
    unset($array2[$key]);
  }
  return !count($array2);
}

function extractArrayLines($array, $column)
{
  $result=array();
  foreach($array as $line) $result[$line[$column]]=$line;
  return $result;
}

function extractArrayColumn($array, $column)
{
  $result=array();
  foreach($array as $key=>$line) $result[$key]=$line[$column];
  return $result;
}

function extractArrayColumns($array, $column1, $column2)
{
  $result=array();
  foreach($array as $line) $result[$line[$column1]]=$line[$column2];
  return $result;
}

function isArrayFieldPresent($array, $column, $needle, $caseless=false)
{
  if($caseless) foreach($array as $line)
    if(!phpcstrcasecmp($line[$column],$needle)) return true;
  if(!$caseless) foreach($array as $line)
    if($line[$column]==$needle) return true;
  return false;
}

function searchArrayKey($array, $column, $needle, $caseless=false)
{
  if($caseless) foreach($array as $key=>$line)
    if(!phpcstrcasecmp($line[$column],$needle)) return $key;
  if(!$caseless) foreach($array as $key=>$line)
    if($line[$column]==$needle) return $key;
  return false;
}

function searchArrayKeyByPrefix($array, $column, $needle, $caseless=true)
{
  $result=false;
  $weight=-1;
  foreach($array as $key=>$line) {
    $length=phpcstrlen($line[$column]);
    if($length<=$weight) continue;
    if($caseless && phpcstrncasecmp($line[$column],$needle,$length)) continue;
    if(!$caseless && phpcstrncmp($line[$column],$needle,$length)) continue;
    $result=$key;
    $weight=$length;
  }
  return $result;
}

function searchArrayField($array, $column1, $column2, $needle, $caseless=false)
{
  if($caseless) foreach($array as $line)
    if(!phpcstrcasecmp($line[$column1],$needle)) return $line[$column2];
  if(!$caseless) foreach($array as $line)
    if($line[$column1]==$needle) return $line[$column2];
  return false;
}

function searchArrayLine($array, $column, $needle, $caseless=false)
{
  if($caseless) foreach($array as $line)
    if(!phpcstrcasecmp($line[$column],$needle)) return $line;
  if(!$caseless) foreach($array as $line)
    if($line[$column]==$needle) return $line;
  return false;
}

/******************************************************************************/

function addSlashesSmart($text)
{
  static $quotes;
  if(!isset($quotes)) $quotes=
    function_exists("get_magic_quotes_gpc")?get_magic_quotes_gpc():false;
  $text=is_scalar($text)?(string)$text:"";
  return $quotes?addslashes($text):$text;
}

function stripSlashesSmart($text)
{
  static $quotes;
  if(!isset($quotes)) $quotes=
    function_exists("get_magic_quotes_gpc")?get_magic_quotes_gpc():false;
  $text=is_scalar($text)?(string)$text:"";
  return $quotes?stripslashes($text):$text;
}

function explodeSmart($separator, $text)
{
  return $text==""?array():explode($separator,$text);
}

function explodeAssigns($separator, $text, $symbol="=")
{
  $result=array();
  $pieces=explodeSmart($separator,$text);
  foreach($pieces as $piece) {
    $piece=explode($symbol,$piece,2);
    $result[$piece[0]]=ifset($piece[1],true);
  }
  return $result;
}

function implodeAssigns($glue, $pieces, $symbol="=")
{
  foreach($pieces as $key=>$piece) $pieces[$key]=$key.$symbol.$piece;
  return implode($glue,$pieces);
}

function incrementIdentifier($text)
{
  preg_match("{^(.*?)(\d*)\$}",$text,$matches);
  return $matches[1].($matches[2]+1);
}

function formatInteger($value)
{
  global $language;
  return number_format((int)$value,0,$language["format_separator"],$language["format_thousands"]);
}

function formatFloat($value)
{
  global $language;
  return number_format((float)$value,$language["format_decimals"],$language["format_separator"],$language["format_thousands"]);
}

function format($text, $arguments=array())
{
  if(!is_array($arguments)) $arguments=array($arguments);
  $pattern="{(%[dfs])}";
  $split=preg_split($pattern,$text,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
  $index=0;
  foreach($split as $key=>$item) if(preg_match($pattern,$item)) {
    $argument=$index<count($arguments)?$arguments[$index++]:false;
    switch($item) {
      case "%d": $split[$key]=formatInteger($argument); break;
      case "%f": $split[$key]=formatFloat($argument); break;
      case "%s": $split[$key]=(string)$argument; break;
    }
  }
  return implode("",$split);
}

function encode($text, $seed="")
{
  $scramble=phpchash(EncodingPrefix.$seed,false,true);
  $textlength=strlen($text);
  $hashlength=strlen($scramble);
  for($index=$position=0; $index<$textlength; $index++) {
    if($position>=$hashlength) { $scramble=phpchash($scramble,false,true); $position=0; }
    $text[$index]=chr(ord($text[$index])+ord($scramble[$position++]));
  }
  return bin2hex($text);
}

function decode($text, $seed="")
{
  $text=hex2bin($text);
  $scramble=phpchash(EncodingPrefix.$seed,false,true);
  $textlength=strlen($text);
  $hashlength=strlen($scramble);
  for($index=$position=0; $index<$textlength; $index++) {
    if($position>=$hashlength) { $scramble=phpchash($scramble,false,true); $position=0; }
    $text[$index]=chr(ord($text[$index])-ord($scramble[$position++]));
  }
  return $text;
}

/******************************************************************************/

function optimizeText($text)
{
  if(($text=(string)$text)==="") return "";
  if(strpos($text,"\n")===false) return optimizeTextStrict($text);
  $text=trim(strtr($text,"\t\r","  "));
  $text=preg_replace("{(?<! ) +\n}","\n",$text);
  return str_replace("\n","\r\n",$text);
}

function optimizeTextStrict($text)
{
  if(($text=(string)$text)==="") return "";
  return preg_replace("{[ \t\r\n]+}"," ",trim($text));
}

function quoteText($text)
{
  if(($text=(string)$text)==="") return "\"\"";
  $search=array("\\","\"","\$","\t","\r","\n");
  $replace=array("\\\\","\\\"","\\\$","\\t","\\r","\\n");
  return "\"".str_replace($search,$replace,$text)."\"";
}

function unquoteText($text)
{
  if(($text=(string)$text)==="") return "";
  $length=strlen($text);
  if($length<3 || $text[0]!="\"" || $text[$length-1]!="\"") return "";
  $replace=array("\\\\"=>"\\","\\\""=>"\"","\\\$"=>"\$");
  $replace+=array("\\t"=>"\t","\\r"=>"\r","\\n"=>"\n");
  return strtr(substr($text,1,$length-2),$replace);
}

function filterText($text, $suspicious=false)
{
  if(($text=(string)$text)==="") return "";
  $entity=strpos($text,"&")!==false;
  $text=htmlspecialchars($text,ENT_QUOTES);
  $pattern=$suspicious?"{&amp;(?=\w+;|#039;)}":"{&amp;(?=#?\w+;)}";
  if($entity) $text=preg_replace($pattern,"&",$text);
  if($suspicious) $text=preg_replace("{[a-z]*script:}i","noscript:",$text);
  return str_replace("\r\n",PredefinedNewline."\r\n",$text);
}

function chopText($text, $limit, $words=true)
{
  global $language;
  if(($text=(string)$text)==="") return "";
  if(class_exists("UnicodeSupport")) return UnicodeSupport::chopText($text,$limit,$words);
  if(strlen($text)<=$limit) return $text;
  $saved=$limit=max($limit-strlen(PredefinedDots),0);
  if($words) {
    $pattern="{^[$language[charset_regexp]]+\$}";
    while($limit>0 && preg_match($pattern,substr($text,$limit-1,2))) $limit--;
    if(!$limit) $limit=$saved;
  }
  return rtrim(substr($text,0,$limit),PredefinedChop).PredefinedDots;
}

function antispamText($text, $method="hex")
{
  if(($text=(string)$text)==="") return "";
  if(!CompilerAntispamEnabled) return $text;
  if($method=="hex" || $method=="url") {
    $text=phpchtmldecode($text);
    $prefix=$method=="hex"?"&#x":"%";
    $suffix=$method=="hex"?";":"";
    for($index=strlen($text)-1; $index>=0; $index--) {
      if(ord($text[$index])>=128) continue;
      if($method=="url" && $text[$index]=="@") continue;
      $replace=$prefix.bin2hex($text[$index]).$suffix;
      $text=substr_replace($text,$replace,$index,1);
    }
    return $text;
  }
  $class=class_exists("AntispamSupport");
  return $class?AntispamSupport::processText($text,$method):$text;
}

/******************************************************************************/

function phpcmicrotime()
{
  $microtime=microtime();
  if(is_float($microtime)) return $microtime;
  $microtime=explode(" ",$microtime);
  return array_sum($microtime);
}

function phpctime()
{
  global $timeOffsetServer;
  return time()+round($timeOffsetServer*OneHour);
}

function phpcdate($format, $time=false)
{
  global $language, $timeOffsetClient;
  if($time===false) $time=phpctime();
  $time+=round($timeOffsetClient*OneHour);
  $index=0;
  while($index<strlen($format)) {
    if($format[$index]=="\\") { $index+=2; continue; }
    $replacement=false;
    switch($format[$index]) {
      case "l": $replacement=$language["weekday"][@date("w",$time)]; break;
      case "D": $replacement=$language["weekday_short"][@date("w",$time)]; break;
      case "F": $replacement=$language["month"][@date("n",$time)]; break;
      case "f": $replacement=$language["month_gen"][@date("n",$time)]; break;
      case "M": $replacement=$language["month_short"][@date("n",$time)]; break;
    }
    if($replacement!==false) {
      $replacement=preg_replace("{.}","\\\\\\0",$replacement);
      $format=substr_replace($format,$replacement,$index,1);
      $index+=strlen($replacement);
    }
    else $index++;
  }
  return @date($format,$time);
}

function timestamp2date($time=false)
{
  if($time===false) $time=phpctime();
  return $time?@date("Y-m-d",$time):"0000-00-00";
}

function timestamp2datetime($time=false)
{
  if($time===false) $time=phpctime();
  return $time?@date("Y-m-d H:i:s",$time):"0000-00-00 00:00:00";
}

function date2timestamp($datetime)
{
  $pattern="{^(\d+)-(\d+)-(\d+)\$}";
  if(!preg_match($pattern,$datetime,$matches)) return 0;
  $year=(int)$matches[1];
  $month=(int)$matches[2];
  $day=(int)$matches[3];
  $valid=$year || $month || $day;
  return $valid?@mktime(12,0,0,$month,$day,$year):0;
}

function datetime2timestamp($datetime)
{
  $pattern="{^(\d+)-(\d+)-(\d+)\s*(\d*):?(\d*):?(\d*)\$}";
  if(!preg_match($pattern,$datetime,$matches)) return 0;
  $year=(int)$matches[1];
  $month=(int)$matches[2];
  $day=(int)$matches[3];
  $hour=(int)$matches[4];
  $minute=(int)$matches[5];
  $second=(int)$matches[6];
  $valid=$year || $month || $day;
  return $valid?@mktime($hour,$minute,$second,$month,$day,$year):0;
}

/******************************************************************************/

function sendParameter($name, $value, $exact=false)
{
  if(!$exact) $value=addSlashesSmart($value);
  $_GET[$name]=$value;
  $_REQUEST[$name]=$value;
}

function acceptParameter($name)
{
  if(!is_array($name)) $name=array($name);
  $result=$_REQUEST;
  foreach($name as $part) {
    if(!is_array($result) || !isset($result[$part])) return "";
    $result=$result[$part];
  }
  $result=is_scalar($result)?stripSlashesSmart($result):"";
  if(class_exists("PrefilterSupport"))
    $result=PrefilterSupport::processText($result);
  return $result;
}

function acceptIntParameter($name, $min=false, $max=false)
{
  $value=(int)trim(acceptParameter($name));
  if($max!==false && $value>$max) $value=$max;
  if($min!==false && $value<$min) $value=$min;
  return $value;
}

function acceptFloatParameter($name, $min=false, $max=false)
{
  $value=trim(acceptParameter($name));
  $value=(float)str_replace(",",".",$value);
  if($max!==false && $value>(float)$max) $value=(float)$max;
  if($min!==false && $value<(float)$min) $value=(float)$min;
  return $value;
}

function acceptStringParameter($name, $limit=false, $optimize=true)
{
  $value=(string)acceptParameter($name);
  if($optimize) $value=optimizeText($value);
  if($limit!==false) $value=chopText($value,$limit);
  return $value;
}

function acceptAlphaParameter($name, $limit=100)
{
  $value=trim(acceptParameter($name));
  if($value=="" || strlen($value)>$limit) return "";
  return preg_match("{^[A-Za-z_\d]+\$}",$value)?$value:"";
}

function acceptDateParameter($name)
{
  $value=trim(acceptParameter($name));
  if(preg_match("{^(\d+)-(\d+)-(\d+)\$}",$value,$matches)) {
    $year=(int)$matches[1];
    $month=(int)$matches[2];
    $day=(int)$matches[3];
  }
  else {
    if(!is_array($name)) $name=array($name);
    $year=acceptIntParameter(array_merge($name,array("year")));
    $month=acceptIntParameter(array_merge($name,array("month")));
    $day=acceptIntParameter(array_merge($name,array("day")));
  }
  if($year<1 || $year>9999) return false;
  if($month<1 || $month>12) return false;
  if($day<1 || $day>31) return false;
  return phpcpad($year,4)."-".phpcpad($month)."-".phpcpad($day);
}

function acceptArrayParameter($name, $keys=false)
{
  if(!is_array($name)) $name=array($name);
  $result=$_REQUEST;
  foreach($name as $part) {
    if(!is_array($result) || !isset($result[$part])) return array();
    $result=$result[$part];
  }
  if(!is_array($result)) return array();
  $result=$keys?array_keys($result):array_values($result);
  $prefilter=class_exists("PrefilterSupport");
  foreach($result as $index=>$value) {
    $value=is_scalar($value)?stripSlashesSmart($value):"";
    if($prefilter) $value=PrefilterSupport::processText($value);
    $result[$index]=$value;
  }
  return $result;
}

/******************************************************************************/

function outputStart()
{
  global $fileSystem;
  if(headers_sent($file,$line)) {
    $fileSystem->localize($file);
    fatalError("fatal_gzip",array($file,$line));
  }
  $buffer="";
  while(ob_get_level()) $buffer=ob_get_clean().$buffer;
  ob_start("outputGzipHandler");
  ob_start();
  echo $buffer;
}

function outputErase($nocompress=false)
{
  if($nocompress) @define("OutputCompressionSkip",true);
  while(ob_get_level()>=2) ob_end_clean();
  ob_start();
}

function outputAddHandler($callback)
{
  ob_start($callback);
}

function outputGzipHandler($content)
{
  if(defined("OutputCompressionSkip")) return $content;
  if(OutputCompressionEnabled && $content!="") {
    $encoding=ifset($_SERVER["HTTP_ACCEPT_ENCODING"],"");
    preg_match_all("{[\w\-]+}",$encoding,$matches);
    $encoding=false;
    if(in_array("x-gzip",$matches[0])) $encoding="x-gzip";
    if(in_array("gzip",$matches[0])) $encoding="gzip";
    if($encoding && function_exists("gzcompress")) {
      $header="\x1f\x8b\x08\x00\x00\x00\x00\x00";
      $compressed=substr(gzcompress($content,OutputCompressionLevel),0,-4);
      $trailer=pack("V",crc32($content)).pack("V",strlen($content));
      $content=$header.$compressed.$trailer;
      @header("Content-Encoding: $encoding");
    }
  }
  @header("Content-Length: ".strlen($content));
  return $content;
}

function halt()
{
  global $compiler;
  while(ob_get_level()) ob_end_flush();
  if(is_object($compiler)) $compiler->shutdown();
  exit;
}

/******************************************************************************/

function getServerAddress()
{
  return $_SERVER["SERVER_ADDR"];
}

function getClientAddress()
{
  return $_SERVER["REMOTE_ADDR"];
}

function sendStatus($code)
{
  $statuses=array(
    200=>"OK",
    401=>"Unauthorized",
    403=>"Forbidden",
    404=>"Not Found");
  $status=ifset($statuses[$code],"Unknown");
  @header("HTTP/1.0 $code $status");
  @header("HTTP/1.1 $code $status");
  @header("Status: $code $status");
}

function redirect($link, $params=array(), $anchor="")
{
  global $compiler;
  $complex=char($link,0)!="/" || count($params);
  if($complex) $link=$compiler->createLink($link,$params);
  outputErase();
  @header("Location: $link$anchor");
  halt();
}

function redirectBack($default=false)
{
  global $compiler;
  $link=$compiler->createLinkBack($default);
  outputErase();
  @header("Location: $link");
  halt();
}

function httpAuthentication()
{
  $username=trim(stripSlashesSmart(ifset($_SERVER["PHP_AUTH_USER"],"")));
  $password=trim(stripSlashesSmart(ifset($_SERVER["PHP_AUTH_PW"],"")));
  return compact("username","password");
}

function httpAuthenticate($realm="Restricted Area", $message="")
{
  global $language;
  $realm=str_replace("\"","''",$realm);
  outputErase();
  sendStatus(401);
  @header("WWW-Authenticate: Basic realm=\"$realm\"");
  @header("Content-Type: text/html; charset=$language[charset]");
  echo $message;
  halt();
}

function imageDisposition($filename, $mimetype, $content)
{
  global $language;
  outputErase(true);
  @header("Cache-Control: max-age=".OneYear.", private");
  @header("Pragma: cache");
  @header("Content-Type: $mimetype; charset=$language[charset]");
  @header("Content-Length: ".strlen($content));
  @header("Content-Disposition: inline; filename=$filename");
  @header("Content-Transfer-Encoding: binary");
  echo $content;
  halt();
}

function contentDisposition($filename, $mimetype, $content)
{
  global $language;
  outputErase(true);
  @header("Cache-Control: no-store, no-cache, must-revalidate");
  @header("Pragma: no-cache");
  @header("Content-Type: $mimetype; charset=$language[charset]");
  @header("Content-Length: ".strlen($content));
  @header("Content-Disposition: attachment; filename=$filename");
  @header("Content-Transfer-Encoding: binary");
  echo $content;
  halt();
}

/******************************************************************************/

function phpcsetcookie($name, $value="", $permanent=false)
{
  static $secure;
  if(!isset($secure)) $secure=CompilerSecureCookies && (float)phpversion()>=5.2;
  $expire=$permanent?phpctime()+OneYear:0;
  $root=defined("PhpcRoot")?PhpcRoot:"/";
  if($secure)
    @setcookie($name,$value,$expire,$root,"",false,true);
    else @setcookie($name,$value,$expire,$root);
}

function processEncodedCookie($requestParam, $cookieParam, $update=false)
{
  $result=$cookie="";
  $seed=$cookieParam.getClientAddress();
  if(isset($_COOKIE[$cookieParam]) && is_scalar($_COOKIE[$cookieParam]))
    $result=$cookie=decode(stripSlashesSmart($_COOKIE[$cookieParam]),$seed);
  if(isset($_POST[$requestParam]) && is_scalar($_POST[$requestParam]))
    $result=trim(stripSlashesSmart($_POST[$requestParam]));
  if(isset($_GET[$requestParam]) && is_scalar($_GET[$requestParam]))
    $result=trim(stripSlashesSmart($_GET[$requestParam]));
  if(phpcstrlen($result)>100) $result="";
  if($update!==false) $result=$update; else return $result;
  if($result!=$cookie) phpcsetcookie($cookieParam,encode($result,$seed));
  return $result;
}

function isLocalhost()
{
  return getServerAddress()==PredefinedLocalhost;
}

function isAdministrator($script=false)
{
  global $adminAccessRights;
  static $access;
  if(defined("PhpcMemberPanel")) return isMember($script);
  if(!isset($access)) {
    $password=processEncodedCookie(PhpcPasswordParam,PhpcPasswordCookie);
    $access=ifset($adminAccessRights[$password],"");
    if($access=="") $password="";
    processEncodedCookie(PhpcPasswordParam,PhpcPasswordCookie,$password);
  }
  return phpcmatch($access,$script);
}

function isMember($script)
{
  global $database, $usersSupport;
  static $access;
  if($script===false) return false;
  if(!isset($access)) {
    if(!$database->isTablePresent("users")) fatalError("fatal_users");
    if(!$database->isTablePresent("usergroups")) fatalError("fatal_users");
    $membername=processEncodedCookie(PhpcMemberNameParam,PhpcMemberNameCookie);
    $memberpass=processEncodedCookie(PhpcMemberPassParam,PhpcMemberPassCookie);
    $user=$membername?$database->getLine("users","username=".slashes($membername)):false;
    if($user) {
      $supported=class_exists("UsersSupport");
      $passhash=$supported?$usersSupport->hashPassword($memberpass,$user):phpchash($memberpass);
      if($passhash!=$user["password"]) $user=false;
    }
    if($user) {
      $usergroup=$database->getLine("usergroups","id=$user[usergroupid]");
      if(!isset($usergroup["memberaccess"])) fatalError("fatal_useraccess");
      $GLOBALS["userInfo"]=$user;
      $GLOBALS["usergroupInfo"]=$usergroup;
    }
    $access=$user?$usergroup["memberaccess"]:"";
    if($access=="") $membername=$memberpass="";
    processEncodedCookie(PhpcMemberNameParam,PhpcMemberNameCookie,$membername);
    processEncodedCookie(PhpcMemberPassParam,PhpcMemberPassCookie,$memberpass);
  }
  return phpcmatch($access,$script);
}

function isClientBanned($blackList)
{
  $ipaddress=getClientAddress();
  preg_match_all("{\d[\d.]*}",$blackList,$matches);
  foreach($matches[0] as $mask)
    if(!strncmp($ipaddress,$mask,strlen($mask))) return true;
  return false;
}

/******************************************************************************/

function fatalError($title, $params=array(), $error="", $query="")
{
  global $language;
  $title=format($language[$title],$params);
  $special=preg_match("{table.*(settings|styles)\b.*exist}i",$error);
  if($special) { $title=$language["fatal_install"]; $error=$query=""; }
  if($error!="") {
    $error=optimizeTextStrict($error);
    if(strlen($error)>500) $error=substr($error,0,500).PredefinedDots;
    $error=str_replace("{text}",htmlspecialchars($error),FatalReportError);
    $error=str_replace("{header}",$language["fatal_error"],$error);
  }
  if($query!="") {
    $query=optimizeTextStrict($query);
    if(strlen($query)>500) $query=substr($query,0,500).PredefinedDots;
    $query=str_replace("{text}",htmlspecialchars($query),FatalReportQuery);
    $query=str_replace("{header}",$language["fatal_query"],$query);
  }
  $report=str_replace("{text}",$title,FatalReport);
  $report=str_replace("{header}",$language["fatal_title"],$report);
  $report=str_replace("{error}",$error,$report);
  $report=str_replace("{query}",$query,$report);
  outputErase();
  @header("Cache-Control: no-store, no-cache, must-revalidate");
  @header("Pragma: no-cache");
  @header("Content-Type: text/html; charset=$language[charset]");
  echo $report;
  halt();
}

function variableToString($value, $prefix="", $offset=0)
{
  $result=str_repeat(" ",$offset);
  if(is_null($value)) $value="null";
  if(is_bool($value)) $value=$value?"true":"false";
  $array=is_array($value);
  $object=is_object($value);
  if(!$array && !$object) { $result.=$prefix.$value; return $result; }
  $result.=$prefix.($object?"object":"array")."(";
  if($object) $value=get_object_vars($value);
  if(count($value)) $result.="\r\n";
  foreach($value as $key=>$item)
    $result.=variableToString($item,"$key=>",$offset+2)."\r\n";
  if(count($value)) $result.=str_repeat(" ",$offset);
  $result.=")";
  return $result;
}

function trace($variable)
{
  global $language;
  outputErase();
  @header("Cache-Control: no-store, no-cache, must-revalidate");
  @header("Pragma: no-cache");
  @header("Content-Type: text/plain; charset=$language[charset]");
  echo variableToString($variable);
  halt();
}

/******************************************************************************/

function generatePassword($length, $digits=true, $small=true, $caps=true)
{
  $scramble=$digits?"0123456789":"";
  if($small) $scramble.="abcdefghijklmnopqrstuvwxyz";
  if($caps) $scramble.="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $puzzle=phpcmicrotime();
  $puzzle=(int)(($puzzle-(int)$puzzle)*1000)+1000;
  mt_srand();
  for($index=0; $index<$puzzle; $index++) random();
  for($index=0; $index<$puzzle; $index+=5) {
    $saved=$scramble; $scramble="";
    while($left=strlen($saved)) {
      $affect=phpcmicrotime();
      $affect=(int)(($affect-(int)$affect)*$left);
      $position=(random($left)+$affect)%$left;
      $scramble.=$saved[$position];
      $saved=substr_replace($saved,"",$position,1);
    }
  }
  for($index=0; $index<$puzzle; $index++) random();
  mt_srand();
  return substr($scramble,0,$length);
}

function createDateOptions($value=false, $strict=false, $minyear=false, $maxyear=false)
{
  global $language;
  $year=phpcdate("Y");
  if($minyear===false) $minyear=defined("PredefinedMinYear")?PredefinedMinYear:$year-50;
  if($maxyear===false) $maxyear=defined("PredefinedMaxYear")?PredefinedMaxYear:$year+50;
  $days=combineArrays(range(1,31),range(1,31));
  $months=$language["month_gen"];
  $years=combineArrays(range($minyear,$maxyear),range($minyear,$maxyear));
  if(!$strict) {
    $days=array("")+$days;
    $months=array("")+$months;
    $years=array("")+$years;
  }
  if(preg_match("{^(\d+)-(\d+)-(\d+)\$}",$value,$matches)) {
    $year=(int)$matches[1];
    $month=(int)$matches[2];
    $day=(int)$matches[3];
    $value=compact("day","month","year");
  }
  else unset($value);
  return compact("days","months","years","value");
}

function createSimpleCompareFunction($column, $caseless=false)
{
  $a="\$a[".quoteText($column)."]";
  $b="\$b[".quoteText($column)."]";
  if($caseless)
    $content="return phpcstrcasecmp($a,$b);";
    else $content="if($a==$b) return 0; return $a<$b?-1:1;";
  return create_function("\$a,\$b",$content);
}

function createComplexCompareFunction($columns)
{
  if(!is_array($columns)) $columns=explodeSmart(",",$columns);
  $content="";
  foreach($columns as $column=>$options) {
    if(!is_array($options)) { $column=$options; $options=array(); }
    $a="\$a[".quoteText($column)."]";
    $b="\$b[".quoteText($column)."]";
    $minus=isset($options["desc"])?"-":"";
    $operator=isset($options["desc"])?">":"<";
    if(isset($options["caseless"]))
      $content.="\$c={$minus}phpcstrcasecmp($a,$b);";
      else $content.="\$c=$a==$b?0:($a$operator$b?-1:1);";
    $content.="if(\$c) return \$c;";
  }
  $content.="return 0;";
  return create_function("\$a,\$b",$content);
}

function createSimpleNavigation($min, $max, $current, $start=1)
{
  $result=array("range"=>array());
  for($index=$min; $index<=$max; $index++) {
    $item=array("value"=>$index,"label"=>$start++);
    if($index==$current) $item["current"]=true;
    $result["range"][]=$item;
  }
  $result["current"]=$current;
  if($current>$min) $result["prev"]=$current-1;
  if($current<$max) $result["next"]=$current+1;
  if($current>$min) $result["first"]=$min;
  if($current<$max) $result["last"]=$max;
  $result["count"]=max($max-$min+1,0);
  return $result;
}

function createComplexNavigation($min, $max, $current, $limit=9, $start=1)
{
  $result=array("range"=>array());
  $offset=floor(($limit-1)/2);
  $mindisp=max(min($current-$offset,$max-$limit+1),$min);
  $maxdisp=min($mindisp+$limit-1,$max);
  $start+=$mindisp-$min;
  for($index=$mindisp; $index<=$maxdisp; $index++) {
    $item=array("value"=>$index,"label"=>$start++);
    if($index==$current) $item["current"]=true;
    $result["range"][]=$item;
  }
  $result["current"]=$current;
  if($current>$min) $result["prev"]=$current-1;
  if($current<$max) $result["next"]=$current+1;
  if($mindisp>$min) $result["prevmore"]=true;
  if($maxdisp<$max) $result["nextmore"]=true;
  if($current>$min) $result["first"]=$min;
  if($current<$max) $result["last"]=$max;
  $result["count"]=max($max-$min+1,0);
  return $result;
}

function parsePhpcManual($filename, $link)
{
  global $fileSystem;
  $result=array();
  $content=$fileSystem->openFile($filename);
  $link=format($link,"\\1");
  $pattern="{<section:(\w+)>(.*?)</section:\\1>}s";
  preg_match_all($pattern,$content,$matches,PREG_SET_ORDER);
  foreach($matches as $fragment) {
    $text=trim($fragment[2]);
    $text=preg_replace("{\{section:(\w+)\}}",$link,$text);
    $text=preg_replace("{\{const:(\w+)\}}e","\\1",$text);
    $result[$fragment[1]]=$text;
  }
  return $result;
}

?>
