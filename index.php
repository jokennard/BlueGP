<?php
  require("connect.php");
  session_start();
  session_unset();
  session_destroy();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Main Page</title>
        <link rel="stylesheet" type="text/css" href="css/loader.css" />
        <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <script>
            window.addEventListener('error', function (error) {
                if (ChromeSamples && ChromeSamples.setStatus) {
                    console.error(error);
                    ChromeSamples.setStatus(error.message + ' (Your browser may not support this feature.)');
                    error.preventDefault();
                }
            });
        </script>
    </head>

    <body>
        <center>
            <form method="POST" action="ImagePassPage.php">
                <div>
                    <h1>BlueGP System</h1>
                    <h3>Log In Page</h3>
                </div>
                <table cellspacing="10">
                    <tbody>
                        <tr>
                            <td align="center">
                                <button id="getUserBtn" class="btn btn-primary" onClick="getDataFromPhone()">Get Username</button>
                                <input id="username" class="btn btn-primary" type="hidden" name="userName">
                                <input id="chesspass" type="hidden" name="chessPass">

                                <!-- Loader while getting username -->
                                <br>
                                <div id="loader" class="loaderCust row d-none" hidden></div>
                                <div id="user_get" class="row d-none" hidden>Username obtained.</div>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input class="btn btn-primary" id="login_button" type="hidden" name="nameSubmit"
                                    value="Log In">
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <!-- <br> -->
                                <input class="btn btn-info" type="button" name"SignUp" value="Sign Up"
                                    onclick="window.location = 'SignUpPage.php';">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    const searchParams = new URL(location).searchParams;
                    const inputs = Array.from(document.querySelectorAll('input[id]'));

                    inputs.forEach(input => {
                        if (searchParams.has(input.id)) {
                            if (input.type == 'checkbox') {
                                input.checked = searchParams.get(input.id);
                            } else {
                                input.value = searchParams.get(input.id);
                                input.blur();
                            }
                        }
                        if (input.type == 'checkbox') {
                            input.addEventListener('change', function(event) {
                                const newSearchParams = new URL(location).searchParams;
                                if (event.target.checked) {
                                    newSearchParams.set(input.id, event.target.checked);
                                } else {
                                    newSearchParams.delete(input.id);
                                }
                                history.replaceState({}, '', Array.from(newSearchParams).length ?
                                    location.pathname + '?' + newSearchParams : location.pathname);
                            });
                        } else {
                            input.addEventListener('input', function(event) {
                                const newSearchParams = new URL(location).searchParams;
                                if (event.target.value) {
                                    newSearchParams.set(input.id, event.target.value);
                                } else {
                                    newSearchParams.delete(input.id);
                                }
                                history.replaceState({}, '', Array.from(newSearchParams).length ?
                                    location.pathname + '?' + newSearchParams : location.pathname);
                            });
                        }
                    });
                });
            </script>

            <script>
                var ChromeSamples = {
                    log: function () {
                        var line = Array.prototype.slice.call(arguments).map(function (argument) {
                            return typeof argument === 'string' ? argument : JSON.stringify(argument);
                        }).join(' ');
                        // document.querySelector('#log').textContent += line + '\n';
                    },

                    clearLog: function () {
                        // document.querySelector('#log').textContent = '';
                    },

                    setStatus: function (status) {
                        // document.querySelector('#status').textContent = status;
                    },

                    setContent: function (newContent) {
                        var content = document.querySelector('#content');
                        while (content.hasChildNodes()) {
                            content.removeChild(content.lastChild);
                        }
                        content.appendChild(newContent);
                    }
                };
            </script>

            <!-- <h3>Live Output</h3> -->
            <div id="output" class="output">
                <div id="content"></div>
                <div id="status"></div>
                <pre id="log"></pre>
            </div>

            <script>
                function getUsernameButton() {
                    var characteristicValue;
                    navigator.bluetooth.requestDevice(
                        { filters: [{ services: ['battery_service'] }] }
                    )
                        .then(device => {
                            // Display loading animation (bootstrap)
                            document.getElementById("loader").className = "loaderCust row d-block m-3"; 
                            return device.gatt.connect();
                        })
                        .then(server => {
                            return server.getPrimaryService('battery_service');
                        })
                        .then(service => {
                            return service.getCharacteristic('battery_level');
                        })
                        .then(characteristicDescriptor => {
                            characteristicValue = characteristicDescriptor;
                            return characteristicValue.getDescriptors();
                        })
                        // Get username
                        .then(descriptors => {
                            let queue = Promise.resolve();
                            descriptors.forEach(descriptor => {
                                switch (descriptor.uuid) {

                                    case BluetoothUUID.getDescriptor('gatt.characteristic_user_description'):
                                        queue = queue.then(_ => descriptor.readValue()).then(value => {
                                            let decoder = new TextDecoder('utf-8');
                                            var username = decoder.decode(value);

                                            document.getElementById("username").value = username;
                                        });
                                        break;

                                    default:
                                }
                            });
                            return queue;
                        })
                        // Get chess instructions
                        .then(queue => {
                            return characteristicValue.readValue();
                        })
                        .then(value => {
                            let chessPass = value.getUint32(0);

                            // Display Message (bootstrap)
                            document.getElementById("user_get").className = "row d-block m-3"; 

                            // Display loading animation (bootstrap)
                            document.getElementById("loader").className = "row d-none"; 

                            document.getElementById("chesspass").value = chessPass;
                            document.getElementById("login_button").type = "submit";
                            document.getElementById("getUserBtn").disabled = true;
                            
                        })

                        .catch(error => {
                            if (!alert(error)) window.location.reload();
                        });
                }

            </script>

            <script>
                function getDataFromPhone() {
                    event.stopPropagation();
                    event.preventDefault();

                    if (isWebBluetoothEnabled()) {
                        ChromeSamples.clearLog();
                        getUsernameButton();
                    }
                }
            </script>

            <script>
                log = ChromeSamples.log;

                function isWebBluetoothEnabled() {
                    if (navigator.bluetooth) {
                        return true;
                    } else {
                        window.alert('Web Bluetooth API is not available.\n' +
                            'Please make sure the "Experimental Web Platform features" flag is enabled.');
                        return false;
                    }
                }
            </script>
        </center>
    </body>
</html>