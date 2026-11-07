
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medina Car Rental Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }

        header {
            background: #14213d;
            color: white;
            padding: 22px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        .refresh-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .refresh-btn:hover {
            background: #1d4ed8;
        }

        .refresh-btn:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        main {
            max-width: 1150px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .top-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .muted {
            color: #64748b;
            font-size: 14px;
        }

        .customer-name {
            font-size: 28px;
            margin: 8px 0;
        }

        .badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: bold;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .summary-item {
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            min-width: 0;
        }

        .summary-item .label {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .summary-item .value {
            font-size: 15px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .section-title {
            margin: 28px 0 12px;
            font-size: 19px;
        }

        details {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        summary {
            padding: 17px 20px;
            cursor: pointer;
            font-weight: bold;
        }

        summary:hover {
            background: #f8fafc;
        }

        .detail-content {
            padding: 0 20px 20px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: minmax(140px, 1fr) 2fr;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .detail-row .key {
            color: #64748b;
        }

        .detail-row .data {
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        #message {
            margin-bottom: 15px;
            color: #64748b;
        }

        @media (max-width: 850px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Medina Car Rental</h1>
    <button class="refresh-btn" id="refreshBtn">
        Refresh
    </button>
</header>

<main>
    <p id="message">Loading rental records...</p>

    <div class="card" id="customerCard" hidden>
        <div class="top-line">
            <div>
                <div class="muted">Customer Information</div>
                <h2 class="customer-name" id="customerName">-</h2>
                <div class="muted" id="customerId">-</div>
            </div>

            <span class="badge" id="customerStatus">-</span>
        </div>

        <div class="summary-grid" id="summaryGrid"></div>
    </div>

    <div id="detailsContainer"></div>
</main>

<script>
    let rentals = [];
    let currentIndex = 0;

    const refreshBtn = document.getElementById('refreshBtn');
    const message = document.getElementById('message');

    const groups = [
        {
            title: 'Customer & Booking',
            sections: [
                ['customer', 'Customer Details'],
                ['rental_information', 'Rental Information']
            ]
        },
        {
            title: 'Vehicle & Inspection',
            sections: [
                ['vehicle', 'Vehicle Details'],
                ['vehicle_condition', 'Vehicle Condition']
            ]
        },
        {
            title: 'Financial & Insurance',
            sections: [
                ['rental_charges', 'Rental Charges'],
                ['payment_information', 'Payment Information'],
                ['insurance', 'Insurance']
            ]
        },
        {
            title: 'Return & Records',
            sections: [
                ['return_information', 'Return Information'],
                ['rental_summary', 'Rental Summary'],
                ['metadata', 'Metadata']
            ]
        }
    ];

    function formatLabel(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/\b\w/g, letter => letter.toUpperCase());
    }

    function formatValue(value) {
        if (value === null || value === undefined || value === '') {
            return 'Not Available';
        }

        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }

        return String(value);
    }

    function createSummaryItem(label, value) {
        const item = document.createElement('div');
        item.className = 'summary-item';

        const labelEl = document.createElement('div');
        labelEl.className = 'label';
        labelEl.textContent = label;

        const valueEl = document.createElement('div');
        valueEl.className = 'value';
        valueEl.textContent = formatValue(value);

        item.append(labelEl, valueEl);

        return item;
    }

    function createDetailSection(key, title, data) {
        const details = document.createElement('details');

        const summary = document.createElement('summary');
        summary.textContent = title;
        details.appendChild(summary);

        const content = document.createElement('div');
        content.className = 'detail-content';

        Object.entries(data || {}).forEach(([field, value]) => {
            const row = document.createElement('div');
            row.className = 'detail-row';

            const keyEl = document.createElement('div');
            keyEl.className = 'key';
            keyEl.textContent = formatLabel(field);

            const valueEl = document.createElement('div');
            valueEl.className = 'data';
            valueEl.textContent = formatValue(value);

            row.append(keyEl, valueEl);
            content.appendChild(row);
        });

        details.appendChild(content);

        return details;
    }

    function renderDashboard(record) {
        const customer = record.customer || {};
        const rental = record.rental_information || {};
        const vehicle = record.vehicle || {};
        const charges = record.rental_charges || {};
        const payment = record.payment_information || {};

        document.getElementById('customerName').textContent =
            formatValue(customer.name);

        document.getElementById('customerId').textContent =
            formatValue(customer.customer_id);

        document.getElementById('customerStatus').textContent =
            formatValue(customer.customer_status);

        const summaryGrid = document.getElementById('summaryGrid');
        summaryGrid.replaceChildren();

        const items = [
            ['Rental ID', rental.rental_id],
            ['Vehicle', [vehicle.brand, vehicle.model].filter(Boolean).join(' ')],
            ['Rental Status', rental.rental_status],
            ['Payment Status', payment.payment_status],
            ['Total Amount', '₱' + Number(charges.total_amount || 0).toLocaleString()],
            ['Remaining Balance', '₱' + Number(payment.remaining_balance || 0).toLocaleString()],
            ['Pickup Date', rental.pickup_date],
            ['Return Date', rental.return_date]
        ];

        items.forEach(([label, value]) => {
            summaryGrid.appendChild(createSummaryItem(label, value));
        });

        const container = document.getElementById('detailsContainer');
        container.replaceChildren();

        groups.forEach(group => {
            const heading = document.createElement('h3');
            heading.className = 'section-title';
            heading.textContent = group.title;

            container.appendChild(heading);

            group.sections.forEach(([key, title]) => {
                container.appendChild(
                    createDetailSection(key, title, record[key])
                );
            });
        });

        document.getElementById('customerCard').hidden = false;

            message.textContent = "";
    }

    async function loadRentals() {
        refreshBtn.disabled = true;

        try {
            const response = await fetch('/api/rentals', {
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('API returned HTTP ' + response.status);
            }

            const data = await response.json();

            if (!Array.isArray(data) || data.length === 0) {
                throw new Error('No rental records found.');
            }

            rentals = data;
            currentIndex = 0;

            renderDashboard(rentals[currentIndex]);

        } catch (error) {
            message.textContent = 'Error: ' + error.message;
            console.error(error);
        } finally {
            refreshBtn.disabled = false;
        }
    }

    async function refreshCustomer() {
        refreshBtn.disabled = true;

        try {
            const response = await fetch('/api/rentals', {
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('API returned HTTP ' + response.status);
            }

            const data = await response.json();

            if (!Array.isArray(data) || data.length === 0) {
                throw new Error('No rental records found.');
            }

            rentals = data;

            currentIndex = (currentIndex + 1) % rentals.length;

            renderDashboard(rentals[currentIndex]);

        } catch (error) {
            message.textContent = 'Error: ' + error.message;
            console.error(error);
        } finally {
            refreshBtn.disabled = false;
        }
    }

    refreshBtn.addEventListener('click', refreshCustomer);

    document.addEventListener('DOMContentLoaded', loadRentals);
</script>

</body>
</html>
