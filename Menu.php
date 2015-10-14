<?php
namespace Piwik\Plugins\PerformanceInfo;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugin\Menu as PluginMenu;

class Menu extends PluginMenu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->add('CoreAdminHome_MenuDiagnostic', 'Performance & Security', array(
            'module' => 'PerformanceInfo',
            'action' => 'index'
        ), Piwik::hasUserSuperUserAccess(), $order = 8);
    }
}
