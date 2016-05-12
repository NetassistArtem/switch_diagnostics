<?php
 $routes = array(

    'account_test_id' => array(
        'pattern' => '/account_test/{account_id}',
        'controller' => 'Index',
        'action' => 'snmpData',
        'params' => array(
            'account_id' => '[1-9][0-9]*',

        )
    ),

     'account_test' => array(
         'pattern' => '/account_test',
         'controller' => 'Index',
         'action' => 'index',
         'params' => array(
             'id' => 4
         )
     ),
     'error_404' => array(
         'pattern' => '/error_404',
         'controller' => 'Node',
         'action' => 'index',
         'params' => array(
             'id' => 1
         )
     ),
     'error_500' => array(
         'pattern' => '/error_500',
         'controller' => 'Node',
         'action' => 'index',
         'params' => array(
             'id' => 2
         )
     ),
     'error_403' => array(
         'pattern' => '/error_403',
         'controller' => 'Node',
         'action' => 'index',
         'params' => array(
             'id' => 3
         )
     ),
     'error_SNMP' => array(
         'pattern' => '/error_SNMP',
         'controller' => 'Node',
         'action' => 'index',
         'params' => array(
             'id' => 5
         )
     ),
     'error_mac_switch' => array(
         'pattern' => '/error_mac_switch',
         'controller' => 'Node',
         'action' => 'index',
         'params' => array(
             'id' => 6
         )
     ),
     'account_test_id_history' => array(
         'pattern' => '/account_test/history/{account_id}',
         'controller' => 'Index',
         'action' => 'history',
         'params' => array(
             'account_id' => '[1-9][0-9]*',

         )
     ),
     'account_test_history' => array(
         'pattern' => '/account_test/history',
         'controller' => 'Index',
         'action' => 'historySelect',

     ),
     'error_user' => array(
         'pattern' => '/account_test/user_error',
         'controller' => 'Index',
         'action' => 'errorUser',

     ),
     'insert_cable_length' => array(
         'pattern' => '/account_test/insert_cable_length/{account_id}',
         'controller' => 'Index',
         'action' => 'insertCableLength',
         'params' => array(
             'account_id' => '[1-9][0-9]*',

         )

     ),
     'admin_insert_switch' => array(
         'pattern' => '/account_test/admin/insert_switch',
         'controller' => 'Admin',
         'action' => 'insertSwitch',
     ),

     'admin' => array(
         'pattern' => '/account_test/admin',
         'controller' => 'Admin',
         'action' => 'index',
     ),

     'admin_switch' => array(
         'pattern' => '/account_test/admin/switch_list',
         'controller' => 'Admin',
         'action' => 'switchList',
     ),

     'admin_switch_edit' => array(
         'pattern' => '/account_test/admin/switch_edit/{switch_id}',
         'controller' => 'Admin',
         'action' => 'editSwitch',
         'params' => array(
             'switch_id' => '[1-9][0-9]*',

         )
     ),

     'admin_switch_delete' => array(
         'pattern' => '/account_test/admin/switch_delete/{switch_id}',
         'controller' => 'Admin',
         'action' => 'deleteSwitch',
         'params' => array(
             'switch_id' => '[1-9][0-9]*',

         )
     ),

     'admin_pattern' => array(
         'pattern' => '/account_test/admin/pattern_list',
         'controller' => 'Admin',
         'action' => 'patternList',
     ),
     'admin_insert_pattern' => array(
         'pattern' => '/account_test/admin/insert_pattern',
         'controller' => 'Admin',
         'action' => 'insertPattern',
     ),

     'admin_pattern_edit' => array(
         'pattern' => '/account_test/admin/pattern_edit/{pattern_id}',
         'controller' => 'Admin',
         'action' => 'editPattern',
         'params' => array(
             'pattern_id' => '[1-9][0-9]*',

         )
     ),
     'admin_pattern_delete' => array(
         'pattern' => '/account_test/admin/pattern_delete/{pattern_id}',
         'controller' => 'Admin',
         'action' => 'deletePattern',
         'params' => array(
             'pattern_id' => '[1-9][0-9]*',

         )
     ),



);