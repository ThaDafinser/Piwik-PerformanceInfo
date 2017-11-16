<?php
namespace Piwik\Plugins\PerformanceInfo;

use Piwik\Piwik;
use Piwik\Config;
use Piwik\View;
use Piwik\Plugin\ControllerAdmin;

class Controller extends ControllerAdmin
{

    const PIWIK_GITHUB = 'https://github.com/piwik/piwik/tree/8914e371016a98fd4883cbe1fbe7b1aa1827c540';

    private $plugins = [
        'ExampleAPI' => 'disabled',
        'ExampleCommand' => 'disabled',
        'ExamplePlugin' => 'disabled',
        
        'ExampleReport' => 'disabled',
        'ExampleRssWidget' => 'disabled',
        'ExampleSettingsPlugin' => 'disabled',
        
        'ExampleTracker' => 'disabled',
        'ExampleUI' => 'disabled',
        'ExampleVisualization' => 'disabled',
        
        'Feedback' => 'disabled',
        
        'SecurityInfo' => 'enabled'
    ];

    /**
     *
     * @return array
     */
    private function getValues($section, $setting, $recommended, $type = '', $link = '', $description = '')
    {
        $config = Config::getInstance();
        
        $globalConfig = $config->getFromGlobalConfig($section);
        $commonConfig = $config->getFromCommonConfig($section);
        $localConfig = $config->getFromLocalConfig($section);
        
        $global = '';
        if(isset($globalConfig[$setting])){
            $global = $globalConfig[$setting];
        }
        
        $common = '';
        if(isset($commonConfig[$setting])){
            $common = $commonConfig[$setting];
        }
        
        $local = '';
        if(isset($localConfig[$setting])){
            $local = $localConfig[$setting];
        }
        
        $used = '';
        if(isset($config->{$section}[$setting])){
            $used = $config->{$section}[$setting];
        }
        
        $result = [
            'section' => $section,
            'setting' => $setting,
            'global' => $global,
            'common' => $common,
            'local' => $local,
            'used' => $used,
            'recommended' => $recommended,
            'type' => $type,
            'link' => $link,
            'description' => $description
        ];
        
        foreach ($result as &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
        }
        
        return $result;
    }

    /**
     *
     * @return array
     */
    private function getPlugins()
    {
        $config = Config::getInstance();
        $activePlugins = $config->{'Plugins'}['Plugins'];
        $installedPlugins = $config->{'PluginsInstalled'}['PluginsInstalled'];
        
        $pluginSuggestions = [];
        foreach ($this->plugins as $name => $suggestedMode) {
            
            $realMode = 'disabled';
            if (in_array($name, $activePlugins)) {
                $realMode = 'enabled';
            }
            
            $isInstalled = 'no';
            if (in_array($name, $installedPlugins)) {
                $isInstalled = 'yes';
            }
            
            $pluginSuggestions[] = [
                'name' => $name,
                'current' => $realMode,
                'recommended' => $suggestedMode,
                'isInstalled' => $isInstalled
            ];
        }
        
        return $pluginSuggestions;
    }

    /**
     *
     * @return array
     */
    private function getResult()
    {
        $configFile = self::PIWIK_GITHUB . '/config/global.ini.php';
        
        // , $configFile . ''
        $result = [];
        $result['log'] = [
            $this->getValues('log', 'log_level', 'WARN', 'security', $configFile . '#L65-L68')
        ];
        
        $result['Cache'] = [
            $this->getValues('Cache', 'backend', 'chained', 'performance', $configFile . '#L73-L80')
        ];
        
        $result['ChainedCache'] = [
            $this->getValues('ChainedCache', 'backends', 'array,redis', 'performance', $configFile . '#L82-L86')
        ];
        
        $result['Debug'] = [
            $this->getValues('Debug', 'always_archive_data_period', 0, 'performance', $configFile . '#L99-L101'),
            $this->getValues('Debug', 'always_archive_data_day', 0, 'performance', $configFile . '#L102'),
            $this->getValues('Debug', 'always_archive_data_range', 0, 'performance', $configFile . '#L103-L104'),
            
            $this->getValues('Debug', 'enable_sql_profiler', 0, 'performance', $configFile . '#L106-L109'),
            $this->getValues('Debug', 'enable_measure_piwik_usage_in_idsite', 0, '', $configFile . '#L111-L114'),
            
            $this->getValues('Debug', 'tracker_always_new_visitor', 0, '', $configFile . '#L116-L117'),
            $this->getValues('Debug', 'allow_upgrades_to_beta', 0, 'security', $configFile . '#L119-L120', 'Beta releases may have bugs')
        ];
        
        $result['DebugTests'] = [
            $this->getValues('Debug', 'enable_load_standalone_plugins_during_tests', 0, '', $configFile . '#L123-L125')
        ];
        
        $result['DebugTests'] = [
            $this->getValues('DebugTests', 'enable_load_standalone_plugins_during_tests', 0, '', $configFile . '#L123-L125')
        ];
        
        $result['Development'] = [
            $this->getValues('Development', 'enabled', 0, 'security', $configFile . '#L128-L132'),
            $this->getValues('Development', 'disable_merged_assets', 0, 'performance', $configFile . '#L134-L136')
        ];
        
        $result['General'] = [
            $this->getValues('General', 'enable_processing_unique_visitors_day', 1, 'performance', $configFile . '#L140-L148'),
            $this->getValues('General', 'enable_processing_unique_visitors_week', 1, 'performance', $configFile . '#L140-L148'),
            $this->getValues('General', 'enable_processing_unique_visitors_month', 1, 'performance', $configFile . '#L140-L148'),
            $this->getValues('General', 'enable_processing_unique_visitors_year', 0, 'performance', $configFile . '#L140-L148'),
            $this->getValues('General', 'enable_processing_unique_visitors_range', 0, 'performance', $configFile . '#L140-L148'),
            
            $this->getValues('General', 'enabled_periods_UI', 'day,week,month,year', 'performance', $configFile . '#L155-L159'),
            $this->getValues('General', 'enabled_periods_API', 'day,week,month,year', 'performance', $configFile . '#L155-L159'),
            
            $this->getValues('General', 'maintenance_mode', 0, '', $configFile . '#L161-L163'),
            
            $this->getValues('General', 'action_category_level_limit', 3, 'performance', $configFile . '#L173-L176', 'Many actions lead to many entries in the archives ( = more space and more time for loading data)'),
            
            $this->getValues('General', 'show_multisites_sparklines', 0, 'performance', $configFile . '#L184-L185'),
            
            $this->getValues('General', 'anonymous_user_enable_use_segments_API', 0, 'security', $configFile . '#L190-L192'),
            
            $this->getValues('General', 'browser_archiving_disabled_enforce', 1, '', $configFile . '#L194-L197'),
            
            $this->getValues('General', 'enable_create_realtime_segments', 0, '', $configFile . '#L199-L206'),
            $this->getValues('General', 'enable_segment_suggested_values', 0, '', $configFile . '#L208-L210'),
            $this->getValues('General', 'adding_segment_requires_access', 'superuser', 'performance', $configFile . '#L212-L216'),
            $this->getValues('General', 'allow_adding_segments_for_all_websites', 0, 'performance', $configFile . '#L218-L221'),
            
            $this->getValues('General', 'datatable_row_limits', '5,10,25,50', 'performance', $configFile . '#L234-L236', 'Listing 500 entries could blow up your browser or you might run in a timeout'),
            
            $this->getValues('General', 'default_day', 'yesterday', 'performance', $configFile . '#L248-L250'),
            $this->getValues('General', 'default_period', 'day', 'performance', $configFile . '#L251-L252'),
            
            $this->getValues('General', 'enable_browser_archiving_triggering', 0, 'performance', $configFile . '#L259-L262'),
            
            $this->getValues('General', 'force_ssl', 1, 'security', $configFile . '#L306-L310', 'https is always better than http'),
            
            $this->getValues('General', 'live_widget_refresh_after_seconds', 30, 'performance', $configFile . '#L399-L401'),
            
            $this->getValues('General', 'multisites_refresh_after_seconds', 0, 'performance', $configFile . '#L408-L410'),
            
            $this->getValues('General', 'enable_delete_old_data_settings_admin', 0, '', $configFile . '#L517-L519'),
            
            $this->getValues('General', 'enable_auto_update', 0, 'security', $configFile . '#L527-L528', 'Package are currently not verified/checked'),
            $this->getValues('General', 'enable_update_communication', 1, 'security', $configFile . '#L530-L532', 'Get an email when updates are available')
            $this->getValues('General', 'enable_internet_features', 0, 'security', $configFile . '#L585-L587', 'Turn of any internet communication')
        ];
        
        /*
         * Tracker
         */
        $result['Tracker'] = [
            $this->getValues('Tracker', 'enable_fingerprinting_across_websites', 0, '', $configFile . '#L547-L551'),
            $this->getValues('Tracker', 'new_visit_api_requires_admin', 0, ''),
            $this->getValues('Tracker', 'debug', 0, '', $configFile . '#L558-L560'),
            
            $this->getValues('Tracker', 'tracking_requests_require_authentication', 1, 'security', $configFile . '#L651-L656')
        ];
        
        $result['Deletelogs'] = [
            $this->getValues('Deletelogs', 'delete_logs_enable', 0, '', $configFile . '#L672-L676')
        ];
        
        return $result;
    }

    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();
        
        $view = new View('@PerformanceInfo/index');
        $this->setBasicVariablesView($view);
        
        $config = Config::getInstance();
        $view->paths = [
            'global' => PIWIK_USER_PATH . Config::DEFAULT_GLOBAL_CONFIG_PATH,
            'common' => $config->getCommonConfigPath(),
            'local' => $config->getLocalConfigPath()
        ];
        $view->results = $this->getResult();
        $view->plugins = $this->getPlugins();
        
        return $view->render();
    }
}
