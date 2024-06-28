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

  private $_products_database_offset;
  private $_products_sitemap_offset;
  private $_products_sitemap_index;

  private $_categories_database_offset;
  private $_categories_sitemap_offset;
  private $_categories_sitemap_index;

  private $_manufacturers_database_offset;
  private $_manufacturers_sitemap_offset;
  private $_manufacturers_sitemap_index;

  private $_informations_database_offset;
  private $_informations_sitemap_offset;
  private $_informations_sitemap_index;

  public function __construct($registry) {

    parent::__construct($registry);

    // Load dependencies
    $this->load->model('catalog/product');
    $this->load->model('tool/image');
    $this->load->model('catalog/information');
    $this->load->model('extension/feed/trs_sitemap');
  }

  public function index() {

    if (!$this->config->get('feed_trs_sitemap_status') ||
        !(isset($this->request->get['key']) && $this->request->get['key'] == $this->config->get('feed_trs_sitemap_key'))) {
      return false;
    }

    // If generation completed
    if ($this->config->get('feed_trs_sitemap_products_sitemap_completed') &&
        $this->config->get('feed_trs_sitemap_categories_sitemap_completed') &&
        $this->config->get('feed_trs_sitemap_manufacturers_sitemap_completed') &&
        $this->config->get('feed_trs_sitemap_informations_sitemap_completed')) {
        $this->_updateSitemapRoot();
    }

    // Calculate duration
    $duration_hours   = $this->config->get('feed_trs_sitemap_duration');
    $duration_minutes = ceil($duration_hours * 60);

    // Calculate changefreq
    if ($duration_hours >= 8064) {
      $changefreq = 'yearly';
    } else if ($duration_hours >= 672) {
      $changefreq = 'monthly';
    } else if ($duration_hours >= 168) {
      $changefreq = 'weekly';
    } else if ($duration_hours >= 24) {
      $changefreq = 'daily';
    } else if ($duration_hours >= 1) {
      $changefreq = 'hourly';
    } else {
      $changefreq = 'always';
    }

    // Process products
    $products_count = $this->model_catalog_product->getTotalProducts();

    if ($products_count) {
      $this->_products_database_offset = $this->config->get('feed_trs_sitemap_products_database_offset') ? $this->config->get('feed_trs_sitemap_products_database_offset') : 0;
      $this->_products_sitemap_offset  = $this->config->get('feed_trs_sitemap_products_sitemap_offset')  ? $this->config->get('feed_trs_sitemap_products_sitemap_offset')  : 0;
      $this->_products_sitemap_index   = $this->config->get('feed_trs_sitemap_products_sitemap_index')   ? $this->config->get('feed_trs_sitemap_products_sitemap_index')   : 1;

      foreach ($this->model_catalog_product->getProducts(['start' => $this->_products_database_offset,
                                                          'limit' => ceil($products_count / $duration_minutes)
                                                        ]) as $product) {

          $this->_writeSitemapIndex('products', [
            'url' => [
              'loc'         => $this->url->link('product/product', 'product_id=' . $product['product_id'], true),
              'lastmod'     => date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])),
              'changefreq'  => $changefreq,
              'priority'    => '1.0',
              'image:image' => [
                  'image:loc' => $this->model_tool_image->resize($product['image'],
                                                                 $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'),
                                                                 $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
                  'image:caption' => trim(htmlspecialchars($product['name'], ENT_COMPAT)),
                  'image:title'   => trim(htmlspecialchars($product['name'], ENT_COMPAT))
                ],
              ],
            ],
            $products_count,
            $this->_products_database_offset,
            $this->_products_sitemap_offset,
            $this->_products_sitemap_index
          );
      }
    }

    // Process manufacturers
    $manufacturers_count = $this->model_extension_feed_trs_sitemap->getTotalManufacturers();

    if ($manufacturers_count) {
      $this->_manufacturers_database_offset = $this->config->get('feed_trs_sitemap_manufacturers_database_offset') ? $this->config->get('feed_trs_sitemap_manufacturers_database_offset') : 0;
      $this->_manufacturers_sitemap_offset  = $this->config->get('feed_trs_sitemap_manufacturers_sitemap_offset')  ? $this->config->get('feed_trs_sitemap_manufacturers_sitemap_offset')  : 0;
      $this->_manufacturers_sitemap_index   = $this->config->get('feed_trs_sitemap_manufacturers_sitemap_index')   ? $this->config->get('feed_trs_sitemap_manufacturers_sitemap_index')   : 1;

      foreach ($this->model_extension_feed_trs_sitemap->getManufacturers($this->_manufacturers_database_offset,
                                                                         ceil($manufacturers_count / $duration_minutes)) as $manufacturer) {

          $this->_writeSitemapIndex('manufacturers', [
            'url' => [
              'loc'         => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'], true),
              'changefreq'  => $changefreq,
              'priority'    => '0.7',
              ],
            ],
            $manufacturers_count,
            $this->_manufacturers_database_offset,
            $this->_manufacturers_sitemap_offset,
            $this->_manufacturers_sitemap_index
          );
      }
    }

    // Process information
    $informations_count = $this->model_extension_feed_trs_sitemap->getTotalInformations();

    if ($informations_count) {

      $this->_informations_database_offset = $this->config->get('feed_trs_sitemap_informations_database_offset') ? $this->config->get('feed_trs_sitemap_informations_database_offset') : 0;
      $this->_informations_sitemap_offset  = $this->config->get('feed_trs_sitemap_informations_sitemap_offset')  ? $this->config->get('feed_trs_sitemap_informations_sitemap_offset')  : 0;
      $this->_informations_sitemap_index   = $this->config->get('feed_trs_sitemap_informations_sitemap_index')   ? $this->config->get('feed_trs_sitemap_informations_sitemap_index')   : 1;

      foreach ($this->model_extension_feed_trs_sitemap->getInformations($this->_informations_database_offset,
                                                                        ceil($informations_count / $duration_minutes)) as $information) {

          $this->_writeSitemapIndex('informations', [
            'url' => [
              'loc'         => $this->url->link('product/information/info', 'information_id=' . $information['information_id'], true),
              'changefreq'  => $changefreq,
              'priority'    => '0.5',
              ],
            ],
            $informations_count,
            $this->_informations_database_offset,
            $this->_informations_sitemap_offset,
            $this->_informations_sitemap_index
          );
      }
    }

    // Process categories
    $categories_count = $this->model_extension_feed_trs_sitemap->getTotalCategories();

    if ($categories_count) {

      $this->_categories_database_offset = $this->config->get('feed_trs_sitemap_categories_database_offset') ? $this->config->get('feed_trs_sitemap_categories_database_offset') : 0;
      $this->_categories_sitemap_offset  = $this->config->get('feed_trs_sitemap_categories_sitemap_offset')  ? $this->config->get('feed_trs_sitemap_categories_sitemap_offset')  : 0;
      $this->_categories_sitemap_index   = $this->config->get('feed_trs_sitemap_categories_sitemap_index')   ? $this->config->get('feed_trs_sitemap_categories_sitemap_index')   : 1;

      foreach ($this->model_extension_feed_trs_sitemap->getCategories($this->_categories_database_offset,
                                                                      ceil($categories_count / $duration_minutes)) as $category) {

          $this->_writeSitemapIndex('categories', [
            'url' => [
              'loc'         => $this->url->link('product/category', 'path=' . $category['path'], true),
              'changefreq'  => $changefreq,
              'priority'    => '0.7',
              ],
            ],
            $categories_count,
            $this->_categories_database_offset,
            $this->_categories_sitemap_offset,
            $this->_categories_sitemap_index
          );
      }
    }
  }

  private function _updateSitemapRoot() {

    $lastmod       = date('Y-m-d\TH:i:sP');
    $tmp_directory = str_replace(sprintf('%scache', DIRECTORY_SEPARATOR), false, DIR_CACHE);
    $per_directory = str_replace(sprintf('%ssystem', DIRECTORY_SEPARATOR), false, DIR_SYSTEM);

    // Delete old sitemaps
    $handle = opendir($per_directory);
    while ($filename = readdir($handle)) {
      if (1 === preg_match('/^.*(sitemap\.[\w]+\.[\d]+.xml)$/ui', $filename, $matches)) {
        // Delete file
        unlink(sprintf('%s%s', $per_directory, $filename));
      }
    }
    closedir($handle);

    // Move completed files to the root directory
    $body   = '';
    $handle = opendir($tmp_directory);
    while ($filename = readdir($handle)) {
      if (1 === preg_match('/^.*(sitemap\.[\w]+\.[\d]+.xml)$/ui', $filename, $matches)) {

        // Update file
        $old_file = sprintf('%s%s', $tmp_directory, $filename);
        $new_file = sprintf('%s%s', $per_directory, $filename);
        rename($old_file, $new_file);

        // Collect sitemap body
        $data = [
          'sitemap' => [
            'loc'     => sprintf('%s%s', HTTPS_SERVER, $matches[1]),
            'lastmod' => $lastmod,
          ]
        ];

        foreach ($data as $key => $value) {
          $body .= $this->_parseSitemapBody($key, $value);
        }
      }
    }
    closedir($handle);

    // Update root sitemap
    if ($body) {
      $data  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
      $data .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
      $data .= $body;
      $data .= "</sitemapindex>";

      // Overwrite the file
      unlink(sprintf('%ssitemap.xml', $per_directory));
      $handle = fopen(sprintf('%ssitemap.xml', $per_directory), 'w');
      fwrite($handle, $data);
      fclose($handle);

      // Update completed, begin new update
      $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                           'feed_trs_sitemap_products_sitemap_completed',
                                                           0,
                                                           $this->config->get('store_id'));
      $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                           'feed_trs_sitemap_categories_sitemap_completed',
                                                           0,
                                                           $this->config->get('store_id'));
      $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                           'feed_trs_sitemap_manufacturers_sitemap_completed',
                                                           0,
                                                           $this->config->get('store_id'));
      $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                           'feed_trs_sitemap_informations_sitemap_completed',
                                                           0,
                                                           $this->config->get('store_id'));
    }
  }

  private function _parseSitemapBody($key, $value, $i = 1) {

    $body   = '';
    $prefix = str_repeat("\t", $i++);

    if (is_array($value)) {
      $body .= $prefix . "<{$key}>\n";
      foreach ($value as $child_key => $child_value) {
        $body .= $this->_parseSitemapBody($child_key, $child_value, $i);
      }
      $body .= $prefix . "</{$key}>\n";
    } else {
      $body .= $prefix . "<{$key}>{$value}</{$key}>\n";
    }

    return $body;
  }

  private function _addSitemapHeaders($filename) {

    // Prepare content
    $data  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $data .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\">\n";
    $data .= file_get_contents($filename);
    $data .= "</urlset>";

    // Overwrite the file
    $handle = fopen($filename, 'w');
    fwrite($handle, $data);
    fclose($handle);
  }

  private function _addBody($filename, $body) {

    // Add body
    $handle = fopen($filename, 'a');
    fwrite($handle, $body);
    fclose($handle);
  }

  private function _writeSitemapIndex($type, $data, $total, &$database_offset, &$sitemap_offset, &$sitemap_index) {

    // Skip sitemap writing if type was completed and pending for update
    if ($this->config->get(sprintf('feed_trs_sitemap_%s_sitemap_completed', $type))) {
      return false;
    }

    // Define filename
    $filename = sprintf('%ssitemap.%s.%s.xml', str_replace(sprintf('%scache', DIRECTORY_SEPARATOR), false, DIR_CACHE),
                                               $type,
                                               $sitemap_index);

    // Generate content
    $body = '';
    foreach ($data as $key => $value) {
      $body .= $this->_parseSitemapBody($key, $value);
    }

    // Add body
    $this->_addBody($filename, $body);

    // Update indexes
    $database_offset++;
    $sitemap_offset++;
    $completed = 0;

    // Sitemap completed
    if ($database_offset >= $total) {

      $database_offset = 0;
      $sitemap_offset  = 0;
      $sitemap_index   = 1;
      $completed       = 1;

      $this->_addSitemapHeaders($filename);
    }

    // Offset reached limits
    if ($sitemap_offset >= $this->config->get('feed_trs_sitemap_limit')) {

      $sitemap_offset = 0;
      $sitemap_index++;

      $this->_addSitemapHeaders($filename);
    }

    // Update DB
    $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                          sprintf('feed_trs_sitemap_%s_database_offset', $type),
                                                          $database_offset,
                                                          $this->config->get('store_id'));

    $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                          sprintf('feed_trs_sitemap_%s_sitemap_offset', $type),
                                                          $sitemap_offset,
                                                          $this->config->get('store_id'));

    $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                          sprintf('feed_trs_sitemap_%s_sitemap_index', $type),
                                                          $sitemap_index,
                                                          $this->config->get('store_id'));

    $this->model_extension_feed_trs_sitemap->editSetting('feed_trs_sitemap',
                                                          sprintf('feed_trs_sitemap_%s_sitemap_completed', $type),
                                                          $completed,
                                                          $this->config->get('store_id'));
  }
}
