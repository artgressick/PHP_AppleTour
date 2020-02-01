<?php
/**
 * DrupalAuthenticatorImpl.php
 *
 * @package MCImageManager.authenicators
 * @author Moxiecode
 * @copyright Copyright © 2005, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is a External authenticator implementation.
 *
 * @package MCImageManager.Authenticators
 */
class Moxiecode_ExternalAuthenticator extends Moxiecode_ManagerPlugin {
    /**#@+
	 * @access public
	 */

	/**
	 * Main constructor.
	 */
	function Moxiecode_ExternalAuthenticator() {
	}

	function onAuthenticate(&$man) {
		$config =& $man->getConfig();

		$extPage = "example_ext_auth.php";

		@session_start();

		// Check session data
		if (isset($_SESSION['mcmanager_ext_auth']) && $_SESSION['mcmanager_ext_auth'] == true) {
			$path = $_SESSION['mcmanager_ext_path'];
			$rootpath = $_SESSION['mcmanager_ext_rootpath'];
			$user = $_SESSION['mcmanager_ext_user'];

			if ($path)
				$config['filesystem.path'] = $path;

			if ($rootpath)
				$config['filesystem.rootpath'] = $rootpath;

			return true;
		}

		// If RPC or stream then return it using config
		$dir = basename(dirname($_SERVER["PHP_SELF"]));
		if ($dir == "rpc" || $dir  == "stream") {
			$config['authenticator.login_page'] = 'plugins/ExternalAuthenticator/' . $extPage;
			return false;
		}

		// Not logged redirect to External backend
		header('location: plugins/ExternalAuthenticator/' . $extPage . "?return_url=" . urlencode('../../pages/' . $config["general.theme"] . "/index.html"));
		die();
	}

	/**#@-*/
}

// Add plugin to MCManager
$man->registerPlugin("ExternalAuthenticator", new Moxiecode_ExternalAuthenticator());

?>