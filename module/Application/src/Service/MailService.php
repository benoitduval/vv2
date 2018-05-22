<?php

namespace Application\Service;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class MailService
{
    protected $_transport;
    protected $_mail;
    protected $_attachment = null;

    public function __construct($transport)
    {
        $this->_transport = $transport;
        $this->_mail = new Message();
    }

    public function send()
    {
        try {
            $this->_mail->addFrom('volleyball.eu@gmail.com');
            $this->_transport->send($this->_mail);
        } catch (\Exception $e) {
            \Zend\Debug\Debug::dump($e->getMessage());die;
        }
    }

    public function addBcc($recipients)
    {
        $this->_mail->addBcc($recipients);
    }

    public function addTo($email)
    {
        $this->_mail->addTo($email);
    }

    public function setBcc($emails)
    {
        $this->_mail->setBcc($emails);
    }

    public function getBcc()
    {
        return $this->_mail->getBcc();
    }

    public function setSubject($subject)
    {
        $this->_mail->setSubject($subject);
    }

    public function setBody($content)
    {
        $html = new MimePart($content);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->addPart($html);

        if ($this->_attachment) $body->addPart($this->_attachment);

        $this->_mail->setBody($body);
    }

    public function toString()
    {
        return $this->_mail->toString();
    }

    public function addIcalEvent($event)
    {
        $calendar = new \Application\Service\Calendar([$event->toArray()]);
        $ical = $calendar->generateICS();
        $attach = new MimePart($ical);
        $attach->type = 'text/calendar';
        $attach->disposition = \Zend\Mime\Mime::DISPOSITION_INLINE;
        $attach->encoding = \Zend\Mime\Mime::ENCODING_8BIT;
        $attach->filename = $event->name . '-' . $event->date . '.ics';

        $this->_attachment = $attach;
    }
}
