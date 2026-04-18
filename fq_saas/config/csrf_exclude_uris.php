<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!defined('FQ_SAAS_ROUTE_NAME')) {
    return [];
}

/**
 * API endpoints are protected by the Authorization header (api_key middleware),
 * so CSRF tokens would be redundant and break server-to-server usage.
 * The regex is anchored to the API sub-path only — admin routes stay CSRF-protected.
 */
return [
    FQ_SAAS_ROUTE_NAME . '/api/.+',
];