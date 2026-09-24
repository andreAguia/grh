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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração Atribuição");
    $page->iniciaPagina();

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Verifica se o perfil permite a declaração
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    if (($idPerfil == 1) OR ($idPerfil == 4)) {

        # Servidor
        $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
        $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
        $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);

        # Servidor Logado
        $assina = $intra->get_idServidor($idUsuario);
        $nome = $pessoal->get_nome($assina);
        $cargo = $pessoal->get_cargoSimples($assina);
        $idFuncionalLogado = $pessoal->get_idFuncional($assina);

        # Monta a Declaração
        $dec = new Declaracao();
        $dec->set_declaracaoNome("CERTIDÃO");
        $dec->set_carimboCnpj(true);
        #$dec->set_assinatura(true);
        $dec->set_data(date("d/m/Y"));

        $dec->set_texto("Certifico, em cumprimento ao inc. IV, do art. 3º da Portaria 
            SEPLAG/SUBAP nº 65/2012, que foi realizada consulta no Sistema
            SEI/RJ, objetivando evitar a duplicidade de análise de processos de
            acumulação de cargos, com base nos seguintes parâmetros de busca de
            processos: Tipo de processo: Acumulação de cargos; Classificação
            por assunto: Recursos Humanos - código 13.06.04.23 - Processo de
            acumulação de cargos; e Interessado: nome do servidor; e,
            no Sistema Único de Processos - UPO, com as seguintes bases de
            procura: Nome do Interessado (parcial): nome do servidor, 
            <b>não sendo localizado</b>, nesta data, em ambos os Sistemas, nenhum
            processo com as bases de dados mencionada em nome de <b>{$nomeServidor}</b>, 
                Id Funcional {$idFuncional}.");

        $dec->set_texto("E, por nada mais constar, eu, {$nome}, {$cargo},
            ID Funcional nº {$idFuncionalLogado}, lavrei a presente Certidão, "
            . "que dato e assino.");

        $dec->set_rodapeSoUntimaPag(true);

        $dec->set_origemNome($nome);
        $dec->set_origemDescricao($cargo);
        $dec->set_origemIdFuncional($idFuncionalLogado);
        $dec->show();

        # Grava o log da visualização do relatório
        $data = date("Y-m-d H:i:s");
        $atividades = 'Visualizou a certidão de não localização de processo de acumulação';
        $tipoLog = 4;
        $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);
    } else {
        br(4);
        p("A Declaração de Atribuições é somente para Servidores Concursados", "f14", "center");
    }

    $page->terminaPagina();
}