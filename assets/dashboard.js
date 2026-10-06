/**
 * BookMarket Research — Dashboard Logic Script
 * Gestão do Painel, Polling em Tempo Real (5s), Gráficos Chart.js e Algoritmo Score
 */

let interviewsData = [];
let chartInstances = {};
let pollingInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
});

async function checkAuth() {
    try {
        const res = await fetch('api.php?action=checkAuth');
        const data = await res.json();
        if (data.success) {
            document.getElementById('loginModal').style.display = 'none';
            document.getElementById('adminApp').style.display = 'flex';
            initDashboard();
        } else {
            document.getElementById('loginModal').style.display = 'block';
            document.getElementById('adminApp').style.display = 'none';
        }
    } catch (e) {
        console.error("Erro ao verificar sessão", e);
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const username = document.getElementById('login_user').value;
    const password = document.getElementById('login_pass').value;

    const res = await fetch('api.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    const data = await res.json();

    if (data.success) {
        checkAuth();
    } else {
        alert(data.error || 'Credenciais inválidas');
    }
}

async function handleLogout() {
    await fetch('api.php?action=logout');
    location.reload();
}

function initDashboard() {
    loadData();
    // PollingNear-real-time a cada 5 segundos
    pollingInterval = setInterval(loadData, 5000);
}

async function loadData() {
    try {
        const res = await fetch('api.php?action=getStats');
        const data = await res.json();

        if (data.success) {
            interviewsData = data.interviews;
            updateMetrics(data);
            renderFunnel();
            generateInsights();
            renderTable();
            renderCharts();
            renderLeads();
            calculateConclusion();
            document.getElementById('syncTimestamp').textContent = `Sincronizado: ${new Date().toLocaleTimeString()}`;
        }
    } catch (e) {
        document.getElementById('syncTimestamp').textContent = '🔴 Sem ligação ao servidor';
    }
}

function updateMetrics(data) {
    const total = data.total;
    const goal = data.target_goal;

    document.getElementById('metric-total').textContent = total;
    document.getElementById('metric-goal').textContent = `${total} / ${goal}`;

    if (total === 0) return;

    let interestedCount = 0;
    let payingCount = 0;

    interviewsData.forEach(item => {
        if (['Muito útil', 'Útil'].includes(item.validacao?.utilidadePlataforma)) interestedCount++;
        if (['Sim', 'Talvez'].includes(item.validacao?.dispostoPagar)) payingCount++;
    });

    const interestedPct = Math.round((interestedCount / total) * 100);
    const payingPct = Math.round((payingCount / total) * 100);

    document.getElementById('metric-interested').textContent = `${interestedPct}%`;
    document.getElementById('metric-paying').textContent = `${payingPct}%`;

    // Market Validation Score Algorithm
    const score = calculateScore(total, interestedPct, payingPct);
    document.getElementById('metric-score').textContent = `${score} / 100`;

    let classification = "0–39 = Sinal fraco";
    if (score >= 70) classification = "70–100 = Forte sinal de oportunidade!";
    else if (score >= 40) classification = "40–69 = Sinal moderado. Necessita validação.";

    document.getElementById('scoreClassification').textContent = classification;
}

function calculateScore(total, interestedPct, payingPct) {
    if (total === 0) return 0;
    let score = (interestedPct * 0.4) + (payingPct * 0.6);
    return Math.min(100, Math.round(score));
}

function renderFunnel() {
    const total = interviewsData.length;
    if (total === 0) return;

    const stages = [
        { name: 'Total Entrevistados', count: total },
        { name: 'Usam WhatsApp para Vendas', count: interviewsData.filter(i => (i.marketing?.whatsapp || '').includes('Frequentemente') || (i.marketing?.whatsapp || '').includes('Quase todos')).length },
        { name: 'Acham Plataforma Útil', count: interviewsData.filter(i => ['Muito útil', 'Útil'].includes(i.validacao?.utilidadePlataforma)).length },
        { name: 'Querem Experimentar', count: interviewsData.filter(i => i.validacao?.experimentarHoje === 'Sim').length },
        { name: 'Dispostos a Pagar', count: interviewsData.filter(i => ['Sim', 'Talvez'].includes(i.validacao?.dispostoPagar)).length }
    ];

    const container = document.getElementById('funnelContainer');
    container.innerHTML = stages.map(st => {
        const pct = Math.round((st.count / total) * 100);
        return `
            <div style="background: var(--bg-main); padding: 10px 14px; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.85rem;">
                    <span>${st.name}</span>
                    <span>${st.count} (${pct}%)</span>
                </div>
                <div style="height: 6px; background: #e2e8f0; border-radius: 3px; margin-top: 6px; overflow: hidden;">
                    <div style="width: ${pct}%; background: var(--primary); height: 100%;"></div>
                </div>
            </div>
        `;
    }).join('');
}

function generateInsights() {
    const list = document.getElementById('insightsList');
    const total = interviewsData.length;

    if (total === 0) {
        list.innerHTML = '<li>Ainda não existem entrevistas gravadas.</li>';
        return;
    }

    const noSite = interviewsData.filter(i => i.marketing?.temSite === 'Não').length;
    const wa = interviewsData.filter(i => (i.marketing?.whatsapp || '').includes('Frequentemente') || (i.marketing?.whatsapp || '').includes('Quase todos')).length;

    list.innerHTML = `
        <li><strong>${wa} de ${total} (${Math.round((wa/total)*100)}%)</strong> utilizam WhatsApp frequentemente para receber pedidos.</li>
        <li><strong>${noSite} de ${total} (${Math.round((noSite/total)*100)}%)</strong> não possuem site ou catálogo online.</li>
    `;
}

function renderTable() {
    const tbody = document.getElementById('interviewsTableBody');
    if (interviewsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Nenhuma entrevista registada.</td></tr>';
        return;
    }

    tbody.innerHTML = interviewsData.map(item => `
        <tr>
            <td><strong>${item.id}</strong></td>
            <td>${escapeHtml(item.entrevistado?.nome || '')}</td>
            <td>${escapeHtml(item.entrevistado?.telefone || '-')}</td>
            <td>${escapeHtml(item.entrevistado?.cidade || '')}</td>
            <td>${escapeHtml(item.marketing?.whatsapp || '-')}</td>
            <td>${escapeHtml(item.validacao?.utilidadePlataforma || '-')}</td>
            <td>${escapeHtml(item.validacao?.dispostoPagar || '-')}</td>
            <td>
                <button class="btn btn-secondary" onclick="viewDetail('${item.id}')" style="padding: 4px 8px; font-size: 0.8rem;">Ver</button>
            </td>
        </tr>
    `).join('');
}

function viewDetail(id) {
    const item = interviewsData.find(i => i.id === id);
    if (!item) return;

    document.getElementById('modalTitle').textContent = `Entrevista: ${item.id}`;
    document.getElementById('modalBody').innerHTML = `
        <p><strong>Nome:</strong> ${escapeHtml(item.entrevistado?.nome)}</p>
        <p><strong>Telefone:</strong> ${escapeHtml(item.entrevistado?.telefone)}</p>
        <p><strong>Cidade:</strong> ${escapeHtml(item.entrevistado?.cidade)}</p>
        <hr style="margin: 10px 0;">
        <p><strong>Maior Dificuldade:</strong> ${escapeHtml(item.problemas?.maiorDificuldade || 'N/A')}</p>
        <p><strong>Funcionalidades Desejadas:</strong> ${escapeHtml(item.validacao?.funcionalidadesDesejadas || 'N/A')}</p>
        <p><strong>Observações:</strong> ${escapeHtml(item.observacoes || 'Sem notas')}</p>
    `;
    document.getElementById('detailModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

function renderCharts() {
    if (interviewsData.length === 0) return;

    // Cidade
    const cities = {};
    interviewsData.forEach(i => {
        const c = i.entrevistado?.cidade || 'Outra';
        cities[c] = (cities[c] || 0) + 1;
    });

    createOrUpdateChart('chartCidade', 'pie', Object.keys(cities), Object.values(cities), 'Cidades');
}

function createOrUpdateChart(canvasId, type, labels, data, label) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    chartInstances[canvasId] = new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

function renderLeads() {
    const container = document.getElementById('leadsContainer');
    const leads = interviewsData.filter(i => 
        i.validacao?.experimentarHoje === 'Sim' && 
        ['Sim', 'Talvez'].includes(i.validacao?.dispostoPagar)
    );

    if (leads.length === 0) {
        container.innerHTML = '<p style="color: var(--text-muted);">Nenhum lead qualificado encontrado até ao momento.</p>';
        return;
    }

    container.innerHTML = leads.map(l => `
        <div class="card" style="border-left: 4px solid var(--success); padding: 16px;">
            <div style="display: flex; justify-content: space-between;">
                <strong>${escapeHtml(l.entrevistado?.nome)} (${escapeHtml(l.entrevistado?.cidade)})</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">${escapeHtml(l.entrevistado?.telefone || 'Sem contacto')}</span>
            </div>
            <p style="font-size: 0.85rem; margin-top: 6px;">Disposto a pagar: <strong>${escapeHtml(l.validacao?.valorMensal)}</strong></p>
        </div>
    `).join('');
}

function calculateConclusion() {
    const total = interviewsData.length;
    const box = document.getElementById('recommendationBox');

    if (total < 5) {
        box.textContent = "CONTINUAR PESQUISA (Amostra insuficiente)";
        box.style.background = "#fef3c7";
        return;
    }

    const interestedPct = (interviewsData.filter(i => ['Muito útil', 'Útil'].includes(i.validacao?.utilidadePlataforma)).length / total) * 100;
    const payingPct = (interviewsData.filter(i => ['Sim', 'Talvez'].includes(i.validacao?.dispostoPagar)).length / total) * 100;

    if (interestedPct >= 70 && payingPct >= 40) {
        box.textContent = "🚀 RECOMENDAÇÃO: CRIAR MVP / INICIAR PILOTO";
        box.style.background = "#d1fae5";
    } else {
        box.textContent = "⚠️ RECOMENDAÇÃO: REAVALIAR OU CONTINUAR PESQUISA";
        box.style.background = "#fee2e2";
    }
}

function switchTab(tabId) {
    document.querySelectorAll('section[id^="tab-"]').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.nav-link').forEach(a => a.classList.remove('active'));

    document.getElementById(`tab-${tabId}`).style.display = 'block';
    event.currentTarget.classList.add('active');
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function exportCSV() {
    window.open('api.php?action=backup', '_blank');
}

function downloadBackup() {
    window.open('api.php?action=backup', '_blank');
}

async function saveSettings(e) {
    e.preventDefault();
    const payload = {
        survey_name: document.getElementById('set_survey_name').value,
        target_goal: document.getElementById('set_target_goal').value,
        new_password: document.getElementById('set_new_password').value
    };

    const res = await fetch('api.php?action=saveSettings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    alert(data.message || data.error);
}