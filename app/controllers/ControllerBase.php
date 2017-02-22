<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    protected function forward($uri) {
        return $this->response->redirect($uri);
    }

    protected function generateErrorResponse($text) {
        return json_encode(['error' => $text]);
    }
}
