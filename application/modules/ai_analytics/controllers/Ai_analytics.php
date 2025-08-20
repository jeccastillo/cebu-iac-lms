<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ai_analytics extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // User Level Validation - same as AdmissionsV1 controller
        $userlevel = $this->session->userdata('intUserLevel');   
        $ip = $this->input->ip_address();        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 7 && $userlevel != 3 &&  $ip != "172.16.80.22")
            redirect(base_url()."unity");

        $this->config->load('themes');		
        $theme = $this->config->item('unity');
        if($theme == "" || !isset($theme))
            $theme = $this->config->item('global_theme');
        
        $settings = $this->data_fetcher->fetch_table('su-tb_sys_settings');
        foreach($settings as $setting)
        {
            $this->settings[$setting['strSettingName']] = $setting['strSettingValue'];
        }
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity - AI Analytics";
        
        $this->load->library("email");	
        $this->load->helper("cms_form");	
        $this->load->model("user_model");
        $this->load->library('Admissions_Data_Processor');
        $this->load->library('AI_Analyzer');
        
        $this->config->load('courses');
        $this->data['department_config'] = $this->config->item('department');
        $this->data['terms'] = $this->config->item('terms');
        $this->data['term_type'] = $this->config->item('term_type');
        
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['campus'] = $this->config->item('campus');
        $sem = $this->data_fetcher->get_processing_sem();        
        $this->data['current_sem'] = $sem['intID'];
        $this->data['page'] = "ai_analytics";
        $this->data['opentree'] = "leads";
    }
    
    /**
     * Main AI Analytics Dashboard
     */
    public function index($term = 0)
    {
        if($term == 0)
            $term = $this->data_fetcher->get_processing_sem();        
        else
            $term = $this->data_fetcher->get_sem_by_id($term);
            
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['current_sem'] = $term['intID'];
        $this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/ai_analysis",$this->data);
        $this->load->view("common/footer",$this->data);
    }
    
    /**
     * Generate AI Analysis for Admissions Data
     */
    public function generate_analysis()
    {
        $term = $this->input->post('term') ?: $this->data_fetcher->get_processing_sem()['intID'];
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $analysis_type = $this->input->post('analysis_type') ?: 'comprehensive';
        
        try {
            // Collect and process admissions data
            $admissions_data = $this->admissions_data_processor->collect_admissions_data($term, $start_date, $end_date);
            
            // Generate AI analysis
            $ai_analysis = $this->ai_analyzer->analyze_admissions_data($admissions_data, $analysis_type);
            
            $response = array(
                'success' => true,
                'data' => $ai_analysis,
                'term_info' => $this->data_fetcher->get_sem_by_id($term),
                'generated_at' => date('Y-m-d H:i:s')
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Get Historical Trends Data
     */
    public function get_historical_trends()
    {
        $terms_back = $this->input->post('terms_back') ?: 6; // Default 6 terms back
        $current_term = $this->input->post('current_term') ?: $this->data_fetcher->get_processing_sem()['intID'];
        
        try {
            $trends_data = $this->admissions_data_processor->get_historical_trends($current_term, $terms_back);
            
            $response = array(
                'success' => true,
                'data' => $trends_data
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Export AI Analysis Report
     */
    public function export_analysis()
    {
        $analysis_data = $this->input->post('analysis_data');
        $format = $this->input->post('format') ?: 'pdf'; // pdf, excel, csv
        
        try {
            $export_result = $this->ai_analyzer->export_analysis($analysis_data, $format);
            
            $response = array(
                'success' => true,
                'download_url' => $export_result['url'],
                'filename' => $export_result['filename']
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Save AI Analysis Results
     */
    public function save_analysis()
    {
        $analysis_data = $this->input->post('analysis_data');
        $term = $this->input->post('term');
        $analysis_type = $this->input->post('analysis_type');
        
        try {
            $save_data = array(
                'term_id' => $term,
                'analysis_type' => $analysis_type,
                'analysis_data' => json_encode($analysis_data),
                'generated_by' => $this->session->userdata('intID'),
                'generated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            // Save to database (we'll create this table later if needed)
            $this->data_poster->post_data('tb_mas_ai_analysis_results', $save_data);
            
            $response = array(
                'success' => true,
                'message' => 'Analysis saved successfully'
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Get Saved Analysis Results
     */
    public function get_saved_analyses($term = null)
    {
        try {
            $where = array();
            if($term) {
                $where['term_id'] = $term;
            }
            
            $saved_analyses = $this->data_fetcher->fetch_table('tb_mas_ai_analysis_results', array('generated_at', 'desc'), 10, $where);
            
            $response = array(
                'success' => true,
                'data' => $saved_analyses
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Get Real-time Admissions Metrics
     */
    public function get_realtime_metrics()
    {
        $term = $this->input->post('term') ?: $this->data_fetcher->get_processing_sem()['intID'];
        
        try {
            $metrics = $this->admissions_data_processor->get_realtime_metrics($term);
            
            $response = array(
                'success' => true,
                'data' => $metrics,
                'last_updated' => date('Y-m-d H:i:s')
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
