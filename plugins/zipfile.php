<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

// PHPC Online Plugin - ZIP File Format v1.0 by Dagdamor

/****************************** Class Definition ******************************/

class ZipFile
{
  var $entries=array();

  function addFile($filename, $content)
  {
    $filename=str_replace("\\","/",$filename);
    $size=strlen($content);
    $crc=crc32($content);
    $data=gzcompress($content);
    $this->entries[]=compact("filename","size","crc","data");
  }

  function file()
  {
    $date=getdate();
    $stamp=($date["year"]-1980)<<25;
    $stamp+=$date["mon"]<<21;
    $stamp+=$date["mday"]<<16;
    $stamp+=$date["hours"]<<11;
    $stamp+=$date["minutes"]<<5;
    $stamp+=$date["seconds"]>>1;
    $dateline=chr($stamp&255);
    $dateline.=chr(($stamp>>8)&255);
    $dateline.=chr(($stamp>>16)&255);
    $dateline.=chr(($stamp>>24)&255);
    $result="";
    $offset=$length=0;
    foreach($this->entries as $index=>$entry) {
      $info=pack("VVV",$entry["crc"],strlen($entry["data"])-6,$entry["size"]);
      $block="\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00";
      $block.=$dateline.$info;
      $block.=pack("vv",strlen($entry["filename"]),0).$entry["filename"];
      $block.=substr(substr($entry["data"],0,strlen($entry["data"])-4),2).$info;
      $this->entries[$index]["offset"]=$offset;
      $result.=$block;
      $offset+=strlen($block);
    }
    foreach($this->entries as $entry) {
      $info=pack("VVV",$entry["crc"],strlen($entry["data"])-6,$entry["size"]);
      $block="\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00";
      $block.=$dateline.$info;
      $block.=pack("vVVVV",strlen($entry["filename"]),0,0,32,$entry["offset"]);
      $block.=$entry["filename"];
      $result.=$block;
      $length+=strlen($block);
    }
    $result.="\x50\x4b\x05\x06\x00\x00\x00\x00";
    $result.=pack("vv",count($this->entries),count($this->entries));
    $result.=pack("VVv",$length,$offset,0);
    return $result;
  }
}

?>
