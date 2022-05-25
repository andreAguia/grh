<?php

/**
 * Pastas e Pasta Funcional
 *  
 * By Alat
 */
# Servidor logado
$idUsuario = null;

# Servidor Editado na pesquisa do sistema do GRH
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase');

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Pasta Funcional";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    ################################################################
    switch ($fase) {
        case "" :

            # Menu
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Documentos Gerais', '#', 'Área Especial');
            $menu->add_item('linkWindow', 'Codigos de Afastemento', PASTA_DOCUMENTOS . 'codigoAfastamento.pdf');
            $menu->add_item('titulo', 'Legislação', '#', 'Área Especial');
            $menu->add_item('linkWindow', 'Estatuto dos Servidores', "http://alerjln1.alerj.rj.gov.br/decest.nsf/968d5212a901f75f0325654c00612d5c/2caa8a7c2265c33b0325698a0068e8fb?OpenDocument#_Section1", "Decreto nº 2479 de 08 de Março de 1979");
            $menu->add_item('linkWindow', 'Plano de Cargos e Vencimentos', "http://alerjln1.alerj.rj.gov.br/contlei.nsf/b24a2da5a077847c032564f4005d4bf2/aa5390d4c58db774832571b60066a2ba?OpenDocument", "LEI Nº 4.800 de 29 de Junho de 2006");
            $menu->add_item('linkWindow', 'Resoluções da Reitoria', "http://uenf.br/reitoria/legislacao/resolucoes/");
            $menu->add_item('linkWindow', 'Portarias', "http://uenf.br/reitoria/legislacao/portarias/");
            #$menu->add_item('linkWindow', 'Estatuto da UENF', "http://www.uenf.br/Uenf/Downloads/REITORIA_1360_1101117875.pdf");

            $menu->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}