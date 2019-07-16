<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once('lob/lob-php/include/create_postcard.php');
require_once('lob/lob-php/include/create_letter.php');
class Direct_mails extends CI_Controller
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */

    public function __construct()
    {	//echo date('Y-m-d','1504418649');exit;//2017-09-03
	/*date_default_timezone_set("UTC");
	echo  date('Y-m-d\TH:i:s.0\Z',strtotime('+4 hours'));exit;*/
        parent::__construct();

        if (!$this->session->userdata('user_id') || !$this->session->userdata('investor_id')) {
            redirect('user/login');
        }
        $this->load->model('common_model');
        $this->load->helper('twilio_helper');
        $this->load->model('token_model');
        $this->load->model('lead_model');
		$this->load->model('direct_mail');
        $this->token_model->createToken();
		$this->load->library('pagination');
		
		ini_set('auto_detect_line_endings', true);
    }
    public function index()
    {//echo check_letter_cancelable(17);exit;
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
        $data['page']  = '';
        $data['menu']  = '';
        $data['menu']  = '';
        $data['sub_menu'] = '';
        $data['title']  = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Marketing', '/direct_mails');
        $this->breadcrumbs->push('Direct Mail', '/');


        $config = array();
        $config["base_url"] = base_url() . "direct_mails/index";
        $total_row = $this->common_model->getCount("campaigns", array('user_id' =>$this->session->userdata('user_id'),'deleted'=>0));
        $config["total_rows"] = $total_row;
        $config["per_page"] = PAGINATION;
        $config['use_page_numbers'] = true;
        $config['uri_segment'] = 3;
        $config['num_links'] = $total_row;
        $config['cur_tag_open'] = '&nbsp;<a class="current">';
        $config['cur_tag_close'] = '</a>';
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['display_pages'] = true;
        $this->pagination->initialize($config);

        if ($this->uri->segment(3)) {
            $page = ($this->uri->segment(3)) ;
        } else {
            $page = 1;
        }
        $paginate = false;
        if( $total_row > $config["per_page"] ){
          $paginate = true;
        }

        $data['campaigns'] = $this->direct_mail->get_all_campaigns($config["per_page"], $page,  $paginate);

        $str_links = $this->pagination->create_links();
        $links = explode('&nbsp;', $str_links);
        $data["links"] = $links;


        //$data['campaigns'] = $this->direct_mail->get_all_campaigns();

        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail_campaigns/index', $data);
        $this->load->view('includes/footer', $data);
    }
    public function postcards($campaign_id='')
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
 
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';
		$this->load->library('pagination');
        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', '/direct_mails');
        $this->breadcrumbs->push('Postcards', '/');

        $search = array('user_id'=>$this->session->userdata('user_id'),'type'=>'postcard');
        if ($_POST) {
            if ($this->input->post("campaign_id") != "") {
                $search["campaign_id"] =$campaign_id= $this->input->post("campaign_id");
            }

            $this->session->set_userdata(array("search" => $search));
			} elseif (!empty($campaign_id)) {
				//from campaigns  page
				$search["campaign_id"]    =    $campaign_id;
				
				$this->session->set_userdata(array("search" => $search));
				
			} elseif ($this->uri->segment(4) !="page") {
				$this->session->set_userdata(array("search" => array()));
			} elseif (count($this->session->userdata("search"))) {
				$search = $this->session->userdata("search");
			}
			$campaign    =    ($campaign_id!='')?$campaign_id:0;
	
			$data["result"] = $this->common_model->get_all('lob_to_address', $search, 'send_date desc', 30, $this->uri->segment(5));
			
			$this->session->set_userdata('post_card_query_array',array('lob_to_address', $search, 'send_date desc', 30, $this->uri->segment(5)));
      		//echo $this->session->userdata('post_card_query');
			$config['base_url'] = site_url('direct_mails/postcards/'.$campaign.'/page/');
			$total = $this->common_model->getSearchCount();
			$config['total_rows'] = $total;
			$config['per_page'] = 30;
			$config['num_links'] = 2;
			$config['uri_segment'] = 5;
	
			$config['anchor_class'] = '';
			$config['next_link'] = '<i class="fa fa-angle-double-right"></i>';
			$config['prev_link'] = '<i class="fa fa-angle-double-left"></i>';
			$config['next_tag_open'] = '<li>';
			$config['next_tag_close'] = '</li>';
			$config['prev_tag_open'] = '<li>';
			$config['prev_tag_close'] = '</li>';
			$config['prev_link_not_exists'] = '';
			$config['num_tag_open'] = '<li>';
			$config['num_tag_close'] = '</li>';
			$config['cur_tag_open'] = '<li class="active"><a href="">';
			$config['cur_tag_close'] = '</a></li> ';
			$config['first_tag_open'] = '<li>';
			$config['first_tag_close'] = '</li>';
			$config['last_tag_open'] = '<li>';
			$config['last_tag_close'] = '</li>';
			$this->pagination->initialize($config);
			$data["pagination"] = $this->pagination->create_links();
			$data["uri_segment"] = $this->uri->segment(5);

        $data["search"] = $search;
        $data["campaigns"] = $this->common_model->get_all('campaigns', array('user_id'=>$this->session->userdata('user_id'),'deleted'=>0));
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/index', $data);
        $this->load->view('includes/footer', $data);
    }
	
	public function postcards_ajax()
	{
		
		if($_POST){
			

			$post_card_query_array	=	$this->session->userdata('post_card_query_array');
			$search = array('user_id'=>$this->session->userdata('user_id'));
			//$post_card_query_array[1] = array();
			if ($this->input->post("campaign_id") != "") {
				$post_card_query_array[1]["campaign_id"] = $this->input->post("campaign_id");
                $search["campaign_id"] =$campaign_id= $this->input->post("campaign_id");
            }
			$search["type"]	="postcard";
			//print_r($post_card_query_array);
			$data["result"] = $this->common_model->get_all($post_card_query_array[0], $post_card_query_array[1], $post_card_query_array[2], $post_card_query_array[3], $post_card_query_array[4]);
			
			$data['html']	=	$this->load->view('direct_mail/postcards_ajax',$data,true);
			
			$results = $this->common_model->get_all('lob_to_address', $search, 'send_date desc');
			$data['total']	=	count($results);
			$data['pending']	=	0;
			$data['sent']		=	0;
			$data['undeliverable']	=	0;
			$data['failed']	=	0;
			$data['cancelled']	=	0;
			$data['delivered']	=	0;
			if(!empty($results))
			{
				foreach($results as $row)
				{
					if($row['status']==0)
					{
						$data['pending'] +=1;
					}
					else if($row['status']==1){
						$data['sent'] +=1;
					}
					else if($row['status']==2)
					{
						$data['undeliverable'] +=1;
					}
					else if($row['status']==3)
					{
						$data['failed'] +=1;
					}
					else if($row['status']==4)
					{
						$data['cancelled'] +=1;
					}
					else if($row['status']==5)
					{
						$data['delivered'] +=1;
					}
				}
			}
			if($data['total']>0){
				$data['pending_percentage']	=	(($data['pending']/$data['total'])*100);
				$data['sent_percentage']	=	(($data['sent']/$data['total'])*100);
				$data['undeliverable_percentage']	=	(($data['undeliverable']/$data['total'])*100);
				$data['failed_percentage']	=	(($data['failed']/$data['total'])*100);
				$data['cancelled_percentage']	=	(($data['cancelled']/$data['total'])*100);
				$data['delivered_percentage']	=	(($data['delivered']/$data['total'])*100);
				 $data['progress_bar']     =    '<td colspan="8">
													<div class="progress">
														<div class="progress-bar progress-bar-info" role="progressbar" style="width:'.$data['pending_percentage'].'%">'.ceil($data['pending_percentage']).'%</div>
														<div class="progress-bar progress-bar-success" role="progressbar" style="width:'.$data['sent_percentage'].'%">'.ceil($data['sent_percentage']).'%</div><div class="progress-bar progress-bar-warning" role="progressbar" style="width:'.$data['undeliverable_percentage'].'%">'.ceil($data['undeliverable_percentage']).'%</div>
														<div class="progress-bar progress-bar-danger" role="progressbar" style="width:'.$data['failed_percentage'].'%">'.ceil($data['failed_percentage']).'%</div>
														<div class="progress-bar progress-bar-primary" role="progressbar" style="width:'.$data['cancelled_percentage'].'%">'.ceil($data['cancelled_percentage']).'%</div>
														<div class="progress-bar progress-bar-primary" role="progressbar" style="background-color:green;width:'.$data['delivered_percentage'].'% ">'.ceil($data['delivered_percentage']).'%</div>
													</div>  
													<span class="label label-sm label-default"> Total : '.$data['total'].'</span>
													<span class="label label-sm label-info"> Pending : '.$data['pending'].'</span>
													<span class="label label-sm label-success"> Processed : '.$data['sent'].'</span>
													<span class="label label-sm label-warning"> Undeliverable : '.$data['undeliverable'].'</span>
													<span class="label label-sm label-danger"> Failed : '.$data['failed'].'</span>
													<span class="label label-sm label-primary"> Cancelled : '.$data['cancelled'].'</span>
													<span class="label label-sm label-primary" style="background-color:green"> Delivered : '.$data['delivered'].'</span>
												</td>';
			}
			header('Content-type: application/json');
			die(json_encode($data));
		}
	}
	
    public function view($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }


        $data['action']  = 'View';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', '/direct_mails');
        $this->breadcrumbs->push('View Postcard', '/');
//checking if postcard exist
		$data['postcard']	=	array();
		$data['postcard_queue']    =    $this->common_model->get('lob_to_address', array('id'=>$idx), 'array');
		if (count($data['postcard_queue']) == 0) {
            $this->session->set_flashdata('errors', 'Postcard not found !!!');
            redirect('direct_mails/postcards/'.$campaign_id);
        }
		else if($data['postcard_queue']['lob_postcard_id']!='')
		{
        	$data['postcard']    =    $this->common_model->get('lob_postcards', array('id'=>$data['postcard_queue']['lob_postcard_id']), 'array');
		}

        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/view', $data);
        $this->load->view('includes/footer', $data);
    }
	
	//cancel postcard from view page
	public function cancel_letter($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
			date_default_timezone_set("UTC");

		$postcard_queue    =    $this->common_model->get('lob_to_address', array('lob_postcard_id'=>$idx), 'array');
		//fetching api key
		$private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
		$lob = new Create_letter($private_key->value);
		
		$cancel_data    =    $lob->cancel_letter($idx);
		if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
			$this->session->set_flashdata('errors', $cancel_data['message']);
			redirect('direct_mails/view_letter/'.$postcard_queue['id'].'/'.$campaign_id);
		}
		else if (isset($cancel_data['deleted']) && $cancel_data['deleted']==true) {
			
			$this->common_model->update_balance($postcard_queue['amt_charge'],array('client_id' => $this->session->userdata('client_id')));
			$this->common_model->update('lob_letters',array('id'=>$idx),array('status'=>4));
			$this->common_model->update('lob_to_address',array('lob_postcard_id'=>$idx),array('status'=>4));
			$activity_data	=	array(
												'client_id'=>$this->session->userdata('client_id'),
												'user_id'=>$this->session->userdata('user_id'),
												'activity'=>'Letter cancelled',
												'type'=>'direct_mail',
												'date_time'=>date('m-d-Y H:i A'),
											);
			$this->common_model->insert('activities', $activity_data);
			//updating usage history
			if($postcard_queue['queue_id']!=0)
			{
				//individual postcard/letter cannot send as send date will be current date
				$usage_history	=	$this->common_model->get('usage_history',array('queue_id'=>$postcard_queue['queue_id']),'array');
				if(!empty($usage_history))
				{
					if($usage_history['item_count']==1)
					{
						$this->common_model->delete('usage_history',array('queue_id'=>$postcard_queue['queue_id']));
					}
					else{
						$charged_amount	=	$usage_history['charged_amount']-$postcard_queue['amt_charge'];
						$item_count		=	$usage_history['item_count']-1;
						$update_data	=	array(
										  		'charged_amount'=>$charged_amount,
										  		'item_count'	=>$item_count,
										  	);
						$this->common_model->update('usage_history',array('id'=>$usage_history['id']),$update_data);
						
					}
				}
				
				$insert_data	=	array(
									  'queue_id' => $postcard_queue['queue_id'],
									  'status'=>4,
									  'credited_amt'=>$postcard_queue['amt_charge'],
									  'client_id'=>$postcard_queue['client_id'],
									  'user_id'=>$postcard_queue['user_id'],
									  'date'=>date('Y-m-d H:i:s'),
									 );
				$this->common_model->insert('lob_credit_history', $insert_data);
			}
			redirect('direct_mails/view_letter/'.$postcard_queue['id'].'/'.$campaign_id);
		}
    }
	//cancel postcard from view page
    public function cancel_postcard($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
		date_default_timezone_set("UTC");

		$postcard_queue    =    $this->common_model->get('lob_to_address', array('lob_postcard_id'=>$idx), 'array');
		//fetching api key
		$private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
		$lob = new LobSend($private_key->value);//'test_3862a0764560e95227a2b84dac329e52226'
		//us address verification
		$cancel_data    =    $lob->cancel_postcard($idx);//print_r($cancel_data);exit;
		if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
			$this->session->set_flashdata('errors', $cancel_data['message']);
			redirect('direct_mails/view/'.$postcard_queue['id'].'/'.$campaign_id);
		}
		else if (isset($cancel_data['deleted']) && $cancel_data['deleted']==true) {//print_r($postcard_queue);exit;
			
			$this->common_model->update_balance($postcard_queue['amt_charge'],array('client_id' => $this->session->userdata('client_id')));
			$this->common_model->update('lob_postcards',array('id'=>$idx),array('status'=>4));
			$this->common_model->update('lob_to_address',array('lob_postcard_id'=>$idx),array('status'=>4));
			$activity_data	=	array(
									'client_id'=>$this->session->userdata('client_id'),
									'user_id'=>$this->session->userdata('user_id'),
									'activity'=>'Postcard cancelled',
									'type'=>'direct_mail',
									'date_time'=>date('m-d-Y H:i A'),
								);
			$this->common_model->insert('activities', $activity_data);
			//updating usage history
			if($postcard_queue['queue_id']!=0)
			{
				//individual postcard/letter cannot send as send date will be current date
				$usage_history	=	$this->common_model->get('usage_history',array('queue_id'=>$postcard_queue['queue_id']),'array');
				if(!empty($usage_history))
				{
					if($usage_history['item_count']==1)
					{
						$this->common_model->delete('usage_history',array('queue_id'=>$postcard_queue['queue_id']));
					}
					else{
						$charged_amount	=	$usage_history['charged_amount']-$postcard_queue['amt_charge'];
						$item_count		=	$usage_history['item_count']-1;
						$update_data	=	array(
										  		'charged_amount'=>$charged_amount,
										  		'item_count'	=>$item_count,
										  	);
						$this->common_model->update('usage_history',array('id'=>$usage_history['id']),$update_data);
					}
				}
				$insert_data	=	array(
									  'queue_id' => $postcard_queue['queue_id'],
									  'status'=>4,
									  'credited_amt'=>$postcard_queue['amt_charge'],
									  'client_id'=>$postcard_queue['client_id'],
									  'user_id'=>$postcard_queue['user_id'],
									  'date'=>date('Y-m-d H:i:s'),
									 );
				$this->common_model->insert('lob_credit_history', $insert_data);
			}
			redirect('direct_mails/view/'.$postcard_queue['id'].'/'.$campaign_id);
		}
    }
	
	//cancel letter by campaign
	public function cancel_letter_campaign()
	{
		if ($_POST) {
			$campaign_id	=	$this->input->post('campaign_id');
			
			$results	=	$this->common_model->get_all('lob_to_address',array('campaign_id'=>$campaign_id,'user_id'=>$this->session->userdata('user_id'),'type'=>'letter'),'',0,0,'array');
			$update_data	=	array();
			$insert_data	=	array();
			$where_inarr	=	array();
			$rev_amount	=	0;
			$charged_amount	=	array();
			$cancelled_data	=	array();
			if(!empty($results))
			{
				foreach($results as $row)
				{
					$ret	=	$this->cancel_letters($row);
					if(!empty($ret)){
						$update_data[]	=	$ret;
						if(isset($ret['status']))
						{
							$rev_amount		+=	$row['amt_charge'];
							
							$insert_data[]	=	array(
									  'queue_id' => $row['queue_id'],
									  'status'=>4,
									  'credited_amt'=>$row['amt_charge'],
									  'client_id'=>$row['client_id'],
									  'user_id'=>$row['user_id'],
									  'date'=>date('Y-m-d H:i:s'),
								 );
							if(!in_array($row['queue_id'],$where_inarr) && $row['queue_id']!=0)
							{
								$where_inarr[]	=$row['queue_id'];
								
							}
							$cancelled_data[$row['queue_id']][]	=	$ret;
							if(isset($charged_amount[$row['queue_id']])){
								$charged_amount[$row['queue_id']]	+=	$row['amt_charge'];
							}
							else{
								$charged_amount[$row['queue_id']]	=	$row['amt_charge'];
							}
						}
					}
				}
			}
			if(!empty($update_data))
			{
				$this->common_model->update_batch('lob_to_address',$update_data,'id');
			}
			if(!empty($insert_data))
			{
				$this->common_model->update_balance($rev_amount,array('client_id' => $this->session->userdata('client_id')));
				
				
				$this->common_model->insert_batch('lob_credit_history', $insert_data);
				
				$usage_history	=	$this->common_model->get_all('usage_history','','',0,0,'array','queue_id',$where_inarr);
				$delete_data	=	array();
				$usage_data		=	array();
				if(!empty($usage_history))
				{
					foreach($usage_history as $row){
						if($row['item_count']==count($cancelled_data[$row['queue_id']]))
						{
							$delete_data[]	=	$row['queue_id'];
						}
						else{
							$charged_amount	=	$row['charged_amount']-$charged_amount[$row['queue_id']];
							$item_count		=	$row['item_count']-count($cancelled_data[$row['queue_id']]);
							$usage_data[]	=	array(
													'queue_id'		=>$row['queue_id'],
													'charged_amount'=>$charged_amount,
													'item_count'	=>$item_count,
												);
						}
					}
				}
				if(!empty($delete_data))
				{
					$this->common_model->delete_batch('usage_history','queue_id',$delete_data);
				}
				if(!empty($usage_data))
				{
					$this->common_model->update_batch('usage_history',$usage_data,'queue_id');
				}
				
			}
		
			$this->session->set_flashdata('msg', 'Letter(s) cancelled successfully.');	
			$redirect_uri	=	$this->input->post('redirect_uri');
			redirect($redirect_uri);
        }
	}
	//cancel postcards by campaign
	public function cancel_postcard_campaign()
	{
		if ($_POST) {
			$campaign_id	=	$this->input->post('campaign_id');
			
			$results	=	$this->common_model->get_all('lob_to_address',array('campaign_id'=>$campaign_id,'user_id'=>$this->session->userdata('user_id'),'type'=>'postcard'),'',0,0,'array');
			$update_data	=	array();
			$rev_amount	=	0;
			$insert_data	=	array();
			$where_inarr	=	array();
			$charged_amount	=	array();
			$cancelled_data	=	array();
			if(!empty($results))
			{
				foreach($results as $row)
				{
					
						$ret	=	$this->cancel_postcards($row);
						if(!empty($ret)  ){
							$update_data[]	=	$ret;
							if(isset($ret['status'])){
								
								$rev_amount		+=	$row['amt_charge'];
								
								$insert_data[]	=	array(
										  'queue_id' => $row['queue_id'],
										  'status'=>4,
										  'credited_amt'=>$row['amt_charge'],
										  'client_id'=>$row['client_id'],
										  'user_id'=>$row['user_id'],
										  'date'=>date('Y-m-d H:i:s'),
									 );
								if(!in_array($row['queue_id'],$where_inarr) && $row['queue_id']!=0)
								{
									$where_inarr[]	=$row['queue_id'];
									
								}
								$cancelled_data[$row['queue_id']][]	=	$ret;
								if(isset($charged_amount[$row['queue_id']])){
									$charged_amount[$row['queue_id']]	+=	$row['amt_charge'];
								}
								else{
									$charged_amount[$row['queue_id']]	=	$row['amt_charge'];
								}
							}
						
						}
					
				}
			}
			if(!empty($update_data))
			{
				$this->common_model->update_batch('lob_to_address',$update_data,'id');

			}
			
			if(!empty($insert_data))
			{	
				$this->common_model->update_balance($rev_amount,array('client_id' => $this->session->userdata('client_id')));
				
				$this->common_model->insert_batch('lob_credit_history', $insert_data);
				$usage_history	=	$this->common_model->get_all('usage_history','','',0,0,'array','queue_id',$where_inarr);
				$delete_data	=	array();
				$usage_data		=	array();
				if(!empty($usage_history))
				{
					foreach($usage_history as $row){
						if($row['item_count']==count($cancelled_data[$row['queue_id']]))
						{
							$delete_data[]	=	$row['queue_id'];
						}
						else{
							$charged_amount	=	$row['charged_amount']-$charged_amount[$row['queue_id']];
							$item_count		=	$row['item_count']-count($cancelled_data[$row['queue_id']]);
							$usage_data[]	=	array(
													'queue_id'		=>$row['queue_id'],
													'charged_amount'=>$charged_amount,
													'item_count'	=>$item_count,
												);
						}
					}
				}
				if(!empty($delete_data))
				{
					$this->common_model->delete_batch('usage_history','queue_id',$delete_data);
				}
				if(!empty($usage_data))
				{
					$this->common_model->update_batch('usage_history',$usage_data,'queue_id');
				}

			}
			$this->session->set_flashdata('msg', 'Postcard(s) cancelled successfully.');
			$redirect_uri	=	$this->input->post('redirect_uri');
			redirect($redirect_uri);
        }
	}
	//cancel letters in a single page
	public function multiple_cancel_letters()
	{
		if ($_POST) {
			$where_inarr	=	array();
            foreach ($_POST['ids'] as $id) {
				$where_inarr[]	=	$id;
                
            }
			if(!empty($where_inarr))
			{
				$results	=	$this->common_model->get_all('lob_to_address','','',0,0,'array','id',$where_inarr);
				$update_data	=	array();
				$rev_amount	=	0;
				$update_data	=	array();
				$insert_data	=	array();
				$where_inarr	=	array();
				$charged_amount	=	array();
				$cancelled_data	=	array();
				if(!empty($results))
				{
					foreach($results as $row)
					{
						$ret	=	$this->cancel_letters($row);
						if(!empty($ret)){
							$update_data[]	=	$ret;
							if(isset($ret['status'])){
								$rev_amount		+=	$row['amt_charge'];
							
								$insert_data[]	=	array(
									  'queue_id' => $row['queue_id'],
									  'status'=>4,
									  'credited_amt'=>$row['amt_charge'],
									  'client_id'=>$row['client_id'],
									  'user_id'=>$row['user_id'],
									  'date'=>date('Y-m-d H:i:s'),
								 );
								if(!in_array($row['queue_id'],$where_inarr) && $row['queue_id']!=0)
								{
									$where_inarr[]	=$row['queue_id'];
									
								}
								$cancelled_data[$row['queue_id']][]	=	$ret;
								if(isset($charged_amount[$row['queue_id']])){
									$charged_amount[$row['queue_id']]	+=	$row['amt_charge'];
								}
								else{
									$charged_amount[$row['queue_id']]	=	$row['amt_charge'];
								}
							}
						}
					}
				}
				if(!empty($update_data))
				{
					$this->common_model->update_batch('lob_to_address',$update_data,'id');

					$activity_data	=	array(
												'client_id'=>$this->session->userdata('client_id'),
												'user_id'=>$this->session->userdata('user_id'),
												'activity'=>count($update_data).' letters cancelled',
												'type'=>'direct_mail',
												'date_time'=>date('m-d-Y H:i A'),
											);
					$this->common_model->insert('activities', $activity_data);
				}
				if(!empty($insert_data)){
					$this->common_model->update_balance($rev_amount,array('client_id' => $this->session->userdata('client_id')));
					
					$this->common_model->insert_batch('lob_credit_history', $insert_data);
					$usage_history	=	$this->common_model->get_all('usage_history','','',0,0,'array','queue_id',$where_inarr);
					$delete_data	=	array();
					$usage_data		=	array();
					if(!empty($usage_history))
					{
						foreach($usage_history as $row){
							if($row['item_count']==count($cancelled_data[$row['queue_id']]))
							{
								$delete_data[]	=	$row['queue_id'];
							}
							else{
								$charged_amount	=	$row['charged_amount']-$charged_amount[$row['queue_id']];
								$item_count		=	$row['item_count']-count($cancelled_data[$row['queue_id']]);
								$usage_data[]	=	array(
														'queue_id'		=>$row['queue_id'],
														'charged_amount'=>$charged_amount,
														'item_count'	=>$item_count,
													);
							}
						}
					}
					if(!empty($delete_data))
					{
						$this->common_model->delete_batch('usage_history','queue_id',$delete_data);
					}
					if(!empty($usage_data))
					{
						$this->common_model->update_batch('usage_history',$usage_data,'queue_id');
					}
				}
					
				echo 1;exit;	
			}
        }
	}
	//cancel postcards in a single page by checkbox
	public function multiple_cancel_postcards()
	{
		if ($_POST) {
			$where_inarr	=	array();
            foreach ($_POST['ids'] as $id) {
				$where_inarr[]	=	$id;
            }
			if(!empty($where_inarr))
			{
				$results	=	$this->common_model->get_all('lob_to_address','','',0,0,'array','id',$where_inarr);
				$update_data	=	array();
				$rev_amount	=	0;
				$update_data	=	array();
				$insert_data	=	array();
				$where_inarr	=	array();
				$charged_amount	=	array();
				$cancelled_data	=	array();
				if(!empty($results))
				{
					foreach($results as $row)
					{
						$ret	=	$this->cancel_postcards($row);
						if(!empty($ret)){
							$update_data[]	=	$ret;
							if(isset($ret['status'])){
								$rev_amount		+=	$row['amt_charge'];
								$insert_data[]	=	array(
									  'queue_id' => $row['queue_id'],
									  'status'=>4,
									  'credited_amt'=>$row['amt_charge'],
									  'client_id'=>$row['client_id'],
									  'user_id'=>$row['user_id'],
									  'date'=>date('Y-m-d H:i:s'),
								 );
								if(!in_array($row['queue_id'],$where_inarr) && $row['queue_id']!=0)
								{
									$where_inarr[]	=$row['queue_id'];
									
								}
								$cancelled_data[$row['queue_id']][]	=	$ret;
								if(isset($charged_amount[$row['queue_id']])){
									$charged_amount[$row['queue_id']]	+=	$row['amt_charge'];
								}
								else{
									$charged_amount[$row['queue_id']]	=	$row['amt_charge'];
								}
							}
						}
					}
				}
				if(!empty($update_data))
				{
					$this->common_model->update_batch('lob_to_address',$update_data,'id');

					$activity_data	=	array(
												'client_id'=>$this->session->userdata('client_id'),
												'user_id'=>$this->session->userdata('user_id'),
												'activity'=>count($update_data).' postcards cancelled',
												'type'=>'direct_mail',
												'date_time'=>date('m-d-Y H:i A'),
											);
					$this->common_model->insert('activities', $activity_data);
				}
				
				//usage history and credit details
				if(!empty($insert_data)){
					$this->common_model->update_balance($rev_amount,array('client_id' => $this->session->userdata('client_id')));
					$this->common_model->insert_batch('lob_credit_history', $insert_data);
				
					$usage_history	=	$this->common_model->get_all('usage_history','','',0,0,'array','queue_id',$where_inarr);
					$delete_data	=	array();
					$usage_data		=	array();
					if(!empty($usage_history))
					{
						foreach($usage_history as $row){
							if($row['item_count']==count($cancelled_data[$row['queue_id']]))
							{
								$delete_data[]	=	$row['queue_id'];
							}
							else{
								$charged_amount	=	$row['charged_amount']-$charged_amount[$row['queue_id']];
								$item_count		=	$row['item_count']-count($cancelled_data[$row['queue_id']]);
								$usage_data[]	=	array(
														'queue_id'		=>$row['queue_id'],
														'charged_amount'=>$charged_amount,
														'item_count'	=>$item_count,
													);
							}
						}
					}
					if(!empty($delete_data))
					{
						$this->common_model->delete_batch('usage_history','queue_id',$delete_data);
					}
					if(!empty($usage_data))
					{
						$this->common_model->update_batch('usage_history',$usage_data,'queue_id');
					}
				}
					
				
						
				echo 1;exit;	
			}
        }
	}
	
	//cancelling individual letters
	public function cancel_letters($row=array())
	{
		if(!empty($row))
		{	
			$update_data	=	array();
			date_default_timezone_set("UTC");
			if($row['status']==1  && $row['lob_postcard_id']!='' && (date('Y-m-d\TH:i:s.0\Z')<$row['send_date']) )
			{
				//processed and todays date less than send date
				$update_data['id']	=	$row['id'];
				
				//fetching api key
				$private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
				$lob = new Create_letter($private_key->value);
				
				$cancel_data    =    $lob->cancel_letter($row['lob_postcard_id']);
				if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
					$update_data['err_message']	=	$cancel_data['message'];
					
				}
				else if (isset($cancel_data['deleted']) && $cancel_data['deleted']==true) {
					
					$this->common_model->update('lob_letters',array('id'=>$row['lob_postcard_id']),array('status'=>4));
					$update_data['status']	=	4;
					
				}
			}
			else if($row['status']==0 ){
				
				//pending
				$update_data['id']	=	$row['id'];
				$update_data['status']	=	4;
			}
			
			return $update_data;
			
		}
	}
	//cancelling individual postcards
	public function cancel_postcards($row=array())
	{
		if(!empty($row))
		{	
			$update_data	=	array();
			date_default_timezone_set("UTC");
			//processed and send date greater than current date time
			if($row['status']==1 && $row['lob_postcard_id']!='' && (date('Y-m-d\TH:i:s.0\Z')<$row['send_date']) )
			{
				
				$update_data['id']	=	$row['id'];
				
				//fetching api key
				$private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
				$lob = new LobSend($private_key->value);
				
				//us address verification
				$cancel_data    =    $lob->cancel_postcard($row['lob_postcard_id']);
				if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
					$update_data['err_message']	=	$cancel_data['message'];
					
				}
				else if (isset($cancel_data['deleted']) && $cancel_data['deleted']==true) {
					
					$this->common_model->update('lob_postcards',array('id'=>$row['lob_postcard_id']),array('status'=>4));
					$update_data['status']	=	4;
					
				}
			}
			//pending
			else if($row['status']==0){
				
				
				$update_data['id']	=	$row['id'];
				$update_data['status']	=	4;
			}
			
			return $update_data;
			
		}
	}
    public function price_calculation()
    {
    	if($_POST)
    	{
    		$bill_account = $this->common_model->get('billing_accounts', array('client_id' => $this->session->userdata('client_id')));
    		$data    =    array();

    		//postcard charge
			if ($this->input->post('mail_type')=="postcard") {
				if ($this->input->post('size')=="4x6") {
					$amt_charge = get_usage_charge('postcard_4_6');
					
					//$amt_charge = ($totalPostCard < 1000)?get_usage_charge('postcard_4_6_1000'):get_usage_charge('postcard_4_6');
				} elseif ($this->input->post('size')=="6x11") {
					$amt_charge = get_usage_charge('postcard_6_11');
					
					
				}
			}
			//letter charge
			else if ($this->input->post('mail_type')=="letter") {
				if ($this->input->post('color')==0) {
					$amt_charge = get_usage_charge('letter_black_white');
					
					
				} elseif ($this->input->post('color')==1) {
					$amt_charge = get_usage_charge('letter_color');
				}
			}

	        //if (isset($_FILES['userfile']) && $_FILES['userfile']['name']!='') {
	    	if (($this->input->post('address_option')=='upload_csv' || $this->input->post('address_option')=='upload_csv_no') && isset($_FILES['userfile']) && $_FILES['userfile']['name']!='') {
				
				if (pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION)!="csv") {
					$data['message']    =   'The file you uploaded is not a valid CSV file.';
					$data['status']    =    false;
				}
				else{
	            
					//fetching  postcard charge
					
					$csv_file =  $_FILES['userfile']['tmp_name']; // Name of your CSV file
					$fp = file($csv_file, FILE_SKIP_EMPTY_LINES);
					$mail_count    =    count($fp);
					$totalPostCard = $mail_count - 1;
					if($totalPostCard > 0)
				 	{

						//checking balance
						if ($bill_account->balance < ($totalPostCard*$amt_charge)) {
							//no balance
							$data['message']    =    "Total cost for creating ".($mail_count-1)." ".$this->input->post('mail_type')." is $".(($mail_count-1)*$amt_charge).".You don't have enough balance in your account for sending ".$this->input->post('mail_type').". Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.";
							$data['status']    =    false;
						} else {
							$total_amt	=	number_format((float)(($mail_count-1)*$amt_charge), 2, '.', '');
							$data['message']    =    "<b>PLEASE DOUBLE CHECK YOUR MAILING LISTS!</b><br/><br/>Once you send a piece of mail it is transmitted immediately. Think of our product like email.<p>You will be charged $".$total_amt." for ".($mail_count-1)." ".$this->input->post('mail_type')."(s). Do you want to continue?</p>";
							$data['status']    =    true;
						}
					}
					else{
						//no postcards or invalid csv file
						$data['message']    =    "There is no contacts found or the file you uploaded is not a valid CSV file.";
						$data['status']    =    false;
						
					}
				}
	           
	        }
	        else if ($this->input->post('address_option')=='all_contacts' || $this->input->post('address_option')=='exclude_contacts')
	        {
				
				if ($this->input->post('campaign_id')=="") {
					$data['message']    =   'Please select a campaign.';
					$data['status']    =    false;
				}
				else{
	            
					//fetching user account balance and postcard charge
					
					$contacts = $this->direct_mail->get_mailing_addresses($_POST);

                    $states_org = $this->common_model->getStates();

                    $mail_count    =    count($contacts);

                    $totalPostCard = $mail_count;
	                    

					if($totalPostCard > 0)
				 	{

						//checking balance
						if ($bill_account->balance < ($totalPostCard*$amt_charge)) {
							//no balance
							$data['message']    =    "Total cost for creating ".($mail_count)." ".$this->input->post('mail_type')." is $".(($mail_count)*$amt_charge).".You don't have enough balance in your account for sending ".$this->input->post('mail_type').". Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.";
							$data['status']    =    false;
						} else {
							$total_amt	=	number_format((float)(($mail_count)*$amt_charge), 2, '.', '');
							$data['message']    =    "<b>PLEASE DOUBLE CHECK YOUR MAILING LISTS!</b><br/><br/>Once you send a piece of mail it is transmitted immediately. Think of our product like email.<p>You will be charged $".$total_amt." for ".($mail_count)." ".$this->input->post('mail_type')."(s). Do you want to continue?</p>";
							$data['status']    =    true;
						}
					}
					else{
						//no postcards or invalid csv file
						$data['message']    =    "There is no contacts found.";
						$data['status']    =    false;
						
					}
				}
	           
	        }
	        echo json_encode($data);
    	}
    }
	public function check_data_variables()
	{
		if ($this->input->post('template_type')=="own_design") {
			$front_matches[1]	=	array();
			if($this->input->post('front_option')=="paste_html")
			{

				if($this->input->post('front_paste_html')=='')
				{
					return true;
				}
				
				$postcard_data['front']=$this->input->post('front_paste_html');
				
				//find all string inside double curly braces
				$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
				preg_match_all($regex, $postcard_data['front'], $front_matches);
	
				//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
				$front_matches[1] = $this->find_replace($front_matches[1]);
				
				if(!empty($front_matches[1]))
				{
					for($i=0;$i<count($front_matches[1]);$i++)
					{
						$org_variable[$front_matches[1][$i]]	=	$front_matches[0][$i];
					}
				}

			}
			$back_matches[1]	=	array();
			if($this->input->post('back_option')=="paste_html")
			{
				if($this->input->post('back_paste_html')=='')
				{
					return true;
				}
				$postcard_data['back'] = $this->input->post('back_paste_html');
				
				//find all string inside double curly braces
				$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
				preg_match_all($regex, $postcard_data['back'], $back_matches);
				
				//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
				$back_matches[1] = $this->find_replace($back_matches[1]);
				
				
				if(!empty($back_matches[1]))
				{
					for($i=0;$i<count($back_matches[1]);$i++)
					{
						$org_variable[$back_matches[1][$i]]	=	$back_matches[0][$i];
					}
				}

				
				
			}
		}
		
		else if ($this->input->post('template_type')=="predefined") {
					if($this->input->post('template')=='')
					{
						return true;
					}
                    //fetching front and back html for selected template
                    $data    =    $this->common_model->get("lob_templates", array('id'=>$this->input->post('template')), 'array', 'front_html,back_html');
                    $postcard_data['front']    =    $data['front_html'];
                    $postcard_data['back']    =    $data['back_html'];

                    //find all string inside double curly braces
                    $regex    =    "~\{\{\s*(.*?)\s*\}\}~";
                    preg_match_all($regex, $postcard_data['back'], $back_matches);
                    preg_match_all($regex, $postcard_data['front'], $front_matches);

                    //replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
                    $back_matches[1] = $this->find_replace($back_matches[1]);
                    $front_matches[1] = $this->find_replace($front_matches[1]);
					$org_variable	=	array();
					if(!empty($back_matches[1]))
					{
						for($i=0;$i<count($back_matches[1]);$i++)
						{
							$org_variable[$back_matches[1][$i]]	=	$back_matches[0][$i];
						}
					}
					if(!empty($front_matches[1]))
					{
						for($i=0;$i<count($front_matches[1]);$i++)
						{
							$org_variable[$front_matches[1][$i]]	=	$front_matches[0][$i];
						}
					}

             }
				
			if ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {

					if($_FILES['userfile']['name']=='')
					{
						return true;
					}
                    $states_org = $this->common_model->getStates();
					$csv_file = $_FILES['userfile']['tmp_name'];
                    
                    $fp = file($csv_file, FILE_SKIP_EMPTY_LINES);
                    $mail_count    =    count($fp);

					
                    $csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );
					$unmatched=	array();
					
					$all_variables	=	array_merge($front_matches[1],$back_matches[1]);
					if (isset($all_variables) && !empty($all_variables)) {
						foreach ($all_variables as $row) {

									
							$search_array = array_map('strtolower', $csv_headers);		
							
							if(!in_array(strtolower($row),$search_array) && !in_array($row,$unmatched)){
								$unmatched[]	=	(isset($org_variable[$row]))?$org_variable[$row]:'';
							}
						}
					}
					 
					if(!empty($unmatched)){
					
						$this->form_validation->set_message('check_data_variables', "Following data variables not found in uploaded CSV : ".implode(', ',$unmatched));
						 return false;
					}
                     

					
                }
				else if ($this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") {
					$contacts = $this->direct_mail->get_mailing_addresses($_POST);

                    $csv_headers	=	array('name','address_line1','address_line2','address_city','address_state','address_zip');  
					
                    $k=0;
                    $j=1;
                    $arrayKey = 0;
					
					$unmatched=	array();
					
					$all_variables	=	array_merge($front_matches[1],$back_matches[1]);
					if (isset($all_variables) && !empty($all_variables)) {
						foreach ($all_variables as $row) {

									
							$search_array = $csv_headers;		
							
							if(!in_array(strtolower($row),$search_array) && !in_array($row,$unmatched)){
								$unmatched[]	=	(isset($org_variable[$row]))?$org_variable[$row]:'';
							}
						}
					}
					 
					if(!empty($unmatched)){
					
						$this->form_validation->set_message('check_data_variables', "Following data variables not found : ".implode(', ',$unmatched));
						 return false;
					}


                }
	}
	public function get_contacts()
    {
        if ($_POST) {
            $html = '';
            $leads = $this->direct_mail->get_mailing_addresses($_POST);
            $count=0;
            if(!empty($leads))
            {
		        foreach ($leads as $lead) {
		        	if($lead['name']!='' && $lead['address_line1']!='' && $lead['address_city']!='' && $lead['address_state']!='' && $lead['address_zip']!='')
		        	{
		            	$html .= '	<div class="col-md-4">
			                            <div class="checkbox">
			                                <label>
			                                    <input type="checkbox" class="checklist  icheck-ind" value="'.$lead['id'].'" name="contacts[]"    checked>'.
			                                     $lead['name']
			                                .'</label>
			                            </div>
	                          		</div>';
	                    $count++;
                    }
		        }
	    	}
           else{
           	$html .= '	<div class="col-md-8">
		                             <div class="alert alert-danger fade in">

                                        No contacts found
                                    </div>
                          		</div>';
           }
           
            $response	=	array(
            					'html'=>$html,
            					'count'=>$count,
            				);
            echo json_encode($response);
        }
    }
    //CREATE POSTCARD AND SEND
    public function create()
    {	//echo $this->session->userdata('client_id');exit;
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
        /*echo date('Y-m-d h:i:sa','1489968000');exit;
         echo date_create( date('Y-m-d'), timezone_open( 'UTC' ) )->getTimestamp();exit;*/
         set_time_limit(0);
        $data['action']  = 'Add';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title']  = 'Add Postcard';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', '/direct_mails');
        $this->breadcrumbs->push('Add Postcard', '/');
        $this->load->library('form_validation');


        $defaults = array(
            "campaign_id"=>'',
            "userfile"=>"",
             "size"    =>    "",
             "address_option"    =>    "",
             "name"    =>    "",
             "address_line1"    =>    "",
             "address_line2"    =>    "",
             "address_city"    =>"",
             "address_state"    =>"",
             "address_zip"    =>"",
             "from_address_option"=>"",
             "from_name"    =>"",
             "from_address_line1"    =>"",
             "from_address_line2"    =>"",
             "from_address_city"    =>"",
             "from_address_state"    =>"",
             "from_address_zip"    =>"",
             "front_option"    =>"",
             "front_paste_html"    =>"",
             "back_option"    =>"",
             "back_paste_html"    =>"",
            "messageAndBack"=>'',
            "message"=>'',
            "template_type"    =>'',
            "send_date"        =>'',
            "mail_drops"        =>'',
            'template'=>'',



        );
        if ($_POST) {
            
            $this->form_validation->set_message('required', '%s is required.');
            $this->form_validation->set_rules('size', 'Size', 'required');
            $this->form_validation->set_rules('campaign_id', 'Campaign', 'required');

            if ($this->input->post('template_type')=="own_design") {
				
				if($this->input->post('front_option')=="paste_html")
				{
					$this->form_validation->set_rules('front_paste_html', 'Paste HTML', 'required');
				}
				if($this->input->post('front_option')=="choose_file")
				{	
					$this->form_validation->set_rules('front_userfile', 'File', 'callback_front_file_type');
				}
				if($this->input->post('messageAndBack')=="message")
				{
					$this->form_validation->set_rules('message', 'Message', 'required|callback_validate_message');
				}
				else if($this->input->post('messageAndBack')=="back"){
					if($this->input->post('back_option')=="paste_html"){
						$this->form_validation->set_rules('back_paste_html', 'Paste HTML', 'required');
					}
					if($this->input->post('back_option')=="choose_file"){
						$this->form_validation->set_rules('back_userfile', 'File', 'callback_back_file_type');
					}
				}
			} elseif ($this->input->post('template_type')=="predefined") {
                $this->form_validation->set_rules('template', 'Template', 'required');
            }
            if ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {
                $this->form_validation->set_rules('userfile', 'CSV file', 'callback_check_file_type');
            }
			
			if(($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no" || $this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") && ($this->input->post('front_option')=="paste_html" || $this->input->post('back_option')=="paste_html" || $this->input->post('template_type')=="predefined"))
			{
				 $this->form_validation->set_rules('data_variables', 'Data Variables', 'callback_check_data_variables');
			}
            if ($this->input->post('address_option')=="create_new") {
                $this->form_validation->set_rules('name', 'Name', 'required');
                $this->form_validation->set_rules('address_line1', 'Address Line1', 'required');
                $this->form_validation->set_rules('address_city', 'City', 'required');
                $this->form_validation->set_rules('address_state', 'State', 'required');
                $this->form_validation->set_rules('address_zip', 'Zip/Postal Code', 'required');
            }
            $this->form_validation->set_rules('from_address_option', 'From Address', 'required');
            if ($this->input->post('from_address_option')=="create_new") {
                $this->form_validation->set_rules('from_name', 'Name', 'required');
                $this->form_validation->set_rules('from_address_line1', 'Address Line1', 'required');
                $this->form_validation->set_rules('from_address_city', 'City', 'required');
                $this->form_validation->set_rules('from_address_state', 'State', 'required');
                $this->form_validation->set_rules('from_address_zip', 'Zip/Postal Code', 'required');
            }

            $this->form_validation->set_error_delimiters('<span class="has-error help-block">', '</span>');




            if (!$this->form_validation->run()) {
                $data['postcard'] = array_merge($defaults, $_POST);
            } else {

                //fetching user account balance and postcard charge
                $bill_account = $this->common_model->get('billing_accounts', array('client_id' => $this->session->userdata('client_id')));
                
                $postcard_data = array(

                                    "size"    =>    $this->input->post('size'),

                              );
                if ($this->input->post('from_address_option')=="create_new") {
                    $postcard_data['from'] = $from_address =    array(

                                                               'name'=>$this->input->post('from_name'),
                                                               'address_line1'=>$this->input->post('from_address_line1'),
                                                               'address_line2'=>$this->input->post('from_address_line2'),
                                                               'address_city'=>$this->input->post('from_address_city'),
                                                               'address_state'=>$this->input->post('from_address_state'),
                                                               'address_zip'=>$this->input->post('from_address_zip'),

                                                                );

                    $from_address['user_id']    =    $this->session->userdata('user_id');
                    $from_address['client_id']    =    $this->session->userdata('client_id');
                } else {
                    $postcard_data['from']    =    $this->common_model->get("lob_address", array('id'=>$this->input->post('from_address_option')), 'array', 'name,address_line1,address_line2,address_city,address_state,address_zip');
                }

    //front content
                if ($this->input->post('template_type')=="own_design") {
					
					if($this->input->post('front_option')=="choose_file")
					{
						$config['upload_path'] = './uploads/direct_mail/'; 
						$config['allowed_types'] = 'pdf|png|jpeg|jpg'; 
						
						$this->load->library('upload', $config); 
						if ( ! $this->upload->do_upload('front_userfile')) 
						{ 
	//if not uploaded 
							$error =  $this->upload->display_errors(); 
				 
							$this->session->set_flashdata('errors', $error); 
				 
							redirect('direct_mails/create/'); 
						}
						else{
							$file = $this->upload->data(); 
							$data['filename']	=	$file['file_name']; 
							$postcard_data['front'] =  base_url().'uploads/direct_mail/'.$file['file_name'];
							$front_file	=	$file['file_name'];
						}
						
					}
					else if($this->input->post('front_option')=="paste_html")
					{
						
						$postcard_data['front']=$this->input->post('front_paste_html');
						
						//find all string inside double curly braces
						$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
						preg_match_all($regex, $postcard_data['front'], $front_matches);
	
						//replace all white spaces and hypens with underscore inside double curly braces(not removing double curly braces)
						$front_replaces = $this->find_replace($front_matches[0]);
	
						//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
						$front_matches[1] = $this->find_replace($front_matches[1]);
						
						
						//final front and back html, changed data variables format acceptable by lob
						$postcard_data['front'] = str_replace($front_matches[0], $front_replaces, $postcard_data['front']);
						
					}
		
		//back content,either message or back is needed
					if($this->input->post('messageAndBack')=="back")
					{
						if($this->input->post('back_option')=="choose_file")
						{
							$config['upload_path'] = './uploads/direct_mail/'; 
							$config['allowed_types'] = 'pdf|png|jpeg|jpg'; 
							
							$this->load->library('upload', $config); 
							if ( ! $this->upload->do_upload('back_userfile')) 
							{ 
		//if not uploaded 
								$error =  $this->upload->display_errors(); 
					 
								$this->session->set_flashdata('errors', $error); 
					 
								redirect('direct_mails/create/'); 
							}
							else{
								$file = $this->upload->data(); 
								$data['filename']	=	$file['file_name'];
		
								$postcard_data['back'] =  base_url().'uploads/direct_mail/'.$file['file_name'];
								$back_file	=	$file['file_name'];
							}
						}
					
						else if($this->input->post('back_option')=="paste_html")
						{
							$postcard_data['back'] = $this->input->post('back_paste_html');
							
							//find all string inside double curly braces
							$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
							preg_match_all($regex, $postcard_data['back'], $back_matches);
							
		
							//replace all white spaces and hypens with underscore inside double curly braces(not removing double curly braces)
							$back_replaces = $this->find_replace($back_matches[0]);
							
		
							//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
							$back_matches[1] = $this->find_replace($back_matches[1]);
							

							//final front and back html, changed data variables format acceptable by lob
							
							$postcard_data['back'] = str_replace($back_matches[0], $back_replaces, $postcard_data['back']);
							
						}
					}
					else if($this->input->post('messageAndBack')=="message"){
						$postcard_data['message'] = $this->input->post('message');
					}
				}

//if using predefined template
                elseif ($this->input->post('template_type')=="predefined") {
                    //fetching front and back html for selected template
                    $data    =    $this->common_model->get("lob_templates", array('id'=>$this->input->post('template')), 'array', 'front_html,back_html');
                    $postcard_data['front']    =    $data['front_html'];
                    $postcard_data['back']    =    $data['back_html'];

                    //find all string inside double curly braces
                    $regex    =    "~\{\{\s*(.*?)\s*\}\}~";
                    preg_match_all($regex, $postcard_data['back'], $back_matches);
                    preg_match_all($regex, $postcard_data['front'], $front_matches);

                    //replace all white spaces and hypens with underscore inside double curly braces(not removing double curly braces)
                    $back_replaces = $this->find_replace($back_matches[0]);
                    $front_replaces = $this->find_replace($front_matches[0]);

                    //replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
                    $back_matches[1] = $this->find_replace($back_matches[1]);
                    $front_matches[1] = $this->find_replace($front_matches[1]);
					
                    //final front and back html, changed data variables format acceptable by lob
                    $postcard_data['front'] = str_replace($front_matches[0], $front_replaces, $postcard_data['front']);
                    $postcard_data['back'] = str_replace($back_matches[0], $back_replaces, $postcard_data['back']);
                }

//fetching api key
                $private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
                $lob = new LobSend($private_key->value);//'test_3862a0764560e95227a2b84dac329e52226'
//single to address
                if ($this->input->post('address_option')=="create_new") {
                    $postcard_data['to']=array(

                                               'name'=>$this->input->post('name'),
                                               'address_line1'=>$this->input->post('address_line1'),
                                               'address_line2'=>$this->input->post('address_line2'),
                                               'address_city'=>$this->input->post('address_city'),
                                               'address_state'=>$this->input->post('address_state'),
                                               'address_zip'=>$this->input->post('address_zip'),

                                              );

					if ($this->input->post('size')=="4x6") {
						$amt_charge = get_usage_charge('postcard_4_6');
					} elseif ($this->input->post('size')=="6x11") {
						$amt_charge = get_usage_charge('postcard_6_11');
					}
                    //checking balance
                    if ($bill_account->balance < $amt_charge) {
                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending postcard. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");

                        redirect('direct_mails/create/');
                    }
					
					$postcard_data['metadata[user_id]']	= $meta_data['metadata[user_id]'] =$this->session->userdata('user_id');	
					$postcard_data['metadata[username]']	=$meta_data['metadata[username]'] = getUser($this->session->userdata('user_id'));
					$postcard_data['metadata[campaign]']	=$meta_data['metadata[campaign]']	=	direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
					$address_data		=array(

                                               'recipient'=>$this->input->post('name'),
                                               'primary_line'=>$this->input->post('address_line1'),
                                               'secondary_line'=>$this->input->post('address_line2'),
                                               'city'=>$this->input->post('address_city'),
                                               'state'=>$this->input->post('address_state'),
                                               'zip_code'=>$this->input->post('address_zip'),

                                              );
					//us address verification
                    $verified_address    =    $lob->verify_address($address_data);
					if (isset($verified_address['status']) && $verified_address['status']=="failed") {
                        $this->session->set_flashdata('errors', $verified_address['message']);
                        redirect('direct_mails/create');
					}
					elseif (isset($verified_address['deliverability']) && $verified_address['deliverability']!='undeliverable' && $verified_address['deliverability']!='no_match') {
						//postcard send
						$postcardData    =    $lob->send_postcard($postcard_data);
	
						if (isset($postcardData['status']) && $postcardData['status']=="failed") {
							$this->session->set_flashdata('errors', $postcardData['message']);
							redirect('direct_mails/create');
						} elseif (!empty($postcardData)) {//print_r($postcardData);exit;
							$postcard['id'] = $postcardData['id'];
							$postcard['description'] = $postcardData['description'];
							$postcard['metadata'] = $postcardData['metadata'];
							$postcard['to'] = $postcardData['to'];

							$postcard['from'] = $postcardData['from'];
							$postcard['url'] = $postcardData['url'];
							$postcard['carrier'] = $postcardData['carrier'];
							$postcard['tracking_events'] = $postcardData['tracking_events'];
							$postcard['thumbnails'] = $postcardData['thumbnails'];
							$postcard['size'] = $postcardData['size'];
							$postcard['expected_delivery_date'] = $postcardData['expected_delivery_date'];
							$postcard['date_created'] = $postcardData['date_created'];
							$postcard['date_modified'] = $postcardData['date_modified'];
							$postcard['object'] = $postcardData['object'];
							$postcard['send_date'] = $postcardData['send_date'];
							//deducting amount
							$new_balance = $bill_account->balance - $amt_charge;
							$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
							//usage history
							$usage_data	=	array(
												  'client_id'		=>$this->session->userdata('client_id'),
												  'sub_user_id'		=>$this->session->userdata('user_id'),
												  'type'			=>'postcard',
												  'date'			=>date('Y-m-d'),
												  'time_stamp'			=>date('H:i:s'),
												  'charged'			=>1,
												  'charged_amount'	=>$amt_charge,
												  'price_per_item'	=>$amt_charge,
												  'item_count'		=>1,
												  );
							$this->common_model->insert('usage_history',$usage_data);
							
							unset($postcard['metadata'], $postcard['tracking_events'], $postcard['object'], $postcard['message']);
							$postcard['to']    =    serialize($postcard['to']);
							$postcard['from']    =    serialize($postcard['from']);
							$postcard['thumbnails']    =    serialize($postcard['thumbnails']);
							$postcard['user_id']    =    $this->session->userdata('user_id');
							$postcard['client_id']    =    $this->session->userdata('client_id');
							$postcard['campaign_id']    =    $this->input->post('campaign_id');
							$lob_id	=	$this->common_model->insert('lob_postcards', $postcard);
							
							$mailing_address_data	=	$postcard_data['to'];
							$mailing_address_data['campaign_id']			=	$this->input->post('campaign_id');
							$mailing_address_data['user_id']    		=    $this->session->userdata('user_id');
							$mailing_address_data['client_id']    		=    $this->session->userdata('client_id');
							$mailing_address_data['created_date']    			=     date('Y-m-d H:i:s');
							$mailing_address_id = $this->common_model->insert('lead_mailing_address', $mailing_address_data);

							$to_address[0]    					=    $postcard_data['to'];
							$to_address[0]['lob_postcard_id']   =    $postcard['id'];
							$to_address[0]['campaign_id']			=	$this->input->post('campaign_id');
							$to_address[0]['mailing_address_id']			=	$mailing_address_id;
							$to_address[0]['user_id']    		=    $this->session->userdata('user_id');
							$to_address[0]['client_id']    		=    $this->session->userdata('client_id');
							$to_address[0]['type']    			=    'postcard';
							$to_address[0]['size']    			=     $postcard_data['size'];
							$to_address[0]['from']    			=     serialize($postcard_data['from']);
							$to_address[0]['front']   			=     $postcard_data['front'];
							$to_address[0]['messageAndBack']	=	$this->input->post('messageAndBack');
							$to_address[0]['message']    		=     (isset($postcard_data['message']))?$postcard_data['message']:'';
							$to_address[0]['back']    			=     (isset($postcard_data['back']))?$postcard_data['back']:'';
							$to_address[0]['status']    			=     1;
							$to_address[0]['processed_date']    			=     date('Y-m-d H:i:s');
							$to_address[0]['send_date']   =    $postcardData['send_date'];
							$to_address[0]['meta_data']   =     serialize($meta_data);
							$actitvity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>'1 postcard send',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
							$this->common_model->insert('activities', $actitvity_data);
							//$to_address[0]['meta_data']			=	serialize($meta_data);
							//print_r($postcardData['send_date']);exit;
						}
						
		
						//delete fornt file
						if (isset($front_file)) {
							$files    =    glob(getcwd().'/uploads/direct_mail/'.$front_file);
		
							if (!empty($files)) {
								unlink($files[0]);
							}
						}
						//delete back file
						if (isset($back_file)) {
							$files    =    glob(getcwd().'/uploads/direct_mail/'.$back_file);
		
							if (!empty($files)) {
								unlink($files[0]);
							}
						}
						
						
						
		
						//inserting to address(queue)
						if (isset($to_address) && !empty($to_address)) {
							$this->common_model->insert_batch('lob_to_address', $to_address);
						}
						
						

						if (isset($from_address) && !empty($from_address)) {
							//inserting 'from' address
							$this->common_model->insert("lob_address", $from_address);
						}
				
                		$this->session->set_flashdata('msg', 'Postcard(s) Send Successfully!');
						redirect('direct_mails/postcards/'.$this->input->post('campaign_id'));
					}
					else{
							$this->session->set_flashdata('errors', 'This address is not deliverable.');
							redirect('direct_mails/create');
					}
					
                } else if ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {
                    $uploaddir = getcwd().'/uploads/direct_mail/';
                    $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
                    move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);

                    $states_org = $this->common_model->getStates();

                    $csv_file =  getcwd().'/uploads/direct_mail/'.basename($_FILES['userfile']['name']); // Name of your CSV file
                    $fp = file($csv_file, FILE_SKIP_EMPTY_LINES);
                    $mail_count    =    count($fp);

          			/* get postcard count and mail drop count */
                    $totalPostCard = $mail_count - 1;
                    $mailDropCount = $this->input->post('mail_drops');
                    $scheduleDate = '';

                    if ($mailDropCount == "") {

                        /* Empty  Mail Drops. it is required */
                        $this->session->set_flashdata('errors', "Please choose Mail Drops.");
                        redirect('direct_mails/create/');
                    }


                    /* check mails and mail drop counts */
                    if ($mailDropCount > $totalPostCard) {

                        /* wrong mail drop count */
                        $this->session->set_flashdata('errors', "You can't select more than $totalPostCard Mail Drops.");
                        redirect('direct_mails/create/');
                    }

                    /* create Mail Drop Slabs */
                    $mailsPerDrop = ceil($totalPostCard / $mailDropCount);
                    $mailSlab = array();
                    $slabIncrement = 0;

      				/* create a dates for mail drop slabs */
                    for ($k = 0; $k < $mailDropCount; $k++) {
                        $slabIncrement = $mailsPerDrop + $slabIncrement;
                        if ($k == 0) {
                            //$scheduleDate = date('Y-m-d', strtotime(' +1 day'));
							date_default_timezone_set("UTC");
							$scheduleDate	=	 date('Y-m-d\TH:i:s.0\Z',strtotime(' +8 hours '));
                        } else {
                            $scheduleDate = date('Y-m-d', strtotime(' + '. $k .' week'));
                        }
                        $mailSlab[$k] = $scheduleDate .'>'. $slabIncrement;
                    }
					//print_r($totalPostCard);exit;
					if ($this->input->post('size')=="4x6") {
						//$amt_charge = ($totalPostCard < 1000)?get_usage_charge('postcard_4_6_1000'):get_usage_charge('postcard_4_6');
						
                    	$amt_charge = get_usage_charge('postcard_4_6');
					} elseif ($this->input->post('size')=="6x11") {
						$amt_charge = get_usage_charge('postcard_6_11');
						
						//$amt_charge = ($totalPostCard < 1000)?get_usage_charge('postcard_6_11_1000'):get_usage_charge('postcard_6_11');
					}
                    //checking balance
                    if ($bill_account->balance < ($totalPostCard*$amt_charge)) {

                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending postcard. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");
                        redirect('direct_mails/create/');
                    }
					
                    $csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );

                       
					
                    $k=0;
                    $j=1;
                    $arrayKey = 0;
					
					//inserting data to queue_list
					$list_data	=	array('count'=>$totalPostCard,
											  'client_id'=>$this->session->userdata('client_id'),
											  'user_id'=>$this->session->userdata('user_id'),
											  'type'=>'postcard',
											  'created_date'=>date('Y-m-d H:i:s'),
											  );
					$queue_id	=	$this->common_model->insert('lob_queue_list',$list_data);
					$meta_data['metadata[user_id]']	=$this->session->userdata('user_id');	
					$meta_data['metadata[username]']	=getUser($this->session->userdata('user_id'));
					$meta_data['metadata[campaign]']	=direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
                    while (!feof($csvfile)) {
                        $csv_array = fgetcsv($csvfile);

                        $state    = array_search(strtolower($csv_array[5]), array_map('strtolower', $states_org));

                        if (count($csv_headers)==count($csv_array) && $csv_array[0] != '' && $csv_array[1] != '' && $csv_array[3] !='' && $csv_array[4] !='' && $csv_array[5] !='' && (isset($states_org[strtoupper($csv_array[4])]) || $state!='') ) {
                            $postcard_data['to'] = array(
                                        'name'=>$csv_array[0],
                                        'address_line1'=>$csv_array[1],
                                        'address_line2'=>$csv_array[2],
                                        'address_city'=>$csv_array[3],
                                        'address_state'=>$csv_array[4],
                                        'address_zip'=>$csv_array[5]
                                );
							$data_variable	=	array();	
                            if (isset($front_matches[1]) && !empty($front_matches[1])) {
                                foreach ($front_matches[1] as $row) {

									$search_array = array_map('strtolower', $csv_headers);		
									
									if(in_array(strtolower($row),$search_array) ){
										$ckey	=	array_search(strtolower($row),$search_array)	;
										$data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];
											
									}
                                }
                            }

                            if (isset($back_matches[1]) && !empty($back_matches[1])) {
                                foreach ($back_matches[1] as $row) {

									$search_array = array_map('strtolower', $csv_headers);		
									
									if(in_array(strtolower($row),$search_array) ){
										$ckey	=	array_search(strtolower($row),$search_array)	;
										$data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];
											
									}
                                            
                                }
                            }

              				/* Devide CSV records into slabs and assign scheduled date accordingly */
                            if (array_key_exists($arrayKey, $mailSlab)) {
                                $temp = explode('>', $mailSlab[$arrayKey]);
                            }
                            $cnt = count($temp);
                            if ($cnt  > 1) {
                                if ($j >= $temp[1]) {
                                    $arrayKey++;
                                }
                            }
                           	$postcard_data['send_date']    = $temp[0];
							
							$mailing_address_data	=	$postcard_data['to'];
							$mailing_address_data['campaign_id']			=	$this->input->post('campaign_id');
							$mailing_address_data['user_id']    		=    $this->session->userdata('user_id');
							$mailing_address_data['client_id']    		=    $this->session->userdata('client_id');
							$mailing_address_data['created_date']    			=     date('Y-m-d H:i:s');
							$mailing_address_id = $this->common_model->insert('lead_mailing_address', $mailing_address_data);

							$queue_data[$k]    =    $postcard_data['to'];
							
							$queue_data[$k]['user_id']    =    $this->session->userdata('user_id');
							$queue_data[$k]['client_id']    =    $this->session->userdata('client_id');
							$queue_data[$k]['type']    =    'postcard';
							$queue_data[$k]['size']    =     $postcard_data['size'];
							$queue_data[$k]['from']    =     serialize($postcard_data['from']);
							$queue_data[$k]['front']   =     $postcard_data['front'];
							$queue_data[$k]['messageAndBack']	=	$this->input->post('messageAndBack');
							$queue_data[$k]['message']    =     (isset($postcard_data['message']))?$postcard_data['message']:'';;
							$queue_data[$k]['back']    =     (isset($postcard_data['back']))?$postcard_data['back']:'';
							$queue_data[$k]['data']    =     serialize($data_variable);
							$queue_data[$k]['meta_data']    =     serialize($meta_data);
							$queue_data[$k]['send_date']  =     $postcard_data['send_date'];
							$queue_data[$k]['amt_charge']  = $amt_charge;
							$queue_data[$k]['campaign_id']  = $this->input->post('campaign_id');
							$queue_data[$k]['mailing_address_id']  = $mailing_address_id;
                 			$queue_data[$k]['queue_id']  = $queue_id;

                 			
							$k++;
							
                        }
						
                        
                        $j++;
                    }
					
                    fclose($csvfile);
					//delete csv file from folder

                    $files    =    glob(getcwd().'/uploads/direct_mail/'.basename($_FILES['userfile']['name']));

                    if (!empty($files)) {
                        unlink($files[0]);
                    }
					//inserting to address(queue)
					if (isset($queue_data) && !empty($queue_data)) {
						$this->common_model->insert_batch('lob_to_address', $queue_data);
						
						$activity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>count($queue_data).' postcards scheduled',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
						$this->common_model->insert('activities', $activity_data);
						//deducting amount
						$new_balance	=	$bill_account->balance - (count($queue_data)*$amt_charge);
						$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
						
						//usage history
						$usage_data	=	array(
											  'client_id'		=>$this->session->userdata('client_id'),
											  'sub_user_id'		=>$this->session->userdata('user_id'),
											  'type'			=>'postcard',
											  'date'			=>date('Y-m-d'),
											  'time_stamp'		=>date('H:i:s'),
											  'charged'			=>1,
											  'charged_amount'	=>count($queue_data)*$amt_charge,
											  'price_per_item'	=>$amt_charge,
											  'item_count'		=>count($queue_data),
											  'queue_id'		=> $queue_id,
											  );
						$this->common_model->insert('usage_history',$usage_data);
					}
	
					if (isset($from_address) && !empty($from_address)) {
						//inserting 'from' address
						$this->common_model->insert("lob_address", $from_address);
					}
					
					$this->session->set_flashdata('msg', 'Postcard(s) Send Successfully!');
                	redirect('direct_mails/postcards/'.$this->input->post('campaign_id'));
                }
				else if ($this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") {
					$contacts = $this->direct_mail->get_mailing_addresses($_POST);

                    $states_org = $this->common_model->getStates();

                    $mail_count    =    count($contacts);

          			/* get postcard count and mail drop count */
                    $totalPostCard = $mail_count;
                    $mailDropCount = $this->input->post('mail_drops');
                    $scheduleDate = '';

                    if ($mailDropCount == "") {

                        /* Empty  Mail Drops. it is required */
                        $this->session->set_flashdata('errors', "Please choose Mail Drops.");
                        redirect('direct_mails/create/');
                    }


                    /* check mails and mail drop counts */
                    if ($mailDropCount > $totalPostCard) {

                        /* wrong mail drop count */
                        $this->session->set_flashdata('errors', "You can't select more than $totalPostCard Mail Drops.");
                        redirect('direct_mails/create/');
                    }

                    /* create Mail Drop Slabs */
                    $mailsPerDrop = ceil($totalPostCard / $mailDropCount);
                    $mailSlab = array();
                    $slabIncrement = 0;

      				/* create a dates for mail drop slabs */
                    for ($k = 0; $k < $mailDropCount; $k++) {
                        $slabIncrement = $mailsPerDrop + $slabIncrement;
                        if ($k == 0) {
                            //$scheduleDate = date('Y-m-d', strtotime(' +1 day'));
							date_default_timezone_set("UTC");
							$scheduleDate	=	 date('Y-m-d\TH:i:s.0\Z',strtotime(' +8 hours '));
                        } else {
                            $scheduleDate = date('Y-m-d', strtotime(' + '. $k .' week'));
                        }
                        $mailSlab[$k] = $scheduleDate .'>'. $slabIncrement;
                    }
					//print_r($totalPostCard);exit;
					if ($this->input->post('size')=="4x6") {
						//$amt_charge = ($totalPostCard < 1000)?get_usage_charge('postcard_4_6_1000'):get_usage_charge('postcard_4_6');
						
                    	$amt_charge = get_usage_charge('postcard_4_6');
					} elseif ($this->input->post('size')=="6x11") {
						$amt_charge = get_usage_charge('postcard_6_11');
						
						//$amt_charge = ($totalPostCard < 1000)?get_usage_charge('postcard_6_11_1000'):get_usage_charge('postcard_6_11');
					}
                    //checking balance
                    if ($bill_account->balance < ($totalPostCard*$amt_charge)) {

                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending postcard. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");
                        redirect('direct_mails/create/');
                    }
					
                    /*$csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );*/

                     $csv_headers	=	array('name','address_line1','address_line2','address_city','address_state','address_zip');  
					
                    $k=0;
                    $j=1;
                    $arrayKey = 0;
					
					//inserting data to queue_list
					$list_data	=	array('count'=>$totalPostCard,
											  'client_id'=>$this->session->userdata('client_id'),
											  'user_id'=>$this->session->userdata('user_id'),
											  'type'=>'postcard',
											  'created_date'=>date('Y-m-d H:i:s'),
											  );
					$queue_id	=	$this->common_model->insert('lob_queue_list',$list_data);
					$meta_data['metadata[user_id]']	=$this->session->userdata('user_id');	
					$meta_data['metadata[username]']	=getUser($this->session->userdata('user_id'));
					$meta_data['metadata[campaign]']	=direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
                    //while (!feof($csvfile)) {
					if(!empty($contacts))
					{
						foreach ($contacts as $contact) {
							
                        

                       // $state    = array_search(strtolower($contact['state']), array_map('strtolower', $states_org));

                        //if (count($csv_headers)==count($csv_array) && $csv_array[0] != '' && $csv_array[1] != '' && $csv_array[3] !='' && $csv_array[4] !='' && $csv_array[5] !='' && (isset($states_org[strtoupper($csv_array[4])]) || $state!='') ) {
                            $postcard_data['to'] = array(
                                        'name'=>$contact['name'],
                                        'address_line1'=>$contact['address_line1'],
                                        'address_line2'=>$contact['address_line2'],
                                        'address_city'=>$contact['address_city'],
                                        'address_state'=>$contact['address_state'],
                                        'address_zip'=>$contact['address_zip'],
                                );
							$data_variable	=	array();	
                            if (isset($front_matches[1]) && !empty($front_matches[1])) {
                                foreach ($front_matches[1] as $row) {

									//$search_array = array_map('strtolower', $csv_headers);		
									$search_array = $csv_headers;
									if(in_array(strtolower($row),$search_array) ){
										// $ckey	=	array_search(strtolower($row),$search_array)	;
										// $data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];
										
										$data_variable['merge_variables['.$row.']']	=	$contact[$row];	
									}
                                }
                            }

                            if (isset($back_matches[1]) && !empty($back_matches[1])) {
                                foreach ($back_matches[1] as $row) {

									$search_array = $csv_headers;		
									
									if(in_array(strtolower($row),$search_array) ){
										/*$ckey	=	array_search(strtolower($row),$search_array)	;
										$data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];*/
										$data_variable['merge_variables['.$row.']']	=	$contact[$row];		
									}
                                            
                                }
                            }

              				/* Devide CSV records into slabs and assign scheduled date accordingly */
                            if (array_key_exists($arrayKey, $mailSlab)) {
                                $temp = explode('>', $mailSlab[$arrayKey]);
                            }
                            $cnt = count($temp);
                            if ($cnt  > 1) {
                                if ($j >= $temp[1]) {
                                    $arrayKey++;
                                }
                            }
                           	$postcard_data['send_date']    = $temp[0];
						

							$queue_data[$k]    =    $postcard_data['to'];
							
							$queue_data[$k]['user_id']    =    $this->session->userdata('user_id');
							$queue_data[$k]['client_id']    =    $this->session->userdata('client_id');
							$queue_data[$k]['type']    =    'postcard';
							$queue_data[$k]['size']    =     $postcard_data['size'];
							$queue_data[$k]['from']    =     serialize($postcard_data['from']);
							$queue_data[$k]['front']   =     $postcard_data['front'];
							$queue_data[$k]['messageAndBack']	=	$this->input->post('messageAndBack');
							$queue_data[$k]['message']    =     (isset($postcard_data['message']))?$postcard_data['message']:'';;
							$queue_data[$k]['back']    =     (isset($postcard_data['back']))?$postcard_data['back']:'';
							$queue_data[$k]['data']    =     serialize($data_variable);
							$queue_data[$k]['meta_data']    =     serialize($meta_data);
							$queue_data[$k]['send_date']  =     $postcard_data['send_date'];
							$queue_data[$k]['amt_charge']  = $amt_charge;
							$queue_data[$k]['campaign_id']  = $this->input->post('campaign_id');
							$queue_data[$k]['mailing_address_id']  = $contact['id'];
                 			$queue_data[$k]['queue_id']  = $queue_id;

                 			
							$k++;
							
                        //}
						
                        
                        $j++;
						}

                    }
					
                    
					//inserting to address(queue)
					if (isset($queue_data) && !empty($queue_data)) {
						$this->common_model->insert_batch('lob_to_address', $queue_data);
						
						$activity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>count($queue_data).' postcards scheduled',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
						$this->common_model->insert('activities', $activity_data);
						//deducting amount
						$new_balance	=	$bill_account->balance - (count($queue_data)*$amt_charge);
						$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
						
						//usage history
						$usage_data	=	array(
											  'client_id'		=>$this->session->userdata('client_id'),
											  'sub_user_id'		=>$this->session->userdata('user_id'),
											  'type'			=>'postcard',
											  'date'			=>date('Y-m-d'),
											  'time_stamp'		=>date('H:i:s'),
											  'charged'			=>1,
											  'charged_amount'	=>count($queue_data)*$amt_charge,
											  'price_per_item'	=>$amt_charge,
											  'item_count'		=>count($queue_data),
											  'queue_id'		=> $queue_id,
											  );
						$this->common_model->insert('usage_history',$usage_data);
					}
	
					if (isset($from_address) && !empty($from_address)) {
						//inserting 'from' address
						$this->common_model->insert("lob_address", $from_address);
					}
					
					$this->session->set_flashdata('msg', 'Postcard(s) Send Successfully!');
                	redirect('direct_mails/postcards/'.$this->input->post('campaign_id'));
                }
            }
        } else {
            $data['postcard'] = $defaults;
        }

        $data['templates']    =    $this->common_model->get_all('lob_templates','','','','','array','client_id',array(0,$this->session->userdata('client_id')));
//print_r($data['templates']);exit;
        $data['states'] = $this->common_model->getStates();
        $data['from_address']    =    $this->common_model->get_all('lob_address', array('client_id'=>$this->session->userdata('client_id')));
        $data["campaigns"] = $this->common_model->get_all('campaigns', array('user_id'=>$this->session->userdata('user_id'),'deleted'=>0));

        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/form', $data);
        $this->load->view('includes/footer', $data);
}

//CALLBACK FUNCTION FOR VALIDATING CSV FILE
    public function check_file_type()
    {

//checking userfile empty or not
        if (empty($_FILES['userfile']['name'])) {
            $this->form_validation->set_message('check_file_type', 'CSV file is required.');
            return false;
        }
//checking file is csv or not
        elseif (pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION)!="csv") {
            $this->form_validation->set_message('check_file_type', 'The file you uploaded is not a valid CSV file.');
            return false;
        } else {
            return true;
        }
    }

//CALLBACK FUNCTION FOR VALIDATING FRONT FILE
    public function front_file_type()
    {

//checking userfile empty or not
        if (empty($_FILES['front_userfile']['name'])) {
            $this->form_validation->set_message('front_file_type', 'File is required.');
            return false;
        }
//checking file is csv or not
        elseif (pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)!="pdf" && pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)!="png" && pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)!="jpeg" && pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)!="jpg") {
            $this->form_validation->set_message('front_file_type', 'The file you uploaded is not a valid file.Supported file types are PDF, PNG, and JPEG. ');
            return false;
        } elseif (pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)=="png" || pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)=="jpeg" || pathinfo($_FILES['front_userfile']['name'], PATHINFO_EXTENSION)=="jpg") {
            $original_info = getimagesize($_FILES['front_userfile']['tmp_name']);
            if ($this->input->post('size')=='4x6') {
                if ($original_info[0] != 1875 || $original_info[1] != 1275) {
                    $this->form_validation->set_message('front_file_type', 'Image size must be  1875 x 1275 pixels.');
                    return false;
                }
            } elseif ($this->input->post('size')=='6x11') {
                if ($original_info[0] != 3375 || $original_info[1] != 1875) {
                    $this->form_validation->set_message('front_file_type', 'Image size must be  3375 x 1875 pixels.');
                    return false;
                }
            }
        } else {
            return true;
        }
    }

//CALLBACK FUNCTION FOR VALIDATING BACK FILE
    public function back_file_type()
    {

//checking userfile empty or not
        if (empty($_FILES['back_userfile']['name'])) {
            $this->form_validation->set_message('back_file_type', 'File is required.');
            return false;
        }
//checking file is csv or not
        elseif (pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)!="pdf" && pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)!="png" && pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)!="jpeg" && pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)!="jpg") {
            $this->form_validation->set_message('back_file_type', 'The file you uploaded is not a valid file.Supported file types are PDF, PNG, and JPEG. ');
            return false;
        }

//checking file dimensions
        elseif (pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)=="png" || pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)=="jpeg" || pathinfo($_FILES['back_userfile']['name'], PATHINFO_EXTENSION)=="jpg") {
            $original_info = getimagesize($_FILES['back_userfile']['tmp_name']);
            if ($this->input->post('size')=='4x6') {
                if ($original_info[0] != 1875 || $original_info[1] != 1275) {
                    $this->form_validation->set_message('back_file_type', 'Image size must be  1875 x 1275 pixels.');
                    return false;
                }
            } elseif ($this->input->post('size')=='6x11') {
                if ($original_info[0] != 3375 || $original_info[1] != 1875) {
                    $this->form_validation->set_message('back_file_type', 'Image size must be  3375 x 1875 pixels.');
                    return false;
                }
            }
        } else {
            return true;
        }
    }

//CALLBACK FUNCTION FOR VALIDATING HTML
    public function validate_html($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            $this->form_validation->set_message('validate_url', 'This is not a valid url.Example : http://www.example.com ');
            return false;
        }
    }
//CALLBACK FUNCTION FOR VALIDATE MESSAGE
    public function validate_message()
    {
        $strlen    =    strlen($this->input->post('message'));
        if ($strlen > 350) {
            $this->form_validation->set_message('validate_message', ' Max of 350 characters to be included on the back of postcard');
            return false;
        } else {
            return true;
        }
    }
//DOWNLOAD SAMPLE CSV
    public function sample_csv($template='lob_templates', $template_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }

        if (!empty($template_id)) {
            $template    =    $this->common_model->get($template, array('id'=>$template_id));
            $file_name = 'https://s3.amazonaws.com/reisoft/'.$template->csv_file;
        } else {
            $file_name = "address.csv";
            $f = fopen("address.csv", "w");

            $heading = array(

                             0=>'Name',
                             1=>'Address Line 1',
                             2=>'Address Line 2',
                             3=>'City',
                             4=>'State',
                             5=>'Zip/Postal Code',


                        );

            fputcsv($f, $heading);

            $aData = array(

               0=>'Harry Zhang',
               1=>'123 Test Street',
               2=>'Suite 1',
               3=>'San Francisco',
               4=>'CA',
               5=>'94107',


               );

            fputcsv($f, $aData);
            fclose($f);
        }
        header('Content-Type: application/csv');
        header('Content-Disposition:attachment;filename=REI_Direct_Mail_CSV_'.date('dMy-His').'.csv');
        header('Pragma: no-cache');
        readfile($file_name);
    }


    //DELETE POSTCARD
    public function delete()
    {
        if (isset($_POST['idx']) && isset($_POST['campaign_id'])) {

            $idx = $_POST['idx'];
            $campaign_id = $_POST['campaign_id'];
        } else {

            redirect('unauthorized');
        }


        if (!has_permission('direct_mail')) {

            redirect('unauthorized');
        }

        //checking if postcard exist
        $data['postcard']    =    $this->common_model->get('lob_postcards', array('id'=>$idx), 'array');
        if (count($data['postcard']) == 0) {
            $this->session->set_flashdata('errors', 'Postcard not found !!!');
            redirect('direct_mails/postcards/'.$campaign_id);
        }

        $this->common_model->delete('lob_postcards', array('id'=>$idx));

        $this->session->set_flashdata('msg', 'Postcard Deleted Successfully!');
        redirect('direct_mails/postcards/'.$campaign_id);
    }

//LETTER FUNCTIONS BEGINS
	public function check_data_variables_letter()
	{
		if ($this->input->post('template_type')=="own_design") {
			if($this->input->post('file_option')=="paste_html")
			{

				if($this->input->post('file_paste_html')=='')
				{
					return true;
				}
				
				$postcard_data['file']=$this->input->post('file_paste_html');
				
				//find all string inside double curly braces
				$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
				preg_match_all($regex, $postcard_data['file'], $matches);
	
				//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
				$matches[1] = $this->find_replace($matches[1]);
				
				if(!empty($matches[1]))
				{
					for($i=0;$i<count($matches[1]);$i++)
					{
						$org_variable[$matches[1][$i]]	=	$matches[0][$i];
					}
				}

			}
			
		}
		
		else if ($this->input->post('template_type')=="predefined") {
				if($this->input->post('template')=='')
				{
					
					return true;
				}
				//fetching front and back html for selected template
				$data    =    $this->common_model->get("lob_letter_templates", array('id'=>$this->input->post('template')), 'array', 'html');
				
				$postcard_data['file']    =    $data['html'];
			  

				//find all string inside double curly braces
				$regex    =    "~\{\{\s*(.*?)\s*\}\}~";
				preg_match_all($regex, $postcard_data['file'], $matches);
			   

				//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
			  
				$matches[1] = $this->find_replace($matches[1]);
				$org_variable	=	array();
				
				if(!empty($matches[1]))
				{
					for($i=0;$i<count($matches[1]);$i++)
					{
						$org_variable[$matches[1][$i]]	=	$matches[0][$i];
					}
				}

             }
				
			if ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {

					if($_FILES['userfile']['name']=='')
					{
						return true;
					}
					$csv_file = $_FILES['userfile']['tmp_name'];
                    
                    $fp = file($csv_file, FILE_SKIP_EMPTY_LINES);
                    $mail_count    =    count($fp);

					
                    $csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );
					$unmatched=	array();
					
					$all_variables	=	$matches[1];
					if (isset($all_variables) && !empty($all_variables)) {
						foreach ($all_variables as $row) {

									
							$search_array = array_map('strtolower', $csv_headers);		
							
							if(!in_array(strtolower($row),$search_array) && !in_array($row,$unmatched)){
								$unmatched[]	=	(isset($org_variable[$row]))?$org_variable[$row]:'';
							}
						}
					}
					 
					if(!empty($unmatched)){
					
						$this->form_validation->set_message('check_data_variables_letter', "Following data variables not found in uploaded CSV : ".implode(', ',$unmatched));
						 return false;
					}
                     
                }
            else if ($this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") 
            {

					$contacts = $this->direct_mail->get_mailing_addresses($_POST);

                    $csv_headers	=	array('name','address_line1','address_line2','address_city','address_state','address_zip');

					
					$unmatched=	array();
					
					$all_variables	=	$matches[1];
					if (isset($all_variables) && !empty($all_variables)) {
						foreach ($all_variables as $row) {

									
							$search_array = $csv_headers;	
							
							if(!in_array(strtolower($row),$search_array) && !in_array($row,$unmatched)){
								$unmatched[]	=	(isset($org_variable[$row]))?$org_variable[$row]:'';
							}
						}
					}
					 
					if(!empty($unmatched)){
					
						$this->form_validation->set_message('check_data_variables_letter', "Following data variables not found : ".implode(', ',$unmatched));
						 return false;
					}
                     
                
            }

	}
//CREATE LETTER AND SEND
    public function create_letter()
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }
        set_time_limit(0);
        $data['action']  = 'Create';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title']  = 'Create Letter';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', '/direct_mails');
        $this->breadcrumbs->push('Create Letter', '/');
        $this->load->library('form_validation');


        $defaults = array(
            "campaign_id"=>"",

             "userfile"=>"",
             "address_option"    =>    "",
             "name"    =>    "",
             "address_line1"    =>    "",
             "address_line2"    =>    "",
             "address_city"    =>"",
             "address_state"    =>"",
             "address_zip"    =>"",
             "from_address_option"=>"",
             "from_name"    =>"",
             "from_address_line1"    =>"",
             "from_address_line2"    =>"",
             "from_address_city"    =>"",
             "from_address_state"    =>"",
             "from_address_zip"    =>"",
             "file_option"    =>"",
             "file_paste_html"    =>"",
             "double_sided"    =>"",
             'color'=>"",
             'address_placement'=>"",
             "template_type"=>'',
            'template'=>'',
             'send_date'=>'',
             'mail_drops'=>'',
        );
        if ($_POST) {
            $this->form_validation->set_message('required', '%s is required.');
            $this->form_validation->set_rules('campaign_id', 'Campaign', 'required');
            if ($this->input->post('template_type')=="own_design") {
				if($this->input->post('file_option')=="paste_html")
				{
					$this->form_validation->set_rules('file_paste_html', 'Paste HTML', 'required');
				}
				if($this->input->post('file_option')=="choose_file")
				{
                	$this->form_validation->set_rules('letter_userfile', 'File', 'callback_letter_file_type');
				}
            } elseif ($this->input->post('template_type')=="predefined") {
                $this->form_validation->set_rules('template', 'Template', 'required');
            }
            if ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {
                $this->form_validation->set_rules('userfile', 'CSV file', 'callback_check_file_type');
            }

            if ($this->input->post('address_option')=="create_new") {
                $this->form_validation->set_rules('name', 'Name', 'required');
                $this->form_validation->set_rules('address_line1', 'Address Line1', 'required');
                $this->form_validation->set_rules('address_city', 'City', 'required');
                $this->form_validation->set_rules('address_state', 'State', 'required');
                $this->form_validation->set_rules('address_zip', 'Zip/Postal Code', 'required');
            }
			
			if(($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no"  || $this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") && ($this->input->post('file_option')=="paste_html" || $this->input->post('template_type')=="predefined"))
			{
				 $this->form_validation->set_rules('data_variables', 'Data Variables', 'callback_check_data_variables_letter');
			}
            $this->form_validation->set_rules('from_address_option', 'From Address', 'required');
            if ($this->input->post('from_address_option')=="create_new") {
                $this->form_validation->set_rules('from_name', 'Name', 'required');
                $this->form_validation->set_rules('from_address_line1', 'Address Line1', 'required');
                $this->form_validation->set_rules('from_address_city', 'City', 'required');
                $this->form_validation->set_rules('from_address_state', 'State', 'required');
                $this->form_validation->set_rules('from_address_zip', 'Zip/Postal Code', 'required');
            }


            $this->form_validation->set_error_delimiters('<span class="has-error help-block">', '</span>');


            if (!$this->form_validation->run()) {
                $data['letter'] = array_merge($defaults, $_POST);
            } else {
                //fetching user account balance and postcard charge
                $bill_account = $this->common_model->get('billing_accounts', array('client_id' => $this->session->userdata('client_id')));


                $letter_data = array(

                                    "double_sided"    =>    $this->input->post('double_sided'),
                                    "color"    =>    $this->input->post('color'),


                              );
                if ($this->input->post('address_placement')!='') {
                    $letter_data["address_placement"]    =    $this->input->post('address_placement');
                }
                if ($this->input->post('from_address_option')=="create_new") {
                    $letter_data['from'] = $from_address =    array(

                                                               'name'=>$this->input->post('from_name'),
                                                               'address_line1'=>$this->input->post('from_address_line1'),
                                                               'address_line2'=>$this->input->post('from_address_line2'),
                                                               'address_city'=>$this->input->post('from_address_city'),
                                                               'address_state'=>$this->input->post('from_address_state'),
                                                               'address_zip'=>$this->input->post('from_address_zip'),

                                                                );

                    $from_address['user_id']    =    $this->session->userdata('user_id');
                    $from_address['client_id']    =    $this->session->userdata('client_id');
                } else {
                    $letter_data['from']    =    $this->common_model->get("lob_address", array('id'=>$this->input->post('from_address_option')), 'array', 'name,address_line1,address_line2,address_city,address_state,address_zip');
                }

    //front content
                if ($this->input->post('template_type')=="own_design") {
					if($this->input->post('file_option')=="choose_file")
					{
						$config['upload_path'] = './uploads/direct_mail/';
						$config['allowed_types'] = 'pdf';
	
						$this->load->library('upload', $config);
						if (! $this->upload->do_upload('letter_userfile')) {
							//if not uploaded
							$error =  $this->upload->display_errors();
	
							$this->session->set_flashdata('errors', $error);
	
							redirect('direct_mails/create_letter/');
						} else {
							$file = $this->upload->data();
							$data['filename']    =    $file['file_name'];
							$letter_data['file'] =  base_url().'uploads/direct_mail/'.$file['file_name'];
							$letter_file    =    $file['file_name'];
						}
					}
					else if($this->input->post('file_option')=="paste_html")
					{
						$letter_data['file']=$this->input->post('file_paste_html');
						$regex = '!\{\{(\w+)\}\}!'; 
						preg_match_all($regex, $letter_data['file'], $matches);
						//replace all white spaces and hypens with underscore inside double curly braces(not removing double curly braces)
						$replaces = $this->find_replace($matches[0]);
	
						//replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
						$matches[1] = $this->find_replace($matches[1]);
	
						//final front and back html, changed data variables format acceptable by lob
						$letter_data['file'] = str_replace($matches[0], $replaces, $letter_data['file']);
					}
                }
//if using predefined template
                elseif ($this->input->post('template_type')=="predefined") {
                    $data    =    $this->common_model->get("lob_letter_templates", array('id'=>$this->input->post('template')), 'array', 'html');
                    $letter_data['file']    =    $data['html'];

                    $regex    =    "~\{\{\s*(.*?)\s*\}\}~";
                    preg_match_all($regex, $letter_data['file'], $matches);
                    //replace all white spaces and hypens with underscore inside double curly braces(not removing double curly braces)
                    $replaces = $this->find_replace($matches[0]);

                    //replace all white spaces and hypens with underscore inside double curly braces(removing double curly braces)
                    $matches[1] = $this->find_replace($matches[1]);

                    //final front and back html, changed data variables format acceptable by lob
                    $letter_data['file'] = str_replace($matches[0], $replaces, $letter_data['file']);
                }

//fetching api key
                $private_key = $this->common_model->get("admin_settings", array('id'=>30), 'object', 'value');
                $lob = new Create_letter($private_key->value);
//to single address
                if ($this->input->post('address_option')=="create_new") {
					if ($this->input->post('color')==0) {
						$amt_charge = get_usage_charge('letter_black_white');
					} elseif ($this->input->post('color')==1) {
						$amt_charge = get_usage_charge('letter_color');
					}
                    //checking balance
                    if ($bill_account->balance < $amt_charge) {
                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending letter. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");

                        redirect('direct_mails/create_letter/');
                    }

                    $letter_data['to']=array(

                                               'name'=>$this->input->post('name'),
                                               'address_line1'=>$this->input->post('address_line1'),
                                               'address_line2'=>$this->input->post('address_line2'),
                                               'address_city'=>$this->input->post('address_city'),

                                               'address_state'=>$this->input->post('address_state'),
                                               'address_zip'=>$this->input->post('address_zip'),

                                              );
                	$address_data		=array(

                                               'recipient'=>$this->input->post('name'),
                                               'primary_line'=>$this->input->post('address_line1'),
                                               'secondary_line'=>$this->input->post('address_line2'),
                                               'city'=>$this->input->post('address_city'),
                                               'state'=>$this->input->post('address_state'),
                                               'zip_code'=>$this->input->post('address_zip'),

                                              );
					
					$letter_data['metadata[user_id]']	=$meta_data['metadata[user_id]']=$this->session->userdata('user_id');	
					$letter_data['metadata[username]']	=$meta_data['metadata[username]']=getUser($this->session->userdata('user_id'));
					$letter_data['metadata[campaign]']	=$meta_data['metadata[campaign]']=direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
					
					//us address verification
                    $verified_address    =    $lob->verify_address($address_data);
					if (isset($verified_address['status']) && $verified_address['status']=="failed") {
                        $this->session->set_flashdata('errors', $verified_address['message']);
                         redirect('direct_mails/create_letter/');
					}
					elseif (isset($verified_address['deliverability']) && $verified_address['deliverability']!='undeliverable' && $verified_address['deliverability']!='no_match') {
						
						//send letter
						$letter    =    $lob->send_letter($letter_data);
						if (isset($letter['status']) && $letter['status']=="failed") {
							$this->session->set_flashdata('errors', $letter['message']);
							redirect('direct_mails/create_letter');
						} elseif (!empty($letter)) {
							//unset($letter['metadata'], $letter['object'], $letter['template_id'], $letter['template_version_id']);
							
							//letter details
							$letter_details['campaign_id']    =    $this->input->post('campaign_id');
							$letter_details['id'] = $letter['id'];
							$letter_details['description'] = $letter['description'];
							$letter_details['to']    =    serialize($letter['to']);
							$letter_details['from']    =    serialize($letter['from']);
							$letter_details['color'] = $letter['color'];
							$letter_details['double_sided'] = $letter['double_sided'];
							$letter_details['address_placement'] = $letter['address_placement'];
							$letter_details['return_envelope'] = $letter['return_envelope'];
							$letter_details['perforated_page'] = $letter['perforated_page'];
							$letter_details['extra_service'] = $letter['extra_service'];
							$letter_details['url'] = $letter['url'];
							$letter_details['carrier'] = $letter['carrier'];
							$letter_details['thumbnails']    =    serialize($letter['thumbnails']);
							$letter_details['tracking_events']    =    serialize($letter['tracking_events']);
							$letter_details['tracking_number']    =    $letter['tracking_number'];
							$letter_details['tracking']    =    (isset($letter['tracking']))?serialize($letter['tracking']):'';
							$letter_details['expected_delivery_date'] = $letter['expected_delivery_date'];
							$letter_details['date_created'] = $letter['date_created'];
							$letter_details['date_modified'] = $letter['date_modified'];
							$letter_details['send_date'] = $letter['send_date'];
							$letter_details['user_id']    =    $this->session->userdata('user_id');
							$letter_details['client_id']    =    $this->session->userdata('client_id');
							
							//inserting letter details
							$lob_id	=	$this->common_model->insert('lob_letters', $letter_details);
							
							$mailing_address_data	=	$letter_data['to'];
							$mailing_address_data['campaign_id']			=	$this->input->post('campaign_id');
							$mailing_address_data['user_id']    		=    $this->session->userdata('user_id');
							$mailing_address_data['client_id']    		=    $this->session->userdata('client_id');
							$mailing_address_data['created_date']    			=     date('Y-m-d H:i:s');
							$mailing_address_id = $this->common_model->insert('lead_mailing_address', $mailing_address_data);
							//deducting amount
							$new_balance = $bill_account->balance - $amt_charge;
							$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
							
							//usage history
							$usage_data	=	array(
												  'client_id'		=>$this->session->userdata('client_id'),
												  'sub_user_id'		=>$this->session->userdata('user_id'),
												  'type'			=>'letter',
												  'date'			=>date('Y-m-d'),
												  'time_stamp'		=>date('H:i:s'),
												  'charged'			=>1,
												  'charged_amount'	=>$amt_charge,
												  'price_per_item'	=>$amt_charge,
												  'item_count'		=>1,
												  );
							$this->common_model->insert('usage_history',$usage_data);
							
							$to_address[0]    					=    $letter_data['to'];
							$to_address[0]['lob_postcard_id']   =    $letter['id'];
							$to_address[0]['campaign_id']		=	$this->input->post('campaign_id');
							$to_address[0]['mailing_address_id']			=	$mailing_address_id;
							$to_address[0]['user_id']    		=    $this->session->userdata('user_id');
							$to_address[0]['client_id']    		=    $this->session->userdata('client_id');
							$to_address[0]['type']    			=    'letter';
							$to_address[0]['color']    			=     $letter_data['color'];
							$to_address[0]['double_sided']    	=     $letter_data['double_sided'];
							$to_address[0]['address_placement'] =     $letter_data['address_placement'];
							$to_address[0]['from']    			=     serialize($letter_data['from']);
							$to_address[0]['file']   			=     $letter_data['file'];
							$to_address[0]['status']    			=     1;
							$to_address[0]['processed_date']    			=     date('Y-m-d H:i:s');
							$to_address[0]['send_date']   =    $letter['send_date'];
							$to_address[0]['meta_data']   =     serialize($meta_data);

							$actitvity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>'1 letter send',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
							$this->common_model->insert('activities', $actitvity_data);
						}
						//delete letter file
						if (isset($letter_file)) {
							$files    =    glob(getcwd().'/uploads/direct_mail/'.$letter_file);
		
							if (!empty($files)) {
								unlink($files[0]);
							}
						}
						
						//inserting to address(queue)
						if (isset($to_address) && !empty($to_address)) {
							$this->common_model->insert_batch('lob_to_address', $to_address);
						}
		
						if (isset($from_address) && !empty($from_address)) {
							//inserting 'from' address
							$this->common_model->insert("lob_address", $from_address);
						}
						$this->session->set_flashdata('msg', 'Postcard(s) Send Successfully!');
						redirect('direct_mails/letters/'.$this->input->post('campaign_id'));
					}
					else{
							$this->session->set_flashdata('errors', 'This address is not deliverable.');
							 redirect('direct_mails/create_letter/');
					}
                } elseif ($this->input->post('address_option')=="upload_csv" || $this->input->post('address_option')=="upload_csv_no") {
                    $uploaddir = getcwd().'/uploads/direct_mail/';
                    $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
                    move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);

                    $states_org = $this->common_model->getStates();

                    $csv_file =  getcwd().'/uploads/direct_mail/'.basename($_FILES['userfile']['name']); // Name of your CSV file
                    $fp = file($csv_file, FILE_SKIP_EMPTY_LINES);
                    $mail_count    =    count($fp);

          /* get postcard count and mail drop count */
                    $totalPostCard = $mail_count - 1;
                    $mailDropCount = $this->input->post('mail_drops');
                    $scheduleDate = '';

                    if ($mailDropCount == "") {

                        /* Empty  Mail Drops. it is required */
                        $this->session->set_flashdata('errors', "Please choose Mail Drops.");
                        redirect('direct_mails/create_letter/');
                    }


                    /* check mails and mail drop counts */
                    if ($mailDropCount > $totalPostCard) {

                        /* wrong mail drop count */
                        $this->session->set_flashdata('errors', "You can't select more than $totalPostCard Mail Drops.");
                        redirect('direct_mails/create_letter/');
                    }

                    /* create Mail Drop Slabs */
                    $mailsPerDrop = ceil($totalPostCard / $mailDropCount);
                    $mailSlab = array();
                    $slabIncrement = 0;

          /* create a dates for mail drop slabs */
                    for ($k = 0; $k < $mailDropCount; $k++) {
                        $slabIncrement = $mailsPerDrop + $slabIncrement;
                        if ($k == 0) {
                           // $scheduleDate = date('Y-m-d', strtotime(' +1 day'));
						   	date_default_timezone_set("UTC");
							$scheduleDate	=	 date('Y-m-d\TH:i:s.0\Z',strtotime(' +8 hours '));
                        } else {
                            $scheduleDate = date('Y-m-d', strtotime(' + '. $k .' week'));
                        }
                        $mailSlab[$k] = $scheduleDate .'>'. $slabIncrement;
                    }

					if ($this->input->post('color')==0) {
						$amt_charge = get_usage_charge('letter_black_white');
					} elseif ($this->input->post('color')==1) {
						$amt_charge = get_usage_charge('letter_color');
					}
                    //checking balance
                    if ($bill_account->balance < ($totalPostCard*$amt_charge)) {
                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending letter. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");

                        redirect('direct_mails/create_letter/');
                    }
                    $csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );
                    $k=0;
                    $j=1;
                    $arrayKey = 0;
					
					//inserting data to queue_list
					$list_data	=	array('count'=>$totalPostCard,
											  'client_id'=>$this->session->userdata('client_id'),
											  'user_id'=>$this->session->userdata('user_id'),
											  'type'=>'letter',
											  'created_date'=>date('Y-m-d H:i:s'),
											  );
					$queue_id	=	$this->common_model->insert('lob_queue_list',$list_data);
					$meta_data['metadata[user_id]']	=$this->session->userdata('user_id');	
					$meta_data['metadata[username]']	=getUser($this->session->userdata('user_id'));
					$meta_data['metadata[campaign]']	=direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
                    while (!feof($csvfile)) {
                        $csv_array = fgetcsv($csvfile);

                        $state    = array_search(strtolower($csv_array[5]), array_map('strtolower', $states_org));


                        if (count($csv_headers)==count($csv_array) && $csv_array[0] != '' && $csv_array[1] != '' && $csv_array[3] !='' && $csv_array[4] !='' && $csv_array[5] !='' && (isset($states_org[strtoupper($csv_array[4])]) || $state!='') && preg_match("/^([0-9]{5})(-[0-9]{4})?$/i", $csv_array[5])) {
                            $letter_data['to']=array(

                                   'name'=>$csv_array[0],
                                   'address_line1'=>$csv_array[1],
                                   'address_line2'=>$csv_array[2],
                                   'address_city'=>$csv_array[3],
                                   'address_state'=>$csv_array[4],
                                   'address_zip'=>$csv_array[5],


                                  );

							$data_variable	=	array();	
                            if (isset($matches[1]) && !empty($matches[1])) {
                                foreach ($matches[1] as $row) {

									$search_array = array_map('strtolower', $csv_headers);		
									
									if(in_array(strtolower($row),$search_array) ){
										$ckey	=	array_search(strtolower($row),$search_array)	;
										$data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];
											
									}
                                }
                            }
                           
                            /* Devide CSV records into slabs and assign scheduled date accordingly */
                            if (array_key_exists($arrayKey, $mailSlab)) {
                                $temp = explode('>', $mailSlab[$arrayKey]);
                            }
                            $cnt = count($temp);
                            if ($cnt  > 1) {
                                if ($j >= $temp[1]) {
                                    $arrayKey++;
                                }
                            }
                           $letter_data['send_date']    = $temp[0];
						   	
						   	$mailing_address_data	=	$letter_data['to'];
							$mailing_address_data['campaign_id']			=	$this->input->post('campaign_id');
							$mailing_address_data['user_id']    		=    $this->session->userdata('user_id');
							$mailing_address_data['client_id']    		=    $this->session->userdata('client_id');
							$mailing_address_data['created_date']    			=     date('Y-m-d H:i:s');
							$mailing_address_id = $this->common_model->insert('lead_mailing_address', $mailing_address_data);
						   	$queue_data[$k]    =    $letter_data['to'];
							
							$queue_data[$k]['user_id']    =    $this->session->userdata('user_id');
							$queue_data[$k]['client_id']  =    $this->session->userdata('client_id');
							$queue_data[$k]['type']    	  =    'letter';
							$queue_data[$k]['color']    =     $letter_data['color'];
							$queue_data[$k]['double_sided']    =     $letter_data['double_sided'];
							$queue_data[$k]['address_placement']    =     $letter_data['address_placement'];
							$queue_data[$k]['file']    =     $letter_data['file'];
							$queue_data[$k]['from']    =     serialize($letter_data['from']);

							$queue_data[$k]['data']    =     serialize($data_variable);
							$queue_data[$k]['meta_data']    =     serialize($meta_data);
							$queue_data[$k]['send_date']  =     $letter_data['send_date'];
							$queue_data[$k]['amt_charge']  = $amt_charge;
							$queue_data[$k]['campaign_id']  = $this->input->post('campaign_id');
							$queue_data[$k]['mailing_address_id']  = $mailing_address_id;
                 			$queue_data[$k]['queue_id']  = $queue_id;
							$k++;

                        }
                        
                    }

                    fclose($csvfile);
//delete csv file from folder

                    $files    =    glob(getcwd().'/uploads/direct_mail/'.basename($_FILES['userfile']['name']));

                    if (!empty($files)) {
                        unlink($files[0]);
                    }
					
					//inserting to address(queue)
					if (isset($queue_data) && !empty($queue_data)) {
						$this->common_model->insert_batch('lob_to_address', $queue_data);
						$activity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>count($queue_data).' letters scheduled',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
						$this->common_model->insert('activities', $activity_data);
						//deducting amount
						$new_balance	=	$bill_account->balance - (count($queue_data)*$amt_charge);
						$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
						//usage history
						$usage_data	=	array(
											  'client_id'		=>$this->session->userdata('client_id'),
											  'sub_user_id'		=>$this->session->userdata('user_id'),
											  'type'			=>'letter',
											  'date'			=>date('Y-m-d'),
											  'time_stamp'		=>date('H:i:s'),
											  'charged'			=>1,
											  'charged_amount'	=>count($queue_data)*$amt_charge,
											  'price_per_item'	=>$amt_charge,
											  'item_count'		=>count($queue_data),
											  'queue_id'		=> $queue_id,
											  );
						$this->common_model->insert('usage_history',$usage_data);
					}
	
					if (isset($from_address) && !empty($from_address)) {
						//inserting 'from' address
						$this->common_model->insert("lob_address", $from_address);
					}
					
					$this->session->set_flashdata('msg', 'Letter(s) Send Successfully!');
					redirect('direct_mails/letters/'.$this->input->post('campaign_id'));
                }
                else if ($this->input->post('address_option')=="all_contacts" || $this->input->post('address_option')=="exclude_contacts") {
                	$contacts = $this->direct_mail->get_mailing_addresses($_POST);

                    $states_org = $this->common_model->getStates();
 					$mail_count    =    count($contacts);
                    

          /* get postcard count and mail drop count */
                    $totalPostCard = $mail_count;
                    $mailDropCount = $this->input->post('mail_drops');
                    $scheduleDate = '';

                    if ($mailDropCount == "") {

                        /* Empty  Mail Drops. it is required */
                        $this->session->set_flashdata('errors', "Please choose Mail Drops.");
                        redirect('direct_mails/create_letter/');
                    }


                    /* check mails and mail drop counts */
                    if ($mailDropCount > $totalPostCard) {

                        /* wrong mail drop count */
                        $this->session->set_flashdata('errors', "You can't select more than $totalPostCard Mail Drops.");
                        redirect('direct_mails/create_letter/');
                    }

                    /* create Mail Drop Slabs */
                    $mailsPerDrop = ceil($totalPostCard / $mailDropCount);
                    $mailSlab = array();
                    $slabIncrement = 0;

          /* create a dates for mail drop slabs */
                    for ($k = 0; $k < $mailDropCount; $k++) {
                        $slabIncrement = $mailsPerDrop + $slabIncrement;
                        if ($k == 0) {
                           // $scheduleDate = date('Y-m-d', strtotime(' +1 day'));
						   	date_default_timezone_set("UTC");
							$scheduleDate	=	 date('Y-m-d\TH:i:s.0\Z',strtotime(' +8 hours '));
                        } else {
                            $scheduleDate = date('Y-m-d', strtotime(' + '. $k .' week'));
                        }
                        $mailSlab[$k] = $scheduleDate .'>'. $slabIncrement;
                    }

					if ($this->input->post('color')==0) {
						$amt_charge = get_usage_charge('letter_black_white');
					} elseif ($this->input->post('color')==1) {
						$amt_charge = get_usage_charge('letter_color');
					}
                    //checking balance
                    if ($bill_account->balance < ($totalPostCard*$amt_charge)) {
                        //no balance
                        $this->session->set_flashdata('errors', "You don't have enough balance in your account for sending letter. Please click <a href='".base_url('billing')."'>here</a> to go to the billing page.");

                        redirect('direct_mails/create_letter/');
                    }
                   /* $csvfile = fopen($csv_file, 'r');
                    $csv_headers = fgetcsv($csvfile);
                    $csv_headers = array_map(
                                        function ($str) {
                                            return str_replace(str_split(' -'), '_', $str);
                                        },
                                        $csv_headers
                                    );*/
                    $csv_headers	=	array('name','address_line1','address_line2','address_city','address_state','address_zip');  
                    $k=0;
                    $j=1;
                    $arrayKey = 0;
					
					//inserting data to queue_list
					$list_data	=	array('count'=>$totalPostCard,
											  'client_id'=>$this->session->userdata('client_id'),
											  'user_id'=>$this->session->userdata('user_id'),
											  'type'=>'letter',
											  'created_date'=>date('Y-m-d H:i:s'),
											  );
					$queue_id	=	$this->common_model->insert('lob_queue_list',$list_data);
					$meta_data['metadata[user_id]']	=$this->session->userdata('user_id');	
					$meta_data['metadata[username]']	=getUser($this->session->userdata('user_id'));
					$meta_data['metadata[campaign]']	=direct_mail_campaign($this->input->post('campaign_id'))->campaign_name;
                    //while (!feof($csvfile)) {
                    if(!empty($contacts))
					{
						foreach ($contacts as $contact) {

	                        
                            $letter_data['to']=array(

                                   'name'=>$contact['name'],
	                                'address_line1'=>$contact['address_line1'],
	                                'address_line2'=>$contact['address_line2'],
	                                'address_city'=>$contact['address_city'],
	                                'address_state'=>$contact['address_state'],
	                                'address_zip'=>$contact['address_zip'],


                                  );

							$data_variable	=	array();	
                            if (isset($matches[1]) && !empty($matches[1])) {
                                foreach ($matches[1] as $row) {

									//$search_array = array_map('strtolower', $csv_headers);		
									$search_array = $csv_headers;
									if(in_array(strtolower($row),$search_array) ){
										/*$ckey	=	array_search(strtolower($row),$search_array)	;
										$data_variable['merge_variables['.$row.']']	=	$csv_array[$ckey];*/
										$data_variable['merge_variables['.$row.']']	=	$contact[$row];	
									}
                                }
                            }
                           
                            /* Devide CSV records into slabs and assign scheduled date accordingly */
                            if (array_key_exists($arrayKey, $mailSlab)) {
                                $temp = explode('>', $mailSlab[$arrayKey]);
                            }
                            $cnt = count($temp);
                            if ($cnt  > 1) {
                                if ($j >= $temp[1]) {
                                    $arrayKey++;
                                }
                            }
                           $letter_data['send_date']    = $temp[0];
						   	

						   	$queue_data[$k]    =    $letter_data['to'];
							
							$queue_data[$k]['user_id']    =    $this->session->userdata('user_id');
							$queue_data[$k]['client_id']  =    $this->session->userdata('client_id');
							$queue_data[$k]['type']    	  =    'letter';
							$queue_data[$k]['color']    =     $letter_data['color'];
							$queue_data[$k]['double_sided']    =     $letter_data['double_sided'];
							$queue_data[$k]['address_placement']    =     $letter_data['address_placement'];
							$queue_data[$k]['file']    =     $letter_data['file'];
							$queue_data[$k]['from']    =     serialize($letter_data['from']);

							$queue_data[$k]['data']    =     serialize($data_variable);
							$queue_data[$k]['meta_data']    =     serialize($meta_data);
							$queue_data[$k]['send_date']  =     $letter_data['send_date'];
							$queue_data[$k]['amt_charge']  = $amt_charge;
							$queue_data[$k]['campaign_id']  = $this->input->post('campaign_id');
							$queue_data[$k]['mailing_address_id']  = $contact['id'];
                 			$queue_data[$k]['queue_id']  = $queue_id;
							$k++;

	                        
                       } 
                    }


					//inserting to address(queue)
					if (isset($queue_data) && !empty($queue_data)) {
						$this->common_model->insert_batch('lob_to_address', $queue_data);
						$activity_data	=	array(
													'client_id'=>$this->session->userdata('client_id'),
													'user_id'=>$this->session->userdata('user_id'),
													'activity'=>count($queue_data).' letters scheduled',
													'type'=>'direct_mail',
													'date_time'=>date('m-d-Y H:i A'),
												);
						$this->common_model->insert('activities', $activity_data);
						//deducting amount
						$new_balance	=	$bill_account->balance - (count($queue_data)*$amt_charge);
						$this->common_model->update('billing_accounts', array('client_id' => $this->session->userdata('client_id')), array('balance'=>$new_balance,'updated_at'=> date('Y-m-d H:i:s')));
						//usage history
						$usage_data	=	array(
											  'client_id'		=>$this->session->userdata('client_id'),
											  'sub_user_id'		=>$this->session->userdata('user_id'),
											  'type'			=>'letter',
											  'date'			=>date('Y-m-d'),
											  'time_stamp'		=>date('H:i:s'),
											  'charged'			=>1,
											  'charged_amount'	=>count($queue_data)*$amt_charge,
											  'price_per_item'	=>$amt_charge,
											  'item_count'		=>count($queue_data),
											  'queue_id'		=> $queue_id,
											  );
						$this->common_model->insert('usage_history',$usage_data);
					}
	
					if (isset($from_address) && !empty($from_address)) {
						//inserting 'from' address
						$this->common_model->insert("lob_address", $from_address);
					}
					
					$this->session->set_flashdata('msg', 'Letter(s) Send Successfully!');
					redirect('direct_mails/letters/'.$this->input->post('campaign_id'));
                }
            }
        } else {
            $data['letter'] = $defaults;
        }

        //$data['templates']        =    $this->common_model->get_all('lob_letter_templates');
		$data['templates']    =    $this->common_model->get_all('lob_letter_templates','','','','','array','client_id',array(0,$this->session->userdata('client_id')));
        
        $data['states']        =    $this->common_model->getStates();
        $data['from_address']    =    $this->common_model->get_all('lob_address', array('client_id'=>$this->session->userdata('client_id')));
        $data["campaigns"]        =    $this->common_model->get_all('campaigns', array('user_id'=>$this->session->userdata('user_id'),'deleted'=>0));
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/create_letter_form', $data);
        $this->load->view('includes/footer', $data);
    }

//CALLBACK FUNCTION FOR VALIDATING LETTER FILE
    public function letter_file_type()
    {

//checking userfile empty or not
        if (empty($_FILES['letter_userfile']['name'])) {
            $this->form_validation->set_message('letter_file_type', 'File is required.');
            return false;
        }
//checking file is pdf or not
        elseif (pathinfo($_FILES['letter_userfile']['name'], PATHINFO_EXTENSION)!="pdf") {
            $this->form_validation->set_message('letter_file_type', 'The file you uploaded is not a valid file.Supported file type is 8.5"x11" PDF. ');
            return false;
        } else {
            return true;
        }
    }

    public function letters($campaign_id='')
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }

        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mails', '/direct_mails');
        $this->breadcrumbs->push('Letters', '/');

        $this->load->library('pagination');


        $search = array('user_id'=>$this->session->userdata('user_id'),'type'=>'letter');

        if ($_POST) {
            if ($this->input->post("campaign_id") != "") {
                $search["campaign_id"]=$campaign_id=  $this->input->post("campaign_id");
            }

            $this->session->set_userdata(array("search" => $search));
        } elseif (!empty($campaign_id)) {
            //from campaigns  page
            $search["campaign_id"]    =    $campaign_id;
            $this->session->set_userdata(array("search" => $search));
        } elseif ($this->uri->segment(4) !="page") {
            $this->session->set_userdata(array("search" => array()));
        } elseif (count($this->session->userdata("search"))) {
            $search = $this->session->userdata("search");
        }
        $campaign    =    ($campaign_id!='')?$campaign_id:0;
        $data["result"] = $this->common_model->get_all('lob_to_address', $search, 'send_date desc', 30, $this->uri->segment(5));
		$this->session->set_userdata('letter_query_array',array('lob_to_address', $search, 'send_date desc', 30, $this->uri->segment(5)));//print_r($this->session->userdata('letter_query_array'));exit;
        $total = $this->common_model->getSearchCount();

        $config['base_url'] = site_url('direct_mails/letters/'.$campaign.'/page/');
        $config['total_rows'] = $total;
        $config['per_page'] = 30;
        $config['num_links'] = 2;
        $config['uri_segment'] = 5;

        $config['anchor_class'] = '';
        $config['next_link'] = '<i class="fa fa-angle-double-right"></i>';
        $config['prev_link'] = '<i class="fa fa-angle-double-left"></i>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['prev_link_not_exists'] = '';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="">';
        $config['cur_tag_close'] = '</a></li> ';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);
        $data["pagination"] = $this->pagination->create_links();
        $data["uri_segment"] = $this->uri->segment(5);
        $data['search'] = $search;
        $data["campaigns"] = $this->common_model->get_all('campaigns', array('user_id'=>$this->session->userdata('user_id'),'deleted'=>0));
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/letters', $data);
        $this->load->view('includes/footer', $data);
    }
	
	public function letters_ajax()
	{
		
		if($_POST){
			

			$letter_query_array	=	$this->session->userdata('letter_query_array');
			$search = array('user_id'=>$this->session->userdata('user_id'));
			//$letter_query_array[1]["campaign_id"] = '';
			if ($this->input->post("campaign_id") != "") {
				$letter_query_array[1]["campaign_id"] = $this->input->post("campaign_id");
                $search["campaign_id"] =$campaign_id= $this->input->post("campaign_id");
            }
			$search["type"]	="letter";
			//print_r($letter_query_array);
			$data["result"] = $this->common_model->get_all($letter_query_array[0], $letter_query_array[1], $letter_query_array[2], $letter_query_array[3], $letter_query_array[4]);
			//echo $this->db->last_query();exit;
			$data['html']	=	$this->load->view('direct_mail/letters_ajax',$data,true);
			
			$results = $this->common_model->get_all('lob_to_address', $search, 'send_date desc');
			$data['total']	=	count($results);
			$data['pending']	=	0;
			$data['sent']		=	0;
			$data['undeliverable']	=	0;
			$data['failed']	=	0;
			$data['cancelled']	=	0;
			$data['delivered']	=	0;
			if(!empty($results))
			{
				foreach($results as $row)
				{
					if($row['status']==0)
					{
						$data['pending'] +=1;
					}
					else if($row['status']==1){
						$data['sent'] +=1;
					}
					else if($row['status']==2)
					{
						$data['undeliverable'] +=1;
					}
					else if($row['status']==3)
					{
						$data['failed'] +=1;
					}
					else if($row['status']==4)
					{
						$data['cancelled'] +=1;
					}
					else if($row['status']==5)
					{
						$data['delivered'] +=1;
					}
				}
			}
			//echo count($data['total']);exit;
			if($data['total']>0){
				$data['pending_percentage']	=	(($data['pending']/$data['total'])*100);
				$data['sent_percentage']	=	(($data['sent']/$data['total'])*100);
				$data['undeliverable_percentage']	=	(($data['undeliverable']/$data['total'])*100);
				$data['failed_percentage']	=	(($data['failed']/$data['total'])*100);
				$data['cancelled_percentage']	=	(($data['cancelled']/$data['total'])*100);
				$data['delivered_percentage']	=	(($data['delivered']/$data['total'])*100);
				 $data['progress_bar']     =    '<td colspan="8">
													<div class="progress">
														<div class="progress-bar progress-bar-info" role="progressbar" style="width:'.$data['pending_percentage'].'%">'.ceil($data['pending_percentage']).'%</div>
														<div class="progress-bar progress-bar-success" role="progressbar" style="width:'.$data['sent_percentage'].'%">'.ceil($data['sent_percentage']).'%</div><div class="progress-bar progress-bar-warning" role="progressbar" style="width:'.$data['undeliverable_percentage'].'%">'.ceil($data['undeliverable_percentage']).'%</div>
														<div class="progress-bar progress-bar-danger" role="progressbar" style="width:'.$data['failed_percentage'].'%">'.ceil($data['failed_percentage']).'%</div>
														<div class="progress-bar progress-bar-primary" role="progressbar" style="width:'.$data['cancelled_percentage'].'%">'.ceil($data['cancelled_percentage']).'%</div>
														<div class="progress-bar progress-bar-primary" role="progressbar" style="background-color:green;width:'.$data['delivered_percentage'].'%">'.ceil($data['delivered_percentage']).'%</div>
													</div>  
													<span class="label label-sm label-default"> Total : '.$data['total'].'</span>
													<span class="label label-sm label-info"> Pending : '.$data['pending'].'</span>
													<span class="label label-sm label-success"> Processed : '.$data['sent'].'</span>
													<span class="label label-sm label-warning"> Undeliverable : '.$data['undeliverable'].'</span>
													<span class="label label-sm label-danger"> Failed : '.$data['failed'].'</span>
													<span class="label label-sm label-primary"> Cancelled : '.$data['cancelled'].'</span>
													<span class="label label-sm label-primary" style="background-color:green;"> Delivered : '.$data['delivered'].'</span>
												</td>';
			}
			header('Content-type: application/json');
			die(json_encode($data));
		}
	}
	
//DELETE LETTERS
    public function delete_letter()
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }

				if(isset($_POST['idx']) && isset($_POST['campaign_id'])){

						$idx = $_POST['idx'];
						$campaign_id = $_POST['campaign_id'];
				}else {

            redirect('unauthorized');
        }


				//checking if postcard exist
        $data['letter']    =    $this->common_model->get('lob_letters', array('idx'=>$idx), 'array');
        if (count($data['letter']) == 0) {
            $this->session->set_flashdata('errors', 'Letter not found !!!');
            redirect('direct_mails/letters/'.$campaign_id);
        }

        $this->common_model->delete('lob_letters', array('idx'=>$idx));

        $this->session->set_flashdata('msg', 'Letter Deleted Successfully!');
        redirect('direct_mails/letters/'.$campaign_id);
    }

//VIEW LETTER
    public function view_letter($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }


        $data['action']  = 'View';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', 'direct_mails');
        $this->breadcrumbs->push('View Letter', '/');
	
//checking if leter exist
		$data['letter']	=	array();
        $data['letter_queue']    =    $this->common_model->get('lob_to_address', array('id'=>$idx), 'array');
        if (count($data['letter_queue']) == 0) {
            $this->session->set_flashdata('errors', 'Letter not found !!!');
            redirect('direct_mails/letters/'.$campaign_id);
        }
		else if($data['letter_queue']['lob_postcard_id']!='')
		{
        	$data['letter']    =    $this->common_model->get('lob_letters', array('id'=>$data['letter_queue']['lob_postcard_id']), 'array');
		}
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/view_letter', $data);
        $this->load->view('includes/footer', $data);
    }

    //REPLACE WHITE SPACES AND HYPHEN WITH UNDERSCORE
    public function find_replace($array=array())
    {
        $result    =    array_map(
                        function ($str) {
                            return strtolower(str_replace(str_split(' -'), '_', $str));
                        },
                        $array
                    );
        return $result;
    }

    public function view_campaign($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }


        $data['action']  = 'View';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', '/direct_mails');
        $this->breadcrumbs->push('View Campaign', '/');
//checking if postcard exist
        $data['postcard']    =    $this->common_model->get('lob_postcards', array('id'=>$idx), 'array');
        if (count($data['postcard']) == 0) {
            $this->session->set_flashdata('errors', 'Postcard not found !!!');
            redirect('direct_mails/postcards/'.$campaign_id);
        }
        $data['campaign']    =    $this->common_model->get('campaigns', array('id'=>$campaign_id), 'array');
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/view_campaign', $data);
        $this->load->view('includes/footer', $data);
    }

    public function view_letter_campaign($idx=0, $campaign_id=0)
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }


        $data['action']  = 'View';
        $data['page'] = 'direct_mail';
        $data['menu'] = 'direct_mail';
        $data['sub_menu'] = '';
        $data['title'] = 'Direct Mail';

        $this->load->library('breadcrumbs');
        $this->breadcrumbs->push('Home', '/dashboard');
        $this->breadcrumbs->push('Direct Mail', 'direct_mails');
        $this->breadcrumbs->push('View Direct Mail', '/');

//checking if leter exist
        $data['letter']    =    $this->common_model->get('lob_letters', array('id'=>$idx), 'array');
        if (count($data['letter']) == 0) {
            $this->session->set_flashdata('errors', 'Letter not found !!!');
            redirect('direct_mails/letters/'.$campaign_id);
        }
        $data['campaign']    =    $this->common_model->get('campaigns', array('id'=>$campaign_id), 'array');
        $this->load->view('includes/header', $data);
        $this->load->view('direct_mail/view_letter_campaign', $data);
        $this->load->view('includes/footer', $data);
    }

	public function postcard_demo()
	{
		if($_POST) 
		{

			//$data['postcard'] = array_merge($defaults, $_POST);
			$postcard_data = array(
								"send_date"=>date('Y-m-d', strtotime(' +20 day')),
								"size"	=>	$this->input->post('size'), 
								"front"	=>	$this->input->post('front_paste_html'),
								"back"	=>	$this->input->post('back_paste_html'),
								"to"	=>	array(
												
											   'name'=>"Tim Herndon ",
											   'address_line1'=> "11118 Fallgate Point Ct",
											   'address_city'=>"Jacksonville",
											   'address_state'=>"FL",
											   'address_zip'=>"32256",

											  ),
								"from"	=>	array(
												'name'=>'Ami Wang',
											   'address_line1'=>"123 Test Avenue",
											   'address_city'=>"Seattle",
											   'address_state'=>"WA",
											   'address_zip'=>"94041",

												),
						  );
			
			
//fetching api key
			$private_key = $this->common_model->get("admin_settings",array('id'=>30),'object','value');
			$lob = new LobSend($private_key->value);//'test_3862a0764560e95227a2b84dac329e52226'
			//send postcard
			$postcard	=	$lob->send_postcard($postcard_data);
			if(isset($postcard['status']) && $postcard['status']=="failed")
			{ 
				
				$data['status']	=	'failed';
				$data['html']	=	'<div class="alert alert-danger alert-dismissable "  >'.$postcard['message'].'</div>';
			}
			else if(!empty($postcard))
			{

				
				$data['html']	=	  '<div class="alert alert-success alert-dismissable "  >Postcard Send Successfully!</div>
										<div class="col-md-12">&nbsp;</div>
                    					<div class="col-md-5"></div>
                    					<div class="col-md-6">

                        					<a target="_blank" href="'.$postcard['url'].'" class="btn btn-success">View Postcard</a>

                    					</div>
            							<div class="col-md-12">&nbsp;</div>'	;
				$data['status']	=	'success';
				$cancel_data    =    $lob->cancel_postcard($postcard['id']);
				if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
					$data['html']	=	  '<div class="alert alert-danger alert-dismissable ">'.$cancel_data['message'].'</div>';
					
				}
			}
				
			echo json_encode($data);
			
		}
	}
	
	public function letter_demo()
	{
		if($_POST) 
		{

			//$data['postcard'] = array_merge($defaults, $_POST);
			$postcard_data = array(
								"send_date"=>date('Y-m-d', strtotime(' +20 day')),
								"color"	=>	$this->input->post('color'), 
								"file"	=>	$this->input->post('file'),
								"double_sided"=>1,
								"address_placement"=>'insert_blank_page',
								"to"	=>	array(
												
											   'name'=>"Tim Herndon ",
											   'address_line1'=> "11118 Fallgate Point Ct",
											   'address_city'=>"Jacksonville",
											   'address_state'=>"FL",
											   'address_zip'=>"32256",

											  ),
								"from"	=>	array(
												'name'=>'Ami Wang',
											   'address_line1'=>"123 Test Avenue",
											   'address_city'=>"Seattle",
											   'address_state'=>"WA",
											   'address_zip'=>"94041",

												),
						  );
			
			
//fetching api key
			$private_key = $this->common_model->get("admin_settings",array('id'=>30),'object','value');
			$lob = new Create_letter($private_key->value);//'test_3862a0764560e95227a2b84dac329e52226'
			//send postcard
			$postcard	=	$lob->send_letter($postcard_data);
			if(isset($postcard['status']) && $postcard['status']=="failed")
			{ 
				
				$data['status']	=	'failed';
				$data['html']	=	'<div class="alert alert-danger alert-dismissable "  >'.$postcard['message'].'</div>';
			}
			else if(!empty($postcard))
			{

				
				$data['html']	=	  '<div class="alert alert-success alert-dismissable "  >Letter Send Successfully!</div>
										<div class="col-md-12">&nbsp;</div>
                    					<div class="col-md-5"></div>
                    					<div class="col-md-6">

                        					<a target="_blank" href="'.$postcard['url'].'" class="btn btn-success">View Letter</a>

                    					</div>
            							<div class="col-md-12">&nbsp;</div>'	;
				$data['status']	=	'success';
				$cancel_data    =    $lob->cancel_letter($postcard['id']);
				if (isset($cancel_data['status']) && $cancel_data['status']=="failed") {
					$data['html']	=	  '<div class="alert alert-danger alert-dismissable ">'.$cancel_data['message'].'</div>';
					
				}
			}
				
			echo json_encode($data);
			
		}
	}
	
	function update_address()
	{
		$results	=	$this->common_model->get_all('lob_postcards');
		$insert_data	=	array();
		if(!empty($results))
		{
			foreach($results as $row)
			{
				$to	=	unserialize($row['to']);
				$insert_data[]	=	array(
										  'lob_postcard_id'=>$row['id'],
										  'name'=>$to['name'],
										  'address_line1'=>$to['address_line1'],
										  'address_city'=>$to['address_city'],
										  'address_state'=>$to['address_state'],
										  'address_zip'=>$to['address_zip'],
										  );
			}
		}
		
		if(!empty($insert_data))
		{
			$this->common_model->update_batch('lob_to_address',$insert_data,'lob_postcard_id');
		}
	}
	
    public function delete_all()
    {
        if (!has_permission('direct_mail')) {
            redirect('unauthorized');
        }

		if(isset($_POST['id'])){

			$id = (int)$_POST['id'];
			//$this->common_model->delete("campaigns", array('id'=>$id));
			$this->common_model->delete("lob_postcards", array('campaign_id'=>$id));
			$this->common_model->delete("lob_letters", array('campaign_id'=>$id));
			$this->common_model->delete("lob_to_address", array('campaign_id'=>$id));
			$this->common_model->delete("lob_area_mail", array('campaign_id'=>$id));
			$this->session->set_flashdata('msg', 'Direct Mails Deleted Successfully!');
		}

        redirect('direct_mails');
    }
}
