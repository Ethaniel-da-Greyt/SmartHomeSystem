<!DOCTYPE html>
<html>
<head>
    <title>Smart Home Power</title>
    <style>
        body { font-family: Arial; text-align: center; margin-top: 50px; }
        button { padding: 15px 30px; font-size: 18px; margin: 10px; cursor: pointer; }
        .status { font-size: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Smart Home Power Control</h1>
    <div class="status">
        Door: <strong><?= $status['door'] ?></strong><br>
        Relay: <strong><?= $status['relay'] ?></strong>
    </div>

    <a href="/smarthome/setPower/1"><button style="background-color:green; color:white;">TURN ON</button></a>
    <a href="/smarthome/setPower/0"><button style="background-color:red; color:white;">TURN OFF</button></a>
</body>
</html>
