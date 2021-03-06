<?php
/**
 * extJs panel
 *
 */
class afExtjsPanel
{
	/**
	 * default attributes for the window
	 */
	public $attributes=array('border'=>false,'header'=>false,'style'=>'padding:5px;','idxml'=>false);
	public $menuactions_items = array();
	
	public $afExtjs=null;	
	public $privateName=null;
							
	public function __construct($attributes=array())
	{		
		
		$this->afExtjs=afExtjs::getInstance();
		$this->afExtjs->setAddons(array('js'=>array('/appFlowerPlugin/js/custom/cheatJS.js')));
		$this->privateName='panel_'.Util::makeRandomKey();
		
		$this->attributes['id']=$this->privateName;
		
		if(isset($attributes['idxml'])&&$attributes['idxml'])
		{
			$this->attributes['id']=$attributes['idxml'];
		}
		
		if(isset($attributes['plugins'])){			
			$this->attributes['plugins'][] = $attributes['plugins'];
			unset($attributes['plugins']);			
		}				
		if(isset($attributes['portal'])&&$attributes['portal']==true)
		{
			
			$this->attributes=array_merge($this->attributes,array('anchor'=> '100%',
															'frame'=>true,
															'collapsible'=>true,
															'draggable'=>true,
															'maximizable'=>true,
															
															'cls'=>'x-portlet'));
			$this->attributes['plugins'][] = 'new Ext.ux.MaximizeTool()';												
			unset($attributes['portal']);
		}
		
		if(isset($attributes['tools']))
		{
			$this->attributes['tools']=$attributes['tools']->end();
			
			unset($attributes['tools']);
		}
		
		$this->attributes['getWidgetConfig']=$this->afExtjs->asMethod("var o={}; o.idxml=this.idxml || false; return o;");
		
		if(count($attributes)>0)
		$this->attributes=array_merge($this->attributes,$attributes);
		
		$this->attributes['listeners']['bodyresize']=$this->afExtjs->asMethod(array(
																			"parameters"=>"object,layout",
																			"source"=>"if(object.ownerCt.ownerCt)object.ownerCt.ownerCt.doLayout();"
																	));
		
		if((isset($attributes['autoEnd'])&&$attributes['autoEnd'])||!isset($attributes['autoEnd']))
		{
			$this->end();
		}
	}
	
	public function addMenuActionsItem($attributes)
	{			
		$this->menuactions_items[]=$attributes;			
	}
	
	public function addMenuActions()
	{
		
		if(count($this->menuactions_items)>0)
		{		
			
			/**
			 * Fill to move menuactions button to the right
			 */
			new afExtjsToolbarFill($this);
			
			$menuactions_button=new afExtjsToolbarButton($this,array('label'=>'More Actions'));
			$menuactions_menu=new afExtjsToolbarMenu($menuactions_button);		
			
			foreach ($this->menuactions_items as $attributes)
			{
				$item=new afExtjsToolbarMenuItem($menuactions_menu,$attributes);$item->end();
			}		
			
			$menuactions_menu->end();
			$menuactions_button->end();
		}
	}
	
	public function addHelp($html)
	{
		if(!isset($this->attributes['tbar']))
		{
			$this->attributes['tbar']=array();
		}
		
		$panel=new afExtjsPanel(array('html'=>'<div style="white-space:normal;">'.$html.'</div>','listeners'=>array('render'=>$this->afExtjs->asMethod(array("parameters"=>"panel","source"=>"if(panel.body){panel.body.dom.style.border='0px';panel.body.dom.style.background='transparent';}")))));
		$this->attributes['listeners']['render']=$this->afExtjs->asMethod("var tb;if(this.getTopToolbar()&&this.getTopToolbar().items.items.length==0){tb = this.getTopToolbar();tb.addItem(".$panel->privateName.");}else{ tb = new Ext.Toolbar({renderTo: this.tbar,items: [".$panel->privateName."]});}if(tb&&tb.container){tb.container.addClass('tbarBottomBorderFix');}");
	}
		
	public function addMember($item)
	{
		$this->attributes['items'][]=$this->afExtjs->asAnonymousClass($item);
	}
	
	public function addButton($button)
	{
		if(!isset($this->attributes['tbar']))
		$this->attributes['tbar']=array();
		
		if(is_object($button))
		{
			array_push($this->attributes['tbar'],$this->afExtjs->asVar($button->end()));
		}
		else {
			array_push($this->attributes['tbar'],$this->afExtjs->asAnonymousClass($button));
			//print_r($this->attributes);
		}
	}
	
	public function end()
	{			
		$this->addMenuActions();
		$this->afExtjs->private[$this->privateName]=$this->afExtjs->Panel($this->attributes);
	}
}
?>
