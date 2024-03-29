<h4 style="text-align:center">Adjustments</h4>
<h5><?php echo $student['strFirstname'].", ".$student['strLastname']; ?></h5>
<hr />
<table style="font-size:11px;" class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th width='15%'>Subject</th>
            <th width='15%'>Adjustment</th>
            <th width='5%'>Removed</th>                                                
            <th width='5%'>Added</th>  
            <th width='15%'>Adjusted By</th> 
            <th width='30%'>Remarks</th>                                 
            <th width='15%'>Date</th>
        </tr>
    </thead>
    <tbody> 
        <?php foreach($adjustments as $adj): ?>                                         
        <tr style="font-size: 10px;">
            <td width='15%'><?php echo $adj['strCode']; ?></td>
            <td width='15%'><?php echo $adj['adjustment_type']; ?></td>
            <td width='5%'><?php echo $adj['from_subject']; ?></td>                                                                                                
            <td width='5%'><?php echo $adj['to_subject']; ?></td>
            <td width='15%'><?php echo $adj['strLastname']." ".$adj['strFirstname']; ?></td>
            <td width='30%'><?php echo $adj['remarks']; ?></td>
            <td width='15%'><?php echo $adj['date']; ?></td>                                                                                                                                                                                                  
        </tr>                                         
        <?php endforeach; ?>   
    </tbody>
</table>