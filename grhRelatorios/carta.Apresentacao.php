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
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Servidor
    $nomeServidor = strtoupper($pessoal->get_nome($idServidorPesquisado));
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoServidor = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao($idLotacao);
    $dtAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
    
    $idChefe = $pessoal->get_chefiaImediata($idServidorPesquisado);
    $chefe = $pessoal->get_nome($idChefe);
    $cargo = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);
    
    $diretor = $pessoal->get_nome($pessoal->get_chefiaImediata($idChefe));
    $cargoDiretor = $pessoal->get_chefiaImediataDescricao($idChefe);
            

    # Monta a Carta
    $carta = new Carta();

    $carta->set_nomeCarta("CARTA DE APRESENTAÇÃO");
    $carta->set_destinoNome($chefe);
    $carta->set_destinoSetor($cargo);
    
    $carta->set_destinoNomeCC($diretor);
    $carta->set_destinoSetorCC($cargoDiretor);
    $carta->set_assinatura(true);

    $texto = "Apresentamos a V.Sª. o(a) Sr(a) <b>{$nomeServidor}</b>, cargo {$cargoServidor}, para exercer suas atividades na {$lotacao}
        , a contar de {$dtAdmissao}, data de sua posse no Cargo Público de {$cargoServidor}, previamente aprovado em Concurso Público.";

    $carta->set_texto($texto);

    $carta->set_saltoRodape(3);
    $carta->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Carta de Apresentação.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tblicencasemvencimentos", $idServidorPesquisado, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}