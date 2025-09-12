<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>iACADEMY Interview Result - Congratulations!</title>
</head>
<body>
    <div style="width:100%;margin:0 auto;display:block;">
        <div style="width:800px;display:block;margin:0 auto;">
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="iACADEMY Header">
            <div style="padding:20px;font-family:verdana;font-size:14px;">
                <p style="margin-bottom:40px;">Dear {{ $applicant_name }},</p>
                
                <div style="background-color:#d4edda;border:1px solid #c3e6cb;padding:25px;margin:20px 0;border-radius:8px;text-align:center;">
                    <h2 style="margin-top:0;color:#155724;font-size:28px;">🎉 Congratulations! 🎉</h2>
                    <p style="font-size:18px;color:#155724;margin:15px 0;">
                        <strong>You have successfully passed your interview!</strong>
                    </p>
                </div>
                
                <p>
                    We are thrilled to inform you that you have successfully passed your admission interview for iACADEMY. 
                    Our interview panel was impressed with your responses and believes you have the potential to excel in your chosen program.
                </p>
                
                @if($remarks)
                <div style="background-color:#f8f9fa;border-left:4px solid #28a745;padding:15px;margin:20px 0;">
                    <h4 style="margin-top:0;color:#155724;">Interview Feedback:</h4>
                    <p style="margin:0;font-style:italic;">"{{ $remarks }}"</p>
                </div>
                @endif
                
                <div style="background-color:#fff3cd;border:1px solid #ffeaa7;padding:20px;margin:20px 0;border-radius:5px;">
                    <h4 style="margin-top:0;color:#856404;">Next Steps:</h4>
                    <ol style="margin:10px 0;padding-left:20px;">
                        <li><strong>Await Enrollment Information:</strong> You will receive detailed enrollment instructions and requirements within the next 3-5 business days</li>
                        <li><strong>Complete Required Documents:</strong> Prepare all necessary documents for enrollment as specified in your enrollment packet</li>
                        <li><strong>Reserve Your Slot:</strong> Follow the enrollment process to secure your spot in your chosen program</li>
                        <li><strong>Attend Orientation:</strong> Join our new student orientation program to familiarize yourself with campus life and academic expectations</li>
                    </ol>
                </div>
                
                <p>
                    <strong>Important Reminders:</strong>
                </p>
                <ul>
                    <li>Keep this email for your records</li>
                    <li>Ensure all contact information is up to date</li>
                    <li>Complete enrollment requirements within the specified timeframe</li>
                    <li>Contact us immediately if you have any questions or concerns</li>
                </ul>
                
                <p>
                    We are excited to welcome you to the iACADEMY family! Our programs are designed to prepare you for success in your chosen career, 
                    and we look forward to supporting your academic journey.
                </p>
                
                <div style="background-color:#e7f3ff;border:1px solid #b8daff;padding:15px;margin:20px 0;border-radius:5px;">
                    <p style="margin:0;">
                        <strong>Questions or Need Assistance?</strong><br>
                        Our Admissions team is here to help! Contact us at:
                        <br>📧 <a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a>
                        <br>📞 (032) 260-8888
                    </p>
                </div>
                
                <p>
                    Once again, congratulations on this achievement! We can't wait to see you thrive at iACADEMY.
                </p>
                
                <br>
                <p>Warmest congratulations,</p>
                <strong>iACADEMY Admissions Team</strong><br>
                <strong><a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a></strong><br>
                <strong>Phone: (032) 260-8888</strong>
            </div>
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="iACADEMY Footer">
        </div>
    </div>
</body>
</html>
