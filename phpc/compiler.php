<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

class Compiler
{
  var $style;
  var $session;
  var $updated;
  var $page;
  var $template;
  var $bundles;
  var $plugins;
  var $stack;

  function Compiler()
  {
    $this->style=array();
    $this->session=array();
    $this->updated=array();
    $this->page=array("name"=>"");
    $this->template="";
    $this->bundles=array();
    $this->plugins=array();
    $this->stack=array();
  }

  function getPreloadPlugins()
  {
    global $fileSystem;
    $result=explodeSmart(",",PhpcPreloadPlugins);
    foreach($result as $index=>$plugin) {
      $result[$index]=format(CompilerPluginFilename,$plugin);
      $fileSystem->normalize($result[$index]);
    }
    return $result;
  }

  function getInstalledPlugins()
  {
    global $fileSystem;
    static $cache;
    if(!isset($cache)) {
      $extension=$fileSystem->getFileExtension(CompilerPluginFilename);
      $folder=dirname(CompilerPluginFilename)."/";
      $cache=$fileSystem->getFolder($folder,$extension);
      foreach($cache as $index=>$filename)
        $cache[$index]=basename($filename,$extension);
    }
    return $cache;
  }

  function isPluginInstalled($plugin)
  {
    $plugins=$this->getInstalledPlugins();
    return in_array($plugin,$plugins);
  }

  function getStyles()
  {
    global $database;
    static $cache;
    if(!isset($cache))
      $cache=arrangeArraysSet($database->getOrderedLines("styles","folder,id"));
    return $cache;
  }

  function getPages()
  {
    global $database;
    static $cache;
    if(!isset($cache)) {
      $cache=array();
      $pages=$database->getLines("pages");
      foreach($pages as $page) {
        $bundles=explodeSmart(",",$page["bundles"]);
        $bundlekeys=explodeSmart(",",phpcstrtolower($page["bundles"]));
        $page["aliaslower"]=phpcstrtolower($page["alias"]);
        $page["bundles"]=combineArrays($bundlekeys,$bundles);
        $page["params"]=explodeAssigns(",",$page["params"]);
        localizeField($page,"title");
        $cache[phpcstrtolower($page["name"])]=arrangeArrayValues($page);
      }
    }
    return $cache;
  }

  function getInheritanceChain($subject)
  {
    global $database;
    static $cache=array();
    if(!isset($cache[$subject])) {
      $chain=array();
      $setid=$this->style["{$subject}setid"];
      $sets=$database->getLines("{$subject}sets");
      $sets=extractArrayColumns($sets,"id","parentid");
      while($setid) {
        if(!isset($sets[$setid])) fatalError("fatal_constraints");
        if(in_array($setid,$chain)) fatalError("fatal_recursion","{$subject}sets");
        $chain[]=$setid;
        $setid=$sets[$setid];
      }
      if(!count($chain)) fatalError("fatal_constraints");
      $cache[$subject]=$chain;
    }
    return $cache[$subject];
  }

  function getReplacements()
  {
    global $database;
    static $cache;
    if(!isset($cache)) {
      $cache=array();
      $chain=$this->getInheritanceChain("replacement");
      foreach($chain as $setid) {
        $set=$database->getLines("replacements","setid=$setid");
        foreach($set as $item) {
          $name=phpcstrtolower($item["name"]);
          if(!isset($cache[$name])) $cache[$name]=$item["content"];
        }
        ksort($cache);
      }
    }
    return $cache;
  }

  function getTemplateCallback($matches)
  {
    $result=preg_replace("{^//.*\$}m","",$matches[0]);
    $result=preg_replace("{(?<=\n)[\r\n]+}s","",$result);
    if(strpos($result,"\$")===false) return $result;
    $pattern="{(?<![\\\\\\\$])\\\$(?!_phpc_)(\w+)=(?![=>&])}";
    preg_match_all($pattern,$result,$matches);
    if(!count($matches[1])) return $result;
    $names=implode(",",array_map("quoteText",$matches[1]));
    $replace="phpcStackMerge(\$_phpc_scope,compact($names));";
    return preg_replace("{(?=\?>\$)}",$replace,$result);
  }

  function getTemplate($name, $used=array())
  {
    global $language, $database;
    static $cache=array();
    $namelower=phpcstrtolower($name);
    if(!isset($cache[$namelower])) {
      $cache[$namelower]=false;
      $replacements=$this->getReplacements();
      $chain=$this->getInheritanceChain("template");
      $conditions=$database->getListCondition("setid",$chain);
      $conditions="name=".slashes($name)." AND $conditions";
      $templates=$database->getLines("templates",$conditions);
      foreach($chain as $setid) {
        $template=searchArrayLine($templates,"setid",$setid);
        if(!$template) continue;
        $content=$template["content"];
        $patternLines="{(?<=\n)[\r\n]+}s";
        $patternComment="{<\?--.*?(--\?>\r?\n?|\$)}s";
        $patternCode1="{\s*\?>\r?\n?<\?php\s+}s";
        $patternCode2="{<\?php.*?\?>}s";
        $callback=array("Compiler","getTemplateCallback");
        $content=preg_replace($patternLines,"",$content);
        $content=preg_replace($patternComment,"",$content);
        $content=preg_replace($patternCode1,"\r\n",$content);
        $content=preg_replace_callback($patternCode2,$callback,$content);
        foreach($replacements as $search=>$replace) {
          $pattern=preg_quote($search);
          $replace=str_replace("\\","\\\\",$replace);
          $replace=str_replace("\$","\\\$",$replace);
          $content=preg_replace("{{$pattern}}i",$replace,$content);
        }
        if(CompilerCacheTemplateNames)
          $content="<!--$name-->".trim($content)."<!--/$name-->";
        $template["content"]=$content;
        $content=preg_replace($patternCode2,"",$content);
        $patternError="{.*(<\?|\?>).*}";
        if(preg_match($patternError,$content,$matches))
          fatalError("fatal_compile",array(),format($language["fatal_reason_phptag"],array($name,trim($matches[0]))));
        if($template["parent"]!="") {
          if(in_array($namelower,$used)) fatalError("fatal_recursion","templates");
          $used[]=$namelower;
          $parents=$this->getTemplate($template["parent"],$used);
          if(!$parents) fatalError("fatal_compile",array(),format($language["fatal_reason_notemplateparent"],array($template["parent"],$name)));
          $parents[]=$template;
          $cache[$namelower]=$parents;
        }
        else $cache[$namelower]=array($template);
        break;
      }
    }
    return $cache[$namelower];
  }

  function getBundle($name)
  {
    global $database;
    static $cache=array();
    $namelower=phpcstrtolower($name);
    if(!isset($cache[$namelower])) {
      $cache[$namelower]=false;
      $chain=$this->getInheritanceChain("bundle");
      $conditions=$database->getListCondition("setid",$chain);
      $conditions="name=".slashes($name)." AND $conditions";
      $bundles=$database->getLines("bundles",$conditions);
      foreach($chain as $setid) {
        $bundle=searchArrayLine($bundles,"setid",$setid);
        if(!$bundle) continue;
        $plugins=explodeSmart(",",$bundle["plugins"]);
        $pluginkeys=explodeSmart(",",phpcstrtolower($bundle["plugins"]));
        $bundle["plugins"]=combineArrays($pluginkeys,$plugins);
        $cache[$namelower]=$bundle;
        break;
      }
    }
    return $cache[$namelower];
  }

  function getLinkStyles()
  {
    global $database;
    static $cache;
    if(!isset($cache)) {
      $cache=$database->getOrderedLines("linkstyles","useorder");
      foreach($cache as $index=>$rule) {
        $rule["assign"]=explodeAssigns(",",$rule["assign"]);
        preg_match_all("{\\\$(\w+)}",$rule["pattern"],$matches);
        $rule["params"]=array_merge($matches[1],array_keys($rule["assign"]));
        $cache[$index]=arrangeArrayValues($rule);
      }
    }
    return $cache;
  }

  function getRelations()
  {
    global $database;
    static $cache;
    if(!isset($cache)) {
      $cache=$database->getOrderedLines("relations","id");
      foreach($cache as $index=>$relation) {
        $callback=phpccallback($relation["callback"]);
        if(!$callback) { unset($cache[$index]); continue; }
        $cache[$index]["callback"]=$callback;
      }
    }
    return $cache;
  }

  function getRequest($cleanup=false)
  {
    $result=stripSlashesSmart($_SERVER["REQUEST_URI"]);
    $position=strpos($result,"?");
    if($position!==false) $result=substr($result,0,$position);
    $result=phpcurldecode($result,true,true);
    if(!$cleanup) return $result;
    $root=defined("PhpcRoot")?PhpcRoot:"/";
    $match=$root!="" && substr($result,0,strlen($root))==$root;
    if($match) $result=substr($result,strlen($root));
    $suffix=defined("PhpcSuffix")?PhpcSuffix:"";
    $match=$suffix!="" && substr($result,-strlen($suffix))==$suffix;
    if($match) $result=substr($result,0,strlen($result)-strlen($suffix));
    return trim($result,"/");
  }

  function processGlobalCache()
  {
    global $language, $database, $settings;
    $prefix=defined("DatabasePrefix")?DatabasePrefix:"";
    $cache=$database->getField($prefix."settings","value","id=1");
    $settings=$cache?unserialize($cache):array();
    if(PhpcLocale!="") return;
    $cache=$database->getLine($prefix."messages","id=1");
    if($cache) $cache=getLocalizedField($cache,"content");
    if($cache) $cache=unserialize($cache);
    if($cache) foreach($cache as $key=>$value) $language[$key]=$value;
  }

  function processStyle(&$request)
  {
    global $usersSupport;
    $styles=$this->getStyles();
    $styleid=isset($_COOKIE[PhpcStyleCookie])?(int)$_COOKIE[PhpcStyleCookie]:0;
    if(class_exists("UsersSupport")) $usersSupport->processStyle($styleid);
    $bestWeight=-1;
    foreach($styles as $style) {
      if(!$style["visible"] && !isAdministrator()) continue;
      $hostMatch=$style["host"]==$_SERVER["HTTP_HOST"];
      $folder=$style["folder"]."/";
      $folderMatch=substr("$request/",0,strlen($folder))==$folder;
      if($style["host"]!="" && !$hostMatch) continue;
      if($style["folder"]!="" && !$folderMatch) continue;
      $weight=0;
      if($style["forusers"]) $weight+=1;
      if($style["id"]==$styleid) $weight+=2;
      if($folderMatch) $weight+=4;
      if($hostMatch) $weight+=8;
      if($weight>=$bestWeight) { $result=$style; $bestWeight=$weight; }
    }
    if($bestWeight<0) fatalError("fatal_nostyle");
    $cutoff=strlen($result["folder"]);
    if($cutoff) $request=(string)substr($request,$cutoff+1);
    return $result;
  }

  function updateStyle($styleid)
  {
    phpcsetcookie(PhpcStyleCookie,$styleid,true);
  }

  function processSessionCleanup()
  {
    global $database, $usersSupport;
    if(!PhpcSessionEnabled || random(PhpcSessionGCDivisor)>=PhpcSessionGCProbability) return;
    $minimalTime=phpctime()-PhpcSessionTimeout;
    if(PhpcSessionCleanup && class_exists("UsersSupport")) {
      $sessions=$database->getLines("sessions","lastactivity<$minimalTime");
      if(count($sessions)) {
        $usersSupport->processSessionCleanup($sessions);
        $hashes=extractArrayColumn($sessions,"hash");
        $conditions=$database->getListCondition("hash",$hashes,true);
        $database->deleteLines("sessions",$conditions);
      }
    }
    else $database->deleteLines("sessions","lastactivity<$minimalTime");
  }

  function processSession(&$request)
  {
    global $database;
    if(!PhpcSessionEnabled) return array();
    $hash=preg_match("{^([\da-f]{32})(/|\$)}",$request,$matches)?$matches[1]:"";
    if($hash!="") $request=(string)substr($request,33);
    if(!PhpcSessionUseURLs) $hash="";
    if($hash=="") {
      $cookiehash=isset($_COOKIE[PhpcSessionCookie]) && is_string($_COOKIE[PhpcSessionCookie]);
      $cookiehash=($cookiehash && PhpcSessionUseCookies)?trim(stripSlashesSmart($_COOKIE[PhpcSessionCookie])):"";
      if($cookiehash!="" && preg_match("{^[\da-f]{32}\$}",$cookiehash)) $hash=$cookiehash;
    }
    $ipaddress=getClientAddress();
    $minimalTime=phpctime()-PhpcSessionTimeout;
    $conditions="ipaddress=".slashes($ipaddress)." AND lastactivity>=$minimalTime";
    $sessions=$database->getOrderedLinesRange("sessions","lastactivity DESC",0,PhpcSessionCatchLimit,$conditions);
    $session=searchArrayLine($sessions,"hash",$hash);
    if(!$session && PhpcSessionCatchEnabled) {
      $restrictions=explodeSmart(",",PhpcSessionCatchRestrictions);
      foreach($sessions as $catch) {
        $success=true;
        foreach($restrictions as $field) if(ifset($catch[$field])) { $success=false; break; }
        if($success) { $session=$catch; break; }
      }
    }
    if(!$session) {
      $hash=phpchash(EncodingPrefix.uniqid(random(),true),32);
      $lastactivity=phpctime();
      $session=compact("hash","ipaddress","lastactivity");
      $database->addLine("sessions",$session);
      $session=$database->getLine("sessions","hash=".slashes($session["hash"]));
      if(!$session) return array();
      $session["phpcNewSession"]=true;
    }
    if(PhpcSessionUseCookies) phpcsetcookie(PhpcSessionCookie,$session["hash"]);
    $propagate=(!PhpcSessionUseCookies || !count($_COOKIE)) && PhpcSessionUseURLs;
    if($propagate) $session["propagate"]=$session["hash"];
    return $session;
  }

  function processSessionUpdate()
  {
    global $usersSupport;
    if(!isset($this->session["hash"])) return;
    $values=array();
    $pattern="{^[A-Za-z_\d\-/]{1,50}\$}";
    foreach($_GET as $param=>$value) {
      if(!is_string($param) || !is_string($value)) continue;
      $value=acceptStringParameter($param);
      if(!preg_match($pattern,$param) || !preg_match($pattern,$value)) continue;
      $values[$param]=$value;
    }
    $values=array_merge($values,$this->page["params"]);
    ksort($values);
    while(phpcstrlen(implodeAssigns(",",$values))>PhpcSessionParamsLimit) array_pop($values);
    $values=array(
      "lastactivity"=>phpctime(),
      "pageid"=>$this->page["id"],
      "params"=>implodeAssigns(",",$values));
    if(class_exists("UsersSupport"))
      $usersSupport->processSessionUpdate($this->session,$values);
    $this->updateSession($this->session,$values);
  }

  function updateSession(&$session, $values)
  {
    if(!isset($this->session["hash"])) return;
    $session=array_merge($session,$values);
    $this->session=array_merge($this->session,$values);
    $this->updated=array_merge($this->updated,$values);
  }

  function processRequest(&$request)
  {
    if(phpcstrtolower($request)==CompilerIndexPage) return 0;
    $linkStyles=$this->getLinkStyles();
    foreach($linkStyles as $rule) {
      $symbols=CompilerRequestSymbols;
      $pattern=preg_quote($rule["pattern"]);
      $pattern=preg_replace("{\\\{\\\\\\\$\w+\\\}}","([$symbols/]+)",$pattern);
      $pattern=preg_replace("{\\\\\\\$\w+}","([$symbols]+)",$pattern);
      $pattern=phpcpattern("{^$pattern\$}i");
      if(!preg_match($pattern,$request,$matches)) continue;
      $values=array_slice($matches,1);
      preg_match_all("{\\\$(\w+)}",$rule["pattern"],$matches);
      $params=combineArrays($matches[1],$values);
      foreach($params as $param=>$value) sendParameter($param,$value);
      foreach($rule["assign"] as $param=>$value) sendParameter($param,$value);
      return $rule["pageid"];
    }
    return 0;
  }

  function processPage(&$request)
  {
    if($request=="") $request=CompilerIndexPage;
    $requestlower=phpcstrtolower($request);
    $pages=$this->getPages();
    $page=searchArrayLine($pages,"aliaslower",$requestlower);
    if(!$page && isset($pages[$requestlower]) &&
      $pages[$requestlower]["alias"]=="") $page=$pages[$requestlower];
    $pageid=$page?0:$this->processRequest($request);
    if($pageid) {
      $page=searchArrayLine($pages,"id",$pageid);
      if(!$page) fatalError("fatal_constraints");
    }
    if(!$page || !$page["visible"]) {
      if(!isset($pages[CompilerErrorPage])) fatalError("fatal_no404");
      $page=$pages[CompilerErrorPage];
    }
    $page=array($page);
    $chain=array($page[0]["id"]);
    while($page[0]["parentid"]) {
      $parent=searchArrayLine($pages,"id",$page[0]["parentid"]);
      if(!$parent || in_array($parent["id"],$chain)) fatalError("fatal_constraints");
      array_unshift($page,$parent);
      $chain[]=$parent["id"];
    }
    $result=$page[0];
    $complexInherit=explodeSmart(",",CompilerComplexInherit);
    foreach($page as $index=>$item) if($index)
      foreach($item as $key=>$value) if(in_array($key,$complexInherit))
        $result[$key]=array_merge($result[$key],$value);
        else if($value!="") $result[$key]=$value;
    $result["bundles"]=array_values($result["bundles"]);
    $result["request"]=$request;
    $result["link"]=$this->createLink($result["name"]);
    return $result;
  }

  function processRelations($master, $operation, $id)
  {
    if(!$id) return;
    $relations=$this->getRelations();
    foreach($relations as $relation)
      if($relation["master"]==$master && $relation["operation"]==$operation)
        call_user_func($relation["callback"],$id);
  }

  function internalCreateLink($page, $params=array())
  {
    $params=array_map("phpcurlencode",$params);
    $params=count($params)?"?".implodeAssigns("&",$params):"";
    if(strpos($page,"://")!==false) return $page.$params;
    $root=defined("PhpcRoot")?PhpcRoot:"/";
    $suffix=defined("PhpcSuffix")?PhpcSuffix:"";
    $folder=ifset($this->style["folder"],"");
    if(defined("PhpcPreferredFolder")) $folder=PhpcPreferredFolder;
    $session=ifset($this->session["propagate"],"");
    $result=$page!=CompilerIndexPage?$page:"";
    $glue=$result!="" && $session!=""?"/":"";
    $result=$session.$glue.$result;
    $glue=$result!="" && $folder!=""?"/":"";
    $result=$root.$folder.$glue.$result;
    if(substr($result,0,2)=="//") $result="/".ltrim($result,"/");
    if(strpos($result,".")!==false || substr($result,-1)=="/") $suffix="";
    return phpcurlencode($result.$suffix,true).$params;
  }

  function createLink($page, $params=array())
  {
    if($page=="") return "";
    if($page=="/") $page=CompilerIndexPage;
    $pagelower=phpcstrtolower($page);
    $pages=$this->getPages();
    if(!isset($pages[$pagelower]))
      return $this->internalCreateLink($page,$params);
    $page=$pages[$pagelower];
    if($page["name"]==CompilerIndexPage && $page["alias"]==""
      && !count($params)) return $this->internalCreateLink(CompilerIndexPage);
    $result=$page["alias"]!=""?$page["alias"]:$page["name"];
    $linkStyles=$this->getLinkStyles();
    $paramNames=array_keys($params);
    foreach($linkStyles as $rule) if($rule["pageid"]==$page["id"]) {
      if(count(array_diff($rule["params"],$paramNames))) continue;
      $paramsCopy=$params;
      $success=true;
      foreach($rule["assign"] as $param=>$value) {
        if($paramsCopy[$param]!=$value) { $success=false; break; }
        unset($paramsCopy[$param]);
      }
      if(!$success) continue;
      $result=phpcurlencode($rule["pattern"]);
      $result=str_replace(array("%24","%7B","%7D"),array("\$","{","}"),$result);
      foreach($paramsCopy as $param=>$value) {
        if(!in_array($param,$rule["params"])) continue;
        $pattern="{\\{\\\$$param\}|\\\$$param\b}";
        $result=preg_replace($pattern,phpcurlencode($value),$result);
        unset($paramsCopy[$param]);
      }
      $result=phpcurldecode($result);
      $params=$paramsCopy;
      break;
    }
    return $this->internalCreateLink($result,$params);
  }

  function createLinkBack($default=false)
  {
    $host=$_SERVER["HTTP_HOST"];
    $referer=stripSlashesSmart(ifset($_SERVER["HTTP_REFERER"],""));
    $pattern="{^https?://([^/]+)(/.*)\$}";
    $success=preg_match($pattern,$referer,$matches) && $matches[1]==$host;
    if($success) return $matches[2];
    return $default!==false?$default:$this->createLink("/");
  }

  function prepare()
  {
    global $fileSystem, $optimizer;
    $request=$this->getRequest(true);
    $this->processSessionCleanup();
    $this->style=$this->processStyle($request);
    $this->session=$this->processSession($request);
    $this->page=$this->processPage($request);
    $this->processSessionUpdate();
    $styleid=$this->style["id"];
    $bundles=$this->page["bundles"];
    $this->template=$this->page["template"];
    $this->bundles=$optimizer->getBundles($styleid,$bundles);
    if(!$this->bundles) $this->bundles=
      $optimizer->addBundles($styleid,$bundles,$this->compileBundles($bundles));
    $this->plugins=$optimizer->getBundlesPlugins($this->bundles);
    foreach($this->plugins as $index=>$name) {
      $filename=format(CompilerPluginFilename,$name);
      $fileSystem->normalize($filename);
      $this->plugins[$index]=$filename;
    }
    if(class_exists("StatisticsSupport"))
      StatisticsSupport::processVisitor($this->session,$request);
  }

  function shutdown()
  {
    global $database;
    if(!isset($this->session["hash"]) || !count($this->updated)) return;
    $conditions="hash=".slashes($this->session["hash"]);
    $database->modifyLine("sessions",$this->updated,$conditions);
    $this->updated=array();
  }

  function processTemplateAreas($template)
  {
    global $language;
    $areaPattern="{(</?area:.*?>)\r?\n?}s";
    $areaPatternStrict="{^<(/?)area:([\w\-]+)\s*(/?)>\$}s";
    foreach($template as $itemIndex=>$item) {
      $split=preg_split($areaPattern,$item["content"],-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
      foreach($split as $index=>$part) if(preg_match($areaPattern,$part)) {
        if(!preg_match($areaPatternStrict,$part,$matches))
          fatalError("fatal_compile",array(),format($language["fatal_reason_area"],array($item["name"],$part)));
        if($matches[1]!="" && $matches[3]!="")
          fatalError("fatal_compile",array(),format($language["fatal_reason_area"],array($item["name"],$part)));
        $split[$index]=array(
          "name"=>$matches[2],
          "opening"=>$matches[1]=="",
          "closing"=>$matches[1]!="" || $matches[3]!="",
          "content"=>$part);
      }
      $used=array();
      $nesting=array();
      foreach($split as $index=>$part) if(is_array($part)) {
        $name=$part["name"];
        if($part["opening"]) {
          if(in_array($name,$used))
            fatalError("fatal_compile",array(),format($language["fatal_reason_areaalready"],array($item["name"],$part["content"])));
          $used[]=$name;
          $nesting[]=compact("name","index");
        }
        if($part["closing"]) {
          $match=array_pop($nesting);
          if(!$match || $match["name"]!=$name)
            fatalError("fatal_compile",array(),format($language["fatal_reason_areamissing"],array($item["name"],$part["content"])));
          $split[$match["index"]]["delta"]=$index-$match["index"];
        }
      }
      foreach($split as $part)
        if(is_array($part) && $part["opening"] && !isset($part["delta"]))
          fatalError("fatal_compile",array(),format($language["fatal_reason_areaunclosed"],array($item["name"],$part["content"])));
      $template[$itemIndex]=array("id"=>$item["id"],"name"=>$item["name"],"content"=>$split);
    }
    $result=$template[0];
    foreach($template as $itemIndex=>$item) if($itemIndex) {
      $result["id"]=$item["id"];
      $result["name"]=$item["name"].".".$result["name"];
      $index=0;
      while($index<count($item["content"])) {
        $part=$item["content"][$index];
        if(!is_array($part)) { $index++; continue; }
        $position=false;
        foreach($result["content"] as $fragmentIndex=>$fragment)
          if(is_array($fragment) && $fragment["name"]==$part["name"])
            { $position=$fragmentIndex; break; }
        if($position===false) fatalError("fatal_compile",array(),
          format($language["fatal_reason_areanoparent"],array($item["name"],$part["content"])));
        $replacement=array_slice($item["content"],$index,$part["delta"]+1);
        array_splice($result["content"],$position,$fragment["delta"]+1,$replacement);
        $index+=$part["delta"]+1;
      }
    }
    foreach($result["content"] as $itemIndex=>$item)
      if(is_array($item)) unset($result["content"][$itemIndex]);
    $result["content"]=implode("",$result["content"]);
    $globals=array("language"=>true,"settings"=>true,"compiler"=>true);
    preg_match_all("{<\?php.*?\?>}s",$result["content"],$matches);
    $fragments=$matches[0];
    foreach($fragments as $fragment) {
      $pattern="{(?<![\\\\\\\$])\\\$(\w+)->}";
      preg_match_all($pattern,$fragment,$matches);
      foreach($matches[1] as $global) $globals[$global]=true;
    }
    $result["globals"]=array_keys($globals);
    return $result;
  }

  function processTemplateTags($template)
  {
    global $language;
    $tagStringBlock="\"(?:\\\\\\\\|\\\\\"|.)*?\"";
    $tagParamsBlock="[A-Za-z_]\w*(?:=[A-Za-z_]\w*(?::\w+)*|=$tagStringBlock)?";
    $tagPattern="{(<\?php.*?\?>|</?(?:insert|logic):(?:$tagStringBlock|.)*?>)\r?\n?}s";
    $tagPatternStrict="{^<(/?)(insert|logic):([\w\-]+)\s*((?:$tagParamsBlock\s*)*)(/?)>\$}s";
    $tagParamsPattern="{(\w+)(?:=(\w+(?::\w+)*)|=($tagStringBlock))?}s";
    $result=preg_split($tagPattern,$template["content"],-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    foreach($result as $index=>$part) if(preg_match($tagPattern,$part)) {
      if(substr($part,0,5)=="<?php") continue;
      if(!preg_match($tagPatternStrict,$part,$matches))
        fatalError("fatal_compile",array(),format($language["fatal_reason_tag"],array($template["name"],$part)));
      if($matches[1]!="" && $matches[5]!="")
        fatalError("fatal_compile",array(),format($language["fatal_reason_tag"],array($template["name"],$part)));
      $returns=$this->getLogicTagReturns();
      $tagInfo=array(
        "type"=>$matches[2],
        "name"=>$matches[3],
        "typename"=>$matches[2].":".$matches[3],
        "opening"=>$matches[1]=="",
        "closing"=>$matches[1]!="" || $matches[5]!="",
        "params"=>array(),
        "returns"=>ifset($returns[$matches[3]],array()),
        "content"=>$part);
      preg_match_all($tagParamsPattern,$matches[4],$matches);
      $tagInfo["params"]=array_slice($matches,1);
      if(!$tagInfo["opening"] && count($tagInfo["params"][0]))
        fatalError("fatal_compile",array(),format($language["fatal_reason_tag"],array($template["name"],$part)));
      if($tagInfo["type"]=="logic" &&
        !in_array($tagInfo["name"],$this->getLogicTags()))
          fatalError("fatal_compile",array(),format($language["fatal_reason_unknownlogic"],array($template["name"],$part)));
      $result[$index]=$tagInfo;
    }
    $nesting=array();
    foreach($result as $index=>$part) if(is_array($part)) {
      $typename=$part["typename"];
      if($part["opening"]) $nesting[]=compact("typename","index");
      if($part["closing"]) {
        $match=array_pop($nesting);
        if(!$match || $match["typename"]!=$typename)
          fatalError("fatal_compile",array(),format($language["fatal_reason_tagmissing"],array($template["name"],$part["content"])));
        $result[$match["index"]]["delta"]=$index-$match["index"];
      }
    }
    foreach($result as $part)
      if(is_array($part) && $part["opening"] && !isset($part["delta"]))
        fatalError("fatal_compile",array(),format($language["fatal_reason_tagunclosed"],array($template["name"],$part["content"])));
    return $result;
  }

  function processTemplateContents($fragment)
  {
    $contentPattern="{(<var:content\s*>)\r?\n?}s";
    $result=preg_split($contentPattern,$fragment,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    foreach($result as $index=>$part)
      if(preg_match($contentPattern,$part)) $result[$index]=array();
    return $result;
  }

  function processTemplateVars($name, $fragment)
  {
    global $language;
    $tagStringBlock="\"(?:\\\\\\\\|\\\\\"|.)*?\"";
    $tagParamsBlock="[A-Za-z_]\w*(?:=[A-Za-z_]\w*(?::\w+)*|=$tagStringBlock)?";
    $tagPattern="{(<\?php.*?\?>|<(?:var|const|write):(?:$tagStringBlock|.)*?>)}s";
    $tagPatternStrict="{^<(var|const|write):(\w+(?::\w+)*)\s*((?:$tagParamsBlock\s*)*)>\$}s";
    $tagParamsPattern="{(\w+)(?:=(\w+(?::\w+)*)|=($tagStringBlock))?}s";
    $result=preg_split($tagPattern,$fragment,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    foreach($result as $index=>$part) if(preg_match($tagPattern,$part)) {
      if(substr($part,0,5)=="<?php") continue;
      if(!preg_match($tagPatternStrict,$part,$matches))
        fatalError("fatal_compile",array(),format($language["fatal_reason_tag"],array($name,$part)));
      $tagInfo=array(
        "type"=>$matches[1],
        "name"=>$matches[2],
        "params"=>array(),
        "content"=>$part);
      preg_match_all($tagParamsPattern,$matches[3],$matches);
      $tagInfo["params"]=array_slice($matches,1);
      if($tagInfo["type"]=="write" &&
        !in_array($tagInfo["name"],$this->getWriteTags()))
          fatalError("fatal_compile",array(),format($language["fatal_reason_unknownwrite"],array($name,$part)));
      $result[$index]=$tagInfo;
    }
    return $result;
  }

  function compileTemplateFragment($fragment)
  {
    if($fragment=="") return "";
    $result="?>\r\n$fragment<?php\r\n";
    $useecho=phpcstrlen($fragment)<=CompilerEchoLimit && strpos($fragment,"<?")===false;
    if($useecho) $result="echo".quoteText($fragment).";";
    return $result;
  }

  function compileTemplateParams($params)
  {
    $prefix="\$_phpc_args=array();";
    $result="";
    $shortParams=$actual=array();
    $shortValid=true;
    foreach($params[0] as $paramIndex=>$param) {
      if($param=="currentScope") {
        $prefix="\$_phpc_args=phpcStackEnd(\$_phpc_scope);";
        $shortValid=false;
        continue;
      }
      unset($actual[$param]);
      $paramQuoted=quoteText($param);
      $valueRef=$params[1][$paramIndex];
      $valueStr=$params[2][$paramIndex];
      if($valueRef!="") {
        $parts=explode(":",$valueRef);
        $subject="\$".array_shift($parts);
        foreach($parts as $part)
          $subject.="[".(isTrueInteger($part)?$part:quoteText($part))."]";
        $result.="if(isset($subject))\$_phpc_args[$paramQuoted]=$subject;";
        $shortValid=false;
      }
      else {
        $subject=$valueStr!=""?$valueStr:"true";
        $actual[$param]=$valueStr!=""?unquoteText($valueStr):true;
        $number=(int)unquoteText($subject);
        if(quoteText($number)==$subject) $subject=$actual[$param]=$number;
        $result.="\$_phpc_args[$paramQuoted]=$subject;";
        $shortParams[]="$paramQuoted=>$subject";
      }
    }
    $result=$prefix.$result; $param="\$_phpc_args";
    if($shortValid) $result="$param=array(".implode(",",$shortParams).");";
    $simple=preg_match("{^\\\$_phpc_args=(array|phpcStackEnd)\([^\)]*\);\$}",$result);
    if($simple) { $param=rtrim(substr($result,12),";"); $result=""; }
    return compact("result","param","actual");
  }

  function compileTemplateVars($name, $fragment)
  {
    global $language;
    $result="";
    $fragments=$this->processTemplateVars($name,$fragment);
    foreach($fragments as $fragment) if(is_array($fragment)) {
      if($fragment["type"]=="var" || $fragment["type"]=="const") {
        $const=$fragment["type"]=="const";
        $parts=explode(":",$fragment["name"]);
        if($const && count($parts)>1)
          fatalError("fatal_compile",array(),format($language["fatal_reason_tag"],array($name,$fragment["content"])));
        $subject=($const?"":"\$").array_shift($parts);
        foreach($parts as $part)
          $subject.="[".(isTrueInteger($part)?$part:quoteText($part))."]";
        $params=array();
        foreach($fragment["params"][0] as $paramIndex=>$param) {
          if($fragment["params"][1][$paramIndex]!="")
            fatalError("fatal_compile",array(),format($language["fatal_reason_varref"],array($name,$fragment["content"])));
          $value=$fragment["params"][2][$paramIndex];
          $params[$param]=$value!=""?unquoteText($value):true;
        }
        $method=$const?"defined":"isset";
        $source=$const?quoteText($subject):$subject;
        $filter=true;
        $suspicious=$quotes=$search=false;
        foreach($params as $param=>$value) switch($param) {
          case "integer": $subject="formatInteger($subject)"; break;
          case "float": $subject="formatFloat($subject)"; break;
          case "length": $subject="phpcstrlen($subject)"; break;
          case "count": $subject="count($subject)"; break;
          case "limit": $subject="chopText($subject,".(int)$value.")"; break;
          case "uppercase": $subject="phpcstrtoupper($subject,true)"; break;
          case "lowercase": $subject="phpcstrtolower($subject,true)"; break;
          case "titlecase": $subject="phpcucfirst($subject,true)"; break;
          case "wordscase": $subject="phpcucwords($subject,true)"; break;
          case "url": $subject="phpcurlencode($subject,true)"; break;
          case "trim":
          case "ltrim":
          case "rtrim":
            $extra=$value!==true?",".quoteText($value):"";
            $subject="$param($subject$extra)";
            break;
          case "email":
            $extra=$value!==true?",".quoteText($value):"";
            $subject="antispamText($subject$extra)";
            break;
          case "implode":
            $extra=quoteText($value!==true?$value:"");
            $subject="implode($extra,$subject)";
            break;
          case "localize":
            $extra=$value!==true?",".quoteText($value):"";
            $subject="getLocalizedField($subject$extra)";
            break;
          case "search": if($value!==true) $search=$value; break;
          case "replace":
            if($search===false) { $search=$value; $value=""; }
            if($search!==true && $value!==true)
              $subject="str_replace(".quoteText($search).",".quoteText($value).",$subject)";
            break;
          case "callback": $subject=str_replace(".","::",$value)."($subject)"; break;
          case "quotes": $quotes=$value==="double"?"quoteText":"slashes"; break;
          case "spaces": $subject="str_replace(\" \",\"&nbsp;\",$subject)"; break;
          case "textarea": $subject="htmlspecialchars($subject)"; $filter=false; break;
          case "suspicious": $suspicious=true; break;
          case "nofilter": $filter=false; break;
          default: if(isset($language["format_datetime"][$param])) {
            $param=quoteText($param);
            $subject="phpcdate(\$language[\"format_datetime\"][$param],$subject)";
          }
        }
        $suspicious=$suspicious?",true":"";
        if($filter) $subject="filterText($subject$suspicious)";
        if($quotes) $subject="$quotes($subject)";
        $result.="if($method($source))echo $subject;";
        if(isset($params["default"]))
          $result.="else echo".quoteText($params["default"]).";";
      }
      else {
        $params=$this->compileTemplateParams($fragment["params"]);
        $templateName=quoteText($name);
        $tagName=quoteText($fragment["name"]);
        $result.=$params["result"];
        $result.="\$compiler->processWrite($templateName,$tagName,$params[param]);";
      }
    }
    else $result.=$this->compileTemplateFragment($fragment);
    return $result;
  }

  function compileTemplateContents($tid, $name, $fragment)
  {
    $result="";
    if(!isTrueInteger($tid)) $tid=quoteText($tid);
    $fragments=$this->processTemplateContents($fragment);
    foreach($fragments as $fragment) if(is_array($fragment)) {
      $result.="\$_phpc_stack=phpcStackPop(\$_phpc_scope,\$_phpc_name,$tid);";
      $result.="\$_phpc_name(\$_phpc_stack);";
    }
    else $result.=$this->compileTemplateVars($name,$fragment);
    return $result;
  }

  function compileTemplateTags($tid, $id, $task, &$queue, &$queueMap)
  {
    global $language;
    $result="";
    $header="\r\nfunction _phpc{$tid}_exec$id";
    if(!count($task["body"])) return "$header(\$scope){}";
    $globals="\$".implode(",\$",$task["globals"]);
    $globals="global $globals;extract(phpcStackEnd(\$_phpc_scope));";
    $index=0;
    while($index<count($task["body"])) {
      $fragment=$task["body"][$index];
      if(!is_array($fragment)) {
        $result.=$this->compileTemplateContents($tid,$task["name"],$fragment);
        $index++;
        continue;
      }
      $nestingBody=array_slice($task["body"],$index+1,max($fragment["delta"]-1,0));
      if(count($nestingBody)) {
        $nextTask=array(
          "name"=>$task["name"],
          "body"=>$nestingBody,
          "globals"=>$task["globals"],
          "content"=>$task["content"]);
        $queue[]=$nextTask;
        $contentHandler=count($queue);
      }
      else $contentHandler=0;
      $params=$this->compileTemplateParams($fragment["params"]);
      $result.=$params["result"];
      if($fragment["type"]=="insert") {
        $name=$fragment["name"];
        if(!isset($queueMap[$name])) {
          $template=$this->getTemplate($name);
          if(!$template) fatalError("fatal_compile",array(),format($language["fatal_reason_notemplateinsert"],array($name,$task["name"],$fragment["content"])));
          $template=$this->processTemplateAreas($template);
          $nextTask=array(
            "name"=>$template["name"],
            "body"=>$this->processTemplateTags($template),
            "globals"=>$template["globals"],
            "content"=>$contentHandler);
          $queue[]=$nextTask;
          $handler=count($queue);
          $queueMap[$name]=$handler;
        }
        else $handler=$queueMap[$name];
        $result.="\$compiler->stack[]=".quoteText($name).";";
        $result.="_phpc{$tid}_exec$handler(phpcStackPush(\$_phpc_scope,$params[param],$contentHandler));";
        $result.="array_pop(\$compiler->stack);";
      }
      else {
        $templateName=quoteText($task["name"]);
        $tagName=quoteText($fragment["name"]);
        $contentExecutor=quoteText("_phpc{$tid}_exec$contentHandler");
        $result.="\$compiler->processLogic($templateName,$tagName,$params[param],\$_phpc_scope,$contentExecutor);";
        $returns=selectArrayKeys($params["actual"],$fragment["returns"]);
        $returns=count($returns)?quoteText(implode(",",$returns)):false;
        if($returns) $result.="extract(selectArrayKeys(phpcStackEnd(\$_phpc_scope),$returns));";
      }
      $index+=$fragment["delta"]+1;
    }
    $match=preg_match("{^\s*\?>(.*)<\?php\s*\$}s",$result,$matches);
    if($match && strpos($matches[1],"<?")===false) $globals="";
    $pattern="{^\\\$_phpc_stack=phpcStackPop\([^\)]*\);\\\$_phpc_name\([^\)]*\);\$}";
    if(preg_match($pattern,$result)) $globals="";
    return "$header(\$_phpc_scope){{$globals}$result}";
  }

  function compileTemplate($name, $template=false)
  {
    global $language;
    if(!$template) $template=$this->getTemplate($name);
    if(!$template) fatalError("fatal_compile",array(),format($language["fatal_reason_notemplate"],array($name,$this->page["name"])));
    $template=$this->processTemplateAreas($template);
    $tid=$template["id"];
    $result="function _phpc{$tid}_exec0(\$scope){}";
    $firstTask=array(
      "name"=>$template["name"],
      "body"=>$this->processTemplateTags($template),
      "globals"=>$template["globals"],
      "content"=>0);
    $queue=array($firstTask);
    $queueMap=array($name=>1);
    $done=0;
    while($done<count($queue)) $result.=
      $this->compileTemplateTags($tid,$done+1,$queue[$done++],$queue,$queueMap);
    $result=preg_replace("{\?>\r\n(?!\r\n)}","?>",$result);
    $result=preg_replace("{<\?php\s+}","<?php ",$result);
    $result=preg_replace("{\?><\?php\s+}"," ",$result);
    return $result;
  }

  function processHeaders()
  {
    global $language;
    if($this->page["name"]==CompilerErrorPage) sendStatus(404);
    if(CompilerExpose) @header("X-Generated-By: PHPC/".phpcversion());
    if(CompilerNoCacheHeaders) {
      @header("Cache-Control: no-store, no-cache, must-revalidate");
      @header("Pragma: no-cache");
    }
    @header("Content-Type: text/html; charset=$language[charset]");
  }

  function processTemplate($name, $scope=array())
  {
    global $optimizer;
    if($name=="") halt();
    if(!isset($this->style["id"])) fatalError("fatal_nostyle");
    $styleid=$this->style["id"];
    $handle=$optimizer->getTemplate($styleid,$name);
    if(!$handle) $handle=$optimizer->addTemplate($styleid,$name,$this->compileTemplate($name));
    $this->processHeaders();
    $optimizer->executeTemplate($handle,$scope);
    halt();
  }

  function captureTemplate($name, $scope=array())
  {
    global $optimizer;
    if(!isset($this->style["id"])) fatalError("fatal_nostyle");
    $styleid=$this->style["id"];
    $handle=$optimizer->getTemplate($styleid,$name);
    if(!$handle) $handle=$optimizer->addTemplate($styleid,$name,$this->compileTemplate($name));
    ob_start();
    $optimizer->executeTemplate($handle,$scope);
    return ob_get_clean();
  }

  function interceptTemplate($name, $scope=array())
  {
    return $this->captureTemplate($name,$scope);
  }

  function compileBundles($bundles)
  {
    global $language;
    $classes=array("language","settings","fileSystem","mailSystem","database","formatter","optimizer","compiler");
    $globals=array();
    foreach($classes as $class) $globals[$class]=true;
    $result=array("plugins"=>array(),"content"=>array());
    foreach($bundles as $name) {
      $bundle=$this->getBundle($name);
      if(!$bundle) fatalError("fatal_compile",array(),format($language["fatal_reason_nobundle"],array($name,$this->page["name"])));
      $pattern="{(?<![\\\\\\\$])\\\$(\w+)->}";
      preg_match_all($pattern,$bundle["content"],$matches);
      foreach($matches[1] as $global) $globals[$global]=true;
      $result["plugins"]=array_merge($result["plugins"],$bundle["plugins"]);
      $result["content"][]=$bundle["content"];
    }
    unset($globals["this"]);
    $globals="\$".implode(",\$",array_keys($globals));
    $header="global $globals;\r\nunset(\$_phpc_scope,\$_phpc_content);\r\n";
    $footer="\r\nunset($globals);";
    $result["plugins"]=array_values($result["plugins"]);
    $result["content"]=$header.implode("\r\n",$result["content"]).$footer;
    $result["content"]=preg_replace("{^//.*\$}m","",$result["content"]);
    $result["content"]=preg_replace("{(?<=\n)[\r\n]+}s","",$result["content"]);
    return $result;
  }

  function processBundles()
  {
    global $optimizer;
    $scope=array(
      "currentStyle"=>$this->style,
      "currentSession"=>$this->session,
      "currentPage"=>$this->page);
    return $optimizer->executeBundles($this->bundles,$scope);
  }

  function getWriteTags()
  {
    $tags="lang,link,anchor,cycle,options,groupoptions,format,trace";
    return explode(",",$tags);
  }

  function getLogicTags()
  {
    $tags=
      "present,notPresent,empty,notEmpty,equal,notEqual,less,greater,".
      "lessEqual,greaterEqual,regexp,notRegexp,test,iterator,capture,".
      "cache,once,skipOnce,admin,notAdmin,local,notLocal";
    return explode(",",$tags);
  }

  function getLogicTagReturns()
  {
    return array("capture"=>array("property"));
  }

  function processWrite($templateName, $tagName, $params)
  {
    global $formatter;
    if($tagName=="lang") {
      echo getLocalizedField($params);
      return;
    }
    if($tagName=="link" || $tagName=="anchor") {
      $page=ifset($params["property"],"/");
      $filter=!isset($params["nofilter"]);
      unset($params["property"],$params["nofilter"]);
      if($tagName=="anchor") {
        $target=ifset($params["target"],"");
        $content=ifset($params["content"],"");
        unset($params["target"],$params["content"]);
      }
      if(isset($params["params"]) && is_array($params["params"])) {
        $extra=$params["params"];
        unset($params["params"]);
        $params+=$extra;
      }
      $link=$this->createLink($page,$params);
      if($filter) $link=filterText($link,true);
      if($tagName=="anchor") $link=$target!=""?
        format(PredefinedLinkTarget,array($link,$target,$content)):
        format(PredefinedLinkDefault,array($link,$content));
      echo $link;
      return;
    }
    if($tagName=="cycle") {
      static $cycleCache=array();
      $property=(string)ifset($params["property"],"");
      unset($params["property"]);
      if(!isset($cycleCache[$property]))
        $cycleCache[$property]=0; else $cycleCache[$property]++;
      if(!count($params)) return;
      $params=array_values($params);
      echo $params[$cycleCache[$property]%count($params)];
      return;
    }
    if($tagName=="options") {
      $property=ifset($params["property"],array());
      if(!is_array($property)) $property=array($property);
      $selected=ifset($params["selected"]);
      if(!isset($property[$selected])) $selected=false;
      $indent=(int)ifset($params["indent"]);
      $indent=str_repeat(" ",$indent);
      $nokeys=isset($params["nokeys"]);
      $filter=!isset($params["nofilter"]);
      $separator="";
      foreach($property as $key=>$value) {
        if($nokeys) $key=$value;
        if($filter) $value=filterText($value);
        if($selected===false) $selected=$key;
        $option=(string)$key==(string)$selected?
          PredefinedOptionSelected:PredefinedOptionDefault;
        $option=format($option,array(htmlspecialchars($key),$value));
        echo $separator.$indent.$option;
        $separator="\r\n";
      }
      return;
    }
    if($tagName=="groupoptions") {
      $property=ifset($params["property"],array());
      if(!is_array($property)) $property=array($property);
      $selected=ifset($params["selected"]);
      $found=false;
      foreach($property as $group)
        if(isset($group["items"][$selected])) $found=true;
      if(!$found) $selected=false;
      $indent=(int)ifset($params["indent"]);
      $indent=str_repeat(" ",$indent);
      $nokeys=isset($params["nokeys"]);
      $filter=!isset($params["nofilter"]);
      $separator="";
      foreach($property as $group) {
        if(!isset($group["items"]) || !is_array($group["items"])) continue;
        $title=filterText(ifset($group["title"]),true);
        $text="";
        foreach($group["items"] as $key=>$value) {
          if($nokeys) $key=$value;
          if($filter) $value=filterText($value);
          if($selected===false) $selected=$key;
          $option=(string)$key==(string)$selected?
            PredefinedOptionSelected:PredefinedOptionDefault;
          $option=format($option,array(htmlspecialchars($key),$value));
          $text.=$indent.$option."\r\n";
        }
        if($text!="") $text="\r\n$text$indent";
        $text=format(PredefinedOptionGroup,array($title,$text));
        echo $separator.$indent.$text;
        $separator="\r\n";
      }
      return;
    }
    if($tagName=="format") {
      $text=ifset($params["property"],"");
      $classes=ifset($params["class"],"");
      if(isset($params["strict"])) $text=optimizeTextStrict($text);
      if(isset($params["limit"])) $text=chopText($text,(int)$params["limit"]);
      $text=$formatter->processClasses($text,$classes,$params);
      if(isset($params["wrap"]))
        $text=wordwrap($text,(int)$params["wrap"],"\r\n");
      echo $text;
      return;
    }
    if($tagName=="trace") trace(ifset($params["property"]));
  }

  function processLogicCacheCallback($paramsStack, $contentExecutor)
  {
    ob_start();
    $contentExecutor($paramsStack);
    return ob_get_clean();
  }

  function processLogic($templateName, $tagName, $params, &$paramsStack, $contentExecutor)
  {
    global $optimizer;
    $tags=array("present","notPresent","empty","notEmpty");
    if(in_array($tagName,$tags)) {
      switch($tagName) {
        case "present": $success=isset($params["property"]); break;
        case "notPresent": $success=!isset($params["property"]); break;
        case "empty": $success=!isset($params["property"]) || !$params["property"]; break;
        case "notEmpty": $success=isset($params["property"]) && $params["property"]; break;
      }
      if($success) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
    $tags=array("equal","notEqual","less","greater","lessEqual","greaterEqual","regexp","notRegexp");
    if(in_array($tagName,$tags)) {
      if(isset($params["property"]) && isset($params["value"]))
        switch($tagName) {
          case "equal": $success=$params["property"]==$params["value"]; break;
          case "notEqual": $success=$params["property"]!=$params["value"]; break;
          case "less": $success=$params["property"]<$params["value"]; break;
          case "greater": $success=$params["property"]>$params["value"]; break;
          case "lessEqual": $success=$params["property"]<=$params["value"]; break;
          case "greaterEqual": $success=$params["property"]>=$params["value"]; break;
          case "regexp": $success=@preg_match($params["value"],$params["property"]); break;
          case "notRegexp": $success=!@preg_match($params["value"],$params["property"]); break;
        }
        else $success=false;
      if($success) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
    if($tagName=="test") {
      $conditions=ifset($params["property"],"false");
      $callback=create_function("","return (boolean)($conditions);");
      if($callback()) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
    if($tagName=="iterator") {
      $source=ifset($params["property"],array());
      if(!is_array($source)) $source=array();
      if(ifset($params["reverse"])) $source=array_reverse($source,true);
      $sourceKeys=array_keys($source);
      $count=count($sourceKeys);
      if(isset($params["offset"])) $offset=(int)$params["offset"];
      else if(isset($params["chunk"]) && isset($params["count"]))
        $offset=(int)$params["chunk"]*(int)$params["count"];
      else if(isset($params["start"])) {
        for($index=0; $index<$count; $index++)
          if($sourceKeys[$index]==$params["start"]) break;
        $offset=$index;
      }
      else $offset=0;
      $limit=(int)ifset($params["count"],$count);
      $filter=phpccallback(ifset($params["filter"]));
      $already=false;
      for($processed=0; $processed<$limit; $processed++) {
        $index=$offset+$processed;
        if($index<0) continue;
        if($index>=$count) break;
        $key=$sourceKeys[$index];
        if(isset($params["stop"]) && $params["stop"]==$key) break;
        if($filter && !call_user_func($filter,$source[$key])) continue;
        $paramsStackRef=&$paramsStack[count($paramsStack)-1][0];
        if(isset($params["item"])) $paramsStackRef[$params["item"]]=$source[$key];
        if(isset($params["key"])) $paramsStackRef[$params["key"]]=$key;
        if(isset($params["index"])) $paramsStackRef[$params["index"]]=$index;
        if(isset($params["index0"])) $paramsStackRef[$params["index0"]]=$index?$index:"";
        if(isset($params["index1"])) $paramsStackRef[$params["index1"]]=$index+1;
        if($already && isset($params["separator"])) echo $params["separator"];
        $contentExecutor($paramsStack);
        $already=true;
      }
      return;
    }
    if($tagName=="capture") {
      $property=ifset($params["property"]);
      ob_start();
      $contentExecutor($paramsStack);
      $result=ob_get_clean();
      $paramsStackRef=&$paramsStack[count($paramsStack)-1][0];
      $paramsStackRef[$property]=$result;
      return;
    }
    if($tagName=="cache") {
      $property=ifset($params["property"],"cache");
      $time=round(ifset($params["time"],1)*OneHour);
      unset($params["property"],$params["time"]);
      $property.=implode("",$params);
      $callback=array("Compiler","processLogicCacheCallback");
      $params=array($paramsStack,$contentExecutor);
      echo $optimizer->processFileCache($property,$time,$callback,$params);
    }
    if($tagName=="once" || $tagName=="skipOnce") {
      static $onceCache=array();
      $property=ifset($params["property"],"");
      $success=(!isset($onceCache[$property]) xor $tagName=="skipOnce");
      $onceCache[$property]=true;
      if($success) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
    if($tagName=="admin" || $tagName=="notAdmin") {
      $success=(isAdministrator() xor $tagName=="notAdmin");
      if($success) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
    if($tagName=="local" || $tagName=="notLocal") {
      $success=(isLocalhost() xor $tagName=="notLocal");
      if($success) {
        if(isset($params["then"])) echo $params["then"];
        $contentExecutor($paramsStack);
      }
      else if(isset($params["else"])) echo $params["else"];
      return;
    }
  }

  function standardError($message)
  {
    $this->processTemplate("standardError",compact("message"));
  }

  function standardRedirect($message, $link=false)
  {
    if($link===false) $link=$this->createLink("/");
    $this->processTemplate("standardRedirect",compact("message","link"));
  }
}

?>
