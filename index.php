<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pesquisa de Mercado — Venda de Livros em Moçambique</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="app-container">
    
    <!-- Topbar / Header -->
    <header class="app-header">
        <div class="brand">
            <div class="brand-icon"><i data-lucide="book-open"></i></div>
            <div class="brand-title">
                <h1>BookMarket Research</h1>
                <p>Estudo do Mercado Livreiro Moçambicano</p>
            </div>
        </div>
        <div class="connection-status" id="connectionStatus">
            <span class="status-dot online" id="statusDot"></span>
            <span id="statusText">Online</span>
        </div>
    </header>

    <!-- Rascunho / Draft Banner -->
    <div id="draftAlert" class="card" style="display: none; background: #fffbebfb; border-color: #fcd34d; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Rascunho Encontrado</strong>
                <p style="font-size: 0.85rem; color: #92400e;">Existe uma entrevista pendente neste dispositivo.</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary" onclick="discardDraft()" style="padding: 6px 12px; font-size: 0.8rem;">Eliminar</button>
                <button class="btn btn-primary" onclick="resumeDraft()" style="padding: 6px 12px; font-size: 0.8rem;">Continuar</button>
            </div>
        </div>
    </div>

    <!-- Tela Inicial de Boas-Vindas -->
    <div id="welcomeScreen" class="card">
        <h2 style="font-size: 1.4rem; margin-bottom: 12px; color: var(--primary);">Pesquisa de Mercado — Venda de Livros em Moçambique</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">
            Estamos a estudar como os vendedores de livros trabalham actualmente e quais são as principais dificuldades encontradas no processo de venda e distribuição.
        </p>
        <div style="background: var(--primary-light); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <p style="font-size: 0.85rem; color: #1e40af;">
                <i data-lucide="info" style="vertical-align: middle; width: 16px;"></i> 
                Duração estimada: 5 minutos. Funciona em modo offline se a ligação falhar na rua.
            </p>
        </div>
        <button class="btn btn-primary" onclick="startSurvey()" style="width: 100%;">
            Iniciar Entrevista <i data-lucide="arrow-right"></i>
        </button>
        <a href="admin.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px; text-decoration: none; text-align: center;">
            Área Administrativa
        </a>
    </div>

    <!-- Form Wizard Card -->
    <div id="surveyWizard" class="card" style="display: none;">
        
        <div class="progress-container">
            <div class="progress-info">
                <span id="stepCounter">Pergunta 1 de 28</span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progressBar"></div>
            </div>
        </div>

        <form id="surveyForm" onsubmit="return false;">
            
            <!-- ETAPA 1: Identificação -->
            <div class="question-step active" data-step="1">
                <h3 class="question-title">Dados do Entrevistado</h3>
                <p class="question-subtitle">Informações iniciais do vendedor</p>

                <div class="form-group">
                    <label>Nome do Vendedor / Estabelecimento *</label>
                    <input type="text" name="nome" id="input_nome" placeholder="Ex: Livraria Nova / João Manuel" required>
                </div>

                <div class="form-group">
                    <label>Número de Telefone (WhatsApp)</label>
                    <input type="tel" name="telefone" id="input_telefone" placeholder="84 / 85 / 82 / 86 / 87 ...">
                </div>

                <div class="form-group">
                    <label>Cidade *</label>
                    <select name="cidade" id="input_cidade" required>
                        <option value="">Selecione a cidade...</option>
                        <option value="Pemba">Pemba</option>
                        <option value="Nampula">Nampula</option>
                        <option value="Nacala">Nacala</option>
                        <option value="Maputo">Maputo</option>
                        <option value="Matola">Matola</option>
                        <option value="Beira">Beira</option>
                        <option value="Chimoio">Chimoio</option>
                        <option value="Quelimane">Quelimane</option>
                        <option value="Tete">Tete</option>
                        <option value="Xai-Xai">Xai-Xai</option>
                        <option value="Inhambane">Inhambane</option>
                        <option value="Vilankulo">Vilankulo</option>
                        <option value="Outra">Outra</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bairro / Local de Venda</label>
                    <input type="text" name="local" placeholder="Ex: Mercado Central / Baixa">
                </div>

                <div class="form-group">
                    <label>Nome do Entrevistador</label>
                    <input type="text" name="entrevistador" value="Pesquisador">
                </div>
            </div>

            <!-- ETAPA 2: Consentimento -->
            <div class="question-step" data-step="2">
                <h3 class="question-title">Consentimento de Contacto</h3>
                <p class="question-subtitle">
                    Os dados recolhidos serão utilizados para fins desta pesquisa. O número de telefone só será utilizado para contacto relacionado com a solução caso autorize.
                </p>
                <div class="options-grid">
                    <label class="option-card">
                        <input type="radio" name="consentimentoContacto" value="Sim" checked>
                        <span>Sim, autorizo contacto futuro</span>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="consentimentoContacto" value="Não">
                        <span>Não autorizo contacto</span>
                    </label>
                </div>
            </div>

            <!-- ETAPA 3: Perfil do Vendedor (Q1) -->
            <div class="question-step" data-step="3">
                <h3 class="question-title">1. Como costuma vender os seus livros?</h3>
                <p class="question-subtitle">Pode selecionar múltiplas opções</p>
                <div class="options-grid">
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Loja/livraria"><span>Loja / Livraria</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Banca"><span>Banca</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Rua"><span>Vendedor de Rua</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Casa"><span>Em Casa</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Instagram"><span>Instagram</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Facebook"><span>Facebook</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="WhatsApp"><span>WhatsApp</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Marketplace"><span>Marketplace</span></label>
                    <label class="option-card"><input type="checkbox" name="tipoVenda[]" value="Outro"><span>Outro</span></label>
                </div>
            </div>

            <!-- ETAPA 4: Tempo de Atividade (Q2) -->
            <div class="question-step" data-step="4">
                <h3 class="question-title">2. Há quanto tempo vende livros?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="tempoVenda" value="Menos de 1 ano"><span>Menos de 1 ano</span></label>
                    <label class="option-card"><input type="radio" name="tempoVenda" value="1–3 anos"><span>1 – 3 anos</span></label>
                    <label class="option-card"><input type="radio" name="tempoVenda" value="3–5 anos"><span>3 – 5 anos</span></label>
                    <label class="option-card"><input type="radio" name="tempoVenda" value="Mais de 5 anos"><span>Mais de 5 anos</span></label>
                </div>
            </div>

            <!-- ETAPA 5: Volume de Stock (Q3) -->
            <div class="question-step" data-step="5">
                <h3 class="question-title">3. Aproximadamente quantos livros costuma ter disponíveis?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="Menos de 20"><span>Menos de 20</span></label>
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="20–50"><span>20 – 50</span></label>
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="51–100"><span>51 – 100</span></label>
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="101–500"><span>101 – 500</span></label>
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="Mais de 500"><span>Mais de 500</span></label>
                    <label class="option-card"><input type="radio" name="quantidadeLivros" value="Não sabe"><span>Não sabe</span></label>
                </div>
            </div>

            <!-- ETAPA 6: Categorias (Q4) -->
            <div class="question-step" data-step="6">
                <h3 class="question-title">4. Que tipos de livros vende principalmente?</h3>
                <p class="question-subtitle">Selecione todas as aplicáveis</p>
                <div class="options-grid">
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Literatura"><span>Literatura</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Desenvolvimento pessoal"><span>Desenvolvimento Pessoal</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Negócios"><span>Negócios / Finanças</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Religião"><span>Religião / Espiritualidade</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Académicos"><span>Académicos / Universitários</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Escolares"><span>Escolares</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Infantis"><span>Infantis</span></label>
                    <label class="option-card"><input type="checkbox" name="categorias[]" value="Livros usados"><span>Livros Usados</span></label>
                </div>
            </div>

            <!-- ETAPA 7: Aquisição de Clientes (Q5) -->
            <div class="question-step" data-step="7">
                <h3 class="question-title">5. Como os clientes normalmente descobrem os seus livros?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="checkbox" name="descobertaClientes[]" value="Passam pelo local"><span>Passam pelo local</span></label>
                    <label class="option-card"><input type="checkbox" name="descobertaClientes[]" value="Indicação de amigos"><span>Indicação de amigos</span></label>
                    <label class="option-card"><input type="checkbox" name="descobertaClientes[]" value="WhatsApp"><span>WhatsApp / Status</span></label>
                    <label class="option-card"><input type="checkbox" name="descobertaClientes[]" value="Instagram"><span>Instagram</span></label>
                    <label class="option-card"><input type="checkbox" name="descobertaClientes[]" value="Facebook"><span>Facebook</span></label>
                </div>
                <div class="form-group" style="margin-top: 12px;">
                    <label>Outras formas:</label>
                    <input type="text" name="descobertaOutros" placeholder="Descreva brevemente...">
                </div>
            </div>

            <!-- ETAPA 8: Frequência WhatsApp (Q6) -->
            <div class="question-step" data-step="8">
                <h3 class="question-title">6. Recebe pedidos através do WhatsApp?</h3>
                <div class="options-grid">
                    <label class="option-card"><input type="radio" name="whatsapp" value="Nunca"><span>Nunca</span></label>
                    <label class="option-card"><input type="radio" name="whatsapp" value="Poucas vezes"><span>Poucas vezes</span></label>
                    <label class="option-card"><input type="radio" name="whatsapp" value="Às vezes"><span>Às vezes</span></label>
                    <label class="option-card"><input type="radio" name="whatsapp" value="Frequentemente"><span>Frequentemente</span></label>
                    <label class="option-card"><input type="radio" name="whatsapp" value="Quase todos os dias"><span>Quase todos os dias</span></label>
                </div>
            </div>

            <!-- ETAPA 9: Redes Sociais (Q7 & Q8) -->
            <div class="question-step" data-step="9">
                <h3 class="question-title">7 & 8. Presença Digital</h3>
                
                <div class="form-group">
                    <label>Usa Instagram ou Facebook para divulgar/vender?</label>
                    <select name="usaRedesSociais" id="usaRedesSociais">
                        <option value="Não">Não</option>
                        <option value="Instagram">Apenas Instagram</option>
                        <option value="Facebook">Apenas Facebook</option>
                        <option value="Ambos">Ambos (Instagram e Facebook)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nome da Página / Perfil Social</label>
                    <input type="text" name="perfilSocial" placeholder="@sualivraria">
                </div>

                <div class="form-group">
                    <label>Tem algum site ou catálogo online?</label>
                    <select name="temSite">
                        <option value="Não">Não</option>
                        <option value="Sim">Sim</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>URL do Site (se aplicável)</label>
                    <input type="text" name="urlSite" placeholder="https://...">
                </div>
            </div>

            <!-- ETAPA 10: Gestão e Operação (Q9 & Q10) -->
            <div class="question-step" data-step="10">
                <h3 class="question-title">9 & 10. Registo e Controlo</h3>
                
                <div class="form-group">
                    <label>Como normalmente regista as vendas?</label>
                    <select name="registoVendas">
                        <option value="Caderno">Caderno em papel</option>
                        <option value="Excel">Excel / Folha de cálculo</option>
                        <option value="Sistema">Sistema POS / Software</option>
                        <option value="WhatsApp">Mensagens de WhatsApp</option>
                        <option value="Memória">Apenas na memória</option>
                        <option value="Não regista">Não regista</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Como controla os livros disponíveis (stock)?</label>
                    <select name="controloStock">
                        <option value="Caderno">Caderno em papel</option>
                        <option value="Excel">Excel</option>
                        <option value="Sistema">Sistema de Gestão</option>
                        <option value="Memória">De cabeça / Memória</option>
                        <option value="Não controla">Não faz controlo de stock</option>
                    </select>
                </div>
            </div>

            <!-- ETAPA 11: Rapidez e Volume (Q11 & Q12) -->
            <div class="question-step" data-step="11">
                <h3 class="question-title">11 & 12. Disponibilidade e Pedidos</h3>

                <div class="form-group">
                    <label>Quando alguém pergunta se determinado livro está disponível, consegue confirmar rapidamente?</label>
                    <div class="options-grid">
                        <label class="option-card"><input type="radio" name="confirmaStockRapido" value="Sim, imediatamente"><span>Sim, imediatamente</span></label>
                        <label class="option-card"><input type="radio" name="confirmaStockRapido" value="Sim, mas demora"><span>Sim, mas demora</span></label>
                        <label class="option-card"><input type="radio" name="confirmaStockRapido" value="Às vezes"><span>Às vezes</span></label>
                        <label class="option-card"><input type="radio" name="confirmaStockRapido" value="Normalmente não"><span>Normalmente não</span></label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Aproximadamente quantos pedidos recebe por semana?</label>
                    <select name="pedidosSemana">
                        <option value="0">0 pedidos</option>
                        <option value="1–5">1 – 5 pedidos</option>
                        <option value="6–10">6 – 10 pedidos</option>
                        <option value="11–20">11 – 20 pedidos</option>
                        <option value="21–50">21 – 50 pedidos</option>
                        <option value="Mais de 50">Mais de 50 pedidos</option>
                        <option value="Não sabe">Não sabe</option>
                    </select>
                </div>
            </div>

            <!-- ETAPA 12: Pagamentos (Q13) -->
            <div class="question-step" data-step="12">
                <h3 class="question-title">13. Como recebe pagamentos?</h3>
                <p class="question-subtitle">Selecione todas as modalidades aceites</p>
                <div class="options-grid">
                    <label class="option-card"><input type="checkbox" name="pagamentos[]" value="Dinheiro"><span>Dinheiro físico</span></label>
                    <label class="option-card"><input type="checkbox" name="pagamentos[]" value="M-Pesa"><span>M-Pesa</span></label>
                    <label class="option-card"><input type="checkbox" name="pagamentos[]" value="e-Mola"><span>e-Mola</span></label>
                    <label class="option-card"><input type="checkbox" name="pagamentos[]" value="mKesh"><span>mKesh</span></label>
                    <label class="option-card"><input type="checkbox" name="pagamentos[]" value="Transferência bancária"><span>Transferência Bancária (POS/TPA/Contas)</span></label>
                </div>
            </div>

            <!-- ETAPA 13: Entregas (Q14, Q15, Q16) -->
            <div class="question-step" data-step="13">
                <h3 class="question-title">14-16. Logística e Entregas</h3>

                <div class="form-group">
                    <label>Faz entregas de livros?</label>
                    <select name="fazEntregas">
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                        <option value="Às vezes">Às vezes</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Como faz as entregas?</label>
                    <select name="metodoEntrega">
                        <option value="Cliente levanta">Cliente levanta no local</option>
                        <option value="Entrega própria">Entrega própria</option>
                        <option value="Motorizada">Motorizada / Estafeta</option>
                        <option value="Táxi">Táxi / Chopela</option>
                        <option value="Transportadora">Transportadora / Correios</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Vende para outras cidades/províncias?</label>
                    <select name="vendeOutrasCidades">
                        <option value="Sim, frequentemente">Sim, frequentemente</option>
                        <option value="Às vezes">Às vezes</option>
                        <option value="Raramente">Raramente</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
            </div>

            <!-- ETAPA 14: Dificuldades (Q17 & Q18) -->
            <div class="question-step" data-step="14">
                <h3 class="question-title">17 & 18. Desafios Actuais</h3>

                <div class="form-group">
                    <label>Qual é actualmente a maior dificuldade para vender os seus livros?</label>
                    <textarea name="maiorDificuldade" rows="3" placeholder="Resposta livre do entrevistado..."></textarea>
                </div>

                <div class="form-group">
                    <label>O que gostaria de melhorar no seu negócio?</label>
                    <textarea name="melhorarNegocio" rows="3" placeholder="Resposta livre..."></textarea>
                </div>
            </div>

            <!-- ETAPA 15: Perda de Vendas (Q19 & Q20) -->
            <div class="question-step" data-step="15">
                <h3 class="question-title">19 & 20. Perda de Oportunidades</h3>

                <div class="form-group">
                    <label>Já perdeu alguma venda por demora na resposta, confirmação de stock ou preço?</label>
                    <select name="perdeuVenda">
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                        <option value="Não sabe">Não sabe</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Se sim, o que aconteceu?</label>
                    <input type="text" name="explicacaoPerda" placeholder="Ex: Cliente comprou noutro lugar porque demorei a responder">
                </div>

                <div class="form-group">
                    <label>Quais destas situações mais dificultam o negócio?</label>
                    <div class="options-grid">
                        <label class="option-card"><input type="checkbox" name="dificuldades[]" value="Encontrar novos clientes"><span>Encontrar novos clientes</span></label>
                        <label class="option-card"><input type="checkbox" name="dificuldades[]" value="Divulgar livros"><span>Divulgar livros</span></label>
                        <label class="option-card"><input type="checkbox" name="dificuldades[]" value="Controlar stock"><span>Controlar stock</span></label>
                        <label class="option-card"><input type="checkbox" name="dificuldades[]" value="Fazer entregas"><span>Fazer entregas</span></label>
                        <label class="option-card"><input type="checkbox" name="dificuldades[]" value="Falta de presença online"><span>Falta de presença online</span></label>
                    </div>
                </div>
            </div>

            <!-- ETAPA 16: Validação da Solução (Q21, Q22, Q23) -->
            <div class="question-step" data-step="16">
                <h3 class="question-title">21-23. Validação do Conceito</h3>

                <div class="form-group">
                    <label>Se existisse uma plataforma para colocar livros, preços e contactos para que pessoas de vários lugares os encontrassem, isso seria útil?</label>
                    <select name="utilidadePlataforma">
                        <option value="Muito útil">Muito útil</option>
                        <option value="Útil">Útil</option>
                        <option value="Pouco útil">Pouco útil</option>
                        <option value="Não seria útil">Não seria útil</option>
                        <option value="Não sei">Não sei</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>O que gostaria que uma plataforma dessas fizesse por si?</label>
                    <textarea name="funcionalidadesDesejadas" rows="3" placeholder="Sem induzir respostas..."></textarea>
                </div>

                <div class="form-group">
                    <label>Se tivesse uma página própria da sua loja onde clientes vêm o catálogo e fazem pedidos, usaria?</label>
                    <select name="usariaPagina">
                        <option value="Sim">Sim</option>
                        <option value="Talvez">Talvez</option>
                        <option value="Não">Não</option>
                    </select>
                </div>
            </div>

            <!-- ETAPA 17: Disposição para Pagar (Q24, Q25, Q26) -->
            <div class="question-step" data-step="17">
                <h3 class="question-title">24-26. Modelo Monetário</h3>

                <div class="form-group">
                    <label>Se ajudasse a encontrar novos clientes e organizar vendas, estaria disposto a pagar?</label>
                    <select name="dispostoPagar">
                        <option value="Sim">Sim</option>
                        <option value="Talvez">Talvez</option>
                        <option value="Não">Não</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quanto estaria disposto a pagar mensalmente?</label>
                    <select name="valorMensal">
                        <option value="Não pagaria">Não pagaria</option>
                        <option value="Até 200 MT">Até 200 MT</option>
                        <option value="201–500 MT">201 – 500 MT</option>
                        <option value="501–1.000 MT">501 – 1.000 MT</option>
                        <option value="1.001–2.000 MT">1.001 – 2.000 MT</option>
                        <option value="Mais de 2.000 MT">Mais de 2.000 MT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Qual modelo preferiria?</label>
                    <select name="modeloPagamento">
                        <option value="Mensalidade fixa">Mensalidade fixa</option>
                        <option value="Pequena comissão por venda">Pequena comissão por venda</option>
                        <option value="Mensalidade + comissão">Mensalidade + comissão</option>
                        <option value="Pagamento anual">Pagamento anual</option>
                        <option value="Não pagaria">Não pagaria</option>
                    </select>
                </div>
            </div>

            <!-- ETAPA 18: Early Adopter & Observações (Q27, Q28 & Obs) -->
            <div class="question-step" data-step="18">
                <h3 class="question-title">27-28. Conclusão & Observações</h3>

                <div class="form-group">
                    <label>Se essa solução estivesse disponível hoje, estaria disposto a experimentar?</label>
                    <select name="experimentarHoje">
                        <option value="Sim">Sim</option>
                        <option value="Talvez">Talvez</option>
                        <option value="Não">Não</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observações Privadas do Entrevistador</label>
                    <textarea name="observacoes" rows="3" placeholder="Ex: Muito interessado, possui grande volume de stock, perfil forte para piloto..."></textarea>
                </div>
            </div>

            <!-- Botões de Navegação do Wizard -->
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" id="btnPrev" onclick="prevStep()"><i data-lucide="arrow-left"></i> Anterior</button>
                <button type="button" class="btn btn-primary" id="btnNext" onclick="nextStep()">Próximo <i data-lucide="arrow-right"></i></button>
                <button type="button" class="btn btn-primary" id="btnSubmit" style="display:none; background: var(--success);" onclick="submitSurvey()">✓ Finalizar Entrevista</button>
            </div>

        </form>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <footer>
        <p>Pesquisa desenvolvida por <strong>Zedeck's IT</strong> &copy; <?php echo date('Y'); ?></p>
    </footer>

</div>

<script src="assets/app.js"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>