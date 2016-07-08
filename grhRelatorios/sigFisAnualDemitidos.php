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

    ###### Relatório 1
    
    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbfuncionario.matricula,
                      tbperfil.nome,
                      tbfuncionario.dtAdmissao,
                      tbfuncionario.dtDemissao,
                      tbfuncionario.dtPublicExo,
                      MONTH(tbfuncionario.dtDemissao)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                
                                    LEFT JOIN tbperfil ON(tbfuncionario.idPerfil = tbperfil.idPerfil)
                                    LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                WHERE YEAR(tbfuncionario.dtDemissao) = "'.$relatorioAno.'"
             ORDER BY MONTH(tbfuncionario.dtDemissao), dtDemissao';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Demitidos e Exonerados em '.$relatorioAno);
    $relatorio->set_tituloLinha2('Demitidos da Fenorte');
    $relatorio->set_subtitulo('Ordenado pela Data de Demissão');

    $relatorio->set_label(array('Matrícula','Id','Nome','CPF','Nascimento','Cargo','Perfil','Admissão','Demissão','Publicação','Mês'));
    $relatorio->set_width(array(7,5,28,10,10,20,8,10,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,null,"date_to_php",null,null,"date_to_php","date_to_php","date_to_php","get_NomeMes"));
    
    $relatorio->set_classe(array(null,null,null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,null,null,null,"get_cargo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(10);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_dataImpressao(false);
    #$relatorio->set_totalRegistro(false);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    ###### Relatório 2
    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      CONCAT(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                      tbperfil.nome,                  
                      tbcomissao.dtExo,
                      tbcomissao.dtPublicExo,
                      MONTH(tbcomissao.dtExo)
                 FROM tbfuncionario JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                
                                    JOIN tbperfil ON(tbfuncionario.idPerfil = tbperfil.idPerfil)
                                    JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                    LEFT JOIN tbcomissao ON (tbfuncionario.matricula = tbcomissao.matricula)
                                    JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                WHERE YEAR(tbcomissao.dtExo) = "'.$relatorioAno.'"
             ORDER BY MONTH(tbcomissao.dtExo), tbcomissao.dtExo';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo(null);
    $relatorio->set_tituloLinha2('Exonerados em um Cargo em Comissao');
    $relatorio->set_subtitulo('Ordenado pela Data de Exoneração');

    $relatorio->set_label(array('Matrícula','Id','Nome','CPF','Nascimento','Cargo','Perfil','Exoneração','Publicação','Mês'));
    $relatorio->set_width(array(7,5,28,10,10,20,8,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,null,"date_to_php",null,null,"date_to_php","date_to_php","get_NomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $page->terminaPagina();
}