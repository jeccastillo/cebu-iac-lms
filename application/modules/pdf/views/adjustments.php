<h4 style="text-align:center">Adjustments</h4>
<p><?php $student['strFirstname']." ".$student['strLastname']; ?></p>
<hr />
<table class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th width='15%'>Subject</th>
            <th width='15%'>Adjustment</th>
            <th width='10%'>Removed</th>                                                
            <th width='10%'>Added</th>  
            <th width='15%'>Adjusted By</th> 
            <th width='20%'>Remarks</th>                                 
            <th width='15%'>Date</th>
        </tr>
    </thead>
    <tbody> 
        <?php foreach($adjustments as $adj): ?>                                         
        <tr style="font-size: 13px;">
            <td width='15%'><?php echo $adj['strCode']; ?></td>
            <td width='15%'><?php echo $adj['adjustment_type']; ?></td>
            <td width='10%'><?php echo $adj['from_subject']; ?></td>                                                                                                
            <td width='10%'><?php echo $adj['to_subject']; ?></td>
            <td width='15%'><?php echo $adj['strLastname']." ".$adj['strFirstname']; ?></td>
            <td width='20%'><?php echo $adj['remarks']; ?></td>
            <td width='15%'><?php echo $adj['date']; ?></td>                                                                                                                                                                                                  
        </tr>                                         
        <?php endforeach; ?>   
    </tbody>
</table>