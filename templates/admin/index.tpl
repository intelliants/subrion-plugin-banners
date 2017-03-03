{if (iaCore::ACTION_ADD == $pageAction || iaCore::ACTION_EDIT == $pageAction) && $positions}
	<form class="sap-form" action="" method="post" enctype="multipart/form-data">
	{preventCsrf}
	<div class="wrap-list">
		<fieldset class="wrap-group">
			<div class="wrap-group-heading">
				<h4>{lang key='options'}</h4>
			</div>

			{if $item.image}
				{assign var='folder' value=$core.config.banner_folder|replace:'/':''|cat:'/'}
				<div class="row">
					<label class="col col-lg-2 control-label" for="js-banner-position">{lang key='banner_image'}</label>
					<div class="col col-lg-4" id="bannerThumbnail" style="display: none;">
						<div class="thumbnail thumbnail-single">
							{if $item.type == 'image'}
								{ia_image file=$item.image title=$item.title}
							{elseif $item.type == 'flash' and $item.image|substr:-3 == 'swf'}
								<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="{$item.width}" height="{$item.height}">
									<param name="movie" value="{ia_print_img fl=$item.image ups='true'}">
									<param name="quality" value="high">
									<embed src="{ia_print_img fl=$item.image folder=$folder ups='true'}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="{$item.width}" height="{$item.height}"></embed>
								</object>
							{/if}
						</div>
					</div>
				</div>
			{/if}
			
			<div class="row">
				<label class="col col-lg-2 control-label" for="js-banner-position">{lang key='banner_block'}</label>
				<div class="col col-lg-4">
					<select name="position" id="js-banner-position">
						{foreach $positions as $position}
							<option value="{$position.id}" data-width="{$position.width}" data-height="{$position.height}"
								{if $position.id == $item.position} selected="selected"{/if}
								{if $position.bn_max == $position.bn_col && $position.id != $item.position} disabled="disabled"{/if}>
									{$position.position}: {if $position.title}{$position.title}{else}{lang key='without_title'}{/if} ({$position.bn_col}/{$position.bn_max})
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			
			<div class="row">
				<label class="col col-lg-2 control-label" for="js-type-selector">{lang key='banner_type'}</label>
				<div class="col col-lg-4">
					<select id="js-type-selector" name="type">
					{foreach $types as $code => $type}
						<option value="{$code}"{if $code == $item.type} selected="selected"{/if}>{$type}</option>
					{/foreach}
					</select>
				</div>
			</div>

		<div class="row" id="imageUrl" style="display: none;">
			<label class="col col-lg-2 control-label" for="bannerImageUrl">{lang key='banner_img_url'}</label>
			<div class="col col-lg-4">
				<input type="text" name="image" id="bannerImageUrl" value="{$item.image}">
			</div>
		</div>
		
		<div class="row" id="uploadcontainer" style="display: none;">
			<label class="col col-lg-2 control-label" for="file">{lang key='choose_file_upload'} {lang key='field_required'}</label>
			<div class="col col-lg-4">
				{ia_html_file name="uploadfile" id='file'}
			</div>
		</div>

		<div class="row">
			<label class="col col-lg-2 control-label" for="banner-title">{lang key='banner_title'} {lang key='field_required'}</label>
			<div class="col col-lg-4">
				<input type="text" name="title" value="{$item.title}" id="banner-title">
			</div>
		</div>

		<div class="row" id="imageTitle" style="display: none;">
			<label class="col col-lg-2 control-label" for="banner-alt">{lang key='banner_alt'}</label>
			<div class="col col-lg-4">
				<input type="text" name="alt" size="32" value="{$item.alt}" id="banner-alt">
			</div>
		</div>

		<div class="row" id="textcontainer" style="display: none;">
			<label class="col col-lg-2 control-label">{lang key='content'}</label>
			<div class="col col-lg-4">
				{ia_wysiwyg name='content' value=$item.content}
			</div>
		</div>

		<div class="row" id="planetextcontainer" style="display: none;">
			<label class="col col-lg-2 control-label">{lang key='content'}</label>
			<div class="col col-lg-4">
				<textarea name="planetext_content">{$item.planetext_content}</textarea>
			</div>
		</div>

		<div class="row">
			<label class="col col-lg-2 control-label">Nofollow</label>
			<div class="col col-lg-4">
				{html_radio_switcher value=$item.no_follow name='no_follow'}
			</div>
		</div>

		<div class="field_type" id="imageParams" style="display: none;">
			<div class="row">
				<label class="col col-lg-2 control-label" for="imageWidth">{lang key='image_width'}</label>
				<div class="col col-lg-4">
					<input type="text" maxlength="3" name="width" id="imageWidth" value="{$item.width}" readonly>
					<input type="hidden" id="imageWidth_opt">
				</div>
			</div>
			<div class="row">
				<label class="col col-lg-2 control-label" for="imageHeight">{lang key='image_height'}</label>
				<div class="col col-lg-4">
					<input type="text" maxlength="3" name="height" id="imageHeight" value="{$item.height}" readonly>
					<input type="hidden" id="imageHeight_opt">
				</div>
			</div>
		</div>

		<div class="field_type" id="bannerurl" style="display: none;">
			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='banner_url'}</label>
				<div class="col col-lg-4">
					<input type="text" name="url" value="{$item.url}">
				</div>
			</div>
			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='target'}</label>
				<div class="col col-lg-4">
					<select id="getTarget" name="target">
						{foreach $targets as $code => $target}
							<option value="{$code}"{if $code == $item.target} selected="selected"{/if}>{$target}</option>
						{/foreach}
						<option value="other">{lang key='other'}...</option>
					</select>
					<span id="settarget" style="display: none;">
						<input type="text" name="targetframe" class="common" value="{if $item.target}{$item.target}{else}_blank{/if}">
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="wrap-list">
		{include file='fields-system.tpl'}
	</div>
	<input type="hidden" name="id" value="{if isset($item.id)}{$item.id}{/if}">
	</form>
{else}
	{include file='grid.tpl'}
{/if}

{ia_print_js files='_IA_URL_modules/banners/js/admin/banners'}