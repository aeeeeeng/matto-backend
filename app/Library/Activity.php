<?php

    namespace App\Library;

    use Request;
    use App\Models\Activity as ActivityModel;

    class Activity {

        public static function addToLog($subject, $count = 0, $status = 1)
        {
            $log = [];
            $log['subject'] = $subject;
            $log['url'] = Request::fullUrl();
            $log['method'] = Request::method();
            $log['ip'] = Request::ip();
            $log['agent'] = Request::header('user-agent');
            $log['user_id'] = auth()->check() ? auth()->user()->id : NULL;
            $log['count_data'] = $count;
            $log['status'] = $status;
            ActivityModel::create($log);
        }

        public static function list()
        {
            return ActivityModel::latest()->get();
        }

    }
