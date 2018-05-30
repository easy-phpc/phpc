<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.4.5, Copyright 2007
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

// PHPC Online Plugin - Statistics v1.4 by Dagdamor

/****************************** Class Definition ******************************/

class StatisticsSupport
{
  /**
   * Возвращает поисковую фразу на основе анализа заголовка HTTP_REFERER
   * Автор: http://trickywebs.org.ua/
   */
  function search_query($queryUrl='')
  {
    $queryUrl=trim($queryUrl);
    if(empty($queryUrl)) return false;
    $parseUrl=parse_url($queryUrl);
    // Detect search engine
    switch($parseUrl['host'])
    {
      case 'yandex.ru':
      case 'images.yandex.ru':
      case 'yandex.ua':
      case 'images.yandex.ua':
      case 'yandex.by':
      case 'images.yandex.by':
      case 'yandex.com':
      case 'images.yandex.com':
      $word='text';
      $engine='yandex';
      break;
      
      case 'rambler.ru':
      case 'nova.rambler.ru':
      $word='query';
      $engine='rambler';
      break;
      
      case 'search.qip.ru':
      $word='query';
      $engine='qip';
      break;
      
      case 'yahoo.com':
      case 'search.yahoo.com':
      $word='p';
      $engine='yahoo';
      break;
      
      case 'search.icq.com':
      $word='q';
      $engine='icq';
      break;
      
      case 'google.ru':
      case 'google.com':
      case 'google.com.ua':
      case 'google.com.by':
      case 'www.google.ru':
      case 'www.google.com':
      case 'www.google.com.ua':
      case 'www.google.com.by':
      $word='q';
      $engine='google';
      break;
      
      case 'mail.ru':
      case 'go.mail.ru':
      $word='q';
      $engine='mail';
      break;
      
      case 'msn.com':
      case 'bing.com':
      $word='q';
      $engine='msn';
      break;
      
      default:
      $word='q';
      $engine='unknown';
      break;
    }
    $query=isset($parseUrl['query'])?$parseUrl['query']:'';
    parse_str($query,$output);
    $result=isset($output[$word])?trim(stripslashes($output[$word])):'';
    switch($engine)
    {
      // mail.ru encodes it's queries in Windows-1251
      case 'mail':
      $result=iconv('windows-1251','utf-8',$result);
      break;
      
      case 'rambler':
      // Double-decoded strings are sometimes sent by Rambler
      if(strpos($queryUrl,'&old_q=&'))
      {
        $result=iconv('utf-8','windows-1251',$result);
        $result=iconv('KOI8-R','utf-8',$result);
      }
      break;
      
      // Decode strange queries from Google images search
      case 'google':
      if(isset($output['prev']) && ($result=$output['prev']) && (0===strpos($result,'/images?')))
      {
        $imagesQuery=strstr($result,'q=');
        parse_str($imagesQuery,$queryVars);
        $result=isset($queryVars[$word])?trim(stripslashes($queryVars[$word])):'';
      }
      break;
    }
    return empty($result)?false:$result;
  }
  
  function processVisitor($session, $request)
  {
    global $database;
    $unique=isset($session["NewSession"]);
    //$unique=isset($session["phpcNewSession"]);
    $phpctime=phpctime();
    $date=date("Y-m-d",$phpctime);
    $minimalTime=$phpctime-PhpcSessionTimeout;
    $online=$database->getLinesCount("sessions","lastactivity>=$minimalTime");
    $database->addLine("statistics",array("date"=>$date));
    $fragment="hits=hits+1".($unique?",hosts=hosts+1":"");
    $query="UPDATE statistics SET $fragment,online=GREATEST(online,$online)";
    $database->customQueryBoolean("$query WHERE date='0000-00-00' LIMIT 1");
    $database->customQueryBoolean("$query WHERE date=".slashes($date)." LIMIT 1");
    if(!isset($_SERVER["HTTP_REFERER"])) return;
    $referer=strtolower(trim($_SERVER["HTTP_REFERER"]));
    $server=strtolower(trim($_SERVER["HTTP_HOST"]));
    $pattern="{^(?:\w+://)?(?:www\.)?([\w\-]+\.[\w\-\.]*\w)}i";
    if(!preg_match($pattern,$referer,$matches)) return;
    $referer=$matches[1];
    if($referer==$server || "www.$referer"==$server) return;
    $database->addLine("statreferers",array("address"=>$referer));
    $query="UPDATE statreferers SET counter=counter+1,lastclick=".slashes($phpctime).",lastipaddress=".slashes($_SERVER[REMOTE_ADDR])." WHERE address=".slashes($referer)." LIMIT 1";
    $database->customQueryBoolean($query);
    //$query="UPDATE sessions SET getstatpng=1 WHERE hash=".slashes($session["hash"])." LIMIT 1";
    //$database->customQueryBoolean($query);
  }
}

$statisticsSupport=new StatisticsSupport;

?>
