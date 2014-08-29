<?php

class TableBuilder {

    protected $_columns = array();
    
    protected $_actions = array();
    
    protected $_rows = array();
    
    public function __construct($columns, $rows = array(), $actions = array()) {
        $this->_columns = $columns;
        $this->_rows = $rows;
        $this->_actions = $actions;
    }
    
    public function addRow($row){
        $this->_rows[] = $row;
    }        
        
    public function render(){
        // Generate initial sort
        $initial_sort = "[";
        $i = 0;
        foreach($this->_columns as $column_name => $column) {
            if (isset($column['sort'])){
                if ($column['sort'] == "asc")
                    $initial_sort .= "[$i, 0]";
                else if ($column['sort'] == "desc")
                    $initial_sort .= "[$i, 1]";
            }
            $i++;
        }
        $initial_sort .= "]";  
        
        // Render header rows
        $result = "
        <div class='table-responsive'>
            <table class='tablesorter table table-bordered table-hover table-striped tablesorter-bootstrap' data-sortlist='$initial_sort'>
                <thead><tr>";
        foreach($this->_columns as $column_name => $column) {
            if (isset($column['sorter'])){
                $sorter = 'sorter-' . $column['sorter'];
            } else {
                $sorter = "";
            }
        
            $result .= "<th class=$sorter>{$column['label']} <i class='fa fa-sort'></i></th>";
        }
        
        // Add action column, if specified
        if (!empty($this->_actions)){
            $result .= "<th>Status/Actions <i class='fa fa-sort'></i></th>";
        }
        
        $result .= "</tr></thead><tbody>";
        
        // Render data rows
        foreach ($this->_rows as $row_id => $row) {
            // Render rows
           $result .= $this->renderRow($row_id);
        }
        
        // Close table
        $result .= "</tbody></table>";
        
        // Render paging controls
        $result .= "
            <div class='pager pager-lg'>
                <span class='pager-control first' title='First page'><i class='fa fa-angle-double-left'></i></span>
                <span class='pager-control prev' title='Previous page'><i class='fa fa-angle-left'></i></span>
                <span class='pagedisplay'></span> <!-- this can be any element, including an input -->
                <span class='pager-control next' title='Next page'><i class='fa fa-angle-right'></i></span>
                <span class='pager-control last' title= 'Last page'><i class='fa fa-angle-double-right'></i></span>
                <br><br>
                Jump to Page: <select class='gotoPage'></select>
                &bull; Show: <select class='pagesize'>
                    <option value='2'>2</option>
                    <option value='5'>5</option>
                    <option value='10'>10</option>
                    <option value='100'>100</option>
                </select>
            </div>";
        
        
        return $result;
    }

    private function renderRow($row_id){
        $row = $this->_rows[$row_id];
    
        $result = "<tr>";
         foreach($this->_columns as $column_name => $column) {
             $result .= $this->renderCell($row_id, $column_name);
         }
         
         // Build action menu
         if (!empty($this->_actions)){
             $menu = "";
             foreach ($this->_actions as $action_name => $action) {
                 if ($action_name == "user_activate"){
                     if ($row['active'] == '0') {
                         $menu .= "
                             <li>
                                 <a href='#' data-id='{$row_id}' class='btn-activate-user'><i class='fa fa-bolt'></i> {$action['label']}</a>
                             </li>
                             <li class='divider'>
                             </li>";
                     }
                 }
 
                 if ($action_name == 'user_edit') {
                     $menu .= "
                         <li>
                             <a href='#' data-target='#user-update-dialog' data-toggle='modal' data-id='{$row_id}' class='btn-edit-user'><i class='fa fa-edit'></i> {$action['label']}</a>
                         </li>
                         <li class='divider'>
                         </li>";            
                 }
                 
                 if ($action_name == 'user_disable') {
                     if ($row['enabled'] == '1') {
                         $menu .= "
                             <li>
                                 <a href='#' data-id='{$row_id}' class='btn-disable-user'><i class='fa fa-minus-circle'></i> {$action['label-disable']}</a>
                             </li>";
                     } else {
                         $menu .= "
                             <li>
                                 <a href='#' data-id='{$row_id}' class='btn-enable-user'><i class='fa fa-plus-circle'></i> {$action['label-enable']}</a>
                             </li>";
                     }
                 }
                 
                 if ($action_name == 'user_delete') {
                     $menu .= "
                         <li>
                             <a href='#' data-target='#user-delete-dialog' data-toggle='modal' data-id='{$row_id}' data-user_name='{$row['user_name']}' class='btn-delete-user'><i class='fa fa-trash-o'></i> {$action['label']}</a>
                         </li>";       
                 }

                 if ($action_name == 'student_assign') {
                     $menu .= $this->renderString($row, "
                         <li>
                             <a href='#' data-target='#student-assign-dialog' data-toggle='modal' data-id='{{id}}' data-name='{{first_name}} {{last_name}}' class='btn-assign-student'><i class='fa fa-thumb-tack'></i> {$action['label']}</a>
                         </li>
                         <li class='divider'></li>");       
                 }
                 if ($action_name == 'student_edit') {
                     $menu .= $this->renderString($row, "
                         <li>
                             <a href='#' data-target='#student-edit-dialog' data-toggle='modal' data-id='{{id}}' class='btn-edit-student'><i class='fa fa-edit'></i> {$action['label']}</a>
                         </li>");       
                 }                    
                 if ($action_name == 'student_deactivate') {
                     $menu .= $this->renderString($row, "
                         <li>
                             <a href='#' data-target='#student-deactivate-dialog' data-toggle='modal' data-id='{{id}}' data-name='{{first_name}} {{last_name}}' class='btn-deactivate-student'><i class='fa fa-minus-circle'></i> {$action['label-disable']}</a>
                         </li>");       
                 }
                 if ($action_name == 'student_delete') {
                     $menu .= $this->renderString($row, "
                         <li>
                             <a href='#' data-target='#student-delete-dialog' data-toggle='modal' data-id='{{id}}' data-name='{{first_name}} {{last_name}}' class='btn-delete-student'><i class='fa fa-trash-o'></i> {$action['label']}</a>
                         </li>");       
                 }
             }
             
             
             $result .= "
                 <td>
                     <div class='btn-group'>
                         <button type='button' class='btn btn-primary'>actions</button>
                         <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
                         <span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>
                         <ul class='dropdown-menu' role='menu'>
                             {$menu}
                         </ul>
                     </div>
                 </td>";            
             
         }  
        // Close row
        $result .= "</tr>";
         
        return $result;
    }
    
    
    private function renderCell($row_id, $column_name){
        $row = $this->_rows[$row_id];
        $column = $this->_columns[$column_name];
        $template = isset($column['template']) ? $column['template'] : "";
        $empty_field = isset($column['empty_field']) ? $column['empty_field'] : null;
        $empty_value = isset($column['empty_value']) ? $column['empty_value'] : null;
        $empty_template = isset($column['empty_template']) ? $column['empty_template'] : "";
        $sorter = isset($column['sorter']) ? $column['sorter'] : null;
        $sort_field = isset($column['sort_field']) ? $column['sort_field'] : null;

        $td = "<td>";
        // If a sort_field is set, construct the appropriate metadata td
        if ($sorter && $sort_field && isset($row[$sort_field])){
            if ($sorter == "metanum"){
                $td = "<td data-num='{$row[$sort_field]}'>";
            } else if ($sorter == "metatext"){
                $td = "<td data-text='{$row[$sort_field]}'>";
            } else {
                $td = "<td>";       // Default will be empty
            }
        }
        
        // If an empty_field name was specified, and its value matches the "empty value", render the empty template 
        if ($empty_field && ($row[$empty_field] == $empty_value)){
            return $td . $this->renderString($row, $empty_template) . "</td>";
        } else {
            return $td . $this->renderString($row, $template) . "</td>";
        }
    }    
        
    private function renderString($row, $template){
        $result = $template;
  
        // First, replace any arrays (format: [[array_name template]])
        if (preg_match_all("/\[\[([a-zA-Z_0-9]*)\s(.+?)\]\]/", $result, $matches)){
            // Iterate through each array template that was matched
            for ($i=0; $i<count($matches[0]); $i++){
                if (!isset($matches[1]) || !isset($matches[1][$i]))
                    continue;
                if (!isset($matches[2]) || !isset($matches[2][$i]))
                    continue;
                // Get array name and template
                $array_name = $matches[1][$i];
                $array_template = $matches[2][$i];
                //error_log($array_name . ":" . $array_template);
                // Check that array name exists in $row and is of type 'array' 
                if (!isset($row[$array_name]) || gettype($row[$array_name]) != "array")
                    continue;
                //error_log("$array_name is a valid array element");
                // Construct the rendered array template
                $array_result = "";
                // This loop iterates over the elements in the array
                foreach ($row[$array_name] as $array_idx => $array_el){
                    // Each element in the array must itself be an array
                    if (gettype($array_el) != "array")
                        continue;
                    //error_log("Replacing hooks in $array_template");
                    $array_instance = $this->replaceKeyHooks($array_el, $array_template);
                    // Append the array instance to the overall array result
                    $array_result .= $array_instance;
                }
                
                // Ok, now replace the entire array placeholder with the rendered array
                $result = str_replace($matches[0][$i], $array_result, $result);
            }

        }
        // Then, replace all remaining scalar values
        $result = $this->replaceKeyHooks($row, $result);
        return $result;
    }

    private function replaceKeyHooks($row, $template){
        foreach ($row as $key => $value){
            if (gettype($value) != "array" && gettype($value) != "object") {
                $find = '{{' . $key . '}}';
                $template = str_replace($find, $value, $template);
            }
        }
        return $template;
    }
        
    
}
