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

    # Pega os parâmetros  
    $parametroLotacao = get_session('parametroLotacao', 'Todos');
    $parametroVacinado = get_session('parametroVacinado', 'Todos');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    /*
     * Vacinados
     */

    if ($parametroVacinado == "Sim") {

        $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        $select .= " AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina)
                ORDER BY lotacao, tbpessoa.nome";

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo('Relatório de Servidores Vacinados');
        $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Lotação", "Vacinas"]);
        $relatorio->set_width([10, 30, 30, 0, 30]);
        $relatorio->set_align(["center", "left", "left", "left", "left"]);

        $relatorio->set_classe([null, null, "pessoal", null, "Vacina"]);
        $relatorio->set_metodo([null, null, "get_cargoSimples", null, "exibeVacinas"]);

        $relatorio->set_conteudo($result);
        $relatorio->set_numGrupo(3);
        #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
        $relatorio->show();
    }

    ######

    /*
     * Não Vacinados
     */

    if ($parametroVacinado == "Não") {

        $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        $select .= " AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina)
                ORDER BY lotacao, tbpessoa.nome";

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo('Relatório de Servidores Não Vacinados');
        $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Lotação"]);
        $relatorio->set_width([10, 45, 45, 0]);
        $relatorio->set_align(["center", "left", "left"]);

        $relatorio->set_classe([null, null, "pessoal"]);
        $relatorio->set_metodo([null, null, "get_cargoSimples"]);

        $relatorio->set_conteudo($result);
        $relatorio->set_numGrupo(3);
        #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
        $relatorio->show();
    }

    ######

    /*
     * Todos
     */

    if ($parametroVacinado == "Todos") {

        $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        $select .= " ORDER BY lotacao, tbpessoa.nome";

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo('Relatório de Vacinação dos Servidores');
        $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Lotação", "Vacinas"]);
        $relatorio->set_width([10, 30, 30, 0, 30]);
        $relatorio->set_align(["center", "left", "left", "left", "left"]);

        $relatorio->set_classe([null, null, "pessoal", null, "Vacina"]);
        $relatorio->set_metodo([null, null, "get_cargoSimples", null, "exibeVacinas"]);

        $relatorio->set_conteudo($result);
        $relatorio->set_numGrupo(3);
        #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
        $relatorio->show();
    }

    ######


    $page->terminaPagina();
}