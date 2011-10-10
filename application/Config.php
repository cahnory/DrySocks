<?php

	require_once	'../library/DS/Application/Config.php';
	
	class Config extends DS\Application\Config
	{
		public function apply(DS\Application $app) {
			parent::apply($app);
			
			if(!$app->request->isCli()) {
				$app->setErrorController('Error');
				$app->request->setBase('../');
				
				// View template
				$template		= new DS\View\Template($app->getView());
				$app->template	= $template;
				$template->request	= $app->request;
				$template->router	= $app->router;
				$template->title	= 'DrySocks framework';
				
				$template->setPath('View/');
				$template->setFormat($app->request->getFormat());
				
				// Router
				$app->router->setCacheFile('Cache/router.txt');
				$app->router->bind('<:end>', 			'Document'); // homepage
				$app->router->bind('Document/<:rest>',	'Document/read/$1');
			}
		}
	}

?>