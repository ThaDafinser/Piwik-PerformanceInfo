<?php
namespace Piwik\Plugins\PerformanceInfo;

use Piwik\Piwik;
use Piwik\Config;
use Piwik\View;
use Piwik\Plugin\ControllerAdmin;

class Controller extends ControllerAdmin
{

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

		'RerUserDates' => 'enabled',
		'SecurityInfo' => 'enabled'
	];

    /**
     *
     * @return array
     */
    private function getValues($section, $setting, $recommended)
    {
        $config = Config::getInstance();
        
        $result = [
            'section' => $section,
            'setting' => $setting,
            'global' => $config->getFromGlobalConfig($section)[$setting],
            'common' => $config->getFromCommonConfig($section)[$setting],
            'local' => $config->getFromLocalConfig($section)[$setting],
            'used' => $config->{$section}[$setting],
            'recommended' => $recommended
        ];
        
        foreach ($result as &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
        }
        
        return $result;
    }

    private function getPlugins($name, $recommendEnable = true){
		$config = Config::getInstance();
		$activePlugins = $config->{'Plugins'}['Plugins'];
		$installedPlugins = $config->{'PluginsInstalled'}['PluginsInstalled'];
		
		$pluginSuggestions = [];
		foreach($this->plugins as $name => $suggestedMode){
		   
		    $realMode = 'disabled';
		    if(in_array($name, $activePlugins)){
		        $realMode = 'enabled';
		    }
		    
		    $isInstalled = 'no';
		    if(in_array($name, $installedPlugins)){
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
        $result = [];
        
        $result['log'] = [
            $this->getValues('log', 'log_level', 'WARN')
        ];
        
        $result['Cache'] = [
            $this->getValues('Cache', 'backend', 'chained')
        ];
        
        $result['ChainedCache'] = [
            $this->getValues('ChainedCache', 'backends', 'array,redis,file')
        ];
        
        $result['Debug'] = [
            $this->getValues('Debug', 'always_archive_data_period', 0),
            $this->getValues('Debug', 'always_archive_data_day', 0),
            $this->getValues('Debug', 'always_archive_data_range', 0),
            
            $this->getValues('Debug', 'enable_sql_profiler', 0),
            $this->getValues('Debug', 'enable_measure_piwik_usage_in_idsite', 0),
            
            $this->getValues('Debug', 'tracker_always_new_visitor', 0),
            $this->getValues('Debug', 'allow_upgrades_to_beta', 0)
        ];
        
        $result['DebugTests'] = [
            $this->getValues('Debug', 'enable_load_standalone_plugins_during_tests', 0),
        ];
        
        $result['DebugTests'] = [
            $this->getValues('DebugTests', 'enable_load_standalone_plugins_during_tests', 0),
        ];
        
        $result['Development'] = [
            $this->getValues('Development', 'enabled', 0),
            $this->getValues('Development', 'disable_merged_assets', 0),
        ];
        
        $result['General'] = [
            $this->getValues('General', 'enable_processing_unique_visitors_day', 1),
            $this->getValues('General', 'enable_processing_unique_visitors_week', 1),
            $this->getValues('General', 'enable_processing_unique_visitors_month', 1),
            $this->getValues('General', 'enable_processing_unique_visitors_year', 0),
            $this->getValues('General', 'enable_processing_unique_visitors_range', 0),
            
            $this->getValues('General', 'enabled_periods_UI', 'day,week,month,year'),
            $this->getValues('General', 'enabled_periods_API', 'day,week,month,year'),
            
            $this->getValues('General', 'maintenance_mode', 0),
            
            $this->getValues('General', 'action_category_level_limit', 3),
            
            $this->getValues('General', 'show_multisites_sparklines', 0),
            
            $this->getValues('General', 'anonymous_user_enable_use_segments_API', 0),
            
            $this->getValues('General', 'browser_archiving_disabled_enforce', 1),
            
            $this->getValues('General', 'enable_create_realtime_segments', 0),
            $this->getValues('General', 'enable_segment_suggested_values', 0),
            $this->getValues('General', 'adding_segment_requires_access', 'superuser'),
            $this->getValues('General', 'allow_adding_segments_for_all_websites', 0),
            
            $this->getValues('General', 'datatable_row_limits', '5,10,25,50'),
            
            $this->getValues('General', 'default_day', 'yesterday'),
            $this->getValues('General', 'default_period', 'day'),
            
            $this->getValues('General', 'enable_browser_archiving_triggering', 0),
            
            $this->getValues('General', 'force_ssl', 1),
            
            $this->getValues('General', 'live_widget_refresh_after_seconds', 30),
            
            $this->getValues('General', 'multisites_refresh_after_seconds', 0),
            
            $this->getValues('General', 'enable_delete_old_data_settings_admin', 0),
            
            $this->getValues('General', 'enable_auto_update', 0),
            $this->getValues('General', 'enable_update_communication', 1),
        ];
        
        /*
         * Tracker
         */
        $result['Tracker'] = [
            $this->getValues('Tracker', 'enable_fingerprinting_across_websites', 0),
            $this->getValues('Tracker', 'new_visit_api_requires_admin', 0),
            $this->getValues('Tracker', 'debug', 0),
            
            $this->getValues('Tracker', 'tracking_requests_require_authentication', 1),
            
        ];
        
        $result['Deletelogs'] = [
            $this->getValues('Deletelogs', 'delete_logs_enable', 0),
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
