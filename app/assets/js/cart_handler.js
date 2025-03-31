let products = [];
let itemBarcodeScannerLock = true;

document.addEventListener("DOMContentLoaded", async () => {
  try {
    const barcodeInput = document.getElementById("barcode-input");
    barcodeInput.focus();
    const response = await fetch("getAllProducts.php");
    const data = await response.json();
    if (data.error) {
      console.error(data.error);
      return;
    }
    products = data.map((product) => product);
  } catch (error) {
    console.error("Failed to fetch products:", error);
  }
});

document.addEventListener("keydown", function (event) {
  if (event.ctrlKey && event.key === "b") {
    event.preventDefault();
    const barcodeInput = document.getElementById("barcode-input");
    barcodeInput.focus();
  }
});

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

document.addEventListener("keydown", function (event) {
  if (event.altKey && event.key === "s") {
    event.preventDefault();
    showIwbModal();
  }
});

document.addEventListener("keydown", function (e) {
  if (itemBarcodeScannerLock && e.key === "Enter") {
    const qtyPlaceHolder = document.getElementById("quantity");
    const bcodeInputField = document.getElementById("barcode-input");
    const barcodeInput = bcodeInputField.value.trim();
    // this function is used to handle the cart in previous way (if same barcode scanned twice, qty will be increased.)
    // fetchProduct(barcodeInput);
    fetchProductDetails(barcodeInput);
    qtyPlaceHolder.focus();
  }
});

let currentRowIndex = 0;
let currentInputIndex = 0;

// const showMisModal = (products) => {
//   const misModal = document.getElementById("iwb-modal");
//   const misTableBody = document.querySelector("#iwb-table tbody");

//   misTableBody.innerHTML = "";
//   products.forEach((product, index) => {
//     const row = document.createElement("tr");
//     row.dataset.productId = product.id;
//     row.dataset.productDetails = JSON.stringify(product); // Store product details
//     row.innerHTML = `
//           <td>${product.stock_id}</td>
//           <td>${product.product_name}</td>
//           <td>${product.barcode}</td>
//           <td>${parseFloat(product.max_retail_price).toFixed(2)}</td>
//           <td><button onclick="selectProductFromModal(this)">Select</button></td>
//       `;
//     misTableBody.appendChild(row);
//   });

//   misModal.classList.add("show-iwb-modal");
// };

const selectProductFromModal = (button) => {
  const row = button.closest("tr");
  const productDetails = JSON.parse(row.dataset.productDetails);
  currentProduct = productDetails; // Update current product globally
  updateProductUI(productDetails); // Update the UI fields
  hideIwbModal(); // Close the modal
};

const showMisModal = (products) => {
  const misModal = document.getElementById("mis-modal");
  const misTableBody = document.querySelector("#mis-table tbody");

  console.log(products);

  misTableBody.innerHTML = "";
  products.forEach((product, index) => {
    const row = document.createElement("tr");
    if (index === 0) {
      row.classList.add("selected-row");
    }
    row.dataset.productId = product.id;
    row.innerHTML = `
      <td>${product.stock_id}</td>
      <td>${product.product_name}</td>
      <td>${product.barcode}</td>
      <td>${parseFloat(product.max_retail_price).toFixed(2)}</td>
    `;
    misTableBody.appendChild(row);
  });

  // Show the modal and add the class
  misModal.classList.add("show-mis-modal");

  // Set focus on the modal or the table body
  misModal.setAttribute("tabindex", "-1");
  misModal.focus();

  // Add the keydown event listener
  document.addEventListener("keydown", handleArrowKeyNavigation);
};

const handleArrowKeyNavigation = (e) => {
  const rows = document.querySelectorAll("#mis-table tbody tr");
  if (!rows.length) return;

  if (e.key === "ArrowDown" || e.key === "ArrowUp") {
    e.preventDefault();
  } else {
    return;
  }
  rows[currentRowIndex].classList.remove("selected-row");
  if (e.key === "ArrowDown") {
    currentRowIndex = (currentRowIndex + 1) % rows.length; // Wrap to the top
  } else if (e.key === "ArrowUp") {
    currentRowIndex = (currentRowIndex - 1 + rows.length) % rows.length; // Wrap to the bottom
  }
  rows[currentRowIndex].classList.add("selected-row");
  rows[currentRowIndex].scrollIntoView({ block: "nearest" });
};

const hideMisModal = () => {
  const misModal = document.getElementById("mis-modal");
  misModal.classList.remove("show-mis-modal");
};

const showIwbModal = () => {
  const iwbModal = document.getElementById("iwb-modal");
  const iwbInputField = document.getElementById("iwb-search-field");
  iwbModal.classList.add("show-iwb-modal");
  iwbInputField.focus();
};

const hideIwbModal = () => {
  const iwbModal = document.getElementById("iwb-modal");
  iwbModal.classList.remove("show-iwb-modal");
};

// custom item search
const searchProducts = () => {
  const iwbInput = document
    .getElementById("iwb-search-field")
    .value.trim()
    .toLowerCase();
  const filteredProducts = products.filter((product) =>
    product.product_name.toLowerCase().includes(iwbInput)
  );

  updateIwbTable(filteredProducts);
};

const updateIwbTable = (filteredProducts) => {
  const tableBody = document.querySelector("#iwb-table tbody");
  tableBody.innerHTML = "";

  if (filteredProducts.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="5">No products found</td></tr>`;
    return;
  }

  filteredProducts.forEach((product) => {
    const row = document.createElement("tr");
    let productString = product;
    let discount = 0;
    let freeIssueCount = 0;
    productString = {
      ...productString,
      discount,
      freeIssueCount,
    };
    productString = JSON.stringify(productString)
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
    row.innerHTML = `
      <td>${product.stock_id}</td>
      <td>${product.product_name}</td>
      <td>${parseFloat(product.our_price).toFixed(2)}</td>
      <td>${parseFloat(product.max_retail_price).toFixed(2)}</td>
      <td><button class="add-item-btn-iwb">Add Item</button></td>
    `;

    row.querySelector(".add-item-btn-iwb").addEventListener("click", () => {
      updateProductUIEnhanced(product);
    });
    tableBody.appendChild(row);
  });
};

function fetchProduct(barcode) {
  fetch("fetch_product_static.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `barcode=${encodeURIComponent(barcode)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const products = data.products;
        if (products.length > 1) {
          showMisModal(products);
        }
      } else {
        console.log(data);
        let notifier = new AWN();
        notifier.alert("Product Not Exist in Database!");
      }
    })
    .catch((error) => console.error("Error fetching product:", error));
}
// function selectProduct(product) {
//   // Populate the input fields in the barcode-reader-items section with the selected product
//   document.getElementById("barcode-input").value = product.barcode;
//   document.getElementById("product-name").value = product.product_name;
//   document.getElementById("quantity").value = 1; // Default quantity
//   document.getElementById("our-price").value = product.our_price;

//   // Show the barcode-reader-items section (if hidden)
//   const barcodeReaderSection = document.getElementById("barcode-reader-items");
//   barcodeReaderSection.style.display = "table-row-group"; // Make sure the table is visible

//   // Hide the multiple item selector modal
//   hideMisModal();
// }

function selectProduct(product) {
  const barcodeHolder = document.getElementById("barcode-input");
  addToCart(product);
  barcodeHolder.value = "";
  hideMisModal();
  barcodeHolder.focus();
}

// const handleInsertKeyPress = (products) => {
//   const rows = document.querySelectorAll("#mis-table tbody tr");
//   if (rows.length === 0) return;

//   const highlightedRow = rows[currentRowIndex];
//   const productId = highlightedRow.dataset.productId;
//   console.log(productId + "is pid");

//   const selectedProduct = products.find((product) => product.id == productId);
//   console.log(products);

//   if (selectedProduct) {
//     // selectProduct(selectedProduct);
//     updateProductUI(selectedProduct);
//     hideMisModal();
//   } else {
//     let notifier = new AWN();
//     notifier.alert("data record not exist!");
//   }
// };

// document.addEventListener("keydown", (e) => {
//   const misModal = document.getElementById("mis-modal");
//   if (e.key === "End" && misModal.classList.contains("show-mis-modal")) {
//     handleInsertKeyPress(products);
//   }
// });

// if multiple product selection menu is off this will be executed! Do not delete!
document.addEventListener("keydown", (e) => {
  const barcodeValueHolder = document.getElementById("barcode-input");
  const measurementUnit = document.getElementById("mes-unit-name");
  const prodName = document.getElementById("product-name");
  const qty = document.getElementById("quantity");
  const ourPrice = document.getElementById("our-price");
  const discount = document.getElementById("bcode_discount");
  const free_issue = document.getElementById("bcode_fi");
  const misModal = document.getElementById("mis-modal");
  if (e.key === "Insert" && !misModal.classList.contains("show-mis-modal")) {
    if (measurementUnit.value != "null") {
      measurementConverter(barcodeValueHolder.value, measurementUnit.value);
    }
    addToCartFromInput();
    barcodeValueHolder.value = "";
    prodName.value = "";
    qty.value = "";
    ourPrice.value = "";
    discount.value = "";
    free_issue.value = "";
    barcodeValueHolder.focus();
  }
});

// function addToCart(product) {
//   let finalPrice = 0;
//   let isPromotion = false;
//   const promoStartDate = new Date(product.start_date);
//   const promoEndDate = new Date(product.end_date);
//   const currentDate = new Date();

//   promoStartDate.setHours(0, 0, 0, 0);
//   promoEndDate.setHours(23, 59, 59, 999);
//   currentDate.setHours(0, 0, 0, 0);

//   if (currentDate >= promoStartDate && currentDate <= promoEndDate) {
//     isPromotion = true;
//     finalPrice = product.deal_price;
//   } else {
//     isPromotion = false;
//     finalPrice = product.our_price;
//   }

//   const wholesalePrice = document.getElementById("alt-wholesale");
//   const mrp = document.getElementById("alt-mrp");
//   const ourPrice = document.getElementById("alt-our-price");
//   const itemName = document.getElementById("info-item");
//   const cartTableBody = document.querySelector("#pos-cart-tb tbody");
//   const rowId = `${product.stock_id}_${product.itemcode}`;
//   const existingRow = Array.from(cartTableBody.rows).find(
//     (row) => row.dataset.rowId === rowId
//   );

//   wholesalePrice.textContent = parseFloat(product.wholesale_price).toFixed(2);
//   mrp.textContent = parseFloat(product.cost_price).toFixed(2);
//   ourPrice.textContent = parseFloat(product.our_price).toFixed(2);
//   itemName.textContent = product.product_name;

//   if (existingRow) {
//     const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
//     const subtotalCell = existingRow.querySelector(`.subtotal_${rowId}`);
//     const newQty = parseInt(qtyCell.value) + 1;
//     qtyCell.value = newQty;
//     subtotalCell.textContent = (newQty * finalPrice).toFixed(2);
//   } else {
//     const newRow = document.createElement("tr");
//     newRow.dataset.rowId = rowId;
//     newRow.dataset.remainingStock = product.available_stock;
//     newRow.dataset.productRealId = product.id;
//     newRow.dataset.ourPrice = finalPrice;
//     newRow.dataset.wholesalePrice = product.wholesale_price;
//     newRow.setAttribute(
//       "onclick",
//       `alternatePricePopulator(
//         ${parseFloat(product.wholesale_price).toFixed(2)},
//         ${parseFloat(product.cost_price).toFixed(2)},
//         ${parseFloat(product.our_price).toFixed(2)},
//         '${product.product_name}'
//       )`
//     );
//     newRow.innerHTML = `
//             <td class="stock_id_${rowId}">${product.stock_id}</td>
//             <td class="item_id_${rowId}">${product.barcode}</td>
//             <td style="text-transform: capitalize;10002 10012
//             " class="product_name_${rowId}">${product.product_name}</td>
//             <td class="mrp_${rowId}">${parseFloat(
//       product.max_retail_price
//     ).toFixed(2)}</td>
//             <td class="unit_price_${rowId}">${parseFloat(finalPrice).toFixed(
//       2
//     )} ${isPromotion ? '<i class="fa-solid fa-certificate"></i>' : ""} </td>
//             <td><input type="text" class="disc-percentage qty_${rowId}" value="1" onkeyup="updateCartTotal()"/></td>
//             <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="0" onkeyup="updateCartTotal()"/></td>
//             <td class="free_${rowId}">0</td>
//             <td class="subtotal_${rowId}">${parseFloat(
//       product.max_retail_price
//     ).toFixed(2)}</td>
//      <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
//     cartTableBody.appendChild(newRow);
//   }

//   itemCounter();
//   updateCartTotal();
// }

// document.addEventListener("keydown", function (event) {
//   const misModal = document.getElementById("mis-modal");
//   const cartTableBody = document.querySelector("#pos-cart-tb tbody");
//   const rows = cartTableBody.querySelectorAll("tr");
//   if (rows.length === 0) return;

//   let selectedRow = cartTableBody.querySelector(".selected-row");

//   if (
//     event.key === "ArrowDown" &&
//     !misModal.classList.contains("show-mis-modal")
//   ) {
//     event.preventDefault();
//     if (selectedRow) {
//       let nextRow = selectedRow.nextElementSibling;
//       if (nextRow) {
//         selectedRow.classList.remove("selected-row");
//         nextRow.classList.add("selected-row");
//         nextRow.scrollIntoView({ behavior: "smooth", block: "center" });
//       }
//     } else {
//       rows[0].classList.add("selected-row");
//       rows[0].scrollIntoView({ behavior: "smooth", block: "center" });
//     }
//   } else if (
//     event.key === "ArrowUp" &&
//     !misModal.classList.contains("show-mis-modal")
//   ) {
//     event.preventDefault();
//     if (selectedRow) {
//       let prevRow = selectedRow.previousElementSibling;
//       if (prevRow) {
//         selectedRow.classList.remove("selected-row");
//         prevRow.classList.add("selected-row");
//         prevRow.scrollIntoView({ behavior: "smooth", block: "center" });
//       }
//     } else {
//       rows[rows.length - 1].classList.add("selected-row");
//       rows[rows.length - 1].scrollIntoView({
//         behavior: "smooth",
//         block: "center",
//       });
//     }
//   }
// });

document.addEventListener("keydown", function (event) {
  const misModal = document.getElementById("mis-modal");
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  const rows = cartTableBody.querySelectorAll("tr");
  if (rows.length === 0) return;

  let selectedRow = cartTableBody.querySelector(".selected-row");
  if (misModal.classList.contains("show-mis-modal")) return;

  if (event.key === "ArrowDown") {
    event.preventDefault();
    if (selectedRow) {
      let nextRow = selectedRow.nextElementSibling;
      if (nextRow) {
        selectedRow.classList.remove("selected-row");
        nextRow.classList.add("selected-row");
        nextRow.scrollIntoView({ behavior: "smooth", block: "center" });
        focusFirstInput(nextRow);
      }
    } else {
      rows[0].classList.add("selected-row");
      rows[0].scrollIntoView({ behavior: "smooth", block: "center" });
      focusFirstInput(rows[0]);
    }
  } else if (event.key === "ArrowUp") {
    event.preventDefault();
    if (selectedRow) {
      let prevRow = selectedRow.previousElementSibling;
      if (prevRow) {
        selectedRow.classList.remove("selected-row");
        prevRow.classList.add("selected-row");
        prevRow.scrollIntoView({ behavior: "smooth", block: "center" });
        focusFirstInput(prevRow);
      }
    } else {
      rows[rows.length - 1].classList.add("selected-row");
      rows[rows.length - 1].scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
      focusFirstInput(rows[rows.length - 1]);
    }
  } else if (event.key === "ArrowRight" || event.key === "ArrowLeft") {
    event.preventDefault();
    if (selectedRow) {
      const inputs = selectedRow.querySelectorAll("input");
      let focusedIndex = Array.from(inputs).findIndex(
        (input) => document.activeElement === input
      );

      if (event.key === "ArrowRight" && focusedIndex < inputs.length - 1) {
        inputs[focusedIndex + 1].focus();
      } else if (event.key === "ArrowLeft" && focusedIndex > 0) {
        inputs[focusedIndex - 1].focus();
      }
    }
  }
});

function focusFirstInput(row) {
  const inputs = row.querySelectorAll("input");
  if (inputs.length > 0) {
    inputs[0].focus();
  }
}

function removeFromCart(rowId) {
  const rowToDelete = document.querySelector(`tr[data-row-id='${rowId}']`);
  if (rowToDelete) {
    deleteBillItem(rowId);
    rowToDelete.remove();
    updateCartTotal();
    itemCounter();
  }
}

function addHeldItemsToCart(product) {
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");

  fetch("fetch_hold_product_static.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `barcode=${encodeURIComponent(product.barcode)}`,
  })
    .then((response) => response.json())
    .then((stockData) => {
      if (stockData.success) {
        product.itemcode = stockData.product.itemcode;
        product.wholesale_price = stockData.product.wholesale_price;
        product.cost_price = stockData.product.cost_price;
        product.fixed_our_price = stockData.product.our_price;

        //if()
        const rowId = `${product.stock_id}_${product.itemcode}`; // Changed rowId definition
        console.log(rowId);

        const existingRow = Array.from(cartTableBody.rows).find(
          (row) => row.dataset.rowId === rowId
        );

        if (existingRow) {
          const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
          const subtotalCell = existingRow.querySelector(`.subtotal_${rowId}`);
          const newQty = parseFloat(qtyCell.textContent) + 1;
          qtyCell.textContent = newQty;
          subtotalCell.textContent = (
            newQty * product.max_retail_price
          ).toFixed(2);
        } else {
          const newRow = document.createElement("tr");
          newRow.dataset.rowId = rowId;
          newRow.dataset.ourPrice = product.our_price;
          newRow.dataset.wholesalePrice = product.wholesale_price;
          newRow.setAttribute(
            "onclick",
            `alternatePricePopulator(
              ${parseFloat(product.wholesale_price).toFixed(2)},
              ${parseFloat(product.cost_price).toFixed(2)},
              ${parseFloat(product.our_price).toFixed(2)},
              '${product.product_name}'
            )`
          );
          // newRow.innerHTML = `
          //   <td class="stock_id_${rowId}">${product.stock_id}</td>
          //   <td class="item_id_${rowId}">${product.barcode}</td>
          //   <td style="text-transform: capitalize;" class="product_name_${rowId}">${product.product_name}</td>
          //   <td class="mrp_${rowId}">${parseFloat(product.max_retail_price).toFixed(2)}</td>
          //   <td class="unit_price_${rowId}">${parseFloat(product.our_price).toFixed(2)}
          //   ${isPromotion ? '<i class="fa-solid fa-certificate"></i>' : ""} </td>
          //   <td class="disc-percentage qty_${rowId}">${product.purchase_qty}</td>
          //   <td><input type="text" class="disc-percentage qty_${rowId}" value="${product.purchase_qty}" onkeyup="updateCartTotal()"/></td>
          //   <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(product.discount_percentage)}" onkeyup="updateCartTotal()"/></td>
          //   <td class="free_${rowId}">${product.free}</td>
          //   <td class="subtotal_${rowId}">${parseFloat(product.our_price*product.purchase_qty).toFixed(2)}</td>
          // `;
          // cartTableBody.appendChild(newRow);

          newRow.innerHTML = `
                  <td class="item_no_${rowId}">${product.row_count}</td>
                  <td class="stock_id_${rowId}">${product.stock_id}</td>
                  <td class="item_id_${rowId}">${product.barcode}</td>
                  <td style="text-transform: capitalize;10002 10012
                  " class="product_name_${rowId}">${product.product_name}</td>
                  <td class="mrp_${rowId}">${parseFloat(
            product.max_retail_price
          ).toFixed(2)}</td>
                  <td class="unit_price_${rowId}">${parseFloat(
            product.our_price
          ).toFixed(2)} ${
            product.fixed_our_price > product.our_price
              ? '<i class="fa-solid fa-certificate"></i>'
              : ""
          } </td>
                  <td>
                    <input type="text" class="disc-percentage qty_${rowId}" value="${parseFloat(
            product.purchase_qty
          )}" onkeyup="updateCartTotal()"/>
                    <input type="text" class="disc-percentage" id="discount_amount_val_${rowId}" value="${
            product.discountAmount || 0
          }" hidden/>

                  </td>
                  <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(
            product.discount_percentage
          )}" onkeyup="updateCartTotal()"/></td>
                  <td class="free_${rowId}">${product.free}</td>
                  <td class="subtotal_${rowId}">${parseFloat(
            product.our_price * product.purchase_qty
          ).toFixed(2)}</td>
             <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
          cartTableBody.appendChild(newRow);
        }
        updateCartTotal();
      } else {
        toastr.info("Stock entry not found for the barcode.");
      }
    })
    .catch((error) => console.error("Error fetching stock entry:", error));

  itemCounter();
  updateCartTotal();
}

// function addHeldItemsToCart(product) {
//   const cartTableBody = document.querySelector("#pos-cart-tb tbody");

//   fetch("fetch_hold_product_static.php", {
//     method: "POST",
//     headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     body: `barcode=${encodeURIComponent(product.barcode)}`,
//   })
//     .then((response) => response.json())
//     .then((stockData) => {
//       if (stockData.success) {
//         product.itemcode = stockData.product.itemcode;
//         product.wholesale_price = stockData.product.wholesale_price;
//         product.cost_price = stockData.product.cost_price;
//         product.fixed_our_price = stockData.product.our_price;

//         //if()
//         const rowId = `${product.stock_id}_${product.itemcode}`; // Changed rowId definition
//         console.log(rowId);

//         const existingRow = Array.from(cartTableBody.rows).find(
//           (row) => row.dataset.rowId === rowId
//         );

//         if (existingRow) {
//           const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
//           const subtotalCell = existingRow.querySelector(`.subtotal_${rowId}`);
//           const newQty = parseInt(qtyCell.textContent) + 1;
//           qtyCell.textContent = newQty;
//           subtotalCell.textContent = (
//             newQty * product.max_retail_price
//           ).toFixed(2);
//         } else {
//           const newRow = document.createElement("tr");
//           newRow.dataset.rowId = rowId;
//           newRow.setAttribute(
//             "onclick",
//             `alternatePricePopulator(
//               ${parseFloat(product.wholesale_price).toFixed(2)},
//               ${parseFloat(product.cost_price).toFixed(2)},
//               ${parseFloat(product.our_price).toFixed(2)},
//               '${product.product_name}'
//             )`
//           );
//           // newRow.innerHTML = `
//           //   <td class="stock_id_${rowId}">${product.stock_id}</td>
//           //   <td class="item_id_${rowId}">${product.barcode}</td>
//           //   <td style="text-transform: capitalize;" class="product_name_${rowId}">${product.product_name}</td>
//           //   <td class="mrp_${rowId}">${parseFloat(product.max_retail_price).toFixed(2)}</td>
//           //   <td class="unit_price_${rowId}">${parseFloat(product.our_price).toFixed(2)}
//           //   ${isPromotion ? '<i class="fa-solid fa-certificate"></i>' : ""} </td>
//           //   <td class="disc-percentage qty_${rowId}">${product.purchase_qty}</td>
//           //   <td><input type="text" class="disc-percentage qty_${rowId}" value="${product.purchase_qty}" onkeyup="updateCartTotal()"/></td>
//           //   <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(product.discount_percentage)}" onkeyup="updateCartTotal()"/></td>
//           //   <td class="free_${rowId}">${product.free}</td>
//           //   <td class="subtotal_${rowId}">${parseFloat(product.our_price*product.purchase_qty).toFixed(2)}</td>
//           // `;
//           // cartTableBody.appendChild(newRow);

//           newRow.innerHTML = `
//                   <td class="stock_id_${rowId}">${product.row_count}</td>
//                   <td class="stock_id_${rowId}">${product.stock_id}</td>
//                   <td class="item_id_${rowId}">${product.barcode}</td>
//                   <td style="text-transform: capitalize;10002 10012
//                   " class="product_name_${rowId}">${product.product_name}</td>
//                   <td class="mrp_${rowId}">${parseFloat(
//             product.max_retail_price
//           ).toFixed(2)}</td>
//                   <td class="unit_price_${rowId}">${parseFloat(
//             product.our_price
//           ).toFixed(2)} ${
//             product.fixed_our_price > product.our_price
//               ? '<i class="fa-solid fa-certificate"></i>'
//               : ""
//           } </td>
//                   <td><input type="text" class="disc-percentage qty_${rowId}" value="${
//             product.purchase_qty
//           }" onkeyup="updateCartTotal()"/></td>
//             <td class="discount_${rowId}">
//               <input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(
//             product.discount_percentage
//           )}" onkeyup="updateCartTotal()"/>
//               <input type="text" class="disc-percentage" id="discount_amount_val_${rowId}" value="${
//             product.discountAmount || 0
//           }" hidden/>
//             </td>
//                   <td class="free_${rowId}">${product.free}</td>
//                   <td class="subtotal_${rowId}">${parseFloat(
//             product.our_price * product.purchase_qty
//           ).toFixed(2)}</td>
//              <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
//           cartTableBody.appendChild(newRow);
//         }
//         updateCartTotal();
//       } else {
//         toastr.info("Stock entry not found for the barcode.");
//       }
//     })
//     .catch((error) => console.error("Error fetching stock entry:", error));

//   itemCounter();
//   updateCartTotal();
// }

// function addHeldItemsToCart(product) {
//   const cartTableBody = document.querySelector("#pos-cart-tb tbody");

//   fetch("fetch_hold_product_static.php", {
//     method: "POST",
//     headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     body: `barcode=${encodeURIComponent(product.barcode)}`,
//   })
//     .then((response) => response.json())
//     .then((stockData) => {
//       if (stockData.success) {
//         product.itemcode = stockData.product.itemcode;
//         product.wholesale_price = stockData.product.wholesale_price;
//         product.cost_price = stockData.product.cost_price;
//         product.fixed_our_price = stockData.product.our_price;

//         //if()
//         const rowId = `${product.stock_id}_${product.itemcode}`; // Changed rowId definition
//         console.log(rowId);

//         const existingRow = Array.from(cartTableBody.rows).find(
//           (row) => row.dataset.rowId === rowId
//         );

// if (existingRow) {
//   const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
//   const subtotalCell = existingRow.querySelector(`.subtotal_${rowId}`);
//   const newQty = parseFloat(qtyCell.textContent) + 1;
//   qtyCell.textContent = newQty;
//   subtotalCell.textContent = (newQty * product.max_retail_price).toFixed(2);
// } else {
//   const newRow = document.createElement("tr");
//   newRow.dataset.rowId = rowId;
//   newRow.dataset.ourPrice = product.our_price;
//   newRow.dataset.wholesalePrice = product.wholesale_price;
//   newRow.setAttribute(
//     "onclick",
//     `alternatePricePopulator(
//               ${parseFloat(product.wholesale_price).toFixed(2)},
//               ${parseFloat(product.cost_price).toFixed(2)},
//               ${parseFloat(product.our_price).toFixed(2)},
//               '${product.product_name}'
//             )`
//   );
// newRow.innerHTML = `
//   <td class="stock_id_${rowId}">${product.stock_id}</td>
//   <td class="item_id_${rowId}">${product.barcode}</td>
//   <td style="text-transform: capitalize;" class="product_name_${rowId}">${product.product_name}</td>
//   <td class="mrp_${rowId}">${parseFloat(product.max_retail_price).toFixed(2)}</td>
//   <td class="unit_price_${rowId}">${parseFloat(product.our_price).toFixed(2)}
//   ${isPromotion ? '<i class="fa-solid fa-certificate"></i>' : ""} </td>
//   <td class="disc-percentage qty_${rowId}">${product.purchase_qty}</td>
//   <td><input type="text" class="disc-percentage qty_${rowId}" value="${product.purchase_qty}" onkeyup="updateCartTotal()"/></td>
//   <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(product.discount_percentage)}" onkeyup="updateCartTotal()"/></td>
//   <td class="free_${rowId}">${product.free}</td>
//   <td class="subtotal_${rowId}">${parseFloat(product.our_price*product.purchase_qty).toFixed(2)}</td>
// `;
// cartTableBody.appendChild(newRow);

//           newRow.innerHTML = `
//                   <td class="stock_id_${rowId}">${product.row_count}</td>
//                   <td class="stock_id_${rowId}">${product.stock_id}</td>
//                   <td class="item_id_${rowId}">${product.barcode}</td>
//                   <td style="text-transform: capitalize;10002 10012
//                   " class="product_name_${rowId}">${product.product_name}</td>
//                   <td class="mrp_${rowId}">${parseFloat(
//             product.max_retail_price
//           ).toFixed(2)}</td>
//                   <td class="unit_price_${rowId}">${parseFloat(
//             product.our_price
//           ).toFixed(2)} ${
//             product.fixed_our_price > product.our_price
//               ? '<i class="fa-solid fa-certificate"></i>'
//               : ""
//           } </td>
//                   <td>
//                     <input type="text" class="disc-percentage qty_${rowId}" value="${
//             product.purchase_qty
//           }" onkeyup="updateCartTotal()"/>
//                     <input type="text" class="disc-percentage" id="discount_amount_val_${rowId}" value="${
//             product.discountAmount || 0
//           }" hidden/>

//                   </td>
//                   <td class="discount_${rowId}"><input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${parseFloat(
//             product.discount_percentage
//           )}" onkeyup="updateCartTotal()"/></td>
//                   <td class="free_${rowId}">${product.free}</td>
//                   <td class="subtotal_${rowId}">${parseFloat(
//             product.our_price * product.purchase_qty
//           ).toFixed(2)}</td>
//              <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
//           cartTableBody.appendChild(newRow);
//         }
//         updateCartTotal();
//       } else {
//         toastr.info("Stock entry not found for the barcode.");
//       }
//     })
//     .catch((error) => console.error("Error fetching stock entry:", error));

//   itemCounter();
//   updateCartTotal();
// }

const updateCartTotal = () => {
  const totalDiscountValue = document.getElementById("total_discount");
  const totalDiscount = parseFloat(totalDiscountValue.value) || 0;
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  let total = 0;
  Array.from(cartTableBody.rows).forEach((row) => {
    const rowId = row.dataset.rowId;
    const subtotalCell = row.querySelector(`.subtotal_${rowId}`);
    const discountInput = row.querySelector(`#discount_val_${rowId}`);
    const unitPriceCell = row.querySelector(`.unit_price_${rowId}`);
    const qtyCell = row.querySelector(`.qty_${rowId}`);
    const discountAmount = parseFloat(
      row.querySelector(`#discount_amount_val_${rowId}`).value
    );

    const unitPrice = parseFloat(unitPriceCell.textContent) || 0;
    const qty = parseFloat(qtyCell.value) || 0;
    const discount = parseFloat(discountInput.value) || 0;

    const grossTotal = unitPrice * qty;

    const discountPrice = (discount / 100) * grossTotal;
    let netTotal = (grossTotal - discountPrice).toFixed(2);

    if (discountAmount > 0) {
      netTotal = netTotal - discountAmount;
    }

    const subtotal = netTotal;

    subtotalCell.textContent = parseFloat(subtotal).toFixed(2);

    total = parseFloat(total) + parseFloat(subtotal);
  });

  total = total - totalDiscount;
  document.getElementById("total_amount").value = total.toFixed(2);
};

const calculateCartTotal = () => {
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  let total = 0;
  Array.from(cartTableBody.rows).forEach((row) => {
    const rowId = row.dataset.rowId;
    const subtotalCell = row.querySelector(`.subtotal_${rowId}`);
    const discountInput = row.querySelector(`#discount_val_${rowId}`);
    const unitPriceCell = row.querySelector(`.unit_price_${rowId}`);
    const qtyCell = row.querySelector(`.qty_${rowId}`);

    const unitPrice = parseFloat(unitPriceCell.textContent) || 0;
    const qty = parseFloat(qtyCell.value) || 0;
    const discount = parseFloat(discountInput.value) || 0;

    const grossTotal = unitPrice * qty;
    const discountPrice = (discount / 100) * grossTotal;
    const netTotal = (grossTotal - discountPrice).toFixed(2);

    const subtotal = netTotal;

    subtotalCell.textContent = subtotal;

    total = parseFloat(total) + parseFloat(subtotal);
  });

  return total;
};

const calculateCartGrossTotal = () => {
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  let total = 0;
  Array.from(cartTableBody.rows).forEach((row) => {
    const rowId = row.dataset.rowId;
    const unitPriceCell = row.querySelector(`.unit_price_${rowId}`);
    const qtyCell = row.querySelector(`.qty_${rowId}`);

    const unitPrice = parseFloat(unitPriceCell.textContent) || 0;
    const qty = parseFloat(qtyCell.value) || 0;

    const grossTotal = unitPrice * qty;

    total = parseFloat(total) + parseFloat(grossTotal);
  });

  return total;
};

const balanceHandler = () => {
  const balanceIndicator = document.getElementById("total_balance_final");
  const cashTenderIndicator = document.getElementById("total_cash_tendered");
  const totalIndicator = document.getElementById("total_amount");

  const totalValue = parseFloat(totalIndicator.value);
  const cashTenderedValue = parseFloat(cashTenderIndicator.value);
  balanceIndicator.value = (cashTenderedValue - totalValue || 0).toFixed(2);
};

const discountHandlerFinal = () => {
  const totalIndicator = document.getElementById("total_amount");
  const discountValue = document.getElementById("total_discount").value;

  const cartTotal = calculateCartTotal();

  const totalValue = parseFloat(cartTotal) - parseFloat(discountValue);

  totalIndicator.value = totalValue.toFixed(2);
};

// alternative price displaying functions
const alternateWholesale = () => {
  const wholesalePrice = document.getElementById("alt-wholesale");
  const wholesalePriceBtn = document.getElementById("alt-wholesale-btn");

  wholesalePrice.classList.toggle("show-alt");
  if (wholesalePriceBtn.textContent === "show") {
    wholesalePriceBtn.textContent = "hide";
  } else {
    wholesalePriceBtn.textContent = "show";
  }
};

const alternateSCGP = () => {
  const scgPrice = document.getElementById("alt-scg-price");
  const scgBtn = document.getElementById("alt-scg-btn");

  scgPrice.classList.toggle("show-alt");
  if (scgBtn.textContent === "show") {
    scgBtn.textContent = "hide";
  } else {
    scgBtn.textContent = "show";
  }
};

const alternateMRP = () => {
  const mrp = document.getElementById("alt-mrp");
  const mrpBtn = document.getElementById("alt-mrp-btn");

  mrp.classList.toggle("show-alt");
  if (mrpBtn.textContent === "show") {
    mrpBtn.textContent = "hide";
  } else {
    mrpBtn.textContent = "show";
  }
};

const alternateOurPrice = () => {
  const ourPrice = document.getElementById("alt-our-price");
  const ourPriceBtn = document.getElementById("alt-our-price-btn");

  ourPrice.classList.toggle("show-alt");
  if (ourPriceBtn.textContent === "show") {
    ourPriceBtn.textContent = "hide";
  } else {
    ourPriceBtn.textContent = "show";
  }
};

const alternateRemainingStock = () => {
  const remainingStock = document.getElementById("rem-stock");
  const remainingStockBtn = document.getElementById("rem-stock-price-btn");

  remainingStock.classList.toggle("show-alt");
  if (remainingStockBtn.textContent === "show") {
    remainingStockBtn.textContent = "hide";
  } else {
    remainingStockBtn.textContent = "show";
  }
};

const alternatePricePopulator = (
  wholesaleTD,
  costPriceTD,
  remainingStock,
  prdName,
  imagePath,
  ourPrice,
  scgPrice,
  mrpPrice
) => {
  const remainingStockAmount = document.getElementById("rem-stock");
  const mrp = document.getElementById("alt-mrp");
  const wholesalePrice = document.getElementById("alt-wholesale");
  const itemName = document.getElementById("info-item");
  const itemImage = document.getElementById("selected-product-image");
  const altOurPrice = document.getElementById("alt-our-price");
  const altScgPrice = document.getElementById("alt-scg-price");
  const altMRPrice = document.getElementById("alt-mrprice");

  remainingStockAmount.textContent = remainingStock;
  mrp.textContent = costPriceTD;
  wholesalePrice.textContent = wholesaleTD;
  itemName.textContent = prdName;
  itemImage.src = `../inventory/${imagePath}`;
  altOurPrice.textContent = ourPrice;
  altScgPrice.textContent = scgPrice;
  altMRPrice.textContent = mrpPrice;
};

const barcodeGrabber = () => {
  const barCodeContainer = document.getElementById("barcode-grabber-cont");
  const barcodeInput = document.getElementById("bcode-pholder");
  barCodeContainer.classList.toggle("bgrab-expand");
  barcodeInput.focus();
};

const getCartRecords = () => {
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  const cartRecords = [];

  Array.from(cartTableBody.rows).forEach((row) => {
    const remainingStock = row.dataset.remainingStock;
    const productRealId = row.dataset.productRealId;
    const ourPrice = row.dataset.ourPrice;
    const wholesalePrice = row.dataset.wholesalePrice;
    const rowId = row.dataset.rowId;
    const barcode = row.querySelector(`.item_id_${rowId}`).textContent.trim();
    const productName = row
      .querySelector(`.product_name_${rowId}`)
      .textContent.trim();
    const unitPrice = parseFloat(
      row.querySelector(`.mrp_${rowId}`).textContent.trim()
    );
    const quantity = parseFloat(
      row.querySelector(`.qty_${rowId}`).value.trim()
    );
    const discount = parseFloat(
      row.querySelector(`#discount_val_${rowId}`).value.trim()
    );
    const free = parseInt(
      row.querySelector(`.free_${rowId}`).textContent.trim()
    );
    const subtotal = parseFloat(
      row.querySelector(`.subtotal_${rowId}`).textContent.trim()
    );
    const stockId = parseInt(
      row.querySelector(`.stock_id_${rowId}`).textContent.trim()
    );
    const sellPrice = parseFloat(
      row.querySelector(`.unit_price_${rowId}`).textContent.trim()
    );
    const freeIssueAmount = parseInt(
      row.querySelector(`.free_${rowId}`).textContent.trim()
    );

    const newRemainingStock = remainingStock - (quantity + freeIssueAmount);

    cartRecords.push({
      productName,
      unitPrice,
      quantity,
      discount,
      subtotal,
      barcode,
      newRemainingStock,
      productRealId,
      ourPrice,
      wholesalePrice,
      stockId,
      free,
      sellPrice,
      freeIssueAmount,
    });
  });

  return cartRecords;
};

const itemCounter = () => {
  const countIndicator = document.getElementById("item-count-indicator");
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  const cartItemCount = Array.from(cartTableBody.rows).length;
  countIndicator.textContent = cartItemCount;
};

const goToPaymentScreen = () => {
  const invoiceType = getSelectedInvoiceType();
  const grossTotal = calculateCartGrossTotal();
  const cashierName = document.getElementById("current_user").value.trim();
  const customerName = document
    .getElementById("search-customer")
    .value.trim()
    .split(" ")[0];
  const customerId = document.getElementById("customerIdAdvPay").value;
  const billID = document.getElementById("next_bill_no").value.trim();
  const totalValue = document.getElementById("total_amount").value.trim();
  const totalDiscount = document.getElementById("total_discount").value.trim();
  const totalCashTendered = document
    .getElementById("total_cash_tendered")
    .value.trim();
  const productList = encodeURIComponent(JSON.stringify(getCartRecords()));
  if (!billID || !totalValue || !totalCashTendered) {
    alert("Please fill in all the required fields.");
    return;
  }
  window.location.href = `./pos_checkout.php?billID=${billID}&totalValue=${totalValue}&customerName=${customerName}&customerId=${customerId}&totalDiscount=${totalDiscount}&totalCashTendered=${totalCashTendered}&grossTotal=${grossTotal}&productList=${productList}&invoiceType=${invoiceType}&cashierName=${cashierName}`;
};

const goToPaymentScreen2 = () => {
  const invoiceType = getSelectedInvoiceType();
  const grossTotal = calculateCartGrossTotal();
  const cashierName = document.getElementById("current_user").value.trim();
  const customerName = document
    .getElementById("search-customer")
    .value.trim()
    .split(" ")[0];
  const customerId = document.getElementById("customerIdAdvPay").value;
  const billID = document.getElementById("next_bill_no").value.trim();
  const totalValue = document.getElementById("total_amount").value.trim();
  const totalDiscount = document.getElementById("total_discount").value.trim();
  const totalCashTendered = document
    .getElementById("total_cash_tendered")
    .value.trim();
  const productList = encodeURIComponent(JSON.stringify(getCartRecords()));

  if (!billID || !totalValue || !totalCashTendered) {
    alert("Please fill in all the required fields.");
    return;
  }

  const url = `./pos_checkout_modal.php?billID=${billID}&totalValue=${totalValue}&customerName=${customerName}&customerId=${customerId}&totalDiscount=${totalDiscount}&totalCashTendered=${totalCashTendered}&grossTotal=${grossTotal}&productList=${productList}&invoiceType=${invoiceType}&cashierName=${cashierName}`;

  // Set iframe source and show modal
  const modal = document.getElementById("payment-modal");
  const iframe = document.getElementById("payment-iframe");
  iframe.src = url;
  modal.style.display = "flex";
};

// Add event listener for closing the modal
document.addEventListener("DOMContentLoaded", () => {
  const closeModal = document.querySelector(".close-modal");
  const modal = document.getElementById("payment-modal");

  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
    // Clear iframe src when closing to stop any ongoing processes
    document.getElementById("payment-iframe").src = "";
  });

  // Close modal when clicking outside of modal content
  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
      document.getElementById("payment-iframe").src = "";
    }
  });
});

const createHoldBillRecord = () => {
  const billID = document.getElementById("next_bill_no").value.trim();

  if (billID) {
    const data = {
      billId: billID,
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "create_hold_bill_record.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        console.log("success bill record!");
      }
    };

    const urlEncodedData = Object.keys(data)
      .map((key) => `${key}=${encodeURIComponent(data[key])}`)
      .join("&");

    xhr.send(urlEncodedData);
  } else {
    Swal.fire("Error: Missing required data.");
  }
};

const createHoldPurchaseRecords = () => {
  const billID = document.getElementById("next_bill_no").value.trim();
  const productList = getCartRecords();

  if (billID && productList && productList.length > 0) {
    productList.forEach((product) => {
      const data = {
        billId: billID,
        barcode: product.barcode,
        product_name: product.productName,
        unit_price: product.unitPrice,
        our_price: product.ourPrice,
        qty: product.quantity,
        disc_percentage: product.discount || 0,
        disc_amount: product.discountAmount || 0,
        subtotal: product.subtotal,
        stock_id: product.stockId,
        free: product.free,
      };
      console.log(data);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "create_hold_purchase_item_record.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          console.log("Response from server:", xhr.responseText);
          location.reload();
        }
      };

      const urlEncodedData = Object.keys(data)
        .map((key) => `${key}=${encodeURIComponent(data[key])}`)
        .join("&");
      xhr.send(urlEncodedData);
    });
  } else {
    Swal.fire("Error: Missing Bill ID or Product List.");
    return;
  }
};

async function holdInvoice() {
  await createHoldBillRecord();
  await createHoldPurchaseRecords();
  clearCartState();
}

document.addEventListener("DOMContentLoaded", function () {
  // Fetch held invoices
  fetch("fetch_held_bill_records.php")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("held-invoices").innerHTML = data;
      setupInvoiceClickHandler();
      setupF2Navigation();
    });

  // Setup click handler for invoices
  function setupInvoiceClickHandler() {
    const buttons = document.querySelectorAll(".held-invoice-btn");
    buttons.forEach((button) => {
      button.addEventListener("click", function () {
        const billNo = this.getAttribute("data-bill-no");
        clearCartItems();
        fetchHeldItems(billNo);
        document.getElementById("next_bill_no").value = billNo;
      });
    });
  }

  function clearCartItems() {
    const cartItemsTableBody = document.getElementById("pos-cart-items");
    cartItemsTableBody.innerHTML = ""; // Clear the content of the table body
  }

  function setupF2Navigation() {
    const heldInvoiceItems = document.querySelectorAll(".held-invoice-item");
    let currentIndex = -1;
    function highlightItem(index) {
      heldInvoiceItems.forEach((item) => item.classList.remove("highlighted"));

      if (index >= 0 && index < heldInvoiceItems.length) {
        heldInvoiceItems[index].classList.add("highlighted");
        heldInvoiceItems[index].scrollIntoView({
          behavior: "smooth",
          block: "center",
        });

        const button =
          heldInvoiceItems[index].querySelector(".held-invoice-btn");
        if (button) {
          button.click();
        }
      }
    }
    document.addEventListener("keydown", function (event) {
      if (event.key === "F2") {
        event.preventDefault();
        currentIndex = (currentIndex + 1) % heldInvoiceItems.length;
        highlightItem(currentIndex);
      }
    });
  }
});

function fetchHeldItems(billNo) {
  fetch(
    `fetch_held_purchase_item_records.php?billNo=${encodeURIComponent(billNo)}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (Array.isArray(data) && data.length > 0) {
        clearCartState();
        let row_count = 1;
        data.forEach((item) => {
          addHeldItemsToCart({
            row_count,
            stock_id: item.stock_id,
            barcode: item.product_barcode,
            product_name: item.product_name,
            max_retail_price: item.product_mrp,
            our_price: item.our_price,
            purchase_qty: item.purchase_qty,
            free: item.free,
            discount_percentage: item.discount_percentage,
          });
          row_count += 1;
        });
        //toastr.success("All hold items added to the cart.");
      } else {
        toastr.info("No items found for this hold invoice.");
      }
    })
    .catch((error) => console.error("Error fetching held items:", error));
}

async function fetchTotalValueOfHeldBill(billNo) {
  try {
    const response = await fetch(
      `fetch_held_purchase_item_records.php?billNo=${encodeURIComponent(
        billNo
      )}`
    );
    const data = await response.json();

    let TotalValue = 0;
    if (Array.isArray(data) && data.length > 0) {
      data.forEach((item) => {
        let itemGrossTotalValue = parseFloat(
          item.our_price * item.purchase_qty
        );
        let itemDiscount =
          itemGrossTotalValue * (item.discount_percentage / 100);
        let itemNetTotalValue = itemGrossTotalValue - itemDiscount;
        TotalValue += itemNetTotalValue;
      });

      return TotalValue.toFixed(2);
    } else {
      return "0";
    }
  } catch (error) {
    console.error("Error fetching held items:", error);
    return "0";
  }
}

function deleteHeldInvoice(billNo) {
  // newest version with cancellation reeason
  openBcModal(billNo);

  // Show confirmation before deleting
  // if (confirm("Are you sure you want to delete this held invoice?")) {
  //   // Send DELETE request to the server to delete the invoice
  //   fetch("delete_held_invoice.php", {
  //     method: "POST",
  //     headers: {
  //       "Content-Type": "application/x-www-form-urlencoded",
  //     },
  //     body: `bill_no=${encodeURIComponent(billNo)}`,
  //   })
  //     .then((response) => response.json())
  //     .then((data) => {
  //       if (data.success) {
  //         alert("Invoice deleted successfully");

  //         // Remove the invoice item from the DOM using data-bill-no to select the correct span
  //         const invoiceItem = document
  //           .querySelector(`span[data-bill-no="${billNo}"]`)
  //           .closest(".held-invoice-item");
  //         invoiceItem.remove(); // This removes the entire row of the invoice item
  //         //location.reload();
  //       } else {
  //         alert("Failed to delete invoice: " + data.message);
  //       }
  //     })
  //     .catch((error) => {
  //       console.error("Error deleting invoice:", error);
  //       alert("An error occurred while deleting the invoice");
  //     });
  // }
}

//next bill no
document.addEventListener("DOMContentLoaded", () => {
  fetch("fetch_next_bill_no.php") // Replace with the correct path to your PHP file
    .then((response) => response.json())
    .then((data) => {
      if (data.next_bill_no) {
        document.getElementById("next_bill_no").value = data.next_bill_no;
      }
    })
    .catch((error) => {
      console.error("Error fetching next bill number:", error);
    });
});

const getHoldCartRecords = () => {
  const cartTableBody = document.querySelector("#pos-cart-tb tbody");
  const cartRecords = [];

  Array.from(cartTableBody.rows).forEach((row) => {
    const rowId = row.dataset.rowId;
    const barcode = row.querySelector(`.item_id_${rowId}`).textContent.trim();
    const productName = row
      .querySelector(`.product_name_${rowId}`)
      .textContent.trim();
    const unitPrice = parseFloat(
      row.querySelector(`.unit_price_${rowId}`).textContent.trim()
    );
    const quantity = parseFloat(
      row.querySelector(`.qty_${rowId}`).textContent.trim()
    );
    const discount = parseFloat(
      row.querySelector(`#discount_val_${rowId}`).value.trim()
    );
    const free = parseInt(
      row.querySelector(`.free_${rowId}`).textContent.trim()
    );
    const subtotal = parseFloat(
      row.querySelector(`.subtotal_${rowId}`).textContent.trim()
    );
    const stockId = parseInt(
      row.querySelector(`.stock_id_${rowId}`).textContent.trim()
    ); // New:

    cartRecords.push({
      productName,
      unitPrice,
      quantity,
      discount,
      subtotal,
      barcode,
      stockId,
      free,
    });
  });

  return cartRecords;
};

document.addEventListener("DOMContentLoaded", function () {
  const refundRadio = document.getElementById("tra_refund");
  const modal = document.getElementById("refundModal");
  const closeModal = document.querySelector("#refund-close-button");

  refundRadio.addEventListener("click", function () {
    modal.style.display = "flex";
  });

  closeModal.addEventListener("click", function () {
    modal.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
});

document.addEventListener("keydown", function (event) {
  if (event.ctrlKey && event.key === "r") {
    event.preventDefault();
    const modal = document.getElementById("refundModal");
    modal.style.display = "flex";
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const billInput = document.querySelector(".refund-bill-id-search-input");
  const searchButton = document.querySelector(".refund-bill-id-search-btn");
  const refundTableBody = document.querySelector(".refund-table tbody");
  const returnCashAmountInput = document.querySelector("#return-cash-amount");
  const cashReturnBtn = document.querySelector(".cash-return-btn");
  const itemCodeFilterInput = document.querySelector(
    ".refund-bill-id-filter-input"
  );

  function fetchPurchaseItems() {
    const billID = billInput.value.trim();
    if (billID === "") {
      alert("Please enter a Bill ID.");
      return;
    }

    fetch("fetch_purchase_items.php?bill_id=" + billID)
      .then((response) => response.json())
      .then((data) => {
        refundTableBody.innerHTML = ""; // Clear previous results

        if (data.length === 0) {
          alert("No purchases found for this Bill ID.");
          return;
        }

        data.forEach((item) => {
          const row = document.createElement("tr");
          row.innerHTML = `
                      <td>${item.stock_id}</td>
                      <td class="refund-item-code">${item.product_barcode}</td>
                      <td>${item.product_name}</td>
                      <td>${item.price}</td>
                      <td class="qty">${item.purchase_qty}</td>
                      <td>${item.discount_percentage}%</td>
                      <td class="subtotal">${item.subtotal}</td>
                      <td><input type="number" class="return-qty" min="0" max="${item.purchase_qty}" value="0" 
                                 data-subtotal="${item.subtotal}" data-qty="${item.purchase_qty}"></td>
                      <td class="return-cash-amount">0.00</td>
                  `;
          refundTableBody.appendChild(row);
        });

        // Add event listener to enforce limits
        document.querySelectorAll(".return-qty").forEach((input) => {
          input.addEventListener("input", function () {
            const maxQty = parseFloat(this.getAttribute("max"));
            if (parseFloat(this.value) > maxQty) {
              this.value = maxQty; // Reset to max if exceeded
            }
          });
        });

        // Attach event listeners to return quantity inputs
        attachReturnQtyListeners();
      })
      .catch((error) => console.error("Error fetching data:", error));
  }

  function attachReturnQtyListeners() {
    document.querySelectorAll(".return-qty").forEach((input) => {
      input.addEventListener("input", updateReturnAmount);
    });
  }

  function updateReturnAmount() {
    let totalReturnAmount = 0;

    document.querySelectorAll(".return-qty").forEach((input) => {
      const returnQty = parseFloat(input.value) || 0;
      const subtotal = parseFloat(input.dataset.subtotal) || 0;
      const qty = parseFloat(input.dataset.qty) || 1;
      const row = input.closest("tr"); // Get the row where input is changed
      const returnCashAmountCell = row.querySelector(".return-cash-amount"); // Get the cell for return amount

      const returnCash = returnQty * (subtotal / qty);
      returnCashAmountCell.textContent = returnCash.toFixed(2); // Update individual return amount
      totalReturnAmount += returnCash; // Add to total
    });

    returnCashAmountInput.value = totalReturnAmount.toFixed(2); // Update total return amount
  }

  function generateRefundReceipt(refundData) {
    // Create a hidden iframe for printing
    const printFrame = document.createElement("iframe");
    printFrame.style.display = "none";
    document.body.appendChild(printFrame);

    // Get the current date and time
    const now = new Date();
    const dateString = now.toLocaleDateString();
    const timeString = now.toLocaleTimeString();

    // Calculate total refund amount
    const totalRefund = refundData.reduce(
      (sum, item) => sum + item.refund_amount,
      0
    );

    // Logo path
    const logoPath = "../../views/invoice/images/bill-header.png";

    // Function to load the receipt after image is loaded
    const loadReceipt = () => {
      const receiptHtml = `
          <!DOCTYPE html>
          <html>
          <head>
              <style>
                  @page {
                      size: A4;
                      margin: 0;
                  }
                  html, body {
                      width: 100%;
                      height: 100%;
                      margin: 0;
                      padding: 0;
                      display: flex;
                      justify-content: center;
                      align-items: flex-start;
                  }
                  .receipt-container {
                      width: 80mm;
                      font-family: Bookman Old Style;
                      margin: 20px auto;
                      padding: 5mm;
                      background: white;
                      box-sizing: border-box;
                  }
                  .logo {
                      width: 100%;
                      text-align: center;
                      margin-bottom: 3mm;
                  }
                  .logo img {
                      max-width: 100%;
                      height: auto;
                      display: block;
                      margin: 0 auto;
                  }
                  .header {
                      text-align: center;
                      margin-bottom: 3mm;
                  }
                  .header h2 {
                      margin: 0 0 2mm 0;
                  }
                  .divider {
                      border-top: 1px dashed #000;
                      margin: 2mm 0;
                  }
                  .item {
                      font-size: 12px;
                      margin: 1mm 0;
                  }
                  .item-details {
                      display: flex;
                      justify-content: space-between;
                  }
                  .totals {
                      margin-top: 3mm;
                      text-align: right;
                  }
                  .footer {
                      text-align: center;
                      margin-top: 5mm;
                      font-size: 12px;
                  }
                  .footer p {
                      margin: 1mm 0;
                  }
              </style>
              <script>
                  function onImageLoad() {
                      setTimeout(() => {
                          window.print();
                      }, 500);
                  }
              </script>
          </head>
          <body>
              <div class="receipt-container">
                  <div class="logo">
                      <img src="${logoPath}" alt="Company Logo" onload="onImageLoad()" onerror="onImageLoad()">
                  </div>
                  
                  <div class="header">
                      <h2>REFUND RECEIPT</h2>
                      <div>Bill ID: ${refundData[0].bill_id}</div>
                      <div>Date: ${dateString}</div>
                      <div>Time: ${timeString}</div>
                  </div>
                  
                  <div class="divider"></div>
                  
                  <div class="items">
                      ${refundData
                        .map(
                          (item) => `
                          <div class="item">
                              <div>${item.product_name}</div>
                              <div class="item-details">
                                  <span>Qty: ${item.return_quantity}</span>
                                  <span>Amount: ${item.refund_amount.toFixed(
                                    2
                                  )}</span>
                              </div>
                          </div>
                      `
                        )
                        .join("")}
                  </div>
                  
                  <div class="divider"></div>
                  
                  <div class="totals">
                      <div><strong>Total Refund Amount: ${totalRefund.toFixed(
                        2
                      )}</strong></div>
                  </div>
                  
                  <div class="footer">
                      <p>Thank you for shopping with us!</p>
                      <p>This is your refund receipt.</p>
                  </div>
              </div>
          </body>
          </html>
      `;

      // Write the receipt HTML to the iframe
      const frameDoc = printFrame.contentWindow.document;
      frameDoc.open();
      frameDoc.write(receiptHtml);
      frameDoc.close();
    };

    // Load the receipt immediately
    loadReceipt();

    // Clean up after printing
    setTimeout(() => {
      document.body.removeChild(printFrame);
    }, 2000);
  }

  // Modify your existing submitRefund function to include receipt generation
  function submitRefund() {
    const billID = billInput.value.trim();
    if (billID === "") {
      alert("Please enter a Bill ID.");
      return;
    }

    const refundData = [];

    document.querySelectorAll(".return-qty").forEach((input) => {
      const returnQty = parseFloat(input.value) || 0;
      if (returnQty > 0) {
        const row = input.closest("tr");
        refundData.push({
          bill_id: billID,
          stock_id: row.children[0].textContent,
          product_barcode: row.children[1].textContent,
          product_name: row.children[2].textContent,
          return_quantity: returnQty,
          refund_amount: parseFloat(row.children[8].textContent),
        });
      }
    });

    if (refundData.length === 0) {
      alert("Please enter at least one valid return quantity.");
      return;
    }

    fetch("process_refund.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(refundData),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          // Generate and print the refund receipt
          generateRefundReceipt(refundData);

          alert("Refund processed successfully.");
          // refundTableBody.innerHTML = ""; // Clear table after refund
          // returnCashAmountInput.value = "0.00";
        } else {
          alert("Error processing refund.");
        }
      })
      .catch((error) => console.error("Error submitting refund:", error));
  }
  // Function to filter records by item code
  function filterByItemCode() {
    const filterValue = itemCodeFilterInput.value.trim().toLowerCase();
    document.querySelectorAll(".refund-table tbody tr").forEach((row) => {
      const itemCode = row
        .querySelector(".refund-item-code")
        .textContent.toLowerCase();
      if (itemCode.includes(filterValue)) {
        row.style.display = ""; // Show row if matches
      } else {
        row.style.display = "none"; // Hide row if not matching
      }
    });
  }

  // Fetch data on button click
  searchButton.addEventListener("click", fetchPurchaseItems);

  // Fetch data on pressing Enter
  billInput.addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
      fetchPurchaseItems();
    }
  });

  cashReturnBtn.addEventListener("click", submitRefund);
  itemCodeFilterInput.addEventListener("input", filterByItemCode);
});

const itemPromotionCheck = async () => {
  let notifier = new AWN();
  const barcodeValue = document.getElementById("barcode-input").value.trim();
  const qtyValue = parseFloat(document.getElementById("quantity").value.trim());
  const discountPercentage = document.getElementById("bcode_discount");
  const discountAmount = document.getElementById("bcode_discount_amount");
  const freeIssueValueHolder = document.getElementById("bcode_fi");
  let freeIssueAmount = 0;
  if (!barcodeValue || barcodeValue === null) {
    notifier.alert("valid barcode required!");
    return;
  }

  if (isNaN(qtyValue)) {
    notifier.alert("valid quantity required!");
    return;
  }
  const promotionResults = await fetchPromotions(barcodeValue);
  if (Object.keys(promotionResults).length === 0) {
    // notifier.tip("no promotions for this item");
  } else {
    Object.values(promotionResults).forEach((promotion) => {
      if (
        promotion.promo_type === "item" &&
        qtyValue >= promotion.buy_quantity
      ) {
        freeIssueAmount = promotion.free_quantity;
      } else if (
        promotion.promo_type === "discount" &&
        qtyValue >= promotion.buy_quantity
      ) {
        if (promotion.discount_percentage !== null) {
          discountPercentage.value = parseFloat(promotion.discount_percentage);
        } else {
          discountAmount.value = parseFloat(promotion.discount_amount);
        }
      }
    });
    freeIssueValueHolder.value = freeIssueAmount;
  }
};

const fetchPromotions = (barcode) => {
  return new Promise((resolve, reject) => {
    if (!barcode || barcode.length < 5) {
      console.log("Invalid barcode.");
      reject(new Error("Invalid barcode."));
      return;
    }

    fetch("fetch_promotions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `barcode=${encodeURIComponent(barcode)}`,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.success && data.promotions && data.promotions.length > 0) {
          const promotions = {};
          data.promotions.forEach((promo) => {
            promotions[promo.id] = promo;
          });
          resolve(promotions);
        } else {
          resolve({});
        }
      })
      .catch((error) => {
        console.error("Error fetching promotions:", error);
        reject(error);
      });
  });
};

// Open calculator
function openCalc() {
  document.getElementById("calcInput").value = ""; // Clear input field
  document.getElementById("calculatorModal").style.display = "block";
}

// Close calculator
function closeCalc() {
  document.getElementById("calculatorModal").style.display = "none";
}

// Append value to input
function appendCalc(value) {
  document.getElementById("calcInput").value += value;
}

// Clear input
function clearCalc() {
  document.getElementById("calcInput").value = "";
}

// Evaluate expression
function calculate() {
  try {
    document.getElementById("calcInput").value = eval(
      document.getElementById("calcInput").value
    );
  } catch {
    alert("Invalid Expression");
  }
}

// Handle keyboard input
document.addEventListener("keydown", function (event) {
  const key = event.key;
  const allowedKeys = "0123456789+-*/";

  if (allowedKeys.includes(key)) {
    appendCalc(key);
  } else if (key === "Enter" || key === "=") {
    calculate();
  } else if (key === "Backspace") {
    let input = document.getElementById("calcInput").value;
    document.getElementById("calcInput").value = input.slice(0, -1);
  } else if (key === "Escape") {
    closeCalc();
  } else if (key === "F9") {
    event.preventDefault(); // Prevent default browser behavior
    openCalc();
  }
});

// Make calculator draggable
dragElement(document.getElementById("calculatorModal"));

function dragElement(el) {
  var pos1 = 0,
    pos2 = 0,
    pos3 = 0,
    pos4 = 0;
  if (document.getElementById("calculatorHeader")) {
    document.getElementById("calculatorHeader").onmousedown = dragMouseDown;
  } else {
    el.onmousedown = dragMouseDown;
  }

  function dragMouseDown(e) {
    e.preventDefault();
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    document.onmousemove = elementDrag;
  }

  function elementDrag(e) {
    e.preventDefault();
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;
    el.style.top = el.offsetTop - pos2 + "px";
    el.style.left = el.offsetLeft - pos1 + "px";
  }

  function closeDragElement() {
    document.onmouseup = null;
    document.onmousemove = null;
  }
}

const deleteBillItem = async (rowId) => {
  const notifier = new AWN();
  const bill_type = getSelectedInvoiceType();
  const bill_id = document.getElementById("next_bill_no").value.trim();
  const item_name = document.querySelector(
    `.product_name_${rowId}`
  ).textContent;
  const barcode = document.querySelector(`.item_id_${rowId}`).textContent;
  const unit_price = document.querySelector(`.unit_price_${rowId}`).textContent;
  const quantity = document.querySelector(`.qty_${rowId}`).value.trim();
  const total_price = document.querySelector(`.subtotal_${rowId}`).textContent;
  const discount = document
    .getElementById(`discount_val_${rowId}`)
    .value.trim();
  const deleted_by = document.getElementById("current_user").value.trim();

  const data = {
    bill_id,
    item_name,
    barcode,
    unit_price,
    quantity,
    total_price,
    discount,
    bill_type,
    deleted_by,
  };

  try {
    let response = await fetch("./create_delete_bill_item_record.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    let result = await response.json();
    if (result.status === "success") {
      notifier.success("cart item removed. record saved.");
    } else {
      notifier.warning("Error: " + result.message);
    }
  } catch (error) {
    notifier.alert("An error occurred while deleting the bill item.");
  }
};

const saveCartState = (cart) => {
  localStorage.setItem("shopping_cart", JSON.stringify(cart));
};

// const loadCartState = () => {
//   const cart = JSON.parse(localStorage.getItem("shopping_cart")) || [];
//   return cart;
// };

// const clearCartState = () => {
//   localStorage.removeItem("shopping_cart");
// };

// document.addEventListener("DOMContentLoaded", function () {
//   const img = document.getElementById("selected-product-image");
//   const placeholderText = document.querySelector(".img-phold-text");

//   function checkImage() {
//       if (img.src && img.src.trim() !== "" && img.src !== window.location.href) {
//           img.style.display = "block";
//           placeholderText.style.display = "none";
//       } else {
//           img.style.display = "none";
//           placeholderText.style.display = "block";
//       }
//   }

//   // Run checkImage initially
//   checkImage();

// });

//shorkut key setup
document.addEventListener("keydown", (event) => {
  if (event.key === "F1") {
    event.preventDefault();
    holdInvoice();
  }
});

document.addEventListener("keydown", (event) => {
  const customerPlaceholder = document.getElementById("search-customer");
  if (event.key === "F3") {
    event.preventDefault();
    customerPlaceholder.focus();
  }
});

document.addEventListener("keydown", (event) => {
  const invoiceRadio = document.getElementById("tra_invoice");
  const wholesaleRadio = document.getElementById("tra_quotation");
  if (event.key === "F5") {
    event.preventDefault();
    if (invoiceRadio.checked) {
      wholesaleRadio.checked = true;
    } else {
      invoiceRadio.checked = true;
    }
  }
});

document.addEventListener("keydown", (event) => {
  const discountPlaceholder = document.getElementById("bcode_discount");
  if (event.key === "F6") {
    event.preventDefault();
    discountPlaceholder.focus();
  }
});

document.addEventListener("keydown", (event) => {
  const freeIssuePlaceholder = document.getElementById("bcode_fi");
  if (event.key === "F7") {
    event.preventDefault();
    freeIssuePlaceholder.focus();
  }
});

document.addEventListener("keydown", (event) => {
  const shortcutKeyModal = document.querySelector(".short-cut-keys-modal");
  if (event.key === "F8") {
    event.preventDefault();
    shortcutKeyModal.classList.toggle("fade-sk-modal");
  }
});

document.addEventListener("keydown", (event) => {
  const attendanceMarkModal = document.querySelector(".attendance-mark-modal");
  const barcodeInput = document.getElementById("barcode-input");
  const attendanceInput = document.getElementById("attendanceBarcodeInput");
  if (event.key === "F11") {
    event.preventDefault();
    attendanceMarkModal.classList.toggle("fade-sk-modal");
    if (attendanceMarkModal.classList.contains("fade-sk-modal")) {
      attendanceInput.focus();
      itemBarcodeScannerLock = false;
    } else {
      barcodeInput.focus();
      itemBarcodeScannerLock = true;
    }
  }
});

const markEmployeeAttendance = () => {
  const attendanceMarkModal = document.querySelector(".attendance-mark-modal");
  const barcodeInput = document.getElementById("barcode-input");
  const attendanceInput = document.getElementById("attendanceBarcodeInput");
  attendanceMarkModal.classList.toggle("fade-sk-modal");
  if (attendanceMarkModal.classList.contains("fade-sk-modal")) {
    attendanceInput.focus();
    itemBarcodeScannerLock = false;
  } else {
    barcodeInput.focus();
    itemBarcodeScannerLock = true;
  }
};

document.addEventListener("keydown", (event) => {
  if (event.key === "F10") {
    event.preventDefault();
    window.location.href = "../invoice/advance_list.php";
  }
});

document.addEventListener("keydown", (event) => {
  if (event.key === "PageUp") {
    const cashTenderedFocus = document.getElementById("total_cash_tendered");
    cashTenderedFocus.focus();
  }
});

const setMeasurementUnit = (unit) => {
  let notifier = new AWN();
  const mesUnit = document.getElementById("mes-unit-name");
  mesUnit.value = unit;
  updateProductUI(currentProduct);
  hideMesUnitModal();
};

document
  .getElementById("attendanceBarcodeInput")
  .addEventListener("change", function () {
    const empId = this.value.trim();
    if (!empId) return;

    // Fetch employee details first
    fetch("../../controllers/employee_controller.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "get",
        emp_id: empId,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          Swal.fire({
            title: "Confirm Employee",
            html: `Employee ID: ${data.data.emp_id}<br>Name: ${data.data.name}`,
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "OK",
            cancelButtonText: "Cancel",
          }).then((result) => {
            if (result.isConfirmed) {
              // Record attendance
              fetch("../../controllers/attendance_controller.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  action: "record",
                  emp_id: empId,
                }),
              })
                .then((response) => response.json())
                .then((data) => {
                  if (data.success) {
                    Swal.fire({
                      icon: "success",
                      title: data.message,
                      showConfirmButton: false,
                      timer: 1500,
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: data.message,
                    });
                  }
                  this.value = ""; // Clear input
                  this.focus(); // Refocus for next scan
                })
                .catch((error) => {
                  Swal.fire("Error", "An error occurred.", "error");
                  this.value = "";
                  this.focus();
                });
            } else {
              this.value = ""; // Clear input
              this.focus(); // Refocus for next scan
            }
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Employee not found",
          });
          this.value = ""; // Clear input
          this.focus(); // Refocus for next scan
        }
      })
      .catch((error) => {
        Swal.fire("Error", "An error occurred.", "error");
        this.value = "";
        this.focus();
      });
  });

const getPossibleBarcodeCombinations = (currentBranch) => {
  const barcodeInput = document.getElementById("barcode-input");
  const suggestionBox = document.getElementById("suggestion-box");

  barcodeInput.addEventListener("input", function () {
    const query = barcodeInput.value.trim();
    const branch = currentBranch;

    if (query.length < 5) {
      suggestionBox.innerHTML = "";
      return;
    }

    fetch(`./fetch_barcode_suggestions.php?query=${query}&branch=${branch}`)
      .then((response) => response.json())
      .then((data) => {
        suggestionBox.innerHTML = "";

        if (data.error) {
          console.error(data.error);
          return;
        }

        if (data.length === 0) {
          suggestionBox.innerHTML =
            "<div class='no-results'>No matches found</div>";
          return;
        }

        data.forEach((item) => {
          const suggestionItem = document.createElement("div");
          suggestionItem.classList.add("suggestion-item");
          suggestionItem.textContent = item.barcode;

          suggestionItem.addEventListener("click", function () {
            barcodeInput.value = item.barcode;
            suggestionBox.innerHTML = "";
          });

          suggestionBox.appendChild(suggestionItem);
        });
      })
      .catch((error) => console.error("Error fetching barcodes:", error));
  });

  // Hide suggestions when clicking outside
  document.addEventListener("click", function (event) {
    if (
      !barcodeInput.contains(event.target) &&
      !suggestionBox.contains(event.target)
    ) {
      suggestionBox.innerHTML = "";
    }
  });
};

document.addEventListener("keydown", function (event) {
  const mesUnitModal = document.getElementById("mes-unit-modal");
  if (!mesUnitModal.classList.contains("show-mes-unit-modal")) {
    return;
  }

  const rows = mesUnitModal.querySelectorAll("tr");
  let activeRowIndex = -1;

  rows.forEach((row, index) => {
    if (row.style.backgroundColor === "rgb(41, 128, 185)") {
      activeRowIndex = index;
    }
  });

  switch (event.key) {
    case "w":
      event.preventDefault();

      if (activeRowIndex >= 0) {
        rows[activeRowIndex].style.backgroundColor = "";
        rows[activeRowIndex].style.color = "";
      }

      activeRowIndex =
        activeRowIndex <= 0 ? rows.length - 1 : activeRowIndex - 1;

      rows[activeRowIndex].style.backgroundColor = "#2980b9";
      rows[activeRowIndex].style.color = "white";
      break;

    case "s":
      event.preventDefault();

      if (activeRowIndex >= 0) {
        rows[activeRowIndex].style.backgroundColor = "";
        rows[activeRowIndex].style.color = "";
      }

      activeRowIndex =
        activeRowIndex >= rows.length - 1 ? 0 : activeRowIndex + 1;

      rows[activeRowIndex].style.backgroundColor = "#2980b9";
      rows[activeRowIndex].style.color = "white";
      break;

    case "Control": // Trigger click on active row
      if (activeRowIndex >= 0) {
        event.preventDefault();
        rows[activeRowIndex].click();
      }
      break;
  }
});

document.addEventListener("DOMNodeInserted", function (event) {
  const mesUnitModal = document.getElementById("mes-unit-modal");
  if (
    event.target === mesUnitModal &&
    mesUnitModal.classList.contains("show-mes-unit-modal")
  ) {
    const rows = mesUnitModal.querySelectorAll("tr");
    if (rows.length > 0) {
      rows[0].style.backgroundColor = "#2980b9";
      rows[0].style.color = "white";
    }
  }
});

const mesUnitModal = document.getElementById("mes-unit-modal");
if (mesUnitModal) {
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "class" &&
        mesUnitModal.classList.contains("show-mes-unit-modal")
      ) {
        const rows = mesUnitModal.querySelectorAll("tr");
        if (rows.length > 0) {
          rows[0].style.backgroundColor = "#2980b9";
          rows[0].style.color = "white";
        }
      }
    });
  });

  observer.observe(mesUnitModal, { attributes: true });
}

const showMesUnitModal = () => {
  const targetModal = document.getElementById("mes-unit-modal");
  targetModal.classList.add("show-mes-unit-modal");
};

const hideMesUnitModal = () => {
  const targetModal = document.getElementById("mes-unit-modal");
  targetModal.classList.remove("show-mes-unit-modal");
};
