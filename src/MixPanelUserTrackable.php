<?php namespace GeneaLabs\MixPanel;

use Illuminate\Support\Facades\App;

trait MixPanelUserTrackable
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $mixPanel = App::make(MixPanel::class);
            $mixPanel->people->set($model->id, [
                '$first_name' => $model->first_name,
                '$last_name' => $model->last_name,
                '$email' => $model->email,
            ]);
            $mixPanel->track('User Registered');
        });

        static::deleting(function ($model) {
            $mixPanel = App::make(MixPanel::class);
            $mixPanel->identify($model->id);
            $mixPanel->track('User Deleted');
        });
    }
}
