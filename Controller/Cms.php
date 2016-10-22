<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cms extends CI_Controller {

    public function __construct() { 
        parent::__construct();

        $this->load->model("Admincms_model", "cms");    // Load Admin CMS Model

        checkAdminValidate();   //Check Validation
    }

    /*
    * @function : index
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To render list of all the CMS Pages
    */
    public function index()
    {   
        // Query to fetch the All the pages
        $get_pages = $this->cms->getPages();

        $data['pages'] = $get_pages;
        $data['content'] = 'naredcoadmin/cms';        

        $this->load->view("naredcoadmin/admin_master" , $data); // redering the View
    }

    /*
    * @function : cms_list
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To render list of all the CMS Blocks through Ajax Datatables
    */ 
    public function cms_list(){
        $list = $this->cms->cms_get_datatables();
        $data = array();
        $no = $_POST['start'];


        foreach ($list as $cms) {
            $no++;
            $row = array();
            /*
			$row[] = '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                            <input type="checkbox" class="checkboxes" name="cms[]" value="'.$cms->page_id.'" />
                            <span></span>
                        </label>';
                        */
            $status = ($cms->status == "1") ? '<span class="label label-sm label-success"> Active </span>' : '<span class="label label-sm label-warning"> In-Active </span>' ;

            $row[] = $cms->page_name;
            $row[] = $cms->slug;
            $row[] = $cms->created_date;
            $row[] = $status;

            //add html for action
            $row[] = '<a class="btn btn-sm btn-primary" href="'.base_url().'naredcoadmin/cms/page/'.$cms->slug.'" title="Edit"><i class="glyphicon glyphicon-pencil"></i> Edit</a>
				      <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_cms('."'".$cms->page_id."'".')"><i class="glyphicon glyphicon-trash"></i> Delete</a>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->cms->cms_count_all(),
            "recordsFiltered" => $this->cms->cms_count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    /*
    * @function : create
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To Render a Create CMS Page form
    * @purpose : To Render a Create CMS Page form
    */
    public function create(){
        $data['content'] = 'naredcoadmin/cms_form';      
        $this->load->view("naredcoadmin/admin_master" , $data); // redering the View
    }
    public function add(){
        if($this->input->server('REQUEST_METHOD') == "POST"){

            /* Load form validation library */ 
            $this->load->library('form_validation');

            /* Set validation rule for name field in the form */ 
            $this->form_validation->set_rules('page_name', 'Page name', 'required'); 
            $this->form_validation->set_rules('slug', 'Page Slug', 'required|is_unique[tbl_cms.slug.'.str_replace(" ","_",'slug').']');
            //$this->form_validation->set_rules('page_url', 'Page URL', 'required'); 
            $this->form_validation->set_rules('cms_content', 'Page Content', 'required'); 
            //$this->form_validation->set_rules('page_keywords', 'Meta Key-words', 'required'); 
            //$this->form_validation->set_rules('page_description', 'Meta Description', 'required'); 
            $this->form_validation->set_rules('meta_head', 'Meta Head', 'required'); 
            //$this->form_validation->set_rules('start_date', 'Start Date', 'required'); 
            //$this->form_validation->set_rules('end_date', 'End Date', 'required'); 


            if ($this->form_validation->run() == FALSE) { 

                $data['content'] = 'naredcoadmin/cms_form';      
                $this->load->view("naredcoadmin/admin_master" , $data); // redering the View 
            } 
            else { 
                
                $header_image = "";
                // Check if Header Image Is Selected 
                if($_FILES['header_image']['name']!=''){
                    
                    
                    $this->load->library('upload');

                    $config['upload_path'] = './uploads/';
                    $config['allowed_types'] = 'gif|jpg|png|JPEG|jpeg|PNG|Png';
                    $config['max_width']  = '4000';

                    $newFileName = $_FILES['header_image']['name'];
                    $fileExt = explode(".", $newFileName);
                    $filename = date("dmY_his").".".$fileExt[1];
                    $config['file_name']  = $filename; // must include extension and extension must be available in allowed_types

                    $this->upload->initialize($config);

                    if($this->upload->do_upload('header_image')) {                         
                        $header_image = $filename;
                    }
                }

                $cms_data = array(
                    'page_name'    =>   $this->input->post('page_name'),
                    'slug'         =>   str_replace(" ","_",$this->input->post("slug")),
                    'page_url'     =>   $this->input->post('page_url'),
                    'page_content' =>   $this->input->post('cms_content'),
                    'header_image' =>   $header_image,
                    //'page_keywords'=>   $this->input->post('page_keywords'),
                    //'page_description' => $this->input->post('page_description'),
                    'meta_head'    => $this->input->post('meta_head'),
                    'status'       =>   $this->input->post('page_status'),
                    'start_date'   =>   ($this->input->post('start_date')) ? date("Y-m-d" ,strtotime($this->input->post('start_date'))) : date("Y-m-d H:i:s"),
                    'end_date'     =>   ($this->input->post('end_date')) ? date("Y-m-d" ,strtotime($this->input->post('end_date'))) : "",
                    'created_date' =>   date("Y-m-d H:i:s")
                );

                // Create a Route for This CMS Page    
                
                if(trim($this->input->post('page_url')!='')){
                    $txt = "$"."route['".$this->input->post('page_url')."/".str_replace(' ','_',$this->input->post('slug'))."'] = 'dashboard/index/".str_replace(' ','_',$this->input->post('slug'))."';";
                }
                else{
                    $txt = "$"."route['".str_replace(' ','_',$this->input->post('slug'))."'] = 'dashboard/index/".str_replace(' ','_',$this->input->post('slug'))."';";
                }
                
                $myfile = fopen(APPPATH.'/config/routes.php' , "a+") or die("Unable to open file!"); 
                fwrite($myfile, $txt);
                fclose($myfile);
                
                $insert_query =  $this->cms->create($cms_data);

                if($insert_query){
                    $this->session->set_flashdata('success_message', 'CMS Page added successfully..!');
                    redirect('naredcoadmin/cms');
                }
                else{
                    $this->session->set_flashdata('error_message', 'Sorry, There was some problem, Please try again..!');
                    redirect('naredcoadmin/cms');
                }

            } 
        }
        else{
            redirect('naredcoadmin/dashboard');
        }
    }


    /*
    * @function : page
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To render the Selected Page
    */
    public function page($page_name = null)
    {   
        if($page_name!= null){
            // Query to fetch the Page content for about us page
            $page_info = $this->cms->getPagecontent($page_name);

            if($page_info){
                //echo "<pre>"; print_r($page_info); die();
                $data['page_info'] = $page_info;
                $data['content'] = 'naredcoadmin/cms_update';        
                $this->load->view("naredcoadmin/admin_master" , $data); // redering the View
            }
            else{
                redirect('naredcoadmin/dashboard');
            }
        }
        else{
            redirect('naredcoadmin/dashboard');
        }
    }	

    /*
    * @function : update
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To Update the CMS page content 
    */    
    public function update(){

        if($this->input->server('REQUEST_METHOD') == "POST"){

            //Get the data to Update
            $where = array("page_id" => $this->input->post('page_id') , "slug" => $this->input->post('cms_slug'));
            
            
            $attachment_file=$_FILES["header_image"];

            if($attachment_file['name']!='') {

                $this->load->library('upload');

                $config['upload_path'] = './uploads/';
                $config['allowed_types'] = 'gif|jpg|png|JPEG|jpeg|PNG|Png';
                $config['max_width']  = '4000';

                $newFileName = $_FILES['header_image']['name'];
                $fileExt = explode(".", $newFileName);
                $filename =date("dmY_his").".".$fileExt[1];
                $config['file_name']  = $filename; // must include extension and extension must be available in allowed_types

                $this->upload->initialize($config);

                if($this->upload->do_upload('header_image')) { 
                        if(file_exists('uploads/'.$this->input->post('original_header_image'))){
                            unlink('uploads/'.$this->input->post('original_header_image')); // Unlink the old file
                        }

                    $my_data['upload_status'] = "TRUE";
                }

            }
            
            $header_image = (isset($filename) && !empty($filename)) ? $filename : $this->input->post('original_header_image');              
            
            $update_data = array(
                'page_name'    =>   $this->input->post('page_name'),
                'page_url'     =>   $this->input->post('page_url'),
                'page_content' =>   $this->input->post('cms_content'), 
                'header_image' =>   $header_image,
                //'page_keywords'=>   $this->input->post('page_keywords'),
                //'page_description' => $this->input->post('page_description'),
                'meta_head'    => $this->input->post('meta_head'),
                'status'       =>   $this->input->post('page_status'),
                'start_date'   =>   date("Y-m-d" ,strtotime($this->input->post('start_date'))),
                'end_date'     =>   date("Y-m-d" ,strtotime($this->input->post('end_date'))),
                'modified_date'=>   date("Y-m-d H:i:s")
            );

            $update_query =  $this->cms->update($where , $update_data);

            if($update_query){
                $this->session->set_flashdata('success_message', 'CMS Page Updated successfully..!');
                redirect('naredcoadmin/cms');
            }
            else{
                $this->session->set_flashdata('error_message', 'Sorry, There was some problem, Please try again..!');
                redirect('naredcoadmin/cms');
            }

        }
        else{
            redirect('naredcoadmin/dashboard');
        }

    }	

    /*
    * @function : delete
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To Delete the selected CMS Page
    */
    public function delete($page_id = null){

        if($page_id!=''){
            $delete_query = $this->cms->delete_by_id($page_id);
            echo json_encode(array("status" => TRUE));
        }

    }

    /*
    * @function : blocks
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To render list of all the CMS Blocks Also if User has seleced the Identifier then it will render Edit Block page
    */    
    public function blocks($identifier = null){

        if(isset($identifier)){
            // Get the Block Details for Edit 

            $block_info = $this->cms->get_block_by_identifier($identifier);

            $data['block_info'] = $block_info;
            $data['content'] = 'naredcoadmin/edit_block';        

            $this->load->view("naredcoadmin/admin_master" , $data); // redering the View

        }
        else{
            $data['content'] = 'naredcoadmin/blocks';        

            $this->load->view("naredcoadmin/admin_master" , $data); // redering the View
        }
    }

    /*
    * @function : block_list
    * @date-created : 20th Sep 2016
    * @autor : Punit Gajjar
    * @purpose : To render list of all the CMS Blocks through Ajax Datatables
    */ 
    public function block_list(){
        $list = $this->cms->get_datatables();
        $data = array();
        $no = $_POST['start'];


        foreach ($list as $blocks) {
            $no++;
            $row = array();
            /*
			$row[] = '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                            <input type="checkbox" class="checkboxes" name="blocks[]" value="'.$blocks->block_id.'" />
                            <span></span>
                        </label>';
                        */
            $status = ($blocks->status == "1") ? '<span class="label label-sm label-success"> Active </span>' : '<span class="label label-sm label-warning"> In-Active </span>' ;

            $row[] = $blocks->name;
            $row[] = $blocks->identifier;
            $row[] = $blocks->created_date;
            $row[] = $status;

            //add html for action
            $row[] = '<a class="btn btn-sm btn-primary" href="'.base_url().'naredcoadmin/cms/blocks/'.$blocks->identifier.'" title="Edit" onclick="edit_block('."'".$blocks->block_id."'".')"><i class="glyphicon glyphicon-pencil"></i> Edit</a>
				      <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_block('."'".$blocks->block_id."'".')"><i class="glyphicon glyphicon-trash"></i> Delete</a>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->cms->count_all(),
            "recordsFiltered" => $this->cms->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function block_create(){
        $data['content'] = 'naredcoadmin/block_form';        

        $this->load->view("naredcoadmin/admin_master" , $data); // redering the View
    }

    public function addblock(){
        if($this->input->server('REQUEST_METHOD') == "POST"){

            /* Load form validation library */ 
            $this->load->library('form_validation');

            /* Set validation rule for name field in the form */ 
            $this->form_validation->set_rules('block_name', 'Block name', 'required'); 
            $this->form_validation->set_rules('identifier', 'Block Identifier', 'required|is_unique[tbl_blocks.identifier.'.str_replace(" ","_",'slug').']');
            $this->form_validation->set_rules('block_content', 'Block Content', 'required'); 


            if ($this->form_validation->run() == FALSE) { 

                $data['content'] = 'naredcoadmin/block_form';      
                $this->load->view("naredcoadmin/admin_master" , $data); // redering the View 
            } 
            else { 

                $block_data = array(
                    'name'    =>   $this->input->post('block_name'),
                    'identifier'         =>   str_replace(" ","_",$this->input->post("identifier")),
                    'content'     =>   $this->input->post('block_content'),
                    'status'       =>   $this->input->post('block_status'),
                    'created_date'=>   date("Y-m-d H:i:s")
                );

                $insert_query =  $this->cms->create_block($block_data);

                if($insert_query){
                    $this->session->set_flashdata('success_message', 'CMS Block added successfully..!');
                    redirect('naredcoadmin/cms/blocks');
                }
                else{
                    $$this->session->set_flashdata('error_message', 'Sorry, There was some problem, Please try again..!');
                    redirect('naredcoadmin/dashboard');
                }

            } 
        }
        else{
            redirect('naredcoadmin/dashboard');
        }

    }

    public function delete_block($id){
        $this->cms->delete_by_block_id($id);
        echo json_encode(array("status" => TRUE));
    }

    public function update_block(){
        if($this->input->server('REQUEST_METHOD') == "POST"){

            /* Load form validation library */ 
            $this->load->library('form_validation');

            /* Set validation rule for name field in the form */ 
            $this->form_validation->set_rules('block_name', 'Block name', 'required'); 
            //$this->form_validation->set_rules('identifier', 'Block Identifier', 'required|is_unique[tbl_blocks.identifier.'.str_replace(" ","_",'slug').']');
            $this->form_validation->set_rules('block_content', 'Block Content', 'required'); 


            if ($this->form_validation->run() == FALSE) { 
                redirect('naredcoadmin/cms/blocks/'.$this->input->post("identifier"));
            } 
            else { 
                $where = array('block_id' => $this->input->post('block_id'));

                $block_data = array(
                    'name'    =>   $this->input->post('block_name'),
                    'identifier'         =>   str_replace(" ","_",$this->input->post("identifier")),
                    'content'     =>   $this->input->post('block_content'),
                    'status'       =>   $this->input->post('block_status'),
                    'created_date'=>   date("Y-m-d H:i:s")
                );

                $insert_query =  $this->cms->update_block($where , $block_data);

                if($insert_query){
                    $this->session->set_flashdata('success_message', 'CMS Block Updated successfully..!');
                    redirect('naredcoadmin/cms/blocks');
                }
                else{
                    $this->session->set_flashdata('error_message', 'Sorry, There was some problem, Please try again..!');
                    redirect('naredcoadmin/dashboard');
                }

            } 
        }
        else{
            redirect('naredcoadmin/dashboard');
        }

    }
}
?>