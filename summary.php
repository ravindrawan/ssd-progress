<?php
require 'auth.php';
require_login();
$user_role = get_user_role();
$username = get_username();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>මාසික ප්‍රගති සාරාංශය (Monthly Summary)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        @media print {
            body { background: white; color: black; }
            .orb, .controls, .tabs, .print-btn, .back-btn { display: none !important; }
            .container { max-width: 100%; box-shadow: none; margin: 0; padding: 0; }
            .glass-panel { background: none; border: none; box-shadow: none; padding: 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000 !important; padding: 8px; text-align: left; }
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
            <h2>වයඹ පළාත - සියලුම ප්‍රාදේශීය ලේකම් කාර්යාල වල එකතුව</h2>
        </header>

        <div class="glass-panel controls">
            <div class="control-group">
                <label for="ag_office">ප්‍රාදේශීය ලේකම් කාර්යාලය</label>
                <select id="ag_office">
                    <option value="all">සියලුම ප්‍රාදේශීය ලේකම් කාර්යාල වල එකතුව</option>
                </select>
            </div>
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
                    <thead id="tableHead">
                        <tr>
                            <th>කාණ්ඩය</th>
                            <th style="text-align: right;">ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                            <th style="text-align: right;">ප්‍රතිලාභීන්</th>
                            <th style="text-align: right;">මාසික වියදම (රු.)</th>
                            <th style="text-align: right;">සමුච්චිත මුදල (රු.)</th>
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
        const categories = {
            financial: [
                'මහජනාධාර', 'පිලිකාධාර', 'තැලසීමියාධාර', 'ක්ෂය රෝගාධාර', 
                'ලාදුරු ආධාර', 'සිසුමිණ ශිෂ්‍යාධාර', 'විශේෂ වෛද්‍යාධාර', 'වකුගඩු ආධාර'
            ],
            equipment: [
                'වන අලි වගා හානි සඳහා ගෙවීම් සිදු කිරීම', 'නිවාස හානි සඳහා ගෙවීම් සිදු කිරීම', 
                'රෝද පුටු ලබාදීම', 'ත්‍රෛසිකල් ලබාදීම', 'කිහිලිකරු ලබාදීම', 'අවිදින රාමු ලබාදීම', 
                'අත්වාරු ලබාදීම', 'වායුමෙට්ට/ජල මෙට්ට ලබාදීම', 'ශ්‍රවණ උපකරණ ලබාදීම', 
                'කෘතිම අත් ලබාදීම', 'කෘතිම පාද ලබාදීම', 'සුදුසැරයටි ලබාදීම', 'ඇස් කණ්ණාඩි ලබාදීම', 
                'පිළිසරණී නිවාස ආධාර ලබාදීම', 'ස්වශක්ති ආධාර ලබාදීම', 'ස්වයංරැකියා ආධාර ලබාදීම'
            ]
        };

        const monthNames = ["ජනවාරි", "පෙබරවාරි", "මාර්තු", "අප්‍රේල්", "මැයි", "ජූනි", "ජූලි", "අගෝස්තු", "සැප්තැම්බර්", "ඔක්තෝබර්", "නොවැම්බර්", "දෙසැම්බර්"];

        let currentType = 'financial';

        async function loadOffices() {
            try {
                const res = await fetch('api.php?action=get_offices');
                const offices = await res.json();
                const select = document.getElementById('ag_office');
                offices.forEach(office => {
                    const opt = document.createElement('option');
                    opt.value = office.id;
                    opt.textContent = office.name;
                    select.appendChild(opt);
                });
                loadSummary();
            } catch (e) {
                console.error('Failed to load offices', e);
            }
        }

        async function loadSummary() {
            const agOffice = document.getElementById('ag_office').value;
            const agOfficeName = document.getElementById('ag_office').options[document.getElementById('ag_office').selectedIndex].text;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            
            const typeLabel = currentType === 'financial' ? 'මූල්‍යාධාර තොරතුරු' : 'ආබාධිත උපකරණ / වෙනත්';
            
            document.querySelector('header h2').textContent = agOffice === 'all' ? 'වයඹ පළාත - සියලුම ප්‍රාදේශීය ලේකම් කාර්යාල වල එකතුව' : `වයඹ පළාත - ${agOfficeName}`;
            
            document.getElementById('reportTitle').textContent = `වර්ෂ ${year} ${monthNames[month - 1]} මාසය - ${typeLabel}`;

            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) {
                tableContainer.style.minHeight = tableContainer.offsetHeight + 'px';
            }

            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>';

            try {
                const res = await fetch(`api.php?action=get_summary&year=${year}&month=${month}&type=${currentType}&ag_office_id=${agOffice}`);
                const data = await res.json();
                renderTable(data.records, data.cumulative);
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color: red;">Error loading summary</td></tr>';
            } finally {
                if (tableContainer) {
                    tableContainer.style.minHeight = '';
                }
            }
        }

        function renderTable(records, cumulative) {
            const tbody = document.getElementById('tableBody');
            const thead = document.getElementById('tableHead');
            tbody.innerHTML = '';
            
            if (currentType === 'financial') {
                thead.innerHTML = `
                    <tr>
                        <th>කාණ්ඩය</th>
                        <th style="text-align: right;">ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                        <th style="text-align: right;">ප්‍රතිලාභීන්</th>
                        <th style="text-align: right;">මාසික වියදම (රු.)</th>
                        <th style="text-align: right;">සමුච්චිත මුදල (රු.)</th>
                    </tr>
                `;
            } else {
                thead.innerHTML = `
                    <tr>
                        <th>කාණ්ඩය</th>
                        <th style="text-align: right;">ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                        <th style="text-align: right;">මාසය තුළ ප්‍රතිලාභීන් ගණන</th>
                        <th style="text-align: right;">සමුච්චිත ප්‍රතිලාභීන්</th>
                    </tr>
                `;
            }
            
            let totalEst = 0;
            let totalAct = 0;
            let totalAmt = 0;
            let totalAlloc = 0;
            let totalCumAmt = 0;
            let totalBal = 0;
            let totalCumAct = 0;

            categories[currentType].forEach(cat => {
                const rec = records[cat] || { est: 0, act: 0, amt: 0, alloc: 0 };
                let cumAmt = 0;
                let cumAct = 0;

                if (cumulative[cat] !== undefined) {
                    if (typeof cumulative[cat] === 'object') {
                        cumAmt = parseFloat(cumulative[cat].amount) || 0;
                        cumAct = parseInt(cumulative[cat].act) || 0;
                    } else {
                        cumAmt = parseFloat(cumulative[cat]) || 0;
                    }
                }
                
                const est = parseInt(rec.est) || 0;
                const act = parseInt(rec.act) || 0;
                const amt = parseFloat(rec.amt) || 0;
                const alloc = parseFloat(rec.alloc) || 0;
                const bal = alloc - cumAmt;

                totalEst += est;
                totalAct += act;
                totalAmt += amt;
                totalAlloc += alloc;
                totalCumAmt += cumAmt;
                totalBal += bal;
                totalCumAct += cumAct;

                const tr = document.createElement('tr');
                if (currentType === 'financial') {
                    tr.innerHTML = `
                        <td>${cat}</td>
                        <td style="text-align: right;">${est}</td>
                        <td style="text-align: right;">${act}</td>
                        <td style="text-align: right;">${amt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td style="text-align: right;">${cumAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    `;
                } else {
                    tr.innerHTML = `
                        <td>${cat}</td>
                        <td style="text-align: right;">${est}</td>
                        <td style="text-align: right;">${act}</td>
                        <td style="text-align: right;">${cumAct}</td>
                    `;
                }
                tbody.appendChild(tr);
            });

            // Add Total Row
            const trTotal = document.createElement('tr');
            trTotal.style.fontWeight = 'bold';
            trTotal.style.background = 'rgba(255, 255, 255, 0.1)';
            if (currentType === 'financial') {
                trTotal.innerHTML = `
                    <td>එකතුව (Total)</td>
                    <td style="text-align: right;">${totalEst}</td>
                    <td style="text-align: right;">${totalAct}</td>
                    <td style="text-align: right;">${totalAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td style="text-align: right;">${totalCumAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                `;
            } else {
                trTotal.innerHTML = `
                    <td>එකතුව (Total)</td>
                    <td style="text-align: right;">${totalEst}</td>
                    <td style="text-align: right;">${totalAct}</td>
                    <td style="text-align: right;">${totalCumAct}</td>
                `;
            }
            tbody.appendChild(trTotal);
        }

        // Event Listeners
        document.getElementById('ag_office').addEventListener('change', loadSummary);
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
        loadOffices();
    </script>
    <?php include 'chatbot.php'; ?>
</body>
</html>
