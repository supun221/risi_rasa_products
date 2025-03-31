const showErrorMessage = (message) => {
    const errorDiv = document.getElementById("error-message");
    errorDiv.innerHTML = message;
    errorDiv.style.display = "block";

    setTimeout(() => {
        errorDiv.style.display = "none"; // Hide message after 3 seconds
    }, 3000);
};

const showSuccessMessage = (message) => {
    const messageDiv = document.getElementById("success-message");
    messageDiv.innerHTML = message;
    messageDiv.style.display = "block";

    setTimeout(() => {
        messageDiv.style.display = "none"; // Hide message after 3 seconds
    }, 3000);
};

const showErrorMessageStock = (message) => {
    const errorDiv = document.getElementById("error-message-stock");
    errorDiv.innerHTML = message;
    errorDiv.style.display = "block";

    setTimeout(() => {
        errorDiv.style.display = "none"; // Hide message after 3 seconds
    }, 4000);
};

const showSuccessMessageStock = (message) => {
    const messageDiv = document.getElementById("success-message-stock");
    messageDiv.innerHTML = message;
    messageDiv.style.display = "block";

    setTimeout(() => {
        messageDiv.style.display = "none"; // Hide message after 3 seconds
    }, 3000);
};


// Functions to display today's date and time
function updateTime() {
    const timeElement = document.getElementById('current-time');
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    timeElement.textContent = timeString;
}

function updateDate() {
    const dateElement = document.getElementById('current-date');
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = today.toLocaleDateString(undefined, options);
    dateElement.textContent = dateString;
}

// Update the time every second
setInterval(updateTime, 1000);
updateTime(); // Initialize immediately
updateDate();


//
window.onload = function () {
    document.getElementById("auto").checked = true;
};


//
const bulkCountInput = document.getElementById('stock-bulkcount');
const unitCountInput = document.getElementById('stock-unitcount');
const totalQuantityInput = document.getElementById('purchase-qty');

// Function to update the total quantity
function updateTotalQuantity() {
    const bulkCount = parseInt(bulkCountInput.value) || 0;
    const unitCount = parseInt(unitCountInput.value) || 0;
    const totalQuantity = bulkCount * unitCount;

    totalQuantityInput.value = totalQuantity; // Update the total quantity input
}

// Add event listeners to update total quantity when inputs change
bulkCountInput.addEventListener('input', updateTotalQuantity);
unitCountInput.addEventListener('input', updateTotalQuantity);


  //
  document.getElementById('discount-percent').addEventListener('input', calculateValues);
  document.getElementById('cost-price').addEventListener('input', calculateValues);
  document.getElementById('our-price').addEventListener('input', calculateValues);
  document.getElementById('purchase-qty').addEventListener('input', calculateValues);
  
  function calculateValues() {
      const costPrice = parseFloat(document.getElementById('cost-price').value) || 0;
      const ourPrice = parseFloat(document.getElementById('our-price').value) || 0;
      const discountPercent = parseFloat(document.getElementById('discount-percent').value) || 0;
      const purchaseQty = parseFloat(document.getElementById('purchase-qty').value) || 0;
  
      // Calculate discount value
      const discountValue = (costPrice * discountPercent) / 100;
      document.getElementById('discount-value').textContent = discountValue.toFixed(2);
  
      // Calculate profit percentage and value
      const profitValue = ourPrice - (costPrice - discountValue);
      const profitPercent = (profitValue / (costPrice - discountValue)) * 100;
  
      document.getElementById('unit-profit-value').textContent = (profitValue).toFixed(2);
      document.getElementById('profit-value').textContent = (profitValue*purchaseQty).toFixed(2);
      document.getElementById('profit-percentage').textContent = profitPercent.toFixed(2)+"%";
  
      // Calculate net amount
      const netAmount = (costPrice - discountValue) * purchaseQty;
      document.getElementById('net-amount').textContent = netAmount.toFixed(2);

      const unitCost = costPrice - (costPrice * discountPercent) / 100;
      document.getElementById('unit-cost').textContent = unitCost.toFixed(2);
  
  }
  calculateValues();
  

document.addEventListener("DOMContentLoaded", () => {
    const modeInputs = document.getElementsByName("mode");
    const itemCodeInput = document.getElementById("item-code");
    const saveButton = document.getElementById("save-product");
    const productList = document.getElementById("product-list");
    
    // Function to generate a 10-digit random number
    const generateRandomCode = () => {
        return Math.floor(100000000 + Math.random() * 900000000).toString();
    };
    
    // Function to fetch and validate the generated item code
    const fetchUniqueItemCode = async () => {
        let itemCode;
        let isUnique = false;
    
        while (!isUnique) {
            itemCode = generateRandomCode();
            const response = await fetch("check_product_item_code.php?item_code=" + itemCode);
            const data = await response.json();
            
            if (!data.exists) {
                isUnique = true;
            }
        }
    
        return itemCode;
    };
    
    // Fetch and display the auto-generated item code on page load
    const initializeAutoItemCode = async () => {
        if (document.querySelector('input[name="mode"]:checked').value === "auto") {
            const uniqueCode = await fetchUniqueItemCode();
            itemCodeInput.value = uniqueCode;
            itemCodeInput.readOnly = true;
        }
    };
    
    // Initialize the page with an auto-generated item code
    initializeAutoItemCode();
    
    // Handle mode change
    modeInputs.forEach(input => {
        input.addEventListener("change", async () => {
            if (input.value === "auto") {
                itemCodeInput.readOnly = true;
                const uniqueCode = await fetchUniqueItemCode();
                itemCodeInput.value = uniqueCode;
            } else {
                itemCodeInput.readOnly = false;
                itemCodeInput.value = "";
            }
        });
    });


  //save product
  saveButton.addEventListener("click", () => {
    const mode = document.querySelector('input[name="mode"]:checked').value;
    const category = document.getElementById("category-select").value;
    const productName = document.getElementById("product-name").value.trim();
    const sinhalaProductName = document.getElementById("sinhala-product-name").value.trim();
    const itemCode = document.getElementById("item-code").value;
    const productImage = document.getElementById("product-image").files[0];
    // const productPrice = document.getElementById("product-price").value;
    // const productWPrice = document.getElementById("product-wprice").value;
    // const productMRPrice = document.getElementById("product-mrprice").value;

    // const missingFields = [];
    // if (!category) missingFields.push("Category");
    // if (!productName) missingFields.push("Product Name");
    // if (!sinhalaProductName) missingFields.push("නිෂ්පාදනයේ නම");
    // if (!itemCode) missingFields.push("Barcode");
    // // if (!productPrice) missingFields.push("Our Price");
    // // if (!productWPrice) missingFields.push("Maximim Retail Price");
    // // if (!productMRPrice) missingFields.push("Wholesale Price");

    // if (missingFields.length > 0) {
    //     showErrorMessage(`⚠️ Please fill in the following fields: <br> <b>${missingFields.join(", ")}</b>`);
    //     return; // Stop execution if fields are missing
    // }else if(itemCode == 0){
    //     showErrorMessage(`⚠️ Barcode cannot be 0`);
    //     return; // Stop execution if fields are missing
    // }

    const payload = { category, productName, sinhalaProductName, itemCode, mode };

    if (productImage) {
        const reader = new FileReader();

        reader.onload = () => {
            payload.productImage = reader.result.split(",")[1]; // Add base64 image if available

            sendProductData(payload);
        };

        reader.readAsDataURL(productImage); // Convert image to base64
    } else {
        sendProductData(payload); // Send data without image
        
    }
  });

    function sendProductData(payload) {
        const mode = document.querySelector('input[name="mode"]:checked').value;
        fetch("save_product.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        })
        .then((response) => response.json()) // Always parse JSON response
        .then((data) => {
            if (data.success) {  // Only show success if 'success' is true
                showSuccessMessage("✅ Product saved successfully!");
                resetFormFields(); // Reset input fields
                loadProducts();

                if (mode === "auto") {
                    initializeAutoItemCode();
                }
            } else {
                showErrorMessage(`❌ ${data.message}`); // Show error message from PHP
            }
        })
        .catch((error) => {
            showErrorMessage(`❌ Error: ${error.message}`);
        });
    }


    const resetFormFields = () => {
            document.getElementById("category-select").value = "";
            document.getElementById("product-name").value = "";
            document.getElementById("sinhala-product-name").value = "";
            document.getElementById("item-code").value = "";
            document.getElementById("product-image").value = "";
    };

  //Load manual itemcodes
  function loadProducts() {
      fetch("fetch_manual_item_codes.php")
          .then(response => response.json())
          .then(data => {
              productList.innerHTML = data.products.map(product => `
                  <tr>
                      <td>${product.item_code}</td>
                      <td>${product.product_name}</td>
                     
                  </tr>
              `).join("");
          });
  }

  // Initial load
  loadProducts();


  //all products and delete products
  const allProductsButton = document.querySelector(".delete-button");
  const modal = document.getElementById("all-products-modal");
  const closeModal = document.querySelector(".all-products-modal-close");
  const modalProductList = document.getElementById("modal-product-list");

  allProductsButton.addEventListener("click", () => {
    function refreshTable() {
        fetch("get_products.php")
            .then(response => response.json())
            .then(data => {
                modalProductList.innerHTML = ""; // Clear the table content
                
                // Populate the table without grouping by category
                data.forEach(product => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${product.item_code}</td>
                        <td>${product.product_name}</td>
                        <td>${product.sinhala_name}</td>
                        <td>${product.category}</td>
                        <td><img src="${product.image_path}" alt="Product Image" style="width: 50px; height: 50px;"></td>
                    
                        <td>
                            <button class="delete-product" data-id="${product.item_code}">
                                <i class="fa fa-trash delete-icon"></i>
                            </button>
                        </td>
                    `;
                    modalProductList.appendChild(row);
                });

    
                document.querySelectorAll(".delete-product").forEach(button => {
                    button.addEventListener("click", (e) => {
                        const itemCode = e.target.getAttribute("data-id");
                        if (confirm("Are you sure you want to delete this product?")) {
                            fetch("delete_product.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ item_code: itemCode }),
                            })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message);
                                    e.target.closest("tr").remove();
                                    //loadProducts();
                                    //refreshTable();
                                });
                        }
                    });
                });
  
                modal.style.display = "block";
            })
            .catch(error => {
                alert("Failed to refresh product table: " + error.message);
            });
    }
    refreshTable();
              
    });

    closeModal.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });





    //category screen
    const addCategoryBtn = document.getElementById("add-category-btn");
    const categoryModal = document.getElementById("category-modal");
    const categoryTableBody = document.querySelector("#category-table tbody");
    const closeModalBtn = document.getElementById("close-modal");

    const saveCategoryBtn = document.getElementById("save-category");

    // Function to load categories
    function loadCategories() {
        fetch("get_categories.php")
            .then(response => response.json())
            .then(categories => {
                categoryTableBody.innerHTML = ""; // Clear table
                categories.forEach(category => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${category.category_name}</td>
                        <td>${category.key_code}</td>
                    `;
                    // Add click event to each row
                    row.addEventListener("click", () => {
                        selectRow(row, category);
                    });

                    categoryTableBody.appendChild(row);
                });
            });
    }

    // Show modal when clicking the "+" button
    addCategoryBtn.addEventListener("click", () => {
        categoryModal.style.display = "block";
        loadCategories();
    });

    // Close modal when clicking the close icon
    closeModalBtn.addEventListener("click", () => {
        categoryModal.style.display = "none"; // Hide modal

        document.getElementById("category-name").value = '';
        document.getElementById("category-key").value = '';
    });


    // Save new category
    saveCategoryBtn.addEventListener("click", () => {
        const name = document.getElementById("category-name").value;
        const key = document.getElementById("category-key").value;

        if (!name || !key) {
            alert("Both category name and key are required!");
            return;
        }

        fetch("save_category.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name, key }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Category added successfully");
                    loadCategories(); // Refresh table
                    loadCategorieslist()
                }

                 // Clear the input fields after saving
                document.getElementById("category-name").value = '';
                document.getElementById("category-key").value = '';
            });
    });

    // Fetch categories from the server
    function loadCategorieslist() {
    fetch("get_categories.php")
        .then(response => response.json())
        .then(data => {
            const categorySelect = document.getElementById("category-select");
            categorySelect.innerHTML = ''; // Clear existing options

            // Create a default option
            const defaultOption = document.createElement("option");
            defaultOption.text = "Select category";
            defaultOption.value = "";
            categorySelect.appendChild(defaultOption);

            // Populate the dropdown with categories
            data.forEach(category => {
                const option = document.createElement("option");
                option.value = category.category_name; // Assuming 'key' is the unique identifier
                option.text = category.category_name; // Assuming 'name' is the category name
                categorySelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error fetching categories:", error);
        });
    }
    loadCategorieslist();


    //update category
    const updateCategoryModal = document.getElementById("update-category-modal");
    const updateCategoryButton = document.getElementById("update-category");
    const closeUpdateModal = document.getElementById("close-update-modal");
    const confirmUpdateButton = document.getElementById("confirm-update-category");

    let selectedCategory = null; // Store selected category key for reference

    // Display Update Category Modal
    updateCategoryButton.addEventListener("click", () => {
        const table = document.getElementById("category-table");
        const selectedRow = table.querySelector("tr.selected");

        if (!selectedRow) {
            alert("Please select a category to update.");
            return;
        }

        // Extract existing data
        const existingName = selectedRow.cells[0].innerText;
        const existingKey = selectedRow.cells[1].innerText;

        // Fill the modal fields
        document.getElementById("existing-category-name").value = existingName;
        document.getElementById("existing-category-key").value = existingKey;

        document.getElementById("update-category-name").value = existingName;
        document.getElementById("update-category-key").value = existingKey;

        // Store the original key for backend reference
        selectedCategory = existingKey;

        // Show the modal
        updateCategoryModal.style.display = "block";
    });

    // Close Update Modal
    closeUpdateModal.addEventListener("click", () => {
        updateCategoryModal.style.display = "none";
        // Clear the input fields after saving
        document.getElementById("update-category-name").value = '';
        document.getElementById("update-category-key").value = '';
    });

    // Confirm Update Button
    confirmUpdateButton.addEventListener("click", () => {
        const newName = document.getElementById("update-category-name").value;
        const newKey = document.getElementById("update-category-key").value;

        if (!newName || !newKey) {
            alert("Please enter both New Name and New Key.");
            return;
        }

        // Send AJAX request to PHP backend
        fetch("update_category.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                oldKey: selectedCategory,
                newName: newName,
                newKey: newKey
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Update the table with new values
                const table = document.getElementById("category-table");
                const selectedRow = table.querySelector("tr.selected");
                selectedRow.cells[0].innerText = newName;
                selectedRow.cells[1].innerText = newKey;

                // Clear the input fields after saving
                document.getElementById("update-category-name").value = '';
                document.getElementById("update-category-key").value = '';

                updateCategoryModal.style.display = "none";
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Highlight selected row
    const table = document.getElementById("category-table");
    table.addEventListener("click", (e) => {
        const rows = table.getElementsByTagName("tr");
        for (const row of rows) {
            row.classList.remove("selected");
        }
        const clickedRow = e.target.parentElement;
        if (e.target.tagName === "TH" || e.target.closest("thead")) {
            clickedRow.classList.remove("selected");
        }
        else if(clickedRow.tagName === "TR") {
            clickedRow.classList.add("selected");
        }
    });



    //unit screen
    const addUnitBtn = document.getElementById("add-unit-btn");
    const unitModal = document.getElementById("unit-modal");
    const unitTableBody = document.querySelector("#unit-table tbody");
    const closeUnitModalBtn = document.getElementById("close-unit-modal");

    const saveUnitBtn = document.getElementById("save-unit");


    // Function to load units
    function loadUnits() {
        fetch("get_units.php")
            .then(response => response.json())
            .then(units => {
                unitTableBody.innerHTML = ""; // Clear table
                units.forEach(unit => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${unit.unit_name}</td>
                    `;
                    // Add click event to each row
                    row.addEventListener("click", () => {
                        selectRow(row, unit);
                    });

                    unitTableBody.appendChild(row);
                });
            });
    }

    // Show modal when clicking the "+" button
    addUnitBtn.addEventListener("click", () => {
        unitModal.style.display = "block";
        loadUnits();
    });

    // Close modal when clicking the close icon
    closeUnitModalBtn.addEventListener("click", () => {
        unitModal.style.display = "none"; // Hide modal

        document.getElementById("unit-name").value = '';
    });


    // Save new unit
    saveUnitBtn.addEventListener("click", () => {
        const name = document.getElementById("unit-name").value;

        if (!name) {
            alert("Unit name is required!");
            return;
        }

        fetch("save_unit.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name }),
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                loadUnits(); // Refresh table
                loadUnitsList()

                 // Clear the input fields after saving
                document.getElementById("unit-name").value = '';
            });
    });

    // Fetch units from the server
    function loadUnitsList() {
    fetch("get_units.php")
        .then(response => response.json())
        .then(data => {
            const unitSelect = document.getElementById("unit-select");
            unitSelect.innerHTML = ''; // Clear existing options

            // Create a default option
            const defaultOption = document.createElement("option");
            defaultOption.text = "unit";
            defaultOption.value = "";
            unitSelect.appendChild(defaultOption);

            // Populate the dropdown with units
            data.forEach(unit => {
                const option = document.createElement("option");
                option.value = unit.unit_name; // Assuming 'key' is the unique identifier
                option.text = unit.unit_name; // Assuming 'name' is the unit name
                unitSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error fetching units:", error);
        });
    }
    loadUnitsList();


    //update unit
    const updateUnitModal = document.getElementById("update-unit-modal");
    const updateUnitButton = document.getElementById("update-unit");
    const closeUnitUpdateModal = document.getElementById("close-unit-update-modal");
    const confirmUnitUpdateButton = document.getElementById("confirm-update-unit");

    let selectedUnit = null; // Store selected unit key for reference

    // Display Update Unit Modal
    updateUnitButton.addEventListener("click", () => {
        const table = document.getElementById("unit-table");
        const selectedRow = table.querySelector("tr.selected");

        if (!selectedRow) {
            alert("Please select a unit to update.");
            return;
        }

        // Extract existing data
        const existingName = selectedRow.cells[0].innerText;

        // Fill the modal fields
        document.getElementById("existing-unit-name").value = existingName;

        // Store the original key for backend reference
        selectedUnit = existingName;

        // Show the modal
        updateUnitModal.style.display = "block";
    });

    // Close Update Modal
    closeUnitUpdateModal.addEventListener("click", () => {
        updateUnitModal.style.display = "none";
        // Clear the input fields after saving
        document.getElementById("update-unit-name").value = '';
    });

    // Confirm Update Button
    confirmUnitUpdateButton.addEventListener("click", () => {
        const newName = document.getElementById("update-unit-name").value;

        if (!newName) {
            alert("Please enter the New Name.");
            return;
        }

        // Send AJAX request to PHP backend
        fetch("update_unit.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                oldName: selectedUnit,
                newName: newName,
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Update the table with new values
                const table = document.getElementById("unit-table");
                const selectedRow = table.querySelector("tr.selected");
                selectedRow.cells[0].innerText = newName;

                // Clear the input fields after saving
                document.getElementById("update-unit-name").value = '';

                updateUnitModal.style.display = "none";
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Highlight selected row
    const unit_table = document.getElementById("unit-table");
    unit_table.addEventListener("click", (e) => {
        const rows = unit_table.getElementsByTagName("tr");
        for (const row of rows) {
            row.classList.remove("selected");
        }
        const clickedRow = e.target.parentElement;
        if (e.target.tagName === "TH" || e.target.closest("thead")) {
            clickedRow.classList.remove("selected");
        }
        else if(clickedRow.tagName === "TR") {
            clickedRow.classList.add("selected");
        }
    });

  
    
});


//supplier
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("supplier-modal");
    const openModalBtn = document.getElementById("add-supplier-btn");
    const closeModalBtn = document.getElementById("close-supplier-modal");
    const saveSupplierBtn = document.getElementById("save-supplier");
    const tableBody = document.querySelector("#supplier-table tbody");

    // Open Modal
    openModalBtn.addEventListener("click", () => {
        modal.style.display = "flex";
        loadSuppliers(); // Load supplier details
        document.getElementById("supplier-form").reset();
    });

    // Close Modal
    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";      
    });

    // Save Supplier
    saveSupplierBtn.addEventListener("click", () => {
        const name = document.getElementById("supplier-name").value;
        const telephone = document.getElementById("telephone-no").value;
        const company = document.getElementById("company").value;

        if (!name || !company) {
            alert("name and company are required!");
            return;
        }

        fetch("save_supplier.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name, telephone, company }),
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                loadSuppliers(); // Refresh the table
                loadSuppliersList()
                document.getElementById("supplier-form").reset();
            })
            .catch(error => console.error("Error:", error));
    });

    // Load Suppliers into Table
    function loadSuppliers() {
        fetch("get_suppliers.php")
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ""; // Clear table
                data.forEach(supplier => {
                    const row = `
                        <tr>
                            <td>${supplier.supplier_id}</td>
                            <td>${supplier.supplier_name}</td>
                            <td>${supplier.telephone_no}</td>
                            <td>${supplier.company}</td>
                            <td>${supplier.credit_balance || "N/A"}</td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML("beforeend", row);
                });
            })
            .catch(error => console.error("Error loading suppliers:", error));
    }


    // Update Supplier
    const updateSupplierModal = document.getElementById("update-supplier-modal");
    const updateSupplierButton = document.getElementById("update-supplier");
    const closeSupplierUpdateModal = document.getElementById("close-supplier-update-modal");
    const confirmSupplierUpdateButton = document.getElementById("confirm-update-supplier");
    const selectSupplierButton = document.getElementById("select-supplier");

    let selectedSupplier = null; // Store selected supplier key for reference

    // Display Update Supplier Modal
    updateSupplierButton.addEventListener("click", () => {

        // Clear the input fields
        

        const table = document.getElementById("supplier-table");
        const selectedRow = table.querySelector("tr.selected");

        if (!selectedRow) {
            alert("Please select a supplier to update.");
            return;
        }

        // Extract existing data
        const supplier_Id = selectedRow.cells[0].innerText;
        const existingSupplierName = selectedRow.cells[1].innerText;
        const existingSupplierPhone = selectedRow.cells[2].innerText;
        const existingSupplierCompany = selectedRow.cells[3].innerText;

        
        // Fill the modal fields
        document.getElementById("existing-supplier-id").value = supplier_Id;
        document.getElementById("existing-supplier-name").value = existingSupplierName;
        document.getElementById("existing-supplier-company").value = existingSupplierCompany;
        document.getElementById("existing-supplier-phone").value = existingSupplierPhone;

        document.getElementById("update-supplier-name").value = existingSupplierName;
        document.getElementById("update-supplier-company").value = existingSupplierCompany;
        document.getElementById("update-supplier-phone").value = existingSupplierPhone;

        // Store the original key for backend reference
        selectedSupplier = supplier_Id;

        // Show the modal
        updateSupplierModal.style.display = "block";
    });

    // Close Update Modal
    closeSupplierUpdateModal.addEventListener("click", () => {
        updateSupplierModal.style.display = "none";

    });

    // Confirm Update Button
    confirmSupplierUpdateButton.addEventListener("click", () => {
        const newName = document.getElementById("update-supplier-name").value;
        const newCompany = document.getElementById("update-supplier-company").value;
        const newPhone = document.getElementById("update-supplier-phone").value;

        if (!newName) {
            alert("Please enter the New Name.");
            return;
        }

        // Send AJAX request to PHP backend
        fetch("update_supplier.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                supplierId: selectedSupplier,
                newName: newName,
                newCompany: newCompany,
                newPhone: newPhone
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Update the table with new values
                const table = document.getElementById("supplier-table");
                const selectedRow = table.querySelector("tr.selected");
                selectedRow.cells[1].innerText = newName;
                selectedRow.cells[2].innerText = newPhone;
                selectedRow.cells[3].innerText = newCompany ;

                // Clear the input fields after saving
                document.getElementById("update-supplier-name").value = '';
                document.getElementById("update-supplier-company").value = '';
                document.getElementById("update-supplier-phone").value = '';

                updateSupplierModal.style.display = "none";
                loadSuppliersList()
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Highlight selected row
    const supplier_table = document.getElementById("supplier-table");
    supplier_table.addEventListener("click", (e) => {
        const rows = supplier_table.getElementsByTagName("tr");
        for (const row of rows) {
            row.classList.remove("selected");
        }
        const clickedRow = e.target.parentElement;
        if (e.target.tagName === "TH" || e.target.closest("thead")) {
            clickedRow.classList.remove("selected");
        }
        else if(clickedRow.tagName === "TR") {
            clickedRow.classList.add("selected");
        }
    });


    selectSupplierButton.addEventListener("click", () => {
        const table = document.getElementById("supplier-table");
        const selectedRow = table.querySelector("tr.selected");

        if (!selectedRow) {
            alert("Please click on a supplier to select.");
            return;
        }

        const supplier_Id = selectedRow.cells[0].innerText;
        const supplierName = selectedRow.cells[1].innerText;

        document.getElementById("supplier-select").value = supplier_Id;
        document.getElementById("supplier-id").value = supplier_Id;


        modal.style.display = "none";  
    })


    // Fetch suppliers from the server
    function loadSuppliersList() {
        fetch("get_suppliers.php")
            .then(response => response.json())
            .then(data => {
                const supplierSelect = document.getElementById("supplier-select");
                supplierSelect.innerHTML = ''; // Clear existing options
                document.getElementById("supplier-id").value = "";

                // Create a default option
                const defaultOption = document.createElement("option");
                defaultOption.text = "Select supplier";
                defaultOption.value = "";
                supplierSelect.appendChild(defaultOption);

                // Populate the dropdown with suppliers
                data.forEach(supplier => {
                    const option = document.createElement("option");
                    option.value = supplier.supplier_id; // Assuming 'supplier_name' is the unique identifier
                    option.text = supplier.supplier_name; // Assuming 'supplier_name' is the supplier name
                    supplierSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error("Error fetching suppliers:", error);
            });
    }
    loadSuppliersList();

});

// Set the Supplier ID when a supplier is selected
// function setSupplierId() {
//     const supplierSelect = document.getElementById("supplier-select");
//     const supplierIdInput = document.getElementById("supplier-id");
    
//     // Assign the selected supplier's ID to the input field
//     supplierIdInput.value = supplierSelect.value || ""; // Clear the field if no supplier is selected
    
//     if (supplierSelect.value) {
//         fetch(`get_supplier_credit_balance.php?supplierId=${supplierSelect.value}`)
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     document.querySelector('.footer-balance-value').textContent = `: ${data.credit_balance}`;
//                 } else {
//                     document.querySelector('.footer-balance-value').textContent = ': 0.00';
//                 }
//             })
//             .catch(error => {
//                 console.error('Error fetching credit balance:', error);
//                 document.querySelector('.footer-balance-value').textContent = ': 0.00';
//             });
//     } else {
//         document.querySelector('.footer-balance-value').textContent = ': 0.00';
//     }
// }
function setSupplierId() {
    const supplierSelect = document.getElementById("supplier-select");
    const supplierIdInput = document.getElementById("supplier-id");
    
    // Assign the selected supplier's ID to the input field
    supplierIdInput.value = supplierSelect.value || ""; // Clear the field if no supplier is selected
    
    if (supplierSelect.value) {
        // Fetch Supplier Credit Balance
        fetch(`get_supplier_credit_balance.php?supplierId=${supplierSelect.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.footer-balance-value').textContent = `: ${data.credit_balance}`;
                } else {
                    document.querySelector('.footer-balance-value').textContent = ': 0.00';
                }
            })
            .catch(error => {
                console.error('Error fetching credit balance:', error);
                document.querySelector('.footer-balance-value').textContent = ': 0.00';
            });

        // Fetch Supplier Return Amount
        fetch(`get_supplier_return.php?supplier_id=${supplierSelect.value}`)
            .then(response => response.json())
            .then(data => {
                let returnAmount = parseFloat(data.total_amount) || 0;
                document.getElementById("footer-return-amount-value").textContent = `: ${returnAmount.toFixed(2)}`;
            })
            .catch(error => {
                console.error("Error fetching return amount:", error);
                document.getElementById("footer-return-amount-value").textContent = ": 0.00";
            });
    } else {
        document.querySelector('.footer-balance-value').textContent = ': 0.00';
        document.getElementById("footer-return-amount-value").textContent = ": 0.00";
    }
}


function updateInvoiceTotal() {
    const netAmount = document.getElementById('net-amount').textContent;
    const invoiceTotalElement = document.getElementById('footer-total-amount-value'); 
    invoiceTotalElement.textContent = `: ${netAmount}`;  // Set the New Invoice Total with net-amount
    updateSupplierPaymentAmount();
}

function updateSupplierPaymentAmount() {
    const totalAmount = parseFloat(document.getElementById('footer-total-amount-value').textContent.replace(':', '').trim()) || 0;
    const returnAmount = parseFloat(document.getElementById('footer-return-amount-value').textContent.replace(':', '').trim()) || 0;
    const paymentAmountElement = document.getElementById('footer-supplier-payment-amount-value');
    const paymentAmount = totalAmount - returnAmount;
    paymentAmountElement.textContent = `: ${paymentAmount.toFixed(2)}`;  // Update Supplier Payment Amount
}

// Observe changes to 'net-amount' content
const observer = new MutationObserver(() => {
    updateInvoiceTotal();  // Automatically update when net-amount changes
});
observer.observe(document.getElementById('net-amount'), { childList: true });


//stock
document.getElementById("save-stock").addEventListener("click", function(event) {
    event.preventDefault(); // Prevent form submission until validation

    // const costPrice = parseFloat(document.getElementById("cost-price").value) || 0;
    // const discountPercent = parseFloat(document.getElementById("discount-percent").value) || 0;
    // const maxRetailPrice = parseFloat(document.getElementById("max-retail-price").value) || 0;

    const missingFields = [];

    // Get input values and check for missing fields
    const supplierId = document.getElementById("supplier-id").value.trim();
    if (!supplierId) missingFields.push("Supplier");

    const itemCode = document.getElementById("stock-itemcode").value.trim();
    if (!itemCode) missingFields.push("Barcode");

    const productname = document.getElementById("stock-productname").value.trim();
    if (!productname) missingFields.push("Product Name");

    const purchaseQty = document.getElementById("purchase-qty").value.trim();
    if (!purchaseQty || purchaseQty <= 0) missingFields.push("Purchase Quantity");

    const costPrice2 = parseFloat(document.getElementById("cost-price").value) || 0;
    if (!costPrice2 || costPrice2 <= 0) missingFields.push("Cost Price");

    // const wholesalePrice = parseFloat(document.getElementById("wholesale-price").value) || 0;
    // if (!wholesalePrice || wholesalePrice <= 0) missingFields.push("Wholesale Price");

    // const maxRetailPrice2 = parseFloat(document.getElementById("max-retail-price").value) || 0;
    // if (!maxRetailPrice2 || maxRetailPrice2 <= 0) missingFields.push("Maximum Retail Price");

    // const superCustomerPrice = parseFloat(document.getElementById("super-customer-genuine-price").value) || 0;
    // if (!superCustomerPrice || superCustomerPrice <= 0) missingFields.push("Super Customer Price");

    // const ourPrice = parseFloat(document.getElementById("our-price").value) || 0;
    // if (!ourPrice || ourPrice <= 0) missingFields.push("Our Price");


    // Show error message if any required field is missing
    if (missingFields.length > 0) {
        showErrorMessageStock(`⚠️ Please fill in the following fields: <br> <b>${missingFields.join(", ")}</b>`);
        return;
    }

    // Calculate Unit Cost Price after discount
    // const unitCostPrice = costPrice - (costPrice * discountPercent / 100);

    // if (maxRetailPrice <= unitCostPrice) {
    //     showErrorMessageStock("⚠️ Maximum retail price must be higher than the unit cost price..");
    //     return;
    // }else if (wholesalePrice <= unitCostPrice) {
    //     showErrorMessageStock("⚠️ Wholesale price must be higher than the unit cost price..");
    //     return;
    // }else if (superCustomerPrice <= unitCostPrice) {
    //     showErrorMessageStock("⚠️ Super customer price must be higher than the unit cost price..");
    //     return;
    // }else if (ourPrice <= unitCostPrice) {
    //     showErrorMessageStock("⚠️ Our price must be higher than the unit cost price..");
    //     return;
    // }

    const formData = new FormData(document.getElementById("stock-form"));

    // Set unchecked checkboxes as false explicitly
    const checkboxes = ["free-item", "gift", "voucher"];
    checkboxes.forEach(name => {
        if (!formData.has(name)) {
            formData.append(name, "false");
        } else {
            formData.append(name, "true");
        }
    });

    fetch("save_stock.php", {
        method: "POST",
        body: formData
    }).then(response => response.text())
      .then(data => {
        if (data.includes("Stock saved successfully")) {
            showSuccessMessageStock("✅ Stock saved successfully!");
            resetStockFormFields();
        } else {
            showErrorMessageStock("❌ Error saving stock: " + data);
        }
        console.log(data);
    }).catch(error => {
        showErrorMessageStock("❌ Network error. Please try again.");
        console.error("Error:", error);
    });
});

const resetStockFormFields = () => {
    const form = document.getElementById("stock-form");

    if (form) {
        // Reset text, number, and date input fields
        form.querySelectorAll("input[type='text'], input[type='number'], input[type='date']").forEach(input => {
            input.value = input.type === "number" ? 1 : "";
        });

        // Uncheck all checkboxes
        form.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
            checkbox.checked = false;
        });

        // Reset all select elements to their first option
        form.querySelectorAll("select").forEach(select => {
            select.selectedIndex = 0;
        });

        // Reset displayed values
        document.getElementById("discount-value").textContent = "0.00";
        document.getElementById("net-amount").textContent = "0.00";
        document.getElementById("profit-percentage").textContent = "0.00%";
        document.getElementById("unit-profit-value").textContent = "0.00";
        document.getElementById("profit-value").textContent = "0.00";
        document.getElementById("unit-cost").textContent = "0.00";
    }
};


// document.getElementById("print-barcode").addEventListener("click", function () {
//     const barcodeValue = document.getElementById("itemcode").value;
//     const price= document.getElementById("our-price").value;

//     if (!barcodeValue) {
//         alert("Enter a barcode value!");
//         return;
//     }

//     // Generate Barcode using JsBarcode
//     JsBarcode("#barcode", barcodeValue, price, {
//         format: "CODE128",
//         displayValue: true,
//         lineColor: "#000",
//         width: 2,
//         height: 50
//     });

//     // Display the barcode
//     document.getElementById("barcode-container").style.display = "block";

//     // // Optionally, save to server
//     // const formData = new FormData(document.getElementById("stock-form"));
//     // fetch("save_stock.php", {
//     //     method: "POST",
//     //     body: formData
//     // })
//     // .then(response => response.text())
//     // .then(data => {
//     //     console.log(data);
//     // });
// });



const stockSearchInput = document.getElementById("stock-search-input");
const stockSearchBySelect = document.getElementById("stock-search-by");
const stockTableBody = document.querySelector("#print-barcode-stock-table tbody");

// Open the modal on "Print Barcode" button click
document.getElementById("print-barcode-show-stock").addEventListener("click", () => {
    fetchAllStocks(); // Load data dynamically
    document.getElementById("stock-modal").style.display = "flex";
    stockSearchInput.value=""
    stockSearchBySelect.value="all"
});

// Close the modal
document.getElementById("stock-close-modal").addEventListener("click", () => {
    document.getElementById("stock-modal").style.display = "none";
});

// Mock stock data (fetch all stock data once)
let allStocks = [];

// Fetch stock data from the backend (populate the modal with data initially)
function fetchAllStocks() {
    fetch("get_stock_data.php")
        .then(response => response.json())
        .then(data => {
            allStocks = data; // Store all stocks in memory
            renderTableRows(allStocks); // Render all stocks initially
        })
        .catch(error => console.error("Error fetching stock data:", error));
}

// Render table rows dynamically
function renderTableRows(data) {
    stockTableBody.innerHTML = ""; // Clear existing rows
    data.forEach(stock => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${stock.itemcode}</td>
            <td>${stock.stock_id}</td>
            <td>${stock.barcode}</td>
            <td>${stock.product_name}</td>
            <td>${stock.cost_price}</td>
            <td>${stock.purchase_qty}</td>
            <td>${stock.wholesale_price}</td>
            <td>${stock.max_retail_price}</td>
        `;
        stockTableBody.appendChild(row);
    });
}

// Filter stocks dynamically
function filterStocks() {
    const searchBy = stockSearchBySelect.value; // Selected filter type
    const keyword = stockSearchInput.value.toLowerCase(); // Input text

    const filteredStocks = allStocks.filter(stock => {
        if (searchBy === "all") {
            // Match any field
            return (
                stock.product_id.toString().toLowerCase().includes(keyword) ||
                stock.stock_id.toString().toLowerCase().includes(keyword) ||
                stock.product_name.toLowerCase().includes(keyword)
            );
        } else if (searchBy === "stock_id") {
            // Match stock ID
            return stock.stock_id.toString().toLowerCase().includes(keyword);
        } else if (searchBy === "product_id") {
            // Match product ID
            return stock.itemcode.toString().toLowerCase().includes(keyword);
        } else if (searchBy === "product_name") {
            // Match product name
            return stock.product_name.toLowerCase().includes(keyword);
        }
        return false; // Default case (shouldn't happen)
    });

    renderTableRows(filteredStocks); // Update table with filtered data
}

// Event listeners
stockSearchInput.addEventListener("input", filterStocks); // Filter on input change
stockSearchBySelect.addEventListener("change", filterStocks); // Filter on filter type change



document.addEventListener("DOMContentLoaded", () => {
    const barcodeModal = document.getElementById("barcode-modal");
    const barcodeCloseBtn = document.getElementById("barcode-close-modal");
    const printBarcodeBtn = document.getElementById("stock-print-barcode-btn");

    const productIdInput = document.getElementById("productID");
    const stockIdInput = document.getElementById("stockID");
    const productNameInput = document.getElementById("productName");
    const printQtyInput = document.getElementById("printQty");
    const printPriceInput = document.getElementById("printPrice");
    const barcodeInput = document.getElementById("barcodeID");

    let selectedstock = null;

    // Close Modals
    barcodeCloseBtn.addEventListener("click", () => (barcodeModal.style.display = "none"));

    const stock_table = document.getElementById("print-barcode-stock-table");
    stock_table.addEventListener("click", (e) => {
        // Check if the clicked element is in the table header

    
        const rows = stock_table.getElementsByTagName("tr");
        for (const row of rows) {
            row.classList.remove("selected");
        }
    
        const clickedRow = e.target.parentElement;
        if (e.target.tagName === "TH" || e.target.closest("thead")) {
            clickedRow.classList.remove("selected");
        }
        else if(clickedRow.tagName === "TR") {
            clickedRow.classList.add("selected");
        }
    });
    

    // Open Barcode Modal
    printBarcodeBtn.addEventListener("click", async () => {
        document.getElementById("barcodeForm").reset();
        // Logic to open modal with pre-filled values from the table
        const table = document.getElementById("print-barcode-stock-table");
        const selectedRow = table.querySelector("tr.selected");
        if (selectedRow) {
            const cells = selectedRow.children;
            productIdInput.value = cells[0].textContent;
            stockIdInput.value = cells[1].textContent;
            barcodeInput.value = cells[2].textContent;
            productNameInput.value = cells[3].textContent;
            printQtyInput.value = cells[5].textContent;
            printPriceInput.value = cells[7].textContent;
            barcodeModal.style.display = "flex";

            // if (!isValidSriLankaEAN13(barcodeInput.value)) {
            //     const barcode = await setUniqueBarcode();
            //     document.getElementById("barcodeID").value = barcode;
            // }

            generateBarcode();
        } else {
            alert("Please select a stock!");
        }
    });

    // Function to validate a Sri Lanka EAN-13 barcode number
    // function isValidSriLankaEAN13(barcode) {
    //     if (!/^\d{13}$/.test(barcode)) {
    //         return false;  // Must be exactly 13 digits
    //     }
    //     if (!barcode.startsWith('479')) {
    //         return false;  // Must start with '479' for Sri Lanka
    //     }
    //     let sum = 0;
    //     for (let i = 0; i < 12; i++) {
    //         const digit = parseInt(barcode[i], 10);
    //         sum += (i % 2 === 0) ? digit : digit * 3;
    //     }
    //     const checksum = (10 - (sum % 10)) % 10;
    //     return checksum === parseInt(barcode[12], 10);  // Validate checksum
    // }

    // Function to generate a valid Sri Lankan EAN-13 barcode number
    // function generateSriLankaEAN13() {
    //     let baseNumber = '479'; // Sri Lanka prefix
    //     for (let i = 0; i < 9; i++) {
    //         baseNumber += Math.floor(Math.random() * 10); // Generate next 9 random digits
    //     }
    //     const checksum = calculateEAN13Checksum(baseNumber);
    //     return baseNumber + checksum; // Return full barcode with checksum
    // }

    // Function to calculate checksum for EAN-13
    // function calculateEAN13Checksum(digits) {
    //     let sum = 0;
    //     for (let i = 0; i < 12; i++) {
    //         sum += parseInt(digits[i]) * (i % 2 === 0 ? 1 : 3);
    //     }
    //     return (10 - (sum % 10)) % 10;
    // }

    // Check if a barcode is unique
    // async function isBarcodeUnique(barcode) {
    //     const response = await fetch(`check_barcode.php?barcode=${barcode}`);
    //     const data = await response.json();
    //     return !data.exists;  // Returns true if barcode does not exist
    // }

    // Generate unique barcode and set it in the input field
    // async function setUniqueBarcode() {
    //     let barcode;
    //     do {
    //         barcode = generateSriLankaEAN13();
    //     } while (!(await isBarcodeUnique(barcode)));  // Loop until a unique barcode is found
    //     return barcode;
    // }

    // Barcode Generation with Company Name and Price
    function generateBarcode() {
        const companyName = "SPICES"; // Replace with your actual company name
        const address = "Jaffna."; 
        const product = productNameInput.value
        const price = document.getElementById("printPrice").value;
        const mfd = document.getElementById("mfDate").value;
        const ed = document.getElementById("expDate").value;
        const barcode = document.getElementById("barcodeID").value || "";

        const mainCanvas = document.getElementById("barcodeCanvas");
        const ctx = mainCanvas.getContext("2d");

        // Clear the canvas
        ctx.clearRect(0, 0, mainCanvas.width, mainCanvas.height);

        const tempCanvas = document.createElement("canvas");
        tempCanvas.width = 170; // Adjust to match your canvas width
        tempCanvas.height = 50; // Adjust for barcode height

        JsBarcode(tempCanvas, barcode, {
            format: "CODE128",
            displayValue: true,
            lineColor: "#000",
            width: 1.7,
            height: 50,
            fontSize: 14,
            textMargin: 0,
            font:"Serif",
        });

        // Check if productIdInput.value has exactly 5 digits
        // let productId = productIdInput.value;
        // if (productId.length === 5 && /^\d{5}$/.test(productId)) {
        //     productId = '0' + productId;
        // }

        // let stockId = stockIdInput.value;
        // if (stockId.length === 5 && /^\d{5}$/.test(stockId)) {
        //     stockId = '0' + stockId;
        // }


        // Generate the barcode
        // JsBarcode(tempCanvas, stockId+ " " + productId, {
        //     format: "CODE128",
        //     displayValue: true,
        //     lineColor: "#000",
        //     width: 1.7,
        //     height: 50,
        //     fontSize: 14,
        //     textMargin: 0,
        //     font:"Serif",
        // });

        // Draw the barcode from the temporary canvas onto the main canvas
        const barcodeY = 15; // Y-coordinate for the barcode
        ctx.drawImage(tempCanvas, (mainCanvas.width - tempCanvas.width-10) / 2, barcodeY);


        ctx.font = "17px Serif";
        ctx.textAlign = "center";
        ctx.fillStyle = "#000";
        ctx.fillText(product, mainCanvas.width / 2, 17);

        ctx.font = "22px Serif";
        ctx.textAlign = "center";
        ctx.fillStyle = "#000";
        ctx.fillText(companyName, mainCanvas.width / 2, barcodeY + tempCanvas.height + 28);

        ctx.font = "13px Serif";
        ctx.fillText(address, mainCanvas.width / 2, barcodeY + tempCanvas.height + 44);

        // Draw Price Vertically on the Left Side of Barcode
        ctx.font = "13px Serif";  // Adjust font size for the price
        ctx.textAlign = "center"; // Center the text horizontally
        ctx.save();  // Save the current context state

        // Rotate context for vertical text
        ctx.translate(16, mainCanvas.height / 2);  // Move the context to a position on the left side
        ctx.rotate(-Math.PI / 2);  // Rotate the context by 90 degrees counterclockwise
        ctx.fillText(`Price: Rs ${price}`, 0, 0);  // Draw text at the rotated position
        ctx.restore();  // Restore the context state to avoid affecting other drawing operations


        // Draw Price Vertically on the Left Side of Barcode
        ctx.font = "11px Serif";  // Adjust font size for the price
        ctx.textAlign = "center"; // Center the text horizontally
        ctx.save();  // Save the current context state

        // Rotate context for vertical text
        ctx.translate(mainCanvas.width-17, mainCanvas.height/2);  // Move the context to a position on the rightside
        ctx.rotate(-Math.PI / 2);  // Rotate the context by 90 degrees counterclockwise
        ctx.fillText(`MFD: ${mfd}`, -2, 0);  // Draw text at the rotated position
        ctx.restore();  // Restore the context state to avoid affecting other drawing operations

        // Draw Price Vertically on the Left Side of Barcode
        ctx.font = "11px Serif";  // Adjust font size for the price
        ctx.textAlign = "center"; // Center the text horizontally
        ctx.save();  // Save the current context state

        ctx.translate(mainCanvas.width-6, mainCanvas.height/2);  // Move the context to a position on the rightside
        ctx.rotate(-Math.PI / 2);  // Rotate the context by 90 degrees counterclockwise
        ctx.fillText(`EXP : ${ed}`, -2, 0);  // Draw text at the rotated position
        ctx.restore();  // Restore the context state to avoid affecting other drawing operations

        // Draw a border around the entire content in the canvas
        ctx.save(); // Save the current context state
        ctx.strokeStyle = "#000"; // Border color (black)
        ctx.lineWidth = 4; // Border thickness
        ctx.strokeRect(0, 0, mainCanvas.width, mainCanvas.height); // Draw the border
        ctx.restore(); // Restore the context state to avoid affecting other drawing operations
        
    }

    // Event Listener for 'Generate' Button
    document.getElementById("generateStockBarcode").addEventListener("click", function () {
        const barcode = document.getElementById("barcodeID").value.trim();
        const stockId = document.getElementById("stockID").value.trim();
        
    
        // Regular expression to check if barcode is exactly 10 digits
        // const barcodePattern = /^\d{13}$/;
    
        // if (!barcodePattern.test(barcode)) {
        //     alert("Barcode must be exactly 13 digits.");
        //     return;
        // }

        // AJAX request to send barcode and stockId to server-side PHP
        if (barcode && stockId) {
            generateBarcode();
            console.log(barcode);
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_stock_barcode.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText); // Display server response
                    fetchAllStocks();
                }
            };
            xhr.send("barcode=" + encodeURIComponent(barcode) + "&stockId=" + encodeURIComponent(stockId));
        } else {
            alert("Please enter the barcode");
        }
    });


    document.getElementById("printBarcode").addEventListener("click", () => {
        const printQty = parseInt(document.getElementById("printQty").value);
        const barcode = document.getElementById("barcodeID").value.trim();
        const stockId = document.getElementById("stockID").value.trim();

        if (printQty <= 0) {
            alert("Please enter a valid print quantity!");
            return;
        }

        // const barcodePattern = /^\d{13}$/;
    
        // if (!barcodePattern.test(barcode)) {
        //     alert("Barcode must be exactly 13 digits.");
        //     return;
        // }

        // AJAX request to send barcode and stockId to server-side PHP
        if (barcode && stockId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_stock_barcode.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    //alert(xhr.responseText); // Display server response
                    fetchAllStocks();
                }
            };
            xhr.send("barcode=" + encodeURIComponent(barcode) + "&stockId=" + encodeURIComponent(stockId));
        } else {
            alert("Please enter the barcode");
        }
    
        const price = document.getElementById("printPrice").value;
        const mfd = document.getElementById("mfDate").value;
        const ed = document.getElementById("expDate").value;
        const companyName = "SPICES"; // Replace with your actual company name
        const address = "Jaffna."; 
        const product = productNameInput.value;
    
        // Create a new document for printing
        const printWindow = window.open("", "_blank");
        printWindow.document.write(`
        <html>
        <head>
            <title>Print Barcodes</title>
            <style>
                @media print {
                    body {
                        display: flex;
                        flex-wrap: wrap;
                    }
                    img {
                        margin: 10px;
                    }
                }
            </style>
        </head>
        <body style="display: flex; flex-wrap: wrap;">
    `);
    
        for (let i = 0; i < printQty; i++) {
            const mainCanvas = document.getElementById("barcodeCanvas");
            const ctx = mainCanvas.getContext("2d");
    
            // Clear the canvas
            ctx.clearRect(0, 0, mainCanvas.width, mainCanvas.height);
    
            const tempCanvas = document.createElement("canvas");
            tempCanvas.width = 200; // Adjust to match your canvas width
            tempCanvas.height = 50; // Adjust for barcode height

            JsBarcode(tempCanvas, barcode, {
                format: "CODE128",
                displayValue: true,
                lineColor: "#000",
                width: 1.7,
                height: 50,
                fontSize: 14,
                textMargin: 0,
                font:"Serif",
            });

            // Check if productIdInput.value has exactly 5 digits
            // let productId = productIdInput.value;
            // if (productId.length === 5 && /^\d{5}$/.test(productId)) {
            //     productId = '0' + productId;
            // }

            // let stockId = stockIdInput.value;
            // if (stockId.length === 5 && /^\d{5}$/.test(stockId)) {
            //     stockId = '0' + stockId;
            // }

            // // Generate the barcode
            // JsBarcode(tempCanvas, stockId+ " " + productId, {
            //     format: "CODE128",
            //     displayValue: true,
            //     lineColor: "#000",
            //     width: 1.7,
            //     height: 50,
            //     fontSize: 14,
            //     textMargin: 0,
            //     font:"Serif",
            // });
    
            // Draw the barcode from the temporary canvas onto the main canvas
            const barcodeY = 15; // Y-coordinate for the barcode
            ctx.drawImage(tempCanvas, (mainCanvas.width - tempCanvas.width - 10) / 2, barcodeY);
    
            ctx.font = "17px Serif";
            ctx.textAlign = "center";
            ctx.fillStyle = "#000";
            ctx.fillText(product, mainCanvas.width / 2, 17);
    
            ctx.font = "22px Serif";
            ctx.textAlign = "center";
            ctx.fillStyle = "#000";
            ctx.fillText(companyName, mainCanvas.width / 2, barcodeY + tempCanvas.height + 28);
    
            ctx.font = "13px Serif";
            ctx.fillText(address, mainCanvas.width / 2, barcodeY + tempCanvas.height + 44);
    
            // Draw Price Vertically on the Left Side of Barcode
            ctx.font = "13px Serif";
            ctx.textAlign = "center";
            ctx.save();
            ctx.translate(16, mainCanvas.height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText(`Price: Rs ${price}`, 0, 0);
            ctx.restore();
    
            // Draw Manufacturing and Expiration Dates on the Right Side
            ctx.font = "11px Serif";
            ctx.textAlign = "center";
            ctx.save();
            ctx.translate(mainCanvas.width - 17, mainCanvas.height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText(`MFD: ${mfd}`, -2, 0);
            ctx.restore();
    
            ctx.save();
            ctx.translate(mainCanvas.width - 6, mainCanvas.height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText(`EXP: ${ed}`, -2, 0);
            ctx.restore();
    
            // Draw a border around the entire content in the canvas
            ctx.save();
            ctx.strokeStyle = "#000";
            ctx.lineWidth = 4;
            ctx.strokeRect(0, 0, mainCanvas.width, mainCanvas.height);
            ctx.restore();
    
            // Append the canvas to the print document
            
            const dataURL = mainCanvas.toDataURL();
            printWindow.document.write(`<div style="padding: 10px; box-sizing: border-box;"><img src="${dataURL}" style="margin-bottom: 20px;"></div>`);
        }
    
        printWindow.document.write("</body></html>");
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
        }, 200); // Ensure rendering completes before printing
    });
});



document.getElementById("combineStockCheckbox").addEventListener("change", async (event) => {
    // Function to generate a 10-digit random number
    const generateRandomCode = () => {
        return Math.floor(100000000 + Math.random() * 900000000).toString();
    };
    
    // Function to fetch and validate the generated item code
    const fetchUniqueItemCode = async () => {
        let itemCode;
        let isUnique = false;
    
        while (!isUnique) {
            itemCode = generateRandomCode();
            const response = await fetch("check_product_item_code.php?item_code=" + itemCode);
            const data = await response.json();
            
            if (!data.exists) {
                isUnique = true;
            }
        }
    
        return itemCode;
    };

    if (event.target.checked) {
        const uniqueCode = await fetchUniqueItemCode();
        document.getElementById("autoItemCode").value = uniqueCode;
        document.getElementById("mergeItemsModal").style.display = "flex";
    } else {
        document.getElementById("mergeItemsModal").style.display = "none";
    }



    

});

// const itemCodeInput = document.getElementById("item-code"); //normal product itemcode
// const initializeNormalAutoItemCode = () => {
//     fetch("generate_item_code.php")
//         .then(response => response.json())
//         .then(data => {
//             itemCodeInput.value = data.itemCode;
//             itemCodeInput.disabled = true; // Disable item code in auto mode
//         })
//         .catch(error => console.error("Error fetching auto item code:", error));
//   };

document.getElementById("mergeItemsSaveButton").addEventListener("click", () => {
    const motherItem = document.getElementById("motherItem").value;
    const motherQty = document.getElementById("motherQty").value;
    const childItem = document.getElementById("childItem").value;
    const childQty = document.getElementById("childQty").value;
    const motherDes = document.getElementById("motherDescription").value;
    const childDes = document.getElementById("childDescription").value;
    

    if (!motherItem || !motherQty || !childItem || !childQty) {
        alert("Please fill all required fields.");
        return;
    }

    const productName = `${motherItem}(${motherQty})+${childItem}(${childQty})`;
    const payload = {
        itemCode: document.getElementById("autoItemCode").value,
        productName,
        category: "Merged Items",
        motherDes,
        childDes,
    };

    fetch("save_merge_item.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            document.getElementById("mergeItemsModal").style.display = "none";
            initializeNormalAutoItemCode();
            document.getElementById("stock-productname").value = data.productName;
            document.getElementById("stock-itemcode").value = data.itemCode;

        })
        .catch(error => alert("Error saving item: " + error.message));
});

document.getElementById("mergeItemsCloseModalButton").addEventListener("click", () => {
    document.getElementById("mergeItemsModal").style.display = "none";
});



const stockSupplierSelect = document.getElementById("supplier-select");
const stockItemCodeInput = document.getElementById("stock-itemcode");
const stockItemInput = document.getElementById("stock-productname");
const stockSearchCheckbox = document.getElementById("searchWithProductCheckbox");

// Function to handle the search
function searchStock() {
    const isChecked = stockSearchCheckbox.checked;
    const supplierId = document.getElementById("supplier-id").value;
    const itemCode = stockItemCodeInput.value;
    const stockTableBody = document.getElementById("searchStockTableBody");

    if (isChecked) {
        // Validate inputs
        if (!supplierId || !itemCode) {
            stockTableBody.innerHTML = `<tr><td colspan="12" class="message">Please select the supplier and the product.</td></tr>`;
            return;
        }

        fetch("search_stock.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ supplierId, itemCode }),
        })
            .then((response) => response.json())
            .then((data) => {
                const stockTableBody = document.getElementById("searchStockTableBody");
                stockTableBody.innerHTML = ""; // Clear previous results

                if (data.length === 0) {
                    const noDataMessage = `<tr><td colspan="12" class="message">No items found for the selected supplier and product.</td></tr>`;
                    stockTableBody.innerHTML = noDataMessage;
                } else {
                    data.forEach((item) => {
                        // Calculate discountValue and totalAmount
                        const discountValue = (item.discount / 100) * item.costPrice; // Discount is a percentage
                        const totalAmount = (item.costPrice - discountValue) * item.qty; // Total amount after applying discount

                        const row = `<tr>
                            <td>${item.stockId}</td>
                            <td>${item.itemCode}</td>
                            <td>${item.productName}</td>
                            <td>${item.costPrice}</td>
                            <td>${item.wholesalePrice}</td>
                            <td>${item.maximumRetailPrice}</td>
                            <td>${item.superCustomerPrice}</td>
                            <td>${item.ourPrice}</td>
                            <td>${item.qty}</td>
                            <td>${item.availableStock}</td>
                            <td>${item.discount}</td>
                            <td>${discountValue.toFixed(2)}</td>
                            <td>${totalAmount.toFixed(2)}</td>
                        </tr>`;
                        stockTableBody.innerHTML += row;
                    });
                }
            })
            .catch((error) => {
                console.error("Error fetching stock data:", error);
                alert("Error fetching stock data.");
            });
    }else {
        // Clear the table when the checkbox is unchecked
        document.getElementById("searchStockTableBody").innerHTML = "";
    }
}

// Event listener for the checkbox
stockSearchCheckbox.addEventListener("change", () => {
    const stockTableBody = document.getElementById("searchStockTableBody");

    if (!stockSearchCheckbox.checked) {
        // Clear the table when the checkbox is unchecked
        stockTableBody.innerHTML = "";
    } else {
        // Trigger search when checkbox is checked
        searchStock();
    }
});

// Event listeners for supplier and item code changes
stockSupplierSelect.addEventListener("change", () => {
    if (stockSearchCheckbox.checked) {
        // // Update supplier ID and trigger search
        // document.getElementById("supplier-id").value = supplierSelect.value;
        searchStock();
    }
});

// stockItemCodeInput.addEventListener("input", () => {
//     if (stockSearchCheckbox.checked) {
//         // Trigger search when item code changes
//         searchStock();
//     }
// });

stockItemCodeInput.addEventListener("change", () => {
    if (stockSearchCheckbox.checked) {
        // Trigger search when item code changes
        searchStock();
    }
});



document.addEventListener('DOMContentLoaded', function () {
    const productNameInput = document.getElementById('stock-productname');
    const itemCodeInput = document.getElementById('stock-itemcode');

    // Create dropdowns
    const productDropdown = document.createElement('div');
    styleDropdown(productDropdown);
    document.body.appendChild(productDropdown);

    const itemCodeDropdown = document.createElement('div');
    styleDropdown(itemCodeDropdown);
    document.body.appendChild(itemCodeDropdown);

    let products = [];

    // Fetch products and item codes from the server
    fetch('fetch_productname_itemcode.php?action=fetchProducts')
        .then(response => response.json())
        .then(data => {
            products = data;
        });

    // Listen for Enter key in itemCode input
    itemCodeInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission (if inside a form)
            const enteredItemCode = itemCodeInput.value.trim().toLowerCase();
            const selectedProduct = products.find(p => p.item_code.toLowerCase() === enteredItemCode);
            
            if (selectedProduct) {
                productNameInput.value = selectedProduct.product_name;
                searchStock(); // Call searchStock() if needed
                productDropdown.style.display = 'none';
                itemCodeDropdown.style.display = 'none';
            } else {
                productNameInput.value = ''; // Clear product name if no match found
                alert('No product found for this item code');
            }
        }
    });

    productNameInput .addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission (if inside a form)
            const enteredProduct  = productNameInput .value.trim().toLowerCase();
            const selectedItemCode = products.find(p => p.product_name.toLowerCase() === enteredProduct);
            
            if (selectedItemCode) {
                itemCodeInput.value = selectedItemCode.item_code;
                searchStock(); // Call searchStock() if needed
                productDropdown.style.display = 'none';
                itemCodeDropdown.style.display = 'none';
            } else {
                itemCodeInput.value = ''; // Clear product name if no match found
                alert('No item code found for this product');
            }
        }
    });

    // Show and filter dropdown when product-name input is focused or typed
    productNameInput.addEventListener('input', function () {
        const searchValue = productNameInput.value.toLowerCase();
        const filteredProducts = products.filter(product => product.product_name.toLowerCase().includes(searchValue));
        populateDropdown(productDropdown, filteredProducts.map(p => p.product_name), (selectedValue) => {
            productNameInput.value = selectedValue;
            const selectedProduct = products.find(p => p.product_name === selectedValue);
            itemCodeInput.value = selectedProduct ? selectedProduct.item_code : '';
            productDropdown.style.display = 'none';
            searchStock();
        });
        positionDropdown(productDropdown, productNameInput);
    });

    // Show and filter dropdown when itemcode input is focused or typed
    itemCodeInput.addEventListener('input', function () {
        const searchValue = itemCodeInput.value.toLowerCase();
        const filteredProducts = products.filter(product => product.item_code.toLowerCase().includes(searchValue));
        populateDropdown(itemCodeDropdown, filteredProducts.map(p => p.item_code), (selectedValue) => {
            itemCodeInput.value = selectedValue;
            const selectedProduct = products.find(p => p.item_code === selectedValue);
            productNameInput.value = selectedProduct ? selectedProduct.product_name : '';
            itemCodeDropdown.style.display = 'none';
            searchStock();
        });
        positionDropdown(itemCodeDropdown, itemCodeInput);
         
    });

    // Show dropdown when product-name input is focused
    productNameInput.addEventListener('focus', function () {
        populateDropdown(productDropdown, products.map(p => p.product_name), (selectedValue) => {
            productNameInput.value = selectedValue;
            const selectedProduct = products.find(p => p.product_name === selectedValue);
            itemCodeInput.value = selectedProduct ? selectedProduct.item_code : '';
            productDropdown.style.display = 'none';
            searchStock();
        });
        positionDropdown(productDropdown, productNameInput);
    });

    // Show dropdown when itemcode input is focused
    itemCodeInput.addEventListener('focus', function () {
        populateDropdown(itemCodeDropdown, products.map(p => p.item_code), (selectedValue) => {
            itemCodeInput.value = selectedValue;
            const selectedProduct = products.find(p => p.item_code === selectedValue);
            productNameInput.value = selectedProduct ? selectedProduct.product_name : '';
            itemCodeDropdown.style.display = 'none';
            searchStock();
        });
        positionDropdown(itemCodeDropdown, itemCodeInput);
    });


    // Populate dropdown with filtered options
    function populateDropdown(dropdown, options, onSelect) {
        dropdown.innerHTML = '';
        if (options.length === 0) {
            const noResults = document.createElement('div');
            noResults.textContent = 'No results found';
            noResults.style.padding = '5px';
            noResults.style.color = 'gray';
            dropdown.appendChild(noResults);
        } else {
            options.forEach(option => {
                const div = document.createElement('div');
                div.textContent = option;
                div.style.padding = '5px';
                div.style.cursor = 'pointer';
                div.addEventListener('click', () => onSelect(option));
                dropdown.appendChild(div);
            });
        }
        dropdown.style.display = 'block';
    }

    // Position dropdown below the input field
    function positionDropdown(dropdown, input) {
        const rect = input.getBoundingClientRect();
        dropdown.style.left = `${rect.left}px`;
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.minWidth = `${rect.width}px`;
    }

    // Apply styles to dropdown
    function styleDropdown(dropdown) {
        dropdown.style.position = 'absolute';
        dropdown.style.border = '1px solid #ccc';
        dropdown.style.background = '#ebeff5 '; // Set background color to light blue
        dropdown.style.zIndex = '1000';
        dropdown.style.display = 'none';
        dropdown.style.width = 'fit-content';
        dropdown.style.maxHeight = '200px'; // Fixed maximum height
        dropdown.style.overflowY = 'auto'; // Enable scrolling when content exceeds height
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!productNameInput.contains(e.target) && !productDropdown.contains(e.target)) {
            productDropdown.style.display = 'none';
        }
        if (!itemCodeInput.contains(e.target) && !itemCodeDropdown.contains(e.target)) {
            itemCodeDropdown.style.display = 'none';
        }
    });
});



document.addEventListener("DOMContentLoaded", () => {
    const payNowModal = document.getElementById("payNowModal");
    const closeModalButton = document.getElementById("payNowCloseModal");

    const outstandingAmount = document.getElementById("outstandingAmount");
    const selectedSupplierName = document.getElementById("selectedSupplierName");
    const selectedSupplierID = document.getElementById("selectedSupplierId");
    const selectedSupplierCredit = document.getElementById("selectedSupplierCredit");
  
    document.querySelector(".footer-pay-button").addEventListener("click", () => {
        const tableBody = document.getElementById("paySupplierTableBody");
        const paySupplierStocksTableBody = document.getElementById("paySupplierStocksTableBody");
        const supplierPaymentsTableBody = document.getElementById("supplierPaymentsTableBody");

        document.getElementById('selectedSupplierDetails').style.display = 'none';
        selectedSupplierName.textContent = "";
        selectedSupplierID.textContent = "";
        selectedSupplierCredit.textContent = "";
        document.getElementById('paySupplierStocksTableBody').innerHTML = '';
        document.getElementById('supplierPaymentsTableBody').innerHTML = '';

        // Fetch supplier details from the server
        fetch("get_suppliers.php")
            .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
            })
            .then((data) => {
            // Calculate and display outstanding
            const totalOutstanding = data.reduce((sum, supplier) => sum + parseFloat(supplier.credit_balance), 0);
            outstandingAmount.textContent = totalOutstanding.toFixed(2);

            // Populate the table with supplier details
            tableBody.innerHTML = ''; // Clear previous rows
            data.forEach((supplier) => {
                const row = document.createElement("tr");
    
                row.innerHTML = `
                <td>${supplier.supplier_id}</td>
                <td>${supplier.supplier_name}</td>
                <td>${supplier.telephone_no}</td>
                <td>${supplier.company}</td>
                <td>${supplier.credit_balance}</td>
                `;
    
                // Add event listener for clicking on the row
                row.addEventListener("click", () => {
                    selectedSupplierName.textContent = supplier.supplier_name;
                    selectedSupplierID.textContent = supplier.supplier_id;
                    selectedSupplierCredit.textContent = parseFloat(supplier.credit_balance).toFixed(2);
                    loadSupplierDetails(supplier.supplier_id);
                    document.getElementById('pay-now-supplier-table').setAttribute('data-selected-id', supplier.supplier_id);
                    document.getElementById('selectedSupplierDetails').style.display = 'block';
                    row.setAttribute("data-supplier-id", supplier.supplier_id);
                    
                });
    
                tableBody.appendChild(row);
            });
            })
            .catch((error) => {
            console.error("There was an error fetching the supplier details:", error);
            });
    
    
        // Function to load stocks and payments for the selected supplier
        function loadSupplierDetails(supplierId) {
            // Fetch supplier stocks
            fetch(`get_supplier_stocks.php?supplier_id=${supplierId}`)
                .then((response) => response.json())
                .then((stocks) => {
                    // Clear previous stock data
                    paySupplierStocksTableBody.innerHTML = '';
                    let totalStocksAmount = 0; 
                    stocks.forEach((stock) => {
    
                        const totalAmount = stock.cost_price  * stock.purchase_qty; // Total amount after applying discount
    
                        totalStocksAmount += totalAmount;

                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${stock.stock_id}</td>
                            <td>${stock.created_at}</td>
                            <td>${stock.itemcode}</td>
                            <td>${stock.product_name}</td>
                            <td>${stock.purchase_qty}</td>
                            <td>${totalAmount.toFixed(2)}</td>
                            
                        `;
                        paySupplierStocksTableBody.appendChild(row);
                    });
                    document.getElementById("stocksTotalAmount").textContent = totalStocksAmount.toFixed(2);
                })
                .catch((error) => {
                    console.error("There was an error fetching the stocks:", error);
                });
    
            // Fetch supplier payments
            fetch(`get_supplier_payments.php?supplier_id=${supplierId}`)
                .then((response) => response.json())
                .then((payments) => {
                    // Clear previous payment data
                    supplierPaymentsTableBody.innerHTML = '';
                    let totalPaymentsAmount = 0;
                    
                    // Group payments by payment_no
                    const groupedPayments = payments.reduce((acc, payment) => {
                        if (!acc[payment.payment_no]) {
                            acc[payment.payment_no] = [];
                        }
                        acc[payment.payment_no].push(payment);
                        return acc;
                    }, {});

                    // Iterate over grouped payments and build rows
                    for (const paymentNo in groupedPayments) {
                        const paymentGroup = groupedPayments[paymentNo];
                        paymentGroup.forEach((payment, index) => {

                            totalPaymentsAmount += parseFloat(payment.amount);
                            
                            const row = document.createElement("tr");
                            if (index === 0) {
                                row.innerHTML = `
                                    <td rowspan="${paymentGroup.length}">${payment.payment_no}</td>
                                    <td>${payment.payment_method}</td>
                                    <td>${payment.date}</td>
                                    <td>${payment.amount}</td>
                                    <td rowspan="${paymentGroup.length}">
                                        <button class="remove-payment-button" onclick="deletePayment(${payment.payment_no}, this.parentElement.parentElement);">
                                            <i class="fa fa-trash delete-icon"></i>
                                        </button>
                                    </td>
                                `;
                            } else {
                                row.innerHTML = `
                                    <td>${payment.payment_method}</td>
                                    <td>${payment.date}</td>
                                    <td>${payment.amount}</td>
                                `;
                            }
                            supplierPaymentsTableBody.appendChild(row);
                        });
                    }
                    document.getElementById("paymentsTotalAmount").textContent = totalPaymentsAmount.toFixed(2);
                })
                .catch((error) => {
                console.error("There was an error fetching the payments:", error);
            });
        }

        

        payNowModal.style.display = "flex";

        const paySupplierTable = document.getElementById("pay-now-supplier-table");
        paySupplierTable.addEventListener("click", (e) => {
            // Check if the clicked element is in the table header
    
        
            const rows = paySupplierTable.getElementsByTagName("tr");
            for (const row of rows) {
                row.classList.remove("selected");
            }
        
            const clickedRow = e.target.parentElement;
            if (e.target.tagName === "TH" || e.target.closest("thead")) {
                clickedRow.classList.remove("selected");
            }
            else if(clickedRow.tagName === "TR") {
                clickedRow.classList.add("selected");
            }
        });

        
    });
  
    closeModalButton.addEventListener("click", () => {
      payNowModal.style.display = "none";
    });
});


function deletePayment(paymentNo, rowElement) {
    const selectedSupplierID = document.getElementById("selectedSupplierId").textContent;
    const selectedSupplierCredit = document.getElementById("selectedSupplierCredit");

    if (confirm(`Are you sure you want to delete payment number ${paymentNo}?`)) {
        fetch('delete_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payment_no: paymentNo }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.message.includes("successfully")) {
                //rowElement.remove(); // Remove the row from the table
                loadSupplierPayments(selectedSupplierID)

                // Update credit balance from the response (assuming new_balance is sent back)
                if (data.new_balance !== undefined) {
                    selectedSupplierCredit.textContent = parseFloat(data.new_balance).toFixed(2);
                    
                    // Also update the corresponding row in the supplier table
                    const supplierTableRow = document.querySelector(`#pay-now-supplier-table tr[data-supplier-id="${selectedSupplierID}"]`);
                    if (supplierTableRow) {
                        const creditBalanceCell = supplierTableRow.querySelector("td:nth-child(5)");
                        if (creditBalanceCell) {
                            creditBalanceCell.textContent = parseFloat(data.new_balance).toFixed(2);
                        }
                    }
                }

                alert(data.message);

                
            } else {
                alert(data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert("An error occurred while deleting the payment.");
        });
    }
}

function loadSupplierPayments(supplierId) {

    // Fetch supplier payments
    fetch(`get_supplier_payments.php?supplier_id=${supplierId}`)
        .then((response) => response.json())
        .then((payments) => {
            // Clear previous payment data
            supplierPaymentsTableBody.innerHTML = '';
            
            // Group payments by payment_no
            const groupedPayments = payments.reduce((acc, payment) => {
                if (!acc[payment.payment_no]) {
                    acc[payment.payment_no] = [];
                }
                acc[payment.payment_no].push(payment);
                return acc;
            }, {});

            // Iterate over grouped payments and build rows
            for (const paymentNo in groupedPayments) {
                const paymentGroup = groupedPayments[paymentNo];
                paymentGroup.forEach((payment, index) => {
                    const row = document.createElement("tr");
                    if (index === 0) {
                        row.innerHTML = `
                            <td rowspan="${paymentGroup.length}">${payment.payment_no}</td>
                            <td>${payment.payment_method}</td>
                            <td>${payment.date}</td>
                            <td>${payment.amount}</td>
                            <td rowspan="${paymentGroup.length}">
                                <button class="remove-payment-button" onclick="deletePayment(${payment.payment_no}, this.parentElement.parentElement);">
                                    <i class="fa fa-trash delete-icon"></i>
                                </button>
                            </td>
                        `;
                    } else {
                        row.innerHTML = `
                            <td>${payment.payment_method}</td>
                            <td>${payment.date}</td>
                            <td>${payment.amount}</td>
                        `;
                    }
                    supplierPaymentsTableBody.appendChild(row);
                });
            }
        })
        .catch((error) => {
        console.error("There was an error fetching the payments:", error);
    });
}

// function loadSuppliers(){
//     const outstandingAmount = document.getElementById("outstandingAmount");
//     const selectedSupplierName = document.getElementById("selectedSupplierName");
//     const selectedSupplierID = document.getElementById("selectedSupplierId");
//     const selectedSupplierCredit = document.getElementById("selectedSupplierCredit");

//     const tableBody = document.getElementById("paySupplierTableBody");

//     // Fetch supplier details from the server
//     fetch("get_suppliers.php")
//     .then((response) => {
//     if (!response.ok) {
//         throw new Error("Network response was not ok");
//     }
//     return response.json();
//     })
//     .then((data) => {
//     // Calculate and display outstanding
//     const totalOutstanding = data.reduce((sum, supplier) => sum + parseFloat(supplier.credit_balance), 0);
//     outstandingAmount.textContent = totalOutstanding.toFixed(2);

//     // Populate the table with supplier details
//     tableBody.innerHTML = ''; // Clear previous rows
//     data.forEach((supplier) => {
//         const row = document.createElement("tr");

//         row.innerHTML = `
//         <td>${supplier.supplier_id}</td>
//         <td>${supplier.supplier_name}</td>
//         <td>${supplier.telephone_no}</td>
//         <td>${supplier.company}</td>
//         <td>${supplier.credit_balance}</td>
//         `;

//         // Add event listener for clicking on the row
//         row.addEventListener("click", () => {
//             selectedSupplierName.textContent = supplier.supplier_name;
//             selectedSupplierID.textContent = supplier.supplier_id;
//             selectedSupplierCredit.textContent = parseFloat(supplier.credit_balance).toFixed(2);
//             loadSupplierDetails(supplier.supplier_id);
//             document.getElementById('pay-now-supplier-table').setAttribute('data-selected-id', supplier.supplier_id);
//             document.getElementById('selectedSupplierDetails').style.display = 'block';
            
//         });

//         tableBody.appendChild(row);
//     });
//     })
//     .catch((error) => {
//     console.error("There was an error fetching the supplier details:", error);
//     });
// }


//payment methods
document.querySelector('#confirm-pay-now-button').addEventListener('click', () => {
    const selectedSupplierId = document.getElementById('pay-now-supplier-table').getAttribute('data-selected-id');
    const selectedSupplierName = document.getElementById('selectedSupplierName').textContent;
    const selectedSupplierCredit = document.getElementById('selectedSupplierCredit').textContent;
    const blueBox= document.getElementById('blue-box');
    const addPaymentButton = document.getElementById("add-payment");
    const addPrintButton = document.getElementById("pay-print-button");
    const paymentContainer = document.getElementById("payment-container");

    if (selectedSupplierId === "" || selectedSupplierName === "") {
        alert("Please select a supplier"); 
        return;
    }

    document.getElementById('payment-supplier-id').value = selectedSupplierId || 'N/A';
    document.getElementById('payment-supplier-name').value = selectedSupplierName || 'N/A';
    document.getElementById('payment-credit-balance').value = selectedSupplierCredit;
    document.getElementById('payment-modal').style.display = "flex";

    const paymentMethod = document.getElementById('payment-method');
    const paymentDetails = document.getElementById('payment-details');
    paymentDetails.innerHTML = '';
    paymentMethod.value="";

    const paymentBoxes = document.querySelectorAll('#payment-container .multi-payment-box');
    paymentBoxes.forEach((box, index) => {
        if (index > 0) {
            box.remove(); // Remove all except the first
        }
    });
    document.getElementById('multi-payment-method-0').value="";
    document.getElementById('multi-payment-details-0').innerHTML = '';

    document.getElementById('single-payment').checked = true;
    const paymentType = document.querySelector('input[name="payment-type"]:checked');
    if (paymentType && paymentType.value === 'single') {
        blueBox.style.display = 'block';
        addPaymentButton.style.display = "none";
        addPrintButton.style.display = "none";
        paymentContainer.style.display = "none";
    }
});

document.getElementById('paymentCloseModal').addEventListener("click", () => {
    document.getElementById('payment-modal').style.display = "none";
    closeBankPopup();
});

document.querySelector('#payment-form').addEventListener('change', async(e) => {
    const paymentType = document.querySelector('input[name="payment-type"]:checked');
    const paymentMethod = document.getElementById('payment-method').value;

    const paymentDetails = document.getElementById('payment-details');
    const blueBox= document.getElementById('blue-box');

    const addPaymentButton = document.getElementById("add-payment");
    const addPrintButton = document.getElementById("pay-print-button");
    const paymentContainer = document.getElementById("payment-container");

    if (paymentType && paymentType.value === 'single') {
        blueBox.style.display = 'block';
        addPaymentButton.style.display = "none";
        addPrintButton.style.display = "none";
        paymentContainer.style.display = "none";
    } else if(paymentType && paymentType.value === 'multiple'){
        //paymentDetails.innerHTML = '';
        blueBox.style.display = 'none';
        addPaymentButton.style.display = "block";
        addPrintButton.style.display = "block";
        paymentContainer.style.display = "flex";
    }

    if (e.target.id === 'payment-method') {
        const bankOptions = await loadBankOptions();
        switch (paymentMethod) {
            case 'cash':
                paymentDetails.innerHTML = `
                    <div class="payment-form-group">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" placeholder="Enter Amount">
                    </div>
                    <div class="payment-form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date">
                    </div>
                    <button type="button" onclick="saveSinglePayment()" class="pay-print-button">
                        <i class="fas fa-print" style="margin-right: 8px;"></i>Pay & Print Receipt
                    </button>
                `;
                break;
            case 'cheque':
                paymentDetails.innerHTML = `
                    <div class="payment-form-group">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" placeholder="Enter Amount">
                    </div>
                    <div class="payment-form-group">
                        <label for="cheque-no">Cheque No#</label>
                        <input type="text" id="cheque-no" placeholder="Enter Cheque No">
                    </div>
                    <div class="payment-form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date">
                    </div>
                    <div class="payment-form-group">
                        <label for="bank">Select Bank</label>
                        <div style="display:flex">
                            <select id="bank">
                                <option value="">-- Select Bank --</option>
                                ${bankOptions}
                            </select>
                        </div>
                    </div>
                    <button type="button" onclick="saveSinglePayment()" class="pay-print-button">
                        <i class="fas fa-print" style="margin-right: 8px;"></i>Pay & Print Receipt
                    </button>
                `;
                break;
            case 'online':
                paymentDetails.innerHTML = `
                    <div class="payment-form-group">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" placeholder="Enter Amount">
                    </div>
                    <div class="payment-form-group">
                        <label for="ref-no">Ref No#</label>
                        <input type="text" id="ref-no" placeholder="Enter Reference No">
                    </div>
                    <div class="payment-form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date">
                    </div>
                    <div class="payment-form-group">
                        <label for="bank">Select Bank</label>
                        <div style="display:flex">
                            <select id="bank">
                                <option value="">-- Select Bank --</option>
                                ${bankOptions}
                            </select>
                        </div>
                    </div>
                    <button type="button" onclick="saveSinglePayment()" class="pay-print-button">
                        <i class="fas fa-print" style="margin-right: 8px;"></i>Pay & Print Receipt
                    </button>
                `;
                break;
            default:
                paymentDetails.innerHTML = '';
        }
    }
});

async function loadBankOptions() {
    let bankOptions = '';
    try {
        const response = await fetch('fetch_banks.php');
        const banks = await response.json();
        if (banks.error) {
            throw new Error(banks.error);
        }
        bankOptions = banks.map(bank => `<option value="${bank.bank_code}">${bank.bank_name}</option>`).join('');
    } catch (error) {
        console.error('Error loading bank options:', error);
        bankOptions = `<option value="">Failed to load banks</option>`;
    }
    return bankOptions;
}


// document.getElementById('payment-details').addEventListener('click', (e) => {
//     if (e.target.tagName === 'BUTTON') {

function saveSinglePayment(){
        const paymentMethod = document.getElementById('payment-method').value;
        const amount = document.getElementById('amount') ? document.getElementById('amount').value : '';
        const date = document.getElementById('date') ? document.getElementById('date').value : '';
        const selectedSupplierId = document.getElementById('payment-supplier-id').value
        let data = { selectedSupplierId, paymentMethod, amount, date };

        if (paymentMethod === 'cheque') {
            data.chequeNo = document.getElementById('cheque-no').value;
            // data.date = document.getElementById('date').value;
            data.bank = document.getElementById('bank').value;
        } else if (paymentMethod === 'online') {
            data.refNo = document.getElementById('ref-no').value;
            //data.date = document.getElementById('date').value;
            data.bank = document.getElementById('bank').value;
        }

        fetch('save_single_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Payment saved successfully!');
                updateSelectedRow();
                
            } else {
                alert('Failed to save payment. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    
};

function updateSelectedRow(){
    // Update credit balance in the corresponding row
    const selectedSupplierId = document.getElementById('payment-supplier-id').value;
    const amount = document.getElementById('amount') ? document.getElementById('amount').value : '';
    const tableRows = document.querySelectorAll("#pay-now-supplier-table tbody tr");
    tableRows.forEach(row => {
        const supplierIdCell = row.cells[0]; // Assuming supplier_id is in the first column
        const creditBalanceCell = row.cells[4]; // Assuming credit_balance is in the fifth column
        
        if (supplierIdCell.textContent.trim() === selectedSupplierId) {
            const currentBalance = parseFloat(creditBalanceCell.textContent);
            const newBalance = (currentBalance - amount).toFixed(2);
            creditBalanceCell.textContent = newBalance;
            
            // Update the displayed selected supplier credit
            document.getElementById('selectedSupplierCredit').textContent = newBalance;
            document.getElementById('payment-credit-balance').value = newBalance;
        }
    });
}


let paymentCount = 1;

document.getElementById('add-payment').addEventListener('click', () => {
    const newBox = document.createElement('div');
    newBox.className = 'multi-payment-box';
    newBox.innerHTML = `
        <div class="multi-payment-form-group">
            <label for="multi-payment-method-${paymentCount}">Select Payment Method</label>
            <select id="multi-payment-method-${paymentCount}" class="multi-payment-method" data-index="${paymentCount}">
                <option value="">-- Select Method --</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
                <option value="online">Online</option>
            </select>
            <div id="multi-payment-details-${paymentCount}" class="multi-payment-details"></div>
            <button type="button" class="delete-payment-row" data-index="${paymentCount}" style="margin-top: 10px;">
                <i class="fa fa-trash delete-icon delete-payment-row-icon"></i>
            </button>
        </div>
    `;
    document.getElementById('payment-container').appendChild(newBox);
    paymentCount++;
});

document.addEventListener('change', async(event) => {
    if (event.target.matches('.multi-payment-method')) {
        const index = event.target.dataset.index;
        const detailsContainer = document.getElementById(`multi-payment-details-${index}`);
        detailsContainer.innerHTML = ''; // Clear previous fields
        const method = event.target.value;

        let fields = '';
        let bankOptions = '';

        // Fetch bank data from PHP endpoint
        try {
            const response = await fetch('fetch_banks.php');
            const banks = await response.json();
            if (banks.error) {
                throw new Error(banks.error);
            }
            bankOptions = banks.map(bank => `<option value="${bank.bank_code}">${bank.bank_name}</option>`).join('');
        } catch (error) {
            console.error('Error loading bank options:', error);
            bankOptions = `<option value="">Failed to load banks</option>`;
        }

        if (method === 'cash') {
            fields = `
                <label for="cash-amount-${index}">Amount</label>
                <input type="number" name="payments[${index}][amount]" id="cash-amount-${index}" placeholder="Enter amount">
                <label for="cash-date-${index}">Date</label>
                <input type="date" name="payments[${index}][date]" id="cash-date-${index}">
                <input type="hidden" name="payments[${index}][method]" value="cash">
            `;
        } else if (method === 'cheque') {
            fields = `
                <label for="cheque-amount-${index}">Amount</label>
                <input type="number" name="payments[${index}][amount]" id="cheque-amount-${index}" placeholder="Enter amount">
                <label for="cheque-no-${index}">Cheque No#</label>
                <input type="text" name="payments[${index}][cheque_no]" id="cheque-no-${index}" placeholder="Enter cheque number">
                <label for="cheque-date-${index}">Date</label>
                <input type="date" name="payments[${index}][date]" id="cheque-date-${index}">
                <label for="cheque-bank-${index}">Select Bank</label>
                <div style="display:flex">
                    <select name="payments[${index}][bank]" id="cheque-bank-${index}">
                        <option value="">-- Select Bank --</option>
                        ${bankOptions}
                    </select>
                </div>
                <input type="hidden" name="payments[${index}][method]" value="cheque">
            `;
        } else if (method === 'online') {
            fields = `
                <label for="online-amount-${index}">Amount</label>
                <input type="number" name="payments[${index}][amount]" id="online-amount-${index}" placeholder="Enter amount">
                <label for="online-ref-${index}">Reference No#</label>
                <input type="text" name="payments[${index}][ref_no]" id="online-ref-${index}" placeholder="Enter reference number">
                <label for="online-date-${index}">Date</label>
                <input type="date" name="payments[${index}][date]" id="online-date-${index}">
                <label for="online-bank-${index}">Select Bank</label>
                <div style="display:flex">
                    <select name="payments[${index}][bank]" id="online-bank-${index}">
                        <option value="">-- Select Bank --</option>
                        ${bankOptions}
                    </select>
                </div>
                <input type="hidden" name="payments[${index}][method]" value="online">
            `;
        }
        
        detailsContainer.innerHTML = fields;
    }
});

document.addEventListener('click', (event) => {
    if (event.target.matches('.delete-payment-row-icon')) {
        //const index = event.target.dataset.index;
        const paymentBox = event.target.closest('.multi-payment-box');
        paymentBox.remove();
    }
});

document.getElementById('pay-print-button').addEventListener('click', (event) => {
    event.preventDefault(); // Prevent form submission

    const selectedSupplierId = document.getElementById('payment-supplier-id').value

    const payments = [];
    let totalPaymentAmount = 0;
    document.querySelectorAll('.multi-payment-box').forEach((box) => {
        const methodElement = box.querySelector('.multi-payment-method');
        const index = methodElement.dataset.index;
        const method = methodElement.value;

        if (!method) {
            alert(`Please select a payment method for row ${index + 1}`);
            return;
        }
        
        const payment = { method: method };
        
        if (method === 'cash') {
            payment.amount = document.querySelector(`#cash-amount-${index}`).value;
            payment.date = document.querySelector(`#cash-date-${index}`).value;
        } else if (method === 'cheque') {
            payment.amount = document.querySelector(`#cheque-amount-${index}`).value;
            payment.cheque_no = document.querySelector(`#cheque-no-${index}`).value;
            payment.date = document.querySelector(`#cheque-date-${index}`).value;
            payment.bank = document.querySelector(`#cheque-bank-${index}`).value;
        } else if (method === 'online') {
            payment.amount = document.querySelector(`#online-amount-${index}`).value;
            payment.ref_no = document.querySelector(`#online-ref-${index}`).value;
            payment.date = document.querySelector(`#online-date-${index}`).value;
            payment.bank = document.querySelector(`#online-bank-${index}`).value;
        }

        payments.push(payment);
        totalPaymentAmount += parseFloat(payment.amount);
    });

    const data = { payments: payments, selectedSupplierId:selectedSupplierId };
    console.log(data);
    console.log(payments);

    fetch('save_multiple_payments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Multiple Payments saved successfully!');

            const tableRows = document.querySelectorAll("#pay-now-supplier-table tbody tr");
            tableRows.forEach(row => {
                const supplierIdCell = row.cells[0]; // Assuming supplier_id is in the first column
                const creditBalanceCell = row.cells[4]; // Assuming credit_balance is in the fifth column

                if (supplierIdCell.textContent.trim() === selectedSupplierId) {
                    const currentBalance = parseFloat(creditBalanceCell.textContent);
                    const newBalance = (currentBalance - totalPaymentAmount).toFixed(2);
                    creditBalanceCell.textContent = newBalance;

                    // Update displayed selected supplier credit
                    document.getElementById('selectedSupplierCredit').textContent = newBalance;
                    document.getElementById('payment-credit-balance').value = newBalance;
                }
            });
        } else {
            alert('Failed to save payments. Please try again.');
        }
    })
    .catch(error => {
        console.log(data);
        console.log(payments);
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

function addBank() {
   const bankPopup = document.getElementById('bank-popup');
    bankPopup.style.display = 'block';  // Show popup
    fetchBanks(); 
}

function closeBankPopup() {
    const popup = document.getElementById('bank-popup');
    popup.style.display = 'none'; // Hide popup
}


function saveBank() {
    const bankCode = document.getElementById('bank-code').value.trim();
    const bankName = document.getElementById('bank-name').value.trim();

    if (!bankCode || !bankName) {
        alert('Bank code and name are required.');
        return;
    }

    // Send bank data to the server
    fetch('save_bank.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ bankCode, bankName }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bank saved successfully.');
            addBankToTable(bankCode, bankName);  // Add to table
        } else {
            alert('Failed to save bank. ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function addBankToTable(bankCode, bankName) {
    const bankList = document.getElementById('bank-list');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${bankCode}</td>
        <td>${bankName}</td>
    `;
    bankList.appendChild(newRow);
}

function fetchBanks() {
    fetch('fetch_banks.php')
    .then(response => response.json())
    .then(data => {
        const bankList = document.getElementById('bank-list');
        bankList.innerHTML = ''; // Clear existing data

        data.forEach(bank => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${bank.bank_code}</td>
                <td>${bank.bank_name}</td>
            `;
            bankList.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error fetching bank data:', error);
    });
}

function closeBankPopup() {
    document.getElementById('bank-popup').style.display = 'none';
}


//
// document.addEventListener("DOMContentLoaded", function () {
//     const form = document.getElementById("product-form");
//     const inputs = Array.from(form.querySelectorAll("input, select, button"));
//     const saveButton = document.getElementById("save-product");

//     // Function to move focus to the next field
//     function moveToNextField(currentElement, direction) {
//         const currentIndex = inputs.indexOf(currentElement);
//         let nextIndex = direction === "down" ? currentIndex + 1 : currentIndex - 1;

//         if (nextIndex >= 0 && nextIndex < inputs.length) {
//             inputs[nextIndex].focus();
//         }
//     }

//     // Handle key events
//     document.addEventListener("keydown", function (event) {
//         if (event.key === "F1") {
//             event.preventDefault();
//             document.querySelector(".product-registration").scrollIntoView({ behavior: "smooth" });
//             document.getElementById("product-form").querySelector("input, select").focus(); // Focus on first input
//         }

//         const activeElement = document.activeElement;

//         if (event.key === "ArrowDown") {
//             event.preventDefault();
//             moveToNextField(activeElement, "down");
//         } else if (event.key === "ArrowUp") {
//             event.preventDefault();
//             moveToNextField(activeElement, "up");
//         } else if (event.key === "Enter") {
//             // if (activeElement.tagName !== "TEXTAREA" && activeElement.type !== "submit") {
//             //     event.preventDefault();
//             //     moveToNextField(activeElement, "down");
//             // }
//             event.preventDefault(); // Prevent form submission
//             if (activeElement.id === "save-product") {
//                 saveButton.click(); // Trigger click event to save data
//             }

//             moveToNextField(activeElement, "down");
//         }
//     }); 
//     // Click Save Button when Enter is Pressed
//     saveButton.addEventListener("keydown", function (event) {
//         if (event.key === "Enter") {
//             event.preventDefault();
//             saveButton.click();
//         }
//     });
// });

// document.addEventListener("DOMContentLoaded", function () {
//     const form = document.getElementById("product-form");
//     const inputs = Array.from(form.querySelectorAll("input, select, button"));

//     // Function to move focus to the next field
//     function moveToNextField(currentElement, direction) {
//         const currentIndex = inputs.indexOf(currentElement);
//         let nextIndex = direction === "down" ? currentIndex + 1 : currentIndex - 1;

//         if (nextIndex >= 0 && nextIndex < inputs.length) {
//             inputs[nextIndex].focus();

//             // If the next field is a select dropdown, open the options
//             if (inputs[nextIndex].tagName === "SELECT") {
//                 inputs[nextIndex].click(); // Open dropdown
//             }
//         }
//     }

//     // Handle key events
//     document.addEventListener("keydown", function (event) {
//         if (event.key === "F1") {
//             event.preventDefault();
//             document.querySelector(".product-registration").scrollIntoView({ behavior: "smooth" });
//             document.getElementById("product-form").querySelector("input, select").focus(); // Focus on first input
//         }

//         const activeElement = document.activeElement;

//         if (event.key === "ArrowDown") {
//             event.preventDefault();
//             moveToNextField(activeElement, "down");
//         } else if (event.key === "ArrowUp") {
//             event.preventDefault();
//             moveToNextField(activeElement, "up");
//         } else if (event.key === "Enter") {
//             event.preventDefault(); // Prevent form submission

//             if (activeElement.tagName === "SELECT") {
//                 activeElement.click(); // Open select dropdown
//             }

//             moveToNextField(activeElement, "down");
//         }
//     });

//     // Auto open select dropdown when focused
//     document.querySelectorAll("select").forEach(select => {
//         select.addEventListener("focus", function () {
//             this.click();
//         });
//     });
// });

// document.addEventListener("DOMContentLoaded", function () {
//     const form = document.getElementById("product-form");
//     const inputs = Array.from(form.querySelectorAll("input, select, button"));

//     // Function to move focus to the next field
//     function moveToNextField(currentElement, direction) {
//         const currentIndex = inputs.indexOf(currentElement);
//         let nextIndex = direction === "down" ? currentIndex + 1 : currentIndex - 1;

//         if (nextIndex >= 0 && nextIndex < inputs.length) {
//             inputs[nextIndex].focus();

//             // If the next field is a select dropdown, open the options
//             if (inputs[nextIndex].tagName === "SELECT") {
//                 setTimeout(() => openSelectDropdown(inputs[nextIndex]), 100); // Delay to ensure focus
//             }
//         }
//     }

//     // Function to open a select dropdown
//     function openSelectDropdown(selectElement) {
//         // Works in Chrome & Edge:
//         selectElement.size = selectElement.options.length; // Expand dropdown

//         // Works in Firefox:
//         const event = new KeyboardEvent("keydown", { key: "ArrowDown", bubbles: true });
//         selectElement.dispatchEvent(event);
//     }

//     // Handle key events for navigation
//     document.addEventListener("keydown", function (event) {
//         if (event.key === "F1") {
//             event.preventDefault();
//             document.querySelector(".product-registration").scrollIntoView({ behavior: "smooth" });
//             document.getElementById("product-form").querySelector("input, select").focus(); // Focus on first input
//         }


//         const activeElement = document.activeElement;

//         if (event.key === "ArrowDown") {
//             event.preventDefault();
//             moveToNextField(activeElement, "down");
//         } else if (event.key === "ArrowUp") {
//             event.preventDefault();
//             moveToNextField(activeElement, "up");
//         } else if (event.key === "Enter") {
//             event.preventDefault(); // Prevent form submission

//             if (activeElement.type === "radio") {
//                 activeElement.checked = true; // Select radio button
//             } else if (activeElement.tagName === "SELECT") {
//                 setTimeout(() => openSelectDropdown(activeElement), 100);
//             }

//             moveToNextField(activeElement, "down");
//         }
//     });

//     // Auto open select dropdown when focused via Tab
//     document.querySelectorAll("select").forEach(select => {
//         select.addEventListener("focus", function () {
//             setTimeout(() => openSelectDropdown(this), 100);
//         });

//         select.addEventListener("blur", function () {
//             this.size = 1; // Collapse dropdown when losing focus
//         });
//     });
// });


// document.addEventListener("DOMContentLoaded", function () {
//     // Select the first input field in the Stock Form
//     const stockFormFirstField = document.querySelector("#stock-form input, #stock-form select");

//     // Add event listener for F4 key to focus on the first input field in the stock form
//     document.addEventListener("keydown", function (event) {
//         // If the user presses the F4 key
//         if (event.key === "F4") {
//             event.preventDefault(); // Prevent default F4 action (browser/OS)
//             if (stockFormFirstField) {
//                 stockFormFirstField.focus(); // Focus on the first field of the stock form
//             }
//         }
//     });

//     // Function to navigate to the next or previous field in the stock form
//     function moveToNextField2(currentElement, direction) {
//         const allStockFields = document.querySelectorAll("#stock-form input, #stock-form select, #stock-form button");
//         const currentIndex = [...allStockFields].indexOf(currentElement);
//         let nextIndex = direction === "down" ? currentIndex + 1 : currentIndex - 1;

//         // Ensure the next field is within the stock form
//         if (nextIndex >= 0 && nextIndex < allStockFields.length) {
//             allStockFields[nextIndex].focus();
//         }
//     }

//     // Add event listener to handle arrow down, arrow up, and enter key navigation
//     document.addEventListener("keydown", function (event) {
//         const activeElement = document.activeElement;

//         // Only allow arrow key navigation inside the stock form
//         if (activeElement.closest("#stock-form")) {
//             if (event.key === "ArrowDown") {
//                 event.preventDefault(); // Prevent default scroll behavior
//                 moveToNextField2(activeElement, "down");
//             } else if (event.key === "ArrowUp") {
//                 event.preventDefault(); // Prevent default scroll behavior
//                 moveToNextField2(activeElement, "up");
//             } else if (event.key === "Enter") {
//                 event.preventDefault(); // Prevent form submission
                
//                 // Handle Enter key for different elements
//                 if (activeElement.type === "radio") {
//                     activeElement.checked = true; // Select radio button
//                 } else if (activeElement.tagName === "SELECT") {
//                     activeElement.size = activeElement.options.length; // Expand select dropdown
//                 } else if (activeElement.type === "checkbox") {
//                     activeElement.checked = !activeElement.checked; // Toggle checkbox
//                 } else if (activeElement.id === "save-stock") {
//                     activeElement.click(); // Trigger Save Stock button click
//                 }
//             }
//         }
//     });

//     // Auto open select dropdown when focused
//     document.querySelectorAll("select").forEach(select => {
//         select.addEventListener("focus", function () {
//             setTimeout(() => {
//                 this.size = this.options.length; // Expand dropdown
//             }, 100);
//         });

//         select.addEventListener("blur", function () {
//             this.size = 1; // Collapse dropdown when losing focus
//         });
//     });
// });


document.addEventListener("DOMContentLoaded", function () {
    const productForm = document.getElementById("product-form");
    const stockForm = document.getElementById("stock-form");

    const productInputs = Array.from(productForm.querySelectorAll("input, select, button"));
    const stockInputs = Array.from(stockForm.querySelectorAll("input, select, button"));

    const saveProductBtn = document.getElementById("save-product");
    const saveStockBtn = document.getElementById("save-stock");

    let currentForm = productForm; // Default to Product Form

    // Function to switch focus to a specific form
    function switchForm(targetForm) {
        currentForm = targetForm;
        currentForm.scrollIntoView({ behavior: "smooth" });

        const firstInput = currentForm.querySelector("input, select, button");
        if (firstInput) firstInput.focus();
    }

    // Function to move focus within the form
    function moveToNextField(currentElement, direction) {
        const inputs = currentForm === productForm ? productInputs : stockInputs;
        const currentIndex = inputs.indexOf(currentElement);
        let nextIndex = direction === "down" ? currentIndex + 1 : currentIndex - 1;

        if (nextIndex >= 0 && nextIndex < inputs.length) {
            inputs[nextIndex].focus();
        }
    }

    // Handle keyboard shortcuts and checkbox toggling
    document.addEventListener("keydown", function (event) {
        if (event.key === "F1") {
            event.preventDefault();
            switchForm(productForm);
        } else if (event.key === "F4") {
            event.preventDefault();
            switchForm(stockForm);
        }

        const activeElement = document.activeElement;

        if (event.key === "ArrowDown") {
            event.preventDefault();
            moveToNextField(activeElement, "down");
        } else if (event.key === "ArrowUp") {
            event.preventDefault();
            moveToNextField(activeElement, "up");
        } else if (event.key === "Enter") {
            event.preventDefault();

            // If the active element is a checkbox, toggle it
            if (activeElement.type === "checkbox") {
                activeElement.checked = !activeElement.checked; // Toggle checkbox
                moveToNextField(activeElement, "down"); // Move to the next field
            }
            // If the active element is a save button, trigger a click
            else if (activeElement === saveProductBtn || activeElement === saveStockBtn) {
                activeElement.click();
            }
            // Otherwise, move to the next field
            else {
                moveToNextField(activeElement, "down");
            }
        }
    });

    // Detect click on input fields to set the active form
    document.querySelectorAll("input, select, button").forEach(input => {
        input.addEventListener("focus", function () {
            if (productForm.contains(input)) {
                currentForm = productForm;
            } else if (stockForm.contains(input)) {
                currentForm = stockForm;
            }
        });
    });
});



// // Function to fetch and display stocks
// function fetchAllStocks2() {
//     fetch('get_all_stocks.php')
//         .then(response => response.json())
//         .then(data => {
//             if (data.status === 'success') {
//                 displayAllStocks(data.data);
//             } else {
//                 console.error('Error:', data.message);
//                 showError('Failed to load stocks');
//             }
//         })
//         .catch(error => {
//             console.error('Error:', error);
//             showError('Failed to load stocks');
//         });
// }

// // Function to display stocks in the table
// function displayAllStocks(stocks) {
//     const tableBody = document.getElementById('allStockTableBody');
//     tableBody.innerHTML = ''; // Clear existing content

//     stocks.forEach(stock => {
//         const row = document.createElement('tr');
//         row.innerHTML = `
//             <td>${stock.stock_id}</td>
//             <td>${stock.itemcode}</td>
//             <td>${stock.product_name}</td>
//             <td>${formatPrice(stock.max_retail_price)}</td>
//             <td>${formatPrice(stock.wholesale_price)}</td>
//             <td>${formatPrice(stock.super_customer_price)}</td>
//             <td>${formatPrice(stock.our_price)}</td>
//             <td>${stock.purchase_qty}</td>
//             <td>${stock.available_stock}</td>
            
//         `;
//         tableBody.appendChild(row);
//     });
// }

// // Function to format price with 2 decimal places
// function formatPrice(price) {
//     return parseFloat(price).toFixed(2);
// }

// // Function to show error message
// function showError(message) {
//     const tableBody = document.getElementById('allStockTableBody');
//     tableBody.innerHTML = `
//         <tr>
//             <td colspan="9" class="error-message">${message}</td>
//         </tr>
//     `;
// }

// document.addEventListener('DOMContentLoaded', fetchAllStocks2);

