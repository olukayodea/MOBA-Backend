<?php
class post extends database {
    public function clean($data, $from) {
        global $users;
        global $rating;
        global $rating_question;
         
        $checkRate = $rating_question->getSortedList("vendors", "question_type");

        $data['image_url'] = $users->picURL(@$data['ref'], "75");
        
        $data['categories'] = $users->getCatList(@$data['ref']);
        
        $data['rating']['score'] = round($rating->getRate(@$data['ref']), 2);
        $data['rating']['total'] = 5;
        for ($i = 0; $i < count($checkRate); $i++) {
            $data['rating']['data'][$i]['question'] = $checkRate[$i]['question'];
            $data['rating']['data'][$i]['review'] = $rating->getRate($data['ref'], $checkRate[$i]['ref']);
        }
        $data['maps'] = $this->googleDirection($from, @$data);

        unset($data['account_type']);
        unset($data['total']);
        return $data;
    }
    
    public function formatResults($data, $location, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i], $location);
            }
        } else {
            $data = $this->clean($data, $location);
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
            $return['data'] = $this->formatResults( $result['data'], $location );
        } else if ($type == "search") {
            $result = $search->keywordSearchData($location, $ref, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResults( $result['data'], $location );
        } else if ($type == "featured") {
            $result = $search->aroundMeData($location, $start, $limit);
            //$result = $search->isFeaturedData($location, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResults( $result['data'], $location );
        } else if ($type == "aroundme") {
            $result = $search->aroundMeData($location, $start, $limit);
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['count']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['data']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['count'];
            $return['data'] = $this->formatResults( $result['data'], $location );
        }
        return $return;
    }
}
?>