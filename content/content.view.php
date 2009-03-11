<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	require_once(TOOLKIT . '/class.datasource.php');

	Class contentExtensionDatasource_cacheView extends AdministrationPage{

		protected $driver;

		function __construct(&$parent){
			parent::__construct($parent);
			$this->setTitle('Symphony &ndash; Data Source Cache');
			$this->driver = $this->_Parent->ExtensionManager->create('datasource_cache');
		}
	
		function view(){
			
			$this->setPageType('table');
			$heading = new XMLElement('h2', 'Data Source Cache');
			$this->Form->appendChild($heading);
			
			$aTableHead = array(
				array('Data Source', 'col'),
				array('Cache files', 'col'),
				array('Cache size', 'col'),
			);
			
			$dsm = new DatasourceManager($this->_Parent);
			$datasources = $dsm->listAll();	
			
			$cachedata = $this->driver->buildCacheFileList();						
			
			$aTableBody = array();

			if(!is_array($datasources) || empty($datasources)){
				$aTableBody = array(
					Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', NULL, count($aTableHead))))
				);
			} else {
				
				$bEven = false;
				
				foreach($datasources as $ds) {
					
					$datasource = $dsm->create($ds['handle']);

					if ($datasource->dsParamCACHE) {
						
						$name = Widget::TableData($ds['name']);
						$name->appendChild(Widget::Input("items[{$ds['handle']}]", null, 'checkbox'));

						$files = Widget::TableData(isset($cachedata[$ds['handle']]['count']) ? $cachedata[$ds['handle']]['count'] : '0');
						
						if (isset($cachedata[$ds['handle']]['size']))
						{
							if ($cachedata[$ds['handle']]['size'] < 1024)
								$size_str = $cachedata[$ds['handle']]['size'] . "b";
							else
								$size_str = floor($cachedata[$ds['handle']]['size']/1024) . "kb";
						}
						else
							$size_str = "0kb";
						
						$size = Widget::TableData($size_str);

						$aTableBody[] = Widget::TableRow(array($name, $files, $size), ($bEven ? 'even' : NULL));

						$bEven = !$bEven;
					}					

				}
			}
						
			$table = Widget::Table(
				Widget::TableHead($aTableHead), 
				NULL, 
				Widget::TableBody($aTableBody)
			);

			$this->Form->appendChild($table);
			
			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');
			
			$options = array(
				array(null, false, __('With Selected...')),
				array('clear', false, __('Clear Cache'))							
			);
			
			$tableActions->appendChild(Widget::Select('with-selected', $options));
			$tableActions->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));
			
			$this->Form->appendChild($tableActions);
			
		}
		
		function __actionIndex(){
			$checked = @array_keys($_POST['items']);

			if(is_array($checked) && !empty($checked)){
				
				switch($_POST['with-selected']) {

					case 'clear':								
						$this->driver->clearCache($checked);											
						
						redirect($this->_Parent->getCurrentPageURL());
						break;
				}
			}
		}
	
	}
	
?>