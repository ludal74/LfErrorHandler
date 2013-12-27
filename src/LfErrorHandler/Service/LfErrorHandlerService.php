<?php
namespace LfErrorHandler\Service;

class LfErrorHandlerService
{
	protected $logFile;
	protected $enabled;
	
	function __construct( $config )
	{
		$this->logFile         = $config["filePath"];
		$this->enabled		   = $config["enabled"];
	}
	
	public function log( $message )
	{
		if( $this->enabled )
		{
			$logger = new \Zend\Log\Logger;
			$logger->addWriter('stream', null, array('stream' => $this->logFile ));
			$logger->registerErrorHandler($logger);
			$logger->unregisterExceptionHandler($logger);
			$logger->ERR( $message."\r\n" );
			$logger->__destruct();
		}
		
		return;
	}
}