<?php

$language["admin_installer_title"]="PHPC Installer";
$language["admin_installer_header"]="<b>PHP Compiler Installation Script</b>";
$language["admin_installer_firststep"]="Return to the First Step";
$language["admin_installer_nextstep"]="Proceed to the Next Step";
$language["admin_installer_admin"]="Proceed to the Control Panel";
$language["admin_installer_intro"]="Welcome! This will help you to install and configure PHPC.<br>\r\nPlease notice that PHPC requires PHP version 4.3.0 or higher and empty database.";
$language["admin_installer_already"]="Sorry, installation already finished and cannot be repeated.";
$language["admin_installer_notice"]="<b>Notice</b>: Don't forget to delete &quot;install&quot; folder with all its contents. It's not needed anymore.";

$language["admin_installer_input"]="Please fill in the following form.";
$language["admin_installer_inputform"]="Configuration Data";
$language["admin_installer_inputadmin"]="Control Panel Password:";
$language["admin_installer_inputadmindesc"]="You can use this password (it was generated randomly) or type your own. Remember the Control Panel password or write it down somewhere. Do not reveal this password to anyone. Remember that anyone who knows this password will have full access to your project!";
$language["admin_installer_inputhost"]="Database Host:";
$language["admin_installer_inputhostdesc"]="Host address where database is located. You can specify both hostname and port number separated by colon, e.g. localhost:3306.";
$language["admin_installer_inputdb"]="Database Name:";
$language["admin_installer_inputdbdesc"]="If the database doesn't exist yet, you have to create it manually now.";
$language["admin_installer_inputuser"]="Database Username:";
$language["admin_installer_inputuserdesc"]="Username used then connecting to the database.";
$language["admin_installer_inputpass"]="Database Password:";
$language["admin_installer_inputpassdesc"]="Password for this database user.";
$language["admin_installer_inputprefix"]="Database Table Prefix:";
$language["admin_installer_inputprefixdesc"]="Specify an unique prefix for each PHPC project, if you are going to install several projects into the same database.";
$language["admin_installer_inputencode"]="Encryption Key:";
$language["admin_installer_inputencodedesc"]="Used for data encryption and decryption. Press F5 to generate another key.";
$language["admin_installer_inputlocale"]="Language of Your Project:";
$language["admin_installer_inputlocaledesc"]="You can switch project to your language now or create multilanguage website.";
$language["admin_installer_inputlocaleall"]="Multilanguage Project";
$language["admin_installer_inputunicode"]="Enable Unicode Support?";
$language["admin_installer_inputunicodedesc"]="&quot;Yes&quot; means that all data will be stored in UTF-8 multibyte character set. &quot;No&quot; means that more efficient single-byte character set will be used instead.";

$language["admin_installer_check"]="Database connection test...";
$language["admin_installer_checknodb"]="Unfortunately, your server doesn't seem to have database support at all. If you are using MySQL, you need to enable mysqli_xxx (MySQL Improved) functions in PHP.";
$language["admin_installer_checksuccess"]="Connection successful!";
$language["admin_installer_checkfailure"]="<b>Warning</b>: Unable to connect to the database. You have to go back and check configuration data.";
$language["admin_installer_checkreport"]="Please check out all parameters once again:";
$language["admin_installer_checkadmin"]="Control Panel Password: <b>%s</b>";
$language["admin_installer_checkhost"]="Database Host: <b>%s</b>";
$language["admin_installer_checkdb"]="Database Name: <b>%s</b>";
$language["admin_installer_checkuser"]="Database Username: <b>%s</b>";
$language["admin_installer_checkpass"]="Database Password: <b>%s</b>";
$language["admin_installer_checkprefix"]="Database Table Prefix: <b>%s</b>";
$language["admin_installer_checkprompt"]="Proceed and save these parameters in the configuration file?";

$language["admin_installer_savesuccess"]="Creating PHPC configuration file... Done!<br>\r\nAll you need now to complete the installation is to enter the Control Panel and install standard plugins.";
$language["admin_installer_savefailure"]="<b>Warning</b>: Unable to create configuration file. Maybe <b>phpc</b> folder does not have write permissions.<br>\r\nPlease check the ability to write in this folder, then go back and try again.";

/******************************************************************************/

$language["admin_auth_prompt"]="Control Panel Password:";
$language["admin_auth_promptmember"]="Enter your Username and Password:";
$language["admin_auth_submit"]="Submit";
$language["admin_auth_noreferer"]="Control Panel locked (possible attack attempt). The requested page doesn't have a referer.";
$language["admin_auth_wrongreferer"]="Control Panel locked (possible attack attempt). The requested page has invalid referer (%s).";

$language["admin_upgrade_menu"]="Upgrade";
$language["admin_upgrade_upgrade"]="Upgrade PHPC";
$language["admin_upgrade_prompt"]="It seems that your database needs to be upgraded to match the latest version of PHPC scripts. Do you want to proceed? (It will affect only few internal tables.)";
$language["admin_upgrade_start"]="Upgrading PHPC tables...";
$language["admin_upgrade_success"]="PHPC tables upgrade complete!";

$language["admin_title"]="PHPC Control Panel";
$language["admin_home"]="Control Panel Home";
$language["admin_opensite"]="Open Website in New Window";
$language["admin_logout"]="Leave Control Panel";
$language["admin_welcome"]="<b>Welcome to the PHP Compiler Control Panel!</b><br><br>\r\nHere you can easily manage any of your website's features. Please select appropriate option from the left-side menu.";
$language["admin_welcomemember"]="<b>Welcome to the Control Panel, %s!</b><br><br>\r\nHere you can easily manage any of website's features. Please select appropriate option from the left-side menu.";
$language["admin_nolanguage"]="Choose Automatically";
$language["admin_noframes"]="Please enable frames and reload the page.";

$language["admin_submit"]="Submit";
$language["admin_update"]="Update";
$language["admin_reset"]="Reset";
$language["admin_action"]="Selected items:";
$language["admin_go"]="Go!";
$language["admin_accept"]="Accept";
$language["admin_reject"]="Skip";
$language["admin_delete"]="Delete";
$language["admin_treeonce"]="All Items";
$language["admin_treemany"]="Other Items";
$language["admin_tree_expandall"]="Expand All";
$language["admin_tree_contractall"]="Contract All";
$language["admin_pages"]="Pages:";
$language["admin_pages_all"]="All";
$language["admin_pages_first"]="First Page";
$language["admin_pages_last"]="Last Page";
$language["admin_pages_previous"]="Previous Page";
$language["admin_pages_next"]="Next Page";
$language["admin_total"]="Total";
$language["admin_totalvalue"]="Total: %s";
$language["admin_allowcodes"]="You can use standard BB codes.";
$language["admin_allowhtml"]="You can use HTML tags.";
$language["admin_unknowncontrol"]="<b>Warning:</b> Unknown control element (%s).";
$language["admin_refreshmenu"]="Refresh Main Menu";

/******************************************************************************/

$language["admin_toolbar1"]="Useful Functions";
$language["admin_toolbar2"]="Other Functions";
$language["admin_toolbar_php"]="PHP Function Lookup:";
$language["admin_toolbar_mysql"]="MySQL Language Lookup:";
$language["admin_toolbar_links"]="Useful Links:";
$language["admin_toolbar_link1"]="PHP Compiler Official Site";
$language["admin_toolbar_link2"]="PHP Compiler Online Manual";
$language["admin_toolbar_link3"]="PHP Official Site";
$language["admin_toolbar_link4"]="PHP Online Manual";
$language["admin_toolbar_link5"]="MySQL Official Site";
$language["admin_toolbar_link6"]="MySQL Online Manual";
$language["admin_toolbar_language"]="Language Select:";
$language["admin_toolbar_langsuccess"]="Language selected!";
$language["admin_toolbar_skin"]="Control Panel Skin Select:";
$language["admin_toolbar_skindefault"]="Default";
$language["admin_toolbar_skinsuccess"]="Control Panel Skin selected!";

/******************************************************************************/

$language["admin_install"]="Install this Plugin";
$language["admin_installstart"]="Plugin installation started...";
$language["admin_installsuccess"]="Plugin installed successfully!";
$language["admin_installtable"]="Creating table &quot;%s&quot;... Done";
$language["admin_installalter"]="Altering table &quot;%s&quot;... Done";
$language["admin_installdrop"]="Dropping obsolete table &quot;%s&quot;... Done";
$language["admin_installdata"]="Adding data to the table &quot;%s&quot;... Done";
$language["admin_installchanges"]="Modifying table &quot;%s&quot;... Done";
$language["admin_installfolder_success"]="Creating folder &quot;%s&quot;... Done";
$language["admin_installfolder_failure"]="Creating folder &quot;%s&quot;... <b>Error!</b> You have to create this folder manually and grant write permissions to it.";
$language["admin_installoptions"]="Creating new options... Done";
$language["admin_installrelations"]="Creating new plugin relations... Done";
$language["admin_installpage"]="Adding page &quot;%s&quot;... Done";
$language["admin_installtemplate"]="Adding template &quot;%s&quot;... Done";
$language["admin_installbundle"]="Adding bundle &quot;%s&quot;... Done";
$language["admin_installreplacements"]="Creating new replacements... Done";
$language["admin_installformatting"]="Creating new formatting rules... Done";
$language["admin_installlinkstyles"]="Creating new link styles... Done";
$language["admin_installalterpage"]="Updating page &quot;%s&quot;... Done";
$language["admin_installaltertemplate"]="Updating template &quot;%s&quot;... Done";
$language["admin_installalterbundle"]="Updating bundle &quot;%s&quot;... Done";

$language["admin_upgrade"]="Upgrade this Plugin";
$language["admin_upgradestart"]="Plugin upgrade started...";
$language["admin_upgradesuccess"]="Plugin upgraded successfully!";

/******************************************************************************/

$language["admin_addlocale_form"]="Add Language";
$language["admin_addlocale_success"]="Language added!";
$language["admin_addlocale_locale"]="Choose a Language:";
$language["admin_addlocale_position"]="Where to Add:";
$language["admin_addlocale_method"]="How to Add:";
$language["admin_addlocale_first"]="To the beginning";
$language["admin_addlocale_after"]="After the language \"%s\"";
$language["admin_addlocale_empty"]="Create blank";
$language["admin_addlocale_copy"]="Copy from the language \"%s\"";

$language["admin_removelocale_prompt"]="Are you sure you want to delete this language? All associated texts will be deleted also!";
$language["admin_removelocale_success"]="Language deleted!";

$language["admin_modifylocales_locale"]="Language";
$language["admin_modifylocales_options"]="Options";
$language["admin_modifylocales_remove"]="Delete";
$language["admin_modifylocales_add"]="Add Language";

/******************************************************************************/

$language["admin_warning"]="<b>Warning</b>: %s";
$language["admin_error"]="<b>Error</b>: %s";

$language["admin_error_already"]="Plugin already installed.";
$language["admin_error_wrongdb"]="Sorry, your database (%s) is not supported by this plugin.";
$language["admin_error_structure"]="Structure errors found in table &quot;%s&quot;.";
$language["admin_error_todo"]="Sorry, this feature is not implemented yet.";
$language["admin_error_nousersstyle"]="No styles selected as default for website displaying. You should assign or create one.";
$language["admin_error_noadminstyle"]="No styles selected for editing in the Control Panel. You should assign or create one.";
$language["admin_error_nolocales"]="No installed languages found.";
$language["admin_error_nofreelocales"]="No available languages found.";

?>
