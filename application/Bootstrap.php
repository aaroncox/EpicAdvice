<?php

function fb($message, $label=null)
{
  if(APPLICATION_ENV != "production")
  {
    if ($label!=null) {
        $message = array($label,$message);
    }
    Zend_Registry::get('logger')->debug($message);    
  }
}

class Bootstrap extends MW_Application_Bootstrap
{
  public function _initApplication()
  {
		Zend_Paginator::setDefaultItemCountPerPage(25);
    Zend_Session::start();
    $this->bootstrap(array('mongo','MwView','layout','frontController')); 
		if(APPLICATION_ENV != "production") {
			$logger = new Zend_Log();
			$writer = new Zend_Log_Writer_Firebug();
			$logger->addWriter($writer);
			Zend_Registry::set('logger',$logger);
		} 
		
		$view = $this->getResource('view');
		// This needs to be abstracted elsewhere...
		$view->bundleScript()
			->setCacheDir(APPLICATION_PATH . '/../cache/js')
			->setDocRoot(APPLICATION_PATH . '/../public_html')
			->setUseGzip(true)
			->setGzipLevel(9)
			->setUrlPrefix('j');

    $view->bundleLink()
			->setCacheDir(APPLICATION_PATH . '/../cache/css')
			->setDocRoot(APPLICATION_PATH . '/../public_html')
			->setUseGzip(true)
			->setGzipLevel(9)
			->setUrlPrefix('c');
		
		
  }

}