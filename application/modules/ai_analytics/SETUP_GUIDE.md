# AI Analytics System - Quick Setup Guide

## 🚀 Quick Start (5 Minutes)

### Step 1: Database Setup
Run the SQL script to create the required tables:

```sql
-- In your MySQL/phpMyAdmin, execute:
source application/modules/ai_analytics/sql/create_ai_analysis_table.sql
```

### Step 2: Configure AI Service
Edit the configuration file:

```php
// File: application/modules/ai_analytics/config/ai_config.php
// Line 15: Change 'service' from 'mock' to your preferred AI service

$config['ai_settings'] = array(
    'service' => 'openai', // Change this line
    
    'openai' => array(
        'api_key' => 'sk-your-openai-api-key-here', // Add your API key
        'model' => 'gpt-4',
        'max_tokens' => 2500,
        'temperature' => 0.7
    ),
);
```

### Step 3: Create Export Directory
```bash
mkdir -p assets/exports
chmod 755 assets/exports
```

### Step 4: Test the System
1. Go to: `your-domain.com/admissionsV1/admissions_report`
2. Click the green "AI Analysis" button
3. Select analysis parameters and click "Generate AI Analysis"

## 🔧 Configuration Options

### For Development/Testing (No API Key Required)
Keep the service as 'mock' to test without API costs:
```php
'service' => 'mock', // Uses sample responses
```

### For Production (API Key Required)
Set up your preferred AI service:

#### OpenAI Setup
1. Get API key from: https://platform.openai.com/api-keys
2. Update config:
```php
'service' => 'openai',
'openai' => array(
    'api_key' => 'sk-your-key-here',
    'model' => 'gpt-4', // or 'gpt-3.5-turbo' for lower cost
)
```

#### Google AI Setup
1. Get API key from: https://makersuite.google.com/app/apikey
2. Update config:
```php
'service' => 'google',
'google' => array(
    'api_key' => 'your-google-ai-key-here',
    'model' => 'gemini-pro',
)
```

## 📊 Features Overview

### Analysis Types Available
- **Comprehensive**: Complete overview of all metrics
- **Conversion Optimization**: Focus on improving funnel rates
- **Program Analysis**: Performance by academic program
- **Temporal Analysis**: Timing and seasonal patterns
- **Competitive Analysis**: Market positioning insights

### Dashboard Features
- Real-time admissions metrics
- Historical trend analysis with charts
- Export to PDF, Excel, CSV
- Save and retrieve past analyses
- Priority-based recommendations

## 🛠 Troubleshooting

### Common Issues

**"AI Analysis failed" Error**
- Check if API key is correctly set
- Verify internet connection
- Ensure you haven't exceeded rate limits

**"Insufficient permissions" Error**
- Check that exports directory exists and is writable
- Verify user has access to admissions module

**Slow Performance**
- Enable caching in config (should be enabled by default)
- Consider using gpt-3.5-turbo instead of gpt-4 for faster responses

### Debug Mode
Enable detailed error logging:
```php
'development' => array(
    'debug_mode' => true,
    'log_ai_responses' => true
)
```

## 💰 Cost Management

### Estimated API Costs (OpenAI)
- **Mock Mode**: $0 (for testing)
- **GPT-3.5-turbo**: ~$0.02-0.05 per analysis
- **GPT-4**: ~$0.10-0.20 per analysis

### Cost Optimization Tips
1. Use caching (enabled by default)
2. Set appropriate rate limits
3. Start with gpt-3.5-turbo for testing
4. Monitor usage in the dashboard

## 🔒 Security Notes

- API keys are stored in config files (keep secure)
- Rate limiting prevents abuse
- User access follows existing LMS permissions
- Data is sanitized before AI processing

## 📞 Support

If you encounter issues:
1. Check this setup guide
2. Review the main README.md
3. Check system logs
4. Verify configuration settings

## 🎯 Next Steps After Setup

1. **Test with Sample Data**: Run a few analyses to verify everything works
2. **Train Users**: Show admissions staff how to use the system
3. **Customize Prompts**: Refine AI prompts based on your specific needs
4. **Set Up Monitoring**: Track API usage and costs
5. **Schedule Regular Analysis**: Set up routine analysis schedules

---

## Quick Checklist ✅

- [ ] Database tables created
- [ ] AI service configured (API key added)
- [ ] Export directory created with proper permissions
- [ ] System tested with at least one analysis
- [ ] Users trained on basic functionality
- [ ] Monitoring set up for API usage

**Congratulations! Your AI Analytics System is ready to use! 🎉**
