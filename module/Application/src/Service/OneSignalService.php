<?php

namespace Application\Service;

use OneSignalApi\OneSignal;

class OneSignalService
{
    protected $_onesignal;

    protected $_userkey;
    protected $_appid;
    protected $_appkey;

    protected $_header   = [];
    protected $_content  = [];
    protected $_subtitle = [];
    protected $_url      = '';

    public function __construct($userkey, $appid, $appkey)
    {
        $this->_userkey = $userkey;
        $this->_appid   = $appid;
        $this->_appkey  = $appkey;

        $this->_onesignal = new OneSignal([
            'userkey' => $this->_userkey,
            'appid'   => $this->_appid,
            'appkey'  => $this->_appkey
        ]);
    }

    public function setContent($data)
    {
        $this->_content = ['en' => $data];
    }

    public function setHeader($data)
    {
        $this->_header = ['en' => $data];
    }

    public function setSubtitle($data)
    {
        $this->_subtitle = ['en' => $data];
    }

    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function setData(array $data)
    {
        foreach ($data as $method => $value) {
            $method = 'set' . ucfirst($method);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function sendTo($email)
    {
        $notification = [
            'app_id'   => '',
            'app_ids'  => [], // only iOS or Android. Required User Auth Key
            'headings' => $this->_header,
            'contents' => $this->_content,
            'subtitle' => $this->_subtitle,
            'data'     => [],
            'filters'  => [],
            'url'      => $this->_url,
            'tags'     => [[
                "key"       => "email",
                "relation"  => "=",
                "value"     => $email
            ]],
            // 'template_id' => '',
            // 'content_available' => false, // only iOS
            // 'mutable_content' => false, // only iOS 10+
            // 'included_segments' => ['All'],
            // 'send_after' => '2018-04-04 17:22:30 GMT+0200',
            // 'ios_badgeType' => 'Increase',
            // 'ios_badgeCount' => 1,
        ];
        $this->_onesignal->createNotification($notification);
    }
}