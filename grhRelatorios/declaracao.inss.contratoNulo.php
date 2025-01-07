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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Servidor
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $matricula = $pessoal->get_matricula($idServidorPesquisado);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    $dtAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoSimples($idServidorPesquisado);
    $pis = $pessoal->get_Pis($idPessoa);
    $cpf = $pessoal->get_cpf($idPessoa);

    #$cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);

    # Texto da declaração
    $paragrafo1 = "Declaramos para fins de comprovação junto ao Instituto Nacional do Seguro Social"
            . " - INSS, que esta Universidade Estadual do Norte Fluminense Darcy Ribeiro é portadora do"
            . " CNPJ nº 04.809.688/0001-06, com sede na Av. Alberto Lamego, 2.000, Parque Califórnia – "
            . "Campos dos Goytacazes – RJ, CEP: 28.013-602.";

    $paragrafo2 = "Outrossim, declaramos que ";

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $paragrafo2 .= "o Sr. <b>" . strtoupper($nomeServidor) . "</b>,";
    } else {
        $paragrafo2 .= "a Sra. <b>" . strtoupper($nomeServidor) . "</b>,";
    }

    # Verifica se tem id
    if (!empty($idFuncional)) {
        $paragrafo2 .= " ID funcional nº {$idFuncional},";
    }

    $paragrafo2 .= " matrícula {$matricula}, inscrito no PIS/PASEP sob o nº {$pis} e CPF nº {$cpf}, ";
    
    if ($sexo == "Masculino") {
        $paragrafo2 .= "foi contratado ";
    } else {
        $paragrafo2 .= "foi contratada ";
    }
    
    $paragrafo2 .= "sob o regime CLT, com desconto previdenciário para o INSS no período de "
            . "{$dtAdmissao} a {$dtSaida}, pela FUNDAÇÃO ESTADUAL NORTE FLUMINENSE,"
            . " CNPJ nº 39.229.406/0001-86, sendo transferido para a UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE"
            . " DARCY RIBEIRO, a partir de 01/01/2002, por força da Lei Estadual nº 3.684/01.";

    $paragrafo3 = "Esclarecemos que a referida Fundação era uma Instituição de Direito Público Estadual e "
            . "mantenedora da Universidade Estadual Norte Fluminense Darcy Ribeiro – UENF, sendo extinta pela "
            . "Lei Estadual nº 7.237 de 16 de março de 2016, que transferiu as atribuições, estrutura e "
            . "patrimônio para a UENF.";
    
    $paragrafo4 = "Adicionalmente declaramos que o referido contrato foi considerado Nulo pelo Tribunal de Contas"
            . " do Estado do Rio de Janeiro com publicação no DOERJ de 07/12/1998, pág. 23.";

    # Monta a Declaração
    $dec = new Declaracao();

    $dec->set_data(date("d/m/Y"));
    $dec->set_texto($paragrafo1);
    $dec->set_texto($paragrafo2);
    $dec->set_texto($paragrafo3);
    $dec->set_texto($paragrafo4);

    $dec->set_assinatura(true);
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração para o inss de contrato nulo';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}