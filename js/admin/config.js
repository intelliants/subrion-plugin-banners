$(function()
{
	$(".delete-block").click(function (){
		intelli.admin.bannersPluginBlock = $(this).parents(".banner-block");
		var block_id = intelli.admin.bannersPluginBlock.attr('id').split('_')[1];

		if (confirm(_t('sure_delete_block')))
		{
			$.get(intelli.config.admin_url + '/banners/config/edit.json',
			{action: 'remove_block', id: block_id},
			function(data)
			{
				if (data.error)
				{
					intelli.notifFloatBox({msg: _t('cant_delete_block'), type: 'error', autohide: true});
				}
				else
				{
					intelli.admin.bannersPluginBlock.remove();
					intelli.notifFloatBox({msg: _t('deleted'), type: 'success', autohide: true});
				}
			});
		}
	});

	$(".save-block").click(function() {
		var block = $(this).parents(".banner-block");
		var block_id = block.attr('id').split('_')[1];

		var amount = block.find('input.amount').val();
		var amount_displayed = block.find('input.amount_displayed').val();
		var width = block.find('input.width').val();
		var height = block.find('input.height').val();
		var slider = block.find('input#slider').val();

		$.get(intelli.config.admin_url + '/banners/config/edit.json',
		{ action: 'save_block', id: block_id, amount: amount, amount_displayed: amount_displayed, width: width, height: height, slider: slider },
		function(data)
		{
			if (!data.error)
			{
				intelli.notifFloatBox({msg: data.msg, type: 'notif', autohide: true});
			}
			else
			{
				intelli.notifFloatBox({msg: data.msg, type: 'error', autohide: true});
			}
		});
	});

	var add_block_win = null;
	var link_to_add = null;
	var add_block_tpl = null;

	$(".add-banner-block").click(function()
	{
		link_to_add = $(this).attr("href");

		add_block_tpl = '<div class="add_block_win"><p class="field"><span style="margin: 0 5px 0 0">'+ _t('banner_block_title') +':</span>'
				+ '<input type="text" name="title" class="title common" size="46"/></p>'
				+ '<p class="field" style="float: right"><input name="add_block" type="button" class="add_block common" style="margin: 0 5px 0 0;" value="' + _t('add') + '" />'
				+ '<input name="cancel" type="button" class="cancel common" style="margin: 0 4px 0 5px;" value="' + _t('cancel') + '" /></p></div>';

		if(!add_block_win)
		{
			add_block_win = new Ext.Window(
			{
				title: _t('add_banner_block'),
				width : 345,
				height : 130,
				modal: true,
				closeAction : 'hide',
				bodyStyle: 'padding: 10px;',
				html: add_block_tpl
			});
		}

		add_block_win.show();

		$(".add_block").click(function(){
			var title = $(this).parents('.add_block_win:first').find('.title').val();

			if (typeof(title) != 'undefined')
			{
				window.location = link_to_add + '&title=' + title;
			}
		});

		$(".cancel").click(function(){
			$(this).parents('.add_block_win:first').find('.title').val('');
			add_block_win.hide();
		});

		return false;
	});
});
