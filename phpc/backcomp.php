<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

define("APPROVAL_ACCEPT",0);
define("APPROVAL_REJECT",1);
define("APPROVAL_DELETE",2);

function administrator($script=false) { return isAdministrator($script); }
function clientAddress() { return getClientAddress(); }
function equalArrays($array1,$array2) { return areArraysEqual($array1,$array2); }
function incrementalValue() { return getIncrementalValue(); }
function isBanned($blackList) { return isClientBanned($blackList); }
function trueInteger($value) { return isTrueInteger($value); }
function trueFloat($value) { return isTrueFloat($value); }
function gzipCompressionSkip() {}

if(!function_exists("mysqli_connect") && function_exists("mysql_connect")) {
  function mysqli_connect($host,$user,$pass,$db,$port) { return ($result=mysql_connect($port?"$host:$port":$host,$user,$pass))?(mysql_select_db($db,$result)?$result:false):$result; }
  function mysqli_error($conn) { return mysql_error($conn); }
  function mysqli_fetch_assoc($resource) { return mysql_fetch_assoc($resource); }
  function mysqli_fetch_row($resource) { return mysql_fetch_row($resource); }
  function mysqli_free_result($resource) { mysql_free_result($resource); }
  function mysqli_get_server_info($conn) { return mysql_get_server_info($conn); }
  function mysqli_insert_id($conn) { return mysql_insert_id($conn); }
  function mysqli_more_results() { return false; }
  function mysqli_multi_query($conn,$queries) { foreach(explode(";",$queries) as $query) mysql_query($query,$conn); }
  function mysqli_query($conn,$query) { return mysql_query($query,$conn); }
  function mysqli_select_db($conn,$db) { return mysql_select_db($db,$conn); }
}

?>
