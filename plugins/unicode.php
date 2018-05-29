<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

// PHPC Online Plugin - Unicode Support v1.0 by Dagdamor

define("UnicodeLangFolder","/language/%s/");
define("UnicodeLangExtension1",".php");
define("UnicodeLangExtension2",".utf.php");

/****************************** Class Definition ******************************/

class UnicodeSupport
{
  function UnicodeSupport()
  {
    global $language, $fileSystem;
    if(!function_exists("mb_strlen")) fatalError("fatal_function","mb_strlen");
    if(defined("UnicodeSkipConvert")) return;
    $folder=format(UnicodeLangFolder,$language["locale"]);
    $files=$fileSystem->getFolder($folder,UnicodeLangExtension1);
    $extension1=preg_quote(UnicodeLangExtension1);
    $extension2=preg_quote(UnicodeLangExtension2);
    $patternFile="{($extension2|$extension1)\$}i";
    $patternLang1="{(\r\n\\\$language\[\"charset(?:_iconv|_sql|)\"\]=\")([^\"]+)(\";)}";
    $patternLang2="{(\r\n\\\$language\[\"charset_sql\"\]=\")([^\"]+)(\";)}";
    $alright=true;
    foreach($files as $filename) {
      $utfname=preg_replace($patternFile,UnicodeLangExtension2,$filename);
      if($utfname==$filename || in_array($utfname,$files)) continue;
      $charset=$language[isset($language["charset_saved"])?"charset_saved":"charset_iconv"];
      $content=$fileSystem->openFile($folder.$filename);
      $content=mb_convert_encoding($content,"UTF-8",strtoupper($charset));
      $content=preg_replace($patternLang1,"\\1utf-8\\3",$content);
      $content=preg_replace($patternLang2,"\\1utf8\\3",$content);
      $compare=mb_convert_encoding($content,"UTF-8","UTF-8");
      if($compare!==$content || !preg_match("{\?>\r?\n?\$}",$content)) continue;
      $fileSystem->saveFile($folder.$utfname,$content);
      $alright=false;
    }
    if($alright) return;
    $folder=getPhpcLocaleFolder($language["locale"]);
    foreach($folder as $filename) { require $filename; }
  }

  function internalCheckExtendedSupport()
  {
    if(defined("UnicodeDisableExtendedSupport")) return false;
    $version=phpversion();
    return $version>="5.1" || ($version<"5" && $version>="4.4");
  }

  function char($text, $index)
  {
    if($index<0) return "\x00";
    if($index>=mb_strlen($text,"UTF-8")) return "\x00";
    return mb_substr($text,$index,1,"UTF-8");
  }

  function strlen($text) { return mb_strlen($text,"UTF-8"); }
  function strtoupper($text, $html=false) { return phpcstrfix($text,mb_strtoupper($text,"UTF-8"),$html); }
  function strtolower($text, $html=false) { return phpcstrfix($text,mb_strtolower($text,"UTF-8"),$html); }

  function substr($text, $offset, $length=false)
  {
    if($length===false) $length=mb_strlen($text,"UTF-8");
    return (string)mb_substr($text,$offset,$length,"UTF-8");
  }

  function strncmp($text1, $text2, $length)
  {
    $text1=mb_substr($text1,0,$length,"UTF-8");
    $text2=mb_substr($text2,0,$length,"UTF-8");
    return strcmp($text1,$text2);
  }

  function strcasecmp($text1, $text2)
  {
    $text1=mb_strtolower($text1,"UTF-8");
    $text2=mb_strtolower($text2,"UTF-8");
    return strcmp($text1,$text2);
  }

  function strncasecmp($text1, $text2, $length)
  {
    $text1=mb_strtolower(mb_substr($text1,0,$length,"UTF-8"),"UTF-8");
    $text2=mb_strtolower(mb_substr($text2,0,$length,"UTF-8"),"UTF-8");
    return strcmp($text1,$text2);
  }

  function ucfirst($text, $html=false)
  {
    if(($text=(string)$text)==="") return "";
    $oldsymbol=mb_substr($text,0,1,"UTF-8");
    $newsymbol=mb_strtoupper($oldsymbol,"UTF-8");
    $result=substr_replace($text,$newsymbol,0,strlen($oldsymbol));
    return phpcstrfix($text,$result,$html);
  }

  function ucwords($text, $html=false)
  {
    global $language;
    if(($text=(string)$text)==="") return "";
    $extendedSupport=self::internalCheckExtendedSupport();
    $chars=$extendedSupport?"\pL":"[$language[charset_regexp]]";
    preg_match_all("{(?<!$chars)$chars}u",$text,$matches,PREG_OFFSET_CAPTURE);
    if(!count($matches)) return $text;
    $result=$text;
    for($index=count($matches[0])-1; $index>=0; $index--) {
      $oldsymbol=$matches[0][$index][0];
      $newsymbol=mb_strtoupper($oldsymbol,"UTF-8");
      $offset=$matches[0][$index][1];
      $result=substr_replace($result,$newsymbol,$offset,strlen($oldsymbol));
    }
    return phpcstrfix($text,$result,$html);
  }

  function chopText($text, $limit, $words=true)
  {
    global $language;
    if(mb_strlen($text,"UTF-8")<=$limit) return $text;
    $saved=$limit=max($limit-mb_strlen(PredefinedDots,"UTF-8"),0);
    if($words) {
      $extendedSupport=self::internalCheckExtendedSupport();
      $chars=$extendedSupport?"\pL":"[$language[charset_regexp]]";
      while($limit>0 && preg_match("{^$chars+\$}u",mb_substr($text,$limit-1,2,"UTF-8"))) $limit--;
      if(!$limit) $limit=$saved;
    }
    return rtrim(mb_substr($text,0,$limit,"UTF-8"),PredefinedChop).PredefinedDots;
  }

  function processPattern($pattern)
  {
    global $language;
    if(($pattern=(string)$pattern)==="") return $pattern;
    if($pattern[strlen($pattern)-1]=="u") return $pattern;
    if(strpos($pattern,"\x80-")!==false) return $pattern;
    if(preg_match("{u[a-zA-Z]+\$}",$pattern)) return $pattern;
    $pattern.="u";
    if(!self::internalCheckExtendedSupport()) return $pattern;
    $pattern=str_replace($language["charset_regexp"],"\p{L}",$pattern);
    $pattern=str_replace($language["charset_regexp_uppers"],"\p{Lu}",$pattern);
    $pattern=str_replace($language["charset_regexp_lowers"],"\p{Ll}",$pattern);
    return $pattern;
  }
}

$unicodeSupport=new UnicodeSupport;

?>
