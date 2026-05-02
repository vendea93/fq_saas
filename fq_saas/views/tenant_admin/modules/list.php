<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-6">
                    <h4 class="tw-mt-0 tw-mb-2"><?= e($title); ?></h4>
                    <p class="text-muted tw-mb-0">
                        <?= _l('fq_saas_plugin_marketplace_subtitle'); ?>
                    </p>
                </div>
                <?php if (!empty($is_demo_marketplace)) { ?>
                <div class="alert alert-info">
                    <?= _l('fq_saas_demo_marketplace_notice'); ?><br>
                    <?= _l('fq_saas_marketplace_admin_only_note'); ?>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        $package_modules = [];
                        $extra_modules = [];
                        foreach ($modules as $module_item) {
                            if (!empty($module_item['in_package'])) {
                                $package_modules[] = $module_item;
                            } else {
                                $extra_modules[] = $module_item;
                            }
                        }
                        ?>
                        <h5 class="tw-mt-0 tw-mb-4">W pakiecie (zainstalowane)</h5>
                        <div class="row">
                            <?php foreach ($package_modules as $module) {
                                $system_name = $module['system_name'];
                                $description = $module['description'];
                                $versionRequirementMet = $this->app_modules->is_minimum_version_requirement_met($system_name);
                                $is_active = (int)($module['activated'] ?? 0) === 1;
                                $price = (string)($module['price'] ?? '');
                            ?>
                            <div class="col-md-4 col-sm-6 tw-mb-4">
                                <div class="panel_s tw-h-full">
                                    <div class="panel-body tw-flex tw-flex-col tw-h-full">
                                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                                            <div>
                                                <h4 class="tw-mt-0 tw-mb-1"><?= e($module['custom_name']); ?></h4>
                                                <span class="label <?= $is_active ? 'label-success' : 'label-default'; ?>">
                                                    <?= $is_active ? _l('active') : _l('inactive'); ?>
                                                </span>
                                            </div>
                                            <i class="fa fa-plug text-muted tw-text-2xl"></i>
                                        </div>
                                        <p class="text-muted tw-mt-4 tw-flex-1" data-version="<?= e($module['headers']['version'] ?? ''); ?>">
                                            <?= e(empty($description) ? ($module['headers']['description'] ?? '') : $description); ?>
                                        </p>
                                        <?php if ($price !== '') { ?>
                                        <p class="tw-mb-3">
                                            <strong><?= _l('fq_saas_price'); ?>:</strong> <?= app_format_money((float)$price, get_base_currency()); ?>/mies.
                                        </p>
                                        <?php } ?>
                                        <div class="tw-mt-4">
                                            <?php if (!$is_active && $versionRequirementMet) { ?>
                                            <a href="<?= admin_url('apps/modules/update/' . $system_name . '/enable'); ?>" class="btn btn-primary btn-sm">
                                                <?= _l('fq_saas_marketplace_enable_plugin'); ?>
                                            </a>
                                            <?php } ?>
                                            <?php if ($is_active) { ?>
                                            <a href="<?= admin_url('apps/modules/update/' . $system_name . '/disable'); ?>" class="btn btn-default btn-sm _delete">
                                                <?= _l('fq_saas_marketplace_disable_plugin'); ?>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <hr class="tw-my-6">
                        <h5 class="tw-mt-0 tw-mb-4">Dodatkowe moduły</h5>
                        <div class="row">
                            <?php foreach ($extra_modules as $module) {
                                $system_name = $module['system_name'];
                                $description = $module['description'];
                                $versionRequirementMet = $this->app_modules->is_minimum_version_requirement_met($system_name);
                                $price = (string)($module['price'] ?? '');
                            ?>
                            <div class="col-md-4 col-sm-6 tw-mb-4">
                                <div class="panel_s tw-h-full">
                                    <div class="panel-body tw-flex tw-flex-col tw-h-full">
                                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                                            <div>
                                                <h4 class="tw-mt-0 tw-mb-1"><?= e($module['custom_name']); ?></h4>
                                                <span class="label label-default">Poza pakietem</span>
                                            </div>
                                            <i class="fa fa-plug text-muted tw-text-2xl"></i>
                                        </div>
                                        <p class="text-muted tw-mt-4 tw-flex-1" data-version="<?= e($module['headers']['version'] ?? ''); ?>">
                                            <?= e(empty($description) ? ($module['headers']['description'] ?? '') : $description); ?>
                                        </p>
                                        <?php if ($price !== '') { ?>
                                        <p class="tw-mb-3">
                                            <strong><?= _l('fq_saas_price'); ?>:</strong> <?= app_format_money((float)$price, get_base_currency()); ?>/mies.
                                        </p>
                                        <?php } ?>
                                        <div class="tw-mt-4">
                                            <?php if (!empty($is_demo_marketplace) && $versionRequirementMet) { ?>
                                            <a href="<?= admin_url('apps/modules/update/' . $system_name . '/enable'); ?>" class="btn btn-primary btn-sm">
                                                <?= _l('fq_saas_marketplace_enable_plugin'); ?>
                                            </a>
                                            <?php } else { ?>
                                            <a href="<?= admin_url('billing/my_account'); ?>" class="btn btn-default btn-sm">
                                                <?= _l('fq_saas_marketplace_buy_install_plugin'); ?>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>
