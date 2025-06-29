<?php
/**
 * Module: DefaultOrderFilter
 * Description: Applies a default 1-month date filter to orders page for employees.
 * Author: Mohammad Babaei - https://adschi.com
 * Version: 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class DefaultOrderFilter extends Module
{
    public function __construct()
    {
        $this->name = 'defaultorderfilter';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Mohammad Babaei';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->displayName = $this->l('Default Orders Date Filter');
        $this->description = $this->l('Automatically sets the default order date filter to the last month.');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionGridFilterSubmitBefore');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookActionGridFilterSubmitBefore(array $params)
    {
        if ($params['grid_id'] !== 'order') {
            return;
        }

        // Only set if user hasn't already applied a filter
        if (empty($params['filters']['date_add[from]']) && empty($params['filters']['date_add[to]'])) {
            $params['filters']['date_add[from]'] = date('Y-m-d', strtotime('-1 month'));
            $params['filters']['date_add[to]'] = date('Y-m-d');
        }
    }
}
