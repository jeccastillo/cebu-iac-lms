<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>iACADEMY Interview Result</title>
</head>
<body>
    <div style="width:100%;margin:0 auto;display:block;">
        <div style="width:800px;display:block;margin:0 auto;">
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="iACADEMY Header">
            <div style="padding:20px;font-family:verdana;font-size:14px;">
                <p style="margin-bottom:40px;">Dear {{ $applicant_name }},</p>
                
                <p>
                    Thank you for taking the time to interview with us for admission to iACADEMY. 
                    We appreciate your interest in our programs and the effort you put into the application process.
                </p>
                
                <div style="background-color:#f8d7da;border:1px solid #f5c6cb;padding:20px;margin:20px 0;border-radius:5px;">
                    <p style="margin:0;color:#721c24;">
                        After careful consideration, we regret to inform you that we will not be able to offer you admission at this time.
                    </p>
                </div>
                
                @if($reason_for_failing)
                <div style="background-color:#f8f9fa;border-left:4px solid #6c757d;padding:15px;margin:20px 0;">
                    <h4 style="margin-top:0;color:#495057;">Feedback:</h4>
                    <p style="margin:0;">{{ $reason_for_failing }}</p>
                </div>
                @endif
                
                <p>
                    This decision was not made lightly, and we understand this may be disappointing news. 
                    Please know that our admission process is highly competitive, and this outcome does not reflect your worth or potential for success.
                </p>
                
                <div style="background-color:#e7f3ff;border:1px solid #b8daff;padding:20px;margin:20px 0;border-radius:5px;">
                    <h4 style="margin-top:0;color:#004085;">Future Opportunities:</h4>
                    <ul style="margin:10px 0;padding-left:20px;">
                        <li><strong>Reapplication:</strong> You are welcome to reapply for future admission cycles</li>
                        <li><strong>Alternative Programs:</strong> Consider exploring other programs that might be a better fit for your current qualifications</li>
                        <li><strong>Preparation Recommendations:</strong> Use this time to strengthen your application for future consideration</li>
                        <li><strong>Transfer Opportunities:</strong> You may also consider transferring to iACADEMY after completing coursework at another institution</li>
                    </ul>
                </div>
                
                <p>
                    <strong>Ways to Strengthen Your Application for Future Consideration:</strong>
                </p>
                <ul>
                    <li>Pursue additional academic coursework or certifications relevant to your chosen field</li>
                    <li>Gain practical experience through internships, volunteer work, or personal projects</li>
                    <li>Develop a portfolio showcasing your skills and interests</li>
                    <li>Improve English proficiency and communication skills</li>
                    <li>Seek mentorship or guidance in your area of interest</li>
                </ul>
                
                <div style="background-color:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:20px 0;border-radius:5px;">
                    <p style="margin:0;color:#856404;">
                        <strong>Stay Connected:</strong> We encourage you to follow iACADEMY's social media channels and website 
                        to stay updated on upcoming programs, workshops, and admission cycles. Sometimes opportunities arise 
                        that might be perfect for your situation.
                    </p>
                </div>
                
                <p>
                    We genuinely appreciate the time and effort you invested in your application. We wish you the very best 
                    in your educational pursuits and future endeavors.
                </p>
                
                <div style="background-color:#f8f9fa;border-left:4px solid #28a745;padding:15px;margin:20px 0;">
                    <p style="margin:0;">
                        <strong>Questions or Need Guidance?</strong><br>
                        If you have questions about this decision or would like guidance on strengthening your application for the future, 
                        please don't hesitate to contact our Admissions team:
                        <br>📧 <a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a>
                        <br>📞 (032) 260-8888
                    </p>
                </div>
                
                <p>
                    Remember, this is just one step in your educational journey. We believe in your potential and encourage you 
                    to continue pursuing your dreams.
                </p>
                
                <br>
                <p>Best wishes for your future success,</p>
                <strong>iACADEMY Admissions Team</strong><br>
                <strong><a href="mailto:admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a></strong><br>
                <strong>Phone: (032) 260-8888</strong>
            </div>
            <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="iACADEMY Footer">
        </div>
    </div>
</body>
</html>
