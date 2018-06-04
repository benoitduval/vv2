<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AssetsHelper extends AbstractHelper
{

    public function __construct($assets)
    {
        $this->assets = $assets;
    }

    public function render($type, $page)
    {
        $assets = $this->assets['global'][$type];
        if (isset($this->assets[$page][$type])) {
            $assets = array_merge($assets, $this->assets[$page][$type]);
        }
        $assets = array_merge($assets, $this->assets['global']['fonts']);

        $result = '';
        if ($type === 'js') {
            foreach ($assets as $url) {
                $result .= "<script src=\"$url\"></script>\n";
            }
        }

        if ($type === 'css') {
            foreach ($assets as $url) {
                $result .= "<link href=\"$url\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />\n";
            }
        }
        return $result;
    }
}