<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    #$parametroMes = post('mes',date('m'));
    $parametroAno = post('ano', date('Y'));
    $parametroLotacao = post('lotacao');

    if ($parametroLotacao == "*") {
        $parametroLotacao = NULL;
    }

    ######

    $afast = new Afastamento();
    $afast->set_ano($parametroAno);
    $afast->set_mes(NULL);
    $afast->set_lotacao($parametroLotacao);
    $afast->set_linkEditar('?fase=editaServidor');
    $afast->set_formulario(TRUE);
    $afast->set_campoMes(FALSE);
    $afast->exibeRelatorio();

    $page->terminaPagina();
}