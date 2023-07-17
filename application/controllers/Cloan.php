<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cloan extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->db->query('SET SESSION sql_mode = ""');
        $this->load->library('lsettings');
        $this->load->library('auth');
        $this->load->library('session');
        $this->load->model('Settings');
        $this->auth->check_admin_auth();
        $this->load->model('Web_settings');

        
    }

    #================Add Person==============#

    public function add_ofloan_person() {
        $content = $this->lsettings->add_person1();
        $this->template->full_admin_html_view($content);
    }

    #================Manage Person==============#

    public function manage_ofln_person() {
          $CI = & get_instance();
         $CI->load->model('Settings');
         $alldata = $this->input->post('all',TRUE);
            if(!empty($alldata)){
            $perpagdata = $this->Settings->count_office_loan();
        }else{
          $perpagdata = 10;  
        }
        #pagination starts
        $config["base_url"] = base_url('Cloan/manage_ofln_person/');
        $config["total_rows"] = $this->Settings->person_list_count();
        $config["per_page"] = $perpagdata;
        $config["uri_segment"] = 3;
        $config["num_links"] = 5;
        /* This Application Must Be Used With BootStrap 3 * */
        $config['full_tag_open'] = "<ul class='pagination'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        /* ends of bootstrap */
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $links = $this->pagination->create_links();
        #
        #pagination ends
        #  
        $content = $this->lsettings->manage_person($links, $config["per_page"], $page);
        $this->template->full_admin_html_view($content);
    }

    public function submit_person1() {
          $person_id = $this->auth->generator(10);
         $coa = $this->loanheadcode();
           if($coa->HeadCode!=NULL){
                $headcode=$coa->HeadCode+1;
           }else{
                $headcode="10203020001";
            }
        $data = array(
       'transaction_id' => $transaction_id,
            'person_id'      => $this->input->post('person_id',TRUE),
            'credit'         => $this->input->post('ammount',TRUE),
            'date'           => $this->input->post('date',TRUE),
            'details'        => $this->input->post('details',TRUE),
            'phone'          => $this->input->post('phone',TRUE),
            'create_by'      => $this->session->userdata('user_id'),
            'status'         => 2
        );
         $loan_coa = [
             'HeadCode'         => $headcode,
             'HeadName'         => $person_id.'-'.$this->input->post('name',TRUE),
             'PHeadName'        => 'Loan Receivable',
             'HeadLevel'        => '4',
             'IsActive'         => '1',
             'IsTransaction'    => '1',
             'IsGL'             => '0',
             'HeadType'         => 'A',
             'IsBudget'         => '0',
             'IsDepreciation'   => '0',
             'DepreciationRate' => '0',
             'CreateBy'         => $this->session->userdata('user_id'),
             'CreateDate'       => date('Y-m-d H:i:s'),
        ];

        $result = $this->Settings->submit_person($data);
        if ($result) {
             $this->db->insert('acc_coa',$loan_coa);
            $this->session->set_userdata(array('message' => display('successfully_added')));
            redirect(base_url('Cloan/manage_ofln_person'));
        } else {
            $this->session->set_userdata(array('error_message' => display('not_added')));
            redirect(base_url('Cloan/manage_ofln_person'));
        }
    }
     public function loanheadcode(){
        $query=$this->db->query("SELECT MAX(HeadCode) as HeadCode FROM acc_coa WHERE HeadLevel='4' And HeadCode LIKE '1020302000%'");
        return $query->row();

    }
        public function add_office_loan() {
            $data['person_list'] = $this->Settings->office_loan_person();
            $data['bank_list']   = $this->Web_settings->bank_list();
         $content = $this->parser->parse('settings/add_office_loan', $data, true);
        $this->template->full_admin_html_view($content);
    }






    
    public function submit_loan() {
        $personid = $this->input->post('person_id',TRUE);
        $personinfo = $this->db->select('first_name,last_name')->from('employee_history')->where('id',$personid)->get()->row();
        // print_r($headname);
// echo $this->db->last_query();die();
        $headname = $personid.'-'.$personinfo->first_name." ".$personinfo->last_name;
        // print_r($headname);
        $headcid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName',$headname)->get()->row()->HeadCode;
 
        // echo $this->db->last_query();
        //  print_r($HeadCode);



    $bank_id = $this->input->post('bank_id',TRUE);
// echo "Bank id :".$bank_id;
        if(!empty($bank_id)){
       $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id',$bank_id)->get()->row()->bank_name;
//  echo $this->db->last_query();
       $bankcoaid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName',$bankname)->get()->row()->HeadCode;
    //  echo $this->db->last_query(); die();
   }else{
    $bankcoaid='';
   }
//echo "Bank_COA_id".$bankcoaid;
        $data = array(
            'transaction_id' => $this->input->post('transaction_id',TRUE),
            'person_id'      => $this->input->post('person_id',TRUE),
            'debit'          => $this->input->post('ammount',TRUE),
            'date'           => $this->input->post('date',TRUE),
            'details'        => $this->input->post('details',TRUE),
            'phone'          => $this->input->post('phone',TRUE),
            'create_by'      => $this->session->userdata('user_id'),
            'paytype'          => $this->input->post('paytype',TRUE),
            'bank_name'          => $this->input->post('bank_id',TRUE),
            'status'         => 1
          );

        //  print_r($data); die()

        $loan = array(
          'VNo'            =>  $transaction_id,
          'Vtype'          =>  'LNR',
          'VDate'          =>  $this->input->post('date',TRUE),
          'COAID'          =>  $headcid,
          'Narration'      =>  'Loan for .'.$personinfo->first_name." ".$personinfo->last_name,
          'Debit'          =>  $this->input->post('ammount',TRUE),
          'Credit'         =>  0,
          'IsPosted'       =>  1,
          'CreateBy'       =>  $this->session->userdata('user_id'),
          'CreateDate'     =>  date('Y-m-d H:i:s'),
          'IsAppove'       =>  1
        ); 
         $cc = array(
      'VNo'            =>  $transaction_id,
      'Vtype'          =>  'LNR',
      'VDate'          =>  $this->input->post('date',TRUE),
      'COAID'          =>  1020101,
      'Narration'      =>  'Cash in Hand Credit For '.$personinfo->first_name." ".$personinfo->last_name,
      'Debit'          =>   0,
      'Credit'         =>  $this->input->post('ammount',TRUE),
      'IsPosted'       =>  1,
      'CreateBy'       =>  $this->session->userdata('user_id'),
      'CreateDate'     =>  date('Y-m-d H:i:s'),
      'IsAppove'       =>  1
    ); 

              // bank ledger
   $bankc = array(
      'VNo'            =>  $transaction_id,
      'Vtype'          =>  'LNR',
      'VDate'          =>  $this->input->post('date',TRUE),
      'COAID'          =>  $bankcoaid,
      'Narration'      =>  'Loan for .'.$personinfo->first_name." ".$personinfo->last_name,
      'Debit'          =>  0,
      'Credit'         =>  $this->input->post('ammount',TRUE),
      'IsPosted'       =>  1,
      'CreateBy'       =>  $this->session->userdata('user_id'),
      'CreateDate'     =>  date('Y-m-d H:i:s'),
      'IsAppove'       =>  1
    ); 


        $result = $this->Settings->submit_payment($data);
        if ($result) {
            $this->db->insert('acc_transaction',$loan);
               if($this->input->post('paytype',TRUE) == display('bank_payment')){
        $this->db->insert('acc_transaction',$bankc);
    //    echo $this->db->last_query();
         $this->session->set_userdata(array('message' => display('successfully_added')));
           redirect(base_url('Chrm/manage_officeloan'));
        }
           else{
        $this->db->insert('acc_transaction',$cc);
        //   echo $this->db->last_query();
           $this->session->set_userdata(array('message' => display('successfully_added')));
          redirect(base_url('Chrm/manage_officeloan'));
        }
         
        } else {
            $this->session->set_userdata(array('error_message' => display('not_added')));
          redirect(base_url('Chrm/add_office_loan'));
        }
    }







   public function officeloan_update($transaction_id){

        $this->load->model('Hrm_model');
    //      $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id',$this->input->post('bank_id',true))->get()->row()->bank_name;
    // echo $this->db->last_query();
           $postData = [

                'transaction_id' =>$transaction_id,
                'person_id'            => $this->input->post('person_id',true),
                'phone'    => $this->input->post('phone',true),
                'debit'     => $this->input->post('ammount',true),
                'paytype'   => $this->input->post('paytype',true),
                'phone'         => $this->input->post('phone',true),
                'bank_name' =>$this->input->post('bank_id',true),
                'create_by'       =>  $this->session->userdata('user_id'),
                'bank_id'     => $this->input->post('bank_id',true),
                'date'         => $this->input->post('date',true),
                'details'         => $this->input->post('details',true),

            ];   
          //   print_r( $postData); die();
             if ($this->Settings->update_employee($postData,$transaction_id)) { 
$this->db->insert('person_ledger',$postData);
echo $this->db->last_query();
                $this->session->set_flashdata('message', display('successfully_updated'));
            } else {

              //  $this->db->insert('acc_transaction',$cc);

                $this->session->set_flashdata('error_message',  display('please_try_again'));
            }
             redirect("Chrm/manage_officeloan");
    }

 








    public function add_loan_payment() {
         $data['person_list'] = $this->Settings->office_loan_person();
         $data['bank_list']   = $this->Web_settings->bank_list();
       $content = $this->parser->parse('settings/add_officeloan_payment', $data, true);
        $this->template->full_admin_html_view($content);
    }
  #================Submit loan Payment==============#

    public function submit_payment() {
         $personid = $this->input->post('person_id',TRUE);
        $personinfo = $this->db->select('person_name')->from('person_information')->where('person_id',$personid)->get()->row();
        $headname = $personid.'-'.$personinfo->person_name;
        $headcid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName',$headname)->get()->row()->HeadCode;
        $transaction_id = $this->auth->generator(10);

   $bank_id = $this->input->post('bank_id',TRUE);
        if(!empty($bank_id)){
       $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id',$bank_id)->get()->row()->bank_name;
    
       $bankcoaid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName',$bankname)->get()->row()->HeadCode;
   }else{
    $bankcoaid='';
   }

        $data = array(
            'transaction_id' => $transaction_id,
            'person_id'      => $this->input->post('person_id',TRUE),
            'credit'         => $this->input->post('ammount',TRUE),
            'date'           => $this->input->post('date',TRUE),
            'details'        => $this->input->post('details',TRUE),
            'status'         => 2
        );
        $paidloan = array(
          'VNo'            =>  $transaction_id,
          'Vtype'          =>  'LNP',
          'VDate'          =>  $this->input->post('date',TRUE),
          'COAID'          =>  $headcid,
          'Narration'      =>  'Loan Payment from .'.$personinfo->person_name,
          'Debit'          =>  0,
          'Credit'         =>  $this->input->post('ammount',TRUE),
          'IsPosted'       =>  1,
          'CreateBy'       =>  $this->session->userdata('user_id'),
          'CreateDate'     =>  date('Y-m-d H:i:s'),
          'IsAppove'       =>  1
        ); 
         $cc = array(
      'VNo'            =>  $transaction_id,
      'Vtype'          =>  'LNR',
      'VDate'          =>  $this->input->post('date',TRUE),
      'COAID'          =>  1020101,
      'Narration'      =>  'Cash in Hand Debit For '.$personinfo->person_name,
      'Debit'          =>  $this->input->post('ammount',TRUE),
      'Credit'         =>  0,
      'IsPosted'       =>  1,
      'CreateBy'       =>  $this->session->userdata('user_id'),
      'CreateDate'     =>  date('Y-m-d H:i:s'),
      'IsAppove'       =>  1
    ); 


   // bank ledger
   $bankc = array(
      'VNo'            =>  $transaction_id,
      'Vtype'          =>  'LNR',
      'VDate'          =>  $this->input->post('date',TRUE),
      'COAID'          =>  $bankcoaid,
      'Narration'      =>  'Loan for .'.$personinfo->person_name,
      'Debit'          =>  $this->input->post('ammount',TRUE),
      'Credit'         =>  0,
      'IsPosted'       =>  1,
      'CreateBy'       =>  $this->session->userdata('user_id'),
      'CreateDate'     =>  date('Y-m-d H:i:s'),
      'IsAppove'       =>  1
    ); 



        $result = $this->Settings->submit_payment($data);
        if ($result) {
            $this->db->insert('acc_transaction',$paidloan);
            if($this->input->post('paytype',TRUE) == 2){
        $this->db->insert('acc_transaction',$bankc);
       
        }
            if($this->input->post('paytype',TRUE) == 1){
        $this->db->insert('acc_transaction',$cc);
        }
            $this->session->set_userdata(array('message' => display('successfully_added')));
            redirect(base_url('Cloan/add_loan_payment'));
        } else {
            $this->session->set_userdata(array('error_message' => display('not_added')));
            redirect(base_url('Cloan/add_loan_payment'));
        }
    }
        //Person loan search by phone number
    public function loan_phone_search_by_name() {
        $person_id = $this->input->post('person_id',TRUE);
        $result = $this->db->select('person_phone')
                ->from('person_information')
                ->where('person_id', $person_id)
                ->get()
                ->row();
        if ($result) {
            echo $result->person_phone;
        } else {
            return false;
        }
    }

}
