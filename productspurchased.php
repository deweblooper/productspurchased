<?php
	if (!defined('_PS_VERSION_'))
	exit;
	
	class ProductsPurchased extends Module
	{
		public function __construct()
		{
			$this->name = 'productspurchased';
			$this->tab = 'checkout';
			$this->version = '1.0.0';
			$this->author = 'waterwhite';
			$this->need_instance = 0;
			$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99'); 
			$this->bootstrap = true;
			
			parent::__construct();
			
			$this->displayName = $this->l('Purchased products');
			$this->description = $this->l('Products purchased by customer in list at Customer Account');
			
			$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

			
			$this->lang = (int)Configuration::get('PS_LANG_DEFAULT');
			$this->shop = (int)Configuration::get('PS_SHOP_DEFAULT');
			
		}
		
		
		// install and register hooks
		public function install()
		{
			if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
			if (!parent::install() ||
			!Configuration::updateValue('PRODUCTSPURCHASED_ORDER_STATES', '2,3,5,14') ||
			!$this->registerHook('displayCustomerAccount') ||
			!$this->addMetaSeo()
			)
			return false;
			
			return true;
		}
		
		
		// uninstall
		public function uninstall()
		{
			// remove SEO page meta
			$page = 'module-' . $this->name . '-products';
			$meta_values = Meta::getMetaByPage($page, $this->lang);
			$meta = new Meta($meta_values['id_meta']);
			$meta->delete();
			
			$sql = 'DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "PRODUCTSPURCHASED_%"';

			if (!parent::uninstall() ||
			!Db::getInstance()->execute($sql)
			)
			return false;
			
			return true;
		}
		
		
		// add SEO page meta
		public function addMetaSeo()
		{
			$meta = [
				'page'     => 'module-' . $this->name . '-products',
				'configurable'      => 1,
			];
			$query_meta = Db::getInstance()->insert('meta', $meta, false, true, Db::INSERT_IGNORE);
			$id_meta = Db::getInstance()->Insert_ID();

			$meta_lang = [
				'id_meta' => (int) $id_meta,
				'id_shop' => (int) $this->shop,
				'id_lang' => (int) $this->lang,
				'title' => $this->displayName,
				'description' => "",
				'keywords' => "",
				'url_rewrite' => $this->l('purchased-products'),
			];

			$query_meta_lang = Db::getInstance()->insert('meta_lang', $meta_lang, false, true, Db::INSERT_IGNORE);
			
			if (!$id_meta || !$query_meta_lang)
				return false;
			
			return true;
		}


		/**
		* Order statuses array
		* @returns list of values in format for form group helper
		*/
		public function getOrderStatuses()
		{
			$orderstates = OrderState::getOrderStates($this->context->language->id);
			$order_states = [];
			foreach ($orderstates as $orderstate) {
				$order_states[$orderstate['id_order_state']] = [
					'id_group' => $orderstate['id_order_state'],
					'name' => $orderstate['name'],
				];
			}
			sort($order_states);
			return $order_states;
		}
		
		
		// helps to generate setting form
		public function displayForm()
		{
			// Get default language
			$default_lang = $this->lang;
				
			// Init Fields form array
			$fields_form[0]['form'] = array(
					'legend' => array(
						'title' => $this->l('Settings'),
					),
					'input' => array(
						array(
							'type' => 'switch',
							'label' => $this->l('Original price'),
							'name' => 'productspurchased_prices_orig',
							'desc' => $this->l('Display product price from customer order.'),
							'hint' => $this->l('Switch to NO if you want to display actual today prices.'),
							'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
							'tab'   => 'settings',
						),
						array(
							'type'   => 'group',
							'label' => $this->l('Order Status Used'),
							'desc' => $this->l('Select orders states of customer orders which will be included to processing of product lists.'),
							'hint' => $this->l('Products in lists extracted just from his orders will be displayed for logged-in customer only.'),
							'name'   => 'productspurchased_order_states',
							'values' => $this->getOrderStatuses(),
							'required' => true,
						),
					),
					'submit' => array(
						'title' => $this->l('Save'),
						'class' => 'btn btn-default pull-right'
					)
			);
				
			$helper = new HelperForm();
				
			// Module, token and currentIndex
			$helper->module = $this;
			$helper->name_controller = $this->name;
			$helper->token = Tools::getAdminTokenLite('AdminModules');
			$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
				
			// Language
			$helper->default_form_language = $default_lang;
			$helper->allow_employee_form_lang = $default_lang;
				
			// Title and toolbar
			$helper->title = $this->displayName;
			$helper->show_toolbar = true;        // false -> remove toolbar
			$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
			$helper->submit_action = 'submit'.$this->name;
			$helper->toolbar_btn = array(
				'save' =>
				array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
				),
				'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
				)
			);
				
			// Load current value
				$helper->fields_value['productspurchased_prices_orig'] = Configuration::get('PRODUCTSPURCHASED_PRICES_ORIG');

				$group_statuses = $this->getOrderStatuses();
				$groupbox_values = explode(",", Configuration::get('PRODUCTSPURCHASED_ORDER_STATES'));
				foreach ($group_statuses as $group_status) {
					$helper->fields_value['groupBox_' . $group_status['id_group']] = (in_array($group_status['id_group'], $groupbox_values)) ? 1 : 0;
				}
				
			return $helper->generateForm($fields_form);
		}
			
			
		// generates content for module settings in Administration of module page
		public function getContent()
		{
			$output = null;
				
			if (Tools::isSubmit('submit'.$this->name))
			{
					$productspurchased_prices_orig = strval(Tools::getValue('productspurchased_prices_orig'));
					Configuration::updateValue('PRODUCTSPURCHASED_PRICES_ORIG', $productspurchased_prices_orig);

					$productspurchased_order_state_vals = 0;
					$productspurchased_order_states = 0;
					if ($productspurchased_order_state_vals = Tools::getValue('groupBox')) {
						$productspurchased_order_states = implode( ',', array_values( $productspurchased_order_state_vals ) );
					} else {
						$output .= $this->displayError($this->l('No order state is selected. Is it OK?'));
					}
					Configuration::updateValue('PRODUCTSPURCHASED_ORDER_STATES', $productspurchased_order_states);

					$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
			return $output.$this->displayForm();
		}
		
		
		public function hookCustomerAccount($params)
		{
			$page = 'module-' . $this->name . '-products';
			$meta = Meta::getMetaByPage($page, $this->lang);
			
			$this->smarty->assign(array(
				'productspurchased_page' => $meta['url_rewrite'],
			));
			return $this->display(__FILE__, 'my-account.tpl');

		}
		
	}	
