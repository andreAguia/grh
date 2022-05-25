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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idServidor,
                             tbpessoa.nome,
                             tbcomissao.dtNom,
                             tbcomissao.dtExo,
                             tbcomissao.idComissao,
                             idPerfil,
                             concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)                                        
                                             JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                             JOIN tbdescricaocomissao USING (idDescricaoComissao)
                    WHERE tbtipocomissao.ativo IS true                         
                    ORDER BY tbtipocomissao.simbolo, tbdescricaocomissao.descricao, tbcomissao.dtNom desc';

    $result = $pessoal->select($select);

    $label = array('Id / Matrícula', 'Nome', 'Nomeação', 'Exoneração', 'Nome do Cargo', 'Perfil', 'Cargo');
    $align = array("center", "left", "center", "center", "left", "center");
    $function = array("idMatricula", null, "date_to_php", "date_to_php", "descricaoComissao");
    $classe = array(null, null, null, null, null, "Pessoal");
    $metodo = array(null, null, null, null, null, "get_perfil");

    # Monta a tabela
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);
    $relatorio->set_label($label);
    $relatorio->set_titulo("Relatório Geral do Histórico de Servidores Comissionados");
    $relatorio->set_align($align);
    $relatorio->set_funcao($function);
    $relatorio->set_classe($classe);
    $relatorio->set_metodo($metodo);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}