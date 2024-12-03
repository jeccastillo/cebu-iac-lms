<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
    <tr>
        <td width="64" align="right"><img src= "<?php echo $img_dir .'tagaytayseal.png'; ?>"  width="50" height="50"/></td>
        <td width="400" style="text-align: center; line-height:100%">
         
         <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">iACADEMY Inc.</font><br />
         <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />
         <font style="font-family:Calibri Light; font-size: 10;">Telephone No: (046) 483-0470 / (046) 483-0672</font><br />
        </td>
        <td width="64" align="left" valign="middle"><img src= "<?php echo $img_dir .'iacademy-logo.png'; ?>"  width="50" height="50"/></td>
    </tr>
    <tr>
    <td colspan="3" style="font-size:10;">
        
    </td>
    </tr>
</table>
<table>
    <tbody>
        <tr>
            <td style="font-size:10px;line-height:15px;text-align:center;">STUDENT PORTAL LOGIN INFORMATION</td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:35px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:15px;">
            <td style="font-size:10px;"><?php echo date("M j,Y"); ?></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:35px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:20px;">
            <td style="font-size:10px;"><?php echo $student['strLastname'].", ".$student['strFirstname']; ?></td>
        </tr>
        <tr style="line-height:20px;">
            <td style="font-size:10px;"><?php echo $student['strProgramDescription']; ?></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:15px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:20px;">
            <td style="font-size:10px;">Thank you for registering at City College of Tagaytay. This letter will provide you with your login credentials for the iACADEMY Student Portal.</td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:15px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:20px;">
            <td style="font-size:10px;">To access the portal, you need an internet-enabled PC/Mobile Phone. Open your web browser and navigate to: http://portal.citycollegeoftagaytay.com </td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:25px;">
            <td style="font-size:10px;border-bottom:1px solid #525252;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:15px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table width="50%" style="border:1px solid #a2a2a2;">
    <tbody>
        <tr style="line-height:20px;">
            <td  style="font-size:10px;">
            </td>
        </tr>
        <tr style="line-height:20px;">
            <td  style="font-size:10px;">
                Temporary Login Credentials (You may change your password upon logging in to the portal)
            </td>
        </tr>
        <tr style="line-height:20px;">
            <td  style="font-size:10px;">
                Student Number: <?php echo $student['strStudentNumber']; ?>
            </td>
        </tr>
        <tr style="line-height:20px;">
            <td  style="font-size:10px;">
                Password: <?php echo $student['strPass']; ?>
            </td>
        </tr>
        <tr style="line-height:20px;">
            <td  style="font-size:10px;">
            </td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:25px;">
            <td style="font-size:10px;"></td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>
        <tr style="line-height:20px;">
            <td style="font-size:10px;text-align:right">Regards,</td>
        </tr>
        <tr style="line-height:20px;">
            <td style="font-size:10px;text-align:right">CCT-ITC</td>
        </tr>
    </tbody>
</table>
