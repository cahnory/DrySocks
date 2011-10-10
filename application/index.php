<?php
	
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	
	require_once	'Config.php';
	$config	= new Config;
	$app	= new DS\Application($config);
	
	$p	= new \DS\Password\Protector;
	
	/* Set the hash algorithm
	 * sha512 is used by default so this has no effect
	 */
	$p->setAlgorithm('sha512');
	
	/* Set unic salt properties
	 * The generated salt value is added after the hash.
	 * 
	 * First agument is its length, second one is its position (0 by default)
	 */
	$p->setUnicSalt(64, 64);
	
	/* Set a shared salt value
	 * This salt is always the same and is added before hashing the password.
	 * In case where, the unic salt length and position are found, this salt
	 * prevent the use of a hash dictionary by adding some complexity to the
	 * password.
	 * 
	 * First agument is its value, second one is its position (0 by default)
	 */
	$p->setSalt('465ez*4f-5d(2f~4cEdq', 4);
	
	/* Set a function which will edit the hash
	 * Used to add a transormation that is unic to your app.
	 * 
	 * Use with caution, you have to know what you're doing.
	 */
	$p->setProcessor(function($hash) {
		// apply transformation here and return the modified hash
		return	$hash;
	});
	
	/* Return the hashed password.
	 * The hashed password is not always the same as a new salt is
	 * generated each time you call this method.
	 */
	$hash	=	$p->hash('myPassword');
	
	$p->match('someoneElsePassword', $hash); // false
	$p->match('myPassword', $hash); // true
	
	$app->dispatch();

?>