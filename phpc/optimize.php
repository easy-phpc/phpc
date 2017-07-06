<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

class Optimizer
{
  function addTemplate($styleid, $name, $content)
  {
    global $database;
    $compressed=CompilerCacheEnabled && CompilerCacheCompressionEnabled && function_exists("gzcompress");
    if($compressed) $content=gzcompress($content,CompilerCacheCompressionLevel);
    $template=$name;
    $result=compact("styleid","template","content","compressed");
    if(CompilerCacheEnabled) $database->addLine("cachetemplates",$result);
    return $result;
  }

  function getTemplate($styleid, $name)
  {
    global $database;
    static $cache=array();
    if(!CompilerCacheEnabled) return false;
    $key="$styleid:$name";
    if(!ifset($cache[$key])) {
      $conditions="styleid=$styleid AND template=".slashes($name);
      $cache[$key]=$database->getLine("cachetemplates",$conditions);
    }
    return $cache[$key];
  }

  function executeTemplate($handle, $scope)
  {
    if(!$handle) return;
    $content=$handle["compressed"]?gzuncompress($handle["content"]):$handle["content"];
    $this->processTemplate($handle["template"],$content,$scope);
  }

  function processTemplate($name, $content, $scope)
  {
    global $compiler;
    $match=substr($content,0,14)=="function _phpc";
    if(!$match) { unset($scope["_phpc_scope"]); eval($content); return; }
    $tid=substr($content,14,strpos($content,"_",14)-14);
    $callback="_phpc{$tid}_exec1";
    if(!function_exists($callback)) eval($content);
    if(!function_exists($callback)) return;
    unset($content,$match,$tid,$scope["_phpc_scope"]);
    $_phpc_stack=$compiler->stack;
    $compiler->stack=array($name);
    $callback(array(array($scope,0)));
    $compiler->stack=$_phpc_stack;
  }

  function addBundles($styleid, $names, $compilation)
  {
    global $database;
    $content=$compilation["content"];
    $compressed=CompilerCacheEnabled && CompilerCacheCompressionEnabled && function_exists("gzcompress");
    if($compressed) $content=gzcompress($content,CompilerCacheCompressionLevel);
    $bundles=implode(",",$names);
    $plugins=implode(",",$compilation["plugins"]);
    $result=compact("styleid","bundles","plugins","content","compressed");
    if(CompilerCacheEnabled) $database->addLine("cachebundles",$result);
    return $result;
  }

  function getBundles($styleid, $names)
  {
    global $database;
    if(!count($names)) return array("bundles"=>"","plugins"=>"","content"=>"","compressed"=>0);
    if(!CompilerCacheEnabled) return false;
    $bundles=implode(",",$names);
    $conditions="styleid=$styleid AND bundles=".slashes($bundles);
    return $database->getLine("cachebundles",$conditions);
  }

  function getBundlesPlugins($handle)
  {
    return explodeSmart(",",$handle["plugins"]);
  }

  function executeBundles($handle, $scope)
  {
    if(!$handle) return array();
    $_phpc_scope=$scope;
    $_phpc_content=$handle["content"];
    if($handle["compressed"]) $_phpc_content=gzuncompress($_phpc_content);
    unset($handle,$scope);
    extract($_phpc_scope); eval($_phpc_content);
    unset($_phpc_scope,$_phpc_content);
    return get_defined_vars();
  }

  function clearCache($strict=false)
  {
    global $database;
    static $already=false;
    if(CompilerCacheCombined) $this->clearFileCache();
    if($already && !$strict) return; else $already=true;
    $tables=explodeSmart(",",CompilerCacheTables);
    foreach($tables as $table) $database->clearTable($table);
  }

  function processFileCache($name, $time, $callback, $params=array())
  {
    global $fileSystem;
    if(!is_array($params)) $params=array($params);
    if(!FileCacheEnabled) return call_user_func_array($callback,$params);
    $filename=format(FileCacheFilename,$name);
    $content=$fileSystem->openFile($filename);
    if($content) $content=unserialize($content);
    if($content && $content["time"]>=phpctime()) return $content["data"];
    $result=call_user_func_array($callback,$params);
    $content=array("time"=>phpctime()+$time,"data"=>$result);
    $success=$fileSystem->saveFile($filename,serialize($content));
    if(!$success) fatalError("fatal_filecache");
    return $result;
  }

  function clearFileCache($mask="*")
  {
    global $fileSystem;
    $extension=$fileSystem->getFileExtension(FileCacheFilename);
    $folder=dirname(FileCacheFilename)."/";
    $cache=$fileSystem->getFolder($folder,$extension);
    foreach($cache as $entry) if(phpcmatch($mask,basename($entry,$extension)))
      $fileSystem->deleteFile($folder.$entry);
  }
}

?>
