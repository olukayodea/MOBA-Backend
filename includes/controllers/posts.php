<?php
class post extends users {
    public function postAPI($location, $type, $ref=false, $page=false) {
        global $options;
        global $search;
        if (intval($page) == 0) {
            $page = 1;
        }
        $current = intval($page)-1;
        
        $limit = $options->get("result_per_page_mobile");
        $start = $current*$limit;

        if ($type == "category") {
            $result = $search->catSearchData($location, $ref, $start, $limit);
            
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            //$return['data'] = $this->formatResult( $result['data'] );
        }

        print_r($return);
    }
}
?>