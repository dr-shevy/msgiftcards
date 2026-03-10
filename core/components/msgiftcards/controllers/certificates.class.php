<?php

if (!class_exists('MsgiftcardsManagerController')) {
    require_once dirname(__FILE__) . '/index.class.php';
}

class MsgiftcardsCertificatesManagerController extends MsgiftcardsManagerController
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

        $this->addJavascript($this->assetsUrl . 'js/mgr/msgiftcards.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/widgets/certificates.grid.js');
        $this->addJavascript($this->assetsUrl . 'js/mgr/widgets/certificates.windows.js');
        $this->addHtml('<script type="text/javascript">if(window.msGiftCards&&msGiftCards.config){msGiftCards.config.defaultCurrency=' . json_encode($defaultCurrency) . ';}</script>');

        $this->addHtml('<script type="text/javascript">Ext.onReady(function(){var debug=document.getElementById("msgiftcards-debug");var wrap=document.getElementById("msgiftcards-grid-wrapper");if(!wrap){if(debug){debug.innerHTML="Container #msgiftcards-grid-wrapper not found";}return;}if(typeof msGiftCards==="undefined"||typeof msGiftCards.grid==="undefined"||typeof msGiftCards.grid.Certificates==="undefined"){if(debug){debug.innerHTML="Grid class not loaded: msGiftCards.grid.Certificates";}return;}try{MODx.load({xtype:"msgiftcards-grid-certificates",renderTo:"msgiftcards-grid-wrapper"});}catch(e){if(debug){debug.innerHTML="Render error: "+(e.message||e);}}});</script>');
    }

    public function process(array $scriptProperties = [])
    {
        $title = $this->modx->lexicon('msgiftcards');
        return '<div class="container" id="msgiftcards-panel-home-div">'
            . '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>'
            . '<div id="msgiftcards-debug" style="margin:8px 0;color:#666;"></div>'
            . '<div id="msgiftcards-grid-wrapper"></div>'
            . '</div>';
    }
}

class CertificatesManagerController extends MsgiftcardsCertificatesManagerController
{
}

return 'CertificatesManagerController';
