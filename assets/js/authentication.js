$(document).ready(function () {});

function login(_email, _password) {
    $.post(base_url + "account/auth/", {
        email: _email,
        password: _password
    }, function (response) {
        loginUser(response);
    });
    return false;
}
function logout() {
    $.post(base_url + "account/logout/", {}, function (response) {
        removeLoginBlock();
        loadLoggedOutBlock();
    });
    return false;
}
function loginUser(response) {
    if (response == 1) {
        window.parent.location = base_url;
    } else {
        loginError("Invalid username or password.");
    }
}
function loginError(msg) {
    $("#login-error").empty().append(msg).fadeIn('fast');
    setTimeout('$("#login-error").fadeOut("fast")', 2000);
}
function removeLoginBlock() {
    $("#user").empty();
}
function loadLoggedOutBlock() {
    $.post(base_url + "templates/get/user_logged_out/", {}, function (response) {
        $("#user").append(response);
        $("#user").removeClass("loggedin");
        $("body").removeClass("loggedin");
    });
}