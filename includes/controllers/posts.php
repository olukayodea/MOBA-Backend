<?php
class post extends users {
    private function clean($data) {
        if ($data['image_url'] == "") {
            $data['image_url'] = "https://ui-avatars.com/api/?name=".urlencode($data['screen_name']);
        } else if (($data['image_url'] != "") && ($data['account_type'] == "local")) {
            $data['image_url'] = URL.$data['image_url'];
        }
        unset($data['account_type']);
        return $data;
    }
    
    public function formatResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i]);
            }
        } else {
            $data = $this->clean($data);
        }
        return $data;
    }

    public function postAPI($location, $type, $ref=false, $page=false) {
        global $options;
        global $search;
        global $category;
        if (intval($page) == 0) {
            $page = 1;
        }
        $current = intval($page)-1;
        
        $limit = $options->get("result_per_page_mobile");
        $start = $current*$limit;

        if ($type == "category") {
            $categoryData = $category->listOne($ref);
            $result = $search->catSearchData($location, $ref, $start, $limit);
            
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['category'] = $category->formatResult( $categoryData, true );
            $return['data'] = $this->formatResult( $result['data'] );
        } else if ($type == "search") {
            $result = $search->keywordSearchData($location, $ref, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResult( $result['data'] );
        } else if ($type == "featured") {
            $result = $search->isFeaturedData($location, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResult( $result['data'] );
        } else if ($type == "aroundme") {
            $result = $search->aroundMeData($location, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResult( $result['data'] );
        }
        return $return;
    }
}
?>