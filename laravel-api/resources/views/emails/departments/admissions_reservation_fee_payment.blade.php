<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Fee Payment - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">ADMISSIONS DEPARTMENT NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1e40af; margin-bottom: 20px; font-size: 24px;">🎯 Reservation Fee Payment Received</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Admissions Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">An admitted applicant has successfully paid their reservation fee, securing their slot for the upcoming academic term. The student is now ready to proceed with the enrollment process.</p>
        
        <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1e40af; margin-bottom: 15px; font-size: 18px;">Student Details:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Student Name:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $applicant_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Application Number:</td>
                    <td style="padding: 8px 0; color: #2d3748; font-family: monospace; font-weight: bold;">{{ $application_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #7c3aed; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">RESERVATION FEE PAID</span></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Program:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $program ?? 'To be assigned' }}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #166534; margin-bottom: 15px; font-size: 18px;">💰 Reservation Payment Information:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; font-weight: bold; color: #4a5568;">Amount Paid:</td>
                    <td style="padding: 5px 0; color: #166534; font-weight: bold;">{{ $payment_amount ?? 'As per program requirement' }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-weight: bold; color: #4a5568;">Payment Date:</td>
                    <td style="padding: 5px 0; color: #2d3748;">{{ $payment_date ?? date('Y-m-d H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-weight: bold; color: #4a5568;">Receipt Number:</td>
                    <td style="padding: 5px 0; color: #2d3748; font-family: monospace;">{{ $receipt_number ?? 'Auto-generated' }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-weight: bold; color: #4a5568;">Academic Term:</td>
                    <td style="padding: 5px 0; color: #2d3748;">{{ $academic_term ?? 'Current Term' }}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #166534; margin-bottom: 15px; font-size: 18px;">🎉 Slot Secured - Ready for Enrollment!</h3>
            <div style="color: #2d3748; font-size: 14px;">
                <div style="margin-bottom: 8px;">✅ Admission confirmed</div>
                <div style="margin-bottom: 8px;">✅ Reservation fee payment verified</div>
                <div style="margin-bottom: 8px;">✅ Slot secured for academic term</div>
                <div style="margin-bottom: 8px;">✅ Ready to proceed with enrollment</div>
            </div>
        </div>
        
        <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c2410c; margin-bottom: 15px; font-size: 18px;">📋 Enrollment Transition Actions:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Generate student ID number</li>
                <li style="margin-bottom: 8px;">Create academic records entry</li>
                <li style="margin-bottom: 8px;">Assign to appropriate program section</li>
                <li style="margin-bottom: 8px;">Send enrollment instructions to student</li>
                <li style="margin-bottom: 8px;">Coordinate with Registrar for class scheduling</li>
                <li style="margin-bottom: 8px;">Forward to Finance for tuition assessment</li>
            </ul>
        </div>
        
        <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>🚀 Transition to Enrollment:</strong> This student has secured their slot and is ready to transition from applicant to enrolled student status. Please coordinate with relevant departments.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/admissionsV1/view_lead_new/{{ $application_number }}" style="background: #1e40af; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; margin-right: 10px;">View Application</a>
            <a href="{{ config('app.url') }}/enrollment/process/{{ $application_number }}" style="background: #7c3aed; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Start Enrollment</a>
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
