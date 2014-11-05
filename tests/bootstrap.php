<?php
namespace Users;

use Zend\Loader\AutoloaderFactory;
use zend\mvc\service\ServiceManagerConfig;
use zend\servicemanager\ServiceManager;
use zend\stdlib\ArrayUtils;
use runtimeexception;
error_reporting(E_ALL | E_STRICT);

chdir(__DIR__);

class Bootstrap
{

    protected static $serviceManager;

    protected static $config;

    protected static $bootstrap;

    public static function init()
    {
        if (is_readable(__DIR__ . '/TestConfig.php')) {
            $testConfig = include __DIR__ . '/TestConfig.php';
        } else {
            $testConfig = include __DIR__ . '/TestConfiguration.php.dist';
        }
        
        $zf2ModulePaths = array();
        
        if (isset($testConfig['module_listener_options']['module_paths'])) {
            $modulePaths = $testConfig['module_listener_options']['module_paths'];
            
            foreach ($modulePaths as $modulePath) {
                if ($path = static::findParentPath($modulePath)) {
                    $zf2ModulePaths[] = $path;
                }
            }
            
            $zf2ModulePaths = implode(PATH_SEPARATOR, $zf2ModulePaths) . PATH_SEPARATOR;
            
            $zf2ModulePaths .= getenv('ZF_MODULES_TEST_PATHS') ?  : (defined('ZF_MODULES_TEST_PATHS') ? ZF_MODULES_TEST_PATHS : '');
            
            static::initAutoloader();
            
            $baseConfig = array(
                'module_listener_options' => array(
                    'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths)
                )
            );
            
            $config = ArrayUtils::merge($baseConfig, $testConfig);
            
            $serviceManager = new ServiceManager(new ServiceManagerConfig());
            
            $serviceManager->setService('Users', $config);
            
            $serviceManager->get('ModuleManager')->loadModules();
            
            static::$serviceManager = $serviceManager;
            static::$config = $config;
        }
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getConfig()
    {
        return static::$config;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');
        
        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            throw new RuntimeException('Unable to load ZF2.');
        }
        
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__
                )
            )
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        
        while (! is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            
            if ($previousDir === $dir)
                return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();