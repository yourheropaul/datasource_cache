<?php
	
	class extension_datasource_cache extends Extension {
		public function about() {
			return array(
				'name'			=> 'Data Source Cache',
				'version'		=> '0.1',
				'release-date'	=> '2009-02-23',
				'author'		=> array(
					'name'			=> 'Nick Dunn',
					'website'		=> 'http://airlock.com',
					'email'			=> 'nick.dunn@airlock.com'
				),
				'description'	=> 'Force refresh of data source caches'
			);
		}
		
		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/publish/new/',
					'delegate' => 'EntryPostCreate',
					'callback' => '__refresh'
				),
				array(
					'page' => '/publish/edit/',
					'delegate' => 'EntryPostEdit',
					'callback' => '__refresh'
				),
			);
		}
		
		public function __refresh($context){

			/*
				Cear the DS cache when its associated Section is edited
			*/

			$sm = new SectionManager($this->_Parent);
			$section_handle = Administration::instance()->Page->_context['section_handle'];
			$section_id = $sm->fetchIDFromHandle($section_handle);
			
			// find all native data sources (not added via Extensions)
			$dsm = new DatasourceManager($this->_Parent);
			$datasources = $dsm->listAll();		
			
			if(is_array($datasources) && !empty($datasources)){
				
				foreach($datasources as $ds){
					
					// check they are "Section" DSs and not Dynamic/Static XML, Authors or Navigation
					if (is_numeric($ds['type']) && $ds['type'] == $section_id){
						
						// instantiate the DS class and see if it has caching enabled
						$datasource = $dsm->create($ds['handle']);
						
						if ($datasource->dsParamCACHE){	
							$this->clearCache($ds['handle']);							
						}
						
					}
					
				}
				
			}
			
			die;
			
		}
		
		public function clearCache($handle) {
			// TODO:
			// find all cache files with this handle?
		}
		
		public function fetchNavigation(){			
			return array(
				array(
					'location'	=> 300,
					'name'	=> 'Data Source Cache',
					'link'	=> '/view/'
				)
			);		
		}
		
	}
	
?>