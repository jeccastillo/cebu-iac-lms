
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
                    <div class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                        Dear <span style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"> {{  $information['first_name'] ." ". $information['last_name'] }}</span>,
                    </div>

                    <div>
                        Congratulations! You completed the first step of your application.
                    </div>
                    <div>
                        Please submit the following requirements and settle the 500.00 Php. application fee.
                    </div>
                    <div>
                        <!-- @if ($information->studentType)
                        <ol>
                           @foreach($information->studentType->uploadTypes as $uploadType)
                           <li>{{ $uploadType->label }}</li> 
                           @endforeach
                        </ol>
                        @endif -->
                        <ol>
                            <li>
                                Scanned copy of school ID
                            </li>
                            <li>
                                Scanned copy of PSA Birth Certificate
                            </li>
                            <li>
                                Scanned copy of 2x2 ID photo
                            </li>
                            <li>
                                Scanned copy of proof of payment for the 500 Php application fee
                            </li>
                        </ol>
                    </div>
                    <div>
                        <i>*Incomplete requirements may be submitted later on.</i><br>
                        <i>*For Transferees and 2nd degree takers, please submit a scanned copy of your Transcript of Records</i>
                    </div>
                    <h4>iACADEMY BANK DETAILS</h4>
                    <strong>Account Name: Information and Communications Technology Academy Inc.</strong>
                    <br><br>
                    <table>
                        <tr>
                            <th>BDO</th>
                            <th>SECURITY BANK</th>
                            <th>CHINABANK</th>
                            <th>METROBANK</th>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px; padding-right: 10px;">
                                <div>
                                    Account No: <br>0089-5800-5430
                                </div>
                                <div>
                                    Swift Code: BNORPHMM
                                </div>
                            </td>
                            <td style="padding-left: 10px; padding-right: 10px;">
                                <div>
                                    Account No: <br>0514-043205-001
                                </div>
                                <div>
                                    Swift Code: SETCPHMM
                                </div>
                            </td>
                            <td style="padding-left: 10px; padding-right: 10px;">
                                <div>
                                    Account No: <br>1003-0000-5963
                                </div>
                                <div>
                                    Swift Code: CHBKPHMM
                                </div>
                            </td>
                            <td style="padding-left: 10px; padding-right: 10px;">
                                <div>
                                    Account No: <br>291-7-29181232-1
                                </div>
                                <div>
                                    Swift Code: MBTCPHMM
                                </div>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <div>
                        <div>*For online banking, you may write "iACADEMY" only if the account name is too long.</div><br>
                        <div>Our Admissions Officer will call you for verification of documents and scheduling of Online Interview via Google Meet.</div>
                    </div>
                    <br><br>

                    <div  style="text-align:center; margin-top:30px;">
                        <a href="{{url('/#/admissions/requirement-submission/submission/' . $information->slug)}}" style="text-align:center">
                            <button type="button" style="text-decoration:none;display: inline-block;font-weight: 400;color: #fff;text-align: center;vertical-align: middle;border: 1px solid transparent;padding: 8px 12px;font-size: 16px;line-height: 1.6;border-radius: 0.25rem;background-color: #1c54a5;border: 1px solid #1c54a5;">Submit my Requirements</button>
                        </a>
                    </div>

                    <br><br>
                    <div>Sincerely,</div>
                    <br><br>
                    <strong>iACADEMY Admissions</strong><br>
                    <a href="admissions@iacademy.edu.ph">admissions@iacademy.edu.ph</a>
                    <br>
                </div>
                <img style="display:block;margin:0 auto; width:800px" width="800" src="https://i.ibb.co/M9SntyB/footer.png" alt="">
            </div>
        </div>
    </body>
</html>
