<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admissions_Data_Processor {
    
    private $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('data_fetcher');
        $this->CI->load->model('data_poster');
    }
    
    /**
     * Collect comprehensive admissions data for AI analysis
     */
    public function collect_admissions_data($term_id, $start_date = null, $end_date = null)
    {
        $term_data = $this->CI->data_fetcher->get_sem_by_id($term_id);
        
        // Get admissions statistics from external API (similar to admissions_report)
        $api_stats = $this->get_api_admissions_stats($term_id, $start_date, $end_date);
        
        // Get registration data
        $registrations = $this->get_registration_data($term_id, $start_date, $end_date);
        
        // Get program-wise breakdown
        $program_breakdown = $this->get_program_breakdown($term_id);
        
        // Get temporal patterns
        $temporal_data = $this->get_temporal_patterns($term_id, $start_date, $end_date);
        
        // Get conversion funnel data
        $funnel_data = $this->calculate_conversion_funnel($api_stats);
        
        return array(
            'term_info' => $term_data,
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'term_id' => $term_id
            ),
            'api_stats' => $api_stats,
            'registrations' => $registrations,
            'program_breakdown' => $program_breakdown,
            'temporal_data' => $temporal_data,
            'funnel_data' => $funnel_data,
            'summary_metrics' => $this->calculate_summary_metrics($api_stats, $registrations)
        );
    }
    
    /**
     * Get admissions statistics from API (simulating the external API call)
     */
    private function get_api_admissions_stats($term_id, $start_date = null, $end_date = null)
    {
        // This simulates the API call that the original admissions_report makes
        // In a real implementation, you would make the actual API call here
        
        // For now, we'll create sample data structure based on the admissions_report view
        return array(
            'paid' => 150,
            'unpaid' => 75,
            'will_not_proceed' => 25,
            'cancelled' => 10,
            'interviewed' => 140,
            'for_reservation' => 120,
            'rejected' => 15,
            'did_not_reserve' => 20,
            'reserved' => 85,
            'confirmed' => 80,
            'enlisted' => 75,
            'for_enrollment' => 70,
            'enrolled' => 65,
            'withdrawn_before' => 5,
            'withdrawn_after' => 3,
            'withdrawn_end' => 2,
            'floating' => 10,
            'for_interview' => 15,
            'waiting' => 5,
            'new' => 30
        );
    }
    
    /**
     * Get registration data with timestamps
     */
    private function get_registration_data($term_id, $start_date = null, $end_date = null)
    {
        $where_clause = array('intAYID' => $term_id);
        
        if ($start_date && $end_date) {
            $where_clause['dteRegistered >='] = $start_date;
            $where_clause['dteRegistered <='] = $end_date;
        }
        
        $registrations = $this->CI->db->select('tb_mas_users.slug, tb_mas_registration.dteRegistered, tb_mas_registration.enumStudentType, tb_mas_users.intProgramID, tb_mas_programs.strProgramCode')
                                     ->from('tb_mas_registration')
                                     ->join('tb_mas_users', 'tb_mas_registration.intStudentID = tb_mas_users.intID')
                                     ->join('tb_mas_programs', 'tb_mas_users.intProgramID = tb_mas_programs.intProgramID', 'left')
                                     ->where($where_clause)
                                     ->get()
                                     ->result_array();
        
        return $registrations;
    }
    
    /**
     * Get program-wise breakdown of admissions
     */
    private function get_program_breakdown($term_id)
    {
        $programs = $this->CI->db->select('tb_mas_programs.intProgramID, tb_mas_programs.strProgramCode, tb_mas_programs.strProgramDescription, COUNT(tb_mas_registration.intRegistrationID) as total_registrations')
                                ->from('tb_mas_programs')
                                ->join('tb_mas_users', 'tb_mas_programs.intProgramID = tb_mas_users.intProgramID', 'left')
                                ->join('tb_mas_registration', 'tb_mas_users.intID = tb_mas_registration.intStudentID', 'left')
                                ->where('tb_mas_registration.intAYID', $term_id)
                                ->group_by('tb_mas_programs.intProgramID')
                                ->get()
                                ->result_array();
        
        return $programs;
    }
    
    /**
     * Get temporal patterns (registrations over time)
     */
    private function get_temporal_patterns($term_id, $start_date = null, $end_date = null)
    {
        $where_clause = array('intAYID' => $term_id);
        
        if ($start_date && $end_date) {
            $where_clause['dteRegistered >='] = $start_date;
            $where_clause['dteRegistered <='] = $end_date;
        }
        
        $daily_registrations = $this->CI->db->select('DATE(dteRegistered) as registration_date, COUNT(*) as count')
                                           ->from('tb_mas_registration')
                                           ->where($where_clause)
                                           ->group_by('DATE(dteRegistered)')
                                           ->order_by('registration_date', 'ASC')
                                           ->get()
                                           ->result_array();
        
        return array(
            'daily_registrations' => $daily_registrations,
            'peak_days' => $this->identify_peak_days($daily_registrations),
            'weekly_patterns' => $this->calculate_weekly_patterns($daily_registrations)
        );
    }
    
    /**
     * Calculate conversion funnel metrics
     */
    private function calculate_conversion_funnel($api_stats)
    {
        $total_signups = $api_stats['paid'] + $api_stats['unpaid'];
        
        $funnel = array(
            'signups' => $total_signups,
            'paid_applications' => $api_stats['paid'],
            'interviewed' => $api_stats['interviewed'],
            'reserved' => $api_stats['reserved'],
            'enrolled' => $api_stats['enrolled']
        );
        
        // Calculate conversion rates
        $conversion_rates = array(
            'signup_to_paid' => $total_signups > 0 ? ($api_stats['paid'] / $total_signups) * 100 : 0,
            'paid_to_interviewed' => $api_stats['paid'] > 0 ? ($api_stats['interviewed'] / $api_stats['paid']) * 100 : 0,
            'interviewed_to_reserved' => $api_stats['interviewed'] > 0 ? ($api_stats['reserved'] / $api_stats['interviewed']) * 100 : 0,
            'reserved_to_enrolled' => $api_stats['reserved'] > 0 ? ($api_stats['enrolled'] / $api_stats['reserved']) * 100 : 0,
            'overall_conversion' => $total_signups > 0 ? ($api_stats['enrolled'] / $total_signups) * 100 : 0
        );
        
        return array(
            'funnel_stages' => $funnel,
            'conversion_rates' => $conversion_rates,
            'drop_off_points' => $this->identify_drop_off_points($funnel, $conversion_rates)
        );
    }
    
    /**
     * Calculate summary metrics
     */
    private function calculate_summary_metrics($api_stats, $registrations)
    {
        $total_signups = $api_stats['paid'] + $api_stats['unpaid'];
        
        return array(
            'total_applications' => $total_signups,
            'total_enrolled' => $api_stats['enrolled'],
            'overall_conversion_rate' => $total_signups > 0 ? ($api_stats['enrolled'] / $total_signups) * 100 : 0,
            'payment_rate' => $total_signups > 0 ? ($api_stats['paid'] / $total_signups) * 100 : 0,
            'interview_show_rate' => $api_stats['paid'] > 0 ? ($api_stats['interviewed'] / $api_stats['paid']) * 100 : 0,
            'reservation_rate' => $api_stats['interviewed'] > 0 ? ($api_stats['reserved'] / $api_stats['interviewed']) * 100 : 0,
            'enrollment_rate' => $api_stats['reserved'] > 0 ? ($api_stats['enrolled'] / $api_stats['reserved']) * 100 : 0,
            'total_withdrawals' => $api_stats['withdrawn_before'] + $api_stats['withdrawn_after'] + $api_stats['withdrawn_end'],
            'withdrawal_rate' => $api_stats['enrolled'] > 0 ? (($api_stats['withdrawn_before'] + $api_stats['withdrawn_after'] + $api_stats['withdrawn_end']) / $api_stats['enrolled']) * 100 : 0
        );
    }
    
    /**
     * Get historical trends for comparison
     */
    public function get_historical_trends($current_term, $terms_back = 6)
    {
        $historical_data = array();
        
        // Get previous terms
        $terms = $this->CI->db->select('intID, enumSem, strYearStart, strYearEnd, term_label')
                             ->from('tb_mas_sy')
                             ->where('intID <=', $current_term)
                             ->order_by('intID', 'DESC')
                             ->limit($terms_back)
                             ->get()
                             ->result_array();
        
        foreach ($terms as $term) {
            $term_data = $this->collect_admissions_data($term['intID']);
            $historical_data[] = array(
                'term_info' => $term,
                'metrics' => $term_data['summary_metrics'],
                'funnel_data' => $term_data['funnel_data']
            );
        }
        
        return array(
            'historical_data' => array_reverse($historical_data), // Chronological order
            'trends' => $this->calculate_trends($historical_data)
        );
    }
    
    /**
     * Get real-time metrics for dashboard
     */
    public function get_realtime_metrics($term_id)
    {
        $today = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        $month_ago = date('Y-m-d', strtotime('-30 days'));
        
        return array(
            'today' => $this->get_daily_metrics($term_id, $today),
            'this_week' => $this->get_period_metrics($term_id, $week_ago, $today),
            'this_month' => $this->get_period_metrics($term_id, $month_ago, $today),
            'term_total' => $this->get_period_metrics($term_id, null, null)
        );
    }
    
    /**
     * Helper methods
     */
    private function identify_peak_days($daily_registrations)
    {
        if (empty($daily_registrations)) return array();
        
        $max_count = max(array_column($daily_registrations, 'count'));
        $peak_days = array_filter($daily_registrations, function($day) use ($max_count) {
            return $day['count'] == $max_count;
        });
        
        return array_values($peak_days);
    }
    
    private function calculate_weekly_patterns($daily_registrations)
    {
        $weekly_data = array();
        
        foreach ($daily_registrations as $day) {
            $day_of_week = date('l', strtotime($day['registration_date']));
            if (!isset($weekly_data[$day_of_week])) {
                $weekly_data[$day_of_week] = 0;
            }
            $weekly_data[$day_of_week] += $day['count'];
        }
        
        return $weekly_data;
    }
    
    private function identify_drop_off_points($funnel, $conversion_rates)
    {
        $drop_offs = array();
        
        if ($conversion_rates['signup_to_paid'] < 50) {
            $drop_offs[] = array(
                'stage' => 'Signup to Payment',
                'rate' => $conversion_rates['signup_to_paid'],
                'severity' => 'high'
            );
        }
        
        if ($conversion_rates['paid_to_interviewed'] < 80) {
            $drop_offs[] = array(
                'stage' => 'Payment to Interview',
                'rate' => $conversion_rates['paid_to_interviewed'],
                'severity' => 'medium'
            );
        }
        
        if ($conversion_rates['interviewed_to_reserved'] < 70) {
            $drop_offs[] = array(
                'stage' => 'Interview to Reservation',
                'rate' => $conversion_rates['interviewed_to_reserved'],
                'severity' => 'high'
            );
        }
        
        if ($conversion_rates['reserved_to_enrolled'] < 85) {
            $drop_offs[] = array(
                'stage' => 'Reservation to Enrollment',
                'rate' => $conversion_rates['reserved_to_enrolled'],
                'severity' => 'medium'
            );
        }
        
        return $drop_offs;
    }
    
    private function calculate_trends($historical_data)
    {
        if (count($historical_data) < 2) return array();
        
        $trends = array();
        $metrics = array('overall_conversion_rate', 'payment_rate', 'interview_show_rate', 'reservation_rate', 'enrollment_rate');
        
        foreach ($metrics as $metric) {
            $values = array_column(array_column($historical_data, 'metrics'), $metric);
            $trends[$metric] = array(
                'direction' => $this->calculate_trend_direction($values),
                'change_percentage' => $this->calculate_percentage_change($values),
                'values' => $values
            );
        }
        
        return $trends;
    }
    
    private function calculate_trend_direction($values)
    {
        if (count($values) < 2) return 'stable';
        
        $recent = array_slice($values, -3); // Last 3 terms
        $older = array_slice($values, 0, -3);
        
        if (empty($older)) return 'stable';
        
        $recent_avg = array_sum($recent) / count($recent);
        $older_avg = array_sum($older) / count($older);
        
        if ($recent_avg > $older_avg * 1.05) return 'increasing';
        if ($recent_avg < $older_avg * 0.95) return 'decreasing';
        return 'stable';
    }
    
    private function calculate_percentage_change($values)
    {
        if (count($values) < 2) return 0;
        
        $latest = end($values);
        $previous = $values[count($values) - 2];
        
        if ($previous == 0) return 0;
        
        return (($latest - $previous) / $previous) * 100;
    }
    
    private function get_daily_metrics($term_id, $date)
    {
        $registrations = $this->CI->db->select('COUNT(*) as count')
                                     ->from('tb_mas_registration')
                                     ->where('intAYID', $term_id)
                                     ->where('DATE(dteRegistered)', $date)
                                     ->get()
                                     ->row_array();
        
        return array(
            'registrations' => $registrations['count']
        );
    }
    
    private function get_period_metrics($term_id, $start_date, $end_date)
    {
        $where = array('intAYID' => $term_id);
        
        if ($start_date) $where['dteRegistered >='] = $start_date;
        if ($end_date) $where['dteRegistered <='] = $end_date;
        
        $registrations = $this->CI->db->select('COUNT(*) as count')
                                     ->from('tb_mas_registration')
                                     ->where($where)
                                     ->get()
                                     ->row_array();
        
        return array(
            'registrations' => $registrations['count']
        );
    }
}
