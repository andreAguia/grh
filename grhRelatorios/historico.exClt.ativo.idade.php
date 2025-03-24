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

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    if ($lotacao == "*") {
        $lotacao = null;
    }

    $subTitulo = null;

    ######

    $select = 'SELECT tbservidor.idFuncional, 
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      dtNasc,
                      DATE_FORMAT(tbpessoa.dtNasc, "%d/%m/%Y")             
                FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.dtAdmissao < "2002/01/01"
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbpessoa.dtNasc, dtAdmissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ex-CLT Ativos');
    $relatorio->set_tituloLinha2('Admitidos antes de 01/01/2002');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pela Idade');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'Admissão', 'Nascimento,', 'Idade']);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", "date_to_php", "idade"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_LotacaoSimples"]);

    $relatorio->set_conteudo($result);

    $relatorio->show();
    $page->terminaPagina();
}