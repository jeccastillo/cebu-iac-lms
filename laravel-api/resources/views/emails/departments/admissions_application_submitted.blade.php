<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Application Submitted - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #5a2d5a 0%, #9f4f96 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">ADMISSIONS DEPARTMENT NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #5a2d5a; margin-bottom: 20px; font-size: 24px;">🎓 New Application Submitted</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Admissions Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">A new application has been submitted to iACADEMY and requires your attention for initial processing and review.</p>
        
        <div style="background: #faf5ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #9f4f96;">
            <h3 style="color: #5a2d5a; margin-bottom: 15px; font-size: 18px;">Applicant Details:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Applicant Name:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $applicant_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Application Number:</td>
                    <td style="padding: 8px 0; color: #2d3748; font-family: monospace; font-weight: bold;">{{ $application_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Program Applied:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $program }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Campus:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $campus }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #9f4f96; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">NEW APPLICATION</span></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c53030; margin-bottom: 15px; font-size: 18px;">📋 Initial Processing Required:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Review application form completeness</li>
                <li style="margin-bottom: 8px;">Verify program eligibility requirements</li>
                <li style="margin-bottom: 8px;">Check submitted documents</li>
                <li style="margin-bottom: 8px;">Schedule interview if required</li>
                <li style="margin-bottom: 8px;">Send application fee payment instructions</li>
            </ul>
        </div>
        
        <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #0369a1; margin-bottom: 15px; font-size: 18px;">📚 Next Steps in Admissions Process:</h3>
            <div style="color: #2d3748; font-size: 14px;">
                <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #3b82f6;">
                    <strong>Step 1:</strong> Application Review & Document Verification
                </div>
                <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #6b7280;">
                    <strong>Step 2:</strong> Interview Scheduling (if applicable)
                </div>
                <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #6b7280;">
                    <strong>Step 3:</strong> Application Fee Payment
                </div>
                <div style="padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #6b7280;">
                    <strong>Step 4:</strong> Admission Decision & Notification
                </div>
            </div>
        </div>
        
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #047857; font-size: 14px;">
                <strong>Response Time:</strong> Please review this application within 2-3 business days to maintain our service quality standards and provide timely feedback to the applicant.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/admissionsV1/view_lead_new/{{ $application_number }}" style="background: #9f4f96; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; margin-right: 10px;">Review Application</a>
            <a href="{{ config('app.url') }}/admissionsV1/schedule_interview" style="background: #3182ce; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Schedule Interview</a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #718096; margin-bottom: 5px;">Best regards,</p>
        <p style="font-size: 14px; color: #718096; margin-bottom: 20px;"><strong>iACADEMY Student Information System</strong></p>
        
        <div style="background: #f7fafc; padding: 15px; border-radius: 6px; text-align: center;">
            <p style="font-size: 12px; color: #a0aec0; margin: 0;">
                This is an automated notification from the iACADEMY Student Management System.<br>
                For technical support, contact: <a href="mailto:tech@iacademy.edu.ph" style="color: #4299e1;">tech@iacademy.edu.ph</a>
            </p>
        </div>
    </div>
</body>
</html>
