msGiftCards.grid.Redemptions = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    id: config.id || 'msgiftcards-grid-redemptions',
    url: msGiftCards.config.connectorUrl,
    baseParams: {
      action: 'certificate/redemptions',
      certificate_id: config.certificateId || 0
    },
    fields: ['id', 'order_id', 'amount', 'balance_after', 'operation', 'createdon'],
    columns: [{
      header: _('id'),
      dataIndex: 'id',
      width: 60,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_order_id'),
      dataIndex: 'order_id',
      width: 120,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_amount'),
      dataIndex: 'amount',
      width: 120,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_operation'),
      dataIndex: 'operation',
      width: 120,
      sortable: true,
      renderer: function(v) {
        return v === 'credit'
          ? '<span style="color:green;">' + _('msgiftcards_mgr_redemption_operation_credit') + '</span>'
          : '<span style="color:#b00020;">' + _('msgiftcards_mgr_redemption_operation_debit') + '</span>';
      }
    }, {
      header: _('msgiftcards_mgr_balance_after'),
      dataIndex: 'balance_after',
      width: 180,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_date'),
      dataIndex: 'createdon',
      width: 180,
      sortable: true,
      renderer: function(v) {
        return msGiftCards.utils.formatDateTime(v);
      }
    }],
    paging: true,
    remoteSort: true,
    autoHeight: true
  });
  msGiftCards.grid.Redemptions.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.grid.Redemptions, MODx.grid.Grid);
Ext.reg('msgiftcards-grid-redemptions', msGiftCards.grid.Redemptions);

msGiftCards.window.CertificateCreate = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    title: _('msgiftcards_mgr_create'),
    width: 760,
    autoHeight: true,
    url: msGiftCards.config.connectorUrl,
    baseParams: {
      action: 'certificate/create'
    },
    fields: [{
      xtype: 'modx-tabs',
      deferredRender: false,
      border: true,
      stateful: false,
      items: [{
        title: _('msgiftcards_mgr_certificate_tab'),
        layout: 'column',
        defaults: {
          layout: 'form',
          border: false,
          labelAlign: 'top',
          anchor: '100%'
        },
        items: msGiftCards.window.certificateMainTabFields()
      }]
    }]
  });
  msGiftCards.window.CertificateCreate.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.window.CertificateCreate, MODx.Window, {
  submit: function(close) {
    var form = this.fp.getForm();
    if (!form.isValid()) {
      return false;
    }

    var values = form.getValues();
    values = Ext.apply(values, this.baseParams || {});

    MODx.Ajax.request({
      url: this.config.url || this.url,
      params: values,
      listeners: {
        success: {
          fn: function(response) {
            this.fireEvent('success', response);
            this.hide();
          },
          scope: this
        },
        failure: {
          fn: function(response) {
            MODx.msg.alert(_('error'), response.message || _('error'));
          },
          scope: this
        }
      }
    });
    return true;
  }
});
Ext.reg('msgiftcards-window-certificate-create', msGiftCards.window.CertificateCreate);

msGiftCards.window.CertificateUpdate = function(config) {
  config = config || {};
  config.id = config.id || Ext.id();
  config.redemptionsGridId = config.id + '-redemptions-grid';

  Ext.applyIf(config, {
    title: _('msgiftcards_mgr_update'),
    width: 760,
    autoHeight: true,
    url: msGiftCards.config.connectorUrl,
    baseParams: {
      action: 'certificate/update'
    },
    fields: [{
      xtype: 'hidden',
      name: 'id'
    }, {
      xtype: 'modx-tabs',
      deferredRender: false,
      border: true,
      stateful: false,
      items: [{
        title: _('msgiftcards_mgr_certificate_tab'),
        layout: 'column',
        defaults: {
          layout: 'form',
          border: false,
          labelAlign: 'top',
          anchor: '100%'
        },
        items: msGiftCards.window.certificateMainTabFields()
      }, {
        title: _('msgiftcards_mgr_redemptions_tab'),
        layout: 'anchor',
        items: [{
          xtype: 'msgiftcards-grid-redemptions',
          id: config.redemptionsGridId,
          anchor: '100%',
          certificateId: 0
        }]
      }]
    }]
  });
  msGiftCards.window.CertificateUpdate.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.window.CertificateUpdate, MODx.Window, {
  submit: function(close) {
    var form = this.fp.getForm();
    if (!form.isValid()) {
      return false;
    }

    var values = form.getValues();
    values = Ext.apply(values, this.baseParams || {});

    MODx.Ajax.request({
      url: this.config.url || this.url,
      params: values,
      listeners: {
        success: {
          fn: function(response) {
            this.fireEvent('success', response);
            this.hide();
          },
          scope: this
        },
        failure: {
          fn: function(response) {
            MODx.msg.alert(_('error'), response.message || _('error'));
          },
          scope: this
        }
      }
    });
    return true;
  },

  setCertificateId: function(id) {
    var grid = Ext.getCmp(this.config.redemptionsGridId);
    if (grid) {
      grid.getStore().baseParams.certificate_id = id || 0;
      grid.getBottomToolbar().changePage(1);
    }
  }
});
Ext.reg('msgiftcards-window-certificate-update', msGiftCards.window.CertificateUpdate);

msGiftCards.window.certificateMainTabFields = function() {
  return [{
    columnWidth: .5,
    items: [{
      xtype: 'textfield',
      fieldLabel: _('msgiftcards_info_code'),
      name: 'code',
      anchor: '98%'
    }, {
      xtype: 'label',
      cls: 'desc-under',
      text: _('msgiftcards_mgr_code_desc')
    }, {
      xtype: 'numberfield',
      fieldLabel: _('msgiftcards_info_nominal'),
      name: 'nominal',
      decimalPrecision: 2,
      anchor: '98%',
      allowBlank: false
    }, {
      xtype: 'numberfield',
      fieldLabel: _('msgiftcards_mgr_balance'),
      name: 'balance',
      decimalPrecision: 2,
      anchor: '98%',
      allowBlank: false
    }]
  }, {
    columnWidth: .5,
    items: [{
      xtype: 'textfield',
      fieldLabel: _('msgiftcards_mgr_currency'),
      name: 'currency',
      anchor: '98%'
    }, {
      xtype: 'xdatetime',
      fieldLabel: _('msgiftcards_mgr_expireson'),
      name: 'expireson',
      dateFormat: msGiftCards.config.managerDateFormat || 'Y-m-d',
      timeFormat: msGiftCards.config.managerTimeFormat || 'H:i',
      hiddenFormat: 'Y-m-d H:i:s',
      anchor: '98%'
    }, {
      xtype: 'combo-boolean',
      fieldLabel: _('msgiftcards_mgr_active'),
      name: 'active',
      hiddenName: 'active',
      value: 1,
      anchor: '98%'
    }]
  }];
};


