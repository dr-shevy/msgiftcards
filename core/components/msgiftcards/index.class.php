<?php

abstract class MsgiftcardsManagerController extends modExtraManagerController
{
    protected $assetsUrl = '';

    public function initialize()
    {
        $this->assetsUrl = $this->modx->getOption('assets_url') . 'components/msgiftcards/';
        return parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['msgiftcards:default'];
    }

    public function checkPermissions()
    {
        return true;
    }
}

class MsgiftcardsIndexManagerController extends MsgiftcardsManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}

return 'MsgiftcardsIndexManagerController';
