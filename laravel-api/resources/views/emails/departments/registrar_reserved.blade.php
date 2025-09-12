<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Applicant Reserved - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">REGISTRAR NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1a365d; margin-bottom: 20px; font-size: 24px;">New Applicant Reserved</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Registrar Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">An applicant has successfully paid their reservation fee and is now ready for enlistment processing.</p>
        
        <div style="background: #f7fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4299e1;">
            <h3 style="color: #1a365d; margin-bottom: 15px; font-size: 18px;">Applicant Details:</h3>
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
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Payment Date:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $payment_date }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #48bb78; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">RESERVED</span></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c53030; margin-bottom: 15px; font-size: 18px;">📋 Action Required:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Review applicant's program eligibility</li>
                <li style="margin-bottom: 8px;">Prepare enlistment documentation</li>
                <li style="margin-bottom: 8px;">Schedule enlistment appointment if required</li>
                <li style="margin-bottom: 8px;">Update student information system</li>
            </ul>
        </div>
        
        <div style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #234e52; font-size: 14px;">
                <strong>Note:</strong> This applicant has completed the reservation payment and is ready to proceed to the enlistment phase. Please ensure all required documents are verified before processing.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/unity/student_viewer" style="background: #4299e1; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Applicant Details</a>
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
