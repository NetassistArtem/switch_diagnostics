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



);