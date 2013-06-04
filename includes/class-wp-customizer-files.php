<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('WP_CustomizerFiles')) {
	class WP_CustomizerFiles extends WP_List_Table {
		private static $file_types;
		
		private	$type;
		
		function __construct($path, $type) {
			self::$file_types = array(
				'css' => array(
					'label_singular' => 'CSS',
					'label_plural' => 'CSS',
					'type' => 'css'
				),
				'functions' => array(
					'label_singular' => 'Function',
					'label_plural' => 'Functions',
					'type' => 'php'
				),
				'scripts' => array(
					'label_singular' => 'Script',
					'label_plural' => 'Scripts',
					'type' => 'js'
				)
			);
			
			parent::__construct(array(
				'singular' => 'file',
				'plural' => 'files',
				'ajax' => false
			));
			
			$this->path = $path;
			$this->type = $type;
		}
		
		function column_default($item, $column) {
			return $item[$column];
		}
		
		function column_cb($item) {
			$fmt = '<input type="checkbox" name="wp_customizer_%1$s_enabled[]" id="wp-customizer-enabled-%1$s" value="%2$s" />';
			return sprintf($fmt, $this->_args['singular'], $item['file']);
		}

		function column_dependencies($item) {
			$fmt = '<input type="text" name="wp-customizer-dependencies-%1$s[]" id="wp-customizer-dependencies-%1$s" value="" />';
			return sprintf($fmt, $this->_args['singular']);
		}
		
		function get_columns() {
			return array(
				'cb' => '<input type="checkbox" />',
				'file' => __('File', 'wp-customizer'),
				'url' => __('URL', 'wp-customizer'),
				'dependencies' => __('Dependencies', 'wp-customizer')
			);
		}
		
		function get_sortable_columns() {
			return array(
				'file' => array('file', false),
				'url' => array('url', false),
				'dependencies' => array('dependencies', false)
			);
		}

		function prepare_items() {
			$per_page = 5;
			
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			
			$this->_column_headers = array($columns, $hidden, $sortable);
			//$this->process_bulk_action();
			
			$path = ABSPATH . $this->path;
			$glob = $path . DIRECTORY_SEPARATOR . '*.' . self::$file_types[$this->type]['type'];
			$files = glob($glob);
			$data = array();
			foreach ($files as $file) {
				$data[] = array(
					'file' => basename($file),
					'url' => network_site_url($this->path) . DIRECTORY_SEPARATOR . basename($file),
					'dependencies' => ''
					);
			}
			
			$current_page = $this->get_pagenum();
			$total_items = count($data);
			$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
			$this->items = $data;
			
			$this->set_pagination_args(array(
				'total_items' => $total_items,
				'per_page' => $per_page,
				'total_pages' => ceil($total_items / $per_page)
				));
		}
		
		function display_rows() {
			$items = $this->items;
			list($columns, $hidden) = $this->get_column_info();
			if (!empty($items)) {
				foreach ($items as $item) {
					echo '<tr class="alternate">';
					foreach ($columns as $column_name => $column_display_name) {
						$class = 'class="' . $column_name . ' column-' . $column_name . '"';
						$style = '';
						
						if (in_array($column_name, $hidden)) {
							$style = ' style="display: none;"';
						}

						$attrs = $class . $style;
						
						switch ($column_name) {
							case 'cb':
								echo '<th scope="row" class="check-column">';
								echo $this->column_cb($item);
								echo '</th>';
								break;
							case 'file':
								echo '<td ' . $attrs . '>' . $item['file'] . '</td>';
								break;
							case 'url':
								echo '<td ' . $attrs . '>' . $item['url'] . '</td>';
								break;
							case 'dependencies':
								echo '<td ' . $attrs . '>';
								echo $this->column_dependencies($item);
								echo '</td>';
								break;
							default:
								break;
						}
					}
					echo '</tr>';
				}
			}
		}
	}	// end-class(...)
}	// end-if(!class_exists(...))

?>