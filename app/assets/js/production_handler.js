let raw_materials = [];
let current_stock = [];
let notifier = new AWN();

document.addEventListener("DOMContentLoaded", async function () {
  try {
    const response = await fetch("./logic/fetch_stock_entries.php");
    current_stock = await response.json();
  } catch (error) {
    notifier.alert("Error fetching stock entries:", error);
  }
});

document.addEventListener("DOMContentLoaded", async function () {
  try {
    const response = await fetch("./logic/fetch_raw_materials.php");
    raw_materials = await response.json();
    const dropdown = document.getElementById("raw-ingredient-selector");
    raw_materials.forEach((entry) => {
      let option = document.createElement("option");
      option.value = entry.id;
      const createdDate = new Date(entry.created_at);
      const formattedDate = createdDate.toLocaleDateString();
      option.textContent = `${entry.product_name} (Added: ${formattedDate})`;

      option.dataset.stock = entry.available_stock;
      dropdown.appendChild(option);
    });
  } catch (error) {
    notifier.alert("Error fetching stock entries:", error);
  }
});

document
  .getElementById("raw-ingredient-selector")
  .addEventListener("change", function () {
    const selectedId = this.value;
    const selectedEntry = raw_materials.find((entry) => entry.id == selectedId);

    if (selectedEntry) {
      document.getElementById("raw-material-name").value =
        selectedEntry.product_name;
      document.getElementById("raw-material-rem-stock").value =
        selectedEntry.available_stock;
    }
    materialValidator();
  });

const materialValidator = () => {
  const remainderPlaceHolder = document.querySelector(".rem-amount-info");
  const remainderValue = document.getElementById(
    "raw-material-rem-stock"
  ).value;
  const materialSelector = document.getElementById(
    "raw-ingredient-selector"
  ).value;

  if (materialSelector != "null") {
    remainderPlaceHolder.textContent = `Remaining stock is: ${remainderValue} Kilograms.`;
  } else {
    remainderPlaceHolder.textContent = "";
  }
  filterToggler();
};

const amountValidator = () => {
  const remainderValue = document.getElementById(
    "raw-material-rem-stock"
  ).value;
  const amountPlaceHolder = parseFloat(
    document.getElementById("raw-material-inp-amount").value
  );
  const errorBanner = document.querySelector(".error-displayer");
  if (amountPlaceHolder > remainderValue) {
    errorBanner.classList.remove("hide-error");
  } else {
    errorBanner.classList.add("hide-error");
  }
  filterToggler();
};

const filterToggler = () => {
  const filterLayer = document.getElementById("filter-layer");
  const rawMaterialSelector = document.getElementById(
    "raw-ingredient-selector"
  ).value;
  const amountPlaceHolder = document.getElementById(
    "raw-material-inp-amount"
  ).value;

  if (amountPlaceHolder > 0 && rawMaterialSelector != "null") {
    filterLayer.classList.add("hide-filter-layer");
  } else {
    filterLayer.classList.remove("hide-filter-layer");
  }
};

const itemRowGenerator = () => {
  const tableBody = document.querySelector("#produced-items-tb tbody");
  const rowCount = tableBody.getElementsByTagName("tr").length;
  const newRow = document.createElement("tr");
  newRow.id = `item_${rowCount}`;

  // <td><input type="number" id="stock_id_${rowCount}" /></td>
  newRow.innerHTML = `
      <td><input type="text" id="item_barcode_${rowCount}" /></td>
      <td>
        <input type="text" id="item_name_${rowCount}" />
        <div class="item-name-suggester name_suggestor_${rowCount}"></div>
      </td>
      <td><input type="number" id="item_weight_${rowCount}" /></td>
      <td><input type="number" id="item_qty_${rowCount}" /></td>
      <td><input type="number" id="item_price_${rowCount}" /></td>
      <td><input type="number" id="cost_price_${rowCount}" /></td>
      <td><input type="number" id="wholesale_price_${rowCount}" /></td>
      <td><input type="number" id="mr_price_${rowCount}" /></td>
      <td><input type="number" id="sc_price_${rowCount}" /></td>
      <td><button class="remove-item-btn" data-row="${rowCount}">❌</button></td>
    `;

  tableBody.appendChild(newRow);
  newRow
    .querySelector(".remove-item-btn")
    .addEventListener("click", function () {
      document.getElementById(`item_${this.dataset.row}`).remove();
    });
};

document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelector("#produced-items-tb tbody")
    .addEventListener("keydown", function (event) {
      if (
        event.key === "Enter" &&
        event.target.id.startsWith("item_barcode_")
      ) {
        event.preventDefault();
        const rowIndex = event.target.id.split("_").pop();
        fetchProductNames(event.target.value, rowIndex);
      }
    });
});

const fetchProductNames = async (barcode, rowIndex) => {
  try {
    const response = await fetch("./logic/product_name_finder.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ barcode }),
    });

    const result = await response.json();
    if (result.status === "success") {
      displaySuggestions(result.data, rowIndex);
    } else {
      notifier.alert(result.message);
    }
  } catch (error) {
    console.error("Error fetching product names:", error);
  }
};

const displaySuggestions = (suggestions, rowIndex) => {
  const suggestor = document.querySelector(`.name_suggestor_${rowIndex}`);
  suggestor.innerHTML = ""; // Clear previous results
  suggestor.style.display = suggestions.length > 0 ? "block" : "none";

  suggestions.forEach((name) => {
    const div = document.createElement("div");
    div.classList.add("suggestion-item");
    div.textContent = name;
    div.onclick = () => {
      document.getElementById(`item_name_${rowIndex}`).value = name;
      suggestor.style.display = "none";
    };
    suggestor.appendChild(div);
  });
};

async function createStock(
  barcode,
  productName,
  availableStock,
  ourPrice,
  costPrice,
  wholesalePrice,
  mrPrice,
  scPrice
) {
  try {
    const response = await fetch("./logic/create_stock_entry.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `barcode=${barcode}&stock_id=0&product_name=${productName}&available_stock=${availableStock}&our_price=${ourPrice}&cost_price=${costPrice}&wholesale_price=${wholesalePrice}&mr_price=${mrPrice}&sc_price=${scPrice}`,
    });

    const data = await response.json();
    console.log(data.message);
  } catch (error) {
    console.error("Error creating stock:", error);
  }
}

async function updateStock(barcode, price, newStock) {
  try {
    const response = await fetch("./logic/update_stock_entry.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `barcode=${barcode}&our_price=${price}&new_stock=${newStock}`,
    });

    const data = await response.text();
    console.log(data);
  } catch (error) {
    console.error("Error updating stock:", error);
  }
}

const findExistingStock = (barcode, price) => {
  let designatedItem = current_stock.find((item) => {
    if (
      item.barcode == barcode &&
      item.our_price == parseFloat(price).toFixed(2)
    ) {
      return item;
    }
  });

  return designatedItem;
};

const loader = document.getElementById("loader-container");

const executeProduction = () => {
  let totalWeight = 0;
  const inputWeight = parseFloat(
    document.getElementById("raw-material-inp-amount").value
  );
  const date = getCurrentDate();
  loader.classList.remove("hide-spinner");
  const materialItem = parseInt(
    document.getElementById("raw-ingredient-selector").value
  );
  const tableRows = document.querySelectorAll("#produced-items-tb tbody tr");
  const remainder = parseFloat(
    document.getElementById("raw-material-rem-stock").value
  );
  const productionData = [];

  tableRows.forEach((row) => {
    const rowId = row.id.split("_")[1];
    const barcode = document.getElementById(`item_barcode_${rowId}`).value;
    // const stockId = document.getElementById(`stock_id_${rowId}`).value;
    const productName = document.getElementById(`item_name_${rowId}`).value;
    const weight = document.getElementById(`item_weight_${rowId}`).value;
    const quantity = document.getElementById(`item_qty_${rowId}`).value;
    const price = document.getElementById(`item_price_${rowId}`).value;
    const cost_price = document.getElementById(`cost_price_${rowId}`).value;
    const wholesale_price = document.getElementById(
      `wholesale_price_${rowId}`
    ).value;
    const mr_price = document.getElementById(`mr_price_${rowId}`).value;
    const sc_price = document.getElementById(`sc_price_${rowId}`).value;
    const totalOutput = (weight * quantity) / 1000;

    productionData.push({
      barcode,
      productName,
      weight,
      quantity,
      price,
      totalOutput,
      cost_price,
      wholesale_price,
      mr_price,
      sc_price,
    });
  });

  const productionOrderId = generateProductionOrderId();

  productionData.forEach((item) => {
    let existingStock = findExistingStock(item.barcode, item.price);
    totalWeight += item.totalOutput;

    if (existingStock) {
      const newStock =
        parseFloat(existingStock.available_stock) + parseFloat(item.quantity);
      updateStock(item.barcode, item.price, newStock);
    } else {
      createStock(
        item.barcode,
        item.productName,
        item.quantity,
        item.price,
        item.cost_price,
        item.wholesale_price,
        item.mr_price,
        item.sc_price
      );
    }

    createProductionOrderItem(
      productionOrderId,
      item.productName,
      item.barcode,
      item.quantity,
      item.weight,
      item.price,
      item.totalOutput
    );
  });
  createProductionOrder(
    productionOrderId,
    date,
    inputWeight,
    totalWeight,
    productionData.length
  );
  updateRawMaterialStock(materialItem, parseInt(remainder - inputWeight));

  setTimeout(() => {
    loader.classList.add("hide-spinner");
  }, 2000);
  notifier.success("stock entries updated!");
  setTimeout(() => {
    window.location.reload();
  }, 1000);
};

const createProductionOrder = async (
  production_order_id,
  produced_date,
  input_amount,
  output_amount,
  number_item_variations
) => {
  const formData = new FormData();
  formData.append("production_order_id", production_order_id);
  formData.append("produced_date", produced_date);
  formData.append("input_amount", input_amount);
  formData.append("output_amount", output_amount);
  formData.append("number_item_variations", number_item_variations);

  try {
    const response = await fetch("./logic/create_production_order.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    console.log(result);
    return result;
  } catch (error) {
    console.error("Error:", error);
    return { status: "error", message: "Request failed" };
  }
};

const createProductionOrderItem = async (
  production_order_id,
  item_name,
  barcode,
  quantity,
  unit_weight,
  price,
  total_weight
) => {
  const formData = new FormData();
  formData.append("production_order_id", production_order_id);
  formData.append("item_name", item_name);
  formData.append("barcode", barcode);
  formData.append("quantity", quantity);
  formData.append("unit_weight", unit_weight);
  formData.append("price", price);
  formData.append("total_weight", total_weight);

  try {
    const response = await fetch("./logic/create_production_order_items.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    console.log(result);
    return result;
  } catch (error) {
    console.error("Error:", error);
    return { status: "error", message: "Request failed" };
  }
};

const generateProductionOrderId = () => {
  const now = new Date();
  const formattedDateTime =
    now.getFullYear().toString() +
    String(now.getMonth() + 1).padStart(2, "0") +
    String(now.getDate()).padStart(2, "0") +
    String(now.getHours()).padStart(2, "0") +
    String(now.getMinutes()).padStart(2, "0") +
    String(now.getSeconds()).padStart(2, "0");
  const randomNum = Math.floor(100 + Math.random() * 900);

  return `PRD/${formattedDateTime}${randomNum}`;
};

const getCurrentDate = () => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const updateRawMaterialStock = async (id, new_stock) => {
  try {
    const response = await fetch("./logic/update_raw_material_stock.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id, new_stock }),
    });

    const result = await response.json();
    console.log(result.message);

    return result.status === "success";
  } catch (error) {
    console.error("Error updating stock:", error);
    return false;
  }
};
