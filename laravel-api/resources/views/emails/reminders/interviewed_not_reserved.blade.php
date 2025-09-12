<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>iACADEMY Reservation Reminder</title>
</head>
<body>
    <div style="width:100%;margin:0 auto;display:block;">
        <div style="width:800px;display:block;margin:0 auto;">
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="iACADEMY Header">
            <div style="padding:20px;font-family:verdana;font-size:14px;">
                <p style="margin-bottom:40px;">Dear {{ $name }},</p>
                
                <div style="background-color:#d1ecf1;border:1px solid #bee5eb;padding:25px;margin:20px 0;border-radius:8px;text-align:center;">
                    <h2 style="color:#0c5460;margin:0 0 15px 0;">🎉 Congratulations! Reserve Your Slot Now</h2>
                    <p style="margin:0;font-size:16px;color:#0c5460;">You passed your interview {{ $days_since_interview }} days ago</p>
                </div>
                
                <p>Congratulations on successfully passing your interview for <strong>{{ $program }}</strong>! We're excited to welcome you to the iACADEMY family.</p>
                
                <div style="background-color:#f8d7da;border:1px solid #f5c6cb;padding:20px;margin:20px 0;border-radius:5px;">
                    <h3 style="color:#721c24;margin:0 0 15px 0;">⚠️ Action Required</h3>
                    <p style="margin:0;color:#721c24;">To secure your slot, you need to pay the reservation fee. Don't let this opportunity slip away!</p>
                </div>
                
                <div style="background-color:#f8f9fa;border-left:4px solid #28a745;padding:15px;margin:20px 0;">
                    <p style="margin:0;"><strong>Application Details:</strong></p>
                    <p style="margin:5px 0;">Application Number: <strong>{{ $application_number }}</strong></p>
                    <p style="margin:5px 0;">Interview Date: <strong>{{ \Carbon\Carbon::parse($interview_date)->format('F j, Y') }}</strong></p>
                    <p style="margin:5px 0;">Program: <strong>{{ $program }}</strong></p>
                </div>
                
                <div style="background-color:#e7f3ff;border:1px solid #b3d7ff;padding:20px;margin:20px 0;border-radius:5px;">
                    <h3 style="color:#0066cc;margin:0 0 15px 0;">Next Steps to Secure Your Slot:</h3>
                    <ul style="margin:0;padding-left:20px;">
                        <li>Log in to your application portal</li>
                        <li>Proceed to payment section</li>
                        <li>Pay the reservation fee</li>
                        <li>Upload payment receipt</li>
                        <li>Wait for confirmation</li>
                    </ul>
                </div>
                
                <div style="text-align:center;margin:30px 0;">
                    <a href="#" style="background-color:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;display:inline-block;">Pay Reservation Fee</a>
                </div>
                
                <div style="background-color:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:20px 0;border-radius:5px;">
                    <p style="margin:0;"><strong>💡 Important:</strong> Slots are limited and available on a first-come, first-served basis. Reserve your slot today to guarantee your place at iACADEMY!</p>
                </div>
                
                <p>If you have any questions about the reservation process or payment options, please contact our admissions team immediately.</p>
                
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
