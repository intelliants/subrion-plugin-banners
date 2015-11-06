intelli.banner_plans = function()
{
	return {
		oGrid: null,
		vUrl: intelli.config.admin_url+'/banners/plans/',
		statusesStore: new Ext.data.SimpleStore(
		{
			fields: ['value', 'display'],
			data : [['active', 'active'],['inactive', 'inactive']]
		}),
		pagingStore: new Ext.data.SimpleStore(
		{
			fields: ['value', 'display'],
			data : [['10', '10'],['20', '20'],['30', '30'],['40', '40'],['50', '50']]
		})
	};
}();

intelli.exGModel = Ext.extend(intelli.gmodel,
{
	constructor: function(config)
	{
		intelli.exGModel.superclass.constructor.apply(this, arguments);
	},
	setupReader: function()
	{
		this.record = Ext.data.Record.create([
			{name: 'title', mapping: 'title'},
			{name: 'cost', mapping: 'cost'},
			{name: 'period', mapping: 'period'},
			{name: 'lang', mapping: 'lang'},
			{name: 'order', mapping: 'order'},
			{name: 'status', mapping: 'status'},
			{name: 'edit', mapping: 'edit'},
			{name: 'remove', mapping: 'remove'}
		]);

		this.reader = new Ext.data.JsonReader({
			root: 'data',
			totalProperty: 'total',
			id: 'id'
			}, this.record
		);

		return this.reader;
	},
	setupColumnModel: function()
	{
		this.columnModel = new Ext.grid.ColumnModel([
		this.checkColumn,
		{
			header: _t('title'), 
			dataIndex: 'title', 
			sortable: true, 
			width: 250,
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},{
			header: _t('cost'),
			dataIndex: 'cost', 
			width: 70,
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},{
			header: _t('days'), 
			dataIndex: 'period', 
			width: 70,
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},{
			header: _t('language'), 
			dataIndex: 'lang',
			width: 100
		},{
			header: _t('order'), 
			dataIndex: 'order',
			width: 100,
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},{
			header: _t('status'), 
			dataIndex: 'status',
			width: 100,
			editor: new Ext.form.ComboBox({
				typeAhead: true,
				triggerAction: 'all',
				editable: false,
				lazyRender: true,
				store: intelli.banner_plans.statusesStore,
				displayField: 'display',
				valueField: 'value',
				mode: 'local'
			})
		},{
			header: "", 
			dataIndex: 'edit',
			width: 40,
			hideable: false,
			menuDisabled: true,
			align: 'center'
		},{
			header: "",
			dataIndex: 'remove',
			width: 40,
			hideable: false,
			menuDisabled: true,
			align: 'center'
		}]);

		return this.columnModel;
	}
});

intelli.exGrid = Ext.extend(intelli.grid,
{
	model: null,
	constructor: function(config)
	{
		intelli.exGrid.superclass.constructor.apply(this, arguments);

		this.model = new intelli.exGModel({url: config.url});

		this.dataStore = this.model.setupDataStore();
		this.columnModel = this.model.setupColumnModel();
		this.selectionModel = this.model.setupSelectionModel();

		this.dataStore.setDefaultSort('title');
	},
	init: function()
	{
		this.plugins = new Ext.ux.PanelResizer({
            minHeight: 100
		});

		this.title = _t('plans');
		this.renderTo = 'box_banner_plans';

		this.setupBaseParams();
		this.setupPagingPanel();
		this.setupGrid();

		this.setRenderers();
		this.setEvents();

		this.grid.autoExpandColumn = 1;

		this.loadData();
	},
	setupPagingPanel: function()
	{
		var pagingPanel = new Ext.form.ComboBox(
		{
			typeAhead: true,
			allowDomMove: false,
			triggerAction: 'all',
			editable: false,
			lazyRender: true,
			width: 80,
			store: intelli.banner_plans.pagingStore,
			value: '10',
			displayField: 'display',
			valueField: 'value',
			mode: 'local',
			id: 'pgnPnl'
		});

		var removeButton = new Ext.Toolbar.Button({
			text: 'Remove',
			id: 'removeBtn',
			iconCls: 'remove-grid-ico',
			disabled: true
		});

		var changeStatus = new Ext.form.ComboBox({
			typeAhead: true,
			allowDomMove: false,
			triggerAction: 'all',
			editable: false,
			lazyRender: true,
			store: intelli.banner_plans.statusesStore,
			value: 'active',
			displayField: 'value',
			valueField: 'display',
			mode: 'local',
			disabled: true,
			id: 'statusCmb'
		});

		var goButton = new Ext.Toolbar.Button({
			text: 'Go',
			disabled: true,
			iconCls: 'go-grid-ico',
			id: 'goBtn'
		});
		this.bottomToolbar = new Ext.PagingToolbar(
		{
			store: this.dataStore,
			pageSize: 10,
			displayInfo: true,
			plugins: new Ext.ux.ProgressBarPager(),
			items: [
				'-',
				_t('items_per_page') + ':',
				{
					xtype: 'bettercombo',
					typeAhead: true,
					triggerAction: 'all',
					editable: false,
					lazyRender: true,
					width: 80,
					store: intelli.banner_plans.pagingStore,
					value: '10',
					displayField: 'display',
					valueField: 'value',
					mode: 'local',
					id: 'pgnPnl'
				},
				'-',
				{
					text: _t('remove'),
					id: 'removeBtn',
					iconCls: 'remove-grid-ico',
					disabled: true
				},
				'-',
				'Status:',
				{
					xtype: 'combo',
					typeAhead: true,
					triggerAction: 'all',
					editable: false,
					lazyRender: true,
					store: intelli.banner_plans.statusesStore,
					value: 'active',
					displayField: 'value',
					valueField: 'display',
					mode: 'local',
					disabled: true,
					id: 'statusCmb'
				},{
					text: 'Go',
					disabled: true,
					iconCls: 'go-grid-ico',
					id: 'goBtn'
				}
			]
		});
	},
	setupBaseParams: function()
	{
		this.dataStore.baseParams = {action: 'get'};
	},
	setRenderers: function()
	{
		/* change background color for status field */
		this.columnModel.setRenderer(6, function(value, metadata)
		{
			metadata.css = value;

			return value;
		});

		/* add edit link */
		this.columnModel.setRenderer(7, function(value, metadata)
		{
			return '<img class="grid_action" alt="'+ _t('edit') +'" title="'+ _t('edit') +'" src="templates/'+ intelli.config.admin_tmpl +'/img/icons/edit-grid-ico.png" />';
		});

		/* add remove link */
		this.columnModel.setRenderer(8, function(value, metadata)
		{
			return '<img class="grid_action" alt="'+ _t('remove') +'" title="'+ _t('remove') +'" src="templates/'+ intelli.config.admin_tmpl +'/img/icons/remove-grid-ico.png" />';
		});
	},
	setEvents: function()
	{
		/* 
		 * Events
		 */

		/* Edit fields */
		intelli.banner_plans.oGrid.grid.on('afteredit', function(editEvent)
		{
			Ext.Ajax.request(
			{
				waitMsg: 'Saving changes...',
				url: intelli.banner_plans.vUrl,
				method: 'POST',
				params:
				{
					action: 'update',
					'ids[]': editEvent.record.id,
					field: editEvent.field,
					value: editEvent.value
				},
				failure: function()
				{
					Ext.MessageBox.alert(_t('error_saving_changes'));
				},
				success: function(data)
				{
					editEvent.grid.getStore().reload();

					var response = Ext.decode(data.responseText);
					var type = response.error ? 'error' : 'notif';
						
					intelli.admin.notifBox({msg: response.msg, type: type, autohide: true});
					
					intelli.banner_plans.oGrid.grid.getStore().reload();
				}
			});
		});

		/* Go button action */
		Ext.getCmp('goBtn').on('click', function()
		{
			var rows = intelli.banner_plans.oGrid.grid.getSelectionModel().getSelections();
			var status = Ext.getCmp('statusCmb').getValue();
			var ids = new Array();

			for(var i = 0; i < rows.length; i++)
			{
				ids[i] = rows[i].json.id;
			}

			Ext.Ajax.request(
			{
				waitMsg: 'Saving changes...',
				url: intelli.banner_plans.vUrl,
				method: 'POST',
				params:
				{
					action: 'update',
					'ids[]': ids,
					field: 'status',
					value: status
				},
				failure: function()
				{
					Ext.MessageBox.alert('Error saving changes...');
				},
				success: function(data)
				{
					intelli.banner_plans.oGrid.grid.getStore().reload();

					var response = Ext.decode(data.responseText);
					var type = response.error ? 'error' : 'notif';
						
					intelli.admin.notifBox({msg: response.msg, type: type, autohide: true});
				}
			});
		});
		
		/* Remove click */
		intelli.banner_plans.oGrid.grid.on('cellclick', function(grid, rowIndex, columnIndex)
		{
			var record = grid.getStore().getAt(rowIndex);
			var fieldName = grid.getColumnModel().getDataIndex(columnIndex);
			var data = record.get(fieldName);

			if('edit' == fieldName)
			{
				intelli.banner_plans.oGrid.saveGridState();

				window.location = intelli.config.admin_url+'/banners/plans/edit/?id='+ record.json.id;
			}

			if('remove' == fieldName)
			{
				Ext.Msg.show(
				{
					title: _t('confirm'),
					msg: _t('are_you_sure_to_delete_this_plan'),
					buttons: Ext.Msg.YESNO,
					icon: Ext.Msg.QUESTION,
					fn: function(btn)
					{
						if('yes' == btn)
						{
							Ext.Ajax.request(
							{
								url: intelli.banner_plans.vUrl,
								method: 'POST',
								params:
								{
									prevent_csrf: $("#box_banner_plans input[name='prevent_csrf']").val(),
									action: 'remove',
									'ids[]': record.json.id
								},
								failure: function()
								{
									Ext.MessageBox.alert(_t('error_saving_changes'));
								},
								success: function(data)
								{
									var response = Ext.decode(data.responseText);
									var type = response.error ? 'error' : 'notif';
									
									intelli.admin.notifBox({msg: response.msg, type: type, autohide: true});

									Ext.getCmp('removeBtn').disable();

									intelli.banner_plans.oGrid.grid.getStore().reload();
								}
							});
						}
					}
				});
			}
		});

		/* Enable disable functionality buttons */
		intelli.banner_plans.oGrid.grid.getSelectionModel().on('rowselect', function()
		{
			Ext.getCmp('statusCmb').enable();
			Ext.getCmp('goBtn').enable();
			Ext.getCmp('removeBtn').enable();
		});

		intelli.banner_plans.oGrid.grid.getSelectionModel().on('rowdeselect', function(sm)
		{
			if(0 == sm.getCount())
			{
				Ext.getCmp('statusCmb').disable();
				Ext.getCmp('goBtn').disable();
				Ext.getCmp('removeBtn').disable();
			}
		});

		/* remove button action */
		Ext.getCmp('removeBtn').on('click', function()
		{
			var rows = intelli.banner_plans.oGrid.grid.getSelectionModel().getSelections();
			var ids = new Array();

			for(var i = 0; i < rows.length; i++)
			{
				ids[i] = rows[i].json.id;
			}

			Ext.Msg.show(
			{
				title: _t('confirm'),
				msg: (ids.length > 1) ? _t('are_you_sure_to_delete_selected_plans') : _t('are_you_sure_to_delete_this_plan'),
				buttons: Ext.Msg.YESNO,
				icon: Ext.Msg.QUESTION,
				fn: function(btn)
				{
					if('yes' == btn)
					{
						Ext.Ajax.request(
						{
							url: intelli.banner_plans.vUrl,
							method: 'POST',
							params:
							{
								action: 'remove',
								'ids[]': ids
							},
							failure: function()
							{
								Ext.MessageBox.alert(_t('error_saving_changes'));
							},
							success: function(data)
							{
								var response = Ext.decode(data.responseText);
								var type = response.error ? 'error' : 'notif';
									
								intelli.admin.notifBox({msg: response.msg, type: type, autohide: true});

								intelli.banner_plans.oGrid.grid.getStore().reload();

								Ext.getCmp('removeBtn').disable();
							}
						});
					}
				}
			});
		});

		/* Paging panel event */
		Ext.getCmp('pgnPnl').on('change', function(field, new_value, old_value)
		{
			intelli.banner_plans.oGrid.grid.getStore().baseParams.limit = new_value;
			intelli.banner_plans.oGrid.grid.bottomToolbar.pageSize = parseInt(new_value);

			intelli.banner_plans.oGrid.grid.getStore().reload();
		});
	}
});

Ext.onReady(function()
{
	if(Ext.get('box_banner_plans'))
	{
		intelli.banner_plans.oGrid = new intelli.exGrid({url: intelli.banner_plans.vUrl});

		/* Initialization grid */
		intelli.banner_plans.oGrid.init();
	}

	var check_all = true;

	$("input[name='blocks[]']").each(function()
	{
		if(!$(this).attr("checked"))
		{
			check_all = false;
		}
	});

	$("#check_all_fields").attr("checked", check_all);

	$("#check_all_fields").click(function()
	{
		var checked = $(this).attr("checked");

		$("input[name='blocks[]']").each(function()
		{
			$(this).attr("checked", checked);
		});
	});
});
