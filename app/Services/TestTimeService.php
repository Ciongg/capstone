<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TestTimeService
{
    const TEST_TIME_KEY = 'test_time_override';
    
    public static function setTestTime($dateTime)
    {
        Cache::put(self::TEST_TIME_KEY, $dateTime, now()->addHours(24));
    }
    
    public static function clearTestTime()
    {
        Cache::forget(self::TEST_TIME_KEY);
    }
    
    public static function mock($dateTime)
    {
        return self::setTestTime($dateTime);
    }
    
    public static function getTestTime()
    {
        return Cache::get(self::TEST_TIME_KEY);
    }
    
    public static function now()
    {
        $testTime = self::getTestTime();
        return $testTime ? Carbon::parse($testTime) : Carbon::now('Asia/Manila');
    }
    
    public static function isTestModeActive()
    {
        return Cache::has(self::TEST_TIME_KEY);
    }
}
