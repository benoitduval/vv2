<?php

namespace Application\Service;

class Date
{

    public static $toFr = array(
        '/Monday/i'    => 'Lundi',
        '/Tuesday/i'   => 'Mardi',
        '/Wednesday/i' => 'Mercredi',
        '/Thursday/i'  => 'Jeudi',
        '/Friday/i'    => 'Vendredi',
        '/Saturday/i'  => 'Samedi',
        '/Sunday/i'    => 'Dimanche',
        '/january/i'   => 'Janvier',
        '/february/i'  => 'Février',
        '/march/i'     => 'Mars',
        '/april/i'     => 'Avril',
        '/may/i'       => 'Mai',
        '/june/i'      => 'Juin',
        '/july/i'      => 'Juillet',
        '/august/i'    => 'Août',
        '/september/i' => 'Septembre',
        '/october/i'   => 'Octobre',
        '/november/i'  => 'Novembre',
        '/december/i'  => 'Décembre',
        '/Mon/i'       => 'Lun',
        '/Tue/i'       => 'Mar',
        '/Wed/i'       => 'Mer',
        '/Thu/i'       => 'Jeu',
        '/Fri/i'       => 'Ven',
        '/Sat/i'       => 'Sam',
        '/Sun/i'       => 'Dim',
        '/jan/i'       => 'Jan',
        '/feb/i'       => 'Fev',
        '/mar/i'       => 'Mar',
        '/apr/i'       => 'Avr',
        '/may/i'       => 'Mai',
        '/jun/i'       => 'Juin',
        '/jul/i'       => 'Juil',
        '/aug/i'       => 'Aout',
        '/sep/i'       => 'Sep',
        '/oct/i'       => 'Oct',
        '/nov/i'       => 'Nov',
        '/dec/i'       => 'Dec',
    );

    public static $toEn = [
        '/janvier/'   => 'january',
        '/fevrier/'   => 'february',
        '/mars/'      => 'march',
        '/avril/'     => 'april',
        '/mai/'       => 'may',
        '/juin/'      => 'june',
        '/juillet/'   => 'july',
        '/aout/'      => 'august',
        '/septembre/' => 'september',
        '/octobre/'   => 'october',
        '/novembre/'  => 'november',
        '/decembre/'  => 'december',
    ];

    public static function toFr($str)
    {
        return preg_replace(array_keys(static::$toFr), array_values(static::$toFr), $str);
    }

    public static function isMonth($str)
    {
        return in_array($str, ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre']);
    }

    public static function toEn($str)
    {
        return preg_replace(array_keys(static::$toEn), array_values(static::$toEn), $str);
    }

    public static function getSeasonsDates()
    {
        $y = date('Y');
        $dates = [];
        if (time() < strtotime($y . '-09-01')) {
            $dates = [
                'from' => new \Datetime($y . '-09-01 00:00:00 -1years'),
                'to'   => new \Datetime($y . '-08-31 23:59:59'),
            ];
        } else {
            $dates = [
                'from' => new \Datetime($y . '-09-01 00:00:00'),
                'to'   => new \Datetime($y . '-08-31 23:59:59  +1years'),
            ];
        }

        return $dates;
    }

    public static function getInterval(\Datetime $date1, \Datetime $date2)
    {
        $interval  = $date2->diff($date1);
        if ($interval->y) {
            $elapsed = $interval->format('%y years, %m months');
        } else if ($interval->m) {
            $elapsed = $interval->format('%m months, %d days');
        } else if ($interval->d) {
            $elapsed = $interval->format('%d days, %h hours');
        } else if ($interval->h) {
            $elapsed = $interval->format('%h hours, %i minutes');
        } else if ($interval->i) {
            $elapsed = $interval->format('%i minutes, %S seconds');
        } else if ($interval->s) {
            $elapsed = $interval->format('%S seconds');
        }

        $elapsed   = str_replace(array('0 years,', ' 0 months,', ' 0 days,',  ' 0 hours,', ' 0 minutes,'), '', $elapsed);
        $elapsed   = str_replace(array('1 years, ', ' 1 months, ', ' 1 days, ',  ' 1 hours, ', ' 1 minutes'), array('1 year, ', '1 month, ', ' 1 day, ', ' 1 hour, ', ' 1 minute'), $elapsed);
        return $elapsed;
    }
}
