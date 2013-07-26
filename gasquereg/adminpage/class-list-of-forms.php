<?php
/* Class based on Matt Van Andel's example plugin 'Custom List Table Example' */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class List_Of_Forms extends WP_List_Table {
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'formulär',     //singular name of the listed records
            'plural'    => 'formulär',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }
    function column_default($item, $column_name){
        switch($column_name){
            case 'title':
            case 'author':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&form=%s">Redigera</a>',$_REQUEST['page'],'edit',$item['formId'])//,
            //'delete'    => sprintf('<a href="?page=%s&action=%s&form=%s">Radera</a>',$_REQUEST['page'],'delete',$item['ID'])
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['formId'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    function column_shortcode($item){
		return '<bb>[gasque_form id='.$item['formId'].']</bb>';
    }
	function column_date($item){
		return $item['date'];
    }
	function column_num_ans($item){
        
        //Build row actions
        $actions = array(
            'show_answers'      => sprintf('<a href="?page=%s&action=%s&form=%s">Visa svar</a>',$_REQUEST['page'],'answers',$item['formId'])
        );
        
		return $item['num_ans'].$this->row_actions($actions);
    }
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['formId']                //The value of the checkbox should be the record's id
        );
    }
	function get_columns(){
        $columns = array(
            //'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Titel',
			'num_ans' => 'Antal svar',
			'shortcode'  => 'Kod',
            'author'    => 'Skapat av',
            'date'    => 'Datum'
        );
        return $columns;
    }
	function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
			'num_ans' => array('num_ans',false),
			'author'    => array('author',false),
			'date'    => array('date',false)
        );
        return $sortable_columns;
    }
    function get_bulk_actions() {
        $actions = array(
            //'delete'    => 'Radera'
        );
        return $actions;
    }
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }
    function prepare_items() {
        global $wpdb;
		
        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
		
		$formsTableName = $wpdb->prefix.'gasquereg_forms';
		$answersTableName = $wpdb->prefix.'gasquereg_answers';
        $current_page = $this->get_pagenum();
		$order_fields = array(
			'date' => $formsTableName.'.createdTime',
			'title'=> $formsTableName.'.title',
			'author'=> $wpdb->users.'.display_name',
			'num_ans' => 'num_ans'
		);
		if(empty($_REQUEST['orderby']) || !array_key_exists($_REQUEST['orderby'],$order_fields)) $orderby = $order_fields['date'];
		else $orderby = $order_fields[$_REQUEST['orderby']];
		
		$query = 'SELECT '.$formsTableName.'.title AS title, '.
							$formsTableName.'.id AS formId,'.
							$formsTableName.'.createdTime AS date,'.
							$wpdb->users.'.display_name AS author, '.
							'COUNT('.$answersTableName.'.id) AS num_ans '.
							'FROM '.$wpdb->users.', '.$formsTableName.' '.
							'LEFT JOIN '.$answersTableName.' ON '.$answersTableName.'.form = '.$formsTableName.'.id '.
							'WHERE '.$formsTableName.'.createdBy = '.$wpdb->users.'.ID '.
							'GROUP BY '.$formsTableName.'.id '.
							'ORDER BY '.$orderby;
		if($_REQUEST['order'] == 'desc') $query .= ' DESC';
		$query .= sprintf(' LIMIT %d,%d',($current_page-1)*$per_page,$per_page);
		$data = $wpdb->get_results($query,ARRAY_A);
		/*print($query);
		print('<br>');
		print($wpdb->last_error);*/
        
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = $wpdb->get_var('SELECT COUNT(*) FROM '.$formsTableName);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}