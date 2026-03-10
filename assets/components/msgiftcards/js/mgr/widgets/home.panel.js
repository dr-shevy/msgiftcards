msGiftCards.panel.Home = function(config) {
  config = config || {};
  var tabs = [{
    title: _('msgiftcards'),
    items: [{
      xtype: 'panel',
      cls: 'tab-panel-wrapper',
      border: false,
      layout: 'anchor',
      items: [{
        html: '<p>' + _('msgiftcards_mgr_tab_desc') + '</p>',
        bodyCssClass: 'panel-desc'
      }, {
        xtype: 'msgiftcards-grid-certificates',
        cls: 'main-wrapper',
        preventRender: true,
        anchor: '100%'
      }]
    }]
  }, {
    title: _('msgiftcards_mgr_redemptions_tab'),
    items: [{
      xtype: 'panel',
      cls: 'tab-panel-wrapper',
      border: false,
      layout: 'anchor',
      items: [{
        html: '<p>' + _('msgiftcards_mgr_redemptions_tab_desc') + '</p>',
        bodyCssClass: 'panel-desc'
      }, {
        xtype: 'msgiftcards-grid-redemptions-all',
        cls: 'main-wrapper',
        preventRender: true,
        anchor: '100%'
      }]
    }]
  }];

  Ext.apply(config, {
    id: 'msgiftcards-panel-home',
    cls: 'container',
    items: [{
      html: '<h2>' + _('msgiftcards') + '</h2>',
      cls: 'modx-page-header'
    }, {
      xtype: 'modx-tabs',
      items: tabs
    }]
  });
  msGiftCards.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.panel.Home, MODx.FormPanel);
Ext.reg('msgiftcards-panel-home', msGiftCards.panel.Home);
