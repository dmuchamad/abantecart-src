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
class ControllerPagesDesignMenu extends AController {
	public $data = array ();
	private $error = array ();
	private $columns = array ('item_id', 'item_icon', 'item_text', 'item_url', 'parent_id', 'sort_order' );
	/**
	 * @var AMenu_Storefront
	 */
	private $menu;

	public function main() {

		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);

		$this->document->setTitle( $this->language->get ( 'heading_title' ) );

		$this->document->initBreadcrumb( array(
			'href' => $this->html->getSecureURL ( 'index/home' ),
			'text' => $this->language->get ( 'text_home' ),
			'separator' => FALSE,
		));
		$this->document->addBreadcrumb( array (
		     'href' => $this->html->getSecureURL ( 'design/menu' ),
		     'text' => $this->language->get ( 'heading_title' ),
		     'separator' => ' :: ',
		));

		$this->menu = new AMenu_Storefront();
		$menu_parents = $this->menu->getItemIds();

		$menu_id = array ('' => $this->language->get ( 'text_select_parent_id' ) );
		foreach ( $menu_parents as $item ) {
			if ($item != '') {
				$menu_id[$item] = $item;
			}
		}

		$grid_settings = array (
			'table_id' => 'menu_grid',
			'url' => $this->html->getSecureURL ( 'listing_grid/menu', '&parent_id=' . $this->request->get ['parent_id'] ),
			'editurl' => $this->html->getSecureURL ( 'listing_grid/menu/update' ),
			'update_field' => $this->html->getSecureURL ( 'listing_grid/menu/update_field' ),
			'sortname' => 'sort_order',
			'sortorder' => 'asc',
			'drag_sort_column' => 'sort_order',
			'columns_search' => false,
			'actions' => array (
				'edit' => array ( 'text' => $this->language->get ( 'text_edit' ), 'href' => $this->html->getSecureURL ( 'design/menu/update', '&item_id=%ID%' ) ),
				'delete' => array ('text' => $this->language->get ( 'button_delete' ) ),
				'save' => array ('text' => $this->language->get ( 'button_save' ) )
			)
		);

		$form = new AForm ();
		$form->setForm ( array ('form_name' => 'menu_grid_search' ) );

		$grid_search_form = array();
		$grid_search_form['id'] = 'menu_grid_search';
		$grid_search_form['form_open'] = $form->getFieldHtml ( array ('type' => 'form',
		                                                                'name' => 'menu_grid_search',
		                                                                'action' => '' ) );
		$grid_search_form['submit'] = $form->getFieldHtml ( array ('type' => 'button',
		                                                          'name' => 'submit',
		                                                          'text' => $this->language->get ( 'button_go' ), 'style' => 'button1' ) );
		$grid_search_form['reset'] = $form->getFieldHtml ( array ('type' => 'button',
		                                                         'name' => 'reset',
		                                                         'text' => $this->language->get ( 'button_reset' ), 'style' => 'button2' ) );
		$grid_search_form['fields']['parent_id'] = $form->getFieldHtml ( array ('type' => 'selectbox',
		                                                                       'name' => 'parent_id',
		                                                                       'options' => $menu_id,
		                                                                       'value' => $this->request->get ['parent_id'] ) );

		$grid_settings['search_form'] = true;

		$grid_settings['colNames'] = array (
			'',
			$this->language->get ( 'entry_item_id' ),
			$this->language->get ( 'entry_item_text' ),
			$this->language->get ( 'entry_sort_order' )
		);
		$grid_settings['colModel'] = array (
			array ('name' => 'item_icon',
			       'index' => 'item_icon',
			       'width' => 80,
			       'align' => 'center',
			       'sortable' => false,
			       'search' => false ),
			array ('name' => 'item_id',
			       'index' => 'item_id',
			       'width' => 120,
			       'align' => 'left',
			       'search' => false ),
			array ('name' => 'item_text',
			       'index' => 'item_text',
			       'width' => 360,
			       'align' => 'center',
			       'search' => false ),
			array ('name' => 'sort_order',
			       'index' => 'sort_order',
			       'align' => 'center',
			       'search' => false )
		);

		if ( $this->config->get('config_show_tree_data') ) {
			$grid_settings[ 'expand_column' ] = "item_id";	
			$grid_settings[ 'multiaction_class' ] = 'hidden';	
		}

		$grid = $this->dispatch ( 'common/listing_grid', array ($grid_settings ) );
		$this->view->assign ( 'listing_grid', $grid->dispatchGetOutput () );
		$this->view->assign ( 'search_form', $grid_search_form );

		$this->view->batchAssign (  $this->language->getASet () );
		$this->view->assign ( 'insert', $this->html->getSecureURL ( 'design/menu/insert' ) );
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('help_url', $this->gen_help_url('menu_listing') );

		$this->processTemplate ( 'pages/design/menu.tpl' );
		//update controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	public function insert() {

		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);

		$this->document->setTitle ( $this->language->get ( 'heading_title' ) );

		$this->menu = new AMenu_Storefront();

		if (($this->request->server ['REQUEST_METHOD'] == 'POST') && $this->_validateForm() ) {

    	    $languages = $this->language->getAvailableLanguages();
		    foreach ( $languages as $l ) {
			    if ( $l['language_id'] == $this->session->data['content_language_id'] ) continue;
			    $this->request->post['item_text'][$l['language_id']] = $this->request->post['item_text'][ $this->session->data['content_language_id'] ];
		    }

            $this->request->post['item_icon'] = html_entity_decode($this->request->post['item_icon'], ENT_COMPAT, 'UTF-8');
            $textid = preformatTextID($this->request->post ['item_id']);
			$result = $this->menu->insertMenuItem ( array (
				'item_id' => $textid,
				'item_icon' => $this->request->post ['item_icon'],
				'item_text' => $this->request->post ['item_text'],
				'parent_id' => $this->request->post ['parent_id'],
				'item_url' => $this->request->post ['item_url'],
				'sort_order' => $this->request->post ['sort_order'],
				'item_type' => 'core',
			));

			if ($result !== true) {
				$this->error ['warning'] = $result;
			} else {
				$this->session->data ['success'] = $this->language->get ( 'text_success' );
				$this->redirect ( $this->html->getSecureURL ( 'design/menu/update', '&item_id=' . $textid) );
			}
		}

		$this->_getForm ();

		//update controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	public function update() {

		//init controller data
		$this->extensions->hk_InitData($this,__FUNCTION__);

		$this->document->setTitle ( $this->language->get ( 'heading_title' ) );

		$this->menu = new AMenu_Storefront();

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		if (($this->request->server ['REQUEST_METHOD'] == 'POST') && $this->_validateForm ()) {

            if (isset ( $this->request->post ['item_icon'] )) {
                $this->request->post['item_icon'] = html_entity_decode($this->request->post['item_icon'], ENT_COMPAT, 'UTF-8');
            }

			$item_keys = array('item_icon', 'item_text', 'item_url', 'parent_id', 'sort_order' );

			$update_item = array();

			if ($this->request->get['item_id']) {

				foreach ( $item_keys as $item_key ) {
					if (isset ( $this->request->post [$item_key] )) {
						$update_item [$item_key] = $this->request->post [$item_key];
					}
				}
				// set condition for updating row
				$this->menu->updateMenuItem( $this->request->get ['item_id'], $update_item );

			}
			$this->session->data ['success'] = $this->language->get ( 'text_success' );
			$this->redirect ( $this->html->getSecureURL ( 'design/menu/update', '&item_id=' . $this->request->get ['item_id'] ) );
		}

		$this->_getForm ();

		//update controller data
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	private function _getForm() {

		if (isset ( $this->error ['warning'] )) {
			$this->data ['error_warning'] = $this->error ['warning'];
		} else {
			$this->data ['error_warning'] = '';
		}

		$this->data ['error'] = $this->error;

		$this->document->initBreadcrumb ( array ('href' => $this->html->getSecureURL ( 'index/home' ), 'text' => $this->language->get ( 'text_home' ), 'separator' => FALSE ) );
		$this->document->addBreadcrumb ( array ('href' => $this->html->getSecureURL ( 'design/menu' ), 'text' => $this->language->get ( 'heading_title' ), 'separator' => ' :: ' ) );

		$this->data ['cancel'] = $this->html->getSecureURL ( 'design/menu' );

		$menu_item = null;
		$parent_id = array();
		$menu_ids = $this->menu->getItemIds();
		foreach ( $menu_ids as $v ) {
			$parent_id[$v] = $v;
		}
		if ( isset($this->request->get ['item_id']) ) {
			$menu_item = $this->menu->getMenuItem( $this->request->get ['item_id'] );
			unset($parent_id[ array_search($this->request->get ['item_id'], $parent_id) ]);
		}

		foreach ( $this->columns as $column ) {

			if (isset ( $this->request->post [$column] )) {
				$this->data [$column] = $this->request->post [$column];
			} elseif (!empty( $menu_item )) {
				$this->data [$column] = $menu_item [$column];
			} else {
				$this->data [$column] = '';
			}
		}

		if (! isset ( $this->request->get ['item_id'] )) {
			$this->data ['action'] = $this->html->getSecureURL ( 'design/menu/insert' );
			$this->data ['heading_title'] = $this->language->get('text_insert') . '&nbsp;' . $this->language->get('heading_title');
			$this->data ['update'] = '';
			$form = new AForm ( 'ST' );
		} else {

			//do not allow to edit item_id
			$this->data['item_id'] = $menu_item['item_id'];

			$this->data ['action'] = $this->html->getSecureURL ( 'design/menu/update', '&item_id=' . $this->request->get ['item_id'] );
			$this->data ['heading_title'] = $this->language->get('text_edit') . $this->language->get('heading_title');
			$this->data ['update'] = $this->html->getSecureURL ( 'listing_grid/menu/update_field', '&id=' . $this->request->get ['item_id'] );
			$form = new AForm ( 'HS' );
		}

		$this->document->addBreadcrumb( array (
       		'href'      => $this->data['action'],
       		'text'      => $this->data['heading_title'],
      		'separator' => ' :: '
   		 ));

		$form->setForm ( array ('form_name' => 'menuFrm', 'update' => $this->data ['update'] ) );

		$this->data['form']['form_open'] = $form->getFieldHtml ( array ('type' => 'form',
		                                                                'name' => 'menuFrm',
		                                                                'attr' => 'confirm-exit="true"',
		                                                                'action' => $this->data ['action'] ) );
		$this->data['form']['submit'] = $form->getFieldHtml ( array ('type' => 'button',
		                                                             'name' => 'submit',
		                                                             'text' => $this->language->get ( 'button_save' ), 'style' => 'button1' ) );
		$this->data['form']['cancel'] = $form->getFieldHtml ( array ('type' => 'button',
		                                                             'name' => 'cancel',
		                                                             'text' => $this->language->get ( 'button_cancel' ), 'style' => 'button2' ) );

		if (! isset ( $this->request->get ['item_id'] )) {
			$this->data['form']['fields']['item_id'] = $form->getFieldHtml ( array ('type' => 'input',
			                                                                        'name' => 'item_id',
			                                                                        'value' => $this->data ['item_id'],
			                                                                        'required' => true ) );
		} else {
			$this->data['form']['fields']['item_id'] = $this->data ['item_id'];
		}

		$this->data['form']['fields']['item_text'] = $form->getFieldHtml(
            array(
                'type' => 'input',
                'name' => 'item_text['.$this->session->data['content_language_id'].']',
                'value' => $this->data['item_text'][$this->session->data['content_language_id']],
                'required' => true,
                'style' => 'large-field',
	    ));
		$this->data['form']['fields']['item_url'] = $form->getFieldHtml (
            array (
                'type' => 'input',
                'name' => 'item_url',
                'value' => $this->data ['item_url'],
                'style' => 'large-field',
	            'help_url' => $this->gen_help_url('item_url'),
        ) );
		$this->data['form']['fields']['parent_id'] = $form->getFieldHtml (
            array (
                'type' => 'selectbox',
                'name' => 'parent_id',
                'options' => array_merge ( array ('' => $this->language->get ( 'text_none' ) ), $parent_id ),
                'value' => $this->data ['parent_id'],
                'style' => 'medium-field',
        ) );
		$this->data['form']['fields']['sort_order'] = $form->getFieldHtml (
            array (
                'type' => 'input',
                'name' => 'sort_order',
                'value' => $this->data ['sort_order'],
                'style' => 'small-field',
        ) );
        $this->data['form']['fields']['item_icon'] = $form->getFieldHtml(
            array(
                'type' => 'hidden',
                'name' => 'item_icon',
                'value' => htmlspecialchars($this->data['item_icon'], ENT_COMPAT, 'UTF-8'),
            )
        );

		$this->loadModel ( 'catalog/category' );
		$categories = $this->model_catalog_category->getCategories ( 0 );
		$options = array ('' => $this->language->get ( 'text_select' ) );
		foreach ( $categories as $c ) {
			if (! $c ['status'])
				continue;
			$options [$c ['category_id']] = $c ['name'];
		}
		$this->data ['categories'] = $this->html->buildSelectbox (
            array (
                'type' => 'selectbox',
                'name' => 'menu_categories',
                'options' => $options,
                'style' => 'no-save large-field',
        ) );

        $acm = new AContentManager();
        $results = $acm->getContents();
		$options = array ('' => $this->language->get ( 'text_select' ) );
        foreach ( $results as $c ) {
            if (! $c ['status'])
                continue;
			$options [$c ['content_id']] = $c ['title'];
        }

		$this->data ['pages'] = $this->html->buildSelectbox (
            array (
                'type' => 'selectbox',
                'name' => 'menu_information',
                'options' => $options,
                'style' => 'no-save large-field',
        ) );

		$this->data ['button_link'] = $this->html->buildButton (
            array (
                'type' => 'button',
                'name' => 'submit',
                'text' => $this->language->get ( 'text_link' ),
                'style' => 'button1'
        ) );

		$resource = new AResource( 'image' );
		$this->data['icon'] = $this->dispatch(
					'responses/common/resource_library/get_resource_html_single',
				array('type'=>'image',
					  'wrapper_id'=>'item_icon',
					  'resource_id'=> $resource->getIdFromHexPath(str_replace('image/','',$menu_item['item_icon'])),
					  'field' => 'item_icon'));
		$this->data['icon'] = $this->data['icon']->dispatchGetOutput();

        $resources_scripts = $this->dispatch(
                   'responses/common/resource_library/get_resources_scripts',
                    array(
                        'object_name' => 'storefront_menu_item',
                        'object_id' => $this->request->get['item_id'],
                        'types' => 'image',
                        'mode' => 'url'
                    )
                );
        $this->data['resources_scripts'] = $resources_scripts->dispatchGetOutput();

		$this->view->batchAssign (  $this->language->getASet () );
		$this->view->batchAssign ( $this->data );
		$this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
		$this->view->assign('help_url', $this->gen_help_url('menu_edit') );
		$this->processTemplate ( 'pages/design/menu_form.tpl' );
	}

	private function _validateForm() {
		if (! $this->user->canModify('design/menu' )) {
			$this->error['warning'] = $this->language->get ( 'error_permission' );
		}

		if ( !empty($this->request->post ['item_id']) ) {
			$ids = $this->menu->getItemIds ();
			if (!ctype_alnum($this->request->post['item_id'])) {
				$this->error['item_id'] = $this->language->get ( 'error_non_ascii' );
			} else if (in_array ( $this->request->post['item_id'], $ids )) {
				$this->error['item_id'] = $this->language->get ( 'error_non_unique' );
			}
		}
		if (empty ($this->request->post['item_id'] ) && empty ($this->request->get['item_id'] )) {
			$this->error['item_id'] = $this->language->get ( 'error_empty' );
		}

		if (empty ( $this->request->post ['item_text'][$this->session->data['content_language_id']] )) {
			$this->error['item_text'] = $this->language->get ( 'error_empty' );
		}

		if (! $this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}