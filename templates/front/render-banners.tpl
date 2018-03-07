{if isset($banners[$block.id]) && is_array($banners[$block.id]) && $banners[$block.id]}
	{if $banners_slider[$block.id]}
		<div class="owl-carousel pr-list js-carousel-banners">
			{assign var='folder' value=$core.config.banner_folder|replace:'/':''|cat:'/'}

			{foreach $banners[$block.id] as $banner}
				<div class="ia-carousel__item">
					{if $banner.type == 'image'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}"{if $banner.no_follow} rel="nofollow"{/if}>
								{ia_image file=$banner.image title=$banner.title type='large'}
							</a>
						{else}
							{ia_image file=$banner.image title=$banner.title type='large'}
						{/if}
					{elseif $banner.type == 'flash'}
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}{/if}">
							<param name="movie" value="{ia_print_img folder=$folder fl=$banner.image ups='true'}">
							<param name="quality" value="high">
							<param name="wmode" value="transparent">
							<embed src="{ia_print_img folder=$folder fl=$banner.image ups='true'}" quality="high" pluginspage=" http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}"{/if}></embed>
						</object>
					{elseif $banner.type == 'remote'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}>
								<img src="{$banner.image}" alt="{$banner.alt}" title="{$banner.title}" {if $banner.width != 0 and $banner.height != 0}style="width:{$banner.width};height:{$banner.height}px"{/if}>
							</a>
						{else}
							<img id="b{$banner.id}" src="{$banner.image}" alt="{$banner.alt}" title="{$banner.title}" {if $banner.width != 0 and $banner.height != 0}style="width:{$banner.width};height:{$banner.height}px"{/if}>
						{/if}
					{elseif $banner.type == 'html'}
						{$banner.content}
					{elseif $banner.type == 'text'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}>
								{$banner.planetext_content|escape:"html"}
							</a>
						{else}
							{$banner.planetext_content|escape:"html"}
						{/if}
					{/if}
				</div>
			{/foreach}
		</div>

		{ia_add_js}
$(function() {
	$('.js-carousel-banners').owlCarousel({
		items: 1,
		margin: 0,
		dots: false,
		nav: true,
		loop: true,
		autoplay:true,
		autoplayTimeout:6000,
		autoplayHoverPause:true,
		navText: ['<span class="fa fa-angle-left"></span>','<span class="fa fa-angle-right"></span>']
	});
});
		{/ia_add_js}

		{ia_print_js files='_IA_TPL_owl.carousel.min'}
	{else}
		<div class="text-center">
			{assign var='folder' value=$core.config.banner_folder|replace:'/':''|cat:'/'}
			{capture}{$banners[$block.id]|shuffle}{/capture}
			{section name=banner loop=$banners[$block.id] max=$banners_displayed[$block.id]}
				{assign 'banner' $banners[$block.id][banner]}
				<div class="p-b">
					{if $banner.type == 'image'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}"{if $banner.no_follow} rel="nofollow"{/if}>
								{ia_image file=$banner.image title=$banner.title type='large'}
							</a>
						{else}
							{ia_image file=$banner.image title=$banner.title type='large'}
						{/if}
					{elseif $banner.type == 'flash'}
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}{/if}">
							<param name="movie" value="{ia_print_img folder=$folder fl=$banner.image ups='true'}">
							<param name="quality" value="high">
							<param name="wmode" value="transparent">
							<embed src="{ia_print_img folder=$folder fl=$banner.image ups='true'}" quality="high" pluginspage=" http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" {if $banner.width != 0 and $banner.height != 0}width="{$banner.width}" height="{$banner.height}"{/if}></embed>
						</object>
					{elseif $banner.type == 'remote'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}>
								<img src="{$banner.image}" alt="{$banner.alt}" title="{$banner.title}" {if $banner.width != 0 and $banner.height != 0}style="width:{$banner.width};height:{$banner.height}px"{/if}>
							</a>
						{else}
							<img id="b{$banner.id}" src="{$banner.image}" alt="{$banner.alt}" title="{$banner.title}" {if $banner.width != 0 and $banner.height != 0}style="width:{$banner.width};height:{$banner.height}px"{/if}>
						{/if}
					{elseif $banner.type == 'html'}
						{$banner.content}
					{elseif $banner.type == 'text'}
						{if !empty($banner.url) && 'http://' != $banner.url}
							<a id="b{$banner.id}" href="{$banner.url}" target="{$banner.target}" {if $banner.no_follow}rel="nofollow"{/if}>
								{$banner.planetext_content|escape:"html"}
							</a>
						{else}
							{$banner.planetext_content|escape:"html"}
						{/if}
					{/if}
				</div>
				{bannerImpressionsCount id=$banner.id}
			{/section}
		</div>
	{/if}
{/if}
{ia_add_media files='js:_IA_URL_modules/banners/js/frontend/banners'}