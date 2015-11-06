Ext.onReady(function()
{
	var pageUrl = intelli.config.admin_url + '/banners/';

	if (Ext.get('js-grid-placeholder'))
	{
		intelli.banners = new IntelliGrid(
		{
			columns:[
				'selection',
				'expander',
				{name: 'title', title: _t('title'), width: 2, editor: 'text'},
				{name: 'position_title', title: _t('block'), width: 100},
				{name: 'banner_position', title: _t('position'), width: 100},
				{name: 'type', title: _t('type'), width: 130},
				{name: 'showed', title: _t('impressions'), width: 130},
				{name: 'clicked', title: _t('clicks'), width: 80},
				'status',
				'update',
				'delete'
			],
			url: pageUrl
		});
	}
	else
	{
		intelli.changeType = function(){
			$('#imageTitle, #imageParams, #imageUrl, #bannerurl, #htmlsettings, #bannerThumbnail, #uploadcontainer, #textcontainer, #planetextcontainer, #imageFit').hide();
			switch($('option:selected', '#js-type-selector').val())
			{
				case 'html':
					$('#htmlsettings, #textcontainer').show();
				break;

				case 'text':
					$('#planetextcontainer, #bannerurl').show();
				break;

				case 'local':
					$('#bannerThumbnail, #imageParams, #imageTitle, #uploadcontainer, #bannerurl').show();
					if($('#setown').attr("checked"))	{	$('#imageParams').show();	}
					if($('#imageKeepRatio').attr("checked"))	{	$('#imageFit').show();	}
				break;

				case 'remote':
					$('#imageUrl, #imageTitle, #bannerurl').show();
					if($('#setown').attr("checked"))	{	$('#imageParams').show();	}
					if($('#imageKeepRatio').attr("checked"))	{	$('#imageFit').show();	}
				break;

				case 'flash':
					$('#uploadcontainer, #imageParams, #bannerThumbnail').show();
				break;
			}
		};
		intelli.changePos = function(){
			var block = $('option:selected', '#js-banner-position');
			if (!block || !block.val())
			{
				return false;
			}

			$('#imageWidth').val(block.data('width'));
			$('#imageHeight').val(block.data('height'));
		};
		intelli.getTarget = function(){
			var type = $(this).val();

			if('other' == type)
			{
				$('#settarget input').val('');
				$('#settarget').show();
			}
			else
			{
				$('#settarget').hide();
				$('#settarget input').val(type);
			}
		};
		$(function(){
			$('#js-banner-position').change(intelli.changePos).change();
			$('#js-type-selector').change(intelli.changeType).change();
			$('#getTarget').change(intelli.getTarget);
		});
	}
});