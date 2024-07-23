<div style="height:65px"></div>
<table>
    <tr style="line-height:12px;font-size:10px;text-align:left;">
        <td style="width:50%;font-size:8px;height:110px;"><?php echo $description; ?> <?php echo $description == "Reservation Payment" ? "<br />NON REFUNDABLE AND NON <br />TRANSFERABLE":""; ?></td>
        <td style="width:50%"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
    </tr>
</table>