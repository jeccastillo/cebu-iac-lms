
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
                    <p style="margin-bottom:40px;">Hey {{  @$user->first_name." ". @$user->last_name }}!</p>
                    <p>
                       {{$campaign->body_first}}
                    </p>

                    <div style="text-align:center; margin-top:20px; margin-bottom:20px;">
                        <img src="{{url('storage/admissions/campaigns/'.$campaign->thumbnail)}}" alt="">
                    </div>

                    <p>
                        {{$campaign->body_second}}
                    </p>

                    <div class="" style="text-align:center">
                        <a href="{{route('api.campaigns-response', $campaign->id).'?contact_id='.@$user->id.''.'&link='.@$campaign->button_first_link.''.'&response='.@$campaign->button_first_label}}" target="_blank">
                            <button type="button" name="button" style="background:#24b14d;padding:10px 80px; font-size: 0.9rem; line-height: 1.6; border-radius: 0.25rem; color: #fff; font-weight:bold; outline:none; border:0; margin:10px; text-transform:uppercase">{{$campaign->button_first_label}}</button>
                        </a>
                        <a href="{{route('api.campaigns-response', $campaign->id).'?contact_id='.@$user->id.''.'&link='.@$campaign->button_second_link.''.'&response='.@$campaign->button_second_label}}" target="_blank" >
                            <button type="button" name="button" style="background:#000;padding:10px 80px; font-size: 0.9rem; line-height: 1.6; border-radius: 0.25rem; color: #fff; font-weight:bold; outline:none; border:0;  margin:10px; text-transform:uppercase">{{$campaign->button_second_label}}</button>
                        </a>
                    </div>
                    <!-- <a href="{{$campaign->button_first_link}}" target="_blank" > -->
                </div>
                <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="">
            </div>
        </div>
    </body>
</html>
