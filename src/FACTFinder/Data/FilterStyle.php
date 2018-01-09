<?php
namespace FACTFinder\Data;

/**
 * Enum for style of filter groups within the After Search Navigation (ASN).
 * Of course, this is just a workaround for enums in PHP. It's a bit convoluted
 * but in terms of usage, it seems to be one of the cleanest solutions.
 * There are no further doc blocks, so please see the source code for further
 * explanations.
 */
class FilterStyle
{
    // These will store distinct instances of the class.
    private static $regular;
    private static $slider;
    private static $tree;
    private static $multiSelect;

    // This ID is never used, but it ensures that an equality test between two
    // different instances will return false (since '==' object comparison is
    // decided by attributes).
    private static $nextID = 0;
    private $id;
    private function __construct()
    {
        $this->id = self::$nextID++;
    }

    // Another workaround! We need to initialize those private properties with
    // instances of the class, but PHP does not allow calling functions
    // (including constructors) when declaring properties. Hence, we need a
    // static class constructor to do that. But PHP does not have those either,
    // so we write our own and call it at the end of the file. At the same time,
    // we use a private flag to ensure that after this file has been loaded,
    // calling the initializer again will have no effect.
    // By the way, alternatively we could generate these instances lazily in all
    // the getters at the bottom.
    private static $initialized = false;
    public static function initialize()
    {
        if (!self::$initialized) {
            self::$regular       = new FilterStyle();
            self::$slider        = new FilterStyle();
            self::$tree          = new FilterStyle();
            self::$multiSelect   = new FilterStyle();

            self::$initialized = true;
        }
    }

    // Let's provide read-access to those instances.
    public static function Regular()
    {
        return self::$regular;
    }
    public static function Slider()
    {
        return self::$slider;
    }
    public static function Tree()
    {
        return self::$tree;
    }
    public static function MultiSelect()
    {
        return self::$multiSelect;
    }
}

// And finally we call the class initializer.
FilterStyle::initialize();
