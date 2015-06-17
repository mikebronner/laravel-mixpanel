<?php namespace GeneaLabs\MixPanel;

use Closure;
use GeneaLabs\MixPanel\MixPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class MixPanelViewComposer
{
    protected $mixPanel;

    /**
     *
     */
    public function __construct(MixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function compose($view)
    {
        $this->mixPanel->track('Page View', [
            'Url' => Request::url(),
            'Route' => Request::route()->uri,
            'View' => $view->view,
        ]);
    }
}
