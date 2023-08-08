<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class UserController extends RestController {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
        $this->load->model("usermodel/UserModel");
        $this->load->library('encryption');
        $this->load->library('form_validation');

        
    }

    public function allUser_get()
    {
        $user=new UserModel;
        $result= $user->get_user(); 
        $this->response($result,200); 
    }

    public function createUser_post()
    {  
        $path_file = '';
        if(isset($_FILES['image']) && $_FILES['image'] != ""){

            $file= $_FILES['image'];

            //  var_dump($_FILES['image']);
            $path="uploads/user/";
            if(!is_dir($path))
            {
                mkdir($path,0777,true);
            }
            $oath_file = "";
            if(!empty($file['name']))
            {
                $config['upload_path']='./'.$path;
                $config['allowed_types']="jpg|jpeg|png|git";
                $config['file_name'] = time();
                $config['max_size']=1024;
                $this->upload->initialize($config);
                if($this->upload->do_upload('image'))
                {
                    $uploadData= $this->upload->data();
                    $path_file = './' . $path . $uploadData['file_name'];
                }
            }
        }
        
        $user=new UserModel;
        $this->form_validation->set_rules(
            'first_name',
            'First Name',
            'required',
            array(
                'required'=>'{field} is reuired'
            )
        );
        $this->form_validation->set_rules(
            'last_name',
            'last Name',
            'required',
            array(
                'required'=>'{field} is reuired'
            )
        );
        $this->form_validation->set_rules(
            'email',
            'email Name',
            'required|valid_email|is_unique[users.email]',
            array(
                'required'=>'{field} is reuired'
            )
        );
        $this->form_validation->set_rules(
            'password',
            'Password',
            'required',
            array(
                'required'=>'{field} is required'
            )
        );
        $this->form_validation->set_rules(
            'cpassword',
            'Confirm Password',
            'required|matches[password]',
            array(
                'required'=>'{field} is required'
            )
        );
        if($this->form_validation->run()==false)
        {
            $this->response([
                'status'=>false,
                'message'=>strip_tags(validation_errors()),
            ], RestController::HTTP_BAD_REQUEST);

        }
        else
        {
            $data = [
                'first_name'=> $this->input->post('first_name',TRUE),
                'last_name'=> $this->input->post('last_name',TRUE),
                'email'=> $this->input->post('email',TRUE),
                'password'=> $this->input->post('password',TRUE),
                'image'=>$path_file,
            ]; 

            $data['password'] = $this->encryption->encrypt($data['password']);
            
            $result=$user->insertUser($data);
            if($result!=NULL)
            {
                // var_dump($result);
                $this->response([
                    'data'=>$result,
                    'status'=>true,
                    'message'=>'NEW USER CREATED'
                ], RestController::HTTP_OK);

            }
            else
            {
                $this->response([
                    'status'=>false,
                    'message'=>'FAILED TO CREATE USER'
                ], RestController::HTTP_BAD_REQUEST);

            }
        }   

    }
    
    public function findUserById_get($id)
    {
        $user = new UserModel;
        $result = $user->findUser($id);

        $wordToRemove="./uploads/user/";
        $originalPath=$result->image;
        $modifiedPath = str_replace($wordToRemove, '', $originalPath);
        $imagePath=base_url("uploads/user/".$modifiedPath);
        $result->image=$imagePath;
        $this->response($result,200);
    }

    public function findUserByEmail_post()
    {
        $email=$this->input->post('email');
        $user = new UserModel;
        $result = $user->findUserByEmailId($email);

        $this->response($result,200);

    }

    public function findUserById($id)
    {
        $user = new UserModel;
        $result = $user->findUser($id);
        return $result;
    }

    public function updateUser_post()
    {
        $id = $this->input->post('user_id');
        $user = new UserModel;
        // $data = $user->findUser($id);
         $data_user= $this->findUserById($id);
        $file= $_FILES['image'];
        // var_dump($file);
        $path="uploads/user/";
        if(!is_dir($path))
        {
        
            mkdir($path,0777,true);
        }
        $path_file = "";
        if(!empty($file['name']))
        {
            $wordToRemove="./uploads/user/";
            $originalPath=$data_user->image;
            $modifiedPath = str_replace($wordToRemove, '', $originalPath);
            // echo $modifiedString;
            $config['upload_path']='./'.$path;
            $config['allowed_types']="jpg|jpeg|png|git";
            $config['file_name'] = $modifiedPath;
            $config['max_size']=1024;
            $this->upload->initialize($config);
            
            if($this->upload->do_upload('image'))
            {
                if(file_exists($data_user->image)){
                    $data['image']=$data_user->image;
                    $temp="C:/xampp/htdocs/clover_api/uploads/user/";
                    $deletedpath=$temp.$modifiedPath;
                    echo $deletedpath;
                    unlink($deletedpath);   
                }

            }
        }
        $data['first_name']= $this->input->post('first_name',TRUE);
        $data['last_name']= $this->input->post('last_name',TRUE);
        $data['email']= $this->input->post('email',TRUE);
        $data['password']= $this->input->post('password',TRUE);
        $update_result=$user->update_User($id,$data);

        if($update_result>0)
        {
            $this->response([
                'status'=>true,
                'message'=>'USER UPDATED!!!'
            ], RestController::HTTP_OK);

        }
        else
        {
            $this->response([
                'status'=>false,
                'message'=>'FAILED TO UPDATE USER'
            ], RestController::HTTP_BAD_REQUEST);

        } 
    
    }

    public function getUserImage_get($user_id)
    {
        // $user_id=$this->input->post('id');
        var_dump($user_id);
    
        $user = new UserModel;
        $userData = $this->findUserById($user_id);
        // var_dump($userData);
       

    
        if ($userData && !empty($userData->image)) {
            $imagePath = $userData->image;
            // Check if the image file exists
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                echo $imagePath;
                die;
                // Read the image file and send it as a response
                $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
                $contentType = 'image/jpeg'; // Default content type for JPEG images
            
                if ($imageExtension === 'png') {
                    $contentType = 'image/png';
                } elseif ($imageExtension === 'gif') {
                    $contentType = 'image/gif';
                }
                header("Content-type: $contentType");
                echo $imageData;
                // Send the image data as the response
               
                exit;
                // Set the appropriate content type for the response
            }
        }
    
        // If the image is not found or an error occurs, you can send a default image or an error response.
        header("Content-type: image/jpeg");
        echo file_get_contents("path_to_default_image.jpg"); // Replace 'path_to_default_image.jpg' with the path to your default image file.
        exit;
    }

    public function deleteUser_delete($id)
    {
        $user = new UserModel;
        
        $userData=$this->findUserById($id);
        // $currentPath = $userData->image;
        $wordToRemove="./uploads/user/";
        $originalPath=$userData->image;
        $modifiedPath = str_replace($wordToRemove, '', $originalPath);
            
        echo $modifiedPath;
        
        $temp="C:/xampp/htdocs/clover_api/uploads/user/";
        $deletedpath=$temp.$modifiedPath;
        if(file_exists($deletedpath))
        {
            echo $deletedpath;
            unlink($deletedpath);
            $result = $user->delete_User($id);
        }
        if($result>0)
        {
            $this->response([
                'res'=>$userData,
                'status'=>true,
                'message'=>'USER DELETED!!!'
            ], RestController::HTTP_OK);

        }
        else
        {
            $this->response([
                'status'=>false,
                'message'=>'FAILED TO DELETED USER'
            ], RestController::HTTP_BAD_REQUEST);

        }

    }
}
?>