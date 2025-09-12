<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"="width=device-width, initial-scale=1.0">
    <title>Applicant Successfully Enrolled - iACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">iACADEMY</h1>
        <p style="color: #e2e8f0; margin: 10px 0 0 0; font-size: 16px;">ADMISSIONS DEPARTMENT NOTIFICATION</p>
    </div>
    
    <div style="background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1e40af; margin-bottom: 20px; font-size: 24px;">🎓 Applicant Successfully Enrolled!</h2>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Dear Admissions Team,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Congratulations! An applicant has successfully completed the entire admissions and enrollment process. They are now officially enrolled as a student at iACADEMY.</p>
        
        <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1e40af; margin-bottom: 15px; font-size: 18px;">New Student Details:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Student Name:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $applicant_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Original Application #:</td>
                    <td style="padding: 8px 0; color: #2d3748; font-family: monospace; font-weight: bold;">{{ $application_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Student ID:</td>
                    <td style="padding: 8px 0; color: #16a34a; font-family: monospace; font-weight: bold;">{{ $student_id ?? 'Generated' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Status:</td>
                    <td style="padding: 8px 0;"><span style="background: #16a34a; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">ENROLLED</span></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Program:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $program ?? 'Program Information' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #4a5568;">Academic Year:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $academic_year ?? 'Current Academic Year' }}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #166534; margin-bottom: 15px; font-size: 18px;">🎉 Enrollment Journey Complete!</h3>
            <div style="color: #2d3748; font-size: 14px;">
                <div style="margin-bottom: 8px;">✅ Application submitted and reviewed</div>
                <div style="margin-bottom: 8px;">✅ All requirements submitted and verified</div>
                <div style="margin-bottom: 8px;">✅ Application fee payment processed</div>
                <div style="margin-bottom: 8px;">✅ Admission decision made and communicated</div>
                <div style="margin-bottom: 8px;">✅ Reservation fee payment confirmed</div>
                <div style="margin-bottom: 8px;">✅ Enrollment process completed successfully</div>
            </div>
        </div>
        
        <div style="background: #f0f9ff; border: 1px solid #7dd3fc; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #0c4a6e; margin-bottom: 15px; font-size: 18px;">📊 Enrollment Statistics Update:</h3>
            <div style="color: #2d3748; font-size: 14px;">
                <div style="margin-bottom: 8px;">📈 Another successful admission and enrollment</div>
                <div style="margin-bottom: 8px;">🎯 Journey from application to enrollment completed</div>
                <div style="margin-bottom: 8px;">⏱️ Process duration: From {{ $application_date ?? 'application date' }} to enrollment</div>
                <div style="margin-bottom: 8px;">📋 All department workflows successfully executed</div>
            </div>
        </div>
        
        <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #c2410c; margin-bottom: 15px; font-size: 18px;">📋 Post-Enrollment Actions:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
                <li style="margin-bottom: 8px;">Archive application documents</li>
                <li style="margin-bottom: 8px;">Update enrollment statistics and reports</li>
                <li style="margin-bottom: 8px;">Send welcome package to new student</li>
                <li style="margin-bottom: 8px;">Coordinate with Student Services for orientation</li>
                <li style="margin-bottom: 8px;">Ensure proper transition to academic records</li>
                <li style="margin-bottom: 8px;">Update recruitment and admissions analytics</li>
            </ul>
        </div>
        
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #166534; font-size: 14px;">
                <strong>🎊 Success Story:</strong> This represents a successful completion of our admissions process. The student journey from initial application to enrollment has been completed successfully!
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}/admissionsV1/view_lead_new/{{ $application_number }}" style="background: #1e40af; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; margin-right: 10px;">View Application</a>
            <a href="{{ config('app.url') }}/students/profile/{{ $student_id }}" style="background: #16a34a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Student Profile</a>
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
