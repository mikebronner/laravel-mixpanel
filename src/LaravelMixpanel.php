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
        $browserVersion = trim(str_replace('unknown', '', $browserInfo->getName() . ' ' . $browserInfo->getVersion()));
        $osVersion = trim(str_replace('unknown', '', $osInfo->getName() . ' ' . $osInfo->getVersion()));
        $hardwareVersion = trim(str_replace('unknown', '', $deviceInfo->getName()));
        $data = [
            'Url' => $this->request->getUri(),
            'Operating System' => $osVersion,
            'Hardware' => $hardwareVersion,
            '$browser' => $browserVersion,
            'Referrer' => $this->request->header('referer'),
            '$referring_domain' => ($this->request->header('referer')
                ? parse_url($this->request->header('referer'))['host']
                : null),
            'ip' => $this->request->ip(),
        ];
        $data = array_filter($data);
        $properties = array_filter($properties);

        if ((! array_key_exists('$browser', $data)) && $browserInfo->isRobot()) {
            $data['$browser'] = 'Robot';
        }

        parent::track($event, $data + $properties);
    }
}
