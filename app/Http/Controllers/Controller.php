<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * * Checks if provided variable is a unsigned integer
     * @param input Input variable
     */
    public function is_uint($input)
    {
        $input = (int) $input;

        if ($input >= 0)
            return true;
        else
            return false;
    }
}
