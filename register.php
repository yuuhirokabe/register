<?php
require_once 'config.php';
if(isset($_SESSION['user_id']))
{
	header('location:index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="croppie.css" />
    <link rel="stylesheet" href="style.css" />
    <script src="croppie.js"></script>
    <style>
        #username_msg, #password_msg, #verify_password_msg {
            color: red;
        }
        #msg {
            color: green;
        }
        #register {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="imageContainer">
        <img id="croppieImg" class="uImg" src = "placeholder.png">
        <div id="addImg">+</div>
        <!-- <input type="submit" class="delett" value='+' name="addImg"> -->
        <!-- href="../croppie/index" -->
        <!-- TODO: nomainīt a uz input type submit un saglabāt ievietoto info starp bildes ievadi-->
    </div>
    <div id="croppieWindow" style="display:none;">
        <div id="myform">
            <input type="file" name="fileToUpload" id="fileToUpload" style="display:none;">
            <div id="vanilla-demo"><div id="warn" style="visibility: visible;">Upload an image first!</div></div>
            <label for="fileToUpload">Choose a file</label>
            <input id="doneButton" type="button" value="done" class="vanilla-result" disabled="">
            <div id="demo"></div>
        </div>
    </div>

    <input type="text" id="username" placeholder="Lietotājvārds" autocomplete="off"><span id="username_msg"></span><br>
    <input type="text" id="password" placeholder="Parole" autocomplete="off"><span id="password_msg"></span><br>
    <input type="text" id="verify_password" placeholder="Apstipriniet paroli" autocomplete="off"><span id="verify_password_msg"></span><br>
    <div id="register">Reģistrēties</div>
    <div id="msg"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>

        // Šeit tiek definēti Error tipi t.i. vietas, kur parādās errori
        const ErrorType = {

            // Error tips lietotājvārdu kļūdām
            USERNAME: "username",

            // Error tips paroļu kļūdām
            PASSWORD: "password",

            // Error tips atkārtotas paroles kļūdām
            VER_PASSWORD: "password_verify",

            // Ziņojuma tips veiksmīgiem ziņojumiem
            SUCCESS: "success"
        }

        // Funckija, kas iztīra error laukus
        const errorClear = () => {
            $('#username_msg').text('')
            $('#password_msg').text('')
            $('#verify_password_msg').text('')
        }

        // Funkcija, kas parāda <message> vietā <type> (ErrorType)
        const errorOut = (type, message) => {

            // Skatoties pēc tipa, izvadam ziņojumu/kļūdu
            switch(type){
                case ErrorType.USERNAME:
                    $('#username_msg').text(message)
                    break
                
                case ErrorType.PASSWORD:
                    $('#password_msg').text(message)
                    break
                
                case ErrorType.VER_PASSWORD:
                    $('#verify_password_msg').text(message)
                    break
                
                case ErrorType.SUCCESS:
                    $('#msg').text(message)
                    break
                
                default:
                    break
            }
        }

        // Sākuma funkcija - tiek palaista lapas ielādes beigās.
        $( document ).ready(function() {

            $('#register').click(registerUser);

            $('#addImg').click(function(){
                $('#croppieWindow').show();
            });

            $('#fileToUpload').on("input",function(){readFile(this);});
            $("#doneButton").click(cropImage);
        });

        // Funkcija, kas reģistrē lietotāju
        function registerUser(){
            var username = $('#username').val()
            var password = $('#password').val()
            var verify_password = $('#verify_password').val()

            // iztīram ziņojumus
            errorClear();

            // Pārbaudam ievadīto lietotājvārdu
            if(username.length < 5) {
                errorOut(ErrorType.USERNAME, "Lietotājvārdam jābūt vismaz 5 simbolus garam")
                return
            }

            // Pārbaudam ievadīto paroli
            if(password.length < 5) {
                errorOut(ErrorType.PASSWORD, "Parolei jābūt vismaz 5 simbolus garai")
                return
            }

            // Pārbaudam abu ievadīto paroļu līdzību
            if (verify_password != password) {
                errorOut(ErrorType.VER_PASSWORD, "Paroles nesakrīt")
                return
            }

            // Ja neviens no erroriem netika triggerots, sūtam pieprasījumu serverim
            $.post("server.php", {
                    action: "insert_user",
                    username: username,
                    password: password

                // Ja serveris atbild ar 200 (Success)
                }, (data) => {
                    errorOut(ErrorType.SUCCESS, data.message)
                    return;

                // Ja serveris atbild ar 404, 500 u.c. (Not found / Failed)
                }).fail((data) => {

                    // Skatamies kāda tipa error serveris atsūta, uz to arī reaģējam
                    switch(data.responseJSON.type){
                        case "username_error":
                            errorOut(ErrorType.USERNAME, data.responseJSON.message)
                            break
                        
                        case "password_error":
                            errorOut(ErrorType.PASSWORD, data.responseJSON.message)
                            break
                        
                        default:
                            // Ja nav definēts servera errors tad klientam izvadīsies atbildes dump konsolē (response dump)
                            console.log(data)
                            break
                    }
                    return;
                })
            }

        var imageBlob;
        var el = document.getElementById('vanilla-demo');
        var vanilla = new Croppie(el, {
            viewport: { width: 200, height: 200, type: 'circle'},
            boundary: { width: 250, height: 250 },
            showZoomer: false,
        });

        function cropImage(){
            vanilla.result({
                type: 'blob',
                //size: { width: 100, height: 100 },
                circle: false
                }).then(function(blob) {
                    imageBlob = blob;
                    var reader = new FileReader();
                    reader.readAsDataURL(blob); 
                    reader.onloadend = function() {
                        $("#croppieImg").attr("src",reader.result);
                    }
                    $('#croppieWindow').hide();
            });
        }

        function readFile(input) {
 			if (input.files && input.files[0]) {
	            var reader = new FileReader();
	            
	            reader.onload = function (e) {
					// $('.upload-demo').addClass('ready');
	            	vanilla.bind({
	            		url: e.target.result
	            	}).then(function(){
	            		console.log('jQuery bind complete');
                        $("#doneButton").prop("disabled",false);
                        $("#warn").css("visibility","hidden");
	            	});
	            }
	            reader.readAsDataURL(input.files[0]);
	        }
	        else {
		        console.log("Sorry - your browser doesn't support the FileReader API");
		    }
		}
    </script>
</body>
</html>