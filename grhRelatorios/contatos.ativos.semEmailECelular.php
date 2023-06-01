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

    $relatorio = new Relatorio();

    $select = 'SELECT tbservidor.idServidor,        
                      concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao, 
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> "Outros"
                  AND ((telCelular IS NULL) OR (telCelular = "") OR
                       (emailUenf IS NULL) OR (emailUenf = ""))
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $relatorio->set_numGrupo(1);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
            $relatorio->set_numGrupo(1);
        }
    }

    if (is_null($lotacao)) {
        $select .= ' ORDER BY tbpessoa.nome';
    } else {
        $select .= ' ORDER BY DIR, GER, tbpessoa.nome';
    }

    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Servidores Ativos');
    $relatorio->set_tituloLinha2('Sem E-mail Institucional ou Celular Cadastrado');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pelo Nome');
    $relatorio->set_label(['Servidor', 'Lotação', 'Telefones', 'Emails']);
    $relatorio->set_classe(["pessoal", null, "pessoal", "pessoal"]);
    $relatorio->set_metodo(["get_nomeECargo", null, "get_telefones", "get_emails"]);
    $relatorio->set_align(["left","left","left","left"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    array_unshift($listaLotacao, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'title' => 'Mês',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}