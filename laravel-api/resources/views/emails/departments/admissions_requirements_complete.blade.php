<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Requirements Submitted - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">ADMISSIONS DEPARTMENT NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1e40af; margin-bottom: 20px; font-size: 24px;">✅ All Requirements Submitted</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Admissions Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">An applicant has completed and submitted all required documents. The application is now ready for final review and admission decision.</p>
        
        <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1e40af; margin-bottom: 15px; font-size: 18px;">Applicant Details:</h3>
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
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #16a34a; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">REQUIREMENTS COMPLETE</span></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #166534; margin-bottom: 15px; font-size: 18px;">✅ Completed Requirements Checklist:</h3>
            <div style="color: #2d3748; font-size: 14px;">
                <div style="margin-bottom: 8px;">✅ Application Form</div>
                <div style="margin-bottom: 8px;">✅ Academic Transcripts</div>
                <div style="margin-bottom: 8px;">✅ Identification Documents</div>
                <div style="margin-bottom: 8px;">✅ Recommendation Letters (if required)</div>
                <div style="margin-bottom: 8px;">✅ Medical Certificate (if required)</div>
                <div style="margin-bottom: 8px;">✅ Additional Program-Specific Requirements</div>
            </div>
        </div>
        
        <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c2410c; margin-bottom: 15px; font-size: 18px;">🎯 Final Review Required:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Conduct comprehensive document verification</li>
                <li style="margin-bottom: 8px;">Review academic qualifications against program requirements</li>
                <li style="margin-bottom: 8px;">Process interview results (if applicable)</li>
                <li style="margin-bottom: 8px;">Make admission decision</li>
                <li style="margin-bottom: 8px;">Prepare admission notification letter</li>
            </ul>
        </div>
        
        <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>⏰ Priority Review:</strong> This application is complete and ready for final admission decision. Please review within 3-5 business days to maintain our commitment to timely processing.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/admissionsV1/view_lead_new/{{ $application_number }}" style="background: #1e40af; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; margin-right: 10px;">Review Application</a>
            <a href="{{ config('app.url') }}/admissionsV1/update_requirements" style="background: #059669; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Documents</a>
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
