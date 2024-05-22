{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}"
                      title="{l s='Manage my account' mod='productspurchased'}"
                      rel="nofollow">{l s='My account' mod='productspurchased'}</a>
  <span class="navigation-pipe">{$navigationPipe}</span>
  <span class="navigation_page">{l s='My purchased products' mod='productspurchased'}</span>
{/capture}


	<h1 class="page-heading">{l s='My purchased products' mod='productspurchased'}</h1>
	<p class="block">{l s='Here you can view each of your products ordered, from all your payed and closed orders in this e-shop.' mod='productspurchased'}<br/>{l s='Products are grouped by orders.' mod='productspurchased'}<br/>
		{if $prices_orig == 1}
			{l s='Prices displayed here are according to date of placed order.' mod='productspurchased'}
		{else}
			{l s='Prices displayed here are actual today, not according to date of placed order.' mod='productspurchased'}
		{/if}
	</p>
	
	<div class="block-center" id="purchased-products">
		<div id="cart_summary" class="container">
		{if $custom_products && count($custom_products)}
			{assign var="same_cycle" value=0}
			{foreach from=$custom_products item='product'}
				{if $same_cycle != $product.id_order|intval}
					{assign var="id_order_state" value=$product.current_state}
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-12">
							<h3 class="page-subheading"><a href="{$link->getPageLink('order-detail', true)|escape:'html':'UTF-8'}?id_order={$product.id_order|intval}" title="{l s='Open order detail #' mod='productspurchased'}: {$product.id_order|intval}">#{$product.id_order|intval}</a> <small>({$product.date_add|date_format:'j. n. Y'})</small> <span class="badge" style="background-color:{$order_states.$id_order_state.color};">{$order_states.$id_order_state.name}</span></h3>
						</div>
					</div>
					{$same_cycle=$product.id_order|intval}
				{/if}
				<div class="row">
					<div class="col-xs-3 col-sm-2 col-md-2 col-lg-1"><a href="{$link->getProductLink($product.product_id, null, null, null, null, null, null, Configuration::get('PS_REWRITING_SETTINGS'), false, true)|escape:'html':'UTF-8'}" title="{l s='Go to product page' mod='productspurchased'}"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'cart_default', null, ImageManager::retinaSupport())|escape:'html':'UTF-8'}" class="img-responsive" alt="" /></a></div>
					<div class="col-xs-0 col-sm-10"><p>&nbsp;</p></div>
					<div class="col-sm-6"><p><a href="{$link->getProductLink($product.product_id, null, null, null, null, null, null, Configuration::get('PS_REWRITING_SETTINGS'), false, true)|escape:'html':'UTF-8'}" title="{l s='Go to product page' mod='productspurchased'}">{$product.product_name|escape:'html':'UTF-8'}</a></p></div>
					<div class="col-sm-3"><p>{$product.product_quantity|intval}x&nbsp;<span class="price product-price">
						{if $prices_orig == 1}
							{if (isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label)}
								{convertPrice price=$product.price_tax_incl}
							{else}
								{convertPrice price=$product.price_tax_excl}
							{/if}
						{else}
							{if (isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label)}
								{convertPrice price=(($product.price_now_notax/100*$product.rate)+$product.price_now_notax)}
							{else}
								{convertPrice price=$product.price_now_notax}
							{/if}
						{/if}
					</span>
					{if (isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label)}
						<small>({l s='tax incl.' mod='productspurchased'})</small>
					{else}
						<small>({l s='tax excl.' mod='productspurchased'})</small>
					{/if}
					</p></div>
				</div>
			{/foreach}
		{else}
			<div class="alert alert-success">{l s='You have no closed order yet.' mod='productspurchased'}</div>
		{/if}
		</div>
	</div>

	<nav>
	  <ul class="footer_links clearfix">
		<li>
		  <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" class="btn btn-default">
			<i class="icon-chevron-left"></i> {l s='Back to your account' mod='productspurchased'}
		  </a>
		</li>
	  </ul>
	</nav>
