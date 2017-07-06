<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

define("PhpcLocalesList","en,ru");
define("PhpcHashFunction","sha1");
define("PhpcPreloadPlugins","");

define("PhpcPasswordParam","phpcpassword");
define("PhpcPasswordCookie","phpcpassword");
define("PhpcLocaleCookie","phpclocale");
define("PhpcStyleCookie","phpcstyle");
define("PhpcSessionCookie","phpcsession");
define("PhpcMemberNameParam","phpcmembername");
define("PhpcMemberPassParam","phpcmemberpass");
define("PhpcMemberNameCookie","phpcmembername");
define("PhpcMemberPassCookie","phpcmemberpass");

define("PhpcSessionEnabled",false);
define("PhpcSessionUseCookies",true);
define("PhpcSessionUseURLs",false);
define("PhpcSessionCleanup",false);
define("PhpcSessionCatchEnabled",true);
define("PhpcSessionCatchRestrictions","");
define("PhpcSessionCatchLimit",10);
define("PhpcSessionTimeout",600);
define("PhpcSessionGCProbability",1);
define("PhpcSessionGCDivisor",100);
define("PhpcSessionParamsLimit",200);

define("OutputCompressionEnabled",true);
define("OutputCompressionLevel",1);

define("FileCacheEnabled",true);
define("FileCacheFilename","/cache/%s.dat");
define("FileSystemUserAgent","Mozilla/5.0 (compatible)");
define("FileSystemTimeout",10);

define("DatabaseQueryLogEnabled",false);
define("DatabaseStartupQueries","");
define("DatabaseRestrictedTables","adminlog,memberlog");

define("CompilerExpose",true);
define("CompilerEfficientSlashes",false);
define("CompilerSecureCookies",true);
define("CompilerNoCacheHeaders",true);
define("CompilerAntispamEnabled",true);
define("CompilerRecodeRequest",false);
define("CompilerRecodeSpaces",false);
define("CompilerRecodePreserve","\w\x80-\xff/");
define("CompilerRequestSymbols","\w\x80-\xff%");
define("CompilerIndexPage","index");
define("CompilerErrorPage","404");
define("CompilerComplexInherit","bundles,params");
define("CompilerPluginFilename","/plugins/%s.php");
define("CompilerEchoLimit",10);

define("CompilerCacheEnabled",true);
define("CompilerCacheCombined",false);
define("CompilerCacheTemplateNames",false);
define("CompilerCacheCompressionEnabled",true);
define("CompilerCacheCompressionLevel",1);
define("CompilerCacheTables","cachetemplates,cachebundles");

define("MailHeadersNewline","\n");
define("MailMessageNewline","\n");
define("MailDebuggerEnabled",false);
define("MailDebuggerFilename","/mail/%s.txt");

/******************************************************************************/

define("OneMinute",60);
define("OneHour",3600);
define("OneDay",86400);
define("OneWeek",604800);
define("OneMonth",2592000);
define("OneYear",31536000);

define("FolderCreateAttributes",0777);
define("EmailAddressPattern","{^\w[\w\-.]*@\w[\w\-]*\.[\w\-.]*\w\$}");
define("UploadSafeExtensions",".gif,.jpg,.jpeg,.png");

define("PredefinedLocalhost","127.0.0.1");
define("PredefinedChop"," .,:;-!?([{/\t\r\n");
define("PredefinedDots","...");
define("PredefinedNewline","<br>");
define("PredefinedWrap","<wbr>");
define("PredefinedParagraphOpen","<p>");
define("PredefinedParagraphClose","</p>");
define("PredefinedLinkDefault","<a href=\"%s\">%s</a>");
define("PredefinedLinkTarget","<a href=\"%s\" target=\"%s\">%s</a>");
define("PredefinedCode","<div class=\"code\">%s</div>");
define("PredefinedOptionDefault","<option value=\"%s\">%s</option>");
define("PredefinedOptionSelected","<option value=\"%s\" selected=\"selected\">%s</option>");
define("PredefinedOptionGroup","<optgroup label=\"%s\">%s</optgroup>");

define("FatalReport","<b>{header}</b> {text}<br>\r\n{error}{query}");
define("FatalReportError","<b>{header}</b> {text}<br>\r\n");
define("FatalReportQuery","<b>{header}</b> {text}<br>\r\n");

/******************************************************************************/

$timeOffsetServer=0;
$timeOffsetClient=0;
$uploadDefaultFormat=array("start"=>1,"digits"=>8,"prepend"=>"","append"=>"");

?>
