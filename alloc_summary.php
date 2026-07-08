<?php
require 'auth.php';
require_login();
$user_role = get_user_role();
$username = get_username();

if ($user_role !== 'superadmin' && $user_role !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ප්‍රතිපාදන පිළිබඳ සාරාංශය (Allocation Summary)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        @media print {
            body { background: white; color: black; }
            .orb, .controls, .tabs, .print-btn, .back-btn { display: none !important; }
            .container { max-width: 100%; box-shadow: none; margin: 0; padding: 0; }
            .glass-panel { background: none; border: none; box-shadow: none; padding: 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
            th, td { border: 1px solid #000 !important; padding: 6px; text-align: left; }
            header { text-align: center; margin-bottom: 20px; }
            header h1 { color: black; font-size: 24px; }
            header h2 { color: #333; font-size: 18px; }
            .summary-header h3 { color: black; }
            tr:nth-child(even) { background-color: #f9f9f9; }
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        @media (max-width: 600px) {
            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .user-info {
                justify-content: space-between;
                width: 100%;
            }
            .header-actions > div:first-child {
                display: flex;
                width: 100%;
            }
            .back-btn {
                width: 100%;
                text-align: center;
            }
        }
        .print-btn, .back-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: white;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .print-btn { background: #10b981; }
        .print-btn:hover { background: #059669; }
        .back-btn { background: #6b7280; }
        .back-btn:hover { background: #4b5563; }
        .summary-header { margin-bottom: 15px; text-align: center; color: white; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <div class="header-actions">
            <div>
                <a href="index.php" class="back-btn">ආපසු (Back)</a>
            </div>
            <div class="user-info">
                <span style="color: rgba(255,255,255,0.8); font-size: 14px;">Logged in as: <strong style="color: #fff; text-transform: capitalize;"><?php echo htmlspecialchars($username); ?></strong></span>
                <button class="print-btn" onclick="window.print()">මුද්‍රණය කරන්න (Print)</button>
            </div>
        </div>
        
        <header>
            <h1>සමාජ සේවා දෙපාර්තමේන්තුවේ මාසික ප්‍රගති සාරාංශය</h1>
            <h2>ප්‍රතිපාදන පිළිබඳ සාරාංශය (Allocation Summary)</h2>
        </header>

        <div class="glass-panel controls">
            <div class="control-group">
                <label for="year">වර්ෂය</label>
                <input type="number" id="year" value="2026" min="2020" max="2050">
            </div>
            <div class="control-group">
                <label for="month">මාසය</label>
                <select id="month">
                    <option value="1">ජනවාරි</option>
                    <option value="2">පෙබරවාරි</option>
                    <option value="3">මාර්තු</option>
                    <option value="4">අප්‍රේල්</option>
                    <option value="5">මැයි</option>
                    <option value="6">ජූනි</option>
                    <option value="7">ජූලි</option>
                    <option value="8">අගෝස්තු</option>
                    <option value="9">සැප්තැම්බර්</option>
                    <option value="10">ඔක්තෝබර්</option>
                    <option value="11">නොවැම්බර්</option>
                    <option value="12">දෙසැම්බර්</option>
                </select>
            </div>
        </div>

        <div class="glass-panel">
            <div class="tabs">
                <button class="tab-btn active" data-type="financial">මූල්‍යාධාර තොරතුරු</button>
                <button class="tab-btn" data-type="equipment">ආබාධිත උපකරණ / වෙනත්</button>
            </div>
            
            <div class="summary-header">
                <h3 id="reportTitle"></h3>
            </div>

            <div class="table-container">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>කාණ්ඩය</th>
                            <th style="text-align: right;">වෙන් කරන ලද ප්‍රතිපාදන (රු.)</th>
                            <th style="text-align: right;">සමුච්චිත වියදම (රු.)</th>
                            <th style="text-align: right;">ඉතිරි ප්‍රතිපාදන (රු.)</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Rows injected by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const userRole = '<?php echo $user_role; ?>';
        const monthNames = ["ජනවාරි", "පෙබරවාරි", "මාර්තු", "අප්‍රේල්", "මැයි", "ජූනි", "ජූලි", "අගෝස්තු", "සැප්තැම්බර්", "ඔක්තෝබර්", "නොවැම්බර්", "දෙසැම්බර්"];
        let currentType = 'financial';

        async function loadSummary() {
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            
            const typeLabel = currentType === 'financial' ? 'මූල්‍යාධාර තොරතුරු' : 'ආබාධිත උපකරණ / වෙනත්';
            document.getElementById('reportTitle').textContent = `වර්ෂ ${year} ${monthNames[month - 1]} මාසය දක්වා - ප්‍රතිපාදන පිළිබඳ සාරාංශය (${typeLabel})`;

            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>';

            try {
                const res = await fetch(`api.php?action=get_alloc_summary&year=${year}&month=${month}&type=${currentType}&t=${new Date().getTime()}`);
                const data = await res.json();
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Error: ${data.error}</td></tr>`;
                } else {
                    renderTable(data);
                }
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color: red;">Error loading summary</td></tr>';
            }
        }

        function renderTable(records) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            let totalAlloc = 0;
            let totalSpent = 0;
            let totalRemaining = 0;

            records.forEach(rec => {
                const alloc = parseFloat(rec.allocated_amount) || 0;
                const spent = parseFloat(rec.cumulative_spent) || 0;
                const remaining = parseFloat(rec.remaining_balance) || 0;

                totalAlloc += alloc;
                totalSpent += spent;
                totalRemaining += remaining;

                const tr = document.createElement('tr');
                
                const allocCell = (userRole === 'superadmin')
                    ? `<input type="number" step="0.01" class="alloc-input" data-cat="${rec.category}" value="${alloc.toFixed(2)}" style="width: 120px; text-align: right; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 4px; padding: 4px 8px; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">`
                    : `<span>${alloc.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;

                tr.innerHTML = `
                    <td>${rec.category}</td>
                    <td style="text-align: right; padding: 2px 8px;">${allocCell}</td>
                    <td style="text-align: right;">${spent.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td style="text-align: right; color: ${remaining < 0 ? '#ef4444' : '#86efac'};">${remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                `;
                tbody.appendChild(tr);
            });

            if (records.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No data found</td></tr>`;
                 return;
            }

            // Add Total Row
            const trTotal = document.createElement('tr');
            trTotal.style.fontWeight = 'bold';
            trTotal.style.background = 'rgba(255, 255, 255, 0.1)';
            trTotal.innerHTML = `
                <td>එකතුව (Total)</td>
                <td style="text-align: right;">${totalAlloc.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td style="text-align: right;">${totalSpent.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td style="text-align: right; color: ${totalRemaining < 0 ? '#ef4444' : '#86efac'};">${totalRemaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(trTotal);

            // Add change event listener for inline editing of allocations
            if (userRole === 'superadmin') {
                document.querySelectorAll('.alloc-input').forEach(input => {
                    input.addEventListener('change', async function() {
                        const catName = this.dataset.cat;
                        const val = parseFloat(this.value) || 0;
                        const year = document.getElementById('year').value;
                        const month = document.getElementById('month').value;
                        
                        try {
                            const res = await fetch('api.php?action=update_allocation', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    year: year,
                                    month: month,
                                    type: currentType,
                                    category: catName,
                                    allocated_amount: val
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                showToast('ප්‍රතිපාදන සාර්ථකව යාවත්කාලීන කරන ලදී (Allocation Updated)');
                                loadSummary();
                            } else {
                                showToast('Error: ' + data.error, true);
                            }
                        } catch (e) {
                            console.error(e);
                            showToast('Network error', true);
                        }
                    });
                });
            }
        }

        function showToast(msg, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            if (isError) toast.classList.add('error');
            else toast.classList.remove('error');
            
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Event Listeners
        document.getElementById('year').addEventListener('change', loadSummary);
        document.getElementById('month').addEventListener('change', loadSummary);
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                currentType = e.target.dataset.type;
                loadSummary();
            });
        });

        // Set current month/year
        const d = new Date();
        document.getElementById('year').value = d.getFullYear();
        document.getElementById('month').value = d.getMonth() + 1;

        // Init
        loadSummary();
    </script>
    <div id="toast">Data saved successfully!</div>
    <?php include 'chatbot.php'; ?>
</body>
</html>
