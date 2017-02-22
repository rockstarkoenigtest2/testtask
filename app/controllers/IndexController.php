<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->flashSession->output();

        if ($this->session->get('id')) {
            $this->forward("/users");
        }
    }

}

