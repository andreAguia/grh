<?php

/**
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

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroSexo = get_session('parametroSexo', "Feminino");
    $parametroNome = get_session('parametroNome');
    $parametroLotacao = get_session('parametroLotacao');
    $subtitulo = null;

    # Lotação
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    } else {
        if (!is_numeric($parametroLotacao) AND!is_null($parametroLotacao)) {
            $subtitulo .= "{$parametroLotacao}<br/>";
        }
    }

    if (!is_null($parametroNome)) {
        $subtitulo .= "Filtro nome: {$parametroNome}";
    }

    ######
    # Monta o select
    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbpessoa.sexo = '{$parametroSexo}'";

    if (!is_null($parametroLotacao)) {  // senão verifica o da classe
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    if (!is_null($parametroNome)) {
        $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
    }

    $select .= " ORDER BY lotacao,tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: ' . $parametroSexo);

    if (!is_null($subtitulo)) {
        $relatorio->set_subtitulo($subtitulo);
    }

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Integral', 'Proporcional', 'Compulsória'));
    #$tabela->set_width(array(30,15,15,15,15));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcaoDepoisClasse(array(null, null, null, null, "marcaSePassou", "marcaSePassou", "marcaSePassou"));

    $relatorio->set_classe(array(null, null, "pessoal", null, "Aposentadoria", "Aposentadoria", "Aposentadoria"));
    $relatorio->set_metodo(array(null, null, "get_CargoSimples", null, "get_dataAposentadoriaIntegral", "get_dataAposentadoriaProporcional", "get_dataAposentadoriaCompulsoria"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    $page->terminaPagina();
}    