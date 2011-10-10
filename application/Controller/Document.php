<?php

	namespace Controller;
	
	class Document extends \DS\Controller
	{
		private	$path		=	'Document/';
		private $mainFormat	=	'html';
		
		public function index() {
			$this->read();
		}
		
		public function read() {
			// Filename
			$filename	=	func_num_args()
						?	str_replace('../', '', implode('/', func_get_args()))
						:	'index';
			
			// Add extension
			$format		=	$this->app->request->getFormat();
			$filename	.=	$format !== NULL
						?	'.'.$format
						:	'.'.$this->mainFormat;
			
			// File not found
			if(!is_readable($this->path.$filename)) {
				throw new \DS\Controller\Exception('Error 404: document "'.$filename.'" not found');	
			}
			
			// Execute file
			$this->app->template->put('Document/read', array(
				'document' => $this->app->template->executeFile($this->path.$filename)
			));
		}
	}

?>