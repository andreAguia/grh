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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração Atribuição");
    $page->iniciaPagina();

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));
    $especifico = post('especifico');

    # Verifica se o perfil permite a declaração
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    if (($idPerfil == 1) OR ($idPerfil == 4)) {

        # Servidor
        $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
        $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
        $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
        $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
        $idCargo = $pessoal->get_idCargo($idServidorPesquisado);
        $atribuicoesCargo = $pessoal->get_cargoAtribuicoes($idCargo);
        $idArea = $pessoal->get_idAreaCargo($idCargo);
        $atribuicoesArea = $pessoal->get_areaDescricao($idArea);
        $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);

        # Monta a Declaração
        $dec = new Declaracao();
        $dec->set_carimboCnpj(true);
        $dec->set_data(date("d/m/Y"));

        if ($idSituacao == 1) {
            $texto = "Declaramos que o(a) Sr.(a) <b>" . strtoupper($nomeServidor) . "</b>,";

            if (!vazio($idFuncional)) {
                $texto .= " ID Funcional n° $idFuncional,";
            }

            $texto .= " é servidor(a) desta Universidade, admitido(a) através de Concurso Público em $dtAdmissao,"
                    . " para ocupar o cargo de $cargoEfetivo, lotado(a) no(a) $lotacao. ";

            $dec->set_texto($texto);
        } else {
            # Pega a data de Saída
            $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);

            $dec->set_texto("Declaramos que o(a) Sr.(a) <b>" . strtoupper($nomeServidor) . "</b>, ID Funcional n° $idFuncional, foi servidor(a) concursado desta"
                    . "  Universidade, no período $dtAdmissao a $dtSaida, ocupando o cargo de $cargoEfetivo.");
        }

        # Pega o histórico de lotação
        $lotacaoClasse = new Lotacao();
        $dadosLotacao = $lotacaoClasse->get_historicoLotacao($idServidorPesquisado);

        if (count($dadosLotacao) > 1) {

            $texto2 = "Declaramos, também, que desde a sua admissão, o(a) servidor(a) atuou nos seguintes setores desta universidade:<br/>";
            #$texto2 .= "<ul>";

            foreach ($dadosLotacao as $item) {

                if (!empty($item['data'])) {

                    # Pega a data da lotação posterior
                    $dtPosterior = $lotacaoClasse->get_dataLotacaoPosterior($item['idHistLot']);

                    if (empty($dtPosterior)) {
                        $texto2 .= "- A partir de ";
                    } else {
                        $texto2 .= "- De ";
                    }
                    $texto2 .= date_to_php($item['data']);

                    if (!empty($dtPosterior)) {
                        $texto2 .= " a ";
                        $texto2 .= $lotacaoClasse->get_dataLotacaoPosterior($item['idHistLot']);
                    }

                    $texto2 .= " - ";
                    $texto2 .= $pessoal->get_nomeLotacao3($item['lotacao']);
                    $texto2 .= "<br/>";
                }
            }
            #$texto2 .= "</'ul>";

            $dec->set_texto($texto2);
        }

        if (!empty($especifico)) {
            $dec->set_texto("Informamos abaixo as atribuições específicas do(a) servidor(a) nos setores em que atuou: ");
            $texto3 = "<ul>";

            # Pega as tarefas
            $linhas = explode("- ", $especifico);
            foreach ($linhas as $linha) {
                if (!empty($linha)) {
                    $texto3 .= "<li>{$linha}</li>";
                }
            }
            
            $texto3 .= "</ul>";
            $dec->set_texto($texto3);
        }


        $dec->set_texto("Conforme Lei Estadual 4.800/06 de 29/06/06, publicada DOERJ em 30/06/06"
                . " e Resolução CONSUNI 005/06 de 08/07/2006, publicada DOERJ em 19/10/2006,"
                . "o cargo de $cargoEfetivo possui as seguintes atribuições:");

//        $dec->set_texto("Atribuições da Área");
//
//        $dec->set_texto($atribuicoesArea);
//        $dec->set_texto("Atribuições do Cargo / Função");

        if (empty($atribuicoesCargo)) {
            $dec->set_texto("(Não há atribuições cadastradas para esse cargo / função.)");
        } else {
            $dec->set_texto(formataAtribuicao($atribuicoesCargo), false);
        }

        #$dec->set_texto("Outrossim, declaramos que esta Universidade Estadual do Norte Fluminense Darcy Ribeiro – UENF"
        #              . " é portadora do CNPJ nº 04.809.688/0001-06, com sede na Av. Alberto"
        #              . " Lamego, 2.000, Parque Califórnia – Campos dos Goytacazes – RJ, CEP: 28.013-602.");

        $dec->set_texto("O(A) servidor(a) em tela cumpre a carga horária de 40 horas semanais.");
        $dec->set_texto("Sendo expressão da verdade, subscrevemo-nos.");

        $dec->set_rodapeSoUntimaPag(true);

        # Pega o idServidor do gerente GRH
        $idGerente = $pessoal->get_gerente(66);

        if ($assina == $idGerente) {
            $nome = $pessoal->get_nome($idGerente);
            $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
            $idFuncional = $pessoal->get_idFuncional($idGerente);
            $dec->set_assinatura(true);
        } else {
            $nome = $pessoal->get_nome($assina);

            if (empty($pessoal->get_cargoComissaoDescricao($assina))) {
                $cargo = $pessoal->get_cargoSimples($assina);
            } else {
                $cargo = $pessoal->get_cargoComissaoDescricao($assina);
            }
            $idFuncional = $pessoal->get_idFuncional($assina);
        }

        $dec->set_origemNome($nome);
        $dec->set_origemDescricao($cargo);
        $dec->set_origemIdFuncional($idFuncional);

        $listaServidor = $pessoal->select('SELECT tbservidor.idServidor,
                                              tbpessoa.nome
                                         FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                         LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        WHERE situacao = 1
                                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                          AND tblotacao.idlotacao = 66
                                          ORDER BY tbpessoa.nome');

        $dec->set_formCampos(array(
            array('nome' => 'assina',
                'label' => 'Assinatura:',
                'tipo' => 'combo',
                'array' => $listaServidor,
                'size' => 30,
                'padrao' => $assina,
                'title' => 'Quem assina o documento',
                'onChange' => 'formPadrao.submit();',
                'col' => 10,
                'linha' => 1),
//            array('nome' => 'especifico',
//                'label' => 'Atribuições Específicas: (Inicie as tarefas com -)',
//                'tipo' => 'textarea',
//                'size' => array(80, 6),
//                'padrao' => $especifico,
//                'title' => 'Atribuições Específicas.',
//                'onChange' => 'formPadrao.submit();',
//                'linha' => 2),
        ));

        $dec->set_formLink("?");

        $dec->show();

        # Grava o log da visualização do relatório
        $data = date("Y-m-d H:i:s");
        $atividades = 'Visualizou a declaração de atribuições do cargo';
        $tipoLog = 4;
        $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);
    } else {
        br(4);
        p("A Declaração de Atribuições é somente para Servidores Concursados", "f14", "center");
    }

    $page->terminaPagina();
}