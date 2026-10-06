<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookMarket Research — Painel de Administração</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="background-color: #f1f5f9;">

<div id="loginModal" class="card" style="max-width: 400px; margin: 80px auto; padding: 32px;">
    <h2 style="margin-bottom: 20px; text-align: center; color: var(--primary);">Acesso Restrito</h2>
    <form id="loginForm" onsubmit="handleLogin(event)">
        <div class="form-group">
            <label>Utilizador</label>
            <input type="text" id="login_user" required placeholder="admin">
        </div>
        <div class="form-group">
            <label>Palavra-passe</label>
            <input type="password" id="login_pass" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar no Painel</button>
    </form>
</div>

<div id="adminApp" class="admin-layout" style="display: none;">
    
    <!-- Sidebar Nav -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i data-lucide="bar-chart-3" style="vertical-align: middle;"></i> BookMarket Admin
        </div>
        <ul class="nav-menu">
            <li><a class="nav-link active" onclick="switchTab('overview')"><i data-lucide="layout-dashboard"></i> Visão Geral</a></li>
            <li><a class="nav-link" onclick="switchTab('interviews')"><i data-lucide="file-text"></i> Entrevistas</a></li>
            <li><a class="nav-link" onclick="switchTab('charts')"><i data-lucide="pie-chart"></i> Gráficos</a></li>
            <li><a class="nav-link" onclick="switchTab('leads')"><i data-lucide="users"></i> Leads</a></li>
            <li><a class="nav-link" onclick="switchTab('conclusion')"><i data-lucide="check-circle-2"></i> Conclusão</a></li>
            <li><a class="nav-link" onclick="switchTab('backups')"><i data-lucide="database"></i> Backups</a></li>
            <li><a class="nav-link" onclick="switchTab('settings')"><i data-lucide="settings"></i> Configurações</a></li>
            <li style="margin-top: auto;"><a class="nav-link" onclick="handleLogout()"><i data-lucide="log-out"></i> Sair</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-content">
        
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 id="pageTitle" style="font-size: 1.5rem;">Visão Geral</h2>
            <div style="font-size: 0.85rem; color: var(--text-muted); text-align: right;">
                <span id="syncTimestamp">Sincronizado há pouco</span>
            </div>
        </header>

        <!-- SEÇÃO: VISÃO GERAL -->
        <section id="tab-overview">
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="title">Total de Entrevistas</div>
                    <div class="value" id="metric-total">0</div>
                </div>
                <div class="metric-card">
                    <div class="title">Meta de Entrevistas</div>
                    <div class="value" id="metric-goal">0 / 10</div>
                </div>
                <div class="metric-card">
                    <div class="title">Interessados Fortes</div>
                    <div class="value" id="metric-interested">0%</div>
                </div>
                <div class="metric-card">
                    <div class="title">Dispostos a Pagar</div>
                    <div class="value" id="metric-paying">0%</div>
                </div>
            </div>

            <div class="metrics-grid">
                <div class="metric-card" style="border-left: 4px solid var(--primary);">
                    <div class="title">Market Validation Score</div>
                    <div class="value" id="metric-score" style="color: var(--primary);">0 / 100</div>
                    <p id="scoreClassification" style="font-size: 0.8rem; margin-top: 4px; color: var(--text-muted);"></p>
                </div>
            </div>

            <!-- Funil de Validação -->
            <div class="card" style="margin-bottom: 24px;">
                <h3 style="margin-bottom: 16px;">Funil de Validação de Mercado</h3>
                <div id="funnelContainer" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Renderizado dinamicamente via JavaScript -->
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 12px;">Principais Descobertas Automáticas</h3>
                <ul id="insightsList" style="line-height: 1.8; color: var(--text-main); font-size: 0.95rem;">
                    <li>A carregar descobertas...</li>
                </ul>
            </div>

        </section>

        <!-- SEÇÃO: ENTREVISTAS -->
        <section id="tab-interviews" style="display: none;">
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <input type="text" id="searchInput" placeholder="Pesquisar por nome, telefone ou cidade..." onkeyup="filterTable()" style="max-width: 300px;">
                <button class="btn btn-secondary" onclick="exportCSV()"><i data-lucide="download"></i> Exportar CSV</button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <th>WhatsApp</th>
                            <th>Interesse</th>
                            <th>Pagaria</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="interviewsTableBody">
                        <!-- Renderizado via JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SEÇÃO: GRÁFICOS -->
        <section id="tab-charts" style="display: none;">
            <div class="charts-grid">
                <div class="chart-card">
                    <h4>Distribuição por Cidade</h4>
                    <canvas id="chartCidade"></canvas>
                </div>
                <div class="chart-card">
                    <h4>Uso do WhatsApp para Vendas</h4>
                    <canvas id="chartWhatsapp"></canvas>
                </div>
                <div class="chart-card">
                    <h4>Interesse na Plataforma</h4>
                    <canvas id="chartInteresse"></canvas>
                </div>
                <div class="chart-card">
                    <h4>Disposição para Pagar</h4>
                    <canvas id="chartPagamento"></canvas>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: LEADS -->
        <section id="tab-leads" style="display: none;">
            <h3 style="margin-bottom: 16px;">🔥 Leads Quentes (Prontos para Piloto)</h3>
            <div id="leadsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                <!-- Renderizado via Javascript -->
            </div>
        </section>

        <!-- SEÇÃO: CONCLUSÃO DA PESQUISA -->
        <section id="tab-conclusion" style="display: none;">
            <div class="card">
                <h3 style="margin-bottom: 16px; color: var(--primary);">Recomendação Estratégica Automática</h3>
                <div id="recommendationBox" style="padding: 20px; border-radius: 8px; background: #eff6ff; font-weight: bold; font-size: 1.2rem; text-align: center; margin-bottom: 20px;">
                    A calcular recomendação...
                </div>
                <div id="recommendationDetails" style="line-height: 1.8; color: var(--text-main);">
                    <!-- Detalhes do cálculo -->
                </div>
            </div>
        </section>

        <!-- SEÇÃO: BACKUPS -->
        <section id="tab-backups" style="display: none;">
            <div class="card">
                <h3>Exportar e Importar Ficheiros JSON</h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Faça backup completo das entrevistas para salvar segurança offline.</p>
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-primary" onclick="downloadBackup()"><i data-lucide="download"></i> Baixar JSON Backup</button>
                    <input type="file" id="importFile" style="display: none;" onchange="importBackup(event)">
                    <button class="btn btn-secondary" onclick="document.getElementById('importFile').click()"><i data-lucide="upload"></i> Importar JSON Backup</button>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: CONFIGURAÇÕES -->
        <section id="tab-settings" style="display: none;">
            <div class="card" style="max-width: 500px;">
                <h3>Configurações do Sistema</h3>
                <form onsubmit="saveSettings(event)" style="margin-top: 16px;">
                    <div class="form-group">
                        <label>Nome da Pesquisa</label>
                        <input type="text" id="set_survey_name">
                    </div>
                    <div class="form-group">
                        <label>Meta de Entrevistas</label>
                        <input type="number" id="set_target_goal">
                    </div>
                    <div class="form-group">
                        <label>Nova Palavra-passe Admin (deixe em branco se não quiser alterar)</label>
                        <input type="password" id="set_new_password">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Alterações</button>
                </form>
            </div>
        </section>

    </main>
</div>

<!-- Modal Detalhes Entrevista -->
<div id="detailModal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 650px; width: 90%; max-height: 85vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 id="modalTitle">Detalhes da Entrevista</h3>
            <button onclick="closeModal()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="modalBody" style="font-size: 0.9rem; line-height: 1.6;">
            <!-- Conteúdo injetado via JS -->
        </div>
    </div>
</div>

<script src="assets/dashboard.js"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>