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

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $matricula = $pessoal->get_matricula($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $texto1 = "Eu, <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, matricula {$matricula}"
            . " ocupante do cargo de {$cargoEfetivo}, nesta Universidade,"
            . " cumprindo a carga horária de 40 horas (semanal),"
            . " declaro que, além deste cargo, <b><u>NÃO POSSUO</u> outro"
            . " vínculo público</b>, resultante de proventos recebidos"
            . " ou do exercício em cargo, emprego ou função na administração"
            . " pública direta ou indireta, em Fundação mantida pelo Poder"
            . " Público, Empresa de Economia Mista ou Empresa Pública. ";

    $texto2 = "Declaro, ainda, que é do meu conhecimento que qualquer omissão"
            . " constituirá má fé, bem como estou ciente de que qualquer"
            . " alteração da situação aqui declarada deverá ser imediatamente"
            . " comunicada à Gerencia de Recursos Humanos através de"
            . " processo no SEI.";

    $texto3 = "Telefones para contato: ____________________________________________";

    # Monta a Declaração
    $dec = new Declaracao("DECLARAÇÃO ANUAL DE NÃO ACUMULAÇÃO<br/>DE CARGOS / EMPREGO / FUNÇÃO");
    #$dec->set_carimboCnpj(true);
    $dec->set_linhaAssinatura(true);
    #$dec->set_data(date("d/m/Y"));
    $dec->set_texto($texto1);
    $dec->set_texto($texto2);
    $dec->set_texto($texto3);
    $dec->set_saltoAssinatura(2);

    # De quem assina
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemSetor("");
    $dec->set_origemDescricao("");

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de rendimentos';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}