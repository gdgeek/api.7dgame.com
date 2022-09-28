<?php

namespace common\components\editor;


class Action {


    public function __construct($id, $title, $json, $enabled) {
        $this->id = $id;
        $this->title = $title;
        $this->json = $json;
        $this->enabled = $enabled;
    }





}
