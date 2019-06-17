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

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbservidor.idServidor,
                    tbpessoa.nome,
                    tbcomissao.dtNom,
                    tbcomissao.dtPublicNom,
                    tbcomissao.idComissao,
                    idPerfil,
                    concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
               FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                               LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                    JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                    JOIN tbdescricaocomissao  USING (idDescricaoComissao)    
           WHERE tbtipocomissao.ativo IS TRUE
             AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)                    
           ORDER BY tbtipocomissao.simbolo, tbdescricaocomissao.descricao, tbcomissao.dtNom desc';

    $result = $pessoal->select($select);
    
    $label = array('Id / Matrícula','Nome','Nomeação','Publicação','Nome do Cargo','Perfil','Cargo');
    $align = array("center","left","center","center","left","center");
    $function = array("idMatricula",NULL,"date_to_php","date_to_php","descricaoComissao");
    $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal");
    $metodo = array(NULL,NULL,NULL,NULL,NULL,"get_perfil");

    # Monta a tabela
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);
    $relatorio->set_label($label);
    $relatorio->set_titulo("Relatório Servidores Comissionados");
    $relatorio->set_align($align);
    $relatorio->set_funcao($function);
    $relatorio->set_classe($classe);
    $relatorio->set_metodo($metodo);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}