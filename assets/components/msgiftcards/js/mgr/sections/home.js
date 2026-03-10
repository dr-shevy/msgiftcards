Ext.onReady(function() {
  MODx.load({
    xtype: 'msgiftcards-page-home'
  });
});

msGiftCards.page.Home = function(config) {
  config = config || {};
  config.buttons = [];
  Ext.applyIf(config, {
    components: [{
      xtype: 'msgiftcards-panel-home',
      renderTo: 'msgiftcards-panel-home-div'
    }]
  });
  msGiftCards.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.page.Home, MODx.Component);
Ext.reg('msgiftcards-page-home', msGiftCards.page.Home);
