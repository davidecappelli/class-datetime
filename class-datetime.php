<?php

class DateTime{

	/**
	 * Accepted string formats for dates (list could be integrated). FIRST ITEM is
	 * the default one.
	 * @var DATEFORMATS
	 */
	public const DATEFORMATS = [
		'Y-m-d', 'd/m/y', 'd/m/Y', 'd/m', 'd.m.Y',
		];

	public const TIMEFORMATS = [
		'H:i:s', 'H:i',
	];

    public static $local_datetime_zone;
    public static $gmt_datetime_zone;

    public static function getDateTimes(){
        return [
            'now'       => new \DateTime('',self::$local_datetime_zone),
            'nowGMT'    => new \DateTime('',self::$gmt_datetime_zone),
        ];
    }
    public static function getDateTime($GMT=FALSE){
        return $GMT === TRUE ? self::getDateTimes()['now'] : self::getDateTimes()['nowGMT'];
    }

	/**
	 * Returns, as an OBJECT, the comparison for two dates provided as array (or
	 * exploded from string to array), either if [1] the arey equal (->result = 0);
	 * [2] the first date is prior to the second one (->result = NEGATIVE INTEGER
	 * of the count of the days); [3] the second date is prior to the first one
	 * (->result = POSITIVE INTEGER). Supports an array of multiple dates, from
	 * witch only first and last value are popped, thus returning the count of days
	 * in the interval. Both dates are returned in the same order they were provided.
	 *
	 * To be a commonly usable interval ->return should be "=0" (same date) or "<0"
	 * (first date prior to last date). For example:
	 *
	 * $comparison     = compareTwoDates([$first,$last],'m/d/Y');
	 * if($comparison->result <= 0){
	 *      // Dates are suitable
	 * }
	 *
	 * @param array|string $dates
	 * @param string $format
	 * @return false|\DateInterval
	 */
	public static function compareTwoDates($dates = null, $format = null){
		if (empty($dates)) {
			return false;
		}
		if (is_string($dates)) {
			return Data::compareTwoDates(explode(",", $dates)); //recursion for array
		}
		if (!is_array($dates)) {
			return false;
		}

		if ((count($dates) > 1) && strtotime($dates[0]) !== false && strtotime(end($dates)) !== false) {
			$date_format    = in_array($format, self::DATEFORMATS) ? $format : self::DATEFORMATS[0];
			$first_date     = new \DateTime($dates[0],self::$local_datetime_zone);
			$last_date      = new \DateTime(end($dates),self::$local_datetime_zone);
			$interval       = $first_date->diff($last_date);
			if ($interval->days > 0) {
				if ($interval->invert > 0) {
					$interval->result   = $interval->days; // $last_date is prior to $first_date (BAD)
				} else {
					$interval->result   = 0 - $interval->days; // $first_date is prior to $last_date (OK)
				}
			} else {
				$interval->result   = $interval->d; // $first_date and $last_date are equal (OK)
			}
			$interval->first_date   = $first_date->format($date_format);
			$interval->last_date    = $last_date->format($date_format);
			return $interval;
		}
		return false;
	}

	/**
	 * Returns, as an OBJECT, the difference between the given date ($date_string)
	 * and the current day, or FALSE either: [1] if $date_string is not provided;
	 * [2] if $date_string is not a valid date; [3] if $date_string is prior to
	 * today. The OBJECT, returned by compareTwoDates(), includes the two dates.
	 *
	 * @param string $date_string
	 * @return object from compareTwoDates()
	 */
	public static function isDateInFuture($date_string = null){
		if (empty($date_string) || strtotime($date_string) == false) {
			return false;
		}
		$today      = new \DateTime('today',self::$local_datetime_zone);
		$interval   = self::compareTwoDates([$date_string, $today->format(self::DATEFORMATS[0])]);
		return $interval->result > 0 ? $interval : false; //$date_string is at least 1 day in the future
	}

    public static function printDateTimeLocal($datetime = NULL, $reverse = FALSE) : NULL|string {
        if(!isset($datetime) || strtotime($datetime) === FALSE) return NULL;
        $datetime   = new \DateTime($datetime,self::$local_datetime_zone);
        return $reverse === TRUE ? str_replace(',','T',$datetime->format('Y-m-d,H:i')) : $datetime->format('Y-m-d H:i:00');
    }

	public static function getYearStamp() {
		$datetime   = self::getDateTime();
		return $datetime ? $datetime->format('Y') : NULL;
	}



    public function __construct(){
        self::$local_datetime_zone  = new \DateTimeZone('Europe/Rome');
        self::$gmt_datetime_zone    = new \DateTimeZone('GMT');
    }

}
