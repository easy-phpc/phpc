<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

error_reporting(7);

require "config.php";
require "constant.php";
require "language.php";
require "function.php";
require "database.php";

define("StatisticsImageCounter","../images/statistics/counter.png");
define("StatisticsImageDigits","../images/statistics/digits.png");

/******************************************************************************/

function getStatistics()
{
  global $database;
  $phpctime=phpctime();
  $date=date("Y-m-d",$phpctime);
  $total=$database->getLine("statistics","date='0000-00-00'");
  $today=$database->getLine("statistics","date=".slashes($date));
  $sumhits=$total["hits"];
  $sumhosts=$total["hosts"];
  $maxonline=$total["online"];
  $hits=$today["hits"];
  $hosts=$today["hosts"];
  $minimalTime=$phpctime-PhpcSessionTimeout;
  $online=$database->getLinesCount("sessions","lastactivity>=$minimalTime");
  return compact("sumhits","sumhosts","maxonline","hits","hosts","online");
}

function makeNumber($image, $digits, $value, $x, $y, $color=0, $align=false)
{
  $value=(string)(int)$value;
  if($align) $x-=strlen($value)*6;
  for($index=0; $index<strlen($value); $index++) {
    $digit=ord($value[$index])-48;
    @imagecopy($image,$digits,$x,$y,$digit*6,$color*8,6,8);
    $x+=6;
  }
}

/******************************************************************************/

$database=new Database;
$database->modifyLine("sessions",array("getstatpng"=>"1"),"hash=".slashes($_COOKIE[PhpcSessionCookie])." AND ipaddress=".slashes(getClientAddress()));
//$database->addLine("sessions_test",array("hash"=>$_COOKIE[PhpcSessionCookie],"ipaddress"=>getClientAddress()));

@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
@header("Content-Type: image/png");

$image=@imagecreatefrompng(StatisticsImageCounter);
$digits=@imagecreatefrompng(StatisticsImageDigits);
$statistics=getStatistics();
makeNumber($image,$digits,$statistics["sumhits"],4,3);
makeNumber($image,$digits,$statistics["sumhosts"],4,12);
makeNumber($image,$digits,$statistics["maxonline"],4,21,1);
makeNumber($image,$digits,$statistics["hits"],84,3,0,true);
makeNumber($image,$digits,$statistics["hosts"],84,12,0,true);
makeNumber($image,$digits,$statistics["online"],84,21,1,true);
@imagetruecolortopalette($image,false,128);
@imagepng($image);

?>
