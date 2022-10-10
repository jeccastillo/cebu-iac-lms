<?php
/*Feed Fetcher Model*/

class Feeds extends CI_Model {

	function fetch_latest_feeds($community,$amount=null,$offset=0,$type)
	{
		$this->db->select('*');
		$this->db->where(array('fk-tb_mas_community'=>$community,'enumEnabled'=>'enabled','fk-tb_mas_content_types'=>$type));		
		$this->db->order_by('dteStart desc');
		if($amount==null)
			$this->db->limit(4,$offset);
		else
			$this->db->limit($amount,$offset);
		$query = $this->db->get('tb_mas_content');
		return $query->result_array();
	}
		
	function get_feed_count($community,$type)
	{		
		$this->db->where(array('fk-tb_mas_community'=>$community,'enumEnabled'=>'enabled','fk-tb_mas_content_types'=>$type));		
		$this->db->from('tb_mas_content');
		return $this->db->count_all_results();
	}
	function fetch_featured($community=null,$amount=null)
	{
		$this->db->select('*');
		$this->db->where(array('status'=>'enabled'));
		if($community!=null)
			$this->db->where('category',$community);
		$this->db->where('featured >',0);
		$this->db->order_by("featured","desc");
		$this->db->join('content_image', 'content.id = content_image.id');
		if($amount==null)
			$this->db->limit(10);
		else
			$this->db->limit($amount);
		$query = $this->db->get('content');
		return $query->result_array();
	}

	function fetch_feed_type($community,$type,$limit,$order)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->select('*');
		$this->db->where(array('category'=>$community,'status'=>'enabled','type'=>$type));	
		$this->db->order_by("weight ".$order.", start ".$order);
		$this->db->join('content_image', 'content.id = content_image.id');
		$this->db->limit($limit);
		$query = $this->db->get('content');
		return $query->result_array();
	}
	function fetch_gigs($community,$dates=null,$start=null)
	{
		$date = date('Y-m-d');
		$this->db->select('*');
		$this->db->where(array('category'=>$community,'status'=>'enabled','type'=>'gig'));
		if($start!=null)
			$this->db->where('start',$start);
		$this->db->order_by("start","desc");
		$this->db->join('content_image', 'content.id = content_image.id');
		if($dates!=null)
		{
			$this->db->limit(5);
			$this->db->group_by('start');
		}
		else
		{
			$this->db->limit(6);
		}
		$query = $this->db->get('content');
		return $query->result_array();
	}

}
?>