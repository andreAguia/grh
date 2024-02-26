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
                      tbservidor.idservidor,
                      DATE_FORMAT(tbpessoa.dtNasc, "%d/%m/%Y") ,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      DATE_ADD(tbservidor.dtAdmissao, INTERVAL 5 YEAR),                      
                      tbservidor.idservidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!empty($lotacao)) {
# Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $servidor->get_nomeCompletoLotacao($lotacao) . "<br/>";
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
        }
    }

    $select .= ' ORDER BY tbpessoa.dtNasc, dtAdmissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo($subTitulo . 'Análise de Aposentadoria<br/>Em Ordem Decrescente de Idade');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Data de Ingresso<br/>no Serv.Público', 'Idade', 'Tempo de Contribuição<br/>até 31/12/2021 (dias)', 'Tempo de Contribuição<br/>Geral (dias)', 'Tempo Averbado<br/>Público (dias)', 'Tempo Averbado<br/>Privado (dias)', "Data com<br/>5 anos de Cargo", "Data com<br/>20 anos Públicos"]);
#$relatorio->set_width([30, 20, 25, 15, 10]);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, null, "idade", null, null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, null, "Aposentadoria", null, "Aposentadoria", "Aposentadoria", "Averbacao", "Averbacao", null, "Aposentadoria"]);
    $relatorio->set_metodo([null, null, "get_dtIngresso", null, "get_tempoTotalAntes31_12_21", "get_tempoTotal", "get_tempoAverbadoPublico", "get_tempoAverbadoPrivado", null, "get_data20anosPublicos"]);
    $relatorio->set_conteudo($result);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($listaLotacao, array('*', '-- Selecione a Lotação --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}