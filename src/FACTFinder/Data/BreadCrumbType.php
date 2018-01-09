<?php
namespace FACTFinder\Data;

/**
 * Enum for type of a bread crumb item.
 * @see FilterStyle for documentation of the enum workaround.
 */
class BreadCrumbType
{
    private static $search;
    private static $filter;
    private static $advisor;

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
            self::$search      = new BreadCrumbType();
            self::$filter      = new BreadCrumbType();
            self::$advisor     = new BreadCrumbType();

            self::$initialized = true;
        }
    }

    public static function Search()
    {
        return self::$search;
    }
    public static function Filter()
    {
        return self::$filter;
    }
    public static function Advisor()
    {
        return self::$advisor;
    }
}

BreadCrumbType::initialize();
