<?php

/**
 * @property CI_DB_active_record $db
 * @property DX_Auth $dx_auth
 */
class facebook_feed_model extends CI_Model
{

    /**
     * @return array
     */



    public function getAllCatalogs($active)
    {

        if($active =='active'){
            $result = $this->db->where('active', 1);
            $result = $result->order_by('id');
            $result = $result->get('mod_facebook_feed');
        }else{
            $result = $this->db->order_by('id');
            $result = $result->get('mod_facebook_feed');
        }

        $result = $result->num_rows() > 0 ? $result->result_array() : [];

        $ids = array_column($result, 'id');

        return array_combine($ids, $result);
    }
    public function addCatalog($name, $catalog_id, $categories)
    {

        foreach ($categories as $cat){
            $cats_to_mod[]=$cat['value'];
        }

        if(in_array('all', $cats_to_mod) && !in_array('none', $cats_to_mod)){
            $cats = json_encode(['all']);
        }elseif(in_array('none', $cats_to_mod)){
            $cats = json_encode(['none']);
        }else{
            $cats = json_encode($cats_to_mod);
        }

        $data = [
            'catalog_name' => $name,
            'catalog_id' =>$catalog_id,
            'categories' => $cats,
        ];

        $this->db->insert('mod_facebook_feed',$data);

        if ($this->db->_error_message() && $this->db->_error_message() != null && $this->db->_error_message() != '') {
            return false;
        }
        return true;
    }



    public function deleteCatalog($id)
    {

        $city = $this->db->where('id', $id)->get('mod_facebook_feed');

        if($city && $city->num_rows()>0){
            $this->db->where('id', $id)->delete('mod_facebook_feed');
        }


        if ($this->db->_error_message() && $this->db->_error_message() != null && $this->db->_error_message() != '') {
            return false;
        }
        return true;
    }



    public function setActiveCatalog($id, $active, $feed_id)
    {
        $data = [
            'active' => $active,
            'feed_id' =>$active=='1'?$feed_id:null,
        ];
        $city = $this->db->where('id', $id)->get('mod_facebook_feed');

        if($city && $city->num_rows()>0){
            $this->db->where('id', $id)->set($data)->update('mod_facebook_feed');
        }


        if ($this->db->_error_message() && $this->db->_error_message() != null && $this->db->_error_message() != '') {
            return false;
        }
        return true;
    }


    public function upadateCatalog($id, $new_name, $categories)
    {

        foreach ($categories as $cat){
            $cats_to_mod[]=$cat['value'];
        }

        if(in_array('all', $cats_to_mod) && !in_array('none', $cats_to_mod)){
            $cats = json_encode(['all']);
        }elseif(in_array('none', $cats_to_mod)){
            $cats = json_encode(['none']);
        }else{
            $cats = json_encode($cats_to_mod);
        }

        $data = [
            'catalog_name' => $new_name,
            'categories' => $cats,
        ];


        $new_data = $this->db->where('id', $id)->get('mod_facebook_feed');

        if($new_data && $new_data->num_rows()>0){
            $this->db->where('id', $id)->set($data)->update('mod_facebook_feed');
        }


        if ($this->db->_error_message() && $this->db->_error_message() != null && $this->db->_error_message() != '') {
            return false;
        }
        return true;
    }

}