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

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Parametros    
    $idCandidatoPesquisado = get_session("idCandidatoPesquisado");

    # Pega os dados digitados
    $data = date_to_php(post("data"));
    $hora = post("hora");

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Ofício de Abertura de Conta");
    $page->iniciaPagina();

    # Dados do Candidato
    $candidatoClasse = new CandidatoAdm2025();
    $dados = $candidatoClasse->get_dados($idCandidatoPesquisado);

    # Pega o Número do Ofício
    if (empty($dados["numOficio"])) {
        $numero = $candidatoClasse->get_numOficio(date("Y"));
        $ano = date("Y");
    }else{
        $numero = $dados["numOficio"];
        $ano = $dados["anoOficio"];
    }

    # Monta o Ofício
    $oficio = new Oficio(str_pad($numero, 3, "0", STR_PAD_LEFT) . " / {$ano}");

    # Destino Nome
    $oficio->set_destinoNome("Ao");
    $oficio->set_destinoNome("Ilmo Sr.");
    $oficio->set_destinoNome("Dr. Carlos Eduardo Merenlender");
    $oficio->set_destinoNome("Superintendente Central de Perícia Médica e Saúde Ocupacional");

    # Destino Setor
    $oficio->set_destinoSetor("SECRETARIA DE ESTADO DE SAÚDE - SES");
    $oficio->set_destinoSetor("Rua Silva Jardim n.º 31 – Praça Tiradentes");
    $oficio->set_destinoSetor("Centro – Rio de Janeiro/RJ");

    # Outros Parâmetros
    $oficio->set_assinatura(true);
    $oficio->set_carimboCnpj(true);
    $oficio->set_carimboAberturaConta(false);

    $oficio->set_texto("Tenho a honra de encaminhar a V.S.ª, o(a) candidato(a) {$dados['nome']}, "
            . "RG nº {$dados['identidade']} Detran RJ, CPF {$dados['cpf']}, "
            . "aprovado(a) em Concurso Público da Universidade Estadual do Norte Fluminense "
            . "Darcy Ribeiro (UENF), para a realização de exame médico admissional no dia {$data} às {$hora} horas,"
            . " na Rua Silva Jardim, 31 – Praça Tiradentes – Centro - Rio de Janeiro/RJ tel.(21) 2332-6526.");
    $oficio->set_texto("O referido exame é requisito indispensável ao processo de admissão no cargo de {$dados['cargo']}, "
            . "do Quadro Permanente desta Universidade, vinculada à Secretaria de Estado de "
            . "Ciência, Tecnologia e Inovação do Rio de Janeiro.");

    $oficio->temRodape(false);
    $oficio->show();

    # Grava o número do Oficio
    $sql = "UPDATE tbcandidato SET numOficio = {$numero},anoOficio = {$ano}                                 
             WHERE idCandidato = {$idCandidatoPesquisado}";
    $pessoal->update($sql);

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou o ofício de encaminhamento de candidato';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbcandidato", $idCandidatoPesquisado, $tipoLog);

    $page->terminaPagina();
}