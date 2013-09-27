<?php
namespace FACTFinder\Data;

/**
 * Enum for status of the search result.
 * Of course, this is just a workaround for enums in PHP. It's a bit convoluted
 * but in terms of usage, it seems to be one of the cleanest solutions.
 * There are no further doc blocks, so please see the source code for further
 * explanations.
 */
class SearchStatus
{
    // These will store distinct instances of the class.
    static private $noQuery;
    static private $noResult;
    static private $emptyResult;
    static private $recordsFound;

    // This ID is never used, but it ensures that an equality test between two
    // different instances will returns (since '==' object comparison is decided
    // by attributes).
    static private $nextID = 0;
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
    static private $initialized = false;
    public function initialize()
    {
        if (!self::$initialized)
        {
            self::$noQuery      = new SearchStatus();
            self::$noResult     = new SearchStatus();
            self::$emptyResult  = new SearchStatus();
            self::$recordsFound = new SearchStatus();

            self::$initialized = true;
        }
    }

    // Let's provide read-access to those instances.
    static public function NoQuery()      { return self::$noQuery; }
    static public function NoResult()     { return self::$noResult; }
    static public function EmptyResult()  { return self::$emptyResult; }
    static public function RecordsFound() { return self::$recordsFound; }
}

// And finally we call the class initializer.
SearchStatus::initialize();
