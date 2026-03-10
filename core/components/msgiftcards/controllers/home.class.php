<?php

require_once dirname(__DIR__) . '/index.class.php';

class MsgiftcardsHomeManagerController extends MsgiftcardsManagerController
{
    public function getPageTitle()
    {
        return $this->modx->lexicon('msgiftcards');
    }

    public function loadCustomCssJs()
    {
        $defaultCurrency = trim((string)$this->modx->getOption('msgiftcards_default_currency', null, $this->modx->getOption('ms2_frontend_currency', null, 'RUB')));
        if ($defaultCurrency === '') {
            $defaultCurrency = 'RUB';
        }

        $this->addCss($this->assetsUrl . 'css/mgr/main.css');
        $this->addJavascript($this->assetsUrl . 'js/mgr/msgiftcards.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/widgets/certificates.windows.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/widgets/certificates.grid.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/widgets/home.panel.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/sections/home.js');
        $this->addHtml('<script type="text/javascript">if(window.msGiftCards&&msGiftCards.config){msGiftCards.config.defaultCurrency=' . json_encode($defaultCurrency) . ';}</script>');
    }

    public function process(array $scriptProperties = [])
    {
        return '<div id="msgiftcards-panel-home-div"></div>';
    }
}

class HomeManagerController extends MsgiftcardsHomeManagerController
{
}

return 'HomeManagerController';
