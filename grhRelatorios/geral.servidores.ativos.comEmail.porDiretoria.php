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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

# Parametros
$parametroLotacao = post("parametroLotacao", "DGA");

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Servidores Ativos - {$parametroLotacao}");
    $page->iniciaPagina();

    ######

    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> 'Outros'
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    $select .= " ORDER BY lotacao, tbpessoa.nome";

    $result = $servidor->select($select);

    $classeLot = new Lotacao();

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_tituloLinha2($classeLot->get_nomeDiretoriaSigla($parametroLotacao));
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Email']);
    $relatorio->set_align(["center", "left", "left", "left", "left"]);

    $relatorio->set_classe([null, null, "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_CargoSimples", null, "get_emailUenf"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);

    $listaLotacao = $servidor->select('SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo');
    array_unshift($listaLotacao, array('*', 'Escolha a Lotação'));

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 20,
            'padrao' => $parametroLotacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
    ));

    $relatorio->show();
    $page->terminaPagina();
}
