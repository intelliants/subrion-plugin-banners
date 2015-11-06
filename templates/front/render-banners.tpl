{if isset($banners[$block.id]) && is_array($banners[$block.id]) && $banners[$block.id]}
<div style="text-align: center;">
	{assign var='folder' value=$core.config.banner_folder|replace:'/':''|cat:'/'}
	{capture}{$banners[$block.id]|shuffle}{/capture}
	{section name=banner loop=$banners[$block.id] max=$banners_displayed[$block.id]}
		{assign 'banner' $banners[$block.id][banner]}
		<div style="text-align: center; padding-bottom: 5px;">
			{if $banner.type == 'image'}
			<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}"{if $banner.no_follow} rel="nofollow"{/if}>
				{printImage imgfile=$folder|cat:$banner.image title=$banner.alt fullimage=true}
			</a>
			{elseif $banner.type == 'flash'}
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}{/if}">
				<param name="movie" value="{ia_print_img folder=$folder fl=$banner.image ups='true'}">
				<param name="quality" value="high">
				<param name="wmode" value="transparent">
				<embed src="{ia_print_img folder=$folder fl=$banner.image ups='true'}" quality="high" pluginspage=" http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}"{/if}></embed>
			</object>
			{elseif $banner.type == 'remote'}
			<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}><img src="{$banner.image}" alt="{$banner.alt}" title="{$banner.title}" {if $banner.width != 0 and $banner.height != 0}style="width:{$banner.width};height:{$banner.height}px"{/if}></a>
			{elseif $banner.type == 'html'}
			{$banner.content}
			{elseif $banner.type == 'text'}
			<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}>{$banner.planetext_content|escape:"html"}</a>
			{/if}
		</div>
		{bannerImpressionsCount id=$banner.id}
	{/section}
</div>
{/if}
{ia_add_media files='js:_IA_URL_plugins/banners/js/frontend/banners'}