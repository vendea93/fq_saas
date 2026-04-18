<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_035 extends App_module_migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        fq_saas_install();

        $CI = &get_instance();

        // Upgrade path: copy version option from legacy perfex_saas module key if present
        if (!get_option('fq_saas_latest_version') && get_option('perfex_saas_latest_version')) {
            update_option('fq_saas_latest_version', get_option('perfex_saas_latest_version'));
        }

        // Ensure extension tables exist on upgrades where install.php block was skipped
        if (!$CI->db->table_exists(fq_saas_extensions_table('activity_log'))) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS `" . fq_saas_extensions_table('activity_log') . "` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `created_at` datetime NOT NULL,
                    `staff_id` int DEFAULT NULL,
                    `event` varchar(191) NOT NULL,
                    `context` longtext,
                    PRIMARY KEY (`id`),
                    KEY `event` (`event`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        if (!$CI->db->table_exists(fq_saas_extensions_table('landing_pages'))) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS `" . fq_saas_extensions_table('landing_pages') . "` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `slug` varchar(191) NOT NULL,
                    `title` varchar(255) NOT NULL DEFAULT '',
                    `status` varchar(32) NOT NULL DEFAULT 'draft',
                    `body_html` longtext,
                    `body_json` longtext,
                    `revisions` longtext,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `slug` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        if (!$CI->db->table_exists(fq_saas_extensions_table('coupons'))) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS `" . fq_saas_extensions_table('coupons') . "` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `code` varchar(64) NOT NULL,
                    `type` varchar(16) NOT NULL DEFAULT 'percent',
                    `value` decimal(10,2) NOT NULL DEFAULT '0.00',
                    `currency` varchar(10) DEFAULT NULL,
                    `max_uses` int DEFAULT NULL,
                    `uses` int NOT NULL DEFAULT '0',
                    `expires_at` date DEFAULT NULL,
                    `package_ids` text,
                    `stripe_coupon_id` varchar(64) DEFAULT NULL,
                    `active` tinyint(1) NOT NULL DEFAULT '1',
                    `created_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `code` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        if (!$CI->db->table_exists(fq_saas_extensions_table('affiliates'))) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS `" . fq_saas_extensions_table('affiliates') . "` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `clientid` int NOT NULL,
                    `code` varchar(64) NOT NULL,
                    `commission_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
                    `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
                    `payout_status` varchar(32) NOT NULL DEFAULT 'none',
                    `status` varchar(32) NOT NULL DEFAULT 'active',
                    `created_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `code` (`code`),
                    KEY `clientid` (`clientid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        if (!$CI->db->table_exists(fq_saas_extensions_table('cms_pages'))) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS `" . fq_saas_extensions_table('cms_pages') . "` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `slug` varchar(191) NOT NULL,
                    `type` varchar(32) NOT NULL DEFAULT 'page',
                    `title` varchar(255) NOT NULL DEFAULT '',
                    `body_html` longtext,
                    `status` varchar(32) NOT NULL DEFAULT 'draft',
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `slug_type` (`slug`,`type`),
                    KEY `type` (`type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

        fq_saas_migrate_legacy_module_options();

        if (get_option('fq_saas_landing_builtin_slug') === false) {
            add_option('fq_saas_landing_builtin_slug', '');
        }

        $tCoupons = fq_saas_extensions_table('coupons');
        if ($CI->db->table_exists($tCoupons) && !$CI->db->field_exists('stripe_coupon_id', $tCoupons)) {
            $CI->db->query("ALTER TABLE `" . $tCoupons . "` ADD `stripe_coupon_id` varchar(64) DEFAULT NULL AFTER `package_ids`");
        }
        $tAff = fq_saas_extensions_table('affiliates');
        if ($CI->db->table_exists($tAff) && !$CI->db->field_exists('payout_status', $tAff)) {
            $CI->db->query("ALTER TABLE `" . $tAff . "` ADD `payout_status` varchar(32) NOT NULL DEFAULT 'none' AFTER `balance`");
        }
    }

    public function down()
    {
    }
}
