//FORM DATA
var formdata= new FormData();
for (const [key, value] of Object.entries(this.add_subject)) {
    formdata.append(key,value);
}


//Swal Confirmation
Swal.fire({
    title: 'Delete Entry?',
    text: "Continue deleting entry?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    imageWidth: 100,
    icon: "question",
    cancelButtonText: "No, cancel!",
    showCloseButton: true,
    showLoaderOnConfirm: true,
    preConfirm: (login) => {
        var formdata= new FormData();
        formdata.append("intCSID",classlistID);                                                            
        return axios
            .post('<?php echo base_url(); ?>unity/delete_student_cs',formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
            .then(data => {
                console.log(data.data);
                if (data.data.success) {
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Failed!',
                        data.data.message,
                        'error'
                    )
                }
            });
    },
    allowOutsideClick: () => !Swal.isLoading()
});