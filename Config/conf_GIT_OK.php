<?php
/*!
\file
\brief Конфигурационные нстройки приложения для prod версии

Файл содержит все конфигурационные настройки для prod версии приложения, включая параметры подключения к базам данных
*/

/**
 * \defgroup config_prod Конфигурационные настройки для PROD версии приложения
 * \brief Конфигурационные настройки приложения
 *
 * Конфигурационные настройки приложения. Включает все контанты используемые в приложении, параметры подключения к базам
 * данных, и все возможные настройки параметров
 *
 */
define('DS',DIRECTORY_SEPARATOR);
$root = dirname(dirname(__FILE__)) . DS;
define('ROOT',$root );
define('CONF_DIR', ROOT . 'Config' . DS);
define('CONTROLLER_DIR', ROOT . 'Controller' . DS);
define('LANG_DIR', ROOT . 'Language' . DS);
define('LIB_DIR', ROOT . 'Library' . DS);
define('MODEL_DIR', ROOT . 'Model' . DS);
define('VIEW_DIR', ROOT . 'View' . DS);
define('APPROOT_DIR', ROOT . 'Applicationroot' . DS);




$request = new Request();///<  Объект принимает и обрабатывает все запросы _POST, _GET, _SERVER

/**
 * \defgroup basic_config  Базовые настройки приложения
 * \ingroup config_prod
 *
 * \brief Базовые настройки приложения
 *
 * \code
 *  Config::set('default_rout', 'account_test');
 *  Config::set('default_controller', 'Index');
 *  Config::set('default_action', 'index');
 *  Config::set('default_id', 4);
 * \endcode
 */
Config::set('default_rout', 'account_test');

Config::set('default_controller', 'Index');
Config::set('default_action', 'index');
Config::set('default_id', 4);


/**
 * \defgroup DB_1  DB_1 Connect
 * \ingroup config_prod
 *
 * \brief Основная база приложения
 *
 * Определение конфигурационных настроек подключения к базе DB_1 :
 * \code
 *
 * Config::set('db_1', $db_1);
 * \endcode
 * @{
 */

$host_1 = 'localhost';
$dbname_1 = 'db_name_1';
$user_1 ='user_name_1';
$pass_1 = 'pass';

define('DSN_1', "mysql:host=$host_1;dbname=$dbname_1; charset=UTF8");
define('USER_1', $user_1);
define('PASS_1', $pass_1);

$db_1 = array(
    'dsn' => DSN_1,
    'user' => USER_1,
    'pass' => PASS_1
);

Config::set('db_1', $db_1);

/*! @} */

/**
 * \defgroup DB_2  DB_2 Connect
 * \ingroup config_prod
 *
 * \brief тестовая база
 *
 * Тестовая база использовалас на стадии начальной разработки в режиме приложения 'test'
 * Определение конфигурационных настроек подключения к базе DB_2 :
 * \code
 *
 * Config::set('db_2', $db_2);
 * \endcode
 * @{
 */

$host_2 = 'localhost';
$dbname_2 = 'db_test';
$user_2 ='db_test_user';
$pass_2 = 'aTfrjbMRFqbE3tuD';

define('DSN_2', "mysql:host=$host_2;dbname=$dbname_2; charset=UTF8");
define('USER_2', $user_2);
define('PASS_2', $pass_2);

$db_2 = array(
    'dsn' => DSN_2,
    'user' => USER_2,
    'pass' => PASS_2
);

Config::set('db_2', $db_2);

/*! @} */


/**
 * \defgroup DB_3  DB_3 Connect
 * \ingroup config_prod
 *
 * \brief Подключение к базе биллинга
 *
 * Подключение к базе данных биллинга. Используется в prod  версии приложения
 * Определение конфигурационных настроек подключения к базе DB_3 :
 * \code
 *
 * Config::set('db_3', $db_3);
 * \endcode
 * @{
 */

$host_3 = '193.104.254.29';
$dbname_3 = 'billing_db';
$user_3 ='user_billing';
$pass_3 = 'pass_billing';

define('DSN_3', "mysql:host=$host_3;dbname=$dbname_3; charset=UTF8");
define('USER_3', $user_3);
define('PASS_3', $pass_3);

$db_3 = array(
    'dsn' => DSN_3,
    'user' => USER_3,
    'pass' => PASS_3
);

Config::set('db_3', $db_3);
/*! @} */

/**
 * \defgroup snmp  SNMP
 * \ingroup config_prod
 *
 * \brief Некоторые настройки для получения данных по протоколу snmp.
 *
 * SNMP Connect, Имена комьюнити необходимы для получения данных по протоколу snmp:
 * \code
 *  Config::set('community_read_default', 'Swstat');
 *  Config::set('community_write_default', 'Swwrite');
 * \endcode
 * oid необходимы для получения предварительных данных про модель свича и статус портов
 * - используются для определения необходимого шаблона (find switch pattern)
 * \code
 *  Config::set('oid_switch_model', '.1.3.6.1.2.1.1.1.0');
 *  Config::set('port_status', '.1.3.6.1.2.1.2.2.1.8');
 * \endcode
 * @{
 */

Config::set('community_read_default', 'Swstat');
Config::set('community_write_default', 'SwStatHLNetAssist');

//find switch pattern

Config::set('oid_switch_model', '.1.3.6.1.2.1.1.1.0');
Config::set('port_status', '.1.3.6.1.2.1.2.2.1.8');

/*! @} */

/**
 * \defgroup time_clean  Время жизни истории
 * \ingroup config_prod
 *
 * \brief Время жизни истории
 *
 * Время в секундах  по истечению которого история по запосам пользователей,и по истории ошибок будет удалена
 * \code
 * Config::set('time_clean_history', 3600 );
 * Config::set('time_clean_user_error', 3600 );
 * \endcode
 *
 * time clean history
 * день  86400 секунд
 * неделя 604800 секунд
 * месяц 2629743 секунд
 * год  31556926 секунд
 */
Config::set('time_clean_history', 3600 );

Config::set('time_clean_user_error', 3600 );
/**
 * \defgroup timeout_cabletest  Задержка времени после запуска кабель-теста
 * \ingroup config_prod
 *
 * \brief Задержка времени после запуска кабель-теста
 *
 * Задержка по времени после запуска кабель теста для свичей разных производителей.
 * Указанное время подоброну практическим путем, возможно его изменение в меньшую сторону. Если время задержки будет
 * не достаточно порт после проведения кабель-теста не успеет включиться что приведет к получению не правильных данных,
 * (будут полученны данные по порту в состоянии 'off')
 * Определение времени задержки:
 * \code
 * Config::set('timeout_cabletest', $timeout_cabletest);
 * \endcode
 * @{
 */
$timeout_cabletest = array(
    'Eltex' => 11,
    'Huawei' => 2,
    'D-Link' => 0,
    'Edge-Core' => 1,
    'D-Link_DES-1210-28' => 8,
    'D-Link_DGS-1100-06/ME' => 8
);
Config::set('timeout_cabletest', $timeout_cabletest);
/*! @} */


/**
 * \defgroup  timeout_bite_velocity Задержка времени между измерениями битов входящих и исходящих
 * \ingroup config
 *
 * \brief Задержка времени между измерениями битов входящих и исходящих
 *
 * Задержка времени после измерения счетчика битов входищих и исходящих. Получение этих данных осуществляется
 * в одном снмп запросе с получением данных про статус порта. после этого устанавливается задержка по времени. Повторный
 * замер битов происходит с основным запросом снмп для получения основной части данных. По двум данным и общему времени
 * между измерениями вычислется скорость в байтах. Два варианта 'timeout_bite_velocity_default'- установленно по умолчанию
 * для всех запросов, 'timeout_bite_velocity' - устанавливается вместо дефолтового значения при нажатии на кнопку - померять
 * скорость в байтах

 * Определение времени задержки:
 * \code
 * Config::set('timeout_bite_velocity_default',1);
 * Config::set('timeout_bite_velocity',16);
 * \endcode
 * @{
 */
Config::set('timeout_bite_velocity_default',1);
Config::set('timeout_bite_velocity',16);

/*! @} */


/**
 * \defgroup on_off_cabletest  Включение-выключение кабельтеста
 * \ingroup config_prod
 *
 * \brief Режимы запуска приложения с включенным и не включенным кабельтестом
 *
 * Включение и выкл. кабельтеста - гет заросом cabletest=on или cabletest=off и cabletest=onoff - запускается когда не активен порт.
 * Определение режима кабельтеста:
 * \code
 * Config::set('cabletest_on_off', $cabletest);
 * \endcode
 * @{
 */

/**
 * \brief Функция принимает значение запуска кабель теста "ON", "OFF", "ONOFF"
 *
 * Включение и выкл. кабельтеста - get заросом cabletest=on или cabletest=off и cabletest=onoff - запускается когда не активен порт
 *
 */
$cabletest = ($request->get('cabletest') && ($request->get('cabletest') == 'off' || $request->get('cabletest') == 'on') || $request->get('cabletest') == 'onoff') ? $request->get('cabletest') : 'onoff';


Config::set('cabletest_on_off', $cabletest);
/*! @} */

/**
 * \defgroup mode  Режимы работы приложения "test", "prod"
 * \ingroup config_prod
 *
 * \brief Режимы работы приложения "test", "prod"
 *
 * включить-выключить тестовый режим. значение 'test' - тестовый режим с модельной базой данных юзеров, 'prod' -  работа с базой
 * данных билинга.
 * Определение режима кабельтеста:
 * \code
 * Config::set('mode','prod');
 * \endcode
 * @{
 */
Config::set('mode','prod');
/*! @} */

/**
 * \defgroup cable_length  Дельта длинны кабеля
 * \ingroup config_prod
 *
 * \brief Дельта длинны кабеля
 *
 * При запуске кабельтеста для каждого пользователя записывается полученное значение длины кабеля (может быть перезаписанно).
 * При повторном запросе кабельтеста полученные данные сравниваются с уже записанными. В случае расхождения этих данных
 * выводится предпреждение. При сравнении данных длины кабеля используется дельта длинны кабеля которая отображает
 * погрешность измерений.
 * Определение дельты длинны кабеля:
 * \code
 * Config::set('delta_cable_langth', 1);
 * \endcode
 * @{
 */
Config::set('delta_cable_langth', 1);
/*! @} */

/**
 * \defgroup code_time  Время отработки скрипта
 * \ingroup config_prod
 *
 * \brief Время отработки скрипта
 *
 * Время отработки скрипта, два значения "on", "off" если включено, в конце таблици будет графа "Время отработки свича, с"
 * Определение:
 * \code
 * Config::set('time_switch_response','off');
 * \endcode
 * @{
 */
Config::set('time_switch_response','on');
/*! @} */

/**
 * \defgroup data_view  Форма отображения некоторых данных
 * \ingroup config_prod
 *
 * \brief Вид отображения производителя, модели, версии прошивки свича, дуплекса, скорости.
 *
 *Вид отображения информации о свиче "string"-производитель, модель, версия пошивки все в строку, "table"-в виде таблици
 * \code
 * Config::set('switch_info_view','string');
 * \endcode
 * Вид отображения информации дуплекс и скорость "string"-дуплекс, скорость в строку, "table"-в виде таблици
 * \code
 *Config::set('duplex_speed_view','string');
 * \endcode
 * @{
 */
//Вид отображения информации о свиче "string"-производитель, модель, версия пошивки все в строку, "table"-в виде таблици
Config::set('switch_info_view','string');

//Вид отображения информации дуплекс и скорость "string"-дуплекс, скорость в строку, "table"-в виде таблици
Config::set('duplex_speed_view','string');
/*! @} */

/**
 * \defgroup switch_manufacturer  Производители свичей
 * \ingroup config_prod
 *
 * \brief Наименование свичей
 *
 * Массив содержащий наименование всех производителей свичей.
 * Определение:
 * \code
 * Config::set('switch_manufacturer', $switch_manufacturer);
 * \endcode
 * @{
 */
$switch_manufacturer = array('Huawei', 'D-Link', 'Eltex', 'TP-Link', 'Cisco', 'Edge-Core', 'ZyXel');

Config::set('switch_manufacturer', $switch_manufacturer);
/*! @} */

/**
 * \defgroup host_for_link  Имя хостинга для ссылок
 * \ingroup config_prod
 *
 * \brief  Имя хостинга для ссылок
 *
 * Имя хостинга которое используется для формирование некоторых ссылок
 * Определение:
 * \code
 * Config::set('host_for_link', 'http://test.naic.29632.as');
 * \endcode
 * @{
 */
Config::set('host_for_link', 'http://test.naic.29632.as');
/*! @} */

/**
 * \defgroup styles  Стили для подсветки сообщений
 * \ingroup config_prod
 *
 * \brief Стили для подсветки сообщений
 *
 * Массив содержащий три варианта стиля для подстветки выпадающих сообщений, принимает стандартные значения warning',
 * 'notice', 'information', если гет запросом не приходят названия классов стилей из биллинга.
 * Определение:
 * \code
 * Config::set('style_class',$style_array);
 * \endcode
 * @{
 */
$style_array = array(
    'warning'=> $request->get('warning') ? $request->get('warning') : 'warning',
    'notice' => $request->get('notice') ? $request->get('notice') : 'notice',
    'information' => $request->get('information') ? $request->get('information') : 'information',
);

Config::set('style_class',$style_array);
/*! @} */

/**
 * \defgroup critical_temperature  Лимиты температур для свичей
 * \ingroup config
 *
 * \brief Лимиты температур для свичей, oid, диапазон для предупреждения
 *
 * Массив содержит для каждого произодителя значения максимальных и минимальных температур (сильно отличается
 * от температур эксплуатации из мануалов), также содержит пароговое значение при котором выдается предупреждение. Также
 * oid  по которым для даннго производителя можно посмотреть лимиты температур.
 * @{
 */
$critical_temperature = array(
    'Huawei' =>array(
        'warning' =>10,
        'max' => 84,
        'min' => 0,
        'max_oid' =>'.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.12.67108873',
        'min_oid' => '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.16.67108873'
    ),
    'D-Link' => array(
        'warning' =>10,
        'max' => 79,
        'min' => 11,
        'max_oid' =>'.1.3.6.1.4.1.171.12.11.1.8.1.3.1',
        'min_oid' => '.1.3.6.1.4.1.171.12.11.1.8.1.4.1'
    ),
    'Eltex' => array(
        'warning' =>10,
        'max' => 79,
        'min' => 0,
        'max_oid' =>'',
        'min_oid' => ''
    ),
);

Config::set('critical_temperature', $critical_temperature);
/*! @} */

/**
 * \defgroup critical_cpu_loading  Перегрузка процессора свича
 * \ingroup config
 *
 * \brief Критическая загрузка процессора свича
 *
 * Значение (в процентах) загрузки свича при котором предположительно могут возникнуть сбои в работе оборудования
 * @{
 */
Config::set('critical_cpu_loading',70);
/*! @} */
