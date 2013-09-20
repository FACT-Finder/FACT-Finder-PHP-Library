<?php
namespace FACTFinder\Core\Server;

interface RequestFactoryInterface
{
    /**
     * Returns a request object all wired up and ready for use.
     * @return Request
     */
    public function getRequest();
}
