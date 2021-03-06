<?php
/**
 * DrySocks Framework
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   DS
 * @package	DS\Doctrine
 * @author	 François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license	http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;
use \Doctrine\ORM\Configuration,
	\Doctrine\ORM\EntityManager,
	\Doctrine\Common\Cache\ArrayCache,
	\Doctrine\Common\Cache\ApcCache,
	\Doctrine\DBAL\Logging\EchoSQLLogger;

/**
 * Class for dealing with class files.
 *
 * @category   DS
 * @package	DS\Doctrine
 * @author	 François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license	http://www.opensource.org/licenses/mit-license.php
 */
class Doctrine {

	protected	$em;

	public function __construct($options = array(), $devMode = false)
	{
		$options	= new \DS\ArrayObject($options);
		
		$loader	= new \DS\ClassLoader('Model/Proxy', 'Proxy');
		$loader->register();
		$loader	= new \DS\ClassLoader('Model/Entity', 'Entity');
		$loader->register();
		
		// Set up caches
		$config = new Configuration;
		$cache	= $devMode || !function_exists('apc_fetch') ? new ArrayCache : new ApcCache;
		$config->setMetadataCacheImpl($cache);
		$driverImpl = $config->newDefaultAnnotationDriver(array('Model/Entity'));
		$config->setMetadataDriverImpl($driverImpl);
		$config->setQueryCacheImpl($cache);
		
		// Proxy configuration
		$config->setProxyDir('Model/Proxy');
		$config->setProxyNamespace('Proxy');
		$config->setEntityNamespaces(array('Entity'));
		$config->setAutoGenerateProxyClasses($devMode);
		
		// Database connection information
		$connectionOptions = array(
			'driver'	=> $options->driver		?: 'pdo_mysql',
			'user'		=> $options->username,
			'password'	=> $options->password,
			'host'		=> $options->hostname	?: 'localhost',
			'dbname'	=> $options->database
		);
		
		// Create EntityManager
		$this->em = EntityManager::create($connectionOptions, $config);
	}
  
	public function getEntityManager() {
		return	$this->em;
	}
}

?>