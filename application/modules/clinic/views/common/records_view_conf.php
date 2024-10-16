<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    //var table = $('#users_table').DataTable( {
    //$('#users_table').dataTable( {

    $('#users_table thead tr.search td').each( function () {
        var title = $(this).text();
        if(title != "Actions")
            $(this).html( '<input type="text" class="form-control" placeholder="'+title+'" size="15" />');
        else
            $(this).html('');
    });

    var table = $('#users_table').DataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "ordering": false,
            "autoWidth": false,
            "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_ajax/tb_mas_health_records",
            "aoColumnDefs":[
                {
                    "aTargets":[9],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>clinic/view_record/'+row[1]+'/'+row[4]">View</a></li>'                                                                
                                
                                +'</ul></div>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },       
                {
                    "aTargets":[1],
                    "bVisible": false 
                },              
            ],
            "aaSorting": [[3,'asc']],
            "fnDrawCallback": function () {                  
            
            },
        });

         //Apply the search
         table.columns().every( function () {
            var that = this;

            // $( 'input', this.footer() ).on( 'keyup change', function () {
            //     if ( that.search() !== this.value ) {
            //         that
            //             .search( this.value )
            //             //.search( "^" + $(this).val() + "$", true, false, true )
            //             .draw();
            //     }
            // } );

            $( 'input', this.header() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        //.search( "^" + $(this).val() + "$", true, false, true )
                        .draw();
                }
            } );

        } );

        
        
    });

</script>