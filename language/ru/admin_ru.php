<?php

$language["admin_installer_title"]="Установка PHPC";
$language["admin_installer_header"]="<b>Скрипт установки PHP Compiler</b>";
$language["admin_installer_firststep"]="Вернуться к началу";
$language["admin_installer_nextstep"]="Перейти к следующему шагу";
$language["admin_installer_admin"]="Перейти в Панель Администратора";
$language["admin_installer_intro"]="Добро пожаловать! Данный скрипт поможет вам установить и сконфигурировать PHPC.<br>\r\nПожалуйста, учтите, что для работы системы необходима версия PHP не ниже 4.3.0, а также отдельная база данных.";
$language["admin_installer_already"]="Извините, установка невозможна, так как конфигурационный файл уже существует.";
$language["admin_installer_notice"]="<b>Внимание</b>: Не забудьте вручную удалить каталог <b>install</b> вместе со всем содержимым. Он больше не нужен.";

$language["admin_installer_input"]="Пожалуйста, заполните нижеприведенную форму.";
$language["admin_installer_inputform"]="Ввод конфигурационных данных";
$language["admin_installer_inputadmin"]="Пароль администратора:";
$language["admin_installer_inputadmindesc"]="Используйте этот пароль (он сгенерирован случайным образом) или введите свой. Запомните пароль администратора или запишите его. Не показывайте этот пароль никому. Помните, что тот, кто знает этот пароль, имеет неограниченный доступ к сайту!";
$language["admin_installer_inputhost"]="Хост базы данных:";
$language["admin_installer_inputhostdesc"]="Адрес хоста, на котором находится база данных. Можно указать хост и порт через двоеточие, например localhost:3306.";
$language["admin_installer_inputdb"]="Название базы данных:";
$language["admin_installer_inputdbdesc"]="Если этой базы еще нет, вам следует создать ее самостоятельно.";
$language["admin_installer_inputuser"]="Имя пользователя:";
$language["admin_installer_inputuserdesc"]="Имя пользователя для подключения к базе данных.";
$language["admin_installer_inputpass"]="Пароль:";
$language["admin_installer_inputpassdesc"]="Пароль этого пользователя.";
$language["admin_installer_inputprefix"]="Префикс таблиц БД:";
$language["admin_installer_inputprefixdesc"]="Если вы устанавливаете несколько PHPC-проектов в одну базу данных, укажите каждому проекту свой префикс.";
$language["admin_installer_inputencode"]="Ключ шифрования данных:";
$language["admin_installer_inputencodedesc"]="Используется в различных частях системы. Нажмите F5, чтобы сгенерировать новый ключ.";
$language["admin_installer_inputlocale"]="Язык вашего проекта:";
$language["admin_installer_inputlocaledesc"]="Вы можете настроить систему на один язык либо создать многоязычный сайт.";
$language["admin_installer_inputlocaleall"]="Многоязычный проект";
$language["admin_installer_inputunicode"]="Включить поддержку Unicode?";
$language["admin_installer_inputunicodedesc"]="Вариант &quot;Да&quot; означает, что все данные проекта будут храниться в кодировке UTF-8. Вариант &quot;Нет&quot; позволит хранить данные в более быстрой и компактной однобайтовой кодировке.";

$language["admin_installer_check"]="Проверка подключения к базе данных...";
$language["admin_installer_checknodb"]="К сожалению, на вашем сервере отсутствует поддержка баз данных. Для баз данных MySQL это набор mysqli_xxx функций в PHP. Без них дальнейшая установка невозможна.<br>\r\nЧтобы включить поддержку MySQLi (MySQL Improved) на вашем сервере, выполните следующие шаги:<br><br>\r\n1. Найдите и откройте файл php.ini.<br>\r\n2. Раскомментируйте (уберите точку с запятой в начале) строку &quot;extension=php_mysqli.dll&quot;.<br>\r\n3. Сохраните файл и перезагрузите сервер.<br><br>\r\nПосле этого вы сможете вернуться к установке PHP Compiler.";
$language["admin_installer_checksuccess"]="Подключение произошло успешно!";
$language["admin_installer_checkfailure"]="<b>Внимание</b>: Не удалось подключиться к базе данных. Вам следует вернуться назад и проверить параметры.";
$language["admin_installer_checkreport"]="Еще раз проверьте введенные данные:";
$language["admin_installer_checkadmin"]="Пароль администратора: <b>%s</b>";
$language["admin_installer_checkhost"]="Хост базы данных: <b>%s</b>";
$language["admin_installer_checkdb"]="Название базы данных: <b>%s</b>";
$language["admin_installer_checkuser"]="Имя пользователя: <b>%s</b>";
$language["admin_installer_checkpass"]="Пароль пользователя: <b>%s</b>";
$language["admin_installer_checkprefix"]="Префикс таблиц БД: <b>%s</b>";
$language["admin_installer_checkprompt"]="Сохранить эти данные в конфигурационном файле системы?";

$language["admin_installer_savesuccess"]="Создание конфигурационного файла... Успешно!<br>\r\nВсе, что вам осталось сделать - это зайти в панель администратора и установить модули.";
$language["admin_installer_savefailure"]="<b>Внимание</b>: Не удалось создать конфигурационный файл. Возможно, у папки <b>phpc</b> отсутствуют права на запись.<br>\r\nПроверьте возможность записи в эту папку, вернитесь назад и повторите попытку.";

/******************************************************************************/

$language["admin_auth_prompt"]="Введите пароль администратора сайта:";
$language["admin_auth_promptmember"]="Введите имя пользователя и пароль:";
$language["admin_auth_submit"]="Вперед!";
$language["admin_auth_noreferer"]="Панель управления заблокирована (возможная попытка взлома). У страницы отсутствует источник перехода.";
$language["admin_auth_wrongreferer"]="Панель управления заблокирована (возможная попытка взлома). У страницы неверный источник перехода (%s).";

$language["admin_upgrade_menu"]="Обновление";
$language["admin_upgrade_upgrade"]="Обновить PHPC";
$language["admin_upgrade_prompt"]="Вашу базу данных необходимо обновить, чтобы она соответствовала последней версии скриптов PHPC. Продолжить? (Будут затронуты только системные таблицы.)";
$language["admin_upgrade_start"]="Обновление таблиц PHPC...";
$language["admin_upgrade_success"]="Обновление таблиц PHPC завершено!";

$language["admin_title"]="Панель управления сайтом";
$language["admin_home"]="Главная страница";
$language["admin_opensite"]="Открыть сайт в новом окне";
$language["admin_logout"]="Выйти из панели управления";
$language["admin_welcome"]="<b>Добро пожаловать в панель управления PHP Compiler!</b><br><br>\r\nОтсюда вы легко сможете управлять возможностями вашего сайта. Пожалуйста, выберите нужный вам пункт в меню слева.";
$language["admin_welcomemember"]="<b>Добро пожаловать в панель управления, %s!</b><br><br>\r\nОтсюда вы легко сможете управлять возможностями сайта. Пожалуйста, выберите нужный вам пункт в меню слева.";
$language["admin_nolanguage"]="Автоматический выбор языка";
$language["admin_noframes"]="Пожалуйста, включите поддержку фреймов и обновите страницу.";

$language["admin_submit"]="Вперед!";
$language["admin_update"]="Обновить";
$language["admin_reset"]="Сброс";
$language["admin_action"]="Выбранные элементы:";
$language["admin_go"]="Пошел";
$language["admin_accept"]="Принять";
$language["admin_reject"]="Отложить";
$language["admin_delete"]="Удалить";
$language["admin_treeonce"]="Все элементы";
$language["admin_treemany"]="Остальные элементы";
$language["admin_tree_expandall"]="Развернуть все";
$language["admin_tree_contractall"]="Свернуть все";
$language["admin_pages"]="Страницы:";
$language["admin_pages_all"]="Все";
$language["admin_pages_first"]="Первая страница";
$language["admin_pages_last"]="Последняя страница";
$language["admin_pages_previous"]="Предыдущая страница";
$language["admin_pages_next"]="Следующая страница";
$language["admin_total"]="Всего";
$language["admin_totalvalue"]="Всего: %s";
$language["admin_allowcodes"]="Вы можете использовать стандартные BB-коды.";
$language["admin_allowhtml"]="Вы можете использовать HTML-теги.";
$language["admin_unknowncontrol"]="<b>Внимание:</b> Обнаружен неизвестный элемент управления (%s).";
$language["admin_refreshmenu"]="Обновить Главное Меню";

/******************************************************************************/

$language["admin_toolbar1"]="Полезные возможности";
$language["admin_toolbar2"]="Прочие возможности";
$language["admin_toolbar_php"]="Поиск по документации PHP:";
$language["admin_toolbar_mysql"]="Поиск по документации MySQL:";
$language["admin_toolbar_links"]="Различные полезные ссылки:";
$language["admin_toolbar_link1"]="Официальный сайт PHP Compiler";
$language["admin_toolbar_link2"]="Документация по PHP Compiler";
$language["admin_toolbar_link3"]="Официальный сайт PHP";
$language["admin_toolbar_link4"]="Документация по PHP";
$language["admin_toolbar_link5"]="Официальный сайт MySQL";
$language["admin_toolbar_link6"]="Документация по MySQL";
$language["admin_toolbar_language"]="Выбор языка интерфейса:";
$language["admin_toolbar_langsuccess"]="Язык выбран!";
$language["admin_toolbar_skin"]="Выбор стиля оформления:";
$language["admin_toolbar_skindefault"]="Обычный";
$language["admin_toolbar_skinsuccess"]="Стиль выбран!";

/******************************************************************************/

$language["admin_install"]="Установить плагин";
$language["admin_installstart"]="Установка плагина...";
$language["admin_installsuccess"]="Плагин установлен успешно!";
$language["admin_installtable"]="Создание таблицы &quot;%s&quot;... Готово";
$language["admin_installalter"]="Изменение структуры таблицы &quot;%s&quot;... Готово";
$language["admin_installdrop"]="Удаление ненужной таблицы &quot;%s&quot;... Готово";
$language["admin_installdata"]="Добавление данных в таблицу &quot;%s&quot;... Готово";
$language["admin_installchanges"]="Внесение изменений в таблицу &quot;%s&quot;... Готово";
$language["admin_installfolder_success"]="Создание каталога &quot;%s&quot;... Готово";
$language["admin_installfolder_failure"]="Создание каталога &quot;%s&quot;... <b>Ошибка!</b> Вам придется создать его вручную и дать ему права на запись.";
$language["admin_installoptions"]="Создание новых настроек... Готово";
$language["admin_installrelations"]="Создание новых связей между плагинами... Готово";
$language["admin_installpage"]="Создание страницы &quot;%s&quot;... Готово";
$language["admin_installtemplate"]="Создание шаблона &quot;%s&quot;... Готово";
$language["admin_installbundle"]="Создание пакета &quot;%s&quot;... Готово";
$language["admin_installreplacements"]="Создание новых подстановок... Готово";
$language["admin_installformatting"]="Создание новых правил форматирования... Готово";
$language["admin_installlinkstyles"]="Создание новых правил представления ссылок... Готово";
$language["admin_installalterpage"]="Внесение изменений в страницу &quot;%s&quot;... Готово";
$language["admin_installaltertemplate"]="Внесение изменений в шаблон &quot;%s&quot;... Готово";
$language["admin_installalterbundle"]="Внесение изменений в пакет &quot;%s&quot;... Готово";

$language["admin_upgrade"]="Обновить плагин";
$language["admin_upgradestart"]="Обновление плагина...";
$language["admin_upgradesuccess"]="Плагин обновлен успешно!";

/******************************************************************************/

$language["admin_addlocale_form"]="Добавление языка";
$language["admin_addlocale_success"]="Язык добавлен!";
$language["admin_addlocale_locale"]="Выберите язык:";
$language["admin_addlocale_position"]="Куда добавить:";
$language["admin_addlocale_method"]="Как добавлять:";
$language["admin_addlocale_first"]="В начало списка";
$language["admin_addlocale_after"]="После языка \"%s\"";
$language["admin_addlocale_empty"]="С чистого листа";
$language["admin_addlocale_copy"]="Скопировать с языка \"%s\"";

$language["admin_removelocale_prompt"]="Вы действительно хотите удалить этот язык? Все данные, относящиеся к этому языку, будут уничтожены!";
$language["admin_removelocale_success"]="Язык удален!";

$language["admin_modifylocales_locale"]="Язык";
$language["admin_modifylocales_options"]="Управление";
$language["admin_modifylocales_remove"]="Удалить";
$language["admin_modifylocales_add"]="Добавить язык";

/******************************************************************************/

$language["admin_warning"]="<b>Предупреждение</b>: %s";
$language["admin_error"]="<b>Ошибка</b>: %s";

$language["admin_error_already"]="Плагин уже установлен.";
$language["admin_error_wrongdb"]="Извините, ваша БД (%s) не поддерживается плагином.";
$language["admin_error_structure"]="Обнаружены нарушения структуры таблицы &quot;%s&quot;.";
$language["admin_error_todo"]="Извините, данная возможность еще не реализована.";
$language["admin_error_nousersstyle"]="Не обнаружен стиль, предназначенный для отображения посетителям. Вам следует создать его или выбрать.";
$language["admin_error_noadminstyle"]="Не обнаружен стиль, предназначенный для редактирования в панели. Вам следует создать его или выбрать.";
$language["admin_error_nolocales"]="Не обнаружено ни одного установленного языка.";
$language["admin_error_nofreelocales"]="Все доступные языки уже использованы.";

?>
