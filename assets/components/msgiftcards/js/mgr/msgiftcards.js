Ext.namespace('msGiftCards');
Ext.namespace('msGiftCards.page');
Ext.namespace('msGiftCards.panel');
Ext.namespace('msGiftCards.grid');
Ext.namespace('msGiftCards.window');
Ext.namespace('msGiftCards.combo');

msGiftCards.config = {
  connectorUrl: MODx.config.assets_url + 'components/msgiftcards/connector.php'
};
msGiftCards.config.managerDateFormat = (MODx.config && MODx.config.manager_date_format) ? MODx.config.manager_date_format : 'Y-m-d';
msGiftCards.config.managerTimeFormat = (MODx.config && MODx.config.manager_time_format) ? MODx.config.manager_time_format : 'H:i';

msGiftCards.utils = msGiftCards.utils || {};
msGiftCards.utils.getManagerDateTimeFormat = function() {
  return msGiftCards.config.managerDateFormat + ' ' + msGiftCards.config.managerTimeFormat;
};
msGiftCards.utils.formatDateTime = function(value) {
  if (!value || value === '0000-00-00 00:00:00') {
    return '';
  }

  var date = value;
  if (!(date instanceof Date)) {
    date = Date.parseDate(String(value), 'Y-m-d H:i:s')
      || Date.parseDate(String(value), 'Y-m-d H:i')
      || Date.parseDate(String(value), 'Y-m-d');
  }

  if (!date) {
    return value;
  }

  return date.format(msGiftCards.utils.getManagerDateTimeFormat());
};

msGiftCards.utils.parseManagerDate = function(value) {
  if (!value) {
    return null;
  }

  if (value instanceof Date) {
    return value;
  }

  return Date.parseDate(String(value), msGiftCards.config.managerDateFormat)
    || Date.parseDate(String(value), 'Y-m-d');
};
msGiftCards.utils.toIsoDate = function(value) {
  var date = msGiftCards.utils.parseManagerDate(value);
  return date ? date.format('Y-m-d') : '';
};
msGiftCards.combo.Search = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    xtype: 'twintrigger',
    ctCls: 'x-field-search',
    allowBlank: true,
    msgTarget: 'under',
    emptyText: _('search'),
    name: 'query',
    triggerAction: 'all',
    clearBtnCls: 'x-field-search-clear',
    searchBtnCls: 'x-field-search-go',
    onTrigger1Click: this._triggerSearch,
    onTrigger2Click: this._triggerClear
  });
  msGiftCards.combo.Search.superclass.constructor.call(this, config);
  this.on('render', function() {
    this.getEl().addKeyListener(Ext.EventObject.ENTER, function() {
      this._triggerSearch();
    }, this);
  });
  this.addEvents('clear', 'search');
};
Ext.extend(msGiftCards.combo.Search, Ext.form.TwinTriggerField, {
  initComponent: function() {
    Ext.form.TwinTriggerField.superclass.initComponent.call(this);
    this.triggerConfig = {
      tag: 'span',
      cls: 'x-field-search-btns',
      cn: [
        {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
        {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
      ]
    };
  },

  _triggerSearch: function() {
    this.fireEvent('search', this);
  },

  _triggerClear: function() {
    this.fireEvent('clear', this);
  }
});
Ext.reg('msgiftcards-combo-search', msGiftCards.combo.Search);
Ext.reg('msgiftcards-field-search', msGiftCards.combo.Search);

msGiftCards.combo.DateFilter = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    xtype: 'twintrigger',
    ctCls: 'x-field-search',
    allowBlank: true,
    msgTarget: 'under',
    triggerAction: 'all',
    calendarBtnCls: 'x-form-date-trigger x-field-date-go',
    clearBtnCls: 'x-field-search-clear',
    onTrigger1Click: this._triggerCalendar,
    onTrigger2Click: this._triggerClear
  });
  msGiftCards.combo.DateFilter.superclass.constructor.call(this, config);
  this.addEvents('clear', 'search');
};
Ext.extend(msGiftCards.combo.DateFilter, Ext.form.TwinTriggerField, {
  initComponent: function() {
    Ext.form.TwinTriggerField.superclass.initComponent.call(this);
    this.triggerConfig = {
      tag: 'span',
      cls: 'x-field-search-btns',
      cn: [
        {tag: 'div', cls: 'x-form-trigger ' + this.calendarBtnCls},
        {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
      ]
    };
  },

  _triggerCalendar: function() {
    if (!this.menu) {
      this.menu = new Ext.menu.DateMenu({
        hideOnClick: true,
        handler: function(dp, date) {
          this.setValue(date.format(msGiftCards.config.managerDateFormat || 'Y-m-d'));
          this.fireEvent('search', this);
        },
        scope: this
      });
    }

    var parsed = msGiftCards.utils.parseManagerDate(this.getValue());
    if (parsed) {
      this.menu.picker.setValue(parsed);
    }

    var pos = this.el.getXY();
    this.menu.showAt([pos[0], pos[1] + this.el.getHeight()]);
  },

  _triggerClear: function() {
    this.setValue('');
    this.fireEvent('clear', this);
  }
});
Ext.reg('msgiftcards-combo-datefilter', msGiftCards.combo.DateFilter);
Ext.reg('msgiftcards-field-datefilter', msGiftCards.combo.DateFilter);


