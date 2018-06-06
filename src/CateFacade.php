<?php

namespace Virtualorz\Cate;

use Illuminate\Support\Facades\Facade;

/**
 * @see Virtualorz\Cate\Cate
 */
class CateFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'cate';
    }

}
