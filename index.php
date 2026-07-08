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
    <title>Social Services Monthly Progress - Wayamba</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <nav class="navbar">
            <div class="navbar-user">
                Logged in as: <span style="color: #3b82f6; text-transform: capitalize;"><?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($user_role); ?>)</span>
                | <a href="logout.php">Logout</a>
            </div>
            <div class="navbar-menu">
                <?php if ($user_role === 'superadmin'): ?>
                <a href="users.php" style="background: #f59e0b; border-color: #d97706;">පරිශීලක කළමනාකරණය (Users)</a>
                <button id="clearDataBtn" style="background: #ef4444; border-color: #dc2626;">දත්ත මකා දමන්න (Clear Data)</button>
                <?php endif; ?>
                <?php if ($user_role === 'superadmin' || $user_role === 'admin'): ?>
                <a href="alloc_summary.php" style="background: #10b981; border-color: #059669;">ප්‍රතිපාදන සාරාංශය (Alloc Summary)</a>
                <?php endif; ?>
                <a href="ag_summary.php" style="background: #8b5cf6; border-color: #7c3aed;">ප්‍රා. ලේකම් සාරාංශය (AG Summary)</a>
                <a href="summary.php" style="background: #3b82f6; border-color: #2563eb;">කාණ්ඩ සාරාංශය (Category Summary)</a>
            </div>
        </nav>
        <header>
            <h1>සමාජ සේවා දෙපාර්තමේන්තුවේ මාසික ප්‍රගතිය</h1>
            <h2>වයඹ පළාත - ප්‍රාදේශීය ලේකම් කාර්යාල තොරතුරු පද්ධතිය</h2>
        </header>

        <div class="glass-panel">
            <div class="controls">
                <div class="control-group">
                    <label for="ag_office">ප්‍රාදේශීය ලේකම් කාර්යාලය</label>
                    <select id="ag_office"></select>
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
        </div>

        <div class="glass-panel">
            <div class="tabs">
                <button class="tab-btn active" data-type="financial">මූල්‍යාධාර තොරතුරු</button>
                <button class="tab-btn" data-type="equipment">ආබාධිත උපකරණ / වෙනත්</button>
            </div>

            <input type="hidden" id="allocated_amount" value="0">
            <input type="hidden" id="remaining_balance" value="0">
            <input type="hidden" id="current_expenditure" value="0">
            <div id="chartContainer" style="margin-top:20px; display:none;">
                <canvas id="officeChart" height="200"></canvas>
            </div>

            <div class="table-container">
                <table id="dataTable">
                    <thead id="tableHead">
                        <tr>
                            <th>කාණ්ඩය</th>
                            <th>ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                            <th>ප්‍රතිලාභීන්</th>
                            <th>මාසික වියදම (රු.)</th>
                            <th>සමුච්චිත මුදල (රු.)</th>
                            <?php if ($user_role === 'superadmin'): ?>
                            <th>ක්‍රියාමාර්ග (Action)</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Rows injected by JS -->
                    </tbody>
                </table>
            </div>

            <div class="button-group">
                <button class="save-btn btn-save" id="saveBtn">දත්ත සුරකින්න (Save Data)</button>
                <?php if ($user_role === 'superadmin'): ?>
                <button class="save-btn btn-clear" id="clearSelectedBtn" style="background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.4);">මෙම දත්ත මකන්න (Clear Data)</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="toast">Data saved successfully!</div>

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

        let currentType = 'financial';
        const userRole = '<?php echo $user_role; ?>';
        
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
                loadData();
            } catch (e) {
                console.error('Failed to load offices', e);
            }
        }

        async function loadData() {
            const office = document.getElementById('ag_office').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            if (!office) return;

            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) {
                tableContainer.style.minHeight = tableContainer.offsetHeight + 'px';
            }

            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>';

            try {
                const res = await fetch(`api.php?action=get_records&ag_office_id=${office}&year=${year}&month=${month}&type=${currentType}`);
                const data = await res.json();
                document.getElementById('allocated_amount').value = data.allocated_amount || 0;
                 renderTable(data.records, data.cumulative);
                calculateTotals();
                loadSummaryChart();
            } catch (e) {
                console.error(e);
                showToast('Error loading data', true);
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
                        <th>ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                        <th>ප්‍රතිලාභීන්</th>
                        <th>මාසික වියදම (රු.)</th>
                        <th>සමුච්චිත මුදල (රු.)</th>
                        ${userRole === 'superadmin' ? '<th>ක්‍රියාමාර්ග (Action)</th>' : ''}
                    </tr>
                `;
            } else {
                thead.innerHTML = `
                    <tr>
                        <th>කාණ්ඩය</th>
                        <th>ඇස්තමේන්තු ප්‍රතිලාභීන්</th>
                        <th>මාසය තුළ ප්‍රතිලාභීන් ගණන</th>
                        <th>සමුච්චිත ප්‍රතිලාභීන්</th>
                        ${userRole === 'superadmin' ? '<th>ක්‍රියාමාර්ග (Action)</th>' : ''}
                    </tr>
                `;
            }

            categories[currentType].forEach(cat => {
                const rec = records[cat] || { estimated_beneficiaries: 0, actual_beneficiaries: 0, amount: 0, allocated_amount: 0 };
                let cumAmt = 0;
                let cumAct = 0;
                
                if (cumulative[cat] !== undefined) {
                    if (typeof cumulative[cat] === 'object') {
                        cumAmt = cumulative[cat].amount || 0;
                        cumAct = cumulative[cat].act || 0;
                    } else {
                        cumAmt = cumulative[cat] || 0;
                    }
                }
                const tr = document.createElement('tr');
                const estDisabled = (userRole !== 'superadmin') ? 'disabled readonly style="background: rgba(255,255,255,0.02); cursor: not-allowed;"' : '';
                
                let actionCell = '';
                if (userRole === 'superadmin') {
                    actionCell = `<td style="text-align: center;"><button class="row-clear-btn" data-cat="${cat}" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.4)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.2)'">මකන්න</button></td>`;
                }

                if (currentType === 'financial') {
                    const monthlyVal = parseFloat(rec.amount) || 0;
                    const cumVal = parseFloat(cumAmt) || 0;
                    const prevCum = cumVal - monthlyVal;
                    tr.innerHTML = `
                        <td>${cat}</td>
                        <td class="input-cell"><input type="number" class="est" data-cat="${cat}" value="${rec.estimated_beneficiaries}" ${estDisabled}></td>
                        <td class="input-cell"><input type="number" class="act" data-cat="${cat}" value="${rec.actual_beneficiaries}"></td>
                        <td class="input-cell">
                            <input type="number" step="0.01" class="amt" data-cat="${cat}" value="${monthlyVal}" data-prev-cum="${prevCum}">
                        </td>
                        <td class="input-cell"><input type="number" step="0.01" class="cum-amt" readonly value="${cumVal.toFixed(2)}"></td>
                        ${actionCell}
                    `;
                } else {
                    tr.innerHTML = `
                        <td>${cat}</td>
                        <td class="input-cell"><input type="number" class="est" data-cat="${cat}" value="${rec.estimated_beneficiaries}" ${estDisabled}></td>
                        <td class="input-cell"><input type="number" class="act" data-cat="${cat}" value="${rec.actual_beneficiaries}"></td>
                        <td class="input-cell"><input type="number" readonly value="${cumAct}"></td>
                        <input type="hidden" class="amt" value="0">
                        ${actionCell}
                    `;
                }
                tbody.appendChild(tr);
            });

            // Add event listeners for dynamic recalculation
            document.querySelectorAll('.amt').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const amtInput = row.querySelector('.amt');
                    if (amtInput) {
                        const monthlyVal = parseFloat(amtInput.value) || 0;
                        const prevCum = parseFloat(amtInput.dataset.prevCum) || 0;
                        const cumVal = prevCum + monthlyVal;
                        
                        const cumInput = row.querySelector('.cum-amt');
                        if (cumInput) {
                            cumInput.value = cumVal.toFixed(2);
                        }
                    }
                    calculateTotals();
                });
            });

            // Add click listener for individual row clear buttons
            document.querySelectorAll('.row-clear-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const catName = this.dataset.cat;
                    const officeSelect = document.getElementById('ag_office');
                    const officeName = officeSelect.options[officeSelect.selectedIndex].text;
                    const year = document.getElementById('year').value;
                    const monthSelect = document.getElementById('month');
                    const monthName = monthSelect.options[monthSelect.selectedIndex].text;

                    if (confirm(`[${officeName}] කාර්යාලයේ [${year} ${monthName}] මාසයේ [${catName}] දත්ත පමණක් මකා දැමීමට ඔබට විශ්වාසද? (Are you sure you want to clear data for ${catName}?)`)) {
                        try {
                            const res = await fetch('api.php?action=clear_category_records', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    ag_office_id: officeSelect.value,
                                    year: year,
                                    month: monthSelect.value,
                                    type: currentType,
                                    category: catName
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                showToast('තෝරාගත් කාණ්ඩයේ දත්ත සාර්ථකව මකා දමන ලදී (Category data cleared successfully)');
                                loadData(); // reload form
                            } else {
                                showToast('Error: ' + data.error, true);
                            }
                        } catch (e) {
                            console.error(e);
                            showToast('Network error', true);
                        }
                    }
                });
            });
        }

        function calculateTotals() {
            let totalExpenditure = 0;
            document.querySelectorAll('.amt').forEach(input => {
                totalExpenditure += parseFloat(input.value) || 0;
            });
            const expendInput = document.getElementById('current_expenditure');
            if (expendInput) {
                expendInput.value = totalExpenditure.toFixed(2);
            }
        }

        // Fetch and render chart of office performance
        async function loadSummaryChart() {
            const office = document.getElementById('ag_office').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            if (!office) return;
            try {
                const res = await fetch(`api.php?action=get_ag_summary&year=${year}&month=${month}&type=${currentType}`);
                const data = await res.json();
                if (!Array.isArray(data) || data.length === 0) {
                    document.getElementById('chartContainer').style.display = 'none';
                    return;
                }
                const labels = data.map(r => r.office_name);
                const amounts = data.map(r => parseFloat(r.amt) || 0);
                const ctx = document.getElementById('officeChart').getContext('2d');
                if (window.officeChartInstance) {
                    window.officeChartInstance.destroy();
                }
                window.officeChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: currentType === 'financial' ? 'මූල්‍යාධාර මුදල (රු)' : 'ආබාධිත උපකරණ මුදල (රු)',
                            data: amounts,
                            backgroundColor: 'rgba(34, 197, 94, 0.6)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true },
                            title: { display: true, text: 'ඉල්ලීම් අනුව AG කාර්යාලය (රු)' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
                document.getElementById('chartContainer').style.display = 'block';
            } catch (e) {
                console.error('Chart load error', e);
                document.getElementById('chartContainer').style.display = 'none';
            }
        }

        async function saveData() {
            const office = document.getElementById('ag_office').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            const btn = document.getElementById('saveBtn');

            const payload = {
                ag_office_id: office,
                year: year,
                month: month,
                type: currentType,
                allocated_amount: currentType === 'financial' ? (parseFloat(document.getElementById('allocated_amount').value) || 0) : 0,
                records: []
            };

            document.querySelectorAll('#tableBody tr').forEach(tr => {
                const estInput = tr.querySelector('.est');
                const actInput = tr.querySelector('.act');
                const amtInput = tr.querySelector('.amt');
                
                let cat = '';
                let est = 0;
                let act = 0;
                let amt = 0;
                let alloc_cat = 0;
                
                if (estInput) {
                    cat = estInput.dataset.cat;
                    est = parseInt(estInput.value) || 0;
                    act = parseInt(actInput.value) || 0;
                    amt = parseFloat(amtInput.value) || 0;
                }

                if (cat) {
                    payload.records.push({ category: cat, estimated: est, actual: act, amount: amt, allocated_amount: alloc_cat });
                }
            });

            btn.textContent = 'Saving...';
            btn.style.opacity = '0.7';

            try {
                const res = await fetch('api.php?action=save_records', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.success) {
                    showToast('දත්ත සාර්ථකව යාවත්කාලීන කරන ලදී (Saved Successfully)');
                    loadData(); // Reload to update cumulative amounts
                } else {
                    showToast('Error: ' + result.error, true);
                }
            } catch (e) {
                console.error(e);
                showToast('Network error', true);
            } finally {
                btn.textContent = 'දත්ත සුරකින්න (Save Data)';
                btn.style.opacity = '1';
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
        document.getElementById('ag_office').addEventListener('change', loadData);
        document.getElementById('year').addEventListener('change', loadData);
        document.getElementById('month').addEventListener('change', loadData);
        // Total expenditure is automatically recalculated when table values change
        
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                currentType = e.target.dataset.type;
                loadData();
            });
        });

        document.getElementById('saveBtn').addEventListener('click', saveData);

        // Enter key එක press කළ විටද data save වීමට (Trigger save on Enter key)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent default enter key behavior
                saveData();
            }
        });

        // Set current month/year
        const d = new Date();
        document.getElementById('year').value = d.getFullYear();
        document.getElementById('month').value = d.getMonth() + 1;

        <?php if ($user_role === 'superadmin'): ?>
        document.getElementById('clearDataBtn').addEventListener('click', async () => {
            if (confirm('සියලුම දත්ත මකා දැමීමට ඔබට විශ්වාසද? මෙම ක්‍රියාව ආපසු හැරවිය නොහැක. (Are you sure you want to clear all data? This action cannot be undone.)')) {
                try {
                    const res = await fetch('clear_data.php');
                    const data = await res.json();
                    if (data.success) {
                        showToast('සියලුම දත්ත සාර්ථකව මකා දමන ලදී (All data cleared successfully)');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast('Error: ' + data.error, true);
                    }
                } catch (e) {
                    console.error(e);
                    showToast('Network error', true);
                }
            }
        });

        document.getElementById('clearSelectedBtn').addEventListener('click', async () => {
            const officeSelect = document.getElementById('ag_office');
            const officeName = officeSelect.options[officeSelect.selectedIndex].text;
            const year = document.getElementById('year').value;
            const monthSelect = document.getElementById('month');
            const monthName = monthSelect.options[monthSelect.selectedIndex].text;
            const typeLabel = currentType === 'financial' ? 'මූල්‍යාධාර තොරතුරු' : 'ආබාධිත උපකරණ / වෙනත්';

            if (confirm(`තෝරාගත් [${officeName}] කාර්යාලයේ [${year} ${monthName}] මාසයේ [${typeLabel}] දත්ත පමණක් මකා දැමීමට ඔබට විශ්වාසද? (Are you sure you want to clear only the selected data?)`)) {
                try {
                    const res = await fetch('api.php?action=clear_selected_records', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            ag_office_id: officeSelect.value,
                            year: year,
                            month: monthSelect.value,
                            type: currentType
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('තෝරාගත් දත්ත සාර්ථකව මකා දමන ලදී (Selected data cleared successfully)');
                        loadData(); // reload form
                    } else {
                        showToast('Error: ' + data.error, true);
                    }
                } catch (e) {
                    console.error(e);
                    showToast('Network error', true);
                }
            }
        });
        <?php endif; ?>

        // Init
        loadOffices();
    </script>
    <?php include 'chatbot.php'; ?>
</body>
</html>
