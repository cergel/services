<?php

namespace sdkService\service;

use sdkService\model\ApplyActive;

/**
 *app resource
 */
class serviceFeedack extends serviceBase
{
    /**
     * add feedback
     */
    public function addFeedback($arrInput)
    {
        return $this->model("Feedback")->addFeedback($arrInput);
    }
}