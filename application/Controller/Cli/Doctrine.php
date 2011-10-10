<?php

	namespace Controller\Cli;
	
	class Doctrine extends \DS\Controller
	{
		protected $helperSet;
		
		public function __construct($app) {
			parent::__construct($app);
			
			$this->app->loader->setPath('../library/Doctrine/Symfony', 'Symfony');

			/*
			// Bind Symfony absolute namespace to it's path
			$this->app->loader->setPath('../library/Doctrine/Symfony', 'Symfony');
			
			$classLoader	= new \Doctrine\Common\ClassLoader('Doctrine', '../library');
			$classLoader->register();
			

			/*$classLoader = new \Doctrine\Common\ClassLoader('Entities', __DIR__);
			$classLoader->register();
			$classLoader = new \Doctrine\Common\ClassLoader('Proxies', __DIR__);
			$classLoader->register();
			*/
			
			/*$config = new \Doctrine\ORM\Configuration();
			$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
			$config->addEntityNamespace('', 'Entity');
			
			$driver = $config->newDefaultAnnotationDriver(array("Entity/Mapping"));
			$config->setMetadataDriverImpl($driver);
			
			$config->setProxyDir('Entity/Proxy');
			$config->setProxyNamespace('Proxy');
			$connectionOptions = array(
			    'driver'	=> 'pdo_mysql',
			    'path'		=> 'database.mysql'
			);*/
			
			$em = $app->doctrine->getEntityManager();// \Doctrine\ORM\EntityManager::create($connectionOptions, $config);
			
			$helpers = array(
			    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
			    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
			);
			
			$this->helperSet	= new \Symfony\Component\Console\Helper\HelperSet($helpers);
		}

		public function index() {
			// Remove DS routing argv
			if($_SERVER['argv'][1] == 'Doctrine/index' || $_SERVER['argv'][1] == 'Doctrine') {
				array_splice($_SERVER['argv'], 1, 1);
			}
			\Doctrine\ORM\Tools\Console\ConsoleRunner::run($this->helperSet);
		}
		
		public function generateEntities() {
			array_splice($_SERVER['argv'], 1, 1, 'orm:generate-entities');
			$_SERVER['argv'][2] = isset($_SERVER['argv'][2])
								? realpath($_SERVER['argv'][2])
								: realpath('Doctrine/Mapper');
			$this->index();
		}
		
		public function generateProxies() {
			array_splice($_SERVER['argv'], 1, 1, 'orm:generate-proxies');
			$_SERVER['argv'][2] = isset($_SERVER['argv'][2])
								? realpath($_SERVER['argv'][2])
								: realpath('Doctrine/Proxy');
			$this->index();
		}
	}

?>