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
    $sexo = get('sexo', post('sexo', "Feminino"));

    if ($lotacao == "*") {
        $lotacao = null;
    }

    $subTitulo = null;

    if ($sexo == "Feminino") {
        $texto = "Data com<br/>62 anos";
    } else {
        $texto = "Data com<br/>65 anos";
    }

######

    $select = 'SELECT tbservidor.idFuncional, 
                      tbpessoa.nome,
                      tbpessoa.sexo,
                      DATE_FORMAT(tbpessoa.dtNasc, "%d/%m/%Y"),
                      IF(tbpessoa.sexo = "Feminino", DATE_ADD(tbpessoa.dtNasc, INTERVAL 62 YEAR), DATE_ADD(tbpessoa.dtNasc, INTERVAL 65 YEAR)),                     
                      DATE_FORMAT(tbservidor.dtAdmissao, "%d/%m/%Y"),
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      DATE_ADD(tbservidor.dtAdmissao, INTERVAL 5 YEAR),
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor,
                      tbservidor.idservidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1                 
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($sexo)) {
        $select .= 'AND tbpessoa.sexo = "' . $sexo . '"';
    }

    if (!empty($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $subTitulo .= $servidor->get_nomeCompletoLotacao($lotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= $lotacao;
        }
    }

    $select .= ' ORDER BY tbpessoa.sexo, tbpessoa.dtNasc, dtAdmissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Análise de Aposentadoria');
    $relatorio->set_tituloLinha2($subTitulo);
    $relatorio->set_subtitulo('Em Ordem Decrescente de Idade');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Sexo', 'Idade', $texto, 'Data de Admissão', 'Data de Ingresso<br/>no Serv.Público', 'Tempo de Contribuição<br/>até 31/12/2021 (dias)', 'Tempo de Contribuição<br/>Geral (dias)', 'Tempo Averbado<br/>Público (dias)', 'Tempo Averbado<br/>Privado (dias)', "Data com<br/>5 anos de Cargo", "Data com<br/>10 anos Públicos", "Data com<br/>20 anos Públicos", "Data com<br/>25 anos Públicos", "Data com<br/>30 anos Públicos", "Data com<br/>35 anos Públicos"]);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, null, "idade", "date_to_php", null, null, null, null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, null, null, null, null, null, "Aposentadoria", "Aposentadoria", "Aposentadoria", "Averbacao", "Averbacao", null, "Aposentadoria", "Aposentadoria", "Aposentadoria", "Aposentadoria", "Aposentadoria"]);
    $relatorio->set_metodo([null, null, null, null, null, null, "get_dtIngresso", "get_tempoTotalAntes31_12_21", "get_tempoTotal", "get_tempoAverbadoPublico", "get_tempoAverbadoPrivado", null, "get_data10anosPublicos", "get_data20anosPublicos", "get_data25anosPublicos", "get_data30anosPublicos", "get_data35anosPublicos"]);
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
            'col' => 4,
            'linha' => 1),
        array('nome' => 'sexo',
            'label' => 'Sexo:',
            'tipo' => 'combo',
            'array' => [
                ["Masculino", "Masculino"],
                ["Feminino", "Feminino"],
            ],
            'size' => 30,
            'padrao' => $sexo,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
    ));

    $relatorio->set_formFocus('lotacao');
    
    $relatorio->set_numGrupo(2);
    $relatorio->show();

    $page->terminaPagina();
}