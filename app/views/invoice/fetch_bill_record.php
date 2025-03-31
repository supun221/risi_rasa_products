<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Records</title>
    <link rel="stylesheet" href="./styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    .table-controls {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: 20px;
        padding-left: 15px;
    }

    .table-controls label {
        font-size: 16px;
        margin-right: 10px;
    }

    .select-custom {
        width: 150px;
        padding: 5px;
        font-size: 14px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        background: #fff;
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 auto;
    }

    thead {
        background-color: #007bff;
        color: white;
    }

    th,
    td {
        text-align: center;
        padding: 10px;
        border: 1px solid #ddd;
    }

    th {
        font-size: 14px;
    }

    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tbody tr:hover {
        background-color: #f1f1f1;
    }

    button {
        padding: 5px 10px;
        font-size: 14px;
        margin: 2px;
        cursor: pointer;
        border: none;
        border-radius: 5px;
        color: white;
    }

    button:nth-child(1) {
        background-color: #28a745;
    }

    button:nth-child(2) {
        background-color: #007bff;
    }

    button:hover {
        opacity: 0.8;
    }
</style>

<body>
    <h1>Bill Records</h1>

    <!-- Dropdown to select the range -->
    <div class="table-controls">
        <label for="range">Show Last:</label>
        <select id="range" class="select-custom" onchange="recordFilter()">
            <option value="10" selected>10 Bills</option>
        </select>
    </div>

    <table id="bill-records-table" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Bill ID</th>
                <th>Customer ID</th>
                <th>Gross Amount</th>
                <th>Net Amount</th>
                <th>Discount Amount</th>
                <th>Products No</th>
                <th>Payment Type</th>
                <th>Balance</th>
                <th>Bill Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Table rows will be dynamically added here -->
        </tbody>
    </table>

    <script>
        let dataArray = []

        document.addEventListener('DOMContentLoaded', () => {
            const rangeDropdown = document.getElementById('range');
            
            // Initial fetch with default value (10 bills)
            fetchBillRecords(rangeDropdown.value);

            // Re-fetch whenever the dropdown value changes
            // rangeDropdown.addEventListener('change', () => {
            //     const selectedRange = rangeDropdown.value;
            //     fetchBillRecords(selectedRange);
            // });
        });

        const recordFilter = () => {
            let newBillArray = []
            const billRangeSelector = document.getElementById("range")
            if(billRangeSelector.value === '10'){
                dataArray.filter((item, index) => {
                    if(index < 10){
                        newBillArray.push(item)
                    }
                })
                populateTable(newBillArray);
            }
            else if(billRangeSelector.value === '25'){
                dataArray.filter((item, index) => {
                    if(index < 25){
                        newBillArray.push(item)
                    }
                })
                populateTable(newBillArray);
            }
            else if(billRangeSelector.value === '50'){
                dataArray.filter((item, index) => {
                    if(index < 50){
                        newBillArray.push(item)
                    }
                })
                populateTable(newBillArray);
            }else{
                populateTable(dataArray)
            }
        }

        // Fetch bill records with the selected range
        function fetchBillRecords(range) {
            fetch(`./bill_records.php?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        dataArray = data.data
                        recordFilter()
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching records:', error);
                    Swal.fire('Error', 'Failed to fetch bill records.', 'error');
                });
        }

        // Populate the table with fetched records
        function populateTable(records) {
            const tableBody = document.querySelector('#bill-records-table tbody');
            tableBody.innerHTML = ''; // Clear previous rows

            records.forEach((record, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${record.bill_id}</td>
                    <td>${record.customer_id}</td>
                    <td>${record.gross_amount}</td>
                    <td>${record.net_amount}</td>
                    <td>${record.discount_amount}</td>
                    <td>${record.num_of_products}</td>
                    <td>${record.payment_type}</td>
                    <td>${record.balance}</td>
                    <td>${record.bill_date}</td>
                `;

                // Add action buttons
                const actionCell = document.createElement('td');
                // const saveButton = document.createElement('button');
                // saveButton.textContent = 'Save';
                // saveButton.onclick = () => saveRecord(record);

                const viewButton = document.createElement('button');
                viewButton.textContent = 'View';
                viewButton.onclick = () => viewBill(record);

                // actionCell.appendChild(saveButton);
                actionCell.appendChild(viewButton);
                row.appendChild(actionCell);

                tableBody.appendChild(row);
            });
        }

        // Save record as JSON file
        function saveRecord(record) {
            const blob = new Blob([JSON.stringify(record, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `bill_${record.bill_id}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }

        // View record (generate PDF)
        function viewBill(record) {
            const payload = {
                bill_id: record.bill_id,
                bill_date: record.bill_date,
                customer_id: record.customer_id,
                gross_amount: record.gross_amount,
                discount_amount: record.discount_amount,
                net_amount: record.net_amount,
                balance: record.balance,
                payment_type: record.payment_type,
                productList: record.purchase_items,
            };
            console.log(payload);
            fetch('./pos_bill_list_new.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
                .then(response => response.ok ? response.blob() : Promise.reject(response))
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_blank');
                    URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Error generating bill:', error);
                    Swal.fire('Error', 'Failed to generate the bill.', 'error');
                });
        }
        document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });

    </script>
</body>

</html>
