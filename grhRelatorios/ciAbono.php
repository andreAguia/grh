<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $servidor = new Pessoal();
    $aposentadoria = new Aposentadoria();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $parametroSexo = get_session('parametroSexo', "Feminino");
    $parametroLotacao = get_session('parametroLotacao');
    $parametroCargo = get_session('parametroCargo');
    $parametroProcesso = get_session('parametroProcesso');

    # Pega os parâmetros do formulário
    $ci = post('ci');
    $chefia = post('chefia');
    $textoCi = post('textoCi');

    # Trata valores
    if ($parametroLotacao == "*") {
        $nomeLotacao == null;
        $parametroLotacao = null;
    } else {
        $nomeLotacao = $servidor->get_nomeLotacao2($parametroLotacao);
    }

    if ($parametroCargo == "*") {
        $parametroCargo = null;
    }

    if ($parametroProcesso == "*") {
        $parametroProcesso = null;
    }
    
     # Pega as idades de aposentadoria
    if ($parametroSexo == "Feminino") {
        $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.feminino");
    } else {
        $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.masculino");
    }

    # Grava o novo texto nas configurações
    $abono = new Abono();
    $abono->set_textoCi($textoCi);

    # Parâmetro da função
    $parametro = array($nomeLotacao, $ci, $chefia, $textoCi, $parametroLotacao);

    $grid = new Grid();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->abreColuna(10);

    ####

    function exibeTexto($parametro) {

        # Pega os parametros
        $nomeLotacao = $parametro[0];
        $ci = $parametro[1];
        $chefia = $parametro[2];
        $texto = $parametro[3];
        $lotacao = $parametro[4];

        if (!is_null($nomeLotacao)) {

            $servidor = new Pessoal();
            $intra = new Intra();

            $grid = new Grid();
            $grid->abreColuna(5);
            p("CI GRH/DGA/UENF n° $ci/" . date('Y'), "left");
            $grid->fechaColuna();
            $grid->abreColuna(7);
            p("Campos dos Goytacazes, " . dataExtenso(date('d/m/Y')), "right");
            $grid->fechaColuna();
            $grid->fechaGrid();

            $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));

            p("<b>De: Gerência de Recursos Humanos - GRH/UENF</b>", "left");

            if (is_numeric($lotacao)) {
                p("Para: {$chefia}<br/>{$nomeLotacao}", "left");
            } else {
                p("Para: {$chefia} - {$nomeLotacao}", "left");
            }

            p("Prezado(a) Senhor(a)", "left");

            echo $texto;
        }
    }

    ####

    function exibeTextoFinal() {

        $grid = new Grid();
        $grid->abreColuna(3);
        br();
        p("Atenciosamente,", "left");
        $grid->fechaColuna();
        $grid->abreColuna(6);

        # assinatura -> Retirado a pedido de gustavo pois vai assinar digitalmente pelo sei
        #$figura = new Imagem(PASTA_FIGURAS . 'assinatura.png', 'Assinatura do Gerente', 120, 140);
        #$figura->show();
//        $servidor = new Pessoal();
//        $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));
//        $idGerente = $servidor->get_idFuncional($servidor->get_gerente(66));
//        p("$gerenteGrh<br/>Gerente de Recursos Humanos<br/>Id Funcional: $idGerente", "center", "f12");




        $grid->fechaColuna();
        $grid->abreColuna(3);
        $grid->fechaColuna();
        $grid->fechaGrid();
        p("Gerência de Recursos Humanos", "left");

        p("_______________________________________________________________________________<br/>Av. Alberto Lamego 2000 - Parque California - Campos dos Goytacazes/RJ - 28013-602<br/>Tel.: (22) 2739-7064 - correio eletronico: grh@uenf.br", "center", "f12");
    }

    ######
    # Exibe a lista
    $select = "SELECT idFuncional,
                              tbpessoa.nome as servidor,
                              TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                              idServidor,
                              CASE
                                    WHEN status = 1 THEN 'Deferido'
                                    WHEN status = 2 THEN 'Indeferido'
                                    ELSE 'Não Solicitado'
                              END as status
                      FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                      LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                                      LEFT JOIN tbabono USING (idServidor)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND tbpessoa.sexo = '{$parametroSexo}'
                       AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= {$idadeAposent}";

    # lotação
    if (!is_null($parametroLotacao)) {  // senão verifica o da classe
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # cargo
    if (!is_null($parametroCargo)) {
        if (is_numeric($parametroCargo)) {
            $select .= " AND (tbcargo.idcargo = '{$parametroCargo}')";
        } else { # senão é nivel do cargo
            if ($parametroCargo == "Professor") {
                $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
            } else {
                $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
            }
        }
    }

    # Solicitação de abono
    if (!is_null($parametroProcesso)) {
        if ($parametroProcesso == 3) {
            $select .= " AND (status is null)";
        } else {
            $select .= " AND (status = '{$parametroProcesso}')";
        }
    }

    $select .= " ORDER BY idade";

    $result = $servidor->select($select);
    $resultado = [];

    # Percorre o banco para verificar se já pode aposentar
    foreach ($result as $lista) {

        # Pega a data de aposentadoria desse servidor
        $data = $aposentadoria->get_dataAposentadoriaIntegral($lista["idServidor"]);

        # Verifica se a data colhida já passou
        if (jaPassou($data)) {
            $resultado[] = [
                $lista["idFuncional"],
                $lista["servidor"],
                $lista["idServidor"],
                $lista["idServidor"]
            ];
        }
    }

    $relatorio = new Relatorio();
    $relatorio->set_funcaoAntesTitulo('exibeTexto');
    $relatorio->set_funcaoAntesTituloParametro($parametro);

    $relatorio->set_funcaoFinalRelatorio('exibeTextoFinal');

    $relatorio->set_label(array('idFuncional', 'Nome', 'Cargo', 'Lotação'));
    $relatorio->set_width(array(20, 40, 40));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_conteudo($resultado);
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargoSimples", "get_lotacao"));
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}