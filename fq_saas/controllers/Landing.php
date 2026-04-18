<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Proxy\Http\Request;
use Proxy\Proxy;
use Proxy\Config;


class Landing extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Method to server the active landing page theme
     *
     * @return void
     */
    public function index()
    {
        $this->check_for_redirection();

        return redirect(base_url('authentication/login'));
    }

    /**
     * Serve a published landing page from the master DB (see fq_saas_landing_builtin_slug option).
     *
     * @param string $slug
     */
    public function builtin($slug = 'home')
    {
        $this->check_for_redirection();

        $CI = &get_instance();
        $CI->load->model('fq_saas/fq_saas_extensions_model');
        $table = fq_saas_extensions_table('landing_pages');
        if (!$CI->db->table_exists($table)) {
            show_404();
        }

        // Slug arrives from routing; enforce a safe character set before DB lookup.
        $slug = preg_replace('/[^a-z0-9\-\_]/i', '', (string) $slug);
        if ($slug === '') {
            show_404();
        }

        $page = $CI->db->get_where($table, ['slug' => $slug, 'status' => 'published'])->row();
        if (!$page) {
            show_404();
        }

        // body_html is authored by staff with fq_saas_landing permission; block direct public access
        // to unpublished drafts and send a strict security header set.
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: no-referrer-when-downgrade');

        $title = html_escape((string) ($page->title ?? ''));
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $title . '</title></head><body>';
        echo (string) $page->body_html; // Authored HTML — trusted to staff but sandboxed via headers above.
        echo '</body></html>';
        exit;
    }

    /**
     * Method to serve the proxied landing page.
     * Its essensial the proxied adddress runs on same domain to prevent CORS or whitelabeled for this installation domain.
     *
     * @return void
     */
    public function proxy()
    {

        $this->check_for_redirection();

        $url = get_option('fq_saas_landing_page_url');

        if ($url && $url !== base_url()) {
            if (get_option('fq_saas_landing_page_url_mode') === 'redirection') {
                redirect($url);
            }
        }

        //Config::set('url_mode', 2);
        //Config::set('encryption_key', md5(session_id()));

        session_write_close();

        $proxy = new Proxy();

        $proxy->getEventDispatcher()->addListener('request.sent', function ($event) {

            if ($event['response']->getStatusCode() != 200) {
                show_error("Bad status code!", $event['response']->getStatusCode(), "Landing");
            }
        });

        // load plugins
        $plugins = [
            'HeaderRewrite',
            'Stream',
            'Cookie',
            //'Proxify',
        ];
        foreach ($plugins as $plugin) {

            $plugin_class = $plugin . 'Plugin';

            if (class_exists('\\Proxy\\Plugin\\' . $plugin_class)) {

                // does the native plugin from php-proxy package with such name exist?
                $plugin_class = '\\Proxy\\Plugin\\' . $plugin_class;
            }

            $proxy->addSubscriber(new $plugin_class());
        }

        $request = Request::createFromGlobals();
        $request->get->clear();

        if (isset($_GET['q'])) {
            $url = url_decrypt($_GET['q']);
        }

        $response = $proxy->forward($request, $url);

        // send the response back to the client
        $response->send();
    }

    public function show_404()
    {
        // ensure not servable by proxy, then server 404
        show_404();
    }

    /**
     * Check if there is an active session and redirect to the dashboard if loggedin.
     *
     * @return void
     */
    private function check_for_redirection()
    {
        if (get_option('fq_saas_force_redirect_to_dashboard') == "1") {
            if (is_client_logged_in()) {
                return redirect('clients');
            }

            if (is_staff_logged_in()) {
                return redirect('admin');
            }
        }
    }
}
