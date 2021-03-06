<?php   
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2014 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ControllerCommonHead extends AController {
	public function main() {

		//use to init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->load->helper('html');
        $this->loadLanguage('common/header');

		$this->view->assign('title', $this->document->getTitle());		
		$this->view->assign('base', (HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER);
		$this->view->assign('links', $this->document->getLinks());
		$this->view->assign('styles', $this->document->getStyles());
		$this->view->assign('scripts', $this->document->getScripts());

		$icon_path = $this->config->get('config_icon');
		if( $icon_path){
			if(!is_file(DIR_RESOURCE.$this->config->get('config_icon'))){
				$this->messages->saveWarning('Check favicon.','Warning: please check favicon in your store settings. Current path is "'.DIR_RESOURCE.$this->config->get('config_icon').'" but file does not exists.');
				$icon_path ='';
			}
		}
		$this->view->assign('icon', $icon_path);
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
		    $this->view->assign('ssl', 1);
        }
		
		$this->processTemplate('common/head.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

	}	
	
}