
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
      <meta charset="utf-8">
      <title></title>
    </head>
    <body>
        <div class="" style="width:100%;margin:0 auto;display:block;">
            <div class="" style="width:800px;display:block;margin:0 auto;">
                <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/tPGck0d/Header.png" alt="">
                <div class="" style="padding:20px;font-family:verdana;font-size:14px;">
                    <p style="margin-bottom:40px;">Dear {{  @$user->first_name." ". @$user->last_name }},</p>
                    <p>
                        Choosing the right school that will suit your needs, interests, and future career path might be a challenge for students like you. We are here to help. To be able to give you some insights and information on what could be the best program for your academic and professional interests, you may sign up on this
                       <u style="font-weight:bold"><a href="#">online form</a></u>.
                    </p>
                    <p>
                        Get to know our school, student life and culture, and the opportunities provided to our Game Changers by taking a
                        look atthese short videos and informational materials:
                    </p>
                    <ul>
                        <li>A <strong> <u><a href="https://iacademy.edu.ph/homev4/virtual_tour"> virtual tour</a></u> </strong> of our Nexus campus</li>
                        <li>Our  <strong> <u><a href="https://iacademy.edu.ph/homev4/brochure"> interactive brochure</a></u> </strong> with a list of our programs and student activities</li>
                        <li>Our  <strong> <u><a href="https://iacademy.edu.ph/homev4/wswbr?{{'code='.$user->code.'&type='.$user->department}}">PRIME and Alt+Enter workshops </a></u> </strong> —set of creative and technical workshops that will hone your skills in either of these areas—Computing, Business, and Design.</li>
                        <li>Our  <strong> <u><a href="https://iacademy.edu.ph/homev4/workshop_and_webinar">iACADEMY webinars </a></u> </strong>  on various topics like flexible remote learning, specialized programs, and media literacy.</li>
                        <li><strong> <u><a href="https://iacademy.edu.ph/homev4/student_achievements">Link </a></u> </strong>  of students’ output-based projects and achievements (awards won in competitions locally and abroad)</li>
                        <li><strong> <u><a href="https://www.iacademy.edu.ph">List </a></u> </strong>  of our industry partners where our students get their internships, resulting in 96% placement rate of our graduates.</li>
                    </ul>

                    <p>More information about our specialized programs and student activities are available on our website <br> <strong><u><a href="https://iacademy.edu.ph/">www.iacademy.edu.ph</a></u></strong> </p>

                    <p>Should you wish to take our online exam, just click <strong><u><a href="{{url('/#/online-exam?'.'code='.$user->code.'&type='.$user->department)}}">here</a></u></strong> .</p>

                    <p>Let us help you chart your path to another game-changing adventure.</p>
                    <br>

                    <p>Sincerely,</p> <br>

                    <strong>iACADEMY Admissions</strong><br>
                    <strong><u><i><a href="#">admissions@iacademy.edu.ph</a></i></u></strong>

                </div>
                <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="">
            </div>
        </div>
    </body>
</html>
