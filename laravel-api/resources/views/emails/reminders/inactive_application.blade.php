<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>iACADEMY Application Reminder</title>
</head>
<body>
    <div style="width:100%;margin:0 auto;display:block;">
        <div style="width:800px;display:block;margin:0 auto;">
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="iACADEMY Header">
            <div style="padding:20px;font-family:verdana;font-size:14px;">
                <p style="margin-bottom:40px;">Dear {{ $name }},</p>
                
                <div style="background-color:#fff3cd;border:1px solid #ffeaa7;padding:25px;margin:20px 0;border-radius:8px;text-align:center;">
                    <h2 style="color:#856404;margin:0 0 15px 0;">⏰ Application Reminder</h2>
                    <p style="margin:0;font-size:16px;color:#856404;">Your application has been inactive for {{ $days_inactive }} days</p>
                </div>
                
                <p>We noticed that your iACADEMY application hasn't had any recent activity. We want to make sure you don't miss your opportunity to join our community!</p>
                
                <div style="background-color:#f8f9fa;border-left:4px solid #007bff;padding:15px;margin:20px 0;">
                    <p style="margin:0;"><strong>Application Details:</strong></p>
                    <p style="margin:5px 0;">Application Number: <strong>{{ $application_number }}</strong></p>
                    <p style="margin:5px 0;">Current Status: <strong>{{ $status }}</strong></p>
                </div>
                
                <div style="background-color:#e7f3ff;border:1px solid #b3d7ff;padding:20px;margin:20px 0;border-radius:5px;">
                    <h3 style="color:#0066cc;margin:0 0 15px 0;">Next Steps:</h3>
                    <ul style="margin:0;padding-left:20px;">
                        @if($status == 'New')
                        <li>Complete your application requirements</li>
                        <li>Submit all required documents</li>
                        <li>Pay the application fee</li>
                        @elseif($status == 'For Interview')
                        <li>Schedule your interview appointment</li>
                        <li>Prepare for your interview</li>
                        @elseif($status == 'For Requirements')
                        <li>Submit any missing requirements</li>
                        <li>Ensure all documents are complete</li>
                        @endif
                    </ul>
                </div>
                
                <div style="text-align:center;margin:30px 0;">
                    <a href="#" style="background-color:#007bff;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;display:inline-block;">Continue Application</a>
                </div>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our admissions team.</p>
                
                <div style="background-color:#e7f3ff;border:1px solid #b8daff;padding:15px;margin:20px 0;border-radius:5px;">
                    <p style="margin:0;">
                        <strong>Questions or Need Assistance?</strong><br>
                        Our Admissions team is here to help! Contact us at:
                        <br>📧 <a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a>
                        <br>📞 (032) 260-8888
                    </p>
                </div>
                
                <p style="margin-top:40px;">
                    Best regards,<br>
                    <strong>iACADEMY Admissions Team</strong><br>
                    <strong><a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a></strong><br>
                    <strong>Phone: (032) 260-8888</strong>
                </p>
            </div>
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="iACADEMY Footer">
        </div>
    </div>
</body>
</html>
