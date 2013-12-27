<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ErrorHandler for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LfErrorHandler;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig() {
    	return array(
    			'factories' => array(
    					'LfErrorHandlerService' => function ($sm) {
    						$config = $sm->get('config');
    						return new \LfErrorHandler\Service\LfErrorHandlerService($config['LfErrorHandler']);
    					},
    			),
    	);
    }

    public function onBootstrap($mvcEvent)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        
        $eventManager        = $mvcEvent->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $services = $mvcEvent->getApplication()->getServiceManager();

        $config = $services->get("Config");
        $configErrorHandler = $config["LfErrorHandler"];
        
        $logger = new \Zend\Log\Logger;
        $logger->addWriter('stream', null, array('stream' => $configErrorHandler["filePath"] ));
        $logger->registerErrorHandler($logger);
        $logger->unregisterExceptionHandler($logger);

        register_shutdown_function(function () use ($logger)
        {
        	if ($e = error_get_last()) 
        	{
        		$logger->ERR( "\r\n".$e['message'] . " in " . $e['file'] . ' line ' . $e['line']);
        		$logger->__destruct();
        	}
        });
    }

}
