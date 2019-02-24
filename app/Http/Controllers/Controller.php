<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Run validation on request and abort with a 400 status code if no valid
     * data was returned.
     *
     * Use the 'nullable' rule to allow some data to not be required by the
     * request, so won't be returned making for easy eloquent updates
     *
     * @param Request $request
     * @param array $rules
     * @return array valid data
     */
    protected function validDataOrAbort(Request $request, $rules)
    {
        $validData = $this->validate($request, $rules);

        if (empty($validData)) {
            abort(400, 'No valid data provided');
        }

        return $validData;
    }
}
