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
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $cpf = $pessoal->get_cpf($idPessoa);

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração de Não Acumulação");
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao("AUTODECLARAÇÃO ÉTNICO-RACIAL");
    
    $dec->set_texto("Eu, <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, matricula {$matricula}"
            . " ocupante do cargo de {$cargoEfetivo}, inscrito no CPF"
            . " sob o nº {$cpf}, <b>AUTODECLARO,</b>"
            . " sob as penas da lei, minha raça/etinia sendo:");
    
    $dec->set_texto("<br/>"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Branca<br/>"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Preta<br/>"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Parda<br/>"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Amarela<br/>"
            . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Indígena"
            . "<br/>");
    
    $dec->set_texto("Esta autodeclaração atende a exigência do art. 39,"
            . " § 8º, da Lei nº 12.288/2010, alterado pela Lei nº"
            . " 14.553/2023 e da Portaria MTE nº 3.784/2023, que"
            . " obriga a prestação da informação nas inclusões,"
            . " alterações ou retificações cadastrais dos trabalhadores"
            . " ocorridas a partir de 1o de janeiro de 2024,"
            . " respeitando o critério de autodeclaração do"
            . " trabalhador, em conformidade com a classificação"
            . " utilizada pelo Instituto Brasileiro de Geografia"
            . " e Estatística - IBGE.");
    
    $dec->set_texto("Por ser expressão da verdade, firmo e assino a presente"
            . " para que a mesma produza seus efeitos legais e de direito.");
    
    #$dec->set_carimboCnpj(true);
    $dec->set_linhaAssinatura(true);
    $dec->set_data(date("d/m/Y"));
    $dec->set_saltoAssinatura(2);

    # De quem assina
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemSetor("");
    $dec->set_origemDescricao("");

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a autodeclaração etnico-racial';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}