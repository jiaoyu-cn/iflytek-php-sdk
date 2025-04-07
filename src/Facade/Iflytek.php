<?php
namespace Githen\IflytekPhpSdk\Facade;

use Illuminate\Support\Facades\Facade;

class Iflytek extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'iflytek';
    }

}
