-- AI Analytics Database Tables
-- Run this SQL to create the necessary tables for the AI Analytics system

-- Table for storing AI analysis results
CREATE TABLE IF NOT EXISTS `tb_mas_ai_analysis_results` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `term_id` int(11) NOT NULL,
  `analysis_type` varchar(50) NOT NULL,
  `analysis_data` longtext NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`intID`),
  KEY `idx_term_id` (`term_id`),
  KEY `idx_analysis_type` (`analysis_type`),
  KEY `idx_generated_by` (`generated_by`),
  KEY `idx_generated_at` (`generated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for tracking AI API usage and rate limiting
CREATE TABLE IF NOT EXISTS `tb_mas_ai_api_usage` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `api_service` varchar(50) NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `tokens_used` int(11) DEFAULT 0,
  `cost` decimal(10,4) DEFAULT 0.0000,
  `response_time_ms` int(11) DEFAULT 0,
  `status` enum('success','error','timeout') NOT NULL,
  `error_message` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`intID`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_api_service` (`api_service`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for caching AI responses
CREATE TABLE IF NOT EXISTS `tb_mas_ai_cache` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(255) NOT NULL,
  `cache_data` longtext NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`intID`),
  UNIQUE KEY `unique_cache_key` (`cache_key`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for AI analysis templates and prompts
CREATE TABLE IF NOT EXISTS `tb_mas_ai_prompts` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `analysis_type` varchar(50) NOT NULL,
  `prompt_template` longtext NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intID`),
  KEY `idx_analysis_type` (`analysis_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default prompt templates
INSERT INTO `tb_mas_ai_prompts` (`name`, `analysis_type`, `prompt_template`, `description`, `created_by`) VALUES
('Comprehensive Analysis', 'comprehensive', 'Conduct a comprehensive analysis of the admissions data focusing on: 1. Overall funnel performance and conversion rates 2. Program-specific performance variations 3. Temporal patterns and seasonal trends 4. Competitive positioning insights 5. Strategic recommendations for improvement. Provide specific, actionable recommendations with expected impact percentages.', 'Complete analysis covering all aspects of admissions performance', 1),
('Conversion Optimization', 'conversion_optimization', 'Focus specifically on conversion rate optimization: 1. Identify the biggest drop-off points in the funnel 2. Analyze conversion rates at each stage 3. Compare against industry benchmarks 4. Suggest specific interventions to improve each stage 5. Prioritize recommendations by potential impact. Provide detailed conversion improvement strategies.', 'Focus on improving conversion rates at each funnel stage', 1),
('Program Analysis', 'program_analysis', 'Analyze program-specific performance: 1. Compare enrollment rates across different programs 2. Identify high-performing and underperforming programs 3. Suggest program-specific marketing strategies 4. Recommend resource allocation adjustments 5. Identify cross-selling opportunities. Focus on program optimization strategies.', 'Analyze performance by academic program', 1),
('Temporal Analysis', 'temporal_analysis', 'Analyze timing and seasonal patterns: 1. Identify peak application and enrollment periods 2. Analyze day-of-week and time-of-day patterns 3. Suggest optimal timing for marketing campaigns 4. Recommend follow-up timing strategies 5. Identify seasonal opportunities and challenges. Provide timing optimization recommendations.', 'Analyze patterns over time and optimal timing strategies', 1),
('Competitive Analysis', 'competitive_analysis', 'Provide competitive and market insights: 1. Assess market positioning based on conversion rates 2. Identify competitive advantages and weaknesses 3. Suggest differentiation strategies 4. Recommend market expansion opportunities 5. Analyze pricing and value proposition effectiveness. Focus on competitive strategy recommendations.', 'Market positioning and competitive opportunities', 1);

-- Table for AI analysis feedback and ratings
CREATE TABLE IF NOT EXISTS `tb_mas_ai_feedback` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `analysis_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `feedback_text` text,
  `usefulness_score` tinyint(1) DEFAULT NULL CHECK (`usefulness_score` >= 1 AND `usefulness_score` <= 5),
  `accuracy_score` tinyint(1) DEFAULT NULL CHECK (`accuracy_score` >= 1 AND `accuracy_score` <= 5),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`intID`),
  KEY `idx_analysis_id` (`analysis_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_rating` (`rating`),
  FOREIGN KEY (`analysis_id`) REFERENCES `tb_mas_ai_analysis_results` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for AI system configuration
CREATE TABLE IF NOT EXISTS `tb_mas_ai_config` (
  `intID` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` longtext NOT NULL,
  `config_type` enum('string','integer','boolean','json','encrypted') NOT NULL DEFAULT 'string',
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intID`),
  UNIQUE KEY `unique_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration values
INSERT INTO `tb_mas_ai_config` (`config_key`, `config_value`, `config_type`, `description`, `updated_by`) VALUES
('ai_service_provider', 'mock', 'string', 'Default AI service provider (openai, google, anthropic, mock)', 1),
('max_requests_per_hour', '50', 'integer', 'Maximum AI requests per user per hour', 1),
('max_requests_per_day', '200', 'integer', 'Maximum AI requests per user per day', 1),
('cache_enabled', 'true', 'boolean', 'Enable caching of AI responses', 1),
('cache_ttl_hours', '1', 'integer', 'Cache time-to-live in hours', 1),
('analysis_retention_days', '90', 'integer', 'Number of days to retain analysis results', 1),
('enable_feedback', 'true', 'boolean', 'Enable user feedback collection', 1),
('min_data_points', '50', 'integer', 'Minimum data points required for analysis', 1);

-- Create indexes for better performance
CREATE INDEX idx_ai_analysis_term_type ON tb_mas_ai_analysis_results(term_id, analysis_type);
CREATE INDEX idx_ai_usage_user_date ON tb_mas_ai_api_usage(user_id, DATE(created_at));
CREATE INDEX idx_ai_cache_expires ON tb_mas_ai_cache(expires_at);

-- Create a view for analysis summary
CREATE OR REPLACE VIEW vw_ai_analysis_summary AS
SELECT 
    ar.intID,
    ar.term_id,
    ar.analysis_type,
    ar.generated_by,
    ar.generated_at,
    sy.enumSem,
    sy.strYearStart,
    sy.strYearEnd,
    sy.term_label,
    f.strFirstname,
    f.strLastname,
    COALESCE(AVG(fb.rating), 0) as avg_rating,
    COUNT(fb.intID) as feedback_count
FROM tb_mas_ai_analysis_results ar
LEFT JOIN tb_mas_sy sy ON ar.term_id = sy.intID
LEFT JOIN tb_mas_faculty f ON ar.generated_by = f.intID
LEFT JOIN tb_mas_ai_feedback fb ON ar.intID = fb.analysis_id
WHERE ar.is_active = 1
GROUP BY ar.intID, ar.term_id, ar.analysis_type, ar.generated_by, ar.generated_at, 
         sy.enumSem, sy.strYearStart, sy.strYearEnd, sy.term_label, f.strFirstname, f.strLastname;

-- Add comments to tables
ALTER TABLE tb_mas_ai_analysis_results COMMENT = 'Stores AI-generated analysis results for admissions data';
ALTER TABLE tb_mas_ai_api_usage COMMENT = 'Tracks API usage for rate limiting and cost monitoring';
ALTER TABLE tb_mas_ai_cache COMMENT = 'Caches AI responses to reduce API calls and improve performance';
ALTER TABLE tb_mas_ai_prompts COMMENT = 'Stores prompt templates for different analysis types';
ALTER TABLE tb_mas_ai_feedback COMMENT = 'Collects user feedback on AI analysis quality';
ALTER TABLE tb_mas_ai_config COMMENT = 'System configuration for AI analytics module';
