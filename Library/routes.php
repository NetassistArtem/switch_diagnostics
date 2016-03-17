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



);