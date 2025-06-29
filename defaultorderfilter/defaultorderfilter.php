<?php
/**
 * Module: DefaultOrderFilter
 * Description: Set default order grid date filter (last 7 days) if not set manually.
 * Author: Mohammad Babaei - https://adschi.com
 * Version: 1.0.5
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\OrderGridDefinitionFactory;

class DefaultOrderFilter extends Module
{
    public function __construct()
    {
        $this->name = 'defaultorderfilter';
        $this->tab = 'administration';
        $this->version = '1.0.5';
        $this->author = 'Mohammad Babaei - https://adschi.com';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Default Orders Date Filter');
        $this->description = $this->l('Automatically sets a default date range (last 7 days) on orders page.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.99.99'];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionGridFilterSubmitBefore');
    }

    public function uninstall()
    {
        return $this->unregisterHook('actionGridFilterSubmitBefore') && parent::uninstall();
    }

    /**
     * Inject default filter for order grid if none exists
     */
    public function hookActionGridFilterSubmitBefore(array $params)
    {
        // فقط روی گرید سفارشات
        if ($params['grid_id'] !== 'order') {
            return;
        }

        // فیلترها از URL با prefix خاص مثل order[filters] وارد می‌شن
        $filters = $params['filters'] ?? [];

        // بررسی آیا فیلتر تاریخ خالی است
        $from = $filters['date_add[from]'] ?? null;
        $to   = $filters['date_add[to]'] ?? null;

        if (empty($from) && empty($to)) {
            $today = date('Y-m-d');
            $last7 = date('Y-m-d', strtotime('-7 days'));

            $params['filters']['date_add[from]'] = $last7;
            $params['filters']['date_add[to]'] = $today;

            // (اختیاری) برای دیباگ لاگ بنویس
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/defaultorderfilter.log',
                "[AutoInject] Filter applied from $last7 to $today\n", FILE_APPEND);
        }
    }
}
