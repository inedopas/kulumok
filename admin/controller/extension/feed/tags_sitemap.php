<?php 
class ControllerExtensionFeedTagsSitemap extends Controller {

    private $error = array();

    public function index() {

	$this->load->language('extension/feed/tags_sitemap');
	$this->load->model('catalog/tags');
	$this->load->model('setting/setting');

	if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
	    $this->load->model('setting/setting');
	    $this->model_setting_setting->editSetting('feed_tags_sitemap', $this->request->post);
	    $this->session->data['success'] = $this->language->get('text_success');
	    $this->response->redirect($this->url->link('extension/feed/tags_sitemap', 'user_token=' . $this->session->data['user_token'], true));
	}

	if (isset($this->error['warning'])) {
		$data['error_warning'] = $this->error['warning'];
	} else {
		$data['error_warning'] = '';
	}

	$this->document->setTitle($this->language->get('page_title'));

	$data['heading_title'] = $this->language->get('heading_title');
	$data['button_save'] = $this->language->get('button_save');
	$data['button_cancel'] = $this->language->get('button_cancel');
	$data['text_enabled'] = $this->language->get('text_enabled');
	$data['text_disabled'] = $this->language->get('text_disabled');

	$data['entry_status'] = $this->language->get('entry_status');
	$data['entry_data_feed'] = $this->language->get('entry_data_feed');
	$data['entry_limit'] = $this->language->get('entry_limit');
	
	$data['action'] = $this->url->link('extension/feed/tags_sitemap', 'user_token=' . $this->session->data['user_token'], true);
	$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'].'&type=feed', true);

	if (isset($this->request->post['feed_tags_sitemap_status'])) {
	    $data['feed_tags_sitemap_status'] = $this->request->post['feed_tags_sitemap_status'];
	} else {
	    $data['feed_tags_sitemap_status'] = $this->config->get('feed_tags_sitemap_status');
	}

	if (isset($this->request->post['feed_tags_sitemap_limit'])) {
	    $data['feed_tags_sitemap_limit'] = $this->request->post['feed_tags_sitemap_limit'];
	} elseif ($this->config->get('feed_tags_sitemap_limit')) {
	    $data['feed_tags_sitemap_limit'] = $this->config->get('feed_tags_sitemap_limit');
	} else {
		$data['feed_tags_sitemap_limit'] = 500;
	}

	$total =  $this->model_catalog_tags->getTotalTags();
	$limit = $data['feed_tags_sitemap_limit'];
	$parts = ceil($total / $limit) - 1;

	$data['data_feed'] = array();
	$data['data_feed'][0] = HTTPS_CATALOG . 'index.php?route=extension/feed/tags_sitemap&part=0';
	for ($i = 1; $i <= $parts; $i++) {
	    $data['data_feed'][$i] = HTTPS_CATALOG . 'index.php?route=extension/feed/tags_sitemap&part='.$i;
	}


	if (isset($this->error['limit'])) {
	    $data['error_limit'] = $this->error['limit'];
	} else {
	    $data['error_limit'] = '';
	}

	$data['breadcrumbs'] = array();

	$data['breadcrumbs'][] = array(
		'text'      => $this->language->get('text_home'),
		'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		'separator' => false
	);

	$data['breadcrumbs'][] = array(
		'text'      => $this->language->get('text_feed'),
		'href'      => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'].'&type=feed', true),
		'separator' => ' :: '
	);

	$data['breadcrumbs'][] = array(
		'text'      => $this->language->get('page_title'),
		'href'      => $this->url->link('extension/feed/tags_sitemap', 'user_token=' . $this->session->data['user_token'], true),
		'separator' => ' :: '
	);

	$data['header'] = $this->load->controller('common/header');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['footer'] = $this->load->controller('common/footer');
	$this->response->setOutput($this->load->view('extension/feed/tags_sitemap', $data));
    }

    private function validate() {
	
	if ((!$this->request->post['feed_tags_sitemap_limit']) || (!is_numeric($this->request->post['feed_tags_sitemap_limit']))) {
	    $this->error['limit'] = $this->language->get('error_integer');
	}

	if (!$this->error) {
	    return TRUE;
	} else {
	    return FALSE;
	}

	return TRUE;
    }

}