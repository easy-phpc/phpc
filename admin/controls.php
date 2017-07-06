<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

$controlState=array();

/***************************** General Functions ******************************/

function makeHeaders()
{
  global $language;
  @header("Cache-Control: no-store, no-cache, must-revalidate");
  @header("Pragma: no-cache");
  @header("Content-Type: text/html; charset=$language[charset]");
}

function makeInstallerPage($what)
{
  global $language;
  if($what=="header") {
    makeHeaders();
    echo "<html>\r\n";
    makeAdminHeader("admin_installer_title");
    echo "<body>\r\n";
    echo "$language[admin_installer_header]<br><br>\r\n";
  }
  if($what=="footer") {
    echo "</body>\r\n";
    echo "</html>\r\n";
    halt();
  }
}

function makeAdminPage($what, $bodyclass="")
{
  if($what=="header") {
    if($bodyclass!="") $bodyclass=" class=\"$bodyclass\"";
    makeHeaders();
    echo "<html>\r\n";
    makeAdminHeader("admin_title");
    echo "<body$bodyclass>\r\n";
  }
  if($what=="footer") {
    echo "</body>\r\n";
    echo "</html>\r\n";
    halt();
  }
}

function makeAdminFrames()
{
  global $language;
  makeHeaders();
  echo "<html>\r\n";
  makeAdminHeader("admin_title",false);
  echo "<frameset cols=\"".AdminFramesMenuWidth.",*\" framespacing=\"0\" border=\"0\" frameborder=\"0\">\r\n";
  echo "<frame name=\"menu\" src=\"index.php?action=menu\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"auto\" noresize>\r\n";
  echo "<frameset rows=\"".AdminFramesHeaderHeight.",*\" framespacing=\"0\" border=\"0\" frameborder=\"0\">\r\n";
  echo "<frame name=\"head\" src=\"index.php?action=head\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"no\" noresize>\r\n";
  echo "<frame name=\"main\" src=\"index.php?action=home\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"auto\" noresize>\r\n";
  echo "</frameset>\r\n";
  echo "</frameset>\r\n";
  echo "<noframes>$language[admin_noframes]</noframes>\r\n";
  echo "</html>\r\n";
  halt();
}

function makeAdminHeader($title, $patch=true)
{
  global $language;
  $title=$language[$title];
  $styles=getAdminStylesList();
  echo "<head>\r\n";
  echo "<title>$title</title>\r\n";
  foreach($styles as $style)
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$style\">\r\n";
  if($patch) makeAdminTransparencyPatch();
  echo "</head>\r\n";
}

function makeAdminTransparencyPatch()
{
  if(!AdminTransparencyPatch) return;
  $spacer=normalizeAdminPath(AdminSpacerLocation);
  echo "<script type=\"text/javascript\">\r\n";
  echo "function transparent(im)\r\n";
  echo "{\r\n";
  echo "  if(!im.transparented && (/\.png/.test(im.src))) {\r\n";
  echo "    im.transparented=1; var picture=im.src; var w=im.width; var h=im.height; im.src=\"$spacer\";\r\n";
  echo "    im.style.filter=\"progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src='\"+picture+\"');\";\r\n";
  echo "    im.width=w; im.height=h;\r\n";
  echo "  }\r\n";
  echo "  return \"transparent\";\r\n";
  echo "}\r\n";
  echo "</script>\r\n";
  echo "<style type=\"text/css\">* html img { background:expression(transparent(this)); }</style>\r\n";
}

function makeAdminAuthorization()
{
  global $language;
  $memberPanel=defined("PhpcMemberPanel");
  $param=$memberPanel?PhpcMemberPassParam:PhpcPasswordParam;
  $attempt=acceptStringParameter($param)!="";
  if($attempt) sleep(AdminAuthorizationDelay);
  $request=stripSlashesSmart($_SERVER["REQUEST_URI"]);
  $request=preg_replace("{\?$param=.*}","",$request);
  echo "<br><div align=\"center\"><table class=\"form\" align=\"center\">\r\n";
  echo "<form action=\"$request\" method=\"post\" target=\"_self\">\r\n";
  echo "<tr class=\"header auth\"><td>\r\n";
  if($memberPanel) {
    echo "<div style=\"padding:0px 5px\">$language[admin_auth_promptmember]</div>\r\n";
    echo "<div style=\"padding:4px 0px\"><input class=\"authmember\" id=\"focused\" type=\"text\" name=\"".PhpcMemberNameParam."\">\r\n";
    echo "<input class=\"authmember\" type=\"password\" name=\"".PhpcMemberPassParam."\"></div>\r\n";
  }
  else {
    echo "<div style=\"padding:0px 5px\">$language[admin_auth_prompt]</div>\r\n";
    echo "<div style=\"padding:4px 0px\"><input id=\"focused\" type=\"password\" name=\"".PhpcPasswordParam."\"></div>\r\n";
  }
  echo "<div align=\"center\"><input class=\"button\" type=\"submit\" value=\"$language[admin_auth_submit]\"></div>\r\n";
  echo "</td></tr>\r\n";
  echo "</form>\r\n";
  echo "</table></div>\r\n";
  echo "<script type=\"text/javascript\">document.getElementById(\"focused\").focus();</script>\r\n";
}

function makeAdminHeadline()
{
  global $language, $database, $compiler;
  $installed=$database->isTablePresent("styles");
  $root=defined("PhpcRoot")?PhpcRoot:"/";
  $link=$installed?$compiler->createLink("/"):$root;
  $memberPanel=defined("PhpcMemberPanel");
  $param=$memberPanel?PhpcMemberPassParam:PhpcPasswordParam;
  echo "<table width=\"100%\" height=\"100%\">\r\n";
  echo "<tr><td style=\"padding:0px 5px\"><a href=\"$link\" target=\"_blank\">$language[admin_opensite]</a></td>\r\n";
  echo "<td style=\"padding:0px 5px\" align=\"right\"><a href=\"./?$param=clear\" target=\"_top\">$language[admin_logout]</a></td></tr>\r\n";
  echo "</table>\r\n";
}

function makeAdminControlOption($what, $param, $value=null)
{
  global $controlState;
  switch("$what.$param") {
  case "form.width":
    unset($controlState["formwidth"]);
    if($value!==null) $controlState["formwidth"]=$value;
    break;
  case "table.width":
    unset($controlState["table"]["width"]);
    if($value!==null) $controlState["table"]["width"]=$value;
    break;
  case "editor.wrap":
    unset($controlState["editorwrap"]);
    if($value!==null) $controlState["editorwrap"]=$value;
    break;
  }
}

/**************************** Main Menu Functions *****************************/

function makeMenu($what, $plugin="")
{
  global $language, $controlState;
  if($what=="header") {
    $logo=normalizeAdminPath(AdminLogoLocation);
    echo "<div align=\"center\"><a href=\"index.php?action=home\" target=\"main\">";
    echo "<img src=\"$logo\"><br>$language[admin_home]</a></div>\r\n";
    echo "<table class=\"menu\" width=\"100%\">\r\n";
  }
  if($what=="plugin") {
    $controlState["menuplugin"]=$plugin;
  }
  if($what=="separator" || $what=="footer") {
    if(!isset($controlState["menuseparator"]))
      echo "<tr><td><hr class=\"menu\"></td></tr>\r\n";
    $controlState["menuseparator"]=true;
  }
  if($what=="footer") {
    echo "</table>\r\n";
    unset($controlState["menuplugin"]);
    unset($controlState["menuseparator"]);
  }
}

function makeMenuGroup($what, $title="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $title=$lang?$language[$title]:formatAdminText($title);
    $id=isset($controlState["menuplugin"])?" id=\"menu$controlState[menuplugin]\"":"";
    echo "<tr><td><div class=\"menugroup\"$id>$title</div>\r\n<nobr>";
  }
  if($what=="footer") {
    echo "</nobr></td></tr>\r\n";
    unset($controlState["menuseparator"]);
    unset($controlState["menuitem"]);
    unset($controlState["menubreak"]);
  }
}

function makeMenuItem($title, $link, $break=false, $lang=true)
{
  global $language, $controlState;
  $title=$lang?$language[$title]:formatAdminText($title);
  $separator1=isset($controlState["menubreak"])?"":" |";
  $separator2=isset($controlState["menubreak"])?"<br>\r\n":"\r\n";
  if(isset($controlState["menuitem"])) echo "$separator1</nobr>$separator2<nobr>";
  echo "<a href=\"$link\" target=\"main\">$title</a>";
  $controlState["menuitem"]=$controlState["menubreak"]=true;
  if(!$break) unset($controlState["menubreak"]);
}

/***************************** Toolbar Functions ******************************/

function makeToolbar($what, $title="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    echo "<div align=\"center\"><table class=\"form\" align=\"center\" width=\"85%\">\r\n";
    $controlState["toolbar"]=0;
  }
  if($what=="header" || $what=="separator") {
    $title=$lang?$language[$title]:formatAdminText($title);
    echo "<tr class=\"$what\"><td colspan=\"2\">$title</td></tr>\r\n";
  }
  if($what=="footer") {
    echo "</table></div>\r\n";
    unset($controlState["toolbar"]);
  }
}

function makeToolbarItem($what, $title="", $description="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $title=$lang?$language[$title]:formatAdminText($title);
    $description=$lang?$language[$description]:formatAdminText($description);
    $class=(++$controlState["toolbar"]%2)?"firstline":"secondline";
    echo "<tr class=\"$class\"><td><font class=\"title\">$title</font><br>$description</td>\r\n";
    echo "<td align=\"right\" nowrap=\"nowrap\">";
  }
  if($what=="footer") {
    echo "</td></tr>\r\n";
  }
}

function makeToolbarInput($title, $description, $url, $method, $name, $value="", $hidden=array(), $lang=true)
{
  global $language;
  makeToolbarItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  $target="";
  if(preg_match("{^(?!javascript:)(\w+):(.*)}",$url,$matches)) {
    $target=" target=\"$matches[1]\"";
    $url=$matches[2];
  }
  echo "<table class=\"hidden\" align=\"right\">\r\n";
  echo "<form action=\"$url\" method=\"$method\"$target>\r\n";
  foreach($hidden as $field=>$fieldvalue) {
    $fieldvalue=htmlspecialchars($fieldvalue);
    echo "<input type=\"hidden\" name=\"$field\" value=\"$fieldvalue\">\r\n";
  }
  echo "<tr><td><input class=\"toolbar\" type=\"text\" name=\"$name\" value=\"$value\"></td>\r\n";
  echo "<td style=\"padding-left:5px\"><input class=\"button\" type=\"submit\" value=\"$language[admin_submit]\"></td></tr>\r\n";
  echo "</form>\r\n";
  echo "</table>";
  makeToolbarItem("footer");
}

function makeToolbarChooser($title, $description, $value=false, $options=array(), $local=true, $lang=true, $linklang=true)
{
  global $language;
  makeToolbarItem("header",$title,$description,$lang);
  $id="toolbar".getIncrementalValue("toolbar");
  $script=$local?
    "document.location.href=$id.options[$id.selectedIndex].value":
    "window.open($id.options[$id.selectedIndex].value,'','location,menubar,resizable,scrollbars,status,toolbar')";
  $script="if($id.selectedIndex>=0) $script;";
  echo "<table class=\"hidden\" align=\"right\">\r\n";
  echo "<tr><td><select class=\"toolbar\" id=\"$id\">\r\n";
  if(!isset($options[$value])) $value=false;
  foreach($options as $link=>$option) {
    if($value===false) $value=$link;
    $selected=(string)$link===(string)$value?" selected=\"selected\"":"";
    $option=$linklang?$language[$option]:formatAdminText($option);
    echo "<option value=\"$link\"$selected>$option</option>\r\n";
  }
  echo "</select></td>\r\n";
  echo "<td style=\"padding-left:5px\"><input class=\"button\" type=\"button\" value=\"$language[admin_submit]\" onclick=\"$script\"></td></tr>\r\n";
  echo "</table>";
  makeToolbarItem("footer");
}

/******************************* Form Functions *******************************/

function makeForm($what, $title="", $script="", $action="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $controlState["form"]=0;
    $controlState["formid"]="form".getIncrementalValue("form");
    if(is_array($action)) {
      $controlState["formsubmit"]=$action[0];
      $controlState["formupdate"]=$action[1];
      $action=$action[0];
    }
    $controlState["formaction"]=$action;
    $anchor=preg_match("{#.*}",$script,$matches)?$matches[0]:"";
    $script=preg_replace("{#.*}","",$script);
    $width=ifset($controlState["formwidth"],"95%");
    $width=$width?" width=\"$width\"":"";
    if($script!="") $script=" action=\"$script.php$anchor\"";
    echo "<div align=\"center\"><table class=\"form\" align=\"center\"$width>\r\n";
    ob_start();
    echo "<form id=\"$controlState[formid]\"$script method=\"post\">\r\n";
    if($action!="") echo "<input type=\"hidden\" name=\"action\" value=\"$action\">\r\n";
  }
  if($what=="header" || $what=="separator") {
    $title=$lang?$language[$title]:formatAdminText($title);
    if($title!="") echo "<tr class=\"$what\"><td colspan=\"2\">$title</td></tr>\r\n";
  }
  if($what=="footer") {
    echo "<tr class=\"footer\"><td colspan=\"2\" align=\"center\" nowrap=\"nowrap\">\r\n";
    echo "<table class=\"hidden\" align=\"center\"><tr>\r\n";
    if(isset($controlState["formupdate"])) {
      $onclick="document.getElementById('$controlState[formid]').elements['action'].value='$controlState[formsubmit]';return true";
      echo "<td style=\"padding-right:5px\"><input class=\"button\" type=\"submit\" value=\"$language[admin_submit]\" onclick=\"$onclick\"></td>\r\n";
      $onclick="document.getElementById('$controlState[formid]').elements['action'].value='$controlState[formupdate]';return true";
      echo "<td style=\"padding-right:5px\"><input class=\"button\" type=\"submit\" value=\"$language[admin_update]\" onclick=\"$onclick\"></td>\r\n";
    }
    else if($controlState["formaction"]!="")
      echo "<td style=\"padding-right:5px\"><input class=\"button\" type=\"submit\" value=\"$language[admin_submit]\"></td>\r\n";
    echo "<td><input class=\"button\" type=\"reset\" value=\"$language[admin_reset]\"></td>\r\n";
    echo "</tr></table></td></tr>\r\n";
    echo "</form>\r\n";
    if(isset($controlState["formfile"])) {
      $content=ob_get_clean();
      $content=preg_replace("{^<form}","<form enctype=\"multipart/form-data\"",$content);
      echo $content;
    }
    else ob_end_flush();
    echo "</table></div>\r\n";
    unset($controlState["form"]);
    unset($controlState["formid"]);
    unset($controlState["formsubmit"]);
    unset($controlState["formupdate"]);
    unset($controlState["formaction"]);
    unset($controlState["formfile"]);
  }
}

function makeFormItem($what, $title="", $description="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $title=$lang?$language[$title]:formatAdminText($title);
    $description=$lang?$language[$description]:formatAdminText($description);
    $class=(++$controlState["form"]%2)?"firstline":"secondline";
    echo "<tr class=\"$class\"><td><font class=\"title\">$title</font><br>$description</td>\r\n";
    echo "<td align=\"right\" nowrap=\"nowrap\">";
  }
  if($what=="footer") {
    echo "</td></tr>\r\n";
  }
}

function makeFormItemWide($what, $title="", $description="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $title=$lang?$language[$title]:formatAdminText($title);
    $description=$lang?$language[$description]:formatAdminText($description);
    $class=(++$controlState["form"]%2)?"firstline":"secondline";
    echo "<tr class=\"$class\"><td colspan=\"2\">";
    if($title!="" || $description!="") {
      if($title!="") $title="<font class=\"title\">$title</font><br>";
      echo "<div style=\"margin-bottom:4px\">$title$description</div>\r\n";
    }
  }
  if($what=="footer") {
    echo "</td></tr>\r\n";
  }
}

function makeFormHidden($name, $value="")
{
  $value=htmlspecialchars($value);
  echo "<input type=\"hidden\" name=\"$name\" value=\"$value\">\r\n";
}

function makeFormInput($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<input type=\"text\" name=\"$name\" value=\"$value\">";
  makeFormItem("footer");
}

function makeFormInputRead($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<input type=\"text\" name=\"$name\" value=\"$value\" readonly=\"readonly\">";
  makeFormItem("footer");
}

function makeFormInputOrder($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<input class=\"order\" type=\"text\" name=\"$name\" value=\"$value\">";
  makeFormItem("footer");
}

function makeFormPassword($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<input type=\"password\" name=\"$name\" value=\"$value\">";
  makeFormItem("footer");
}

function makeFormDate($title, $description, $name, $value=false, $lang=true)
{
  if($value===false) $value=timestamp2date();
  if($value=="0000-00-00") $value="";
  makeFormInput($title,$description,$name,$value,$lang);
}

function makeFormDatetime($title, $description, $name, $value=false, $lang=true)
{
  if($value===false) $value=phpctime();
  $value=$value?timestamp2datetime($value):"";
  makeFormInput($title,$description,$name,$value,$lang);
}

function makeFormFile($title, $description, $name, $lang=true)
{
  global $controlState;
  makeFormItem("header",$title,$description,$lang);
  echo "<input type=\"file\" name=\"$name\">";
  makeFormItem("footer");
  $controlState["formfile"]=true;
}

function makeFormTextarea($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<textarea name=\"$name\">$value</textarea>";
  makeFormItem("footer");
}

function makeFormTextareaRead($title, $description, $name, $value="", $lang=true)
{
  makeFormItem("header",$title,$description,$lang);
  $value=htmlspecialchars($value);
  echo "<textarea name=\"$name\" readonly=\"readonly\">$value</textarea>";
  makeFormItem("footer");
}

function makeFormEditor($title, $description, $name, $value="", $lang=true)
{
  global $controlState;
  makeFormItemWide("header",$title,$description,$lang);
  $wrap=ifset($controlState["editorwrap"])?"":" wrap=\"off\"";
  $value=htmlspecialchars($value);
  echo "<textarea class=\"editor\" name=\"$name\"$wrap>$value</textarea>";
  makeFormItemWide("footer");
}

function makeFormExternalEditor($class, $title, $description, $name, $value="", $lang=true)
{
  $callback=array($class,"isSupported");
  $supported=class_exists($class) && call_user_func($callback);
  if($supported) {
    $callback=array($class,"getEditorCode");
    $params=array("width"=>ExternalEditorWidth,"height"=>ExternalEditorHeight);
    makeFormItemWide("header",$title,$description,$lang);
    echo call_user_func($callback,$name,$value,$params);
    makeFormItemWide("footer");
  }
  else makeFormEditor($title,$description,$name,$value,$lang);
}

function makeFormHTMLEditor($title, $description, $name, $value="", $lang=true)
{
  $class="HTMLEditorSupport";
  makeFormExternalEditor($class,$title,$description,$name,$value,$lang);
}

function makeFormTPLEditor($title, $description, $name, $value="", $lang=true)
{
  $class="TPLEditorSupport";
  makeFormExternalEditor($class,$title,$description,$name,$value,$lang);
}

function makeFormPHPEditor($title, $description, $name, $value="", $lang=true)
{
  $class="PHPEditorSupport";
  makeFormExternalEditor($class,$title,$description,$name,$value,$lang);
}

function makeFormChooser($title, $description, $name, $value=false, $options=array(), $lang=true, $filter=true)
{
  makeFormItem("header",$title,$description,$lang);
  echo "<select name=\"$name\">\r\n";
  if(!isset($options[$value])) $value=false;
  foreach($options as $key=>$option) {
    if($value===false) $value=$key;
    $selected=(string)$key===(string)$value?" selected=\"selected\"":"";
    $key=htmlspecialchars($key);
    if($filter) $option=formatAdminText($option);
    echo "<option value=\"$key\"$selected>$option</option>\r\n";
  }
  echo "</select>";
  makeFormItem("footer");
}

function makeFormGroupChooser($title, $description, $name, $value=false, $groups=array(), $lang=true, $filter=true)
{
  makeFormItem("header",$title,$description,$lang);
  $found=false;
  foreach($groups as $group) if(isset($group["items"][$value])) $found=true;
  if(!$found) $value=false;
  echo "<select name=\"$name\">\r\n";
  foreach($groups as $group) {
    if(isset($group["title"])) {
      $title=filterText($group["title"],true);
      echo "<optgroup label=\"$title\">\r\n";
    }
    foreach($group["items"] as $key=>$option) {
      if($value===false) $value=$key;
      $selected=(string)$key===(string)$value?" selected=\"selected\"":"";
      $key=htmlspecialchars($key);
      if($filter) $option=formatAdminText($option);
      echo "<option value=\"$key\"$selected>$option</option>\r\n";
    }
    if(isset($group["title"])) echo "</optgroup>\r\n";
  }
  echo "</select>";
  makeFormItem("footer");
}

function makeFormSelector($title, $description, $name, $options=array(), $selected=array(), $lang=true, $filter=true)
{
  makeFormItem("header",$title,$description,$lang);
  echo "<select class=\"selector\" name=\"$name\" multiple=\"multiple\">\r\n";
  foreach($options as $key=>$option) {
    $select=in_array($key,$selected)?" selected=\"selected\"":"";
    $key=htmlspecialchars($key);
    if($filter) $option=formatAdminText($option);
    echo "<option value=\"$key\"$select>$option</option>\r\n";
  }
  echo "</select>";
  makeFormItem("footer");
}

function makeFormRadio($title, $description, $name, $value=false, $options=array(), $lang=true, $filter=true)
{
  makeFormItem("header",$title,$description,$lang);
  if(!isset($options[$value])) $value=false;
  $already=false;
  foreach($options as $key=>$option) {
    if($already) echo "\r\n"; else $already=true;
    if($value===false) $value=$key;
    $selected=(string)$key===(string)$value?" checked=\"checked\"":"";
    $key=htmlspecialchars($key);
    if($filter) $option=formatAdminText($option);
    echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"$key\"$selected> $option&nbsp;";
  }
  makeFormItem("footer");
}

function makeFormYesNo($title, $description, $name, $value=1, $lang=true)
{
  global $language;
  makeFormItem("header",$title,$description,$lang);
  $value=$value?1:0;
  $selected=array("","",$value=>" checked=\"checked\"");
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"1\"$selected[1]> $language[common_yes]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"0\"$selected[0]> $language[common_no]&nbsp;";
  makeFormItem("footer");
}

function makeFormYesNoAny($title, $description, $name, $value=1, $lang=true)
{
  global $language;
  makeFormItem("header",$title,$description,$lang);
  $value=$value<0?"any":($value?1:0);
  $selected=array("","","any"=>"",$value=>" checked=\"checked\"");
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"1\"$selected[1]> $language[common_yes]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"0\"$selected[0]> $language[common_no]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"-1\"$selected[any]> $language[common_any]&nbsp;";
  makeFormItem("footer");
}

function makeFormYesNoNull($title, $description, $name, $value=1, $lang=true)
{
  global $language;
  makeFormItem("header",$title,$description,$lang);
  $value=$value===null?"null":($value?1:0);
  $selected=array("","","null"=>"",$value=>" checked=\"checked\"");
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"1\"$selected[1]> $language[common_yes]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"0\"$selected[0]> $language[common_no]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"\"$selected[null]> $language[common_null]&nbsp;";
  makeFormItem("footer");
}

function makeFormApproval($title, $description, $name, $value=ApprovalValueReject, $lang=true)
{
  global $language;
  makeFormItem("header",$title,$description,$lang);
  $value=(int)$value;
  if($value!=ApprovalValueAccept && $value!=ApprovalValueDelete) $value=ApprovalValueReject;
  $selected=array(
    ApprovalValueAccept=>"",
    ApprovalValueReject=>"",
    ApprovalValueDelete=>"",
    $value=>" checked=\"checked\"");
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"".ApprovalValueAccept."\"".$selected[ApprovalValueAccept]."> $language[admin_accept]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"".ApprovalValueReject."\"".$selected[ApprovalValueReject]."> $language[admin_reject]&nbsp;\r\n";
  echo "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"".ApprovalValueDelete."\"".$selected[ApprovalValueDelete]."> $language[admin_delete]&nbsp;";
  makeFormItem("footer");
}

function makeFormUnknown($type, $title, $description, $name, $value="", $lang=true)
{
  global $language;
  makeFormItem("header",$title,$description,$lang);
  $message=format($language["admin_unknowncontrol"],htmlspecialchars($type));
  $value=htmlspecialchars($value);
  if($message!="") echo "$message<br>\r\n";
  echo "<input type=\"text\" name=\"$name\" value=\"$value\">";
  makeFormItem("footer");
}

function makeHiddenForm($script, $action, $params=array(), $hide=true)
{
  static $already=false;
  if(!$already) {
    echo "<script type=\"text/javascript\">\r\n";
    echo "function hiddenformsubmit(formid) { document.getElementById(formid).submit(); }\r\n";
    echo "</script>\r\n";
    $already=true;
  }
  $formid="hiddenform".getIncrementalValue("hiddenform");
  $anchor=preg_match("{#.*}",$script,$matches)?$matches[0]:"";
  $script=preg_replace("{#.*}","",$script);
  if($hide) echo "<div style=\"display:none\">";
  echo "<form id=\"$formid\" action=\"$script.php$anchor\" method=\"post\">\r\n";
  echo "<input type=\"hidden\" name=\"action\" value=\"$action\">\r\n";
  foreach($params as $param=>$value) {
    $param=htmlspecialchars($param);
    $value=htmlspecialchars($value);
    echo "<input type=\"hidden\" name=\"$param\" value=\"$value\">\r\n";
  }
  echo "</form>".($hide?"</div>":"")."\r\n";
  return "hiddenformsubmit('$formid')";
}

/****************************** Table Functions *******************************/

function makeTableScripts()
{
  static $already=false;
  if($already) return; else $already=true;
  echo "<script type=\"text/javascript\">\r\n";
  echo "function tableclick(formid, itemname, state)\r\n";
  echo "{\r\n";
  echo "  var formobj=document.getElementById(formid);\r\n";
  echo "  var checkbox1=document.getElementById(formid+\"top\");\r\n";
  echo "  var checkbox2=document.getElementById(formid+\"bottom\");\r\n";
  echo "  if(checkbox1 && typeof(checkbox1)!=\"undefined\") checkbox1.checked=state;\r\n";
  echo "  if(checkbox2 && typeof(checkbox2)!=\"undefined\") checkbox2.checked=state;\r\n";
  echo "  if(!formobj) return true;\r\n";
  echo "  var items=formobj.elements[itemname];\r\n";
  echo "  if(items && typeof(items)!=\"undefined\") {\r\n";
  echo "    if(typeof(items.length)!=\"undefined\")\r\n";
  echo "      for(index=0; index<items.length; index++) items[index].checked=state;\r\n";
  echo "      else items.checked=state;\r\n";
  echo "  }\r\n";
  echo "  return true;\r\n";
  echo "}\r\n";
  echo "</script>\r\n";
}

function makeTable($what, $columns=array(), $script="", $action="", $lang=true)
{
  global $language, $controlState;
  if($what=="header" || $what=="headeraction") {
    makeTableScripts();
    $width=ifset($controlState["table"]["width"],"95%");
    $width=$width?" width=\"$width\"":"";
    echo "<div align=\"center\"><table class=\"table\" align=\"center\"$width>\r\n";
    $controlState["table"]["columns"]=0;
    $controlState["table"]["checkbox"]=array();
    $controlState["table"]["line"]=0;
    if($script!="") $controlState["table"]["form"]=true;
  }
  if(($what=="header" || $what=="headeraction") && isset($controlState["table"]["form"])) {
    $formid="action".getIncrementalValue("action");
    $anchor=preg_match("{#.*}",$script,$matches)?$matches[0]:"";
    $script=preg_replace("{#.*}","",$script);
    if($script!="") $script=" action=\"$script.php$anchor\"";
    echo "<form id=\"$formid\"$script method=\"post\">\r\n";
    if($action!="") echo "<input type=\"hidden\" name=\"action\" value=\"$action\">\r\n";
    $controlState["table"]["id"]=$formid;
  }
  if($what=="header" || $what=="headeraction") {
    echo "<tr class=\"header\">";
    if(!is_array($columns)) $columns=array($columns);
    $separator="";
    foreach($columns as $column) {
      if(!is_array($column)) $column=array("title"=>$column);
      $title=$column["title"];
      $colspan=ifset($column["colspan"],1);
      if(substr($title,0,6)=="table:") {
        $parts=explode(":",$title);
        switch($parts[1]) {
          case "checkbox":
            $formid=ifset($controlState["table"]["id"],"unknown");
            $controlState["table"]["checkbox"][$controlState["table"]["columns"]]=$parts[2];
            $content="<input class=\"checkbox\" id=\"{$formid}top\" type=\"checkbox\" onclick=\"tableclick('$formid','$parts[2]',this.checked)\">";
            break;
        }
      }
      else $content=$lang?$language[$title]:formatAdminText($title);
      if(isset($column["link"])) {
        $selected=isset($column["selected"]) && $column["selected"];
        $class=$selected?" class=\"selected\"":"";
        $content="<a$class href=\"$column[link]\">$content</a>";
      }
      $controlState["table"]["columns"]+=$colspan;
      $colspan=$colspan>1?" colspan=\"$colspan\"":"";
      $width=isset($column["width"])?" width=\"$column[width]\"":"";
      $align=ifset($column["align"],"center");
      echo "$separator<td$colspan align=\"$align\"$width nowrap=\"nowrap\">$content</td>";
      $separator="\r\n";
    }
    echo "</tr>\r\n";
    $controlState["table"]["index"]=0;
  }
  if($what=="footer" && isset($controlState["table"]["form"])) {
    $colspan=$controlState["table"]["columns"];
    echo "<tr class=\"footer\"><td colspan=\"$colspan\" align=\"center\" nowrap=\"nowrap\">\r\n";
    echo "<table class=\"hidden\" align=\"center\">\r\n";
    echo "<tr><td><input class=\"button\" type=\"submit\" value=\"$language[admin_submit]\"></td>\r\n";
    echo "<td style=\"padding-left:5px\"><input class=\"button\" type=\"reset\" value=\"$language[admin_reset]\"></td></tr>\r\n";
    echo "</table></td></tr>\r\n";
    echo "</form>\r\n";
  }
  if($what=="footeraction") {
    $prompt=$columns;
    $actions=$script;
    $colspan=$controlState["table"]["columns"];
    $onchange=ifset($controlState["table"]["id"]);
    $onchange=($onchange && AdminTableActionAutostart)?" onchange=\"$onchange.submit()\"":"";
    echo "<tr class=\"footer action\"><td colspan=\"$colspan\" align=\"center\" nowrap=\"nowrap\">\r\n";
    echo "<table class=\"hidden\" align=\"center\">\r\n";
    echo "<tr><td>$prompt</td>\r\n";
    echo "<td style=\"padding-left:5px\"><select class=\"actselect\" name=\"action\"$onchange>\r\n";
    foreach($actions as $key=>$action) {
      $key=htmlspecialchars($key);
      echo "<option value=\"$key\">$action</option>\r\n";
    }
    echo "</select></td>\r\n";
    echo "<td style=\"padding-left:5px\"><input class=\"actbutton\" type=\"submit\" value=\"$language[admin_go]\"></td></tr>\r\n";
    echo "</table></td></tr>\r\n";
    echo "</form>\r\n";
  }
  if($what=="footer" || $what=="footeraction") {
    echo "</table></div>\r\n";
    unset($controlState["table"]);
  }
}

function makeTableAction($what, $columns=array(), $script="", $prompt="", $actions=array(), $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    makeTable("headeraction",$columns,$script,"",$lang);
    if($prompt=="") $prompt=$language["admin_action"];
      else $prompt=$lang?$language[$prompt]:formatAdminText($prompt);
    foreach($actions as $key=>$action)
      $actions[$key]=$lang?$language[$action]:formatAdminText($action);
    $controlState["table"]["prompt"]=$prompt;
    $controlState["table"]["actions"]=$actions;
  }
  if($what=="footer") {
    $prompt=$controlState["table"]["prompt"];
    $actions=$controlState["table"]["actions"];
    makeTable("footeraction",$prompt,$actions);
  }
}

function makeTableArrangement($what)
{
  global $controlState;
  if($what=="header") {
    $columns=$controlState["table"]["columns"];
    $width=floor(100/$columns)."%";
    echo "<tr class=\"header arrange\">";
    for($index=1; $index<$columns; $index++)
      echo "<td style=\"height:0px;padding:0px\" width=\"$width\"></td>\r\n";
    echo "<td style=\"height:0px;padding:0px\"></td></tr>\r\n";
  }
  if($what=="footer") {
    while($controlState["table"]["index"]) makeTableCellSimple();
  }
}

function makeTableTotals($columns=array(), $firstnote=true)
{
  global $language, $controlState;
  if(!is_array($columns)) $columns=array($columns);
  $columns=array_values($columns);
  foreach($columns as $index=>$column)
    if(!is_array($column)) $columns[$index]=array("value"=>$column);
  if(count($columns)==1 && count($columns[0])==1) {
    $colspan=$controlState["table"]["columns"];
    while($colspan>=2 && isset($controlState["table"]["checkbox"][$colspan-1])) $colspan--;
    $columns[0]["colspan"]=$colspan;
  }
  echo "<tr class=\"footer totals\">";
  $separator="";
  foreach($columns as $index=>$column) {
    if(!is_array($column)) $column=array("value"=>$column);
    $value=formatAdminText($column["value"]);
    if(!$index && $firstnote) {
      $value=$value==""?$language["admin_total"]:
        format($language["admin_totalvalue"],$value);
      $column["align"]="left";
    }
    $colspan=ifset($column["colspan"],1);
    $controlState["table"]["index"]+=$colspan;
    $colspan=$colspan!=1?" colspan=\"$colspan\"":"";
    $width=isset($column["width"])?" width=\"$column[width]\"":"";
    $align=ifset($column["align"],"right");
    echo "$separator<td$colspan align=\"$align\"$width nowrap=\"nowrap\">$value</td>";
    $separator="\r\n";
  }
  $rowsleft=0;
  for($index=$controlState["table"]["index"]; $index<$controlState["table"]["columns"]; $index++)
    if(isset($controlState["table"]["checkbox"][$index])) {
      $formid=ifset($controlState["table"]["id"],"unknown");
      $itemname=$controlState["table"]["checkbox"][$index];
      $content="<input class=\"checkbox\" id=\"{$formid}bottom\" type=\"checkbox\" onclick=\"tableclick('$formid','$itemname',this.checked)\">";
      $colspan=$rowsleft>1?" colspan=\"$rowsleft\"":"";
      if($rowsleft) { echo "$separator<td$colspan></td>"; $separator="\r\n"; }
      echo "$separator<td align=\"center\" nowrap=\"nowrap\">$content</td>";
      $rowsleft=0;
      $separator="\r\n";
    }
    else $rowsleft++;
  $colspan=$rowsleft>1?" colspan=\"$rowsleft\"":"";
  if($rowsleft) echo "$separator<td$colspan></td>";
  echo "</tr>\r\n";
  $controlState["table"]["index"]=0;
}

function makeTablePager($page, $total, $link, $all=true)
{
  global $language, $controlState;
  if($page===false || $total<2) return;
  $agent=stripSlashesSmart(ifset($_SERVER["HTTP_USER_AGENT"],""));
  $fancy=FancyNavigationEnabled && preg_match(FancyNavigationPattern,$agent);
  $class=$fancy?"navigation1":"navigation2";
  $symbols=explode(",",$fancy?FancyNavigationSymbols1:FancyNavigationSymbols2);
  $colspan=$controlState["table"]["columns"];
  $links=array(format($link,1),format($link,$page-1),
    format($link,$page+1),format($link,$total),format($link,"all"));
  $spacer=normalizeAdminPath(AdminSpacerLocation);
  $empty="<img width=\"40\" height=\"20\" src=\"$spacer\">";
  $buttons=array(
    "<input class=\"$class\" type=\"button\" value=\"$symbols[0]\" title=\"$language[admin_pages_first]\" onclick=\"document.location.href='$links[0]'\">",
    "<input class=\"$class\" type=\"button\" value=\"$symbols[1]\" title=\"$language[admin_pages_previous]\" onclick=\"document.location.href='$links[1]'\">",
    "<input class=\"$class\" type=\"button\" value=\"$symbols[2]\" title=\"$language[admin_pages_next]\" onclick=\"document.location.href='$links[2]'\">",
    "<input class=\"$class\" type=\"button\" value=\"$symbols[3]\" title=\"$language[admin_pages_last]\" onclick=\"document.location.href='$links[3]'\">");
  if($page<=1) $buttons[0]=$buttons[1]=$empty;
  if($page>=$total) $buttons[2]=$buttons[3]=$empty;
  $pages=array();
  for($index=1; $index<=$total; $index++) if($index!=$page)
    $pages[]="<a href=\"".format($link,$index)."\">$index</a>";
    else $pages[]="[$index]";
  if($all) $pages[]="<a href=\"$links[4]\">$language[admin_pages_all]</a>";
  $pages=implode("\r\n",$pages);
  echo "<tr class=\"footer pager\"><td colspan=\"$colspan\">\r\n";
  echo "<table class=\"hidden\" width=\"100%\">\r\n";
  echo "<tr><td style=\"padding-right:5px\">$buttons[0]</td>\r\n";
  echo "<td style=\"padding-right:5px\">$buttons[1]</td>\r\n";
  echo "<td align=\"center\" width=\"100%\">$language[admin_pages] $pages</td>\r\n";
  echo "<td style=\"padding-left:5px\">$buttons[2]</td>\r\n";
  echo "<td style=\"padding-left:5px\">$buttons[3]</td></tr>\r\n";
  echo "</table></td></tr>\r\n";
}

function makeTableCell($what, $style=array())
{
  global $controlState;
  if($what=="header") {
    if(!$controlState["table"]["index"]) {
      $class=(++$controlState["table"]["line"]%2)?"firstline":"secondline";
      echo "<tr class=\"$class\">";
    }
    $colspan=ifset($style["colspan"],1);
    $controlState["table"]["index"]+=$colspan;
    $colspan=$colspan>1?" colspan=\"$colspan\"":"";
    $align=ifset($style["align"],"left");
    $valign=ifset($style["valign"],"middle");
    $width=ifset($style["width"],"");
    $width=$width!=""?" width=\"$width\"":"";
    $wrap=isset($style["wrap"])?"":" nowrap=\"nowrap\"";
    echo "<td$colspan align=\"$align\" valign=\"$valign\"$width$wrap>";
  }
  if($what=="footer") {
    echo "</td>";
    if($controlState["table"]["index"]==$controlState["table"]["columns"]) {
      $controlState["table"]["index"]=0;
      echo "</tr>";
    }
    echo "\r\n";
  }
}

function makeTableCellExact($content="", $style=array("wrap"=>true))
{
  makeTableCell("header",$style);
  echo $content;
  makeTableCell("footer");
}

function makeTableCellSimple($title="", $style=array(), $lang=false)
{
  global $language;
  makeTableCell("header",$style);
  echo $lang?$language[$title]:formatAdminText($title);
  makeTableCell("footer");
}

function makeTableCellPattern($pattern)
{
  makeTableCell("header");
  $pattern=htmlspecialchars($pattern);
  echo "<font class=\"pattern\">$pattern</font>";
  makeTableCell("footer");
}

function makeTableCellYesNo($value)
{
  global $language;
  makeTableCell("header",array("align"=>"center"));
  $value=$value?"yes":"no";
  $title=$language["common_$value"];
  echo "<font class=\"$value\">$title</font>";
  makeTableCell("footer");
}

function makeTableCellTitle($title, $link=false, $style=array(), $lang=false)
{
  makeTableCell("header",$style);
  makeLinksFormatted(array($title=>$link),"%s","title","title",false,$lang);
  makeTableCell("footer");
}

function makeTableCellLink($title, $link=false, $style=array(), $lang=false)
{
  makeTableCell("header",$style);
  makeLinksFormatted(array($title=>$link),"%s","","nolink",false,$lang);
  makeTableCell("footer");
}

function makeTableCellLinks($links=array(), $style=array("align"=>"center"), $linklang=true)
{
  makeTableCell("header",$style);
  makeLinksArray($links,$linklang);
  makeTableCell("footer");
}

function makeTableCellInput($name, $value="")
{
  makeTableCell("header");
  $value=htmlspecialchars($value);
  echo "<input class=\"cell\" type=\"text\" name=\"$name\" value=\"$value\">";
  makeTableCell("footer");
}

function makeTableCellInputOrder($name, $value="")
{
  makeTableCell("header");
  $value=htmlspecialchars($value);
  echo "<input class=\"order\" type=\"text\" name=\"$name\" value=\"$value\">";
  makeTableCell("footer");
}

function makeTableCellChooser($name, $options, $value=false, $filter=true)
{
  makeTableCell("header");
  echo "<select class=\"cell\" name=\"$name\">\r\n";
  if(!isset($options[$value])) $value=false;
  foreach($options as $key=>$option) {
    if($value===false) $value=$key;
    $selected=(string)$key===(string)$value?" selected=\"selected\"":"";
    $key=htmlspecialchars($key);
    if($filter) $option=formatAdminText($option);
    echo "<option value=\"$key\"$selected>$option</option>\r\n";
  }
  echo "</select>";
  makeTableCell("footer");
}

function makeTableCellCheckbox($name, $value=0)
{
  makeTableCell("header",array("align"=>"center"));
  $checked=$value?" checked=\"checked\"":"";
  echo "<input class=\"checkbox\" type=\"checkbox\" name=\"$name\" value=\"1\"$checked>";
  makeTableCell("footer");
}

function makeTableCellCheckboxArray($name, $value, $checked=false)
{
  makeTableCell("header",array("align"=>"center"));
  $value=htmlspecialchars($value);
  $checked=$checked?" checked=\"checked\"":"";
  echo "<input class=\"checkbox\" type=\"checkbox\" name=\"$name\" value=\"$value\"$checked>";
  makeTableCell("footer");
}

function makeTableCellImage($image, $title="", $style=array("align"=>"center"))
{
  makeTableCell("header",$style);
  if($title!="") $title=" title=\"".filterText($title,true)."\"";
  echo "<img src=\"$image\"$title>";
  makeTableCell("footer");
}

function makeTableCellImageSize($width, $height, $image, $title="", $style=array("align"=>"center"))
{
  makeTableCell("header",$style);
  if($title!="") $title=" title=\"".filterText($title,true)."\"";
  echo "<img width=\"$width\" height=\"$height\" src=\"$image\"$title>";
  makeTableCell("footer");
}

/******************************* Tree Functions *******************************/

function makeTree($what, $title="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $title=$lang?$language[$title]:formatAdminText($title);
    echo "<div align=\"center\"><table class=\"tree\" align=\"center\" width=\"95%\">\r\n";
    echo "<tr class=\"header\"><td>$title</td></tr>\r\n";
    $controlState["tree"]=0;
  }
  if($what=="footer") {
    echo "</table></div>\r\n";
    unset($controlState["tree"]);
  }
}

function makeTreeGroup($what)
{
  global $controlState;
  if($what=="header") {
    $class=(++$controlState["tree"]%2)?"firstline":"secondline";
    echo "<tr class=\"$class\"><td>\r\n";
  }
  if($what=="separator") {
    echo "<ul>\r\n";
  }
  if($what=="footer") {
    echo "</ul></td></tr>\r\n";
  }
}

function makeTreeGroupSimple($what, $title="", $links=array(), $lang=false, $linklang=true)
{
  global $language;
  makeTreeGroup($what);
  if($what=="header") {
    if($title!="") $title=$lang?$language[$title]:formatAdminText($title);
    if($title!="") echo "<font class=\"title\">$title</font> ";
    makeLinksArray($links,$linklang);
    makeTreeGroup("separator");
  }
}

function makeTreeItem($what)
{
  if($what=="header") {
    echo "<li>";
  }
  if($what=="footer") {
    echo "</li>\r\n";
  }
}

function makeTreeItemSimple($title="", $links=array(), $lang=false, $linklang=true)
{
  global $language;
  makeTreeItem("header");
  if($title!="") $title=$lang?$language[$title]:formatAdminText($title);
  if($title!="") echo "$title ";
  makeLinksArray($links,$linklang);
  makeTreeItem("footer");
}

/*************************** Complex Tree Functions ***************************/

function makeComplexTreeScripts()
{
  static $already=false;
  if($already) return; else $already=true;
  $script=basename($_SERVER["PHP_SELF"]);
  $plusimage="$script?action=image&image=treeplus";
  $minusimage="$script?action=image&image=treeminus";
  echo "<style type=\"text/css\">\r\n";
  echo "img.complextreeplus { background-image:url($plusimage); cursor:pointer; }\r\n";
  echo "img.complextreeminus { background-image:url($minusimage); cursor:pointer; }\r\n";
  echo "div.complextreevisible { display:block; }\r\n";
  echo "div.complextreehidden { display:none; }\r\n";
  echo "</style>\r\n";
  echo "<script type=\"text/javascript\">\r\n";
  echo "var complextreedata=new Array();\r\n";
  echo "function complextreeclick(id)\r\n";
  echo "{\r\n";
  echo "  var obj=document.getElementById(\"complextreeswitch\"+id);\r\n";
  echo "  if(obj) if(obj.className==\"complextreeplus\")\r\n";
  echo "    obj.className=\"complextreeminus\";\r\n";
  echo "    else obj.className=\"complextreeplus\";\r\n";
  echo "  var obj=document.getElementById(\"complextreeblock\"+id);\r\n";
  echo "  if(obj) if(obj.className==\"complextreevisible\")\r\n";
  echo "    obj.className=\"complextreehidden\";\r\n";
  echo "    else obj.className=\"complextreevisible\";\r\n";
  echo "}\r\n";
  echo "function complextreeaction(action, treeindex)\r\n";
  echo "{\r\n";
  echo "  if(!treeindex) treeindex=0;\r\n";
  echo "  var ids=complextreedata[treeindex];\r\n";
  echo "  for(var id=ids[0]; id<=ids[1]; id++) if(id) {\r\n";
  echo "    var obj=document.getElementById(\"complextreeswitch\"+id);\r\n";
  echo "    if(action==\"expand\" && obj && obj.className==\"complextreeplus\") complextreeclick(id);\r\n";
  echo "    if(action==\"contract\" && obj && obj.className==\"complextreeminus\") complextreeclick(id);\r\n";
  echo "  }\r\n";
  echo "}\r\n";
  echo "</script>\r\n";
}

function makeComplexTreeContent($nodes, $flags=array())
{
  global $controlState;
  $script=basename($_SERVER["PHP_SELF"]);
  $width=$controlState["complextreewidth"];
  $height=$controlState["complextreeheight"];
  $spacer=normalizeAdminPath(AdminSpacerLocation);
  foreach($nodes as $index=>$node) {
    $complex=count($node["items"]);
    if($complex) {
      $nodeid=getIncrementalValue("complextree");
      $controlState["complextreerange"]+=array("min"=>$nodeid,"max"=>$nodeid);
      $controlState["complextreerange"]["min"]=
        min($controlState["complextreerange"]["min"],$nodeid);
      $controlState["complextreerange"]["max"]=
        max($controlState["complextreerange"]["max"],$nodeid);
    }
    if($index) echo "\r\n";
    echo "<table class=\"hidden\">\r\n";
    echo "<tr>";
    foreach($flags as $flag) {
      $image=$flag?"$script?action=image&image=treeud":$spacer;
      echo "<td><img width=\"$width\" height=\"$height\" src=\"$image\"></td>\r\n";
    }
    $image="";
    if(count($flags) || $index) $image.="u";
    if($index<count($nodes)-1) $image.="d";
    if(count($flags) || count($nodes)>1 || $complex) $image.="r";
    $image=$image!=""?"$script?action=image&image=tree$image":$spacer;
    if($complex) {
      $halfheight1=max(floor(($height-10)/2),0);
      $halfheight2=max($height-$halfheight1-10,0);
      $class=$node["expand"]?"complextreeminus":"complextreeplus";
      echo "<td align=\"center\" background=\"$image\">";
      echo "<img width=\"$width\" height=\"$halfheight1\" src=\"$spacer\"><br>\r\n";
      echo "<img class=\"$class\" id=\"complextreeswitch$nodeid\" width=\"10\" height=\"10\" src=\"$spacer\" onclick=\"complextreeclick($nodeid)\"><br>\r\n";
      echo "<img width=\"$width\" height=\"$halfheight2\" src=\"$spacer\"></td>\r\n";
    }
    else echo "<td><img width=\"$width\" height=\"$height\" src=\"$image\"></td>\r\n";
    echo "<td><img width=\"$width\" height=\"$height\" src=\"$node[icon]\"></td>\r\n";
    echo "<td style=\"padding-left:5px\" nowrap=\"nowrap\">$node[header]</td></tr>\r\n";
    echo "</table>";
    if($complex) {
      $class=$node["expand"]?"complextreevisible":"complextreehidden";
      echo "\r\n<div class=\"$class\" id=\"complextreeblock$nodeid\">\r\n";
      $subflags=$flags;
      $subflags[]=$index<count($nodes)-1;
      makeComplexTreeContent($node["items"],$subflags);
      echo "</div>";
    }
  }
}

function makeComplexTreeSize($what, $width=0, $height=0, $title="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    makeComplexTreeScripts();
    $title=$lang?$language[$title]:formatAdminText($title);
    echo "<div align=\"center\"><table class=\"tree\" align=\"center\" width=\"95%\">\r\n";
    echo "<tr class=\"header\"><td>$title</td></tr>\r\n";
    echo "<tr class=\"firstline\"><td>";
    $controlState["complextree"]=array();
    $controlState["complextreewidth"]=$width;
    $controlState["complextreeheight"]=$height;
    $controlState["complextreedepth"]=0;
    $controlState["complextreerange"]=array();
    if(!isset($controlState["complextreeindex"])) $controlState["complextreeindex"]=0;
  }
  if($what=="footer") {
    makeComplexTreeContent($controlState["complextree"]);
    $range=$controlState["complextreerange"]+array("min"=>0,"max"=>0);
    echo "</td></tr>\r\n";
    echo "</table></div>\r\n";
    echo "<script type=\"text/javascript\">";
    echo "complextreedata[$controlState[complextreeindex]]=[$range[min],$range[max]];</script>\r\n";
    unset($controlState["complextree"]);
    unset($controlState["complextreewidth"]);
    unset($controlState["complextreeheight"]);
    unset($controlState["complextreedepth"]);
    unset($controlState["complextreerange"]);
    $controlState["complextreeindex"]++;
  }
}

function makeComplexTree($what, $title="", $lang=true)
{
  $width=ComplexTreeDefaultWidth;
  $height=ComplexTreeDefaultHeight;
  makeComplexTreeSize($what,$width,$height,$title,$lang);
}

function makeComplexTreeNode($what, $icon="", $header="", $expand=false)
{
  global $controlState;
  if($what=="header") {
    $locator=&$controlState["complextree"];
    for($index=0; $index<$controlState["complextreedepth"]; $index++) {
      if($expand) $locator[count($locator)-1]["expand"]=true;
      $locator=&$locator[count($locator)-1]["items"];
    }
    $items=array();
    $locator[]=compact("icon","header","expand","items");
    $controlState["complextreedepth"]++;
  }
  if($what=="footer") {
    $controlState["complextreedepth"]--;
  }
}

function makeComplexTreeNodeSimple($what, $icon="", $title="", $links=array(), $expand=false, $lang=false, $linklang=true)
{
  global $language;
  if($what=="header") {
    ob_start();
    if($title!="") $title=$lang?$language[$title]:formatAdminText($title);
    if($title!="") echo "$title ";
    makeLinksArray($links,$linklang);
    $header=ob_get_clean();
  }
  else $header="";
  makeComplexTreeNode($what,$icon,$header,$expand);
}

/****************************** Prompt Functions ******************************/

function makePrompt($title, $link, $lang=true)
{
  global $language;
  $title=$lang?$language[$title]:formatAdminText($title);
  echo "<div align=\"center\"><table class=\"prompt\" align=\"center\" width=\"95%\">\r\n";
  echo "<tr class=\"header\"><td>$title</td></tr>\r\n";
  echo "<tr class=\"footer\"><td align=\"center\" nowrap=\"nowrap\">\r\n";
  echo "<table class=\"hidden\" align=\"center\"><tr>\r\n";
  echo "<td><input class=\"button\" type=\"button\" value=\"$language[common_yes]\" onclick=\"document.location.href='$link'\"></td>\r\n";
  echo "<td style=\"padding-left:5px\"><input class=\"button\" type=\"button\" value=\"$language[common_no]\" onclick=\"history.back(1)\"></td>\r\n";
  echo "</tr></table></td></tr>\r\n";
  echo "</table></div>\r\n";
}

function makePromptForm($what, $title="", $script="", $action="", $lang=true)
{
  global $language, $controlState;
  if($what=="header") {
    $controlState["form"]=0;
    $controlState["formid"]="form".getIncrementalValue("form");
    $title=$lang?$language[$title]:formatAdminText($title);
    $anchor=preg_match("{#.*}",$script,$matches)?$matches[0]:"";
    $script=preg_replace("{#.*}","",$script).".php";
    echo "<div align=\"center\"><table class=\"prompt\" align=\"center\" width=\"95%\">\r\n";
    echo "<form id=\"$controlState[formid]\" action=\"$script$anchor\" method=\"post\">\r\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"$action\">\r\n";
    echo "<tr class=\"header\"><td colspan=\"2\">$title</td></tr>\r\n";
  }
  if($what=="footer") {
    echo "<tr class=\"footer\"><td colspan=\"2\" align=\"center\" nowrap=\"nowrap\">\r\n";
    echo "<table class=\"hidden\" align=\"center\"><tr>\r\n";
    echo "<td><input class=\"button\" type=\"submit\" value=\"$language[common_yes]\"></td>\r\n";
    echo "<td style=\"padding-left:5px\"><input class=\"button\" type=\"button\" value=\"$language[common_no]\" onclick=\"history.back(1)\"></td>\r\n";
    echo "</tr></table></td></tr>\r\n";
    echo "</form>\r\n";
    echo "</table></div>\r\n";
    unset($controlState["form"]);
    unset($controlState["formid"]);
  }
}

/************************** Miscellaneous Functions ***************************/

function makeLinksFormatted($links, $format, $class1, $class2, $wrap, $linklang)
{
  global $language;
  if($class1!="") $class1=" class=\"$class1\"";
  if($class2!="") $class2=" class=\"$class2\"";
  $parts=array();
  foreach($links as $key=>$link) {
    if($link===true) { $parts[]="<br>"; continue; }
    $key=format($format,$linklang?$language[$key]:formatAdminText($key));
    if($link===false) { $parts[]="<nobr><font$class2>$key</font></nobr>"; continue; }
    $target="";
    if(preg_match("{^(?!javascript:)(\w+):(.*)}",$link,$matches)) {
      $target=" target=\"$matches[1]\"";
      $link=$matches[2];
    }
    $parts[]="<nobr><a$class1 href=\"$link\"$target>$key</a></nobr>";
  }
  $content=implode(" ",$parts);
  $content=str_replace(" <br> ","<br>\r\n",$content);
  echo $content;
}

function makeLinksArray($links, $linklang=true)
{
  makeLinksFormatted($links,"[%s]","","nolink",false,$linklang);
}

function makeLinks($links, $linklang=true)
{
  echo "<div align=\"center\">";
  makeLinksArray($links,$linklang);
  echo "</div>\r\n";
}

function makeRefreshMenuLink()
{
  makeLinks(array("admin_refreshmenu"=>"_top:."));
}

function makeBreak($count=1)
{
  echo str_repeat("<br>",$count)."\r\n";
}

function makeSeparator($breaks=true)
{
  $breaks=$breaks?"<br>":"";
  echo "$breaks<hr>$breaks\r\n";
}

function makeNotification($title, $params=array(), $lang=true)
{
  global $language;
  $title=$lang?$language[$title]:formatAdminText($title);
  if(!is_array($params)) $params=array($params);
  foreach($params as $index=>$param) $params[$index]=formatAdminText($param);
  $message=format($title,$params);
  echo "$message<br>\r\n";
}

function makeWarning($title, $params=array(), $lang=true)
{
  global $language;
  $title=$lang?$language[$title]:formatAdminText($title);
  if(!is_array($params)) $params=array($params);
  foreach($params as $index=>$param) $params[$index]=formatAdminText($param);
  $message=format($title,$params);
  $message=format($language["admin_warning"],$message);
  echo "$message<br>\r\n";
}

function makeError($title, $params=array(), $lang=true)
{
  global $language;
  $title=$lang?$language[$title]:formatAdminText($title);
  if(!is_array($params)) $params=array($params);
  foreach($params as $index=>$param) $params[$index]=formatAdminText($param);
  $message=format($title,$params);
  $message=format($language["admin_error"],$message);
  echo "$message<br>\r\n";
}

function makeAdminError($title, $params=array(), $lang=true)
{
  makeError($title,$params,$lang);
  makeAdminPage("footer");
}

function makeWrongDBError()
{
  global $database;
  makeError("admin_error_wrongdb",$database->title);
}

function makeTodo()
{
  makeNotification("admin_error_todo");
}

function makeAnchor($name)
{
  echo "<a name=\"$name\"></a>";
}

function makeHeadline($title, $lang=true)
{
  global $language;
  $title=$lang?$language[$title]:formatAdminText($title);
  echo "<div class=\"headline\" align=\"center\">$title</div>\r\n";
}

function makeFormattedText($text)
{
  echo "<hr class=\"formatted\">\r\n";
  echo "<div class=\"formatted\"><pre>$text</pre></div>\r\n";
  echo "<hr class=\"formatted\">\r\n";
}

function makeQuote($text)
{
  $text=htmlspecialchars($text);
  echo "<div class=\"quote\"><pre>$text</pre></div>\r\n";
}

function makeImage($mimetype, $content)
{
  outputErase(true);
  @header("Cache-Control: max-age=".OneYear.", private");
  @header("Pragma: cache");
  @header("Content-Type: $mimetype");
  @header("Content-Length: ".strlen($content));
  echo $content;
  halt();
}

function makeNotificationScript($title, $delay=1000, $lang=true)
{
  global $language;
  $title=quoteText($lang?$language[$title]:$title);
  $function="response".getIncrementalValue("response");
  echo "<script type=\"text/javascript\">\r\n";
  echo "function $function() { alert($title); }\r\n";
  echo "window.setTimeout(\"$function()\",$delay);\r\n";
  echo "</script>\r\n";
}

function makeConfirmationScript($title, $link, $delay=1000, $lang=true)
{
  global $language;
  $title=quoteText($lang?$language[$title]:$title);
  $link=quoteText($link);
  $function="response".getIncrementalValue("response");
  echo "<script type=\"text/javascript\">\r\n";
  echo "function $function()\r\n";
  echo "{\r\n";
  echo "  if(!confirm($title)) return;\r\n";
  echo "  var doc=window.frames.top?window.frames.top.main.document:false;\r\n";
  echo "  if(doc) doc.location.href=$link;\r\n";
  echo "}\r\n";
  echo "window.setTimeout(\"$function()\",$delay);\r\n";
  echo "</script>\r\n";
}

function makeRedirectScript($link, $delay=1500)
{
  $link=quoteText($link);
  $function="response".getIncrementalValue("response");
  echo "<script type=\"text/javascript\">\r\n";
  echo "function $function() { document.location.href=$link; }\r\n";
  echo "window.setTimeout(\"$function()\",$delay);\r\n";
  echo "</script>\r\n";
}

?>
