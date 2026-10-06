/**
 * BookMarket Research — Frontend App Script
 * Lógica do Questionário, Autosave e Sync Offline
 */

let currentStep = 1;
const totalSteps = 18;
const DRAFT_KEY = 'bookmarket_survey_draft';
const PENDING_KEY = 'bookmarket_pending_interviews';

document.addEventListener('DOMContentLoaded', () => {
    checkDraft();
    setupAutoSave();
    updateOnlineStatus();

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
});

function updateOnlineStatus() {
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');

    if (navigator.onLine) {
        statusDot.className = 'status-dot online';
        statusText.textContent = 'Online';
        syncPendingInterviews();
    } else {
        statusDot.className = 'status-dot offline';
        statusText.textContent = 'Modo Offline';
    }
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function startSurvey() {
    document.getElementById('welcomeScreen').style.display = 'none';
    document.getElementById('surveyWizard').style.display = 'block';
    showStep(1);
}

function showStep(step) {
    const steps = document.querySelectorAll('.question-step');
    steps.forEach(s => s.classList.remove('active'));

    const targetStep = document.querySelector(`.question-step[data-step="${step}"]`);
    if (targetStep) {
        targetStep.classList.add('active');
    }

    currentStep = step;
    
    // Atualizar Barra de Progresso
    const progress = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('progressPercent').textContent = `${progress}%`;
    document.getElementById('stepCounter').textContent = `Passo ${currentStep} de ${totalSteps}`;

    // Visibilidade dos Botões
    document.getElementById('btnPrev').style.visibility = (currentStep === 1) ? 'hidden' : 'visible';
    
    if (currentStep === totalSteps) {
        document.getElementById('btnNext').style.display = 'none';
        document.getElementById('btnSubmit').style.display = 'flex';
    } else {
        document.getElementById('btnNext').style.display = 'flex';
        document.getElementById('btnSubmit').style.display = 'none';
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextStep() {
    // Validação Simples do Passo 1
    if (currentStep === 1) {
        const nome = document.getElementById('input_nome').value.trim();
        const cidade = document.getElementById('input_cidade').value;
        if (!nome || !cidade) {
            showToast('Por favor preencha o nome e a cidade antes de avançar.', 'warning');
            return;
        }
    }

    if (currentStep < totalSteps) {
        showStep(currentStep + 1);
    }
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

function setupAutoSave() {
    const form = document.getElementById('surveyForm');
    form.addEventListener('input', () => {
        saveDraft();
    });
}

function saveDraft() {
    const formData = serializeFormData();
    localStorage.setItem(DRAFT_KEY, JSON.stringify(formData));
}

function checkDraft() {
    const draft = localStorage.getItem(DRAFT_KEY);
    if (draft) {
        document.getElementById('draftAlert').style.display = 'block';
    }
}

function resumeDraft() {
    const draft = localStorage.getItem(DRAFT_KEY);
    if (draft) {
        const data = JSON.parse(draft);
        populateForm(data);
        document.getElementById('draftAlert').style.display = 'none';
        startSurvey();
        showToast('Rascunho restaurado com sucesso!');
    }
}

function discardDraft() {
    localStorage.removeItem(DRAFT_KEY);
    document.getElementById('draftAlert').style.display = 'none';
    showToast('Rascunho descartado.');
}

function serializeFormData() {
    const form = document.getElementById('surveyForm');
    const formData = new FormData(form);
    const data = {
        entrevistado: {},
        perfil: { tipoVenda: [], categorias: [] },
        marketing: { descobertaClientes: [] },
        operacao: {},
        pagamentos: [],
        entregas: {},
        problemas: { dificuldades: [] },
        validacao: {}
    };

    data.entrevistado.nome = formData.get('nome') || '';
    data.entrevistado.telefone = formData.get('telefone') || '';
    data.entrevistado.cidade = formData.get('cidade') || '';
    data.entrevistado.local = formData.get('local') || '';
    data.entrevistado.consentimentoContacto = formData.get('consentimentoContacto') || 'Sim';
    data.entrevistado.entrevistador = formData.get('entrevistador') || 'Pesquisador';

    data.perfil.tipoVenda = formData.getAll('tipoVenda[]');
    data.perfil.tempoVenda = formData.get('tempoVenda') || '';
    data.perfil.quantidadeLivros = formData.get('quantidadeLivros') || '';
    data.perfil.categorias = formData.getAll('categorias[]');

    data.marketing.descobertaClientes = formData.getAll('descobertaClientes[]');
    data.marketing.descobertaOutros = formData.get('descobertaOutros') || '';
    data.marketing.whatsapp = formData.get('whatsapp') || '';
    data.marketing.usaRedesSociais = formData.get('usaRedesSociais') || 'Não';
    data.marketing.perfilSocial = formData.get('perfilSocial') || '';
    data.marketing.temSite = formData.get('temSite') || 'Não';
    data.marketing.urlSite = formData.get('urlSite') || '';

    data.operacao.registoVendas = formData.get('registoVendas') || '';
    data.operacao.controloStock = formData.get('controloStock') || '';
    data.operacao.confirmaStockRapido = formData.get('confirmaStockRapido') || '';
    data.operacao.pedidosSemana = formData.get('pedidosSemana') || '';

    data.pagamentos = formData.getAll('pagamentos[]');

    data.entregas.fazEntregas = formData.get('fazEntregas') || '';
    data.entregas.metodoEntrega = formData.get('metodoEntrega') || '';
    data.entregas.vendeOutrasCidades = formData.get('vendeOutrasCidades') || '';

    data.problemas.maiorDificuldade = formData.get('maiorDificuldade') || '';
    data.problemas.melhorarNegocio = formData.get('melhorarNegocio') || '';
    data.problemas.perdeuVenda = formData.get('perdeuVenda') || '';
    data.problemas.explicacaoPerda = formData.get('explicacaoPerda') || '';
    data.problemas.dificuldades = formData.getAll('dificuldades[]');

    data.validacao.utilidadePlataforma = formData.get('utilidadePlataforma') || '';
    data.validacao.funcionalidadesDesejadas = formData.get('funcionalidadesDesejadas') || '';
    data.validacao.usariaPagina = formData.get('usariaPagina') || '';
    data.validacao.dispostoPagar = formData.get('dispostoPagar') || '';
    data.validacao.valorMensal = formData.get('valorMensal') || '';
    data.validacao.modeloPagamento = formData.get('modeloPagamento') || '';
    data.validacao.experimentarHoje = formData.get('experimentarHoje') || '';

    data.observacoes = formData.get('observacoes') || '';

    return data;
}

function populateForm(data) {
    if (data.entrevistado) {
        if (data.entrevistado.nome) document.getElementById('input_nome').value = data.entrevistado.nome;
        if (data.entrevistado.telefone) document.getElementById('input_telefone').value = data.entrevistado.telefone;
        if (data.entrevistado.cidade) document.getElementById('input_cidade').value = data.entrevistado.cidade;
    }
}

async function submitSurvey() {
    const payload = serializeFormData();

    if (!payload.entrevistado.nome || !payload.entrevistado.cidade) {
        showToast('O nome e a cidade são obrigatórios', 'danger');
        showStep(1);
        return;
    }

    if (navigator.onLine) {
        try {
            const response = await fetch('api.php?action=saveInterview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const res = await response.json();

            if (res.success) {
                showToast(`Entrevista gravada com sucesso! ID: ${res.id}`);
                localStorage.removeItem(DRAFT_KEY);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(`Erro ao gravar: ${res.error}`, 'danger');
            }
        } catch (err) {
            savePendingLocally(payload);
        }
    } else {
        savePendingLocally(payload);
    }
}

function savePendingLocally(data) {
    let pending = JSON.parse(localStorage.getItem(PENDING_KEY) || '[]');
    pending.push(data);
    localStorage.setItem(PENDING_KEY, JSON.stringify(pending));
    localStorage.removeItem(DRAFT_KEY);

    showToast('Sem ligação. Entrevista guardada no dispositivo e aguardando sincronização.');
    setTimeout(() => location.reload(), 2000);
}

async function syncPendingInterviews() {
    let pending = JSON.parse(localStorage.getItem(PENDING_KEY) || '[]');
    if (pending.length === 0) return;

    showToast(`A sincronizar ${pending.length} entrevistas pendentes...`);

    let remaining = [];
    for (let interview of pending) {
        try {
            const response = await fetch('api.php?action=saveInterview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(interview)
            });
            const res = await response.json();
            if (!res.success) remaining.push(interview);
        } catch (e) {
            remaining.push(interview);
        }
    }

    localStorage.setItem(PENDING_KEY, JSON.stringify(remaining));
    if (remaining.length === 0) {
        showToast('Todas as entrevistas pendentes foram sincronizadas com sucesso!');
    }
}