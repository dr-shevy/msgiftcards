<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsGiftCardRemoveProcessor extends msGiftCardsWebProcessor
{
    public function process()
    {
        $this->msGiftCards->clearCheckoutState();
        return $this->success('', []);
    }
}

return 'msGiftCardsGiftCardRemoveProcessor';
