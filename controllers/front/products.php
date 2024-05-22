<?php

include_once(dirname(__FILE__) . '../../../productspurchased.php');

class productspurchasedProductsModuleFrontController extends ModuleFrontController
{
  
	protected function getCustomerProducts()
	{
		$id_customer = $this->context->customer->id;
		$id_lang = $this->context->language->id;
		
		$sql = 'SELECT o.`id_order`,i.`id_image`, od.`product_id`,od.`product_name`,od.`product_quantity`,od.`unit_price_tax_incl` AS `price_tax_incl`,od.`unit_price_tax_excl` AS `price_tax_excl`,
		p.`price` AS `price_now_notax`,t.`rate`,o.`id_customer`,o.`current_state`,o.`date_add`,pl.`link_rewrite`
		FROM `' . _DB_PREFIX_ . 'orders` o
		LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.`id_product` = od.`product_id`
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = od.`product_id` AND pl.`id_lang` = '.$id_lang.'
		LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON tr.`id_tax_rules_group` = p.`id_tax_rules_group` AND tr.`id_country` = '.Configuration::get('PS_SHOP_COUNTRY_ID').'
		LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON t.`id_tax` = tr.`id_tax`
		WHERE o.`id_customer` = '.$id_customer.'
		AND i.`cover` = 1
		AND o.`current_state` IN ('.Configuration::get('PRODUCTSPURCHASED_ORDER_STATES').');';
		// L24 states: 2,3,5,14,15,18,22

		return Db::getInstance()->executeS($sql);

	}
  
	public function initContent()
	{
		parent::initContent();

		$orderstates = OrderState::getOrderStates($this->context->language->id);
		$order_states = [];
		foreach ($orderstates as $orderstate) {
			$order_states[$orderstate['id_order_state']] = [
				'name' => $orderstate['name'],
				'color' => $orderstate['color'],
			];
		}
		
		$this->context->smarty->assign(array(
			'prices_orig' => Configuration::get('PRODUCTSPURCHASED_PRICES_ORIG'),
			'custom_products' => $this->getCustomerProducts(),
			'order_states' => $order_states,
		));
		$this->setTemplate('product_lists.tpl');
	}
	

}
