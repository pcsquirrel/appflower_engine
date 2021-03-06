<?php
/**
 * extJs Portal layout
 *
 */
class afExtjsPortalLayout extends afExtjsViewportLayout
{
	public function __construct($attributes=array())
	{
		parent::__construct($attributes);
		$this->afExtjs->setAddons(array ('css' => array('/appFlowerPlugin/css/my-extjs.css',$this->afExtjs->getPluginsDir().'portal/portal.css'), 'js' => array($this->afExtjs->getPluginsDir().'portal/Portal.js',$this->afExtjs->getPluginsDir().'portal/PortalColumn.js',$this->afExtjs->getPluginsDir().'portal/Portlet.js',$this->afExtjs->getPluginsDir().'portal/sample-grid.js','/appFlowerPlugin/js/custom/portalsJS.js',$this->afExtjs->getPluginsDir().'form/Ext.ux.ClassicFormPanel.js',$this->afExtjs->getPluginsDir().'treegrid/TreeGrid.js',$this->afExtjs->getPluginsDir().'treegrid/Ext.ux.CheckboxSelectionModel.js',$this->afExtjs->getPluginsDir().'treegrid/Ext.ux.SynchronousTreeExpand.js')));
	}

	public function addSouthComponent($tools=false,$attributes=array())
	{	
		$attributes=array('id'=>'south_panel',
					      'title'=>isset($attributes['title'])?$attributes['title']:' ',
					      'height'=>'150',
					      'minHeight'=>'0',
					      'split'=>'true',
					      'collapsible'=>'true',
				          'tools'=>($tools?$tools->end():''));
		
		
		if(isset($this->attributes['viewport']['south_panel'])&&count($this->attributes['viewport']['south_panel'])>0)
		$attributes=array_merge($attributes,$this->attributes['viewport']['south_panel']);

		if(isset($attributes['items'])&&count($attributes['items'])>0)
		$this->addPanel('south',$attributes);		
	}
	
	public function startColumn($attributes=array())
	{
		return new afExtjsPortalColumn($attributes);		
	}
	
	public function endColumn($columnObj)
	{
		
		$this->attributes['viewport']['center_panel']['items'][]=$columnObj->end();
	}
	
	public function startTab($attributes=array())
	{
		/**
		 * ticket #74, tickets.appflower.com
		 * 
		 * assign activeTab to the current #<tab-name>, here just put a title slug for the current tab
		 */
		$attributes=array_merge($attributes,array('slug'=>Util::stripText($attributes['title'])));
		
		return new afExtjsPortalTab($attributes);		
	}
	
	public function endTab($tabObj)
	{		
		$this->attributes['viewport']['center_panel']['items'][]=$tabObj->end();
	}
	
	public function beforeEnd()
	{	
		if(isset($this->attributes['viewport']['center_panel'])&&count($this->attributes['viewport']['center_panel'])>0)
		$attributes=array_merge(array(),$this->attributes['viewport']['center_panel']);
						
		if(isset($this->attributes['idxml']))
		{
			$attributes['idxml']=$this->attributes['idxml'];
		}
		
		if(isset($this->attributes['layoutType']))
		{
			$attributes['layoutType']=$this->attributes['layoutType'];
		}
		
		switch ($this->attributes['layoutType'])
		{
			case afPortalStatePeer::TYPE_NORMAL:
				if(isset($this->attributes['tools']))
				{
				    if(!afExtjs::getInstance()->isDesktop())
				    {
				        $attributes['tools']=$this->attributes['tools']->end();
				    }
				    else {
				        $attributes['winTools']=$this->attributes['tools']->end();
				        unset($attributes['tools']);
				    }
				}
				
				if(afExtjs::getInstance()->isDesktop())
				{
				   $attributes['winTitle'] = $attributes['title'];
				   unset($attributes['title']);
				}
				
				if(isset($this->attributes['portalLayoutType']))
				{
					$attributes['portalLayoutType']=$this->attributes['portalLayoutType'];
				}				
				if(isset($this->attributes['portalWidgets']))
				{
					$attributes['portalWidgets']=$this->attributes['portalWidgets'];
				}
				$attributes['autoScroll']=true;
				//$attributes['id']='center_panel_first_panel';
				$attributes['border']=false;
				$attributes['bodyBorder']=false;
				/**
        		 * fixing ticket #1295 part 2
        		 * 
        		 * @author Radu Topala <radu@appflower.com>
        		 */
				$attributes['dropConfig']['ddGroup'] = 'portal_'.Util::makeRandomKey().'_dd';
				
				$this->afExtjs->privateAttributes['container']=$attributes;
				$this->afExtjs->private['center_panel_first_panel']=$this->afExtjs->Portal($attributes);
				
				$attributesPanel['items'][]=$this->afExtjs->asVar('center_panel_first_panel');
				$attributesPanel['border']=true;
				$attributesPanel['bodyBorder']=true;
				$attributesPanel['layout']='fit';
								
				$attributesPanel['id']='center_panel_first';
				$this->afExtjs->private['center_panel_first']=$this->afExtjs->Panel($attributesPanel);
				
				$attributesPanelContainer['items'][]=$this->afExtjs->asVar('center_panel_first');
				$attributesPanelContainer['style']='padding-right:5px;';
				$attributesPanelContainer['border']=false;
				$attributesPanelContainer['bodyBorder']=false;
				$attributesPanelContainer['layout']='fit';
				$attributesPanelContainer['id']='center_panel';         	      	
				
				$this->addPanel('center',$attributesPanelContainer);
				break;
			case afPortalStatePeer::TYPE_TABBED:
				$attributesTabPanel=array_merge($attributes,array('enableTabScroll'=>true,
						  'deferredRender'=>true,
					      'resizeTabs'=>true,
				          'minTabWidth'=>115,
				          'tabWidth'=>135,
					      'frame'=>false,
				          'collapsible'=>false,
				          'afterLayoutOnceEvent'=>false,
				          /*'bodyStyle'=>'height:476px;',*/
				          /*'defaults'=>$this->afExtjs->asAnonymousClass(array('autoScroll'=>true,'hideMode'=>'offsets'))*/));
				/**
				 * ticket #74, tickets.appflower.com
				 * 
				 * assign activeTab to the current #<tab-name> or to first tab if no hash is added in URI
				 */
				$attributesTabPanel['listeners']['afterLayout']=$this->afExtjs->asMethod(array(
				          	      	'parameters'=>'tabPanel,layout',
				          	      	'source'=>"if(tabPanel.getActiveTab())tabPanel.getActiveTab().doLayout();
				          	      	tabPanel.setHeight(tabPanel.ownerCt.getInnerHeight()-1);
				          	      	if(tabPanel.afterLayoutOnceEvent==false){new Portals().onTabChange(tabPanel);}
				          	      	"));
				$attributesTabPanel['listeners']['tabchange']=$this->afExtjs->asMethod(array(
				          	      	'parameters'=>'tabPanel,tab',
				          	      	'source'=>"tabPanel.doLayout();if(tabPanel.getActiveTab().items){tabPanel.getActiveTab().items.items[0].afterLayoutEvent=false;tabPanel.getActiveTab().items.items[0].onPortalAfterLayout(tabPanel.getActiveTab().items.items[0]);}afApp.changeTabHash(tab);"));

				$attributesPanel['title']=$attributesTabPanel['title'];
				unset($attributesTabPanel['title']);
				
				if(isset($this->attributes['tools']))
				{
				    if(!afExtjs::getInstance()->isDesktop())
				    {
				        $attributesPanel['tools']=$this->attributes['tools']->end();
				    }
				    else {
				        $attributesTabPanel['winTools']=$this->attributes['tools']->end();
				        unset($attributes['tools']);
				    }
				}
				
				if(afExtjs::getInstance()->isDesktop())
				{
				   $attributesTabPanel['winTitle'] = $attributes['title'];
				}
				
				//$attributesTabPanel['id']='center_panel_first_panel';
				$this->afExtjs->private['center_panel_first_panel']=$this->afExtjs->TabPanel($attributesTabPanel);
				
				$attributesPanel['items'][]=$this->afExtjs->asVar('center_panel_first_panel');
				$attributesPanel['border']=true;
				$attributesPanel['bodyBorder']=true;
				$attributesPanel['layout']='fit';
								
				$attributesPanel['id']='center_panel_first';
				$this->afExtjs->privateAttributes['container']=$attributesPanel;
				$this->afExtjs->private['center_panel_first']=$this->afExtjs->Panel($attributesPanel);
				
				$attributesPanelContainer['items'][]=$this->afExtjs->asVar('center_panel_first');
				$attributesPanelContainer['style']='padding-right:5px;';
				$attributesPanelContainer['border']=false;
				$attributesPanelContainer['bodyBorder']=false;
				$attributesPanelContainer['layout']='fit';
				$attributesPanelContainer['id']='center_panel';         	      	
				
				$this->addPanel('center',$attributesPanelContainer);
				break;
		}
	}
	
	public function end()
	{
		
		$this->addSouthComponent();
        if(!sfConfig::get('app_parser_skip_west')){
    		sfProjectConfiguration::getActive()->loadHelpers(array('afExtjsWest'));
        }
		
		if($this->showFullCenter())
		{
			$this->beforeEnd();
		}
		
		parent::end();
	}
}
?>
