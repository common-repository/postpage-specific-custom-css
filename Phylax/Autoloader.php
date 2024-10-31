<?php
/**
 * Minimal requirements for this version: PHP 7.1
 *
 * @version   0.5.1
 * @author    Łukasz Nowicki <kontakt@phylax.pl>
 * @copyright Łukasz Nowicki
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later
 * @link      https://github.com/lukasznowicki/psr-autoloader
 */

namespace Phylax;

use Exception;

use const DIRECTORY_SEPARATOR;

/**
 * Class Autoloader
 *
 * Use this class to handle auto-loading classes, traits, implementations and abstracts.
 * You may declare namespace and proper directory in the constructor (usually use this
 * feature when you got only one) or by using registerHandler and addNamespace methods.
 */
class Autoloader {

	/**
	 * The namespace/directory pairs stack. This will be used to store provided
	 * pairs.
	 *
	 * @var array
	 */
	protected $namespaces = [];

	/**
	 * Autoloader constructor. You may assign namespace/directory pair in the constructor.
	 * It is useful, if you have good PSR-4 directory structure. If you do, the register
	 * handler method will be invoked automatically.
	 * This method will use standard class methods to add the namespace/directory into
	 * the stack, so both will be sanitized as in addNamespace() method.
	 *
	 * @param  string|null  $namespace  Namespace you want to set
	 * @param  string|null  $directory  Directory you want to set
	 * @param  bool  $exitOnFail  Set this to false, if you have any fallback for class loader
	 *
	 * @see Autoloader::registerHandler()
	 * @see Autoloader::addNamespace()
	 * @see Autoloader::$namespaces
	 */
	public function __construct( ?string $namespace = null, ?string $directory = null, bool $exitOnFail = true ) {
		if ( ! is_null( $namespace ) && ! is_null( $directory ) ) {
			$this->registerHandler( $exitOnFail );
			$this->addNamespace( $namespace, $directory );
		}
	}

	/**
	 * This method will register the class auto-loading handler (using PHP
	 * spl_autoload_register function). You may choose, whether you want
	 * your application to exit on fail (default) or not.
	 *
	 * @param  bool  $exitOnFail  Set this to false, if you have any fallback for class loader
	 *
	 * @return bool
	 *
	 * @see spl_autoload_register()
	 */
	public function registerHandler( bool $exitOnFail = true ): bool {
		try {
			spl_autoload_register( [
				$this,
				'classLoader',
			] );
		} catch ( Exception $exception ) {
			if ( $exitOnFail ) {
				exit;
			}

			return false;
		}

		return true;
	}

	/**
	 * Use this method to add namespace to the stack. Before the addition, both namespace
	 * and directory will be sanitized to be a valid namespace and directory. Valid,
	 * does not mean - existing one. It only means proper formatting using \ for
	 * namespaces (you may, but you should not provide \ or /) and valid directory
	 * separator for current operating system, using PHP constant DIRECTORY_SEPARATOR.
	 *
	 * @param  string  $namespace  Namespace you want to set
	 * @param  string  $directory  Directory you want to set
	 * @param  bool  $prepend  If you want to add the namespace/directory pair in front of the stack, just set it to true.
	 *
	 * @return bool
	 *
	 * @see Autoloader::$namespaces
	 * @see Autoloader::prepareNamespace()
	 * @see Autoloader::prepareDirectory()
	 */
	public function addNamespace( string $namespace, string $directory, bool $prepend = false ): bool {
		$namespace = $this->prepareNamespace( $namespace );
		$directory = $this->prepareDirectory( $directory );
		if ( ! isset( $this->namespaces[ $namespace ] ) ) {
			$this->namespaces[ $namespace ] = [];
		}
		if ( $prepend ) {
			array_unshift( $this->namespaces[ $namespace ], $directory );
		} else {
			array_push( $this->namespaces[ $namespace ], $directory );
		}

		return true;
	}

	/**
	 * This method will prepare namespace to be valid for PHP. It will replace
	 * / and \ characters with the valid one \. So you may use directory as
	 * the namespace as well. Anyway, you should not, because it's not pretty.
	 *
	 * @param  string  $namespace
	 *
	 * @return string
	 *
	 * @see Autoloader::prepareString()
	 */
	protected function prepareNamespace( string $namespace ): string {
		return $this->prepareString( $namespace, '\\' );
	}

	/**
	 * This method will change all / and \ occurrences with the provided
	 * character.
	 *
	 * @param  string  $string  String to find in and replace the character
	 * @param  string  $inUse  String to be a replacement
	 *
	 * @return string
	 *
	 * @see Autoloader::prepareDirectory()
	 * @see Autoloader::prepareNamespace()
	 */
	protected function prepareString( string $string, string $inUse ): string {
		$string = str_replace( [
			'\\',
			'/',
		], $inUse, $string );

		return rtrim( $string, $inUse ) . $inUse;
	}

	/**
	 * This method will prepare directory to a proper format, using
	 * proper character as a directory separator. Of course, we can
	 * use / because it works for both Linux and Windows. We do that
	 * because I want everything to looks pretty.
	 *
	 * @param  string  $directory
	 *
	 * @return string
	 *
	 * @see Autoloader::prepareString()
	 */
	protected function prepareDirectory( string $directory ): string {
		return $this->prepareString( $directory, DIRECTORY_SEPARATOR );
	}

	/**
	 * This method will search for the proper class name in the declared
	 * namespaces list. It will check every possible combination until
	 * find proper file (will be loaded) or fail.
	 *
	 * @param  string  $class
	 *
	 * @return string|null
	 *
	 * @see Autoloader::$namespaces
	 * @see Autoloader::checkMappedFile()
	 * @see Autoloader::callFile()
	 */
	public function classLoader( string $class ): ?string {
		$classPrefix = $class;
		while ( false !== $position = strrpos( $classPrefix, '\\' ) ) {
			$classPrefix = substr( $class, 0, $position + 1 );
			$relClass    = substr( $class, $position + 1 );
			$mapFile     = $this->checkMappedFile( $classPrefix, $relClass );
			if ( $mapFile ) {
				return $mapFile;
			}
			$classPrefix = rtrim( $classPrefix, '\\' );
		}

		return null;
	}

	/**
	 * This method will check a combination of directories for called
	 * class using callFile. If it finds a proper combination, then
	 * a class file will be loaded.
	 *
	 * @param  string  $namespace
	 * @param  string  $relClass
	 *
	 * @return string|null
	 *
	 * @see Autoloader::$namespaces
	 * @see Autoloader::callFile()
	 */
	protected function checkMappedFile( string $namespace, string $relClass ): ?string {
		if ( false === isset( $this->namespaces[ $namespace ] ) ) {
			return null;
		}
		foreach ( $this->namespaces[ $namespace ] as $baseDir ) {
			$filePath = $baseDir . str_replace( '\\', DIRECTORY_SEPARATOR, $relClass ) . '.php';
			if ( $this->callFile( $filePath ) ) {
				return $filePath;
			}
		}

		return null;
	}

	/**
	 * This method will load the class file, if it is readable,
	 * and it is not a directory, which is obvious.
	 *
	 * @param  string  $filePath
	 *
	 * @return bool
	 */
	protected function callFile( string $filePath ): bool {
		if ( is_readable( $filePath ) && ! is_dir( $filePath ) ) {
			require_once $filePath;

			return true;
		}

		return false;
	}

}
