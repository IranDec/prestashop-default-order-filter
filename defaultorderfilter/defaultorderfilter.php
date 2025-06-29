<?php
/**
 * Module: DefaultOrderFilter
 * Description: Ensures the Orders grid always loads with a default date filter (today) unless overridden.
 * Author: Mohammad Babaei - https://adschi.com
 * Version: 1.0.4
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\OrderGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Definition\DefinitionInterface;
use PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters;
use Symfony\Component\HttpFoundation\Request;

class DefaultOrderFilter extends Module
{
    public function __construct()
    {
        $this->name = 'defaultorderfilter';
        $this->tab = 'administration';
        $this->version = '1.0.4';
        $this->author = 'Mohammad Babaei - https://adschi.com';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Default Orders Date Filter');
        $this->description = $this->l('Loads the Orders grid with today’s date filter by default.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.99.99'];
    }

    public function install()
    {
        return parent::install()
            // دو هوک برای پوشش همه حالتها
            && $this->registerHook('actionOrderGridDefinitionModifier')
            && $this->registerHook('actionGridFilterSubmitBefore');
    }

    public function uninstall()
    {
        $this->unregisterHook('actionOrderGridDefinitionModifier');
        $this->unregisterHook('actionGridFilterSubmitBefore');

        return parent::uninstall();
    }

    /**
     * قبل از رندر تعریف گرید، مقدار پیش‌فرض فرم فیلتر تاریخ را تنظیم می‌کند
     */
    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        // فقط گرید سفارشات
        if (empty($params['id']) || $params['id'] !== OrderGridDefinitionFactory::GRID_ID) {
            return;
        }

        /** @var DefinitionInterface $definition */
        $definition = $params['definition'];
        $filters = $definition->getFilters();

        // از توابع Tools برای گرفتن GET استفاده می‌کنیم
        $from = \Tools::getValue('filters[date_add][from]', null);
        $to   = \Tools::getValue('filters[date_add][to]', null);

        // اگر هیچ فیلتری نیست، ست می‌کنیم
        if (empty($from) && empty($to)) {
            $today = date('Y-m-d');
            $filters->setDefaultValue('date_add', [
                'from' => $today,
                'to'   => $today,
            ]);
            // لاگ برای دیباگ
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/defaultorderfilter.log',
                "[DefinitionModifier] default date_add set to {$today}\n", FILE_APPEND
            );
        }
    }

    /**
     * بعد از submit فیلتر (یا رفتار ریست)، اگر همچنان هیچ فیلتری نباشد، دوباره از امروز تا امروز ست می‌کند
     */
    public function hookActionGridFilterSubmitBefore(array $params)
    {
        if (empty($params['grid_id']) || $params['grid_id'] !== 'order') {
            return;
        }

        // پارامترهای فیلتر را می‌خوانیم
        $filters = $params['filters'] ?? [];
        $from   = $filters['date_add[from]'] ?? null;
        $to     = $filters['date_add[to]']   ?? null;

        if (empty($from) && empty($to)) {
            $today = date('Y-m-d');
            $params['filters']['date_add[from]'] = $today;
            $params['filters']['date_add[to]']   = $today;
            // لاگ برای دیباگ
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/defaultorderfilter.log',
                "[FilterSubmitBefore] default date_add[from,to] = {$today}\n", FILE_APPEND
            );
        }
    }
}
