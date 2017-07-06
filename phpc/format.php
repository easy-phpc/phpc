<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

class Formatter
{
  function getFormatting()
  {
    global $database;
    static $cache;
    if(!isset($cache)) {
      $cache=array("*"=>array(),""=>array());
      $rules=$database->getOrderedLines("formatting","useorder");
      $rules=extractArrayLines($rules,"pattern");
      foreach($rules as $pattern=>$rule) {
        $class=$rule["class"];
        $rule=selectArrayKeys($rule,"content,callback");
        $rule["callback"]=phpccallback($rule["callback"]);
        if(!isset($cache[$class])) $cache[$class]=array();
        $cache[$class][$pattern]=$rule;
        $cache["*"][$pattern]=$rule;
      }
    }
    return $cache;
  }

  function internalProcessFragment($text, $params)
  {
    if($text=="") return "";
    $smilies=ifset($params["smilies"]) && class_exists("SmiliesSupport");
    return $smilies?SmiliesSupport::processText($text):filterText($text);
  }

  function internalProcessParagraph($text, $formatting, $params)
  {
    if($text=="") return "";
    $typography=ifset($params["typo"]) && class_exists("TypographySupport");
    if($typography) $text=TypographySupport::processText($text);
    if(!count($formatting)) return $this->internalProcessFragment($text,$params);
    $pattern="{".implode("|",array_keys($formatting))."}is";
    preg_match_all($pattern,$text,$matches,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    $divider=strlen($text);
    for($index=count($matches)-1; $index>=0; $index--) {
      $start=$matches[$index][0][1];
      $stop=$start+strlen($matches[$index][0][0]);
      $source=substr($text,$stop,$divider-$stop);
      $replace=$this->internalProcessFragment($source,$params);
      $text=substr_replace($text,$replace,$stop,$divider-$stop);
      $source=substr($text,$start,$stop-$start);
      foreach($formatting as $pattern=>$rule) {
        $pattern="{{$pattern}}is";
        if(preg_match($pattern,$source)) break;
      }
      if($rule["callback"])
        $replace=preg_replace_callback($pattern,$rule["callback"],$source);
        else $replace=preg_replace($pattern,$rule["content"],filterText($source,true));
      $text=substr_replace($text,$replace,$start,$stop-$start);
      $divider=$start;
    }
    $source=substr($text,0,$divider);
    $replace=$this->internalProcessFragment($source,$params);
    $text=substr_replace($text,$replace,0,$divider);
    return $text;
  }

  function internalProcessText($text, $formatting, $params)
  {
    if($text=="") return "";
    if(!ifset($params["pars"])) return $this->internalProcessParagraph($text,$formatting,$params);
    $openTag=PredefinedParagraphOpen;
    $closeTag=PredefinedParagraphClose;
    if($params["pars"]!==true) {
      $values=explodeSmart(",",$params["pars"]);
      $openTag=ifset($values[0],"");
      $closeTag=ifset($values[1],"");
    }
    $paragraphs=explodeSmart("\r\n",$text);
    foreach($paragraphs as $index=>$paragraph) {
      $paragraph=$this->internalProcessParagraph($paragraph,$formatting,$params);
      $paragraphs[$index]=$openTag.$paragraph.$closeTag;
    }
    return implode("\r\n",$paragraphs);
  }

  function processTagUrl($matches)
  {
    $link=$matches[1]!=""?ltrim($matches[1],"="):$matches[2];
    $content=$matches[2]!=""?$matches[2]:$link;
    $regularLink=!defined("PhpcAdminPanel") && char($link,0)=="/" && strpos($link,".")===false;
    $adminScript=defined("PhpcAdminPanel") && preg_match("{^\w+\.php\b}",$link);
    $needPrefix=char($link,0)!="/" && (strpos($link,"://")===false || !preg_match("{^\w+://}",$link));
    if($needPrefix && !$adminScript) $link="http://$link";
    $target=!$regularLink && !$adminScript;
    $link=filterText($link,true);
    $content=filterText($content);
    return $target?
      format(PredefinedLinkTarget,array($link,"_blank",$content)):
      format(PredefinedLinkDefault,array($link,$content));
  }

  function processTagEmail($matches)
  {
    $link=$matches[1]!=""?ltrim($matches[1],"="):$matches[2];
    $content=$matches[2]!=""?$matches[2]:$link;
    $equal=$content==$link;
    $link="mailto:".antispamText(filterText($link,true),"url");
    $content=$equal?antispamText(filterText($content)):filterText($content);
    return format(PredefinedLinkDefault,array($link,$content));
  }

  function processTagCode($matches)
  {
    $code=htmlspecialchars(trim($matches[1]));
    $code=str_replace("\r\n",PredefinedNewline."\r\n",$code);
    return format(PredefinedCode,$code);
  }

  function process($text, $params=array())
  {
    $formatting=$this->getFormatting();
    return $this->internalProcessText($text,$formatting[""],$params);
  }

  function processClasses($text, $classes="*", $params=array())
  {
    $formatting=$this->getFormatting();
    if(!is_array($classes)) $classes=explodeSmart(",",$classes);
    $format=$formatting[""];
    foreach($classes as $class)
      if(isset($formatting[$class])) $format+=$formatting[$class];
    return $this->internalProcessText($text,$format,$params);
  }

  function parse($text)
  {
    $codesBlock="(?:url|email)";
    $codesPattern="{(\[$codesBlock(?:=[^\]]*)?.*?\[/$codesBlock\])}is";
    $headerBlock="\b(?:www\.|https?://|ftp://)";
    $trailerBlock="\w(?:[\w\-#%+./:;=?~]|&amp;|&(?!#?\w+;))*[\w/=]+";
    $emailBlock=str_replace("{^","",EmailAddressPattern);
    $emailBlock=str_replace("\$}","",$emailBlock);
    $linkPattern="{($headerBlock$trailerBlock|$emailBlock)}is";
    $emailPattern="{($emailBlock)}is";
    $split=preg_split($codesPattern,$text,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    foreach($split as $index=>$item) if(!preg_match($codesPattern,$item)) {
      $subsplit=preg_split($linkPattern,$item,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
      foreach($subsplit as $subindex=>$subitem)
        if(preg_match($linkPattern,$subitem)) {
          $code=preg_match($emailPattern,$subitem)?"email":"url";
          $subsplit[$subindex]="[$code]{$subitem}[/$code]";
        }
      $split[$index]=implode("",$subsplit);
    }
    return implode("",$split);
  }
}

?>
