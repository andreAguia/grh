<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario            = null;              # Servidor logado
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
    $idFuncional  = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $sexo         = $pessoal->get_sexo($idServidorPesquisado);

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
    $dec->set_declaracaoNome("DECLARAÇÃO DE RESPONSABILIZAÇÃO DE ENTREGA DA CERTIDÃO DE REGISTRO DA CANDIDATURA");
    #$dec->set_carimboCnpj(true);
    #$dec->set_assinatura(true);
    
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemSetor(null);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemDescricao(null);

    $dec->set_data(null);

    $dec->set_texto("<b>" . strtoupper($nomeServidor) . "</b>, ID funcional nº {$idFuncional}, ocupente do cargo de {$cargoEfetivo}, "
            . "<b>declara</b> para os devidos fins, especialmente para fins de afastamento eleitoral ora requerido, na forma do "
            . "disposto no parágrafo 3º do art. 2º da Resolução SEPLAG nº 1436 de 04/02/2016, estar ciente da obrigatoriedade"
            . " de fornecer a Certidão de Registro da Candidatura (Item 5 anexo I), ao órgão/setorial de Recursos Humanos,"
            . " tão logo seja expedida pela Justiça Eleitoral.");
    
    $dec->set_texto("Declaro ainda, estar ciente que em caso de não apresentação da Certidão, o pedido de afastamento será considerado"
            . " irregular, e o servidor incorrerá em faltas e abandono de cargo, devendo, ainda, ressarcir a Fazenda estadual pelas"
            . " remunerações recebidas durante aquele período, na forma da legislação em vigor, ressalvadas, as hipóteses de indeferimento"
            . " do registro e de desistência.");

    $dec->set_saltoAssinatura(6);    
    $dec->show();

    # Grava o log da visualização do relatório
    $data       = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de frequência';
    $tipoLog    = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}