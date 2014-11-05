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
            'Users\Controller\Index' => 'Users\Controller\IndexController',
            'Users\Controller\Info' => 'Users\Controller\InfoController',
            'Users\Controller\Login' => 'Users\Controller\LoginController',
            'Users\Controller\ResetPassword' => 'Users\Controller\ResetPasswordController',
            'Users\Controller\Register' => 'Users\Controller\RegisterController',
            'Users\Controller\Setting' => 'Users\Controller\SettingController',
            'Users\Controller\MediaManager' => 'Users\Controller\MediaManagerController',
            'Users\Controller\Target' => 'Users\Controller\TargetController',
            'Users\Controller\Helper' => 'Users\Controller\HelperController',
            'Users\Controller\Home' => 'Users\Controller\HomeController',
            'Users\Controller\Admin' => 'Users\Controller\AdminController',
            
            'Users\Controller\WebServiceLogin' => 'Users\Controller\WebServiceLoginController',
            'Users\Controller\WebServiceRegister' => 'Users\Controller\WebServiceRegisterController',
            'Users\Controller\WebServiceHelper' => 'Users\Controller\WebServiceHelperController',
            'Users\Controller\WebServiceTarget' => 'Users\Controller\WebServiceTargetController',
            'Users\Controller\WebServiceTransaction' => 'Users\Controller\WebServiceTransactionController',
            'Users\Controller\WebServiceImageUpLoad' => 'Users\Controller\WebServiceImageUpLoadController',
            'Users\Controller\Download' => 'Users\Controller\DownloadController',
            'Users\Controller\PushManagement' => 'Users\Controller\PushManagementController',
            'Users\Controller\CheckNewestVersion' => 'Users\Controller\CheckNewestVersionController',
        )
    ),
    'router' => array(
        'routes' => array(
            // set routes
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
            ), // end webservicelogin
            'admin' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/admin[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Admin',
                        'action' => 'index'
                    )
                )
            ), // end admin
            'info' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/info[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Info',
                        'action' => 'index'
                    )
                )
            ), // end info
            'target' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/target[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Target',
                        'action' => 'index'
                    )
                )
            ), // end target
            'helper' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/helper[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Helper',
                        'action' => 'index'
                    )
                )
            ), // end helper
            'home' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/home[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Home',
                        'action' => 'index'
                    )
                )
            ), // end home
            'users' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/users',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                
                'may_terminate' => true,
                'child_routes' => array(
                    'login' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/login[/:action[/:tabs]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Login',
                                'action' => 'index'
                            )
                        )
                    ), // end of login
                    'resetPassword' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/resetPassword[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\ResetPassword',
                                'action' => 'index'
                            )
                        )
                    ), // end of resetPasword
                    'register' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/register[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Register',
                                'action' => 'index'
                            )
                        )
                    ), // end of register
                    'setting' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/setting[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Setting',
                                'action' => 'index'
                            )
                        )
                    ), // end of setting
                    'upload-manager' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/upload-manager[/:action[/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\UploadManager',
                                'action' => 'index'
                            )
                        )
                    ), // end of upload-manager
                    'media' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/media[/:action[/:key[/:subaction]]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'subaction' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\MediaManager',
                                'action' => 'index'
                            )
                        )
                    ) // end of media
                                )
            )
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
