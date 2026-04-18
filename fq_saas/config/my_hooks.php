<?php defined('BASEPATH') or exit('No direct script access allowed');

$hook['post_system'][] = [
    'class'    => '',
    'function' => 'fq_saas_post_system_hook',
    'filename' => 'my_hooks.php',
    'filepath' => 'config',
];


function fq_saas_post_system_hook()
{
    if (!function_exists('fq_saas_can_mask_page_content'))
        require_once(__DIR__ . '/../helpers/fq_saas_helper.php');

    if (fq_saas_is_tenant() && fq_saas_can_mask_page_content()) {
        fq_saas_mask_buffer_content();
    }
}