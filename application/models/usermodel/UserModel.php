<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model
{
    public function get_user()
    {
        $query= $this->db->get('users');
        return $query->result();

    }

    public function insertUser($data)
    {
        $success = $this->db->insert('users',$data);
        if($success)
        {
            $ins_id=$this->db->insert_id();
            $data=$this->findUser($ins_id);
            return $data;
        }
        else
        {
            return false;
        }
    }
    public function findUserByEmailId($email)
    {
        $this->db->where('email',$email);
        $query = $this->db->get('users');
        return $query->row();
    }

    public function findUser($id)
    {
        $this->db->where('id',$id);
        $query= $this->db->get('users');
        return $query->row();
    }

    public function update_User($id, $data)
    {
        $this->db->where('id',$id);
        return $this->db->update('users',$data);
    }

    public function delete_User($id)
    {
        return $this->db->delete('users',['id'=>$id]);
    }


}
?>