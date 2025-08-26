<?php

namespace App\Helper;

use Carbon\Carbon;
use DateTimeZone;

class ArabicConvertorHelper
{
    public static function convertToArabicNumbers($string)
    {
        $arabicNumbers = [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩'
        ];
        
        $arabicMonths = [
            'January' => 'يناير',
            'February' => 'فبراير',
            'March' => 'مارس',
            'April' => 'أبريل',
            'May' => 'مايو',
            'June' => 'يونيو',
            'July' => 'يوليو',
            'August' => 'أغسطس',
            'September' => 'سبتمبر',
            'October' => 'أكتوبر',
            'November' => 'نوفمبر',
            'December' => 'ديسمبر'
        ];
        
        $string = strtr($string, $arabicMonths);

        return strtr($string, $arabicNumbers);
    }
}
