<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

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

						// TODO: show total cache XML files and their associated file size
						$files = Widget::TableData('5');
						$size = Widget::TableData('4kb');

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
					
						foreach ($checked as $ds) {
							$this->driver->clearCache($ds);
						}
						die;
						
						redirect($this->_Parent->getCurrentPageURL());
						break;
				}
			}
		}
	
	}
	
?>