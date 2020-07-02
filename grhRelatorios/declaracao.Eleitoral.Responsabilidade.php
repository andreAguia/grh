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
    $dec->set_declaracaoNome("DECLARAÇÃO DE RESPONSABILIZAÇÃO DE<br/>ENTREGA DA CERTIDÃO DE REGISTRO DA CANDIDATURA");
    #$dec->set_carimboCnpj(true);
    #$dec->set_assinatura(true);
    
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemSetor(null);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemDescricao(null);

    $dec->set_data(null);

    $dec->set_texto("<b>" . strtoupper($nomeServidor) . "</b>, ID funcional nº {$idFuncional}, ocupente do cargo de {$cargoEfetivo}, "
            . "<b>declaro(a)</b> para o fim de afastamento eleitoral ora requerido, na forma do disposto no inciso IV,"
            . " do art. 74, do Decreto no 2.479, de 08 de março de 1979, c/c a Lei Complementar no 64, de 18 de maio de 1990,"
            . " e em observância ao <b>§3o do artigo 2o da Resolução SEPLAG No 1436 de 04 de fevereiro de 2016</b>, que me responsabilizo perante"
            . " a Administração Pública estadual em fornecer ao setor de Recursos Humanos competente, a devida Certidão de Registro de Candidatura,"
            . " tão logo a mesma esteja disponibilizada para o servidor. Do mesmo modo, afirmo ter ciência de que a não apresentação da referida"
            . " Certidão de Registro de Candidatura pode acarretar em irregularidade do afastamento e possível configuração de abandono de cargo,"
            . " conforme incisos V, VI e §1o do artigo 52 do Decreto Lei 220 de 1975, e incisos V, VI e §1o do artigo 298 do Decreto 2.479 de 1979,"
            . " sem prejuízo do ressarcimento à Fazenda Pública estadual pelas remunerações percebidas durante o período de afastamento, nos termos"
            . " do <b>§4o do art. 2o da Resolução SEPLAG no 1436 de 04 de fevereiro de 2016</b>.");

    $dec->set_saltoAssinatura(6);    
    $dec->show();

    # Grava o log da visualização do relatório
    $data       = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de responsabilização';
    $tipoLog    = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}