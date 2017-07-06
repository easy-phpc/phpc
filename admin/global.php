<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

error_reporting(7);

require "../phpc/config.php";
require "../phpc/constant.php";
require "../phpc/language.php";
require "../phpc/function.php";
require "../phpc/filesyst.php";
require "../phpc/mailsyst.php";
require "../phpc/database.php";
require "../phpc/format.php";
require "../phpc/optimize.php";
require "../phpc/compiler.php";
require "../phpc/backcomp.php";

require "constant.php";
require "controls.php";
require "function.php";

$fileSystem=new FileSystem;
$mailSystem=new MailSystem;
$database=new Database;
$formatter=new Formatter;
$optimizer=new Optimizer;
$compiler=new Compiler;

outputStart();
processGlobalCache();

$plugins=$compiler->getPreloadPlugins();
foreach($plugins as $plugin) { require_once $plugin; }

checkAdminAuthorization();
checkAdminValidity();

?>
