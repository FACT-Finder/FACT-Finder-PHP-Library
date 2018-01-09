<?php
namespace FACTFinder\Data;

/**
 * Enum for sorting directions.
 * @see FilterStyle for documentation of the enum workaround.
 */
class SortingDirection
{
    // These will store distinct instances of the class.
    private static $asc;
    private static $desc;

    private static $nextID = 0;
    private $id;
    private function __construct()
    {
        $this->id = self::$nextID++;
    }

    private static $initialized = false;
    public static function initialize()
    {
        if (!self::$initialized) {
            self::$asc         = new SortingDirection();
            self::$desc       = new SortingDirection();

            self::$initialized = true;
        }
    }

    public static function Ascending()
    {
        return self::$asc;
    }
    public static function Descending()
    {
        return self::$desc;
    }
}

SortingDirection::initialize();
