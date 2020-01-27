<?php

    namespace App\Library;

    class Helper {
        public static function handleRequest($request, $obj, $default = null)
        {
            if(!$request->has($obj) || $request->input($obj) === null) {
                if($default !== null) {
                    return $default;
                }
            }
            return $request->input($obj);
        }
    }
