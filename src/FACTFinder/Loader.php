<?php
/**
 * Bootstrap file which should be called on every request. It defines some basic
 * constants and defines the autoloader class, which handles classloading,
 * constructing and holds Singletons.
 */

namespace FACTFinder;

use FACTFinder\Loader as FF;
use FACTFinder\Util\LoggerInterface;

// short cut for the constant DIRECTORY_SEPARATOR
if (!defined('DS'))
{
    define('DS', DIRECTORY_SEPARATOR);
}

// contains the complete lib directory path
if (!defined('LIB_DIR'))
{
    define('LIB_DIR', dirname(dirname(__FILE__)));
}

// set as include path if this is not the case yet
$includePaths = explode(PATH_SEPARATOR, get_include_path());
if ( array_search(LIB_DIR, $includePaths, true) === false )
{
	set_include_path( get_include_path() . PATH_SEPARATOR . LIB_DIR);
}
spl_autoload_register(array('FACTFinder\Loader', 'autoload'));

// don't know, whether I should do that
if (function_exists('__autoload')
    && array_search('__autoload', spl_autoload_functions()) === false)
{
    spl_autoload_register('__autoload');
}

/**
 * handles different loading tasks
 */
class Loader
{
    protected static $singletons = array();
    protected static $classNames = array();

    public static function autoload($classname)
    {
        $filename = self::getFilename($classname);
        if (file_exists($filename))
            include_once $filename;
    }

    private static function getFilename($classname)
    {
        return LIB_DIR . DS . str_replace('\\', DS, $classname) . '.php';
    }

    private static function canLoadClass($classname)
    {
        return file_exists(self::getFilename($classname));
    }

    /**
     * Creates an instance of a class taking into account classes within the
     * "FACTFinder\Custom\" namespace instead of "FACTFinder\".
     * Note that classes in the \Custom namespace should inherit the class they
     * overwrite - otherwise some of the type hinting within the library might
     * break.
     * USE THIS method instead of the PHP "new" keyword for all classes from
     * this library.
     * Eg. instead of "$obj = new myclass;", you should use
     * "$obj = FACTFinder\Loader::getInstance("myclass")"!
     * You can also pass arguments for a constructor:
     *     Loader::getInstance('myClass', $arg1, $arg2,  ..., $argN)
     *
     * @param    string $name   Class name to instantiate
     * @param    mixed optional Constructor parameters
     * @return   object         A reference to the instance
     */
    public static function getInstance($name)
    {
        if (isset(self::$classNames[$name]))
        {
            $className = self::$classNames[$name];
        }
        else
        {
            $className = self::getClassName($name);
            self::$classNames[$name] = $className;
        }

        // this snippet is from the typo3 class "t3lib_div"
        // written by Kasper Skaarhoj <kasperYYYY@typo3.com>
        if (func_num_args() > 1)
        {
            // getting the constructor arguments by removing this
            // method's first argument (the class name)
            $constructorArguments = func_get_args();
            array_shift($constructorArguments);

            $reflectedClass = new \ReflectionClass($className);
            $instance = $reflectedClass->newInstanceArgs($constructorArguments);
        }
        else
        {
            $instance = new $className;
        }

        return $instance;
    }

    /**
     * creates an instance of the class once and returns it every time.
     *
     * @param    string $name   Class name to instantiate
     * @param    mixed optional Constructor parameters
     * @return   object         A reference to the Singleton instance
     */
    public static function getSingleton($name)
    {
        if (!isset(self::$singletons[$name]))
        {
            $parameters = func_get_args();
            self::$singletons[$name] = call_user_func_array(
                                            array("self", "getInstance"),
                                            $parameters
                                       );
        }
        return self::$singletons[$name];
    }

    /**
     * Expects a fully qualified class name. if the leading namespace is omitted
     * or "FACTFinder", we first check whether there is a custom class in
     * namespace "FACTFinder\Custom\", then we look in "FACTFinder\". If none of
     * them exist, it also checks if the name is the class name itself.
     * Note that classes in the \Custom namespace should inherit the class they
     * overwrite - otherwise some of the type hinting within the library might
     * break.
     *
     * @param string $name The class name to be resolved.
     *
     * @return string The class name to be used.
     */
    public static function getClassName($name)
    {
        $name = trim(preg_replace('/^FACTFinder\\\\/i', '', $name));

        // check whether there is a custom or lib-unrelated class
        $customClassName     = 'FACTFinder\Custom\\' . $name;
        $factfinderClassName = 'FACTFinder\\' . $name;
        $defaultClassName    = $name;

        if (self::canLoadClass($customClassName))
            $className = $customClassName;
        else if (self::canLoadClass($factfinderClassName))
            $className = $factfinderClassName;
        else if (class_exists($defaultClassName))
            $className = $defaultClassName;
        else
            throw new \Exception("class '$factfinderClassName' not found");
        return $className;
    }
}
