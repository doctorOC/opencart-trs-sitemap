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

class ModelExtensionFeedTrsSitemap extends Model {

  // Settings
  public function editSetting($code, $key, $value, $store_id = 0) {

    $query = $this->db->query("SELECT NULL FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int) $store_id . "'
                                                                           AND   `key`      = '" . $this->db->escape($key) . "'
                                                                           AND   `code`     = '" . $this->db->escape($code) . "'

                                                                           LIMIT 1");

    if ($query->num_rows) {

      $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET   `value`    = '" . $this->db->escape($value) . "'
                                                          WHERE `store_id` = '" . (int) $store_id . "'
                                                            AND `code`     = '" . $this->db->escape($code) . "'
                                                            AND `key`      = '" . $this->db->escape($key) . "'

                                                          LIMIT 1");

    } else {

      $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int) $store_id . "',
                                                                   `code`     = '" . $this->db->escape($code) . "',
                                                                   `key`      = '" . $this->db->escape($key) . "',
                                                                   `value`    = '" . $this->db->escape($value) . "'");
    }
  }

  // Manufacturers
  public function getTotalManufacturers() {

    $query = $this->db->query("SELECT COUNT(DISTINCT `manufacturer_id`) AS `total` FROM `" . DB_PREFIX . "manufacturer`");

    return $query->row['total'];
  }

  // Categories
  public function getTotalCategories() {

    $query = $this->db->query("SELECT COUNT(DISTINCT `c`.`category_id`) AS `total` FROM `" . DB_PREFIX . "category` AS `c`
                                                                          LEFT JOIN `" . DB_PREFIX . "category_to_store` AS `c2s` ON (`c`.`category_id` = `c2s`.`category_id`)

                                                                          WHERE `c2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                                                            AND `c`.`status`       = '1'");

    return $query->row['total'];
  }

  // Informations
  public function getTotalInformations() {

    $query = $this->db->query("SELECT COUNT(DISTINCT `i`.`information_id`) AS `total`

                                        FROM `" . DB_PREFIX . "information` AS `i`
                                        LEFT JOIN `" . DB_PREFIX . "information_to_store` `i2s` ON (`i`.`information_id` = `i2s`.`information_id`)

                                        AND   `i2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                        AND   `i`.`status`       = '1'");

    return $query->row['total'];
  }

  // Products

    public function getTotalProducts($data = array()) {

      $query = $this->db->query("SELECT COUNT(DISTINCT `p`.`product_id`) AS `total` FROM `" . DB_PREFIX . "product` AS `p`
                                                                                    LEFT JOIN `" . DB_PREFIX . "product_to_store` AS `p2s` ON (`p`.`product_id` = `p2s`.`product_id`)

                                                                                    WHERE `p`.`status`     = '1'
                                                                                    AND   `p`.`date_available` <= NOW()
                                                                                    AND   `p2s`.`store_id` = '" . (int) $this->config->get('config_store_id') . "'");

      return $query->row['total'];
    }
}
