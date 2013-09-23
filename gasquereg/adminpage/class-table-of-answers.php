<?php
/* Class based on Matt Van Andel's example plugin 'Custom List Table Example' */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Table_Of_Answers extends WP_List_Table {
    protected $formId;
	protected $elements;
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
        return $item[$column_name];
    }
	function column_date($item) {
		return $item['date'];
	}
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['formId']                //The value of the checkbox should be the record's id
        );
    }
	function get_columns(){
        $columns = array();
		foreach($this->elements as $element) {
			$columns['form_elem'.$element->id] = explode(';',$element-> description)[0];
		}
		$columns['date'] = "Svarsdatum";
        return $columns;
    }
	function get_sortable_columns() {
        $sortable_columns = array();
		foreach($this->elements as $element) $sortable_columns['form_elem'.$element->id] = array('form_elem'.$element->id,false);
        $sortable_columns['date'] = array("date",false);
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
	function queryAndPivotData() {
		global $wpdb;
		$formsTableName = $wpdb->prefix.'gasquereg_forms';
		$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
		$answersTableName = $wpdb->prefix.'gasquereg_answers';
		$answerElementsTableName = $wpdb->prefix.'gasquereg_answer_elements';
		//Get (general) data from answers table
		$answers = $wpdb->get_results('SELECT id AS answerId,user,submitted AS date FROM '.$answersTableName.' WHERE form = '.$this->formId,ARRAY_A);
		//No answers at all, might as well return an empty array straight away!
		if(count($answers) <= 0) return array();
		//Make the data fit the new data structure
		foreach($answers as $answer) $assoc_data[$answer['answerId']] = $answer;
        
		//Get detailed and actual data from answer elements table
		$query = 'SELECT '
					.$answerElementsTableName.'.val AS val,'
					.$answerElementsTableName.'.element AS elem,'
					.$answersTableName.'.id AS answerId '
					.'FROM '.$answerElementsTableName.','.$answersTableName.' '
					.'WHERE '.$answersTableName.'.id = '.$answerElementsTableName.'.answer '
					.'AND '.$answersTableName.'.form = '.$this->formId;
		$answer_elems = $wpdb->get_results($query);		
		foreach($answer_elems as $answer_elem) {
			$assoc_data[$answer_elem->answerId]['form_elem'.$answer_elem->elem] = $answer_elem->val;
		}
		//Convert the data from accociative to numbered, i.e. remove id as key.
		return array_values($assoc_data);
	}
    function prepare_items() {
        global $wpdb;
		
		$this->formId = (int)$_GET['form'];
		$this->elements = $wpdb->get_results('SELECT id,description,type FROM '.$wpdb->prefix.'gasquereg_form_elements WHERE form = '.$this->formId.' ORDER BY order_in_form');
		
        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
		
        $current_page = $this->get_pagenum();

		//Get and parse the data
		$data = $this->queryAndPivotData();
		
		//Sort the data according to the passed parameters
		function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        //Manage pagination
		$total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        //Apply data
        $this->items = $data;
        
        //Pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }   
}