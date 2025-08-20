<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| AI Analytics Configuration
|--------------------------------------------------------------------------
|
| This file contains configuration settings for AI services used in
| the admissions analytics system.
|
*/

$config['ai_settings'] = array(
    
    /*
    |--------------------------------------------------------------------------
    | Default AI Service
    |--------------------------------------------------------------------------
    |
    | Specify which AI service to use by default. Options:
    | 'openai', 'google', 'anthropic', 'mock'
    |
    */
    'service' => 'mock', // Change to 'openai' when API key is available
    
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI GPT models
    |
    */
    'openai' => array(
        'api_key' => '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', // Add your OpenAI API key here
        'model' => 'gpt-4', // Options: gpt-4, gpt-3.5-turbo
        'max_tokens' => 2500,
        'temperature' => 0.7,
        'timeout' => 30
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Google AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google AI (Gemini) models
    |
    */
    'google' => array(
        'api_key' => '', // Add your Google AI API key here
        'model' => 'gemini-pro',
        'max_tokens' => 2048,
        'temperature' => 0.7,
        'timeout' => 30
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Anthropic Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Anthropic Claude models
    |
    */
    'anthropic' => array(
        'api_key' => '', // Add your Anthropic API key here
        'model' => 'claude-3-sonnet-20240229',
        'max_tokens' => 2048,
        'temperature' => 0.7,
        'timeout' => 30
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Analysis Types
    |--------------------------------------------------------------------------
    |
    | Define available analysis types and their descriptions
    |
    */
    'analysis_types' => array(
        'comprehensive' => array(
            'name' => 'Comprehensive Analysis',
            'description' => 'Complete analysis covering all aspects of admissions performance',
            'icon' => 'fa-chart-line'
        ),
        'conversion_optimization' => array(
            'name' => 'Conversion Optimization',
            'description' => 'Focus on improving conversion rates at each funnel stage',
            'icon' => 'fa-funnel-dollar'
        ),
        'program_analysis' => array(
            'name' => 'Program Performance',
            'description' => 'Analyze performance by academic program',
            'icon' => 'fa-graduation-cap'
        ),
        'temporal_analysis' => array(
            'name' => 'Timing Analysis',
            'description' => 'Analyze patterns over time and optimal timing strategies',
            'icon' => 'fa-clock'
        ),
        'competitive_analysis' => array(
            'name' => 'Competitive Insights',
            'description' => 'Market positioning and competitive opportunities',
            'icon' => 'fa-trophy'
        )
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for AI responses to reduce API calls
    |
    */
    'cache' => array(
        'enabled' => true,
        'ttl' => 3600, // Cache for 1 hour
        'prefix' => 'ai_analytics_'
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to prevent API abuse
    |
    */
    'rate_limit' => array(
        'enabled' => true,
        'max_requests_per_hour' => 50,
        'max_requests_per_day' => 200
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for exporting analysis results
    |
    */
    'export' => array(
        'formats' => array('pdf', 'excel', 'csv'),
        'max_file_size' => '10MB',
        'storage_path' => 'assets/exports/',
        'cleanup_after_days' => 7
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Prompt Templates
    |--------------------------------------------------------------------------
    |
    | Predefined prompt templates for different analysis types
    |
    */
    'prompts' => array(
        'system_context' => 'You are an expert education consultant specializing in admissions analytics and optimization for higher education institutions.',
        
        'analysis_instructions' => 'Analyze the provided admissions data and provide actionable insights. Structure your response with clear sections for executive summary, detailed analysis, key insights, recommendations, metrics to track, and implementation timeline.',
        
        'output_format' => array(
            'executive_summary' => 'Provide 2-3 key findings in bullet points',
            'detailed_analysis' => 'Break down analysis by conversion stages, programs, and timing',
            'key_insights' => 'List 5-7 key insights as bullet points',
            'recommendations' => 'Provide prioritized, actionable recommendations with expected impact',
            'metrics_to_track' => 'Suggest KPIs to monitor improvement',
            'implementation_timeline' => 'Categorize actions as short-term, medium-term, and long-term'
        )
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Benchmarks
    |--------------------------------------------------------------------------
    |
    | Industry benchmarks for comparison
    |
    */
    'benchmarks' => array(
        'conversion_rates' => array(
            'signup_to_paid' => array('good' => 75, 'average' => 60, 'poor' => 45),
            'paid_to_interviewed' => array('good' => 90, 'average' => 80, 'poor' => 70),
            'interviewed_to_reserved' => array('good' => 85, 'average' => 75, 'poor' => 65),
            'reserved_to_enrolled' => array('good' => 90, 'average' => 80, 'poor' => 70),
            'overall_conversion' => array('good' => 40, 'average' => 30, 'poor' => 20)
        ),
        'timing' => array(
            'peak_application_months' => array('March', 'April', 'May'),
            'optimal_follow_up_days' => 3,
            'max_decision_time_days' => 14
        )
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notifications for analysis completion
    |
    */
    'notifications' => array(
        'enabled' => true,
        'email_on_completion' => true,
        'email_on_error' => true,
        'admin_emails' => array(
            // Add admin email addresses here
        )
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security configurations for AI service integration
    |
    */
    'security' => array(
        'encrypt_api_keys' => true,
        'log_requests' => true,
        'sanitize_data' => true,
        'allowed_file_types' => array('pdf', 'xlsx', 'csv', 'txt'),
        'max_analysis_size' => '5MB'
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings for development and testing
    |
    */
    'development' => array(
        'debug_mode' => false,
        'log_ai_responses' => true,
        'use_mock_data' => true, // Set to false in production
        'test_mode' => false
    )
);

/*
|--------------------------------------------------------------------------
| Analysis Prompt Templates
|--------------------------------------------------------------------------
|
| Detailed prompt templates for different analysis scenarios
|
*/

$config['prompt_templates'] = array(
    
    'comprehensive' => "
        Conduct a comprehensive analysis of the admissions data focusing on:
        1. Overall funnel performance and conversion rates
        2. Program-specific performance variations
        3. Temporal patterns and seasonal trends
        4. Competitive positioning insights
        5. Strategic recommendations for improvement
        
        Provide specific, actionable recommendations with expected impact percentages.
    ",
    
    'conversion_optimization' => "
        Focus specifically on conversion rate optimization:
        1. Identify the biggest drop-off points in the funnel
        2. Analyze conversion rates at each stage
        3. Compare against industry benchmarks
        4. Suggest specific interventions to improve each stage
        5. Prioritize recommendations by potential impact
        
        Provide detailed conversion improvement strategies.
    ",
    
    'program_analysis' => "
        Analyze program-specific performance:
        1. Compare enrollment rates across different programs
        2. Identify high-performing and underperforming programs
        3. Suggest program-specific marketing strategies
        4. Recommend resource allocation adjustments
        5. Identify cross-selling opportunities
        
        Focus on program optimization strategies.
    ",
    
    'temporal_analysis' => "
        Analyze timing and seasonal patterns:
        1. Identify peak application and enrollment periods
        2. Analyze day-of-week and time-of-day patterns
        3. Suggest optimal timing for marketing campaigns
        4. Recommend follow-up timing strategies
        5. Identify seasonal opportunities and challenges
        
        Provide timing optimization recommendations.
    ",
    
    'competitive_analysis' => "
        Provide competitive and market insights:
        1. Assess market positioning based on conversion rates
        2. Identify competitive advantages and weaknesses
        3. Suggest differentiation strategies
        4. Recommend market expansion opportunities
        5. Analyze pricing and value proposition effectiveness
        
        Focus on competitive strategy recommendations.
    "
);

/*
|--------------------------------------------------------------------------
| Data Quality Thresholds
|--------------------------------------------------------------------------
|
| Define minimum data requirements for reliable analysis
|
*/

$config['data_quality'] = array(
    'minimum_applications' => 50,
    'minimum_time_period_days' => 30,
    'required_funnel_stages' => array('applications', 'paid', 'interviewed', 'enrolled'),
    'data_completeness_threshold' => 0.8 // 80% data completeness required
);
