<?php

    namespace App\Library;

    class Response {

        public static function success($message, $data = [])
        {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $data
            ];
            return $response;
        }

        public static function error($message)
        {
            $response = [
                'success' => false,
                'message' => $message
            ];
            return $response;
        }

    }
