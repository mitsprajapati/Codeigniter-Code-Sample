<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admincms_model extends CI_Model {
    
    var $table = 'tbl_cms'; 
    var $cms_column_order = array('page_id','page_name','slug','page_url','page_content','page_keywords' ,'page_description' ,'header_image' ,'meta_head' , 'start_date' , 'end_date' , 'status' , 'created_date' , 'modified_date'); //set column field database for datatable orderable
	var $cms_column_search = array('page_id','page_name','slug','page_url','page_content','page_keywords' ,'page_description', 'meta_head'); //set column field database for datatable searchable 
	var $cms_order = array('page_id' => 'asc'); // default order 
    
    
    
    var $table_blocks = 'tbl_blocks';
    
    var $column_order = array('name','identifier','content','status','created_date','modified_date'); //set column field database for datatable orderable
	var $column_search = array('name','identifier','content'); //set column field database for datatable searchable 
	var $order = array('block_id' => 'asc'); // default order 

	public function getPagecontent($slug)
	{
        $this->db->select('page_id , page_name , slug , page_url , page_content , page_keywords , page_description , header_image , meta_head , DATE_FORMAT(start_date , "%m/%d/%Y") as start_date, DATE_FORMAT(end_date , "%m/%d/%Y") as end_date , status');
		$this->db->from($this->table);
		$this->db->where('slug',$slug);
		$query = $this->db->get();

		return $query->row();
	}

	public function update($where, $data)
	{
		$this->db->update($this->table, $data, $where);
		return $this->db->affected_rows();
	}
    
    public function getPages(){
        $this->db->select('page_id,
                           page_name, 
                           slug,
                           header_image,
                           page_url,
                           IF(status = "1" , "Active", "In-Active") as status ,
                           DATE_FORMAT(start_date,"%D %M %Y") as start_date ,
                           DATE_FORMAT(end_date,"%D %M %Y") as end_date
                         ');
		$this->db->from($this->table);
        $query = $this->db->get();
		return $query->result();
    }
    
    public function delete_by_id($page_id)
	{
		$this->db->where('page_id', $page_id);
		$query = $this->db->delete($this->table);
        return $query;
	}
    
    public function create($insert_data){
        
            $query = $this->db->insert($this->table, $insert_data); 
            return $this->db->insert_id();
    }
    
    
    
    public function getBlocks(){
        $this->db->select('block_id,
                           name, 
                           identifier,
                           content,
                           IF(status = "1" , "Active", "In-Active") as status ,
                           DATE_FORMAT(created_date,"%D %M %Y") as creted_date
                         ');
		$this->db->from($this->table_blocks);
        $query = $this->db->get();
		return $query->result();
    }
    
    private function _get_datatables_query()
	{
		
		$this->db->from($this->table_blocks);

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table_blocks);
		return $this->db->count_all_results();
	}
    
    public function get_block_by_identifier($identifier)
	{
		$this->db->from($this->table_blocks);
		$this->db->where('identifier',$identifier);
		$query = $this->db->get();

		return $query->row();
	}
    
    public function create_block($data){
            $query = $this->db->insert($this->table_blocks, $data); 
            return $this->db->insert_id();       
    }

	public function delete_by_block_id($id)
	{
		$this->db->where('block_id', $id);
		$this->db->delete($this->table_blocks);
	}
    
    public function update_block($where, $data)
	{
		$this->db->update($this->table_blocks, $data, $where);
		return $this->db->affected_rows();
	}
    
    
    
    private function cms_get_datatables_query()
	{
		
		$this->db->from($this->table);

		$i = 0;
	
		foreach ($this->cms_column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->cms_column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->cms_column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->cms_order))
		{
			$order = $this->cms_order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function cms_get_datatables()
	{
		$this->cms_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function cms_count_filtered()
	{
		$this->cms_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function cms_count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}
}