<?php

namespace Application\Service;

class Calendar
{
    protected $_events;

    public function __construct($events)
    {
        if (!is_array($events)) {
            $events = [$events];
        }
        $this->_events = $events;
    }

    public function generateICS()
    {
        $ical = 'BEGIN:VCALENDAR' . "\r\n" .
        'PRODID:-//Volley-ball.eu//Volley-ball.eu//EN' . "\r\n" .
        'VERSION:2.0' . "\r\n" .
        'CALSCALE:GREGORIAN' . "\r\n" .
        'METHOD:PUBLISH' . "\r\n";
        foreach ($this->_events as $event) {
            $eventDate = \DateTime::createFromFormat('Y-m-d H:i:s', $event['date']);
            $ical .= 'BEGIN:VEVENT' . "\r\n" .
            'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
            'UID:' . $event['id'] . $eventDate->format('YmdHis') . '@' . 'volley-ball.eu' . "\r\n" .
            'DTSTAMP:' . date("Ymd\TGis") . "\r\n" .
            'DTSTART:' . $eventDate->format('Ymd\THis') . "\r\n" .
            'DTEND:' . $eventDate->modify('+ 2 hours')->format('Ymd\THis') . "\r\n" .
            'SUMMARY:' . $this->_escapeString($event['name']) . "\r\n" .
            'LOCATION:' . $this->_escapeString($event['address']) . "\r\n" .
            'CLASS:PUBLIC' . "\r\n" .
            'END:VEVENT'. "\r\n";
        }
        $ical .= 'END:VCALENDAR'. "\r\n";
        return $ical;
    }

    protected function _escapeString($string) {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }
}