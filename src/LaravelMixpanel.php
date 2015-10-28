<?php namespace GeneaLabs\LaravelMixpanel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Os;

class LaravelMixpanel extends \Mixpanel
{
    private $defaults = [
        'consumer' => 'socket',
        'connect_timeout' => 2,
        'timeout' => 2,
    ];
    private $request;

    /**
     * @param Request $request
     * @param array   $options
     *
     * @internal param Result $browser
     */
    public function __construct(Request $request, array $options = [])
    {
        $this->request = $request;

        $options = array_merge($this->defaults, $options);
        parent::__construct(config('services.mixpanel.token'), $options);
    }

    /**
     * @param string $event
     * @param array  $properties
     *
     * @internal param array $data
     */
    public function track($event, $properties = [])
    {
        $browserInfo = new Browser();
        $osInfo = new Os();
        $deviceInfo = new Device();
        $osVersion = $osInfo->getName() . ' ' . $osInfo->getVersion();
        $hardware = $deviceInfo->getName() . ' ' . $deviceInfo->getVersion();
        $data = [
            'Url' => $this->request->getUri(),
            'Operating System' => $osVersion,
            'Hardware' => $hardware,
            '$browser' => $browserInfo->getName() . ' ' . $browserInfo->getVersion(),
            'Referrer' => $this->request->header('referer'),
            '$referring_domain' => ($this->request->header('referer')
                ? parse_url($this->request->header('referer'))['host']
                : null),
            'ip' => $this->request->ip(),
        ];
        array_filter($data);
        array_filter($properties);
        $properties = $data + $properties;

        parent::track($event, $properties);
    }
}
