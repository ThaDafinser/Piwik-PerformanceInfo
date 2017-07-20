<?php
namespace Piwik\Plugins\PerformanceInfo;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugin\Menu as PluginMenu;

class Menu extends PluginMenu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
         if (! Piwik::isUserHasSomeAdminAccess()) {
            return;
        }

        $menu->addDiagnosticItem(
            'Performance Info',
            array(
                'module' => 'PerformanceInfo',
                'action' => 'index'
            ),
            $order = 100);
    }
}
