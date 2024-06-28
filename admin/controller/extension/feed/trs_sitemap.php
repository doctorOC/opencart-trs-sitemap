<?php

/**
 * LICENSE CC BY-NC-SA 4.0
 *
 * This source file is subject to the Attribution-NonCommercial-ShareAlike 4.0 International License
 * It is also available through the world-wide-web at this URL:
 * https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode
 *
 * @package    TrS Sitemap
 * @copyright  Copyright (c) 2020 drOC
 * @license    https://creativecommons.org/licenses/by-nc-sa/4.0/
 */

class ControllerExtensionFeedTrsSitemap extends Controller {

  private $_error = [];

  public function install() {
    $this->load->model('extension/feed/trs_sitemap');
    $this->model_extension_feed_trs_sitemap->editSetting('trs_sitemap', 'trs_sitemap_key', substr(md5(rand() . microtime()), 0, 6));
  }

  public function index() {

    $data = $this->load->language('extension/feed/trs_sitemap');

    $this->document->setTitle($this->language->get('heading_title'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

      $this->load->model('extension/feed/trs_sitemap');

      foreach ($this->request->post as $key => $value) {
        $this->model_extension_feed_trs_sitemap->editSetting('trs_sitemap', $key, $value);
      }

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=feed', true));
    }

    if (isset($this->_error['warning'])) {
      $data['error_warning'] = $this->_error['warning'];
    } else {

      // Root directory writable test
      $directory = str_replace(sprintf('system%s', DIRECTORY_SEPARATOR), false, DIR_SYSTEM);

      if (!is_writable($directory) || !is_readable($directory)) {
        $data['error_warning'] = sprintf($this->language->get('error_directory'), $directory);
      } else {
        $data['error_warning'] = '';
      }

      // System directory writable test
      $directory = str_replace(sprintf('%scache', DIRECTORY_SEPARATOR), false, DIR_CACHE);
      if (!is_writable($directory) || !is_readable($directory)) {
        $data['error_warning'] = sprintf($this->language->get('error_directory'), DIR_SYSTEM);
      } else {
        $data['error_warning'] = '';
      }
    }

    $data['breadcrumbs'] = [];

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=feed', true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/feed/trs_sitemap', 'token=' . $this->session->data['token'], true)
    );

    $data['action'] = $this->url->link('extension/feed/trs_sitemap', 'token=' . $this->session->data['token'], true);
    $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=feed', true);

    if (isset($this->request->post['trs_sitemap_status'])) {
      $data['trs_sitemap_status'] = $this->request->post['trs_sitemap_status'];
    } else {
      $data['trs_sitemap_status'] = $this->config->get('trs_sitemap_status');
    }

    if (isset($this->request->post['trs_sitemap_limit'])) {
      $data['trs_sitemap_limit'] = $this->request->post['trs_sitemap_limit'];
    } else if ($this->config->get('trs_sitemap_limit')) {
      $data['trs_sitemap_limit'] = $this->config->get('trs_sitemap_limit');
    } else {
      $data['trs_sitemap_limit'] = 50000;
    }

    if (isset($this->request->post['trs_sitemap_duration'])) {
      $data['trs_sitemap_duration'] = $this->request->post['trs_sitemap_duration'];
    } else if ($this->config->get('trs_sitemap_duration')) {
      $data['trs_sitemap_duration'] = $this->config->get('trs_sitemap_duration');
    } else {
      $data['trs_sitemap_duration'] = 24;
    }

    $data['data_crontab_task'] = sprintf('* * * * * /usr/bin/curl --silent \'%sindex.php?route=extension/feed/trs_sitemap&key=%s\' &> /dev/null', HTTPS_CATALOG, $this->config->get('trs_sitemap_key'));
    $data['data_sitemap_url']  = sprintf('%ssitemap.xml', HTTPS_CATALOG);
    $data['data_duration']     = $this->estimateDuration();
    $data['data_token']        = $this->session->data['token'];

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/feed/trs_sitemap', $data));
  }

  public function estimateDuration() {

    $this->load->model('extension/feed/trs_sitemap');
    $this->load->language('extension/feed/trs_sitemap');

    if (isset($this->request->get['duration']) && $this->request->get['duration'] > 0) {
      $duration = (int) $this->request->get['duration'];
    } else if ($this->config->get('trs_sitemap_duration')) {
      $duration = $this->config->get('trs_sitemap_duration');
    } else {
      $duration = 24;
    }

    $result = sprintf($this->language->get('text_duration_counts'),
                      ceil($this->model_extension_feed_trs_sitemap->getTotalProducts() / ceil($duration * 60)),
                      ceil($this->model_extension_feed_trs_sitemap->getTotalCategories() / ceil($duration * 60)),
                      ceil($this->model_extension_feed_trs_sitemap->getTotalManufacturers() / ceil($duration * 60)),
                      ceil($this->model_extension_feed_trs_sitemap->getTotalInformations() / ceil($duration * 60)));

    if (isset($this->request->get['duration'])) {
      echo $result;
    } else {
      return $result;
    }
  }

  protected function validate() {

    if (!$this->user->hasPermission('modify', 'extension/feed/trs_sitemap')) {
      $this->_error['warning'] = $this->language->get('error_permission');
    }

    if ($this->request->post['trs_sitemap_limit'] > 50000) {
      $this->_error['warning'] = $this->language->get('error_limit_max_reached');
    }

    if ($this->request->post['trs_sitemap_limit'] < 100) {
      $this->_error['warning'] = $this->language->get('error_limit_min_reached');
    }

    if ($this->request->post['trs_sitemap_duration'] < 1) {
      $this->_error['warning'] = $this->language->get('error_duration_min_reached');
    }

    return !$this->_error;
  }
}
