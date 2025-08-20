# AI Admissions Analytics System

## Overview

The AI Admissions Analytics System is a comprehensive module that analyzes admissions data and provides AI-powered insights and recommendations to improve enrollment conversion rates and overall admissions performance.

## Features

- **AI-Powered Analysis**: Uses advanced AI models (OpenAI GPT, Google Gemini, Anthropic Claude) to analyze admissions data
- **Multiple Analysis Types**: 
  - Comprehensive Analysis
  - Conversion Optimization
  - Program Performance Analysis
  - Temporal/Timing Analysis
  - Competitive Insights
- **Real-time Metrics Dashboard**: Live tracking of admissions metrics
- **Historical Trends**: Compare performance across multiple terms
- **Actionable Recommendations**: Prioritized, specific recommendations with expected impact
- **Export Functionality**: Export analysis results to PDF, Excel, or CSV
- **Caching System**: Reduces API costs and improves performance
- **Rate Limiting**: Prevents API abuse and manages costs
- **User Feedback**: Collect feedback on analysis quality

## Installation

### 1. Database Setup

Run the SQL script to create the necessary database tables:

```sql
-- Execute the SQL file
source application/modules/ai_analytics/sql/create_ai_analysis_table.sql
```

Or manually run the SQL commands in your database management tool.

### 2. AI Service Configuration

Edit the configuration file to set up your AI service:

```php
// application/modules/ai_analytics/config/ai_config.php

$config['ai_settings'] = array(
    'service' => 'openai', // Change from 'mock' to your preferred service
    
    'openai' => array(
        'api_key' => 'your-openai-api-key-here',
        'model' => 'gpt-4',
        'max_tokens' => 2500,
        'temperature' => 0.7
    ),
    
    // Configure other services as needed
);
```

### 3. Directory Permissions

Ensure the exports directory has write permissions:

```bash
mkdir -p assets/exports
chmod 755 assets/exports
```

### 4. CodeIgniter Configuration

Add the module to your CodeIgniter modules configuration if using HMVC.

## Configuration Options

### AI Service Providers

The system supports multiple AI service providers:

- **OpenAI GPT**: Most comprehensive, requires API key
- **Google Gemini**: Alternative option, requires API key  
- **Anthropic Claude**: Another alternative, requires API key
- **Mock Service**: For development/testing without API costs

### Analysis Types

1. **Comprehensive Analysis**: Complete overview of all admissions metrics
2. **Conversion Optimization**: Focus on improving funnel conversion rates
3. **Program Analysis**: Program-specific performance insights
4. **Temporal Analysis**: Timing and seasonal pattern analysis
5. **Competitive Analysis**: Market positioning and competitive insights

### Rate Limiting

Configure rate limits to manage API costs:

```php
'rate_limit' => array(
    'enabled' => true,
    'max_requests_per_hour' => 50,
    'max_requests_per_day' => 200
)
```

### Caching

Enable caching to reduce API calls:

```php
'cache' => array(
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'prefix' => 'ai_analytics_'
)
```

## Usage

### Accessing the System

1. Navigate to the Admissions Report page
2. Click the "AI Analysis" button
3. Select your analysis parameters:
   - Academic Term
   - Analysis Type
   - Date Range (optional)
4. Click "Generate AI Analysis"

### Understanding Results

The AI analysis provides:

- **Executive Summary**: Key findings at a glance
- **Key Insights**: Bullet-pointed discoveries
- **Detailed Analysis**: In-depth breakdown
- **Recommendations**: Prioritized action items
- **Metrics to Track**: KPIs for monitoring improvement
- **Implementation Timeline**: Short, medium, and long-term actions

### Exporting Results

Export analysis results in multiple formats:
- **PDF**: Formatted report for presentations
- **Excel**: Structured data for further analysis
- **CSV**: Raw data for custom processing

## API Endpoints

### Generate Analysis
```
POST /ai_analytics/generate_analysis
Parameters: term, analysis_type, start_date, end_date
```

### Get Historical Trends
```
POST /ai_analytics/get_historical_trends
Parameters: current_term, terms_back
```

### Save Analysis
```
POST /ai_analytics/save_analysis
Parameters: analysis_data, term, analysis_type
```

### Export Analysis
```
POST /ai_analytics/export_analysis
Parameters: analysis_data, format
```

## Data Sources

The system analyzes data from:

- **Registration Data**: Student enrollment information
- **Application Funnel**: Signup → Payment → Interview → Reservation → Enrollment
- **Program Performance**: Enrollment by academic program
- **Temporal Patterns**: Time-based application and enrollment trends
- **External API**: Additional admissions statistics

## Security Features

- **API Key Encryption**: Secure storage of AI service credentials
- **Rate Limiting**: Prevents abuse and manages costs
- **Data Sanitization**: Cleans data before AI processing
- **Access Control**: Inherits existing user permission system
- **Audit Logging**: Tracks all AI analysis requests

## Troubleshooting

### Common Issues

1. **"AI Analysis failed" Error**
   - Check AI service configuration
   - Verify API key is valid
   - Ensure internet connectivity
   - Check rate limits

2. **"Insufficient Data" Warning**
   - Ensure minimum data requirements are met
   - Check date range selection
   - Verify database connectivity

3. **Slow Performance**
   - Enable caching
   - Reduce analysis scope
   - Check server resources

### Debug Mode

Enable debug mode for detailed error information:

```php
'development' => array(
    'debug_mode' => true,
    'log_ai_responses' => true
)
```

## Cost Management

### API Cost Optimization

1. **Enable Caching**: Reduces repeat API calls
2. **Set Rate Limits**: Prevents excessive usage
3. **Use Appropriate Models**: Balance cost vs. quality
4. **Monitor Usage**: Track API consumption

### Estimated Costs (OpenAI GPT-4)

- Comprehensive Analysis: ~$0.10-0.20 per analysis
- Quick Analysis: ~$0.05-0.10 per analysis
- Monthly Usage (50 analyses): ~$5-10

## Maintenance

### Regular Tasks

1. **Clean Old Cache**: Remove expired cache entries
2. **Archive Old Analyses**: Move old results to archive
3. **Monitor API Usage**: Track costs and performance
4. **Update Prompts**: Refine analysis prompts based on feedback

### Database Maintenance

```sql
-- Clean expired cache entries
DELETE FROM tb_mas_ai_cache WHERE expires_at < NOW();

-- Archive old analyses (older than 90 days)
UPDATE tb_mas_ai_analysis_results 
SET is_active = 0 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Support

For technical support or feature requests:

1. Check the troubleshooting section
2. Review configuration settings
3. Check system logs
4. Contact system administrator

## Version History

- **v1.0.0**: Initial release with core AI analysis features
- **v1.1.0**: Added historical trends and caching
- **v1.2.0**: Multiple AI service provider support
- **v1.3.0**: Enhanced export functionality and user feedback

## License

This module is part of the iACADEMY LMS system and follows the same licensing terms.
