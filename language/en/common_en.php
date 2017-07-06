<?php

$language["locale"]="en";
$language["charset"]="windows-1252";
$language["charset_sql"]="latin1";
$language["charset_iconv"]="cp1252";
$language["charset_saved"]="cp1252";

$language["charset_uppers"]="ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ";
$language["charset_lowers"]="abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþ";
$language["charset_regexp"]="A-Za-zÀ-Öà-öØ-Þø-þ";
$language["charset_regexp_uppers"]="A-ZÀ-ÖØ-Þ";
$language["charset_regexp_lowers"]="a-zà-öø-þ";
$language["charset_subst1"]="ÀàÁáÂâÃãÄäÅåÆæÇçÈèÉéÊêËëÌìÍíÎîÏïÐðÑñÒòÓóÔôÕõÖöØøÙùÚúÛûÜüÝý";
$language["charset_subst2"]="AaAaAaAaAaAaAaCcEeEeEeEeIiIiIiIiDdNnOoOoOoOoOoOoUuUuUuUuYy";

$language["common_yes"]="Yes";
$language["common_no"]="No";
$language["common_any"]="Any";
$language["common_null"]="N/A";
$language["common_today"]="Today";
$language["common_yesterday"]="Yesterday";
$language["common_tomorrow"]="Tomorrow";
$language["common_on"]="On";
$language["common_off"]="Off";

$language["weekday"][0]="Sunday";
$language["weekday"][1]="Monday";
$language["weekday"][2]="Tuesday";
$language["weekday"][3]="Wednesday";
$language["weekday"][4]="Thursday";
$language["weekday"][5]="Friday";
$language["weekday"][6]="Saturday";

$language["weekday_short"][0]="Sun";
$language["weekday_short"][1]="Mon";
$language["weekday_short"][2]="Tue";
$language["weekday_short"][3]="Wed";
$language["weekday_short"][4]="Thu";
$language["weekday_short"][5]="Fri";
$language["weekday_short"][6]="Sat";

$language["month"][1]="January";
$language["month"][2]="February";
$language["month"][3]="March";
$language["month"][4]="April";
$language["month"][5]="May";
$language["month"][6]="June";
$language["month"][7]="July";
$language["month"][8]="August";
$language["month"][9]="September";
$language["month"][10]="October";
$language["month"][11]="November";
$language["month"][12]="December";

$language["month_gen"]=$language["month"];

$language["month_short"][1]="Jan";
$language["month_short"][2]="Feb";
$language["month_short"][3]="Mar";
$language["month_short"][4]="Apr";
$language["month_short"][5]="May";
$language["month_short"][6]="Jun";
$language["month_short"][7]="Jul";
$language["month_short"][8]="Aug";
$language["month_short"][9]="Sep";
$language["month_short"][10]="Oct";
$language["month_short"][11]="Nov";
$language["month_short"][12]="Dec";

$language["format_decimals"]=2;
$language["format_separator"]=".";
$language["format_thousands"]=",";

$language["format_datetime"]["date"]="F j, Y";
$language["format_datetime"]["time"]="h:ia";
$language["format_datetime"]["datetime"]="F j, Y, h:ia";

$language["fatal_title"]="PHPC Fatal Error:";
$language["fatal_error"]="Error Description:";
$language["fatal_query"]="Failed Query:";

$language["fatal_function"]="Call to undefined function %s(). Check your PHP configuration.";
$language["fatal_install"]="Some important tables are missing in your database. Make sure you installed all PHPC core plugins in the <a href=\"admin/\">Control Panel</a>.";
$language["fatal_connection"]="Database connection failure. Check your connection information (located in phpc/config.php).";
$language["fatal_wrongquery"]="Wrong database query.";
$language["fatal_constraints"]="Database integrity errors found.";
$language["fatal_recursion"]="Recursion found in the &quot;%s&quot; table.";
$language["fatal_nostyle"]="No appropriate style was found for showing the site.";
$language["fatal_no404"]="Page 404 not found. Create one in the Control Panel.";
$language["fatal_compile"]="Page compilation error.";
$language["fatal_filecache"]="Unable to save file cache entry on disk. Make sure that &quot;cache&quot; folder has write permissions.";
$language["fatal_gzip"]="Unable to start GZIP compression. Unexpected output in file &quot;%s&quot;, line %s.";
$language["fatal_users"]="Users and Access Rights plugin not installed.";
$language["fatal_useraccess"]="User access right &quot;memberaccess&quot; not found. You should add it manually.";

$language["fatal_reason_notemplate"]="Template \"%s\" not found - specified for page \"%s\".";
$language["fatal_reason_notemplateparent"]="Template \"%s\" not found - used for inheritance in template \"%s\".";
$language["fatal_reason_notemplateinsert"]="Template \"%s\" not found - used for insertion in template \"%s\", fragment \"%s\".";
$language["fatal_reason_nobundle"]="Bundle \"%s\" not found - specified for page \"%s\".";
$language["fatal_reason_phptag"]="Unclosed PHP tag found - template \"%s\", fragment \"%s\".";
$language["fatal_reason_area"]="Syntax error in \"area\" tag - template \"%s\", fragment \"%s\".";
$language["fatal_reason_areaalready"]="Duplicate \"area\" tag with the same parameter - template \"%s\", fragment \"%s\".";
$language["fatal_reason_areamissing"]="Missing opening \"area\" tag - template \"%s\", fragment \"%s\".";
$language["fatal_reason_areaunclosed"]="Missing closing \"area\" tag - template \"%s\", fragment \"%s\".";
$language["fatal_reason_areanoparent"]="Missing matching \"area\" tag - \"%s\" template's parent, fragment \"%s\".";
$language["fatal_reason_tag"]="Control tag syntax error - template \"%s\", fragment \"%s\".";
$language["fatal_reason_tagmissing"]="Missing opening control tag - template \"%s\", fragment \"%s\".";
$language["fatal_reason_tagunclosed"]="Missing closing control tag - template \"%s\", fragment \"%s\".";
$language["fatal_reason_varref"]="Variable used in \"var\" tag's parameters - template \"%s\", fragment \"%s\".";
$language["fatal_reason_unknownlogic"]="Unknown \"logic\" tag found - template \"%s\", fragment \"%s\".";
$language["fatal_reason_unknownwrite"]="Unknown \"write\" tag found - template \"%s\", fragment \"%s\".";

?>
