<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi Ingredients Production</title>
    <link rel="stylesheet" href="./mu_production.styles.css">
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    <script src="../../assets/notifier/index.var.js"></script>
    <script src="../../assets/js/production_handler_multiple.js" defer></script>
    <link rel="predb_connect" href="https://fonts.googleapis.com">
    <link rel="predb_connect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="si-production-container">
        <div class="loader-container hide-spinner" id="loader-container">
            <div class="dot-spinner">
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
                <div class="dot-spinner__dot"></div>
            </div>
        </div>

        <span class="container-headline">Multiple Ingredient Production</span>

        <div class="si-content-container">
            <div class="si-content-upper">
                <div class="raw-mat-selection" id="raw-ingredients-container">
                    <div class="input-field-cont">
                        <label class="si-prod-label">Raw Material</label>
                        <select class="si-prod-input" id="raw-ingredient-selector-0">
                            <option value="null" selected>Select a raw material</option>
                        </select>
                        <input type="text" id="raw-material-name-0" hidden>
                        <input type="text" id="raw-material-rem-stock-0" hidden>
                    </div>
                    <div class="input-field-cont">
                        <label class="si-prod-label">Input Amount (in Kilograms)</label>
                        <input class="si-prod-input" type="number" id="raw-material-inp-amount-0" oninput="amountValidator(0)" value="0">
                        <p class="rem-amount-info" id="rem-amount-info-0"></p>
                    </div>
                </div>
                <div class="production-option-btn-container">
                    <button class="add-ingredient-btn" id="add-ingredient-btn" onclick="addRawIngredientRow()">
                        Add Ingredient <i class="fa-solid fa-plus" style="margin: 3px 10px; font-size:1.3em;"></i>
                    </button>
                </div>
                <div class="error-displayer hide-error">
                    <i class="fa-solid fa-triangle-exclamation error-icon"></i>
                    <p class="error-info">Please select a lower or equal amount regarding to remaining stock.</p>
                </div>
            </div>
            <div class="si-content-lower">
                <div class="filer-layer" id="filter-layer"></div>
                <span class="container-sub-heading">Production Output Information</span>

                <!-- produced items lister-->
                 <table id="produced-items-tb">
                    <thead>
                        <tr>
                            <th>Item Barcode</th>
                            <th>Item Name</th>
                            <th>Weight (g)</th>
                            <th>Quantity</th>
                            <th>Our Price</th>
                            <th>Cost Price</th>
                            <th>Wholesale</th>
                            <th>MRP</th>
                            <th>Sup. Cus. Price</th>
                            <th style="width: 70px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="item_0">
                            <td>
                                <input type="text" id="item_barcode_0"/>
                            </td>
                            <td>
                                <input type="text" id="item_name_0"/>
                                <div class="item-name-suggester name_suggestor_0"></div>
                            </td>
                            <td>
                                <input type="number" id="item_weight_0"/>
                            </td>
                            <td>
                                <input type="number" id="item_qty_0"/>
                            </td>
                            <td>
                                <input type="number" id="item_price_0"/>
                            </td>
                            <td>
                                <input type="number" id="cost_price_0"/>
                            </td>
                            <td>
                                <input type="number" id="wholesale_price_0"/>
                            </td>
                            <td>
                                <input type="number" id="mr_price_0"/>
                            </td>
                            <td>
                                <input type="number" id="sc_price_0"/>
                            </td>
                            <td><button class="remove-item-btn" data-row="0">❌</button></td>
                        </tr>
                    </tbody>
                 </table>

                 <div class="production-option-btn-container">
                    <button class="add-item-btn" id="add-item-btn" onclick="itemRowGenerator()">
                        Add Item <i class="fa-solid fa-plus" style="margin: 3px 10px; font-size:1.3em;"></i>
                    </button>
                    <button class="finalize-btn" id="finalize-btn" onclick="executeProduction()">
                        Finalize <i class="fa-solid fa-check" style="margin: 3px 10px; font-size:1.3em;"></i>
                    </button>
                 </div>
            </div>
        </div>
    </div>
</body>
</html>