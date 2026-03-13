msGiftCards.grid.Certificates = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    id: 'msgiftcards-grid-certificates',
    url: msGiftCards.config.connectorUrl,
    baseParams: {
      action: 'certificate/getlist'
    },
    fields: ['id', 'code', 'nominal', 'balance', 'currency', 'active', 'order_id', 'order_product_id', 'item_index', 'createdon', 'updatedon', 'expireson', 'view_url'],
    paging: true,
    remoteSort: true,
    anchor: '100%',
    autoHeight: true,
    autoExpandColumn: 'msgiftcards-col-code',
    viewConfig: {
      forceFit: true,
      getRowClass: function(record) {
        return record.get('active') ? '' : 'msgiftcards-grid-row_disabled';
      }
    },
    columns: [{
      header: '',
      dataIndex: 'code',
      width: 42,
      sortable: false,
      menuDisabled: true,
      fixed: true,
      renderer: function(v) {
        var code = String(v || '').replace(/"/g, '&quot;');
        return '<div class="msgiftcards-grid-col__clipboard msgiftcards-grid-col__value">'
          + '<button type="button" class="msgiftcards-grid-col__clipboard-button msgiftcards-btn msgiftcards-btn-default icon icon-clipboard js-msgiftcards-code-copy" data-clipboard-text="' + code + '" title="' + _('msgiftcards_mgr_copy') + '" qtip="' + _('msgiftcards_mgr_copy') + '"></button>'
          + '</div>';
      }
    }, {
      header: _('id'),
      dataIndex: 'id',
      width: 50,
      sortable: true
    }, {
      header: _('msgiftcards_info_code'),
      id: 'msgiftcards-col-code',
      dataIndex: 'code',
      width: 160,
      sortable: true
    }, {
      header: _('msgiftcards_info_nominal'),
      dataIndex: 'nominal',
      width: 90,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_balance'),
      dataIndex: 'balance',
      width: 90,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_currency'),
      dataIndex: 'currency',
      width: 80
    }, {
      header: _('msgiftcards_mgr_active'),
      dataIndex: 'active',
      width: 70,
      renderer: function(v) {
        return v ? '<span style="color:green;">' + _('yes') + '</span>' : '<span style="color:#b00020;">' + _('no') + '</span>';
      }
    }, {
      header: _('msgiftcards_mgr_expireson'),
      dataIndex: 'expireson',
      width: 130,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_createdon'),
      dataIndex: 'createdon',
      width: 130,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_actions'),
      dataIndex: 'id',
      width: 150,
      sortable: false,
      menuDisabled: true,
      fixed: true,
      renderer: function(v, meta, row) {
        var active = parseInt(row.data.active, 10) === 1;
        var toggleAction = active ? 'disable' : 'enable';
        var toggleIcon = active ? 'icon-toggle-off action-red' : 'icon-toggle-on action-green';
        var toggleTitle = active ? _('msgiftcards_mgr_disable') : _('msgiftcards_mgr_enable');
        var viewButton = '';

        if (row.data.view_url) {
          viewButton = '<li><button type="button" class="msgiftcards-btn msgiftcards-btn-default icon icon-eye js-msgiftcards-action" data-action="view" title="' + _('msgiftcards_mgr_view') + '" qtip="' + _('msgiftcards_mgr_view') + '"></button></li>';
        }

        return '<ul class="msgiftcards-row-actions">'
          + '<li><button type="button" class="msgiftcards-btn msgiftcards-btn-default icon icon-edit js-msgiftcards-action" data-action="update" title="' + _('msgiftcards_mgr_update') + '" qtip="' + _('msgiftcards_mgr_update') + '"></button></li>'
          + viewButton
          + '<li><button type="button" class="msgiftcards-btn msgiftcards-btn-default icon ' + toggleIcon + ' js-msgiftcards-action" data-action="' + toggleAction + '" title="' + toggleTitle + '" qtip="' + toggleTitle + '"></button></li>'
          + '<li><button type="button" class="msgiftcards-btn msgiftcards-btn-default icon icon-trash-o action-red js-msgiftcards-action" data-action="remove" title="' + _('msgiftcards_mgr_remove') + '" qtip="' + _('msgiftcards_mgr_remove') + '"></button></li>'
          + '</ul>';
      }
    }],
    tbar: [{
      cls: 'primary-button',
      text: '<i class="icon icon-plus"></i> ' + _('msgiftcards_mgr_create'),
      xtype: 'button',
      id: 'msgiftcards-btn-create',
      handler: this.createCertificate,
      scope: this
    }, '->', {
      xtype: 'msgiftcards-field-search',
      id: 'msgiftcards-grid-certificates__tbar-search',
      width: 250,
      listeners: {
        search: {fn: function(field) {
          this._doSearch(field);
        }, scope: this},
        clear: {fn: function(field) {
          field.setValue('');
          this._clearSearch();
        }, scope: this}
      }
    }],
  });
  msGiftCards.grid.Certificates.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.grid.Certificates, MODx.grid.Grid, {
  onClick: function(e) {
    var copyBtn = e.getTarget('.js-msgiftcards-code-copy');
    var actionBtn = e.getTarget('.js-msgiftcards-action');
    if (!copyBtn && !actionBtn) {
      return msGiftCards.grid.Certificates.superclass.onClick.call(this, e);
    }

    var row = e.getTarget('.x-grid3-row');
    var rowIndex = this.getView().findRowIndex(row);
    var record = this.getStore().getAt(rowIndex);
    if (!record) {
      e.stopEvent();
      return false;
    }

    if (copyBtn) {
      this.copyCode(record);
      e.stopEvent();
      return false;
    }

    var action = actionBtn.getAttribute('data-action') || '';
    if (action === 'update') {
      this.updateCertificate(record, e);
    } else if (action === 'view') {
      this.viewCertificate(record);
    } else if (action === 'disable') {
      this.disableCertificate(record);
    } else if (action === 'enable') {
      this.enableCertificate(record);
    } else if (action === 'remove') {
      this.removeCertificate(record);
    }
    e.stopEvent();
    return false;
  },

  copyCode: function(record) {
    var text = record.get('code') || '';
    if (!text) {
      return;
    }

    var copied = false;
    if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
      try {
        window.navigator.clipboard.writeText(text);
        copied = true;
      } catch (err) {
        copied = false;
      }
    }

    if (!copied) {
      var textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        copied = document.execCommand('copy');
      } catch (err2) {
        copied = false;
      }
      document.body.removeChild(textarea);
    }

    if (copied) {
      MODx.msg.alert(_('info'), _('msgiftcards_mgr_copied'));
    }
  },

  getMenu: function() {
    var r = this.menu.record || {};
    var isActive = parseInt(r.active, 10) === 1;
    var m = [{
      text: _('msgiftcards_mgr_update'),
      handler: this.updateCertificate
    }, {
      text: isActive ? _('msgiftcards_mgr_disable') : _('msgiftcards_mgr_enable'),
      handler: isActive ? this.disableCertificate : this.enableCertificate
    }, '-', {
      text: _('msgiftcards_mgr_remove'),
      handler: this.removeCertificate
    }];
    if (r.view_url) {
      m.splice(1, 0, {
        text: _('msgiftcards_mgr_view'),
        handler: this.viewCertificate
      });
    }
    return m;
  },

  _doSearch: function(field) {
    this.getStore().baseParams.query = field.getValue();
    this.getBottomToolbar().changePage(1);
  },

  _clearSearch: function() {
    this.getStore().baseParams.query = '';
    this.getBottomToolbar().changePage(1);
  },

  createCertificate: function(btn, e) {
    if (!this.createWindow) {
      this.createWindow = MODx.load({
        xtype: 'msgiftcards-window-certificate-create',
        listeners: {
          success: {fn: function() { this.refresh(); }, scope: this}
        }
      });
    }
    this.createWindow.reset();
    this.createWindow.setValues({active: 1, currency: (msGiftCards.config.defaultCurrency || 'RUB')});
    this.createWindow.show(e.target);
  },

  updateCertificate: function(recordOrBtn, e) {
    var r = recordOrBtn && recordOrBtn.data ? recordOrBtn.data : this.menu.record;
    if (!r) return;

    if (!this.updateWindow) {
      this.updateWindow = MODx.load({
        xtype: 'msgiftcards-window-certificate-update',
        listeners: {
          success: {fn: function() { this.refresh(); }, scope: this}
        }
      });
    }
    this.updateWindow.reset();
    this.updateWindow.setValues(r);
    if (this.updateWindow.setCertificateId) {
      this.updateWindow.setCertificateId(r.id);
    }
    this.updateWindow.show((e && e.target) ? e.target : null);
  },

  viewCertificate: function(recordOrBtn) {
    var r = recordOrBtn && recordOrBtn.data ? recordOrBtn.data : this.menu.record;
    if (!r || !r.view_url) {
      MODx.msg.alert(_('error'), _('msgiftcards_mgr_view_unavailable'));
      return;
    }

    window.open(r.view_url, '_blank');
  },

  removeCertificate: function(recordOrBtn) {
    var r = recordOrBtn && recordOrBtn.data ? recordOrBtn.data : this.menu.record;
    if (!r) return;

    MODx.msg.confirm({
      title: _('warning'),
      text: _('msgiftcards_mgr_remove_confirm'),
      url: this.config.url,
      params: {
        action: 'certificate/remove',
        id: r.id
      },
      listeners: {
        success: {fn: function() { this.refresh(); }, scope: this}
      }
    });
  },

  disableCertificate: function(recordOrBtn) {
    var r = recordOrBtn && recordOrBtn.data ? recordOrBtn.data : this.menu.record;
    if (!r) return;

    MODx.msg.confirm({
      title: _('warning'),
      text: _('msgiftcards_mgr_disable_confirm'),
      url: this.config.url,
      params: {
        action: 'certificate/disable',
        id: r.id
      },
      listeners: {
        success: {fn: function() { this.refresh(); }, scope: this}
      }
    });
  },

  enableCertificate: function(recordOrBtn) {
    var r = recordOrBtn && recordOrBtn.data ? recordOrBtn.data : this.menu.record;
    if (!r) return;

    MODx.msg.confirm({
      title: _('warning'),
      text: _('msgiftcards_mgr_enable_confirm'),
      url: this.config.url,
      params: {
        action: 'certificate/enable',
        id: r.id
      },
      listeners: {
        success: {fn: function() { this.refresh(); }, scope: this}
      }
    });
  }
});
Ext.reg('msgiftcards-grid-certificates', msGiftCards.grid.Certificates);

msGiftCards.grid.RedemptionsAll = function(config) {
  config = config || {};
  var orderFieldId = 'msgiftcards-grid-redemptions-all__filter-order';
  var codeFieldId = 'msgiftcards-grid-redemptions-all__filter-code';
  var dateFromFieldId = 'msgiftcards-grid-redemptions-all__filter-date-from';
  var dateToFieldId = 'msgiftcards-grid-redemptions-all__filter-date-to';

  Ext.applyIf(config, {
    id: 'msgiftcards-grid-redemptions-all',
    url: msGiftCards.config.connectorUrl,
    baseParams: {
      action: 'certificate/redemptionsall'
    },
    fields: ['order_id', 'code', 'nominal', 'amount', 'balance_after', 'operation', 'createdon'],
    paging: true,
    remoteSort: true,
    anchor: '100%',
    autoHeight: true,
    viewConfig: {
      forceFit: true
    },
    columns: [{
      header: _('msgiftcards_mgr_order_id'),
      dataIndex: 'order_id',
      width: 120,
      sortable: true
    }, {
      header: _('msgiftcards_info_code'),
      dataIndex: 'code',
      width: 190,
      sortable: true
    }, {
      header: _('msgiftcards_info_nominal'),
      dataIndex: 'nominal',
      width: 130,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_amount'),
      dataIndex: 'amount',
      width: 140,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_operation'),
      dataIndex: 'operation',
      width: 130,
      sortable: true,
      renderer: function(v) {
        return v === 'credit'
          ? '<span style="color:green;">' + _('msgiftcards_mgr_redemption_operation_credit') + '</span>'
          : '<span style="color:#b00020;">' + _('msgiftcards_mgr_redemption_operation_debit') + '</span>';
      }
    }, {
      header: _('msgiftcards_mgr_balance_after'),
      dataIndex: 'balance_after',
      width: 160,
      sortable: true
    }, {
      header: _('msgiftcards_mgr_redemption_date'),
      dataIndex: 'createdon',
      width: 180,
      sortable: true
    }],
    tbar: [{
      xtype: 'msgiftcards-field-search',
      id: orderFieldId,
      width: 180,
      emptyText: _('msgiftcards_filter_order_id'),
      listeners: {
        search: {fn: function() { this._applyFilters(); }, scope: this},
        clear: {fn: function(field) {
          field.setValue('');
          this._applyFilters();
        }, scope: this}
      }
    }, {
      xtype: 'msgiftcards-field-search',
      id: codeFieldId,
      width: 220,
      emptyText: _('msgiftcards_filter_code'),
      listeners: {
        search: {fn: function() { this._applyFilters(); }, scope: this},
        clear: {fn: function(field) {
          field.setValue('');
          this._applyFilters();
        }, scope: this}
      }
    }, {
      xtype: 'msgiftcards-field-datefilter',
      id: dateFromFieldId,
      width: 165,
      emptyText: _('msgiftcards_filter_date_from'),
      listeners: {
        search: {fn: function() { this._applyFilters(); }, scope: this},
        clear: {fn: function() { this._applyFilters(); }, scope: this}
      }
    }, {
      xtype: 'msgiftcards-field-datefilter',
      id: dateToFieldId,
      width: 165,
      emptyText: _('msgiftcards_filter_date_to'),
      listeners: {
        search: {fn: function() { this._applyFilters(); }, scope: this},
        clear: {fn: function() { this._applyFilters(); }, scope: this}
      }
    }, '->', {
      xtype: 'button',
      text: _('msgiftcards_filter_reset_all'),
      handler: this._clearAllFilters,
      scope: this
    }]
  });
  msGiftCards.grid.RedemptionsAll.superclass.constructor.call(this, config);
};
Ext.extend(msGiftCards.grid.RedemptionsAll, MODx.grid.Grid, {
  _applyFilters: function() {
    var orderField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-order');
    var codeField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-code');
    var dateFromField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-date-from');
    var dateToField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-date-to');

    var dateFrom = dateFromField ? dateFromField.getValue() : '';
    var dateTo = dateToField ? dateToField.getValue() : '';

    this.getStore().baseParams.order_id = orderField ? orderField.getValue() : '';
    this.getStore().baseParams.code = codeField ? codeField.getValue() : '';
    this.getStore().baseParams.date_from = (dateFrom && dateFrom.format) ? dateFrom.format('Y-m-d') : (dateFrom || '');
    this.getStore().baseParams.date_to = (dateTo && dateTo.format) ? dateTo.format('Y-m-d') : (dateTo || '');
    this.getBottomToolbar().changePage(1);
  },

  _clearAllFilters: function() {
    var orderField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-order');
    var codeField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-code');
    var dateFromField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-date-from');
    var dateToField = Ext.getCmp('msgiftcards-grid-redemptions-all__filter-date-to');

    if (orderField) { orderField.setValue(''); }
    if (codeField) { codeField.setValue(''); }
    if (dateFromField) { dateFromField.setValue(''); }
    if (dateToField) { dateToField.setValue(''); }

    this.getStore().baseParams.order_id = '';
    this.getStore().baseParams.code = '';
    this.getStore().baseParams.date_from = '';
    this.getStore().baseParams.date_to = '';
    this.getStore().baseParams.query = '';
    this.getBottomToolbar().changePage(1);
  }
});
Ext.reg('msgiftcards-grid-redemptions-all', msGiftCards.grid.RedemptionsAll);
