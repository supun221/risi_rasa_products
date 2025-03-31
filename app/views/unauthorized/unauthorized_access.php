<?php 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eggland | Unauthorized Access</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
    <style>
        body{
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .access-pholder-image{
            width: 300px;
        }

        .access-title{
            font-family: "Roboto" , serif;
            font-size: 1.5em;
            font-weight: 600;
            color: #2980b9;
            margin: 10px;
        }

        .access-info-message{
            display: block;
            margin: 0 auto;
            text-align: center;
            width: 500px;
            font-size: .9em;
            color: grey;
            font-family: "Poppins" , serif;
        }

        .nav-home-btn{
            border: none;
            background-color: #2980b9;
            color: white;
            font-family: "Poppins", serif;
            outline: none;
            padding: 6px 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .nav-home-btn:hover{
            cursor: pointer;
        }

    </style>
</head>
<body>
    <img src="../../assets/images/access_denied.png" class="access-pholder-image"/>
    <span class="access-title">Access Restricted!</span>
    <p class="access-info-message">
        You do not have the required access permissions to access this page. please contact your administrator for detailed information.
    </p>
    <button class="nav-home-btn" onclick="navigateIndex()">
        Back to Main Page
    </button>
</body>
<script>
    const navigateIndex = () => {
        window.location.href = '../dashboard/index.php'
    }
</script>
</html>