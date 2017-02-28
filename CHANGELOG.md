#### 2.0.3
* Back to classmap loading, WP vs PSR-4 = fail

#### 2.0.2
* Large rewrite to handle namespaces and psr-4 option
* Alpha sorted parameter and function names
* Uses namespace matching filter for better performance
* Now requires use of namespaces to properly load files
* Updated documentation

#### 1.1.3
* Fixed filename to follow PSR-4 standard requirement

#### 1.1.2
* Fixed / moved main plugin file into src/

#### 1.1.1
* Introduced namespaced version to handle problems with composer in WordPress plugins
* Removed some parts of the class that were not generic enough for this class
* Fixed / Handled namespacing with composer autoload and PSR-4

#### 1.0.3
* call onload function in constructor bug fix

#### 1.0.2
* allow autoload to pull array of directories to autoload
* filter out namespacing from class names for get_file_name_from_class()

#### 1.0.1
* Initial release