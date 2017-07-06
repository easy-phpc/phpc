<?php

$language["phpc_menu"]="PHPC Control Center";
$language["phpcp_menu"]="Website Pages";
$language["phpct_menu"]="Templates";
$language["phpcb_menu"]="Bundles";
$language["phpcr_menu"]="Replacements";
$language["phpcf_menu"]="Formatting Rules";
$language["phpcl_menu"]="Friendly Links";
$language["phpcs_menu"]="Styles";

$language["phpc_manual"]="Built-in Manual";
$language["phpc_statistics"]="Statistics";
$language["phpc_export"]="Export Tables";
$language["phpc_import"]="Import Tables";
$language["phpc_check"]="Integrity Check";
$language["phpc_clearcache"]="Clear Cache";
$language["phpc_add"]="Add";
$language["phpc_addgroup"]="Add Group";
$language["phpc_modify"]="Modify";
$language["phpc_addpair"]="Page + Template";
$language["phpc_languages"]="Languages";
$language["phpc_search"]="Search";
$language["phpc_replace"]="Search &amp; Replace";
$language["phpc_assign"]="Assign";
$language["phpc_addtemplateset"]="Add Template Set";
$language["phpc_addbundleset"]="Add Bundle Set";
$language["phpc_addreplacementset"]="Add Replacement Set";

$language["phpc_defstyle"]="General";
$language["phpc_deftemplate_general1"]="General Template";
$language["phpc_deftemplate_general2"]="All other templates which purpose is page displaying,";
$language["phpc_deftemplate_general3"]="you should inherit from this one, and overlap <area:content> area";
$language["phpc_deftemplate_general4"]="Sorry, this page is under construction.";
$language["phpc_deftemplate_index1"]="Main Page Template";
$language["phpc_deftemplate_index2"]="Congratulations with the successful installation of <b>PHP Compiler</b>!<br><br>\r\nThe message you're reading now is located in the <b>index</b> template.<br>\r\nThe website design is located in the <b>htmlDesign</b> template. You can alter it as you like.";
$language["phpc_deftemplate_404a"]="404 Error Template";
$language["phpc_deftemplate_404b"]="Sorry, the requested page not found.";
$language["phpc_deftemplate_htmldesign1"]="This template contains whole website design";
$language["phpc_deftemplate_htmldesign2"]="Central part (content) of the page marked as <var:content>";
$language["phpc_deftemplate_htmlstyles1"]="Here you can keep the style sheet of your project";
$language["phpc_deftemplate_htmlstyles2"]="This template automatically includes into htmlDesign template";
$language["phpc_deftemplate_htmlstandardstyles1"]="The style sheet of the standard error and redirect messages is stored here";
$language["phpc_deftemplate_htmlstandardstyles2"]="This template automatically includes into standardError and standardRedirect templates";
$language["phpc_deftemplate_standarderror1"]="Standard Error Template";
$language["phpc_deftemplate_standarderror2"]="Error";
$language["phpc_deftemplate_standarderror3"]="Back to the previous page";
$language["phpc_deftemplate_standardredirect1"]="Standard Redirect Template";
$language["phpc_deftemplate_standardredirect2"]="System Message";
$language["phpc_deftemplate_standardredirect3"]="Click here if you don't want to wait,<br>\r\nor if your browser doesn't support redirects";
$language["phpc_defbundle_general"]="General Bundle. Used for all pages of the project";
$language["phpc_defbundle_actiongeneral"]="General Controller";
$language["phpc_defpage_general"]="General Page";
$language["phpc_defpage_actiongeneral"]="General Controller";
$language["phpc_defpage_index"]="Main Page";
$language["phpc_defpage_404"]="404";

$language["phpc_defformatting_title1"]="Text Formatting";
$language["phpc_defformatting_title2"]="Link";
$language["phpc_defformatting_title3"]="E-mail Address";
$language["phpc_defformatting_sample1"]="[b]Bold[/b] text.\r\n[i]Italic[/i] text.\r\n[u]Underlined[/u] text.";
$language["phpc_defformatting_sample2"]="Check out our guestbook: [url]/guestbook[/url].\r\nThe most popular Simpsons Forum in Russia: [url]http://www.allsimpsons.ru[/url].\r\nOfficial PHPC site is located here: [url]www.phpc.ru[/url].\r\n\r\nCheck out our [url=/guestbook]guestbook[/url].\r\nThe most popular [url=http://www.allsimpsons.ru]Simpsons Forum[/url] in Russia.\r\nOfficial PHPC site is located [url=www.phpc.ru]here[/url].";
$language["phpc_defformatting_sample3"]="Post all questions and suggestions here: [email]dagdamor@phpc.ru[/email].\r\nPost all questions and suggestions [email=dagdamor@phpc.ru]here[/email].";

/******************************************************************************/

$language["phpc_stats_name"]="Parameter";
$language["phpc_stats_value"]="Value";
$language["phpc_stats_phpversion"]="PHP Version";
$language["phpc_stats_phpcversion"]="PHPC Version";
$language["phpc_stats_dbtype"]="Database Type";
$language["phpc_stats_dbversion"]="Database Version";
$language["phpc_stats_pages"]="Total Number of Pages";
$language["phpc_stats_templates"]="Total Number of Templates";
$language["phpc_stats_bundles"]="Total Number of Bundles";
$language["phpc_stats_replacements"]="Total Number of Replacements";
$language["phpc_stats_others"]="Number of Other Elements";
$language["phpc_stats_styles"]="Number of Styles";
$language["phpc_stats_diagnose"]="Check Server Configuration";
$language["phpc_stats_phpinfo"]="Show PHP Information";
$language["phpc_stats_conclusion"]="<b>Approximate Project Complexity</b>: %s";
$language["phpc_stats_conclusion1"]="Project is Under Construction.";
$language["phpc_stats_conclusion2"]="Simple Website.";
$language["phpc_stats_conclusion3"]="Complex Website.";
$language["phpc_stats_conclusion4"]="Interactive Portal.";
$language["phpc_stats_conclusion5"]="Something Amazing!";

$language["phpc_diagnose_name"]="Parameter";
$language["phpc_diagnose_value"]="Value";
$language["phpc_diagnose_comment"]="Comment";
$language["phpc_diagnose_correct"]="Correct Value!";
$language["phpc_diagnose_rg"]="Automatically creates unnecessary global variables containing data sent by user. Doesn't affect security, but it's still recommended to disable this option (in your php.ini file).";
$language["phpc_diagnose_rla"]="Automatically creates global arrays with long names (HTTP_XXX_VARS). These arrays are deprecated and it's recommended to disable this option (in your php.ini file).";
$language["phpc_diagnose_mqg"]="Performs unnecessary escaping for all data sent by user. Doesn't affect security, but affects server performance. This option is deprecated and it's recommended to disable it (in your php.ini file).";
$language["phpc_diagnose_mqr"]="Automatically performs unnecessary escaping for all internal data. This option is deprecated and can affect site's stability! Recommended to disable as soon as possible (in your php.ini file).";
$language["phpc_diagnose_mqs"]="Performs unnecessary escaping for all incoming data, in non-standard way. This option is deprecated and can affect site's stability! Recommended to disable as soon as possible (in your php.ini file).";
$language["phpc_diagnose_aui"]="Allows to execute PHP scripts located on remote servers. Having this option enabled is a serious vulnerability risk; if you don't need this feature - disable it as soon as possible (in your php.ini file).";
$language["phpc_diagnose_sa"]="On the production server, SERVER_ADDR variable should not have value equal to localhost address. Probably your server was not configured correctly.";
$language["phpc_diagnose_ra"]="On the production server, IP address of a visitor should not have value equal to server address or to localhost address. Probably your server was not configured correctly.";

$language["phpc_export_form"]="Database Export";
$language["phpc_export_formtemplates"]="Templates Export";
$language["phpc_export_formbundles"]="Bundles Export";
$language["phpc_export_tables"]="Select tables to export:";
$language["phpc_export_tablesdesc"]="Choose one or more tables (use Ctrl+click to select/deselect a table). If no tables selected, whole database will be exported.";
$language["phpc_export_templates"]="Select templates to export:";
$language["phpc_export_templatesdesc"]="Choose one or more templates (use Ctrl+click to select/deselect a template). If no templates selected, whole set will be exported.";
$language["phpc_export_bundles"]="Select bundles to export:";
$language["phpc_export_bundlesdesc"]="Choose one or more bundles (use Ctrl+click to select/deselect a bundle). If no bundles selected, whole set will be exported.";
$language["phpc_export_structure"]="Include tables structure?";
$language["phpc_export_data"]="Include tables data?";
$language["phpc_export_pack"]="ZIP archive?";

$language["phpc_import_form"]="Database Import";
$language["phpc_import_file"]="File containing SQL query:";
$language["phpc_import_filedesc"]="Choose a file saved from database export (unpacked).";
$language["phpc_import_empty"]="File not selected or was not uploaded properly.";
$language["phpc_import_success"]="Query executed successfully! (Instructions processed: %s)";
$language["phpc_import_failure"]="Next instruction caused an error:";

$language["phpc_check_prompt"]="This will check data integrity in all PHPC tables and repair errors if possible. Proceed?";
$language["phpc_check_start"]="PHPC data integrity check started...";
$language["phpc_check_templateset"]="Checking template set &quot;%s&quot;...";
$language["phpc_check_bundleset"]="Checking bundle set &quot;%s&quot;...";
$language["phpc_check_success"]="All done, no errors found!";
$language["phpc_check_errors"]="All done.";
$language["phpc_check_invalidid"]="Incorrect &quot;%s&quot; value found in the table &quot;%s&quot; (fixed)";
$language["phpc_check_recursion"]="Recursion found in the table &quot;%s&quot; (fixed)";
$language["phpc_check_templaterecursion"]="Template &quot;%s&quot; seems to be inherited from itself.";
$language["phpc_check_templateparent"]="Template &quot;%s&quot; is inherited from missing template &quot;%s&quot;.";
$language["phpc_check_pagetemplate"]="Page &quot;%s&quot; referring to the missing template &quot;%s&quot;.";
$language["phpc_check_pagebundle"]="Page &quot;%s&quot; referring to the missing bundle &quot;%s&quot;.";

$language["phpc_clearcache_start"]="Clearing compiler cache...";
$language["phpc_clearcache_success"]="Compiler cache clear!";
$language["phpc_clearfilecache_start"]="Clearing file cache...";
$language["phpc_clearfilecache_success"]="File cache clear!";

/******************************************************************************/

$language["phpc_addpage_form"]="Add New Page";
$language["phpc_addpage_success"]="Page added!";
$language["phpc_addpage_failure"]="Unable to add page. Seems to be another page with the same name.";
$language["phpc_editpage_form"]="Edit Page";
$language["phpc_editpage_success"]="Page updated!";
$language["phpc_editpage_failure"]="Unable to update page. Seems to be another page with the same name.";
$language["phpc_addeditpage_name"]="Page Name:";
$language["phpc_addeditpage_namedesc"]="Used for creating links. Try to make simple and clear names, e.g. &quot;photoGallery&quot;.";
$language["phpc_addeditpage_alias"]="Alias:";
$language["phpc_addeditpage_aliasdesc"]="Just another name for the page, if needed. Will appear only &quot;outside&quot;, nevertheless, you should always use original name in templates and bundles, not alias.";
$language["phpc_addeditpage_parent"]="Parent:";
$language["phpc_addeditpage_parentdesc"]="If specified, page will inherit all omitted parameters (including bundles list) from it.";
$language["phpc_addeditpage_noparent"]="No one";
$language["phpc_addeditpage_template"]="Template:";
$language["phpc_addeditpage_templatedesc"]="Name of the template used for page displaying.";
$language["phpc_addeditpage_bundles"]="Bundles List:";
$language["phpc_addeditpage_bundlesdesc"]="List of the bundle names used for preparing page's data. Separate names by comma.";
$language["phpc_addeditpage_title"]="Title in &quot;%s&quot;:";
$language["phpc_addeditpage_params"]="Additional Parameters:";
$language["phpc_addeditpage_paramsdesc"]="List of extra parameters in form param=value, separated by comma.";
$language["phpc_addeditpage_visible"]="Is Available for Users?";

$language["phpc_removepage_prompt"]="Are you sure you want to delete this page?";
$language["phpc_removepage_success"]="Page deleted!";

$language["phpc_addpagegroup_form"]="Add New Page Group";
$language["phpc_addpagegroup_success"]="Group added!";
$language["phpc_addpagegroup_failure"]="Unable to add group. Seems to be another group with the same prefix.";
$language["phpc_editpagegroup_form"]="Edit Page Group";
$language["phpc_editpagegroup_success"]="Group updated!";
$language["phpc_editpagegroup_failure"]="Unable to update group. Seems to be another group with the same prefix.";
$language["phpc_addeditpagegroup_title"]="Title:";
$language["phpc_addeditpagegroup_prefix"]="Prefix:";
$language["phpc_addeditpagegroup_prefixdesc"]="Pages with names starting from this string will be automatically moved to this group.";
$language["phpc_addeditpagegroup_displayorder"]="Display Order:";

$language["phpc_removepagegroup_prompt"]="Are you sure you want to delete this group? All associated pages will be moved to the Common Group.";
$language["phpc_removepagegroup_success"]="Group deleted!";

$language["phpc_orderpages_form"]="Page Groups Display Order";
$language["phpc_orderpages_success"]="Order updated!";

$language["phpc_modifypages_tree"]="Pages Tree";
$language["phpc_modifypages_defgrouponce"]="All Pages";
$language["phpc_modifypages_defgroupmany"]="Other Pages";
$language["phpc_modifypages_expand"]="Expand";
$language["phpc_modifypages_expandall"]="Expand All";
$language["phpc_modifypages_groupedit"]="Edit";
$language["phpc_modifypages_groupremove"]="Delete";
$language["phpc_modifypages_groupadd"]="Add Page";
$language["phpc_modifypages_itemedit"]="Edit";
$language["phpc_modifypages_itemremove"]="Delete";
$language["phpc_modifypages_itemopen"]="Open";

$language["phpc_addpair_form"]="Add New Page/Template Pair";
$language["phpc_addpair_separator"]="Secondary Parameters (you can leave them as they are)";
$language["phpc_addpair_success"]="Page/template pair added!";
$language["phpc_addpair_failure1"]="Unable to add page. Seems to be another page with the same name.";
$language["phpc_addpair_failure2"]="Unable to add template. Seems to be another template with the same name.";
$language["phpc_addpair_name"]="Page Name:";
$language["phpc_addpair_title"]="Title in &quot;%s&quot;:";
$language["phpc_addpair_content"]="Template Text:";
$language["phpc_addpair_filedata"]="Or File with Template Content:";
$language["phpc_addpair_alias"]="Page Alias:";
$language["phpc_addpair_parent"]="Page Parent:";
$language["phpc_addpair_noparent"]="No one";
$language["phpc_addpair_templateparent"]="Template Parent:";
$language["phpc_addpair_template"]="Template Name:";
$language["phpc_addpair_templatedesc"]="If not specified, page name will be used instead.";
$language["phpc_addpair_bundles"]="Bundles List:";

/******************************************************************************/

$language["phpc_addtemplate_form"]="Add New Template";
$language["phpc_addtemplate_success"]="Template added!";
$language["phpc_addtemplate_failure"]="Unable to add template. Seems to be another template with the same name.";
$language["phpc_edittemplate_form"]="Edit Template";
$language["phpc_edittemplate_success"]="Template updated!";
$language["phpc_edittemplate_failure"]="Unable to update template. Seems to be another template with the same name.";
$language["phpc_addedittemplate_name"]="Template Name:";
$language["phpc_addedittemplate_parent"]="Parent:";
$language["phpc_addedittemplate_content"]="Template Text:";
$language["phpc_addedittemplate_filedata"]="Or File with Template Content:";

$language["phpc_viewtemplate_prompt"]="Contents of the template <b>%s</b> are displayed here.";
$language["phpc_viewtemplate_edit"]="Edit this Template";
$language["phpc_viewtemplate_execute"]="Execute this Template in a New Window";
$language["phpc_viewtemplate_back"]="Back to the Templates List";

$language["phpc_removetemplate_prompt"]="Are you sure you want to delete this template?";
$language["phpc_reverttemplate_prompt"]="Are you sure you want to revert this template to its original version?";
$language["phpc_removetemplate_success"]="Template deleted!";

$language["phpc_addtemplategroup_form"]="Add New Template Group";
$language["phpc_addtemplategroup_success"]="Group added!";
$language["phpc_addtemplategroup_failure"]="Unable to add group. Seems to be another group with the same prefix.";
$language["phpc_edittemplategroup_form"]="Edit Template Group";
$language["phpc_edittemplategroup_success"]="Group updated!";
$language["phpc_edittemplategroup_failure"]="Unable to update group. Seems to be another group with the same prefix.";
$language["phpc_addedittemplategroup_title"]="Title:";
$language["phpc_addedittemplategroup_prefix"]="Prefix:";
$language["phpc_addedittemplategroup_prefixdesc"]="Templates with names starting from this string will be automatically moved to this group.";
$language["phpc_addedittemplategroup_displayorder"]="Display Order:";

$language["phpc_removetemplategroup_prompt"]="Are you sure you want to delete this group? All associated templates will be moved to the Common Group.";
$language["phpc_removetemplategroup_success"]="Group deleted!";

$language["phpc_ordertemplates_form"]="Template Groups Display Order";
$language["phpc_ordertemplates_success"]="Order updated!";

$language["phpc_modifytemplates_tree"]="Templates Tree";
$language["phpc_modifytemplates_defgrouponce"]="All Templates";
$language["phpc_modifytemplates_defgroupmany"]="Other Templates";
$language["phpc_modifytemplates_expand"]="Expand";
$language["phpc_modifytemplates_expandall"]="Expand All";
$language["phpc_modifytemplates_groupedit"]="Edit";
$language["phpc_modifytemplates_groupremove"]="Delete";
$language["phpc_modifytemplates_groupadd"]="Add Template";
$language["phpc_modifytemplates_itemview"]="View";
$language["phpc_modifytemplates_itemedit"]="Edit";
$language["phpc_modifytemplates_itemcopy"]="Create Copy";
$language["phpc_modifytemplates_itemremove"]="Delete";
$language["phpc_modifytemplates_itemrevert"]="Revert To Original";

/******************************************************************************/

$language["phpc_addbundle_form"]="Add New Bundle";
$language["phpc_addbundle_success"]="Bundle added!";
$language["phpc_addbundle_failure"]="Unable to add bundle. Seems to be another bundle with the same name.";
$language["phpc_editbundle_form"]="Edit Bundle";
$language["phpc_editbundle_success"]="Bundle updated!";
$language["phpc_editbundle_failure"]="Unable to update bundle. Seems to be another bundle with the same name.";
$language["phpc_addeditbundle_name"]="Bundle Name:";
$language["phpc_addeditbundle_plugins"]="Plugins List:";
$language["phpc_addeditbundle_content"]="Bundle Text:";
$language["phpc_addeditbundle_filedata"]="Or File with Bundle Content:";

$language["phpc_viewbundle_prompt"]="Contents of the bundle <b>%s</b> are displayed here.";
$language["phpc_viewbundle_edit"]="Edit this Bundle";
$language["phpc_viewbundle_back"]="Back to the Bundles List";

$language["phpc_removebundle_prompt"]="Are you sure you want to delete this bundle?";
$language["phpc_revertbundle_prompt"]="Are you sure you want to revert this bundle to its original version?";
$language["phpc_removebundle_success"]="Bundle deleted!";

$language["phpc_addbundlegroup_form"]="Add New Bundle Group";
$language["phpc_addbundlegroup_success"]="Group added!";
$language["phpc_addbundlegroup_failure"]="Unable to add group. Seems to be another group with the same prefix.";
$language["phpc_editbundlegroup_form"]="Edit Bundle Group";
$language["phpc_editbundlegroup_success"]="Group updated!";
$language["phpc_editbundlegroup_failure"]="Unable to update group. Seems to be another group with the same prefix.";
$language["phpc_addeditbundlegroup_title"]="Title:";
$language["phpc_addeditbundlegroup_prefix"]="Prefix:";
$language["phpc_addeditbundlegroup_prefixdesc"]="Bundles with names starting from this string will be automatically moved to this group.";
$language["phpc_addeditbundlegroup_displayorder"]="Display Order:";

$language["phpc_removebundlegroup_prompt"]="Are you sure you want to delete this group? All associated bundles will be moved to the Common Group.";
$language["phpc_removebundlegroup_success"]="Group deleted!";

$language["phpc_orderbundles_form"]="Bundle Groups Display Order";
$language["phpc_orderbundles_success"]="Order updated!";

$language["phpc_modifybundles_tree"]="Bundles Tree";
$language["phpc_modifybundles_defgrouponce"]="All Bundles";
$language["phpc_modifybundles_defgroupmany"]="Other Bundles";
$language["phpc_modifybundles_expand"]="Expand";
$language["phpc_modifybundles_expandall"]="Expand All";
$language["phpc_modifybundles_groupedit"]="Edit";
$language["phpc_modifybundles_groupremove"]="Delete";
$language["phpc_modifybundles_groupadd"]="Add Bundle";
$language["phpc_modifybundles_itemview"]="View";
$language["phpc_modifybundles_itemedit"]="Edit";
$language["phpc_modifybundles_itemcopy"]="Create Copy";
$language["phpc_modifybundles_itemremove"]="Delete";
$language["phpc_modifybundles_itemrevert"]="Revert To Original";

/******************************************************************************/

$language["phpc_searchtemplates_form"]="Search In Templates";
$language["phpc_searchbundles_form"]="Search In Bundles";
$language["phpc_search_text"]="Search Text:";
$language["phpc_search_textdesc"]="Do not specify pattern delimiters for regular expression.";
$language["phpc_search_case"]="Case-Sensitive Search?";
$language["phpc_search_words"]="Search Only Whole Words?";
$language["phpc_search_regexp"]="Regular Expression?";

$language["phpc_searchtemplates_tree"]="Template Search Results";
$language["phpc_searchbundles_tree"]="Bundle Search Results";
$language["phpc_searchtemplates_notfound"]="Specified text was not found in any template.";
$language["phpc_searchbundles_notfound"]="Specified text was not found in any bundle.";

$language["phpc_replacetemplates_form"]="Search And Replace In Templates";
$language["phpc_replacebundles_form"]="Search And Replace In Bundles";
$language["phpc_replace_text"]="Search Text:";
$language["phpc_replace_textdesc"]="Do not specify pattern delimiters for regular expression.";
$language["phpc_replace_replace"]="Replace With:";
$language["phpc_replace_replacedesc"]="You can use back-references like \\1, \\2 etc. for regular expression.";
$language["phpc_replace_case"]="Case-Sensitive Search?";
$language["phpc_replace_words"]="Search Only Whole Words?";
$language["phpc_replace_regexp"]="Regular Expression?";
$language["phpc_replacetemplates_allsets"]="Process all Template Sets?";
$language["phpc_replacebundles_allsets"]="Process all Bundle Sets?";
$language["phpc_replacetemplates_allsetsdesc"]="If yes, then search will be performed through all template sets, not only current one.";
$language["phpc_replacebundles_allsetsdesc"]="If yes, then search will be performed through all bundle sets, not only current one.";

$language["phpc_replacetemplates_prompt"]="There are %s matches in %s templates. Continue?";
$language["phpc_replacebundles_prompt"]="There are %s matches in %s bundles. Continue?";
$language["phpc_replacetemplates_promptempty"]="Attention, the replace string is empty! Process anyway? (There are %s matches in %s templates)";
$language["phpc_replacebundles_promptempty"]="Attention, the replace string is empty! Process anyway? (There are %s matches in %s bundles)";
$language["phpc_replacetemplates_report"]="Processing template &quot;%s&quot;... (done)";
$language["phpc_replacebundles_report"]="Processing bundle &quot;%s&quot;... (done)";
$language["phpc_replacetemplates_success"]="Process finished successfully!";
$language["phpc_replacebundles_success"]="Process finished successfully!";

/******************************************************************************/

$language["phpc_addreplacement_form"]="Add New Replacement";
$language["phpc_addreplacement_success"]="Replacement added!";
$language["phpc_addreplacement_failure"]="Unable to add replacement. Seems to be another replacement with the same name.";
$language["phpc_editreplacement_form"]="Edit Replacement";
$language["phpc_editreplacement_success"]="Replacement updated!";
$language["phpc_editreplacement_failure"]="Unable to update replacement. Seems to be another replacement with the same name.";
$language["phpc_addeditreplacement_name"]="Source Text:";
$language["phpc_addeditreplacement_content"]="Replacement Text:";

$language["phpc_removereplacement_prompt"]="Are you sure you want to delete this replacement?";
$language["phpc_revertreplacement_prompt"]="Are you sure you want to revert this replacement to its original version?";
$language["phpc_removereplacement_success"]="Replacement deleted!";

$language["phpc_modifyreplacements_tree"]="Replacements Tree";
$language["phpc_modifyreplacements_defgroup"]="All Replacements";
$language["phpc_modifyreplacements_add"]="Add Replacement";
$language["phpc_modifyreplacements_edit"]="Edit";
$language["phpc_modifyreplacements_copy"]="Create Copy";
$language["phpc_modifyreplacements_remove"]="Delete";
$language["phpc_modifyreplacements_revert"]="Revert To Original";

/******************************************************************************/

$language["phpc_addformatting_form"]="Add New Formatting Rule";
$language["phpc_addformatting_success"]="Rule added!";
$language["phpc_addformatting_failure"]="Unable to add rule. Seems to be another rule with the same pattern.";
$language["phpc_editformatting_form"]="Edit Formatting Rule";
$language["phpc_editformatting_success"]="Rule updated!";
$language["phpc_editformatting_failure"]="Unable to update rule. Seems to be another rule with the same pattern.";
$language["phpc_addeditformatting_title"]="Title:";
$language["phpc_addeditformatting_class"]="Class (Category):";
$language["phpc_addeditformatting_classdesc"]="Try to unite similar rules into classes, i.e. &quot;smilies&quot;.";
$language["phpc_addeditformatting_pattern"]="Pattern (Regural Expression):";
$language["phpc_addeditformatting_patterndesc"]="Do not specify pattern delimiters (/.../), because they will be added automatically. <a href=\"http://www.php.net/manual/en/ref.pcre.php\" target=\"_blank\">Here</a> you can read more about regular expressions.";
$language["phpc_addeditformatting_content"]="Replacement Text:";
$language["phpc_addeditformatting_contentdesc"]="You can use back-references like \\1, \\2 etc.";
$language["phpc_addeditformatting_callback"]="Replacement Callback Function:";
$language["phpc_addeditformatting_callbackdesc"]="Here you can specify name of the function used for replacement. For more details, read PHP manual, function preg_replace_callback().";
$language["phpc_addeditformatting_sample"]="Sample Text with this Rule:";
$language["phpc_addeditformatting_useorder"]="Use Order:";

$language["phpc_viewformatting_sample"]="Text formatting example:";
$language["phpc_viewformatting_result"]="Formatted text looks like this:";
$language["phpc_viewformatting_source"]="HTML source text looks like this:";

$language["phpc_removeformatting_prompt"]="Are you sure you want to delete this rule?";
$language["phpc_removeformatting_success"]="Rule deleted!";

$language["phpc_orderformatting_success"]="Order updated!";

$language["phpc_modifyformatting_title"]="Title";
$language["phpc_modifyformatting_class"]="Class";
$language["phpc_modifyformatting_pattern"]="Pattern";
$language["phpc_modifyformatting_options"]="Options";
$language["phpc_modifyformatting_useorder"]="Order";
$language["phpc_modifyformatting_view"]="View";
$language["phpc_modifyformatting_edit"]="Edit";
$language["phpc_modifyformatting_remove"]="Delete";

/******************************************************************************/

$language["phpc_addlinkstyle_form"]="Add New Link Style";
$language["phpc_addlinkstyle_success"]="Item added!";
$language["phpc_addlinkstyle_failure"]="Unable to add item. Seems to be another item with the same pattern.";
$language["phpc_editlinkstyle_form"]="Edit Link Style";
$language["phpc_editlinkstyle_success"]="Item updated!";
$language["phpc_editlinkstyle_failure"]="Unable to update rule. Seems to be another rule with the same pattern.";
$language["phpc_addeditlinkstyle_page"]="Page:";
$language["phpc_addeditlinkstyle_pattern"]="Request Pattern:";
$language["phpc_addeditlinkstyle_patterndesc"]="You can use parameters in pattern. They look like variables in PHP. Pattern example: media/gallery/\$galleryid.";
$language["phpc_addeditlinkstyle_assign"]="Assign List:";
$language["phpc_addeditlinkstyle_assigndesc"]="List of parameters and their values in form param=value, separated by comma.";
$language["phpc_addeditlinkstyle_useorder"]="Use Order:";

$language["phpc_removelinkstyle_prompt"]="Are you sure you want to delete this item?";
$language["phpc_removelinkstyle_success"]="Item deleted!";

$language["phpc_orderlinkstyles_success"]="Order updated!";

$language["phpc_modifylinkstyles_page"]="Page";
$language["phpc_modifylinkstyles_pattern"]="Pattern";
$language["phpc_modifylinkstyles_assign"]="Assign";
$language["phpc_modifylinkstyles_options"]="Options";
$language["phpc_modifylinkstyles_useorder"]="Order";
$language["phpc_modifylinkstyles_edit"]="Edit";
$language["phpc_modifylinkstyles_remove"]="Delete";

/******************************************************************************/

$language["phpc_addtemplateset_form"]="Add New Template Set";
$language["phpc_addbundleset_form"]="Add New Bundle Set";
$language["phpc_addreplacementset_form"]="Add New Replacement Set";
$language["phpc_addset_success"]="Set added!";
$language["phpc_edittemplateset_form"]="Edit Template Set";
$language["phpc_editbundleset_form"]="Edit Bundle Set";
$language["phpc_editreplacementset_form"]="Edit Replacement Set";
$language["phpc_editset_success"]="Set updated!";
$language["phpc_addeditset_title"]="Title:";
$language["phpc_addeditset_parent"]="Parent Set:";
$language["phpc_addeditset_parentdesc"]="If parent set is specified, this set will inherit all its items.";
$language["phpc_addeditset_noparent"]="No one";

$language["phpc_removetemplateset_prompt"]="Are you sure you want to delete this set? All associated templates will be deleted also!";
$language["phpc_removebundleset_prompt"]="Are you sure you want to delete this set? All associated bundles will be deleted also!";
$language["phpc_removereplacementset_prompt"]="Are you sure you want to delete this set? All associated replacements will be deleted also!";
$language["phpc_removeset_success"]="Set deleted!";
$language["phpc_removeset_constraint"]="Unable to delete set. Detected styles referencing to this set.";

/******************************************************************************/

$language["phpc_addstyle_form"]="Add New Style";
$language["phpc_addstyle_success"]="Style added!";
$language["phpc_editstyle_form"]="Edit Style";
$language["phpc_editstyle_success"]="Style updated!";
$language["phpc_addeditstyle_title"]="Title:";
$language["phpc_addeditstyle_templateset"]="Template Set:";
$language["phpc_addeditstyle_bundleset"]="Bundle Set:";
$language["phpc_addeditstyle_replacementset"]="Replacement Set:";
$language["phpc_addeditstyle_host"]="Host Name:";
$language["phpc_addeditstyle_hostdesc"]="If specified, all requests from this host will be handled by this style.";
$language["phpc_addeditstyle_folder"]="Virtual Folder:";
$language["phpc_addeditstyle_folderdesc"]="If specified, all requests from this folder will be handled by this style.";
$language["phpc_addeditstyle_visible"]="Available For Users?";
$language["phpc_addeditstyle_note"]="If you need another set, you have to create it first.";

$language["phpc_removestyle_prompt"]="Are you sure you want to delete this style?";
$language["phpc_removestyle_success"]="Style deleted!";

$language["phpc_modifystyles_style"]="Style";
$language["phpc_modifystyles_templateset"]="Template Set";
$language["phpc_modifystyles_bundleset"]="Bundle Set";
$language["phpc_modifystyles_replacementset"]="Replacement Set";
$language["phpc_modifystyles_visible"]="Selectable";
$language["phpc_modifystyles_forusers"]="Selected";
$language["phpc_modifystyles_foradmin"]="Operative";
$language["phpc_modifystyles_options"]="Options";
$language["phpc_modifystyles_edit"]="Edit";
$language["phpc_modifystyles_remove"]="Delete";
$language["phpc_modifystyles_parent"]="Parent Set";

$language["phpc_assignstyles_form"]="Style Selection";
$language["phpc_assignstyles_forusers"]="Which style must be used as default for website displaying?";
$language["phpc_assignstyles_foradmin"]="Which style must be editable via Control Panel?";
$language["phpc_assignstyles_success"]="Style selected!";

/******************************************************************************/

$language["phpc_error_siteclosed"]="Sorry, the site is temporarily closed. Come back later.";
$language["phpc_error_nomanual"]="Sorry, built-in manual is not available yet. Please check out <a href=\"http://www.phpc.ru/\" target=\"_blank\">PHPC official site</a> for the online manual.";
$language["phpc_error_nopages"]="No pages found in your project.";
$language["phpc_error_nosearchtext"]="Search text not specified.";
$language["phpc_error_notemplatesets"]="No template sets found. You should create ones first.";
$language["phpc_error_nobundlesets"]="No bundle sets found. You should create ones first.";
$language["phpc_error_noreplacementsets"]="No replacement sets found. You should create ones first.";

?>
