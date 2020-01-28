<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Response;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function validate($request, $rules)
    {
        $messages = [
            'required' => trans('messages.required'),
            'unique'   => trans('messages.unique'),
            'email'    => trans('messages.email'),
            'numeric'  => trans('messages.numeric'),
            'exists'   => trans('messages.exists'),
            'date'     => trans('messages.date'),
        ];

        $validator = Validator::make($request, $rules);

        if ($validator->fails()) {
            $responseJson = Response::error($validator->errors());
            $return = response()->json($responseJson, 400, [], JSON_PRETTY_PRINT);
            $return->throwResponse();
        }
    }

    public function uniqueCode($model, $field, $prefix, $long)
    {
        $maxCode = $model::max($field);

        if($maxCode) {
            preg_match('!\d+!', $maxCode, $matches);
            $val = intval($matches[0]);
            $code = $prefix . str_pad($val + 1, $long, "0", STR_PAD_LEFT);
        } else {
            $code = $prefix . str_pad(1, $long, "0", STR_PAD_LEFT);
        }
        return $code;
    }
}
