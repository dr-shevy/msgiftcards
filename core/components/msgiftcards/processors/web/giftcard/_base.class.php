<?php
abstract class msGiftCardsWebProcessor extends modProcessor
{
    /** @var msGiftCards */
    public $msGiftCards;

    public function initialize()
    {
        $corePath = $this->modx->getOption('msgiftcards_core_path', null, $this->modx->getOption('core_path') . 'components/msgiftcards/');
        $this->msGiftCards = $this->modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
        if (!$this->msGiftCards || !$this->msGiftCards->config['enabled']) {
            return $this->modx->lexicon('msgiftcards_err_disabled');
        }

        return parent::initialize();
    }
}
