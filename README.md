# Abstract Plugin Base
Used as a base class to help standardize the way we build WordPress plugins.

# WordPress Options Builder Class Library

WordPress options builder class is a library that helps you setup theme or plugin options that store data in the database with just a line or two of code!

## Table of Contents:
- [Installation](#installation)
- [Usage](#usage)

# Installation

## Composer style (recommended)

Via composer command line: 
```
composer require WordPress-Phoenix/abstract-plugin-base && composer install
```

...or by manually configuring the composer file by including in your plugin. Create or add the following to your composer.json file in the root of the plugin: 
```json
{
  "require": {
    "WordPress-Phoenix/abstract-plugin-base": "1.*"
  }
}
```
1. Confirm that composer is installed in your development enviroment using `which composer`.
2. Open CLI into your plugins root directory and run `composer install`.
3. Confirm that it created the vendor folder in your plugin.
4. In your plugins main file, near the code where you include other files place the following:
```php
if( file_exists( dirname( __FILE__ ) . 'vendor/autoload.php' ) ) {
  include_once dirname( __FILE__ ) . 'vendor/autoload.php';
}
```

## Manual Installation
1. Download the most updated copy of this repository from `https://api.github.com/repos/WordPress-Phoenix/abstract-plugin-base/zipball`
2. Extract the zip file, and copy the PHP file into your plugin project.
3. Use SSI (Server Side Includes) to include the file into your plugin.

# Usage

## Why should you use this library when building your plugin?
By building your plugin using OOP principals, and extending this Plugin_Base class object, 
you will be able to quickly and efficiently build your plugin, allowing it to be simple to 
start, but giving it the ability to grow complex without changing its architecture. Immediate 
features include:

- Built in SPL Autoload for your includes folder, should you follow WordPress codex naming standards for class files.
- Template class provides you all the best practices for standard plugin initialization
- Minimizes code needed / maintenance of your main plugin file.
- Assists developers new to WordPress plugin development in file / folder architecture.
- By starting all your plugins with the same architecture, we create a standard thats better for the dev community.

## Main plugin file example

```php
/**
 * Plugin Name: FanSided Powertools
 * Plugin URI: https://github.com/fansided/fansided-powertools.git
 */

// Avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Hook initialization into the plugins_loaded action to maximize flexibility of hooks and filters
add_action( 'plugins_loaded', array( 'Custom_Plugin', 'run' ) );

// Enable composer class libraries.
require_once( 'vendor/autoload.php' );

if ( ! class_exists( 'Custom_Plugin' ) ) {
	/**
	 * Class Custom_Plugin
	 */
	class Custom_Plugin extends \WPAZ_Plugin_Base\V_1_1\Plugin {
		
		protected $current_file = __FILE__;
		
		public function onload( $instance ) {
			// Nothing yet
		} // END public function __construct
		
		public function init() {
			do_action( get_called_class() . '_before_init' );
			
			// Do plugin stuff like:
			//add_action( 'wp_enqueue_scripts', array( get_called_class(), 'my_function' ) );
			
			do_action( get_called_class() . '_after_init' );
		}
		
		public function authenticated_init() {
			if ( is_user_logged_in() ) {
			    // Ready for wp-admin but not required 
			    //require_once( $this->installed_dir . '/admin/class-custom-plugin-admin.php' );
                            //$this->admin = new FanSided_Powertools_Admin( $this );
			}
		}
		
		protected function defines_and_globals() {
		    // None yet.
		}
		
	} // END class
} // END if( ! class_exists() )

```
