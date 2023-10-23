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
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoSimples($idServidorPesquisado);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);
    $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);
    $idMotivo = $pessoal->get_idMotivo($idServidorPesquisado);

    # Começa o texto
    $texto = "Declaro, para os devidos fins, que ";

    # O nome do servidor
    $texto .= " <b>" . strtoupper($nomeServidor) . "</b>,";

    # O id(se tiver)
    if (!empty($idFuncional)) {
        $texto .= " ID funcional nº {$idFuncional},";
    }
    
    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "cedido";
        $texto2 = "servidor";
        $texto3 = "admitido";
    } else {
        $texto1 = "cedida";
        $texto2 = "servidora";
        $texto3 = "admitida";
    }

    # Cedido
    if ($idPerfil == 2) {
        if ($idSituacao == 1) {
            $texto .= " é {$texto1} do(a) {$pessoal->get_orgaoCedidoFora($idServidorPesquisado)} a esta Universidade desde {$dtAdmin}, {$cargoEfetivo}.";
        } else {
            $texto .= " foi {$texto1} do(a) {$pessoal->get_orgaoCedidoFora($idServidorPesquisado)} a esta Universidade do período de {$dtAdmin} a {$dtSaida}, {$cargoEfetivo}.";
        }
    }

    # Estatutário
    if ($idPerfil == 1) {
        if ($idSituacao == 1) {
            $texto .= " é {$texto2} desta Universidade, {$texto3} em {$dtAdmin}, através de Concurso Público, para o cargo de {$cargoEfetivo}.";
        } else {
            $texto .= " foi {$texto2} desta Universidade, {$texto3} em {$dtAdmin}, através de Concurso Público para o cargo de {$cargoEfetivo}, até {$dtSaida}";

            # Continua de acordo com o motido de saída
            switch ($idMotivo) {
                case 1 : // Exoneração a pedido
                    $texto .= ", quando solicitou exoneração.";
                    break;

                case 2 : // Falecimento
                    $texto .= ", por motivo de falecimento.";
                    break;

                case 3 : // Aposentadoria voluntária
                case 5 : // Aposentadoria por invalidez acidente
                case 6 : // Aposentadoria por invalidez doença
                case 15 : // Aposentadoria
                    $texto .= ", quando se aposentou.";
                    break;

                case 4 : // Aposentadoria compulsória
                    $texto .= ", quando se aposentou compulsoriamente.";
                    break;

                case 9 : // Demissão por justa causa
                case 10 : // Demissão
                    $texto .= ", quando foi demitido.";
                    break;

                case 14 : // Exonerado
                    $texto .= ", quando foi exonerado.";
                    break;

                default :
                    $texto .= ".";
                    break;
            }
        }
    }

    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));
    $dec->set_texto($texto);
    $dec->set_saltoAssinatura(2);
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração de Vínculo Empregatício';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}