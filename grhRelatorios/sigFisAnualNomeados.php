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
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano',date('Y'));

    ######

    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      CONCAT(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                      tbperfil.nome,                  
                      tbcomissao.dtNom,
                      tbcomissao.dtPublicNom,
                      MONTH(tbcomissao.dtNom)
                 FROM tbfuncionario JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                
                                    JOIN tbperfil ON(tbfuncionario.idPerfil = tbperfil.idPerfil)
                                    JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                    LEFT JOIN tbcomissao ON (tbfuncionario.matricula = tbcomissao.matricula)
                                    JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                WHERE YEAR(tbcomissao.dtNom) = "'.$relatorioAno.'"
             ORDER BY MONTH(tbcomissao.dtNom), tbcomissao.dtNom';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Nomeados em '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Nomeação');

    $relatorio->set_label(array('Matrícula','Id','Nome','CPF','Nascimento','Cargo','Perfil','Nomeação','Publicação','Mês'));
    $relatorio->set_width(array(7,5,28,10,10,20,8,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,null,"date_to_php",null,null,"date_to_php","date_to_php","get_NomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'padrao' => $relatorioAno,
                         'onChange' => 'formPadrao.submit();',
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}