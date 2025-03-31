<?php 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production | Main</title>
    <link rel="stylesheet" href="./index.styles.css">
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    <script src="../../assets/notifier/index.var.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="prod-index-container">
        <div class="prod-op-selection">
            <!--  -->
            <div class="prod-op-sel-tile">
                <div class="tile-option-cont">
                    <h5 class="tile-heading">Single-Ingredient Production</h5>
                    <p class="tile-info">This feature will allow you to produce item which has single ingredient.</p>
                    <button class="tile-nav-btn" id="single-ing-product">
                        Visit
                    </button>
                </div>
            </div>
            <div class="prod-op-sel-tile">
                <div class="tile-option-cont">
                    <h5 class="tile-heading">Multi-Ingredient Production</h5>
                    <p class="tile-info">This feature will allow you to produce items which have multiple ingredients.</p>
                    <button class="tile-nav-btn" id="multi-ing-product">
                        Visit
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    const singleProductionBtn = document.getElementById('single-ing-product')
    const multiProductionBtn = document.getElementById('multi-ing-product')
    
    singleProductionBtn.addEventListener('click', ()=>{
        window.location.href = "./si_production.php"
    })

    multiProductionBtn.addEventListener('click', ()=>{
        window.location.href = "./mu_production.php"
    })
</script>
</html>