<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Define global constants
defined('FQ_SAAS_MODULE_NAME') or define('FQ_SAAS_MODULE_NAME', 'fq_saas');
/** Physical DB table/column prefix (legacy perfex_saas_* — unchanged for upgrade compatibility). */
defined('FQ_SAAS_DB_TABLE_PREFIX') or define('FQ_SAAS_DB_TABLE_PREFIX', 'perfex_saas');
defined('FQ_SAAS_MODULE_NAME_SHORT') or define('FQ_SAAS_MODULE_NAME_SHORT', 'ps');
defined('FQ_SAAS_MODULE_WHITELABEL_NAME') or define('FQ_SAAS_MODULE_WHITELABEL_NAME', 'fq_saas');

/**@deprecated 0.0.6 */
defined('FQ_SAAS_TENANT_COLUMN') or define('FQ_SAAS_TENANT_COLUMN', 'perfex_saas_tenant_id');

defined('FQ_SAAS_ROUTE_ID') or define('FQ_SAAS_ROUTE_ID', 'ps');
defined('FQ_SAAS_FILTER_TAG') or define('FQ_SAAS_FILTER_TAG', 'psaas');
defined('APP_DB_DRIVER') or define('APP_DB_DRIVER', 'mysqli');
defined('FQ_SAAS_MAX_SLUG_LENGTH') or define('FQ_SAAS_MAX_SLUG_LENGTH', 20);

/** @var string Perfex CRM base upload folder with trailing slash */
defined('FQ_SAAS_UPLOAD_BASE_DIR') or define('FQ_SAAS_UPLOAD_BASE_DIR', 'uploads/');

// Tenant recognition modes
defined('FQ_SAAS_TENANT_MODE_PATH') or define('FQ_SAAS_TENANT_MODE_PATH', 'path');
defined('FQ_SAAS_TENANT_MODE_DOMAIN') or define('FQ_SAAS_TENANT_MODE_DOMAIN', 'custom_domain');
defined('FQ_SAAS_TENANT_MODE_SUBDOMAIN') or define('FQ_SAAS_TENANT_MODE_SUBDOMAIN', 'subdomain');

/** @var string[] List of options field that will should not be controlled by tenants i.e security fields */
defined('FQ_SAAS_ENFORCED_SHARED_FIELDS') or define('FQ_SAAS_ENFORCED_SHARED_FIELDS', ['allowed_files', 'ticket_attachments_file_extensions']);

/** @var string[] List of dangerous extensions */
defined('FQ_SAAS_DANGEROUS_EXTENSIONS') or define('FQ_SAAS_DANGEROUS_EXTENSIONS', [
    ".php", ".exe", ".sh", ".bat", ".cmd", ".js", ".vbs",
    ".py", ".pl", ".jsp", ".aspx", ".cgi", ".htaccess", ".ini", ".dll", ".java", ".applet"
]);


defined('FQ_SAAS_CRON_PROCESS_MODULE') or define('FQ_SAAS_CRON_PROCESS_MODULE', 'module-update');
defined('FQ_SAAS_CRON_PROCESS_SINGLE_TENANT_MODULE') or define('FQ_SAAS_CRON_PROCESS_SINGLE_TENANT_MODULE', 'module-update-single-tenant');
defined('FQ_SAAS_CRON_PROCESS_PACKAGE') or define('FQ_SAAS_CRON_PROCESS_PACKAGE', 'package-update');

defined('FQ_SAAS_GLOBAL_ACTIVE_MODULES_OPTION_KEY') or define('FQ_SAAS_GLOBAL_ACTIVE_MODULES_OPTION_KEY', 'fq_saas_registered_global_active_modules');

/** Route prefix */
defined('FQ_SAAS_ROUTE_NAME') or define('FQ_SAAS_ROUTE_NAME', 'fq_saas');

defined('FQ_SAAS_UPDATE_URL') or define('FQ_SAAS_UPDATE_URL', 'https://perfextosaas.com/evanto.php?purchase_code=[PC]&action=[AC]&module=[MD]');

defined('FQ_SAAS_MINIMUM_AUTO_INSTANCE_REMOVE_GRACE_PERIOD') or define('FQ_SAAS_MINIMUM_AUTO_INSTANCE_REMOVE_GRACE_PERIOD', 7); // 7 days

/** Seed source flags */
defined('FQ_SAAS_SEED_SOURCE_FILE') or define('FQ_SAAS_SEED_SOURCE_FILE', 'file');
defined('FQ_SAAS_SEED_SOURCE_TENANT') or define('FQ_SAAS_SEED_SOURCE_TENANT', 'tenant');
defined('FQ_SAAS_SEED_SOURCE_MASTER') or define('FQ_SAAS_SEED_SOURCE_MASTER', 'master');

/** Status flags */
defined('FQ_SAAS_STATUS_PENDING') or define('FQ_SAAS_STATUS_PENDING', 'pending');
defined('FQ_SAAS_STATUS_PENDING_DELETE') or define('FQ_SAAS_STATUS_PENDING_DELETE', 'pending-delete');
defined('FQ_SAAS_STATUS_ACTIVE') or define('FQ_SAAS_STATUS_ACTIVE', 'active');
defined('FQ_SAAS_STATUS_INACTIVE') or define('FQ_SAAS_STATUS_INACTIVE', 'inactive');
defined('FQ_SAAS_STATUS_BANNED') or define('FQ_SAAS_STATUS_BANNED', 'banned');
defined('FQ_SAAS_STATUS_DEPLOYING') or define('FQ_SAAS_STATUS_DEPLOYING', 'deploying');