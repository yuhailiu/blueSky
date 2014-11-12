<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      
 * @copyright Copyright (c) 2005-2013 Yuhai liu
 * @license    
 */
return array(
    'controllers' => array(
        'invokables' => array(
            
            'Users\Controller\WebServiceLogin' => 'Users\Controller\WebServiceLoginController',
            'Users\Controller\WebServiceRegister' => 'Users\Controller\WebServiceRegisterController',
            'Users\Controller\WebServiceHelper' => 'Users\Controller\WebServiceHelperController',
            'Users\Controller\WebServiceTarget' => 'Users\Controller\WebServiceTarget1Controller',
            'Users\Controller\WebServiceTransaction' => 'Users\Controller\WebServiceTransactionController',
            'Users\Controller\WebServiceImageUpLoad' => 'Users\Controller\WebServiceImageUpLoadController',
            'Users\Controller\Download' => 'Users\Controller\DownloadController',
            'Users\Controller\PushManagement' => 'Users\Controller\PushManagementController',
            'Users\Controller\CheckNewestVersion' => 'Users\Controller\CheckNewestVersionController',
            'Users\Controller\Comment' => 'Users\Controller\WebServiceCommentController',
            
            'Users\Controller\TestNewController' => 'Users\Controller\WebServiceHelper1Controller'
        )
    ),
    'router' => array(
        'routes' => array(
            // set routes
            'testNewController' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/TestNew[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'TestNewController',
                        'action' => 'index'
                    )
                )
            ), // end TestNewController
               // set routes
            'comment' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/WebServiceComment[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Comment',
                        'action' => 'index'
                    )
                )
            ), // end TestNewController
            'checkNewestVersion' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/CheckNewestVersion[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'CheckNewestVersion',
                        'action' => 'index'
                    )
                )
            ), // end CheckNewestVersion
            'pushManagement' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/PushManagement[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'PushManagement',
                        'action' => 'index'
                    )
                )
            ), // end PushManagementController
            'download' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/Download[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Download',
                        'action' => 'index'
                    )
                )
            ), // end DownloadController
            'webserviceimageupload' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/WebServiceImageUpLoad[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceImageUpLoad',
                        'action' => 'index'
                    )
                )
            ), // end WebServiceImageUpLoadController
            'webservicetransaction' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/webserviceTransaction[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceTransaction',
                        'action' => 'index'
                    )
                )
            ), // end webserviceTransaction
            'webservicetarget' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/webservicetarget[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceTarget',
                        'action' => 'index'
                    )
                )
            ), // end webserviceTarget
            'webservicehelper' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/webservicehelper[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceHelper',
                        'action' => 'index'
                    )
                )
            ), // end webserviceHelper
            'webserviceregister' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/webserviceregister[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceRegister',
                        'action' => 'index'
                    )
                )
            ), // end webserviceRegister
            'webservicelogin' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/webservicelogin[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'WebServiceLogin',
                        'action' => 'index'
                    )
                )
            ) // end webservicelogin
                )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Users' => __DIR__ . '/../view'
        ),
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/default-layout.phtml',
            'layout/frame' => __DIR__ . '/../view/layout/frame-layout.phtml'
        )
    ),
    // MODULE CONFIGURATIONS
    'module_config' => array(
        'upload_location' => __DIR__ . '/../data/uploads',
        'image_upload_location' => __DIR__ . '/../../../data/images',
        'search_index' => __DIR__ . '/../data/search_index',
        'certifications' => __DIR__ . '/../../../data/certifications',
        'download' => __DIR__ . '/../../../data/download'
    )
);
