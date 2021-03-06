<?php
if( !function_exists('buildMenuTree_top') ){
	function buildMenuTree_top( $menu, $level = 0 ){
		$menu = (array)$menu;
		$result = '';
		if ( $level ) $result .= "<ul class='dropdown-menu'>\r\n";
		$registry = Registry::getInstance();
		$logged = $registry->get('customer')->isLogged();

		foreach( $menu as $item ) {
			if(($logged && $item['id']=='login')
				||	(!$logged && $item['id']=='logout')){
				continue;
			}

			$id = ( empty($item['id']) ? '' : ' data-id="menu_'.$item['id'].'" ' ); // li ID

			if($level != 0){
				if(empty($item['children'])){
					$class='';
				}else{
					$class = $item['icon']? ' class="parent" style="background-image:none;" ' : ' class="parent menu_'.$item['id'].'" ';
				}
			}else{
				$class = $item['icon'] ? ' class="top" style="background-image:none;" ' : ' class="top menu_'.$item['id'].'" ';
			}

			$href = empty($item['href']) ? '' : ' href="'.$item['href'].'" '; //a href

			$result .= '<li ' . $id . ' class="dropdown hover">';
			$result .= '<a ' . $class . $href . '>';
			$result .= $item['icon'] ? '<img src="'. HTTP_DIR_RESOURCE . $item['icon'].'" alt="" />' : '';
			$result .= '<span>' . $item['text'] . '</span></a>';

			if ( !empty($item['children']) ) $result .= "\r\n" . buildMenuTree_top($item['children'], $level+1) ;
			$result .= "</li>\r\n";
		}
		if ( $level ) $result .= "</ul>\r\n";
		return $result;
	}
}
?>
<div class="navbar" id="topnav">
    <div class="navbar-inner">
		<ul id="main_menu_top" class="nav main_menu">
		<?php
			foreach ($storemenu as $i => $menu_item) {
		 	   if ($menu_item['id'] == 'home') {
		    		unset($storemenu[$i]);
		    		break;
		    	}
			}?>
		    <?php echo buildMenuTree_top( $storemenu ); ?>
		</ul>
    </div>
</div>

