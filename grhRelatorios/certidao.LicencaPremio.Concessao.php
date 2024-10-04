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

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
$licencaPremio = new LicencaPremio();

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Calcula as datas padrão
    $dtInicial = $licencaPremio->get_dataProximoPeriodo($idServidorPesquisado);

    if (empty($dtInicial)) {
        $dtFinal = null;
    } else {
        $dtFinal = addDias($dtInicial, 1825, false);
    }

    # Pega os parâmetros
    $parametroMeses = post('parametroMeses', get_session('parametroMeses', 1));
    $parametroDtInicial = post('parametroDtInicial', get_session('parametroDtInicial', date_to_bd($dtInicial)));
    $parametroDtFinal = post('parametroDtFinal', get_session('parametroDtFinal', date_to_bd($dtFinal)));
    $parametroPreenchido = post('parametroPreenchido', get_session('parametroPreenchido'));
    $parametroAcordo = post('parametroAcordo', get_session('parametroAcordo'));
    
    # Verifica a fase do programa
    $fase = get('fase');

    # Joga os parâmetros par as sessions
    set_session('parametroMeses', $parametroMeses);
    set_session('parametroDtInicial', $parametroDtInicial);
    set_session('parametroDtFinal', $parametroDtFinal);
    set_session('parametroPreenchido', $parametroPreenchido);
    set_session('parametroAcordo', $parametroAcordo);
    
    # Rotina em Jscript
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){                
                  
                // Quando muda a data Inicial
                $("#parametroDtInicial").change(function(){
                   
                    var dt1 = $("#parametroDtInicial").val();
                    var numDias = 1825;
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#parametroDtFinal").val(formatado);
                                   
                });
            });
        </script>';

    # Começa uma nova página
    $page = new Page();
    if ($fase == "") {
        $page->set_jscript($script);
        echo "oi";
    }
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    ###

    switch ($fase) {
        case "":
            # Título
            titulo("Certidão de Concessão para Licença Prêmio");
            br();

            callout("Rotina em Desenvolvimento!!<br/>Ainda não está pronta", "alert");

            # Pega os dados da combo preenchido e de acordo
            $select = 'SELECT tbpessoa.nome, tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                     ORDER BY tbpessoa.nome asc';

            $relacao = $pessoal->select($select);
            array_unshift($relacao, [null, null]);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguarde');

            $controle = new Input('parametroMeses', 'combo', 'Mes(es):', 1);
            $controle->set_size(8);
            $controle->set_title('Meses que Tem Direito');
            $controle->set_valor($parametroMeses);
            #$controle->set_array([3, 6, 9]);
            $controle->set_array([3]);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $controle = new Input('parametroDtInicial', 'data', 'Data Inicial do Período:', 1);
            $controle->set_size(200);
            $controle->set_title('Informe a data inicial do período');
            $controle->set_valor($parametroDtInicial);
            $controle->set_linha(2);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroDtFinal', 'data', 'Data Final do Período:', 1);
            $controle->set_size(200);
            $controle->set_title('Informe a data final do período');
            $controle->set_valor($parametroDtFinal);
            $controle->set_linha(2);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroPreenchido', 'combo', 'Preenchido por:', 1);
            $controle->set_size(200);
            $controle->set_title('quem Preencheu');
            $controle->set_valor($parametroPreenchido);
            $controle->set_array($relacao);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroAcordo', 'combo', 'De Acordo:', 1);
            $controle->set_size(200);
            $controle->set_title('de Acordo');
            $controle->set_valor($parametroAcordo);
            $controle->set_array($relacao);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();
            break;

        #######

        case "aguarde":
            if (empty($parametroMeses)
                    OR empty($parametroDtInicial)
                    OR empty($parametroDtFinal)
                    OR empty($parametroPreenchido)
                    OR empty($parametroAcordo)) {

                alert("Todos os campos devem ser preenchidos !!");
                back(1);
            } else {
                br(4);
                aguarde();
                br();

                # Limita a tela
                $grid1 = new Grid("center");
                $grid1->abreColuna(5);
                p("Aguarde...", "center");
                $grid1->fechaColuna();
                $grid1->fechaGrid();

                loadPage('?fase=folha');
            }


            break;

        #######

        case "folha" :

            ######
            # Trata os parêmetros
            $extenso = numero_to_letra($parametroMeses);
            $data1 = date_to_php($parametroDtInicial);
            $data2 = date_to_php($parametroDtFinal);

            # Monta o texto
            $texto = "O servidor faz jus à concessão de <b>{$parametroMeses} ($extenso) meses</b>"
                    . " de Licença Prêmio relativo ao período de "
                    . "<b>{$data1} - {$data2}</b>";

            # Monta a Declaração
            $dec = new Declaracao();
            $dec->set_declaracaoNome("CERTIDÃO DE CONCESSÃO PARA LICENÇA PRÊMIO");
            $dec->set_texto("ID funcional nº: <b>{$pessoal->get_idFuncional($idServidorPesquisado)}</b>");
            $dec->set_texto("Nome: <b>" . strtoupper($pessoal->get_nome($idServidorPesquisado)) . "</b>");
            $dec->set_texto("Processo de Contagem: <b>{$licencaPremio->get_numProcessoContagem($idServidorPesquisado)}</b>");
            $dec->set_texto("Cargo: <b>{$pessoal->get_cargoSimples($idServidorPesquisado)}</b>");
            $dec->set_texto("Nível: <b>{$pessoal->get_nivelSalarialCargo($idServidorPesquisado)}</b>");
            $dec->set_texto("Assunto: <b>Licença Prêmio</b>");
            $dec->set_texto("( x ) Concessão&nbsp;&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;) Recontagem&nbsp;&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;) Indeferimento");
            $dec->set_texto("<hr/>");
            $dec->set_texto("Fundamento: Art. 129 do decreto2479/79, combinado coma Lei 1054/86");
            $dec->set_texto("<hr/>");
            $dec->set_texto("Sr. Gerente de Recursos Humanos,");
            $dec->set_texto($texto);
            $dec->set_texto("");
            $dec->set_texto("Preenchido por: <b>{$parametroPreenchido}</b>");
            $dec->set_texto("De Acordo: <b>{$parametroAcordo}</b>");
            $dec->set_saltoAssinatura(2);

            $dec->set_exibeData(false);
            $dec->set_exibeAssinatura(false);
            $dec->show();

            # Grava o log da visualização do relatório
            $data = date("Y-m-d H:i:s");
            $atividades = 'Visualizou a certidão de concessão de licença prêmio';
            $tipoLog = 4;
            $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}    