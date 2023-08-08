<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
        $this->load->model("usermodel/UserModel");
        $this->load->library('encryption');
        $this->load->library('form_validation');

    }


	public function index()
	{
        echo "Auth Controller";
    }

    public function login(){

        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = new UserModel;
        $userData = $user->findUserByEmailId($email);

        if(isset($userData) && !empty($userData)){
            
            $db_password = $this->encryption->decrypt($userData->password);

            if($db_password == $password){
                
                $this->load->library('session');

                $session_data = array( 
                    'logged_in' => 'TRUE',
                    'session_id'     => session_id(),
                    'username'  => $userData->first_name." ".$userData->last_name, 
                    'email'     => $userData->email, 
                    'image'     => $userData->image
                );

                $this->session->set_userdata($session_data);
                echo json_encode($session_data);
            }
            else{
                echo 'Invalid Credantials';
            }
        }else{
            echo 'user not found';
        }
    }
}