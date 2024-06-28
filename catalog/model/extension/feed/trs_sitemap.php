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

    $query = $this->db->query("SELECT COUNT(`m`.`manufacturer_id`) AS `total` FROM `" . DB_PREFIX . "manufacturer` AS `m`
                                                                              JOIN `" . DB_PREFIX . "manufacturer_to_store` AS `m2s` ON (`m2s`.`manufacturer_id` = `m`.`manufacturer_id`)

                                                                              WHERE `m2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                                                                AND `m`.`noindex`      > '0'");

    return $query->row['total'];
  }

  public function getManufacturers($start, $limit) {

    $query = $this->db->query("SELECT `m`.`manufacturer_id` FROM `" . DB_PREFIX . "manufacturer` AS `m`
                                                            JOIN `" . DB_PREFIX . "manufacturer_to_store` AS `m2s` ON (`m2s`.`manufacturer_id` = `m`.`manufacturer_id`)

                                                            WHERE `m2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                                              AND `m`.`noindex`      > '0'

                                                            LIMIT {$start}, {$limit}");

    return $query->rows;
  }

  // Categories
  public function getCategories($start, $limit) {

    $query = $this->db->query("SELECT `c`.`category_id`,

                                      (SELECT GROUP_CONCAT(`path_id` ORDER BY `level` ASC SEPARATOR '_')
                                       FROM `" . DB_PREFIX . "category_path` AS `cp`
                                       WHERE `cp`.`category_id` = `c`.`category_id`) AS `path`

                                      FROM `" . DB_PREFIX . "category` AS `c`
                                      JOIN `" . DB_PREFIX . "category_to_store` AS `c2s` ON (`c`.`category_id` = `c2s`.`category_id`)

                                      WHERE `c2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                        AND `c`.`status`       = '1'
                                        AND `c`.`noindex`      > '0'

                                      LIMIT " . (int) $start . "," . (int) $limit);

    return $query->rows;
  }

  public function getTotalCategories() {

    $query = $this->db->query("SELECT COUNT(`c`.`category_id`) AS `total` FROM `" . DB_PREFIX . "category` AS `c`
                                                                          JOIN `" . DB_PREFIX . "category_to_store` AS `c2s` ON (`c`.`category_id` = `c2s`.`category_id`)

                                                                          WHERE `c2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                                                            AND `c`.`status`       = '1'
                                                                            AND `c`.`noindex`      > '0'");

    return $query->row['total'];
  }

  // Informations
  public function getInformations($start, $limit) {

    $query = $this->db->query("SELECT `i`.`information_id` FROM `" . DB_PREFIX . "information` AS `i`
                                                           JOIN `" . DB_PREFIX . "information_to_store` AS `i2s` ON (`i`.`information_id` = `i2s`.`information_id`)

                                                           AND   `i2s`.`store_id`   = '" . (int)$this->config->get('config_store_id') . "'
                                                           AND   `i`.`status`       = '1'
                                                           AND   `i`.`noindex`      > '0'

                                                           LIMIT " . (int) $start . "," . (int) $limit);

    return $query->rows;
  }

  public function getTotalInformations() {

    $query = $this->db->query("SELECT COUNT(`i`.`information_id`) AS `total`

                                        FROM `" . DB_PREFIX . "information` AS `i`
                                        JOIN `" . DB_PREFIX . "information_to_store` AS `i2s` ON (`i`.`information_id` = `i2s`.`information_id`)

                                        AND   `i2s`.`store_id`   = '" . (int) $this->config->get('config_store_id') . "'
                                        AND   `i`.`status`       = '1'
                                        AND   `i`.`noindex`      > '0'");

    return $query->row['total'];
  }
}
