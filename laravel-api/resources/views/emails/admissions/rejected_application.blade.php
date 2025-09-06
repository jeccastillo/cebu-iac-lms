<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>iACADEMY Admissions: Application Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1c1c1c; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .header { border-bottom: 3px solid #0f62fe; padding-bottom: 8px; margin-bottom: 24px; }
        .title { font-size: 20px; font-weight: 700; color: #0f62fe; margin: 0; }
        .content p { line-height: 1.6; margin: 12px 0; font-size: 14px; }
        .reason { background: #f8f9fb; border-left: 3px solid #e0115f; padding: 12px; margin: 16px 0; font-style: italic; }
        .footer { margin-top: 24px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <p class="title">iACADEMY Admissions</p>
    </div>

    <div class="content">
        <p>
            {{ $name ? 'Dear ' . $name . ',' : 'Dear Applicant,' }}
        </p>

        <p>
            Thank you for taking the time to complete your interview with iACADEMY. After careful review, we regret to
            inform you that your application has not been successful at this time.
        </p>

        @if(!empty($reason))
            <div class="reason">
                Reason provided: {{ $reason }}
            </div>
        @endif

        <p>
            We appreciate your interest in iACADEMY and encourage you to consider reapplying in the future or
            exploring other programs that may align with your goals. If you have any questions, you may reach our
            Admissions Office for further assistance.
        </p>

        <p>
            Sincerely,<br>
            iACADEMY Admissions Team
        </p>
    </div>

    <div class="footer">
        <p>
            This is an automated message. Please do not reply directly to this email.
        </p>
    </div>
</div>
</body>
</html>
