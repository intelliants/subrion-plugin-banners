<div class="wrap-list">
	{foreach $positions as $position}
		<fieldset class="wrap-group" id="position-{$position}">
			<div class="wrap-group-heading">
				<h4>{$position}</h4>
			</div>
			<a class="btn btn-primary" id="block_{$position}" href="{$smarty.const.IA_SELF}?action=add_block&pos={$position}&num={if isset($banner_blocks.$position)}{$banner_blocks.$position|@count}{/if}"><i class="i-plus-alt"></i> {lang key="add_banner_block"}</a>
			{if isset($banner_blocks.$position)}
				<div class="plates-list sap-form">
					{foreach $banner_blocks.$position as $block}
						<div class="banner-block col col-lg-2" id="block_{$block.id}">
							<h4>
								{if $block.title}{$block.title}{else}{lang key='without_title'}{/if}
								<a href="{$smarty.const.IA_ADMIN_URL}blocks/edit/{$block.id}/">
									<i class="i-edit"></i>
								</a>
							</h4>
							<p><span class="text-small text-muted">{lang key='banners_amount'}:</span><span class="right"><input class="amount" type="text" value="{$blocks_options[$block.id].amount}" name="num_banners_{$block.id}"></span></p>
							<p><span class="text-small text-muted">{lang key='banners_amount_displayed'}:</span><span class="right"><input class="amount_displayed" type="text" value="{$blocks_options[$block.id].amount_displayed}" name="amount_banners_displayed_{$block.id}"></span></p>
							<p><span class="text-small text-muted">{lang key='banner_width'}:</span><span class="right"><input class="width" type="text" value="{$blocks_options[$block.id].width}" name="banner_width_{$block.id}"></span></p>
							<p><span class="text-small text-muted">{lang key='banner_height'}:</span><span class="right"><input class="height" type="text" value="{$blocks_options[$block.id].height}" name="banner_height_{$block.id}"></span></p>
							<p class="actions">
								<input class="btn btn-sm btn-success save-block" type="button" name="save" value="{lang key='save'}">
								<input class="btn btn-sm btn-danger delete-block" type="button" name="delete" value="{lang key='delete'}">
							</p>
						</div>
					{/foreach}
				</div>
			{else}
				<span class="alert">{lang key='no_banner_blocks'}</span>
			{/if}
		</fieldset>
	{/foreach}
</div>

{ia_print_css files='_IA_URL_plugins/banners/templates/admin/css/style'}
{ia_print_js files="_IA_URL_plugins/banners/js/admin/config"}