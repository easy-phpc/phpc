<?php

// PHPC Control Panel Plugin (50.25) - Statistics v1.4 by Dagdamor

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.4.5, Copyright 2007
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

/*

// Main Menu Construction Code
makeMenuGroup("header","statistics_menu");
if($database->isTablePresent("statistics")) {
  makeMenuItem("statistics_overview","statistics.php?action=overview");
  makeMenuItem("statistics_common","statistics.php?action=common");
  makeMenuItem("statistics_referers","statistics.php?action=referers");
  makeMenuItem("statistics_oneday","statistics.php?action=oneday");
}
else makeMenuItem("admin_install","statistics.php?action=install");
makeMenuGroup("footer");

*/

require "global.php";

define("CounterDefaultWidth",88);
define("CounterDefaultHeight",31);
define("ReferersOnPage",50);

adminLog();
$action=acceptStringParameter("action");

makeAdminPage("header");

/********************************* Functions **********************************/

function makeTableCellQuota($quota, $color=0)
{
  static $autoColor=1;
  if(!$color) { $color=$autoColor; $autoColor=$autoColor%4+1; }
  $link="statistics.php?action=image&image=bar";
  $width=max(round($quota*100)-5,0);
  makeTableCell("header");
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color}l\">";
  echo "<img width=\"$width%\" height=\"10\" border=\"0\" src=\"$link{$color}c\">";
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color}r\">";
  makeTableCell("footer");
}

function makeTableCellDoubleQuota($quota1, $quota2, $color1=3, $color2=1)
{
  $link="statistics.php?action=image&image=bar";
  $width1=max(round($quota1*100)-5,0);
  $width2=max(round($quota2*100)-5,0);
  makeTableCell("header");
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color1}l\">";
  echo "<img width=\"$width1%\" height=\"10\" border=\"0\" src=\"$link{$color1}c\">";
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color1}r\"><br>";
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color2}l\">";
  echo "<img width=\"$width2%\" height=\"10\" border=\"0\" src=\"$link{$color2}c\">";
  echo "<img width=\"3\" height=\"10\" border=\"0\" src=\"$link{$color2}r\">";
  makeTableCell("footer");
}

/**************************** Plugin Installation *****************************/

if($action=="install") {
  if($database->isTablePresent("statistics")) {
    makeError("admin_error_already");
    makeBreak();
    makeRefreshMenuLink();
  }
  else {
    makeNotification("admin_installstart");
    $success=false;
    switch($database->type) {
      case "mysql":
      case "dagsql":
        $database->customQuery("CREATE TABLE statistics (".
          "date DATE NOT NULL,".
          "hits INT NOT NULL,".
          "hosts INT NOT NULL,".
          "online INT NOT NULL,".
          "PRIMARY KEY (date))");
        makeNotification("admin_installtable","statistics");
        $database->customQuery("CREATE TABLE statreferers (".
          "id INT NOT NULL AUTO_INCREMENT,".
          "address TINYTEXT NOT NULL,".
          "counter INT NOT NULL,".
          "lastclick INT NOT NULL,".
          "PRIMARY KEY (id),".
          "UNIQUE KEY address (address(50)))");
        makeNotification("admin_installtable","statreferers");
        $success=true;
        break;
    }
    if($success) {
      $values=array("date"=>"0000-00-00");
      $database->addLine("statistics",$values);
      makeNotification("admin_installdata","statistics");
      makeNotification("admin_installsuccess");
      makeBreak();
      makeRefreshMenuLink();
    }
    else makeWrongDBError();
  }
}

/********************************** Overview **********************************/

if($action=="overview") {
  $phpctime=phpctime();
  $date=date("Y-m-d",$phpctime);
  $total=$database->getLine("statistics","date='0000-00-00'");
  $today=$database->getLine("statistics","date=".slashes($date));
  $minimalTime=$phpctime-PhpcSessionTimeout;
  $online=$database->getLinesCount("sessions","lastactivity>=$minimalTime");
  $columns=array(
    array("title"=>"statistics_overview_name","width"=>"80%"),
    "statistics_overview_value");
  makeTable("header",$columns);
  makeTableCellSimple("statistics_overview_counter",array(),true);
  makeTableCellImageSize(CounterDefaultWidth,CounterDefaultHeight,"/phpc/counter.php");
  $statistics=array(
    "statistics_overview_sumhits"=>(int)$total["hits"],
    "statistics_overview_sumhosts"=>(int)$total["hosts"],
    "statistics_overview_maxonline"=>(int)$total["online"],
    "statistics_overview_hits"=>(int)$today["hits"],
    "statistics_overview_hosts"=>(int)$today["hosts"],
    "statistics_overview_online"=>(int)$online);
  foreach($statistics as $name=>$value) {
    makeTableCellSimple($name,array(),true);
    makeTableCellSimple($value);
  }
  makeTable("footer");
}

/*********************************** Common ***********************************/

if($action=="common") {
  $statistics=$database->getOrderedLines("statistics","date DESC","date!='0000-00-00'");
  $months=array();
  foreach($statistics as $day) {
    $month=substr($day["date"],0,7);
    if(!isset($months[$month])) $months[$month]=array("hits"=>0,"hosts"=>0);
    $months[$month]["hits"]+=$day["hits"];
    $months[$month]["hosts"]+=$day["hosts"];
  }
  $maxValue=1;
  foreach($months as $month) {
    $maxValue=max($maxValue,$month["hits"]);
    $maxValue=max($maxValue,$month["hosts"]);
  }
  $columns=array(
    array("title"=>"statistics_common_date","width"=>"15%"),
    array("title"=>"statistics_common_quota","width"=>"50%"),
    "statistics_common_hits",
    "statistics_common_hosts",
    "statistics_common_options");
  makeTable("header",$columns);
  $sumHits=$sumHosts=0;
  $prevyear=false;
  foreach($months as $key=>$entry) {
    $year=(int)substr($key,0,4);
    $month=(int)substr($key,5,2);
    if($prevyear!==false && $year!=$prevyear) {
      makeTableTotals(array("","",$sumHits,$sumHosts));
      makeTable("footer");
      makeBreak();
      makeTable("header",$columns);
      $sumHits=$sumHosts=0;
    }
    $prevyear=$year;
    $time=mktime(0,0,0,$month,1,$year);
    $date=phpcdate($language["statistics_common_format"],$time);
    $sumHits+=$entry["hits"];
    $sumHosts+=$entry["hosts"];
    $links=array("statistics_common_detailed"=>"statistics.php?action=detailed&year=$year&month=$month");
    makeTableCellSimple($date);
    makeTableCellDoubleQuota($entry["hits"]/$maxValue,$entry["hosts"]/$maxValue);
    makeTableCellSimple($entry["hits"],array("align"=>"right"));
    makeTableCellSimple($entry["hosts"],array("align"=>"right"));
    makeTableCellLinks($links);
  }
  makeTableTotals(array("","",$sumHits,$sumHosts));
  makeTable("footer");
}

/********************************** Detailed **********************************/

if($action=="detailed") {
  $year=acceptIntParameter("year",2000);
  $month=acceptIntParameter("month",1,12);
  $mask=$year."-".($month<10?"0":"").$month."-%";
  $statistics=$database->getOrderedLines("statistics","date DESC","date LIKE ".slashes($mask));
  $maxValue=1;
  foreach($statistics as $entry) {
    $maxValue=max($maxValue,$entry["hits"]);
    $maxValue=max($maxValue,$entry["hosts"]);
  }
  $columns=array(
    array("title"=>"statistics_detailed_date","width"=>"15%"),
    array("title"=>"statistics_detailed_quota","width"=>"55%"),
    array("title"=>"statistics_detailed_hits","width"=>"10%"),
    array("title"=>"statistics_detailed_hosts","width"=>"10%"),
    "statistics_detailed_online");
  makeTable("header",$columns);
  $sumHits=$sumHosts=0;
  foreach($statistics as $entry) {
    $year=(int)substr($entry["date"],0,4);
    $month=(int)substr($entry["date"],5,2);
    $day=(int)substr($entry["date"],8,2);
    $time=mktime(0,0,0,$month,$day,$year);
    $date=phpcdate($language["format_datetime"]["date"],$time);
    $sumHits+=$entry["hits"];
    $sumHosts+=$entry["hosts"];
    makeTableCellSimple($date);
    makeTableCellDoubleQuota($entry["hits"]/$maxValue,$entry["hosts"]/$maxValue);
    makeTableCellSimple($entry["hits"],array("align"=>"right"));
    makeTableCellSimple($entry["hosts"],array("align"=>"right"));
    makeTableCellSimple($entry["online"],array("align"=>"right"));
  }
  makeTableTotals(array("","",$sumHits,$sumHosts));
  makeTable("footer");
}

/********************************** Referers **********************************/

if($action=="referers") {
  $full=acceptIntParameter("full",0,1);
  if(!$full) {
    $links=array("statistics_referers_full"=>"statistics.php?action=referers&full=1");
    makeLinks($links);
    makeBreak();
  }
  $order="counter DESC,address";
  $conditions=$full?"":("lastclick>=".(phpctime()-OneMonth));
  $totalCounter=$database->getLinesFunction("statreferers","SUM(counter)",$conditions);
  $maxCounter=$database->getMaxField("statreferers","counter",$conditions);
  if(!$totalCounter) $totalCounter=$maxCounter=1;
  $referers=getTablePagePortion("statreferers",$order,$conditions,ReferersOnPage,$page,$total);
  $columns=array(
    "statistics_referers_address",
    array("title"=>"statistics_referers_quota","width"=>"40%"),
    "statistics_referers_count",
    "statistics_referers_percent",
    "statistics_referers_lastclick");
  makeTable("header",$columns);
  foreach($referers as $referer) {
    $address=$referer["address"];
    //if(substr_count($address,".")==1) $address="www.".$address;
    //$title="[url=http://$address]$referer[address][/url]";
    $title="[url=http://$referer[address]]$referer[address][/url]";
    $percent=formatFloat($referer["counter"]/$totalCounter*100)."%";
    $lastclick=phpcdate($language["format_datetime"]["datetime"],$referer["lastclick"]);
    makeTableCellSimple($title);
    makeTableCellQuota($referer["counter"]/$maxCounter);
    makeTableCellSimple($referer["counter"],array("align"=>"right"));
    makeTableCellSimple($percent,array("align"=>"right"));
    makeTableCellSimple($lastclick);
  }
  makeTablePager($page,$total,"statistics.php?action=referers&full=$full&page=%s");
  makeTable("footer");
}

/********************************** Referers **********************************/

if($action=="oneday") {
  $full=acceptIntParameter("full",0,1);
  if(!$full) {
    $links=array("statistics_referers_full"=>"statistics.php?action=referers&full=1");
    makeLinks($links);
    makeBreak();
  }
  $order="counter DESC,address";
  $conditions=$full?"":("lastclick>=".(phpctime()-OneDay));
  $totalCounter=$database->getLinesFunction("statreferers","SUM(counter)",$conditions);
  $maxCounter=$database->getMaxField("statreferers","counter",$conditions);
  if(!$totalCounter) $totalCounter=$maxCounter=1;
  $referers=getTablePagePortion("statreferers",$order,$conditions,ReferersOnPage,$page,$total);
  $columns=array(
    array("width"=>"3%"),
    "statistics_referers_address",
    array("title"=>"statistics_referers_quota","width"=>"40%"),
    "statistics_referers_count",
    "statistics_referers_percent",
    "statistics_referers_lastclick");
  makeTable("header",$columns);
  $i=0;
  foreach($referers as $referer) {
    $i++;
    $address=$referer["address"];
    //if(substr_count($address,".")==1) $address="www.".$address;
    //$title="[url=http://$address]$referer[address][/url]";
    $title="[url=http://$referer[address]]$referer[address][/url]";
    $percent=formatFloat($referer["counter"]/$totalCounter*100)."%";
    $lastclick=phpcdate($language["format_datetime"]["datetime"],$referer["lastclick"]);
    makeTableCellSimple($i,array("align"=>"right"));
    makeTableCellSimple($title);
    makeTableCellQuota($referer["counter"]/$maxCounter);
    makeTableCellSimple($referer["counter"],array("align"=>"right"));
    makeTableCellSimple($percent,array("align"=>"right"));
    makeTableCellSimple($lastclick);
  }
  makeTablePager($page,$total,"statistics.php?action=referers&full=$full&page=%s");
  makeTable("footer");
}

/*********************************** Image ************************************/

if($action=="image") {
  $image=acceptStringParameter("image",100);
  switch($image) {
    case "bar1l": $content="R0lGODlhAwAKALMAADkAAAgAAFoICKUQEHsQEN4hIc4hIf85Of9SUudaWv9ra/+EhP+cnP+trQAAAAAAACwAAAAAAwAKAAAEExCdctgqqxm1dueegSQDAQiBEwEAOw=="; break;
    case "bar1c": $content="R0lGODlhAQAKAKIAACkAANYhIcZCQv9ra/dra/+EhAAAAAAAACwAAAAAAQAKAAADBhhT1CTAJAA7"; break;
    case "bar1r": $content="R0lGODlhAwAKALMAADkAACkAACEAAAgAAFoICKUQEHsQEM4hIZwxMf9SUsZCQudaWgAAAAAAAAAAAAAAACwAAAAAAwAKAAAEEfAUksxYgKmg+e4MIjAjYzIRADs="; break;
    case "bar2l": $content="R0lGODlhAwAKAMQAAL3/98b/76X/1hBCKTmta5z/xnPGlDmlY1rejHPenCmESiFjOUK1a3P/pYz/tRAxGAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAAwAKAAAFFWCDMAggMEJAOMXasu6RGMryDFAUAgA7"; break;
    case "bar2c": $content="R0lGODlhAQAKAKIAADmta5z/xoz/tXvWnFqlcwghEAAAAAAAACwAAAAAAQAKAAADBggS00PFJAA7"; break;
    case "bar2r": $content="R0lGODlhAwAKALMAAAgYEBBKKXPGlDmlYymESiFjOWvelEKEWlqlcwghEBAxGAAIAAAAAAAAAAAAAAAAACwAAAAAAwAKAAAEEXCQYMoSiqGk8+7MATAjYzIRADs="; break;
    case "bar3l": $content="R0lGODlhAwAKALMAAAAAOQAACAgIWhAQpRAQeyEh3iEhzjk5/1JS/1pa52tr/4SE/5yc/62t/wAAAAAAACwAAAAAAwAKAAAEExCdctgqqxm1dueegSQDAQiBEwEAOw=="; break;
    case "bar3c": $content="R0lGODlhAQAKAKIAAAAAKSEh1kJCxmtr/2tr94SE/wAAAAAAACwAAAAAAQAKAAADBhhT1CTAJAA7"; break;
    case "bar3r": $content="R0lGODlhAwAKALMAAAAAOQAAKQAAIQAACAgIWhAQpRAQeyEhzjExnFJS/0JCxlpa5wAAAAAAAAAAAAAAACwAAAAAAwAKAAAEEfAUksxYgKmg+e4MIjAjYzIRADs="; break;
    case "bar4l": $content="R0lGODlhAwAKAMQAAAgIAP//hP//nP//rf/nhP/ea//WUqVrEHtSEP+1Of+9Ulo5CN6UIdaMIeetWjkhAM6EIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAAwAKAAAFFaCRMIkQMMHQFMTasi6kOAfyLEAUAgA7"; break;
    case "bar4c": $content="R0lGODlhAQAKAKIAAP/nhP/ea8aUQtaMISkYAPe9awAAAAAAACwAAAAAAQAKAAADBjgB1SXEJAA7"; break;
    case "bar4r": $content="R0lGODlhAwAKALMAAAgIAKVrEHtSEJxzMf+9UsaUQlo5COetWjkhACkYAM6EISEQAAAAAAAAAAAAAAAAACwAAAAAAwAKAAAEEVAFQwQ4iJWk+e7MsDAcYzIRADs="; break;
    default: $content="R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
  }
  makeImage("image/gif",base64_decode($content));
}

/******************************************************************************/

echo "<script src=\"/js/jquery-1.6.min.js\"></script>\r\n";
echo "<script src=\"/js/main.js?".phpctime()."\"></script>\r\n";

makeAdminPage("footer");

?>
