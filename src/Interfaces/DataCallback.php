<?php

namespace GeneaLabs\LaravelMixpanel\Interfaces;

interface DataCallback
{
    public function process(array $data = []) : array;
}
