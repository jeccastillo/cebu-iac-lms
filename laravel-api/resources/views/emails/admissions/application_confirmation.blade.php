<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>iACADEMY Application Confirmation</title>
</head>
<body>
    <div style="width:100%;margin:0 auto;display:block;">
        <div style="width:800px;display:block;margin:0 auto;">
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="iACADEMY Header">
            <div style="padding:20px;font-family:verdana;font-size:14px;">
                <p style="margin-bottom:40px;">Dear {{ $applicant_name }},</p>
                
                <p>
                    <strong>Congratulations!</strong> Your application has been successfully submitted to iACADEMY.
                </p>
                
                <p>
                    Your application details:
                </p>
                
                <div style="background-color:#f5f5f5;padding:20px;margin:20px 0;border-radius:5px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="padding:8px;font-weight:bold;border-bottom:1px solid #ddd;">Application Number:</td>
                            <td style="padding:8px;border-bottom:1px solid #ddd;">{{ $application_number }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px;font-weight:bold;border-bottom:1px solid #ddd;">Email Address:</td>
                            <td style="padding:8px;border-bottom:1px solid #ddd;">{{ $applicant_email }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px;font-weight:bold;border-bottom:1px solid #ddd;">Generated Username:</td>
                            <td style="padding:8px;border-bottom:1px solid #ddd;"><strong>{{ $username }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;font-weight:bold;">Confirmation Code:</td>
                            <td style="padding:8px;"><strong style="color:#007bff;">{{ $confirmation_code }}</strong></td>
                        </tr>
                    </table>
                </div>
                
                <div style="background-color:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:20px 0;border-radius:5px;">
                    <h4 style="margin-top:0;color:#856404;">Important Instructions:</h4>
                    <ul style="margin:10px 0;padding-left:20px;">
                        <li>Keep your username and confirmation code safe for future reference</li>
                        <li>You will need these credentials to access your application status</li>
                        <li>Our Admissions team will review your application and contact you for the next steps</li>
                        <li>You will receive an email notification once your interview schedule is confirmed</li>
                    </ul>
                </div>
                
                <p>
                    <strong>What's Next?</strong><br>
                    Our Admissions team will evaluate your application and supporting documents. You will receive an email notification when:
                </p>
                
                <ul>
                    <li>Your interview schedule has been set</li>
                    <li>Additional requirements are needed</li>
                    <li>Your application status changes</li>
                </ul>
                
                <p>
                    For any questions or concerns, please don't hesitate to contact our Admissions team at 
                    <a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a>
                </p>
                
                <p>
                    We look forward to having you as part of the iACADEMY community!
                </p>
                
                <br>
                <p>Sincerely,</p>
                <strong>iACADEMY Admissions Team</strong><br>
                <strong><a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a></strong>
            </div>
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="iACADEMY Footer">
        </div>
    </div>
</body>
</html>
