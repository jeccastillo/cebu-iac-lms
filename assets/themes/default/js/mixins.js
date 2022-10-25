import Vue from "vue";

Vue.mixin({
  methods: {
    ifStudent: function () {
      if (window.user_data.type == "student") {
        window.location.href = "#/error404";
      }
    },

    replaceUnderScore: function (type) {
      var text = type;
      if (type) {
        text = type.replace(/_/g, " ");
      }
      return text;
    },

    successMessageApi: function (message) {
      this.$swal({
        title: "SUCCESS!",
        text: message,
        imageWidth: 100,
        imageUrl: "images/BLUE-logo.png",
      });
    },

    failedMessageApi: function (message) {
      this.$swal({
        title: "FAILED!",
        text: message,
        imageWidth: 100,
        imageUrl: "images/BLUE-logo.png",
      });
    },

    noChangesApi: function () {
      this.$swal({
        title: "Cancelled",
        text: "No changes were made!",
        imageWidth: 100,
        imageUrl: "images/BLUE-logo.png",
      });
    },

    getDataList: function (url) {
      this.table_loader = true;
      this.$store.state.data_list = [];

      return axios
        .get(this.$store.state.api_base + url, {
          headers: { Authorization: `Bearer ${window.token}` },
        })

        .then((data) => {
          this.$store.state.data_list = data.data.data;
          this.$store.state.meta_data = data.data.meta;

          return {
            table_loader: false,
          };
        })
        .catch((e) => {
          console.log("error");
        });
    },

    getSinglePage: function (url) {
      this.table_loader = true;
      return axios
        .get(this.$store.state.api_base + url, {
          headers: { Authorization: `Bearer ${window.token}` },
        })

        .then((data) => {
          this.table_loader = false;
          return data.data.data;
        })
        .catch((e) => {
          console.log("error");
        });
    },

    getDataPureList: function (url) {
      return axios
        .get(this.$store.state.api_base + url, {
          headers: { Authorization: `Bearer ${window.token}` },
        })

        .then((data) => {
          return data.data.data;
        })
        .catch((e) => {
          console.log("error");
        });
    },

    // customSubmit: function(type, title, text, data, url, redirect) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to " + type + " this " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;
    //             $(".modal").modal("hide");

    //             axios
    //                 .post(this.$store.state.api_base + url, data, {
    //                     headers: { Authorization: `Bearer ${window.token}` }
    //                 })
    //                 .then(data => {
    //                     this.is_done = true;

    //                     if (data.data.success) {
    //                         this.successMessageApi(data.data.message);

    //                         if (redirect) {
    //                             window.location.href = "#/" + redirect;
    //                         } else {
    //                             location.reload();
    //                         }
    //                     } else {
    //                         this.failedMessageApi(data.data.message);
    //                     }
    //                 });
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    // customSubmitNoReload: function(type, title, text, data, url, redirect) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to " + type + " this " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;
    //             $(".modal").modal("hide");

    //             axios
    //                 .post(this.$store.state.api_base + url, data, {
    //                     headers: { Authorization: `Bearer ${window.token}` }
    //                 })
    //                 .then(data => {
    //                     this.is_done = true;

    //                     if (data.data.success) {
    //                         this.successMessageApi(data.data.message);

    //                         if (redirect) {
    //                             window.location.href = "#/" + redirect;
    //                         } else {
    //                             // location.reload();
    //                         }
    //                     } else {
    //                         this.failedMessageApi(data.data.message);
    //                     }
    //                 });
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    // submitForm: function(
    //     type,
    //     title,
    //     text,
    //     data,
    //     url,
    //     redirect,
    //     custom_id
    // ) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to " + type + " this " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;
    //             $(".modal").modal("hide");

    //             if (type == "add") {
    //                 axios
    //                     .post(this.$store.state.api_base + url, data, {
    //                         headers: {
    //                             Authorization: `Bearer ${window.token}`
    //                         }
    //                     })
    //                     .then(data => {
    //                         this.is_done = true;

    //                         if (data.data.success) {
    //                             this.successMessageApi(data.data.message);
    //                             location.reload();
    //                         } else {
    //                             this.failedMessageApi(data.data.message);
    //                         }
    //                     });
    //             } else {
    //                 if (custom_id) {
    //                     data.id = custom_id;
    //                 }

    //                 axios
    //                     .post(
    //                         this.$store.state.api_base + url + data.id,
    //                         data,
    //                         {
    //                             headers: {
    //                                 Authorization: `Bearer ${window.token}`
    //                             }
    //                         }
    //                     )
    //                     .then(data => {
    //                         this.is_done = true;

    //                         if (data.data.success) {
    //                             this.successMessageApi(data.data.message);

    //                             if (redirect) {
    //                                 window.location.href = "#/" + redirect;
    //                             } else {
    //                                 location.reload();
    //                             }
    //                         } else {
    //                             this.failedMessageApi(data.data.message);
    //                         }
    //                     });
    //             }
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    // deleteData: function(title, text, id, url) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to delete/remove this " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;

    //             axios
    //                 .delete(this.$store.state.api_base + url + id, {
    //                     headers: { Authorization: `Bearer ${window.token}` }
    //                 })
    //                 .then(data => {
    //                     if (data.data.success) {
    //                         this.successMessageApi(data.data.message);
    //                         location.reload();
    //                     } else {
    //                         this.failedMessageApi(data.data.message);
    //                     }
    //                     this.is_done = true;
    //                 });
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    // deleteAllData: function(title, text, id, url) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to delete/remove all " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;

    //             axios
    //                 .delete(this.$store.state.api_base + url + id, {
    //                     headers: { Authorization: `Bearer ${window.token}` }
    //                 })
    //                 .then(data => {
    //                     if (data.data.success) {
    //                         this.successMessageApi(data.data.message);
    //                         location.reload();
    //                     } else {
    //                         this.failedMessageApi(data.data.message);
    //                     }
    //                     this.is_done = true;
    //                 });
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    // uploadFile: function(type, url) {
    //     this.spinner_uploader = true;

    //     var file = this.$refs.file__upload__global.files[0];
    //     var formData = "";

    //     formData = new FormData();

    //     formData.append(type, file);

    //     return axios
    //         .post(this.$store.state.api_base + url, formData, {
    //             headers: {
    //                 "Content-Type": "multipart/form-data",
    //                 Authorization: `Bearer ${window.token}`
    //             }
    //         })
    //         .then(data => {
    //             if (data.data.success) {
    //                 this.spinner_uploader = false;

    //                 return {
    //                     success: true,
    //                     file_name: data.data.file_name,
    //                     path: data.data.url_path
    //                 };
    //             } else {
    //                 this.spinner_uploader = false;
    //                 return {
    //                     errors: data.data.response,
    //                     success: false
    //                 };
    //             }
    //         })

    //         .catch(error => {
    //             this.is_done = true;
    //         });
    // },

    // customSubmitNoReloadModal: function(
    //     type,
    //     title,
    //     text,
    //     data,
    //     url,
    //     redirect
    // ) {
    //     this.$swal({
    //         title: title,
    //         text:
    //             "Are you sure you want to " + type + " this " + text + "?",
    //         showCancelButton: true,
    //         confirmButtonText: "Yes",
    //         imageWidth: 100,
    //         imageUrl: "images/BLUE-logo.png",
    //         cancelButtonText: "No, cancel!",
    //         showCloseButton: true,
    //         showLoaderOnConfirm: true
    //     }).then(result => {
    //         if (result.value) {
    //             this.is_done = false;
    //             // $(".modal").modal("hide");

    //             axios
    //                 .post(this.$store.state.api_base + url, data, {
    //                     headers: { Authorization: `Bearer ${window.token}` }
    //                 })
    //                 .then(data => {
    //                     this.is_done = true;

    //                     if (data.data.success) {
    //                         this.successMessageApi(data.data.message);
    //                         return data.data;

    //                         if (redirect) {
    //                             window.location.href = "#/" + redirect;
    //                         } else {
    //                             // location.reload();
    //                         }
    //                     } else {
    //                         this.failedMessageApi(data.data.message);
    //                     }
    //                 });
    //         } else {
    //             this.noChangesApi();
    //         }
    //     });
    // },

    unitOfMeasureMent: function () {
      return [
        "pcs",
        "pack",
        "ream",
        "roll",
        "set",
        "cm",
        "feet",
        "box",
        "lot",
        "bottle",
        "meters",
        "killo",
        "gallon",
        "pad",
        "booklet",
        "pail",
        "sheet",
        "unit",
        "sack",
        "ml",
        "liter",
      ];
    },

    toFormatPirce: function (number) {
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    clearList: function () {
      this.$store.state.data_list = [];
    },
  },
});
