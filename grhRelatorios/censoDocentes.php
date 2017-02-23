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
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbcargo.nome,
                     tbdocumentacao.cpf,
                     "nomedaMae",
                     tbpessoa.dtNasc,
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbdocumentacao USING (idPessoa)
                                LEFT JOIN tbcargo USING (idCargo)
               WHERE tbservidor.situacao = 1
                 AND (idCargo = 128 OR idCargo = 129)
            ORDER BY idCargo,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Docentes');
    #$relatorio->set_subtitulo('Agrupados por Lotação - Com CPF - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','CPF','Mãe','Nascimento','Admissão'));
    #$relatorio->set_width(array(10,20,20,10,20,10,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(null,null,null,null,null,"date_to_php","date_to_php"));
    
    #$relatorio->set_classe(array(null,null,"pessoal"));
    #$relatorio->set_metodo(array(null,null,"get_Cargo"));
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}