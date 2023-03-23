<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Pega o id
    $id = get('id');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cpf = $pessoal->get_cpf($pessoal->get_idPessoa($idServidorPesquisado));
    $identidade = $pessoal->get_identidade($pessoal->get_idPessoa($idServidorPesquisado));    

    # Assunto
    $assunto = "Abertura de Conta para Crédito de Pagamento.";

    # Monta o Ofício
    $oficio = new Oficio(12, null, $assunto);
    
    $oficio->set_destinoNome("Ao Banco Bradesco");
    $oficio->set_destinoSetor("At. Gerente");
    $oficio->set_assinatura(true);
    $oficio->set_carimboCnpj(true);
    $oficio->set_carimboAberturaConta(true);
   
    $oficio->set_texto("Apresentamos o(a) Sr(a) <b>{$nomeServidor}</b>, portador(a) do RG: {$identidade} e CPF: {$cpf} para abertura de Conta para Crédito de pagamento.");
    $oficio->set_texto("Os Servidores do GOVERNO DO ESTADO DO RIO DE JANEIRO já possuem aprovado um Pacote de Benefícios exclusivo, com direito a tarifas e taxas diferenciadas."); 
    
    $oficio->set_obsTitulo("<b>ATENÇÃO AGÊNCIA – REALIZAR O CADASTRAMENTO NAS ROTINAS:</b>");
    $oficio->set_obsFinal("- Contas Bradesco – CSAL Opção – 03 – Subopção 1 – Agência e Conta Salário Bradesco, Agência e Conta-Corrente Bradesco.");
    $oficio->set_obsFinal("- No Aplicativo GFCT – Cesta Serviços – Adesão Individual –Inclusão – Agência e Conta-Corrente – Usar Código: 1115 Cesta Completa ou 1229 Cesta Básica.");
    $oficio->set_obsFinal("- Acessar o correio e enviar O “MPI – BENEFÍCIO SERVIDOR RJ"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            . "&nbsp;&nbsp;&nbsp;&nbsp;Via da Agência");
    $oficio->set_obsFinal("<hr/>");
    $oficio->set_obsFinal("Prezado(a) Servidor(a) do Governo do Estado do Rio de Janeiro,");
    $oficio->set_obsFinal("<br/>");
    $oficio->set_obsFinal("Este protocolo de abertura de conta salário deverá ser entregue na área de Recursos Humanos da Secretaria/Orgão ao qual está vinculado, a fim de proceder o cadastro para recebimento de créditos provenientes de folha de pagamento.");
    $oficio->set_obsFinal("<br/>");
    $oficio->set_obsFinal("Agência: ________________________ Conta Salário: _____________________________________");
    $oficio->set_obsFinal("<br/>");
    $oficio->set_obsFinal("______________________________________");
    $oficio->set_obsFinal("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Banco Bradesco - Carimbo e Assinatura&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            . "Via do Servidor");
    
    
    $oficio->temRodape(false);
    $oficio->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Ci de renovação de redução da carga horária: ';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}