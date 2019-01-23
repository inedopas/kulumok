<?php
class ControllerCatalogTags extends Controller {
	private $error = array();

	public function index() {

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tag` (
			`tag_id` INT(11) NOT NULL AUTO_INCREMENT,
			`status` INT(1) NOT NULL DEFAULT '1',
			`category_id` INT(11) NOT NULL,
			PRIMARY KEY (`tag_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=MyISAM;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tag_description` (
			`tag_id` INT(11) NOT NULL DEFAULT '0',
			`language_id` INT(11) NOT NULL DEFAULT '0',
			`name` VARCHAR(255) NOT NULL,
			`name_short` VARCHAR(255) NOT NULL,
			`description_top` TEXT NOT NULL,
			`description_bottom` TEXT NOT NULL,
			`meta_description` TEXT NOT NULL,
			`meta_keyword` TEXT NOT NULL,
			`meta_title` TEXT NOT NULL,
			`h1` TEXT NOT NULL,
			PRIMARY KEY (`tag_id`, `language_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=MyISAM;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_tag` (
			`product_id` INT(11) NOT NULL,
			`tag_id` INT(11) NOT NULL,
			PRIMARY KEY (`product_id`, `tag_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tag_to_store` (
			`tag_id` INT(11) NOT NULL,
			`store_id` INT(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`tag_id`, `store_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=MyISAM;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tag_to_category` (
			`tag_id` INT(11) NOT NULL,
			`category_id` INT(11) NOT NULL,
			PRIMARY KEY (`tag_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB;");

		if ($this->config->get('newtags_etopd')!=0 && $this->config->get('newtags_etopd')!=1) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'newtags';");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_etopd', `value` = '1', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_ebottomd', `value` = '1', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_only', `value` = '0', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_ajax', `value` = '0', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_category', `value` = '1', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_count', `value` = '0', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_related', `value` = '1', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_delimiter', `value` = ';', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_name', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_h1', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_desc1', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_desc2', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_meta_title', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_template_meta_desc', `value` = '[tag]', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_translit', `value` = '1', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_update', `value` = '0', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_autoproducts', `value` = '0', `serialized` = 0;");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'newtags', `key` = 'newtags_exclude', `value` = '', `serialized` = 0;");
		}

		$this->load->language('catalog/tags');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('catalog/tags');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/tags');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('catalog/tags');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_tags->addTag($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/tags');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('catalog/tags');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_tags->editTag($this->request->get['tag_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/tags');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('catalog/tags');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $tag_id) {
				$this->model_catalog_tags->deleteTags($tag_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}


	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/tags/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/tags/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['settings'] = $this->url->link('catalog/tags/settings', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['group'] = $this->url->link('catalog/tags/group', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['tags'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_status'   => $filter_status,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$tags_total = $this->model_catalog_tags->getTotalTags($filter_data);

		$results = $this->model_catalog_tags->getTags($filter_data);

		foreach ($results as $result) {
			$data['tags'][] = array(
				'tag_id' => $result['tag_id'],
				'name'        => $result['name'],
				'count'	=> $result['count'],
				'edit'        => $this->url->link('catalog/tags/edit', 'user_token=' . $this->session->data['user_token'] . '&tag_id=' . $result['tag_id'] . $url, true),
				'delete'      => $this->url->link('catalog/tags/delete', 'user_token=' . $this->session->data['user_token'] . '&tag_id=' . $result['tag_id'] . $url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_count'] = $this->language->get('column_count');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_settings'] = $this->language->get('button_settings');
		$data['button_group'] = $this->language->get('button_group');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $tags_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($tags_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($tags_total - $this->config->get('config_limit_admin'))) ? $tags_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $tags_total, ceil($tags_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/tags_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['tag_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_names'] = $this->language->get('entry_names');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_meta_title'] = $this->language->get('entry_meta_title');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_keyword'] = $this->language->get('entry_keyword');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_description_top'] = $this->language->get('entry_description_top');
		$data['entry_description_bottom'] = $this->language->get('entry_description_bottom');
		$data['entry_h1'] = $this->language->get('entry_h1');
		$data['entry_category'] = $this->language->get('entry_category');

		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['help_description_top'] = $this->language->get('help_description_top');
		$data['help_description_bottom'] = $this->language->get('help_description_bottom');
		$data['help_h1'] = $this->language->get('help_h1');
		$data['help_category'] = $this->language->get('help_category');
		$data['help_names'] = $this->language->get('help_names');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['tag_id'])) {
			$data['action'] = $this->url->link('catalog/tags/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/tags/edit', 'user_token=' . $this->session->data['user_token'] . '&tag_id=' . $this->request->get['tag_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['tag_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$tag_info = $this->model_catalog_tags->getTag($this->request->get['tag_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['tag_description'])) {
			$data['tag_description'] = $this->request->post['tag_description'];
		} elseif (isset($this->request->get['tag_id'])) {
			$data['tag_description'] = $this->model_catalog_tags->getTagDescriptions($this->request->get['tag_id']);
		} else {
			$data['tag_description'] = array();
		}

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['tag_store'])) {
			$data['tag_store'] = $this->request->post['tag_store'];
		} elseif (isset($this->request->get['tag_id'])) {
			$data['tag_store'] = $this->model_catalog_tags->getTagStores($this->request->get['tag_id']);
		} else {
			$data['tag_store'] = array(0);
		}

		if (isset($this->request->post['tags_seo_url'])) {
			$data['tags_seo_url'] = $this->request->post['tags_seo_url'];
		} elseif (isset($this->request->get['tag_id'])) {
			$data['tags_seo_url'] = $this->model_catalog_tags->getTagsSeoUrls($this->request->get['tag_id']);
		} else {
			$data['tags_seo_url'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($tag_info)) {
			$data['status'] = $tag_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (!empty($tag_info)) {
			$data['category_id'] = $tag_info['category_id'];
		} else {
			$data['category_id'] = 0;
		}

		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/tags_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/tags')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post['tag_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}


		if ($this->request->post['tags_seo_url']) {
				$this->load->model('design/seo_url');
				
				foreach ($this->request->post['tags_seo_url'] as $store_id => $language) {
					foreach ($language as $language_id => $keyword) {
						if (!empty($keyword)) {
							if (count(array_keys($language, $keyword)) > 1) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
							}						
							
							$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
							
							foreach ($seo_urls as $seo_url) {
								if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['tag_id']) || (($seo_url['query'] != 'tag_id=' . $this->request->get['tag_id'])))) {
									$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
									
									break;
								}
							}
						}
					}
				}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}


		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/tags')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/tags');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_tags->getTags($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'tag_id' => $result['tag_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function settings(){
		$this->load->language('catalog/tags');

		$data['heading_title'] = $this->language->get('heading_title')." - ".$this->language->get('button_settings');
		$this->document->setTitle($this->language->get('button_settings'));
		$data['action'] = $this->url->link('catalog/tags/save_settins', 'user_token=' . $this->session->data['user_token'], true);

		$data['text_form'] = $this->language->get('button_settings');

		$data['entry_etopd'] = $this->language->get('entry_etopd');
		$data['entry_ebottomd'] = $this->language->get('entry_ebottomd');
		$data['entry_only'] = $this->language->get('entry_only');
		$data['entry_scategory'] = $this->language->get('entry_scategory');
		$data['entry_count'] = $this->language->get('entry_count');
		$data['entry_related'] = $this->language->get('entry_related');
		$data['entry_delimiter'] = $this->language->get('entry_delimiter');
		$data['entry_template_name'] = $this->language->get('entry_template_name');
		$data['entry_template_h1'] = $this->language->get('entry_template_h1');
		$data['entry_template_desc1'] = $this->language->get('entry_template_desc1');
		$data['entry_template_desc2'] = $this->language->get('entry_template_desc2');
		$data['entry_template_meta_title'] = $this->language->get('entry_template_meta_title');
		$data['entry_template_meta_desc'] = $this->language->get('entry_template_meta_desc');
		$data['entry_translit'] = $this->language->get('entry_translit');
		$data['entry_update'] = $this->language->get('entry_update');
		$data['entry_autoproducts'] = $this->language->get('entry_autoproducts');
		$data['help_autoproducts'] = $this->language->get('help_autoproducts');
		$data['entry_autoproducts1'] = $this->language->get('entry_autoproducts1');
		$data['entry_autoproducts2'] = $this->language->get('entry_autoproducts2');
		$data['entry_exclude'] = $this->language->get('entry_exclude');
		$data['tab_import'] = $this->language->get('tab_import');

		$data['help_etopd'] = $this->language->get('help_etopd');
		$data['help_ebottomd'] = $this->language->get('help_ebottomd');
		$data['help_only'] = $this->language->get('help_only');
		$data['help_scategory'] = $this->language->get('help_scategory');
		$data['help_count'] = $this->language->get('help_count');
		$data['help_related'] = $this->language->get('help_related');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['tab_general'] = $this->language->get('tab_general');

		$data['setting_etopd'] = $this->config->get('newtags_etopd');
		$data['setting_ebottomd'] = $this->config->get('newtags_ebottomd');
		$data['setting_only'] = $this->config->get('newtags_only');
		$data['setting_scategory'] = $this->config->get('newtags_category');
		$data['setting_count'] = $this->config->get('newtags_count');
		$data['setting_related'] = $this->config->get('newtags_related');
		$data['setting_delimiter'] = $this->config->get('newtags_delimiter');
		$data['setting_template_name'] = $this->config->get('newtags_template_name');
		$data['setting_template_h1'] = $this->config->get('newtags_template_h1');
		$data['setting_template_desc1'] = $this->config->get('newtags_template_desc1');
		$data['setting_template_desc2'] = $this->config->get('newtags_template_desc2');
		$data['setting_template_meta_title'] = $this->config->get('newtags_template_meta_title');
		$data['setting_template_meta_desc'] = $this->config->get('newtags_template_meta_desc');
		$data['setting_translit'] = $this->config->get('newtags_translit');
		$data['setting_update'] = $this->config->get('newtags_update');
		$data['setting_autoproducts'] = $this->config->get('newtags_autoproducts');
		$data['setting_exclude'] = $this->config->get('newtags_exclude');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags/settings', 'user_token=' . $this->session->data['user_token'] , true)
		);

		$data['cancel'] = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/tags_settings', $data));
	}

	public function save_settins() {
		$this->load->language('catalog/tags');

		$data['heading_title'] = $this->language->get('heading_title')." - ".$this->language->get('button_settings');

		$this->load->model('catalog/tags');
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('newtags', array(
			'newtags_etopd'=>$this->request->post['setting_etopd'],
			'newtags_ebottomd'=>$this->request->post['setting_ebottomd'],
			'newtags_only'=>$this->request->post['setting_only'],
			'newtags_category'=>$this->request->post['setting_scategory'],
			'newtags_count'=>$this->request->post['setting_count'],
			'newtags_related'=>$this->request->post['setting_related'],
			'newtags_delimiter'=>$this->request->post['setting_delimiter'],
			'newtags_template_name'=>$this->request->post['setting_template_name'],
			'newtags_template_h1'=>$this->request->post['setting_template_h1'],
			'newtags_template_desc1'=>$this->request->post['setting_template_desc1'],
			'newtags_template_desc2'=>$this->request->post['setting_template_desc2'],
			'newtags_template_meta_title'=>$this->request->post['setting_template_meta_title'],
			'newtags_template_meta_desc'=>$this->request->post['setting_template_meta_desc'],
			'newtags_translit'=>$this->request->post['setting_translit'],
			'newtags_update'=>$this->request->post['setting_update'],
			'newtags_autoproducts'=>$this->request->post['setting_autoproducts'],
			'newtags_exclude'=>$this->request->post['setting_exclude']
			));

			$this->model_catalog_tags->setSettings($this->request->post);

			$layout = array(
	            'name' => $this->language->get('page_title'),
	            'layout_route' => array (
	                array(
	                    'route' => 'product/tags',
	                    'store_id' => '0',
	                )
	            )
	        );
	        $this->load->model('design/layout');
	        $sql = "SELECT layout_id FROM ".DB_PREFIX."layout_route WHERE route = 'product/tags' AND store_id = 0 ";
	        $res = $this->db->query($sql);
	        if (!$res->num_rows) {
	        	$this->model_design_layout->addLayout($layout);
	        }
	        
			$this->session->data['success'] = $this->language->get('text_success_settings');
		}

		$this->response->redirect($this->url->link('catalog/tags/settings', 'user_token=' . $this->session->data['user_token'] , true));
	}

	public function group(){
		$this->load->language('catalog/tags');
		$this->load->model('catalog/tags');
		$data['heading_title'] = $this->language->get('heading_title')." - ".$this->language->get('button_group');

		$this->document->setTitle($this->language->get('button_group'));

		$data['action'] = $this->url->link('catalog/tags/group', 'user_token=' . $this->session->data['user_token'] , true);

		$data['button_run'] = $this->language->get('button_run');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['text_add_option'] = $this->language->get('text_add_option');
		$data['text_add_attribute'] = $this->language->get('text_add_attribute');
		$data['text_option_name'] = $this->language->get('text_option_name');
		$data['text_option_value'] = $this->language->get('text_option_value');
		$data['text_attribute_name'] = $this->language->get('text_attribute_name');
		$data['text_attribute_value'] = $this->language->get('text_attribute_value');
		$data['text_category'] = $this->language->get('text_category');
		$data['text_tag'] = $this->language->get('text_tag');
		$data['text_run'] = $this->language->get('text_run');

		$data['text_form'] = $this->language->get('button_group');

		if (isset($this->request->post['tag_id'])){
			if ($this->request->post['tag_id'] != ''){
				$count = $this->model_catalog_tags->updateTags($this->request->post);
				$this->session->data['success'] = $count." ".$this->language->get('text_run_success');
			}
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] , true)
		);

		$data['cancel'] = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] , true);

		$this->load->model('catalog/option');
		$data['options'] = $this->model_catalog_option->getOptions();

		$this->load->model('catalog/attribute');
		$data['attributes'] = $this->model_catalog_attribute->getAttributes();

		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories(array('sort' => 'name'));

		$data['tags'] = $this->model_catalog_tags->getTags();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/tags_group', $data));
	}

	public function import() {
		$this->load->language('catalog/tags');

		$data['heading_title'] = $this->language->get('heading_title')." - ".$this->language->get('button_import');

		$this->document->setTitle($this->language->get('button_import'));

		$data['action'] = $this->url->link('catalog/tags/import', 'user_token=' . $this->session->data['user_token'] , true);

		$this->load->model('catalog/tags');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$file = DIR_DOWNLOAD . 'import.txt';
	  
			if ((isset( $this->request->files['import_file'] )) && (is_uploaded_file($this->request->files['import_file']['tmp_name']))) {
				move_uploaded_file($this->request->files['import_file']['tmp_name'], $file);
			} elseif (!empty($this->request->post['import_data'])) {
				$file_content = $this->request->post['import_data'];
				file_put_contents($file, $file_content);
	 		 } else {
				$this->error['warning'] = $this->language->get('error_empty');
	  		}

	  		if (!isset($this->error['warning'])){
	  			$this->importFile($file);
				$this->session->data['success'] = $this->language->get('text_success_import');
			}
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('button_import'),
			'href' => $this->url->link('catalog/tags_import', 'user_token=' . $this->session->data['user_token'] , true)
		);

		$data['text_form'] = $this->language->get('button_import');
		$data['button_import'] = $this->language->get('button_import');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['entry_delimiter'] = $this->language->get('entry_delimiter');
		$data['entry_file'] = $this->language->get('entry_file');
		$data['help_file'] = $this->language->get('help_file');
		$data['entry_data'] = $this->language->get('entry_data');
		$data['help_data'] = $this->language->get('help_data');

		$data['delimiter'] = $this->config->get('newtags_delimiter');

		$data['cancel'] = $this->url->link('catalog/tags', 'user_token=' . $this->session->data['user_token'] , true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('catalog/tags_import', $data));
	}

	private function importFile($file) {
		set_time_limit(0);

    	$import = (string)file_get_contents($file);

    	if ($import) {

    		$bom = pack('H*','EFBBBF');
    		$import = preg_replace("/^$bom/", '', $import);
    		$specials = array("'", "`",);
        	$import = str_replace($specials, '', $import);
    		$import = iconv(mb_detect_encoding($import, mb_detect_order(), true), "UTF-8", $import);

    		$delimiter = $this->config->get('newtags_delimiter');
			$template_name = $this->config->get('newtags_template_name');
			$template_h1 = $this->config->get('newtags_template_h1');
			$template_desc1 = $this->config->get('newtags_template_desc1');
			$template_desc2 = $this->config->get('newtags_template_desc2');
			$template_meta_title = $this->config->get('newtags_template_meta_title');
			$template_meta_desc = $this->config->get('newtags_template_meta_desc');
			$translit = $this->config->get('newtags_translit');
			$update = $this->config->get('newtags_update');
			$autoproducts = $this->config->get('newtags_autoproducts'); 
	    	
	    	$this->load->model('localisation/language');
	    	$languages = $this->model_localisation_language->getLanguages();

	    	$this->load->model('setting/store');
			$stores = $this->model_setting_store->getStores();

			$import = trim($import, $delimiter);
	    	if ($delimiter == '\r\n'||$delimiter == '\n\r'||$delimiter == '\r'||$delimiter == '\n') {
				$tags = explode(PHP_EOL, $import);
			} else {
	    		$tags = explode($delimiter, $import);
	    	}

	    	foreach ($tags as $tag) {

	    		$data = array();
	    		$tag = trim($tag);
	    		$shortcode = array('[tag]' => $tag);
	    		$tag_name = $template_name ? strtr($template_name, $shortcode) : $tag;
	    		$keyword = $translit ? $this->translitUrl($tag) : $this->noTranslitUrl($tag);

	    		foreach ($languages as $language) {
		    		$data['tag_description'][$language['language_id']]['name'] = $tag_name;
					$data['tag_description'][$language['language_id']]['name_short'] = $tag;
					$data['tag_description'][$language['language_id']]['description_top'] = strtr($template_desc1, $shortcode);
					$data['tag_description'][$language['language_id']]['description_bottom'] = strtr($template_desc2, $shortcode);
					$data['tag_description'][$language['language_id']]['h1'] = strtr($template_h1, $shortcode);
					$data['tag_description'][$language['language_id']]['meta_title'] = $template_meta_title ? strtr($template_meta_title, $shortcode) : $tag;
					$data['tag_description'][$language['language_id']]['meta_description'] = strtr($template_meta_desc, $shortcode);
					$data['tag_description'][$language['language_id']]['meta_keyword'] = strtr($template_name, $shortcode);
					$data['tags_seo_url'][0][$language['language_id']] = $keyword.$language['language_id'];
				}

				$data['tag_store'][0] = 0;
				if (!empty($stores)){
					foreach ($stores as $store) {
						$data['tag_store'][] = $store['store_id'];
						foreach ($languages as $language) {
							$data['tags_seo_url'][$store['store_id']][$language['language_id']] = $keyword.$store['store_id'].$language['language_id'];
						}
					}
				}

				$data['status'] = 1;
				$data['category_id'] = '';

				$exist = $this->model_catalog_tags->getTagByName($tag_name);

				if (empty($exist)&&$tag_name!='') {
					$this->model_catalog_tags->addTag($data);
				} elseif(isset($exist['tag_id']) && $update) {
	    			$this->model_catalog_tags->editTag($exist['tag_id'], $data);
	    		}

	    		if ($autoproducts) {
	    			if (isset($exist['tag_id'])) {
	    				$this->model_catalog_tags->autoProducts($exist['tag_id'], $tag);
	    			} else {
	    				$new_tag = $this->model_catalog_tags->getTagByName($tag_name);
	    				if (!empty($new_tag)) 
	    				$this->model_catalog_tags->autoProducts($new_tag['tag_id'], $tag);
	    			}
	    		}
	    	}
	    	
	    }
	}

	private function noTranslitUrl($string) {
        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", ".", "№");
        $string = html_entity_decode($string);
        $string = mb_strtolower($string);   
        $string = str_replace($special_chars, '', $string);
        $spaces = array('&nnbsp;','&nbsp;',' ',' ');
        $string = str_replace($spaces, '-', $string);
        $string = str_replace('---', '-', $string);
        $string = str_replace('--', '-', $string);
        $string = trim($string,'-');
        return $string;
    }

    private function translitUrl($string) {
    	$tr = array(
	        "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d",
	        "Е"=>"e","Ё"=>"yo","Ж"=>"j","З"=>"z","И"=>"i",
	        "Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
	        "О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
	        "У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"c","Ч"=>"ch",
	        "Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"y","Ь"=>"",
	        "Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
	        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"yo","ж"=>"j",
	        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
	        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
	        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
	        "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
	        "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
	        "і"=> "i", "І"=> "i", "є"=> "ye", "Є"=> "ye",
	        "ґ"=> "g", "Ґ"=> "g", "ї"=> "yi", "Ї"=> "yi"
	    );
	    $string = strtr($string,$tr);

        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", ".", "№");
        $string = html_entity_decode($string);
        $string = mb_strtolower($string);   
        $string = str_replace($special_chars, '', $string);
        $spaces = array('&nnbsp;','&nbsp;',' ',' ');
        $string = str_replace($spaces, '-', $string);
        $string = str_replace('---', '-', $string);
        $string = str_replace('--', '-', $string);
        $string = trim($string,'-');
        return $string;
    }
}