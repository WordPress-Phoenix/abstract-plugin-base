<?php
/**
 * Plugin_Base Class for WordPress Plugins
 *
 * @author  Seth Carstens
 * @package abtract-plugin-base
 * @version 1.1.1
 * @license GPL 2.0 - please retain comments that express original build of this file by the author.
 */

/*
 * Namespace with versions as a solution to composer vs WordPress plugins
 * Reference url https://wptavern.com/a-narrative-of-using-composer-in-a-wordpress-plugin
 */
namespace WPAZ_Plugin_Base\V_1_1;

/**
 * Class Plugin_Base
 */
abstract class Plugin {
	/**
	 * Turn debugging on or off
	 *
	 * @var bool $debug
	 */
	public $debug;

	/**
	 * Plugins installed directory on the server
	 *
	 * @var string $installed_dir
	 */
	public $installed_dir;

	/**
	 * Plugins URL for access to any static files or assets like css, js, or media
	 *
	 * @var string $installed_url
	 */
	public $installed_url;

	/**
	 * Used to hold an instance of the admin object related to the plugin.
	 *
	 * @var null|\stdClass|Plugin $admin
	 */
	public $admin;

	/**
	 * Modules is a collection class that holds the modules / parts of the plugin.
	 *
	 * @var \stdClass $modules
	 */
	public $modules;

	/**
	 * Related WordPress multisite network url with smarter fallbacks to guarantee a value
	 *
	 * @var string $network_url
	 */
	public $network_url;

	/**
	 * Define the folder or folders that spl_autoload should check for custom PHP classes that need autoloaded
	 *
	 * @var array|string $autoload_dir
	 */
	public $autoload_dir = [ '/inc/', '/admin/', '/admin/inc/' ];

	/**
	 * Magic constant trick that allows extended classes to pull actual server file location, copy into subclass.
	 *
	 * @var string $current_file
	 */
	protected $current_file = __FILE__;

	/**
	 * Construct the plugin object.
	 * Note that classes that extend this class should add there construction actions into onload()
	 */
	public function __construct() {

		// Hook can be used by mu plugins to modify plugin behavior after plugin is setup.
		do_action( get_called_class() . '_preface', $this );

		// configure and setup the plugin class variables.
		$this->configure_defaults();

		// Define globals used by the plugin including bloginfo.
		$this->defines_and_globals();

		// Register auto-loading to include any files in the $autoload_dir.
		spl_autoload_register( array( $this, 'autoload' ) );

		// Enable any composer libraries if they exist.
		if ( file_exists( $this->installed_dir . '/vendor/autoload.php' ) ) {
			include_once( $this->installed_dir . '/vendor/autoload.php' );
		}

		// Onload to do things during plugin construction.
		$this->onload( $this );

		// Most actions go into init which loads after WordPress core sets up all the defaults.
		add_action( 'init', array( $this, 'init' ) );

		// Init for use with logged in users, see this::authenticated_init for more details.
		add_action( 'init', array( $this, 'authenticated_init' ) );

		// Hook can be used by mu plugins to modify plugin behavior after plugin is setup.
		do_action( get_called_class() . '_setup', $this );

	} // END public function __construct

	/**
	 * Initialize the plugin - for public (front end)
	 *
	 * @param mixed $instance Parent instance passed through to child.
	 * @since   0.1
	 * @return  void
	 */
	abstract public function onload( $instance );

	/**
	 * Initialize the plugin - for public (front end)
	 * Example of building a module of the plugin into init
	 * ```$this->modules->FS_Mail = new FS_Mail( $this, $this->installed_dir );```
	 *
	 * @since   0.1
	 * @return  void
	 */
	abstract public function init();

	/**
	 * Initialize the plugin - for admin (back end)
	 * You would expected this to be handled on action admin_init, but it does not properly handle
	 * the use case for all logged in user actions. Always keep is_user_logged_in() wrapper within
	 * this function for proper usage.
	 *
	 * @since   0.1
	 * @return  void
	 */
	abstract public function authenticated_init();

	/**
	 * Activated the plugin actions
	 *
	 * @return  void
	 */
	public static function activate() {
	}

	/**
	 * Deactivated the plugin actions
	 *
	 * @return  void
	 */
	public static function deactivate() {
	}

	/**
	 * Enforce that the plugin prepare any defines or globals in a standard location.
	 *
	 * @return mixed
	 */
	abstract protected function defines_and_globals();

	/**
	 * Setup plugins global params.
	 */
	protected function configure_defaults() {
		$this->modules        = new \stdClass();
		$this->modules->count = 0;
		$this->installed_dir  = dirname( $this->current_file );
		$this->installed_url  = plugins_url( '/', $this->current_file );

		// Setup network url and fallback in case siteurl is not defined.
		if ( ! defined( 'WP_NETWORKURL' ) && is_multisite() ) {
			define( 'WP_NETWORKURL', network_site_url() );
		} elseif ( ! defined( 'WP_NETWORKURL' ) ) {
			define( 'WP_NETWORKURL', get_site_url() );
		}
		$this->network_url = WP_NETWORKURL;
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class Raw class name.
	 *
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		$filtered_class_name = explode( '\\', $class );
		$class_filename      = end( $filtered_class_name );

		return 'class-' . str_replace( '_', '-', $class_filename ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path Server path to file for inclusion.
	 *
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );

			return true;
		}

		return false;
	}

	/**
	 * Auto-load classes on demand to reduce memory consumption.
	 *
	 * @param string $class The name of the class object.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		if ( ! is_array( $this->autoload_dir ) ) {
			$this->load_file( $this->installed_dir . $this->autoload_dir . $file );
		} else {
			foreach ( $this->autoload_dir as $dir ) {
				$this->load_file( $this->installed_dir . $dir . $file );
			}
		}
	}

	/**
	 * Build and initialize the plugin.
	 */
	public static function run() {
		// Installation and un-installation hooks.
		register_activation_hook( __FILE__, array( get_called_class(), 'activate' ) );
		register_deactivation_hook( __FILE__, array( get_called_class(), 'deactivate' ) );
		self::set();
	}

	/**
	 * Used to get the instance of the class as an unforced singleton model
	 *
	 * @return bool|Plugin|mixed $instance
	 */
	public function get() {
		global $wp_plugins;
		$plugin_name = strtolower( get_called_class() );
		if ( isset( $wp_plugins ) && isset( $wp_plugins->$plugin_name ) ) {
			return $wp_plugins->$plugin_name;
		} else {
			return false;
		}
	}


	/**
	 * Used to setup the instance of the class and place in wp_plugins collection.
	 *
	 * @param bool|Plugin|mixed $instance Contains object representing the plugin.
	 */
	private static function set( $instance = false ) {
		// Make sure the plugin hasn't already been instantiated before.
		global $wp_plugins;
		if ( ! isset( $wp_plugins ) ) {
			$wp_plugins = new \stdClass();
		}
		// Get the fully qualified parent class name and instantiate an instance of it.
		$called_class = get_called_class();
		$plugin_name  = strtolower( $called_class );
		if ( empty( $instance ) || ! is_a( $instance, $called_class ) ) {
			$wp_plugins->$plugin_name = new $called_class();
		} else {
			$wp_plugins->$plugin_name = $instance;
		}
	}

} // END class
