<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/bootstrap.min.js"></script>
<!--DATA TABLES--->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/datatables.min.js"></script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/AdminLTE/app.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/excanvas.min.js">
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js">
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js">
</script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/select2/select2.full.min.js"></script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.pie.js">
</script>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.categories.min.js"></script>
<script>
$.fn.modal.Constructor.prototype.enforceFocus = function() {};

$(document).ready(function() {


    var total_units = 0;

    $('.date').datepicker({
        pickTime: false
    });

    $(".select2").select2();

    getOnlineUsers();
    // get_messages();

    // setInterval("get_messages()", 5000);
    // setInterval("getOnlineUsers()", 20000);

    $('#addStudentCourse').change(function() {
        load_curriculum();
    });


    $('#subject-to-add').change(function() {
        reset_sections($("#strAcademicYear").val());
    });

    $('#subjectSv').change(function() {
        reset_sections2($("#active-sem-id").val());
    });



    $('#daterange-btn').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                    .endOf('month')
                ]
            },
            startDate: moment().subtract('days', 29),
            endDate: moment()
        },
        function(start, end) {
            var daterange = start.format('YYYY-MM-D') + '/' + end.format('YYYY-MM-D');
            <?php 
                if(!isset($cat))
                    $cat = "";
            ?>
            document.location = "<?php echo base_url(); ?>unity/logs/" + daterange + "/<?php echo $cat; ?>";
        }
    );
    $("#s1").change(function(e) {
        var str = $(this).val();
        $(".filter-year :nth-child(2)").each(function() {
            if ($(this).html().trim() != str)
                $(this).parent().hide();
            else
                $(this).parent().show();
        });
    });


    $('#date-filter-button').click(function(e) {

        var filter = $("#date-filter").val();
        alert(filter);
        if (filter == "")
            document.location = "<?php echo base_url(); ?>";
        else
            document.location = "<?php echo base_url(); ?>unity/faculty_dashboard/" + filter;




    });

    $('#classlist-table').dataTable({
        "aoColumnDefs": [{
                "aTargets": [0]
            },
            {
                "aTargets": [1]
            },
            {
                "aTargets": [2]
            },
            {
                "bSearchable": false,
                "bSortable": false,
                "aTargets": [3]
            }
        ]

    });

    /*$('#classlist-table-admin').dataTable({
         "aoColumnDefs": [
                      { "aTargets": [0]},
                      { "aTargets": [1]},
                      { "aTargets": [2]},
                      { "aTargets": [4]},
                      { "bSearchable":false,"bSortable" :false,"aTargets":[3]},
                      { "bSearchable":false,"bSortable" :false,"aTargets":[5]}
                      
                  ],
        "drawCallback":function(settings){
            
            $(".finalizedOption").change(function(e){
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var final = $(this).val();
                var data = {'intID':id,'intFinalized':final};
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/update_finalized',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        alert("updated");
                    }
                });

            });
            
            $(".trash-classlist").click(function(e){
                conf = confirm("Are you sure you want to delete?");
                if(conf)
                {
                    $(".loading-img").show();
                    $(".overlay").show();
                    var id = $(this).attr('rel');
                    var parent = $(this).parent().parent().parent().parent().parent();
                    //alert(parent.html());
                    var data = {'id':id};

                    $.ajax({
                        'url':'<?php echo base_url(); ?>unity/delete_classlist',
                        'method':'post',
                        'data':data,
                        'dataType':'json',
                        'success':function(ret){
                            if(ret.message == "failed"){
                                $(".alert").show();
                                setTimeout(function() {
                                    $(".alert").hide('fade', {}, 500)
                                }, 3000);
                            }
                            else
                                parent.hide();

                            $(".loading-img").hide();
                            $(".overlay").hide();
                    }
                });
                }
            });
        }
     
     });
    */



    $('#faculty-table').dataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 300, 500],
        "aoColumnDefs": [{
                "aTargets": [0]
            },
            {
                "aTargets": [1]
            },
            {
                "bSearchable": false,
                "bSortable": false,
                "aTargets": [2]
            }
        ]

    });
    /*
    $('#student-chooser').dataTable({
        "aLengthMenu":  [10, 20,50,100,250,300,500, 750, 1000],
        "aoColumnDefs": [
                      { "sTitle": "Name", "aTargets": [0]},
                      { "sTitle": "Course", "aTargets": [1]},
                      { "sTitle": "Year", "aTargets": [2]},
                      { "sTitle": "Section", "aTargets": [3]}
                  ]
     
     });
    */

    $(".prelimInput").blur(function() {
        $(".loading-img").show();
        $(".overlay").show();
        var csid = $(this).attr('rel');
        var points = $(this).val();
        var data = {
            'intCSID': csid,
            'floatPrelimGrade': points
        };
        $.ajax({
            'url': '<?php echo base_url(); ?>unity/update_grade',
            'method': 'post',
            'data': data,
            'dataType': 'json',
            'success': function(ret) {
                $(".loading-img").hide();
                $(".overlay").hide();
                $("#average-" + csid).html('' + ret.average);
                $("#eq-" + csid).html('' + ret.eq);
            }
        });

    });

    $(".midtermInput").blur(function() {
        $(".loading-img").show();
        $(".overlay").show();
        var csid = $(this).attr('rel');
        var points = $(this).val();
        var data = {
            'intCSID': csid,
            'floatMidtermGrade': points
        };
        $.ajax({
            'url': '<?php echo base_url(); ?>unity/update_grade/2',
            'method': 'post',
            'data': data,
            'dataType': 'json',
            'success': function(ret) {
                $(".loading-img").hide();
                $(".overlay").hide();
                $("#average-" + csid).html('' + ret.average);
                $("#eq-" + csid).html('' + ret.eq);
            }
        });

    });
    $(".finalsInput").blur(function() {
        $(".loading-img").show();
        $(".overlay").show();
        var csid = $(this).attr('rel');
        var points = $(this).val();
        var parent = $(this).parent();
        var data = {
            'intCSID': csid,
            'floatFinalGrade': points
        };
        $.ajax({
            'url': '<?php echo base_url(); ?>unity/update_grade/3',
            'method': 'post',
            'data': data,
            'dataType': 'json',
            'success': function(ret) {
                $(".loading-img").hide();
                $(".overlay").hide();
                $("#average-" + csid).html('' + ret.average);
                $("#eq-" + csid).html('' + ret.eq);
            }
        });

    });





    $("#select-sem").change(function(e) {
        document.location = "<?php echo base_url(); ?>unity/view_classlist_archive/" + $(this).val();

    });
    $("#select-sem-admin").change(function(e) {
        document.location = "<?php echo base_url(); ?>unity/view_classlist_archive_admin/" + $(this)
            .val();

    });

    $("#select-sem-dept").change(function(e) {
        document.location = "<?php echo base_url(); ?>unity/view_classlist_archive_dept/" + $(this)
            .val();

    });

    $("#select-sem-student").change(function(e) {
        if ($(this).val() != 0)
            document.location = "<?php echo base_url(); ?>unity/student_viewer/" + $("#student-id")
            .val() + "/" + $(this).val();

    });
    $("#select-sem-report1").change(function(e) {
        document.location = "<?php echo base_url(); ?>unity/registered_students_report/" + $(this)
            .val();

    });

    //newly added 4-30-2016 - jm ^_^

    $("#select-sem-profile").change(function(e) {
        document.location = "<?php echo base_url(); ?>faculty/my_profile/" + $(this).val();

    });


    $("#year-start").change(function(e) {
        var yearStart = $(this).val();
        var yStart = parseInt(yearStart);
        yStart++;
        $('#year-end>option[value="' + yStart + '"]').prop("selected");
        $('#year-end>option:selected').removeAttr("selected");
    });

    $(".radio-select").change(function(e) {
        if ($(this).val() == 'student') {
            $("#student-box").show();
            $("#section-box").hide();

        } else {
            $("#student-box").hide();
            $("#section-box").show();
        }
    });


    $(".update-ay-record").click(function(e) {
        conf = confirm("Are you sure you want to update? There is no undo button for this");
        if (conf) {
            id = $(this).attr("rel");
            document.location = "<?php echo base_url(); ?>registrar/update_incomplete_subjects/" + id;
        }
    });

    $("#transcrossSelect").change(function(e) {
        if ($(this).val() == 'transferee' || $(this).val() == 'cross')
            $('#transcrossText').prop("disabled", false);
        else
            $('#transcrossText').prop("disabled", true);
    });    



    $("#submit-subject").click(function(e) {
        var csid = $(this).attr('rel');
        var parent = $(this).parent().parent();
        var data = {
            'strCode': $("#strCode").val(),
            'strUnits': $("#strUnits").val(),
            'strDescription': $("#strDescription").val()
        };
        if ($("#strCode").val() == "" || $("#strUnits").val() == "") {
            alert("Please fill in required fields");
        } else {
            $.ajax({
                'url': '<?php echo base_url(); ?>unity/submit_subject_ajax',
                'method': 'post',
                'data': data,
                'dataType': 'json',
                'success': function(ret) {
                    $("#subjects").append("<option value='" + ret.newid + "' >" + ret.code +
                        "</option>");
                    $('#myModal').modal('hide');
                    $("#subjects option[value='" + ret.newid + "']").attr("selected",
                        "selected");

                }
            });
        }


    });



    $("#enumScholarship").change(function() {
        return_tuition();
    });

    $("#load-subjects").click(function(e) {
        var button = $(this);
        button.attr('disabled', 'disabled');
        e.preventDefault();
        var container = $("#subject-list");
        container.html("");

        
        var data = {
            'intStudentID': $("#studentID").val(),
            'sem': $("#activeSem").val()
        };

        var subsection = "";
        var selected = "";
        var done = false;
        $.ajax({
            'url': '<?php echo base_url(); ?>unity/load_subjects',
            'method': 'post',
            'data': data,
            'dataType': 'json',
            'success': function(ret) {
                total_units = 0;
                button.removeAttr('disabled');
                $("#submit-button").removeAttr('disabled');
                if (ret.subjects.length > 0) {
                    for (i in ret.subjects) {
                        selected = '';
                        done = false;
                        container.append(
                            "<div><input type='hidden' class='subject-id' name='subjects-loaded[]' value='" +
                            ret.subjects[i].intID +
                            "'><br> <div class='row'><div class='col-xs-3 subject-code'>" +
                            ret.subjects[i].strCode +
                            "</div><div class='col-xs-3 subject-description'>" + ret
                            .subjects[i].strDescription +
                            "</div><div class='col-xs-3 subject-units'>" + ret.subjects[
                                i].strUnits +
                            "</div><div class='col-xs-3'><a class='btn remove-subject-loaded btn-default  btn-flat'><i class='fa fa-minus'></i></a></div></div><hr /></div>"
                        );
                        
                        if (ret.subjects[i].classlists.length > 0) {
                            var str = "<div><select class='form-control' name='section-" +
                                ret.subjects[i].intID +
                                "'>";
                                
                            
                            var program = "<?php echo isset($student['short_name'])?$student['short_name']:"" ?>";                                                        
                            
                            
                            for (j in ret.subjects[i].classlists) {                                                         
                                
                                if(program == ret.subjects[i].classlists[j].strClassName && !done){
                                    selected = "selected";
                                    done = true;
                                }
                                else
                                    selected = "";

                                subsection = ret.subjects[i].classlists[j].sub_section ? ret.subjects[i].classlists[j].sub_section : "";
                                var str = str + "<option "+selected+" value ='" + ret.subjects[i]
                                    .classlists[j].intID + "'>Section: " + ret.subjects[i]
                                    .classlists[j].strClassName + " " 
                                    + ret.subjects[i].classlists[j].year + " "
                                    + ret.subjects[i].classlists[j].strSection + " "
                                    + subsection
                                    + "(" + ret.subjects[i]
                                    .classlists[j].numCount + ")</option>";
                            }

                            var str = str + "</select></div>";

                            container.append(str);
                        }

                        $("#subject-to-add option[value='" + ret.subjects[i].intID + "']")
                            .remove();

                        total_units = parseInt(total_units) + parseInt(ret.subjects[i]
                            .strUnits);

                    }

                    return_tuition();



                    $(".remove-subject-loaded").click(function() {

                        mainContainer = $(this).parent().parent().parent();

                        sid = mainContainer.find('.subject-id').val();
                        scode = mainContainer.find('.subject-code').html() + " " +
                            mainContainer.find('.subject-description').html();
                        sunits = mainContainer.find('.subject-units').html();

                        total_units = parseInt(total_units) - parseInt(sunits);

                        $('#subject-to-add').append($('<option>', {
                            value: sid,
                            text: scode
                        }));

                        $("select[name='section-" + sid + "']").remove();

                        mainContainer.remove();
                        return_tuition();
                    });

                } else {
                    alert("no advised subjects. please see adviser");
                }
            }

        });



    });

    $("#load-subjects2").click(function(e) {
        e.preventDefault();
        var button = $(this);
        button.attr('disabled', 'disabled');
        var container = $("#subject-list");
        container.html("");


        var data = {
            'intStudentID': $("#studentID").val(),
            'sem': $("#activeSem").val()
        };

        $.ajax({
            'url': '<?php echo base_url(); ?>unity/load_subjects2',
            'method': 'post',
            'data': data,
            'dataType': 'json',
            'success': function(ret) {
                total_units = 0;
                button.removeAttr('disabled');
                if (ret.subjects.length > 0) {
                    for (i in ret.subjects) {
                        container.append(
                            "<div><input type='hidden' class='subject-id' name='subjects-loaded[]' value='" +
                            ret.subjects[i].subjectID +
                            "'><br> <div class='row'><div class='col-xs-3 subject-code'>" +
                            ret.subjects[i].strCode +
                            "</div><div class='col-xs-3 subject-description'>" + ret
                            .subjects[i].strDescription +
                            "</div><div class='col-xs-3 subject-units'>" + ret.subjects[
                                i].strUnits +
                            "</div><div class='col-xs-3'><a class='btn remove-subject-loaded btn-default  btn-flat'><i class='fa fa-minus'></i></a></div></div><hr /></div>"
                        );


                        total_units = parseInt(total_units) + parseInt(ret.subjects[i]
                            .strUnits);

                    }

                    return_tuition();




                } else {
                    alert("no classlists enlisted. please see adviser");
                }
            }

        });



    });   


    $("#add-subject-loaded").click(function(e) {
        e.preventDefault();

        var container = $("#subject-list");
        var subjectID = $("#subject-to-add").val();
        var section = $("#sections-to-add").val();
        if (section != "") {
            var data = {
                'intID': $("#subject-to-add").val()
            };

            $.ajax({
                'url': '<?php echo base_url(); ?>unity/add_single_subject',
                'method': 'post',
                'data': data,
                'dataType': 'json',
                'success': function(ret) {

                    container.append(
                        "<div><input type='hidden' name='subjects-loaded[]' class='subject-id' value='" +
                        ret.subject.intID +
                        "'><input type='hidden' name='subjects-section[]' value='" +
                        section +
                        "'><br> <div class='row'><div class='col-xs-3 subject-code'>" +
                        ret.subject.strCode +
                        "</div><div class='col-xs-3 subject-description'>" + ret.subject
                        .strDescription + "</div><div class='col-xs-3 subject-units'>" +
                        ret.subject.strUnits +
                        "</div><div class='col-xs-3'><a class='btn remove-subject-loaded2 btn-default  btn-flat'><i class='fa fa-minus'></i></a></div></div><hr /></div>"
                    );


                    $("#subject-to-add option[value='" + subjectID + "']").remove();
                    reset_sections($("#strAcademicYear").val());
                    if ($("#enumScholarship").val() == "paying")
                        total_units = parseInt(total_units) + parseInt(ret.subject
                            .strUnits);                    
                    
                    $(".remove-subject-loaded2").click(function() {

                        mainContainer = $(this).parent().parent().parent();

                        sid = mainContainer.find('.subject-id').val();
                        scode = mainContainer.find('.subject-code').html() + " " +
                            mainContainer.find('.subject-description').html();
                        sunits = mainContainer.find('.subject-units').html();

                        if (ret.subject.strCode == mainContainer.find(
                                '.subject-code').html())
                            total_units = parseInt(total_units) - parseInt(sunits);

                        $('#subject-to-add').append($('<option>', {
                            value: sid,
                            text: scode
                        }));                        
                        mainContainer.remove();
                        return_tuition();
                    });
                    return_tuition();
                }


            });
        } else {
            alert("please enter section code");
        }



    });

    $("#submit-ay").click(function(e) {
        var submit_sy = confirm(
            "Are you sure you want to switch school year?"
        );
        if (submit_sy) {
            $("#set-ay-form").submit();
        }


    });

    $('#myModal').on('show.bs.modal', function(e) {
        $("#strCode").val('');
        $("#strUnits").val('');
        $("#strDescription").val('');
    });

    $("#submit-ay").click(function(e) {
        var submit_sy = confirm(
            "Are you sure you want to switch school year?"
        );
        if (submit_sy) {
            $("#set-ay-form").submit();
        }


    });

    $("#export_student_account_report").click(function(e){
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        if($("#report_date").val() == ""){
            alert("Please select report date");
        }else{
            var url = base_url + 'excel/student_account_report/' + $("#sem").val() + '/' + campus + '/' + $("#report_date").val();
            window.open(url, '_blank');
        }
    })

    $("#ched_report_excel").click(function(e){
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/ched_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })

    $("#ched_report_pdf").click(function(e){
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/ched_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })
});



/*
 * Custom Label formatter
 * ----------------------
 */
function labelFormatter(label, series) {
    return "<div style='font-size:13px; text-align:center; padding:2px; color: #fff; font-weight: 600;'>" +
        label +
        "<br/>" +
        Math.round(series.percent) + "%</div>";
}

function printGrade() {
    window.print();

}

function return_tuition() {
    sj = new Array();

    $(".subject-id").each(function() {
        sj.push($(this).val());
    });


    var data = {
        'studentID': $("#studentID").val(),
        'subjects_loaded': sj,
        'scholarship': $("#enumScholarship").val(),
        'stype': $("#transcrossSelect").val()
    };
    $.ajax({
        'url': '<?php echo base_url(); ?>unity/get_tuition_ajax',
        'method': 'post',
        'data': data,
        'dataType': 'json',
        'success': function(ret) {
            $("#tuitionContainer").html(ret.tuition);
        }
    });
}

function load_subjects() {
    $("#subject-to-add").find('option').remove();

    var sem = $("#strAcademicYear").find('option:selected').attr('rel');
    var curriculum = $("#intCurriculumID").val();
    var data = {
        'curriculum': curriculum
    };

    $.ajax({
        'url': '<?php echo base_url(); ?>unity/get_subjects_ajax',
        'method': 'post',
        'data': data,
        'dataType': 'json',
        'success': function(ret) {
            $("#subject-list").html('');
            $.each(ret, function(i, item) {
                $('#subject-to-add').append($('<option>', {
                    value: item.intID,
                    text: item.strCode + " " + item.strDescription
                }));

            });
        }
    });
}

function load_curriculum() {
    $("#intCurriculumID").find('option').remove();

    var course = $("#addStudentCourse").val();
    var data = {
        'course': course
    };

    $.ajax({
        'url': '<?php echo base_url(); ?>unity/get_curriculum_ajax',
        'method': 'post',
        'data': data,
        'dataType': 'json',
        'success': function(ret) {
            $("#subject-list").html('');
            $.each(ret, function(i, item) {
                $('#intCurriculumID').append($('<option>', {
                    value: item.intID,
                    text: item.strName
                }));

            });

            load_subjects();
        }
    });
}

function reset_sections(sem) {
    $("#sections-to-add").find('option').remove();
    var subject = $("#subject-to-add").val();
    var data = {
        'subject_id': subject,
        'sem': sem
    };

    $.ajax({
        'url': '<?php echo base_url(); ?>unity/get_sections_ajax',
        'method': 'post',
        'data': data,
        'dataType': 'json',
        'success': function(ret) {
            $.each(ret, function(i, item) {
                $('#sections-to-add').append($('<option>', {
                    value: item.intID,
                    text: item.strSection
                }));
            });
        }
    });
}

function reset_sections2(sem) {
    $("#sections-to-add").find('option').remove();
    var subject = $("#subjectSv").val();
    $("#viewSchedules").attr('href', '<?php echo base_url(); ?>subject/subject_viewer/' + subject);
    var data = {
        'subject_id': subject,
        'sem': sem
    };

    $.ajax({
        'url': '<?php echo base_url(); ?>unity/get_sections_ajax',
        'method': 'post',
        'data': data,
        'dataType': 'json',
        'success': function(ret) {
            $.each(ret, function(i, item) {
                $('#sections-to-add').append($('<option>', {
                    value: item.intID,
                    text: item.strSection
                }));
            });
        }
    });
}

function get_messages() {
    $.ajax({
        type: "GET",
        url: "<?php echo base_url(); ?>messages/get_message_alert",
        dataType: 'json',
        success: function(data) {

            if (data.count > parseInt($(".unread-message-alert").html())) {
                $(".unread-message-alert").removeClass('hide');
                document.getElementById('ping').play();
            }
            if (data.count == 0)
                $(".unread-message-alert").addClass('hide');

            $("#message-list").html('');
            for (i in data.messages) {
                htappend = '<li><a href="<?php echo base_url()."messages/view_message/" ?>' + data.messages[
                        i].intMessageUserID + '"><div class="pull-left"></div><h4>' + data.messages[i]
                    .strFirstname + " " + data.messages[i].strLastname +
                    '<small><i class="fa fa-clock-o"></i> ' + data.messages[i].dteDate +
                    '</small></h4><p>' + data.messages[i].strSubject + '</p></a></li><!-- end message -->';

                $("#message-list").append(htappend);
            }


            $(".unread-message-alert").html(data.count);
            $(".unread-message-text").html(data.count);
        }
    });
}

function getOnlineUsers() {
    $("#online-users").html('');
    $.ajax({
        type: "GET",
        url: "<?php echo base_url(); ?>faculty/get_online_users",
        dataType: 'json',
        success: function(data) {
            for (i in data.users) {
                htappend =
                    '<div class="form-group"><label class="control-sidebar-subheading"><i class="fa fa-circle text-success"></i> <a class="text-muted" href="<?php echo base_url(); ?>messages/compose_message/' +
                    data.users[i].intID + '">' + data.users[i].strFirstname + ' ' + data.users[i]
                    .strLastname + '</a></label></div>';

                $("#online-users").append(htappend);
            }
        }
    });
}
</script>
