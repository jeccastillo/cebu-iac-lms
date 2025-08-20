<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AI_Analyzer {
    
    private $CI;
    private $ai_config;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('ai_config');
        $this->ai_config = $this->CI->config->item('ai_settings');
    }
    
    /**
     * Main method to analyze admissions data using AI
     */
    public function analyze_admissions_data($admissions_data, $analysis_type = 'comprehensive')
    {
        try {
            // Prepare data for AI analysis
            $formatted_data = $this->format_data_for_ai($admissions_data);
            
            // Get appropriate prompt based on analysis type
            $prompt = $this->get_analysis_prompt($analysis_type, $formatted_data);
            
            // Call AI service
            $ai_response = $this->call_ai_service($prompt);
            
            // Process and structure the AI response
            $structured_analysis = $this->process_ai_response($ai_response, $analysis_type);
            
            return array(
                'analysis_type' => $analysis_type,
                'raw_response' => $ai_response,
                'structured_analysis' => $structured_analysis,
                'data_summary' => $this->create_data_summary($admissions_data),
                'recommendations' => $this->extract_recommendations($structured_analysis),
                'generated_at' => date('Y-m-d H:i:s')
            );
            
        } catch (Exception $e) {
            throw new Exception('AI Analysis failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Format admissions data for AI consumption
     */
    private function format_data_for_ai($admissions_data)
    {
        $formatted = array(
            'term_info' => array(
                'term' => $admissions_data['term_info']['enumSem'] . ' ' . $admissions_data['term_info']['strYearStart'] . '-' . $admissions_data['term_info']['strYearEnd'],
                'type' => $admissions_data['term_info']['term_student_type']
            ),
            'summary_metrics' => $admissions_data['summary_metrics'],
            'funnel_data' => $admissions_data['funnel_data'],
            'program_breakdown' => $admissions_data['program_breakdown'],
            'temporal_patterns' => array(
                'peak_days' => $admissions_data['temporal_data']['peak_days'],
                'weekly_patterns' => $admissions_data['temporal_data']['weekly_patterns']
            ),
            'api_stats' => $admissions_data['api_stats']
        );
        
        return $formatted;
    }
    
    /**
     * Get analysis prompt based on type
     */
    private function get_analysis_prompt($analysis_type, $data)
    {
        $base_context = "You are an expert education consultant analyzing admissions data for iACADEMY Cebu. ";
        $base_context .= "Analyze the following admissions data and provide actionable insights and recommendations.\n\n";
        
        $data_context = "ADMISSIONS DATA:\n";
        $data_context .= "Term: " . $data['term_info']['term'] . " (" . $data['term_info']['type'] . ")\n";
        $data_context .= "Total Applications: " . $data['summary_metrics']['total_applications'] . "\n";
        $data_context .= "Total Enrolled: " . $data['summary_metrics']['total_enrolled'] . "\n";
        $data_context .= "Overall Conversion Rate: " . number_format($data['summary_metrics']['overall_conversion_rate'], 2) . "%\n";
        $data_context .= "Payment Rate: " . number_format($data['summary_metrics']['payment_rate'], 2) . "%\n";
        $data_context .= "Interview Show Rate: " . number_format($data['summary_metrics']['interview_show_rate'], 2) . "%\n";
        $data_context .= "Reservation Rate: " . number_format($data['summary_metrics']['reservation_rate'], 2) . "%\n";
        $data_context .= "Enrollment Rate: " . number_format($data['summary_metrics']['enrollment_rate'], 2) . "%\n";
        $data_context .= "Withdrawal Rate: " . number_format($data['summary_metrics']['withdrawal_rate'], 2) . "%\n\n";
        
        $data_context .= "CONVERSION FUNNEL:\n";
        foreach ($data['funnel_data']['conversion_rates'] as $stage => $rate) {
            $data_context .= ucwords(str_replace('_', ' ', $stage)) . ": " . number_format($rate, 2) . "%\n";
        }
        
        if (!empty($data['funnel_data']['drop_off_points'])) {
            $data_context .= "\nIDENTIFIED DROP-OFF POINTS:\n";
            foreach ($data['funnel_data']['drop_off_points'] as $drop_off) {
                $data_context .= "- " . $drop_off['stage'] . ": " . number_format($drop_off['rate'], 2) . "% (Severity: " . $drop_off['severity'] . ")\n";
            }
        }
        
        $data_context .= "\nPROGRAM BREAKDOWN:\n";
        foreach ($data['program_breakdown'] as $program) {
            $data_context .= "- " . $program['strProgramCode'] . ": " . $program['total_registrations'] . " registrations\n";
        }
        
        switch ($analysis_type) {
            case 'conversion_optimization':
                $specific_prompt = "\nFOCUS: Analyze conversion rates at each stage and provide specific recommendations to improve conversion from applications to enrollment. Identify the biggest bottlenecks and suggest actionable solutions.";
                break;
                
            case 'program_analysis':
                $specific_prompt = "\nFOCUS: Analyze program-wise performance and suggest strategies to improve enrollment in underperforming programs. Identify trends and opportunities for program marketing.";
                break;
                
            case 'temporal_analysis':
                $specific_prompt = "\nFOCUS: Analyze timing patterns in applications and enrollments. Suggest optimal timing for marketing campaigns and admission activities.";
                break;
                
            case 'competitive_analysis':
                $specific_prompt = "\nFOCUS: Provide insights on competitive positioning and market opportunities based on the admission patterns and conversion rates.";
                break;
                
            default: // comprehensive
                $specific_prompt = "\nFOCUS: Provide a comprehensive analysis covering all aspects: conversion optimization, program performance, timing patterns, and strategic recommendations for improving overall admissions performance.";
        }
        
        $output_format = "\n\nPLEASE STRUCTURE YOUR RESPONSE AS FOLLOWS:\n";
        $output_format .= "1. EXECUTIVE SUMMARY (2-3 key findings)\n";
        $output_format .= "2. DETAILED ANALYSIS (breakdown by area)\n";
        $output_format .= "3. KEY INSIGHTS (bullet points)\n";
        $output_format .= "4. ACTIONABLE RECOMMENDATIONS (prioritized list with expected impact)\n";
        $output_format .= "5. METRICS TO TRACK (KPIs to monitor improvement)\n";
        $output_format .= "6. IMPLEMENTATION TIMELINE (short-term vs long-term actions)\n";
        
        return $base_context . $data_context . $specific_prompt . $output_format;
    }
    
    /**
     * Call AI service (OpenAI, Google AI, etc.)
     */
    private function call_ai_service($prompt)
    {
        $ai_service = isset($this->ai_config['service']) ? $this->ai_config['service'] : 'mock';
        
        switch ($ai_service) {
            case 'openai':
                return $this->call_openai($prompt);
            case 'google':
                return $this->call_google_ai($prompt);
            case 'anthropic':
                return $this->call_anthropic($prompt);
            default:
                // Fallback to mock response for development
                return $this->generate_mock_response($prompt);
        }
    }
    
    /**
     * OpenAI API integration
     */
    private function call_openai($prompt)
    {
        $api_key = isset($this->ai_config['openai']['api_key']) ? $this->ai_config['openai']['api_key'] : '';
        
        if (empty($api_key)) {
            throw new Exception('OpenAI API key not configured');
        }
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => isset($this->ai_config['openai']['model']) ? $this->ai_config['openai']['model'] : 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an expert education consultant specializing in admissions analytics and optimization.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => isset($this->ai_config['openai']['max_tokens']) ? $this->ai_config['openai']['max_tokens'] : 2000,
            'temperature' => isset($this->ai_config['openai']['temperature']) ? $this->ai_config['openai']['temperature'] : 0.7
        );
        
        $json_data = json_encode($data);
        
        // Check if cURL is available, otherwise use file_get_contents
        if (function_exists('curl_init')) {
            $headers = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            );
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                throw new Exception('OpenAI API request failed with code: ' . $http_code);
            }
        } else {
            // Fallback to file_get_contents if cURL is not available
            $context_options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" .
                               "Authorization: Bearer " . $api_key . "\r\n",
                    'content' => $json_data,
                    'timeout' => 30
                )
            );
            
            $context = stream_context_create($context_options);
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new Exception('OpenAI API request failed using file_get_contents');
            }
        }
        
        $decoded_response = json_decode($response, true);
        
        if (!isset($decoded_response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response from OpenAI API');
        }
        
        return $decoded_response['choices'][0]['message']['content'];
    }
    
    /**
     * Google AI integration (placeholder)
     */
    private function call_google_ai($prompt)
    {
        // Implement Google AI API integration here
        throw new Exception('Google AI integration not yet implemented');
    }
    
    /**
     * Anthropic Claude integration (placeholder)
     */
    private function call_anthropic($prompt)
    {
        // Implement Anthropic API integration here
        throw new Exception('Anthropic integration not yet implemented');
    }
    
    /**
     * Generate mock response for development/testing
     */
    private function generate_mock_response($prompt)
    {
        return "# EXECUTIVE SUMMARY

Based on the admissions data analysis, three critical areas need immediate attention:

1. **Payment Conversion Gap**: Only 67% of applicants complete payment, indicating potential friction in the payment process
2. **Interview Show Rate**: 93% interview attendance is strong, but there's room for improvement in pre-interview engagement
3. **Reservation to Enrollment**: 76% conversion suggests some students are having second thoughts after reservation

# DETAILED ANALYSIS

## Conversion Funnel Performance
- **Signup to Payment (67%)**: Below industry standard of 75-80%
- **Payment to Interview (93%)**: Excellent performance
- **Interview to Reservation (85%)**: Good but could be optimized
- **Reservation to Enrollment (76%)**: Needs improvement

## Program Performance
- Computer Science programs show strongest conversion rates
- Business programs have higher application volume but lower conversion
- Creative programs need targeted marketing strategies

# KEY INSIGHTS

• Payment process friction is the primary bottleneck
• Interview quality is high based on conversion rates
• Post-reservation follow-up needs strengthening
• Timing patterns show peak applications in March-April
• Weekend applications have higher conversion rates

# ACTIONABLE RECOMMENDATIONS

## High Priority (Immediate - 1-2 weeks)
1. **Streamline Payment Process**
   - Implement multiple payment options (GCash, PayMaya, installments)
   - Add payment reminders and deadline extensions
   - Expected Impact: +8-12% payment conversion

2. **Enhance Post-Reservation Engagement**
   - Create welcome packet for reserved students
   - Implement weekly check-ins until enrollment
   - Expected Impact: +5-8% enrollment conversion

## Medium Priority (1-2 months)
3. **Program-Specific Marketing**
   - Develop targeted campaigns for underperforming programs
   - Create program-specific success stories and testimonials
   - Expected Impact: +10-15% overall applications

4. **Optimize Application Timing**
   - Focus marketing efforts on identified peak periods
   - Create urgency campaigns during slow periods
   - Expected Impact: +5-10% application volume

## Long-term (3-6 months)
5. **Implement Predictive Analytics**
   - Develop lead scoring system
   - Create early warning system for at-risk applicants
   - Expected Impact: +15-20% overall efficiency

# METRICS TO TRACK

## Primary KPIs
- Payment conversion rate (target: 75%+)
- Overall funnel conversion (target: 35%+)
- Reservation to enrollment rate (target: 85%+)

## Secondary KPIs
- Time to payment completion
- Interview satisfaction scores
- Program-wise conversion rates
- Cost per enrolled student

# IMPLEMENTATION TIMELINE

## Week 1-2: Quick Wins
- Payment process improvements
- Basic follow-up automation

## Month 1-2: Process Optimization
- Enhanced engagement workflows
- Program-specific campaigns

## Month 3-6: Strategic Initiatives
- Predictive analytics implementation
- Advanced personalization systems

**Expected Overall Impact**: 20-30% improvement in enrollment conversion within 6 months.";
    }
    
    /**
     * Process AI response into structured format
     */
    private function process_ai_response($ai_response, $analysis_type)
    {
        // Parse the AI response into structured sections
        $sections = $this->parse_response_sections($ai_response);
        
        return array(
            'executive_summary' => isset($sections['executive_summary']) ? $sections['executive_summary'] : '',
            'detailed_analysis' => isset($sections['detailed_analysis']) ? $sections['detailed_analysis'] : '',
            'key_insights' => $this->parse_bullet_points(isset($sections['key_insights']) ? $sections['key_insights'] : ''),
            'recommendations' => $this->parse_recommendations(isset($sections['recommendations']) ? $sections['recommendations'] : ''),
            'metrics_to_track' => $this->parse_bullet_points(isset($sections['metrics_to_track']) ? $sections['metrics_to_track'] : ''),
            'implementation_timeline' => isset($sections['implementation_timeline']) ? $sections['implementation_timeline'] : '',
            'analysis_type' => $analysis_type
        );
    }
    
    /**
     * Parse response into sections
     */
    private function parse_response_sections($response)
    {
        $sections = array();
        
        // Define section headers to look for
        $headers = array(
            'executive_summary' => array('EXECUTIVE SUMMARY', 'Executive Summary', '# EXECUTIVE SUMMARY'),
            'detailed_analysis' => array('DETAILED ANALYSIS', 'Detailed Analysis', '# DETAILED ANALYSIS'),
            'key_insights' => array('KEY INSIGHTS', 'Key Insights', '# KEY INSIGHTS'),
            'recommendations' => array('ACTIONABLE RECOMMENDATIONS', 'Recommendations', '# ACTIONABLE RECOMMENDATIONS'),
            'metrics_to_track' => array('METRICS TO TRACK', 'Metrics to Track', '# METRICS TO TRACK'),
            'implementation_timeline' => array('IMPLEMENTATION TIMELINE', 'Implementation Timeline', '# IMPLEMENTATION TIMELINE')
        );
        
        foreach ($headers as $section_key => $possible_headers) {
            foreach ($possible_headers as $header) {
                $pattern = '/(?:^|\n)(?:#\s*)?' . preg_quote($header, '/') . '\s*\n(.*?)(?=\n(?:#\s*)?(?:' . implode('|', array_merge(...array_values($headers))) . ')|\Z)/s';
                if (preg_match($pattern, $response, $matches)) {
                    $sections[$section_key] = trim($matches[1]);
                    break;
                }
            }
        }
        
        return $sections;
    }
    
    /**
     * Parse bullet points from text
     */
    private function parse_bullet_points($text)
    {
        $lines = explode("\n", $text);
        $bullet_points = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[•\-\*]\s*(.+)/', $line, $matches)) {
                $bullet_points[] = trim($matches[1]);
            }
        }
        
        return $bullet_points;
    }
    
    /**
     * Parse recommendations with priority levels
     */
    private function parse_recommendations($text)
    {
        $recommendations = array();
        $current_priority = 'medium';
        
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Check for priority headers
            if (preg_match('/high priority|immediate/i', $line)) {
                $current_priority = 'high';
                continue;
            } elseif (preg_match('/medium priority/i', $line)) {
                $current_priority = 'medium';
                continue;
            } elseif (preg_match('/low priority|long.?term/i', $line)) {
                $current_priority = 'low';
                continue;
            }
            
            // Parse numbered recommendations
            if (preg_match('/^\d+\.\s*\*\*(.+?)\*\*/', $line, $matches)) {
                $recommendations[] = array(
                    'title' => trim($matches[1]),
                    'priority' => $current_priority,
                    'description' => $line
                );
            } elseif (preg_match('/^[•\-\*]\s*(.+)/', $line)) {
                $recommendations[] = array(
                    'title' => trim($line),
                    'priority' => $current_priority,
                    'description' => trim($line)
                );
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Extract key recommendations for quick display
     */
    private function extract_recommendations($structured_analysis)
    {
        $recommendations = isset($structured_analysis['recommendations']) ? $structured_analysis['recommendations'] : array();
        
        // Sort by priority
        usort($recommendations, function($a, $b) {
            $priority_order = array('high' => 1, 'medium' => 2, 'low' => 3);
            return $priority_order[$a['priority']] - $priority_order[$b['priority']];
        });
        
        return array_slice($recommendations, 0, 5); // Top 5 recommendations
    }
    
    /**
     * Create data summary for context
     */
    private function create_data_summary($admissions_data)
    {
        return array(
            'term' => $admissions_data['term_info']['enumSem'] . ' ' . $admissions_data['term_info']['strYearStart'] . '-' . $admissions_data['term_info']['strYearEnd'],
            'total_applications' => $admissions_data['summary_metrics']['total_applications'],
            'total_enrolled' => $admissions_data['summary_metrics']['total_enrolled'],
            'conversion_rate' => $admissions_data['summary_metrics']['overall_conversion_rate'],
            'programs_analyzed' => count($admissions_data['program_breakdown']),
            'analysis_period' => array(
                'start' => $admissions_data['period']['start_date'],
                'end' => $admissions_data['period']['end_date']
            )
        );
    }
    
    /**
     * Export analysis to different formats
     */
    public function export_analysis($analysis_data, $format = 'pdf')
    {
        switch ($format) {
            case 'pdf':
                return $this->export_to_pdf($analysis_data);
            case 'excel':
                return $this->export_to_excel($analysis_data);
            case 'csv':
                return $this->export_to_csv($analysis_data);
            default:
                throw new Exception('Unsupported export format: ' . $format);
        }
    }
    
    /**
     * Export to PDF (placeholder)
     */
    private function export_to_pdf($analysis_data)
    {
        // Implement PDF export using TCPDF or similar
        $filename = 'ai_analysis_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = FCPATH . 'assets/exports/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        // For now, create a simple text file as placeholder
        file_put_contents($filepath, json_encode($analysis_data, JSON_PRETTY_PRINT));
        
        return array(
            'url' => base_url() . 'assets/exports/' . $filename,
            'filename' => $filename
        );
    }
    
    /**
     * Export to Excel (placeholder)
     */
    private function export_to_excel($analysis_data)
    {
        // Implement Excel export using PhpSpreadsheet or similar
        $filename = 'ai_analysis_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = FCPATH . 'assets/exports/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        // For now, create a simple text file as placeholder
        file_put_contents($filepath, json_encode($analysis_data, JSON_PRETTY_PRINT));
        
        return array(
            'url' => base_url() . 'assets/exports/' . $filename,
            'filename' => $filename
        );
    }
    
    /**
     * Export to CSV (placeholder)
     */
    private function export_to_csv($analysis_data)
    {
        $filename = 'ai_analysis_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = FCPATH . 'assets/exports/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        // For now, create a simple text file as placeholder
        file_put_contents($filepath, json_encode($analysis_data, JSON_PRETTY_PRINT));
        
        return array(
            'url' => base_url() . 'assets/exports/' . $filename,
            'filename' => $filename
        );
    }
}
