<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "o servidor";
    } else {
        $texto1 = "a servidora";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    
    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaro para fins de afastamento eleitoral, que {$texto1} <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, {$cargoEfetivo}, teve sua frequência INTEGRAL no período de 02/01/2020 até a presente data.");

    $dec->set_saltoAssinatura(6);      
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de frequência';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}