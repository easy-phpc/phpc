<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

// PHPC Online Plugin - Syntax Colorer v1.0 by Dagdamor

/****************************** Class Definition ******************************/

class SyntaxColorer
{
  function getPhpcTemplateSyntax()
  {
    return array(
      array("pattern"=>"{<\?--.*?(?:--\?>|\$)}s","classes"=>"phpc_comment"),
      array("pattern"=>"{</?[A-Za-z_][\w\-]*:[\w\-:]*((?:\"(?:\\\\\\\\|\\\\\"|.)*?\"|.)*?)/?>}s",
        "classes"=>"phpc_tag,phpc_param","nested"=>array(
        array("pattern"=>"{\"(?:\\\\\\\\|\\\\\"|.)*?\"}s","classes"=>"phpc_value"))),
      array("pattern"=>"{<\?.*?\?>}s","classes"=>"html_default"),
      array("pattern"=>"{<!--.*?-->}s","classes"=>"html_comment"),
      array("pattern"=>"{<!.*?>}s","classes"=>"html_comment"),
      array("pattern"=>"{</?[A-Za-z_][\w\-]*(.*?)/?>}s",
        "classes"=>"html_tag,html_param","nested"=>array(
        array("pattern"=>"{\".*?\"|'.*?'}s","classes"=>"html_value"))));
  }

  function addTemplateSyntaxPart(&$content, $newpart)
  {
    $content["text"]=substr_replace($content["text"],"",$newpart["offset"],strlen($newpart["text"]));
    $newpart["nested"]=array();
    for($index=0; $index<count($content["nested"]); $index++) {
      $part=$content["nested"][$index];
      if($part["offset"]<=$newpart["offset"]) continue;
      if($part["offset"]>=$newpart["offset"]+strlen($newpart["text"])) continue;
      $part["offset"]-=$newpart["offset"];
      $newpart["nested"][]=$part;
      array_splice($content["nested"],$index--,1);
    }
    $position=false;
    for($index=0; $index<count($content["nested"]); $index++)
      if($content["nested"][$index]["offset"]>$newpart["offset"])
        { $position=$index; break; }
    if($position===false) $position=count($content["nested"]);
    for($index=$position; $index<count($content["nested"]); $index++)
      $content["nested"][$index]["offset"]-=strlen($newpart["text"]);
    array_splice($content["nested"],$position,0,array($newpart));
    return $position;
  }

  function analyseTemplateSyntax(&$content, $info)
  {
    $classes=explodeSmart(",",$info["classes"]);
    preg_match_all($info["pattern"],$content["text"],$matches,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    $divider=0;
    foreach($matches as $match) {
      $fragment=$match[0][0];
      $offset=$match[0][1]-$divider;
      $newpart=array("offset"=>$offset,"text"=>$fragment,"class"=>$classes[0]);
      $position=$this->addTemplateSyntaxPart($content,$newpart);
      foreach($match as $blockIndex=>$block) if($blockIndex) {
        $subcontent=&$content["nested"][$position];
        $newpart=array("offset"=>$block[1]-$match[0][1],"text"=>$block[0],"class"=>$classes[$blockIndex]);
        $subposition=$this->addTemplateSyntaxPart($subcontent,$newpart);
        if(isset($info["nested"])) foreach($info["nested"] as $subinfo)
          $this->analyseTemplateSyntax($subcontent["nested"][$subposition],$subinfo);
      }
      $divider+=strlen($fragment);
    }
  }

  function assembleTemplateSyntax($content)
  {
    $result="";
    $divider=0;
    for($index=0; $index<count($content["nested"]); $index++) {
      $part=$content["nested"][$index];
      $result.=
        htmlspecialchars(substr($content["text"],$divider,$part["offset"]-$divider)).
        $this->assembleTemplateSyntax($part);
      $divider=$part["offset"];
    }
    $result.=htmlspecialchars(substr($content["text"],$divider));
    return "<font class=\"$content[class]\">$result</font>";
  }

  function processPhpcTemplate($text, $spans=false)
  {
    if($text==="") return "";
    $result="";
    $syntax=$this->getPhpcTemplateSyntax();
    preg_match_all("{<\?php.*?\?>}s",$text,$matches,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
    $divider=0;
    foreach($matches as $match) {
      $start=$match[0][1];
      $stop=$start+strlen($match[0][0]);
      $content=array(
        "text"=>substr($text,$divider,$start-$divider),
        "class"=>"html_default","nested"=>array());
      foreach($syntax as $info) $this->analyseTemplateSyntax($content,$info);
      $result.=
        $this->assembleTemplateSyntax($content).
        $this->processPhpcBundle(substr($text,$start,$stop-$start));
      $divider=$stop;
    }
    $content=array(
      "text"=>substr($text,$divider),
      "class"=>"html_default","nested"=>array());
    foreach($syntax as $info) $this->analyseTemplateSyntax($content,$info);
    $result.=$this->assembleTemplateSyntax($content);
    $result=preg_replace("{<font class=\"[^\"]*?\"></font>}","",$result);
    if(!$spans) return $result;
    $result=str_replace("<font ","<span ",$result);
    $result=str_replace("</font>","</span>",$result);
    return $result;
  }

  function processPhpcBundle($text, $spans=false)
  {
    ini_set("highlight.html","php_html");
    ini_set("highlight.comment","php_comment");
    ini_set("highlight.keyword","php_keyword");
    ini_set("highlight.default","php_default");
    ini_set("highlight.string","php_string");
    $text=str_replace("<?php","<||||?",$text);
    $text=str_replace("<?","<|||?",$text);
    $text=str_replace("?>","?|||>",$text);
    $text=highlight_string("<?php\r\n$text?>",true);
    $text=str_replace("&lt;||||?","&lt;?php",$text);
    $text=str_replace("&lt;|||?","&lt;?",$text);
    $text=str_replace("?|||&gt;","?&gt;",$text);
    $text=str_replace("&nbsp;"," ",$text);
    $text=str_replace("\n","",$text);
    $text=str_replace("\r","\r\n",$text);
    $text=str_replace("<br />","",$text);
    $text=preg_replace("{<span style=\"color: ?(\w+)\">}","<font color=\"\\1\">",$text);
    $text=str_replace("</span>","</font>",$text);
    $text=preg_replace("{^<code><font color=\"php_html\">}","",$text);
    $text=preg_replace("{</font></code>\$}","",$text);
    $text=preg_replace("{^<font class=\"php_keyword\">&lt;\?</font><font class=\"php_default\">php}","<font class=\"php_default\">&lt;?php",$text);
    $text=preg_replace("{^<font color=\"php_default\">&lt;\?php\r\n}","<font color=\"php_default\">",$text);
    $text=preg_replace("{\?&gt;</font>\$}","</font>",$text);
    $text=preg_replace("{<font color=\"[^\"]*?\"></font>}","",$text);
    $text=str_replace("<font color=\"","<font class=\"",$text);
    if(!$spans) return $text;
    $text=str_replace("<font ","<span ",$text);
    $text=str_replace("</font>","</span>",$text);
    return $text;
  }
}

$syntaxColorer=new SyntaxColorer;

?>
