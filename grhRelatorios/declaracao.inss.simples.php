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
    $nomeServidor = mb_strtoupper($pessoal->get_nome($idServidorPesquisado), 'UTF-8');
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $matricula = $pessoal->get_matricula($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $pis = $pessoal->get_Pis($idPessoa);
    $cpf = $pessoal->get_cpf($idPessoa);

    # Tempo Averbado
    $averb = new Averbacao();
    $tempoAverbado = $averb->get_tempoAverbadoTotal($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração de Não Acumulação");
    $page->iniciaPagina();

    # Pega o órgão
    $parametroOrgao = post('parametroOrgao', "Instituto Nacional de Seguro Social - INSS");

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_linhaAssinatura(true);
    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaramos, para fins de comprovação junto a(o) {$parametroOrgao},"
            . " que a Universidade Estadual do Norte Fluminense Darcy Ribeiro, está inscrita no CNPJ nº 04.809.688/0001-06,"
            . " com sede na Av. Alberto Lamego 2000 - Parque California, Campos dos Goytacazes - RJ, CEP 28013-602.");

    $dec->set_texto("Declaramos, ainda, que o(a) Sr(a). <b>{$nomeServidor}</b>, ID funcional nº {$idFuncional},"
            . " inscrito(a) no PIS/PASEP sob o nº {$pis} e CPF nº {$cpf}, é servidor(a) desta Universidade, por aprovação em concurso público,"
            . " desde {$dtAdmin}, sob o regime estatutário e desconto previdenciário em favor do Rioprevidência, no cargo de {$cargoEfetivo}.");

    # Quando não se tem tempo averbado
    if ($tempoAverbado == 0) {
        $dec->set_texto("Informamos, ainda, que o(a) servidor(a) não averbou qualquer período de serviço prestado a entidade vinculadas ao RGPS nesta Instituição e,"
                . " portanto, não possui vantagens remuneratórias de outros vínculos que não sejam da própria Uenf.");
    }
    $dec->set_saltoAssinatura(2);
    $dec->set_linhaAssinatura(false);

    # De quem assina
    #$dec->set_origemNome($nomeServidor);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_assinatura(true);

    $dec->set_formCampos(array(
        array('nome' => 'parametroOrgao',
            'label' => 'Órgão:',
            'tipo' => 'texto',
            'size' => 250,
            'title' => 'Órgão',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $parametroOrgao,
            'col' => 6,
            'linha' => 1),
        array('nome' => 'submit',
            'valor' => 'Atualiza',
            'label' => '-',
            'size' => 4,
            'col' => 3,
            'tipo' => 'submit',
            'title' => 'Atualiza a tabela',
            'linha' => 1),
    ));

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de rendimentos';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}