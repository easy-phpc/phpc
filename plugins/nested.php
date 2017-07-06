<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

// PHPC Online Plugin - Nested Sets v1.0 by Dagdamor

/****************************** Class Definition ******************************/

class NestedSet
{
  var $table;
  var $autoOrder;
  var $enablePaths;
  var $conditions;
  var $reorderStart=1;
  var $reorderStep=1;
  var $tree=false;

  function NestedSet($table, $autoOrder=false, $enablePaths=false, $conditions="")
  {
    $this->table=$table;
    $this->autoOrder=$autoOrder;
    $this->enablePaths=$enablePaths;
    $this->conditions=$conditions;
  }

  function internalGetTree($check=false)
  {
    global $database;
    $report=$check?array("orphans"=>array(),"recursion"=>array()):array();
    if($this->tree) return $report;
    $order=$this->autoOrder?$this->autoOrder:"itemorder";
    $this->tree=$database->getOrderedLines($this->table,$order,$this->conditions);
    $this->tree=extractArrayLines($this->tree,"id");
    foreach($this->tree as $id=>$item) $this->tree[$id]["items"]=array();
    $this->tree[0]=array("id"=>0,"items"=>array());
    foreach($this->tree as $id=>$item) if($id) {
      $parentid=$item["parentid"];
      if(!isset($this->tree[$parentid])) {
        $this->tree[$id]["parentid"]=$parentid=0;
        $database->modifyField($this->table,"parentid",0,"id=$id");
        if($check) $report["orphans"][]=$id;
      }
      if($check) {
        $locator=$id;
        $used=array();
        while($locator && !isset($used[$locator])) {
          $used[$locator]=true;
          $locator=$this->tree[$locator]["parentid"];
        }
        if($locator) {
          $this->tree[$id]["parentid"]=$parentid=0;
          $database->modifyField($this->table,"parentid",0,"id=$id");
          $report["recursion"][]=$id;
        }
      }
      $this->tree[$parentid]["items"][]=$id;
    }
    return $report;
  }

  function internalRecalculate(&$report, &$counter, $parentid=0, $level=1, $path=array())
  {
    global $database;
    $order=$this->reorderStart;
    foreach($this->tree[$parentid]["items"] as $id) {
      $item=$this->tree[$id];
      $update=array();
      if($order!=$item["itemorder"]) $update["itemorder"]=$order;
      $order+=$this->reorderStep;
      if($level!=$item["itemlevel"]) $update["itemlevel"]=$level;
      $counter++;
      if($counter!=$item["itemleft"]) $update["itemleft"]=$counter;
      $newPath=$path;
      if($this->enablePaths) {
        $newPath[]=$item["name"];
        $newPathLine=implode("/",$newPath);
        if(strpos("/$newPathLine/","//")!==false) $newPathLine="";
        if($newPathLine!=$item["path"]) $update["path"]=$newPathLine;
      }
      $this->internalRecalculate($report,$counter,$id,$level+1,$newPath);
      $counter++;
      if($counter!=$item["itemright"]) $update["itemright"]=$counter;
      if(!count($update)) continue;
      $this->tree[$id]=array_merge($this->tree[$id],$update);
      $database->modifyLine($this->table,$update,"id=$id");
      $report["updated"][]=$id;
    }
  }

  function getTree()
  {
    $this->internalGetTree();
    return $this->tree;
  }

  function cleanup()
  {
    global $database;
    $values=array("itemlevel"=>0,"itemleft"=>0,"itemright"=>0);
    if($this->autoOrder) $values["itemorder"]=0;
    if($this->enablePaths) $values["path"]="";
    $database->modifyLines($this->table,$values,$this->conditions);
    $this->tree=false;
  }

  function recalculate()
  {
    $report=array("updated"=>array());
    $counter=0;
    $this->internalGetTree();
    $this->internalRecalculate($report,$counter);
    return $report;
  }

  function repair()
  {
    global $database;
    $report=$this->internalGetTree(true);
    $report+=$this->recalculate();
    $database->optimizeTable($this->table);
    return $report;
  }

  function prepareSelection($items)
  {
    $result=$locators=array();
    $locators[0]=&$result;
    foreach($items as $item) {
      $item["items"]=array();
      $parentid=isset($locators[$item["parentid"]])?$item["parentid"]:0;
      $locators[$parentid][]=$item;
      $index=count($locators[$parentid])-1;
      $locators[$item["id"]]=&$locators[$parentid][$index]["items"];
    }
    return $result;
  }
}

?>
