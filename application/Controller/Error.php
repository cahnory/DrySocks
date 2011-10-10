<?php

	namespace Controller;
	
	class Error extends \DS\Controller
	{
		public function error404(\Exception $exception) {
			$this->app->view->setHeader('HTTP/1.0 404 Not Found');
			$this->app->template->put('Error/404', array('exception' => $exception));
		}
		public function error500(\Exception $exception) {
			$this->app->view->setHeader('HTTP/1.0 500 Internal Server Error');
			$this->app->template->put('Error/500', array('exception' => $exception));
		}
	}

?>