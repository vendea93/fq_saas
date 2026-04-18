<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h4 class="tw-mb-3"><?php echo _l('fq_saas_cms'); ?></h4>
                <?php echo form_open(admin_url(FQ_SAAS_ROUTE_NAME . '/cms/edit/' . ($page->id ?? ''))); ?>
                <input type="hidden" name="id" value="<?php echo (int) ($page->id ?? 0); ?>" />
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo render_input('slug', _l('slug'), $page->slug ?? '', 'text', ['required' => true]); ?>
                        <?php echo render_select('type', [['id' => 'page', 'name' => 'page'], ['id' => 'blog', 'name' => 'blog'], ['id' => 'faq', 'name' => 'faq']], ['id', 'name'], 'Type', $page->type ?? 'page'); ?>
                        <?php echo render_input('title', _l('name'), $page->title ?? '', 'text'); ?>
                        <?php echo render_select('status', [['id' => 'draft', 'name' => 'draft'], ['id' => 'published', 'name' => 'published']], ['id', 'name'], _l('status'), $page->status ?? 'draft'); ?>
                        <?php echo render_textarea('body_html', _l('fq_saas_body_html'), $page->body_html ?? ''); ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
