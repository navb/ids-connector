<?php

namespace Navb\IdsConnector;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Navb\IdsConnector\Skeleton\SkeletonClass
 */
class IdsConnectorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ids-connector';
    }
}
