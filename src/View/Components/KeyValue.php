<?php

namespace Provydon\JsonToKeyvalue\View\Components;

use Illuminate\View\Component;
use Provydon\JsonToKeyvalue\JsonToKeyvalue;

class KeyValue extends Component
{
    public $items;

    public $label;

    public function __construct($data, string $label = '', array $config = [])
    {
        $converter = JsonToKeyvalue::make($data, $label)->config($config);
        $this->items = $converter->toArray();
        $this->label = $label;
    }

    public function render()
    {
        return view('json-to-keyvalue::keyvalue');
    }
}
