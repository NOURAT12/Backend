<?php

namespace App\Traits;

use Carbon\Carbon;
use DateTime;

/**
 *
 */
trait QuizTrait
{
    public function translate($diff)
    {
        $arr = explode(' ', $diff);
        $res = ['متبقي'];
        foreach ($arr as $key => $value) {
            $res[] = ' ';
            if (is_numeric($value)) {
                $res[] = $value;
            } else {
                switch ($value) {
                    case 'minute':
                        $res[] = 'دقيقة';
                        break;
                    case 'minutes':
                        $res[] =  'دقيقة';
                        break;
                    case 'hours':
                        $res[] =  'ساعة';
                        break;
                    case 'hour':
                        $res[] =  'ساعة';
                        break;
                    case 'weeks':
                        $res[] =  'اسبوع';
                        break;
                    case 'week':
                        $res[] =  'اسبوع';
                        break;
                    case 'days':
                        $res[] =  'يوم';
                        break;
                    case 'day':
                        $res[] =  'يوم';
                        break;
                    case 'seconds':
                        $res[] =  'ثانية';
                        break;
                    case 'second':
                        $res[] =  'ثانية';
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }
        return implode($res);
    }

    public function getResultDate($quiz)
    {
        // $interval = $a->diff($b);
        // return  $interval->format('%Y-%m-%d %H:%i:%s');
        $time = DateTime::createFromFormat('H:i:s', $quiz->total_time);
        $first = DateTime::createFromFormat('Y-m-d H:i:s', $quiz->start);
        $start = DateTime::createFromFormat('Y-m-d H:i:s', $quiz->start);
        $now = DateTime::createFromFormat('Y-m-d H:i:s', now());
        $result = date_time_set($start, $start->format('H') + $time->format('H'), $start->format('i') + $time->format('i'), $start->format('s') + $time->format('s'));
        $state = null;
        if ($now > $first) {
            if ($now < $result) {
                $state = 1;
            }
            $state = 2;
        } else {
            $state = 3;
        }
        switch ($state) {
            case 1:
                return ['available'=>true,'message'=>'متاح'];
                break;
            case 2:
                return ['available'=>false,'message'=>'منتهي'];
                break;

            default:
                $date1 = Carbon::parse(date('Y-m-d H:i:s', $first->getTimestamp()));
                $date2 = Carbon::parse(date('Y-m-d H:i:s', $now->getTimestamp()));
                $diff = $date1->diffForHumans($date2, null, false,2);
                return ['available'=>false,'message'=>$this->translate($diff)];
                // return ['available'=>false,'message'=>$diff];
                break;
        }
    }
}

