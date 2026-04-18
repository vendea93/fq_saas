<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Master-DB helpers for fq_saas_* extension tables (not legacy perfex_saas core tables).
 */
class Fq_saas_extensions_model extends App_Model
{
    protected function t(string $entity): string
    {
        return fq_saas_extensions_table($entity);
    }

    public function landing_pages(): array
    {
        if (!$this->db->table_exists($this->t('landing_pages'))) {
            return [];
        }
        $this->db->order_by('slug', 'ASC');

        return $this->db->get($this->t('landing_pages'))->result();
    }

    public function get_landing_page($id)
    {
        return $this->db->get_where($this->t('landing_pages'), ['id' => (int) $id])->row();
    }

    public function save_landing_page(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        unset($data['id']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id > 0) {
            $this->db->where('id', $id)->update($this->t('landing_pages'), $data);

            return $id;
        }
        $data['slug'] = $data['slug'] ?? 'page-' . uniqid();
        $this->db->insert($this->t('landing_pages'), $data);

        return (int) $this->db->insert_id();
    }

    public function cms_pages(string $type = ''): array
    {
        if (!$this->db->table_exists($this->t('cms_pages'))) {
            return [];
        }
        if ($type !== '') {
            $this->db->where('type', $type);
        }
        $this->db->order_by('slug', 'ASC');

        return $this->db->get($this->t('cms_pages'))->result();
    }

    public function get_cms_page($id)
    {
        return $this->db->get_where($this->t('cms_pages'), ['id' => (int) $id])->row();
    }

    public function save_cms_page(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        unset($data['id']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (empty($data['created_at']) && $id === 0) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if ($id > 0) {
            $this->db->where('id', $id)->update($this->t('cms_pages'), $data);

            return $id;
        }
        $this->db->insert($this->t('cms_pages'), $data);

        return (int) $this->db->insert_id();
    }

    public function coupons(): array
    {
        if (!$this->db->table_exists($this->t('coupons'))) {
            return [];
        }
        $this->db->order_by('code', 'ASC');

        return $this->db->get($this->t('coupons'))->result();
    }

    public function get_coupon($id)
    {
        return $this->db->get_where($this->t('coupons'), ['id' => (int) $id])->row();
    }

    public function save_coupon(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        unset($data['id']);
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        if ($id > 0) {
            $this->db->where('id', $id)->update($this->t('coupons'), $data);

            return $id;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->t('coupons'), $data);

        return (int) $this->db->insert_id();
    }

    public function delete_coupon(int $id): bool
    {
        return (bool) $this->db->where('id', $id)->delete($this->t('coupons'));
    }

    public function delete_landing_page(int $id): bool
    {
        if (!$this->db->table_exists($this->t('landing_pages'))) {
            return false;
        }
        return (bool) $this->db->where('id', $id)->delete($this->t('landing_pages'));
    }

    public function delete_cms_page(int $id): bool
    {
        if (!$this->db->table_exists($this->t('cms_pages'))) {
            return false;
        }
        return (bool) $this->db->where('id', $id)->delete($this->t('cms_pages'));
    }

    public function delete_affiliate(int $id): bool
    {
        if (!$this->db->table_exists($this->t('affiliates'))) {
            return false;
        }
        return (bool) $this->db->where('id', $id)->delete($this->t('affiliates'));
    }

    public function affiliates(): array
    {
        if (!$this->db->table_exists($this->t('affiliates'))) {
            return [];
        }
        $this->db->order_by('id', 'DESC');

        return $this->db->get($this->t('affiliates'))->result();
    }

    public function get_affiliate_by_code(string $code)
    {
        $code = strtoupper(trim($code));

        return $this->db->get_where($this->t('affiliates'), ['code' => $code])->row();
    }

    public function save_affiliate(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        unset($data['id']);
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        if ($id > 0) {
            $this->db->where('id', $id)->update($this->t('affiliates'), $data);

            return $id;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->t('affiliates'), $data);

        return (int) $this->db->insert_id();
    }

    public function credit_affiliate_balance(int $affiliate_id, float $amount): void
    {
        if ($amount <= 0 || !$this->db->table_exists($this->t('affiliates'))) {
            return;
        }
        $this->db->set('balance', 'balance+' . (float) $amount, false);
        $this->db->where('id', $affiliate_id);
        $this->db->update($this->t('affiliates'));
    }
}
