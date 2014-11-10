<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      
 * @copyright Yuhai Liu
 * @license   
 */
namespace Users;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Users\Model\User;
use Users\Model\UserTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;
use Users\Model\OrgnizationTable;
use Users\Model\Orgnization;
use Users\Model\UserInfo;
use Users\Model\UserInfoTable;
use Users\Model\RequestJoin;
use Users\Model\RequestJoinTable;
use Users\Model\UserOrg;
use Users\Model\UserOrgTable;
use Users\Model\TargetMembers;
use Users\Model\TargetMembersTable;
use Users\Model\PushStack;
use Users\Model\PushStackTable;
use Users\Model\CommentTable;
use Users\Model\Comment;

class Module implements AutoloaderProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sharedEventManager = $eventManager->getSharedManager(); // The shared event manager
        
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, function ($e)
        {
            $controller = $e->getTarget(); // The controller which is dispatched
            $controllerName = $controller->getEvent()
                ->getRouteMatch()
                ->getParam('controller');
            
            if (! in_array($controllerName, array(
                'Users\Controller\Index',
                'Users\Controller\Register',
                'Users\Controller\ResetPassword',
                'Users\Controller\Login',
                'Users\Controller\Home',
                'Users\Controller\Info'
            ))) {
                $controller->layout('layout/frame');
            }
        });
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(),
            'factories' => array(
                // SERVICES
                'AuthService' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'users', 'email', 'password', 'MD5(?)');
                    
                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    return $authService;
                },
                
                // DB
                
                // tables
                'CommentTable' => function ($sm)
                {
                    $tableGateway = $sm->get('CommentTableGateway');
                    $table = new CommentTable($tableGateway);
                    return $table;
                },
                'CommentTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Comment());
                    return new TableGateway('comment', $dbAdapter, null, $resultSetPrototype);
                },
                'PushStackTable' => function ($sm)
                {
                    $tableGateway = $sm->get('PushStackTableGateway');
                    $table = new PushStackTable($tableGateway);
                    return $table;
                },
                'PushStackTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PushStack());
                    return new TableGateway('pushStack', $dbAdapter, null, $resultSetPrototype);
                },
                'TargetMembersTable' => function ($sm)
                {
                    $tableGateway = $sm->get('TargetMembersTableGateway');
                    $table = new TargetMembersTable($tableGateway);
                    return $table;
                },
                'TargetMembersTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TargetMembers());
                    return new TableGateway('targetMembers', $dbAdapter, null, $resultSetPrototype);
                },
                'UserTable' => function ($sm)
                {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                'UserInfoTable' => function ($sm)
                {
                    $tableGateway = $sm->get('UserInfoTableGateway');
                    $table = new UserInfoTable($tableGateway);
                    return $table;
                },
                'UserInfoTableGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserInfo());
                    return new TableGateway('userInfo', $dbAdapter, null, $resultSetPrototype);
                },
                'OrgnizationTable' => function ($sm)
                {
                    $tableGateway = $sm->get('OrgnizationGateway');
                    $table = new OrgnizationTable($tableGateway);
                    return $table;
                },
                'OrgnizationGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Orgnization());
                    return new TableGateway('orgnization', $dbAdapter, null, $resultSetPrototype);
                },
                'RequestJoinTable' => function ($sm)
                {
                    $tableGateway = $sm->get('RequestJoinGateway');
                    $table = new RequestJoinTable($tableGateway);
                    return $table;
                },
                'RequestJoinGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new RequestJoin());
                    return new TableGateway('request_join', $dbAdapter, null, $resultSetPrototype);
                },
                'UserOrgTable' => function ($sm)
                {
                    $tableGateway = $sm->get('UserOrgGateway');
                    $table = new UserOrgTable($tableGateway);
                    return $table;
                },
                'UserOrgGateway' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserOrg());
                    return new TableGateway('user_org', $dbAdapter, null, $resultSetPrototype);
                },
                
                // FORMS
                'LoginForm' => function ($sm)
                {
                    $form = new \Users\Form\LoginForm();
                    $form->setInputFilter($sm->get('LoginFilter'));
                    return $form;
                },
                'RegisterForm' => function ($sm)
                {
                    $form = new \Users\Form\RegisterForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    return $form;
                },
                'ImageUploadForm' => function ($sm)
                {
                    $form = new \Users\Form\ImageUploadForm();
                    $form->setInputFilter($sm->get('ImageUploadFilter'));
                    return $form;
                },
                'UserSetForm' => function ($sm)
                {
                    $form = new \Users\Form\UserSetForm();
                    $form->setInputFilter($sm->get('UserSetFilter'));
                    return $form;
                },
                'ChangePasswordForm' => function ($sm)
                {
                    $form = new \Users\Form\ChangePasswordForm();
                    $form->setInputFilter($sm->get('ChangePasswordFilter'));
                    return $form;
                },
                'ConfirmEmailForm' => function ($sm)
                {
                    $form = new \Users\Form\ConfirmEmailForm();
                    return $form;
                },
                'ConfirmCaptchaForm' => function ($sm)
                {
                    $form = new \Users\Form\ConfirmCaptchaForm();
                    return $form;
                },
                'ResetPasswordForm' => function ($sm)
                {
                    $form = new \Users\Form\ResetPasswordForm();
                    return $form;
                },
                'OrgSetForm' => function ($sm)
                {
                    $form = new \Users\Form\OrgSetForm();
                    return $form;
                },
                'OrgSearchForm' => function ($sm)
                {
                    $form = new \Users\Form\OrgSearchForm();
                    return $form;
                },
                'JoinOrgForm' => function ($sm)
                {
                    $form = new \Users\Form\JoinOrgForm();
                    return $form;
                },
                
                // FILTERS
                'LoginFilter' => function ($sm)
                {
                    return new \Users\Form\LoginFilter();
                },
                'RegisterFilter' => function ($sm)
                {
                    return new \Users\Form\RegisterFilter();
                },
                'ImageUploadFilter' => function ($sm)
                {
                    return new \Users\Form\ImageUploadFilter();
                },
                'UserSetFilter' => function ($sm)
                {
                    return new \Users\Form\UserSetFilter();
                },
                'ChangePasswordFilter' => function ($sm)
                {
                    return new \Users\Form\ChangePasswordFilter();
                }
            ),
            'invokables' => array(),
            'services' => array(),
            'shared' => array()
        );
    }
}
