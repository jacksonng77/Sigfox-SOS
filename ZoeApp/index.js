// For an introduction to the Blank template, see the following documentation:
// http://go.microsoft.com/fwlink/?LinkID=397704
// To debug code on page load in cordova-simulate or on Android devices/emulators: launch your app, set breakpoints, 
// and then run "window.location.reload()" in the JavaScript Console.
(function () {
    "use strict";

    $(document).ready(function () {
        $("#btnUpdate").bind("click", function () {
            /*
                Tag this user with the email address that he entered.
                In a real scenario, this email would have been pulled in from the database
                with his email account, and the ID of his hardware device
            */
            window.plugins.OneSignal.sendTag("email", $("#txtEmail").val());
            alert($("#txtEmail").val());
        });

        $("#btnDismiss").bind("click", function () {
            $("#message").html("");
        });
    });

    document.addEventListener('deviceready', function () {
        // Enable to debug issues.
        // window.plugins.OneSignal.setLogLevel({logLevel: 4, visualLevel: 4});

        var notificationOpenedCallback = function (jsonData) {
            console.log('notificationOpenedCallback: ' + JSON.stringify(jsonData));
        };

        var notificationReceivedCallback = function (jsonData) {
            $("#message").html(jsonData.payload.body);  //display message received from onesignal
        };

        window.plugins.OneSignal
            .startInit("your app id")
            .handleNotificationOpened(notificationOpenedCallback)
            .handleNotificationReceived(notificationReceivedCallback)
            .inFocusDisplaying(window.plugins.OneSignal.OSInFocusDisplayOption.None)
            .endInit();
        
    }, false);
    
} )();
