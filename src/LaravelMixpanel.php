<?php namespace GeneaLabs\LaravelMixpanel;

use hisorange\BrowserDetect\Parser;
use hisorange\BrowserDetect\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LaravelMixpanel extends \Mixpanel
{
    private $defaults = [
        'consumer' => 'socket',
        'connect_timeout' => 2,
        'timeout' => 2,
    ];
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Parser
     */
    private $browserParser;
    /**
     * @var Result
     */
    private $browserResult;

    /**
     * @param array   $options
     * @param Request $request
     * @param Parser  $browserParser
     * @param Result  $browserResult
     *
     * @internal param Result $browser
     */
    public function __construct(array $options = [], Request $request, Parser $browserParser, Result $browserResult)
    {
        $this->request = $request;
        $this->browserParser = $browserParser;
        $this->browserResult = $browserResult;

        $options = array_merge($this->defaults, $options);
        parent::__construct(config('services.mixpanel.token'), $options);
    }

    /**
     * @param string $event
     * @param array  $properties
     *
     * @internal param array $data
     */
    public function track($event, array $properties = [])
    {
        $browserInfo = $this->browserParser->detect();
        $osVersion = $this->browserResult->osName();
        $hardware = $browserInfo['deviceFamily'] . ' ' . $browserInfo['deviceModel'];
        $data = [
            'Url' => $this->request->getUri(),
            'Operating System' => $osVersion,
            'Hardware' => $hardware,
            '$browser' => $this->browserResult->browserName(),
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
