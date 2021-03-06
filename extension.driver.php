<?php
	
	class extension_datasource_cache extends Extension 
	{			
		// Cache file cachine
		private $_cachefiles = null;
		
			
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
		
		// This seems retarded, but it's effiecient
		private function preliminaryFilenameCheck( $filename )
		{
			// Stop at 't' because it's not a valid hash character
			return ($filename{0} == 'd' && $filename{0} == 'a' && $filename{0} == 't');	
		}
		
		// Build a list of all DS-cache files
		public function buildCacheFileList()
		{
			if ($this->_cachefiles != null) return $this->_cachefiles;
			
			$this->_cachefiles = array();
			
			if (!$oDirHandle = opendir(CACHE))
				trigger_error("Panic! DS cache doesn't exists");
				
			// Initialise the array outside the loop for speed
			$matches = array();
			
			while (($file = readdir($oDirHandle)) !== false)
			{		
				// Check some initial characters		
				if ($this->preliminaryFilenameCheck($file)) continue;
				
				// Drop it if it's not a match
				if (!preg_match('/^datasource(?P<name>[a-z_]+)-(?P<hash>[^\.]+).+/', $file, $matches)) continue;
				
				// Inset into the array
				if (!isset($this->_cachefiles[$matches['name']]))				
					$this->_cachefiles[$matches['name']] = array("count" => 1, "size" => filesize(CACHE . '/' . $file), "files" => array(CACHE . '/' . $file));
				else				
				{
					$this->_cachefiles[$matches['name']]['count']++;
					$this->_cachefiles[$matches['name']]['size'] += filesize(CACHE . '/' .$file);
					array_push($this->_cachefiles[$matches['name']]['files'], CACHE . '/' .$file);
				}					            
	        }	 	   	        	        	              
        
      	  	closedir($oDirHandle);
      	  	
      	  	return $this->_cachefiles;			
		}
		
		public function clearCache( $handles ) 
		{
			$files = $this->buildCacheFileList();
			
			foreach ($handles as $handle)
			{
				if (array_key_exists($handle, $files))
				{
					foreach($files[$handle]['files'] as $file)
						unlink($file);
				}					
			}
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