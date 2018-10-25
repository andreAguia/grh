<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao',post('lotacao'));
    
    ######
    
    $select ='SELECT nome,
                     tbdocumentacao.cpf
                FROM tbsispatri JOIN tbdocumentacao USING (cpf)
            ORDER BY nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Aniversariantes');
    $relatorio->set_subtitulo($servidor->get_nomeCompletoLotacao($lotacao));
    $relatorio->set_tituloLinha2($servidor->get_nomeLotacao($lotacao));
    $relatorio->set_label(array('Nome','CPF'));
    $relatorio->set_width(array(10,90));
    $relatorio->set_align(array("center","left"));
    #$relatorio->set_funcao(array(NULL,NULL,"get_nomeMes"));
    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    
    $result = $servidor->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($result,array('*','-- Todos --'));

    $relatorio->set_formCampos(array(
                               array ('nome' => 'lotacao',
                                      'label' => 'Lotação:',
                                      'tipo' => 'combo',
                                      'array' => $result,
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