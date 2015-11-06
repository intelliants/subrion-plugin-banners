intelli.banners = function()
{
	return {
		oGrid: null,
		title: _t('manage_banners'),
		url: intelli.config.admin_url + '/banners/',
		removeBtn: true,
		progressBar: false,
		statusesStore: ['active','inactive'],
		record:['title', 'position_title', 'banner_position', 'type', 'status', 'showed', 'clicked', 'edit_block', 'edit', 'remove'],
		columns:[
			'checkcolumn',
			{
				header: _t('title'),
				dataIndex: 'title',
				sortable: true,
				width: 250,
				editor: new Ext.form.TextField({
					allowBlank: false
				})
			},{
				header: _t('block'),
				dataIndex: 'position_title',
				sortable: true,
				width: 85
			},{
                header: _t('position'),
                dataIndex: 'banner_position',
                sortable: true,
                width: 85
            },{
				header: _t('type'),
				dataIndex: 'type',
				width: 130,
				sortable: true
			},'status',{
				header: _t('impressions'),
				dataIndex: 'showed',
				sortable: true,
				width: 80
			},{
				header: _t('clicks'),
				dataIndex: 'clicked',
				sortable: true,
				width: 80
			},{
                header: _t('edit') + ' ' + _t('block'),
                custom: 'edit_block',
                index: true,
                href: intelli.config.admin_url+'/blocks/edit/?id={value}',
                icon: 'manage-grid-ico.png',
                hideable: false,
                menuDisabled: true,
                title: _t('edit') + ' ' + _t('block')
			},{
				custom: 'edit',
				redirect: intelli.config.admin_url+'/banners/edit/?id=',
				icon: 'edit-grid-ico.png',
				title: _t('edit')
			}
			,'remove'
		]
	};

}();

Ext.onReady(function()
{
	intelli.banners = new intelli.exGrid(intelli.banners);
	intelli.banners.cfg.tbar = new Ext.Toolbar(
	{
		items:[
		_t('text') + ':',
		{
			xtype: 'textfield',
			name: 'searchText',
			id: 'searchText',
			emptyText: ''
		},
		_t('status') + ':',
		{
			xtype: 'combo',
			typeAhead: true,
			triggerAction: 'all',
			editable: false,
			lazyRender: true,
			store: intelli.banners.cfg.statusesStore,
			value: 'all',
			displayField: 'display',
			valueField: 'value',
			mode: 'local',
			id: 'stsFilter'
		},{
			text: _t('search'),
			iconCls: 'search-grid-ico',
			id: 'fltBtn',
			handler: function()
			{
				var textSearch = Ext.getCmp('searchText').getValue();
				var status = Ext.getCmp('stsFilter').getValue();

				if('' != textSearch || '' != status)
				{
					intelli.banners.dataStore.baseParams =
					{
						action: 'get',
						textSearch: textSearch,
						status: status
					};

					intelli.banners.dataStore.reload();
				}
			}
		},
		'-',
		{
			text: _t('reset'),
			id: 'resetBtn',
			handler: function()
			{
				Ext.getCmp('searchText').reset();
				Ext.getCmp('stsFilter').setValue('all');

				intelli.banners.dataStore.baseParams =
				{
					action: 'get',
					textSearch: '',
					status: ''
				};

				intelli.banners.dataStore.reload();
			}
		}]
	});

	intelli.banners.init();

	if(intelli.urlVal('status'))
	{
		Ext.getCmp('stsFilter').setValue(intelli.urlVal('status'));
	}
});