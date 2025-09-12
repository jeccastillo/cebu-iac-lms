<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Enlisted - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #2d5a27 0%, #38a169 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">FINANCE DEPARTMENT NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #2d5a27; margin-bottom: 20px; font-size: 24px;">Applicant Enlisted - Ready to Enroll</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Finance Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">An applicant has been successfully enlisted and is now ready to proceed with enrollment and tuition payment processing.</p>
        
        <div style="background: #f0fff4; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #38a169;">
            <h3 style="color: #2d5a27; margin-bottom: 15px; font-size: 18px;">Applicant Details:</h3>
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
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Program:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $program }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #38a169; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">ENLISTED</span></td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fffaf0; border: 1px solid #fbd38d; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c05621; margin-bottom: 15px; font-size: 18px;">💰 Finance Action Required:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Generate tuition fee assessment</li>
                <li style="margin-bottom: 8px;">Set up payment plan options</li>
                <li style="margin-bottom: 8px;">Process enrollment payment</li>
                <li style="margin-bottom: 8px;">Update financial records system</li>
                <li style="margin-bottom: 8px;">Send payment instructions to student</li>
            </ul>
        </div>
        
        <div style="background: #f7fafc; border: 1px solid #cbd5e0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #2d5a27; margin-bottom: 15px; font-size: 18px;">📊 Payment Processing Notes:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748; font-size: 14px;">
                <li style="margin-bottom: 8px;"><strong>Reservation Fee:</strong> Already paid and processed</li>
                <li style="margin-bottom: 8px;"><strong>Tuition Balance:</strong> Awaiting assessment and payment</li>
                <li style="margin-bottom: 8px;"><strong>Payment Options:</strong> Full payment or installment plans available</li>
                <li style="margin-bottom: 8px;"><strong>Deadline:</strong> Payment required before enrollment confirmation</li>
            </ul>
        </div>
        
        <div style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #234e52; font-size: 14px;">
                <strong>Priority Notice:</strong> This student has completed all academic requirements and is ready for financial enrollment. Timely processing ensures smooth transition to enrolled status.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/finance/student-ledger" style="background: #38a169; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; margin-right: 10px;">View Financial Record</a>
            <a href="{{ config('app.url') }}/finance/payment-assessment" style="background: #3182ce; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Generate Assessment</a>
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
