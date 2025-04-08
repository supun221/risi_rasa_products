// document.addEventListener("keydown", function (event) {
//     if (event.ctrlKey && event.key === "b") {
//         event.preventDefault();
//         document.getElementById("barcode-input").focus();
//     }
// });

// document.addEventListener("keydown", function (e) {
//     if (e.key === "Enter") {
//         const barcodeInput = document.getElementById("barcode-input").value.trim();
//         if (barcodeInput) {
//             fetchProductDetails(barcodeInput);
//         }
//     }
// });

// document.addEventListener("keydown", (e) => {
//     if (e.key === "Insert") {
//         addToCartFromInput();
//     }
// });

// let currentProduct = null;
// let stockOptions = [];

// function fetchProductDetails(barcode) {
//     return new Promise((resolve, reject) => {
//         if (!barcode || barcode.length < 5) {
//             alert("Please enter a valid barcode.");
//             reject(new Error("Invalid barcode."));
//             return;
//         }

//         fetch("fetch_product_static.php", {
//             method: "POST",
//             headers: { "Content-Type": "application/x-www-form-urlencoded" },
//             body: `barcode=${encodeURIComponent(barcode)}`
//         })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success && data.products && data.products.length > 0) {
//                 stockOptions = data.products;
//                 if (stockOptions.length === 1) {
//                     currentProduct = stockOptions[0];
//                     updateProductUI(currentProduct);
//                     resolve();
//                 } else {
//                     showStockSelectionModal(stockOptions);
//                 }
//             } else {
//                 alert("Product not found!");
//                 resetProductUI();
//                 reject(new Error("Product not found."));
//             }
//         })
//         .catch(error => {
//             console.error("Error fetching product:", error);
//             alert("An error occurred while fetching product details.");
//             reject(error);
//         });
//     });
// }

// function updateProductUI(product) {
//     document.getElementById("product-name").value = product.product_name;
//     document.getElementById("quantity").value = 1;
// }

// function resetProductUI() {
//     document.getElementById("product-name").value = "";
//     document.getElementById("quantity").value = "";
// }

// // Display modal if multiple stocks exist
// function showStockSelectionModal(stockOptions) {
//     const modal = document.getElementById("stock-selection-modal");
//     const modalBody = document.getElementById("stock-selection-body");

//     modalBody.innerHTML = "";
//     stockOptions.forEach((product, index) => {
//         const row = document.createElement("tr");
//         row.dataset.index = index;
//         row.innerHTML = `
//             <td>${product.stock_id}</td>
//             <td>${product.product_name}</td>
//             <td>${product.available_stock}</td>
//             <td>${parseFloat(product.our_price).toFixed(2)}</td>
//         `;
//         row.addEventListener("click", function() {
//             selectStock(index);
//         });
//         modalBody.appendChild(row);
//     });

//     modal.style.display = "block";

//     // Close modal on Enter key
//     document.addEventListener("keydown", function (e) {
//         if (e.key === "Enter") {
//             const selectedRow = document.querySelector("#stock-selection-body tr.selected");
//             if (selectedRow) {
//                 selectStock(selectedRow.dataset.index);
//             }
//         }
//     });

//     // Highlight row on hover
//     document.querySelectorAll("#stock-selection-body tr").forEach(row => {
//         row.addEventListener("mouseenter", function () {
//             document.querySelectorAll("#stock-selection-body tr").forEach(r => r.classList.remove("selected"));
//             this.classList.add("selected");
//         });
//     });

//     document.getElementById("select-stock-btn").addEventListener("click", function () {
//         const selectedRow = document.querySelector("#stock-selection-body tr.selected");
//         if (selectedRow) {
//             selectStock(selectedRow.dataset.index);
//         }
//     });
// }

// function selectStock(index) {
//     currentProduct = stockOptions[index];
//     updateProductUI(currentProduct);
//     document.getElementById("stock-selection-modal").style.display = "none";
// }

// function processAddToCart(quantity) {
//     const cartItem = { ...currentProduct, quantity };
//     addToCart(cartItem);
// }

// function addToCartFromInput() {
//     const barcodeInput = document.getElementById("barcode-input").value.trim();
//     const quantity = parseFloat(document.getElementById("quantity").value) || 1;

//     if (!barcodeInput) {
//         alert("Please enter a valid barcode.");
//         return;
//     }

//     if (!currentProduct || currentProduct.barcode !== barcodeInput) {
//         fetchProductDetails(barcodeInput).then(() => {
//             if (currentProduct) {
//                 processAddToCart(quantity);
//             }
//         });
//     } else {
//         processAddToCart(quantity);
//     }
// }

// function addToCart(product) {
//     let notifier = new AWN();
//     if (parseInt(product.available_stock) < 0) {
//         notifier.warning("Remaining stock is below zero!");
//     }

//     const cartTableBody = document.querySelector("#st-cart-tb tbody");
//     const rowId = `${product.stock_id}_${product.itemcode}`;
//     const existingRow = Array.from(cartTableBody.rows).find(row => row.dataset.rowId === rowId);

//     if (existingRow) {
//         const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
//         qtyCell.value = parseFloat(qtyCell.value) + product.quantity;
//     } else {
//         const newRow = document.createElement("tr");
//         newRow.dataset.rowId = rowId;
//         newRow.innerHTML = `
//             <td>${cartTableBody.rows.length + 1}</td>
//             <td>${product.stock_id}</td>
//             <td>${product.barcode}</td>
//             <td>${product.product_name}</td>
//             <td>${parseFloat(product.our_price).toFixed(2)}</td>
//             <td><input type="number" class="qty_${rowId}" value="${product.quantity}" min="1"/></td>
//             <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
//         cartTableBody.appendChild(newRow);
//     }
// }

// function removeFromCart(rowId) {
//     const row = document.querySelector(`[data-row-id='${rowId}']`);
//     if (row) row.remove();
// }

document.addEventListener("keydown", function (event) {
  if (event.ctrlKey && event.key === "b") {
    event.preventDefault();
    document.getElementById("barcode-input").focus();
  }
});

document.addEventListener("keydown", function (e) {
  if (e.key === "Enter") {
    const barcodeInput = document.getElementById("barcode-input").value.trim();
    if (barcodeInput) {
      fetchProductDetails(barcodeInput);
    }
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Insert" && isModalOpen()) {
    addSelectedStockToCart();
  } else if (e.key === "Insert") {
    addToCartFromInput();
  }
});

let currentProduct = null;
let stockOptions = [];
let selectedIndex = 0;

function fetchProductDetails(barcode) {
  return new Promise((resolve, reject) => {
    if (!barcode) {
      alert("Please enter a valid barcode.");
      reject(new Error("Invalid barcode."));
      return;
    }

    fetch("fetch_product_static.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `barcode=${encodeURIComponent(barcode)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.products && data.products.length > 0) {
          stockOptions = data.products;
          if (stockOptions.length === 1) {
            currentProduct = stockOptions[0];
            updateProductUI(currentProduct);
            resolve();
          } else {
            showStockSelectionModal(stockOptions);
          }
        } else {
          alert("Product not found!");
          resetProductUI();
          reject(new Error("Product not found."));
        }
      })
      .catch((error) => {
        console.error("Error fetching product:", error);
        alert("An error occurred while fetching product details.");
        reject(error);
      });
  });
}

function updateProductUI(product) {
  document.getElementById("product-name").value = product.product_name;
  document.getElementById("quantity").value = 1;
}

function resetProductUI() {
  document.getElementById("product-name").value = "";
  document.getElementById("quantity").value = "";
}

// Check if modal is open
function isModalOpen() {
  return (
    document.getElementById("stock-selection-modal").style.display === "block"
  );
}

// Display modal if multiple stocks exist
function showStockSelectionModal(stockOptions) {
  const modal = document.getElementById("stock-selection-modal");
  const modalBody = document.getElementById("stock-selection-body");

  modalBody.innerHTML = "";
  stockOptions.forEach((product, index) => {
    const row = document.createElement("tr");
    row.dataset.index = index;
    row.innerHTML = `
          <td>${product.stock_id}</td>
          <td>${product.product_name}</td>
          <td>${product.available_stock}</td>
          <td>${parseFloat(product.our_price).toFixed(2)}</td>
      `;
    row.addEventListener("click", function () {
      selectStock(index);
    });
    modalBody.appendChild(row);
  });

  modal.style.display = "block";
  selectedIndex = 0;
  highlightRow(selectedIndex);

  document.addEventListener("keydown", handleStockSelectionNavigation);
  document.querySelectorAll("#stock-selection-body tr").forEach((row) => {
    row.addEventListener("mouseenter", function () {
      document
        .querySelectorAll("#stock-selection-body tr")
        .forEach((r) => r.classList.remove("selected"));
      this.classList.add("selected");
    });
  });
}

// Highlight selected row
function highlightRow(index) {
  const rows = document.querySelectorAll("#stock-selection-body tr");
  rows.forEach((row) => row.classList.remove("selected"));
  if (rows[index]) {
    rows[index].classList.add("selected");
  }
}

// Handle keyboard navigation
function handleStockSelectionNavigation(event) {
  if (!isModalOpen()) return;

  const rows = document.querySelectorAll("#stock-selection-body tr");

  if (event.key === "ArrowDown") {
    selectedIndex = (selectedIndex + 1) % rows.length;
    highlightRow(selectedIndex);
  } else if (event.key === "ArrowUp") {
    selectedIndex = (selectedIndex - 1 + rows.length) % rows.length;
    highlightRow(selectedIndex);
  } else if (event.key === "Enter") {
    selectStock(selectedIndex);
  } else if (event.key === "Insert") {
    addSelectedStockToCart();
  }
}

// Select stock
function selectStock(index) {
  currentProduct = stockOptions[index];
  updateProductUI(currentProduct);
  closeModal();
}

// Close modal
function closeModal() {
  document.getElementById("stock-selection-modal").style.display = "none";
  document.removeEventListener("keydown", handleStockSelectionNavigation);
}

// Add selected stock to cart
function addSelectedStockToCart() {
  if (stockOptions.length > 0 && selectedIndex >= 0) {
    selectStock(selectedIndex);
    processAddToCart(1);
  }
}

function processAddToCart(quantity) {
  const cartItem = { ...currentProduct, quantity };
  addToCart(cartItem);
}

function addToCart(product) {
  let notifier = new AWN();
  if (parseInt(product.available_stock) < 0) {
    notifier.warning("Remaining stock is below zero!");
  }

  const cartTableBody = document.querySelector("#st-cart-tb tbody");
  const rowId = `${product.stock_id}_${product.itemcode}`;

  // Check if the product already exists in the cart
  const existingRow = Array.from(cartTableBody.rows).find(
    (row) => row.dataset.rowId === rowId
  );

  if (existingRow) {
    const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
    qtyCell.value = parseFloat(qtyCell.value) + product.quantity;
  } else {
    const newRow = document.createElement("tr");
    newRow.dataset.rowId = rowId;
    newRow.dataset.remainingStock = product.available_stock;
    newRow.dataset.productRealId = product.id;
    newRow.dataset.ourPrice = product.our_price;
    newRow.dataset.wholesalePrice = product.wholesale_price;

    newRow.innerHTML = `
      <td class="item_no_${rowId}">${cartTableBody.rows.length + 1}</td>
      <td class="stock_id_${rowId}">${product.stock_id}</td>
      <td class="item_id_${rowId}">${product.barcode}</td>
      <td style="text-transform: capitalize;" class="product_name_${rowId}">${
      product.product_name
    }</td>
      <td class="mrp_${rowId}">${parseFloat(product.our_price).toFixed(2)}</td>
      <td><input type="number" class="qty-indicator qty_${rowId}" value="${
      product.quantity
    }" min="1"/></td>
      <td><i class="fa-solid fa-delete-left remove-btn" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>
    `;
    cartTableBody.appendChild(newRow);
  }
}

function removeFromCart(rowId) {
  const row = document.querySelector(`[data-row-id='${rowId}']`);
  if (row) row.remove();
}

function addToCartFromInput() {
  const barcodeInput = document.getElementById("barcode-input").value.trim();
  const quantity = parseFloat(document.getElementById("quantity").value) || 1;

  if (!barcodeInput) {
    alert("Please enter a valid barcode.");
    return;
  }

  if (!currentProduct || currentProduct.barcode !== barcodeInput) {
    fetchProductDetails(barcodeInput).then(() => {
      if (currentProduct) {
        processAddToCart(quantity);
      }
    });
  } else {
    processAddToCart(quantity);
  }
}

document.addEventListener("keydown", (event) => {
  if (event.key === "PageDown") {
    event.preventDefault();
    switchToNextCell();
  }
});

const switchToNextCell = () => {
  const inputs = Array.from(
    document.querySelectorAll("#barcode-reader-tb tbody input")
  ).filter((input) => !input.disabled && !input.hidden);
  const currentlyActiveElement = document.activeElement;

  const currentIndex = Array.from(inputs).indexOf(currentlyActiveElement);
  if (currentIndex !== -1 && currentIndex < inputs.length - 1) {
    inputs[currentIndex + 1].focus();
  } else {
    inputs[0].focus();
  }
};

const showStockTransferModal = () => {
  const barcodeInput = document.getElementById("barcode-input");
  const transferOrderModal = document.getElementById("transfer-order-modal");
  transferOrderModal.style.display = "flex";
  barcodeInput.focus();
};

const hideStockTransferModal = () => {
  const transferOrderModal = document.getElementById("transfer-order-modal");
  transferOrderModal.style.display = "none";
};

function submitStockTransfer() {
  let notifier = new AWN();
  const currentTime = Math.floor(Date.now() / 1000);
  const transferredBranch = document
    .getElementById("branch_selector")
    .value.trim();
  const branchParts = transferredBranch.split(" ");
  const firstPart = branchParts[0];

  const stockTransferId = `${currentTime}/${firstPart}`;

  const items = [];
  const cartRows = document.querySelectorAll("#st-cart-tb tbody tr");
  cartRows.forEach((row) => {
    items.push({
      stock_id: row.querySelector(`[class^="stock_id_"]`).innerText,
      item_name: row.querySelector(`[class^="product_name_"]`).innerText,
      item_barcode: row.querySelector(`[class^="item_id_"]`).innerText,
      num_of_qty: parseInt(row.querySelector(`input[type="number"]`).value),
    });
  });

  fetch("./create_stock_transfer.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      stock_transfer_id: stockTransferId,
      transferred_branch: transferredBranch,
      items: items,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      if (data.status === "success") {
        notifier.success(data.message);
      } else {
        notifier.alert(data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

let transferred_stocks = [];
let received_stocks = [];

document.addEventListener("DOMContentLoaded", function () {
  fetchStockTransfersByTransferringBranch();
});

function fetchStockTransfersByTransferringBranch() {
  let notifier = new AWN();
  const transferredStocks = document.getElementById(
    "transferred-stocks-pholder"
  );

  fetch("./get_transerring_stock.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        transferredStocks.innerHTML = "";

        let transferred_stocks = [...data.data];

        transferred_stocks.forEach((item) => {
          const divElement = document.createElement("div");
          divElement.setAttribute("id", `item_${item.stock_transfer_id}`);
          divElement.setAttribute("class", "st_record_element");
          divElement.innerHTML = `
            <table>
              <tr>
                <td style="width: 220px;">
                  <span><span class="st-record-info">ID:</span> ${item.stock_transfer_id}</span>
                </td>
                <td style="width: 200px;">
                  <span><span class="st-record-info">Date:</span> ${item.issue_date}</span>
                </td>
                <td style="width: 170px;">
                  <span><span class="st-record-info">To:</span> ${item.transferred_branch}</span>
                </td>
                <td style="width: 195px;">
                  <span><span class="st-record-info">By:</span> ${item.issuer_name}</span>
                </td>
                <td>
                    <span>
                        <button class="st-info-op-btn" onclick="showStockItemInfoTable('${item.stock_transfer_id}')">View</button>
                        <button class="st-info-op-btn" onclick="printStockTransfer('${item.stock_transfer_id}')" >Print</button>
                    </span>
                </td>
              </tr>
            </table>
            <br>
            <table class="st-items-table" id="tb_${item.stock_transfer_id}">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Barcode</th>
                  <th>Quantity</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          `;

          transferredStocks.appendChild(divElement);
          fetchStockTransferItems(item.stock_transfer_id);
        });
      } else {
        notifier.alert(data.message);
      }
    })
    .catch((error) => console.error("Request failed:", error));
}

async function printStockTransfer(stockTransferId) {
  try {
    let response = await fetch(
      `./get_stock_transfer_details.php?stock_transfer_id=${stockTransferId}`
    );
    let data = await response.json();

    if (data.status !== "success") {
      alert("Failed to load stock transfer details!");
      return;
    }

    const { stock_transfer, items } = data.data;

    // Initialize jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add a company logo (Optional: Replace with an actual image URL)
    // const img = new Image();
    // img.src = "../../assets/images/cart.png"; // Ensure logo.png is available in the project
    // doc.addImage(img, "PNG", 10, 5, 30, 20); // X, Y, Width, Height

    // Title
    doc.setFont("helvetica", "bold");
    doc.setFontSize(18);
    doc.text("Stock Transfer Issue", 70, 15);

    // Stock Transfer Details (Formatted)
    doc.setFontSize(12);
    doc.setFont("helvetica", "normal");
    let detailsY = 30;
    // Set Bold for Labels
    doc.setFont("helvetica", "bold");
    doc.text("Stock Transfer ID:", 10, detailsY);
    doc.text("Issue Date:", 10, detailsY + 10);
    doc.text("From:", 10, detailsY + 20);
    doc.text("To:", 10, detailsY + 30);
    doc.text("Issued By:", 10, detailsY + 40);

    // Set Normal for Values
    doc.setFont("helvetica", "normal");
    doc.text(stock_transfer.stock_transfer_id, 50, detailsY);
    doc.text(stock_transfer.issue_date, 50, detailsY + 10);
    doc.text(stock_transfer.transferring_branch, 50, detailsY + 20);
    doc.text(stock_transfer.transferred_branch, 50, detailsY + 30);
    doc.text(stock_transfer.issuer_name, 50, detailsY + 40);

    // Draw Table Header
    let startY = detailsY + 60;
    doc.setFont("helvetica", "bold");
    doc.setFillColor(200, 200, 200); // Gray background
    doc.rect(10, startY - 6, 190, 10, "F"); // X, Y, Width, Height, Fill
    doc.text("Item Name", 15, startY);
    doc.text("Barcode", 90, startY);
    doc.text("Quantity", 160, startY);

    // Draw Table Rows
    doc.setFont("helvetica", "normal");
    let rowY = startY + 10;
    items.forEach((item, index) => {
      doc.rect(10, rowY - 6, 190, 10); // Border for row
      doc.text(item.item_name, 15, rowY);
      doc.text(item.item_barcode, 90, rowY);
      doc.text(item.num_of_qty.toString(), 160, rowY);
      rowY += 10;
    });

    // Footer with Signature Line
    doc.setFontSize(10);
    doc.text("Authorized Signature: ____________________", 10, rowY + 20);
    doc.text(`Page 1 of 1`, 180, rowY + 30);

    // Save or Open PDF
    doc.save(`Stock_Transfer_${stock_transfer.stock_transfer_id}.pdf`);
  } catch (error) {
    console.error("Error generating PDF:", error);
  }
}

function fetchStockTransfersByTransferredBranch() {
  let notifier = new AWN();
  const transferredStocks = document.getElementById("received-stocks-pholder");
  fetch("./get_transferred_stock.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        received_stocks = [...data.data];

        received_stocks.forEach((item) => {
          const divElement = document.createElement("div");
          divElement.setAttribute("id", `item_${item.stock_transfer_id}`);
          divElement.setAttribute("class", "st_record_element");
          divElement.innerHTML = `
            <table>
              <tr>
                <td style="width: 220px;">
                  <span><span class="st-record-info">ID:</span> ${item.stock_transfer_id}</span>
                </td>
                <td style="width: 200px;">
                  <span><span class="st-record-info">Date:</span> ${item.issue_date}</span>
                </td>
                <td style="width: 170px;">
                  <span><span class="st-record-info">From:</span> ${item.transferring_branch}</span>
                </td>
                <td style="width: 195px;">
                  <span><span class="st-record-info">By:</span> ${item.issuer_name}</span>
                </td>
                <td>
                   <span>
                      <button class="st-info-op-btn" onclick="showReceivedStockItemInfoTable('${item.stock_transfer_id}')">View</button>
                      <button class="st-info-op-btn">Print</button>
                   </span>
                </td>
              </tr>
            </table>
           
            <br>
            <table class="st-items-table" id="received_tb_${item.stock_transfer_id}">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Barcode</th>
                  <th>Quantity</th>
                  <th>State</th>
                  <th></th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          `;

          transferredStocks.appendChild(divElement);
          fetchStockTransferItemsUpdateInventory(
            item.stock_transfer_id,
            item.transferring_branch
          );
        });
      } else {
        notifier.alert(data.message);
      }
    })
    .catch((error) => console.error("Request failed:", error));
}

const showStockItemInfoTable = (tableId) => {
  const targetTable = document.getElementById(`tb_${tableId}`);
  targetTable.classList.toggle("show-st-info-table");
};

const showReceivedStockItemInfoTable = (tableId) => {
  const targetTable = document.getElementById(`received_tb_${tableId}`);
  targetTable.classList.toggle("show-st-info-table");
};

function fetchStockTransferItems(stockTransferId) {
  let notifier = new AWN();

  fetch(`./get_transferred_stock_item.php?stock_transfer_id=${stockTransferId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        let stockItems = [...data.data];

        const targetTable = document.getElementById(`tb_${stockTransferId}`);
        if (!targetTable) {
          console.error(
            `Table not found for stock transfer ID: ${stockTransferId}`
          );
          return;
        }

        const targetTableBody = targetTable.querySelector("tbody");
        if (!targetTableBody) {
          console.error(`tbody not found for table #tb_${stockTransferId}`);
          return;
        }

        targetTableBody.innerHTML = "";

        stockItems.forEach((item) => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${item.item_name}</td>
            <td>${item.item_barcode}</td>
            <td>${item.num_of_qty}</td>
          `;
          targetTableBody.appendChild(row);
        });
      } else {
        notifier.alert(data.message);
      }
    })
    .catch((error) => console.error("Request failed:", error));
}

function fetchStockTransferItemsUpdateInventory(
  stockTransferId,
  transferringBranch
) {
  let notifier = new AWN();

  fetch(`./get_transferred_stock_item.php?stock_transfer_id=${stockTransferId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        let stockItems = [...data.data];

        const targetTable = document.getElementById(
          `received_tb_${stockTransferId}`
        );
        if (!targetTable) {
          console.error(
            `Receiver table not found for stock transfer ID: ${stockTransferId}`
          );
          return;
        }

        const targetTableBody = targetTable.querySelector("tbody");
        if (!targetTableBody) {
          console.error(`tbody not found for table #tb_${stockTransferId}`);
          return;
        }

        targetTableBody.innerHTML = "";

        stockItems.forEach((item) => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${item.item_name}</td>
            <td>${item.item_barcode}</td>
            <td>${item.num_of_qty}</td>
            <td style="text-transform:capitalize;">${
              item.state === "not_added"
                ? "<i class='fa-solid fa-clock'></i>"
                : item.state == "rejected"
                ? "<i class='fa-solid fa-rotate-left'></i>"
                : "<i class='fa-solid fa-circle-check'></i>"
            } ${item.state}</td>
            <td>
              ${
                item.state === "not_added"
                  ? `<button class='st-info-op-btn add-stock-btn' data-stock-id='${item.stock_id}' data-available-stock='${item.num_of_qty}' data-supplier='Risi Rasa' data-transfer-id='${item.id}' data-stock-transfer-id='${stockTransferId}'>Add</button>
                  <button class='st-info-op-btn reject-stock-btn' onclick="showRejectTransferModal('${item.id}', '${item.stock_id}', '${item.item_barcode}', '${item.num_of_qty}')">Reject</button>
                  `
                  : ""
              }
            </td>
          `;
          targetTableBody.appendChild(row);
        });
      } else {
        notifier.alert(data.message);
      }
    })
    .catch((error) => console.error("Request failed:", error));
}

document.addEventListener("DOMContentLoaded", function () {
  fetchStockTransfersByTransferredBranch();
  fetchStockTransfersByTransferringBranch();
});

//
document.addEventListener("click", function (event) {
  if (event.target && event.target.classList.contains("add-stock-btn")) {
    const stockId = event.target.dataset.stockId; // Get stock_id from button data attribute
    const availableStock = event.target.dataset.availableStock; // Get available stock from button data attribute
    const supplier = event.target.dataset.supplier;
    const transferId = event.target.dataset.transferId;
    const stockTransferId = event.target.dataset.stockTransferId;

    if (!stockId || !availableStock) {
      alert("Invalid stock ID or available stock.");
      return;
    }

    fetchStockDetailsAndCreateNew(
      stockId,
      availableStock,
      supplier,
      transferId,
      stockTransferId
    );
  }
});

function fetchStockDetailsAndCreateNew(
  stockId,
  availableStock,
  supplier,
  transferId,
  stockTransferId
) {
  let notifier = new AWN();

  fetch(`./get_stock_entry.php?stock_id=${stockId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && data.stock_entry) {
        const stockDetails = data.stock_entry;

        // Find the next available stock_id (assuming it starts from 10001)
        fetch("./get_next_stock_id.php")
          .then((response) => response.json())
          .then((idData) => {
            if (idData.status === "success" && idData.next_stock_id) {
              const newStockId = idData.next_stock_id;
              createNewStockEntry(
                stockDetails,
                newStockId,
                availableStock,
                supplier,
                transferId,
                stockTransferId
              );
            } else {
              notifier.alert("Failed to fetch new stock ID.");
            }
          })
          .catch((error) => {
            console.error("Error fetching next stock ID:", error);
            notifier.alert("Error fetching next stock ID.");
          });
      } else {
        notifier.alert("Stock details not found.");
      }
    })
    .catch((error) => {
      console.error("Request failed:", error);
      notifier.alert("Error fetching stock details.");
    });
}

function createNewStockEntry(
  stockDetails,
  newStockId,
  availableStock,
  supplier,
  transferId,
  stockTransferId
) {
  let notifier = new AWN();

  const newStockData = {
    stock_id: newStockId,
    supplier: supplier,
    itemcode: stockDetails.itemcode,
    product_name: stockDetails.product_name,
    purchase_qty: availableStock,
    unit: stockDetails.unit,
    available_stock: availableStock,
    cost_price: stockDetails.cost_price,
    wholesale_price: stockDetails.wholesale_price,
    max_retail_price: stockDetails.max_retail_price,
    super_customer_price: stockDetails.super_customer_price,
    our_price: stockDetails.our_price,
    expire_date: stockDetails.expire_date,
    discount_percent: stockDetails.discount_percent,
    barcode: stockDetails.barcode,
    deal_price: stockDetails.deal_price,
    start_date: stockDetails.start_date,
    end_date: stockDetails.end_date,
    total_cost_amount: stockDetails.total_cost_amount,
    transferId: transferId,
  };

  fetch("./add_new_stock_entry.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(newStockData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        fetchStockTransferItemsUpdateInventory(stockTransferId, supplier);
        notifier.success("New stock added successfully!");
      } else {
        notifier.alert("Failed to add new stock.");
      }
    })
    .catch((error) => {
      console.error("Error adding new stock:", error);
      notifier.alert("Error adding new stock.");
    });
}

function rejectStockTransfer() {
  let notifier = new AWN();
  const rejectionNote = document.getElementById("st-rejetion-note").value;
  const recordId = document.getElementById("rejecting-record-id").value;
  const stockId = document.getElementById("rejecting-record-stockid").value;
  const barcode = document.getElementById("rejecting-record-barcode").value;
  const qty = document.getElementById("rejecting-record-qty").value;
  if (!rejectionNote.trim()) {
    notifier.alert("Please provide a reason for rejecting this item.");
    return;
  }
  if (recordId == "null") {
    notifier.alert("Record id not found!");
    return;
  }
  const formData = new FormData();
  formData.append("record_id", recordId);
  formData.append("rejected_reason", rejectionNote);
  fetch("reject_transfer.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        revertStockLevels(barcode, stockId, qty);
        notifier.success("Stock transfer rejected successfully.");
        document.getElementById("rejecting-record-barcode").value = "null";
        document.getElementById("rejecting-record-stockid").value = "null";
        document.getElementById("rejecting-record-qty").value = "null";
        location.reload();
      } else {
        notifier.alert("Error: " + data.error);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      notifier.alert("An error occurred. Please try again.");
    });
}

const showRejectTransferModal = (id, stockId, barcode, qty) => {
  const rejectTransferModalContainer = document.querySelector(
    ".st-rejection-modal"
  );
  document.getElementById("rejecting-record-id").value = id;
  document.getElementById("rejecting-record-barcode").value = barcode;
  document.getElementById("rejecting-record-stockid").value = stockId;
  document.getElementById("rejecting-record-qty").value = qty;
  rejectTransferModalContainer.style.display = "flex";
};

const hideRejectTransferModal = () => {
  const rejectTransferModalContainer = document.querySelector(
    ".st-rejection-modal"
  );
  document.getElementById("rejecting-record-id").value = "null";
  rejectTransferModalContainer.style.display = "none";
};

function revertStockLevels(itemBarcode, stockId, quantity) {
  let notifier = new AWN();
  if (!itemBarcode || !stockId || quantity <= 0) {
    console.error("Invalid parameters for stock update.");
    return;
  }
  console.log(`${itemBarcode} ${stockId} ${quantity}`);
  const apiUrl = "./revert_available_stocks.php";

  const requestData = {
    item_barcode: itemBarcode,
    stock_id: stockId,
    quantity: quantity,
  };
  fetch(apiUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(requestData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        console.log("Stock updated successfully:", data.new_stock);
      } else {
        console.error("Stock update failed:", data.message);
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Request failed:", error);
      alert("Stock update request failed.");
    });
}
